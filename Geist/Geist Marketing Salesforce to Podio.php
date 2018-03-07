<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/14/2016
 * Time: 6:48 PM
 */

$result = array();

$api_key = '?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';

$curl = new \Curl\Curl();

$baseURL = 'https://hoist.thatapp.io/api/v2';

$tableName = 'Campaign';

$resource = '/salesforce/_table/'. $tableName;

$fields = 'Name, StartDate, EndDate, Division__c, OwnerId, Status, Id, ParentId';

$params = $api_key;
$params .= '&fields='.urlencode($fields);

$fullURL = $baseURL.$resource.$params;

array_push($result, $fullURL);

$response = $curl->get($fullURL);

$rows = $response;

//array_push($result, $rows);

class PodioSessionManager {
    private static $connection_id = 4;
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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"
    ));

    $names = array();

    foreach($rows->resource as $row){

        $Name = $row->Name;
        $StartDate = $row->StartDate;
        $EndDate = $row->EndDate;
        $Division = $row->Division__c;
        $OwnerId = $row->OwnerId;
        $Status = $row->Status;
        $SFID = $row->Id;
        $ParentId = $row->ParentId;

        $FormatStartDate = new DateTime((string)$StartDate);
        $FormatEndDate = new DateTime((string)$EndDate);

        //array_push($names, $Division);

        list($Division1, $Division2, $Division3) = explode(";", $Division);
        echo $Division1;
        echo $Division2;
        echo $Division3;

        $fieldsArray = array(
            'fields' => array(
                'dashboard' => 450150940,
            ));

        if($Name){
            $fieldsArray['fields']['title'] = $Name;
        }
        if($StartDate && $EndDate){
            $fieldsArray['fields']['date'] = array('start' => $FormatStartDate->format('Y-m-d H:i:s'),'end'=> $FormatEndDate->format('Y-m-d H:i:s'));
        }
        if($Division){
            $fieldsArray['fields']['division'] = array($Division1, $Division2, $Division3);
        }
        if($Status){
            $fieldsArray['fields']['status'] = $Status;
        }
        if($SFID){
            $fieldsArray['fields']['sfid'] = $SFID;
        }
        if($OwnerId){
            $fieldsArray['fields']['owner-id'] = $OwnerId;
        }



        if(!$ParentId){
            $CreateCampaign = PodioItem::create(16261940, $fieldsArray);

        }
    }


    array_push($result, $names);

//RETURN / CATCH
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


//array(
//    'fields' => array(
//        'title' => $Name,
//        'date' => array('start' => $FormatStartDate->format('Y-m-d H:i:s'),'end'=> $FormatEndDate->format('Y-m-d H:i:s')),
//        'division' => $Division,
//        'status' => $Status,
//        'sfid'=> $SFID,
//        'dashboard' => 450150940,
//    )
//)
