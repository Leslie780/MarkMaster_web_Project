<?php
require_once __DIR__ . '/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$studentId = $_GET['student_id'] ?? null;
$courseId = $_GET['course_id'] ?? null;

if (!$studentId || !$courseId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $pdo = getPDO();

    // 1. Get Total Score for All Students in Course
    $stmt1 = $pdo->prepare("SELECT student_id, 
        SUM((mark_obtained / ac.max_mark) * ac.percentage) AS total_score
        FROM assessment_scores s
        JOIN assessment_components ac ON s.component_id = ac.id
        WHERE ac.course_id = ?
        GROUP BY student_id
        ORDER BY total_score DESC");
    $stmt1->execute([$courseId]);
    $rankings = $stmt1->fetchAll();

    $rankingPosition = null;
    foreach ($rankings as $i => $r) {
        if ($r['student_id'] == $studentId) {
            $rankingPosition = $i + 1;
            break;
        }
    }

    // 2. Get Class Averages Per Component
    $stmt2 = $pdo->prepare("SELECT 
        ac.id AS component_id,
        ac.title,
        ac.type,
        ac.max_mark,
        ac.percentage,
        AVG(s.mark_obtained) AS avg_score
        FROM assessment_components ac
        LEFT JOIN assessment_scores s ON ac.id = s.component_id
        WHERE ac.course_id = ?
        GROUP BY ac.id, ac.title, ac.type, ac.max_mark, ac.percentage");
    $stmt2->execute([$courseId]);
    $averages = $stmt2->fetchAll();

    // 3. Get Individual Scores
    $stmt3 = $pdo->prepare("SELECT ac.id AS component_id, ac.title, ac.type, ac.max_mark, ac.percentage,
        COALESCE(s.mark_obtained, 0) AS mark_obtained
        FROM assessment_components ac
        LEFT JOIN assessment_scores s ON ac.id = s.component_id AND s.student_id = ?
        WHERE ac.course_id = ?");
    $stmt3->execute([$studentId, $courseId]);
    $studentScores = $stmt3->fetchAll();

    // Combine student vs class avg comparison
    $comparisons = [];
    foreach ($averages as $avg) {
        foreach ($studentScores as $score) {
            if ($avg['component_id'] == $score['component_id']) {
                $comparisons[] = [
                    'component_id' => $avg['component_id'],
                    'title' => $avg['title'],
                    'type' => $avg['type'],
                    'max_mark' => $avg['max_mark'],
                    'percentage' => $avg['percentage'],
                    'avg_score' => round($avg['avg_score'], 2),
                    'my_score' => $score['mark_obtained']
                ];
                break;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'ranking' => $rankingPosition,
        'total_students' => count($rankings),
        'class_averages' => $averages,
        'comparisons' => $comparisons
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
