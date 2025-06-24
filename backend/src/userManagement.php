<?php
require_once __DIR__ . '/db.php';

// Set full CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * --- NEW: System Log Function ---
 * Logs an event to the system_logs table.
 *
 * @param PDO $pdo The database connection.
 * @param int|null $userId The ID of the user performing the action (e.g., the logged-in admin).
 * @param string $action The action being performed (e.g., 'CREATE_USER').
 * @param string $details A description of the event.
 * @param int|null $targetUserId The ID of the user being affected.
 */
function log_system_event($pdo, $userId, $action, $details, $targetUserId = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO system_logs (user_id, action, details, target_user_id, ip_address) VALUES (?, ?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $action, $details, $targetUserId, $ip_address]);
    } catch (PDOException $e) {
        // In a real app, you might log this error to a file instead of echoing
        error_log('Failed to log system event: ' . $e->getMessage());
    }
}

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

// --- Placeholder for the logged-in admin's ID ---
// In a real application, you would get this from a session or token.
$admin_user_id = 1; 

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? null;
        if ($action === 'logs') {
            // --- NEW: Fetch System Logs ---
            try {
                $stmt = $pdo->query("SELECT * FROM system_logs ORDER BY id DESC LIMIT 200");
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'logs' => $logs]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Log fetch error: ' . $e->getMessage()]);
            }
        } else {
            // Existing user fetch logic
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
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        // ... (existing validation code) ...
        if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid JSON data']); exit; }
        if (empty($input['email']) || empty($input['password']) || empty($input['role'])) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Missing required fields']); exit; }

        $email = $input['email'];
        $password = password_hash($input['password'], PASSWORD_BCRYPT);
        $role = $input['role'];
        $name = $input['name'] ?? null;
        $matricNo = $input['matric_no'] ?? null;
        $staffNo = !empty($input['staff_no']) ? $input['staff_no'] : null;
        $phone = $input['phone'] ?? null;
        $status = $input['status'] ?? 'active';
        $profilePic = $input['profile_pic'] ?? null;
        
        try {
            // ... (existing duplicate checks) ...
            
            $sql = "INSERT INTO users (email, password, role, name, matric_no, staff_no, phone, status, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $password, $role, $name, $matricNo, $staffNo, $phone, $status, $profilePic]);

            // --- NEW: Log Event ---
            $new_user_id = $pdo->lastInsertId();
            log_system_event($pdo, $admin_user_id, 'CREATE_USER', "Admin created user '{$name}' (ID: {$new_user_id})", $new_user_id);

            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        // ... (existing validation) ...
        if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid JSON data']); exit; }
        $id = $input['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'User ID required for update']); exit; }
        
        $name = $input['name'] ?? null;
        $phone = $input['phone'] ?? null;
        $status = $input['status'] ?? null;
        $profilePic = $input['profile_pic'] ?? null;

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, status = ?, profile_pic = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $status, $profilePic, $id]);

            // --- NEW: Log Event ---
            log_system_event($pdo, $admin_user_id, 'UPDATE_USER', "Admin updated user '{$name}' (ID: {$id})", $id);

            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'User ID required']); exit; }

        try {
            // --- NEW: Get user info before deleting for logging purposes ---
            $stmt_info = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt_info->execute([$id]);
            $user_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
            $user_name_for_log = $user_info ? $user_info['name'] : 'Unknown';

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount()) {
                // --- NEW: Log Event ---
                log_system_event($pdo, $admin_user_id, 'DELETE_USER', "Admin deleted user '{$user_name_for_log}' (ID: {$id})", $id);
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