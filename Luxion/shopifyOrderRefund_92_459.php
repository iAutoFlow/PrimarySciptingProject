<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 2:18 PM
 */

$results = [];

//BLOCKLY START CODE
//Shopify API stuff
$API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
$PASSWORD = '35c9b25030258bb045578de683a31c81';
$STORE_URL = 'luxion.myshopify.com';

$curlLogger = function ($errorMessage, $orderID) {

    $errorCurl = curl_init();
    $script = "OrderRefund";
    $errorMessage = urlencode($errorMessage);
    $urlLog = "hoist.thatapp.io/api/v2/automation_logger?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634&msg=" . $errorMessage . "&order=" . $orderID . "&script=" . $script;
    curl_setopt($errorCurl, CURLOPT_URL, $urlLog);
    curl_setopt($errorCurl, CURLOPT_HTTPGET, 1);
    curl_setopt($errorCurl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($errorCurl, CURLOPT_TIMEOUT, 1);
    curl_exec($errorCurl);
    curl_close($errorCurl);

    return;
};

try {
    //stop running OrdedRefund script on 60 sec, to prevent conflicts with Shopify Refund Status
    sleep(60);
    $result = json_decode($triggerData);

    // define("USERNAME", "stephanie@luxion.com");
    // define("PASSWORD", "luxion9000");
    // define("SECURITY_TOKEN", "Pj8rDT98GtyhzUciLthGFFKf")

    define("USERNAME", "theresa@luxion.com");
    define("PASSWORD", "Lady1985!");
    define("SECURITY_TOKEN", "DxgUhKHulesdh5qGYUVClKnh");;

    require_once(base_path().'/public/shopifySalesForce/soapclient/SforceEnterpriseClient.php');

    $mySforceConnection = new SforceEnterpriseClient();
    $mySforceConnection->createConnection(base_path()."/public/shopifySalesForce/soapclient/enterprise.wsdl.xml");
    $mySforceConnection->login(USERNAME, PASSWORD . SECURITY_TOKEN);
    $webhookArr = $result;

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->order_id . '.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $order_json = json_decode($response);

    if ($order_json->order->cancel_reason) {
        return "Warning: Order " . $shopifyOrderID . " is canceled.";
    }

    $shopifyOrderName = $order_json->order->order_number;
    $shopifyOrderID = $order_json->order->id;
    $financialStatus = $order_json->order->financial_status;

    if (strtolower($financialStatus) != "refunded" && strtolower($financialStatus) != "partially_refunded") {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: Order " . $shopifyOrderID . " Payment Status is not in \"refunded\" nor \"partially_refunded\"");
        throw new \RuntimeException("Warning: Order " . $webhookArr->order_id . " Payment Status is not in \"refunded\" nor \"partially_refunded\". Current status: " . $financialStatus);
    }

    //get full information of product
    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/products/' . $order_json->order->line_items[0] . '.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $productFullInfo = json_decode(curl_exec($session));
    curl_close($session);
    ///

    if(strpos($productFullInfo->product->tags, "NFR") !== false) {
        $isNFR = true;
    }

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->order_id . '/transactions.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $txn_json = json_decode($response);

    foreach ($txn_json->transactions as $txn) {

        if (strtolower($txn->kind) == "refund") {

            if (empty($txn->id)) {
                Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Error Getting Refund for Order " . $shopifyOrderID . " - No Refund Found");
                throw new \RuntimeException("Error Getting Refund for Order " . $shopifyOrderID . " - No Refund Found");
            }

            if ($txn->status == "Success" || $txn->message == "Transaction approved") {
                $refundIsSuccess = true;
            }

            if ($txn->gateway == "shopify_payments") {
                $paymentType = "Shopify - Credit Card";
            } else {
                $paymentType = "Shopify - " . $txn->gateway;
            }

            if ($paymentType == "Shopify - " && $order_json->order->total_price <= 0 && $isNFR) {
                $paymentType = "Shopify - NFR";
            }
            $refundAmount = $txn->amount;

        }

    }

    $query = "select id, name, Shopify_Order_Number__c, Shopify_Order_Id__c from opportunity where Shopify_Order_Number__c = '" . $shopifyOrderName . "'";
    $response = $mySforceConnection->query($query);

    if (!$response) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
        throw new \RuntimeException("Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
    }

    if ($response->size <= 0) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: No Opportunity Found for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
        throw new \RuntimeException("Warning: No Opportunity Found for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
    }

    $SF_OpportunityID = $response->records[0]->Id;

    if (strtolower($financialStatus) == "refunded") {
        $description = "Full Refund - Amount: " . $refundAmount;
        $records[0] = new stdClass();
        $records[0]->Id = $SF_OpportunityID;
        $records[0]->Description = $description;
        $records[0]->StageName = "Shopify Order Cancelled";
        $records[0]->Probability = 0;
        $response = $mySforceConnection->update($records, 'Opportunity');
    } else {
        $description = "Partial Refund - Amount: " . $refundAmount;
        $records[0] = new stdClass();
        $records[0]->Id = $SF_OpportunityID;
        $records[0]->Description = $description;
        $records[0]->StageName = "Closed Won";
        $records[0]->Probability = 100;
        $response = $mySforceConnection->update($records, 'Opportunity');

        $query = "select id, OpportunityId , ListPrice, UnitPrice, TotalPrice, Discount, LastModifiedDate from opportunitylineitem where OpportunityId = '" . $SF_OpportunityID . "'";
        $response = $mySforceConnection->query($query);

        if (!$response) {
            Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
            throw new \RuntimeException("Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
        }

        $SF_Discount = ((($order_json->order->total_discounts + $refundAmount) / $order_json->order->total_line_items_price) * 100);

        foreach ($response->records as $record) {
            $contact[0] = new stdClass();
            $contact[0]->Id = $record->Id;
            $contact[0]->Discount = $SF_Discount;
            $response = $mySforceConnection->update($contact, 'OpportunityLineItem');
        }
    }

    //add new note
    $note = array();
    $note[0] = new stdClass();
    $note[0]->Body = $description;
    $note[0]->Title = "Refunded order";
    $note[0]->ParentId = $SF_OpportunityID;
    $noteResponse = $mySforceConnection->create($note, 'Note');

    return [
        'success' => true
    ];


} catch (Exception $e) {
    Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', $e->getMessage());
    throw $e;

}
//BLOCKLY END CODE

