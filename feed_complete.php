<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'performer'){
    end_script_immediately('{"status":"noauth"}');
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['order_id']) || !filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT)){
    end_script_immediately('{"status":"invalid", "msg": "args"}');
}
$id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];
require_once('config.php');
$db_orders_params = $config['mysql']['orders'];
$orders_connection = create_mysql_connection($db_orders_params);
$get_sql = "SELECT cost, performer FROM orders WHERE id=$id";
$result = mysqli_query($orders_connection, $get_sql);
if(!$result){
    end_script_immediately('{"status":"invalid"}', $orders_connection);
}
$row = mysqli_fetch_assoc($result);
if(!$row || $row['performer'] != null){
    end_script_immediately('{"status":"error", "msg":"done_already"}', $orders_connection);
}
$cost = $row['cost'];
$update_sql = "UPDATE orders SET performer=$user_id WHERE id=$id AND performer is NULL";
$update_error = mysqli_query($orders_connection, $update_sql);
if(!$update_error){
    end_script_immediately('{"status":"invalid"}', $orders_connection);
}
$is_updated = mysqli_affected_rows($orders_connection);
if(!$is_updated){
    end_script_immediately('{"status":"error", "msg":"ready"}', $orders_connection);
}
$db_users_params = $config['mysql']['users'];
$users_connection = create_mysql_connection($db_users_params);
$pay_sql = "UPDATE users SET bill=bill+$cost WHERE id=$user_id";
$result = mysqli_query($users_connection, $pay_sql);
require_once('events.php');
if($result){
    $success = create_event($config['mysql']['events'], [
        'type'=> 'done',
        'order_id'=> $id
    ]);
    end_script_immediately('{"status":"ok"}', $orders_connection);
}
end_script_immediately('{"status":"error"}', $orders_connection);