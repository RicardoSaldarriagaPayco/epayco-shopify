<?php
$rawdata = file_get_contents("php://input");
$decoded = json_decode($rawdata, true);

$responseUrl = urlencode('https://admin.shopify.com/store/epaycotext/apps/epayco-payment/elena/response.php');
$formattedData = array(
    "notifyUrl" => $responseUrl,
    "returnurl" => $responseUrl,
    "amount" => $decoded["amount"],
    "currency" =>   in_array($decoded["currency"],array('USD,COP')) ? $decoded["currency"]: 'USD',
    "public_key" => 'c84ad754c728bfb10af2c1c3d1594106',
    "subTotal" =>  $decoded["amount"],
    "tax" => 0,
    "transactionId" =>$decoded["id"],
    "description" => 'my order',
    "country" => $decoded["customer"]["billing_address"]['country_code'],
    "test" => $decoded["test"] ? '1' : '0',
    "extra1" => $decoded["id"],
    "firstName" => $decoded["customer"]["billing_address"]['given_name'],
    "lastName" => $decoded["customer"]["billing_address"]['family_name'],
    "email" => $decoded["customer"]["email"],
    "address" => $decoded["customer"]["billing_address"]['line1'],
    "lang" => $decoded["merchant_locale"],
    "ico" => 0
);
    $queryParams = http_build_query($formattedData);
    $redirectUrl = array("redirect_url"=> 'https://cms.epayco.co/omnipay/checkout/payment' . "?" . $queryParams);
    echo json_encode($redirectUrl);
