<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);
// Loads another PHP file.
require_once __DIR__ . '/auth.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';

// Executes the current PHP statement/block.
$user = requireLogin();
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST','PUT','PATCH','DELETE'], true)) requireCsrf();
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
    // Reads or updates request/session/server data.
    $method = $_SERVER['REQUEST_METHOD'];

    // Checks a condition before continuing.
    if ($method === 'GET') {
        // Reads or updates request/session/server data.
        $sessionId = requirePositiveInt($_GET['session_id'] ?? 0, 'session_id');
        // Executes the current PHP statement/block.
        $session = ensureSession($pdo, $sessionId);
        if ((int)$session['coach_id'] !== (int)$user['user_id']) sendJson(['success'=>false,'message'=>'Not your session.'],403);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'SELECT ss.student_id, ss.student_code, ss.name, ss.phone, ss.email,
                    COALESCE(sp.attendance, \'Pending\') AS attendance
             FROM students ss
             INNER JOIN student_sports sts
                ON sts.student_id = ss.student_id
             INNER JOIN sessions se
                ON se.session_id = :session_id
               AND se.sport_id = sts.sport_id
             LEFT JOIN session_participants sp
                ON sp.session_id = se.session_id
               AND sp.student_id = ss.student_id
             ORDER BY ss.name'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute(['session_id' => $sessionId]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            // Manages PHP session state.
            'session_id' => $sessionId,
            // Retrieves database results.
            'participants' => $stmt->fetchAll()
        // Executes the current PHP statement/block.
        ]);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method !== 'POST') {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Only GET and POST are supported.'], 405);
    // Executes the current PHP statement/block.
    }

    // Decodes incoming JSON into PHP data.
    $input = json_decode(file_get_contents('php://input'), true);
    // Checks a condition before continuing.
    if (!is_array($input)) $input = $_POST;

    // Manages PHP session state.
    $sessionId = requirePositiveInt($input['session_id'] ?? 0, 'session_id');
    // Executes the current PHP statement/block.
    $studentId = requirePositiveInt($input['student_id'] ?? 0, 'student_id');
    // Executes the current PHP statement/block.
    $session = ensureSession($pdo, $sessionId);
        if ((int)$session['coach_id'] !== (int)$user['user_id']) sendJson(['success'=>false,'message'=>'Not your session.'],403);
    // Executes the current PHP statement/block.
    $attendance = (string)($input['attendance'] ?? '');

    // Checks a condition before continuing.
    if (!in_array($attendance, ['Present', 'Absent', 'Pending'], true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'attendance must be Present, Absent, or Pending.'], 422);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    ensureStudentSport($pdo, $studentId, (int)$session['sport_id']);

    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        // Manages PHP session state.
        'INSERT INTO session_participants (session_id, student_id, attendance)
         VALUES (:session_id, :student_id, :attendance)
         ON DUPLICATE KEY UPDATE attendance = VALUES(attendance)'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute([
        // Manages PHP session state.
        'session_id' => $sessionId,
        'student_id' => $studentId,
        'attendance' => $attendance
    // Executes the current PHP statement/block.
    ]);

    // Sends a JSON response to the browser.
    sendJson(['success' => true, 'message' => 'Attendance saved successfully.']);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach attendance error', $e, 'Unable to save attendance.');
// Executes the current PHP statement/block.
}
