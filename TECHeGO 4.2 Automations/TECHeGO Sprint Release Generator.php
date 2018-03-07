<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/17/2016
 * Time: 1:06 PM
 */

date_default_timezone_set('America/Denver');


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

    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));
    $EndofWeekDate = strtotime(date("Y-m-d H:i:s", strtotime($todaysDate). " +".$i."days");

    print_r($EndofWeekDate);
    exit;


    $EnvireTeamItemID = 453152478;
    $SilkSlopesTeamItemID = 453152460;

    $TeamsArray = array($EnvireTeamItemID, $SilkSlopesTeamItemID);



    //SprintFieldsArray
    $SprintItem = array('fields'=>array());


    //ReleaseFieldsArray
    $ReleaseItem = array('fields'=>array());








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