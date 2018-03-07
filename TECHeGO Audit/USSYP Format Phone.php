<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/24/2016
 * Time: 9:43 AM
 */



date_default_timezone_set('America/Denver');
$Curl = new\Curl\Curl();
//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
class PodioSessionManager {
    private static $connection_id = 191;
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


    //Create Function
    function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) > 10) {
            $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree . '-' . $lastFour;
        }

        return $phoneNumber;
    }


    //Get Trigger Subscription Item
    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    //Get Trigger App
    $App = PodioApp::get($appID);
    $AppFields = $App->fields;

    //Get Phone Fields from Trigger App.
    $PhoneExFieldID = array();
    foreach($AppFields as $fields){
        if($fields->type == "phone" && $fields->status == "active"){
            $FieldExID = $fields->external_id;
            array_push($PhoneExFieldID, $FieldExID);
        }
    }



    //For Each phone Field in Trigger Item
    foreach ($PhoneExFieldID as $field) {
        $PhonesArray = array();
        $phones = $item->fields[$field]->values;

        //For Each Phone Value -> Format and Add to Array
        foreach ($phones as $phone) {
            $phoneNumber = $phone['value'];
            $phoneType = $phone['type'];
            $FormattedPhone = formatPhoneNumber($phoneNumber);
            $PhoneComplete = array('type' => $phoneType, 'value' => (string)$FormattedPhone);
            array_push($PhonesArray, $PhoneComplete);

        }


        //Update Trigger Item w/ New formatted Phone Values
        $UpdateTriggerValue = PodioItem::update($itemID, array(
            'fields' => array($field => $PhonesArray)),
            array('hook' => false)
        );

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