<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 4:12 PM
 */

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


//    $todaysDate = date_create("now");
//    $todaysDateFormat = date_format($todaysDate, "L");
//    $ApprovalDateUTC = new DateTime((string)$todaysDateFormat, new DateTimeZone('UTC'));
//    $ApprovalDateMST = $ApprovalDateUTC->setTimezone(new DateTimeZone('America/Denver'));
//    $FinalDateFormat = $ApprovalDateMST->format('Y-m-d H:i:s');

    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));




    //if Triggered from the 2 - Projects Space Deliverables app
    //if($triggerValue == "Work Ready" || $triggerValue == "Complete"){}
    if($appID == 13868972) {
        $approvalStatus = $item->fields['approval-status']->values[0]['text'];

        $RelatedDeliverable = PodioItem::get_references($itemID);
        foreach ($RelatedDeliverable as $relatedItem) {
            //Admin
            if ($relatedItem['app']['name'] == 'Deliverables') {
                $ClientDeliverableItem = $relatedItem['items'];
                $ClientDeliverItemID = $relatedItem['item_id'];
                $ProjectDeliverable = PodioItem::get($ClientDeliverItemID);
                $ProjectItemID = $ProjectDeliverable->fields['project']->values[0]->item_id;
            }
        }
    }







    //When Triggered From a Client Space, Do this

    else {
        $ClientDeliverItemID = $itemID;
        $approvalStatus = $item->fields['approval']->values[0]['text'];
        $ProjectDeliverableID = $item->fields['action-item']->values[0]->item_id;
        $ProjectItemID = $item->fields['project']->values[0]->item_id;
    }



    $ClientProjectItem = PodioItem::get($ProjectItemID);
    $projectAuthorizedApprovers = $ClientProjectItem->fields['authorized-approvers']->values;

    $AuthorizedApproversArray = array();
    foreach($projectAuthorizedApprovers as $approver) {
        $AuthorizedUserProfileID = $approver->profile_id;
        array_push($AuthorizedApproversArray, $AuthorizedUserProfileID);
    }


    $delivAppVoting = PodioVoting::get_voting_id($appID);
    $votingID = $delivAppVoting[0]['voting_id'];

    $ApprovalVoteInfo = PodioVoting::get_result_for_item($ClientDeliverItemID, $votingID);

    $votingAnswers = $ApprovalVoteInfo['voting']['answers'];
    foreach($votingAnswers as $answer){
        if($answer->text == "Yes"){
            $votingYES = $answer->answer_id;
        }
        if($answer->text == "No"){
            $votingNo = $answer->answer_id;
        }

    }

    $YesVotes = array();
    $YesVoteArray = $ApprovalVoteInfo['values'];
    $keys = array_keys($YesVoteArray);
    $YesVoteArray = $YesVoteArray[$keys[0]]['users'];

//get the array of users who have voted
    foreach($YesVoteArray as $User){
        array_push($YesVotes, $User['profile_id']);

    }

    $Status = "...";
    $FinalApprovalStatus = $approvalStatus;
    if($FinalApprovalStatus == "Work Ready" || $FinalApprovalStatus == "Pending Client Approval"){
        foreach($AuthorizedApproversArray as $approver){
            foreach($YesVotes as $vote){
                if($approver == $vote){
                    $FinalApprovalStatus = "Work Ready";
                    $Status = "Active";
                    break;
                }
            }
        }
    }



    $UpdateClientDelivApprovalStatus = PodioItem::update($ClientDeliverItemID, array(
        "fields" => array(
            'approval' => $FinalApprovalStatus
        ),
        array(
            'hook' => false
        )
    ));

    $UpdateProjectDelivApprovalStatus = PodioItem::update($ProjectDeliverableID, array(
        "fields" => array(
            'approval-status' => $FinalApprovalStatus,
            'status' => $Status,
            'approval-date'=>$todaysDate
        ),
        array(
            'hook' => false
        )
    ));













    $event['response'] = [
        'status_code' => 200,
        'content' => $FinalApprovalStatus,
        'content_type' => "json"
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => "fail"
            ,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}