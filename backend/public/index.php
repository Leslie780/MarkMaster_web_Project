<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Normalize path (FIX)
$fullPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . basename($fullPath);

// Log path for debugging
error_log("Normalized path: " . $path);

$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    case '/register':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/register.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/login':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/login.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/forgot-password':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/forgetpassword.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/user-management':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/userManagement.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/course-management':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/courseManagement.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/course-students':
        if (in_array($method, ['GET', 'POST', 'OPTIONS'])) {
            require_once __DIR__ . '/../src/course_students.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    case '/assessment-components':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/assessment.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/assessment-scores':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/assessment.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    case '/final-exam-scores':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/assessment.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    //处理一个lecturer下面多门课程多名学生的最终成绩

    case '/lecturer-results':
        if ($method === 'GET') {
            require_once __DIR__ . '/../src/lecturer_results.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/cgpa':
        if ($method === 'GET') {
            require_once __DIR__ . '/../src/cgpa.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/compare':
        if ($method === 'GET') {
            require_once __DIR__ . '/../src/compare_course_scores.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;

    case '/advisor_api':
    if ($method === 'GET' || $method === 'POST') {
        require_once __DIR__ . '/../src/advisor_api.php';
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    break;


    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
}