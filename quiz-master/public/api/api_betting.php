<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');
session_start();

$user = $_SESSION['user'] ?? null;
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_balance':
        try {
            $stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $wallet = $stmt->fetch();

            if ($wallet) {
                echo json_encode(['success' => true, 'balance' => $wallet['balance']]);
            } else {
                // Self-healing: If a wallet doesn't exist for a logged-in user, create one.
                $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)")->execute([$user['id'], 0.00]);
                echo json_encode(['success' => true, 'balance' => 0.00]);
            }
        } catch (PDOException $e) {
            error_log("API Error (get_balance): " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Could not retrieve wallet balance.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid API action specified.']);
        break;
}

exit;
