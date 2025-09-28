<?php
require_once '../../includes/config.php';
require_login();

$quiz_id = intval($_GET['quiz_id'] ?? 0);
if (!$quiz_id) {
    header('Location: view_quizzes.php');
    exit;
}

$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Quiz - Quiz Master</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 id="quizTitle">Loading Quiz...</h2>
            <div id="quizContent">
                <div class="loading">Loading questions...</div>
            </div>
        </div>
    </div>

    <script src="../js/app.js"></script>
    <script>
    let questions = [];
    let answers = {};
    let timer = null;
    const quizId = <?= $quiz_id ?>;

    function loadQuiz() {
        ajaxRequest(`../api/api_quiz.php?action=get_questions&quiz_id=${quizId}`)
            .then(response => {
                if (response.success) {
                    questions = response.questions;
                    if (questions.length === 0) {
                        document.getElementById('quizContent').innerHTML = '<p>No questions found for this quiz.</p>';
                        return;
                    }
                    
                    document.getElementById('quizTitle').textContent = questions[0].quiz_name;
                    renderQuestions();
                    startTimer(questions[0].time_duration);
                } else {
                    document.getElementById('quizContent').innerHTML = '<p>Error loading quiz questions.</p>';
                }
            });
    }

    function renderQuestions() {
        const html = questions.map((q, index) => 
            `<div class="question-card card">
                <h4>Question ${index + 1} (${q.marks} marks)</h4>
                <p>${q.question_statement}</p>
                <div class="options">
                    ${q.options.map((option, optIndex) => 
                        `<div class="option">
                            <input type="radio" name="q_${q.id}" value="${optIndex}" id="q_${q.id}_${optIndex}">
                            <label for="q_${q.id}_${optIndex}">${option}</label>
                        </div>`
                    ).join('')}
                </div>
            </div>`
        ).join('');
        
        document.getElementById('quizContent').innerHTML = html + 
            '<button onclick="submitQuiz()" class="btn-success" style="margin-top: 20px;">Submit Quiz</button>';
        
        // Add event listeners for answers
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                const questionId = this.name.replace('q_', '');
                answers[questionId] = parseInt(this.value);
            });
        });
    }

    function startTimer(duration) {
        timer = new QuizTimer(duration, submitQuiz);
        timer.start();
    }

    function submitQuiz() {
        if (timer) timer.stop();
        
        if (Object.keys(answers).length === 0) {
            if (!confirm('You haven\'t answered any questions. Are you sure you want to submit?')) {
                return;
            }
        }
        
        const submitBtn = document.querySelector('button');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        }
        
        ajaxRequest('../api/api_quiz.php?action=submit_attempt', {
            quiz_id: quizId,
            answers: JSON.stringify(answers)
        }, 'POST')
        .then(response => {
            if (response.success) {
                alert(`Quiz submitted successfully! Your score: ${response.score}`);
                window.location.href = 'view_scores.php';
            } else {
                alert('Error: ' + response.message);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Quiz';
                }
            }
        });
    }

    // Prevent page refresh/close without warning
    window.addEventListener('beforeunload', function(e) {
        if (Object.keys(answers).length > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    loadQuiz();
    </script>
</body>
</html>