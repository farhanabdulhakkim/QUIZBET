<?php
require_once '../includes/config.php';
$user = current_user();
$wallet_balance = 0;
$quizzes = [];

if ($user) {
    $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $wallet = $stmt->fetch();
    $wallet_balance = $wallet['balance'] ?? 0;

    $quizzes = $pdo->query("SELECT id, name FROM quizzes WHERE is_active = 1 ORDER BY name")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quiz Master</title>
    <link rel="stylesheet" href="css/style.css">
    <style>.wallet-balance { margin-left: 20px; font-weight: bold; color: #28a745; }</style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="/quiz-master/public/">Quiz Master</a>
            <?php if ($user): ?>
                <a href="/quiz-master/public/user/wallet.php">My Wallet</a>
                <a href="auth/logout.php">Logout</a>
                <span class="wallet-balance">Balance: $<?= number_format($wallet_balance, 2) ?></span>
            <?php else: ?>
                <a href="/quiz-master/public/auth/login.php">Login</a>
                <a href="/quiz-master/public/auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <div class="card">
            <h1>Welcome to Quiz Master!</h1>
        </div>
        <?php if ($user && !$user['is_admin']): ?>
        <div class="card">
            <h2>Available Quizzes</h2>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-bet-item">
                    <span><?= htmlspecialchars($quiz['name']) ?></span>
                    <form action="place_bet.php" method="POST">
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <input type="number" name="bet_amount" placeholder="Bet Amount" min="1" required>
                        <button type="submit">Place Bet & Start</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <script src="js/app.js"></script>
</body>
</html>
