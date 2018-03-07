<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */
date_default_timezone_set('America/Denver');
$todaysDate = date("Y-m-d H:i:s", strtotime("now"));

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


//CODE HERE

    $FieldsArray = array('fields' => array(), array('hook' => false));

//Triggered From Proposal Item

    $CreateLineItem = $item->fields['add-line-item']->values[0]['text'];

    if($CreateLineItem == "..." || $CreateLineItem == "Created"){exit;}

    if($CreateLineItem == 'Product Line Item') {
        $FieldsArray['fields']['proposal'] = (int)$itemID;
        $CreateProductLine = PodioItem::create(15856061, $FieldsArray);
    }


    if ($CreateLineItem == 'Subcontractor Service Item') {
        $FieldsArray['fields']['field-material-bid-list'] = (int)$itemID;
        $CreateServiceItem = PodioItem::create(16501379, $FieldsArray);
    }


    if ($CreateLineItem == 'Compare Vendor Sheet') {
        $FieldsArray['fields']['field-material-bid-list'] = (int)$itemID;
        $FieldsArray['fields']['date-created'] = array('start' => $todaysDate);
        $CreateCompSheep = PodioItem::create(16566958, $FieldsArray);
    }




    //Update Trigger Item
    $UpdateTrigger = PodioItem::update($itemID, array(
            'fields' => array(
                'add-line-item' => "Created"),
            array(
                'hook' => false)
        )
    );

    sleep(5);

    $UpdateTrigger = PodioItem::update($itemID, array(
            'fields' => array(
                'add-line-item' => "..."),
            array(
                'hook' => false)
        )
    );






    //Stop Coding HERE



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