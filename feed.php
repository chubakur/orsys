<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id'])){
    die('{"status":"noauth"}');
}
require_once('config.php');
$db_params = $config['mysql']['orders'];
$connection = create_mysql_connection($db_params);
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    //валидация
    //обновляем
    $sql_query = "SELECT id, client, description, cost, date FROM orders WHERE performer IS NULL ORDER BY date DESC LIMIT 20";
    $result = mysql_db_query($db_params['schema'], $sql_query);
    $answers = [];
    while($row = mysql_fetch_assoc($result)){
        $answers[] = $row;
    }
    die(json_encode(['status'=>'ok', 'results'=>$answers]));
}
die('{"status":"invalid"}');