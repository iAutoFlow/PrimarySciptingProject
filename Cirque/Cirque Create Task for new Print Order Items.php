<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/12/2016
 * Time: 10:53 AM
 */


date_default_timezone_set('America/Denver');
//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
class PodioSessionManager {
    private static $connection_id = 191;
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

    $JobItemID = $item->fields['job']->values[0]->item_id;
    $AssigneeArray = array();


    //Get Related Job Item
    $Job = PodioItem::get($JobItemID);
    $JobLink = $Job->link;
    $Assignee = $Job->fields['assigned-to']->values;
    foreach($Assignee as $user){
        $AssigneeUserID = $user->user_id;
        array_push($AssigneeArray, $AssigneeUserID);
    }



    //Set Task Item Attributes
    $AttributesArray = array(
        'text'=>"Attention! Print Run Requested",
        'responsible'=>$AssigneeArray,
        'description'=>"Here is the link to the Job Item:  ".$JobLink
    );



    //Create and assign Task
    $CreateTask = PodioTask::create_for('item',$itemID, $AttributesArray);













    return [
        'success' => true,
        'result' => $HooksArray,
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