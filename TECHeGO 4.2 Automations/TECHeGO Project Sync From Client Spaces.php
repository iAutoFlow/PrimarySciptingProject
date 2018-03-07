<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/3/2016
 * Time: 1:41 PM
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


    //Get fields values from item
    $ProjectItemID = $item->item_id;
    $ClientID = $item->fields['company2']->values[0]->item_id;

    $FilterClientWorkspaceItem = PodioItem::filter(13941091, array('filters' => array('client' =>array((int)$ClientID))));
    $ClientSpaceInfoItemID = $FilterClientWorkspaceItem[0]->item_id;

    $ClientSpaceInfo = PodioItem::get($ClientSpaceInfoItemID);
    $ClientProjectsAppID = $ClientSpaceInfo->fields['projects-app-id']->values;

    $FilterClientProjects = PodioItem::filter($ClientProjectsAppID, array('filters' => array('project-2' => array((int)$itemID))));
    $ClientProjectItemID = $FilterClientProjects[0]->item_id;

    if (!$ClientProjectItemID) {
        PodioItem::create($ClientProjectsAppID, array(
            'fields' => array(
                'project-2' => (int)$itemID,
            ),
            array(
                'hook' => false
            )
        ));
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





