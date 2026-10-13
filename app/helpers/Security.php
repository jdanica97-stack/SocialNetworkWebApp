<?php
// app/helpers/Security.php
// Centralized Security & Validation Helper functions for SMCC Connect

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or retrieve the current session CSRF token.
 * Uses cryptographically secure random_bytes.
 *
 * @return string 64-character hex string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output an HTML hidden input field containing the current CSRF token.
 *
 * @return string HTML string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate a submitted CSRF token against the session token.
 * Supports token passed via POST, JSON body, or X-CSRF-Token / X-XSRF-Token request headers.
 * Uses timing attack-safe hash_equals().
 *
 * @param string|null $token Optional explicit token to validate
 * @return bool True if valid, false otherwise
 */
function validate_csrf_token(?string $token = null): bool {
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';

        // Check request headers for AJAX/Fetch requests
        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
        }
    }

    if (empty($token) || !is_string($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * XSS prevention helper: escape output string with ENT_QUOTES and UTF-8 encoding.
 *
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Synchronize PHP default timezone with local system timezone (Asia/Manila)
date_default_timezone_set('Asia/Manila');

/**
 * Standard relative time formatting (e.g., 'Just now', '5m ago', '2h ago', 'M d, Y')
 *
 * @param string|int $timestamp
 * @return string
 */
function format_time_ago($timestamp): string {
    if (empty($timestamp)) {
        return 'Just now';
    }

    $time = is_numeric($timestamp) ? (int)$timestamp : strtotime($timestamp);
    if ($time === false || $time <= 0) {
        return 'Just now';
    }

    $diff = time() - $time;

    // Handle future drift or clock skew within 10 seconds
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'h ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . 'd ago';
    } else {
        return date('M j, Y', $time);
    }
}
