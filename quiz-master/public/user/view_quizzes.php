<?php
require_once '../../includes/config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Quizzes - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="../">Quiz Master</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="view_quizzes.php">Available Quizzes</a>
            <a href="view_scores.php">My Scores</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Available Quizzes</h2>
            <div id="quizzesList">Loading...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    function loadQuizzes() {
        ajaxRequest('../api/api_quiz.php?action=list_quizzes')
            .then(response => {
                if (response.success) {
                    const html = response.quizzes.map(q => 
                        `<div class="card">
                            <h4>${q.name}</h4>
                            <p><strong>Subject:</strong> ${q.subject_name}</p>
                            <p><strong>Chapter:</strong> ${q.chapter_name}</p>
                            <p><strong>Date:</strong> ${q.date_of_quiz}</p>
                            <p><strong>Duration:</strong> ${q.time_duration} minutes</p>
                            <p><strong>Remarks:</strong> ${q.remarks || 'None'}</p>
                            <button onclick="startQuiz(${q.id})" class="btn-success">Start Quiz</button>
                        </div>`
                    ).join('');
                    document.getElementById('quizzesList').innerHTML = html || '<p>No quizzes available</p>';
                } else {
                    document.getElementById('quizzesList').innerHTML = '<p>Error loading quizzes</p>';
                }
            });
    }

    function startQuiz(quizId) {
        if (confirm('Are you sure you want to start this quiz? You can only attempt it once.')) {
            window.location.href = `attempt_quiz.php?quiz_id=${quizId}`;
        }
    }

    loadQuizzes();
    </script>
</body>
</html>