<?php
require_once __DIR__ . '/db.php'; // Your database connection file

// --- Global Headers ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// --- Handle Preflight OPTIONS Request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Main API Router ---
$pdo = getPDO();
// FIXED: Use $_REQUEST to handle parameters from both GET and POST requests reliably.
$action = $_REQUEST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    // --- Endpoint for main student dashboard data (CGPA, courses, etc.) ---
    // Note: To match the frontend, you might rename this case to 'get_student_data'
    // and update the corresponding fetch URL in the Vue component.
    // For now, keeping original logic.
    case 'get_student_data':
        if ($method === 'GET') {
            get_student_data($pdo);
        }
        break;

    // --- Endpoint for course comparison data ---
    case 'get_course_comparison':
        if ($method === 'GET') {
            get_course_comparison($pdo);
        }
        break;

    // --- NEW: Endpoint for notifications ---
    case 'notifications':
        if ($method === 'GET') {
            get_notifications($pdo);
        }
        break;

    // --- NEW: Endpoint to mark notifications as read ---
    case 'mark_notifications_read':
        if ($method === 'POST') {
            mark_notifications_read($pdo);
        }
        break;

    // --- NEW: Endpoint for submitting a grade appeal ---
    case 'submit_appeal':
        if ($method === 'POST') {
            submit_appeal($pdo);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => "Invalid API action: '{$action}' not found."]);
        break;
}


/**
 * Fetches the main dashboard data for a student, including CGPA, semesters, courses, and assessment details.
 * UPDATED: Now includes the 'remarks' field for each assessment.
 */
function get_student_data($pdo) {
    $student_id = $_GET['student_id'] ?? null;
    if (!$student_id) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
        return;
    }

    try {
        // This is a simplified version of your 'cgpa' logic, focusing on the data structure.
        // You should adapt your full, complex calculation logic here.
        // For this example, we'll build a compatible data structure.

        $stmt = $pdo->prepare("
            SELECT c.id AS course_id, c.course_name, c.course_code, c.academic_year, c.semester, c.credit_hours,
                   cs.final_exam_score, cs.total_score, cs.grade, cs.grade_point
            FROM course_students cs
            JOIN courses c ON cs.course_id = c.id
            WHERE cs.student_id = ?
            ORDER BY c.academic_year DESC, c.semester DESC
        ");
        $stmt->execute([$student_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $semesters = [];
        $total_credits = 0;
        $total_quality_points = 0;

        foreach ($courses as $course) {
            $sem_key = "{$course['academic_year']}-{$course['semester']}";
            if (!isset($semesters[$sem_key])) {
                $semesters[$sem_key] = [
                    'academic_year' => $course['academic_year'],
                    'semester' => $course['semester'],
                    'courses' => [],
                    'total_points' => 0,
                    'total_credits' => 0,
                ];
            }

            // Fetch assessment scores for the course, NOW WITH REMARKS
            $stmt_assess = $pdo->prepare("
                SELECT ac.id AS component_id, ac.title, ac.type, ac.max_mark, ac.percentage, ascore.mark_obtained, ascore.remarks
                FROM assessment_components ac
                LEFT JOIN assessment_scores ascore ON ac.id = ascore.component_id AND ascore.student_id = ?
                WHERE ac.course_id = ?
            ");
            $stmt_assess->execute([$student_id, $course['course_id']]);
            $course['assessment_scores'] = $stmt_assess->fetchAll(PDO::FETCH_ASSOC);
            
            $semesters[$sem_key]['courses'][] = $course;
            $semesters[$sem_key]['total_points'] += $course['grade_point'] * $course['credit_hours'];
            $semesters[$sem_key]['total_credits'] += $course['credit_hours'];
            
            $total_quality_points += $course['grade_point'] * $course['credit_hours'];
            $total_credits += $course['credit_hours'];
        }

        $processed_semesters = [];
        foreach ($semesters as $sem) {
            $sem['cgpa'] = $sem['total_credits'] > 0 ? round($sem['total_points'] / $sem['total_credits'], 2) : 0;
            unset($sem['total_points'], $sem['total_credits']);
            $processed_semesters[] = $sem;
        }

        $response = [
            'success' => true,
            'overall_cgpa' => $total_credits > 0 ? round($total_quality_points / $total_credits, 2) : 0,
            'semesters' => array_values($processed_semesters)
        ];

        echo json_encode($response);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}

/**
 * Fetches course comparison data (student's score vs class average).
 */
function get_course_comparison($pdo) {
    // This function contains the logic from your existing 'compare' endpoint.
    // Ensure the logic correctly calculates your score vs. class average.
    // The implementation details are omitted here for brevity but should be included from your files.
    $student_id = $_GET['student_id'] ?? null;
    $course_id = $_GET['course_id'] ?? null;
    
    // ... Add your full comparison logic here ...

    // Example response structure:
    echo json_encode([
        'success' => true,
        'ranking' => 15,
        'total_students' => 85,
        'comparisons' => [
            ['component_id' => 1, 'title' => 'Assignment 1', 'my_score' => 88, 'avg_score' => 76, 'max_mark' => 100],
            ['component_id' => 2, 'title' => 'Mid-Term Exam', 'my_score' => 75, 'avg_score' => 81, 'max_mark' => 100]
        ]
    ]);
}


/**
 * NEW: Fetches unread notifications for a student.
 */
function get_notifications($pdo) {
    $student_id = $_GET['student_id'] ?? null;
    if (!$student_id) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE student_id = ? ORDER BY id DESC LIMIT 10");
        $stmt->execute([$student_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'notifications' => $notifications]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}

/**
 * NEW: Marks a list of notifications as read.
 */
function mark_notifications_read($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $student_id = $input['student_id'] ?? null;
    $notification_ids = $input['notification_ids'] ?? [];

    if (!$student_id || empty($notification_ids)) {
        echo json_encode(['success' => false, 'message' => 'Student ID and Notification IDs are required.']);
        return;
    }

    try {
        $placeholders = implode(',', array_fill(0, count($notification_ids), '?'));
        $sql = "UPDATE notifications SET is_read = TRUE WHERE student_id = ? AND id IN ($placeholders)";
        
        $params = array_merge([$student_id], $notification_ids);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Notifications marked as read.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}

/**
 * NEW: Submits a new grade appeal request.
 */
function submit_appeal($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    $student_id = $input['student_id'] ?? null;
    $course_id = $input['course_id'] ?? null;
    $reason = $input['reason'] ?? '';

    if (!$student_id || !$course_id || empty(trim($reason))) {
        echo json_encode(['success' => false, 'message' => 'Student ID, Course ID, and a reason are required.']);
        return;
    }

    try {
        $sql = "INSERT INTO grade_appeals (student_id, course_id, reason, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $course_id, $reason]);

        echo json_encode(['success' => true, 'message' => 'Appeal submitted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
