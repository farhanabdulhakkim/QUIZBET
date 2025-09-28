<?php
require_once '../../includes/config.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Scores - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h2>My Quiz Scores</h2>
            <div id="scoresList">Loading...</div>
        </div>

        <div class="card">
            <h3>Performance Chart</h3>
            <canvas id="performanceChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    function loadScores() {
        ajaxRequest('../api/api_scores.php?action=list_user_scores')
            .then(response => {
                if (response.success) {
                    const html = response.attempts.map(a => 
                        `<div style="padding: 15px; border: 1px solid #ddd; margin: 10px 0; border-radius: 4px;">
                            <h4>${a.quiz_name}</h4>
                            <p><strong>Subject:</strong> ${a.subject_name} - ${a.chapter_name}</p>
                            <p><strong>Score:</strong> ${a.total_score}</p>
                            <p><strong>Date:</strong> ${new Date(a.time_stamp_of_attempt).toLocaleString()}</p>
                        </div>`
                    ).join('');
                    document.getElementById('scoresList').innerHTML = html || '<p>No quiz attempts found</p>';
                    
                    // Create chart
                    if (response.attempts.length > 0) {
                        createChart(response.attempts);
                    }
                } else {
                    document.getElementById('scoresList').innerHTML = '<p>Error loading scores</p>';
                }
            });
    }

    function createChart(attempts) {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const labels = attempts.reverse().map(a => new Date(a.time_stamp_of_attempt).toLocaleDateString());
        const scores = attempts.map(a => a.total_score);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Quiz Scores',
                    data: scores,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    loadScores();
    </script>
</body>
</html>