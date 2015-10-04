<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id'])){
    die('{"status":"noauth"}');
}
session_write_close();
require_once('config.php');
require_once('events.php');
$db_params = $config['mysql']['orders'];
$connection = create_mysql_connection($db_params);
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    //валидация
    //обновляем
    if(isset($_GET['minid']) && filter_input(INPUT_GET, 'minid', FILTER_VALIDATE_INT)){
        $minid = $_GET['minid'];
        $sql_query = "SELECT id, client, description, cost, date FROM orders WHERE performer IS NULL AND id < $minid ORDER BY date DESC LIMIT 20";
    }else{
        $sql_query = "SELECT id, client, description, cost, date FROM orders WHERE performer IS NULL ORDER BY date DESC LIMIT 20";
    }
    $result = mysql_db_query($db_params['schema'], $sql_query);
    $answers = [];
    while($row = mysql_fetch_assoc($result)){
        $answers[] = $row;
    }
    die(json_encode(['status'=>'ok', 'ts'=> time(), 'results'=>$answers]));
}elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ts']) && filter_input(INPUT_POST, 'ts', FILTER_VALIDATE_INT)){
    $db_events_params = $config['mysql']['events'];
    $start_time = time();
    $events_query = 0;
    while((time() - $start_time) < 25){
        $events = get_events($db_events_params, $_POST['ts']);
        if(count($events) > 0){
            die(json_encode(['ts'=>time(), 'events'=>$events]));
        }
        sleep(1);
    }
    die(json_encode(['ts'=>time(), 'events'=>[]]));
}
die('{"status":"invalid"}');