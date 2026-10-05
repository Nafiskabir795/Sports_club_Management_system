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
            s.sport_id,
            s.sport_name,
            p.position_id,
            p.position_name,
            c.handedness,
            c.serve_type,
            c.previous_role,
            c.batting_style,
            c.bowling_style
         FROM student_sports ss
         INNER JOIN sports s ON s.sport_id = ss.sport_id
         LEFT JOIN student_sport_choices c
            ON c.student_id = ss.student_id
           AND c.sport_id = ss.sport_id
         LEFT JOIN positions p ON p.position_id = c.position_id
         WHERE ss.student_id = :student_id
         ORDER BY s.sport_id'
    // Executes the current PHP statement/block.
    );

    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $user['user_id']]);
    // Retrieves database results.
    $sports = $stmt->fetchAll();

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'sports' => $sports
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student sports error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load sports.'], 500);
// Executes the current PHP statement/block.
}
