<?php
require_once '../../includes/config.php';
require_login();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="../">Quiz Master</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="view_quizzes.php">Available Quizzes</a>
            <a href="view_scores.php">My Scores</a>
            <a href="../auth/logout.php">Logout (<?= htmlspecialchars($user['username']) ?>)</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1>User Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div class="card">
                <h3>Available Quizzes</h3>
                <p>Browse and take available quizzes</p>
                <a href="view_quizzes.php"><button>View Quizzes</button></a>
            </div>

            <div class="card">
                <h3>My Scores</h3>
                <p>View your quiz results and performance</p>
                <a href="view_scores.php"><button>View Scores</button></a>
            </div>
        </div>

        <div class="card">
            <h3>Recent Activity</h3>
            <div id="recentActivity">Loading...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    ajaxRequest('../api/api_scores.php?action=recent_attempts')
        .then(response => {
            if (response.success && response.attempts.length > 0) {
                const html = response.attempts.map(a => 
                    `<div style="padding: 10px; border: 1px solid #ddd; margin: 5px 0;">
                        <strong>${a.quiz_name}</strong><br>
                        <small>Score: ${a.total_score} | Date: ${new Date(a.time_stamp_of_attempt).toLocaleDateString()}</small>
                    </div>`
                ).join('');
                document.getElementById('recentActivity').innerHTML = html;
            } else {
                document.getElementById('recentActivity').innerHTML = 'No recent activity';
            }
        })
        .catch(() => {
            document.getElementById('recentActivity').innerHTML = 'Error loading activity';
        });
    </script>
</body>
</html>