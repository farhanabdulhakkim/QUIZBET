<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');

$user = $_SESSION['user'] ?? null;
if (!$user || !$user['is_admin']) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    try {
        $stats = [];
        $stats['subjects'] = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
        $stats['chapters'] = $pdo->query("SELECT COUNT(*) FROM chapters")->fetchColumn();
        $stats['quizzes'] = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
        $stats['questions'] = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
        echo json_encode(['success'=>true,'stats'=>$stats]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Error loading stats']);
    }
    exit;
}

if ($action === 'create_subject') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$name) { 
        echo json_encode(['success'=>false,'message'=>'Name required']); 
        exit; 
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (name,description,created_by) VALUES(?,?,?)");
        $stmt->execute([$name,$desc,$user['id']]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'create_chapter') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $subject_id = intval($_POST['subject_id'] ?? 0);
    if (!$name || !$subject_id) { 
        echo json_encode(['success'=>false,'message'=>'Name and subject required']); 
        exit; 
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO chapters (name,description,subject_id) VALUES(?,?,?)");
        $stmt->execute([$name,$desc,$subject_id]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'create_quiz') {
    $name = trim($_POST['name'] ?? '');
    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    $date_of_quiz = $_POST['date_of_quiz'] ?? '';
    $time_duration = intval($_POST['time_duration'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');
    
    if (!$name || !$chapter_id || !$date_of_quiz || !$time_duration) {
        echo json_encode(['success'=>false,'message'=>'All fields required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO quizzes (name,chapter_id,creator_id,date_of_quiz,time_duration,remarks) VALUES(?,?,?,?,?,?)");
        $stmt->execute([$name,$chapter_id,$user['id'],$date_of_quiz,$time_duration,$remarks]);
        echo json_encode(['success'=>true,'quiz_id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'add_question') {
    $quiz_id = intval($_POST['quiz_id'] ?? 0);
    $question = trim($_POST['question_statement'] ?? '');
    $options = $_POST['options'] ?? [];
    $correct = intval($_POST['correct_option'] ?? 0);
    $marks = intval($_POST['marks'] ?? 1);
    
    if (!$quiz_id || !$question || empty($options) || count($options) < 2) {
        echo json_encode(['success'=>false,'message'=>'All fields required with at least 2 options']);
        exit;
    }
    
    try {
        $options_json = json_encode(array_values($options));
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id,question_statement,options,correct_option,marks) VALUES(?,?,?,?,?)");
        $stmt->execute([$quiz_id,$question,$options_json,$correct,$marks]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'get_subjects') {
    try {
        $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name");
        $subjects = $stmt->fetchAll();
        echo json_encode(['success'=>true,'subjects'=>$subjects]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'get_chapters') {
    $subject_id = intval($_GET['subject_id'] ?? 0);
    try {
        if ($subject_id) {
            $stmt = $pdo->prepare("SELECT c.*, s.name as subject_name FROM chapters c JOIN subjects s ON c.subject_id = s.id WHERE c.subject_id = ? ORDER BY c.name");
            $stmt->execute([$subject_id]);
        } else {
            $stmt = $pdo->query("SELECT c.*, s.name as subject_name FROM chapters c JOIN subjects s ON c.subject_id = s.id ORDER BY s.name, c.name");
        }
        $chapters = $stmt->fetchAll();
        echo json_encode(['success'=>true,'chapters'=>$chapters]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

if ($action === 'get_quizzes') {
    $chapter_id = intval($_GET['chapter_id'] ?? 0);
    try {
        if ($chapter_id) {
            $stmt = $pdo->prepare("SELECT q.*, c.name as chapter_name, s.name as subject_name FROM quizzes q JOIN chapters c ON q.chapter_id = c.id JOIN subjects s ON c.subject_id = s.id WHERE q.chapter_id = ? ORDER BY q.date_of_quiz DESC");
            $stmt->execute([$chapter_id]);
        } else {
            $stmt = $pdo->query("SELECT q.*, c.name as chapter_name, s.name as subject_name FROM quizzes q JOIN chapters c ON q.chapter_id = c.id JOIN subjects s ON c.subject_id = s.id ORDER BY q.date_of_quiz DESC");
        }
        $quizzes = $stmt->fetchAll();
        echo json_encode(['success'=>true,'quizzes'=>$quizzes]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>'Database error']);
    }
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action']);