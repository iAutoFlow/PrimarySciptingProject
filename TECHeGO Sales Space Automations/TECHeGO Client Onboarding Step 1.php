<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/14/2016
 * Time: 4:29 PM
 */
//O-AUTH

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



    //Trigger Item Info.
    $CompanyName = $item->fields['company-name-in-podio']->values;
    $LeadStatus = $item->fields['status']->values[0]['text'];
    $LeadItemID = (int)$itemID;

    $ClientsAppID = 13940709;
    $OrgManagementAppID = 14143381;

    //Main function block

    //Fields Array
    $FieldsArray = array();

    if($LeadStatus == 'ENGAGED') {

        //Filter Clients App for Existing Item
        $FilterClients = PodioItem::filter($ClientsAppID, array('filters' => array('company2'=>(int)$LeadItemID)));
        $ClientItemID = $FilterClients[0]->item_id;

        if (!$ClientItemID) {
            $FieldsArray['fields']['company2'] = array((int)$LeadItemID);
            $FieldsArray['fields']['title'] = $CompanyName;

            $CreateNewClientItem = PodioItem::create($ClientsAppID, $FieldsArray);
            $ClientItemID = $CreateNewClientItem->item_id;
        }





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