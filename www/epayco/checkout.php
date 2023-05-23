<?php
$rawdata = file_get_contents("php://input");
$decoded = json_decode($rawdata, true);

$responseUrl = urlencode(getenv("store_admin_url"));
$formattedData = array(
    "notifyUrl" => $responseUrl,
    "returnurl" => $responseUrl,
    "amount" => $decoded["amount"],
    "currency" =>   in_array($decoded["currency"],array('USD,COP')) ? $decoded["currency"]: 'USD',
    "public_key" => getenv("public_key"),
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
    $redirectUrl = array("redirect_url"=> getenv("redirect_checkout_url") . "?" . $queryParams);
    echo json_encode($redirectUrl);
