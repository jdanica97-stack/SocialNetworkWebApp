<?php
// tests/Integration/DatabaseConnectionTest.php
// Integration tests verifying database connectivity, attributes, and safe isolation

namespace Tests\Integration;

use Tests\TestCase;
use Database;
use PDO;

class DatabaseConnectionTest extends TestCase {
    public function testDatabaseConnectionInstance(): void {
        $pdo = Database::connect();

        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertEquals(PDO::FETCH_ASSOC, $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        $this->assertEmpty($pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES));
    }

    public function testDatabaseIsolationGuarantee(): void {
        $activeDb = $this->db->query("SELECT DATABASE()")->fetchColumn();

        $this->assertEquals('social_app_test', $activeDb, 'Test suite MUST run against isolated test database!');
        $this->assertNotEquals('social_app', $activeDb, 'Production database must NEVER be used by test suite!');
    }

    public function testRequiredDatabaseTablesExist(): void {
        $tables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $expected = ['users', 'posts', 'comments', 'likes', 'follows', 'messages', 'notifications', 'events'];
        foreach ($expected as $table) {
            $this->assertContains($table, $tables, "Expected table '{$table}' to exist in test database.");
        }
    }

    public function testDatabaseTimezoneConfigured(): void {
        $stmt = $this->db->query("SELECT @@session.time_zone");
        $tz = $stmt->fetchColumn();

        $this->assertEquals('+08:00', $tz);
    }
}
