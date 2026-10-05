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
            se.session_id,
            se.sport_id,
            sp.sport_name,
            se.session_type,
            se.session_number,
            se.title,
            se.session_date,
            se.start_time,
            se.end_time,
            se.location,
            se.description,
            se.status,
            CONCAT(\'Coach \', co.name) AS coach_name,
            COALESCE(part.attendance, \'Pending\') AS attendance
         FROM sessions se
         INNER JOIN sports sp ON sp.sport_id = se.sport_id
         INNER JOIN coaches co ON co.coach_id = se.coach_id
         INNER JOIN student_sports ss
            ON ss.student_id = :student_id
           AND ss.sport_id = se.sport_id
         LEFT JOIN session_participants part
            ON part.session_id = se.session_id
           AND part.student_id = :student_id_2
         ORDER BY se.session_date, se.start_time'
    // Executes the current PHP statement/block.
    );

    // Executes the prepared SQL statement.
    $stmt->execute([
        'student_id' => $user['user_id'],
        'student_id_2' => $user['user_id']
    // Executes the current PHP statement/block.
    ]);

    // Retrieves database results.
    $schedule = $stmt->fetchAll();

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'schedule' => $schedule
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student schedule error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load schedule.'], 500);
// Executes the current PHP statement/block.
}
