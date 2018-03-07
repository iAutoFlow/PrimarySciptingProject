<?php
/**
 * Created by PhpStorm.
 * User: Isaac[]
 * Date: 7/6/2016
 * Time: 9:58 AM
 */

//O-AUTH

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

    $RelatedDBItemID = $item->fields['milestone']->values[0]->item_id;
    $RelatedDBItem = PodioItem::get($RelatedDBItemID);
    $WorkingTeam = $RelatedDBItem->fields['working-team']->values[0]['text'];
    $Assignee = $RelatedDBItem->fields['assignee']->values[0]['text'];
    $DefaultAssignee = $RelatedDBItem->fields['default-assignee']->values[0]->item_id;

    if(!$DefaultAssignee){

        $FilterTeams = PodioItem::filter(10423243,array('filters'=>array('teams'=>$WorkingTeam)));
        $TeamItemID = $FilterTeams[0]->item_id;

        $FilterResources = PodioItem::filter(10306619, array('filters'=>array('role' =>$Assignee),'team'=>array((int)$TeamItemID)));
        $ResourcesArray = array();
        foreach($FilterResources as $resource){
            $ResouceItemID = $resource->item_id;
            array_push($ResourcesArray, $ResouceItemID);
        }
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'assigned-resource' => $ResourcesArray,
            )
        ));


    }

    else{
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'assigned-resource' => array((int)$DefaultAssignee),
            )));

    }


















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