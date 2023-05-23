<?php
include_once("includes/mysql_connect.php");
$api_key = getenv("api_key");
$secret_key = getenv("secret_key");
$parameters = $_GET;

$shop_url = $parameters['shop'];
$hmac = $parameters['hmac'];
$parameters = array_diff_key($parameters, array('hmac' => ''));
ksort($parameters);

$new_hmac = hash_hmac('sha256', http_build_query($parameters), $secret_key);

if( hash_equals($hmac, $new_hmac) ){
    $access_token_endpoint = 'https://'. $shop_url . '/admin/oauth/access_token';
    // Set variables for our request
    $query = array(
        "client_id" => $api_key, // Your API key
        "client_secret" => $secret_key, // Your app credentials (secret key)
        "code" => $parameters['code'] // Grab the access key from the URL
    );
    // Configure curl client and execute request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $access_token_endpoint);
    curl_setopt($ch, CURLOPT_POST, count($query));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
    $result = curl_exec($ch);
    curl_close($ch);

    // Store the access token
    $result = json_decode($result, true);
    $access_token = $result['access_token'];
    // Show the access token (don't do this in production!)
    $query = "INSERT INTO shop (shop_url, access_token, hmac, install_date) VALUES ('".$shop_url."', '".$result['access_token']."', '".$hmac."', NOW()) ON DUPLICATE KEY UPDATE access_token='".$result['access_token']."' ";

    if($mysql->query($query)){
        header("Location: https://". $shop_url. "/admin/apps");
        exit();
    }else{
        echo "error en la insercion de datos";
    }

}else{
    echo 'this is no comming from shopify';
}