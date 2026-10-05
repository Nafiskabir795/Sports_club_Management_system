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
    $football = $pdo->prepare(
        'SELECT
            fp.performance_id, fp.session_id, fp.student_id,
            s.sport_name, se.session_type, se.session_number, se.title,
            se.session_date, se.start_time, se.end_time,
            fp.passing_accuracy, fp.shot_accuracy, fp.speed_kmh,
            fp.goals, fp.assists, fp.tackles, fp.interceptions,
            fp.fouls, fp.yellow_cards, fp.red_cards,
            fp.saves, fp.goals_conceded, fp.save_accuracy,
            fp.overall_score, fp.coach_notes
         FROM football_performance fp
         INNER JOIN sessions se ON se.session_id = fp.session_id
         INNER JOIN sports s ON s.sport_id = se.sport_id
         WHERE fp.student_id = :student_id
         ORDER BY se.session_date DESC, se.start_time DESC'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $football->execute(['student_id' => $user['user_id']]);

    // Prepares a parameterized SQL statement.
    $volleyball = $pdo->prepare(
        'SELECT
            vp.performance_id, vp.session_id, vp.student_id,
            s.sport_name, se.session_type, se.session_number, se.title,
            se.session_date, se.start_time, se.end_time,
            vp.service_aces, vp.service_errors,
            vp.attack_attempts, vp.successful_attacks, vp.attack_errors,
            vp.blocks, vp.digs, vp.assists, vp.reception_success_rate,
            vp.net_violations, vp.rotation_faults, vp.foot_faults,
            vp.double_contacts, vp.other_faults,
            vp.overall_score, vp.coach_notes
         FROM volleyball_performance vp
         INNER JOIN sessions se ON se.session_id = vp.session_id
         INNER JOIN sports s ON s.sport_id = se.sport_id
         WHERE vp.student_id = :student_id
         ORDER BY se.session_date DESC, se.start_time DESC'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $volleyball->execute(['student_id' => $user['user_id']]);

    // Prepares a parameterized SQL statement.
    $cricket = $pdo->prepare(
        'SELECT
            cp.performance_id, cp.session_id, cp.student_id,
            s.sport_name, se.session_type, se.session_number, se.title,
            se.session_date, se.start_time, se.end_time,
            cp.runs, cp.balls_faced, cp.fours, cp.sixes, cp.dismissals,
            cp.overs, cp.runs_conceded, cp.wickets, cp.maidens,
            cp.wides, cp.no_balls, cp.catches, cp.run_outs, cp.stumpings,
            cp.fielding_errors, cp.fitness_score, cp.experience_score,
            cp.rule_violations, cp.overall_score, cp.coach_notes
         FROM cricket_performance cp
         INNER JOIN sessions se ON se.session_id = cp.session_id
         INNER JOIN sports s ON s.sport_id = se.sport_id
         WHERE cp.student_id = :student_id
         ORDER BY se.session_date DESC, se.start_time DESC'
    // Executes the current PHP statement/block.
    );
    // Executes the prepared SQL statement.
    $cricket->execute(['student_id' => $user['user_id']]);

    // Sends a JSON response to the browser.
    sendJson([
        'success' => true,
        'performance' => [
            // Retrieves database results.
            'football' => $football->fetchAll(),
            // Retrieves database results.
            'volleyball' => $volleyball->fetchAll(),
            // Retrieves database results.
            'cricket' => $cricket->fetchAll()
        ]
    // Executes the current PHP statement/block.
    ]);
// Executes the current PHP statement/block.
} catch (Throwable $e) {
    // Executes the current PHP statement/block.
    error_log('Student performance error: ' . $e->getMessage());
    // Sends a JSON response to the browser.
    sendJson(['success' => false, 'message' => 'Unable to load performance data.'], 500);
// Executes the current PHP statement/block.
}
