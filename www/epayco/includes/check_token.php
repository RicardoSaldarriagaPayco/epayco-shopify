<?php
try{
    $query = "SELECT * FROM shopify_shop WHERE shop_url = '". $url_shop ."' LIMIT 1";
    $result = $mysql->query($query);
    $shopify->set_url($url_shop);
    $shopify->set_apiKey(Shopify::api_key);
    if($result){
        /*$parameters = $_GET;
        if(isset($parameters['hmac'])){
            $hmac = $parameters['hmac'];
            $parameters = array_diff_key($parameters, array('hmac' => ''));
            ksort($parameters);
            $new_hmac = hash_hmac('sha256', http_build_query($parameters), Shopify::secret_key);
            $shopify->checkToken($url_shop,$hmac,$new_hmac,$parameters);
        }*/
        if($result->num_rows < 1){
            header("Location: install.php?shop=". $url_shop);
            exit();
        }else{
            $store_data = $result->fetch_assoc();
            $shopify->set_token($store_data['access_token']);
            $shotInfo = $shopify->rest_api('/admin/api/2024-01/shop.json', [], 'GET');
            if(json_decode($shotInfo['body'])->errors){
                header("Location: install.php?shop=". $url_shop);
                exit();
            }
        }
    }
    $credentialsQuery = "SELECT * FROM epayco_shopify_credentials WHERE shop_url = '". $url_shop ."' LIMIT 1";
    $credentialsResult = $mysql->query($credentialsQuery);
    if($credentialsResult){
        $credentialsData = $credentialsResult->fetch_assoc();
    }




}catch(Exception $e) {
    error_log("check_token " . $e->getMessage());
    echo $e->getMessage();
}catch (Error $err) {
    error_log("check_token " . $err->getMessage());
    echo $err->getMessage();
}
