<?php
// public/index.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// router logic
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    case '/register':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/register.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/login':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/login.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
        break;
}
