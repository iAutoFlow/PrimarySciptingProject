<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table


class PodioSessionManager {
    private static $connection_id = 3;
    private static $connection;
    private static $appConnection;
    private static $connectedAppID;
    private static $auth_type;

    public function __construct() {
    }

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
        }
        return self::$connection;
    }

    public static function getAppConnection($app_id) {

        if(self::$connectedAppID !== $app_id) {
            self::$connectedAppID = $app_id;
            self::$appConnection = null;
        }

        if (!self::$appConnection) {
            self::$appConnection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->where('app_id', $app_id)->first();
        }

        if (!self::$appConnection) {

            $connection = self::getConnection();

            Podio::$oauth = new PodioOAuth(
                $connection->access_token,
                $connection->refresh_token
            );

            $app = PodioApp::get(Podio::$auth_type['identifier']);

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'null']);

            $newAppAuth = Podio::authenticate_with_app(Podio::$auth_type['identifier'], $app->token);

            $connection = new \EnvireTech\OauthConnector\Models\OrganizationConnection();
            $connection->name = "App_".(str_replace(" ", "_", $app->config['name']));
            $connection->app_id = $app->app_id;
            $connection->service_id = 16;
            $connection->refresh_token = Podio::$oauth->refresh_token;
            $connection->access_token = Podio::$oauth->access_token;
            $connection->organization_id = 1;
            $connection->created_by_id = 5;
            $connection->private = 0;
            $connection->save();

            self::$appConnection = $connection;

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
        }

        return self::$appConnection;
    }

    public static function getClientId () {
        return self::getConnection()->connectionService->config['client_id'];
    }

    public static function getClientSecret () {
        return self::getConnection()->connectionService->config['client_secret'];
    }

    public static function authtypeUserAVA(){

        Podio::$auth_type = array(
            "type" => "user",
            "identifier" => 1406952
        );

    }

    public static function authtypeApp($app_id){

        Podio::$auth_type = array(
            "type" => "app",
            "identifier" => $app_id
        );

    }

    public function get(){

        if(Podio::$auth_type['type'] == "app"){
            $connection = self::getAppConnection(Podio::$auth_type['identifier']);
        }
        else {
            $connection = self::getConnection();
        }

        return new PodioOAuth(
            $connection->access_token,
            $connection->refresh_token
        );
    }


    public function set($oauth, $auth_type = null){

        //$auth_type = self::$authtype;

        if($auth_type['type'] == "app") {
            $connection = self::getAppConnection($auth_type['identifier']);

            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;

        }
        else {
            $connection = self::getConnection();
            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;
        }


    }


}

function normalAuth(){
    PodioSessionManager::authtypeUserAVA();

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}

function appAuth($app_id){
    PodioSessionManager::authtypeApp($app_id);

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}

// api/v2/JoshTEST?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

try{

    normalAuth();

    $payload = $event['request']['payload'];
    $type = $payload['type'];

    if($type && $type == 'hook.verify'){

        $code = $payload['code'];
        $hook_id = $payload['hook_id'];

        // Validate the webhook
        PodioHook::validate($hook_id, array('code' => $code));

    }

    $requestParams = $event['request']['parameters'];
    $item_id = (int)$requestParams['item_id'];

    if(!$item_id) {
        $item_id = (int)$payload['item_id'];
    }

///PODIO ID VARIABLES
    $pipelineAppID = 15425585;

    $dealStatusExID = 'status';
    $stepsToCompleteExID = 'action-2';

//|||TECHeGO Step Break|||
//Step Item-#S49#
//Step 1 - Trigger / File Name Parse #

    $triggerItem = PodioItem::get($item_id);

    $triggerCurrentSteps = $triggerItem->fields[$stepsToCompleteExID]->values;

    $currentStepsArray = [];

    foreach($triggerCurrentSteps as $step){

        $currentStepsArray[] = $step['text'];

    }

    $newestFileName = $triggerItem->files[count($triggerItem->files)-1]->name;

    if($newestFileName) {

        switch (true) {

//|||TECHeGO Step Break|||
//Step Item-#S50#
//Step 2 - Switch Case 1 - Sizer #

            case (stripos($newestFileName, 'sizer') !== false && ($key = array_search('Sizer', $currentStepsArray)) !== false):

                unset($currentStepsArray[$key]);

                $currentStepsArray = array_values($currentStepsArray);

                PodioItem::update($item_id, [
                    'fields' => [
                        $stepsToCompleteExID => $currentStepsArray
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S51#
//Step 3 - Switch Case 2 - 2 Pager #

            case (stripos($newestFileName, 'pager') !== false && ($key = array_search('2 Pager', $currentStepsArray)) !== false):

                unset($currentStepsArray[$key]);

                $currentStepsArray = array_values($currentStepsArray);

                PodioItem::update($item_id, [
                    'fields' => [
                        $stepsToCompleteExID => $currentStepsArray
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S52#
//Step 4 - Switch Case 3 - Loan Summary #

            case (stripos($newestFileName, 'summary') !== false && ($key = array_search('Loan Summary', $currentStepsArray)) !== false):

                unset($currentStepsArray[$key]);

                $currentStepsArray = array_values($currentStepsArray);

                PodioItem::update($item_id, [
                    'fields' => [
                        $stepsToCompleteExID => $currentStepsArray
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S53#
//Step 5 - Switch Case 4 - Loan Proposal #

            case (stripos($newestFileName, 'proposal') !== false && ($key = array_search('Loan Proposal', $currentStepsArray)) !== false):

                unset($currentStepsArray[$key]);

                $currentStepsArray = array_values($currentStepsArray);

                PodioItem::update($item_id, [
                    'fields' => [
                        $stepsToCompleteExID => $currentStepsArray,
                        $dealStatusExID => "B-TSI"
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S54#
//Step 6 - Switch Case 5 - NBS #

            case (stripos($newestFileName, 'nbs') !== false && ($key = array_search('NBS', $currentStepsArray)) !== false):

                unset($currentStepsArray[$key]);

                $currentStepsArray = array_values($currentStepsArray);

                PodioItem::update($item_id, [
                    'fields' => [
                        $stepsToCompleteExID => $currentStepsArray
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S55#
//Step 7 - Switch Case 6 - B-TSI #

            case (stripos($newestFileName, 'proposal') !== false):

                PodioItem::update($item_id, [
                    'fields' => [
                        $dealStatusExID => "B-TSI"
                    ]
                ]);

                break;

//|||TECHeGO Step Break|||
//Step Item-#S56#
//Step 8 - Switch Case 7 - A-TSE #

            case (stripos($newestFileName, 'executed') !== false):

                PodioItem::update($item_id, [
                    'fields' => [
                        $dealStatusExID => "A-TSE"
                    ]
                ]);

                break;

        }//end switch

    }

//|||TECHeGO Step Break|||
//End of Script#

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