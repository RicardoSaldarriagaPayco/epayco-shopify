<?php
include_once("includes/mysql_connect.php");
include_once("includes/shopify.php");
$shopify = new Shopify();
$rawdata = file_get_contents("php://input");
$decoded = json_decode($rawdata, true);
$headers = getallheaders();
$url_shop =$headers["Shopify-Shop-Domain"];
try {
      $resultEpayco = $mysql->query("SELECT * FROM epayco_db.epayco_shopify_credentials WHERE shop_url = '". $url_shop ."' LIMIT 1");
      $queryParams = http_build_query(["extra1" => $decoded["id"],"shop" => $url_shop]);
      $decoded['shop'] = $url_shop;
      $decoded['extra'] = 'P38';
      $queryParams = http_build_query($decoded);
      if($resultEpayco->num_rows != 0){
        $epayco_credential_data = $resultEpayco->fetch_assoc();
        if($epayco_credential_data){
            $public_key = $epayco_credential_data["public_key"];
            $private_key = $epayco_credential_data["private_key"];
            $language_checkout = $epayco_credential_data["language_checkout"];
            $testMode = $decoded['test'] == '1' ? 'true' : 'false';
            $data_ico = 0;
            $data_hasCvv = "false";
            $product_subtotal = 0;
            $total = $decoded['amount'];
            $tax=0;
            if($language_checkout=='es'){
                $orderDescription='Compra en Shopify';
            }else{
                $orderDescription='Shopify purchase';
            }
            $countryCode=$decoded['customer']['billing_address']['country_code'];
            $referenceTransactionId = $decoded['id'];
            $orderNumber = $decoded['id'];
            $customer_name = $decoded['customer']['billing_address']['given_name']." ".$decoded['customer']['billing_address']['family_name'];
            $email = isset($decoded['customer']['email']) ? $decoded['customer']['email'] : '';
            $customer_address  = $decoded['customer']['billing_address']['line1'];
            $epaycoLanguage=$language_checkout;
            $formattedData = array(
                "response" => 'https://shop.epayco.io/response.php?response=1',
                "confirmation" => 'https://shop.epayco.io/response.php?confirmation=1',
                "publicKey" => $public_key,
                "privateKey" => $private_key,
                "currency" => in_array($decoded['currency'],array('USD','COP')) ? $decoded['currency']: 'USD',
                "amount" => strval(floatval($total)),
                "tax_base" => strval(floatval($product_subtotal)),
                "tax" => strval($tax),
                "description" =>$orderDescription,
                "name" =>$orderDescription,
                "country" => $countryCode,
                "test" => $testMode,
                //"invoice" => strval($decoded['id']),
                "extras_epayco" => ["extra5" => "p38"],
                "extra1" => $decoded['id'],
                "extra2" => $decoded['id'],
                "extra3" => $decoded['shop'],
                "extra5" => 'P38',
                "methodsDisable" => ["PP"],
                "name_billing" => $customer_name,
                "email_billing" => $email,
                "address_billing" => $customer_address,
                "lang" => $epaycoLanguage,
                "ico" => $data_ico,
                "hasCvv" => $data_hasCvv,
                "ip" => $shopify->getIp(),
            );
        }else{
            $formattedData=[];
        }
          $test=$decoded['test'];
          $id = $decoded['id'];
          $session=$shopify->MakeSessionPayment($public_key, $private_key, $formattedData);
          if ($session->success || $session['success']){
              $decoded=[];
              $decoded['shop'] = $url_shop;
              if ($session->data->sessionId) {
                  $decoded['session'] = $session->data->sessionId;
              } else {
                  $decoded['session'] = $session['data']['sessionId'];
              }
              $decoded['extra'] = 'P38';
              $decoded['test'] = $test;
              $decoded['id'] = $id;
              $queryParams = http_build_query($decoded);
          }
      }
      header('Content-Type: application/json; charset=utf-8');
      error_log('https://cms.epayco.io/shopify/checkout/payment' . "?" . $queryParams);
      $redirectUrl = array("redirect_url"=> 'https://cms.epayco.io/shopify/checkout/payment' . "?" . $queryParams);
      echo json_encode($redirectUrl);


} catch(Exception $e) {
    error_log("checkout_error ".$url_shop." ".$e->getMessage());
    echo $e->getMessage();
} catch (Error $err) {
    error_log("checkout_error ".$url_shop." ".$err->getMessage());
    echo $err->getMessage();
}
