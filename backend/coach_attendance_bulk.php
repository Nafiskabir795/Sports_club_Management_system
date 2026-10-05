<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
require_once __DIR__.'/validation.php';
$user=requireLogin();
if($user['role']!=='coach') sendJson(['success'=>false,'message'=>'Coach access only.'],403);
requireCsrf();
$input=json_decode(file_get_contents('php://input'),true);
if(!is_array($input)||!isset($input['participants'])||!is_array($input['participants'])||count($input['participants'])>500) sendJson(['success'=>false,'message'=>'Invalid attendance batch.'],422);
$sessionId=requirePositiveInt($input['session_id']??0,'session_id');
$pdo=getPDO();$session=ensureSession($pdo,$sessionId);
if((int)$session['coach_id']!==(int)$user['user_id']) sendJson(['success'=>false,'message'=>'Not your session.'],403);
$items=$input['participants'];$seen=[];
foreach($items as $item){
 if(!is_array($item)) sendJson(['success'=>false,'message'=>'Invalid participant.'],422);
 $id=requirePositiveInt($item['student_id']??0,'student_id');
 if(isset($seen[$id])||!in_array($item['attendance']??null,['Present','Absent','Pending'],true)) sendJson(['success'=>false,'message'=>'Duplicate student or invalid attendance.'],422);
 $seen[$id]=true;
}
try {
 $pdo->beginTransaction();
 $check=$pdo->prepare('SELECT 1 FROM student_sports WHERE student_id=? AND sport_id=?');
 $write=$pdo->prepare('INSERT INTO session_participants (session_id,student_id,attendance) VALUES (?,?,?) ON DUPLICATE KEY UPDATE attendance=VALUES(attendance)');
 foreach($items as $item){$id=(int)$item['student_id'];$check->execute([$id,$session['sport_id']]);if(!$check->fetchColumn())throw new DomainException('A player is not registered for this sport.');$write->execute([$sessionId,$id,$item['attendance']]);}
 $pdo->commit();sendJson(['success'=>true,'message'=>count($items).' attendance records saved.']);
}catch(DomainException $e){if($pdo->inTransaction())$pdo->rollBack();sendJson(['success'=>false,'message'=>$e->getMessage()],422);}
catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();apiServerError('Bulk attendance error',$e,'Unable to save attendance.');}
