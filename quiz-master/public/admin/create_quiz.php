<?php
require_once '../../includes/config.php';
require_login();
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <a href="../">Quiz Master</a>
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Create Quiz</h2>
            <form id="quizForm">
                <div class="form-group">
                    <label for="chapter_id">Chapter:</label>
                    <select id="chapter_id" name="chapter_id" required>
                        <option value="">Select Chapter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Quiz Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="date_of_quiz">Quiz Date:</label>
                    <input type="date" id="date_of_quiz" name="date_of_quiz" required>
                </div>
                <div class="form-group">
                    <label for="time_duration">Duration (minutes):</label>
                    <input type="number" id="time_duration" name="time_duration" min="1" required>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks:</label>
                    <textarea id="remarks" name="remarks" rows="3"></textarea>
                </div>
                <button type="submit">Create Quiz</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Quizzes</h3>
            <div id="quizzesList">Loading...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = serializeForm(this);
        ajaxRequest('../api/api_admin.php?action=create_quiz', formData, 'POST')
            .then(response => {
                if (response.success) {
                    showAlert('Quiz created successfully!');
                    this.reset();
                    loadQuizzes();
                } else {
                    showAlert(response.message, 'danger');
                }
            });
    });

    function loadChapters() {
        ajaxRequest('../api/api_admin.php?action=get_chapters')
            .then(response => {
                if (response.success) {
                    const select = document.getElementById('chapter_id');
                    select.innerHTML = '<option value="">Select Chapter</option>' +
                        response.chapters.map(c => `<option value="${c.id}">${c.subject_name} - ${c.name}</option>`).join('');
                }
            });
    }

    function loadQuizzes() {
        ajaxRequest('../api/api_admin.php?action=get_quizzes')
            .then(response => {
                if (response.success) {
                    const html = response.quizzes.map(q => 
                        `<div style="padding: 10px; border: 1px solid #ddd; margin: 5px 0;">
                            <strong>${q.name}</strong><br>
                            <small>${q.subject_name} - ${q.chapter_name} | ${q.date_of_quiz} | ${q.time_duration} min</small><br>
                            <small>${q.remarks || 'No remarks'}</small>
                        </div>`
                    ).join('');
                    document.getElementById('quizzesList').innerHTML = html || 'No quizzes found';
                }
            });
    }

    loadChapters();
    loadQuizzes();
    </script>
</body>
</html>