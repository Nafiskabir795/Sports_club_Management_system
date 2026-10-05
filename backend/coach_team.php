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
            'SELECT t.team_id, t.sport_id, s.sport_name, t.team_name,
                    t.coach_id, c.name AS coach_name, t.formation,
                    tm.team_member_id, tm.student_id, st.student_code,
                    st.name AS student_name, tm.position_id,
                    p.position_name, tm.lineup_status, tm.jersey_number,
                    tm.formation_slot
             FROM teams t
             INNER JOIN sports s ON s.sport_id = t.sport_id
             INNER JOIN coaches c ON c.coach_id = t.coach_id
             LEFT JOIN team_members tm ON tm.team_id = t.team_id
             LEFT JOIN students st ON st.student_id = tm.student_id
             LEFT JOIN positions p ON p.position_id = tm.position_id
             ORDER BY t.sport_id, tm.lineup_status, tm.jersey_number'
        // Executes the current PHP statement/block.
        );
        // Retrieves database results.
        sendJson(['success' => true, 'teams' => $stmt->fetchAll()]);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $input = readJsonInput();

    // Checks a condition before continuing.
    if ($method === 'POST') {
        // Executes the current PHP statement/block.
        $sportId = (int)($input['sport_id'] ?? 0);
        // Executes the current PHP statement/block.
        $teamName = trim((string)($input['team_name'] ?? ''));
        // Executes the current PHP statement/block.
        $formation = trim((string)($input['formation'] ?? ''));

        // Checks a condition before continuing.
        if ($sportId <= 0 || $teamName === '') sendJson(['success' => false, 'message' => 'sport_id and team_name are required.'], 422);
        // Executes the current PHP statement/block.
        ensureSport($pdo, $sportId);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO teams (sport_id, team_name, coach_id, formation)
             VALUES (:sport_id, :team_name, :coach_id, :formation)'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute([
            'sport_id' => $sportId,
            'team_name' => $teamName,
            'coach_id' => (int)$user['user_id'],
            'formation' => $formation
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Team created successfully.',
            'team_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'PUT') {
        // Executes the current PHP statement/block.
        $teamId = (int)($input['team_id'] ?? 0);
        // Checks a condition before continuing.
        if ($teamId <= 0) sendJson(['success' => false, 'message' => 'team_id is required.'], 422);

        // Executes the current PHP statement/block.
        $team = ensureTeam($pdo, $teamId);
        // Executes the current PHP statement/block.
        $sets = [];
        // Executes the current PHP statement/block.
        $params = ['team_id' => $teamId];

        // Executes the current PHP statement/block.
        foreach (['team_name', 'formation'] as $field) {
            // Checks a condition before continuing.
            if (array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $sets[] = "$field = :$field";
                // Executes the current PHP statement/block.
                $params[$field] = $input[$field];
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
            $sets[] = 'coach_id = :coach_id'; $params['coach_id'] = $coachId;
        // Executes the current PHP statement/block.
        }
        // Checks a condition before continuing.
        if (!$sets) sendJson(['success' => false, 'message' => 'No fields to update.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'UPDATE teams SET ' . implode(', ', $sets) . ' WHERE team_id = :team_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Team updated successfully.']);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unsupported request.'], 405);

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Only one official team is allowed per sport.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach team DB error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Team operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach team error', $e, 'Team operation failed.');
// Executes the current PHP statement/block.
}
