<?php
try {
define('SHOPIFY_APP_SECRET','843f500bcd62f437fa1225f354ba430d');
$rawdata = file_get_contents("php://input");
$hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
$decoded = json_decode($rawdata, true);

    function verify_webhook($data, $hmac_header)
    {
        $calculated_hmac = base64_encode( hash_hmac('sha256', $data, SHOPIFY_APP_SECRET, TRUE));
        return hash_equals($hmac_header, $calculated_hmac);
    }
    
    if(verify_webhook($rawdata, $hmac_header)){
        header("HTTP/1.1 200 OK");
        echo "ok";
    }else{
        header("HTTP/1.1 401 Unauthorized");
        echo "error";
    }   
} catch (\Throwable $th) {
    header("HTTP/1.1 401 Unauthorized");
    echo $th->getMessage();
}

