<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/14/2016
 * Time: 6:48 PM
 */

$result = array();

$api_key = '?api_key=36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88';

$SessionToken = '&session_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjYsInVzZXJfaWQiOjYsImVtYWlsIjoiaXJvYmVydHNvbkB0ZWNoZWdvLmNvbS
IsImZvcmV2ZXIiOmZhbHNlLCJpc3MiOiJodHRwczpcL1wvaG9pc3QudGhhdGFwcC5pb1wvYXBpXC92Mlwvc3lzdGVtXC9hZG1pblwvc2Vzc2lvbiIsImlhdCI6MTQ3MjU5MTcwMCwiZ
XhwIjoxNDcyNTk1MzAwLCJuYmYiOjE0NzI1OTE3MDAsImp0aSI6ImNmYTYyMDc2MDZjNTdhMmRmMTdlYTlmOTc0MTdmY2EyIn0.CDvvLcjJtwu4yr_Hiq3YpmQdK10WLX2LrBoOIbQybJ4';

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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"
    ));


    foreach($rows->resource as $row) {

        $Name = $row->Name;
        $StartDate = $row->StartDate;
        $EndDate = $row->EndDate;
        $Division = $row->Division__c;
        $OwnerId = $row->OwnerId;
        $Status = $row->Status;
        $SFID = $row->Id;
        $ParentId = $row->ParentId;


        if (!$ParentId) {


//Hardcode SF Owner ID with Member Item ID

            if ($OwnerId == '00560000001F8pQAAS') {
                $OwnerItemId = (int)397587255;
            }
            if ($OwnerId == '00560000004HbvJAAS') {
                $OwnerItemId = (int)397587252;
            }
            if ($OwnerId == '00560000001vBKkAAM') {
                $OwnerItemId = (int)397587251;
            }
            if ($OwnerId == '00560000004HEeZAAW') {
                $OwnerItemId = (int)397587250;
            }
            if ($OwnerId == '00560000001HzGxAAK') {
                $OwnerItemId = (int)397587248;
            }
            if ($OwnerId == '00560000001Hcff') {
                $OwnerItemId = (int)397587247;
            }
            if ($OwnerId == '005320000057ODXAA2') {
                $OwnerItemId = (int)397581011;
            }


            $fieldsArray = array(
                'fields' => array(
                    'dashboard' => 450150940,
                ));

            if ($Name) {
                $fieldsArray['fields']['title'] = $Name;
            }
            if ($StartDate && $EndDate) {
                $FormatStartDate = new DateTime((string)$StartDate);
                $FormatEndDate = new DateTime((string)$EndDate);
                $fieldsArray['fields']['date'] = array('start' => $FormatStartDate->format('Y-m-d H:i:s'), 'end' => $FormatEndDate->format('Y-m-d H:i:s'));
            }
            if ($Division) {
                list($Division1, $Division2, $Division3) = explode(";", $Division);
                echo $Division1;
                echo $Division2;
                echo $Division3;
                $fieldsArray['fields']['division'] = array($Division1, $Division2, $Division3);
            }
            if ($Status) {
                $fieldsArray['fields']['status'] = $Status;
            }
            if ($SFID) {
                $fieldsArray['fields']['sfid'] = $SFID;
            }
            if ($OwnerItemId) {
                $fieldsArray['fields']['campaign-owner'] = $OwnerItemId;
            }


            $FilterCampaigns = PodioItem::filter(16261940, array('filters' => array('title' => $Name)));
            $CampaignItemID = $FilterCampaigns[0]->item_id;

            if (!$CampaignItemID) {

                $CreateCampaign = PodioItem::create(16261940, $fieldsArray);
            }

            else {
                $UpdateCampaign = PodioItem::update($CampaignItemID, $fieldsArray);
            }
        }
    }


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

