<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET");

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$lecturer_id = $_GET['lecturer_id'] ?? null;
if (!$lecturer_id) {
    echo json_encode(['success' => false, 'message' => 'Missing lecturer_id']);
    exit;
}

// 获取该讲师的所有课程
$stmt = $conn->prepare("SELECT id, course_code, course_name FROM courses WHERE lecturer_id = ?");
$stmt->execute([$lecturer_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach ($courses as $course) {
    $course_id = $course['id'];

    // 获取课程 assessment components
    $stmt = $conn->prepare("SELECT id, title, type, percentage FROM assessment_components WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $component_map = [];
    foreach ($components as $comp) {
        $component_map[$comp['id']] = $comp;
    }

    // 获取所有学生
    $stmt = $conn->prepare("SELECT cs.student_id, u.name as student_name FROM course_students cs JOIN users u ON cs.student_id = u.id WHERE cs.course_id = ?");
    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $student) {
        $sid = $student['student_id'];
        $student_result = [
            'student_id' => $sid,
            'student_name' => $student['student_name'],
            'components' => [],
            'final_exam' => 0,
            'ca_total' => 0,
            'total' => 0,
            'grade' => null,
            'grade_point' => null,
            'achievement' => null
        ];

        // 获取该学生在该课程的 component 分数
        $stmt = $conn->prepare("SELECT component_id, mark_obtained FROM assessment_scores WHERE course_id = ? AND student_id = ?");
        $stmt->execute([$course_id, $sid]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ca_sum = 0;
        foreach ($scores as $score_row) {
            $comp = $component_map[$score_row['component_id']] ?? null;
            if (!$comp) continue;

            $mark = floatval($score_row['mark_obtained']);
            $percent = floatval($comp['percentage']);
            $contrib = $mark / 100 * $percent;

            $student_result['components'][] = [
                'title' => $comp['title'],
                'type' => $comp['type'],
                'score' => $mark,
                'percentage' => $percent,
                'contribution' => round($contrib, 2)
            ];

            $ca_sum += $contrib;
        }
        $student_result['ca_total'] = round($ca_sum, 2);

        // 获取 final_exam 分数
        $stmt = $conn->prepare("SELECT mark_obtained FROM final_exam_scores WHERE course_id = ? AND student_id = ?");
        $stmt->execute([$course_id, $sid]);
        $final_score = $stmt->fetchColumn();
        $final_contrib = $final_score ? floatval($final_score) / 100 * 30 : 0;
        $student_result['final_exam'] = round($final_contrib, 2);

        // 总分
        $total = $ca_sum + $final_contrib;
        $student_result['total'] = round($total, 2);

        // 评分逻辑
        $grading = [
            ['min' => 90, 'grade' => 'A+', 'point' => 4.00, 'achieve' => 'EXCELLENT PASS'],
            ['min' => 80, 'grade' => 'A',  'point' => 4.00, 'achieve' => 'EXCELLENT PASS'],
            ['min' => 75, 'grade' => 'A-', 'point' => 3.67, 'achieve' => 'EXCELLENT PASS'],
            ['min' => 70, 'grade' => 'B+', 'point' => 3.33, 'achieve' => 'GOOD PASS'],
            ['min' => 65, 'grade' => 'B',  'point' => 3.00, 'achieve' => 'GOOD PASS'],
            ['min' => 60, 'grade' => 'B-', 'point' => 2.67, 'achieve' => 'PASS'],
            ['min' => 55, 'grade' => 'C+', 'point' => 2.33, 'achieve' => 'FAIL'],
            ['min' => 50, 'grade' => 'C',  'point' => 2.00, 'achieve' => 'FAIL'],
            ['min' => 45, 'grade' => 'C-', 'point' => 1.67, 'achieve' => 'FAIL'],
            ['min' => 40, 'grade' => 'D+', 'point' => 1.33, 'achieve' => 'FAIL'],
            ['min' => 35, 'grade' => 'D',  'point' => 1.00, 'achieve' => 'FAIL'],
            ['min' => 30, 'grade' => 'D-', 'point' => 0.67, 'achieve' => 'FAIL'],
            ['min' => 0,  'grade' => 'E',  'point' => 0.00, 'achieve' => 'FAIL'],
        ];

        foreach ($grading as $rule) {
            if ($total >= $rule['min']) {
                $student_result['grade'] = $rule['grade'];
                $student_result['grade_point'] = $rule['point'];
                $student_result['achievement'] = $rule['achieve'];
                break;
            }
        }

        $result[] = array_merge(
            ['course_id' => $course_id, 'course_name' => $course['course_name']],
            $student_result
        );
    }
}

echo json_encode(['success' => true, 'data' => $result]);
