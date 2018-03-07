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


    $weeklyTimeCyclesAppID = 18589389;
    $weeklyTimeSheet_currentCyclesViewID = 33657231;

    $projectRollupAppID = 17977584;

    ///AUTOMATION START

    $access_token = "S.2__0ecf451ff55442eaa2443a64c84ccb537af6bd1b";


    $tsheets = new DreamFactory\Services\TSheets\TSheetsRestClient(1, $access_token);

    //////////////////////////////////////////////////////////////////////////////////

    function podioizeISODate($ISODate){

        $date = DateTime::createFromFormat(DateTime::ISO8601, $ISODate);

        $date->setTimeZone("America/Denver");


        return $date->format("Y-m-d H:i:s");

    }

    appAuth($employeesAppID);

    $allEmployees = PodioItem::filter($employeesAppID, ['limit'=>500]);

    function findEmployee($tsheetsID, $employees){

        $employeeItemId = null;

        foreach($employees as $employee){

            if($employee->fields['tsheets-id']->values == $tsheetsID){

                $employeeItemId = $employee->item_id;

            }

        }

        return $employeeItemId;

    };

    function getNTPbyJobCode($jobcodeID, $jobcodeName){

        $NTPAppID = 17978239;

        appAuth($NTPAppID);


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

    function getGeolocationLatLng($punchInTime, $userID){

        $access_token = "S.2__0ecf451ff55442eaa2443a64c84ccb537af6bd1b";

        $tsheets2 = new DreamFactory\Services\TSheets\TSheetsRestClient(1, $access_token);

        $geolocations = $tsheets2->get(DreamFactory\Services\Tsheets\ObjectType::Geolocations, array('modified_since' => $punchInTime, 'user_ids'=>$userID));

        $geolocationFirst = array_values($geolocations['results']['geolocations'])[0];

        $lat = $geolocationFirst['latitude'];

        $lng = $geolocationFirst['longitude'];

        if($lat && $lng){

//            $latlng = $lat.",".$lng
            $latlng = ['lat'=>$lat, 'lng'=>$lng];
        } else {  }

        return $latlng;

    }

    function googleMapsReverseGeocode($latlng){

//        $gMapsKey = "AIzaSyBZ62izWdstoT0u3nDj9REknpXgJaaKV_U";

        $gMapsKey = "AIzaSyBOZhRSgc6WeHAMinxp4YDksdc_8XlTDsY";

        $curlGmaps = new \Curl\Curl();

        $baseGoogleGeocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?key=".$gMapsKey."&latlng=";

        $finalUrl = $baseGoogleGeocodeURL.$latlng;

        $response = $curlGmaps->get($finalUrl);

        return $response->results[0]->formatted_address;

    }


    $result = "";

    $page = 1;  // USE AS OFFSET

    $address = "Not Set";


    // Get current time, then get 30 minutes ago. //////////////


// 	$now = date('c');

// 	$halfHourAgo = date("c", strtotime("-30 minutes", strtotime($now)));

    //	$endDate = "2017-05-15";

    //	$startDate = "2017-05-01";

    $endDate = date("Y-m-d", time());


    $startDate = date("Y-m-d", strtotime("-1 day", time()));

    // 'modified_since' => $halfHourAgo,



//////////////////////////////////////////////////////////////////////

    do {

        // Get Timesheets
        $timesheets = $tsheets->get(DreamFactory\Services\Tsheets\ObjectType::Timesheets, array('start_date' => $startDate,'end_date' => $endDate,'page' => $page,'supplemental_data' => 'yes'));

        $more = $timesheets['more'];

        $results = $timesheets['results']['timesheets'];

        $jobcodes = $timesheets['supplemental_data']['jobcodes'];

        $users = $timesheets['supplemental_data']['users'];


        foreach($results as $tkey => $timesheet){

// 					dd($timesheet);

// 						if($timesheet['customfields']['27449']){

// 							$jobName = $timesheet['customfields']['27449'];

// 						}

            if($timesheet['start']) {

                if($timesheet['start']) {

                    $start = podioizeISODate($timesheet['start']); } else {  }

                if($timesheet['end']) {

                    $end = podioizeISODate($timesheet['end']); } else {  }

                if($timesheet['duration']) {

                    $duration = $timesheet['duration']; } else {  }

                if($timesheet['user_id']) {


                    $employeeItemID = findEmployee($timesheet['user_id'], $allEmployees);

                    appAuth($weeklyTimeCyclesAppID);

                    $existingWeeklyTimeCycleFilter = PodioItem::filter_by_view($weeklyTimeCyclesAppID, $weeklyTimeSheet_currentCyclesViewID,['limit'=>500]);

                    foreach($existingWeeklyTimeCycleFilter as $cycleItem){

                        $cycleItemID = $cycleItem->item_id;

                        $empItemValue = $cycleItem->fields['employee']->values[0]->item_id;

                        if($empItemValue == $employeeItemID){

                            $matchCycleID = $cycleItemID;

                        }

                    }

                } else {  }

                if($timesheet['jobcode_id']) {

                    $ntpItemID = getNTPbyJobCode($timesheet['jobcode_id'], $jobcodes[$timesheet['jobcode_id']]['name']);

                    appAuth($projectRollupAppID);

                    $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                    foreach($rollupFilter as $rollupItem){

                        $rollupItemID = $rollupItem->item_id;

                    }


                } else {  }

                if($timesheet['user_id']) {

                    $geoLatLngIN = getGeolocationLatLng($timesheet['start'], $timesheet['user_id']); } else {   }
                // $geoLatLngIN = "";

                if($timesheet['user_id']) {

                    $geoLatLngOUT = getGeolocationLatLng($timesheet['end'], $timesheet['user_id']); } else {  }
                // $geoLatLngOUT = "";

//                if($geoLatLngIN) {
//
//                    $addressIN = googleMapsReverseGeocode($geoLatLngIN); } else { $addressIN = "None"; }
//
//                if($geoLatLngOUT) {
//
//                    $addressOUT = googleMapsReverseGeocode($geoLatLngOUT); } else { $addressOUT = "None"; }

                if($timesheet['customfields']['27449']) {

                    if($timesheet['customfields']['27449'] == "Travel Time" || $timesheet['customfields']['27449'] == "Admin Time" || $timesheet['customfields']['27449'] == "Work Time On-Site" || $timesheet['customfields']['27449'] == "Lunch Break"){

                        $serviceType = $timesheet['customfields']['27449'];

                    }

                } else { $serviceType = "Undefined"; }


                $newTimesheetFields = ['fields' => [
                    'employee' => [$employeeItemID],
                    'tsheet-id'=>(string)$timesheet['id'],
                    'service-type-2'=>(string)$serviceType,
                    // 'ntp' => [$ntpItemID],
                    'rollup-dashboard' => [$rollupItemID],
                    'weeklytimecycle' => [$matchCycleID],
                    'start' => [
                        'start' => $start
                    ],
                    'end' => [
                        'start' => $end
                    ],
                    'total-duration' => $duration,
                ]];


                if($ntpItemID){

                    $newTimesheetFields['fields']['ntp'] = [$ntpItemID];

                } else {

                    if($jobcodes[$timesheet['jobcode_id']['name']]){
                        $newTimesheetFields['fields']['old-job-name-for-finances'] = (string)$jobcodes[$timesheet['jobcode_id']]['name'];
                    }
                };

                if($geoLatLngIN){
                    $newTimesheetFields['fields']['geolocation-address']['lat'] = $geoLatLngIN['lat'];
                    $newTimesheetFields['fields']['latpunchin'] = (string)$geoLatLngIN['lat'];
                    $newTimesheetFields['fields']['geolocation-address']['lng'] = $geoLatLngIN['lng'];
                    $newTimesheetFields['fields']['longpunchin'] = (string)$geoLatLngIN['lng'];
                }

                if($geoLatLngOUT){
                    $newTimesheetFields['fields']['geolocation-address-punch-out']['lat'] = $geoLatLngOUT['lat'];
                    $newTimesheetFields['fields']['latpunchout'] = (string)$geoLatLngOUT['lat'];
                    $newTimesheetFields['fields']['geolocation-address-punch-out']['lng'] = $geoLatLngOUT['lng'];
                    $newTimesheetFields['fields']['longpunchout'] = (string)$geoLatLngOUT['lng'];
                }


                if($timesheet['notes']) {

                    $notes = $timesheet['notes'];

                    $newTimesheetFields['fields']['tsheet-note'] = $notes;

                } else {  };


                appAuth($tsheetsAppID);

                $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);


                if(count($tsheetExistsFilter) <= 0 ){

                    $newItem = PodioItem::create($tsheetsAppID, $newTimesheetFields);

                } else {

                    $updateItem = PodioItem::update($tsheetExistsFilter[0]->item_id, $newTimesheetFields);

                }

            }
            else{

                if($timesheet['duration']) {

                    $duration = $timesheet['duration']; } else {  }

                if($timesheet['user_id']) {

                    $employeeItemID = findEmployee($timesheet['user_id'], $allEmployees);

                    $employeeItemID = findEmployee($timesheet['user_id'], $allEmployees);

                    appAuth($weeklyTimeCyclesAppID);

                    $existingWeeklyTimeCycleFilter = PodioItem::filter_by_view($weeklyTimeCyclesAppID, $weeklyTimeSheet_currentCyclesViewID,['limit'=>500]);

                    foreach($existingWeeklyTimeCycleFilter as $cycleItem){

                        $cycleItemID = $cycleItem->item_id;

                        $empItemValue = $cycleItem->fields['employee']->values[0]->item_id;

                        if($empItemValue == $employeeItemID){

                            $matchCycleID = $cycleItemID;

                        }

                    }

                } else {  $employeeItemID = array(); };

                if($timesheet['jobcode_id']) {

                    $ntpItemID = getNTPbyJobCode($timesheet['jobcode_id'], $jobcodes[$timesheet['jobcode_id']]['name']);

                    appAuth($projectRollupAppID);

                    $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                    foreach($rollupFilter as $rollupItem){

                        $rollupItemID = $rollupItem->item_id;

                    }


                } else { $ntpItemID = array(); }

                if($timesheet['notes']) {

                    $notes = $timesheet['notes']; } else {  };

                $newTimesheetFields = ['fields' => [
                    'employee' => [$employeeItemID],
                    'tsheet-id'=>(string)$timesheet['id'],
                    // 'ntp' => [$ntpItemID],
                    'rollup-dashboard' => [$rollupItemID],
                    'weeklytimecycle' => [$matchCycleID],
                    'total-duration' => $duration,
                ]];

                if($notes){
                    $newTimesheetFields['fields']['tsheet-note'] = $notes;
                }

                if($ntpItemID){

                    $newTimesheetFields['fields']['ntp'] = [$ntpItemID];

                } else {

                    if($jobcodes[$timesheet['jobcode_id']['name']]){
                        $newTimesheetFields['fields']['old-job-name-for-finances'] = (string)$jobcodes[$timesheet['jobcode_id']];
                    }

                };



                appAuth($tsheetsAppID);

                $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);

                if(count($tsheetExistsFilter) <= 0 ){

                    $newItem = PodioItem::create($tsheetsAppID, $newTimesheetFields);

                } else {

                    $updateItem = PodioItem::update($tsheetExistsFilter[0]->item_id, $newTimesheetFields);

                }


            }


        }


        $page++;

        sleep(10);

    }while($more);


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