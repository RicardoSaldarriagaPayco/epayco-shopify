<?php
$server = 'db';
$username = 'root';
$password = 'test';
$database = 'elena';

$mysql = mysqli_connect($server, $username, $password, $database);
if(!$mysql){
    die("Error in conection ".mysqli_connect_error());
}