<?php
require_once __DIR__ . '/db.php';

header("Content-Type: application/json");

// 获取 JSON 输入 get JSON input
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

$identifier = $input['identifier'];  // it could be email  email, matric_no or staff_no
$password = $input['password'];

try {
    $pdo = getPDO();

    // 用 identifier 查询用户（支持 email、matric_no 或 staff_no）
    //using identifier to find user
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

    // login success return user data without password
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
