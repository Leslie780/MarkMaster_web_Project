<?php
// require_once __DIR__ . '/db.php';

// $conn = getPDO();
// header('Content-Type: application/json');header("Content-Type: application/json");
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
// header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");


// $lecturer_id = $_GET['lecturer_id'] ?? null;

// if (!$lecturer_id) {
//     echo json_encode(['success' => false, 'message' => 'Missing lecturer_id']);
//     exit;
// }

// try {
//     $stmt = $conn->prepare("SELECT id, course_code, course_name, academic_year, semester FROM courses WHERE lecturer_id = ?");
//     $stmt->execute([$lecturer_id]);
//     $courses = $stmt->fetchAll();

//     echo json_encode(['success' => true, 'courses' => $courses]);
// } catch (PDOException $e) {
//     echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
// }