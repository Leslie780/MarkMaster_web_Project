<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

if (!$student_id) {
  echo json_encode(['success' => false, 'message' => 'Missing student_id']);
  exit;
}

// 函数：根据总分获取绩点
function getGradePoint($score) {
  if ($score >= 90) return [ 'grade' => 'A+',  'point' => 4.00 ];
  if ($score >= 80) return [ 'grade' => 'A', 'point' => 4.00 ];
  if ($score >= 75) return [ 'grade' => 'A-', 'point' => 3.67 ];
  if ($score >= 70) return [ 'grade' => 'B+',  'point' => 3.33 ];
  if ($score >= 65) return [ 'grade' => 'B', 'point' => 3.00 ];
  if ($score >= 60) return [ 'grade' => 'B-', 'point' => 2.67 ];
  if ($score >= 55) return [ 'grade' => 'C+', 'point' => 2.33 ];
  if ($score >= 50) return [ 'grade' => 'C', 'point' => 2.00 ];
  if ($score >= 45) return [ 'grade' => 'C-', 'point' => 1.67 ];
  if ($score >= 40) return [ 'grade' => 'D+', 'point' => 1.33 ];
  if ($score >= 35) return [ 'grade' => 'D', 'point' => 1.00 ];
  if ($score >= 30) return [ 'grade' => 'D-', 'point' => 0.67 ];
  // Failing grade
  return [ 'grade' => 'E',  'point' => 0.00 ];
}

// 查询该学生所有修的课程
$sql = "
  SELECT c.id AS course_id, c.course_name, c.credit_hours
  FROM course_students cs
  JOIN courses c ON cs.course_id = c.id
  WHERE cs.student_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$courses) {
  echo json_encode(['success' => false, 'message' => 'No courses found']);
  exit;
}

$total_points = 0;
$total_credits = 0;
$course_details = [];

foreach ($courses as $course) {
  $course_id = $course['course_id'];
  $credit_hours = intval($course['credit_hours']);
  $course_name = $course['course_name'];

  // === 1. 计算 Continuous Assessment 成绩（70%）
  $sql_ca = "
    SELECT ac.id AS component_id, ac.max_mark, ac.percentage, sc.mark_obtained
    FROM assessment_components ac
    LEFT JOIN assessment_scores sc
      ON ac.id = sc.component_id AND sc.student_id = ?
    WHERE ac.course_id = ?
  ";
  $stmt = $conn->prepare($sql_ca);
  $stmt->execute([$student_id, $course_id]);
  $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $ca_score = 0;
  foreach ($assessments as $a) {
    if ($a['mark_obtained'] === null) continue;
    $normalized = ($a['mark_obtained'] / $a['max_mark']) * 100;
    $weighted = $normalized * ($a['percentage'] / 100);
    $ca_score += $weighted;
  }

  // === 2. 计算 Final Exam 成绩（30%）
  $stmt = $conn->prepare("
    SELECT mark_obtained
    FROM final_exam_scores
    WHERE student_id = ? AND course_id = ?
  ");
  $stmt->execute([$student_id, $course_id]);
  $final_score_row = $stmt->fetch(PDO::FETCH_ASSOC);
  $final_score = $final_score_row ? floatval($final_score_row['mark_obtained']) : 0;

  // === 3. 总成绩
  $total_score = $ca_score + $final_score * 0.3;
  $grade_info = getGradePoint($total_score);

  // === 4. 加权绩点
  $total_points += $grade_info['point'] * $credit_hours;
  $total_credits += $credit_hours;

  $course_details[] = [
    'course_id' => $course_id,
    'course_name' => $course_name,
    'total_score' => round($total_score, 2),
    'final_exam_score' => round($final_score, 2),
    'assessment_scores' => array_map(function($a) {
      return [
        'component_id' => $a['component_id'],
        'max_mark' => $a['max_mark'],
        'percentage' => $a['percentage'],
        'mark_obtained' => $a['mark_obtained']
      ];
    }, $assessments),
    'grade' => $grade_info['grade'],
    'grade_point' => $grade_info['point'],
    'credit_hours' => $credit_hours
  ];
}

$cgpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;

echo json_encode([
  'success' => true,
  'student_id' => $student_id,
  'cgpa' => $cgpa,
  'courses' => $course_details
]);
