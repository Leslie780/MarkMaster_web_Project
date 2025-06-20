<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");

// handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// log the requested path for debugging
error_log("Requested path: " . $path);

// router logic
$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    // API routes
    //处理注册逻辑的api路由 
    case '/register':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/register.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
     //处理登录逻辑的api路由
    case '/login':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/login.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
       //处理忘记密码逻辑的api路由 
        case'/forgot-password':
        if ($method === 'POST') {
            require_once __DIR__ . '/../src/forgot_password.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        //处理用户账户管理逻辑路由
    case '/user-management':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/userManagement.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        //处理课程管理逻辑路由 主要是讲师录取学生
        case'/course-management':
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            require_once __DIR__ . '/../src/courseManagement.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        //处理课程学生逻辑路由 主要是学生对应课程的逻辑
        case '/course-students':
        if (in_array($method, ['GET', 'POST', 'OPTIONS'])) {
            require_once __DIR__ . '/../src/course_students.php';
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
        //处理课程成绩组成逻辑路由 主要是学生对应课程的成绩逻辑
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
    case '/results':
    if ($method === 'GET') {
        require_once __DIR__ . '/../src/result.php';
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        
    }
    break;
    //处理讲师成绩逻辑路由 主要是讲师查看自己课程的学生成绩
    case'/lecturer-results':
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


    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
        break;
}