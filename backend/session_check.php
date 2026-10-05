<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/auth.php';

// Executes the current PHP statement/block.
$user = requireLogin();

// Reads or updates request/session/server data.
$requiredRole = strtolower(trim((string)($_GET['role'] ?? '')));

// Checks a condition before continuing.
if ($requiredRole !== '' && $requiredRole !== $user['role']) {
    // Sends a JSON response to the browser.
    sendJson([
        'success' => false,
        'message' => 'You do not have permission to access this page.'
    // Executes the current PHP statement/block.
    ], 403);
// Executes the current PHP statement/block.
}

// Sends a JSON response to the browser.
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
sendJson([
    'csrf_token' => $_SESSION['csrf_token'],
    'success' => true,
    'logged_in' => true,
    'user' => $user
// Executes the current PHP statement/block.
]);
