<?php
require_once('session_mgr.php');
if(isset($_SESSION['user_id'])){
    die("NOT NEED TO REGISTER");
}
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    die("INVALID USAGE");
}
if(!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['role'])){
    print_r($_POST);
}
die("OK");