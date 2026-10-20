<?php
// app/controllers/SettingsController.php
// Controller handling Account Settings: Change Name & Update Password

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/AuthController.php';

class SettingsController {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    /**
     * Enforce authentication for account settings.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to view account settings.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Display Account Settings page.
     */
    public function index() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->getProfileById($userId);

        if (!$user) {
            unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name']);
            $_SESSION['error_message'] = 'User session expired. Please log in again.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }

        $pageTitle = 'Account Settings';
        require __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Update Account Information (Full Name & Username).
     * Validates both fields together prior to updating; prevents partial updates.
     */
    public function updateAccountInfo() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        $currentUser = $this->userModel->getProfileById($userId);
        if (!$currentUser) {
            $_SESSION['error_message'] = 'User profile not found.';
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        // Retrieve submitted fields (or fallback to current user values if field was omitted)
        $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : $currentUser['full_name'];
        $username = isset($_POST['username']) ? trim($_POST['username']) : $currentUser['username'];

        $errors = [];

        // 1. Validate Full Name
        if ($fullName === '') {
            $errors[] = 'Full Name is required.';
        } elseif (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
            $errors[] = 'Full Name must be between 2 and 100 characters.';
        }

        // 2. Validate Username
        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            $errors[] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username may only contain letters, numbers, and underscores.';
        } elseif ($this->userModel->usernameExists($username, $userId)) {
            $errors[] = 'Username is already taken. Please choose another.';
        }

        // 3. Atomically update only if BOTH fields pass validation (no partial updates!)
        if (empty($errors)) {
            $updated = false;
            try {
                $updated = $this->userModel->updateAccountInfo($userId, $fullName, $username);
            } catch (\PDOException $e) {
                error_log("Account update DB error: " . $e->getMessage());
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
                    $errors[] = 'Username is already taken. Please choose another.';
                } else {
                    $errors[] = 'A database error occurred while updating your account. Please try again.';
                }
            } catch (\Throwable $e) {
                error_log("Account update error: " . $e->getMessage());
                $errors[] = 'A system error occurred while updating your account. Please try again.';
            }

            if ($updated) {
                // Update session immediately so top navigation, profile dropdown, and pages reflect change without logout
                $_SESSION['full_name'] = $fullName;
                $_SESSION['username']  = $username;
                $_SESSION['profile_image_synced'] = time();

                $_SESSION['success_message'] = 'Account information has been updated successfully.';
                if (!headers_sent()) {
                    header('Location: ' . BASE_URL . '/index.php?action=settings');
                }
                exit;
            } elseif (empty($errors)) {
                $errors[] = 'Failed to update account information. Please try again.';
            }
        }

        // If validation failed or DB error occurred, no changes saved
        $_SESSION['error_message'] = implode(' ', $errors);
        if (!headers_sent()) {
            header('Location: ' . BASE_URL . '/index.php?action=settings');
        }
        exit;
    }

    /**
     * Change Name (Account setting alias).
     */
    public function updateName() {
        $this->updateAccountInfo();
    }

    /**
     * Change Username (Account setting alias).
     */
    public function updateUsername() {
        $this->updateAccountInfo();
    }

    /**
     * Update Password (Security setting).
     * Verifies current password, validates complexity, and updates password hash.
     */
    public function changePassword() {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }

        // Verify current password against stored hash
        $existingHash = $this->userModel->getPasswordHash($userId);
        if (!empty($currentPassword) && (!$existingHash || !password_verify($currentPassword, $existingHash))) {
            $errors[] = 'Incorrect current password.';
        }

        // Validate new password complexity requirements using AuthController helper
        $complexityErrors = AuthController::validatePasswordComplexity($newPassword, $confirmPassword);
        $errors = array_merge($errors, $complexityErrors);

        // Check that new password is not identical to current password
        if (empty($errors) && $existingHash && password_verify($newPassword, $existingHash)) {
            $errors[] = 'New password cannot be the same as your current password.';
        }

        if (empty($errors)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($this->userModel->updatePassword($userId, $newHash)) {
                $_SESSION['success_message'] = 'Your password has been updated successfully.';
                header('Location: ' . BASE_URL . '/index.php?action=settings');
                exit;
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }
        }

        $_SESSION['error_message'] = implode(' ', $errors);
        header('Location: ' . BASE_URL . '/index.php?action=settings');
        exit;
    }
}
