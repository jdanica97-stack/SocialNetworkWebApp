<?php
// tests/Unit/SecurityTest.php
// Unit tests for Security and Validation helper functions

namespace Tests\Unit;

use Tests\TestCase;

class SecurityTest extends TestCase {
    public function testCsrfTokenGeneration(): void {
        $_SESSION = [];
        $token1 = csrf_token();

        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1), 'CSRF token should be a 64-character hex string');

        // Subsequent call in the same session must return the identical token
        $token2 = csrf_token();
        $this->assertEquals($token1, $token2);
    }

    public function testCsrfFieldGeneratesHtmlInput(): void {
        $_SESSION = [];
        $html = csrf_field();

        $this->assertStringContainsString('<input type="hidden" name="csrf_token"', $html);
        $this->assertStringContainsString('value="' . $_SESSION['csrf_token'] . '"', $html);
    }

    public function testValidateCsrfTokenWithValidSessionToken(): void {
        $_SESSION['csrf_token'] = 'valid_test_token_1234567890abcdef';
        $_POST['csrf_token'] = 'valid_test_token_1234567890abcdef';

        $this->assertTrue(validate_csrf_token());
    }

    public function testValidateCsrfTokenWithInvalidToken(): void {
        $_SESSION['csrf_token'] = 'valid_test_token_1234567890abcdef';
        $_POST['csrf_token'] = 'wrong_token';

        $this->assertFalse(validate_csrf_token());
    }

    public function testValidateCsrfTokenWithMissingSessionToken(): void {
        $_SESSION = [];
        $_POST['csrf_token'] = 'any_token';

        $this->assertFalse(validate_csrf_token());
    }

    public function testValidateCsrfTokenFromRequestHeader(): void {
        $_SESSION['csrf_token'] = 'header_test_token_abc';
        $_POST = [];
        $_SERVER['HTTP_X_CSRF_TOKEN'] = 'header_test_token_abc';

        $this->assertTrue(validate_csrf_token());

        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    }

    public function testXssPreventionHelperEscapesSpecialChars(): void {
        $raw = '<script>alert("XSS")</script>&"\'';
        $escaped = e($raw);

        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;&amp;&quot;&#039;', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function testFormatTimeAgoHandlesVariousTimestamps(): void {
        $now = date('Y-m-d H:i:s');
        $this->assertEquals('Just now', format_time_ago($now));

        $fiveMinutesAgo = date('Y-m-d H:i:s', time() - 300);
        $this->assertEquals('5m ago', format_time_ago($fiveMinutesAgo));

        $threeHoursAgo = date('Y-m-d H:i:s', time() - 10800);
        $this->assertEquals('3h ago', format_time_ago($threeHoursAgo));

        $twoDaysAgo = date('Y-m-d H:i:s', time() - 172800);
        $this->assertEquals('2d ago', format_time_ago($twoDaysAgo));

        $this->assertEquals('Just now', format_time_ago(''));
    }
}
