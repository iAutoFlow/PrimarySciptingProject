<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
 */

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

    $SalesSpaceID = 2732154;
    $ScopeAppID = 10226461;

    $ProductLineID = $item->fields['description']->values[0]->item_id;

    $ScopeRefreshValue = $item->fields['refresh-bomb-elements-from-product-line']->values[0]['text'];

    $BombElementsArray = array();

    if($ScopeRefreshValue == "Refresh"){

        $Product = PodioItem::get($ProductLineID);
        $ProductBombElements = $Product->fields['bomb-elements']->values;
        foreach($ProductBombElements as $BombElements){
            $BombElementsID = $BombElements->item_id;
            array_push($BombElementsArray, $BombElementsID);
        }



    }




    PodioItem::update($itemID, array(
        'fields' => array(
            'refresh-bomb-elements-from-product-line'=>"...",
            'bomb-elements'=>(int)$BombElementsArray,
            array(
                'hook' => false
            )
        ),

    ));



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
