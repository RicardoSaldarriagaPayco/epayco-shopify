<?php
include_once("includes/mysql_connect.php");
class Shopify {
    public $shop_url;
    public $access_token;
    public $apikey;
    const BASE_URL_APIFY = "https://apify.epayco.io";
    const api_key = "11ec2bbbd5bb9b2b62749b20cc8d675e";
    const secret_key = "843f500bcd62f437fa1225f354ba430d";
    public function set_url($url){
        $this->shop_url = $url;
    }
    public function set_token($token){
        $this->access_token = $token;
    }
    public function get_url(){
        return $this->shop_url;
    }

    public function get_token(){
        return $this->access_token;
    }
    public function set_apiKey($key)
    {
        $this->apikey = $key;
    }
    public function get_apikey(){
        return $this->apikey;
    }

    public function rest_api($api_endpoint, $query = array(), $method = 'GET'){
        $url = 'https://' .$this->shop_url. $api_endpoint;
        if( in_array( $method, array('GET','DELETE') ) && !is_null($query) ){
            $url = $url . '?' . http_build_query( $query );
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = '';
        if( !is_null($this->access_token) ){
            $headers[] = "X-Shopify-Access-Token: " . $this->access_token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $headers[] = "X-Shopify-Shop-Api-Call-Limit: 40/40";
        $headers[] = "Retry-After: 1.0";
        if( $method != 'GET' && in_array($method, array('POST','PUT') ) ){
            if( is_array($query) ) $query = http_build_query( $query );
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
        $response = curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);
        if ($error){
            return $error_msg;
        }else{
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
            $headers = array();
            $headers_content = explode("\n", $response[0]);
            $headers['status'] = $headers_content[0];
            array_shift($headers_content);
            foreach ($headers_content as $content){
                $data = explode(":", $content);
                $headers[trim($data[0])] = trim($data[1]);
            }
            return array('headers' => $headers, 'body' => $response[1]);
        }
    }

    public function graph_ql($query = array(), $url = '/admin/api/2024-01/graphql.json' ){
        $url = 'https://' . $this->get_url() . $url;
        $curl = curl_init($url);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $headers[] = "";
        $headers[] = "Content-Type: application/json";
        $headers[] = "X-Shopify-Shop-Api-Call-Limit: 40/40";
        $headers[] = "Retry-After: 1.0";
        if($this->access_token) $headers[] = 'X-Shopify-Access-Token: '. $this->access_token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query));
        curl_setopt($curl, CURLOPT_POST, true);

        $response = curl_exec($curl);
        $error = curl_errno($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);
        if ($error){
            return $error_msg;
        }else{
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
            $headers = array();
            $headers_content = explode("\n", $response[0]);
            $headers['status'] = $headers_content[0];
            array_shift($headers_content);
            foreach ($headers_content as $content){
                $data = explode(":", $content);
                $headers[trim($data[0])] = trim($data[1]);
            }
            return array('headers' => $headers, 'body' => $response[1]);
        }
    }

    
    public function redirectPaymentOption($status){
        $mutations = array("query"=>sprintf('
            mutation  {
              paymentsAppConfigure(ready:%s, externalHandle:"Payco") {
                paymentsAppConfiguration {
                 externalHandle
                  ready
                }
                userErrors {
                  field
                  message
                }
              }
            }',$status));
        return $this->graph_ql($mutations, "/payments_apps/api/2024-01/graphql.json");
    }

    public function returnPaymentAdmin(){
        return "https://".$this->shop_url."/services/payments_partners/gateways/".Shopify::api_key."/settings";
    }

    public function processPayment($mutation){
        return $this->graph_ql($mutation, "/payments_apps/api/2024-01/graphql.json");
    }


    public function processOrder($id,$id_order,$kind){
        $tansaction = [
          "transaction" => [
            "parent_id" => $id,
            "source" => "external",
            "kind" => $kind,
            "gateway"=> 'epayco',
            "status" => "success"
          ]
        ];
        do{
          $data_send = $this->rest_api('/admin/api/2024-01/orders/'.$id_order.'/transactions.json', $tansaction, 'POST');
          $response_send = json_decode($data_send['body'], true);
          }while( empty($response_send['transaction']) );
      }

      public function createOrder($order){
        $curl = curl_init();
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://' . $this->get_url() . '/admin/api/2024-01/orders.json',
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$order,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'X-Shopify-Access-Token:'. $this->access_token,
                'X-Shopify-Shop-Api-Call-Limit: 40/40',
                'Retry-After: 1.0'
            ),
        ));
        $response = curl_exec($curl);
        $error = curl_errno($curl);
        $error_msg = curl_error($curl);
        curl_close($curl);
        if ($error){
          error_log(print_r($error_msg, TRUE));
          return $error_msg;
        }else{
            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
            $data = array();
            $data_content = explode("\n", $response[0]);
            $data['body'] = $data_content[0];
            array_shift($data_content);
            foreach ($data_content as $content){
                $data = explode(":", $content);
                $data[trim($data[0])] = trim($data[1]);
            }
            return array($data);
        }
    }

    public function setOrder($response, $code, $ref_payco){
        $order_datos = $response;
        if($code == 1){
            $order_datos['order']['financial_status']="paid";
        }
        if($code == 3){
            $order_datos['order']['financial_status']="pending";
        }
        $fecha = new DateTime('now');
        $datetime = $fecha->format(DateTime::ISO8601);
        $order_datos['order']['inventory_behaviour'] = "decrement_obeying_policy";
        $order_datos['order']['name'] = $order_datos['order']['name']."-1";
        $order_datos['order']['fulfillment_status']="restocked";
        unset($order_datos['order']['id']);
        unset($order_datos['order']['admin_graphql_api_id']);
        unset($order_datos['order']['number']);
        unset($order_datos['order']['cancel_reason']);
        unset($order_datos['order']['cancelled_at']);
        unset($order_datos['order']['order_number']);
        unset($order_datos['order']['reference']);
        $order_datos['order']['source_name'] = "instagram";
        unset($order_datos['order']['tags']);
        $order_datos['order']['tax_lines'] = [];
        $order_datos['order']['note'] =  "ref_payco: ".$ref_payco;
        $order_datos['order']['created_at'] = $datetime;
        $order_datos['order']['processed_at'] = $datetime;
        $order_datos['order']['updated_at'] = $datetime;
        do{  
            $data = $this->createOrder(json_encode($order_datos));
            $response = json_decode($data[0]['body'], true);
        }while(empty($response['order']));
    }

    public function getAssessToken($shop_url, $api_key, $secret_key, $parameters){
        $access_token_endpoint = 'https://'. $shop_url . '/admin/oauth/access_token';
        // Set variables for our request
        $query = array(
            "client_id" => $api_key, // Your API key
            "client_secret" => $secret_key, // Your app credentials (secret key)
            "code" => $parameters['code'] // Grab the access key from the URL
        );
        // Configure curl client and execute request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $access_token_endpoint);
        curl_setopt($ch, CURLOPT_POST, count($query));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
        $result = curl_exec($ch);
        curl_close($ch);

        // Store the access token
        return json_decode($result, true);
    }

    public function setPendingOrder($data){
        $fecha = new \DateTime('now');
        $agregarDias = 1;
        $fecha->add(new \DateInterval("P{$agregarDias}D"));
        $datetime = $fecha->format(\DateTime::ISO8601);
        $id = "gid://shopify/PaymentSession/".$data["id"];
        $mutation = array("query" => 'mutation {
            paymentSessionPending(
                id: "'. $id .'", 
                pendingExpiresAt: "'.$datetime.'",
                reason:BUYER_ACTION_REQUIRED
                )
            {
                paymentSession {
                  id
                  state {
                    ... on PaymentSessionStatePending {
                      reason
                    }
                  }
                  nextAction {
                    action
                    context {
                      ... on PaymentSessionActionsRedirect {
                        redirectUrl
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
            }
        }');
        return $this->graph_ql($mutation, "/payments_apps/api/2024-01/graphql.json");
    }

    public function setResolvingOrder($data){
        $id = "gid://shopify/PaymentSession/".$data["id"];
        $mutation = array("query" => 'mutation {
            paymentSessionResolve(
                id: "'. $id .'"
                )
            {
                paymentSession {
                  id
                  state {
                    ... on PaymentSessionStateResolved {
                        code
                    }
                  }
                  nextAction {
                    action
                    context {
                      ... on PaymentSessionActionsRedirect {
                        redirectUrl
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
            }
        }');
        return $this->graph_ql($mutation, "/payments_apps/api/2024-01/graphql.json");
    }

    public function setRejectingOrder($data){
        $id = "gid://shopify/PaymentSession/".$data["id"];
        $mutation = array("query" => 'mutation {
            paymentSessionReject(
                id: "'. $id .'",
                reason:{code:RISKY}
                )
            {
                paymentSession {
                  id
                  state {
                    ... on PaymentSessionStateRejected  {
                        code
                    }
                  }
                  nextAction {
                    action
                    context {
                      ... on PaymentSessionActionsRedirect {
                        redirectUrl
                      }
                    }
                  }
                }
                userErrors {
                  field
                  message
                }
            }
        }');
        return $this->graph_ql($mutation, "/payments_apps/api/2024-01/graphql.json");
    }


    public function makeApifyRequest($path,$headers,$data)
    {
        $url = Shopify::BASE_URL_APIFY.$path;
        try {
            $jsonData = json_encode($data);
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>$jsonData,
                CURLOPT_HTTPHEADER => $headers,
            ));
            $resp = curl_exec($curl);
            if ($resp === false) {
                return array('curl_error' => curl_error($curl), 'curerrno' => curl_errno($curl));
            }
            curl_close($curl);
            return json_decode($resp);

        }catch (Exception $e) {
            return [
                "success" => false,
                "titleResponse" => "error",
                "textResponse" => $e->getMessage(),
                "data" => []
            ];
        }
    }

    /**
     * @return mixed
     */
    public function MakeSessionPayment($publicKey,$privateKey,$data)
    {
        $headers = [
            "apikey: ${publicKey}",
            "privatekey: ${privateKey}",
            'Content-Type: application/json'
        ];
        return $this->makeApifyRequest('/checkout/payment/session',$headers,$data);
    }

    public function authentication($api_key, $private_key)
    {
        $token = base64_encode($api_key.":".$private_key);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic '.$token
        );
        $response = $this->makeApifyRequest('/login',$headers,[]);

        return isset($response->token) ? $response->token : false;
    }

    public function getIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function checkToken($shop_url,$hmac,$new_hmac,$parameters)
    {
        if( hash_equals($hmac, $new_hmac) ){
            do{
                $result = $this->getAssessToken($shop_url, Shopify::api_key, Shopify::secret_key, $parameters);
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
                    $this->set_url($shop_url);
                    $this->set_token($access_token);
                    $this->set_apiKey(Shopify::api_key);
                    $this->redirectPaymentOption('false');
                    $redirectUrl=$this->returnPaymentAdmin();
                    //header("Location: https://". $shop_url. "/admin/apps");
                    return $redirectUrl;
                }else{
                    return false;
                }
            }else{
                $querySettings = "UPDATE shopify_shop SET access_token ='".$result['access_token']."',hmac ='".$hmac."',install_date = NOW(), is_active = 2 WHERE shop_url ='".$shop_url."' ";
                if($mysql->query($querySettings)){
                    $this->set_url($shop_url);
                    $this->set_token($access_token);
                    $this->set_apiKey(Shopify::api_key);
                    $this->redirectPaymentOption('false');
                    $redirectUrl=$this->returnPaymentAdmin();
                    //header("Location: https://". $shop_url. "/admin/apps");
                    return $redirectUrl;
                }else{
                    return false;
                }
            }
        }
    }

}
