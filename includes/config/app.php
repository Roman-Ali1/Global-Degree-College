<?php
/**
 * Global Degree College – Application Bootstrapper
 * Save to: includes/config/app.php
 */

declare(strict_types=1);

if (!defined('APP_ENV')) {
    $env = getenv('APP_ENV');

    if ($env) {
        define('APP_ENV', $env);
    } elseif (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'hostingersite.com') !== false) {
        // Temporarily enable development mode on Hostinger to expose the real error.
        define('APP_ENV', 'development');
    } else {
        define('APP_ENV', 'production');
    }
}

// ── Error reporting ───────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}

// ── Load core files ───────────────────────────────────────────
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/sanitize.php';
require_once __DIR__ . '/../classes/CSRF.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Uploader.php';
require_once __DIR__ . '/../classes/RateLimit.php';
require_once __DIR__ . '/../classes/Security.php';

// ── Session hardening ─────────────────────────────────────────
ini_set('session.cookie_httponly',  '1');
ini_set('session.cookie_samesite',  'Strict');
ini_set('session.use_strict_mode',  '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime',   (string) SESSION_LIFETIME);

// Only set secure cookie flag over HTTPS
if (APP_ENV === 'production' && Security::isHttps()) {
    ini_set('session.cookie_secure', '1');
}

if (session_status() === PHP_SESSION_NONE) {
    session_name('GDC_SESSION');
    session_start();

    // Regenerate session ID on first load to prevent fixation
    if (!isset($_SESSION['_initiated'])) {
        session_regenerate_id(true);
        $_SESSION['_initiated'] = true;
        $_SESSION['_ip']        = getClientIP();
        $_SESSION['_ua']        = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    // Session hijack detection — IP + UA binding
    if (
        isset($_SESSION['admin_logged_in']) &&
        (
            ($_SESSION['_ip'] ?? '') !== getClientIP() ||
            ($_SESSION['_ua'] ?? '') !== ($_SERVER['HTTP_USER_AGENT'] ?? '')
        )
    ) {
        session_destroy();
        header('Location: ' . (defined('ADMIN_URL') ? ADMIN_URL : '/') . '/login.php');
        exit;
    }
}

// ── Security headers (PHP-level backup) ──────────────────────
Security::setHeaders();

// ── Load site settings ────────────────────────────────────────
if (!isset($GLOBALS['site_settings'])) {
    try {
        $db   = Database::getInstance();
        $rows = $db->fetchAll("SELECT `key`, `value` FROM settings");
        $GLOBALS['site_settings'] = [];
        foreach ($rows as $row) {
            $GLOBALS['site_settings'][$row['key']] = $row['value'];
        }
    } catch (Exception $e) {
        $GLOBALS['site_settings'] = [];
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return (string)($GLOBALS['site_settings'][$key] ?? $default);
    }
}