<?php
include_once("includes/shopify.php");
include_once("./orderValidation.php");
include_once("includes/mysql_connect.php");
try{
$shopify = new Shopify();
$parameters = $_GET;
$is_confirm = false;
if (!empty($_REQUEST['x_ref_payco'])&& $_GET['confirmation'] == 1){
  $url_shop = trim($_REQUEST['x_extra3']);
  $id_order = trim($_REQUEST['x_extra1']);
  $confirmation = true;
  $x_cod_transaction_state = (int)trim($_REQUEST['x_cod_transaction_state']) ?? (int)trim($_REQUEST['x_cod_response']);
  $payment_id = $_REQUEST['x_extra2'];
  $ref_payco = $_REQUEST['x_ref_payco'];
  $x_ref_payco = $_REQUEST['x_ref_payco'];
  $transaction_id = $_REQUEST['x_transaction_id'];
  $x_amount = $_REQUEST['x_amount'];
  $x_currency_code = $_REQUEST['x_currency_code'];
  $x_signature = $_REQUEST['x_signature'];
  $validationData = $_REQUEST;
  $is_confirm = true;
}

if (!empty($_GET['ref_payco'])&& $_GET['response'] == 1){
    $url = 'https://secure.epayco.io/validation/v1/reference/'.$_GET['ref_payco'];
    $responseData = file_get_contents($url);
    $jsonData = @json_decode($responseData, true);
    $validationData = $jsonData['data'];
    $url_shop = $validationData['x_extra3'];
    $id_order = $validationData['x_extra1'];
    $confirmation = false;
    $x_cod_transaction_state = (int)trim($validationData['x_cod_transaction_state']) ?? (int)trim($validationData['x_cod_response']);
    $payment_id = $validationData['x_extra2'];
    $ref_payco = $_GET['ref_payco'];
    $x_ref_payco = $validationData['x_ref_payco'];
    $transaction_id = $validationData['x_transaction_id'];
    $x_signature = trim($validationData['x_signature']);
    $x_amount = $validationData['x_amount'];
    $x_currency_code = $validationData['x_currency_code'];
    //sleep(3);
}
$valid=false;
$resultEpaycoQuery = $mysql->query("SELECT * FROM epayco_db.epayco_shopify_credentials WHERE shop_url LIKE '%". $url_shop ."%' LIMIT 1");
$token=false;
$resultValidation = false;
if($resultEpaycoQuery->num_rows != 0) {
    $epayco_credential_data = $resultEpaycoQuery->fetch_assoc();
    if ($epayco_credential_data) {
        $epayco_customerid = $epayco_credential_data["p_cust_id"];
        $epayco_secretkey = $epayco_credential_data["p_key"];
        $signature = hash('sha256',
            trim($epayco_customerid).'^'
            .trim($epayco_secretkey).'^'
            .$x_ref_payco.'^'
            .$transaction_id.'^'
            .$x_amount.'^'
            .$x_currency_code
        );
        if($signature == $x_signature ){
            $valid=true;
        }

        $cod_transaction_state = array(1,3);
        
        $redis = new Redis();
        //Connecting to Redis
        $redis->connect('app_redis', 6379);
        $value = $redis->get($x_ref_payco);
        if(!$value){
            $redis->set($x_ref_payco, $x_cod_transaction_state);
            $redis->expire($x_ref_payco, 1800);
            if(!$is_confirm){
                if (!in_array($x_cod_transaction_state, $cod_transaction_state, $is_confirm)){
                    $resultValidation = true;
                }
            }
        }else{
            if (!in_array($x_cod_transaction_state, $cod_transaction_state)){
                //$token = $shopify->authentication($epayco_credential_data["public_key"],$epayco_credential_data["private_key"]);
                if(in_array(intval($value), $cod_transaction_state)){
                    $resultValidation = true;
                }
                if(!$is_confirm){
                    $resultValidation = true;
                }
            }
        }
    }
}

if($token){
    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$token
    );
    $data = array(
        "filter"=>[
            "referencePayco"=>$x_ref_payco
        ]
    );
    $response = $shopify->makeApifyRequest('/transaction',$headers,$data);
    if($response->success){
        $refInfo = isset($response->data->data) ? $response->data->data : false;
        if($refInfo){
            $orderStatus = $refInfo[0]->status;
            $resultValidation = true;
        }
    }
}
    //$resultValidation=(new DumpHTTPRequestToFile($validationData, $mysql, $is_confirm))->execute();

include_once("includes/check.php");
  if($valid){
      switch ($x_cod_transaction_state) {
          case 1: {
              $isValid = false;
              do{
                  $setOrder = $shopify->setResolvingOrder(["id" =>$id_order]);
                  $responseOrder = json_decode($setOrder['body'], true);
                  if( !empty($responseOrder['data'])){
                      $isValid = true;
                  }
              }while( !$isValid );
              $redirtUrl = $responseOrder['data']['paymentSessionResolve']['paymentSession']['nextAction']['context']['redirectUrl'];
              if($is_confirm){
                  echo $x_cod_transaction_state." ok";
              }else{
                  header("Location: ". $redirtUrl);
              }
          }break;
          case 3: {
              $isValid = false;
              do{
                  if(trim($data['x_franchise']) == 'PSE'  && !$this->comfirm) {
                      $setOrder = $shopify->setPendingOrder(["id" =>$id_order]);
                  }else{
                      if(trim($data['x_franchise']) != 'PSE') {
                          $setOrder = $shopify->setPendingOrder(["id" =>$id_order]);
                      }
                  }
                  $responseOrder = json_decode($setOrder['body'], true);
                  if( !empty($responseOrder['data'])){
                      $isValid = true;
                  }
              }while( !$isValid );
              $redirtUrl = $responseOrder['data']['paymentSessionPending']['paymentSession']['nextAction']['context']['redirectUrl'];
              if($is_confirm){
                  echo $x_cod_transaction_state." ok";
              }else{
                  header("Location: ". $redirtUrl);
              }
          }break;
          case 2:
          case 4:
          case 9:
          case 10:
          case 11:
              {
                  if($resultValidation){
                      $isValid = false;
                      do{
                          $setOrder = $shopify->setRejectingOrder(["id" =>$id_order]);
                          $responseOrder = json_decode($setOrder['body'], true);
                          if( !empty($responseOrder['data'])){
                              $isValid = true;
                          }
                      }while( !$isValid );
                      $redirtUrl = $responseOrder['data']['paymentSessionReject']['paymentSession']['nextAction']['context']['redirectUrl'];
                  }else{
                      $redirtUrl = "https://".$url_shop;
                  }
                  if($is_confirm){
                      echo $x_cod_transaction_state." ok";
                  }else{
                      header("Location: ". $redirtUrl);
                  }
              }break;
          default: {
              /*$shopify->processOrder($orderData['id'],$id_order,"authorization");
              $shopify->processOrder($orderData['id'],$id_order,"void");
              do{
                $data = $shopify->rest_api('/admin/api/2023-07/orders/'.$id_order.'/cancel.json', array("restock"=>true,"reason"=>"declined"), 'POST');
                $response = json_decode($data['body'], true);
              }while(empty($response['order']));*/
          }break;
      }
  }else{
      $isValid = false;
      do{
          $setOrder = $shopify->setRejectingOrder(["id" =>$id_order]);
          $responseOrder = json_decode($setOrder['body'], true);
          if( !empty($responseOrder['data'])){
              $isValid = true;
          }
      }while( !$isValid );
      $redirtUrl = $responseOrder['data']['paymentSessionReject']['paymentSession']['nextAction']['context']['redirectUrl'];
      header("Location: ". $redirtUrl);
  }
} catch (Exception $error) {
    exit($error->getMessage());
} catch (Error $e) {
    exit($e->getMessage());
}
    
    
