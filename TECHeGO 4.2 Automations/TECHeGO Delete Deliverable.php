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
    $appID = $item->app->app_id;

    if($appID == 13868972){

        $Status = $item->fields['approval-status']->values[0]['text'];

        if($Status == "Delete"){

            $RelatedItems = PodioItem::get_references($itemID);

            foreach($RelatedItems as $relate){
                if($relate['app']['name'] == "Deliverables"){
                    $RelatedDelivItemID = $relate['items'][0]['item_id'];
                    $DeleteRelatedDeliv = PodioItem::delete($RelatedDelivItemID);
                }
            }

            $DeleteItem = PodioItem::delete($itemID);
        }

    }

    else{
        $Status = $item->fields['approval']->values[0]['text'];

        if($Status == "Delete"){
            $RelatedDelivItemID = $item->fields['action-item']->values[0]->item_id;
            if($RelatedDelivItemID){
                $DeleteRelatedDeliv = PodioItem::delete($RelatedDelivItemID);
            }
            $DeleteItem = PodioItem::delete($itemID);
        }


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