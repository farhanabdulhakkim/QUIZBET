<?php
require_once '../../includes/config.php';
require_once '../../includes/csrf.php';

// If a user is already logged in, redirect them to the main page.
if (current_user()) {
    header('Location: /quiz-master/public/');
    exit;
}

// Handle the AJAX POST request for registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // 1. Sanitize and retrieve user input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $dob = $_POST['dob'] ?? '1970-01-01';
    
    // 2. Validate input
    if (!$username || !$email || !$password || !$full_name) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields. Please fill out all required information.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'The provided email address is not valid.']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
        exit;
    }
    
    // 3. Hash the password for secure storage
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Use a transaction to ensure both user and wallet are created successfully
        $pdo->beginTransaction();

        // Step A: Insert the new user into the 'users' table
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, qualification, dob) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hash, $full_name, $qualification, $dob]);
        $new_user_id = $pdo->lastInsertId();

        // Step B: Create a wallet for the new user with a $10.00 starting bonus
        $wallet_stmt = $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)");
        $wallet_stmt->execute([$new_user_id, 10.00]);

        // If both queries succeed, commit the transaction
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Registration successful! You may now log in.']);

    } catch (PDOException $e) {
        // If any error occurs, roll back the entire transaction
        $pdo->rollBack();
        
        // Check for a duplicate entry error (unique username or email)
        if ($e->errorInfo[1] == 1062) {
            echo json_encode(['success' => false, 'message' => 'This username or email address is already taken.']);
        } else {
            // For any other database error, log it and show a generic message
            error_log("Registration Error: " . $e->getMessage()); 
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred during registration. Please try again.']);
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="/quiz-master/public/">Quiz Master</a>
            <a href="/quiz-master/public/auth/login.php">Login</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Create Your Account</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="qualification">Qualification:</label>
                    <input type="text" id="qualification" name="qualification">
                </div>
                
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <button type="submit">Register</button>
            </form>
            
            <p style="margin-top: 20px;">
                Already have an account? <a href="/quiz-master/public/auth/login.php">Login here</a>
            </p>
        </div>
    </div>

    <script src="../js/app.js"></script>
</body>
</html>
