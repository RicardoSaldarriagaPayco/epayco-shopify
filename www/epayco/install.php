<?php
$_API_KEY = '6ce386c42826963b2ca0cb879d0fd258';
$_NGROK_URL = 'https://72ac-2800-e2-580-30e-eb44-6b59-4724-1d8e.ngrok-free.app';
$shop = $_GET['shop'];
$scopes = 'read_products,write_products,read_orders,write_orders,read_script_tags,write_script_tags,write_payment_gateways,write_payment_sessions,read_order_edits,write_order_edits,write_orders';
$redirect_uri = $_NGROK_URL . '/elena/token.php';
$nonce = bin2hex( random_bytes( 12 ) );
$access_mode = 'per-user';

$oauth_url = 'https://'.$shop.'/admin/oauth/authorize?client_id='.$_API_KEY.'&scope='.$scopes.'&redirect_uri='.urlencode($redirect_uri).'&state='.$nonce.'&grant_options[]='.$access_mode;
header("Location: ". $oauth_url);
exit();