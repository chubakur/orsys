<?php
require('entry_point.php');
$handlers = [];
if(isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])) {
    end_script_immediately('{"status":"invalid", "msg":"api_error"}', $handlers);
}
//ВАЛИДАЦИЯ!!!!!
require_once('utils.php');
$form_email = $_POST['email'];
$password = $_POST['password'];
$form_role = $_POST['role'];
if(!emailIsValid($form_email) || !in_array($form_role, ['client', 'performer'])){
    end_script_immediately('{"status":"validate_error"}', $handlers);
}
$email = normalizeEmail($form_email);
$cpassword = md5($password);
require('config.php');
$db_params = $config['mysql']['users'];
$connection = create_mysql_connection($db_params, $handlers);
if(mysqli_query($connection, "INSERT INTO users (email, password, role) VALUES ('$email', '$cpassword', '$form_role')")){
    // можем использовать, потому что last_insert_id работает для курсора!
    $user_id = mysqli_insert_id($connection);
    if(!$user_id){
        end_script_immediately('{"status":"invalid"}', $handlers);
    }
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $form_email;
    $_SESSION['role'] = $form_role;
    $_SESSION['csrf-token'] = generate_csrf_token($row['id']);
    setcookie("XSRF-TOKEN", $_SESSION['csrf-token']);
    end_script_immediately(json_encode(['status'=>'ok', 'email'=>$form_email, 'role'=>$form_role]), $handlers);
}
end_script_immediately('{"status":"invalid", "msg":"used"}', $handlers);
