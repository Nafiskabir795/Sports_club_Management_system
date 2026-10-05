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
            'SELECT student_id, student_code, name, phone, email, age, blood_group,
                    weight_kg, height_cm, physical_attributes, created_at
             FROM students ORDER BY student_id DESC'
        // Executes the current PHP statement/block.
        );
        // Retrieves database results.
        sendJson(['success' => true, 'students' => $stmt->fetchAll()]);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'POST') {
        // Executes the current PHP statement/block.
        $input = readJsonInput();

        // Executes the current PHP statement/block.
        $required = ['student_code', 'name', 'phone', 'email', 'password', 'age', 'blood_group'];
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

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO students
             (student_code, name, phone, email, password_hash, age, blood_group,
              weight_kg, height_cm, physical_attributes)
             VALUES (:student_code, :name, :phone, :email, :password_hash, :age, :blood_group,
                     :weight_kg, :height_cm, :physical_attributes)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'student_code' => trim((string)$input['student_code']),
            'name' => trim((string)$input['name']),
            'phone' => trim((string)$input['phone']),
            'email' => trim((string)$input['email']),
            // Creates a secure password hash.
            'password_hash' => password_hash((string)$input['password'], PASSWORD_DEFAULT),
            'age' => $age,
            'blood_group' => trim((string)$input['blood_group']),
            'weight_kg' => $weight,
            'height_cm' => $height,
            'physical_attributes' => trim((string)($input['physical_attributes'] ?? ''))
        // Executes the current PHP statement/block.
        ]);

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Student added successfully.',
            'student_id' => (int)$pdo->lastInsertId()
        // Executes the current PHP statement/block.
        ], 201);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'PUT') {
        // Decodes incoming JSON into PHP data.
        $input = json_decode(file_get_contents('php://input'), true);
        // Checks a condition before continuing.
        if (!is_array($input) || empty($input['student_id'])) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'student_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $studentId = requirePositiveInt($input['student_id'], 'student_id');
        // Executes the current PHP statement/block.
        $age = array_key_exists('age', $input) ? (int)$input['age'] : null;
        // Checks a condition before continuing.
        if ($age !== null && ($age < 5 || $age > 100)) sendJson(['success' => false, 'message' => 'Age must be between 5 and 100.'], 422);
        // Checks a condition before continuing.
        if (array_key_exists('weight_kg', $input)) $input['weight_kg'] = optionalFloat($input['weight_kg'], 'weight_kg', 1, 500);
        // Checks a condition before continuing.
        if (array_key_exists('height_cm', $input)) $input['height_cm'] = optionalFloat($input['height_cm'], 'height_cm', 30, 300);

        $fields = [
            'student_code', 'name', 'phone', 'email', 'age', 'blood_group',
            'weight_kg', 'height_cm', 'physical_attributes'
        // Executes the current PHP statement/block.
        ];

        // Executes the current PHP statement/block.
        $sets = [];
        // Executes the current PHP statement/block.
        $params = ['student_id' => $studentId];

        // Executes the current PHP statement/block.
        foreach ($fields as $field) {
            // Checks a condition before continuing.
            if (array_key_exists($field, $input)) {
                // Executes the current PHP statement/block.
                $sets[] = "$field = :$field";
                $params[$field] = in_array($field, ['age']) ? (int)$input[$field]
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
            'UPDATE students SET ' . implode(', ', $sets) . ' WHERE student_id = :student_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $stmt->execute($params);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Student updated successfully.']);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if ($method === 'DELETE') {
        // Reads or updates request/session/server data.
        $studentId = (int)($_GET['student_id'] ?? 0);
        // Checks a condition before continuing.
        if ($studentId <= 0) {
            // Sends a JSON response to the browser.
            sendJson(['success' => false, 'message' => 'student_id is required.'], 422);
        // Executes the current PHP statement/block.
        }

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare('DELETE FROM students WHERE student_id = :student_id');
        // Executes the prepared SQL statement.
        $stmt->execute(['student_id' => $studentId]);

        // Sends a JSON response to the browser.
        sendJson(['success' => true, 'message' => 'Student deleted successfully.']);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unsupported request method.'], 405);

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)$e->errorInfo[1] === 1062) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Student code, email, or phone already exists.'], 409);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    error_log('Coach students error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Database operation failed.'], 500);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Coach students error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Server error.'], 500);
// Executes the current PHP statement/block.
}
