<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

class CardController
{
    public function list(): void
    {
        requireAuth();
        $pdo = getDbConnection();

        $search = $_GET['search'] ?? '';
        $game   = $_GET['game'] ?? '';

        $sql    = 'SELECT * FROM cards WHERE 1=1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (LOWER(name_en) LIKE :search OR LOWER(name_pt) LIKE :search OR LOWER(edition_name) LIKE :search)';
            $params['search'] = '%' . mb_strtolower($search) . '%';
        }
        if ($game !== '') {
            $sql .= ' AND card_game = :game';
            $params['game'] = $game;
        }

        $sql .= ' ORDER BY id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode($stmt->fetchAll());
    }

    public function get(int $id): void
    {
        requireAuth();
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare('SELECT * FROM cards WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $card = $stmt->fetch();

        if (!$card) {
            http_response_code(404);
            echo json_encode(['error' => 'Carta não encontrada']);
            return;
        }

        echo json_encode($card);
    }

    public function create(): void
    {
        $user  = requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $errors = $this->validate($input);
        if ($errors) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $pdo  = getDbConnection();
        // MySQL não suporta RETURNING *, então vamos usar lastInsertId() e um novo SELECT
        $stmt = $pdo->prepare('
            INSERT INTO cards (name_en, name_pt, card_game, edition_id, edition_name, rarity, image_url, created_by)
            VALUES (:name_en, :name_pt, :card_game, :edition_id, :edition_name, :rarity, :image_url, :created_by)
        ');
        
        $stmt->execute([
            'name_en'      => trim($input['name_en'] ?? ''),
            'name_pt'      => trim($input['name_pt'] ?? ''),
            'card_game'    => $input['card_game'] ?? '',
            'edition_id'   => $input['edition_id'] ?? '',
            'edition_name' => $input['edition_name'] ?? '',
            'rarity'       => $input['rarity'] ?? '',
            'image_url'    => $input['image_url'] ?? null,
            'created_by'   => $user['id'] ?? null,
        ]);

        $id = $pdo->lastInsertId();
        $stmtFetch = $pdo->prepare('SELECT * FROM cards WHERE id = ?');
        $stmtFetch->execute([$id]);

        http_response_code(201);
        echo json_encode($stmtFetch->fetch());
    }

    public function update(int $id): void
    {
        requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);

        $errors = $this->validate($input);
        if ($errors) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            return;
        }

        $pdo  = getDbConnection();
        $stmt = $pdo->prepare('
            UPDATE cards
            SET name_en = :name_en, name_pt = :name_pt, card_game = :card_game, 
                edition_id = :edition_id, edition_name = :edition_name, rarity = :rarity,
                image_url = :image_url
            WHERE id = :id
        ');
        $stmt->execute([
            'name_en'      => trim($input['name_en'] ?? ''),
            'name_pt'      => trim($input['name_pt'] ?? ''),
            'card_game'    => $input['card_game'] ?? '',
            'edition_id'   => $input['edition_id'] ?? '',
            'edition_name' => $input['edition_name'] ?? '',
            'rarity'       => $input['rarity'] ?? '',
            'image_url'    => $input['image_url'] ?? null,
            'id'           => $id,
        ]);

        $stmtFetch = $pdo->prepare('SELECT * FROM cards WHERE id = ?');
        $stmtFetch->execute([$id]);
        $card = $stmtFetch->fetch();

        if (!$card) {
            http_response_code(404);
            echo json_encode(['error' => 'Carta não encontrada']);
            return;
        }

        echo json_encode($card);
    }

    public function delete(int $id): void
    {
        requireAuth();
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare('DELETE FROM cards WHERE id = :id');
        $stmt->execute(['id' => $id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Carta não encontrada']);
            return;
        }

        echo json_encode(['message' => 'Carta excluída']);
    }

    private function validate(array $input): array
    {
        $errors = [];
        if (empty(trim($input['name_en'] ?? '')))    $errors[] = 'Nome (EN) é obrigatório';
        if (empty($input['card_game'] ?? ''))        $errors[] = 'Card Game é obrigatório';
        if (empty($input['edition_name'] ?? ''))     $errors[] = 'Edição é obrigatória';
        if (empty($input['rarity'] ?? ''))           $errors[] = 'Raridade é obrigatória';
        return $errors;
    }
}
