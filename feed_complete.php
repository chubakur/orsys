<?php
require('entry_point.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'performer'){
    end_script_immediately('{"status":"noauth"}');
}
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['order_id']) || !filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT)){
    end_script_immediately('{"status":"error", "msg": "args"}');
}
$id = $_POST['order_id'];
$user_id = $_SESSION['user_id'];
require('config.php');
require_once('utils.php');
// берем цену и исполнителя
$orders_connection = create_mysql_connection($config['mysql']['orders']);
$get_sql = "SELECT cost, performer FROM orders WHERE id=$id";
$result = mysqli_query($orders_connection, $get_sql);
if(!$result){
    end_script_immediately('{"status":"invalid"}', $orders_connection);
}
$row = mysqli_fetch_assoc($result);
// проверяем чтобы она не была сделана
if(!$row || $row['performer'] != null){
    end_script_immediately('{"status":"error", "msg":"done_already"}', $orders_connection);
}
$cost = $row['cost'];
$users_connection = create_mysql_connection($config['mysql']['users']);
mysqli_begin_transaction($users_connection);
$get_bill = "SELECT bill FROM users WHERE id=$user_id FOR UPDATE";
$result = mysqli_query($users_connection, $get_bill);
if(!$result || ($row=mysqli_fetch_assoc($result)) == null){
    end_script_immediately('{"status":"invalid"}', $orders_connection, $users_connection);
}
$bill = $row['bill'];
if($bill >= 400000000){
    mysqli_rollback($users_connection);
    end_script_immediately('{"status":"error", "msg":"overflow"}', $orders_connection, $users_connection);
}
// атомарное обновление с одновременной проверкой. Она гарантирует нам отсутствие перетирания прошлого исполнителя.
$update_sql = "UPDATE orders SET performer=$user_id WHERE id=$id AND performer is NULL";
$update_error = mysqli_query($orders_connection, $update_sql);
if(!$update_error){
    mysqli_rollback($users_connection);
    end_script_immediately('{"status":"invalid"}', $orders_connection, $users_connection);
}
// здесь affected_rows показывает успех обновления. Если все ок, то 1. Если нас кто-то обогнал и выполнил его до нас - 0.
$is_updated = mysqli_affected_rows($orders_connection);
if(!$is_updated){
    mysqli_rollback($users_connection);
    end_script_immediately('{"status":"error", "msg":"ready"}', $orders_connection, $users_connection);
}

$pay_sql = "UPDATE users SET bill=bill+$cost WHERE id=$user_id";
$result = mysqli_query($users_connection, $pay_sql);
require('events.php');
if($result){
    $success = create_event($config['mysql']['events'], [
        'type'=> 'done',
        'order_id'=> $id
    ]);
    mysqli_commit($users_connection);
    end_script_immediately('{"status":"ok"}', $orders_connection, $users_connection);
}