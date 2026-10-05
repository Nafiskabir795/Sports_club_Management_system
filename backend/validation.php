<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/config.php';

// Defines a reusable PHP function.
function readJsonInput(): array
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    $raw = file_get_contents('php://input');
    // Decodes incoming JSON into PHP data.
    $input = json_decode($raw ?: '', true);
    // Returns a value from the current function.
    return is_array($input) ? $input : $_POST;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requirePositiveInt(mixed $value, string $field): int
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    // Checks a condition before continuing.
    if ($id === false) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be a positive integer."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return (int)$id;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requireNonEmptyString(mixed $value, string $field, int $maxLength = 255): string
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    $text = trim((string)$value);
    // Checks a condition before continuing.
    if ($text === '') {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field is required."], 422);
    // Executes the current PHP statement/block.
    }
    // Checks a condition before continuing.
    if (mb_strlen($text) > $maxLength) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field is too long."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $text;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function optionalFloat(mixed $value, string $field, float $min = 0, ?float $max = null): ?float
// Executes the current PHP statement/block.
{
    // Checks a condition before continuing.
    if ($value === null || $value === '') return null;
    // Checks a condition before continuing.
    if (!is_numeric($value)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be numeric."], 422);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    $number = (float)$value;
    // Checks a condition before continuing.
    if ($number < $min || ($max !== null && $number > $max)) {
        // Executes the current PHP statement/block.
        $range = $max === null ? "at least $min" : "between $min and $max";
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be $range."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $number;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requireScore(mixed $value, string $field = 'overall_score'): float
// Executes the current PHP statement/block.
{
    // Checks a condition before continuing.
    if ($value === null || $value === '' || !is_numeric($value)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be a number from 0 to 100."], 422);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    $score = (float)$value;
    // Checks a condition before continuing.
    if ($score < 0 || $score > 100) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be between 0 and 100."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $score;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requireDateValue(mixed $value, string $field = 'session_date'): string
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    $date = trim((string)$value);
    // Executes the current PHP statement/block.
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    // Checks a condition before continuing.
    if (!$parsed || $parsed->format('Y-m-d') !== $date) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be a valid YYYY-MM-DD date."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $date;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requireTimeValue(mixed $value, string $field): string
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    $time = trim((string)$value);
    // Executes the current PHP statement/block.
    $parsed = DateTime::createFromFormat('H:i', $time) ?: DateTime::createFromFormat('H:i:s', $time);
    // Checks a condition before continuing.
    if (!$parsed) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "$field must be a valid time."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $parsed->format('H:i:s');
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureTimeOrder(string $start, string $end): void
// Executes the current PHP statement/block.
{
    // Checks a condition before continuing.
    if (strtotime($end) <= strtotime($start)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'end_time must be later than start_time.'], 422);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureSport(PDO $pdo, int $sportId): void
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare('SELECT sport_id FROM sports WHERE sport_id = :sport_id LIMIT 1');
    // Executes the prepared SQL statement.
    $stmt->execute(['sport_id' => $sportId]);
    // Checks a condition before continuing.
    if (!$stmt->fetchColumn()) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Selected sport does not exist.'], 422);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureStudentSport(PDO $pdo, int $studentId, int $sportId): void
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'SELECT 1 FROM student_sports WHERE student_id = :student_id AND sport_id = :sport_id LIMIT 1'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $studentId, 'sport_id' => $sportId]);
    // Checks a condition before continuing.
    if (!$stmt->fetchColumn()) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Student is not registered for this sport.'], 422);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureSession(PDO $pdo, int $sessionId, ?int $sportId = null, ?string $type = null): array
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        // Manages PHP session state.
        'SELECT session_id, sport_id, coach_id, session_type, session_date, start_time, end_time
         FROM sessions WHERE session_id = :session_id LIMIT 1'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute(['session_id' => $sessionId]);
    // Retrieves database results.
    $session = $stmt->fetch();

    // Checks a condition before continuing.
    if (!$session) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Session does not exist.'], 404);
    // Executes the current PHP statement/block.
    }
    // Checks a condition before continuing.
    if ($sportId !== null && (int)$session['sport_id'] !== $sportId) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Session does not belong to the selected sport.'], 422);
    // Executes the current PHP statement/block.
    }
    // Checks a condition before continuing.
    if ($type !== null && $session['session_type'] !== $type) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => "Session must be a $type session."], 422);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $session;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensurePositionForSport(PDO $pdo, int $positionId, int $sportId): void
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'SELECT position_id FROM positions
         WHERE position_id = :position_id AND sport_id = :sport_id LIMIT 1'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute(['position_id' => $positionId, 'sport_id' => $sportId]);
    // Checks a condition before continuing.
    if (!$stmt->fetchColumn()) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Position does not belong to the selected sport.'], 422);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureTeam(PDO $pdo, int $teamId): array
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare('SELECT team_id, sport_id, coach_id FROM teams WHERE team_id = :team_id LIMIT 1');
    // Executes the prepared SQL statement.
    $stmt->execute(['team_id' => $teamId]);
    // Retrieves database results.
    $team = $stmt->fetch();
    // Checks a condition before continuing.
    if (!$team) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Team does not exist.'], 404);
    // Executes the current PHP statement/block.
    }
    // Returns a value from the current function.
    return $team;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureStudentExists(PDO $pdo, int $studentId): void
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare('SELECT student_id FROM students WHERE student_id = :student_id LIMIT 1');
    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $studentId]);
    // Checks a condition before continuing.
    if (!$stmt->fetchColumn()) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Student does not exist.'], 404);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function ensureCoachExists(PDO $pdo, int $coachId): void
// Executes the current PHP statement/block.
{
    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare('SELECT coach_id FROM coaches WHERE coach_id = :coach_id LIMIT 1');
    // Executes the prepared SQL statement.
    $stmt->execute(['coach_id' => $coachId]);
    // Checks a condition before continuing.
    if (!$stmt->fetchColumn()) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Coach does not exist.'], 404);
    // Executes the current PHP statement/block.
    }
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function apiServerError(string $logPrefix, Throwable $e, string $fallback = 'A server/database error occurred.'): never
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    error_log($logPrefix . ': ' . $e->getMessage());
    // Executes the current PHP statement/block.
    $message = APP_DEBUG ? $fallback . ' Details: ' . $e->getMessage() : $fallback;
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => $message], 500);
// Executes the current PHP statement/block.
}
