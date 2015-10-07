<?php
require('entry_point.php');
require_once('utils.php');
require('config.php');
$db_params = $config['mysql']['users'];
$handlers = [];
if(isset($_SESSION['user_id']) && isset($_SESSION['email']) && isset($_SESSION['role'])){
    $connection = create_mysql_connection($db_params, $handlers);
    $id = $_SESSION['user_id'];
    $bill_sql = "SELECT bill FROM users WHERE id=$id";
    $result = mysqli_query($connection, $bill_sql);
    if(!$result){
        end_script_immediately('{"status":"invalid", "msg":"bill"}', $handlers);
    }
    $row = mysqli_fetch_assoc($result);
    if(!$row){
        end_script_immediately('{"status":"invalid", "msg":"bill"}', $handlers);
    }
    $bill = $row['bill'];
    end_script_immediately(json_encode(['status'=> 'ok', 'email'=> $_SESSION['email'], 'role'=> $_SESSION['role'], 'bill'=>$bill]), $handlers);
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['email']) || !isset($_POST['password'])){
    end_script_immediately('{"status":"invalid", "msg":"api_error"}', $handlers);
}
//ВАЛИДАЦИЯ!!!!
$form_email = $_POST['email'];
if(!emailIsValid($form_email)){
    end_script_immediately('{"status":"validate_error"}', $handlers);
}
$email = normalizeEmail($form_email);
$cpassword = md5($_POST['password']);
$connection = create_mysql_connection($db_params, $handlers);
$sql_query = "SELECT id, role, bill FROM users WHERE email='$email' AND password='$cpassword'";
$answer = mysqli_query($connection, $sql_query);
if(!$answer){
    end_script_immediately('{"status":"invalid", "msg":"api_error"}', $handlers);
}
$row = mysqli_fetch_assoc($answer);
if(!$row){
    end_script_immediately('{"status":"invalid", "msg":"incorrect"}', $handlers);
}
$_SESSION['user_id'] = $row['id'];
$_SESSION['email'] = $form_email;
$_SESSION['role'] = $row['role'];
$_SESSION['csrf-token'] = generate_csrf_token($row['id']);
setcookie("XSRF-TOKEN", $_SESSION['csrf-token']);
end_script_immediately(json_encode(['status'=>'ok', 'email'=>$email, 'role'=>$row['role'], 'bill'=>$row['bill']]), $handlers);