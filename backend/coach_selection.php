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
        // Sends an SQL query to MySQL.
        $stmt = $pdo->query(
            'SELECT se.selection_id, se.student_id, st.student_code, st.name,
                    se.sport_id, sp.sport_name, se.testing_session_id,
                    ts.title AS testing_session_title, se.overall_score,
                    se.selection_status, se.coach_notes, se.selected_at
             FROM selections se
             INNER JOIN students st ON st.student_id = se.student_id
             INNER JOIN sports sp ON sp.sport_id = se.sport_id
             LEFT JOIN sessions ts ON ts.session_id = se.testing_session_id
             ORDER BY sp.sport_id, se.overall_score DESC, st.name'
        // Executes the current PHP statement/block.
        );

        // Retrieves database results.
        sendJson(['success' => true, 'selections' => $stmt->fetchAll()]);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method !== 'POST' && $method !== 'PUT') {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Only GET, POST and PUT are supported.'], 405);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $input = readJsonInput();

    // Executes the current PHP statement/block.
    $studentId = requirePositiveInt($input['student_id'] ?? 0, 'student_id');
    // Executes the current PHP statement/block.
    $sportId = requirePositiveInt($input['sport_id'] ?? 0, 'sport_id');
    // Manages PHP session state.
    $testingSessionId = requirePositiveInt($input['testing_session_id'] ?? 0, 'testing_session_id');
    // Executes the current PHP statement/block.
    $score = requireScore($input['overall_score'] ?? null);
    // Executes the current PHP statement/block.
    $notes = trim((string)($input['coach_notes'] ?? ''));
    // Checks a condition before continuing.
    if (mb_strlen($notes) > 5000) sendJson(['success' => false, 'message' => 'coach_notes is too long.'], 422);

    // Executes the current PHP statement/block.
    ensureSport($pdo, $sportId);
    // Executes the current PHP statement/block.
    ensureStudentSport($pdo, $studentId, $sportId);
    // Executes the current PHP statement/block.
    ensureSession($pdo, $testingSessionId, $sportId, 'Testing');

    /*
     * Project rule:
     * score > 55 = Selected
     * score <= 55 = Not Selected
     */
    // Executes the current PHP statement/block.
    $status = $score > 55 ? 'Selected' : 'Not Selected';

    // Checks a condition before continuing.
    if ($method === 'POST') {
        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO selections
             (student_id, sport_id, testing_session_id, overall_score,
              selection_status, coach_notes, selected_at)
             VALUES (:student_id, :sport_id, :testing_session_id, :overall_score,
                     :selection_status, :coach_notes,
                     CASE WHEN :selection_status_2 = \'Selected\' THEN NOW() ELSE NULL END)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'student_id' => $studentId,
            'sport_id' => $sportId,
            // Manages PHP session state.
            'testing_session_id' => $testingSessionId,
            'overall_score' => $score,
            'selection_status' => $status,
            'selection_status_2' => $status,
            'coach_notes' => $notes
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Selection result saved.',
            'selection_status' => $status,
            'selection_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $selectionId = (int)($input['selection_id'] ?? 0);
    // Checks a condition before continuing.
    if ($selectionId <= 0) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'selection_id is required for updates.'], 422);
    // Executes the current PHP statement/block.
    }

    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'UPDATE selections
         SET overall_score = :overall_score,
             selection_status = :selection_status,
             testing_session_id = :testing_session_id,
             coach_notes = :coach_notes,
             selected_at = CASE
                 WHEN :selection_status_2 = \'Selected\' THEN COALESCE(selected_at, NOW())
                 ELSE NULL
             END
         WHERE selection_id = :selection_id'
    // Executes the current PHP statement/block.
    );

    // Executes the prepared SQL statement.
    $stmt->execute([
        'overall_score' => $score,
        'selection_status' => $status,
        'selection_status_2' => $status,
        // Manages PHP session state.
        'testing_session_id' => $testingSessionId,
        'coach_notes' => $notes,
        'selection_id' => $selectionId
    // Executes the current PHP statement/block.
    ]);

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'message' => 'Selection result updated.',
        'selection_status' => $status
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'A selection record already exists for this student and sport. Use PUT to update it.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach selection DB error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Selection database operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach selection error', $e, 'Unable to save selection.');
// Executes the current PHP statement/block.
}
