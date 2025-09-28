CREATE DATABASE IF NOT EXISTS quiz_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quiz_master;

-- Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  qualification VARCHAR(100),
  dob DATE NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subjects Table
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Chapters Table
CREATE TABLE chapters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Quizzes Table (Modified)
CREATE TABLE quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  chapter_id INT NOT NULL,
  creator_id INT,
  date_of_quiz DATE NOT NULL,
  time_duration INT NOT NULL, -- in minutes
  is_active TINYINT(1) DEFAULT 1, -- MODIFICATION: Added to control which quizzes are available for betting
  remarks TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
  FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Questions Table
CREATE TABLE questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  question_statement TEXT NOT NULL,
  options JSON NOT NULL,
  correct_option INT NOT NULL, -- index (0..n-1)
  marks INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Attempts Table (Formerly Scores)
CREATE TABLE attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  user_id INT NOT NULL,
  time_stamp_of_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  score INT NOT NULL, -- Renamed from total_score for consistency
  raw_response JSON,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- NEW TABLES FOR BETTING & E-COMMERCE SYSTEM
-- ========================================================

-- Wallets Table: Stores funds for each user
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bets Table: Records every bet placed on a quiz
CREATE TABLE IF NOT EXISTS bets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    bet_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'won', 'lost') DEFAULT 'pending',
    placed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Transactions Table: Logs all financial movements for auditing
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('deposit', 'bet_placed', 'payout_win') NOT NULL,
    description VARCHAR(255),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ========================================================
-- DEFAULT DATA INSERTION
-- ========================================================

-- Insert default admin user with a securely hashed password (password: admin123)
INSERT INTO users (username, password, full_name, email, dob, is_admin) 
VALUES ('admin', '$2y$10$Iha.e.F8j.551/E9LCR.X.0L2f7J9Yg3/bA/8U2j.G5.Y.dK6Zz.e', 'Administrator', 'admin@quiz.com', '1990-01-01', 1);

