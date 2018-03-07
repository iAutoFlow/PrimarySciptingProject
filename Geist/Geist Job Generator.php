<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/1/2016
 * Time: 11:26 AM
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

    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");
    $TodaysDateFormatted = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));

    //APP / Space ID's
    $MarketingProjectsSpaceID = 4698238;
    $ProjectsAppID = 16261915;
    $JobsAppID = 16358145;
    $MarketingManagementSpaceID = 4698222;
    $TimeCardsAppID = 16261851;


    //Get Values from Trigger item

    $JobTitle = $item->fields['job-title']->values;
    $CreateDeliverablesFieldValue = $item->fields['create-deliverables']->values[0]['text'];
    $Requestor = $item->fields['project-requestor']->values[0]->item_id;
    $AssignedTo = $item->fields['assigned-to-2']->values;
    $ProjectDescription = $item->fields['project-description']->values;
    $Status = $item->fields['status']->values[0]['text'];
    $AllocatedTime = $item->fields['allocated-time']->values;
    $JobTypeItemID = $item->fields['job-type-2']->values[0]->item_id;
    $GraphicsJobSizeItemID = $item->fields['graphics-job-size']->values[0]->item_id;
    $JobDetails = $item->fields['job-details']->values;
    $NextReviewDue = $item->fields['next-review-due']->start;
    $FinalDue = $item->fields['final-due']->start;




    if($CreateDeliverablesFieldValue !== "Create Single Job Item" || !$JobTitle || !$AssignedTo  || !$JobDetails  || !$FinalDue){
        $AddErrorComment = PodioComment::create('item', $itemID, array(
            'value' => "Job can not be created until all required fields have a value."
        ));

        $UpdateTriggerValue = PodioItem::update($itemID, array(
            'fields' => array(
                'create-deliverables' => "...",
            ),
            array(
                'hook' => false
            ),
        ));
    }



    //Assigned Employees Array
    else{
        $AssignedEmployeesArray = array();
        foreach($AssignedTo as $contact) {
            $AssigneeItemID = $contact->item_id;
            array_push($AssignedEmployeesArray, $AssigneeItemID);
        }


        //Format Final Due Date
        //$FinalDueDate = new DateTime((string)$FinalDue, new DateTimeZone('America/Denver'));



        //New Item Fields Array
        $fieldsArray = array(
            'fields' => array(
                'title'=>$JobTitle,
                'project' => array((int)$itemID),
                'assigned-to-2' => $AssignedEmployeesArray,
                'job-details' => $JobDetails,
                'final-due' => array('start' => $FinalDue->format('Y-m-d H:i:s')),
            ),
            array(
                'hook'=>false
            )
        );


        if ($Requestor) {
            $fieldsArray['fields']['requestor-2'] = array((int)$Requestor);
        }
        if ($NextReviewDue) {
            $fieldsArray['fields']['next-review-due'] = array('start' => $NextReviewDue->format('Y-m-d H:i:s'));
        }
        if ($AllocatedTime) {
            $fieldsArray['fields']['allocated-time'] = $AllocatedTime;
        }
        if ($ProjectDescription) {
            $fieldsArray['fields']['project-description'] = $ProjectDescription;
        }
        if ($Status) {
            $fieldsArray['fields']['status'] = $Status;
        }
        if ($GraphicsJobSizeItemID) {
            $fieldsArray['fields']['graphics-job-size'] = (int)$GraphicsJobSizeItemID;
        }
        if ($JobTypeItemID) {
            $fieldsArray['fields']['job-type-2'] = (int)$JobTypeItemID;
        }


        //Create Job Item
        $CreateJobItem = PodioItem::create($JobsAppID, $fieldsArray);


        //Update Trigger Item
        $UpdateTriggerValue = PodioItem::update($itemID, array(
            'fields' => array(
                'create-deliverables' => array('value'=>"..."),
                'job-title' => [],
                'allocated-time' => [],
                'job-details' => [],
                'graphics-job-size' => [],
                'next-review-due' => [],
                'final-due' => [],
                'assigned-to-2' => array(),
                'job-type-2'=>array(),
            )),
            array(
                'hook' => false
            )
        );
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





