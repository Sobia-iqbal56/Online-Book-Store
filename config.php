<?php
// ============================================================
// config.php — Database Configuration
// Online Book Store | WELAB 2026
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'obs_db');
define('DB_USER', 'root');          // Change for production
define('DB_PASS', '');              // Change for production
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'BookHaven');
define('ADMIN_SESSION_TIMEOUT', 1800); // 30 minutes in seconds

/**
 * Returns a PDO connection instance (singleton pattern).
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose real error to users
            die('<p style="color:red;font-family:sans-serif;padding:2rem;">
                 Database connection failed. Please try again later.</p>');
        }
    }
    return $pdo;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin session timeout check
if (isset($_SESSION['admin_id'])) {
    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity']) > ADMIN_SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: /obs/admin/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Sanitize output to prevent XSS.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Check if admin is logged in; redirect if not.
 */
function requireAdmin(): void {
    if (!isset($_SESSION['admin_id'])) {
        redirect('/obs/admin/login.php');
    }
}
