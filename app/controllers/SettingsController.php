<?php
// app/controllers/SettingsController.php
// Controller handling system, security, account, and application settings

require_once __DIR__ . '/../models/UserModel.php';

class SettingsController {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    /**
     * Enforce authentication for settings.
     */
    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'You must be logged in to view settings.';
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    /**
     * Display Settings dashboard.
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

        $pageTitle = 'Settings';
        require __DIR__ . '/../views/settings/index.php';
    }

    /**
     * Update Password (Security setting).
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
            header('Location: ' . BASE_URL . '/index.php?action=settings&tab=security');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = [];

        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $existingHash = $this->userModel->getPasswordHash($userId);
            if (!$existingHash || !password_verify($currentPassword, $existingHash)) {
                $errors[] = 'Incorrect current password.';
            } else {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($this->userModel->updatePassword($userId, $newHash)) {
                    $_SESSION['success_message'] = 'Password changed successfully.';
                    header('Location: ' . BASE_URL . '/index.php?action=settings&tab=security');
                    exit;
                } else {
                    $errors[] = 'Failed to update password. Please try again.';
                }
            }
        }

        $_SESSION['error_message'] = implode(' ', $errors);
        header('Location: ' . BASE_URL . '/index.php?action=settings&tab=security');
        exit;
    }

    /**
     * Update Notification Preferences.
     */
    public function updatePreferences() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?action=settings');
            exit;
        }

        if (!validate_csrf_token()) {
            $_SESSION['error_message'] = 'Security Error: Invalid or expired CSRF token.';
            header('Location: ' . BASE_URL . '/index.php?action=settings&tab=notifications');
            exit;
        }

        // Store notification and display preferences in session
        $_SESSION['pref_notif_likes']    = !empty($_POST['pref_notif_likes']) ? 1 : 0;
        $_SESSION['pref_notif_comments'] = !empty($_POST['pref_notif_comments']) ? 1 : 0;
        $_SESSION['pref_notif_follows']  = !empty($_POST['pref_notif_follows']) ? 1 : 0;
        $_SESSION['pref_sound']          = !empty($_POST['pref_sound']) ? 1 : 0;

        $_SESSION['success_message'] = 'Preferences saved successfully.';
        header('Location: ' . BASE_URL . '/index.php?action=settings&tab=notifications');
        exit;
    }
}
