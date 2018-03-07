//<?php

try {

    $requestParams = $event['request']['parameters'];
    $orderID = $requestParams['order_id'];

    //Modify these
    $API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
    $PASSWORD = '35c9b25030258bb045578de683a31c81';
    $STORE_URL = 'luxion.myshopify.com';

    $url = 'https://'.$API_KEY.':'.$PASSWORD.'@'.$STORE_URL.'/admin/orders/'.$orderID.'.json';

    $session = curl_init();

    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

    if(ereg("^(https)", $url)) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($session);
    curl_close($session);

    $order_json = json_decode($response);

    return json_encode($order_json);exit;

    $orderObj = $order_json->orders[0];

    $totalPrice = $orderObj->total_price;

    $isNFR = false;

    foreach($orderObj->line_items as $item) {
        $orderSKU = $item->sku;

        if($orderSKU == "5-9000-NFR"){
            $isNFR = true;
            break;
        }
    }

    if($totalPrice <= 0 && $isNFR == true){
        $paymentType = "Shopify - NFR";
    }
    elseif($totalPrice <= 0 && $isNFR == false){
        $paymentType = "Free Order";
    }
    else{

        print_r($orderObj->transactions);
        foreach($orderObj->transactions as $transaction){

            if($transaction->kind != "sale"){
                continue;
            }
            if(!$transaction->id){
                throw new Exception("Error Getting Sale for Order".$orderID."- No Sale Found");
            }

            if($transaction->gateway == "shopify_payments"){
                $paymentType = "Shopify - Credit Card";
            }
            else{
                $paymentType = "Shopify - ".$transaction->gateway;
            }

            if($paymentType == "Shopify - " && $totalPrice <= 0 && $isNFR == true){
                $paymentType = "Shopify - NFR";
            }

        }
    }

    //check payment type

    if($paymentType == "Shopify - Purchase Order" || $paymentType == "Shopify - Bank Transfer" || $paymentType == "Shopify - Bank Deposit" || $paymentType == "Shopify - NFR"){
        //do Shopify to Salesforce - Order/Opportunity Integration
    }
    else{
        throw new Exception("Warning: Payment Type for Order Id #".$orderID." is type '".$paymentType."' Stopping Processing for On Order Create.  Only Purchase Order, Bank Transfers, or NFR orders are applicable for On Order Create. ");
    }


    return [
        'success' => true,
        'result' => $result,
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}
