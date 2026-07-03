<?php
/**
 * Future Vision College – Authentication Class
 * Save to: includes/classes/Auth.php
 */

declare(strict_types=1);

class Auth
{
    private static Database $db;

    private static function db(): Database
    {
        if (!isset(self::$db)) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    // ── Login ─────────────────────────────────────────────────

    /**
     * Attempt admin login
     * Returns ['success' => bool, 'message' => string]
     */
    public static function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));

        // 1. Fetch admin by email
        $admin = self::db()->fetch(
            "SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1",
            [$email]
        );

        if (!$admin) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // 2. Check if account is locked
        if (!empty($admin['locked_until'])) {
            $lockedUntil = strtotime($admin['locked_until']);
            if (time() < $lockedUntil) {
                $remaining = ceil(($lockedUntil - time()) / 60);
                return [
                    'success' => false,
                    'message' => "Account locked. Try again in {$remaining} minute(s)."
                ];
            }
            // Lock expired – reset counter
            self::resetAttempts((int)$admin['id']);
            $admin['login_attempts'] = 0;
        }

        // 3. Verify password
        if (!password_verify($password, $admin['password'])) {
            self::recordFailedAttempt((int)$admin['id'], (int)$admin['login_attempts']);
            $remaining = MAX_LOGIN_ATTEMPTS - ((int)$admin['login_attempts'] + 1);
            $msg = $remaining > 0
                ? "Invalid email or password. {$remaining} attempt(s) remaining."
                : 'Account locked due to too many failed attempts.';
            return ['success' => false, 'message' => $msg];
        }

        // 4. Successful login – set session
        self::resetAttempts((int)$admin['id']);
        session_regenerate_id(true);

        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_name']     = $admin['full_name'];
        $_SESSION['admin_email']    = $admin['email'];
        $_SESSION['admin_role']     = $admin['role'];
        $_SESSION['admin_photo']    = $admin['profile_photo'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time']     = time();

        // Update last_login timestamp
        self::db()->execute(
            "UPDATE admins SET last_login = NOW() WHERE id = ?",
            [$admin['id']]
        );

        return ['success' => true, 'message' => 'Login successful.'];
    }

    // ── Logout ────────────────────────────────────────────────

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }

    // ── Guards ────────────────────────────────────────────────

    /**
     * Require admin to be logged in.
     * Call at the top of every admin page.
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . ADMIN_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        // Session timeout check
        if (isset($_SESSION['login_time'])) {
            if ((time() - $_SESSION['login_time']) > SESSION_LIFETIME) {
                self::logout();
            }
            // Refresh activity time
            $_SESSION['login_time'] = time();
        }
    }

    /**
     * Require specific role(s).
     * Usage: Auth::requireRole(['super_admin', 'editor'])
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array($_SESSION['admin_role'] ?? '', $roles, true)) {
            http_response_code(403);
            include ROOT_PATH . '/admin/templates/403.php';
            exit;
        }
    }

    /**
     * Check if currently logged in
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Get currently logged-in admin data
     */
    public static function user(): array
    {
        return [
            'id'    => $_SESSION['admin_id']    ?? null,
            'name'  => $_SESSION['admin_name']  ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
            'role'  => $_SESSION['admin_role']  ?? '',
            'photo' => $_SESSION['admin_photo'] ?? null,
        ];
    }

    /**
     * Check role without redirecting
     */
    public static function hasRole(string $role): bool
    {
        return ($_SESSION['admin_role'] ?? '') === $role;
    }

    // ── Brute force helpers ───────────────────────────────────

    private static function recordFailedAttempt(int $adminId, int $currentAttempts): void
    {
        $newAttempts = $currentAttempts + 1;

        if ($newAttempts >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + (LOCKOUT_MINUTES * 60));
            self::db()->execute(
                "UPDATE admins SET login_attempts = ?, locked_until = ? WHERE id = ?",
                [$newAttempts, $lockUntil, $adminId]
            );
        } else {
            self::db()->execute(
                "UPDATE admins SET login_attempts = ? WHERE id = ?",
                [$newAttempts, $adminId]
            );
        }
    }

    private static function resetAttempts(int $adminId): void
    {
        self::db()->execute(
            "UPDATE admins SET login_attempts = 0, locked_until = NULL WHERE id = ?",
            [$adminId]
        );
    }

    // ── Password utilities ────────────────────────────────────

    /**
     * Hash a password using bcrypt (cost 12)
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify raw password against hash
     */
    public static function checkPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if hash needs rehashing (algorithm or cost upgrade)
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
