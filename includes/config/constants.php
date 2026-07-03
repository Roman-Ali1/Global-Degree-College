<?php
/**
 * Future Vision College – Site-Wide Constants
 * Save to: includes/config/constants.php
 */

declare(strict_types=1);

// ── Base URL ─────────────────────────────────────────────────
// Change to your actual domain in production
define('BASE_URL',   'http://localhost/Global-Degree-College');
define('ADMIN_URL',  BASE_URL . '/admin');
define('DB_USER',  'root');
define('DB_PASS',  '');  // your XAMPP MySQL password if set
define('DB_NAME',  'fvc_db');

// ── Absolute server paths ─────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__, 2));          // /future-vision-college/
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH',  ROOT_PATH . '/uploads');
define('ASSETS_URL',    BASE_URL  . '/assets');

// ── Upload sub-directories ────────────────────────────────────
define('UPLOAD_ADMISSIONS', UPLOADS_PATH . '/admissions/');
define('UPLOAD_GALLERY',    UPLOADS_PATH . '/gallery/');
define('UPLOAD_FACULTY',    UPLOADS_PATH . '/faculty/');
define('UPLOAD_NEWS',       UPLOADS_PATH . '/news/');
define('UPLOAD_EVENTS',     UPLOADS_PATH . '/events/');

// ── Allowed image types & max size ───────────────────────────
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_FILE_SIZE',       5 * 1024 * 1024);  // 5 MB

// ── Pagination ────────────────────────────────────────────────
define('RECORDS_PER_PAGE', 15);
define('ADMIN_PER_PAGE',   20);

// ── Session ───────────────────────────────────────────────────
define('SESSION_LIFETIME', 7200);          // 2 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES',    15);

// ── Application version ───────────────────────────────────────
define('APP_VERSION', '1.0.0');
define('APP_NAME',    'Global Degree College');
