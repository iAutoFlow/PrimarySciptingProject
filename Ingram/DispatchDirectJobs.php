<?php
/**
 * Created by PhpStorm.
 * User: Isaac
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

    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");
    $TodaysDateFormatted = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));


    $ECreativeSpaceID = 3984347;
    $ECreativeDirectAgencyJobAppID = 14761032;
    $ECreativeJobsAppID = 13869166;
    $EDispatchCreativeAppID = 13869216;

    $CreateScheduleStatus = $item->fields['category']->values[0]['text'];
    $CreativeTrafficManagerItemID = $item->fields['creative-project-manager-3']->values[0]->item_id;
    $UniqueJobID = $item->fields['unique-id']->values;
    $SKUTypeItemID = $item->fields['select-sku-type']->values[0]->item_id;


    $jobFieldsArray = array(
        'fields' => array(
            'job-schedule-status' => "On Time",
            'direct-job' => array((int)$itemID),
            'creative-status' => "Created",
            'team-dashboard' => array((int)347566351),
        )
    );

    if($CreativeTrafficManagerItemID){
        $jobFieldsArray['fields']['program-manager-test'] = (int)$CreativeTrafficManagerItemID;
        $jobFieldsArray['fields']['assigned-to'] = (int)$CreativeTrafficManagerItemID;
    }

    if($CreateScheduleStatus == "Create Job (Trigger)"){
        $CreateJobItem = PodioItem::create($ECreativeJobsAppID, $jobFieldsArray,
            array(
                'hook' => false
            )
        );

        $MilestoneArray = array();
        $FilterTDBMilestone = PodioItem::filter(13286876, array('filters'=>array('product-line'=>array((int)$SKUTypeItemID))));
        foreach($FilterTDBMilestone as $milestone){
            $MilestoneItemID = $milestone->item_id;
            array_push($MilestoneArray, $MilestoneItemID);
        }

        $FilterJobs = PodioItem::filter($ECreativeJobsAppID, array('filters'=>array('direct-job'=>array((int)$itemID))));
        $JobItemID = $FilterJobs[0]->item_id;


        $CreateDispatchItem = PodioItem::create($EDispatchCreativeAppID, array(
            'fields' => array(
                'team-job' => array((int)$JobItemID),
                'product-line-2' => array((int)$SKUTypeItemID),
                'timeline' => "Regular",
                'dispatch' => "Idle",
                'milestone-1' => $MilestoneArray,
            ),
            array(
                'hook' => false
            )
        ));

        $updateTriggerValue = PodioItem::update($itemID, array(
            'fields' => array (
                'category' => "Job Created",
            ),
            array(
                'hook' => false
            )
        ));


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