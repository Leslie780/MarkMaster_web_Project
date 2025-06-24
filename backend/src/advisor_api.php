<?php
require_once __DIR__ . '/db.php';
$conn = getPDO();

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");


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

$action = $_REQUEST['action'] ?? '';



if ($action === 'candidate_list') {
    $sql = "
        SELECT u.id as student_id, u.name, u.matric_no
FROM users u
WHERE u.role = 'student'
  AND u.id NOT IN (SELECT student_id FROM advisor_students)

    ";
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $student_map = [];
    foreach ($rows as $row) {
        $student_map[$row['student_id']] = [
            'student_id' => $row['student_id'],
            'matric_no'  => $row['matric_no'],
            'name'       => $row['name']
        ];
    }
    $result = array_values($student_map);

    echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
    exit;
}



if ($action === 'add_student') {
    $advisor_id = $_POST['advisor_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    if (!$advisor_id || !$student_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
   

    $stmt = $conn->prepare("SELECT 1 FROM advisor_students WHERE advisor_id=? AND student_id=?");
    $stmt->execute([$advisor_id, $student_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'already added']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO advisor_students (advisor_id, student_id) VALUES (?, ?)");
    $stmt->execute([$advisor_id, $student_id]);
    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'students_detail') {
    $advisor_id = $_GET['advisor_id'] ?? null;
    if (!$advisor_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT student_id FROM advisor_students WHERE advisor_id=?");
    $stmt->execute([$advisor_id]);
    $student_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'student_id');

    if (empty($student_ids)) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    $in_query = implode(',', array_fill(0, count($student_ids), '?'));
    $stmt = $conn->prepare("SELECT id, name, matric_no, email, phone FROM users WHERE id IN ($in_query)");
    $stmt->execute($student_ids);
    $students_info = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $students_info[$row['id']] = $row;
    }


    $stmt = $conn->prepare("
        SELECT cs.student_id, c.id AS course_id, c.course_name, c.course_code, c.academic_year, c.semester, c.credit_hours
        FROM course_students cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.student_id IN ($in_query)
        ORDER BY cs.student_id, c.academic_year, c.semester, c.course_code
    ");
    $stmt->execute($student_ids);
    $all_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
    $student_semesters = [];
    foreach ($all_courses as $course) {
        $sid = $course['student_id'];
        $key = $course['academic_year'].'|'.$course['semester'];
        if (!isset($student_semesters[$sid])) $student_semesters[$sid] = [];
        if (!isset($student_semesters[$sid][$key])) $student_semesters[$sid][$key] = [];
        $student_semesters[$sid][$key][] = $course;
    }

    $final_data = [];
    foreach ($students_info as $sid => $stu) {
        $stu_result = [
            'student_id' => $stu['id'],
            'matric_no'  => $stu['matric_no'],
            'name'       => $stu['name'],
            'email'      => $stu['email'],
            'phone'      => $stu['phone'],
            'semesters'  => [],
            'advisor_comments' => [],
            'advisor_appointments' => []
        ];
        if (!isset($student_semesters[$sid])) continue;
        foreach ($student_semesters[$sid] as $semkey => $courses_this_sem) {
            if (count($courses_this_sem) ===0) continue;
            list($academic_year, $semester) = explode('|', $semkey);
            $total_points = 0;
            $total_credits = 0;
            $course_list = [];

            foreach ($courses_this_sem as $course) {
                $course_id = $course['course_id'];
                $credit_hours = $course['credit_hours'];
                // assessment
                $stmt2 = $conn->prepare("
                    SELECT ac.id as component_id, ac.title, ac.max_mark, ac.percentage, 
                        COALESCE(ascore.mark_obtained, 0) as mark_obtained
                    FROM assessment_components ac
                    LEFT JOIN assessment_scores ascore
                        ON ac.id = ascore.component_id AND ascore.student_id = ?
                    WHERE ac.course_id = ?
                ");
                $stmt2->execute([$sid, $course_id]);
                $assessments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                $total_assessment_score = 0;
                foreach ($assessments as $a) {
                    $score = ($a['max_mark'] > 0)
                        ? ($a['mark_obtained'] / $a['max_mark']) * $a['percentage']
                        : 0;
                    $total_assessment_score += $score;
                }

                // final
                $stmt3 = $conn->prepare("SELECT mark_obtained FROM final_exam_scores WHERE student_id=? AND course_id=?");
                $stmt3->execute([$sid, $course_id]);
                $final_score = $stmt3->fetchColumn();
                $final_score = $final_score !== false ? floatval($final_score) : 0;

             
                $total_score = $total_assessment_score + ($final_score * 0.3);

                $gradeInfo = getGradePoint($total_score);
                $grade = $gradeInfo['grade'];
                $grade_point = $gradeInfo['point'];

                $course_list[] = [
                    'course_id'    => $course_id,
                    'course_name'  => $course['course_name'],
                    'course_code'  => $course['course_code'],
                    'credit_hours' => $credit_hours,
                    'total_score'  => round($total_score, 2),
                    'grade'        => $grade,
                    'grade_point'  => $grade_point
                ];

                $total_points += $grade_point * $credit_hours;
                $total_credits += $credit_hours;
            }

            $cgpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
            $academic_status = $cgpa <= 2.0 ? 'high-risk' : 'active';

            $stu_result['semesters'][] = [
                'academic_year'    => $academic_year,
                'semester'         => $semester,
                'cgpa'             => $cgpa,
                'academic_status'  => $academic_status,
                'courses'          => $course_list
            ];
        }
        


        $stmtC = $conn->prepare("
            SELECT id, type, content, meeting_time, created_at
            FROM student_advisor_activity
            WHERE student_id=? AND advisor_id=?
            ORDER BY created_at DESC
        ");
        $stmtC->execute([$sid, $advisor_id]);
        $activities = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        foreach ($activities as $a) {
            if ($a['type'] === 'comment') {
                $stu_result['advisor_comments'][] = [
                    'id'        => $a['id'],
                    'content'   => $a['content'],
                    'created_at'=> $a['created_at']
                ];
            } else if ($a['type'] === 'meeting') {
                $stu_result['advisor_appointments'][] = [
                    'id'          => $a['id'],
                    'content'     => $a['content'],
                    'meeting_time'=> $a['meeting_time'],
                    'created_at'  => $a['created_at']
                ];
            }
        }
        $final_data[] = $stu_result;
    }
    echo json_encode(['success' => true, 'data' => $final_data], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}



if ($action === 'add_comment') {
    $advisor_id = $_POST['advisor_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    if (!$advisor_id || !$student_id || !$content) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO student_advisor_activity (student_id, advisor_id, type, content) VALUES (?, ?, 'comment', ?)");
    $stmt->execute([$student_id, $advisor_id, $content]);
    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'delete_comment') {
    $comment_id = $_POST['comment_id'] ?? null;
    $advisor_id = $_POST['advisor_id'] ?? null;
    if (!$comment_id || !$advisor_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM student_advisor_activity WHERE id=? AND advisor_id=? AND type='comment'");
    $stmt->execute([$comment_id, $advisor_id]);
    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'add_meeting') {
    $advisor_id = $_POST['advisor_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    $meeting_time = $_POST['meeting_time'] ?? null;
    if (!$advisor_id || !$student_id || !$content || !$meeting_time) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO student_advisor_activity (student_id, advisor_id, type, content, meeting_time) VALUES (?, ?, 'meeting', ?, ?)");
    $stmt->execute([$student_id, $advisor_id, $content, $meeting_time]);
    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'delete_meeting') {
    $meeting_id = $_POST['meeting_id'] ?? null;
    $advisor_id = $_POST['advisor_id'] ?? null;
    if (!$meeting_id || !$advisor_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM student_advisor_activity WHERE id=? AND advisor_id=? AND type='meeting'");
    $stmt->execute([$meeting_id, $advisor_id]);
    echo json_encode(['success' => true]);
    exit;
}



if ($action === 'remove_student') {
    $advisor_id = $_POST['advisor_id'] ?? null;
    $student_id = $_POST['student_id'] ?? null;
    if (!$advisor_id || !$student_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    $conn->beginTransaction();
    try {
        $stmt1 = $conn->prepare("DELETE FROM advisor_students WHERE advisor_id=? AND student_id=?");
        $stmt1->execute([$advisor_id, $student_id]);
        $stmt2 = $conn->prepare("DELETE FROM student_advisor_activity WHERE advisor_id=? AND student_id=?");
        $stmt2->execute([$advisor_id, $student_id]);
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// NEW: Export all reports for an advisor
if ($action === 'export_all_reports') {
    $advisor_id = $_GET['advisor_id'] ?? null;
    if (!$advisor_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    
    // Get all students for this advisor with summary data
    $stmt = $conn->prepare("SELECT student_id FROM advisor_students WHERE advisor_id=?");
    $stmt->execute([$advisor_id]);
    $student_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'student_id');

    if (empty($student_ids)) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    $in_query = implode(',', array_fill(0, count($student_ids), '?'));
    
    // Get basic student info
    $stmt = $conn->prepare("SELECT id, name, matric_no, email, phone FROM users WHERE id IN ($in_query)");
    $stmt->execute($student_ids);
    $students_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $export_data = [];
    foreach ($students_info as $student) {
        $sid = $student['id'];
        
        // Get comment count
        $stmt_comments = $conn->prepare("SELECT COUNT(*) as count FROM student_advisor_activity WHERE student_id=? AND advisor_id=? AND type='comment'");
        $stmt_comments->execute([$sid, $advisor_id]);
        $comment_count = $stmt_comments->fetchColumn();
        
        // Get appointment count
        $stmt_meetings = $conn->prepare("SELECT COUNT(*) as count FROM student_advisor_activity WHERE student_id=? AND advisor_id=? AND type='meeting'");
        $stmt_meetings->execute([$sid, $advisor_id]);
        $meeting_count = $stmt_meetings->fetchColumn();
        
        // Get last consultation
        $stmt_last = $conn->prepare("SELECT created_at FROM student_advisor_activity WHERE student_id=? AND advisor_id=? ORDER BY created_at DESC LIMIT 1");
        $stmt_last->execute([$sid, $advisor_id]);
        $last_consultation = $stmt_last->fetchColumn() ?: 'N/A';
        
        // Get latest CGPA (simplified - get from most recent semester)
        $stmt_cgpa = $conn->prepare("
            SELECT c.academic_year, c.semester 
            FROM course_students cs
            JOIN courses c ON cs.course_id = c.id
            WHERE cs.student_id = ?
            ORDER BY c.academic_year DESC, c.semester DESC
            LIMIT 1
        ");
        $stmt_cgpa->execute([$sid]);
        $latest_semester = $stmt_cgpa->fetch(PDO::FETCH_ASSOC);
        
        $current_cgpa = 'N/A';
        $academic_status = 'N/A';
        
        if ($latest_semester) {
            // Calculate CGPA for latest semester (simplified calculation)
            $stmt_courses = $conn->prepare("
                SELECT c.id AS course_id, c.credit_hours
                FROM course_students cs
                JOIN courses c ON cs.course_id = c.id
                WHERE cs.student_id = ? AND c.academic_year = ? AND c.semester = ?
            ");
            $stmt_courses->execute([$sid, $latest_semester['academic_year'], $latest_semester['semester']]);
            $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);
            
            $total_points = 0;
            $total_credits = 0;
            
            foreach ($courses as $course) {
                $course_id = $course['course_id'];
                $credit_hours = $course['credit_hours'];
                
                // Get assessment scores
                $stmt_assess = $conn->prepare("
                    SELECT ac.max_mark, ac.percentage, COALESCE(ascore.mark_obtained, 0) as mark_obtained
                    FROM assessment_components ac
                    LEFT JOIN assessment_scores ascore ON ac.id = ascore.component_id AND ascore.student_id = ?
                    WHERE ac.course_id = ?
                ");
                $stmt_assess->execute([$sid, $course_id]);
                $assessments = $stmt_assess->fetchAll(PDO::FETCH_ASSOC);
                
                $total_assessment_score = 0;
                foreach ($assessments as $a) {
                    if ($a['max_mark'] > 0) {
                        $score = ($a['mark_obtained'] / $a['max_mark']) * $a['percentage'];
                        $total_assessment_score += $score;
                    }
                }
                
                // Get final exam score
                $stmt_final = $conn->prepare("SELECT mark_obtained FROM final_exam_scores WHERE student_id=? AND course_id=?");
                $stmt_final->execute([$sid, $course_id]);
                $final_score = $stmt_final->fetchColumn() ?: 0;
                
                $total_score = $total_assessment_score + ($final_score * 0.3);
                $gradeInfo = getGradePoint($total_score);
                
                $total_points += $gradeInfo['point'] * $credit_hours;
                $total_credits += $credit_hours;
            }
            
            if ($total_credits > 0) {
                $current_cgpa = round($total_points / $total_credits, 2);
                $academic_status = $current_cgpa <= 2.0 ? 'high-risk' : 'active';
            }
        }
        
        $export_data[] = [
            'name' => $student['name'],
            'matric_no' => $student['matric_no'],
            'total_comments' => $comment_count,
            'total_appointments' => $meeting_count,
            'last_consultation' => $last_consultation,
            'current_cgpa' => $current_cgpa,
            'academic_status' => $academic_status
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $export_data], JSON_UNESCAPED_UNICODE);
    exit;
}

// NEW: Export individual student report
if ($action === 'export_student_report') {
    $advisor_id = $_GET['advisor_id'] ?? null;
    $student_id = $_GET['student_id'] ?? null;
    
    if (!$advisor_id || !$student_id) {
        echo json_encode(['success' => false, 'message' => 'lack of parameter']);
        exit;
    }
    
    // Check if student belongs to this advisor
    $stmt = $conn->prepare("SELECT 1 FROM advisor_students WHERE advisor_id=? AND student_id=?");
    $stmt->execute([$advisor_id, $student_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'student not found or not assigned to this advisor']);
        exit;
    }
    
    // Reuse the existing students_detail logic but for single student
    $stmt = $conn->prepare("SELECT id, name, matric_no, email, phone FROM users WHERE id = ?");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student_info) {
        echo json_encode(['success' => false, 'message' => 'student not found']);
        exit;
    }
    
    // Get courses and academic data
    $stmt = $conn->prepare("
        SELECT c.id AS course_id, c.course_name, c.course_code, c.academic_year, c.semester, c.credit_hours
        FROM course_students cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.student_id = ?
        ORDER BY c.academic_year, c.semester, c.course_code
    ");
    $stmt->execute([$student_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by semester
    $semesters = [];
    foreach ($courses as $course) {
        $key = $course['academic_year'].'|'.$course['semester'];
        if (!isset($semesters[$key])) $semesters[$key] = [];
        $semesters[$key][] = $course;
    }
    
    $processed_semesters = [];
    foreach ($semesters as $semkey => $courses_this_sem) {
        list($academic_year, $semester) = explode('|', $semkey);
        $total_points = 0;
        $total_credits = 0;
        $course_list = [];
        
        foreach ($courses_this_sem as $course) {
            $course_id = $course['course_id'];
            $credit_hours = $course['credit_hours'];
            
            // Get assessment scores
            $stmt2 = $conn->prepare("
                SELECT ac.max_mark, ac.percentage, COALESCE(ascore.mark_obtained, 0) as mark_obtained
                FROM assessment_components ac
                LEFT JOIN assessment_scores ascore ON ac.id = ascore.component_id AND ascore.student_id = ?
                WHERE ac.course_id = ?
            ");
            $stmt2->execute([$student_id, $course_id]);
            $assessments = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            
            $total_assessment_score = 0;
            foreach ($assessments as $a) {
                if ($a['max_mark'] > 0) {
                    $score = ($a['mark_obtained'] / $a['max_mark']) * $a['percentage'];
                    $total_assessment_score += $score;
                }
            }
            
            // Get final exam score
            $stmt3 = $conn->prepare("SELECT mark_obtained FROM final_exam_scores WHERE student_id=? AND course_id=?");
            $stmt3->execute([$student_id, $course_id]);
            $final_score = $stmt3->fetchColumn() ?: 0;
            
            $total_score = $total_assessment_score + ($final_score * 0.3);
            $gradeInfo = getGradePoint($total_score);
            
            $course_list[] = [
                'course_id' => $course_id,
                'course_name' => $course['course_name'],
                'course_code' => $course['course_code'],
                'credit_hours' => $credit_hours,
                'total_score' => round($total_score, 2),
                'grade' => $gradeInfo['grade'],
                'grade_point' => $gradeInfo['point']
            ];
            
            $total_points += $gradeInfo['point'] * $credit_hours;
            $total_credits += $credit_hours;
        }
        
        $cgpa = $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
        $academic_status = $cgpa <= 2.0 ? 'high-risk' : 'active';
        
        $processed_semesters[] = [
            'academic_year' => $academic_year,
            'semester' => $semester,
            'cgpa' => $cgpa,
            'academic_status' => $academic_status,
            'courses' => $course_list
        ];
    }
    
    // Get advisor activities
    $stmt = $conn->prepare("
        SELECT id, type, content, meeting_time, created_at
        FROM student_advisor_activity
        WHERE student_id=? AND advisor_id=?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$student_id, $advisor_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $comments = [];
    $appointments = [];
    foreach ($activities as $a) {
        if ($a['type'] === 'comment') {
            $comments[] = [
                'id' => $a['id'],
                'content' => $a['content'],
                'created_at' => $a['created_at']
            ];
        } else if ($a['type'] === 'meeting') {
            $appointments[] = [
                'id' => $a['id'],
                'content' => $a['content'],
                'meeting_time' => $a['meeting_time'],
                'created_at' => $a['created_at']
            ];
        }
    }
    
    $export_data = [
        'student_id' => $student_info['id'],
        'matric_no' => $student_info['matric_no'],
        'name' => $student_info['name'],
        'email' => $student_info['email'],
        'phone' => $student_info['phone'],
        'semesters' => $processed_semesters,
        'advisor_comments' => $comments,
        'advisor_appointments' => $appointments
    ];
    
    echo json_encode(['success' => true, 'data' => $export_data], JSON_UNESCAPED_UNICODE);
    exit;
}


echo json_encode(['success' => false, 'message' => 'error action'], JSON_UNESCAPED_UNICODE);