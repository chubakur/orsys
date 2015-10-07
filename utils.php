<?php

function emailIsValid($email){
    $pattern = "/^[A-z0-9_\+\.\-'`]+@[A-z0-9_]+\.[A-z0-9\._]+$/";
    return count($email) <= 255 && preg_match($pattern, $email);
}

function normalizeEmail($email){
    return strtolower(addcslashes($email, '\'"'));
}

function textHtmlify($text){
    return str_replace(PHP_EOL, '<br/>', htmlentities($text, ENT_HTML401 | ENT_QUOTES));
}

function create_mysql_connection($config, &$db_conn_handlers, $critical=true, $permanent=false){
    $host = ($permanent?'p:':'').$config['host'];
    $connection = mysqli_connect($host, $config['user'], $config['password'], $config['schema'], $config['port']);
    if($connection){
        $db_conn_handlers[] = $connection;
    }elseif($critical){
        end_script_immediately('{"status":"invalid", "error":"db"}', $db_conn_handlers);
    }
    return $connection;
}

function generate_csrf_token($salt){
    return md5(time().$salt);
}

function end_script_immediately($message, $db_conn_handlers){
    foreach($db_conn_handlers as $connection){
        mysqli_close($connection);
    }
    die($message);
}

function get_percents($value, $percents){
    return floor(($value * $percents)/100);
}