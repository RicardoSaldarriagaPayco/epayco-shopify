<?php
$query = "SELECT * FROM shop WHERE shop_url= '". $_GET['shop'] ."' LIMIT 1";
$result = $mysql->query($query);

if($result->num_rows < 1){
    header("Location: install.php?shop=". $_GET['shop']);
    exit();
}

$store_data = $result->fetch_assoc();

$shopify->set_url($_GET['shop']);
$shopify->set_token($store_data['access_token']);
$products = $shopify->rest_api('/admin/api/2023-01/shop.json', array(), 'GET');
$response = json_decode($products['body'], true);

if(array_key_exists('errors', $response)){
    header("Location: install.php?shop=". $_GET['shop']);
    exit();
}