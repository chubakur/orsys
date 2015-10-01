<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
    die('{"status":"invalid"}');
}
//ВАЛИДАЦИЯ!!!!!
$form_email = $_POST['email'];
$cpassword = md5($_POST['password']);
$form_role = $_POST['role'];
require_once('config.php');
$connection_parameters = $config['mysql']['users'];
$connection = mysql_connect($connection_parameters[0], $connection_parameters[2], $connection_parameters[3]) or die("Cannot connect to Database");
if(mysql_db_query($connection_parameters[1], "INSERT INTO users (email, password, role) VALUES ('$form_email', '$cpassword', '$form_role');", $connection)){
    $answ = mysql_query("SELECT id FROM users WHERE email='$form_email';", $connection);
    if(!$answ){
        die('{"status":"invalid"}');
    }
    $row = mysql_fetch_assoc($answ);
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['email'] = $form_email;
    $_SESSION['role'] = $form_role;
    die(json_encode(['status'=>'ok', 'email'=>$form_email, 'role'=>$form_role]));
}
die('{"status":"error"}');
