<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/auth.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';

// Checks a condition before continuing.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Only POST requests are allowed.'], 405);
// Executes the current PHP statement/block.
}

// Executes the current PHP statement/block.
$input = readJsonInput();

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $name = requireNonEmptyString($input['name'] ?? '', 'Name', 100);
    // Executes the current PHP statement/block.
    $phone = requireNonEmptyString($input['phone'] ?? '', 'Phone', 20);
    // Executes the current PHP statement/block.
    $email = requireNonEmptyString($input['email'] ?? '', 'Email', 120);
    // Executes the current PHP statement/block.
    $password = (string)($input['password'] ?? '');

    // Checks a condition before continuing.
    if (strlen($password) < 8) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
    // Executes the current PHP statement/block.
    }

    // Checks a condition before continuing.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $age = filter_var($input['age'] ?? null, FILTER_VALIDATE_INT);
    // Checks a condition before continuing.
    if ($age === false || $age < 5 || $age > 100) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Age must be between 5 and 100.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $bloodGroup = requireNonEmptyString($input['blood_group'] ?? '', 'Blood group', 5);
    // Executes the current PHP statement/block.
    $weight = optionalFloat($input['weight_kg'] ?? null, 'Weight', 1, 500);
    // Executes the current PHP statement/block.
    $height = optionalFloat($input['height_cm'] ?? null, 'Height', 30, 300);
    // Executes the current PHP statement/block.
    $physicalAttributes = trim((string)($input['physical_attributes'] ?? ''));

    // Executes the current PHP statement/block.
    $sportsInput = $input['sports'] ?? null;
    // Checks a condition before continuing.
    if (!is_array($sportsInput) || count($sportsInput) < 1) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Select at least one sport.'], 422);
    // Executes the current PHP statement/block.
    }
    // Executes the current PHP statement/block.
    $sports = [];
    // Executes the current PHP statement/block.
    $seenSports = [];
    // Executes the current PHP statement/block.
    foreach ($sportsInput as $item) {
        // Checks a condition before continuing.
        if (!is_array($item)) sendJson(['success' => false, 'message' => 'Invalid sport selection.'], 422);
        // Executes the current PHP statement/block.
        $sportId = requirePositiveInt($item['sport_id'] ?? 0, 'Sport');
        // Executes the current PHP statement/block.
        $positionId = requirePositiveInt($item['position_id'] ?? 0, 'Position');
        // Checks a condition before continuing.
        if (isset($seenSports[$sportId])) sendJson(['success' => false, 'message' => 'A sport can only be selected once.'], 422);
        // Executes the current PHP statement/block.
        $seenSports[$sportId] = true;
        // Executes the current PHP statement/block.
        $sports[] = ['sport_id' => $sportId, 'position_id' => $positionId];
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $pdo = getPDO();
    // Executes the current PHP statement/block.
    foreach ($sports as $selection) {
        // Executes the current PHP statement/block.
        ensureSport($pdo, $selection['sport_id']);
        // Executes the current PHP statement/block.
        ensurePositionForSport($pdo, $selection['position_id'], $selection['sport_id']);
    // Executes the current PHP statement/block.
    }

    // Validate optional sport-specific registration fields.
    $handedness = $input['handedness'] ?? null;
    // Checks a condition before continuing.
    if ($handedness !== null && !in_array($handedness, ['Right-Handed', 'Left-Handed'], true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Invalid handedness selection.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $serveType = $input['serve_type'] ?? null;
    // Checks a condition before continuing.
    if ($serveType !== null && !in_array($serveType, ['Underhanded', 'Normal', 'Jump', 'Jump Float', 'Curved'], true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Invalid serve type selection.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $previousRole = $input['previous_role'] ?? null;
    // Checks a condition before continuing.
    if ($previousRole !== null && !in_array($previousRole, ['Team Captain', 'Ace', 'Head Coach', 'Assistant Coach', 'Team Manager'], true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Invalid previous role selection.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $battingStyle = $input['batting_style'] ?? null;
    // Checks a condition before continuing.
    if ($battingStyle !== null && !in_array($battingStyle, ['Right-Hand Batter', 'Left-Hand Batter'], true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Invalid batting style selection.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $bowlingStyle = $input['bowling_style'] ?? null;
    $validBowlingStyles = [
        'Right-Arm Fast', 'Right-Arm Fast-Medium', 'Right-Arm Medium',
        'Left-Arm Fast', 'Left-Arm Fast-Medium', 'Left-Arm Medium',
        'Off-Spin', 'Leg-Spin', 'Left-Arm Orthodox'
    // Executes the current PHP statement/block.
    ];
    // Checks a condition before continuing.
    if ($bowlingStyle !== null && !in_array($bowlingStyle, $validBowlingStyles, true)) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Invalid bowling style selection.'], 422);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    $pdo->beginTransaction();

    // Starts exception-handling logic.
    try {
        // Temporary unique code lets us use the auto-incremented student_id
        // to generate a friendly STU0001-style login code afterwards.
        $temporaryCode = 'TMP' . bin2hex(random_bytes(8));

        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare(
            'INSERT INTO students
             (student_code, name, phone, email, password_hash, age, blood_group,
              weight_kg, height_cm, physical_attributes)
             VALUES
             (:student_code, :name, :phone, :email, :password_hash, :age, :blood_group,
              :weight_kg, :height_cm, :physical_attributes)'
        // Executes the current PHP statement/block.
        );

        // Executes the prepared SQL statement.
        $stmt->execute([
            'student_code' => $temporaryCode,
            'name' => $name,
            'phone' => $phone,
            'email' => strtolower($email),
            // Creates a secure password hash.
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'age' => $age,
            'blood_group' => strtoupper($bloodGroup),
            'weight_kg' => $weight,
            'height_cm' => $height,
            'physical_attributes' => $physicalAttributes !== '' ? $physicalAttributes : null
        // Executes the current PHP statement/block.
        ]);

        // Executes the current PHP statement/block.
        $studentId = (int)$pdo->lastInsertId();
        // Executes the current PHP statement/block.
        $studentCode = 'STU' . str_pad((string)$studentId, 4, '0', STR_PAD_LEFT);

        // Prepares a parameterized SQL statement.
        $update = $pdo->prepare(
            'UPDATE students SET student_code = :student_code WHERE student_id = :student_id'
        // Executes the current PHP statement/block.
        );
        // Executes the prepared SQL statement.
        $update->execute([
            'student_code' => $studentCode,
            'student_id' => $studentId
        // Executes the current PHP statement/block.
        ]);

        // Prepares a parameterized SQL statement.
        $sportStmt = $pdo->prepare('INSERT INTO student_sports (student_id, sport_id) VALUES (:student_id, :sport_id)');
        // Prepares a parameterized SQL statement.
        $choiceStmt = $pdo->prepare(
            'INSERT INTO student_sport_choices
             (student_id, sport_id, position_id, handedness, serve_type,
              previous_role, batting_style, bowling_style)
             VALUES
             (:student_id, :sport_id, :position_id, :handedness, :serve_type,
              :previous_role, :batting_style, :bowling_style)'
        // Executes the current PHP statement/block.
        );
        // Executes the current PHP statement/block.
        foreach ($sports as $selection) {
            // Executes the prepared SQL statement.
            $sportStmt->execute(['student_id'=>$studentId, 'sport_id'=>$selection['sport_id']]);
            // Executes the prepared SQL statement.
            $choiceStmt->execute([
                'student_id'=>$studentId, 'sport_id'=>$selection['sport_id'], 'position_id'=>$selection['position_id'],
                'handedness'=>$handedness, 'serve_type'=>$serveType, 'previous_role'=>$previousRole,
                'batting_style'=>$battingStyle, 'bowling_style'=>$bowlingStyle
            // Executes the current PHP statement/block.
            ]);
        // Executes the current PHP statement/block.
        }

        // Executes the current PHP statement/block.
        $pdo->commit();

        // Start a normal authenticated student session immediately.
        startSecureSession();
        // Manages PHP session state.
        session_regenerate_id(true);
        // Reads or updates request/session/server data.
        $_SESSION['authenticated'] = true;
        // Reads or updates request/session/server data.
        $_SESSION['user_id'] = $studentId;
        // Reads or updates request/session/server data.
        $_SESSION['user_code'] = $studentCode;
        // Reads or updates request/session/server data.
        $_SESSION['name'] = $name;
        // Reads or updates request/session/server data.
        $_SESSION['email'] = strtolower($email);
        // Reads or updates request/session/server data.
        $_SESSION['role'] = 'student';

        // Sends a JSON response to the browser.
        sendJson([
            'success' => true,
            'message' => 'Registration completed successfully.',
            'student' => [
                'id' => $studentId,
                'code' => $studentCode,
                'name' => $name
            ],
            'redirect' => 'portal.html'
        // Executes the current PHP statement/block.
        ], 201);

    // Executes the current PHP statement/block.
    } catch (Throwable $e) {
        // Checks a condition before continuing.
        if ($pdo->inTransaction()) {
            // Executes the current PHP statement/block.
            $pdo->rollBack();
        // Executes the current PHP statement/block.
        }
        // Executes the current PHP statement/block.
        throw $e;
    // Executes the current PHP statement/block.
    }

// Executes the current PHP statement/block.
} catch (PDOException $e) {
    // Checks a condition before continuing.
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
        // Sends a JSON response to the browser.
        sendJson([
            'success' => false,
            'message' => 'That email or phone number is already registered.'
        // Executes the current PHP statement/block.
        ], 409);
    // Executes the current PHP statement/block.
    }

    // Executes the current PHP statement/block.
    apiServerError('Student registration database error', $e, 'Registration could not be completed.');
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Student registration error', $e, 'Registration could not be completed.');
// Executes the current PHP statement/block.
}
