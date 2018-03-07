<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 2:16 PM
 */

$results = [];

//BLOCKLY START CODE
//Shopify API stuff
$API_KEY = 'da3d76df3dbc00f3d2ea45c52cf578bd';
$PASSWORD = '35c9b25030258bb045578de683a31c81';
$STORE_URL = 'luxion.myshopify.com';

$curlLogger = function ($errorMessage, $orderID) {

    $errorCurl = curl_init();
    $script = "NewProduct";
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

function endsWith($haystack, $needle)
{
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

try {

    $webhookArrY = json_decode($triggerData);
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

    if (isset($webhookArrY->title)) {
        $url = 'https://' . $API_KEY . ':' . $PASSWORD . '@' . $STORE_URL . '/admin/products/' . $webhookArrY->id . '.json';
        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, $url);
        curl_setopt($session, CURLOPT_HTTPGET, 1);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        if (strpos("https", $url) !== false) curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($session);
        curl_close($session);
        $product_json = json_decode($response);
    }

    // foreach ($product_json->product->variants as $variant) {
    //     if (endsWith(str_replace("(Bundle price)", "", $variant->title), ")")) {
    //         continue;
    //     };
    //     $variantTitle = $variant->title;
    // }

    $priceBook2ID = "01sA0000000QTHnIAO";

    foreach ($product_json->product->variants as $variant) {
        if (!endsWith($variant->title, ")")) {
            $mainVariantID = $variant->id;
        } else {
            continue;
        };

        $query = "select Id, Shopify_ID__c from Product2 where Shopify_ID__c = '" . $mainVariantID . "' and isActive = true";
        $response = $mySforceConnection->query($query);
        $textResponse = json_encode($response, true);

        if ($response->size != 0) {
            $product = array();
            $product[0] = new stdClass();
            $product[0]->Id = $response->records[0]->Id;
            $product[0]->Name = $product_json->product->title;
            $product[0]->Description = $variant->title;
            $product[0]->ProductCode = $variant->sku;
            $product[0]->Shopify_ID__c = $mainVariantID;
            $product[0]->Shopify_Barcode__c = 1;
            $responsePU = $mySforceConnection->update($product, 'Product2');

            $SF_ProductID = $responsePU[0]->id;
        } else {
            $product = array();
            $product[0] = new stdClass();
            $product[0]->Name = $product_json->product->title;
            $product[0]->Description = $variant->title;
            $product[0]->ProductCode = $variant->sku;
            $product[0]->IsActive = true;
            $product[0]->Shopify_ID__c = $mainVariantID;
            $product[0]->Shopify_Barcode__c = 1;
            $responsePC = $mySforceConnection->create($product, 'Product2');

            $SF_ProductID = $responsePC->id;
        }

        $query = "select Id, Name from PriceBookEntry where Product2Id = '" . $SF_ProductID . "' and Pricebook2Id = '" . $priceBook2ID . "'";
        $responsePBE = $mySforceConnection->query($query);

        if ($responsePBE->size <= 0) {
            $product = array();
            $product[0] = new stdClass();
            $product[0]->Product2 = $SF_ProductID;
            $product[0]->PriceBook2 = $priceBook2ID;
            $product[0]->UnitPrice = $variant->price;
            $product[0]->IsActive = true;
            $product[0]->Shopify_ID_del__c = $mainVariantID;
            $responsePBEC = $mySforceConnection->create($product, 'PriceBookEntry');
        } else {
            $SF_PriceBookID = $responsePBE->records[0]->Id;

            $product = array();
            $product[0] = new stdClass();
            $product[0]->Id = $SF_PriceBookID;
            $product[0]->UnitPrice = $variant->price;
            $product[0]->UseStandardPrice = false;
            $product[0]->Shopify_ID_del__c = $mainVariantID;
            $responsePBEU = $mySforceConnection->update($product, 'PriceBookEntry');
        }
    }

    return [
        'success' => true,
        'response' => "Product ID: " . $SF_ProductID
    ];


} catch (Exception $e) {
    Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyError('LuxionScript', $e->getMessage());
    throw $e;
}

//BLOCKLY END CODE

