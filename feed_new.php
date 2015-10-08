<?php
require('entry_point.php');
$handlers = [];
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client'){
    end_script_immediately('{"status":"noauth"}', $handlers);
}
require('config.php');
require('events.php');
require_once('utils.php');
$db_params = $config['mysql']['orders'];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description']) && isset($_POST['cost']) && $_SESSION['role'] == 'client'){
    //валидация
    //конвертирование
    $description = textHtmlify($_POST['description']);
    $cost = filter_input(INPUT_POST, 'cost', FILTER_VALIDATE_INT);
    // стоимость должна быть не меньше рубля и не более двух миллионов
    if($cost === false || $cost < 100 || $cost > 200000000){
        end_script_immediately('{"status":"validate_error"}', $handlers);
    }
    $connection = create_mysql_connection($db_params, $handlers);
    $user_id = $_SESSION['user_id'];
    mysqli_begin_transaction($connection);
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
            mysqli_rollback($connection);
            end_script_immediately('{"status":"invalid"}', $handlers);
        }
        mysqli_commit($connection);
        end_script_immediately('{"status":"ok"}', $handlers);
    }
    end_script_immediately('{"status":"invalid"}', $handlers);
}
end_script_immediately('{"status":"error"}', $handlers);