<?php
class DumpHTTPRequestToFile {
    private $requestInfo;
    private $mysql;
    private $comfirm;

    public function __construct($requestInfo, $mysql, $is_confirm){
        $this->requestInfo = $requestInfo;
        $this->mysql = $mysql;
        $this->comfirm = $is_confirm;
    }

    public function execute() {
        $extra2=trim($this->requestInfo['x_extra2']);
        $x_cod_transaction_state_value=intval(trim($this->requestInfo['x_cod_transaction_state']));
        try {
            $query_ = "SELECT * FROM shopify_orders WHERE tienda = '". $this->requestInfo['x_extra3'] ."'
            AND order_id = '". $extra2 ."' LIMIT 1";
            $result = $this->mysql->query($query_);
            if($result->num_rows < 1){
                if(!$this->comfirm){
                    return $this->insertData($extra2,$x_cod_transaction_state_value);
                }
            }
            $store_order_data = $result->fetch_assoc();
            $order_id = $store_order_data['order_id'];
            $x_code = $store_order_data['x_code'];

            if($order_id){
                if(!in_array($x_cod_transaction_state_value,array(1))){
                    if(intval(trim($x_code)) == $x_cod_transaction_state_value){
                        if($x_cod_transaction_state_value!=3){
                            $queryShop = "DELETE FROM shopify_orders WHERE tienda = '". $this->requestInfo['x_extra3'] ."'
                            AND order_id = '". $extra2 ."'";
                            if(!$this->comfirm){
                                $this->mysql->query($queryShop);
                                return true;
                            }
                        }
                        return false;
                    }else{
                        $queryShop = "DELETE FROM shopify_orders WHERE tienda = '". $this->requestInfo['x_extra3'] ."'
                            AND order_id = '". $extra2 ."'";
                        if(intval(trim($x_code)) == 3 && $x_cod_transaction_state_value!=3){

                            if(!$this->comfirm){
                                $this->mysql->query($queryShop);
                                return true;
                            }
                            return true;
                        }if(intval(trim($x_code)) == 2 && $x_cod_transaction_state_value==3){
                            if(!$this->comfirm){
                                $this->mysql->query($queryShop);
                                return true;
                            }
                        }

                        if(intval(trim($x_code)) == 2 && !in_array($x_cod_transaction_state_value,array(1,3))){
                            if(!$this->comfirm){
                                $this->mysql->query($queryShop);
                                return true;
                            }
                        }



                        return false;
                    }
                }else{
                    $queryShop = "DELETE FROM shopify_orders WHERE tienda = '". $this->requestInfo['x_extra3'] ."'
                    AND order_id = '". $extra2 ."'";
                    $this->mysql->query($queryShop);
                    return false;
                }
            }
            if(!$this->comfirm){
                return $this->insertData($extra2,$x_cod_transaction_state_value);
            }
            return false;

        }catch (Exception $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function insertData($extra2,$x_cod_transaction_state_value)
    {
        if(!is_null($extra2) && !is_null($x_cod_transaction_state_value)){
            if (!in_array($x_cod_transaction_state_value, array(1))) {
                $querySettings = "INSERT INTO shopify_orders (
                order_id,
                tienda,
                x_code
             ) VALUES (
               '" . $extra2 . "',
               '" . $this->requestInfo['x_extra3'] . "',
               '" . $x_cod_transaction_state_value . "'
             )";
                if ($this->mysql->query($querySettings)) {
                    return true;
                } else {
                    throw new Exception("Error Inserting data", 1);
                }
            }
            return false;
        }else{
            throw new Exception("Error: la validaci贸n no se pudo procesar!", 1);
        }

    }
}
