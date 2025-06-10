<?php
require_once __DIR__ . '/db.php';

//  Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['email']) || empty($input['password']) || empty($input['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$email = $input['email'];
$password = password_hash($input['password'], PASSWORD_BCRYPT);
$role = $input['role'];
$matricNo = $input['matricNo'] ?? null;
$staffNo = $input['staffNo'] ?? null;

try {
    $pdo = getPDO();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Insert new user
    $sql = "INSERT INTO users (email, password, role, matric_no, staff_no) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $password, $role, $matricNo, $staffNo]);

    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
