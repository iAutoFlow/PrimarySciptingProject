<?php
//Authentication
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


do {

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];


    $offset = 0;
    $i = 0;




        //Trigger item values
        $SalesSpaceID = 2732154;
        $SProductLinesAppID = 12259081;

        $SKUArray = array();
        $ExistingProductItemIDsArray = array();
        $AllProductItemIDsArray = array();


        $offset = $i * 500;
        $SProductLinesFilter = PodioItem::filter($SProductLinesAppID, array('limit' => 500, 'offset' => $offset)); //'limit'=>500
        $filteredNum = count($SProductLinesFilter);


//Get SKU Value of each Item.
        foreach ($SProductLinesFilter as $productLine) {


            //Add ALL Product Item ID's to an Array
            array_push($AllProductItemIDsArray, $productLine->item_id);

            //Add SKU Value to SKU Array if not already there, otherwise add item ID to Existing Items Array
            $SKU = $productLine->fields['sku']->values;
            if (!in_array($SKU, $SKUArray)) {
                array_push($SKUArray, $SKU);
            } else {
                array_push($ExistingProductItemIDsArray, $productLine->item_id);
            };


            } catch (Exception $e) {

                $event['response'] = [
                    'status_code' => 400,
                    'content' => [
                        'success' => false,
                        'result' => $ExistingProductItemIDsArray,
                        'message' => "Error: " . $e,

                    ]
                ];

                return;

            }

            $i++;


        }
    }
        while($filteredNum == 500);


//END AUTOMATION

    return [
        'success' => true,
        'result' => $ExistingProductItemIDsArray
    ];



