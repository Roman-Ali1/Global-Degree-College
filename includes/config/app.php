<?php
/**
 * Future Vision College – Application Bootstrapper
 * Save to: includes/config/app.php
 * Loaded first on EVERY page via require_once
 */

declare(strict_types=1);

// ── Error reporting (set to 0 in production) ────────────────
define('APP_ENV', 'development'); // 'development' | 'production'

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── Load core files in order ─────────────────────────────────
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../helpers/sanitize.php';
require_once __DIR__ . '/../classes/CSRF.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Uploader.php';

// ── Session configuration (before session_start) ─────────────
ini_set('session.cookie_httponly', '1');  // Block JS access to cookie
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

$sessionPath = ROOT_PATH . '/tmp/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', '1'); // HTTPS only in production
}

// ── Start session if not already started ─────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_name('FVC_SESSION');
    session_start();

    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['_initiated'])) {
        session_regenerate_id(false);
        $_SESSION['_initiated'] = true;
    }
}

// ── Load site settings from DB into global $settings array ───
if (!isset($GLOBALS['site_settings'])) {
    try {
        $db  = Database::getInstance();
        $rows = $db->fetchAll("SELECT `key`, `value` FROM settings");
        $GLOBALS['site_settings'] = [];
        foreach ($rows as $row) {
            $GLOBALS['site_settings'][$row['key']] = $row['value'];
        }
    } catch (Exception $e) {
        $GLOBALS['site_settings'] = [];
    }
}

/**
 * Quick helper to read a site setting
 * Usage: setting('site_name', 'Default College')
 */
function setting(string $key, string $default = ''): string
{
    return (string)($GLOBALS['site_settings'][$key] ?? $default);
}
