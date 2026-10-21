<?php
require_once __DIR__ . '/config/database.php';
try {
    $pdo = Database::connect();
    $stmt = $pdo->query('SELECT 1 AS test');
    $row = $stmt->fetch();
    echo "Connection successful, test value: " . $row['test'] . "\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>