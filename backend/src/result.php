<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS, PUT");

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($path, '/results') !== false) {
    handleCourseResults($method);
    exit;
}

function handleCourseResults($method) {
    global $conn;

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $course_id = $_GET['course_id'] ?? null;
    if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing course_id']);
        return;
    }

    // 获取课程的 CA 和 Final 占比
    $stmt = $conn->prepare("SELECT final_exam_percentage, continuous_assessment_percentage FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        return;
    }

    $ca_weight = floatval($course['continuous_assessment_percentage']);
    $final_weight = floatval($course['final_exam_percentage']);

    // 获取课程的 component
    $stmt = $conn->prepare("SELECT * FROM assessment_components WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$components) {
        echo json_encode(['success' => false, 'message' => 'No components found']);
        return;
    }

    // 获取所有学生列表
    $stmt = $conn->prepare("
        SELECT u.id AS student_id, u.name
        FROM course_students cs
        JOIN users u ON cs.student_id = u.id
        WHERE cs.course_id = ?
    ");
    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 获取所有 component 的成绩
    $stmt = $conn->prepare("
        SELECT * FROM assessment_scores
        WHERE course_id = ?
    ");
    $stmt->execute([$course_id]);
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 获取所有 final_exam_scores
    $stmt = $conn->prepare("
        SELECT * FROM final_exam_scores
        WHERE course_id = ?
    ");
    $stmt->execute([$course_id]);
    $final_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $studentMap = [];
    foreach ($students as $student) {
        $studentMap[$student['student_id']] = [
            'student_id' => $student['student_id'],
            'student_name' => $student['name'],
            'components' => [],
            'ca_total' => 0,
            'final_exam' => 0,
            'total' => 0,
            'grade' => '',
            'grade_point' => 0,
            'achievement' => ''
        ];
    }
    

    // Map component ID to details
    $componentMap = [];
    foreach ($components as $comp) {
        $componentMap[$comp['id']] = $comp;
    }

    // Add component scores
    foreach ($scores as $score) {
        $sid = $score['student_id'];
        $cid = $score['component_id'];
        if (!isset($studentMap[$sid]) || !isset($componentMap[$cid])) continue;

        $comp = $componentMap[$cid];
        $mark = floatval($score['mark_obtained']);
        $percentage = floatval($comp['percentage']);
        $contribution = ($mark / 100) * $percentage;

        $studentMap[$sid]['components'][] = [
            'title' => $comp['title'],
            'type' => $comp['type'],
            'score' => $mark,
            'percentage' => $percentage,
            'contribution' => round($contribution, 2)
        ];

        $studentMap[$sid]['ca_total'] += $contribution; // already within CA 70%
    }

    // Add final exam scores
    foreach ($final_scores as $fscore) {
        $sid = $fscore['student_id'];
        if (!isset($studentMap[$sid])) continue;

        $mark = floatval($fscore['mark_obtained']);
        $contribution = ($mark / 100) * 30;

        $studentMap[$sid]['final_exam'] = round($contribution, 2);
        $studentMap[$sid]['total'] = round($studentMap[$sid]['ca_total'] + $contribution, 2);

        // 添加等级、绩点和成就
        [$grade, $gp, $achievement] = calculateGrade($studentMap[$sid]['total']);
        $studentMap[$sid]['grade'] = $grade;
        $studentMap[$sid]['grade_point'] = $gp;
        $studentMap[$sid]['achievement'] = $achievement;
    }

    echo json_encode([
        'success' => true,
        'data' => array_values($studentMap)
    ]);
}

// 分数换等级函数
function calculateGrade($score) {
    if ($score >= 90) return ['A+', 4.00, 'EXCELLENT PASS'];
    if ($score >= 80) return ['A', 4.00, 'EXCELLENT PASS'];
    if ($score >= 75) return ['A-', 3.67, 'EXCELLENT PASS'];
    if ($score >= 70) return ['B+', 3.33, 'GOOD PASS'];
    if ($score >= 65) return ['B', 3.00, 'GOOD PASS'];
    if ($score >= 60) return ['B-', 2.67, 'PASS'];
    if ($score >= 55) return ['C+', 2.33, 'FAIL'];
    if ($score >= 50) return ['C', 2.00, 'FAIL'];
    if ($score >= 45) return ['C-', 1.67, 'FAIL'];
    if ($score >= 40) return ['D+', 1.33, 'FAIL'];
    if ($score >= 35) return ['D', 1.00, 'FAIL'];
    if ($score >= 30) return ['D-', 0.67, 'FAIL'];
    return ['E', 0.00, 'FAIL'];
}
$stmt = $conn->prepare("
    INSERT INTO student_course_results 
    (student_id, course_id, total_score, grade, grade_point)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    total_score = VALUES(total_score),
    grade = VALUES(grade),
    grade_point = VALUES(grade_point),
    updated_at = CURRENT_TIMESTAMP
");
$stmt->execute([$student_id, $course_id, $total, $grade, $grade_point]);
