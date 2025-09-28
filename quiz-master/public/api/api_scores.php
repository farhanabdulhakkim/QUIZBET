<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');
session_start();

$user = $_SESSION['user'] ?? null;
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'submit_score':
        $quiz_id = (int)($_POST['quiz_id'] ?? 0);
        $score = (int)($_POST['score'] ?? 0);
        $user_id = $user['id'];

        if (!$quiz_id) {
            echo json_encode(['success' => false, 'message' => 'Quiz ID is missing.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM bets WHERE user_id = ? AND quiz_id = ? AND status = 'pending' ORDER BY placed_at DESC LIMIT 1");
            $stmt->execute([$user_id, $quiz_id]);
            $bet = $stmt->fetch();

            $pdo->prepare("INSERT INTO attempts (user_id, quiz_id, score) VALUES (?, ?, ?)")->execute([$user_id, $quiz_id, $score]);

            if ($bet) {
                if ($score >= 80) { // WIN CONDITION
                    $payout = $bet['bet_amount'] * 2;
                    $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ?")->execute([$payout, $user_id]);
                    $pdo->prepare("UPDATE bets SET status = 'won' WHERE id = ?")->execute([$bet['id']]);
                    $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'payout_win', ?)")->execute([$user_id, $payout, "Won bet on quiz #" . $quiz_id]);
                    $message = "Congratulations! You scored $score% and won $" . number_format($payout, 2) . ".";
                    echo json_encode(['success' => true, 'outcome' => 'won', 'message' => $message]);
                } else { // LOST CONDITION
                    $pdo->prepare("UPDATE bets SET status = 'lost' WHERE id = ?")->execute([$bet['id']]);
                    $message = "You scored $score%. Unfortunately, you lost the bet.";
                    echo json_encode(['success' => true, 'outcome' => 'lost', 'message' => $message]);
                }
            } else {
                $message = "Your score of $score% has been recorded.";
                echo json_encode(['success' => true, 'outcome' => 'no_bet', 'message' => $message]);
            }
            
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Score Submission Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
        }
        break;

    case 'list_user_scores':
        try {
            $stmt = $pdo->prepare("SELECT a.*, q.name as quiz_name, c.name as chapter_name, s.name as subject_name FROM attempts a JOIN quizzes q ON a.quiz_id=q.id JOIN chapters c ON q.chapter_id=c.id JOIN subjects s ON c.subject_id=s.id WHERE a.user_id = ? ORDER BY a.time_stamp_of_attempt DESC");
            $stmt->execute([$user['id']]);
            $attempts = $stmt->fetchAll();
            echo json_encode(['success' => true, 'attempts' => $attempts]);
        } catch (Exception $e) {
            error_log("List Scores Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error while fetching scores.']);
        }
        break;

    case 'recent_attempts':
        try {
            $stmt = $pdo->prepare("SELECT a.*, q.name as quiz_name FROM attempts a JOIN quizzes q ON a.quiz_id=q.id WHERE a.user_id = ? ORDER BY a.time_stamp_of_attempt DESC LIMIT 5");
            $stmt->execute([$user['id']]);
            $attempts = $stmt->fetchAll();
            echo json_encode(['success' => true, 'attempts' => $attempts]);
        } catch (Exception $e) {
            error_log("Recent Attempts Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error while fetching recent attempts.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        break;
}

exit;
