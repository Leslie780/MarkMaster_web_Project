<?php
require_once __DIR__ . '/db.php';

// 接收 JSON 输入
$input = json_decode(file_get_contents('php://input'), true);

// 校验必要字段
if (!$input || empty($input['email']) || empty($input['password']) || empty($input['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// 提取字段
$email = $input['email'];
$password = password_hash($input['password'], PASSWORD_BCRYPT);
$role = $input['role'];
$name = $input['name'] ?? null;
$matricNo = $input['matricNo'] ?? null;
$staffNo = $input['staffNo'] ?? null;
$phone = $input['phone'] ?? null;
$profilePicBase64 = $input['profile_pic'] ?? null;
$profilePicUrl = null;

// 函数：将 base64 图像保存为文件
function saveBase64Image($base64String, $uploadDir = 'uploads/') {
    if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
        return null; // 非法 base64 图片
    }

    $ext = $matches[1];
    $data = substr($base64String, strpos($base64String, ',') + 1);
    $data = base64_decode($data);

    if (!$data) return null;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('avatar_', true) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    file_put_contents($filepath, $data);

    return 'http://localhost:8085/' . $filepath; // 返回可访问的 URL 路径
}

if ($profilePicBase64) {
    $savedUrl = saveBase64Image($profilePicBase64);
    if ($savedUrl) {
        $profilePicUrl = $savedUrl;
    }
}

try {
    $pdo = getPDO();

    // 检查邮箱是否已存在
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }

    // 插入用户记录
    $sql = "INSERT INTO users (
                email, password, role, matric_no, staff_no, name, phone, profile_pic, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $email,
        $password,
        $role,
        $matricNo,
        $staffNo,
        $name,
        $phone,
        $profilePicUrl,
        'active'
    ]);

    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
