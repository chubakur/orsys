<?php
require_once('utils.php');

function create_event($db_params, $event){
    $db_events_params = $db_params;
    $handlers = [];
    $events_connection = create_mysql_connection($db_events_params, $handlers, false);
    if(!$events_connection) {
        return false;
    }
    $serialized_event = addcslashes(json_encode($event), '\\');
    $add_event_sql = "INSERT INTO events (event) VALUES ('$serialized_event')";
    $success = mysqli_query($events_connection, $add_event_sql);
    mysqli_close($success);
    return $success;
}

function get_events($db_params, $after_ts){
    $handlers = [];
    $events_connection = create_mysql_connection($db_params, $handlers, true, true);
    $get_sql = "SELECT date, event FROM events WHERE date > FROM_UNIXTIME($after_ts)";
    $result = mysqli_query($events_connection, $get_sql);
    $events = [];
    if($result){
        while(($row=mysqli_fetch_assoc($result)) != null){
            $row['event'] = json_decode($row['event']);
            $events[] = $row;
        }
    }
    return $events;
}

function remove_old_events($db_params){
    $handlers = [];
    $events_connection = create_mysql_connection($db_params, $handlers);
    $delete_sql = 'DELETE FROM events WHERE date < (NOW() - INTERVAL 1 MINUTE)';
    $success = mysqli_query($events_connection, $delete_sql);
    mysqli_close($events_connection);
    return $success;
}