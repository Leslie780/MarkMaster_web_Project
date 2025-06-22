<?php
require_once __DIR__ . '/db.php';

// Set JSON header
header("Content-Type: application/json");

// Log raw input (for debugging)
$input = json_decode(file_get_contents('php://input'), true);
error_log("Register.php reached with data: " . json_encode($input));

// Basic input validation
if (!$input || empty($input['email']) || empty($input['password']) || empty($input['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$email = $input['email'];
$password = password_hash($input['password'], PASSWORD_BCRYPT);
$role = $input['role'];
$name = $input['name'] ?? null;
$matricNo = $input['matricNo'] ?? null;
$staffNo = $input['staffNo'] ?? null;
$phone = $input['phone'] ?? null;
$profilePic = $input['profile_pic'] ?? null;

try {
    $pdo = getPDO();

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    $errorInfo = $stmt->errorInfo();
if ($errorInfo[0] !== '00000') {
    error_log('PDO INSERT error: ' . print_r($errorInfo, true));
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database insert failed: ' . $errorInfo[2]
    ]);
    exit;
}

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // Insert new user
    $sql = "INSERT INTO users (email, password, role, matric_no, staff_no, name, phone, profile_pic)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $password, $role, $matricNo, $staffNo, $name, $phone, $profilePic]);

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage()); // Log detailed DB error
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}