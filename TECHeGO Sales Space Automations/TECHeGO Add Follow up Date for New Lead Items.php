<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/17/2016
 * Time: 9:52 AM
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




    //IF Triggered From Leads App///////////////////////////////////////////////////////////////////////////////////
    if($appID == 2933904) {

        $LeadItemID = $itemID;

        //Format Current Date & Time
        $todaysDate = date("Y-m-d H:i:s", strtotime("now" . '+1 day'));
        $FollowUp = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));
        $FollowUpDateFormatted = $FollowUp->format('Y-m-d H:i:s');
        $DOW = $FollowUp->format("l");


        //Add Days if Day of Week is Sat. / Sun.
        if ($DOW == "Saturday") {
            $FollowUpDateFormatted = date('Y-m-d H:i:s', strtotime($FollowUpDateFormatted . "+ 2 day"));
        }
        if ($DOW == "Sunday") {
            $FollowUpDateFormatted = date('Y-m-d H:i:s', strtotime($FollowUpDateFormatted . "+ 1 day"));
        }

    }

    /////////////



    //If Triggered From Interaction Item///////////////////////////////////////////////////////////////////////////////////
    if($appID == 14919370){

        $LeadItemID = $item->fields['lead-2']->values[0]->item_id;
        $ContactDate = $item->fields['date']->values->start;
        $FollowUpDate = $item->fields['follow-up-date']->values['start'];



        //If NO Follow up date is Added to Interaction
        if(!$FollowUpDate) {
            //Format Current Date & Time
            $ContactDateValue = date("Y-m-d H:i:s", strtotime($ContactDate . '+3 day'));
            $FollowUp = new DateTime((string)$ContactDateValue, new DateTimeZone('America/Denver'));
            $FollowUpDateFormatted = $FollowUp->format('Y-m-d H:i:s');
            $DOW = $FollowUp->format("l");
            //Add Days if Day of Week is Sat. / Sun.
            if ($DOW == "Saturday") {
                $FollowUpDateFormatted = date('Y-m-d H:i:s', strtotime($FollowUpDateFormatted . "+ 2 day"));
            }
            if ($DOW == "Sunday") {
                $FollowUpDateFormatted = date('Y-m-d H:i:s', strtotime($FollowUpDateFormatted . "+ 1 day"));
            }

            //Update New Lead Item
            $UpdateInteraction = PodioItem::update($itemID, array(
                'fields' => array(
                    'follow-up-date' => array('start' => $FollowUpDateFormatted)
                )
            ),
                array('hook' => false)
            );
        }

        //Else Format Follow Up Date.
        if($FollowUpDate){
            $FollowUpDateFormatted = $FollowUpDate->format('Y-m-d H:i:s');
        }



    }
    /////////




    //Update New Lead Item///////////////////////////////////////////////////////////////////////////////////
    $UpdateLead = PodioItem::update($LeadItemID, array(
        'fields'=>array(
            'follow-up-date'=>array('start'=>$FollowUpDateFormatted)
        )
    ),
        array('hook'=>false)
    );






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