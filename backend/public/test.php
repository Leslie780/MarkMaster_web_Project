<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test basic PHP functionality
$phpTest = [
    'php_version' => PHP_VERSION,
    'server_time' => date('Y-m-d H:i:s'),
    'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'server_port' => $_SERVER['SERVER_PORT']
];

// Test database connection
try {
    require_once __DIR__ . '/../src/db.php';
    $dbTest = testDatabaseConnection();
} catch (Exception $e) {
    $dbTest = [
        'success' => false,
        'message' => 'Failed to load database: ' . $e->getMessage()
    ];
}

// Test file structure
$fileTest = [
    'current_directory' => __DIR__,
    'login_php_exists' => file_exists(__DIR__ . '/login.php'),
    'db_php_exists' => file_exists(__DIR__ . '/../src/db.php'),
    'index_php_exists' => file_exists(__DIR__ . '/index.php')
];

echo json_encode([
    'success' => true,
    'message' => 'Test completed',
    'php_info' => $phpTest,
    'database_test' => $dbTest,
    'file_structure' => $fileTest
], JSON_PRETTY_PRINT);
?>