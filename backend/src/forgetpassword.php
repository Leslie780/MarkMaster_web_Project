<?php
session_start();
require_once __DIR__ . '/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 获取 JSON 请求
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['identifier']) || empty($input['new_password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$identifier = $input['identifier'];
$newPassword = $input['new_password'];

try {
    $pdo = getPDO();

    // 查找用户
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE email = :id OR matric_no = :id OR staff_no = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $identifier]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // 加密并更新密码
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
    $updateStmt->execute([
        'password' => $hashedPassword,
        'user_id' => $user['id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
