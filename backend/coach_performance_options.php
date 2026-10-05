<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = requireLogin();
if ($user['role'] !== 'coach') sendJson(['success' => false, 'message' => 'Coach access only.'], 403);
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT se.session_id, se.sport_id, sp.sport_name, se.coach_id, c.name 
    AS coach_name, se.title, se.session_date, se.status 
    FROM sessions se INNER JOIN sports sp ON sp.sport_id=se.sport_id 
    INNER JOIN coaches c ON c.coach_id=se.coach_id 
    WHERE se.coach_id=:coach_id AND se.session_type='Practice' AND se.status <> 'Cancelled' 
    ORDER BY se.session_date DESC, se.start_time DESC");
    $stmt->execute(['coach_id' => $user['user_id']]);
    sendJson(['success' => true, 'sessions' => $stmt->fetchAll()]);
} catch (Throwable $e) { apiServerError('Performance options error', $e, 'Unable to load practice sessions.'); }
