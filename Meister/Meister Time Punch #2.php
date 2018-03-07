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
    $revision_id = $requestParams['item_revision_id'];
    $previousRevisionID = $revision_id - 1;
    $revisionDifference = PodioItemDiff::get_for($itemID, $previousRevisionID, $revision_id);
    $revisionToVal = $revisionDifference[0]->to[0]['value']['text'];



    if($revisionToVal != 'Punch In' && $revisionToVal != 'Punch Out'){
        throw new Exception('Trigger not Punch In or Punch Out');
    }

    $item = PodioItem::get($itemID);

    $deliverableItemID = $item->item_id;

    $projectItemID = $item->fields['project']->values[0]->item_id;

    $approvalStatusVal = $item->fields['approval-status']->values[0]['text'];

    $statusVal = $item->fields['status']->values[0]['text'];

    $billingTypeVal = $item->fields['billing-type-2']->values[0]['text'];

    if($statusVal !== 'Active'){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => "Status must be Active."
        ));

        $updateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'time-clock' => "Error"
            ),
            array(
                'hook' => false
            )
        ));
        exit;
    }

    $billingCycleFilter = PodioItem::filter_by_view(16223395, 29774533);
    foreach($billingCycleFilter as $cycle){
        if($cycle->fields['project']->values[0]->item_id == $projectItemID){
            $billingCycleItemID = $cycle->item_id;
        }
    }


    $delivRevision = PodioItemRevision::get( $itemID, $revision_id );

    $triggerUserID = $delivRevision->created_by->id;
    $userContact = PodioContact::get_for_user( $triggerUserID );
    $triggerUserProfileID = $userContact->profile_id;
    $triggerUserName = $userContact->name;

    $triggerTimeStamp = $delivRevision->created_on;
    $dateTimeStamp = new DateTime((string)$triggerTimeStamp, new DateTimeZone('America/Denver'));
    $podioFormatTimeStamp = $dateTimeStamp->format("Y-m-d H:i:s");


    $employeeDBFilter = PodioItem::filter( 15595671, array("filters"=>array('employee'=>$triggerUserProfileID)));
    $employeeDBItemID = $employeeDBFilter[0]->item_id;

    if(!$employeeDBItemID){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => $triggerUserName . " " . "does not exist in the Employee Database."
        ));
        $updateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'time-clock' => "Error"),
            array(
                'hook' => false
            )));
        exit;
    }



    $employeeTimeTracker = PodioItem::filter( 15595816, array("filters"=>array('employee'=>array((int)$employeeDBItemID),'deliverable'=>array((int)$itemID))));
    $timeTrackerItemID = $employeeTimeTracker[0]->item_id;


    if(!$timeTrackerItemID){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => $triggerUserName . " " . "does not have a Time Tracker for this Deliverable You can create one by adding them to the Assigned To field, and then click the Create Time Tracker Button."
        ));
        $updateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'time-clock' => "Error"),
            array(
                'hook' => false
            )));
        exit;
    }



    $timeTrackerItem = PodioItem::get($timeTrackerItemID);
    $timeCard = PodioItem::filter_by_view(16154196, 29675284, array("filters"=>array('employee'=>$employeeDBItemID)));
    $timeCardItemID = $timeCard[0]->item_id;


    $relatedDeliverable = $timeTrackerItem->fields['deliverable']->values;
    foreach($relatedDeliverable as $app){
        $deliverableItemID = $app->item_id;
    }

    $currentPunch = PodioItem::filter( 15595843, array("filters"=>array('employee'=>$employeeDBItemID,'milestone'=>$deliverableItemID,'status'=>'Working')));

    if($revisionToVal == 'Punch In') {

        if(!$deliverableItemID){
            PodioComment::create('item', $timeTrackerItemID, array('value' => 'Punch Error: No Deliverable on Time Tracker'));

            throw new Exception('Punch Error: No Deliverable on Time Tracker');
        }

        foreach($currentPunch as $punch){

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));

            $timeInDateFormat = $timeInDate->format('Y-m-d H:i:s');

            PodioItem::update($punchID, $attributes = array(
                'fields' => array(
                    'time-in' => $timeInDateFormat
                )
            ),
                $options = array(
                    'hook' => false
                )
            );

        }

        $punchFieldsArray = array(
            'fields' => array(
                'employee' => array(
                    (int)$employeeDBItemID
                ),
                'time-card' => array(
                    (int)$timeTrackerItemID
                ),
                'time-card-2' => array(
                    (int)$timeCardItemID
                ),
                'project' => array(
                    (int)$projectItemID
                ),
                'billing-cycle' => array(
                    (int)$billingCycleItemID
                ),
                'milestone' => array(
                    (int)$deliverableItemID
                ),
                'punch-billing-type' => $billingTypeVal,

                'time-in' => array(
                    'start' => $podioFormatTimeStamp
                ),

                'dashboard' => array(
                    411301962
                ),
            )
        );

//  This is for conditions of being able to punch-in
//        if($approvalStatusVal == 'Work Ready'){
//            $punchFieldsArray['punch-billing-type'] = $billingTypeNum;
//            $punchFieldsArray['billing-cycle'] = array($billingCycleItemID);
//        }
//        elseif($approvalStatusVal == 'Awaiting Quote' || $approvalStatusVal == 'Pending Client Approval'){
//            $punchFieldsArray['punch-billing-type'] = "Deuce";
//        }

        PodioItem::create(15595843, $punchFieldsArray
        );


        PodioItem::update($deliverableItemID, $attributes = array(
            'fields' => array(
                'time-clock'=>'Working'
            ),
            $options = array(
                'hook' => false
            )
        ));


    }

    if($revisionToVal == 'Punch Out'){

        foreach($currentPunch as $punch) {

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));



            $totalDuration = $timeInDate->diff($dateTimeStamp);

            $totalDays = $totalDuration->d;
            $totalHours = $totalDuration->h;
            $totalMinutes = $totalDuration->i;
            $totalSeconds = $totalDuration->s;

            $totalDurationMinutes = $totalDays * 24 * 60;
            $totalDurationMinutes += $totalHours * 60;
            $totalDurationMinutes += $totalMinutes;
            $totalDurationMinutes += $totalSeconds / 60;

            if($billingTypeVal == "Billable"){
                $roundedMinutes = ceil($totalDurationMinutes / 15) * 15;

                $totalDurationSeconds = $roundedMinutes * 60;
            }
            else{
                $totalDurationSeconds = $totalDurationMinutes * 60;
            }


            PodioItem::update($punchID, $attributes = array(
                'fields' => array(
                    'time-out' => $podioFormatTimeStamp,
                    'total-duration' => array(
                        (int)$totalDurationMinutes
                    )
                )
            ),
                $options = array(
                    'hook' => false
                )
            );
        }

        PodioItem::update($deliverableItemID, $attributes = array(
            'fields' => array(
                'time-clock'=>"Idle"
            )
        ),
            $options = array(
                'hook' => false
            )
        );





    }//end pm_time_puncher function




    return [
        'success' => true,
        'result' => $itemID,
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