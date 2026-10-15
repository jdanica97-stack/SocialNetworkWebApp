<?php
// tests/Integration/UserModelTest.php
// Integration tests for UserModel CRUD, password hashing, and user search

namespace Tests\Integration;

use Tests\TestCase;
use UserModel;

class UserModelTest extends TestCase {
    private UserModel $userModel;

    protected function setUp(): void {
        parent::setUp();
        $this->userModel = new UserModel();
    }

    public function testUserCreationAndRetrieval(): void {
        $password = 'Secret123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $created = $this->userModel->create('johndoe', $hash, 'John Doe');

        $this->assertTrue($created);

        $user = $this->userModel->findByUsername('johndoe');
        $this->assertIsArray($user);
        $this->assertEquals('johndoe', $user['username']);
        $this->assertEquals('John Doe', $user['full_name']);
        $this->assertTrue(password_verify($password, $user['password']));
    }

    public function testUsernameExistsMethod(): void {
        $this->assertFalse($this->userModel->usernameExists('nonexistent'));

        $this->createTestUser('existinguser');
        $this->assertTrue($this->userModel->usernameExists('existinguser'));
    }

    public function testFindByIdAndGetProfileById(): void {
        $testUser = $this->createTestUser('alice', 'Password123!', 'Alice Wonderland');

        $found = $this->userModel->findById($testUser['id']);
        $this->assertIsArray($found);
        $this->assertEquals('alice', $found['username']);
        $this->assertEquals('Alice Wonderland', $found['full_name']);

        $profile = $this->userModel->getProfileById($testUser['id']);
        $this->assertEquals($found, $profile);
    }

    public function testUpdateProfile(): void {
        $testUser = $this->createTestUser('bob', 'Password123!', 'Bob Builder');

        $updated = $this->userModel->updateProfile($testUser['id'], 'Robert Builder', 'Can we fix it? Yes!');
        $this->assertTrue($updated);

        $refreshed = $this->userModel->findById($testUser['id']);
        $this->assertEquals('Robert Builder', $refreshed['full_name']);
        $this->assertEquals('Can we fix it? Yes!', $refreshed['bio']);
    }

    public function testUpdatePassword(): void {
        $testUser = $this->createTestUser('charlie', 'OldPassword123!');

        $newHash = password_hash('NewPassword456!', PASSWORD_DEFAULT);
        $updated = $this->userModel->updatePassword($testUser['id'], $newHash);
        $this->assertTrue($updated);

        $storedHash = $this->userModel->getPasswordHash($testUser['id']);
        $this->assertTrue(password_verify('NewPassword456!', $storedHash));
        $this->assertFalse(password_verify('OldPassword123!', $storedHash));
    }

    public function testSearchUsers(): void {
        $this->createTestUser('mariah', 'Pass123!', 'Mariah Carey');
        $this->createTestUser('mario', 'Pass123!', 'Mario Brothers');
        $this->createTestUser('luigi', 'Pass123!', 'Luigi Brothers');

        $results = $this->userModel->searchUsers('Mario');
        $this->assertCount(1, $results);
        $this->assertEquals('mario', $results[0]['username']);

        $broResults = $this->userModel->searchUsers('Brothers');
        $this->assertCount(2, $broResults);
    }

    public function testUpdatePrivacyFollowers(): void {
        $testUser = $this->createTestUser('privacyuser');

        $updated = $this->userModel->updatePrivacyFollowers($testUser['id'], 'private');
        $this->assertTrue($updated);

        $user = $this->userModel->findById($testUser['id']);
        $this->assertEquals('private', $user['privacy_followers']);
    }
}
