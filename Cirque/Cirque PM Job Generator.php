<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/1/2016
 * Time: 11:26 AM
 */
date_default_timezone_set('America/Denver');
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

    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));

    $ProjectManagementSpaceID = 2337777;
    $PMJobsAppID = 9063521;
    $PMStrategiesAppID = 8708019;


    //Fields of Trigger Item
    $CreateDeliverablesFieldValue = $item->fields['create-deliverables']->values[0]['text'];
    $Year = $item->fields['year']->values[0]['text'];
    $Graphics = $item->fields['category']->values[0]['text'];
    $Status = $item->fields['status']->values[0]['text'];
    $DueDateType = $item->fields['due-date-type']->values[0]['text'];
    $JobDetails = $item->fields['job-details']->values;
    $CallToAction = $item->fields['call-to-action']->values[0]->item_id;
    $BrandItemID = $item->fields['brands']->values[0]->item_id;
    $NextReviewDue = $item->fields['next-review-due']->start_date;
    $FinalDue = $item->fields['final-due']->start_date;


    //Contact Field Values
    $PointPerson = $item->fields['requestor']->values;
    $PointPersonProfileID = $PointPerson[0]->profile_id;
    $Requestor = $item->fields['requestor-2']->values;
    $RequestorProfileID = $Requestor[0]->profile_id;
    $AssignedEmployeesArray = array();
    $AssignedTo = $item->fields['assigned-to']->values;
    foreach($AssignedTo as $contact) {
        //$EmployeeContact = PodioContact::get_for_user($contact->user_id);
        $AssignedUserProfileID = $contact->profile_id;
        array_push($AssignedEmployeesArray, $AssignedUserProfileID);
    }

    if($CreateDeliverablesFieldValue == "..." || $CreateDeliverablesFieldValue == "Completed"){
        exit;
    }



    if(!$Graphics  || !$AssignedEmployeesArray || !$NextReviewDue || !$FinalDue || !$Year || !$Status || !$DueDateType){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => "Jobs can not be created until all fields have a value."
        ));

        $CreateDeliverablesFieldValue = "...";
        $updateTriggerValue = PodioItem::update($itemID, array(
            'fields' => array(
                'create-deliverables' => $CreateDeliverablesFieldValue),
            array(
                'hook' => false
            )
        ));
        exit;
    }


    $FieldsArray = array(
        'fields'=>array(
            'type' => $Graphics,
            'strategy' => array((int)$itemID),
            'assigned-to' => $AssignedEmployeesArray,
            'date-requested-2' => array('start' => $todaysDate),
            'next-rev-due' => array('start' => $NextReviewDue->format('Y-m-d H:i:s')),
            'final-due-date' => array('start' => $FinalDue->format('Y-m-d H:i:s')),
            'year' => $Year,
            'status' => $Status,
            'due-date-type' => $DueDateType,
        )
    );

//Add Requestor if Not Blank
    if($JobDetails){
        $FieldsArray['fields']['project-description'] = $JobDetails;
    }

    if($CallToAction){
        $FieldsArray['fields']['call-to-action'] = array((int)$CallToAction);
    }

    if($PointPersonProfileID){
        $FieldsArray['fields']['marketing-agent'] = array((int)$PointPersonProfileID);
    }

    if($Requestor){
        $FieldsArray['fields']['requestor'] = array((int)$RequestorProfileID);
    }

    if($BrandItemID){
        $FieldsArray['fields']['brand-3'] = array((int)$BrandItemID);
    }


    //Create Single Job Item
    if($CreateDeliverablesFieldValue == "Create Single Deliverable"){
        $AddSizeItemID = $item->fields['inventory']->values[0]->item_id;
        $FieldsArray['fields']['item'] = (int)$AddSizeItemID;

        $CreateJobItem = PodioItem::create($PMJobsAppID, $FieldsArray);
        $CreateDeliverablesFieldValue = "Completed";
    }



    //Create Set of Job Items
    elseif($CreateDeliverablesFieldValue == "Create Set of Deliverables"){
        $StrategyAddSizesNeeded = $item->fields['inventory']->values;
        foreach($StrategyAddSizesNeeded as $AddSize){
            $AddSizeItemID = $AddSize->item_id;
            $FieldsArray['fields']['item'] = (int)$AddSizeItemID;

            $CreateJobItem = PodioItem::create($PMJobsAppID, $FieldsArray);
            $CreateDeliverablesFieldValue = "Completed";
        }
    }


    $updateTriggerValue = PodioItem::update($itemID, array(
        'fields' => array(
            'requestor-2' => array(),
            'create-deliverables' => $CreateDeliverablesFieldValue,
            'call-to-action' => array(),
            'category' => [],
            'inventory' => array(),
            'job-details' => [],
            'next-review-due' => [],
            'final-due' => [],
            'status' => [],
            'due-date-type' => [],
            'type'=>[],
        ),
        array(
            'hook' => false
        )
    ));

    sleep(20);

    $CreateDeliverablesFieldValue = "...";
    $updateTriggerValue = PodioItem::update($itemID, array(
        'fields' => array(
            'create-deliverables' => $CreateDeliverablesFieldValue),
        array(
            'hook' => false
        )
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





