<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/3/2016
 * Time: 1:41 PM
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

    //Get fields values from item
    $Classification = $item->fields['classification']->values[0]['text'];
    $Status = $item->fields['status']->values[0]['text'];
    $Description = $item->fields['additional-information']->values;
    $Deliverable = $item->fields['deliverable']->values[0]->item_id;
    $Project = $item->fields['project']->values[0]->item_id;


    //Format Date
    $todaysDate = date_create("now");
    $dateTimeStamp = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));
    $FormatTimeStamp = $dateTimeStamp->format("Y-m-d H:i:s");



    //if Resolved Update Date Resolved to Fields Array
    if($Status == "Resolved" || $Status == "Closed"){
        $UpdateResolved = PodioItem::update($itemID, array(
            'fields'=>array(
                'date-resolved'=>array('start'=>$FormatTimeStamp),
            )
        ),
            array('hook'=>false));
    }


    //Get Related Help Desk Ticket Item
    $RelatedItems = PodioItem::get_references($itemID);
    foreach($RelatedItems as $referencedItem){
        if($referencedItem['app']['name'] == 'Help Desk'){
            $ClientTicketItemID = $referencedItem['items'][0]['item_id'];
        }
    }


    //Get Client Deliverable Item ID
    if($Deliverable) {
        $Get42Deliverable = PodioItem::get_references($Deliverable);
        foreach ($Get42Deliverable as $referencedItem) {
            if ($referencedItem['app']['name'] == 'Deliverables') {
                $ClientDelivItemID = $referencedItem['items'][0]['item_id'];
            }
        }
    }

    //Get Client Space Related Project Item
    if($Project) {
        $Get42Project = PodioItem::get_references($Project);
        foreach ($Get42Project as $referencedItem) {
            if ($referencedItem['app']['name'] == 'Projects') {
                $ClientProjectItemID = $referencedItem['items'][0]['item_id'];
            }
        }
    }


    //Fields Array
    $FieldsArray = array(
        'fields' => array(
            'status'=>$Status,
            'classification'=>$Classification,
            'additional-information'=>$Description,
        )
    );

    //Add Values to Field Array if not blank
    if($ClientProjectItemID){
        $FieldsArray['fields']['project'] = array((int)$ClientProjectItemID);
    }

    if($ClientDelivItemID){
        $FieldsArray['fields']['deliverable'] = array((int)$ClientDelivItemID);
    }



    //Update Trigger Item with Newly Created Item Relationship
    $UpdateTickitInClientSpace = PodioItem::update($ClientTicketItemID, $FieldsArray, array('hook'=>false));




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





