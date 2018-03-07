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

// api/v2/JoshTEST?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

    ///PODIO ID VARIABLES
    $employeesAppID = 17977954;
    $tsheetsAppID = 18293481;


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

//        dd($date->format("Y-m-d H:i:s"));

        return $date->format("Y-m-d H:i:s");

    }

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


        $ntpFilter = PodioItem::filter($NTPAppID, ['filters'=>['tsheet-id'=>(string)$jobcodeID]]);

        $ntpItemID = $ntpFilter[0]->item_id;

        if(!$ntpItemID){

            $ntpFilter = PodioItem::filter($NTPAppID, ['filters'=>['quickbooks-name'=>(string)$jobcodeName]]);

            $ntpItemID = $ntpFilter[0]->item_id;

            PodioItem::update($ntpItemID, ['fields'=>['tsheet-id'=>(string)$jobcodeID]]);

        }

        return $ntpItemID;
    }

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


    $now = date('c');

    $halfHourAgo = date("c", strtotime("-30 minutes", strtotime($now)));


//////////////////////////////////////////////////////////////////////

    do {
        // Get Timesheets
        $timesheets = $tsheets->get(DreamFactory\Services\Tsheets\ObjectType::Timesheets, array('modified_since' => $halfHourAgo,'page' => $page));


        $more = $timesheets['more'];

        $results = $timesheets['results']['timesheets'];

        $jobcodes = $timesheets['supplemental_data']['jobcodes'];

        $users = $timesheets['supplemental_data']['users'];


        foreach($results as $tkey => $timesheet){



            if($timesheet['start']) {

                if($timesheet['start']) {

                    $start = podioizeISODate($timesheet['start']); } else {  }

                if($timesheet['end']) {

                    $end = podioizeISODate($timesheet['end']); } else {  }

                if($timesheet['duration']) {

                    $duration = $timesheet['duration']; } else {  }

                if($timesheet['user_id']) {

                    $employeeItemID = findEmployee($timesheet['user_id'], $allEmployees);

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

                    $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                    foreach($rollupFilter as $rollupItem){

                        $rollupItemID = $rollupItem->item_id;

                    }


                } else {  }

                if($timesheet['user_id']) {

                    $geoLatLngIN = getGeolocationLatLng($timesheet['start'], $timesheet['user_id']); } else {   }


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

                    if($timesheet['customfields']['27449'] == "Travel Time" || $timesheet['customfields']['27449'] == "Admin Time" || $timesheet['customfields']['27449'] == "Work Time On-Site"){

                        $serviceType = $timesheet['customfields']['27449'];

                    }

                } else { $serviceType = "Undefined"; }


                $newTimesheetFields = ['fields' => [
                    'employee' => [$employeeItemID],
                    'tsheet-id'=>(string)$timesheet['id'],
                    'service-type-2'=>(string)$serviceType,
                    'ntp' => [$ntpItemID],
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

                // $notes = "";

                //  print_r($newTimesheetFields);exit;

                $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);

                //	print_r("HEY".$tsheetExistsFilter[0]->item_id);exit;

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

                    $existingWeeklyTimeCycleFilter = PodioItem::filter_by_view($weeklyTimeCyclesAppID, $weeklyTimeSheet_currentCyclesViewID,['limit'=>500]);

                    foreach($existingWeeklyTimeCycleFilter as $cycleItem){

                        $cycleItemID = $cycle->item_id;

                        $empItemValue = $newEmailItem->fields['employee']->values[0]->item_id;

                        if($empItemValue == $employeeItemID){

                            $matchCycleID = $cycleItemID;

                        }

                    }

                } else {  $employeeItemID = array(); };

                if($timesheet['jobcode_id']) {

                    $ntpItemID = getNTPbyJobCode($timesheet['jobcode_id'], $jobcodes[$timesheet['jobcode_id']]['name']);

                    $rollupFilter = PodioItem::filter($projectRollupAppID, ['filters' => ['ntp' => [(int)$ntpItemID]]]);

                    foreach($rollupFilter as $rollupItem){

                        $rollupItemID = $rollupItem->item_id;

                    }


                } else { $ntpItemID = array(); }

                if($timesheet['notes']) {

                    $notes = $timesheet['notes']; } else {  };
                // $notes = "";

                $newTimesheetFields = ['fields' => [
                    'employee' => [$employeeItemID],
                    'tsheet-id'=>(string)$timesheet['id'],
                    'ntp' => [$ntpItemID],
                    'rollup-dashboard' => [$rollupItemID],
                    'weeklytimecycle' => [$matchCycleID],
                    'total-duration' => $duration,
                ]];

                if($notes){
                    $newTimesheetFields['fields']['tsheet-note'] = $notes;
                }


                $tsheetExistsFilter = PodioItem::filter($tsheetsAppID, ['filters'=>['tsheet-id'=>(string)$timesheet['id']]]);

                if(count($tsheetExistsFilter) <= 0 ){

                    $newItem = PodioItem::create($tsheetsAppID, $newTimesheetFields);

                } else {

                    $updateItem = PodioItem::update($tsheetExistsFilter[0]->item_id, $newTimesheetFields);

                }


            }


        }


        $page++;

        sleep(1);

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
