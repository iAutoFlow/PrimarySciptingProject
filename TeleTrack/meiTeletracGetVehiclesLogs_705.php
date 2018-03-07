<?php

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

///AUTOMATION START///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// Les Variables

    $navmanUser = 'TECHeGO';
    $navmanPass = 'TECHeGO';
    $acctID = 321499;

    $vehiclesAppID = 18079807;
    $vehicles_vechicleNameXID = 'vehicle-name';
    $vehicles_vehicleNameFieldID = 142826432;
    $vehicles_eventDateTimeXID = 'event-date-time';
    $vehicles_eventDateTimeFieldID = 142826433;
    $vehicles_locationXID = 'location';
    $vehicles_locationFieldID = 142826428;
    $vehicles_statusAbbrXID = 'status-abbr';
    $vehicles_statusAbbrFieldID = 142826434;
    $vehicles_vinXID = 'vin';
    $vehicles_vinFieldID = 142063183;
    $vehicles_ipXID = 'ip';
    $vehicles_ipFieldID = 142826431;
    $vehicles_latitudeXID = 'latitude-hidden';
    $vehicles_latitudeFieldID = 142826429;
    $vehicles_longitudeXID = 'longitude-hidden';
    $vehicles_longitudeFieldID = 142826430;
    $vehicles_vehicleNameHiddenXID = 'vehiclenamehidden';
    $vehicles_vehicleNameHiddenFieldID = 143010931;


    $travelLogsAppID = 18079810;
    $travelLogs_vehicleXID = 'vehicle';
    $travelLogs_vehicleFieldID = 142058851;
    $travelLogs_eventTimeXID = 'event-time';
    $travelLogs_eventTimeFieldID = 142058852;
    $travelLogs_statusXID = 'status';
    $travelLogs_statusFieldId = 142058853;
    $travelLogs_locationXID = 'address';
    $travelLogs_locationFieldID = 142058855;
    $travelLogs_vinXID = 'vin';
    $travelLogs_vinFieldID = 142879606;
    $travelLogs_ipXID = 'ip';
    $travelLogs_ipFieldID = 142879607;
    $travelLogs_latitudeXID = 'latitude-hidden';
    $travelLogs_latitudeFieldID = 142879604;
    $travelLogs_longitudeXID = 'longitude-hidden';
    $travelLogs_longitudeFieldID = 142879605;


    $curl = new \Curl\Curl();

    $getVehiclesURL = 'https://hoist.thatapp.io/api/v2/teletracxml/GetVehicles?api_key=36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88';

    $postData = '{
		"AccountId": '.$acctID.',
		"Username": "'.$navmanUser.'",
		"Password": "'.$navmanPass.'"
		}';

    $curl->setHeader('Content-Type', 'application/json');

    $doLoginRequest = $curl->post($getVehiclesURL,$postData);

    $vehiclesForLoop = $doLoginRequest->GetVehiclesResult->Vehicle;

    foreach($vehiclesForLoop as $vehicleObject){

        $podioLogsItemCreateArray = array();

        $return_streetAddress = $vehicleObject->StreetAddress;
        $return_city = $vehicleObject->City;
        $return_state = $vehicleObject->State;
        $return_zipCode = $vehicleObject->ZipCode;
        $return_vehicleName = $vehicleObject->VehicleName;
        $return_vin = $vehicleObject->VIN;
        $return_ip= $vehicleObject->IP;
        $return_statusAbbr = $vehicleObject->StatusAbbr;
        $return_eventDateTime = $vehicleObject->EventDateTime;
        $return_latitude = $vehicleObject->Latitude;
        $return_longitude = $vehicleObject->Longitude;


        if($return_streetAddress){$podioLogsItemCreateArray['fields'][$travelLogs_locationXID] = (string)$return_streetAddress." ".(string)$return_city.", ".(string)$return_state." ".(string)$return_zipCode;}

        if($return_vin){$podioLogsItemCreateArray['fields'][$travelLogs_vinXID] = (string)$return_vin;}
        if($return_vehicleName){$podioLogsItemCreateArray['fields'][$vehicles_vehicleNameHiddenXID] = (string)$return_vehicleName;}
        if($return_ip){$podioLogsItemCreateArray['fields'][$travelLogs_ipXID] = (string)$return_ip;}
        if($return_statusAbbr){$podioLogsItemCreateArray['fields'][$travelLogs_statusXID] = (string)$return_statusAbbr;}
        if($return_eventDateTime){$podioLogsItemCreateArray['fields'][$travelLogs_eventTimeXID] = date("Y-m-d H:i:s", strtotime($return_eventDateTime));}
        if($return_latitude){$podioLogsItemCreateArray['fields'][$travelLogs_latitudeXID] = (string)$return_latitude;}
        if($return_longitude){$podioLogsItemCreateArray['fields'][$travelLogs_longitudeXID] = (string)$return_longitude;}

        $relatedVehicleFilter = PodioItem::filter($vehiclesAppID, array('filters' => array($vehicles_vinXID => $return_vin)));

        $relatedVehicle = $relatedVehicleFilter[0];
        if(!$relatedVehicle){break;}

        $podioLogsItemCreateArray['fields'][$travelLogs_vehicleXID] = array((int)$relatedVehicle->item_id);

        $relatedItems = PodioItem::get_references($relatedVehicle->item_id);

        if($relatedItems) {

            $mostRecentItemID = $relatedItems[0]['items'][0]['item_id'];
            $mostRecentItem = PodioItem::get($mostRecentItemID);
            $mostRecentStatus = $mostRecentItem->fields[$travelLogs_statusXID]->values[0]['text'];

            if ($mostRecentStatus !== $return_statusAbbr) {
                $hereWeGo = PodioItem::create($travelLogsAppID, $podioLogsItemCreateArray);
            }

            elseif($mostRecentStatus == "AV" || $mostRecentStatus == "IR" || $mostRecentStatus == "ON" || $mostRecentStatus == "PA" || $mostRecentStatus == "VI") {
                $hereWeGoAgain = PodioItem::create($travelLogsAppID, $podioLogsItemCreateArray);
            }
        }

        else{
            PodioItem::create($travelLogsAppID, $podioLogsItemCreateArray);
        }

    }





///END AUTOMATION/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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