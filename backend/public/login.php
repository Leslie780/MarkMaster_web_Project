<?php
session_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug: Check current directory and file paths
$currentDir = __DIR__;
$parentDir = dirname(__DIR__);
$dbPath1 = $parentDir . '/src/db.php';
$dbPath2 = $currentDir . '/../src/db.php';

// Try multiple path possibilities
$possiblePaths = [
    $parentDir . '/src/db.php',
    $currentDir . '/../src/db.php',
    $currentDir . '/../../backend/src/db.php',
    '/Users/arnobrizwan/Documents/GitHub/MarkMaster_web_Project/backend/src/db.php'
];

$dbPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $dbPath = $path;
        break;
    }
}

if (!$dbPath) {
    // If we can't find db.php, return debug info
    echo json_encode([
        'success' => false,
        'message' => 'Cannot find db.php file',
        'debug' => [
            'current_dir' => $currentDir,
            'parent_dir' => $parentDir,
            'tried_paths' => $possiblePaths,
            'files_in_current_dir' => scandir($currentDir),
            'files_in_parent_dir' => is_dir($parentDir) ? scandir($parentDir) : 'Parent dir not found',
            'files_in_src_dir' => is_dir($parentDir . '/src') ? scandir($parentDir . '/src') : 'Src dir not found'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Include the db.php file
require_once $dbPath;

// Log incoming request for debugging
error_log("Login attempt from: " . $_SERVER['REMOTE_ADDR']);

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['identifier']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$identifier = trim($input['identifier']);
$password = $input['password'];

try {
    $pdo = getPDO();
    
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE email = ? OR matric_no = ? OR staff_no = ?
        LIMIT 1
    ");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];

    // Remove password before returning user info
    unset($user['password']);

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'code' => 200,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'matric_no' => $user['matric_no'] ?? null,
            'staff_no' => $user['staff_no'] ?? null,
            'phone' => $user['phone'] ?? null,
            'profile_pic' => $user['profile_pic'] ?? null
        ]
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>