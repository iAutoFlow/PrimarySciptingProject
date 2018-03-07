<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/19/2016
 * Time: 3:07 PM
 */


date_default_timezone_set('America/Denver');
$Curl = new\Curl\Curl();
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
    $OrgItemID = $requestParams['item_id'];

    $item = PodioItem::get($OrgItemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $ClientOrgID = $item->fields['org-id']->values;
    $HelpDeskAppID = $item->fields['app-id']->values;


    //Get All Current Client Spaces
    $ClientSpaces = PodioSpace::get_for_org($ClientOrgID);


    foreach($ClientSpaces as $space){
        $SpaceID = $space->space_id;

        if($SpaceID && $HelpDeskAppID) {
            $BaseURL = 'https://hoist.thatapp.io/api/v2/PodioCreateHook?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
            $RefType = '&ref_type=space';
            $RefID = '&ref_id='.$SpaceID;
            $HookType = '&type=task.create';
            $HookURL = '&url=https://hoist.thatapp.io/podio_catcher.php?service=help_desk_generator_from_task';
            $AppID = '%26app_id='.$HelpDeskAppID;

            $CurlURL = $BaseURL.$RefType.$RefID.$HookType.$HookURL.$AppID;
            $GetAvailable = $Curl->get($CurlURL);

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


