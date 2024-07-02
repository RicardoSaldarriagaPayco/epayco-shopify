<?php
include_once("includes/mysql_connect.php");
include_once("includes/shopify.php");
$shopify = new Shopify();
$api_key = Shopify::api_key;
$secret_key = Shopify::secret_key;
$parameters = $_GET;

$shop_url = $parameters['shop'];
$hmac = $parameters['hmac'];
$parameters = array_diff_key($parameters, array('hmac' => ''));
ksort($parameters);

$new_hmac = hash_hmac('sha256', http_build_query($parameters), $secret_key);
//$shopify->checkToken($shop_url,$hmac,$new_hmac,$parameters);
if( hash_equals($hmac, $new_hmac) ){
    do{
        $result = $shopify->getAssessToken($shop_url, $api_key, $secret_key, $parameters);
    }while( empty($result['access_token']) );
    if(is_null($result)){
        echo "no se puede acceder a los datos";
        die();
    }
    $access_token = $result['access_token'];
    // Show the access token (don't do this in production!)

    $shop_result = $mysql->query("SELECT * FROM shopify_shop WHERE shop_url = '". $shop_url ."' LIMIT 1");
    if($shop_result->num_rows < 1){
        $query = "INSERT INTO shopify_shop (shop_url, access_token, hmac, install_date, is_active) VALUES ('".$shop_url."', '".$result['access_token']."', '".$hmac."', NOW(), 1) ON DUPLICATE KEY UPDATE access_token='".$result['access_token']."' ";
        if($mysql->query($query)){
            $shopify->set_url($shop_url);
            $shopify->set_token($access_token);
            $shopify->set_apiKey(Shopify::api_key);
            $shopify->redirectPaymentOption('false');
            $redirectUrl=$shopify->returnPaymentAdmin();
            //header("Location: https://". $shop_url. "/admin/apps");
            header("Location: ". $redirectUrl);
            exit();
        }
    }else{
        $querySettings = "UPDATE shopify_shop SET access_token ='".$result['access_token']."',hmac ='".$hmac."',install_date = NOW(), is_active = 2 WHERE shop_url ='".$shop_url."' ";
        if($mysql->query($querySettings)){
            $shopify->set_url($shop_url);
            $shopify->set_token($access_token);
            $shopify->set_apiKey(Shopify::api_key);
            $shopify->redirectPaymentOption('false');
            $redirectUrl=$shopify->returnPaymentAdmin();
            //header("Location: https://". $shop_url. "/admin/apps");
            header("Location: ". $redirectUrl);
            exit();
        }else{
            echo "error en la insercion de datos";
            die();
        }
    }

}else{
    echo 'this is no comming from shopify';
    die();
}
