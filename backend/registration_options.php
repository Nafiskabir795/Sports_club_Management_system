<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/config.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';

// Checks a condition before continuing.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Only GET requests are allowed.'], 405);
// Executes the current PHP statement/block.
}

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();

    // Sends an SQL query to MySQL.
    $sports = $pdo->query(
        'SELECT sport_id, sport_name FROM sports ORDER BY sport_id'
    // Retrieves database results.
    )->fetchAll();

    // Sends an SQL query to MySQL.
    $positions = $pdo->query(
        'SELECT position_id, sport_id, position_name, description
         FROM positions
         ORDER BY sport_id, position_id'
    // Retrieves database results.
    )->fetchAll();

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'sports' => $sports,
        'positions' => $positions
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Registration options error', $e, 'Unable to load registration options.');
// Executes the current PHP statement/block.
}
