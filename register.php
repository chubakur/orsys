<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
    die('{"status":"invalid", "msg":"api_error"}');
}
//ВАЛИДАЦИЯ!!!!!
$form_email = $_POST['email'];
$password = $_POST['password'];
$form_role = $_POST['role'];
if(!emailIsValid($form_email) || !in_array($form_role, ['client', 'performer'])){
    die('{"status":"validate_error"}');
}
$email = normalizeEmail($form_email);
$cpassword = md5($password);
require_once('config.php');
$db_params = $config['mysql']['users'];
$connection = create_mysql_connection($db_params);
if(mysql_db_query($db_params['schema'], "INSERT INTO users (email, password, role) VALUES ('$email', '$cpassword', '$form_role');", $connection)){
    $answ = mysql_query("SELECT id FROM users WHERE email='$email';", $connection);
    if(!$answ){
        die('{"status":"invalid"}');
    }
    $row = mysql_fetch_assoc($answ);
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['email'] = $form_email;
    $_SESSION['role'] = $form_role;
    $_SESSION['csrf-token'] = generate_csrf_token($row['id']);
    setcookie("XSRF-TOKEN", $_SESSION['csrf-token']);
    die(json_encode(['status'=>'ok', 'email'=>$form_email, 'role'=>$form_role]));
}
die('{"status":"invalid", "msg":"used"}');
