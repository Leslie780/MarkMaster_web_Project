<?php
require_once __DIR__ . '/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) {
                echo json_encode(['success' => true, 'data' => $course]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Course not found']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM courses");
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $courses]);
        }
        
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            exit;
        }

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input format']);
            exit;
        }
        $required = ['course_code', 'course_name', 'lecturer_id', 'academic_year', 'semester', 'credit_hours', 'final_exam_percentage', 'continuous_assessment_percentage', 'status'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
                exit;
            }
        }

        $course_code = $input['course_code'];
        $course_name = $input['course_name'];
        $lecturer_id = $input['lecturer_id'];
        $academic_year = $input['academic_year'];
        $semester = $input['semester'];
        $credit_hours = $input['credit_hours'];
        $description = $input['description'] ?? null;
        $final_exam_percentage = $input['final_exam_percentage'];
        $continuous_assessment_percentage = $input['continuous_assessment_percentage'];
        $status = $input['status'];

        try {
            $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_name, lecturer_id, academic_year, semester, credit_hours, description, final_exam_percentage, continuous_assessment_percentage, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $course_code, $course_name, $lecturer_id, $academic_year, $semester, $credit_hours, $description, $final_exam_percentage, $continuous_assessment_percentage, $status
            ]);
            echo json_encode(['success' => true, 'message' => 'Course created successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

case 'PUT':
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    if (empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required field: id']);
        exit;
    }

    $id = $input['id'];
    $fields = [
        'course_code', 'course_name', 'lecturer_id', 'academic_year', 'semester',
        'credit_hours', 'description', 'final_exam_percentage', 'continuous_assessment_percentage', 'status'
    ];
    $set = [];
    $params = [];
    foreach ($fields as $field) {
        if (isset($input[$field])) {
            $set[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    if (empty($set)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    $params[] = $id;

    try {
        $stmt = $pdo->prepare("UPDATE courses SET " . implode(', ', $set) . ", updated_at = NOW() WHERE id = ?");
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

  case 'DELETE':
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required field: id']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        break;
}