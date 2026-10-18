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

    public function testPasswordShorterThan8CharsIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('Short1!');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must be at least 8 characters long.', $errors);
    }

    public function testPasswordWithoutUppercaseIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('lowercase123!');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one uppercase letter (A–Z).', $errors);
    }

    public function testPasswordWithoutLowercaseIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('UPPERCASE123!');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one lowercase letter (a–z).', $errors);
    }

    public function testPasswordWithoutNumberIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('NoNumberHere!');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one number (0–9).', $errors);
    }

    public function testPasswordWithoutSpecialCharacterIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('NoSpecialChar123');
        $this->assertNotEmpty($errors);
        $this->assertContains('Password must contain at least one special character (e.g., @, #, $, %, !).', $errors);
    }

    public function testMismatchedPasswordConfirmationIsRejected(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('ValidPass123!', 'DifferentPass123!');
        $this->assertNotEmpty($errors);
        $this->assertContains('Passwords do not match.', $errors);
    }

    public function testValidPasswordComplexityAccepted(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $errors = \AuthController::validatePasswordComplexity('ValidPass123!', 'ValidPass123!');
        $this->assertEmpty($errors);
    }

    public function testFullAccountRegistrationAndLoginFlowWithComplexity(): void {
        require_once __DIR__ . '/../../app/controllers/AuthController.php';
        $username = 'complexuser';
        $plainPassword = 'SuperSecret2026!';
        $fullName = 'Complex Test User';

        // 1. Verify complexity passes
        $errors = \AuthController::validatePasswordComplexity($plainPassword, $plainPassword);
        $this->assertEmpty($errors);

        // 2. Create user with hashed password
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $created = $this->userModel->create($username, $hash, $fullName);
        $this->assertTrue($created);

        // 3. Retrieve user and verify credentials
        $user = $this->userModel->findByUsername($username);
        $this->assertNotEmpty($user);
        $this->assertTrue(password_verify($plainPassword, $user['password']));

        // 4. Test duplicate account prevention
        $this->assertTrue($this->userModel->usernameExists($username));
    }
}
