<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'performer'){
    die('{"status":"noauth"}');
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['order_id']) || !filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT)){
    die('{"status":"invalid", "msg": "args"}');
}
$id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];
require_once('config.php');
$db_orders_params = $config['mysql']['orders'];
$orders_connection = create_mysql_connection($db_orders_params);
$get_sql = "SELECT cost, performer FROM orders WHERE id=$id";
$result = mysql_db_query($db_orders_params['schema'], $get_sql, $orders_connection);
if(!$result){
    die('{"status":"invalid"}');
}
$row = mysql_fetch_assoc($result);
if(!$row || $row['performer'] != null){
    die('{"status":"error", "msg":"done_already"}');
}
$cost = $row['cost'];
$update_sql = "UPDATE orders SET performer=$user_id WHERE id=$id AND performer is NULL";
$update_error = mysql_query($update_sql, $orders_connection);
if(!$update_error){
    die('{"status":"invalid"}');
}
$is_updated = mysql_affected_rows($orders_connection);
if(!$is_updated){
    die('{"status":"error"}');
}
$db_users_params = $config['mysql']['users'];
$users_connection = create_mysql_connection($db_users_params);
$pay_sql = "UPDATE users SET bill=bill+$cost WHERE id=$user_id";
$result = mysql_db_query($db_users_params['schema'], $pay_sql, $users_connection);
if($result){
    die('{"status":"ok"}');
}
die('{"status":"error"}');