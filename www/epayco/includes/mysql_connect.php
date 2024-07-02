<?php
try{
$server = getenv("DB_SERVER");
$username = getenv("DB_USERNAME");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");

$mysql = mysqli_connect('new-prepo-cluster-cluster.cluster-ctl6s80ppnva.us-east-1.rds.amazonaws.com', 'dev-local', 'gcb!rqb1rng7cvy8VEJ', 'epayco_db');
if(!$mysql){
    die("Error in conection ".mysqli_connect_error());
}
}catch(Exception $e) {
    error_log("conection " . $e->getMessage());
    echo $e->getMessage();
}catch (Error $err) {
    error_log("conection " . $err->getMessage());
    echo $err->getMessage();
}