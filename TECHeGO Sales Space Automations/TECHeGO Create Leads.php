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

    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));
    $FormatDate = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));
    $DateFormatted = $FormatDate->format('Y-m-d H:i:s');

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    $item = PodioItem::get($itemID);
    $appID = $item->app->app_id;

    $ContactItemID = $item->fields['contact']->values[0]->item_id;
    $CompanyName = $item->fields['company-name-in-podio']->values;
    $CompanySize = $item->fields['company-size']->values[0]['text'];
    $Industry = $item->fields['industry']->values[0]['text'];
    $Notes = $item->fields['notes']->values;
    $Notes2 = $item->fields['how-do-you-use-your-current-automations']->values;


    //New Lead Fields Array
    $FieldsArray = array(
        'fields'=>array(
            'source' => "AVA Lead",
            'date-added' =>  array('start' => $todaysDate),
            'account-manager' => 125189525,
            //'ava-lead' => (int)$itemID,
        ));


    //Set Fields Array Values
    if($CompanyName){$FieldsArray['fields']['company-name-in-podio'] = $CompanyName;}

    if($ContactItemID){$FieldsArray['fields']['company-contacts'] = (int)$ContactItemID;}

    if($CompanySize){$FieldsArray['fields']['company-size'] = $CompanySize;}

    if($Notes && $Notes2){$FieldsArray['fields']['describe-needs'] = $Notes."--".$Notes2;}
    if($Notes && !$Notes2){$FieldsArray['fields']['describe-needs'] = $Notes;}
    if(!$Notes && $Notes2){$FieldsArray['fields']['describe-needs'] = $Notes2;}

    if($Industry){
        $FilterIndustryItems = PodioItem::filter(16408531, array('filters'=>array('title'=>$Industry)));
        $IndustryItemID = $FilterIndustryItems[0]->item_id;
        if($IndustryItemID){
            $FieldsArray['fields']['industry-2'] = (int)$IndustryItemID;
        }
    }



    //Create New Lead Item
    $CreateLead = PodioItem::create(2933904, $FieldsArray);





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