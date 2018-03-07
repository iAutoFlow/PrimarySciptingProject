<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/14/2016
 * Time: 6:48 PM
 */

$result = array();


$curl = new \Curl\Curl();

$baseURL = 'https://na26.salesforce.com/services/oauth2/authorize?response_type=code&client_id=';

$APIkey = '?api_key=raTRktI3iSMrtCoDO1qR2C4Vs';






//$params = $api_key;
//$params .= '&fields='.urlencode($fields);

//$SFItemID = '?ids=701320000011hCK';



$fullURL = $baseURL.$APIkey;

array_push($result, $fullURL);

$response = $curl->get($fullURL);

$rows = $response;

print_r($rows);
exit;

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

    $Array = array();

    foreach($rows->resource as $row) {

        $Name = $row->Name;
        $StartDate = $row->StartDate;
        $EndDate = $row->EndDate;
        $OwnerId = $row->OwnerId;
        $Status = $row->Status;
        $SFID = $row->Id;
        $ParentId = $row->ParentId;
        $Type = $row->Type;
        $Provider = $row->Tradeshow_Provider__c;
        $Requestor = $row->Campaign_Requestor__c;
        $Description = $row->Description;








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


//Hardcode SF Owner ID with Member Item ID

        if ($Requestor == '00560000001F8pQAAS') {
            $RequestorItemId = (int)397587255;
        }
        if ($Requestor == '00560000004HbvJAAS') {
            $RequestorItemId = (int)397587252;
        }
        if ($Requestor == '00560000001vBKkAAM') {
            $RequestorItemId = (int)397587251;
        }
        if ($Requestor == '00560000004HEeZAAW') {
            $RequestorItemId = (int)397587250;
        }
        if ($Requestor == '00560000001HzGxAAK') {
            $RequestorItemId = (int)397587248;
        }
        if ($Requestor == '00560000001Hcff') {
            $RequestorItemId = (int)397587247;
        }
        if ($Requestor == '005320000057ODXAA2') {
            $RequestorItemId = (int)397581011;
        }




        $fieldsArray = array(
            'fields' => array(
                'dashboard'=>450150940
            ));

        if ($Name) {
            $fieldsArray['fields']['title'] = $Name;
        }
        if ($StartDate) {
            $FormatStartDate = new DateTime((string)$StartDate);
            $fieldsArray['fields']['start-date'] = array('start' => $FormatStartDate->format('Y-m-d H:i:s'));
        }

        if($EndDate){
            $FormatEndDate = new DateTime((string)$EndDate);
            $fieldsArray['fields']['end-date'] = array('start' => $FormatEndDate->format('Y-m-d H:i:s'));
        }

        if ($Status) {
            $fieldsArray['fields']['status'] = $Status;
        }
        if ($SFID) {
            $fieldsArray['fields']['sfid'] = $SFID;
        }
        if ($OwnerItemId) {
            $fieldsArray['fields']['assigned-to-2'] = $OwnerItemId;
        }

        if ($ParentId) {
            $FilterCampaign = PodioItem::filter(16261940, array('filters' => array('sfid' => $ParentId)));
            $CampaignItemID = $FilterCampaign[0]->item_id;
            $fieldsArray['fields']['campaign'] = array((int)$CampaignItemID);
        }

        if($Type){
            $FilterJobTypes = PodioItem::filter(16261755, array('filters' => array('title' => $Type)));
            $JobTypeItemID = $FilterJobTypes[0]->item_id;
        }
        if($JobTypeItemID){
            $fieldsArray['fields']['job-type'] = array((int)$JobTypeItemID);
        }
        if ($RequestorItemId) {
            $fieldsArray['fields']['project-requestor'] = $RequestorItemId;
        }
        if ($Description) {
            $fieldsArray['fields']['project-description'] = $Description;
        }
        if ($Provider) {
            $fieldsArray['fields']['provider-id'] = $Provider;
        }





        if($ParentId){
            $CreateCampaign = PodioItem::create(16261915, $fieldsArray);
        }
    }


//RETURN / CATCH
    return [
        'success' => true,
        'result' => $Array,
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

