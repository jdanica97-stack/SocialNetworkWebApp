<?php
// tests/TestCase.php
// Base Test Case providing safe database rollback and fixture helpers for SMCC Connect

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Database;
use PDO;

abstract class TestCase extends BaseTestCase {
    /**
     * @var PDO
     */
    protected $db;

    protected function setUp(): void {
        parent::setUp();

        // Ensure clean global state
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';

        $this->db = Database::connect();

        // Safety assert: verify database is social_app_test
        $currentDb = $this->db->query("SELECT DATABASE()")->fetchColumn();
        if ($currentDb !== 'social_app_test') {
            $this->fail("FATAL SAFETY CHECK: Attempted to run test on database '{$currentDb}' instead of 'social_app_test'!");
        }

        // Clean tables before each test to guarantee determinism
        $this->cleanDatabase();
    }

    protected function tearDown(): void {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        parent::tearDown();
    }

    /**
     * Safely clear records from test database tables.
     */
    protected function cleanDatabase(): void {
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $tables = ['comments', 'likes', 'notifications', 'messages', 'follows', 'events', 'posts', 'users'];
        foreach ($tables as $table) {
            $this->db->exec("DELETE FROM `{$table}`");
            $this->db->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
        }
        $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    /**
     * Helper to insert a test user into the test database.
     */
    protected function createTestUser(string $username = 'testuser', string $password = 'Password123!', string $fullName = 'Test User', string $role = 'user'): array {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (:u, :p, :f, :r)");
        $stmt->execute([
            ':u' => $username,
            ':p' => $hash,
            ':f' => $fullName,
            ':r' => $role
        ]);
        $id = (int)$this->db->lastInsertId();

        return [
            'id' => $id,
            'username' => $username,
            'password' => $password,
            'full_name' => $fullName,
            'role' => $role
        ];
    }

    /**
     * Helper to insert a test post into the test database.
     */
    protected function createTestPost(int $userId, string $content = 'This is a test post.'): array {
        $stmt = $this->db->prepare("INSERT INTO posts (user_id, content) VALUES (:uid, :cnt)");
        $stmt->execute([
            ':uid' => $userId,
            ':cnt' => $content
        ]);
        $id = (int)$this->db->lastInsertId();

        return [
            'id' => $id,
            'user_id' => $userId,
            'content' => $content
        ];
    }

    /**
     * Helper to simulate a logged-in session.
     */
    protected function loginAs(int $userId, string $username = 'testuser', string $fullName = 'Test User', string $role = 'user'): void {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['role'] = $role;
    }
}
