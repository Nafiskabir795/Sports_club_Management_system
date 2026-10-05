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
            se.selection_id,
            se.student_id,
            se.sport_id,
            sp.sport_name,
            se.testing_session_id,
            ts.title AS testing_session_title,
            ts.session_date AS testing_date,
            se.overall_score,
            se.selection_status,
            se.coach_notes,
            se.selected_at
         FROM selections se
         INNER JOIN sports sp ON sp.sport_id = se.sport_id
         LEFT JOIN sessions ts ON ts.session_id = se.testing_session_id
         WHERE se.student_id = :student_id
         ORDER BY sp.sport_id'
    // Executes the current PHP statement/block.
    );

    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $user['user_id']]);

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        // Retrieves database results.
        'selections' => $stmt->fetchAll()
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student selection error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load selection status.'], 500);
// Executes the current PHP statement/block.
}
