<?php
session_start();
require '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /quiz-master/public/auth/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallet = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 50");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Wallet - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>My Wallet</h1>
            <h2>Current Balance: $<?= number_format($wallet['balance'] ?? 0.00, 2); ?></h2>
        </div>
        <div class="card">
            <h3>Transaction History</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Amount</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="4">No transactions found.</td></tr>
                    <?php else: foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i', strtotime($tx['transaction_date'])) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $tx['type'])) ?></td>
                            <td>$<?= number_format($tx['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($tx['description']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
