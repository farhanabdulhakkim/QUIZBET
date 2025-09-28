<?php
// Test different database configurations
$configs = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'root', 'pass' => 'root'],
    ['user' => 'root', 'pass' => 'password'],
    ['user' => '', 'pass' => ''],
];

echo "<h2>Testing Database Connections</h2>";

foreach ($configs as $i => $config) {
    try {
        $pdo = new PDO("mysql:host=localhost", $config['user'], $config['pass']);
        echo "<p>✓ Config " . ($i+1) . " works: user='{$config['user']}', pass='{$config['pass']}'</p>";
        break;
    } catch (PDOException $e) {
        echo "<p>✗ Config " . ($i+1) . " failed: user='{$config['user']}', pass='{$config['pass']}'</p>";
    }
}
?>