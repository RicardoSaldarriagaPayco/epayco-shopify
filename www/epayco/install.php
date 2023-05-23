<?php
$_API_KEY = getenv("api_key");
$_NGROK_URL = getenv("NGROK_URL");
$shop = $_GET['shop'];
$scopes = 'read_products,write_products,read_orders,write_orders,read_script_tags,write_script_tags,write_payment_gateways,write_payment_sessions,read_order_edits,write_order_edits,write_orders';
$redirect_uri = $_NGROK_URL . '/epayco/token.php';
$nonce = bin2hex( random_bytes( 12 ) );
$access_mode = 'per-user';

$oauth_url = 'https://'.$shop.'/admin/oauth/authorize?client_id='.$_API_KEY.'&scope='.$scopes.'&redirect_uri='.urlencode($redirect_uri).'&state='.$nonce.'&grant_options[]='.$access_mode;
header("Location: ". $oauth_url);
exit();