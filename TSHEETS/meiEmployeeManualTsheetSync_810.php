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

    $employeesAppID = 17977954;
    $tsheetsAppID = 18293481;
    $NTPAppID = 17978239;

    $access_token = "S.2__0ecf451ff55442eaa2443a64c84ccb537af6bd1b";
    $tsheets = new DreamFactory\Services\TSheets\TSheetsRestClient(1, $access_token);

    function getNTPbyJobCode($jobcodeID, $jobcodeName){

        appAuth($NTPAppID);

        $NTPAppID = 17978239;

        $ntpFilter = PodioItem::filter($NTPAppID, ['filters'=>['tsheet-id'=>(string)$jobcodeID]]);

        $ntpItemID = $ntpFilter[0]->item_id;

        if(!$ntpItemID){

            $ntpFilter = PodioItem::filter($NTPAppID, ['filters'=>['quickbooksnamehidden'=>(string)$jobcodeName]]);

            $ntpItemID = $ntpFilter[0]->item_id;

        }

        if(!$ntpItemID){

            $ntpFilter = PodioItem::filter($NTPAppID, ['filters'=>['old-qb-name'=>(string)$jobcodeName]]);

            $ntpItemID = $ntpFilter[0]->item_id;

        }

        if($ntpItemID){

            PodioItem::update($ntpItemID, ['fields'=>['tsheet-id'=>(string)$jobcodeID]]);

        } else { return false; }

        return $ntpItemID;

    };

    $employeesAppAuth = appAuth($employeesAppID);

    $item = PodioItem::get($item_id);

    $employeeTsheetsID = $item->fields['tsheets-id']->values;

//   $now = new DateTime('now',new DateTimeZone('America/Chicago'));

//   //dd($now);

//   $now = $now->format('U');

    // $halfHourAgo = date("c", strtotime("-1 day", time()));

    $startDate  = date("Y-m-d", strtotime("-2 days", time()));
    $endDate = date("Y-m-d", strtotime("-5 hours", time()));

    // dd($halfHourAgo);


    $page = 0;

    //do{

    //'modified_since' => $halfHourAgo,

    $response = $tsheets->get(DreamFactory\Services\Tsheets\ObjectType::Timesheets, array('start_date' => $startDate,'end_date' => $endDate, 'user_ids' => (int)$employeeTsheetsID)); //, 'supplemental_data' => 'yes','page' => $page

    dd($response);


    //  }





///AUTOMATION START



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