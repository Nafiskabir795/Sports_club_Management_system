<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/auth.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';

// Executes the current PHP statement/block.
startSecureSession();

// Checks a condition before continuing.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Sends a JSON response to the browser.
    sendJson([
        'success' => false,
        'message' => 'Only POST requests are allowed.'
    // Executes the current PHP statement/block.
    ], 405);
// Executes the current PHP statement/block.
}

// Decodes incoming JSON into PHP data.
$input = json_decode(file_get_contents('php://input'), true);

// Checks a condition before continuing.
if (!is_array($input)) {
    // Reads or updates request/session/server data.
    $input = $_POST;
// Executes the current PHP statement/block.
}

// Executes the current PHP statement/block.
$identifier = trim((string)($input['identifier'] ?? ''));
// Executes the current PHP statement/block.
$password   = (string)($input['password'] ?? '');

// Checks a condition before continuing.
if ($identifier === '' || $password === '') {
    // Sends a JSON response to the browser.
    sendJson([
        'success' => false,
        'message' => 'Please enter your ID, email, or phone and password.'
    // Executes the current PHP statement/block.
    ], 422);
// Executes the current PHP statement/block.
}

// Starts exception-handling logic.
try {
    // Executes the current PHP statement/block.
    $pdo = getPDO();
    // Executes the current PHP statement/block.
    $account = authenticateAccount($pdo, $identifier, $password);

    // Checks a condition before continuing.
    if ($account === null) {
        // Sends a JSON response to the browser.
        sendJson([
            'success' => false,
            'message' => 'Invalid login details.'
        // Executes the current PHP statement/block.
        ], 401);
    // Executes the current PHP statement/block.
    }

    // Manages PHP session state.
    session_regenerate_id(true);

    // Reads or updates request/session/server data.
    $_SESSION['authenticated'] = true;
    // Reads or updates request/session/server data.
    $_SESSION['user_id']       = (int)$account['user_id'];
    // Reads or updates request/session/server data.
    $_SESSION['user_code']     = $account['user_code'];
    // Reads or updates request/session/server data.
    $_SESSION['name']          = $account['name'];
    // Reads or updates request/session/server data.
    $_SESSION['email']         = $account['email'];
    // Reads or updates request/session/server data.
    $_SESSION['role']          = $account['role'];

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'message' => 'Login successful.',
        'user' => [
            'id'    => (int)$account['user_id'],
            'code'  => $account['user_code'],
            'name'  => $account['name'],
            'email' => $account['email'],
            'role'  => $account['role']
        ],
        'redirect' => ($account['role'] === 'coach')
            ? 'coach.html'
            : 'dashboard.html'
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    apiServerError('Sports Club login error', $e, 'A server/database error occurred.');
// Executes the current PHP statement/block.
}
