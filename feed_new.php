<?php
require_once('session_mgr.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client'){
    end_script_immediately('{"status":"noauth"}');
}
require_once('config.php');
require_once('events.php');
$db_params = $config['mysql']['orders'];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description']) && isset($_POST['cost']) && $_SESSION['role'] == 'client'){
    //валидация
    //конвертирование
    $connection = create_mysql_connection($db_params);
    $description = textHtmlify($_POST['description']);
    $cost = $_POST['cost'];
    $user_id = $_SESSION['user_id'];
    //старт транзакции
    $sql_query = "INSERT INTO orders (client, description, cost) VALUES ($user_id, '$description', $cost)";
    if(mysqli_query($connection, $sql_query)){
        $id = mysqli_insert_id($connection);
        $success = create_event($config['mysql']['events'], [
            'type'=> 'new',
            'description'=> $description,
            'cost'=> $cost,
            'client'=> $user_id,
            'client_email'=> $_SESSION['email'],
            'id'=> $id
        ]);
        if(!$success){
            //откат
            end_script_immediately('{"status":"invalid"}', $connection);
        }
        //коммит
        end_script_immediately('{"status":"ok"}', $connection);
    }
    end_script_immediately('{"status":"error"}', $connection);
}
end_script_immediately('{"status":"invalid"}');