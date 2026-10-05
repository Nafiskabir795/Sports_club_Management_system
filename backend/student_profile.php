<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/auth.php';

// Executes the current PHP statement/block.
$user = requireLogin();

// Checks a condition before continuing.
if ($user['role'] !== 'student') {
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Student access only.'], 403);
// Executes the current PHP statement/block.
}

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();

    // Prepares a parameterized SQL statement.
    $stmt = $pdo->prepare(
        'SELECT student_id, student_code, name, phone, email, age, blood_group,
                weight_kg, height_cm, physical_attributes, created_at
         FROM students
         WHERE student_id = :student_id
         LIMIT 1'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $stmt->execute(['student_id' => $user['user_id']]);
    // Retrieves database results.
    $profile = $stmt->fetch();

    // Checks a condition before continuing.
    if (!$profile) {
        // Sends a JSON response to the browser.
        sendJson(['success' => false, 'message' => 'Student profile not found.'], 404);
    // Executes the current PHP statement/block.
    }

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'profile' => $profile
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student profile error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load profile.'], 500);
// Executes the current PHP statement/block.
}
