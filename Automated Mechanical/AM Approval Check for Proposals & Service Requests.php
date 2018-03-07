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

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    //Format Current Date/Time
    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));
    $FormatDate = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));



    if($appID == 15856042) {

        $fieldsArray = array(
            'fields'=>array(
            )
        );

        $approvalStatus = $item->fields['approval-status']->values[0]['text'];
        $AuthorizedApprovers = $item->fields['approver']->values;

        $AuthorizedApproversArray = array();
        foreach($AuthorizedApprovers as $approver) {
            $AuthorizedUserProfileID = $approver->profile_id;
            array_push($AuthorizedApproversArray, $AuthorizedUserProfileID);
        }


        $AppVoting = PodioVoting::get_voting_id($appID);
        $votingID = $AppVoting[0]['voting_id'];

        $ApprovalVoteInfo = PodioVoting::get_result_for_item($itemID, $votingID);

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


        $FinalApprovalStatus = "Pending";
        foreach($AuthorizedApproversArray as $approver){
            foreach ($YesVotes as $vote) {
                if ($approver == $vote) {
                    $FinalApprovalStatus = "Approved";
                    break;
                }
            }}

        if ($FinalApprovalStatus == "Approved") {
            $fieldsArray['fields']['approval-date-2'] = array('start' => $FormatDate->format('Y-m-d H:i:s'));
        }

        $fieldsArray['fields']['approval-status'] = $FinalApprovalStatus;
        $UpdateClientDelivApprovalStatus = PodioItem::update($itemID, $fieldsArray, array('hook' => false));


    }




    if($appID == 15856045) {


        $fieldsArray = array(
            'fields'=>array(
            )
        );

        $approvalStatus = $item->fields['approval-status']->values[0]['text'];
        $AuthorizedApprovers = $item->fields['authorized-approver-2']->values;

        $AuthorizedApproversArray = array();
        foreach($AuthorizedApprovers as $approver) {
            $AuthorizedUserProfileID = $approver->profile_id;
            array_push($AuthorizedApproversArray, $AuthorizedUserProfileID);
        }


        $AppVoting = PodioVoting::get_voting_id($appID);
        $votingID = $AppVoting[0]['voting_id'];

        $ApprovalVoteInfo = PodioVoting::get_result_for_item($itemID, $votingID);

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

        $FinalApprovalStatus = "Pending";
        foreach($AuthorizedApproversArray as $approver){
            foreach ($YesVotes as $vote) {
                if ($approver == $vote) {
                    $FinalApprovalStatus = "Approved";
                    break;
                }
            }}

        if ($FinalApprovalStatus == "Approved") {
            $fieldsArray['fields']['approval-date'] = array('start' => $FormatDate->format('Y-m-d H:i:s'));
        }

        $fieldsArray['fields']['approval-status'] = $FinalApprovalStatus;
        $UpdateClientDelivApprovalStatus = PodioItem::update($itemID, $fieldsArray, array('hook' => false));

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