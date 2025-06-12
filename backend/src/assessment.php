<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($path) {
  case '/assessment-components':
    handleAssessmentComponents($method);
    break;

  case '/assessment-scores':
    handleAssessmentScores($method);
    break;

  case '/final-exam-scores':
    handleFinalExamScores($method);
    break;

  default:
    echo json_encode(['success' => false, 'message' => 'Route not found']);
    break;
}

function getJsonBody() {
  return json_decode(file_get_contents('php://input'), true);
}

function handleAssessmentComponents($method) {
  global $conn;
  $data = getJsonBody();

  switch ($method) {
    case 'GET':
      $course_id = $_GET['course_id'] ?? null;
      if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing course_id']);
        return;
      }
      $stmt = $conn->prepare("SELECT * FROM assessment_components WHERE course_id = ?");
      $stmt->execute([$course_id]);
      echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
      break;

    case 'POST':
      if (
        empty($data['course_id']) ||
        empty($data['title']) ||
        empty($data['type']) ||
        !isset($data['max_mark']) ||
        !isset($data['percentage']) ||
        !isset($data['due_date'])
      ) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
      }

      $stmt = $conn->prepare("INSERT INTO assessment_components (course_id, title, type, max_mark, percentage, due_date) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $data['course_id'],
        $data['title'],
        $data['type'],
        $data['max_mark'],
        $data['percentage'],
        $data['due_date'] ?? null
      ]);
      echo json_encode(['success' => true]);
      break;

    case 'PUT':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing component ID']);
        return;
      }
      $stmt = $conn->prepare("UPDATE assessment_components SET title=?, type=?, max_mark=?, percentage=?, due_date=? WHERE id=?");
      $stmt->execute([
        $data['title'],
        $data['type'],
        $data['max_mark'],
        $data['percentage'],
        $data['due_date'] ?? null,
        $data['id']
      ]);
      echo json_encode(['success' => true]);
      break;

    case 'DELETE':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing component ID']);
        return;
      }
      $stmt = $conn->prepare("DELETE FROM assessment_components WHERE id = ?");
      $stmt->execute([$data['id']]);
      echo json_encode(['success' => true]);
      break;
  }
}

function handleAssessmentScores($method) {
  global $conn;
  $data = getJsonBody();

  switch ($method) {
    case 'GET':
      $course_id = $_GET['course_id'] ?? null;
      if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing course_id']);
        return;
      }

      $student_id = $_GET['student_id'] ?? null;
      $sql = "SELECT * FROM assessment_scores WHERE course_id = ?";
      $params = [$course_id];

      if ($student_id) {
        $sql .= " AND student_id = ?";
        $params[] = $student_id;
      }

      $stmt = $conn->prepare($sql);
      $stmt->execute($params);
      echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
      break;

    case 'POST':
      if (
        empty($data['student_id']) ||
        empty($data['course_id']) ||
        empty($data['component_id']) ||
        !isset($data['mark_obtained'])
      ) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
      }

      $stmt = $conn->prepare("INSERT INTO assessment_scores (student_id, course_id, component_id, mark_obtained) VALUES (?, ?, ?, ?)");
      $stmt->execute([$data['student_id'], $data['course_id'], $data['component_id'], $data['mark_obtained']]);
      echo json_encode(['success' => true]);
      break;

    case 'PUT':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing score ID']);
        return;
      }

      $stmt = $conn->prepare("UPDATE assessment_scores SET mark_obtained=? WHERE id=?");
      $stmt->execute([$data['mark_obtained'], $data['id']]);
      echo json_encode(['success' => true]);
      break;

    case 'DELETE':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing score ID']);
        return;
      }

      $stmt = $conn->prepare("DELETE FROM assessment_scores WHERE id=?");
      $stmt->execute([$data['id']]);
      echo json_encode(['success' => true]);
      break;
  }
}

function handleFinalExamScores($method) {
  global $conn;
  $data = getJsonBody();

  switch ($method) {
    case 'GET':
      $course_id = $_GET['course_id'] ?? null;
      if (!$course_id) {
        echo json_encode(['success' => false, 'message' => 'Missing course_id']);
        return;
      }

      $stmt = $conn->prepare("SELECT * FROM final_exam_scores WHERE course_id = ?");
      $stmt->execute([$course_id]);
      echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
      break;

    case 'POST':
      if (
        empty($data['student_id']) ||
        empty($data['course_id']) ||
        !isset($data['mark_obtained'])
      ) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
      }

      $stmt = $conn->prepare("INSERT INTO final_exam_scores (student_id, course_id, mark_obtained) VALUES (?, ?, ?)");
      $stmt->execute([$data['student_id'], $data['course_id'], $data['mark_obtained']]);
      echo json_encode(['success' => true]);
      break;

    case 'PUT':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing final exam score ID']);
        return;
      }

      $stmt = $conn->prepare("UPDATE final_exam_scores SET mark_obtained=? WHERE id=?");
      $stmt->execute([$data['mark_obtained'], $data['id']]);
      echo json_encode(['success' => true]);
      break;

    case 'DELETE':
      if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing final exam score ID']);
        return;
      }

      $stmt = $conn->prepare("DELETE FROM final_exam_scores WHERE id = ?");
      $stmt->execute([$data['id']]);
      echo json_encode(['success' => true]);
      break;
  }
}
