<?php

function requireAuth(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autenticado']);
        exit;
    }

    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role'],
    ];
}
