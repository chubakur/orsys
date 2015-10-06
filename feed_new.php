<?php
require('entry_point.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client'){
    end_script_immediately('{"status":"noauth"}');
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
    if($cost === false || $cost < 100 || $cost > 200000000){
        end_script_immediately('{"status":"validate_error"}');
    }
    $connection = create_mysql_connection($db_params);
    $user_id = $_SESSION['user_id'];
    mysqli_query($connection, "START TRANSACTION");
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
            mysqli_query($connection, "ROLLBACK");
            end_script_immediately('{"status":"invalid"}', $connection);
        }
        mysqli_query($connection, "COMMIT");
        end_script_immediately('{"status":"ok"}', $connection);
    }
    end_script_immediately('{"status":"invalid"}', $connection);
}
end_script_immediately('{"status":"error"}');