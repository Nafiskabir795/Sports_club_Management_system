<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = requireLogin();
if (($user['role'] ?? '') !== 'coach') sendJson(['success'=>false,'message'=>'Coach access only.'],403);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') sendJson(['success'=>false,'message'=>'GET only.'],405);
try {
    $pdo = getPDO();
    $sports = $pdo->query('SELECT sport_id, sport_name FROM sports ORDER BY sport_name')->fetchAll();
    $players = $pdo->query('SELECT st.student_id, st.student_code, st.name, ss.sport_id FROM students st JOIN student_sports ss ON ss.student_id = st.student_id ORDER BY st.name')->fetchAll();
    $sessions = $pdo->query("SELECT session_id, sport_id, session_type, title, session_date FROM sessions ORDER BY session_date DESC, session_id DESC")->fetchAll();
    $teams = $pdo->query('SELECT team_id, sport_id, team_name FROM teams ORDER BY team_name')->fetchAll();
    $positions = $pdo->query('SELECT position_id, sport_id, position_name FROM positions ORDER BY position_name')->fetchAll();
    sendJson(['success'=>true,'sports'=>$sports,'players'=>$players,'sessions'=>$sessions,'teams'=>$teams,'positions'=>$positions]);
} catch (Throwable $e) {
    error_log('Coach UI options: '.$e->getMessage());
    sendJson(['success'=>false,'message'=>'Unable to load form options.'],500);
}
