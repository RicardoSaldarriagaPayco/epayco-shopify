<?php
include_once("includes/shopify.php");
$_API_KEY = Shopify::api_key;
$_NGROK_URL = "https://shop.epayco.io";
$shop = $_GET['shop'];
$scopes = 'write_payment_gateways,write_payment_sessions';
$redirect_uri = $_NGROK_URL . '/token.php';
$nonce = bin2hex( random_bytes( 12 ) );
$access_mode = '';

$oauth_url = 'https://'.$shop.'/admin/oauth/authorize?client_id='.$_API_KEY.'&scope='.$scopes.'&redirect_uri='.urlencode($redirect_uri).'&state='.$nonce.'&grant_options[]='.$access_mode;
header("Location: ". $oauth_url);
exit();
