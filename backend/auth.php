<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);

// Loads another PHP file.
require_once __DIR__ . '/config.php';

// Defines a reusable PHP function.
function authenticateAccount(PDO $pdo, string $identifier, string $password): ?array
// Executes the current PHP statement/block.
{
    $queries = [
        [
            'sql' => 'SELECT student_id AS user_id, student_code AS user_code,
                             name, email, phone, password_hash,
                             \'student\' AS role
                      FROM students
                      WHERE student_code = ?
                         OR email = ?
                         OR phone = ?
                      LIMIT 1',
            'role' => 'student'
        ],
        [
            'sql' => 'SELECT coach_id AS user_id, coach_code AS user_code,
                             name, email, phone, password_hash,
                             \'coach\' AS role
                      FROM coaches
                      WHERE coach_code = ?
                         OR email = ?
                         OR phone = ?
                      LIMIT 1',
            'role' => 'coach'
        ]
    // Executes the current PHP statement/block.
    ];

    // Executes the current PHP statement/block.
    foreach ($queries as $query) {
        // Prepares a parameterized SQL statement.
        $stmt = $pdo->prepare($query['sql']);
        // MySQL native prepared statements do not allow reusing one named
        // placeholder more than once. Use positional placeholders instead.
        $stmt->execute([$identifier, $identifier, $identifier]);
        // Retrieves database results.
        $account = $stmt->fetch();

        // Checks a condition before continuing.
        if (!$account) {
            // Executes the current PHP statement/block.
            continue;
        // Executes the current PHP statement/block.
        }

        // Creates a secure password hash.
        $storedHash = (string)$account['password_hash'];

        /*
         * Seed data currently contains SHA-256 hashes of Password123!.
         * New/updated passwords use PHP password_hash().
         * If a legacy SHA-256 hash is detected and verified, upgrade it.
         */
        // Verifies a password against its stored hash.
        $verified = password_verify($password, $storedHash);

        // Checks a condition before continuing.
        if (!$verified && preg_match('/^[a-f0-9]{64}$/i', $storedHash)) {
            $verified = hash_equals(
                strtolower($storedHash),
                hash('sha256', $password)
            // Executes the current PHP statement/block.
            );

            // Checks a condition before continuing.
            if ($verified) {
                // Creates a secure password hash.
                $newHash = password_hash($password, PASSWORD_DEFAULT);

                // Checks a condition before continuing.
                if ($account['role'] === 'student') {
                    // Prepares a parameterized SQL statement.
                    $update = $pdo->prepare(
                        // Creates a secure password hash.
                        'UPDATE students SET password_hash = :hash WHERE student_id = :id'
                    // Executes the current PHP statement/block.
                    );
                // Checks a condition before continuing.
                } else {
                    // Prepares a parameterized SQL statement.
                    $update = $pdo->prepare(
                        // Creates a secure password hash.
                        'UPDATE coaches SET password_hash = :hash WHERE coach_id = :id'
                    // Executes the current PHP statement/block.
                    );
                // Executes the current PHP statement/block.
                }

                // Executes the prepared SQL statement.
                $update->execute([
                    'hash' => $newHash,
                    'id'   => $account['user_id']
                // Executes the current PHP statement/block.
                ]);
            // Executes the current PHP statement/block.
            }
        // Executes the current PHP statement/block.
        }

        // Checks a condition before continuing.
        if (!$verified) {
            // Returns a value from the current function.
            return null;
        // Executes the current PHP statement/block.
        }

        // Creates a secure password hash.
        unset($account['password_hash']);
        // Returns a value from the current function.
        return $account;
    // Executes the current PHP statement/block.
    }

    // Returns a value from the current function.
    return null;
// Executes the current PHP statement/block.
}

// Defines a reusable PHP function.
function requireLogin(): array
// Executes the current PHP statement/block.
{
    // Executes the current PHP statement/block.
    startSecureSession();

    // Checks a condition before continuing.
    if (
        // Reads or updates request/session/server data.
        empty($_SESSION['authenticated']) ||
        // Reads or updates request/session/server data.
        empty($_SESSION['user_id']) ||
        // Reads or updates request/session/server data.
        empty($_SESSION['role'])
    // Executes the current PHP statement/block.
    ) {
        // Sends a JSON response to the browser.
        sendJson([
            'success' => false,
            'message' => 'You must be logged in.'
        // Executes the current PHP statement/block.
        ], 401);
    // Executes the current PHP statement/block.
    }

    // Returns a value from the current function.
    return [
        // Reads or updates request/session/server data.
        'user_id'   => (int)$_SESSION['user_id'],
        // Reads or updates request/session/server data.
        'user_code' => (string)$_SESSION['user_code'],
        // Reads or updates request/session/server data.
        'name'      => (string)$_SESSION['name'],
        // Reads or updates request/session/server data.
        'email'     => (string)$_SESSION['email'],
        // Reads or updates request/session/server data.
        'role'      => (string)$_SESSION['role'],
    // Executes the current PHP statement/block.
    ];
// Executes the current PHP statement/block.
}

/** Validate same-origin state-changing requests using a session-bound token. */
function requireCsrf(): void {
    startSecureSession();
    $sent=(string)($_SERVER['HTTP_X_CSRF_TOKEN']??'');
    if(empty($_SESSION['csrf_token']) || $sent==='' || !hash_equals((string)$_SESSION['csrf_token'],$sent))
        sendJson(['success'=>false,'message'=>'Security token expired. Refresh the page and try again.'],403);
}
