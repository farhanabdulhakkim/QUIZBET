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
    <title>Manage Subjects - Quiz Master</title>
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
            <h2>Create Subject</h2>
            <form id="subjectForm">
                <div class="form-group">
                    <label for="name">Subject Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit">Create Subject</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Subjects</h3>
            <div id="subjectsList">Loading...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    document.getElementById('subjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = serializeForm(this);
        ajaxRequest('../api/api_admin.php?action=create_subject', formData, 'POST')
            .then(response => {
                if (response.success) {
                    showAlert('Subject created successfully!');
                    this.reset();
                    loadSubjects();
                } else {
                    showAlert(response.message, 'danger');
                }
            });
    });

    function loadSubjects() {
        ajaxRequest('../api/api_admin.php?action=get_subjects')
            .then(response => {
                if (response.success) {
                    const html = response.subjects.map(s => 
                        `<div style="padding: 10px; border: 1px solid #ddd; margin: 5px 0;">
                            <strong>${s.name}</strong><br>
                            <small>${s.description || 'No description'}</small>
                        </div>`
                    ).join('');
                    document.getElementById('subjectsList').innerHTML = html || 'No subjects found';
                }
            });
    }
    loadSubjects();
    </script>
</body>
</html>