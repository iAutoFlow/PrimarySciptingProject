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

    // api/v2/dateBlockGenerator?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

    // https://hoist.thatapp.io/api/v2/dateBlockGenerator?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634&item_id=644926263

    // Les Variables

    $item = PodioItem::get($item_id);

    $schedulingAppID = 17995363;
    $generatorAppID = 18965652;

    $employeeItemId = $item->fields['employee']->values[0]->item_id;
    $employeeItem = PodioItem::get($employeeItemId);
    $employeeUserID = $employeeItem->fields['employee']->values[0]->profile_id;
    $dateRangeStart = $item->fields['range']->start;
    $dateRangeEnd = $item->fields['range']->end;
    $dateRangeEnd = $dateRangeEnd->add(new DateInterval('PT1M'));
    $weekdays = $item->fields['weekdays']->values;
    $startTime = $item->fields['start-time']->values[0]['text'];
    $endTime = $item->fields['end-time']->values[0]['text'];
    $timeBlockSize = $item->fields['timeblock-size']->values;
    $automationTrigger = $item->fields['automation-trigger']->values[0]['text'];



    if($automationTrigger == "Run"){

        PodioItem::update($item_id, array('fields'=>array('automation-trigger'=>array("Working"))));

//  if(!$employeeItemId || !$employeeUserID || !$dateRangeStart || !$dateRangeEnd || !$weekdays || !$startTime || !$endTime || !$timeBlockSize){

        // Automation Start
        $weekdayValues = [];
        foreach($weekdays as $day){
            array_push($weekdayValues, $day['text']);
        }


        $endTimeFormatted = str_replace(":", ".", $endTime);
        $startTimeFormatted = str_replace(":", ".", $startTime);
        $startTimeHIS = $startTime.":00";
        $workTime = (int)$endTimeFormatted - (int)$startTimeFormatted;
        $timeBlockSizeHours = ((int)$timeBlockSize/60) / 60;
        $timeBlockSizeMinutes = ((int)$timeBlockSize/60);
        $numberOfBlocks = floor($workTime / $timeBlockSizeHours);
        $timesArray = [];

        $daterange = new DatePeriod($dateRangeStart, new DateInterval('P1D'), $dateRangeEnd);

        // dd($daterange);

        $dates = [];

        //  $count = 0;

        foreach($daterange as $date){

            //  $count++;

            //  dd($date);

            $dateUnix = $date->format('U');

            $dateFormat = $date->format('D M j, Y G:i:s T');

            if(in_array(substr($dateFormat, 0, 3), $weekdayValues)) {

                $loopStartDateTime = $date->format("Y-m-d $startTimeHIS");
                $startDateDO = date_create_from_format("Y-m-d H:i:s",$loopStartDateTime);
                $startDateUnix = $startDateDO->format('U');
                $loopEndDateTime = date("Y-m-d H:i:s", strtotime("+$timeBlockSizeMinutes minutes", $startDateUnix));
                $endDateDO = date_create_from_format("Y-m-d H:i:s",$loopEndDateTime);
                $endDateUnix = $endDateDO->format('U');

                for($i = 1; $i <= $numberOfBlocks; $i++) {

                    $blockCreateArray = [
                        'fields' => [
                            'time' => ['start'=>$loopStartDateTime,'end'=>$loopEndDateTime],
                            'suggested-attendees' => $employeeUserID
                        ]];

                    $newBlock = PodioItem::create($schedulingAppID, $blockCreateArray);

                    $loopStartDateTime = date("Y-m-d H:i:s", strtotime("+$timeBlockSizeMinutes minutes", $startDateUnix));
                    $startDateDO = date_create_from_format("Y-m-d H:i:s",$loopStartDateTime);
                    $startDateUnix = $startDateDO->format('U');
                    $loopEndDateTime = date("Y-m-d H:i:s", strtotime("+$timeBlockSizeMinutes minutes", $endDateUnix));
                    $endDateDO = date_create_from_format("Y-m-d H:i:s",$loopEndDateTime);
                    $endDateUnix = $endDateDO->format('U');

                }
            }
        }

        PodioItem::update($item_id, array('fields'=>array('automation-trigger'=>array("Success!"))));
        PodioComment::create('item', $item_id, array('value'=>"Dateblocks successfully created!"));

//     } else {

//         PodioItem::update($item_id, array('fields'=>array('automation-trigger'=>array("Error"))));
//         PodioComment::create('item', $item_id, array('value'=>"Not all necessary values are present for the date generator to run.  Please double-check that all fields are populated."));

//  }

        //  dd($count);

    } else { return; }

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