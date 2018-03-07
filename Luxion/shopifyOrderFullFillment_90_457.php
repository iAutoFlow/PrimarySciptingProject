<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 2:13 PM
 */

$results = [];

//BLOCKLY START CODE
//Shopify API stuff
$API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
$PASSWORD = '35c9b25030258bb045578de683a31c81';
$STORE_URL = 'luxion.myshopify.com';

$curlLogger = function ($errorMessage, $orderID) {

    $errorCurl = curl_init();
    $script = "OrderFulfillment";
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
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
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
    $shopifyOrderNote = $order_json->order->note;

    $isNFR = false;
    $saleIsSuccess = false;

    if ($order_json->order->line_items[0]->sku == "6-9000-NFR") {

        $isNFR = true;

    }

    if ($order_json->order->total_price <= 0) {

        if ($isNFR) {

            $saleIsSuccess = true;

            $paymentType = "Shopify - NFR";

        } elseif (!$isNFR) {

            $paymentType = "Free Order";

        }
    }

    if ($paymentType == "Free Order") {
        return "Skipping Order " . $shopifyOrderID . " (" . $shopifyOrderName . ") - This is a $0 Non NFR Order (" . $paymentType . ")";
    }

    $query = "select id, name, Shopify_Order_Number__c, Shopify_Order_Id__c from opportunity where Shopify_Order_Number__c = '" . $shopifyOrderName . "'";
    $response = $mySforceConnection->query($query);
    $textResponse = print_r($response, true);

    if (!$response) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
        throw new RuntimeException("Attempted to Find Opportunity (SOQL: " . $query . ") - SalesForce Error");
    }

    if ($response->size = 0) {
        Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', "Warning: No Opportunity Found for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
        throw new \RuntimeException("Warning: No Opportunity Found for Shopify Order Id: " . $webhookArr->id . ", Order Name: " . $shopifyOrderName . " - Stopping Processing");
    }

    $SF_OpportunityID = $response->records[0]->Id;


    if ($shopifyOrderNote) {
        $records[0] = new stdClass();
        $records[0]->Id = $SF_OpportunityID;
        $records[0]->KeyShot_Serial_Code__c = $shopifyOrderNote;
        $response = $mySforceConnection->update($records, 'Opportunity');
        if ($response) {
            return [
                'success' => true,
                'response' => print_r($response)
            ];
        }
    }


    $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/orders/' . $webhookArr->id . '/metafields.json';
    $session = curl_init();
    curl_setopt($session, CURLOPT_URL, $url);
    curl_setopt($session, CURLOPT_HTTPGET, 1);
    curl_setopt($session, CURLOPT_HEADER, false);
    curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($session);
    curl_close($session);
    $metafields_json = json_decode($response);

    $metafieldsArray = $metafields_json->metafields;

    foreach ($metafieldsArray as $metafield) {

        if ($metafield->key == "KeyShot Animation") {
            $keyShotAnimationValue = $metafield->value;
        }

        if ($metafield->key == "KeyShot HD") {
            $keyShotHDValue = $metafield->value;
        }

        if ($metafield->key == "KeyShotVR") {
            $keyShotVRValue = $metafield->value;
        }

        if ($metafield->key == "Network Rendering") {
            $networkRenderingValue = $metafield->value;
        }

    }

    if ($keyShotAnimationValue || $keyShotHDValue || $keyShotVRValue || $networkRenderingValue) {
        $records[0] = new stdClass();
        $records[0]->Id = $SF_OpportunityID;
        if ($keyShotAnimationValue) {
            $records[0]->Animation_Serial_Code__c = $keyShotAnimationValue;
        }
        if ($keyShotHDValue) {
            $records[0]->KeyShot_Serial_Code__c = $keyShotHDValue;
        }
        if ($keyShotVRValue) {
            $records[0]->KeyShotVR_Serial_Code__c = $keyShotVRValue;
        }
        if ($networkRenderingValue) {
            $records[0]->Network_Rendering_Serial_Code__c = $networkRenderingValue;
        }
        $response = $mySforceConnection->update($records, 'Opportunity');
        return [
            'success' => true,
            'response' => print_r($response)
        ];
    }

    return "Couldn't find metafields or note field on Shopify Order";


} catch (Exception $e) {
    Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', $e->getMessage());
    throw $e;
}
//BLOCKLY END CODE

