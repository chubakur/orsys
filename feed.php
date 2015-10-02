<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id'])){
    die('{"status":"invalid"}');
}
require_once('config.php');
$db_params = $config['mysql']['orders'];
$connection = mysql_connect($db_params[0], $db_params[2], $db_params[3]) or die('{"status":"invalid", "error":"db"}');
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description']) && isset($_POST['cost']) && $_SESSION['role'] == 'client'){
    //валидация
    //конвертирование
    $description = textHtmlify($_POST['description']);
    $cost = $_POST['cost'];
    $user_id = $_SESSION['user_id'];
    $sql_query = "INSERT INTO orders (client, description, cost) VALUES ($user_id, '$description', $cost)";
    if(mysql_db_query($db_params[1], $sql_query)){
        die('{"status":"ok"}');
    }
    die('{"status":"error"}');
}elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
    //валидация
    //обновляем
    $sql_query = "SELECT id, client, description, cost, date FROM orders WHERE performer IS NULL ORDER BY date DESC LIMIT 20";
    $result = mysql_db_query($db_params[1], $sql_query);
    $answers = [];
    while($row = mysql_fetch_assoc($result)){
        $answers[] = $row;
    }
    die(json_encode(['status'=>'ok', 'results'=>$answers]));
}elseif($_SERVER['REQUEST_METHOD'] == 'PUT' && $_SESSION['role'] == 'performer'){
    //валидация
    //обновление
}
die('{"status":"invalid"}');