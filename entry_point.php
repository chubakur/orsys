<?php
session_start();

if(isset($_SESSION['csrf-token'])){
    $request_headers = getallheaders();
    if(!isset($request_headers['X-XSRF-TOKEN']) || $request_headers['X-XSRF-TOKEN'] != $_SESSION['csrf-token'])
        die('{"status":"invalid","msg":"xsrf"}');
}