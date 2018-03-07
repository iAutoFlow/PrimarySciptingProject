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



    if($appID == 13868972) {


        //Get fields values from item
        $DeliverableName = $item->fields['client-milestone-title']->values;
        $ProjectItemID = $item->fields['project']->values[0]->item_id;
        $DueDate = $item->fields['due-date-2']->start;
        $ClientFacingDescription = $item->fields['outline-2']->values;
        $Value = $item->fields['value']->values[0]['text'];
        $Status = $item->fields['approval-status']->values[0]['text'];

        $AssignedTo = $item->fields['assigned-to']->values;
        $AssignedEmployeesArray = array();
        foreach($AssignedTo as $contact) {
            //$EmployeeContact = PodioContact::get_for_user($contact->user_id);
            $AssignedUserProfileID = $contact->profile_id;
            array_push($AssignedEmployeesArray, $AssignedUserProfileID);
        }

        $ClientSpaceInfoItemID = $item->fields['client-workspace']->values[0]->item_id;
        $ClientSpaceInfo = PodioItem::get($ClientSpaceInfoItemID);
        $ClientDeliverableAppID = $ClientSpaceInfo->fields['milestones-app-id']->values;
        $ClientProjectAppID = $ClientSpaceInfo->fields['projects-app-id']->values;

        $FilterClientDeliverables = PodioItem::filter($ClientDeliverableAppID, array('filters' => array('action-item' => array((int)$itemID))));
        $ClientDeliverableItemID = $FilterClientDeliverables[0]->item_id;



        $FieldsArray = array(
            'fields' => array(
            ), array('hook'=>false)
        );

        if ($DeliverableName) {
            $FieldsArray['fields']['title'] = $DeliverableName;
        }
        if ($DueDate) {
            $FieldsArray['fields']['start-end-date'] = array('start' => $DueDate->format('Y-m-d H:i:s'));
        }
        if ($Value) {
            $FieldsArray['fields']['value'] = $Value;
        }
        if ($ClientFacingDescription) {
            $FieldsArray['fields']['description'] = $ClientFacingDescription;
        }
        if ($AssignedTo) {
            $FieldsArray['fields']['assigned-to'] = $AssignedEmployeesArray;
        }
        if($Status == "Complete"){
            $FieldsArray['fields']['approval'] = $Status;
        }
        if($Status == "Archived"){
            $FieldsArray['fields']['approval'] = "Canceled";
        }


        if (!$ClientDeliverableItemID){
            if ($ProjectItemID) {
                $FilterClientProjects = PodioItem::filter($ClientProjectAppID, array('filters' => array('project-2' => array((int)$ProjectItemID))));
                $ClientProjectItemID = $FilterClientProjects[0]->item_id;
                $FieldsArray['fields']['project'] = $ClientProjectItemID;
            }

            $FieldsArray['fields']['action-item'] = array((int)$itemID);
            PodioItem::create($ClientDeliverableAppID, $FieldsArray);
        }



        else{
            PodioItem::update($ClientDeliverableItemID, $FieldsArray);
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





