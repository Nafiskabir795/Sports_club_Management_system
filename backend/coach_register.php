<?php
// Enables strict typing for this PHP file.
declare(strict_types=1);
// Loads another PHP file.
require_once __DIR__ . '/auth.php';
// Loads another PHP file.
require_once __DIR__ . '/validation.php';
// Checks a condition before continuing.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJson(['success'=>false,'message'=>'Only POST requests are allowed.'],405);
// Executes the current PHP statement/block.
$input=readJsonInput();
// Starts exception-handling logic.
try {
 // Executes the current PHP statement/block.
 $name=requireNonEmptyString($input['name']??'','Name',100);
 // Executes the current PHP statement/block.
 $phone=requireNonEmptyString($input['phone']??'','Phone',20);
 // Executes the current PHP statement/block.
 $email=requireNonEmptyString($input['email']??'','Email',120);
 // Executes the current PHP statement/block.
 $password=(string)($input['password']??'');
 // Checks a condition before continuing.
 if(strlen($password)<8) sendJson(['success'=>false,'message'=>'Password must be at least 8 characters.'],422);
 // Checks a condition before continuing.
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)) sendJson(['success'=>false,'message'=>'Please enter a valid email address.'],422);
 // Sends a JSON response to the browser.
 $age=filter_var($input['age']??null,FILTER_VALIDATE_INT); if($age===false||$age<18||$age>100) sendJson(['success'=>false,'message'=>'Admin/coach age must be between 18 and 100.'],422);
 // Sends a JSON response to the browser.
 $experience=filter_var($input['experience_years']??null,FILTER_VALIDATE_INT); if($experience===false||$experience<0||$experience>80) sendJson(['success'=>false,'message'=>'Experience must be between 0 and 80 years.'],422);
 // Executes the current PHP statement/block.
 $blood=requireNonEmptyString($input['blood_group']??'','Blood group',5);
 // Sends a JSON response to the browser.
 $sports=$input['sport_ids']??[]; if(!is_array($sports)||!count($sports)) sendJson(['success'=>false,'message'=>'Select at least one sport to manage.'],422);
 // Sends a JSON response to the browser.
 $sports=array_values(array_unique(array_map('intval',$sports))); if(in_array(0,$sports,true)) sendJson(['success'=>false,'message'=>'Invalid sport selection.'],422);
 // Executes the current PHP statement/block.
 $pdo=getPDO();
 // Executes the current PHP statement/block.
 foreach($sports as $sid) ensureSport($pdo,$sid);
 // Executes the current PHP statement/block.
 $pdo->beginTransaction();
 // Starts exception-handling logic.
 try {
  // Executes the current PHP statement/block.
  $tmp='TMP'.bin2hex(random_bytes(8));
  // Prepares a parameterized SQL statement.
  $stmt=$pdo->prepare('INSERT INTO coaches (coach_code,name,phone,email,password_hash,age,blood_group,weight_kg,height_cm,physical_attributes,experience_years) VALUES (:code,:name,:phone,:email,:hash,:age,:blood,:weight,:height,:attrs,:experience)');
  // Executes the prepared SQL statement.
  $stmt->execute(['code'=>$tmp,'name'=>$name,'phone'=>$phone,'email'=>strtolower($email),'hash'=>password_hash($password,PASSWORD_DEFAULT),'age'=>$age,'blood'=>strtoupper($blood),'weight'=>optionalFloat($input['weight_kg']??null,'Weight',1,500),'height'=>optionalFloat($input['height_cm']??null,'Height',30,300),'attrs'=>trim((string)($input['physical_attributes']??''))?:null,'experience'=>$experience]);
  // Executes the current PHP statement/block.
  $id=(int)$pdo->lastInsertId(); $code='COA'.str_pad((string)$id,4,'0',STR_PAD_LEFT);
  // Prepares a parameterized SQL statement.
  $u=$pdo->prepare('UPDATE coaches SET coach_code=:code WHERE coach_id=:id'); $u->execute(['code'=>$code,'id'=>$id]);
  // Prepares a parameterized SQL statement.
  $cs=$pdo->prepare('INSERT INTO coach_sports (coach_id,sport_id) VALUES (:coach,:sport)');
  // Executes the prepared SQL statement.
  foreach($sports as $sid) $cs->execute(['coach'=>$id,'sport'=>$sid]);
  // Manages PHP session state.
  $pdo->commit(); startSecureSession(); session_regenerate_id(true);
  // Reads or updates request/session/server data.
  $_SESSION=['authenticated'=>true,'user_id'=>$id,'user_code'=>$code,'name'=>$name,'email'=>strtolower($email),'role'=>'coach'];
  // Sends a JSON response to the browser.
  sendJson(['success'=>true,'message'=>'Admin/coach registration completed.','coach'=>['id'=>$id,'code'=>$code,'name'=>$name],'redirect'=>'coach.html'],201);
 // Executes the current PHP statement/block.
 } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
// Sends a JSON response to the browser.
} catch(PDOException $e){if((int)($e->errorInfo[1]??0)===1062) sendJson(['success'=>false,'message'=>'That email or phone number is already registered.'],409); apiServerError('Admin registration database error',$e,'Registration could not be completed.');}
catch(Throwable $e){apiServerError('Admin registration error',$e,'Registration could not be completed.');}
