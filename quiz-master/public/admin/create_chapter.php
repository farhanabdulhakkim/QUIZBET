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
    <title>Manage Chapters - Quiz Master</title>
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
            <h2>Create Chapter</h2>
            <form id="chapterForm">
                <div class="form-group">
                    <label for="subject_id">Subject:</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Chapter Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit">Create Chapter</button>
            </form>
        </div>

        <div class="card">
            <h3>Existing Chapters</h3>
            <div id="chaptersList">Loading...</div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    document.getElementById('chapterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = serializeForm(this);
        ajaxRequest('../api/api_admin.php?action=create_chapter', formData, 'POST')
            .then(response => {
                if (response.success) {
                    showAlert('Chapter created successfully!');
                    this.reset();
                    loadChapters();
                } else {
                    showAlert(response.message, 'danger');
                }
            });
    });

    function loadSubjects() {
        ajaxRequest('../api/api_admin.php?action=get_subjects')
            .then(response => {
                if (response.success) {
                    const select = document.getElementById('subject_id');
                    select.innerHTML = '<option value="">Select Subject</option>' +
                        response.subjects.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
                }
            });
    }

    function loadChapters() {
        ajaxRequest('../api/api_admin.php?action=get_chapters')
            .then(response => {
                if (response.success) {
                    const html = response.chapters.map(c => 
                        `<div style="padding: 10px; border: 1px solid #ddd; margin: 5px 0;">
                            <strong>${c.name}</strong> (${c.subject_name})<br>
                            <small>${c.description || 'No description'}</small>
                        </div>`
                    ).join('');
                    document.getElementById('chaptersList').innerHTML = html || 'No chapters found';
                }
            });
    }

    loadSubjects();
    loadChapters();
    </script>
</body>
</html>