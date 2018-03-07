<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
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
    $appID = $item->app->app_id;

    //Format Current Date/Time
    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));



    //Trigger Values
    $CreateItems = $item->fields['status']->values[0]['text'];

    if ($CreateItems == 'Completed') {

        //Get Trigger Item Field Values
        $MeetingTitle = $item->fields['meeting-title']->values;
        $LeadItemID = $item->fields['app-reference']->values[0]->item_id;
        $MeetingDate = $item->fields['date']->start;
        $FollowUpDate = $item->fields['follow-up-date']->start;
        $MeetingAgenda = $item->fields['agenda']->values;
        $MeetingType = $item->fields['type']->values[0]['text'];
        $MeetingPurpose = $item->fields['purpose']->values[0]['text'];
        $MinutesNotes = $item->fields['minutesnotes']->values;
        $MeetingNurturing = $item->fields['nurturing']->values;


        //Create Interactions Field Array
        $InteractionFieldsArray = array(
            'fields' => array(
                'lead-2' => array((int)$LeadItemID),
                'date' => array('start' => $MeetingDate->format('Y-m-d H:i:s')),

            ));


        //Lead Item
        if ($LeadItemID) {
            $LeadItem = PodioItem::get($LeadItemID);
            $LeadContactItemID = $LeadItem->fields['company-contacts']->values[0]->item_id;
            $InteractionFieldsArray['fields']['contact-2'] = array((int)$LeadContactItemID);
        }

        if ($MeetingNurturing) {
            $MeetingNurturingArray = array();
            foreach ($MeetingNurturing as $nurture) {
                $NutureValue = $nurture['text'];
                array_push($MeetingNurturingArray, $NutureValue);
            }
            $InteractionFieldsArray['fields']['nurturing'] = $MeetingNurturingArray;
        }

        if ($MeetingType) {
            if ($MeetingType == "GoToMeeting") {
                $MeetingType = "Meeting, GoToMeeting";
            } elseif ($MeetingType == "In Person") {
                $MeetingType = "Meeting, In Person";
            }
            $InteractionFieldsArray['fields']['type'] = $MeetingType;
        }

        if ($MeetingPurpose) {
            $InteractionFieldsArray['fields']['purpose'] = $MeetingPurpose;
        }

        if ($MinutesNotes) {
            $InteractionFieldsArray['fields']['title'] = $MinutesNotes;
        }

        if ($FollowUpDate) {
            $InteractionFieldsArray['fields']['follow-up-date'] = array('start' => $FollowUpDate->format('Y-m-d H:i:s'));
        }


        //Create Interaction Item
        $CreateInteraction = PodioItem::create(14919370, $InteractionFieldsArray);

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