<?php
/**
 * Future Vision College – Global Utility Functions
 * Save to: includes/helpers/functions.php
 */

declare(strict_types=1);

// ── String helpers ────────────────────────────────────────────

/**
 * Convert a string to a URL slug
 * e.g. "FSc Pre-Medical" → "fsc-pre-medical"
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Truncate text to N characters with ellipsis
 */
function truncate(string $text, int $length = 150, string $suffix = '…'): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Human-readable time difference
 * e.g. "2 days ago", "just now"
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);

    if ($diff < 60)         return 'just now';
    if ($diff < 3600)       return floor($diff / 60)   . ' minute(s) ago';
    if ($diff < 86400)      return floor($diff / 3600)  . ' hour(s) ago';
    if ($diff < 604800)     return floor($diff / 86400) . ' day(s) ago';
    return date('d M Y', strtotime($datetime));
}

/**
 * Format a date string
 * e.g. formatDate('2024-01-15') → "15 January 2024"
 */
function formatDate(string $date, string $format = 'd F Y'): string
{
    if (empty($date) || $date === '0000-00-00') return 'N/A';
    return date($format, strtotime($date));
}

/**
 * Format currency in PKR
 */
function formatCurrency(float $amount): string
{
    return 'PKR ' . number_format($amount, 0);
}

// ── Flash messages ────────────────────────────────────────────

/**
 * Set a flash message (stored in session, shown once)
 * Types: success | error | warning | info
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 * Returns ['type' => string, 'message' => string] or null
 */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── URL & redirect helpers ────────────────────────────────────

/**
 * Redirect to a URL and exit
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect back to referrer or fallback
 */
function redirectBack(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
    redirect($ref);
}

/**
 * Build a full public URL
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Build a full admin URL
 */
function adminUrl(string $path = ''): string
{
    return ADMIN_URL . '/' . ltrim($path, '/');
}

/**
 * Get URL for an uploaded file
 */
function uploadUrl(string $subdir, string $filename): string
{
    if (empty($filename)) return url('assets/images/defaults/placeholder.png');
    return BASE_URL . '/uploads/' . trim($subdir, '/') . '/' . $filename;
}

// ── Pagination ────────────────────────────────────────────────

/**
 * Calculate pagination data
 *
 * @return array{total: int, pages: int, current: int, offset: int, per_page: int}
 */
function paginate(int $total, int $perPage = RECORDS_PER_PAGE): array
{
    $perPage = max(1, $perPage);
    $pages   = (int) ceil($total / $perPage);
    $current = max(1, min((int)($_GET['page'] ?? 1), $pages ?: 1));
    $offset  = ($current - 1) * $perPage;

    return compact('total', 'pages', 'current', 'offset', 'perPage');
}

// ── Application number generator ──────────────────────────────

/**
 * Generate a unique admission application number
 * e.g. APP-2024-00042
 */
function generateAppNumber(): string
{
    $db   = Database::getInstance();
    $year = date('Y');
    $last = $db->fetchColumn(
        "SELECT COUNT(*) FROM admission_applications WHERE YEAR(created_at) = ?",
        [$year]
    );
    return sprintf('APP-%s-%05d', $year, (int)$last + 1);
}

/**
 * Generate a student code
 * e.g. FVC-2024-0001
 */
function generateStudentCode(): string
{
    $db   = Database::getInstance();
    $year = date('Y');
    $last = $db->fetchColumn(
        "SELECT COUNT(*) FROM students WHERE enrollment_year = ?",
        [$year]
    );
    return sprintf('FVC-%s-%04d', $year, (int)$last + 1);
}

// ── Security helpers ──────────────────────────────────────────

/**
 * Get real client IP address
 */
function getClientIP(): string
{
    $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Check if request is POST
 */
function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Active nav link helper
 * Returns 'active' if current page matches
 */
function isActivePage(string $page): string
{
    $current = basename($_SERVER['PHP_SELF'], '.php');
    return ($current === $page) ? 'active' : '';
}
