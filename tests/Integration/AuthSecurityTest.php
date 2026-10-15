<?php
// tests/Integration/AuthSecurityTest.php
// Integration tests for authentication logic, credential verification, and role security

namespace Tests\Integration;

use Tests\TestCase;
use UserModel;

class AuthSecurityTest extends TestCase {
    private UserModel $userModel;

    protected function setUp(): void {
        parent::setUp();
        $this->userModel = new UserModel();
    }

    public function testValidCredentialVerification(): void {
        $rawPassword = 'SecurePassword123!';
        $user = $this->createTestUser('loginuser', $rawPassword, 'Login User');

        $found = $this->userModel->findByUsername('loginuser');
        $this->assertNotEmpty($found);
        $this->assertTrue(password_verify($rawPassword, $found['password']));
    }

    public function testInvalidPasswordFailsVerification(): void {
        $rawPassword = 'RealPassword123!';
        $this->createTestUser('authuser', $rawPassword);

        $found = $this->userModel->findByUsername('authuser');
        $this->assertFalse(password_verify('WrongPassword', $found['password']));
    }

    public function testNonexistentUserFailsAuthentication(): void {
        $found = $this->userModel->findByUsername('ghostuser');
        $this->assertFalse($found);
    }

    public function testPasswordIsHashedWithBcryptOrArgon(): void {
        $user = $this->createTestUser('hashuser', 'AnyPassword');
        $found = $this->userModel->findByUsername('hashuser');

        $info = password_get_info($found['password']);
        $this->assertNotEquals(0, $info['algo'], 'Password must be hashed with a standard secure algorithm');
        $this->assertNotEquals('AnyPassword', $found['password'], 'Plaintext password must never be stored');
    }

    public function testRoleBasedAccessDistinction(): void {
        $admin = $this->createTestUser('superadmin', 'AdminPass123!', 'Admin User', 'admin');
        $regular = $this->createTestUser('regularuser', 'UserPass123!', 'Regular User', 'user');

        $adminRow = $this->userModel->findById($admin['id']);
        $regularRow = $this->userModel->findById($regular['id']);

        $this->assertEquals('admin', $adminRow['role']);
        $this->assertEquals('user', $regularRow['role']);
    }

    public function testSessionSimulationAndAuthenticationEnforcement(): void {
        // Logged-out state
        $this->assertArrayNotHasKey('user_id', $_SESSION);

        // Simulate login
        $this->loginAs(100, 'sessiontest', 'Session Test User', 'user');
        $this->assertEquals(100, $_SESSION['user_id']);
        $this->assertEquals('sessiontest', $_SESSION['username']);
        $this->assertEquals('user', $_SESSION['role']);

        // Simulate logout
        $_SESSION = [];
        $this->assertEmpty($_SESSION);
    }
}
