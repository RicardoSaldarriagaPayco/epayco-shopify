<?php
try{
    $query = "SELECT * FROM shopify_shop WHERE shop_url = '". $url_shop ."' LIMIT 1";
    $result = $mysql->query($query);
    $store_data = $result->fetch_assoc();
    $shopify->set_url($url_shop);
    $shopify->set_token($store_data['access_token']);
    $shopify->set_apiKey(Shopify::api_key);
}catch(Exception $e) {
    error_log("check_error " . $e->getMessage());
    echo $e->getMessage();
}catch (Error $err) {
    error_log("check_error " . $err->getMessage());
    echo $err->getMessage();
}
