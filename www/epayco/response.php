<?php
include_once("includes/mysql_connect.php");
include_once("includes/shopify.php");

$shopify = new Shopify();
$parameters = $_GET;

include_once("includes/check_token.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

}

if(!empty($parameters)){
    $url = 'https://secure.epayco.co/validation/v1/reference/'.$parameters['ref_payco'];
    $responseData = file_get_contents($url);
    $jsonData = @json_decode($responseData, true);
    $validationData = $jsonData['data'];
    $id = "gid://shopify/PaymentSession/".$validationData['x_extra1'];

    $x_cod_transaction_state = (int)trim($validationData['x_cod_transaction_state']) ?? (int)trim($validationData['x_cod_response']);
    switch ($x_cod_transaction_state) {
        case 1: {
            $mutation = array("query" => 'mutation {
            paymentSessionResolve(id: "'. $id .'"){
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
                userErrors {
                    field
                    message
                }
            }
        }');
        }break;
        case 3: {
            $mutation = array("query" => 'mutation {
                paymentSessionPending(
                    id: "'. $id .'", 
                    pendingExpiresAt: "2023-17-05T20:47:55Z",
                    reason: "BUYER_ACTION_REQUIRED"
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
        }break;
        default: {
            $mutation = array("query" => 'mutation {
            	refundSessionReject(id: "'. $id .'", reason: {
            	    "code": "PROCESSING_ERROR",
                    "merchantMessage": "the payment did not work with ePayco"
            	 }) {
                    paymentSession {
                      id
                      state {
                        ... on PaymentSessionStateRejected {
                          code
                          reason
                          merchantMessage
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
        }break;
    }

    $create_script = $shopify->graph_ql($mutation);
    $get_script = json_decode($create_script['body'], true);
    echo var_dump($get_script);


}