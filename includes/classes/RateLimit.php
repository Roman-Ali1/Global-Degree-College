<?php
/**
 * Global Degree College – Rate Limiter
 * Save to: includes/classes/RateLimit.php
 *
 * DB-backed rate limiting. No Redis required.
 * Works by counting recent actions per IP in any existing table,
 * or via a dedicated rate_limits table for login attempts.
 */

declare(strict_types=1);

class RateLimit
{
    /**
     * Check if an IP has exceeded a limit within a time window.
     *
     * @param string $action    Unique action key e.g. 'login', 'contact_form'
     * @param string $ip        IP address
     * @param int    $maxHits   Max allowed attempts
     * @param int    $windowSec Time window in seconds
     */
    public static function check(
        string $action,
        string $ip,
        int    $maxHits   = 5,
        int    $windowSec = 600
    ): bool {
        $db    = Database::getInstance();
        $table = 'rate_limits';

        // Ensure table exists (created in schema update below)
        $count = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM $table
             WHERE action = ? AND ip_address = ?
             AND created_at > NOW() - INTERVAL ? SECOND",
            [$action, $ip, $windowSec]
        );

        return $count < $maxHits;
    }

    /**
     * Record a hit for this action + IP.
     */
    public static function hit(string $action, string $ip): void
    {
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO rate_limits (action, ip_address) VALUES (?,?)",
            [$action, $ip]
        );
    }

    /**
     * Clear old rate limit records (run periodically or on cron).
     * Keeps the table small — deletes anything older than 1 hour.
     */
    public static function cleanup(): void
    {
        $db = Database::getInstance();
        $db->execute(
            "DELETE FROM rate_limits WHERE created_at < NOW() - INTERVAL 1 HOUR"
        );
    }

    /**
     * One-call convenience: check + hit + return bool.
     * Returns TRUE if allowed, FALSE if blocked.
     */
    public static function allow(
        string $action,
        string $ip,
        int    $maxHits   = 5,
        int    $windowSec = 600
    ): bool {
        if (!self::check($action, $ip, $maxHits, $windowSec)) {
            return false;
        }
        self::hit($action, $ip);
        return true;
    }
}