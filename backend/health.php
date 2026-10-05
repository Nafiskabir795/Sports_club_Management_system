<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/config.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();
    // Sends an SQL query to MySQL.
    $version = (string)$pdo->query('SELECT VERSION()')->fetchColumn();
    // Executes the current PHP statement/block.
    $tables = [];
    // Manages PHP session state.
    foreach (['sports','students','coaches','sessions','session_participants','football_performance','volleyball_performance','cricket_performance','selections','teams','team_members'] as $table) {
        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name');
        // Executes the prepared SQL statement.
        $stmt->execute(['table_name' => $table]);
        // Retrieves database results.
        $tables[$table] = (bool)$stmt->fetchColumn();
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'message' => 'Sports Club backend is healthy.',
        'php_version' => PHP_VERSION,
        'database' => DB_NAME,
        'database_server' => $version,
        'all_required_tables_present' => !in_array(false, $tables, true),
        'tables' => $tables,
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Health check error', $e, 'Backend health check failed.');
// Executes the current PHP statement/block.
}
