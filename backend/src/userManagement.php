<?php
require_once __DIR__ . '/db.php';

// 设置完整的 CORS 头部
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'GET':
    try {
        $role = $_GET['role'] ?? null;

        if ($role) {
            $stmt = $pdo->prepare("SELECT id, name, email, role, matric_no, staff_no, phone, status, profile_pic, created_at FROM users WHERE role = ? ORDER BY id DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $pdo->query("SELECT id, name, email, role, matric_no, staff_no, phone, status, profile_pic, created_at FROM users ORDER BY id DESC");
        }

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;


case 'POST':
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    if (empty($input['email']) || empty($input['password']) || empty($input['role'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $email = $input['email'];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);
    $role = $input['role'];
    $name = $input['name'] ?? null;
    $matricNo = $input['matric_no'] ?? null;
    $staffNo = !empty($input['staff_no']) ? $input['staff_no'] : null; // 空字符串变 NULL
    $phone = $input['phone'] ?? null;
    $status = $input['status'] ?? 'active';
    $profilePic = $input['profile_pic'] ?? null;

    try {
        // 检查 email 是否重复
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }

        // 检查 staff_no 是否重复（如果不为空）
        if ($staffNo !== null) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE staff_no = ?");
            $stmt->execute([$staffNo]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Staff number already exists']);
                exit;
            }
        }

        $sql = "INSERT INTO users (email, password, role, name, matric_no, staff_no, phone, status, profile_pic) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $password, $role, $name, $matricNo, $staffNo, $phone, $status, $profilePic]);

        echo json_encode(['success' => true, 'message' => 'User created successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
    break;


    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        // 检查 JSON 解析是否成功
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }
        
        $id = $input['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required for update']);
            exit;
        }

        $name = $input['name'] ?? null;
        $phone = $input['phone'] ?? null;
        $status = $input['status'] ?? null;
        $profilePic = $input['profile_pic'] ?? null;

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, status = ?, profile_pic = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $status, $profilePic, $id]);

            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID required']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount()) {
                echo json_encode(['success' => true, 'message' => 'User deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}