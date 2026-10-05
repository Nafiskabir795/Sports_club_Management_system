<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);
// Loads another PHP file.
require_once __DIR__ . '/auth.php';

// Executes the current PHP statement/block.
$user = requireLogin();
// Checks a condition before continuing.
if ($user['role'] !== 'coach') {
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Coach access only.'], 403);
// Executes the current PHP statement/block.
}

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();

    // Sends an SQL query to MySQL.
    $stmt = $pdo->query(
        'SELECT sport_id, sport_name FROM sports ORDER BY sport_id'
    // Executes the current PHP statement/block.
    );
    // Retrieves database results.
    $sports = $stmt->fetchAll();

    // Sends an SQL query to MySQL.
    $stmt = $pdo->query(
        'SELECT p.position_id, p.sport_id, s.sport_name,
                p.position_name, p.description
         FROM positions p
         INNER JOIN sports s ON s.sport_id = p.sport_id
         ORDER BY p.sport_id, p.position_id'
    // Executes the current PHP statement/block.
    );
    // Retrieves database results.
    $positions = $stmt->fetchAll();

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
    error_log('Coach sports error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load sports/positions.'], 500);
// Executes the current PHP statement/block.
}
