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

// Reads or updates request/session/server data.
$method = $_SERVER['REQUEST_METHOD'];

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();

    // Checks a condition before continuing.
    if ($method === 'GET') {
        // Sends an SQL query to MySQL.
        $stmt = $pdo->query(
            // Manages PHP session state.
            'SELECT se.session_id, se.sport_id, sp.sport_name, se.coach_id,
                    c.name AS coach_name, se.session_type, se.session_number,
                    se.title, se.session_date, se.start_time, se.end_time,
                    se.location, se.description, se.status
             FROM sessions se
             INNER JOIN sports sp ON sp.sport_id = se.sport_id
             INNER JOIN coaches c ON c.coach_id = se.coach_id
             ORDER BY se.session_date DESC, se.start_time DESC'
        // Executes the current PHP statement/block.
        );
        // Retrieves database results.
        sendJson(['success' => true, 'sessions' => $stmt->fetchAll()]);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $input = readJsonInput();

    // Checks a condition before continuing.
    if ($method === 'POST') {
        // Manages PHP session state.
        foreach (['sport_id', 'session_type', 'session_number', 'title', 'session_date', 'start_time', 'end_time'] as $field) {
            // Checks a condition before continuing.
            if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
                // Sends a JSON response to the browser.
                sendJson(['success' => false, 'message' => "Missing field: $field"], 422);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $sportId = requirePositiveInt($input['sport_id'], 'sport_id');
        // Executes the current PHP statement/block.
        ensureSport($pdo, $sportId);
        // Manages PHP session state.
        $type = (string)$input['session_type'];
        // Checks a condition before continuing.
        if (!in_array($type, ['Practice', 'Testing'], true)) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'session_type must be Practice or Testing.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $startTime = requireTimeValue($input['start_time'], 'start_time');
        // Executes the current PHP statement/block.
        $endTime = requireTimeValue($input['end_time'], 'end_time');
        // Executes the current PHP statement/block.
        ensureTimeOrder($startTime, $endTime);
        // Manages PHP session state.
        $sessionNumber = (int)$input['session_number'];
        // Checks a condition before continuing.
        if ($sessionNumber < 1 || $sessionNumber > 255) sendJson(['success' => false, 'message' => 'session_number must be between 1 and 255.'], 422);
        // Executes the current PHP statement/block.
        $status = (string)($input['status'] ?? 'Scheduled');
        // Checks a condition before continuing.
        if (!in_array($status, ['Scheduled','Completed','Cancelled'], true)) sendJson(['success' => false, 'message' => 'Invalid session status.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO sessions
             (sport_id, coach_id, session_type, session_number, title, session_date,
              start_time, end_time, location, description, status)
             VALUES (:sport_id, :coach_id, :session_type, :session_number, :title,
                     :session_date, :start_time, :end_time, :location, :description, :status)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'sport_id' => (int)$input['sport_id'],
            'coach_id' => (int)($input['coach_id'] ?? $user['user_id']),
            // Manages PHP session state.
            'session_type' => $type,
            // Manages PHP session state.
            'session_number' => $sessionNumber,
            'title' => requireNonEmptyString($input['title'], 'title', 150),
            // Manages PHP session state.
            'session_date' => requireDateValue($input['session_date']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => trim((string)($input['location'] ?? '')),
            'description' => trim((string)($input['description'] ?? '')),
            'status' => $status
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Session created successfully.',
            // Manages PHP session state.
            'session_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'PUT') {
        // Checks a condition before continuing.
        if (empty($input['session_id'])) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'session_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Manages PHP session state.
        $sessionId = requirePositiveInt($input['session_id'], 'session_id');
        // Executes the current PHP statement/block.
        $existing = ensureSession($pdo, $sessionId);
        // Executes the current PHP statement/block.
        $sportId = array_key_exists('sport_id', $input) ? requirePositiveInt($input['sport_id'], 'sport_id') : (int)$existing['sport_id'];
        // Executes the current PHP statement/block.
        ensureSport($pdo, $sportId);
        // Manages PHP session state.
        $type = (string)($input['session_type'] ?? $existing['session_type']);
        // Checks a condition before continuing.
        if (!in_array($type, ['Practice','Testing'], true)) sendJson(['success' => false, 'message' => 'session_type must be Practice or Testing.'], 422);
        // Executes the current PHP statement/block.
        $startTime = array_key_exists('start_time', $input) ? requireTimeValue($input['start_time'], 'start_time') : $existing['start_time'];
        // Executes the current PHP statement/block.
        $endTime = array_key_exists('end_time', $input) ? requireTimeValue($input['end_time'], 'end_time') : $existing['end_time'];
        // Executes the current PHP statement/block.
        ensureTimeOrder($startTime, $endTime);
        // Executes the current PHP statement/block.
        $status = (string)($input['status'] ?? 'Scheduled');
        // Checks a condition before continuing.
        if (!in_array($status, ['Scheduled','Completed','Cancelled'], true)) sendJson(['success' => false, 'message' => 'Invalid session status.'], 422);

        // Manages PHP session state.
        $sets = ['sport_id = :sport_id', 'session_type = :session_type', 'start_time = :start_time', 'end_time = :end_time', 'status = :status'];
        // Manages PHP session state.
        $params = ['session_id' => $sessionId, 'sport_id' => $sportId, 'session_type' => $type, 'start_time' => $startTime, 'end_time' => $endTime, 'status' => $status];
        // Manages PHP session state.
        foreach (['session_number','title','session_date','location','description'] as $field) {
            // Checks a condition before continuing.
            if (array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $sets[] = "$field = :$field";
                // Checks a condition before continuing.
                if ($field === 'session_number') $params[$field] = (int)$input[$field];
                // Executes the current PHP statement/block.
                elseif ($field === 'title') $params[$field] = requireNonEmptyString($input[$field], 'title', 150);
                // Manages PHP session state.
                elseif ($field === 'session_date') $params[$field] = requireDateValue($input[$field]);
                // Checks a condition before continuing.
                else $params[$field] = trim((string)$input[$field]);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }
        // Checks a condition before continuing.
        if (array_key_exists('coach_id', $input)) {
            // Executes the current PHP statement/block.
            $coachId = requirePositiveInt($input['coach_id'], 'coach_id');
            // Executes the current PHP statement/block.
            ensureCoachExists($pdo, $coachId);
            // Executes the current PHP statement/block.
            $sets[] = 'coach_id = :coach_id';
            // Executes the current PHP statement/block.
            $params['coach_id'] = $coachId;
        // Executes the current PHP statement/block.
        }

        // Checks a condition before continuing.
        if (!$sets) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'No fields to update.'], 422);
        // Executes the current PHP statement/block.
        }

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            // Manages PHP session state.
            'UPDATE sessions SET ' . implode(', ', $sets) . ' WHERE session_id = :session_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Session updated successfully.']);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'DELETE') {
        // Reads or updates request/session/server data.
        $sessionId = (int)($_GET['session_id'] ?? 0);
        // Checks a condition before continuing.
        if ($sessionId <= 0) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'session_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare('DELETE FROM sessions WHERE session_id = :session_id');
        // Executes the prepared SQL statement.
        $stmt->execute(['session_id' => $sessionId]);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Session deleted successfully.']);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unsupported request method.'], 405);

// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach sessions error', $e, 'Session operation failed.');
// Executes the current PHP statement/block.
}
