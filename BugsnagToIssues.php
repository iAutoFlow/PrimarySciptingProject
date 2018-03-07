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
    $requestPayload = $event['request']['payload'];

///AUTOMATION START


    $requestPayload = json_encode($requestPayload);
    $requestJson = json_decode($requestPayload);

    $project = $requestJson->project->name;

    if($project == "print.thatapp.io"){
        $product = 447156641;
    }
    if($project == "sync.thatapp.io"){
        $product = 447157609;
    }
    if($project == "hoist.thatapp.io"){
        $product = 447155395;
    }

    $error = $requestJson->error;

    $describe = "URL: ".$error->url." \nMessage: ".$error->message." \nFirst Received: ".$error->firstReceived ."\nRequest URL: ".$error->requestUrl." \nSeverity: ".$error->severity." \nException Class: ".$error->exceptionClass;

    $issuesFieldsArray = array(
        'fields'=>array(
            'name-it'=>"Bugsnag Error ID: ".$error->errorId,
            'type'=>"Bugsnag",
            'category'=>"Reported",
            'environment-2'=>"Production",
            'responsible'=>"Developers",
            'describe-it'=>$describe,
            'assign-it-2'=>array(183036609),
            'release'=>453152478,
            'product'=>$product,
        )
    );

    PodioItem::create(15677854, $issuesFieldsArray);


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
            'result' => $issuesFieldsArray,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}

?>