<?php

require_once __DIR__ . '/../config/database.php';

class AuthController
{
    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Usuário e senha são obrigatórios']);
            return;
        }

        $pdo  = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        echo json_encode([
            'message'  => 'Login realizado com sucesso',
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        echo json_encode(['message' => 'Logout realizado']);
    }

    public function me(): void
    {
        require_once __DIR__ . '/../middleware/auth.php';
        $user = requireAuth();
        echo json_encode($user);
    }
}
