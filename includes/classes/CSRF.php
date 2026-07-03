<?php
/**
 * Future Vision College – CSRF Protection
 * Save to: includes/classes/CSRF.php
 */

declare(strict_types=1);

class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_BYTES = 32;

    /**
     * Generate (or return existing) CSRF token
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_BYTES));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Output hidden input field – use inside every <form>
     * Usage: <?= CSRF::field() ?>
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_csrf_token" value="%s">',
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verify token submitted via POST
     * Call at the top of every form-processing page
     */
    public static function verify(): bool
    {
        $submitted = $_POST['_csrf_token'] ?? '';

        if (empty($submitted) || empty($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        // Timing-safe comparison
        return hash_equals($_SESSION[self::TOKEN_KEY], $submitted);
    }

    /**
     * Verify and die with 403 if invalid
     */
    public static function requireValid(): void
    {
        if (!self::verify()) {
            http_response_code(403);
            die('403 – Invalid or missing CSRF token. Please go back and try again.');
        }
        // Rotate token after successful verification
        self::rotate();
    }

    /**
     * Rotate token (call after successful form submission)
     */
    public static function rotate(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_BYTES));
    }
}
