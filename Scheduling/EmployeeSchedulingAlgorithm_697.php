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

//Hook Verify and payload
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
    $item_revision_id = (int)$requestParams['item_revision_id'];

    if(!$item_id) {
        $item_id = (int)$payload['item_id'];
        $item_revision_id = (int)$payload['item_revision_id'];
    }

    $itemRevisionInfo = PodioItemRevision::get($item_id, $item_revision_id);

    $revisionByName = $itemRevisionInfo->created_by->name;
    $revisionByUserID = $itemRevisionInfo->created_by->id;

///AUTOMATION START


//VARS SECTION
    $employeeAvailabilityAppID = 17977954;
    $timeSheetAppID = 17977688;
    $projectSchedulingAppID = 17978257;
    $dispatchAppID = 18070091;
    $scheduleLogAppID = 18862726;
    $scheduleLogCurrentViewID = 34144048;
    $timeOffAppID = 18861783;
    $trainingMatrixAppID = 18333976;

    $gMapsKey = "AIzaSyBZ62izWdstoT0u3nDj9REknpXgJaaKV_U";





    function googleMapsDistanceTimeCheck($fromLocation, $toLocation){

        $gMapsKey = "AIzaSyBZ62izWdstoT0u3nDj9REknpXgJaaKV_U";

        $curlGmaps = new \Curl\Curl();

        $queryUrl = "https://maps.googleapis.com/maps/api/distancematrix/json?key=".$gMapsKey;

        $origins = "origins=".urlencode($fromLocation);

        $destinations = "destinations=".urlencode($toLocation);

        $queryUrl.="&".$origins."&".$destinations;

        $response = $curlGmaps->get($queryUrl);

//    dd($response);

        return ($response->rows[0]->elements[0]->duration->value / 60 / 60);
    }

    function googleMapsGeocodeAddress($address){

        $gMapsKey = "AIzaSyBZ62izWdstoT0u3nDj9REknpXgJaaKV_U";

        $curlGmaps = new \Curl\Curl();

        $baseGoogleGeocodeURL = "https://maps.googleapis.com/maps/api/geocode/json?key=".$gMapsKey."&address=";

        $encodedAddress = urlencode($address);

        $finalUrl = $baseGoogleGeocodeURL.$encodedAddress;

        $response = $curlGmaps->get($finalUrl);

        return $response->results->geometry->location;

    }

    function googleMapsDistanceBetween($origLatLng, $destLatLng){



    }

    function getFlights($date, $origin, $destination){

        $query = "cheapest one way flights from $origin to $destination departing $date";
        $api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
        $url = "https://hoist.thatapp.io/api/v2/houndify-python?api_key=$api_key";
        $url .= "&query=".urlencode($query);

        $curl = new \Curl\Curl();
        $curl->get($url);
        $result = json_decode(base64_decode($curl->response));

//echo json_encode($result);

        foreach($result->AllResults as $answer){

            if(property_exists($answer->NativeData, 'FlightProducts')) {

                $firstFlight = $answer->NativeData->FlightProducts[0];

                $flightResult = [];

                $flightResult['date'] = $date;
                $flightResult['saleTotal'] = $firstFlight->TotalPrice->Amount;
                $flightResult['duration'] = $firstFlight->TotalTravelDuration / 60;
                $flightResult['origin'] = $origin;
                $flightResult['destination'] = $destination;
                $flightResult['cabin'] = $firstFlight->FlightSegmentGroups[0]->FlightSegmentDetails[0]->CabinClass;
                $label = explode(",", $firstFlight->Label);

                $flightResult['carrier'] = $label[0];
                $flightResult['expediaLink'] = $firstFlight->DetailsUrl;

            }

        }

        if(count($flightResult) > 0){
            return $flightResult;
        }
        else{
            return "No Flights";
        }

    }


    function createFlights($flightData){

        $MEIFlightsAppID = 17978122;
        $MEIAirportsAppID = 18108172;

        $date = $flightData['date'];
        $price = $flightData['saleTotal'];
        $duration = $flightData['duration'];
        $origin = $flightData['origin'];
        $destination = $flightData['destination'];
        $cabin = $flightData['cabin'];
        $carrier = $flightData['carrier'];
        $link = $flightData['expediaLink'];

        $carrierArray = [
            'Alaska Airlines',
            'Allegiant Air',
            'American Airlines',
            'Delta Air Lines',
            'Frontier Airlines',
            'Hawaiian Airlines',
            'JetBlue',
            'Southwest Airlines',
            'Spirit Airlines',
            'Sun Country',
            'United Airlines',
            'Virgin America'
        ];

        if(!in_array($carrier, $carrierArray)){
            $flightItem = "No Flights";
            return array('flight' => $flightData, 'podioItem' => $flightItem);
        }

        $flightItemCreateArray = array('fields' => array());

        if($date){
            $flightItemCreateArray['fields']['flight-date']['start'] = $date." 00:00:00";
        }

        if($price) {
            $flightItemCreateArray['fields']['price'] = (string)$price;
        };

        if($duration) {
            $flightItemCreateArray['fields']['duration-min'] = (string)$duration;
        };

        if($origin) {
            $originAirport = PodioItem::filter($MEIAirportsAppID, ['filters'=>['iatacode'=>$origin]]);

            $flightItemCreateArray['fields']['origin'] = [$originAirport[0]->item_id];
        };

        if($destination) {
            $destAirport = PodioItem::filter($MEIAirportsAppID, ['filters'=>['iatacode'=>$destination]]);

            $flightItemCreateArray['fields']['destination'] = [$destAirport[0]->item_id];
        };

        if($cabin) {
            $flightItemCreateArray['fields']['cabin'] = $cabin;
        };

        if($carrier) {
            $flightItemCreateArray['fields']['airline-2'] = $carrier;
        };

        if($link){
            $flightItemCreateArray['fields']['expedia-link-2'] = $link;
        }

        if($price) {
            $flightItem = PodioItem::create($MEIFlightsAppID, $flightItemCreateArray);
        }
        else{
            $flightItem = "No Flights";
        }


        return array('flight' => $flightData, 'podioItem' => $flightItem);

    }

    $item = PodioItem::get($item_id);

    $scheduleTrigger = $item->fields['schedule']->values[0]['text'];

    if($requestParams['admin'] == "true"){
        $scheduleTrigger = "Schedule Now";
    }
    if($requestParams['admin'] == "true2"){
        $scheduleTrigger = "Manually Schedule";
    }

    if($scheduleTrigger !== "Schedule Now" && $scheduleTrigger !== "Manually Schedule") {
        exit;
    }

    PodioComment::create('item', $item_id, ['value'=>"Scheduling Started, Finding Available Employees"]);

    $jobType = $item->fields['job-type']->values[0]['text'];

    if(!$jobType){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Job Type is missing on this NTP, please set a Job Type in order to schedule it."]);
        throw new Exception("No Job Type on NTP");
    }

    $startDate = $item->fields['start-date']->start;

    if(!$startDate){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Start Date is missing on this NTP, please set a Start Date in order to schedule it."]);
        throw new Exception("No Start Date on NTP");
    }

    $startDateFormatted = $startDate->format('Y-m-d H:i:s');

    $completionDate = $item->fields['end-date']->start;

    if(!$completionDate){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The End Date is missing on this NTP, please make sure the End Date is updated in order to schedule it. You can do this by updating the 'Custom Cycle Days' field.(If you don't want to customize the Cycle Days, update it to match the System Type Cycle Days, or update it to anything and then clear the field.)"]);
        throw new Exception("No End Date on NTP");
    }

    if($startDate > $completionDate){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The End Date is before the Start Date on this NTP, please make sure the End Date comes after the Start Date in order to schedule it. You can update the End Date automatically by updating the 'Custom Cycle Days' field."]);
        throw new Exception("End Date before Start Date NTP");
    }

    $completionDateFormatted = $completionDate->format('Y-m-d H:i:s');

    $customerItemID = $item->fields['customer']->values[0]->item_id;

    if(!$customerItemID){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Customer is missing on this NTP, please set a Customer in order to schedule it."]);
        throw new Exception("No Customer on NTP");
    }

    $productItemID = $item->fields['product']->values[0]->item_id;

    if(!$productItemID){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Product is missing on this NTP, please set a Product in order to schedule it."]);
        throw new Exception("No Product on NTP");
    }

    $ntpFieldEngineer = $item->fields['field-engineer']->values[0]->item_id;

    if(!$ntpFieldEngineer){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The NTP is missing a Field Engineer, please set a Field Engineer in order to schedule it."]);
        throw new Exception("No Field Engineer on NTP");
    }

    $ntpInstallManager = $item->fields['install-manager']->values[0]->item_id;

    $regionItemID = $item->fields['region']->values[0]->item_id;

    if(!$regionItemID){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The NTP is missing a Region, please set a Region in order to schedule it."]);
        throw new Exception("No Region on NTP");
    }

    $gon = $item->fields['gon']->values;

    if(!$gon){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The NTP is missing a GON, please add a GON in order to schedule it."]);
        throw new Exception("No Region on NTP");
    }

    $customerItem = PodioItem::get($customerItemID);

    $location = $customerItem->fields['address']->values['value'];

    $closestAirportItemID = $customerItem->fields['closest-airport']->values[0]->item_id;

    $projectManagerItemID = $customerItem->fields['field-engineer']->values[0]->item_id;

    if(!$projectManagerItemID){
        PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Customer is missing a Project Manager. This is required for Scheduling/Dispatching. Please update the customer item: [$customerItem->title]($customerItem->link)"]);
        throw new Exception("No Project Manager on Customer");
    }

    $NTP_References = PodioItem::get_references($item_id);

    foreach($NTP_References as $reference){
        if($reference['app']['app_id'] == $projectSchedulingAppID){
            $existingPSItemID = $reference['items'][0]['item_id'];
        }
        if($reference['app']['app_id'] == $dispatchAppID){
            $existingDispatchItemID = $reference['items'][0]['item_id'];
        }
    }

    if($existingPSItemID) {
        PodioItem::delete($existingPSItemID);
    }
    if($existingDispatchItemID) {
        PodioItem::delete($existingDispatchItemID);
    }



    if($scheduleTrigger == "Schedule Now") {

        PodioItem::update($item_id, ['fields'=>['schedule'=>"In Progress"]]);

        if($closestAirportItemID) {

            $customerAirport = true;

            $closestAirportItem = PodioItem::get($closestAirportItemID);

            $closestAirportCode = $closestAirportItem->fields['iatacode']->values;

            $closestAirportAddress = $closestAirportItem->fields['address']->values['value'];

            $airportToDestination = googleMapsDistanceTimeCheck($closestAirportAddress, $location);

        }else{
            $customerAirport = false;
        }

        $productItem = PodioItem::get($productItemID);

        $trainingType = $productItem->fields['training-type']->values[0]['text'];

        if(!$trainingType && $jobType !== "De-Install"){
            PodioComment::create('item', $item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) The Product for this NTP does not have a Training Type set. Please set one and then try Scheduling again."]);
            throw new Exception("No Training Type on Product");
        }

        if($jobType !== "De-Install") {
            $trainingTypeExID = str_replace(" - ", "-", strtolower($trainingType));
            $trainingTypeExID = str_replace(" ", "-", $trainingTypeExID);
            $trainingTypeExID = str_replace(",", "", $trainingTypeExID);
            $trainingTypeExID = str_replace("/", "", $trainingTypeExID);
        }


        $leadAvailableEmployees = [];

        $assistAvailableEmployees = [];

        $currentScheduleLogs = PodioItem::filter_by_view($scheduleLogAppID, $scheduleLogCurrentViewID, ['limit'=>500]);

        $filterEmployeesAvailable = PodioItem::filter($employeeAvailabilityAppID, array(
            'filters' => array(
                'status' => [1,2],
                'in-compliance' => 1,
            ),
            'limit'=>500
        ));

        foreach($filterEmployeesAvailable as $employeeAvailable){
            if(array_key_exists('project-status',$employeeAvailable->fields)){
                $employeeAvailableProjectStatus = $employeeAvailable->fields['project-status']->values[0]['text'];
                if($employeeAvailableProjectStatus == 'Unavailable'){continue;}
            }

            $percent = "";
            $employeeDivision = $employeeAvailable->fields['vision']->values[0]->title;
            $trainingMatrixItemID = "";
            $percent = "";

            $relatedItems = PodioItem::get_references($employeeAvailable->item_id);
            foreach($relatedItems as $relatedItem){
                if($relatedItem['app']['app_id'] == $trainingMatrixAppID){
                    $trainingMatrixItemID = $relatedItem['items'][0]['item_id'];
                    break;
                }

            }

            if(!$trainingMatrixItemID){
                PodioComment::create('item', $employeeAvailable->item_id, ['value'=>"@[$revisionByName](user:$revisionByUserID) This Employee is set up to be scheduled and is potentially available, but it missing a Training Matrix Item. Add a Training Matrix item if you want them to be scheduled, if not you can mark them as not in compliance."]);
                continue;
            }
            $trainingMatrixItem = "";
            $trainingMatrixItem = PodioItem::get($trainingMatrixItemID);

            if($jobType == "De-Install"){
                $percent = $trainingMatrixItem->fields['deinstall']->values[0]['text'];
            }
            else {
                $percent = $trainingMatrixItem->fields[$trainingTypeExID]->values[0]['text'];
            }

            if($employeeDivision == "GE-DI") {
                $timeOffFilter = PodioItem::filter($timeOffAppID, [
                    'filters'=>[
                        'employee'=>$employeeAvailable->item_id,
                        'date'=>[
                            'from'=>$startDate->format('Y-m-d H:i:s'),
                            'to'=>$completionDate->format('Y-m-d H:i:s')
                        ]
                    ]
                ]);

                if(count($timeOffFilter) == 0) {

                    if($percent == "100%") {

                        $leadScheduled = false;
                        foreach($currentScheduleLogs as $log) {

                            if($log->fields['employee']->values[0]->item_id == $employeeAvailable->item_id) {

                                if(($log->fields['scheduled-dates']->start <= $completionDate && $log->fields['scheduled-dates']->end >= $startDate)) {

                                    $leadScheduled = true;
                                    break;

                                }

                            }

                        }

                        if(!$leadScheduled) {
                            $leadAvailableEmployees[] = $employeeAvailable;
                        }

                    }

                    if(($percent == "75%" || $percent == "50%" || $percent == "25%" || $percent == "0%")) {

                        $assistScheduled = false;
                        foreach($currentScheduleLogs as $log) {

                            if($log->fields['employee']->values[0]->item_id == $employeeAvailable->item_id) {

                                if(($log->fields['scheduled-dates']->start <= $completionDate && $log->fields['scheduled-dates']->end >= $startDate)) {

                                    $assistScheduled = true;
                                    break;

                                }

                            }

                        }

                        if(!$assistScheduled) {
                            $assistAvailableEmployees[] = $employeeAvailable;
                        }
                    }
                }

            }

        }

//        $filterLeadScheduled = PodioItem::filter($employeeAvailabilityAppID, array(
//            'filters' => array(
//                'project-status' => [2,3],
//                'in-compliance' => 1
//            ),
//            'limit'=>500
//        ));
//
//        foreach($filterLeadScheduled as $leadScheduled){
//
//            $percent = "";
//
//            $employeeDivision = $leadScheduled->fields['division']->values;
//
//            $trainingMatrixItemID = $leadScheduled->fields['training-matrix-item']->values[0]->item_id;
//
//            $trainingMatrixItem = PodioItem::get($trainingMatrixItemID);
//
//            if($jobType == "De-Install"){
//
//                $percent = $trainingMatrixItem->fields['deinstall']->values[0]['text'];
//
//            }
//            else {
//
//                $percent = $trainingMatrixItem->fields[$trainingTypeExID]->values[0]['text'];
//
//            }
//
//            if(!($leadScheduled->fields['scheduledcurrent-job-dates']->start <= $completionDate && $leadScheduled->fields['scheduledcurrent-job-dates']->end >= $startDate) && $percent == "100%"  && $employeeDivision == "GE-DI") {
//
//                $leadAvailableEmployees[] = $leadScheduled;
//
//            }
//
//        }

//        foreach($filterEmployeesAvailable as $assistAvailable){
//
//            $percent = "";
//
//            $employeeDivision = $assistUnscheduled->fields['division']->values;
//
//            $trainingMatrixItemID = $assistUnscheduled->fields['training-matrix-item']->values[0]->item_id;
//
//            $trainingMatrixItem = PodioItem::get($trainingMatrixItemID);
//
//            if($jobType == "De-Install"){
//
//                $percent = $trainingMatrixItem->fields['deinstall']->values[0]['text'];
//
//            }
//            else {
//
//                $percent = $trainingMatrixItem->fields[$trainingTypeExID]->values[0]['text'];
//
//            }
//
//            if(($percent == "75%" || $percent == "50%" || $percent == "25%" || $percent == "0%") && $employeeDivision == "GE-DI") {
//
//                $assistAvailableEmployees[] = $assistUnscheduled;
//
//            }
//
//        }

//        $filterAssistScheduled = PodioItem::filter($employeeAvailabilityAppID, array(
//            'filters' => array(
//                'project-status' => [2,3],
//                'in-compliance' => 1
//            ),
//            'limit'=>500
//        ));
//
//        foreach($filterAssistScheduled as $assistScheduled){
//
//            $percent = "";
//
//            $employeeDivision = $assistScheduled->fields['division']->values;
//
//            $trainingMatrixItemID = $assistScheduled->fields['training-matrix-item']->values[0]->item_id;
//
//            $trainingMatrixItem = PodioItem::get($trainingMatrixItemID);
//
//            if($jobType == "De-Install"){
//
//                $percent = $trainingMatrixItem->fields['deinstall']->values[0]['text'];
//
//            }
//            else {
//
//                $percent = $trainingMatrixItem->fields[$trainingTypeExID]->values[0]['text'];
//
//            }
//
//            if(!($assistScheduled->fields['scheduledcurrent-job-dates']->start <= $completionDate && $assistScheduled->fields['scheduledcurrent-job-dates']->end >= $startDate) && ($percent == "75%" || $percent == "50%" || $percent == "25%" || $percent == "0%")  && $employeeDivision == "GE-DI") {
//
//                $assistAvailableEmployees[] = $assistScheduled;
//
//            }
//
//        }

        $newLeadsObject = [];

        $timeSheets = PodioItem::filter($timeSheetAppID, array('filters' => array('status' => 1)));

        PodioComment::create('item', $item_id, ['value'=>"Finding best lead options..."]);

        foreach($leadAvailableEmployees as $leadKey => $potentialLead) {

            $alreadyAtCustomer = false;

            if($potentialLead->fields['location']->values['value']) {

                if($potentialLead->fields['location']->values['value'] !== $location) {

                    $newLeadsObject[$leadKey]->timeToLocation = googleMapsDistanceTimeCheck($potentialLead->fields['location']->values['value'], $location);

                }else{
                    $alreadyAtCustomer = true;
                }

                $loopCurrentVal = null;
                foreach($timeSheets as $timeSheet){
                    if($timeSheet->fields['employee']->values[0]->item_id == $potentialLead->item_id){
                        $loopCurrentVal = $timeSheet->fields['total-hours']->values;
                    }
                }

                $newLeadsObject[$leadKey]->currentHours = $loopCurrentVal;

                $employeeItemID = $potentialLead->item_id;

                $employeePayRate = $potentialLead->fields['money']->values['value'];

                if (!$employeePayRate) {
                    $employeePayRate = 20;
                }

                if(($newLeadsObject[$leadKey]->timeToLocation == 0 && $alreadyAtCustomer) || $newLeadsObject[$leadKey]->timeToLocation > 0) {

                    $newLeadsObject[$leadKey]->totalNewHours = $newLeadsObject[$leadKey]->timeToLocation + $newLeadsObject[$leadKey]->currentHours;

                    $newLeadsObject[$leadKey]->totalNewCost = $newLeadsObject[$leadKey]->totalNewHours * $employeePayRate;

                    $newLeadsObject[$leadKey]->podioItem = $potentialLead;

                }

            }

        }

        PodioComment::create('item', $item_id, ['value'=>"Finding best assist options..."]);

        $newAssistObject = [];
        foreach($assistAvailableEmployees as $assistKey => $potentialAssist) {

            $alreadyAtCustomer = false;

            if($potentialAssist->fields['location']->values['value']) {

                if($potentialAssist->fields['location']->values['value'] !== $location) {

                    $newAssistObject[$assistKey]->timeToLocation = googleMapsDistanceTimeCheck($potentialAssist->fields['location']->values['value'], $location);

                }else{
                    $newAssistObject[$assistKey]->timeToLocation = 0;

                    $alreadyAtCustomer = true;
                }

                $loopCurrentVal = null;
                foreach($timeSheets as $timeSheet){
                    if($timeSheet->fields['employee']->values[0]->item_id == $potentialAssist->item_id){
                        $loopCurrentVal = $timeSheet->fields['total-hours']->values;
                    }
                }

                $newAssistObject[$assistKey]->currentHours = $loopCurrentVal;

                $employeeItemID = $potentialAssist->item_id;

                $employeePayRate = $potentialAssist->fields['money']->values['value'];

                if (!$employeePayRate) {
                    $employeePayRate = 20;
                }

                if(($newAssistObject[$assistKey]->timeToLocation == 0 && $alreadyAtCustomer) || $newAssistObject[$assistKey]->timeToLocation > 0) {

                    $newAssistObject[$assistKey]->totalNewHours = $newAssistObject[$assistKey]->timeToLocation + $newAssistObject[$assistKey]->currentHours;

                    $newAssistObject[$assistKey]->totalNewCost = $newAssistObject[$assistKey]->totalNewHours * $employeePayRate;

                    $newAssistObject[$assistKey]->podioItem = $potentialAssist;

                }

            }

        }

        $newLeadsObject = array_values($newLeadsObject);
        $newAssistObject = array_values($newAssistObject);


        usort($newLeadsObject, function ($item1, $item2) {
            return $item1->totalNewCost <=> $item2->totalNewCost;
        });

        usort($newAssistObject, function ($item1, $item2) {
            return $item1->totalNewCost <=> $item2->totalNewCost;
        });



        $potentialLeads = [];
        $potentialAssists = [];


        $leadFlightFlag = false;
        for($l = 0; $l < 4; $l++) {
            $potentialLeads[] = $newLeadsObject[$l];
            if($newLeadsObject[$l]->timeToLocation > 5) {
//                $leadFlightFlag = true;
            } else {
                unset($newLeadsObject[$l]);
            }
        }


        $newLeadsObject = array_values($newLeadsObject);

        $assistFlightFlag = false;
        for($a = 0; $a < 4; $a++) {
            $potentialAssists[] = $newAssistObject[$a];
            if($newAssistObject[$a]->timeToLocation > 5) {
//                $assistFlightFlag = true;
            } else {
                unset($newAssistObject[$a]);
            }
        }

        $newAssistObject = array_values($newAssistObject);


//        if($leadFlightFlag && $customerAirport) {
//
//            PodioComment::create('item', $item_id, ['value'=>"Searching for flights for leads..."]);
//
////    $closestAirportLatLng = googleMapsGeocodeAddress($closestAirportAddress);
//
//            $airportDistanceList = [];
//
//            foreach($newLeadsObject as $leadKey2 => $potentialLead) {
//
//                $currentLocation = $potentialLead->podioItem->fields['current-location-calculation']->values;
//
//                $homeLocation = $potentialLead->podioItem->fields['home-location']->values;
//
//                if($currentLocation == $homeLocation) {
//
//                    $employeeItemID = $potentialLead->podioItem->fields['employee']->values[0]->item_id;
//
//                    $employeeItem = PodioItem::get($employeeItemID);
//
//                    $closestAirports = $employeeItem->fields['closest-airport']->values;
//
//                    $airportDistances = $employeeItem->fields['airport-distances-csv']->values;
//
//                    $priceCheck = [];
//
//                    foreach($closestAirports as $airportKey => $airport) {
//
//                        $airportItem = PodioItem::get($airport->item_id);
//
//                        $getFlight = getFlights($startDate->format('Y-m-d'), $airportItem->fields['iatacode']->values, $closestAirportCode);
//
//                        if($getFlight !== "No Flights") {
////                            foreach($getFlight->flights as $key => $flightThing) {
//
//                            $priceCheck[$airportKey] = array('price' => (int)$getFlight['saleTotal'], 'flight' => $getFlight, 'airport' => $airportItem);
//
////                            }
//                        }
//
//                    }
//
//                    usort($priceCheck, function ($item1, $item2) {
//                        return $item1->price <=> $item2->price;
//                    });
//
//
//                    if($priceCheck[0]['flight']) {
//                        $closestEmployeeAirport = $priceCheck[0]['airport'];
//
//                        $potentialLead->flightCheckFlight = $priceCheck[0]['flight'];
//                    } else {
//                        $closestEmployeeAirport = "No Flights";
//
//                        $potentialLead->flightCheckFlight = "No Flights";
//                    }
//
//
//
//                } else {
//
//                    $currentJobCustomerItemID = $potentialLead->podioItem->fields['current-job-customer']->values[0]->item_id;
//
//                    $currentJobCustomerItem = PodioItem::get($currentJobCustomerItemID);
//
//                    $currentCustomerAirportItemID = $currentJobCustomerItem->fields['closest-airport']->values[0]->item_id;
//
//                    $closestEmployeeAirport = PodioItem::get($currentCustomerAirportItemID);
//
//                }
//
//                $potentialLead->closestCurrentAirport = $closestEmployeeAirport;
//
//                if($closestEmployeeAirport !== "No Flights") {
//                    $potentialLead->destAirportDistance = googleMapsDistanceTimeCheck($potentialLead->closestCurrentAirport->fields['address']->values['value'], $closestAirportAddress);
//                } else {
//                    $potentialLead->destAirportDistance = 999999999;
//                }
//
//            }
//
//
//            usort($newLeadsObject, function ($item1, $item2) {
//                return $item1->destAirportDistance <=> $item2->destAirportDistance;
//            });
//
//            $checkFlightsArray = [];
//            for($l = 0; $l < 4; $l++) {
//                if($newLeadsObject[$l] && $newLeadsObject[$l]->destAirportDistance !== 999999999) {
//                    $checkFlightsArray[] = $newLeadsObject[$l];
//                }
//            }
//
//
//            $employeeTotalCosts = [];
//
//            foreach($checkFlightsArray as $employeeKey => $employee) {
//
//                if($employee->flightCheckFlight && $employee->flightCheckFlight !== "No Flights") {
//
//                    $flightInfo = $employee->flightCheckFlight;
//
//                } else {
//
////                    $flights = getFlights($startDate->format('Y-m-d'), $employee->closestCurrentAirport->fields['iatacode']->values, $closestAirportItem->fields['iatacode']->values);
////
////                    usort($flights, function ($item1, $item2) {
////                        return $item1->saleTotal <=> $item2->saleTotal;
////                    });
//
//                    $flightInfo = getFlights($startDate->format('Y-m-d'), $employee->closestCurrentAirport->fields['iatacode']->values, $closestAirportItem->fields['iatacode']->values);
//
//                }
//
//                if($flightInfo !== "No Flights") {
//
//                    $flightInfo2 = createFlights($flightInfo);
//
//                    $flightTime = $flightInfo2['flight']['duration'] / 60;
//
//                    $flightPrice = $flightInfo2['flight']['saleTotal'];
//
//                    if($flightInfo2['podioItem'] != "No Flights") {
//                        $flightItemID = $flightInfo2['podioItem']->item_id;
//                    }
//
//                    if($flightPrice < 1 || $flightPrice == null){
//                        $flightTime = 999999999;
//                        $flightPrice = 999999999;
//                    }
//
//
//                }
//                else {
//                    $flightTime = 999999999;
//                    $flightPrice = 999999999;
//                }
//
//
//                $employeeItemID = $employee->podioItem->fields['employee']->values[0]->item_id;
//
//                $employeeItem = PodioItem::get($employeeItemID);
//
//                $employeePayRate = $employeeItem->fields['money']->values['value'];
//
//                if(!$employeePayRate) {
//                    $employeePayRate = 20;
//                }
//
//                $totalCost = (($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) * $employeePayRate) + (((($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) > 40 ? ($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) : 40) - 40) * ($employeePayRate / 2)) + $flightPrice;
//
//                $travelHours = $employee->destAirportDistance + $airportToDestination + $flightTime;
//
//                if($employee->currentHours + $travelHours > 40) {
//                    $overtimeHours = $employee->currentHours + $travelHours - 40;
//                    $normalHours = $travelHours - $overtimeHours;
//
//                    $travelCost = (($normalHours) * $employeePayRate) + (($overtimeHours) * ($employeePayRate * 1.5)) + $flightPrice;
//                } else {
//                    $travelCost = (($employee->destAirportDistance + $airportToDestination + $flightTime) * $employeePayRate) + (((($employee->destAirportDistance + $airportToDestination + $flightTime) > 40 ? ($employee->destAirportDistance + $airportToDestination + $flightTime) : 40) - 40) * ($employeePayRate / 2)) + $flightPrice;
//                }
//
//                if($flightPrice !== 999999999) {
//
//                    $employeeTotalCosts[$employeeKey] = array('employeeObj' => $employee, 'totalCost' => $totalCost, 'travelCost' => $travelCost, 'type' => "Flight", 'flightCost' => $flightPrice, 'flightTime' => $flightTime);
//
//                }
//
//                if($flightItemID){
//                    $employeeTotalCosts[$employeeKey]['flightItem'] = $flightItemID;
//                }
//
//            }
//
//
//            foreach($potentialLeads as $employee) {
//
//                $employeeItemID = $employee->podioItem->item_id;
//
//                if($employeeItemID) {
//
//                    $employeeItem = PodioItem::get($employeeItemID);
//
//                    $employeePayRate = $employeeItem->fields['money']->values['value'];
//
//                    if(!$employeePayRate) {
//                        $employeePayRate = 20;
//                    }
//
//
//                    $totalCost = (($employee->totalNewHours) * $employeePayRate) + ((($employee->totalNewHours > 40 ? $employee->totalNewHours : 40) - 40) * ($employeePayRate / 2));
//
//                    if($employee->totalNewHours > 40) {
//                        $overtimeHours = $employee->totalNewHours - 40;
//                        $normalHours = $employee->timeToLocation - $overtimeHours;
//
//                        $travelCost = (($normalHours) * $employeePayRate) + (($overtimeHours) * ($employeePayRate * 1.5));
//                    } else {
//                        $travelCost = (($employee->timeToLocation) * $employeePayRate) + ((($employee->timeToLocation > 40 ? $employee->timeToLocation : 40) - 40) * ($employeePayRate / 2));
//                    }
//
//                    $employeeTotalCosts[] = array('employeeObj' => $employee, 'totalCost' => $totalCost, 'travelCost' => $travelCost, 'type' => "Driving");
//
//                }
//
//            }
//
//            usort($employeeTotalCosts, function ($item1, $item2) {
//                return $item1['totalCost'] <=> $item2['totalCost'];
//            });
//
//            foreach($employeeTotalCosts as $empCostKey => $employeeTotalCost){
//                if($employeeTotalCost['totalCost'] == 0 && !$employeeTotalCost['employeeObj']->podioItem->item_id){
//                    unset($employeeTotalCosts[$empCostKey]);
//                }
//            }
//
//            $employeeTotalCosts = array_values($employeeTotalCosts);
//
//
//            $finalLeads = [];
//
//            for($l = 0; $l < 4; $l++) {
//                $finalLeads[] = $employeeTotalCosts[$l];
//            }
//
//
//        } else {
        $finalLeads = [];

        for($l = 0; $l < 4; $l++) {
            $finalLeads[$l]['employeeObj'] = $potentialLeads[$l];
            $finalLeads[$l]['travelCost'] = $potentialLeads[$l]->totalNewCost;
        }

//        }


//        if($assistFlightFlag && $customerAirport) {
//
//            PodioComment::create('item', $item_id, ['value'=>"Searching for flights for assists..."]);
//
////    $closestAirportLatLng = googleMapsGeocodeAddress($closestAirportAddress);
//
//            $airportDistanceList = [];
//
//            foreach($newAssistObject as $assistKey2 => $potentialAssist) {
//
//                $currentLocation = $potentialAssist->podioItem->fields['current-location-calculation']->values;
//
//                $homeLocation = $potentialAssist->podioItem->fields['home-location']->values;
//
//                if($currentLocation == $homeLocation) {
//
//                    $employeeItemID = $potentialAssist->podioItem->fields['employee']->values[0]->item_id;
//
//                    $employeeItem = PodioItem::get($employeeItemID);
//
//                    $closestAirports = $employeeItem->fields['closest-airport']->values;
//
//                    $airportDistances = $employeeItem->fields['airport-distances-csv']->values;
//
//                    $priceCheck = [];
//                    foreach($closestAirports as $airportKey => $airport) {
//
//                        $airportItem = PodioItem::get($airport->item_id);
//
//                        $getFlight = getFlights($startDate->format('Y-m-d'), $airportItem->fields['iatacode']->values, $closestAirportCode);
//
//                        if($getFlight !== "No Flights") {
////                            foreach($getFlight->flights as $key => $flightThing) {
//
//                            $priceCheck[$airportKey] = array('price' => (int)$getFlight['saleTotal'], 'flight' => $getFlight, 'airport' => $airportItem);
//
////                            }
//                        }
//
//                    }
//
//                    usort($priceCheck, function ($item1, $item2) {
//                        return $item1->price <=> $item2->price;
//                    });
//
//                    if($priceCheck[0]['flight']) {
//                        $closestEmployeeAirport = $priceCheck[0]['airport'];
//
//                        $potentialAssist->flightCheckFlight = $priceCheck[0]['flight'];
//                    } else {
//                        $closestEmployeeAirport = "No Flights";
//
//                        $potentialAssist->flightCheckFlight = "No Flights";
//                    }
//
//
//                } else {
//
//                    $currentJobCustomerItemID = $potentialAssist->podioItem->fields['current-job-customer']->values[0]->item_id;
//
//                    $currentJobCustomerItem = PodioItem::get($currentJobCustomerItemID);
//
//                    $currentCustomerAirportItemID = $currentJobCustomerItem->fields['closest-airport']->values[0]->item_id;
//
//                    $closestEmployeeAirport = PodioItem::get($currentCustomerAirportItemID);
//
//                }
//
//                $potentialAssist->closestCurrentAirport = $closestEmployeeAirport;
//
//                if($closestEmployeeAirport !== "No Flights") {
//                    $potentialAssist->destAirportDistance = googleMapsDistanceTimeCheck($potentialAssist->closestCurrentAirport->fields['address']->values['value'], $closestAirportAddress);
//
//                } else {
//                    $potentialAssist->destAirportDistance = 999999999;
//                }
//
//            }
//
//            usort($newAssistObject, function ($item1, $item2) {
//                return $item1->destAirportDistance <=> $item2->destAirportDistance;
//            });
//
//            $checkFlightsArray = [];
//            for($l = 0; $l < 4; $l++) {
//                if($newAssistObject[$l] && $newAssistObject[$l]->destAirportDistance !== 999999999) {
//                    $checkFlightsArray[] = $newAssistObject[$l];
//                }
//            }
//
//
//            $employeeTotalCosts = [];
//
//            foreach($checkFlightsArray as $employeeKey => $employee) {
//
//                if($employee->flightCheckFlight && $employee->flightCheckFlight !== "No Flights") {
//
//                    $flightInfo = $employee->flightCheckFlight;
//
//                } else {
//
////                    $flights = getFlights($startDate->format('Y-m-d'), $employee->closestCurrentAirport->fields['iatacode']->values, $closestAirportItem->fields['iatacode']->values);
////
////                    usort($flights, function ($item1, $item2) {
////                        return $item1->saleTotal <=> $item2->saleTotal;
////                    });
//
//                    $flightInfo = getFlights($startDate->format('Y-m-d'), $employee->closestCurrentAirport->fields['iatacode']->values, $closestAirportItem->fields['iatacode']->values);
//
//                }
//
//                if($flightInfo !== "No Flights") {
//
//                    $flightInfo2 = createFlights($flightInfo);
//
//                    $flightTime = $flightInfo2['flight']['duration'] / 60;
//
//                    $flightPrice = $flightInfo2['flight']['saleTotal'];
//
//                    if($flightInfo2['podioItem'] != "No Flights") {
//                        $flightItemID = $flightInfo2['podioItem']->item_id;
//                    }
//
//                    if($flightPrice < 1 || $flightPrice == null){
//                        $flightTime = 999999999;
//                        $flightPrice = 999999999;
//                    }
//
//                } else {
//                    $flightTime = 999999999;
//                    $flightPrice = 999999999;
//                }
//
//
//                $employeeItemID = $employee->podioItem->fields['employee']->values[0]->item_id;
//
//                $employeeItem = PodioItem::get($employeeItemID);
//
//                $employeePayRate = $employeeItem->fields['money']->values['value'];
//
//                if(!$employeePayRate) {
//                    $employeePayRate = 20;
//                }
//
//                $totalCost = (($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) * $employeePayRate) + (((($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) > 40 ? ($employee->currentHours + $employee->destAirportDistance + $airportToDestination + $flightTime) : 40) - 40) * ($employeePayRate / 2)) + $flightPrice;
//
//                $travelHours = $employee->destAirportDistance + $airportToDestination + $flightTime;
//
//                if($employee->currentHours + $travelHours > 40) {
//                    $overtimeHours = $employee->currentHours + $travelHours - 40;
//                    $normalHours = $travelHours - $overtimeHours;
//
//                    $travelCost = (($normalHours) * $employeePayRate) + (($overtimeHours) * ($employeePayRate * 1.5)) + $flightPrice;
//                } else {
//                    $travelCost = (($employee->destAirportDistance + $airportToDestination + $flightTime) * $employeePayRate) + (((($employee->destAirportDistance + $airportToDestination + $flightTime) > 40 ? ($employee->destAirportDistance + $airportToDestination + $flightTime) : 40) - 40) * ($employeePayRate / 2)) + $flightPrice;
//                }
//
//                if($flightPrice !== 999999999) {
//
//                    $employeeTotalCosts[$employeeKey] = array('employeeObj' => $employee, 'totalCost' => $totalCost, 'travelCost' => $travelCost, 'type' => "Flight", 'flightCost' => $flightPrice, 'flightTime' => $flightTime);
//
//                }
//
//                if($flightItemID){
//                    $employeeTotalCosts[$employeeKey]['flightItem'] = $flightItemID;
//                }
//
//            }
//
//
//            foreach($potentialAssists as $employee) {
//
//                $employeeItemID = $employee->podioItem->fields['employee']->values[0]->item_id;
//
//                if($employeeItemID) {
//
//                    $employeeItem = PodioItem::get($employeeItemID);
//
//                    $employeePayRate = $employeeItem->fields['money']->values['value'];
//
//                    if(!$employeePayRate) {
//                        $employeePayRate = 20;
//                    }
//
//
//                    $totalCost = (($employee->totalNewHours) * $employeePayRate) + ((($employee->totalNewHours > 40 ? $employee->totalNewHours : 40) - 40) * ($employeePayRate / 2));
//
//                    if($employee->totalNewHours > 40) {
//                        $overtimeHours = $employee->totalNewHours - 40;
//                        $normalHours = $employee->timeToLocation - $overtimeHours;
//
//                        $travelCost = (($normalHours) * $employeePayRate) + (($overtimeHours) * ($employeePayRate * 1.5));
//                    } else {
//                        $travelCost = (($employee->timeToLocation) * $employeePayRate) + ((($employee->timeToLocation > 40 ? $employee->timeToLocation : 40) - 40) * ($employeePayRate / 2));
//                    }
//
//                    $employeeTotalCosts[] = array('employeeObj' => $employee, 'totalCost' => $totalCost, 'travelCost' => $travelCost, 'type' => "Driving");
//
//                }
//
//            }
//
//            usort($employeeTotalCosts, function ($item1, $item2) {
//                return $item1['totalCost'] <=> $item2['totalCost'];
//            });
//
//            foreach($employeeTotalCosts as $empCostKey => $employeeTotalCost){
//                if($employeeTotalCost['totalCost'] == 0 && !$employeeTotalCost['employeeObj']->podioItem->item_id){
//                    unset($employeeTotalCosts[$empCostKey]);
//                }
//            }
//
//            $employeeTotalCosts = array_values($employeeTotalCosts);
//
//            $finalAssists = [];
//
//            for($l = 0; $l < 4; $l++) {
//                $finalAssists[] = $employeeTotalCosts[$l];
//            }
//
//
//        } else {
        $finalAssists = [];

        for($a = 0; $a < 4; $a++) {
            $finalAssists[$a]['employeeObj'] = $potentialAssists[$a];
            $finalAssists[$a]['travelCost'] = $potentialAssists[$a]->totalNewCost;
        }

//        }

        PodioComment::create('item', $item_id, ['value'=>"Finalizing options..."]);

        //find closest lead for each Assist
        foreach($finalAssists as $assistKey => $finalAssist){

            $timeToLead = [];

            $assistLocation = "";
            $assistLocation = $finalAssist['employeeObj']->podioItem->fields['location']->values['value'];

            foreach($finalLeads as $leadKey => $finalLead){

                $leadLocation = "";
                $leadLocation = $finalLead['employeeObj']->podioItem->fields['location']->values['value'];
                $leadItemID = "";
                $leadItemID = $finalLead['employeeObj']->podioItem->item_id;

                $timeToLead[$leadKey]['distance'] = 0;
                $timeToLead[$leadKey]['distance'] = googleMapsDistanceTimeCheck($assistLocation, $leadLocation);
                $timeToLead[$leadKey]['item_id'] = $leadItemID;

            }

            usort($timeToLead, function ($item1, $item2) {
                return $item1['distance'] <=> $item2['distance'];
            });

            $finalAssists[$assistKey]['closestLead'] = $timeToLead[0]['item_id'];

        }




        $schedulingArray = [
            'fields' => [
                'customer' => [$customerItemID],
                'product' => [$productItemID],
                'ntp' => $item_id,
                'complete-by-date' => [
                    'start' => $startDateFormatted,
                    'end' => $completionDateFormatted
                ],
                'lead-option-1' => [$finalLeads[0]['employeeObj']->podioItem->item_id],
                'lead-option-2' => [$finalLeads[1]['employeeObj']->podioItem->item_id],
                'lead-option-3' => [$finalLeads[2]['employeeObj']->podioItem->item_id],
                'lead-option-4' => [$finalLeads[3]['employeeObj']->podioItem->item_id],
                'assist-option-1' => [$finalAssists[0]['employeeObj']->podioItem->item_id],
                'assist-option-2' => [$finalAssists[1]['employeeObj']->podioItem->item_id],
                'assist-option-3' => [$finalAssists[2]['employeeObj']->podioItem->item_id],
                'assist-option-4' => [$finalAssists[3]['employeeObj']->podioItem->item_id],
                'lead-1-travel-cost' => "$ " . number_format($finalLeads[0]['travelCost'], 2),
                'lead-2-travel-cost' => "$ " . number_format($finalLeads[1]['travelCost'], 2),
                'lead-3-travel-cost' => "$ " . number_format($finalLeads[2]['travelCost'], 2),
                'lead-4-travel-cost' => "$ " . number_format($finalLeads[3]['travelCost'], 2),
                'assist-1-travel-cost' => "$ " . number_format($finalAssists[0]['travelCost'], 2),
                'assist-2-travel-cost' => "$ " . number_format($finalAssists[1]['travelCost'], 2),
                'assist-3-travel-cost' => "$ " . number_format($finalAssists[2]['travelCost'], 2),
                'assist-4-travel-cost' => "$ " . number_format($finalAssists[3]['travelCost'], 2),
                'assist-1-closest-lead' => [$finalAssists[0]['closestLead']],
                'assist-2-closest-lead' => [$finalAssists[1]['closestLead']],
                'assist-3-closest-lead' => [$finalAssists[2]['closestLead']],
                'assist-4-closest-lead' => [$finalAssists[3]['closestLead']]
            ]
        ];

//        foreach($finalLeads as $leadKey => $finalLead) {
//            if($finalLead['flightItem']) {
//                if($leadKey == 0) {
//                    $schedulingArray['fields']['lead-1-flight-options'] = $finalLead['flightItem'];
//                }
//                if($leadKey == 1) {
//                    $schedulingArray['fields']['lead-2-flight-options'] = $finalLead['flightItem'];
//                }
//                if($leadKey == 2) {
//                    $schedulingArray['fields']['lead-3-flight-options'] = $finalLead['flightItem'];
//                }
//                if($leadKey == 3) {
//                    $schedulingArray['fields']['lead-4-flight-options'] = $finalLead['flightItem'];
//                }
//            }
//        }
//
//        foreach($finalAssists as $assistKey => $finalAssist) {
//            if($finalAssist['flightItem']) {
//                if($assistKey == 0) {
//                    $schedulingArray['fields']['assist-1-flight-options'] = $finalAssist['flightItem'];
//                }
//                if($assistKey == 1) {
//                    $schedulingArray['fields']['assist-2-flight-options'] = $finalAssist['flightItem'];
//                }
//                if($assistKey == 2) {
//                    $schedulingArray['fields']['assist-3-flight-options'] = $finalAssist['flightItem'];
//                }
//                if($assistKey == 3) {
//                    $schedulingArray['fields']['assist-4-flight-options'] = $finalAssist['flightItem'];
//                }
//            }
//        }

        $projectSchedulingItem = PodioItem::create($projectSchedulingAppID, $schedulingArray);

        PodioItem::update($item_id, ['fields'=>['schedule'=>"Project Scheduling Item Created",'status'=>"Scheduling"]]);


        $dispatchAppID = 18070091;
        $jobsAppID = 17976754;



        $optionsArray = $item->fields['options']->values;

        $optionsItemIDArray = [];

        foreach($optionsArray as $option) {
            $optionsItemIDArray[] = $option->item_id;
        }

        $productItemID = $projectSchedulingItem->fields['product']->values[0]->item_id;

        $jobsFilter = PodioItem::filter($jobsAppID, ['filters' => ['product' => $productItemID], 'limit' => 500]);

        $jobsArray = [];
        foreach($jobsFilter as $job) {
            $jobsArray[] = $job->item_id;
        }

        $dispatchFieldsArray = [
            'fields' => [
                'project' => [$projectSchedulingItem->item_id],
                'ntp' => [$item_id],
                'product' => [$productItemID],
                'project-manager' => [$projectManagerItemID],
                'field-engineer' => [$ntpFieldEngineer],
                'install-manager' => [$ntpInstallManager]
            ]
        ];


        if(sizeof($jobsArray) > 0) {
            $dispatchFieldsArray['fields']['jobs'] = $jobsArray;
        }

        if(sizeof($optionsArray) > 0) {
            $dispatchFieldsArray['fields']['options'] = $optionsItemIDArray;
        }

        PodioItem::create($dispatchAppID, $dispatchFieldsArray);

    }//end if schedule trigger
    elseif($scheduleTrigger == "Manually Schedule") {
        PodioItem::update($item_id, ['fields' => ['schedule' => "In Progress"]]);

        $schedulingArray = [
            'fields' => [
                'customer' => [$customerItemID],
                'product' => [$productItemID],
                'ntp' => $item_id,
                'complete-by-date' => [
                    'start' => $startDateFormatted,
                    'end' => $completionDateFormatted
                ],
            ]
        ];

        $projectSchedulingItem = PodioItem::create($projectSchedulingAppID, $schedulingArray);

        PodioItem::update($item_id, ['fields' => ['schedule' => "Project Scheduling Item Created", 'status' => "Scheduling"]]);


        $dispatchAppID = 18070091;
        $jobsAppID = 17976754;

        $ntpFieldEngineer = $item->fields['field-engineer']->values[0]->item_id;

        $ntpInstallManager = $item->fields['install-manager']->values[0]->item_id;

        $optionsArray = $item->fields['options']->values;

        $optionsItemIDArray = [];

        foreach($optionsArray as $option) {
            $optionsItemIDArray[] = $option->item_id;
        }

        $productItemID = $projectSchedulingItem->fields['product']->values[0]->item_id;

        $jobsFilter = PodioItem::filter($jobsAppID, ['filters' => ['product' => $productItemID], 'limit' => 500]);

        $jobsArray = [];
        foreach($jobsFilter as $job) {
            $jobsArray[] = $job->item_id;
        }

        $dispatchFieldsArray = [
            'fields' => [
                'project' => [$projectSchedulingItem->item_id],
                'ntp' => [$item_id],
                'product' => [$productItemID],
                'project-manager' => [$projectManagerItemID],
                'field-engineer' => [$ntpFieldEngineer],
                'install-manager' => [$ntpInstallManager]
            ]
        ];


        if(sizeof($jobsArray) > 0) {
            $dispatchFieldsArray['fields']['jobs'] = $jobsArray;
        }

        if(sizeof($optionsArray) > 0) {
            $dispatchFieldsArray['fields']['options'] = $optionsItemIDArray;
        }

        PodioItem::create($dispatchAppID, $dispatchFieldsArray);
    }

//END AUTOMATION

    return [
        'success' => true,
        'result' => $result,
    ];

}catch(Exception $e)
{
    PodioItem::update($item_id, ['fields'=>['schedule'=>"Error"]]);

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