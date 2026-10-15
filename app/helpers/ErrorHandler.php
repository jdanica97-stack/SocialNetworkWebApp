<?php
// app/helpers/ErrorHandler.php
// Centralized Error and Exception Handling for SMCC Connect

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ErrorHandler {
    private static $logFile = __DIR__ . '/../../storage/logs/app.log';

    /**
     * Sanitize message to prevent passwords, tokens, and hashes from appearing in logs.
     */
    public static function sanitizeLogMessage(string $msg): string {
        $msg = preg_replace('/(password|passwd|pwd)(\s*[:=]\s*|\s*=>\s*[\'"]?)[^\s,;\'"]+/i', '$1$2***', $msg);
        $msg = preg_replace('/(csrf_token|token|session_id)(\s*[:=]\s*|\s*=>\s*[\'"]?)[^\s,;\'"]+/i', '$1$2***', $msg);
        $msg = preg_replace('/(mysql:host=[^;]+;port=\d+;dbname=[^;]+;charset=[^;]+)/i', '$1', $msg);
        return $msg;
    }

    /**
     * Write sanitized message to system error_log and storage/logs/app.log.
     */
    public static function writeLog(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $sanitizedMessage = self::sanitizeLogMessage($message);
        $contextStr = !empty($context) ? ' ' . json_encode(array_map(function($v) {
            return is_string($v) ? self::sanitizeLogMessage($v) : $v;
        }, $context)) : '';

        $entry = "[{$timestamp}] [{$level}] {$sanitizedMessage}{$contextStr}" . PHP_EOL;

        // Write to PHP/Apache error log
        error_log("[SMCC Connect {$level}] {$sanitizedMessage}");

        // Also write to dedicated app log file if directory exists
        $dir = dirname(self::$logFile);
        if (is_dir($dir) && is_writable($dir)) {
            @file_put_contents(self::$logFile, $entry, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Register global exception and error handlers.
     */
    public static function register(): void {
        // Report all errors in PHP engine, but control display based on environment
        error_reporting(E_ALL);

        // Turn off display_errors to prevent raw stack traces or DB paths from leaking to users
        ini_set('display_errors', '0');

        // Enable error logging to PHP/Apache error log
        ini_set('log_errors', '1');

        set_error_handler([self::class, 'handlePhpError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Convert PHP Warnings / Notices / Errors into ErrorExceptions when appropriate.
     */
    public static function handlePhpError(int $errno, string $errstr, string $errfile, int $errline): bool {
        // Respect error_reporting setting (@-operator suppression)
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Log the sanitized technical warning or error
        self::writeLog("PHP Error {$errno}", "{$errstr} in {$errfile} on line {$errline}");

        // For severe warnings or errors, throw ErrorException
        if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions uniformly across Web and AJAX requests.
     */
    public static function handleException(Throwable $exception): void {
        // Securely log technical details to server log without exposing secrets
        $details = sprintf(
            "%s: %s in %s:%d\nStack Trace:\n%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        self::writeLog("Uncaught Exception", $details);

        // Detect if request is AJAX
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || isset($_POST['ajax']) || isset($_GET['ajax'])
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        http_response_code(500);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error'   => 'An unexpected server error occurred. Please try again later.'
            ]);
            exit;
        }

        self::renderErrorPage(500, 'Server Error', 'An unexpected error occurred while processing your request. Please try again later.');
        exit;
    }

    /**
     * Helper to render standardized error pages (400, 403, 404, 500).
     */
    public static function renderErrorPage(int $statusCode, string $title, string $message, string $backUrl = ''): void {
        http_response_code($statusCode);
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        if (empty($backUrl)) {
            $backUrl = $baseUrl . '/';
        }

        $pageTitle = $title . ' - SMCC Connect';

        // Check if header exists to render themed page
        $headerPath = __DIR__ . '/../views/layouts/header.php';
        $footerPath = __DIR__ . '/../views/layouts/footer.php';

        if (file_exists($headerPath) && file_exists($footerPath)) {
            require_once $headerPath;
            ?>
            <div class="container py-5">
                <div class="card-modern p-5 text-center mx-auto" style="max-width: 620px;">
                    <div class="mb-3">
                        <div class="brand-icon-box mx-auto" style="width: 64px; height: 64px; font-size: 2rem; background: var(--bg-hover); color: var(--navy-primary, #07193d);">
                            <?php if ($statusCode === 404): ?>
                                <i class="bi bi-question-circle"></i>
                            <?php elseif ($statusCode === 403 || $statusCode === 401): ?>
                                <i class="bi bi-shield-x"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-triangle text-danger"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill mb-2">Error <?= (int)$statusCode ?></span>
                    <h2 class="fw-bold text-dark mb-2"><?= htmlspecialchars($title) ?></h2>
                    <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-outline-primary px-4 rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> Return Home
                        </a>
                    </div>
                </div>
            </div>
            <?php
            require_once $footerPath;
        } else {
            // Minimal standalone fallback
            echo '<!DOCTYPE html><html><head><title>' . htmlspecialchars($title) . '</title></head><body>';
            echo '<h1>' . htmlspecialchars($title) . '</h1>';
            echo '<p>' . htmlspecialchars($message) . '</p>';
            echo '<p><a href="' . htmlspecialchars($backUrl) . '">Return Home</a></p>';
            echo '</body></html>';
        }
    }
}
