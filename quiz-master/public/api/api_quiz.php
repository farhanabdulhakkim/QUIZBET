<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user) { 
    echo json_encode(['success'=>false,'message'=>'Login required']); 
    exit; 
}

$action = $_GET['action'] ?? '';

if ($action == 'list_quizzes') {
    try {
        $stmt = $pdo->query("SELECT q.*, c.name as chapter_name, s.name as subject_name FROM quizzes q JOIN chapters c ON q.chapter_id = c.id JOIN subjects s ON c.subject_id = s.id WHERE q.date_of_quiz >= CURDATE() ORDER BY q.date_of_quiz ASC");
        $quizzes = $stmt->fetchAll();
        echo json_encode(['success'=>true,'quizzes'=>$quizzes]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action == 'get_questions') {
    $quiz_id = intval($_GET['quiz_id'] ?? 0);
    if (!$quiz_id) {
        echo json_encode(['success'=>false,'message'=>'Quiz ID required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT q.id,q.question_statement,q.options,q.marks,qu.time_duration,qu.name as quiz_name FROM questions q JOIN quizzes qu ON q.quiz_id = qu.id WHERE q.quiz_id = ? ORDER BY q.id");
        $stmt->execute([$quiz_id]);
        $questions = $stmt->fetchAll();
        
        foreach ($questions as &$q) {
            $q['options'] = json_decode($q['options']);
        }
        
        echo json_encode(['success'=>true,'questions'=>$questions]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action == 'submit_attempt') {
    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    $answers = $_POST['answers'] ?? [];
    
    if (is_string($answers)) {
        $answers = json_decode($answers, true);
    }
    
    if (!$quiz_id || empty($answers)) {
        echo json_encode(['success'=>false,'message'=>'Quiz ID and answers required']);
        exit;
    }
    
    try {
        // Check if user already attempted this quiz
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attempts WHERE quiz_id = ? AND user_id = ?");
        $stmt->execute([$quiz_id, $user['id']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success'=>false,'message'=>'Quiz already attempted']);
            exit;
        }
        
        // Get correct answers
        $placeholders = implode(',', array_fill(0, count($answers), '?'));
        $qids = array_keys($answers);
        $stmt = $pdo->prepare("SELECT id, correct_option, marks FROM questions WHERE id IN ($placeholders)");
        $stmt->execute($qids);
        $rows = $stmt->fetchAll();
        
        $byId = [];
        foreach ($rows as $r) {
            $byId[$r['id']] = $r;
        }
        
        $total = 0;
        $raw_response = [];
        
        foreach ($answers as $qid => $chosen) {
            if (!isset($byId[$qid])) continue;
            
            $correct = intval($byId[$qid]['correct_option']);
            $marks = intval($byId[$qid]['marks']);
            $awarded = (intval($chosen) === $correct) ? $marks : 0;
            $total += $awarded;
            
            $raw_response[] = [
                'q' => $qid,
                'chosen' => intval($chosen),
                'correct' => $correct,
                'awarded' => $awarded
            ];
        }
        
        $stmt = $pdo->prepare("INSERT INTO attempts (quiz_id,user_id,total_score,raw_response) VALUES(?,?,?,?)");
        $stmt->execute([$quiz_id, $user['id'], $total, json_encode($raw_response)]);
        
        echo json_encode(['success'=>true,'score'=>$total,'attempt_id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action']);