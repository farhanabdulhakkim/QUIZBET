<?php
require_once '../../includes/config.php';

// If already logged in, redirect
if (current_user()) {
    $user = current_user();
    $redirect = $user['is_admin'] ? '/quiz-master/public/admin/dashboard.php' : '/quiz-master/public/user/dashboard.php';
    header('Location: ' . $redirect);
    exit;
}

// Handle AJAX login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        echo json_encode(['success'=>false,'message'=>'Missing credentials']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT id,username,password,is_admin,full_name FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'is_admin' => (bool)$user['is_admin'],
            'full_name' => $user['full_name']
        ];
        
        $redirect = $user['is_admin'] ? '/quiz-master/public/admin/dashboard.php' : '/quiz-master/public/user/dashboard.php';
        echo json_encode(['success'=>true, 'redirect'=>$redirect]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Invalid credentials']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="/quiz-master/public/">Quiz Master</a>
            <a href="/quiz-master/public/auth/register.php">Register</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Login</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Login</button>
            </form>
            
            <p style="margin-top: 20px;">
                Don't have an account? <a href="/quiz-master/public/auth/register.php">Register here</a>
            </p>
            
            <div style="margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 4px;">
                <strong>Demo Accounts:</strong><br>
                Admin: username=<code>admin</code>, password=<code>admin123</code>
            </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = serializeForm(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
        
        ajaxRequest('login.php', formData, 'POST')
            .then(response => {
                if (response.success) {
                    showAlert('Login successful!', 'success');
                    setTimeout(() => {
                        window.location.href = response.redirect || '/';
                    }, 1000);
                } else {
                    showAlert(response.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            });
    });
    </script>
</body>
</html>