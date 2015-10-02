<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id']) && isset($_SESSION['email']) && isset($_SESSION['role'])){
    die(json_encode(['status'=> 'ok', 'email'=> $_SESSION['email'], 'role'=> $_SESSION['role']]));
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
$connection_parameters = $config['mysql']['users'];
$cpassword = md5($_POST['password']);
$connection = mysql_connect($connection_parameters[0], $connection_parameters[2], $connection_parameters[3]) or die("Cannot connect to Database");
$sql_query = "SELECT id, role FROM users WHERE email='$email' AND password='$cpassword'";
$answer = mysql_db_query($connection_parameters[1], $sql_query);;
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
die(json_encode(['status'=>'ok', 'email'=>$email, 'role'=>$row['role']]));