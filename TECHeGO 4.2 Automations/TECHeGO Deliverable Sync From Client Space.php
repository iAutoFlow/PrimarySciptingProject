<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */


date_default_timezone_set('America/Denver');
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


    $DeliverableName = $item->fields['title']->values;
    $ClientProjectItemID = $item->fields['project']->values[0]->item_id;
    $DeliverableItemID = $item->fields['action-item']->values[0]->item_id;
    $DueDate = $item->fields['start-end-date']->start;
    $ClientFacingDescription = $item->fields['description']->values;
    $Value = $item->fields['value']->values[0]['text'];

    $AssignedTo = $item->fields['assigned-to']->values;
    $AssignedEmployeesArray = array();
    foreach ($AssignedTo as $contact) {
        //$EmployeeContact = PodioContact::get_for_user($contact->user_id);
        $AssignedUserProfileID = $contact->profile_id;
        array_push($AssignedEmployeesArray, $AssignedUserProfileID);
    }






    $FieldsArray = array(
        'fields' => array(),array('hook'=>false)
    );

    if ($DeliverableName) {
        $FieldsArray['fields']['client-milestone-title'] = $DeliverableName;
    }

    if ($ClientProjectItemID) {
        $ClientProjectItem = PodioItem::get($ClientProjectItemID);
        $ProjectItemID = $ClientProjectItem->fields['project-2']->values[0]->item_id;
        $FieldsArray['fields']['project'] = $ProjectItemID;
    }

    if ($DueDate) {
        $FieldsArray['fields']['due-date-2'] = array('start' => $DueDate->format('Y-m-d H:i:s'));
    }
    if ($Value) {
        $FieldsArray['fields']['value'] = $Value;
    }
    if ($ClientFacingDescription) {
        $FieldsArray['fields']['outline-2'] = $ClientFacingDescription;
    }
    if ($AssignedTo) {
        $FieldsArray['fields']['assigned-to'] = $AssignedEmployeesArray;
    }


    if (!$DeliverableItemID) {
        $FilterClientWorkspaceItems = PodioItem::filter(13941091, array('filters' => array('milestones-app-id' => (string)$appID)));
        $ClientSpaceItemID = $FilterClientWorkspaceItems[0]->item_id;
        if ($ClientSpaceItemID) {
            $FieldsArray['fields']['client-workspace'] = array((int)$ClientSpaceItemID);
        }

        $CreateDeliv = PodioItem::create(13868972, $FieldsArray);
        $NewDelivItemID = $CreateDeliv->item_id;
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'action-item' => array((int)$NewDelivItemID),
            )));
    }



    else {
        PodioItem::update($DeliverableItemID, $FieldsArray);
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