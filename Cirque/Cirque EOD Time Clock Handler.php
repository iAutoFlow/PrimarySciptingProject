<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
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


    $todaysDate = date_create("LLL");
    $CurrentDateFormatted = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));

    $ProjectManagementSpaceID = 2337777;
    $TimeClockAppID = 8699095;
    $TCStatusFieldXID = 'status';
    $TCActionFieldXID = 'action';
    $TCOutTimeFieldXID = 'out-time';

    $filterTimeClockItems = PodioItem::filter($TimeClockAppID, array('filters' => array($TCStatusFieldXID => "Working")));
    foreach($filterTimeClockItems as $punch){
        $punchItemID = $punch->item_id;
        $updatePunchItem = PodioItem::update($punchItemID, array(
            'fields'=>array(
                $TCOutTimeFieldXID => array('start' => $TodaysDateFormatted->format('Y-m-d H:i:s')),

            )
        ));
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