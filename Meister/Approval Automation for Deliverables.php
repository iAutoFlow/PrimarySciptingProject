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




    //if Triggered from the 2 - Projects Space Deliverables app
    //if($triggerValue == "Work Ready" || $triggerValue == "Complete"){}
    if($appID == 15595774) {
        $approvalStatus = $item->fields['approval-status']->values[0]['text'];
        $projectITEMID = $item->fields['project']->values[0]->item_id;

        $projectITEM = PodioItem::get($projectITEMID);
        $projectName = $projectITEM->fields['title']->values;

        $clientItemID = $projectITEM->fields['client']->values[0]->item_id;
        $clientItem = PodioItem::get($clientItemID);
        $clientProjectAppID = $clientItem->fields['projects-app-id']->values;
        $clientDeliverablesAppID = $clientItem->fields['deliverables-app-id']->values;

        $filterClientProject = PodioItem::filter($clientProjectAppID, array('filters' => array('project-name' => $projectName)));
        $ProjectItemID = $filterClientProject[0]->item_id;
        $ClientProjectItem = PodioItem::get($ProjectItemID);

        $DeliverableUniqueIdentifier = $item->fields['unique-identifier']->values;
        $FilterClientDeliverabes = PodioItem::filter($clientDeliverablesAppID, array("filters" => array('database-job-item' => array((int)$itemID))));
        $ClientDeliverabeItemID = $FilterClientDeliverabes[0]->item_id;

        $numberOFapprovers = $ClientProjectItem->fields['approvers']->values[0]['text'];

        $projectAuthorizedApprovers = $ClientProjectItem->fields['authorized-approvers']->values;
        $AuthorizedApproversArray = array();
        foreach ($projectAuthorizedApprovers as $approver) {
            //$userContact = PodioContact::get_for_user($approver->user_id);
            $AuthorizedUserProfileID = $approver->profile_id;
            array_push($AuthorizedApproversArray, $AuthorizedUserProfileID);
        }


        $delivAppVoting = PodioVoting::get_voting_id($clientDeliverablesAppID);
        $votingID = $delivAppVoting[0]['voting_id'];

        $ApprovalVoteInfo = PodioVoting::get_result_for_item($ClientDeliverabeItemID, $votingID);

        $votingAnswers = $ApprovalVoteInfo['voting']['answers'];
        foreach ($votingAnswers as $answer) {
            if ($answer->text == "Yes") {
                $votingYES = $answer->answer_id;
            }
            if ($answer->text == "No") {
                $votingNo = $answer->answer_id;
            }

        }

        $YesVotes = array();

        $YesVoteArray = $ApprovalVoteInfo['values'];
        $keys = array_keys($YesVoteArray);
        $YesVoteArray = $YesVoteArray[$keys[0]]['users'];

//get the array of users who have voted
        foreach ($YesVoteArray as $User) {
            array_push($YesVotes, $User['profile_id']);

        }

        $Status = "...";
        $FinalApprovalStatus = "Pending Client Approval";
        if ($numberOFapprovers == 'Single') {
            foreach ($AuthorizedApproversArray as $approver) {
                foreach ($YesVotes as $vote) {
                    if ($approver == $vote) {
                        $FinalApprovalStatus = "Work Ready";
                        $Status = "Active";
                        break;
                    }
                }
            }

        }

        $approvedCount = 0;
        if ($numberOFapprovers == 'Multiple') {
            foreach ($AuthorizedApproversArray as $approver) {
                foreach ($YesVotes as $vote) {
                    if ($approver == $vote) {
                        $approvedCount = ($approvedCount + 1);
                    }
                }
            }
        }

        //$AuthorizedArraySize = sizeof($AuthorizedApproversArray);

        if ($approvedCount == sizeof($AuthorizedApproversArray)) {
            $FinalApprovalStatus = "Work Ready";
            $Status = "Active";
        }


        $UpdateClientDelivApprovalStatus = PodioItem::update($ClientDeliverabeItemID, array(
            "fields" => array(
                'approval' => $FinalApprovalStatus
            ),
            array(
                'hook' => false
            )
        ));

        $UpdateProjectDelivApprovalStatus = PodioItem::update($itemID, array(
            "fields" => array(
                'approval-status' => $FinalApprovalStatus,
                'status' => $Status,
            ),
            array(
                'hook' => false
            )
        ));

    }






    //When Triggered From a Client Space, Do this

    else{
        $ClientDeliverabeItemID = $itemID;

        $ProjectDeliverableID = $item->fields['database-job-item']->values[0]->item_id;

        $ProjectItemID = $item->fields['project']->values[0]->item_id;
        $ClientProjectItem = PodioItem::get($ProjectItemID);
        $numberOFapprovers = $ClientProjectItem->fields['approvers']->values[0]['text'];

        $projectAuthorizedApprovers = $ClientProjectItem->fields['authorized-approvers']->values;

        $AuthorizedApproversArray = array();
        foreach($projectAuthorizedApprovers as $approver) {
            //$userContact = PodioContact::get_for_user($approver->user_id);
            $AuthorizedUserProfileID = $approver->profile_id;
            array_push($AuthorizedApproversArray, $AuthorizedUserProfileID);
        }


        $delivAppVoting = PodioVoting::get_voting_id($appID);
        $votingID = $delivAppVoting[0]['voting_id'];

        $ApprovalVoteInfo = PodioVoting::get_result_for_item($ClientDeliverabeItemID, $votingID);

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
        $FinalApprovalStatus = "Pending Client Approval";
        if($numberOFapprovers == 'Single'){
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

        $approvedCount = 0;
        if($numberOFapprovers == 'Multiple'){
            foreach ($AuthorizedApproversArray as $approver){
                foreach ($YesVotes as $vote){
                    if ($approver == $vote){
                        $approvedCount = ($approvedCount + 1);
                    }
                }
            }
        }

        //$AuthorizedArraySize = sizeof($AuthorizedApproversArray);

        if($approvedCount == sizeof($AuthorizedApproversArray)) {
            $FinalApprovalStatus = "Work Ready";
            $Status = "Active";
        }


        $UpdateClientDelivApprovalStatus = PodioItem::update($ClientDeliverabeItemID, array(
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
            ),
            array(
                'hook' => false
            )
        ));

    }











    $event['response'] = [
        'status_code' => 200,
        'content' => $approvedCount,
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