<?php

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$uri = preg_replace('#^/backend(/index\.php)?#', '', $uri);
$uri = '/' . ltrim($uri, '/');

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/CardController.php';

$auth = new AuthController();
$card = new CardController();

if ($uri === '/api/login' && $method === 'POST') {
    $auth->login();
} elseif ($uri === '/api/logout' && $method === 'POST') {
    $auth->logout();
} elseif ($uri === '/api/me' && $method === 'GET') {
    $auth->me();
} elseif ($uri === '/api/cards' && $method === 'GET') {
    $card->list();
} elseif ($uri === '/api/cards' && $method === 'POST') {
    $card->create();
} elseif (preg_match('#^/api/cards/(\d+)$#', $uri, $m) && $method === 'GET') {
    $card->get((int) $m[1]);
} elseif (preg_match('#^/api/cards/(\d+)$#', $uri, $m) && $method === 'PUT') {
    $card->update((int) $m[1]);
} elseif (preg_match('#^/api/cards/(\d+)$#', $uri, $m) && $method === 'DELETE') {
    $card->delete((int) $m[1]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Rota não encontrada']);
}
