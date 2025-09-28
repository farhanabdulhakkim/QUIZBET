<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /quiz-master/public/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $quiz_id = (int)$_POST['quiz_id'];
    $bet_amount = (float)$_POST['bet_amount'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $wallet = $stmt->fetch();

        if ($bet_amount <= 0 || !$wallet || $wallet['balance'] < $bet_amount) {
            die('Invalid bet amount or insufficient funds.');
        }

        $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ?")->execute([$bet_amount, $user_id]);
        $pdo->prepare("INSERT INTO bets (user_id, quiz_id, bet_amount) VALUES (?, ?, ?)")->execute([$user_id, $quiz_id, $bet_amount]);
        $pdo->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'bet_placed', ?)")->execute([$user_id, $bet_amount, "Placed bet on quiz ID: $quiz_id"]);
        
        $pdo->commit();

        header('Location: /quiz-master/public/take_quiz.php?id=' . $quiz_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die('An error occurred while placing your bet.');
    }
}
