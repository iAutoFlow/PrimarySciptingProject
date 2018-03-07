<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */


//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
class PodioSessionManager {
    private static $connection_id = 3;
    private static $connection;

    public function __construct() {
    }

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
        }
        return self::$connection;
    }

    public static function getClientId () {
        return self::getConnection()->connectionService->config['client_id'];
    }

    public static function getClientSecret () {
        return self::getConnection()->connectionService->config['client_secret'];
    }

    public function get($authtype = null){
        $connection = self::getConnection();
        return new PodioOAuth(
            $connection->access_token,
            $connection->refresh_token
        );
    }
    public function set($oauth, $auth_type = null){
        $connection = self::getConnection();
        $connection->access_token = $oauth->access_token;
        $connection->save();
        self::$connection = $connection;
    }


}




try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;


//CODE HERE

    //Trigger Item Values
    $ProductItemID = $item->fields['product']->values[0]->item_id;
    $PartNumber = $item->fields['part-number-2']->values;

    $Description = $item->fields['description']->values;
    $PriceIfKnown = $item->fields['price-if-known']->values;
    $Vendor = $item->fields['vendor-2']->values[0]->item_id;

    //Field Array
    $NewProductFieldsArray = array('fields'=>array());

    //Check for existing Part Number
    if(!$ProductItemID) {
        $FilterProductDB = PodioItem::filter(15856067, array("filters" => array('part' => $PartNumber)));
        $ProductItemID = $FilterProductDB[0]->item_id;

    }


    //Create New Product Item
    if (!$ProductItemID) {
        $NewProductFieldsArray['fields']['part'] = $PartNumber;
        if ($Description) {
            $NewProductFieldsArray['fields']['title'] = $Description;
        }
        if ($PriceIfKnown) {
            $NewProductFieldsArray['fields']['price'] = $PriceIfKnown;
        }
        if ($Vendor) {
            $NewProductFieldsArray['fields']['vendor'] = (int)$Vendor;
        }


        $CreateProduct = PodioItem::create(15856067, $NewProductFieldsArray);
        $ProductItemID = $CreateProduct->item_id;

        //Update Trigger Item
        $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'product' => (int)$ProductItemID)
            )
        );
    }




    else{

        $NewProductFieldsArray['fields']['product'] = (int)$ProductItemID;

        $ProductItem = PodioItem::get($ProductItemID);
        $ProductTitle = $ProductItem->fields['title']->values;
        $VendorItemID = $ProductItem->fields['vendor']->values[0]->item_id;
        $ProductPrice = $ProductItem->fields['price']->values;
        $Part = $ProductItem->fields['part']->values;

        if ($ProductTitle && $ProductTitle !== $Description) {$NewProductFieldsArray['fields']['description'] = $ProductTitle;}
        if ($VendorItemID && !$Vendor) {$NewProductFieldsArray['fields']['vendor-2'] = (int)$VendorItemID;}
        if ($ProductPrice && !$PriceIfKnown) {$NewProductFieldsArray['fields']['price-if-known'] = $ProductPrice;}
        if ($Part && !$PartNumber) {$NewProductFieldsArray['fields']['part-number-2'] = $Part;}

        $UpdateTriggerItem = PodioItem::update($itemID, $NewProductFieldsArray);
    }






    //Stop Coding HERE



//RETURN / CATCH
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