function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    } else {
        document.body.prepend(alertDiv);
    }
    
    setTimeout(() => {
        alertDiv.style.transition = 'opacity 0.3s';
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

function showLoading(element) {
    element.innerHTML = '<div class="loading">Loading...</div>';
}

function hideLoading() {
    const loading = document.querySelector('.loading');
    if (loading) loading.remove();
}

function ajaxRequest(url, data = null, method = 'GET') {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data && method === 'POST') {
        options.body = new URLSearchParams(data);
    }
    
    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        });
}

function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    return data;
}

function updateWalletBalance() {
    const balanceElement = document.querySelector('.wallet-balance');
    if (!balanceElement) return;

    ajaxRequest('/quiz-master/public/api/api_betting.php?action=get_balance', null, 'GET')
        .then(response => {
            if (response.success) {
                const balance = parseFloat(response.balance).toFixed(2);
                balanceElement.textContent = `Balance: $${balance}`;
            } else {
                balanceElement.textContent = 'Balance: N/A';
            }
        })
        .catch(error => {
            console.error('Failed to update wallet balance:', error);
            balanceElement.textContent = 'Balance: Error';
        });
}

function submitQuizAndSettleBet(quizId, score) {
    showAlert('Submitting your score...', 'info');

    const data = {
        quiz_id: quizId,
        score: score
    };

    ajaxRequest('/quiz-master/public/api/api_scores.php?action=submit_score', data, 'POST')
        .then(response => {
            if (response.success) {
                showAlert(response.message, response.outcome === 'won' ? 'success' : 'info');
                updateWalletBalance();
            } else {
                showAlert(response.message || 'An error occurred while submitting your score.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error submitting score:', error);
            showAlert('A critical network error occurred. Please check your connection.', 'danger');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    updateWalletBalance();

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';

            ajaxRequest('register.php', serializeForm(this), 'POST')
                .then(response => {
                    if (response.success) {
                        showAlert('Registration successful! Redirecting to login...', 'success');
                        setTimeout(() => {
                            window.location.href = '/quiz-master/public/auth/login.php';
                        }, 2000);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                })
                .catch(error => showAlert('An error occurred during registration.', 'danger'))
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Register';
                });
        });
    }

    const quizSubmitButton = document.getElementById('submitQuizBtn');
    if (quizSubmitButton) {
        quizSubmitButton.addEventListener('click', function() {
            this.disabled = true;
            const quizId = this.dataset.quizId;
            const score = 95; // Placeholder for your score calculation logic
            submitQuizAndSettleBet(quizId, score);
        });
    }
});

class QuizTimer {
    constructor(duration, onComplete) {
        this.duration = duration * 60;
        this.remaining = this.duration;
        this.onComplete = onComplete;
        this.timerElement = null;
        this.interval = null;
    }
    
    start() {
        this.createTimerElement();
        this.interval = setInterval(() => {
            this.remaining--;
            this.updateDisplay();
            
            if (this.remaining <= 0) {
                this.stop();
                if (this.onComplete) {
                    this.onComplete();
                }
            }
        }, 1000);
    }
    
    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    }
    
    createTimerElement() {
        this.timerElement = document.createElement('div');
        this.timerElement.className = 'quiz-timer';
        document.body.appendChild(this.timerElement);
        this.updateDisplay();
    }
    
    updateDisplay() {
        if (!this.timerElement) return;
        
        const minutes = Math.floor(this.remaining / 60);
        const seconds = this.remaining % 60;
        this.timerElement.textContent = `Time: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (this.remaining <= 300) {
            this.timerElement.style.background = '#dc3545';
        }
    }
}
