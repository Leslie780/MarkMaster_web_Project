<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$student_id = $_GET['student_id'] ?? null;

if (!$student_id) {
  echo json_encode(['success' => false, 'message' => 'Missing student_id']);
  exit;
}

$stmt = $conn->prepare("
  SELECT scr.grade_point, c.credit_hours
  FROM student_course_results scr
  JOIN courses c ON scr.course_id = c.id
  WHERE scr.student_id = ?
");
$stmt->execute([$student_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data) {
  echo json_encode(['success' => false, 'message' => 'No results found']);
  exit;
}

$total_points = 0;
$total_credits = 0;

foreach ($data as $row) {
  $gp = floatval($row['grade_point']);
  $ch = intval($row['credit_hours']);
  $total_points += $gp * $ch;
  $total_credits += $ch;
}

$cgpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;

echo json_encode([
  'success' => true,
  'student_id' => $student_id,
  'cgpa' => $cgpa
]);
