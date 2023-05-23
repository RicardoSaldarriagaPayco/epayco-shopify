<?php
include_once("app.php");
$server = getenv("DB_SERVER");
$username = getenv("DB_USERNAME");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");

$mysql = mysqli_connect($server, $username, $password, $database);
if(!$mysql){
    die("Error in conection ".mysqli_connect_error());
}