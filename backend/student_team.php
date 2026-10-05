<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/auth.php';

// Executes the current PHP statement/block.
$user = requireLogin();

// Checks a condition before continuing.
if ($user['role'] !== 'student') {
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Student access only.'], 403);
// Executes the current PHP statement/block.
}

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();

    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'SELECT
            t.team_id,
            t.sport_id,
            s.sport_name,
            t.team_name,
            t.formation,
            tm.team_member_id,
            tm.lineup_status,
            tm.jersey_number,
            tm.formation_slot,
            p.position_name,
            p.description AS position_description,
            c.name AS coach_name
         FROM team_members tm
         INNER JOIN teams t ON t.team_id = tm.team_id
         INNER JOIN sports s ON s.sport_id = t.sport_id
         LEFT JOIN positions p ON p.position_id = tm.position_id
         INNER JOIN coaches c ON c.coach_id = t.coach_id
         WHERE tm.student_id = :student_id
         ORDER BY t.sport_id'
    // Executes the current PHP statement/block.
    );

    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $user['user_id']]);

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        // Retrieves database results.
        'teams' => $stmt->fetchAll()
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student team error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load team information.'], 500);
// Executes the current PHP statement/block.
}
