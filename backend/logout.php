<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/config.php';

// Executes the current PHP statement/block.
startSecureSession();

// Reads or updates request/session/server data.
$_SESSION = [];

// Checks a condition before continuing.
if (ini_get('session.use_cookies')) {
    // Manages PHP session state.
    $params = session_get_cookie_params();

    setcookie(
        // Manages PHP session state.
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool)$params['secure'],
        (bool)$params['httponly']
    // Executes the current PHP statement/block.
    );
// Executes the current PHP statement/block.
}

// Manages PHP session state.
session_destroy();

// Sends a JSON response to the browser.
sendJson([
    'success' => true,
    'message' => 'Logged out successfully.'
// Executes the current PHP statement/block.
]);
