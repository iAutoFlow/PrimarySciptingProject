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
    $JobItemID = $item->item_id;
    $ProjectItemID = $item->fields['project']->values[0]->item_id;
    $JobStatus = $item->fields['status']->values[0]['text'];


    $AssignedTo = $item->fields['assigned-to-2']->values;
    $AssignedContactsProfileIDArray = array();

    foreach($AssignedTo as $assignee) {
        $MemberItemID = $assignee->item_id;
        $MemberItem = PodioItem::get($MemberItemID);
        $MemberContact = $MemberItem->fields['contact']->values[0]->profile_id;

        //$UserContact = PodioContact::get_for_user((int)$MemberContact->user_id);
        //$AssignedUserProfileID = $UserContact->profile_id;
        array_push($AssignedContactsProfileIDArray, $MemberContact);
    }



//Add Comment if Job is neither Active nor
    if($JobStatus == "Completed" || $JobStatus == "On Hold"){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => "Status must be Active."
        ));

        $updateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'time-puncher' => "Error"
            ),
            array(
                'hook' => false
            )
        ));
        exit;
    }



    $delivRevision = PodioItemRevision::get($itemID, $revision_id);

    $triggerUserID = $delivRevision->created_by->id;
    $userContact = PodioContact::get_for_user( $triggerUserID );
    $triggerUserProfileID = $userContact->profile_id;
    $triggerUserName = $userContact->name;

    $AssignToCheck = 'No';
    if(in_array($triggerUserProfileID, $AssignedContactsProfileIDArray) == 'True') {
        $AssignToCheck = 'Yes';
    }


    if($AssignToCheck == 'No'){
        $AddComment = PodioComment::create('item', $itemID, array(
            'value' => $triggerUserName . " " . "is not assigned to this Job."));
        $updateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'time-puncher' => "Error"),
            array('hook' => false)));
        exit;
    }

    else{

        //Get Employee ItemID
        $employeeDBFilter = PodioItem::filter(15357642, $attributes = array("filters"=>array('contact'=>$triggerUserProfileID)));
        $employeeDBItemID = $employeeDBFilter[0]->item_id;

        //If Employee not found, Comment on Item
        if(!$employeeDBItemID) {
            $AddComment = PodioComment::create('item', $itemID, array(
                'value' => $triggerUserName . " " . "was not found in the Employee Database."
            ));
            $updateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array('time-puncher' => "Error"),
                array('hook' => false)));
            exit;
        }


        //Get TimeCard
        else{
            $FilterTimeCards = PodioItem::filter(16447821, array("filters"=>array('employee'=>array((int)$employeeDBItemID))));
            $TimeCardItemID = $FilterTimeCards[0]->item_id;
            if(!$TimeCardItemID) {
                $AddComment = PodioComment::create('item', $itemID, array(
                    'value' => $triggerUserName . " " . "does not have a current Time Card."));
                $updateTriggerItem = PodioItem::update($itemID, array(
                    'fields' => array(
                        'time-puncher' => "Error"),
                    array('hook' => false)));
                exit;
            }
        }

        $triggerTimeStamp = $delivRevision->created_on;
        $dateTimeStamp = new DateTime((string)$triggerTimeStamp, new DateTimeZone('America/Denver'));
        $podioFormatTimeStamp = $dateTimeStamp->format("Y-m-d H:i:s");

        if($revisionToVal == 'Punch In') {

            $CreateTimePunch = PodioItem::create(16261847, array(
                'fields' => array(
                    'employee' => (int)$employeeDBItemID,
                    'time-card-2' => array((int)$TimeCardItemID),
                    'project' => array((int)$ProjectItemID),
                    'job' => array((int)$itemID),
                    'time-in' => array('start' => $podioFormatTimeStamp),
                    'dashboard' => 450150940,
                )
            ));
            $UpdateTriggerValue = PodioItem::update($itemID, array(
                'fields' => array(
                    'time-puncher'=>'Working',
                )
            ));
        }

        elseif($revisionToVal == 'Punch Out'){

            $currentPunch = PodioItem::filter(16261847, array("filters"=>array('employee'=>array((int)$employeeDBItemID),'job'=>array((int)$JobItemID),'status'=>'Working')));
            $CurrentPunchItemID = $currentPunch[0]->item_id;

            $punchItem = PodioItem::get($CurrentPunchItemID);
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

            $totalDurationSeconds = $totalDurationMinutes * 60;



            $UpdateCurrentPunch = PodioItem::update($CurrentPunchItemID, array(
                'fields' => array(
                    'time-out' => array('start' => $podioFormatTimeStamp),
                    'total-duration' => array((int)$totalDurationSeconds),
                )
            ));
            $UpdateTriggerValue = PodioItem::update($itemID, array(
                'fields' => array(
                    'time-puncher'=>'Idle',
                )
            ));

        }








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