<?php
declare(strict_types=1);

// ── Base URL ─────────────────────────────────────────────────
if (!defined('BASE_URL')) {
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        $scheme = 'https';
    } elseif (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') {
        $scheme = 'https';
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    if (strpos($host, 'hostingersite.com') !== false) {
        define('BASE_URL', $scheme . '://' . rtrim($host, '/'));
    } else {
        define('BASE_URL', $scheme . '://' . rtrim($host, '/') . '/Global-Degree-College');
    }
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}

// ── Absolute server paths ─────────────────────────────────────
// On cPanel: /home/yourusername/public_html
// Detect automatically — works on both local and live
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', ROOT_PATH . '/includes');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT_PATH . '/uploads');
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '/assets');
}

// ── Upload sub-directories ────────────────────────────────────
if (!defined('UPLOAD_ADMISSIONS')) define('UPLOAD_ADMISSIONS', UPLOADS_PATH . '/admissions/');
if (!defined('UPLOAD_GALLERY'))    define('UPLOAD_GALLERY',    UPLOADS_PATH . '/gallery/');
if (!defined('UPLOAD_FACULTY'))    define('UPLOAD_FACULTY',    UPLOADS_PATH . '/faculty/');
if (!defined('UPLOAD_NEWS'))       define('UPLOAD_NEWS',       UPLOADS_PATH . '/news/');
if (!defined('UPLOAD_EVENTS'))     define('UPLOAD_EVENTS',     UPLOADS_PATH . '/events/');

// ── File limits ───────────────────────────────────────────────
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024);
}

if (!defined('RECORDS_PER_PAGE')) define('RECORDS_PER_PAGE', 15);
if (!defined('ADMIN_PER_PAGE'))   define('ADMIN_PER_PAGE',   20);
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME',  7200);
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('LOCKOUT_MINUTES'))    define('LOCKOUT_MINUTES',    15);
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('APP_NAME'))    define('APP_NAME',    'Global Degree College');