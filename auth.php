<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id']) && isset($_SESSION['email']) && isset($_SESSION['role'])){
    die(json_encode(['status'=> 'ok', 'email'=> $_SESSION['email'], 'role'=> $_SESSION['role'], 'bill'=>$_SESSION['bill']]));
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password'])){
    die('{"status":"invalid"}');
}
//ВАЛИДАЦИЯ!!!!
$form_email = $_POST['email'];
if(!emailIsValid($form_email)){
    die('{"status":"validate_error"}');
}
$email = normalizeEmail($form_email);
require_once('config.php');
$db_params = $config['mysql']['users'];
$cpassword = md5($_POST['password']);
$connection = create_mysql_connection($db_params);
$sql_query = "SELECT id, role, bill FROM users WHERE email='$email' AND password='$cpassword'";
$answer = mysql_db_query($db_params['schema'], $sql_query);;
if(!$answer){
    die('{"status":"invalid"}');
}
$row = mysql_fetch_assoc($answer);
if(!$row){
    die('{"status":"wrong"}');
}
$_SESSION['user_id'] = $row['id'];
$_SESSION['email'] = $form_email;
$_SESSION['role'] = $row['role'];
$_SESSION['bill'] = $row['bill'];
die(json_encode(['status'=>'ok', 'email'=>$email, 'role'=>$row['role'], 'bill'=>$row['bill']]));