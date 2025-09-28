<?php
require_once 'includes/config.php';

// Check database connection
echo "Database connection: OK<br>";

// Check if admin user exists
$stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE username = ?");
$stmt->execute(['admin']);
$user = $stmt->fetch();

if ($user) {
    echo "Admin user found:<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    echo "Is admin: " . $user['is_admin'] . "<br><br>";
    
    // Test password verification
    $test_password = 'admin123';
    $verify_result = password_verify($test_password, $user['password']);
    echo "Password verification for 'admin123': " . ($verify_result ? 'SUCCESS' : 'FAILED') . "<br>";
    
    // Generate new hash for comparison
    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "New hash for 'admin123': " . $new_hash . "<br>";
    
} else {
    echo "Admin user NOT found in database<br>";
    
    // Show all users
    $stmt = $pdo->query("SELECT username FROM users");
    $users = $stmt->fetchAll();
    echo "Existing users: " . implode(', ', array_column($users, 'username')) . "<br>";
}
?>