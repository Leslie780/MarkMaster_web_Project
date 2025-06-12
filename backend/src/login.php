<?php
session_start(); // 开启 session

require_once __DIR__ . '/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['identifier']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$identifier = $input['identifier'];
$password = $input['password'];

try {
    $pdo = getPDO();

    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE email = :id OR matric_no = :id OR staff_no = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit;
    }

    // 登录成功 - 存入 SESSION
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];

    // 构造返回 JSON
    unset($user['password']); // 安全考虑

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
            'staff_no' => $user['staff_no'] ?? null
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
