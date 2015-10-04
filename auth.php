<?php
require_once('session_mgr.php');
require_once('config.php');
$db_params = $config['mysql']['users'];
if(isset($_SESSION['user_id']) && isset($_SESSION['email']) && isset($_SESSION['role'])){
    $connection = create_mysql_connection($db_params);
    $id = $_SESSION['user_id'];
    $bill_sql = "SELECT bill FROM users WHERE id=$id";
    $result = mysql_db_query($db_params['schema'], $bill_sql);
    if(!$result){
        die('{"status":"invalid", "msg":"bill"}');
    }
    $row = mysql_fetch_assoc($result);
    if(!$row){
        die('{"status":"invalid", "msg":"bill"}');
    }
    $bill = $row['bill'];
    die(json_encode(['status'=> 'ok', 'email'=> $_SESSION['email'], 'role'=> $_SESSION['role'], 'bill'=>$bill]));
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password'])){
    die('{"status":"invalid", "msg":"api_error"}');
}
//ВАЛИДАЦИЯ!!!!
$form_email = $_POST['email'];
if(!emailIsValid($form_email)){
    die('{"status":"validate_error"}');
}
$email = normalizeEmail($form_email);
$cpassword = md5($_POST['password']);
$connection = create_mysql_connection($db_params);
$sql_query = "SELECT id, role, bill FROM users WHERE email='$email' AND password='$cpassword'";
$answer = mysql_db_query($db_params['schema'], $sql_query);;
if(!$answer){
    die('{"status":"invalid", "msg":"api_error"}');
}
$row = mysql_fetch_assoc($answer);
if(!$row){
    die('{"status":"invalid", "msg":"incorrect"}');
}
$_SESSION['user_id'] = $row['id'];
$_SESSION['email'] = $form_email;
$_SESSION['role'] = $row['role'];
die(json_encode(['status'=>'ok', 'email'=>$email, 'role'=>$row['role'], 'bill'=>$row['bill']]));