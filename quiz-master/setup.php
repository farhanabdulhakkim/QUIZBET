<?php
// Quick setup script for Quiz Master
echo "<h1>Quiz Master Setup</h1>";

// Check if database exists
$host = 'localhost';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS quiz_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✓ Database 'quiz_master' created/verified</p>";
    
    // Use the database
    $pdo->exec("USE quiz_master");
    
    // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>✓ Database tables created successfully</p>";
    echo "<p>✓ Admin user created (username: admin, password: admin123)</p>";
    echo "<h2>Setup Complete!</h2>";
    echo "<p><a href='/quiz-master/public/'>Go to Quiz Master</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure XAMPP MySQL is running</p>";
}
?>