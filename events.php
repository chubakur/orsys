<?php
require_once('session_mgr.php');

function create_event($db_params, $event){
    $db_events_params = $db_params;
    $events_connection = create_mysql_connection($db_events_params);
    $serialized_event = addcslashes(json_encode($event), '\\');
    $add_event_sql = "INSERT INTO events (event) VALUES ('$serialized_event')";
    return mysql_db_query($db_params['schema'], $add_event_sql, $events_connection);
}

function get_events($db_params, $after_ts){
    $events_connection = create_mysql_connection($db_params, true);
    $get_sql = "SELECT date, event FROM events WHERE date > FROM_UNIXTIME($after_ts)";
    $result = mysql_db_query($db_params['schema'], $get_sql, $events_connection);
    $events = [];
    if($result){
        while(($row=mysql_fetch_assoc($result)) != null){
            $row['event'] = json_decode($row['event']);
            $events[] = $row;
        }
    }
    return $events;
}

function remove_old_events($db_params){
    $events_connection = create_mysql_connection($db_params);
    $delete_sql = 'DELETE FROM events WHERE date < (NOW() - INTERVAL 1 MINUTE)';
    return mysql_db_query($db_params['schema'], $delete_sql, $events_connection);
}