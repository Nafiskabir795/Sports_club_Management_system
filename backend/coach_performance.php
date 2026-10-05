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

// Defines a reusable PHP function.
function performanceTableForSport(string $sport): ?string
// Executes the current PHP statement/block.
{
    // Returns a value from the current function.
    return match (strtolower($sport)) {
        'football' => 'football_performance',
        'volleyball' => 'volleyball_performance',
        'cricket' => 'cricket_performance',
        default => null
    // Executes the current PHP statement/block.
    };
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
        $sessionId = (int)($_GET['session_id'] ?? 0);
        // Reads or updates request/session/server data.
        $sport = trim((string)($_GET['sport'] ?? ''));
        // Executes the current PHP statement/block.
        $table = performanceTableForSport($sport);

        // Checks a condition before continuing.
        if ($sessionId <= 0 || $table === null) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'Valid session_id and sport are required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $session = ensureSession($pdo, $sessionId, null);
        if ((int)$session['coach_id'] !== (int)$user['user_id']) sendJson(['success' => false, 'message' => 'Only the assigned coach can view this practice performance.'], 403);
        // Checks a condition before continuing.
        if (strcasecmp($session['session_type'], 'Practice') !== 0) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'Performance records can only be attached to Practice sessions.'], 422);
        // Executes the current PHP statement/block.
        }
        // Prepares a parameterized SQL statement.
        $sportRow = $pdo->prepare('SELECT sport_name FROM sports WHERE sport_id = :sport_id');
        // Executes the prepared SQL statement.
        $sportRow->execute(['sport_id' => $session['sport_id']]);
        // Checks a condition before continuing.
        if (strcasecmp((string)$sportRow->fetchColumn(), $sport) !== 0) sendJson(['success' => false, 'message' => 'Session sport does not match the selected performance sport.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            "SELECT p.*, st.student_code, st.name
             FROM $table p
             INNER JOIN students st ON st.student_id = p.student_id
             WHERE p.session_id = :session_id
             ORDER BY st.name"
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute(['session_id' => $sessionId]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'sport' => $sport,
            // Manages PHP session state.
            'session_id' => $sessionId,
            // Retrieves database results.
            'performance' => $stmt->fetchAll()
        // Executes the current PHP statement/block.
        ]);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method !== 'POST' && $method !== 'PUT') {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Only GET, POST and PUT are supported.'], 405);
    // Executes the current PHP statement/block.
    }

    // Decodes incoming JSON into PHP data.
    $input = json_decode(file_get_contents('php://input'), true);
    // Checks a condition before continuing.
    if (!is_array($input)) $input = $_POST;

    // Executes the current PHP statement/block.
    $sport = trim((string)($input['sport'] ?? ''));
    // Executes the current PHP statement/block.
    $table = performanceTableForSport($sport);

    // Checks a condition before continuing.
    if ($table === null || empty($input['session_id']) || empty($input['student_id'])) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'sport, session_id and student_id are required.'], 422);
    // Executes the current PHP statement/block.
    }

    // Manages PHP session state.
    $sessionId = requirePositiveInt($input['session_id'], 'session_id');
    // Executes the current PHP statement/block.
    $studentId = requirePositiveInt($input['student_id'], 'student_id');
    // Executes the current PHP statement/block.
    $session = ensureSession($pdo, $sessionId);
    if ((int)$session['coach_id'] !== (int)$user['user_id']) sendJson(['success' => false, 'message' => 'Only the assigned coach can record this practice performance.'], 403);
    // Checks a condition before continuing.
    if ($session['session_type'] !== 'Practice') sendJson(['success' => false, 'message' => 'Performance records can only be attached to Practice sessions.'], 422);
    // Checks a condition before continuing.
    if (strcasecmp($sport, (string)(function() use ($pdo, $session) { $q=$pdo->prepare('SELECT sport_name FROM sports WHERE sport_id=:sport_id'); $q->execute(['sport_id'=>$session['sport_id']]); return $q->fetchColumn(); })()) !== 0) sendJson(['success' => false, 'message' => 'Session sport does not match the selected performance sport.'], 422);
    // Executes the current PHP statement/block.
    ensureStudentSport($pdo, $studentId, (int)$session['sport_id']);

    // Executes the current PHP statement/block.
    $allowed = match (strtolower($sport)) {
        'football' => [
            'passing_accuracy','shot_accuracy','speed_kmh','goals','assists',
            'tackles','interceptions','fouls','yellow_cards','red_cards',
            'saves','goals_conceded','save_accuracy','overall_score','coach_notes'
        ],
        'volleyball' => [
            'service_aces','service_errors','attack_attempts','successful_attacks',
            'attack_errors','blocks','digs','assists','reception_success_rate',
            'net_violations','rotation_faults','foot_faults','double_contacts',
            'other_faults','overall_score','coach_notes'
        ],
        'cricket' => [
            'runs','balls_faced','fours','sixes','dismissals','overs',
            'runs_conceded','wickets','maidens','wides','no_balls','catches',
            'run_outs','stumpings','fielding_errors','fitness_score',
            'experience_score','rule_violations','overall_score','coach_notes'
        ]
    // Executes the current PHP statement/block.
    };

    // Executes the current PHP statement/block.
    $data = [];
    // Executes the current PHP statement/block.
    foreach ($allowed as $field) {
        // Checks a condition before continuing.
        if (array_key_exists($field, $input)) {
            // Executes the current PHP statement/block.
            $data[$field] = $input[$field];
        // Executes the current PHP statement/block.
        }
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $percentageFields = ['passing_accuracy','shot_accuracy','save_accuracy','reception_success_rate','fitness_score','experience_score'];
    // Executes the current PHP statement/block.
    foreach ($data as $field => $value) {
        // Checks a condition before continuing.
        if (in_array($field, $percentageFields, true)) $data[$field] = optionalFloat($value, $field, 0, 100);
        // Sends a JSON response to the browser.
        elseif ($field !== 'coach_notes' && $field !== 'overall_score' && $field !== 'speed_kmh' && !is_numeric($value)) sendJson(['success' => false, 'message' => "$field must be numeric."], 422);
        if ($field !== 'coach_notes' && $field !== 'overall_score' && $value !== null && $value !== '') {
            if (!is_numeric($value) || !is_finite((float)$value) || (float)$value < 0) sendJson(['success' => false, 'message' => "$field must be a nonnegative number."], 422);
            if (!in_array($field, array_merge($percentageFields, ['speed_kmh','overs']), true) && (float)$value != floor((float)$value)) sendJson(['success' => false, 'message' => "$field must be a whole number."], 422);
        }
        // Executes the current PHP statement/block.
        elseif ($field === 'speed_kmh') $data[$field] = optionalFloat($value, $field, 0, 200);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if (array_key_exists('overall_score', $data)) {
        // Executes the current PHP statement/block.
        $score = requireScore($data['overall_score']);
        // Executes the current PHP statement/block.
        $data['overall_score'] = $score;
        // Checks a condition before continuing.
        if ($score < 0 || $score > 100) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'Overall score must be between 0 and 100.'], 422);
        // Executes the current PHP statement/block.
        }
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if (array_key_exists('coach_notes', $data) && strlen((string)$data['coach_notes']) > 5000) sendJson(['success' => false, 'message' => 'Coach notes must be at most 5000 characters.'], 422);

    if ($method === 'POST') {
        // Manages PHP session state.
        $columns = ['session_id', 'student_id', 'coach_id'];
        // Manages PHP session state.
        $placeholders = [':session_id', ':student_id', ':coach_id'];
        $params = [
            // Manages PHP session state.
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'coach_id' => $user['user_id']
        // Executes the current PHP statement/block.
        ];

        // Executes the current PHP statement/block.
        foreach ($data as $field => $value) {
            // Executes the current PHP statement/block.
            $columns[] = $field;
            // Executes the current PHP statement/block.
            $placeholders[] = ':' . $field;
            // Executes the current PHP statement/block.
            $params[$field] = $value;
        // Executes the current PHP statement/block.
        }

        $sql = 'INSERT INTO ' . $table .
               ' (' . implode(', ', $columns) . ') VALUES (' .
               // Executes the current PHP statement/block.
               implode(', ', $placeholders) . ')';

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare($sql);
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Performance recorded successfully.',
            'performance_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $performanceId = (int)($input['performance_id'] ?? 0);
    // Checks a condition before continuing.
    if ($performanceId <= 0) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'performance_id is required for updates.'], 422);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    // Bind an update to the selected session, player and assigned coach.
    $existing = $pdo->prepare("SELECT performance_id FROM $table WHERE performance_id = :performance_id AND session_id = :session_id AND student_id = :student_id AND coach_id = :coach_id");
    $existing->execute(['performance_id' => $performanceId, 'session_id' => $sessionId, 'student_id' => $studentId, 'coach_id' => $user['user_id']]);
    if (!$existing->fetchColumn()) sendJson(['success' => false, 'message' => 'Performance record not found for this player and practice.'], 404);

    if (!$data) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'No fields to update.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $sets = [];
    // Executes the current PHP statement/block.
    $params = ['performance_id' => $performanceId];

    // Executes the current PHP statement/block.
    foreach ($data as $field => $value) {
        // Executes the current PHP statement/block.
        $sets[] = "$field = :$field";
        // Executes the current PHP statement/block.
        $params[$field] = $value;
    // Executes the current PHP statement/block.
    }

    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'UPDATE ' . $table . ' SET ' . implode(', ', $sets) .
        ' WHERE performance_id = :performance_id'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute($params);

    // Sends a JSON response to the browser.
    sendJson(['success' => true, 'message' => 'Performance updated successfully.']);

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'A performance record already exists for this student and session. Use PUT to update it.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach performance DB error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Performance database operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach performance error', $e, 'Unable to save performance.');
// Executes the current PHP statement/block.
}
