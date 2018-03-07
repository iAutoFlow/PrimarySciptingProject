<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/17/2016
 * Time: 8:36 AM
 */



date_default_timezone_set('America/Denver');
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
    $item = PodioItem::get($itemID);
    $TriggerEvent = $item->fields['trigger-events']->values[0]['text'];

    //Set Default Trigger Value
    $TriggerValue = "...";



///////////If Trigger Value == "Create Weekly Login Record"////////////////////////////////////////////////////////////////////////////////////////
    if($TriggerEvent == "Add Property Need") {

        //Create Proper Needs Item
        $CreateItem = PodioItem::create(16890776, array(
            'fields'=>array(
                'property'=>(int)$itemID
            )
        ));

        //Update Trigger Item to Created
        $UpdateTriggerStatus = PodioItem::update($itemID, array(
            'fields'=>array(
                'trigger-events'=>"Created"
            )
        ),
            array('hook'=>false)
        );


    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    ///////////If Trigger Value == "Offer"////////////////////////////////////////////////////////////////////////////////////////
    if($TriggerEvent == "Create Offer") {

        //Create Proper Needs Item
        $CreateItem = PodioItem::create(16879250, array(
            'fields'=>array(
                'property'=>(int)$itemID
            )
        ));

        //Update Trigger Item to Created
        $UpdateTriggerStatus = PodioItem::update($itemID, array(
            'fields'=>array(
                'trigger-events'=>"Created"
            )
        ),
            array('hook'=>false)
        );


    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////













    //Sleep 15 Seconds
    sleep(15);



    //Reset Trigger Value
    $UpdateTriggerItem = PodioItem::update($itemID, array(
        'fields' => array('trigger-events' => $TriggerValue)),
        array('hook' => false));





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