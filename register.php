<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
    end_script_immediately('{"status":"invalid", "msg":"api_error"}');
}
//ВАЛИДАЦИЯ!!!!!
$form_email = $_POST['email'];
$password = $_POST['password'];
$form_role = $_POST['role'];
if(!emailIsValid($form_email) || !in_array($form_role, ['client', 'performer'])){
    end_script_immediately('{"status":"validate_error"}');
}
$email = normalizeEmail($form_email);
$cpassword = md5($password);
require_once('config.php');
$db_params = $config['mysql']['users'];
$connection = create_mysql_connection($db_params);
if(mysqli_query($connection, "INSERT INTO users (email, password, role) VALUES ('$email', '$cpassword', '$form_role')")){
    $answ = mysqli_query($connection, "SELECT id FROM users WHERE email='$email'");
    if(!$answ){
        end_script_immediately('{"status":"invalid"}', $connection);
    }
    $row = mysqli_fetch_assoc($answ);
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['email'] = $form_email;
    $_SESSION['role'] = $form_role;
    $_SESSION['csrf-token'] = generate_csrf_token($row['id']);
    setcookie("XSRF-TOKEN", $_SESSION['csrf-token']);
    end_script_immediately(json_encode(['status'=>'ok', 'email'=>$form_email, 'role'=>$form_role]), $connection);
}
end_script_immediately('{"status":"invalid", "msg":"used"}', $connection);
