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
    if ($method === 'POST') {
        // Executes the current PHP statement/block.
        $input = readJsonInput();

        // Executes the current PHP statement/block.
        $required = ['team_id', 'student_id', 'position_id', 'lineup_status'];
        // Executes the current PHP statement/block.
        foreach ($required as $field) {
            // Checks a condition before continuing.
            if (!isset($input[$field]) || (string)$input[$field] === '') {
                // Sends a JSON response to the browser.
                sendJson(['success' => false, 'message' => "Missing field: $field"], 422);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $teamId = requirePositiveInt($input['team_id'], 'team_id');
        // Executes the current PHP statement/block.
        $studentId = requirePositiveInt($input['student_id'], 'student_id');
        // Executes the current PHP statement/block.
        $positionId = requirePositiveInt($input['position_id'], 'position_id');
        // Executes the current PHP statement/block.
        $team = ensureTeam($pdo, $teamId);
        // Executes the current PHP statement/block.
        ensureStudentSport($pdo, $studentId, (int)$team['sport_id']);
        // Executes the current PHP statement/block.
        ensurePositionForSport($pdo, $positionId, (int)$team['sport_id']);
        // Executes the current PHP statement/block.
        $status = (string)$input['lineup_status'];
        // Checks a condition before continuing.
        if (!in_array($status, ['Starter', 'Substitute', 'Reserve'], true)) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'Invalid lineup status.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $jersey = ($input['jersey_number'] ?? '') === '' ? null : (int)$input['jersey_number'];
        // Checks a condition before continuing.
        if ($jersey !== null && ($jersey < 0 || $jersey > 255)) sendJson(['success' => false, 'message' => 'jersey_number must be between 0 and 255.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO team_members
             (team_id, student_id, position_id, lineup_status, jersey_number, formation_slot)
             VALUES (:team_id, :student_id, :position_id, :lineup_status, :jersey_number, :formation_slot)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'team_id' => $teamId,
            'student_id' => $studentId,
            'position_id' => $positionId,
            'lineup_status' => $status,
            'jersey_number' => $jersey,
            'formation_slot' => trim((string)($input['formation_slot'] ?? ''))
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Player assigned to team successfully.',
            'team_member_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'PUT') {
        // Decodes incoming JSON into PHP data.
        $input = json_decode(file_get_contents('php://input'), true);
        // Checks a condition before continuing.
        if (!is_array($input) || empty($input['team_member_id'])) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'team_member_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $memberId = requirePositiveInt($input['team_member_id'], 'team_member_id');
        // Prepares a parameterized SQL statement.
        $memberStmt = $pdo->prepare('SELECT tm.team_id, t.sport_id FROM team_members tm INNER JOIN teams t ON t.team_id=tm.team_id WHERE tm.team_member_id=:id');
        // Executes the prepared SQL statement.
        $memberStmt->execute(['id' => $memberId]);
        // Retrieves database results.
        $member = $memberStmt->fetch();
        // Checks a condition before continuing.
        if (!$member) sendJson(['success' => false, 'message' => 'Team assignment does not exist.'], 404);
        // Executes the current PHP statement/block.
        $team = ensureTeam($pdo, (int)$member['team_id']);
        // Executes the current PHP statement/block.
        $allowed = ['position_id', 'lineup_status', 'jersey_number', 'formation_slot'];
        // Executes the current PHP statement/block.
        $sets = [];
        // Executes the current PHP statement/block.
        $params = ['team_member_id' => $memberId];

        // Executes the current PHP statement/block.
        foreach ($allowed as $field) {
            // Checks a condition before continuing.
            if ($field === 'position_id' && array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $input[$field] = requirePositiveInt($input[$field], 'position_id');
                // Executes the current PHP statement/block.
                ensurePositionForSport($pdo, (int)$input[$field], (int)$team['sport_id']);
            // Executes the current PHP statement/block.
            }
            // Checks a condition before continuing.
            if ($field === 'lineup_status' && array_key_exists($field, $input) && !in_array($input[$field], ['Starter','Substitute','Reserve'], true)) {
                // Sends a JSON response to the browser.
                sendJson(['success' => false, 'message' => 'Invalid lineup status.'], 422);
            // Executes the current PHP statement/block.
            }
            // Checks a condition before continuing.
            if ($field === 'jersey_number' && array_key_exists($field, $input) && $input[$field] !== '') {
                // Executes the current PHP statement/block.
                $input[$field] = (int)$input[$field];
                // Checks a condition before continuing.
                if ($input[$field] < 0 || $input[$field] > 255) sendJson(['success' => false, 'message' => 'jersey_number must be between 0 and 255.'], 422);
            // Executes the current PHP statement/block.
            }
            // Checks a condition before continuing.
            if (array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $sets[] = "$field = :$field";
                // Executes the current PHP statement/block.
                $params[$field] = $input[$field] === '' ? null : $input[$field];
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Checks a condition before continuing.
        if (!$sets) sendJson(['success' => false, 'message' => 'No fields to update.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'UPDATE team_members SET ' . implode(', ', $sets) .
            ' WHERE team_member_id = :team_member_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Team assignment updated successfully.']);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'DELETE') {
        // Reads or updates request/session/server data.
        $teamMemberId = (int)($_GET['team_member_id'] ?? 0);
        // Checks a condition before continuing.
        if ($teamMemberId <= 0) sendJson(['success' => false, 'message' => 'team_member_id is required.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'DELETE FROM team_members WHERE team_member_id = :team_member_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute(['team_member_id' => $teamMemberId]);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Player removed from team successfully.']);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unsupported request method.'], 405);

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'This player or jersey number is already assigned to this team.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach team member DB error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Team assignment operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Coach team member error', $e, 'Team assignment operation failed.');
// Executes the current PHP statement/block.
}
