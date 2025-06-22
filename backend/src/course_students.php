<?php
require_once __DIR__ . '/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");


// 预检请求响应
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['student_id'], $input['course_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing student_id or course_id']);
            exit;
        }

        $studentId = $input['student_id'];
        $courseId = $input['course_id'];

        // ✅ 删除学生操作（action 为 delete）
        if (isset($input['action']) && $input['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM course_students WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$studentId, $courseId]);

            echo json_encode(['success' => true, 'message' => 'Student removed from course']);
            exit;
        }

        // ✅ 添加学生操作（默认行为）
        // 检查是否为学生
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$studentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only students can be added to courses']);
            exit;
        }

        // 检查是否已存在
        $stmt = $pdo->prepare("SELECT * FROM course_students WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Student already enrolled in course']);
            exit;
        }

        // 添加记录
        $stmt = $pdo->prepare("INSERT INTO course_students (student_id, course_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$studentId, $courseId]);

        echo json_encode(['success' => true, 'message' => 'Student added to course']);
        break;

    case 'GET':
    if (!isset($_GET['course_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing course_id']);
        exit;
    }

    $courseId = $_GET['course_id'];

    // 获取课程学生列表，包含头像字段
    $stmt = $pdo->prepare("
        SELECT 
            u.id AS student_id,
            u.name,
            u.email,
            u.matric_no,
            u.profile_pic,
            cs.created_at
        FROM course_students cs
        JOIN users u ON cs.student_id = u.id
        WHERE cs.course_id = ?
    ");
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $students]);
    break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
