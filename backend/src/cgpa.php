<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 统一成绩转等级函数
function getGradePoint($score) {
    if ($score >= 90) return [ 'grade' => 'A+',  'point' => 4.00 ];
    if ($score >= 80) return [ 'grade' => 'A',   'point' => 4.00 ];
    if ($score >= 75) return [ 'grade' => 'A-',  'point' => 3.67 ];
    if ($score >= 70) return [ 'grade' => 'B+',  'point' => 3.33 ];
    if ($score >= 65) return [ 'grade' => 'B',   'point' => 3.00 ];
    if ($score >= 60) return [ 'grade' => 'B-',  'point' => 2.67 ];
    if ($score >= 55) return [ 'grade' => 'C+',  'point' => 2.33 ];
    if ($score >= 50) return [ 'grade' => 'C',   'point' => 2.00 ];
    if ($score >= 45) return [ 'grade' => 'C-',  'point' => 1.67 ];
    if ($score >= 40) return [ 'grade' => 'D+',  'point' => 1.33 ];
    if ($score >= 35) return [ 'grade' => 'D',   'point' => 1.00 ];
    if ($score >= 30) return [ 'grade' => 'D-',  'point' => 0.67 ];
    return [ 'grade' => 'E',  'point' => 0.00 ];
}

$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'Missing student_id']);
    exit;
}

// 查询课程
$stmt = $conn->prepare("
    SELECT c.id AS course_id, c.course_name, c.academic_year, c.semester, c.credit_hours
    FROM course_students cs
    JOIN courses c ON cs.course_id = c.id
    WHERE cs.student_id = ?
    ORDER BY c.academic_year, c.semester
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$courses) {
    echo json_encode(['success' => false, 'message' => 'No courses found']);
    exit;
}

$semester_results = [];
$total_points = 0;
$total_credits = 0;

foreach ($courses as $course) {
    $course_id = $course['course_id'];
    $credit_hours = $course['credit_hours'];
    $semester_key = $course['academic_year'] . ' - ' . $course['semester'];

    // 获取 assessment scores
  $stmt2 = $conn->prepare("
    SELECT ac.id as component_id, ac.title, ac.max_mark, ac.percentage, 
           COALESCE(ascore.mark_obtained, 0) as mark_obtained
    FROM assessment_components ac
    LEFT JOIN assessment_scores ascore
        ON ac.id = ascore.component_id AND ascore.student_id = ?
    WHERE ac.course_id = ?
");

    $stmt2->execute([$student_id, $course_id]);
    $assessments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $total_assessment_score = 0;
    foreach ($assessments as $a) {
        $score = ($a['mark_obtained'] / $a['max_mark']) * $a['percentage'];
        $total_assessment_score += $score;
    }

    // 获取 final exam 分数
    $stmt3 = $conn->prepare("
        SELECT mark_obtained FROM final_exam_scores
        WHERE student_id = ? AND course_id = ?
    ");
    $stmt3->execute([$student_id, $course_id]);
    $final_score = $stmt3->fetchColumn();
    $final_score = $final_score !== false ? floatval($final_score) : 0;

    // 计算总分：70% assessment + 30% final
    $total_score = $total_assessment_score + ($final_score * 0.3);

    // 使用统一函数计算等级和绩点
    $gradeInfo = getGradePoint($total_score);
    $grade = $gradeInfo['grade'];
    $grade_point = $gradeInfo['point'];

    // 初始化学期数据
    if (!isset($semester_results[$semester_key])) {
        $semester_results[$semester_key] = [
            'academic_year' => $course['academic_year'],
            'semester' => $course['semester'],
            'courses' => [],
            'total_points' => 0,
            'total_credits' => 0
        ];
    }

    $semester_results[$semester_key]['courses'][] = [
        'course_id' => $course_id,
        'course_name' => $course['course_name'],
        'total_score' => round($total_score, 2),
        'final_exam_score' => $final_score,
        'grade' => $grade,
        'grade_point' => $grade_point,
        'credit_hours' => $credit_hours,
        'assessment_scores' => $assessments
    ];

    $semester_results[$semester_key]['total_points'] += $grade_point * $credit_hours;
    $semester_results[$semester_key]['total_credits'] += $credit_hours;

    $total_points += $grade_point * $credit_hours;
    $total_credits += $credit_hours;
}

// 组织输出数据
$output = [
    'success' => true,
    'student_id' => $student_id,
    'overall_cgpa' => $total_credits > 0 ? round($total_points / $total_credits, 2) : 0,
    'semesters' => []
];

foreach ($semester_results as $result) {
    $cgpa = $result['total_credits'] > 0
        ? round($result['total_points'] / $result['total_credits'], 2)
        : 0;
    $output['semesters'][] = [
        'academic_year' => $result['academic_year'],
        'semester' => $result['semester'],
        'cgpa' => $cgpa,
        'courses' => $result['courses']
    ];
}

echo json_encode($output, JSON_PRETTY_PRINT);
