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


    //
    $TriggerValue = $item->fields['create-checklist-item']->values[0]['text'];

    if($TriggerValue == "Split System Checklist"){
        $Create = PodioItem::create(16449892, array(
            'fields'=>array(
                'proposal'=>(int)$itemID
            )
        ));
    }
    if($TriggerValue == "MAU Checklist"){
        $Create = PodioItem::create(16449894, array(
            'fields'=>array(
                'proposal'=>(int)$itemID
            )
        ));
    }
    if($TriggerValue == "RTU Checklist"){
        $Create = PodioItem::create(16449895, array(
            'fields'=>array(
                'proposal'=>(int)$itemID
            )
        ));
    }


    $UpdateTrigger = PodioItem::update($itemID, array(
        'fields'=>array(
            'create-checklist-item'=>"..."
        ),
        array(
            'hook'=>false
        )
    ));







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

