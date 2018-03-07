<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 2:11 PM
 */

$results = [];

//BLOCKLY START CODE
//Shopify API stuff
$API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
$PASSWORD = '35c9b25030258bb045578de683a31c81';
$STORE_URL = 'luxion.myshopify.com';

$curlLogger = function ($errorMessage, $orderID) {

    $errorCurl = curl_init();
    $script = "OrderCancel";
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
    //stop running OrderCancel script on 90 sec, to prevent conflicts
    sleep(90);
    $result = json_decode($triggerData);

    // define("USERNAME", "stephanie@luxion.com");
    // define("PASSWORD", "luxion9000");
    // define("SECURITY_TOKEN", "Pj8rDT98GtyhzUciLthGFFKf");

    define("USERNAME", "theresa@luxion.com");
    define("PASSWORD", "Lady1985!");
    define("SECURITY_TOKEN", "DxgUhKHulesdh5qGYUVClKnh");

    require_once(base_path().'/public/shopifySalesForce/soapclient/SforceEnterpriseClient.php');

    $mySforceConnection = new SforceEnterpriseClient();
    $mySforceConnection->createConnection(base_path()."/public/shopifySalesForce/soapclient/enterprise.wsdl.xml");
    $mySforceConnection->login(USERNAME, PASSWORD . SECURITY_TOKEN);
    $webhookArr = $result;

    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->id . '.json';
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

    $shopifyOrderName = $order_json->order->order_number;
    $shopifyOrderID = $order_json->order->id;

    $query = "select id, name, Shopify_Order_Number__c, Shopify_Order_Id__c from opportunity where Shopify_Order_Number__c = '" . $shopifyOrderName . "'";
    $response = $mySforceConnection->query($query);

    if (!$response) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
        throw new \RuntimeException("Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
    }

    if ($response->size <= 0) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: Unable to Sync Cancel for Order " . $shopifyOrderID . " (" . $shopifyOrderName . ").  Unable to Find Existing Opportunity in SalesForce.");
        throw new \RuntimeException("Warning: Unable to Sync Cancel for Order " . $shopifyOrderID . " (" . $shopifyOrderName . ").  Unable to Find Existing Opportunity in SalesForce.");
    }

    $SF_OpportunityID = $response->records[0]->Id;

    $records[0] = new stdClass();
    $records[0]->Id = $SF_OpportunityID;
    $records[0]->Description = "Cancelled Order - Amount: " . $order_json->order->total_price;
    $records[0]->StageName = "Shopify Order Cancelled";
    $records[0]->Probability = 0;
    $response = $mySforceConnection->update($records, 'Opportunity');

    return [
        'success' => true
    ];


} catch (Exception $e) {
    Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', $e->getMessage());
    throw $e;

}
//BLOCKLY END CODE

