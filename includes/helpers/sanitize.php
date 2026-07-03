<?php
/**
 * Future Vision College - Input Sanitization
 * Save to: includes/helpers/sanitize.php
 *
 * Rule: NEVER trust user input.
 * Sanitize on INPUT, escape on OUTPUT with h() before echoing.
 */

declare(strict_types=1);

/**
 * Escape HTML output. Use on every variable echoed in HTML.
 */
function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Sanitize a plain text string.
 */
function cleanString(mixed $value): string
{
    return trim(strip_tags((string)$value));
}

/**
 * Sanitize and validate email.
 */
function cleanEmail(mixed $value): string
{
    $email = strtolower(trim((string)$value));
    $clean = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($clean, FILTER_VALIDATE_EMAIL) ? $clean : '';
}

/**
 * Sanitize a phone number.
 */
function cleanPhone(mixed $value): string
{
    return preg_replace('/[^\d\+\-\s]/', '', (string)$value);
}

/**
 * Sanitize to positive integer.
 */
function cleanInt(mixed $value): int
{
    return abs((int)filter_var($value, FILTER_SANITIZE_NUMBER_INT));
}

/**
 * Sanitize to float.
 */
function cleanFloat(mixed $value): float
{
    return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

/**
 * Sanitize textarea / multi-line input.
 */
function cleanTextarea(mixed $value): string
{
    return strip_tags(trim((string)$value));
}

/**
 * Sanitize a date string. Returns Y-m-d or empty.
 */
function cleanDate(mixed $value): string
{
    $value = trim((string)$value);
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
}

/**
 * Sanitize a URL.
 */
function cleanUrl(mixed $value): string
{
    $url = filter_var(trim((string)$value), FILTER_SANITIZE_URL);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

/**
 * Get and sanitize a POST field.
 */
function post(string $key, string $default = ''): string
{
    return cleanString($_POST[$key] ?? $default);
}

/**
 * Get and sanitize a GET parameter.
 */
function get(string $key, string $default = ''): string
{
    return cleanString($_GET[$key] ?? $default);
}

/**
 * Get and sanitize POST integer.
 */
function postInt(string $key, int $default = 0): int
{
    return cleanInt($_POST[$key] ?? $default);
}

/**
 * Get and sanitize GET integer.
 */
function getInt(string $key, int $default = 0): int
{
    return cleanInt($_GET[$key] ?? $default);
}

/**
 * Validate CNIC / B-Form format.
 */
function validateCNIC(string $value): bool
{
    return (bool)preg_match('/^\d{5}-\d{7}-\d$/', trim($value));
}

/**
 * Validate Pakistani mobile number.
 */
function validateMobile(string $value): bool
{
    $cleaned = preg_replace('/[\s\-]/', '', $value);
    return (bool)preg_match('/^(\+92|92|0)3[0-9]{9}$/', $cleaned);
}
