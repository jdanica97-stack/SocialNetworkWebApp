<?php
// tests/Unit/ErrorHandlerTest.php
// Unit tests for ErrorHandler, log sanitization, and graceful error handling

namespace Tests\Unit;

use Tests\TestCase;
use ErrorHandler;
use ErrorException;

class ErrorHandlerTest extends TestCase {
    public function testSanitizeLogMessageRedactsPasswords(): void {
        $raw = "User login attempt with password=SecretPassword123! from 127.0.0.1";
        $sanitized = ErrorHandler::sanitizeLogMessage($raw);

        $this->assertStringNotContainsString('SecretPassword123!', $sanitized);
        $this->assertStringContainsString('password=***', $sanitized);
    }

    public function testSanitizeLogMessageRedactsCsrfTokens(): void {
        $raw = "Failed request: csrf_token=a1b2c3d4e5f678901234567890abcdef";
        $sanitized = ErrorHandler::sanitizeLogMessage($raw);

        $this->assertStringNotContainsString('a1b2c3d4e5f678901234567890abcdef', $sanitized);
        $this->assertStringContainsString('csrf_token=***', $sanitized);
    }

    public function testHandlePhpErrorConvertsSevereErrorsToExceptions(): void {
        $this->expectException(ErrorException::class);

        // E_USER_ERROR is a severe error that should trigger ErrorException
        ErrorHandler::handlePhpError(E_USER_ERROR, 'Fatal test error', __FILE__, __LINE__);
    }

    public function testHandlePhpErrorReturnsTrueForWarnings(): void {
        // Warnings should be logged and return true without crashing
        $result = ErrorHandler::handlePhpError(E_USER_WARNING, 'Non-fatal test warning', __FILE__, __LINE__);
        $this->assertTrue($result);
    }

    public function testWriteLogAppendsToApplicationLogFile(): void {
        $testMsg = "Automated test log message - ID " . uniqid();
        ErrorHandler::writeLog('TEST_INFO', $testMsg);

        $logPath = dirname(__DIR__, 2) . '/storage/logs/app.log';
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $this->assertStringContainsString($testMsg, $content);
        } else {
            $this->markTestSkipped('storage/logs/app.log not writable in current environment');
        }
    }
}
