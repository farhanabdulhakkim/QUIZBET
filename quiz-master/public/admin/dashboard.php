<?php
require_once '../../includes/config.php';
require_login();
require_admin();

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="../">Quiz Master</a>
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="../auth/logout.php">Logout (<?= htmlspecialchars($user['username']) ?>)</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div class="card">
                <h3>Subjects Management</h3>
                <p>Create and manage quiz subjects</p>
                <a href="create_subject.php"><button>Manage Subjects</button></a>
            </div>

            <div class="card">
                <h3>Chapters Management</h3>
                <p>Create and manage chapters within subjects</p>
                <a href="create_chapter.php"><button>Manage Chapters</button></a>
            </div>

            <div class="card">
                <h3>Quiz Management</h3>
                <p>Create and manage quizzes</p>
                <a href="create_quiz.php"><button>Manage Quizzes</button></a>
            </div>

            <div class="card">
                <h3>Questions Management</h3>
                <p>Add questions to quizzes</p>
                <a href="add_question.php"><button>Manage Questions</button></a>
            </div>
        </div>

        <div class="card">
            <h3>Quick Stats</h3>
            <div id="stats">Loading statistics...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    // Load quick stats
    ajaxRequest('../api/api_admin.php?action=stats')
        .then(response => {
            if (response.success) {
                const stats = response.stats;
                document.getElementById('stats').innerHTML = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div style="text-align: center;">
                            <h4>${stats.subjects}</h4>
                            <p>Subjects</p>
                        </div>
                        <div style="text-align: center;">
                            <h4>${stats.chapters}</h4>
                            <p>Chapters</p>
                        </div>
                        <div style="text-align: center;">
                            <h4>${stats.quizzes}</h4>
                            <p>Quizzes</p>
                        </div>
                        <div style="text-align: center;">
                            <h4>${stats.questions}</h4>
                            <p>Questions</p>
                        </div>
                        <div style="text-align: center;">
                            <h4>${stats.users}</h4>
                            <p>Users</p>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('stats').innerHTML = 'Error loading statistics';
        });
    </script>
</body>
</html>