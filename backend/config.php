<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

/* Sports Club Management Website - MySQL connection for XAMPP */
const DB_HOST = 'localhost';
// Executes the current PHP statement/block.
const DB_NAME = 'sports_club_db';
// Executes the current PHP statement/block.
const DB_USER = 'root';
// Executes the current PHP statement/block.
const DB_PASS = '';
// Executes the current PHP statement/block.
const DB_CHARSET = 'utf8mb4';

// Keep true while developing locally. Set to false before public deployment.
const APP_DEBUG = true;

// Defines a reusable PHP function.
function getPDO(): PDO
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    static $pdo = null;
    // Checks a condition before continuing.
    if ($pdo instanceof PDO) return $pdo;

    // Executes the current PHP statement/block.
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    // Executes the current PHP statement/block.
    ];

    // Starts exception-handling logic.
    try {
        // Executes the current PHP statement/block.
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // Executes the current PHP statement/block.
    } catch (PDOException $e) {
        // Executes the current PHP statement/block.
        error_log('Database connection error: ' . $e->getMessage());
        // Executes the current PHP statement/block.
        $detail = APP_DEBUG ? ' Database details: ' . $e->getMessage() : '';
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Unable to connect to the database.' . $detail], 500);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $pdo;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function sendJson(array $data, int $statusCode = 200): never
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    http_response_code($statusCode);
    // Sends an HTTP header.
    header('Content-Type: application/json; charset=utf-8');
    // Encodes PHP data as JSON.
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    // Executes the current PHP statement/block.
    exit;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function startSecureSession(): void
// Executes the current PHP statement/block.
{
    // Checks a condition before continuing.
    if (session_status() === PHP_SESSION_ACTIVE) return;
    // Manages PHP session state.
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        // Reads or updates request/session/server data.
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    // Executes the current PHP statement/block.
    ]);
    // Manages PHP session state.
    session_start();
// Executes the current PHP statement/block.
}
