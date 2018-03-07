<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/14/2016
 * Time: 6:48 PM
 */

$result = array();

$api_key = '?api_key=36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88';

$curl = new \Curl\Curl();

$baseURL = 'https://hoist.thatapp.io/api/v2';

$tableName = 'Account';

$resource = '/salesforce/_table/'. $tableName;

$fields = 'Name, OwnerId, Account_Short_Name__c, ParentId, Phone, Website, Division__c, Region__c, BillingStreet, BillingCity, BillingState, BillingPostalCode, BillingCountry, ShippingStreet, ShippingCity, ShippingState, ShippingPostalCode, ShippingCountry, Type, Description, Autotask_Account_ID__c, Industry, EPlant__c, Id';

$params = $api_key;
$params .= '&fields='.urlencode($fields);

$fullURL = $baseURL.$resource.$params;

array_push($result, $fullURL);

$response = $curl->get($fullURL);

$rows = $response;

//array_push($result, $rows);

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
        $Title = $row->Name;
        $AccountOwner = $row->OwnerId;
        $AccountShortName = $row->Account_Short_Name__c;
        $Phone = $row->Phone;
        $Website = $row->Website;
        $Division = $row->Division__c;
        $Region = $row->Region__c;
        $Region = utf8_decode($Region);
        $Region = str_replace('?', '-', $Region);
        $BillingStreet = $row->BillingStreet;
        $BillingCity = $row->BillingCity;
        $BillingState = $row->BillingState;
        $BillingPostalCode = $row->BillingPostalCode;
        $BillingCountry = $row->BillingCountry;
        $ShippingStreet = $row->ShippingStreet;
        $ShippingCity = $row->ShippingCity;
        $ShippingState = $row->ShippingState;
        $ShippingPostalCode = $row->ShippingPostalCode;
        $ShippingCountry = $row->ShippingCountry;
        $AccountType = $row->Type;
        $AccountDescription = $row->Description;
        $AutotaskAccountID = $row->Autotask_Account_ID__c;
        $ParentAccount = $row->ParentId;
        $SFID = $row->Id;


        //Filter Accounts App by SFID for existing item
        $FilterForExisting = PodioItem::filter(16307520, array("filters" => array('sfid' => $SFID)));
        $ExistingAccountItemID = $FilterForExisting[0]->item_id;

        //Hardcode SF Owner ID with Member Item ID
        if ($AccountOwner == '00560000001F8pQAAS') {
            $OwnerItemId = (int)397587255;
        }
        if ($AccountOwner == '00560000004HbvJAAS') {
            $OwnerItemId = (int)397587252;
        }
        if ($AccountOwner == '00560000001vBKkAAM') {
            $OwnerItemId = (int)397587251;
        }
        if ($AccountOwner == '00560000004HEeZAAW') {
            $OwnerItemId = (int)397587250;
        }
        if ($AccountOwner == '00560000001HzGxAAK') {
            $OwnerItemId = (int)397587248;
        }
        if ($AccountOwner == '00560000001Hcff') {
            $OwnerItemId = (int)397587247;
        }
        if ($AccountOwner == '005320000057ODXAA2') {
            $OwnerItemId = (int)397581011;
        }


        //Create Fields Array
        $fieldsArray = array(
            'fields' => array(
                'dashboard'=>450150940
            ));


        //Get Item Values
        if ($Website) {
            if (filter_var($Website, FILTER_VALIDATE_URL) === TRUE) {
                $LINK = PodioEmbed::create(array('url' => $Website));
                $LinkEmbedID = $LINK->embed_id;
                $fieldsArray['fields']['website'] = $LinkEmbedID;
            }
        }
        if ($AccountType) {
            $fieldsArray['fields']['account-type'] = $AccountType;
        }
        if ($AccountDescription) {
            $fieldsArray['fields']['account-description'] = $AccountDescription;
        }
        if ($AutotaskAccountID) {
            $fieldsArray['fields']['autotask-account-id'] = $AutotaskAccountID;
        }
        if($ParentAccount){
            $FilterAccount = PodioItem::filter(16307520, array("filters" => array('sfid' => $ParentAccount)));
            $ParentAccountItemID = $FilterAccount[0]->item_id;
            if ($ParentAccountItemID) {
                $fieldsArray['fields']['parent-account'] = array((int)$ParentAccountItemID);
            }
        }
        if ($SFID) {
            $fieldsArray['fields']['sfid'] = $SFID;
        }


        //Create new account Item if does not exist
        if(!$ExistingAccountItemID){
            if ($Title) {
                $fieldsArray['fields']['title'] = $Title;
            }
            if ($OwnerItemId) {
                $fieldsArray['fields']['account-owner-3'] = $OwnerItemId;
            }
            if ($AccountShortName) {
                $fieldsArray['fields']['account-short-name'] = $AccountShortName;
            }
            if ($Phone) {
                $fieldsArray['fields']['phone'] = array('type' => 'work', 'value' => $Phone);
            }
            if ($Division) {
                $fieldsArray['fields']['division'] = $Division;
            }
            if ($Region) {
                $fieldsArray['fields']['region'] = $Region;
            }
            if ($BillingStreet) {
                $fieldsArray['fields']['billing-address']['street_address'] = $BillingStreet;
            }
            if ($BillingCity) {
                $fieldsArray['fields']['billing-address']['city'] = $BillingCity;
            }
            if ($BillingState) {
                $fieldsArray['fields']['billing-address']['state'] = $BillingState;
            }
            if ($BillingPostalCode) {
                $fieldsArray['fields']['billing-address']['postal_code'] = $BillingPostalCode;
            }
            if ($BillingCountry) {
                $fieldsArray['fields']['billing-address']['county'] = $BillingCountry;
            }
            if ($ShippingStreet) {
                $fieldsArray['fields']['shipping-address']['street_address'] = $BillingCountry;
            }
            if ($ShippingCity) {
                $fieldsArray['fields']['shipping-address']['city'] = $ShippingCity;
            }
            if ($ShippingState) {
                $fieldsArray['fields']['shipping-address']['state'] = $ShippingState;
            }
            if ($ShippingPostalCode) {
                $fieldsArray['fields']['shipping-address']['postal_code'] = $ShippingPostalCode;
            }
            if ($ShippingCountry) {
                $fieldsArray['fields']['shipping-address']['county'] = $ShippingCountry;
            }

            //Create New Account Item
            $CreateAccountItem = PodioItem::create(16307520, $fieldsArray);
        }

        //Update existing Account Item
        elseif($ExistingAccountItemID){
            $CreateAccountItem = PodioItem::update($ExistingAccountItemID, $fieldsArray);
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
