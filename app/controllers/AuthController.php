<?php
// app/controllers/AuthController.php
// Controller handling user registration, login, and logout

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    /**
     * Show registration form or process registration submission.
     */
    public function register() {
        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $errors = [];
        $formData = [
            'full_name' => '',
            'username'  => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF Token
            if (!validate_csrf_token()) {
                $errors[] = 'Security Error: Invalid or expired CSRF token. Please try submitting again.';
            }

            // Retrieve and sanitize inputs
            $fullName        = trim($_POST['full_name'] ?? '');
            $username        = trim($_POST['username'] ?? '');
            $password        = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $formData['full_name'] = $fullName;
            $formData['username']  = $username;

            // Server-side validation
            if (empty($fullName)) {
                $errors[] = 'Full Name is required.';
            } elseif (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 100) {
                $errors[] = 'Full Name must be between 2 and 100 characters.';
            }

            if (empty($username)) {
                $errors[] = 'Username is required.';
            } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
                $errors[] = 'Username must be between 3 and 50 characters.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username may only contain letters, numbers, and underscores.';
            } elseif ($this->userModel->usernameExists($username)) {
                $errors[] = 'Username is already taken. Please choose another.';
            }

            if (empty($password)) {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }

            // If validation passes, create user
            if (empty($errors)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $created = false;
                try {
                    $created = $this->userModel->create($username, $passwordHash, $fullName);
                } catch (PDOException $e) {
                    error_log("User registration DB error: " . $e->getMessage());
                    // Check for duplicate entry (SQLSTATE 23000)
                    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
                        $errors[] = 'Username is already taken. Please choose another.';
                    } else {
                        $errors[] = 'A database error occurred while creating your account. Please try again.';
                    }
                } catch (Throwable $e) {
                    error_log("User registration unexpected error: " . $e->getMessage());
                    $errors[] = 'An unexpected error occurred while creating your account. Please try again.';
                }

                if ($created) {
                    $_SESSION['success_message'] = 'Registration successful! You can now log in.';
                    header('Location: ' . BASE_URL . '/index.php?action=login');
                    exit;
                }
            }
        }

        // Render registration view
        $pageTitle = 'Register';
        require __DIR__ . '/../views/auth/register.php';
    }

    /**
     * Show login form or process login submission.
     */
    public function login() {
        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        }

        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF Token
            if (!validate_csrf_token()) {
                $errors[] = 'Security Error: Invalid or expired CSRF token. Please try submitting again.';
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username)) {
                $errors[] = 'Username is required.';
            }
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByUsername($username);

                if (!$user) {
                    $errors[] = 'No account found with that username.';
                } elseif (!password_verify($password, $user['password'])) {
                    $errors[] = 'Incorrect password.';
                } else {
                    // Password is valid - regenerate session ID to prevent fixation attacks
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id']       = $user['id'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['full_name']     = $user['full_name'];
                    $_SESSION['role']          = $user['role'] ?? 'user';
                    $_SESSION['profile_image'] = $user['profile_image'] ?? null;

                    $_SESSION['success_message'] = 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!';

                    // Role-based redirection: Administrator -> Admin Dashboard, Normal User -> Feed
                    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                        header('Location: ' . BASE_URL . '/index.php?action=admin');
                    } else {
                        header('Location: ' . BASE_URL . '/index.php');
                    }
                    exit;
                }
            }
        }

        // Render login view
        $pageTitle = 'Login';
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Log out current user and destroy session.
     */
    public function logout() {
        // Clear all session variables
        $_SESSION = [];

        // Regenerate session ID to ensure a fresh session token
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        // Set flash message on new session state
        $_SESSION['success_message'] = 'You have been logged out successfully.';
        header('Location: ' . BASE_URL . '/index.php?action=login');
        exit;
    }
}
