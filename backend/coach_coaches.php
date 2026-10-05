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
            'SELECT coach_id, coach_code, name, phone, email, age, blood_group,
                    weight_kg, height_cm, physical_attributes, experience_years, created_at
             FROM coaches ORDER BY coach_id DESC'
        // Executes the current PHP statement/block.
        );
        // Retrieves database results.
        sendJson(['success' => true, 'coaches' => $stmt->fetchAll()]);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'POST') {
        // Executes the current PHP statement/block.
        $input = readJsonInput();

        // Executes the current PHP statement/block.
        $required = ['coach_code', 'name', 'phone', 'email', 'password', 'age', 'blood_group'];
        // Executes the current PHP statement/block.
        foreach ($required as $field) {
            // Checks a condition before continuing.
            if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
                // Sends a JSON response to the browser.
                sendJson(['success' => false, 'message' => "Missing field: $field"], 422);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $age = (int)$input['age'];
        // Checks a condition before continuing.
        if ($age < 5 || $age > 100) sendJson(['success' => false, 'message' => 'Age must be between 5 and 100.'], 422);
        // Executes the current PHP statement/block.
        $weight = optionalFloat($input['weight_kg'] ?? null, 'weight_kg', 1, 500);
        // Executes the current PHP statement/block.
        $height = optionalFloat($input['height_cm'] ?? null, 'height_cm', 30, 300);
        // Executes the current PHP statement/block.
        $experience = (int)($input['experience_years'] ?? 0);
        // Checks a condition before continuing.
        if ($experience < 0 || $experience > 80) sendJson(['success' => false, 'message' => 'experience_years must be between 0 and 80.'], 422);

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO coaches
             (coach_code, name, phone, email, password_hash, age, blood_group,
              weight_kg, height_cm, physical_attributes, experience_years)
             VALUES (:coach_code, :name, :phone, :email, :password_hash, :age, :blood_group,
                     :weight_kg, :height_cm, :physical_attributes, :experience_years)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'coach_code' => trim((string)$input['coach_code']),
            'name' => trim((string)$input['name']),
            'phone' => trim((string)$input['phone']),
            'email' => trim((string)$input['email']),
            // Creates a secure password hash.
            'password_hash' => password_hash((string)$input['password'], PASSWORD_DEFAULT),
            'age' => $age,
            'blood_group' => trim((string)$input['blood_group']),
            'weight_kg' => $weight,
            'height_cm' => $height,
            'physical_attributes' => trim((string)($input['physical_attributes'] ?? '')),
            'experience_years' => $experience
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Coach added successfully.',
            'coach_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'PUT') {
        // Decodes incoming JSON into PHP data.
        $input = json_decode(file_get_contents('php://input'), true);
        // Checks a condition before continuing.
        if (!is_array($input) || empty($input['coach_id'])) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'coach_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $coachId = requirePositiveInt($input['coach_id'], 'coach_id');
        // Executes the current PHP statement/block.
        $age = array_key_exists('age', $input) ? (int)$input['age'] : null;
        // Checks a condition before continuing.
        if ($age !== null && ($age < 5 || $age > 100)) sendJson(['success' => false, 'message' => 'Age must be between 5 and 100.'], 422);
        // Checks a condition before continuing.
        if (array_key_exists('weight_kg', $input)) $input['weight_kg'] = optionalFloat($input['weight_kg'], 'weight_kg', 1, 500);
        // Checks a condition before continuing.
        if (array_key_exists('height_cm', $input)) $input['height_cm'] = optionalFloat($input['height_cm'], 'height_cm', 30, 300);
        // Checks a condition before continuing.
        if (array_key_exists('experience_years', $input) && ((int)$input['experience_years'] < 0 || (int)$input['experience_years'] > 80)) sendJson(['success' => false, 'message' => 'experience_years must be between 0 and 80.'], 422);

        $fields = [
            'coach_code', 'name', 'phone', 'email', 'age', 'blood_group',
            'weight_kg', 'height_cm', 'physical_attributes', 'experience_years'
        // Executes the current PHP statement/block.
        ];

        // Executes the current PHP statement/block.
        $sets = [];
        // Executes the current PHP statement/block.
        $params = ['coach_id' => $coachId];

        // Executes the current PHP statement/block.
        foreach ($fields as $field) {
            // Checks a condition before continuing.
            if (array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $sets[] = "$field = :$field";
                $params[$field] = in_array($field, ['age', 'experience_years'])
                    ? (int)$input[$field]
                    // Executes the current PHP statement/block.
                    : ($input[$field] === '' ? null : $input[$field]);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Checks a condition before continuing.
        if (isset($input['password']) && (string)$input['password'] !== '') {
            // Creates a secure password hash.
            $sets[] = 'password_hash = :password_hash';
            // Creates a secure password hash.
            $params['password_hash'] = password_hash((string)$input['password'], PASSWORD_DEFAULT);
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
            'UPDATE coaches SET ' . implode(', ', $sets) . ' WHERE coach_id = :coach_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Coach updated successfully.']);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'DELETE') {
        // Reads or updates request/session/server data.
        $coachId = (int)($_GET['coach_id'] ?? 0);
        // Checks a condition before continuing.
        if ($coachId <= 0) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'coach_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Checks a condition before continuing.
        if ($coachId === (int)$user['user_id']) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'You cannot delete your own active account.'], 400);
        // Executes the current PHP statement/block.
        }

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare('DELETE FROM coaches WHERE coach_id = :coach_id');
        // Executes the prepared SQL statement.
        $stmt->execute(['coach_id' => $coachId]);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Coach deleted successfully.']);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unsupported request method.'], 405);

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Coach code, email, or phone already exists.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach coaches error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Database operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Coach coaches error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Server error.'], 500);
// Executes the current PHP statement/block.
}
