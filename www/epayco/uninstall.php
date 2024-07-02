<?php
define('SHOPIFY_APP_SECRET','843f500bcd62f437fa1225f354ba430d');
include_once("includes/mysql_connect.php");

$rawdata = file_get_contents("php://input");
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$decoded = json_decode($rawdata, true);
$url_shop =$decoded["domain"];

function verify_webhook($data, $hmac_header)
{
    $calculated_hmac = base64_encode( hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, TRUE));
    return hash_equals($hmac_header, $calculated_hmac);
}
if(verify_webhook($rawdata, $hmac_header)){
    $queryPayment = "DELETE FROM epayco_shopify_credentials WHERE shop_url= '". $url_shop ."'";
    $result = $mysql->query($queryPayment);
    $queryShop = "DELETE FROM shopify_shop WHERE shop_url= '". $url_shop ."'";
    $resultShop = $mysql->query($queryShop);
    echo "ok";
}
//unlink('./orderRequest.txt');
