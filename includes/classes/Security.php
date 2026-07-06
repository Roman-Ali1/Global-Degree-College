<?php
/**
 * Global Degree College – Security Helpers
 * Save to: includes/classes/Security.php
 *
 * Centralizes security checks used across the app.
 */

declare(strict_types=1);

class Security
{
    // ── Input length caps ─────────────────────────────────────

    /**
     * Enforce max length on a string — truncates silently.
     * Use BEFORE inserting into DB to prevent buffer overflow
     * and oversized payload attacks.
     */
    public static function maxLen(string $value, int $max): string
    {
        return mb_substr($value, 0, $max);
    }

    /**
     * Sanitize a string for safe DB storage.
     * Strips tags + enforces max length.
     */
    public static function clean(string $value, int $max = 255): string
    {
        return mb_substr(strip_tags(trim($value)), 0, $max);
    }

    /**
     * Deep-clean for rich text: strip dangerous tags only,
     * allow safe formatting.
     * (Upgrade to HTMLPurifier for production rich-text.)
     */
    public static function cleanHtml(string $html, int $max = 65535): string
    {
        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><h2><h3><h4><blockquote>';
        return mb_substr(strip_tags($html, $allowed), 0, $max);
    }

    // ── Output encoding ───────────────────────────────────────

    /**
     * Safe echo for HTML context — alias of h().
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Safe echo for HTML attribute values with quotes.
     */
    public static function attr(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Safe echo for JavaScript string context.
     */
    public static function js(mixed $value): string
    {
        return json_encode((string)$value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    // ── Filename sanitization ─────────────────────────────────

    /**
     * Sanitize a filename — strip path traversal, dangerous chars.
     * Used before saving any user-supplied filename.
     */
    public static function filename(string $name): string
    {
        // Remove directory separators and null bytes
        $name = str_replace(['/', '\\', "\0", '..'], '', $name);
        // Keep only safe characters
        $name = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $name);
        return mb_substr($name, 0, 200);
    }

    // ── Request validation ────────────────────────────────────

    /**
     * Verify request is HTTPS (for production checks).
     */
    public static function isHttps(): bool
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );
    }

    /**
     * Validate that a redirect target is on the same domain.
     * Prevents open redirect attacks.
     */
    public static function safeRedirectUrl(string $url, string $fallback = '/'): string
    {
        $parsed = parse_url($url);
        // Allow relative URLs only
        if (!empty($parsed['host'])) return $fallback;
        if (!empty($parsed['scheme'])) return $fallback;
        return $url ?: $fallback;
    }

    // ── Password policy ───────────────────────────────────────

    /**
     * Enforce password strength.
     * Returns empty string on pass, error message on fail.
     */
    public static function validatePasswordStrength(string $password): string
    {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number.';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return 'Password must contain at least one special character.';
        }
        return '';
    }

    // ── Security headers ──────────────────────────────────────

    /**
     * Set recommended security headers via PHP.
     * Call once in app.php after session_start().
     * These supplement (not replace) .htaccess headers.
     */
    public static function setHeaders(): void
    {
        if (headers_sent()) return;

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Only send HSTS on HTTPS
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}