<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client'){
    die('{"status":"noauth"}');
}
require_once('config.php');
$db_params = $config['mysql']['orders'];
$connection = create_mysql_connection($db_params);
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description']) && isset($_POST['cost']) && $_SESSION['role'] == 'client'){
    //валидация
    //конвертирование
    $description = textHtmlify($_POST['description']);
    $cost = $_POST['cost'];
    $user_id = $_SESSION['user_id'];
    $sql_query = "INSERT INTO orders (client, description, cost) VALUES ($user_id, '$description', $cost)";
    if(mysql_db_query($db_params['schema'], $sql_query)){
        die('{"status":"ok"}');
    }
    die('{"status":"error"}');
}