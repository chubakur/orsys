<?php
session_start();
//print_r(getallheaders());

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

function create_mysql_connection($config, $permanent=false){
    $connect = $permanent?"mysql_pconnect":"mysql_connect";
    $connection = $connect($config['host'], $config['user'], $config['password']) or die('{"status":"invalid", "error":"db"}');
    return $connection;
}