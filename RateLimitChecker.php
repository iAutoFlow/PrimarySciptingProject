<?php
//Authentication
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



try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = (int)$requestParams['item_id'];

///AUTOMATION START

    $requestParams = $event['request']['parameters'];
    $client_key = $requestParams['client_key'];
    $client_secret = $requestParams['client_secret'];

//    $client_key = "hoistpodiolevel2";
//    $client_secret = "AwxPc41rfhJJZR8fXskKUou0SBJMRd9NqDKwjAREjk4o7BfMaQ8hYcwYMnSGkzSY";

    //$platform['api']->get->__invoke("files/rate_limit.log");

    //$testing = $event['response']['content'];

//print_r($testing);exit;

    $result = $testing;

    $result.="Client Key: ".$client_key." | Client Secret: ".$client_secret."<br>";
// Authenticate Podio
    Podio::setup($client_key, $client_secret);

    Podio::authenticate_with_password($username, $password);

    $result.="Podio::authenticate_with_password Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::get(436237677);

    $result.="PodioItem::get Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::filter(4177108);

    $result.="PodioItem::filter Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::create(8773933, array('fields'=>array('duration'=>1)));

    $result.="PodioItem::create Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::update(438367795, array('fields'=>array('duration'=>2)));

    $result.="PodioItem::update Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    $result.="<br><br>";

    //$test = $platform['api']->put->__invoke("files/rate_limit.log", $result);

//END AUTOMATION

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

?>