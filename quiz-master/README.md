# Quiz Master - PHP/AJAX/MySQL Quiz Management System

A comprehensive web-based quiz management system built with PHP, MySQL, and AJAX.

## Features

### Admin Features
- Create and manage subjects
- Create and manage chapters within subjects
- Create and manage quizzes with time limits
- Add multiple-choice questions to quizzes
- View system statistics

### User Features
- User registration and login
- Browse available quizzes
- Take timed quizzes with auto-submit
- View quiz scores and performance charts
- Dashboard with recent activity

## Technology Stack
- **Backend**: PHP 8+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **AJAX**: Fetch API
- **Charts**: Chart.js
- **Server**: XAMPP (Apache + PHP + MySQL)

## Installation

1. **Setup XAMPP**
   - Install XAMPP and start Apache and MySQL services
   - Set document root to `quiz-master/public/` directory

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import `sql/schema.sql` to create database and tables
   - Default admin account: username=`admin`, password=`admin123`

3. **Configuration**
   - Update database credentials in `includes/config.php` if needed
   - Ensure PHP extensions: PDO, PDO_MySQL, JSON

4. **File Permissions**
   - Ensure web server has read access to all files
   - No special write permissions needed

## Directory Structure
```
quiz-master/
├── public/                 # Document root
│   ├── index.php          # Homepage
│   ├── css/style.css      # Styles
│   ├── js/app.js          # JavaScript utilities
│   ├── auth/              # Authentication
│   ├── user/              # User pages
│   ├── admin/             # Admin pages
│   └── api/               # AJAX endpoints
├── includes/              # PHP includes
├── sql/                   # Database schema
└── README.md
```

## Usage

1. **Access the Application**
   - Navigate to `http://localhost/` (or your configured path)
   - Register a new account or login with admin credentials

2. **Admin Workflow**
   - Login as admin
   - Create subjects → Create chapters → Create quizzes → Add questions

3. **User Workflow**
   - Register/Login as user
   - Browse available quizzes
   - Take quizzes (timed with auto-submit)
   - View scores and performance charts

## Security Features
- Password hashing with `password_hash()`
- PDO prepared statements (SQL injection prevention)
- Session-based authentication
- Input validation and sanitization
- CSRF protection ready (tokens implemented)

## API Endpoints

### Admin API (`/api/api_admin.php`)
- `?action=stats` - Get system statistics
- `?action=create_subject` - Create new subject
- `?action=create_chapter` - Create new chapter
- `?action=create_quiz` - Create new quiz
- `?action=add_question` - Add question to quiz

### Quiz API (`/api/api_quiz.php`)
- `?action=list_quizzes` - Get available quizzes
- `?action=get_questions` - Get quiz questions
- `?action=submit_attempt` - Submit quiz answers

### Scores API (`/api/api_scores.php`)
- `?action=list_user_scores` - Get user's quiz scores
- `?action=recent_attempts` - Get recent quiz attempts

## Database Schema

### Tables
- `users` - User accounts and admin flags
- `subjects` - Quiz subjects/categories
- `chapters` - Chapters within subjects
- `quizzes` - Quiz definitions with timing
- `questions` - Questions with JSON options
- `attempts` - User quiz attempts and scores

## Browser Support
- Modern browsers with ES6+ support
- Chrome 60+, Firefox 55+, Safari 12+, Edge 79+

## Development Notes
- Uses vanilla JavaScript (no jQuery dependency)
- Responsive CSS Grid layout
- JSON storage for question options and attempt details
- Timer functionality with auto-submit
- Chart.js for performance visualization

## Troubleshooting

### Common Issues
1. **Database Connection Error**
   - Check MySQL service is running
   - Verify credentials in `includes/config.php`

2. **404 Errors**
   - Ensure document root points to `public/` directory
   - Check Apache rewrite rules if using custom URLs

3. **JavaScript Errors**
   - Check browser console for errors
   - Ensure all script files are loading correctly

4. **Timer Not Working**
   - Check JavaScript console for errors
   - Ensure proper JSON responses from API

## License
This project is for educational purposes. Feel free to modify and use as needed.