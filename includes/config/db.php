<?php
/**
 * Future Vision College – Database Configuration & PDO Wrapper
 * Save to: includes/config/db.php
 *
 * Pattern: Singleton – one PDO connection per request
 */

declare(strict_types=1);

// ── Database credentials ──────────────────────────────────────
// Use host-aware credentials so local and Hostinger environments both work.
if (!defined('DB_HOST')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    if (str_contains($host, 'hostingersite.com')) {
        define('DB_HOST',    'localhost');
        define('DB_NAME',    'u557021395_db_f7xkV3lg'); // cPanel format
        define('DB_USER',    'u557021395_usr_f7xkV3lg');
        define('DB_PASS',    '&5#3NRXl');
    } else {
        define('DB_HOST',    'localhost');
        define('DB_NAME',    'fvc_db');
        define('DB_USER',    'root');
        define('DB_PASS',    '');
    }
}

if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Database – PDO Singleton Wrapper
 *
 * Usage:
 *   $db = Database::getInstance();
 *   $rows = $db->fetchAll("SELECT * FROM courses WHERE is_active = ?", [1]);
 *   $row  = $db->fetch("SELECT * FROM admins WHERE id = ?", [$id]);
 *   $id   = $db->insert("INSERT INTO news (title) VALUES (?)", ['Hello']);
 *   $rows = $db->execute("UPDATE news SET status = ? WHERE id = ?", ['published', 5]);
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // Real prepared statements
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose credentials in error messages
            $this->handleError('Database connection failed.', $e);
        }
    }

    /** Get singleton instance */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Prevent cloning */
    private function __clone() {}

    // ── Query helpers ─────────────────────────────────────────

    /**
     * Fetch multiple rows
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->run($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single row
     * @return array<string, mixed>|false
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        $stmt = $this->run($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch single column value
     */
    public function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = $this->run($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Execute INSERT – returns last inserted ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->run($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Execute UPDATE / DELETE – returns affected row count
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->run($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Count helper
     */
    public function count(string $sql, array $params = []): int
    {
        return (int) $this->fetchColumn($sql, $params);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    // ── Internal ──────────────────────────────────────────────

    private function run(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->handleError('Query failed: ' . $sql, $e);
        }
    }

    private function handleError(string $message, PDOException $e): never
    {
        if (APP_ENV === 'development') {
            throw new RuntimeException($message . ' – ' . $e->getMessage(), 0, $e);
        }
        // In production: log quietly, show generic error
        error_log('[FVC DB Error] ' . $e->getMessage());
        throw new RuntimeException('A database error occurred. Please try again later.');
    }
}
