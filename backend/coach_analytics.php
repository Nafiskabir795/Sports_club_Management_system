<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
$user=requireLogin();if($user['role']!=='coach')sendJson(['success'=>false,'message'=>'Coach only.'],403);
try{
 $pdo=getPDO();$id=(int)$user['user_id'];
 $q=$pdo->prepare('SELECT COUNT(*) FROM sessions WHERE coach_id=?');$q->execute([$id]);$sessions=(int)$q->fetchColumn();
 $q=$pdo->prepare('SELECT COUNT(DISTINCT ss.student_id) FROM student_sports ss JOIN sessions se ON se.sport_id=ss.sport_id WHERE se.coach_id=?');$q->execute([$id]);$players=(int)$q->fetchColumn();
 $q=$pdo->prepare("SELECT ROUND(100*SUM(sp.attendance='Present')/NULLIF(COUNT(*),0),1) FROM session_participants sp JOIN sessions se ON se.session_id=sp.session_id WHERE se.coach_id=? AND sp.attendance<>'Pending'");$q->execute([$id]);$attendance=$q->fetchColumn();
 $scores='SELECT sport_id, overall_score FROM football_performance UNION ALL SELECT sport_id, overall_score FROM volleyball_performance';
 // Performance tables do not contain sport_id; join their parent sessions instead.
 $scores="SELECT se.sport_id,p.overall_score FROM football_performance p JOIN sessions se ON se.session_id=p.session_id WHERE se.coach_id=:c1 UNION ALL SELECT se.sport_id,p.overall_score FROM volleyball_performance p JOIN sessions se ON se.session_id=p.session_id WHERE se.coach_id=:c2 UNION ALL SELECT se.sport_id,p.overall_score FROM cricket_performance p JOIN sessions se ON se.session_id=p.session_id WHERE se.coach_id=:c3";
 $q=$pdo->prepare('SELECT sp.sport_name,ROUND(AVG(scores.overall_score),1) avg_score FROM ('.$scores.') scores JOIN sports sp ON sp.sport_id=scores.sport_id GROUP BY sp.sport_id,sp.sport_name ORDER BY sp.sport_name');$q->execute(['c1'=>$id,'c2'=>$id,'c3'=>$id]);$sports=$q->fetchAll();
 $q=$pdo->prepare('SELECT ROUND(AVG(overall_score),1) FROM ('.$scores.') all_scores');$q->execute(['c1'=>$id,'c2'=>$id,'c3'=>$id]);$average=$q->fetchColumn();
 $missingSql="SELECT st.name,se.title,se.session_date FROM sessions se JOIN student_sports ss ON ss.sport_id=se.sport_id JOIN students st ON st.student_id=ss.student_id WHERE se.coach_id=:coach AND se.session_type='Practice' AND se.status<>'Cancelled' AND se.session_date<=CURDATE() AND NOT EXISTS (SELECT 1 FROM football_performance fp WHERE fp.session_id=se.session_id AND fp.student_id=st.student_id) AND NOT EXISTS (SELECT 1 FROM volleyball_performance vp WHERE vp.session_id=se.session_id AND vp.student_id=st.student_id) AND NOT EXISTS (SELECT 1 FROM cricket_performance cp WHERE cp.session_id=se.session_id AND cp.student_id=st.student_id) ORDER BY se.session_date DESC,st.name LIMIT 20";
 $q=$pdo->prepare($missingSql);$q->execute(['coach'=>$id]);$missing=$q->fetchAll();
 sendJson(['success'=>true,'summary'=>['sessions'=>$sessions,'players'=>$players,'attendance_rate'=>$attendance===false||$attendance===null?0:(float)$attendance,'avg_score'=>$average===false||$average===null?null:(float)$average],'sports'=>$sports,'missing'=>$missing]);
}catch(Throwable $e){apiServerError('Analytics error',$e,'Unable to load analytics.');}
