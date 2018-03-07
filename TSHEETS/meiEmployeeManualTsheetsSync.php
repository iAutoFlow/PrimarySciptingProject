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

// api/v2/meiEmployeeManualTsheetSync?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634@item_id=579334701&admin=true

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


    function podioizeISODate($ISODate){

        $date = DateTime::createFromFormat(DateTime::ISO8601, $ISODate);

        $date->setTimeZone("America/Denver");

//        dd($date->format("Y-m-d H:i:s"));

        return $date->format("Y-m-d H:i:s");

    }

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
        }

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
    $bigAssArray = [];

    $access_token = "S.2__0ecf451ff55442eaa2443a64c84ccb537af6bd1b";
    $tsheets = new DreamFactory\Services\TSheets\TSheetsRestClient(1, $access_token);



    appAuth($employeesAppID);

    $item = PodioItem::get($item_id);

    $triggerValue = $item->fields['tsheets-manual-sync']->values[0]['text'];

    if($triggerValue == "Run" || ($requestParams['admin']) == "true"){

        $tryThis = PodioItem::update($item_id, array(
                'fields' => array(
                    'tsheets-manual-sync' => "Working"
                )
            )
        );


        $employeeTsheetsID = $item->fields['tsheets-id']->values;

        $podioStart = $item->fields['run-sync-for-dates']->start;
        $startDate = $podioStart->format("Y-m-d");

        if(!$startDate){
            $tryThis = PodioItem::update($item_id, array(
                    'fields' => array(
                        'tsheets-manual-sync' => "Error"
                    )
                )
            );

            $alert = PodioComment::create('item', $item_id, array(
                "value"=>"Both Start and End dates are required to run this sync."));

        }

        $podioEnd = $item->fields['run-sync-for-dates']->end;
        $endDate = $podioEnd->format("Y-m-d");

        if(!$startDate){

            $tryThis = PodioItem::update($item_id, array(
                    'fields' => array(
                        'tsheets-manual-sync' => "Error"
                    )
                )
            );

            $alert = PodioComment::create('item', $item_id, array(
                "value"=>"Both Start and End dates are required to run this sync."));

        }


        do {
            // Get Timesheets
            $timesheets = $tsheets->get(DreamFactory\Services\Tsheets\ObjectType::Timesheets, array('start_date' => $startDate,'end_date' => $endDate, 'user_ids' => (int)$employeeTsheetsID,'page' => $page, 'supplemental_data' => 'yes')); // ,'page' => $page

            $more = $timesheets['more'];

            $results = $timesheets['results']['timesheets'];

            $jobcodes = $timesheets['supplemental_data']['jobcodes'];

            $users = $timesheets['supplemental_data']['users'];


            foreach($results as $tkey => $timesheet){
//                unset($ntpItemID);
//                unset($start);
//                unset($end);
//                unset($duration);
//                unset($employeeItemID);
//                unset($existingWeeklyTimeCycleFilter);
//                unset($cycleItemID);
//                unset($empItemValue);
//                unset($matchCycleID);
//                unset($rollupFilter);
//                unset($rollupItemID);
//                unset($geoLatLngOUT);
//                unset($serviceType);
//                unset($newTimesheetFields);
//                unset($jobNameThing);
//                unset($notes);
//                unset($tsheetExistsFilter);
//                unset($newItem);
//                unset($updateItem);

                if($timesheet['start']) {

                    if($timesheet['start']) {
                        $start = podioizeISODate($timesheet['start']);
                    }

                    if($timesheet['end']) {
                        $end = podioizeISODate($timesheet['end']);
                    }

                    if($timesheet['duration']) {
                        $duration = $timesheet['duration'];
                    }

                    if($timesheet['user_id']) {

                        $employeeItemID = $item_id;
                        $existingWeeklyTimeCycleFilter = PodioItem::filter_by_view($weeklyTimeCyclesAppID, $weeklyTimeSheet_currentCyclesViewID,['limit'=>500]);

                        foreach($existingWeeklyTimeCycleFilter as $cycleItem){

                            $cycleItemID = $cycleItem->item_id;
                            $empItemValue = $cycleItem->fields['employee']->values[0]->item_id;

                            if($empItemValue == $employeeItemID){
                                $matchCycleID = $cycleItemID;
                            }
                        }

                    }

                    if($timesheet['jobcode_id']) {

                        $ntpItemID = getNTPbyJobCode($timesheet['jobcode_id'], $jobcodes[$timesheet['jobcode_id']]['name']);

                        $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                        foreach($rollupFilter as $rollupItem){
                            $rollupItemID = $rollupItem->item_id;
                        }
                    }

                    if($timesheet['user_id']) {
                        $geoLatLngIN = getGeolocationLatLng($timesheet['start'], $timesheet['user_id']);
                    }

                    if($timesheet['user_id']) {
                        $geoLatLngOUT = getGeolocationLatLng($timesheet['end'], $timesheet['user_id']);
                    }

//                    if($timesheet['customfields']['27449']) {
//
//                        if($timesheet['customfields']['27449'] == "Travel Time" ||
//                            $timesheet['customfields']['27449'] == "Admin Time" ||
//                            $timesheet['customfields']['27449'] == "Work Time On-Site" ||
//                            $timesheet['customfields']['27449'] == "Lunch Break"){
//
//                            $serviceType = $timesheet['customfields']['27449'];
//                        }
//
//                        array_push($bigAssArray,$timesheet['customfields']['27449']);
//                    }

                    $serviceType = null;
                    if($timesheet['customfields']['27449']) {
                        if ($timesheet['customfields']['27449'] == "Admin Time") {
                            $serviceType = 1;
                        }
                        if ($timesheet['customfields']['27449'] == "Travel Time") {
                            $serviceType = 2;
                        }
                        if ($timesheet['customfields']['27449'] == "Work Time On-Site") {
                            $serviceType = 3;
                        }
                        if ($timesheet['customfields']['27449'] == "Lunch Break") {
                            $serviceType = 4;
                        }
                    }
                    if($serviceType == null){$serviceType = 4;}


                    $newTimesheetFields = [
                        'fields' => [
                            'employee' => [$employeeItemID],
                            'tsheet-id'=>(string)$timesheet['id'],
                            'service-type-2'=>(string)$serviceType,
                            // 'ntp' => [$ntpItemID],
                            //'rollup-dashboard' => [$rollupItemID],
                            //'weeklytimecycle' => [$matchCycleID],
                            'start' => [
                                'start' => $start
                            ],
                            'end' => [
                                'start' => $end
                            ],
                            'total-duration' => $duration,
                        ]
                    ];



                    if(is_int($ntpItemID)){
                        $newTimesheetFields['fields']['ntp'] = [(int)$ntpItemID];

                    } else {
                        $jobNameThing = $jobcodes[$timesheet['jobcode_id']['name']];

                        if(!$jobcodes[$timesheet['jobcode_id']]['name']){
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = $jobcodes[$timesheet['jobcode_id']]['name'];

                        } elseif($jobcodes[$timesheet['jobcode_id']]['name'] == "Lunch Break"){
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = "Not Found";

                        } else {
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = $jobcodes[$timesheet['jobcode_id']]['name'];
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
                    }


                    $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);

                    if(count($tsheetExistsFilter) <= 0 ){

                        $newItem = PodioItem::create($tsheetsAppID, $newTimesheetFields);

                    } else {

                        $updateItem = PodioItem::update($tsheetExistsFilter[0]->item_id, $newTimesheetFields);

                    }

                }
                else{

                    if($timesheet['duration']) {

                        $duration = $timesheet['duration']; }

                    if($timesheet['user_id']) {

                        $employeeItemID = $item_id;

                        $existingWeeklyTimeCycleFilter = PodioItem::filter_by_view($weeklyTimeCyclesAppID, $weeklyTimeSheet_currentCyclesViewID,['limit'=>500]);

                        foreach($existingWeeklyTimeCycleFilter as $cycleItem){

                            $cycleItemID = $cycleItem->item_id;

                            $empItemValue = $cycleItem->fields['employee']->values[0]->item_id;

                            if($empItemValue == $employeeItemID){

                                $matchCycleID = $cycleItemID;

                            }

                        }

                    } else {$employeeItemID = array();};

                    if($timesheet['jobcode_id']) {

                        $ntpItemID = getNTPbyJobCode($timesheet['jobcode_id'], $jobcodes[$timesheet['jobcode_id']]['name']);

                        $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                        foreach($rollupFilter as $rollupItem){

                            $rollupItemID = $rollupItem->item_id;

                        }


                    } else { $ntpItemID = array(); }

                    if($timesheet['notes']) {

                        $notes = $timesheet['notes'];}

                    $newTimesheetFields = ['fields' => [
                        'employee' => [$employeeItemID],
                        'tsheet-id'=>(string)$timesheet['id'],
                        // 'ntp' => [$ntpItemID],
                        'rollup-dashboard' => [$rollupItemID],
                        'weeklytimecycle' => [$matchCycleID],
                        'total-duration' => $duration,
                    ]];

// 									$print  = $jobcodes[$timesheet['jobcode_id']]['name'];

// 										$thisNow = PodioComment::create('item', $item_id, array(
//             "value"=>"The value is: $print"));

// 										dd($jobcodes[$timesheet['jobcode_id']]['name']);


                    if($notes){
                        $newTimesheetFields['fields']['tsheet-note'] = $notes;
                    }

                    if($ntpItemID){
                        $newTimesheetFields['fields']['ntp'] = [(int)$ntpItemID];
                    }
                    else{
                        if(!$jobcodes[$timesheet['jobcode_id']['name']]){
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = $jobcodes[$timesheet['jobcode_id']]['name'];
                        } elseif($jobcodes[$timesheet['jobcode_id']]['name']== "Lunch Break"){
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = "Not Found";
                        } else{
                            $newTimesheetFields['fields']['old-job-name-for-finances'] = $jobcodes[$timesheet['jobcode_id']]['name'];
                        }
                    };

                    $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);

                    if(count($tsheetExistsFilter) <= 0 ){
                        $newItem = PodioItem::create($tsheetsAppID, $newTimesheetFields);

                    } else {$updateItem = PodioItem::update($tsheetExistsFilter[0]->item_id, $newTimesheetFields);}

                }
            }


            $page++;

            sleep(20);

        }while($more);


        $tryThis = PodioItem::update($item_id, array(
                'fields' => array(
                    'tsheets-manual-sync' => "Success!"
                )
            )
        );

        $alert = PodioComment::create('item', $item_id, array(
            "value"=>"Sync of Tsheets from $startDate thru $endDate complete! $page pages completed."));


    }

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