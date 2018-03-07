<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/30/2016
 * Time: 9:05 AM
 */

$result = array();

$api_key = '?api_key=36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88';

$curl = new \Curl\Curl();

$baseURL = 'https://hoist.thatapp.io/api/v2';

$tableName = 'Account';

$resource = '/fpsalesforce/_table/'. $tableName;

$URL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Account";

$fields = 'Id, IsDeleted, MasterRecordId, Name, Type, RecordTypeId, ParentId, BillingStreet, BillingCity, BillingState, BillingPostalCode,
BillingCountry, ShippingStreet, ShippingCity, ShippingState, ShippingPostalCode, ShippingCountry, ShippingLatitude, ShippingLongitude, Phone,
Fax, Website, Industry, AnnualRevenue, NumberOfEmployees, Description,CurrencyIsoCode, CurrencyIsoCode, OwnerId, CreatedDate, CreatedById,
LastModifiedDate, LastModifiedById, SystemModstamp, LastActivityDate, LastViewedDate, LastReferencedDate,JigsawCompanyId, AccountSource, Order_Phone_Number__c,
New_Terms_Conditions_Opps__c, Country__c, City__c, Source__c, Account_Status__c, Chain__c, No_of_restaurants__c , Street_Name__c, House_number__c,Post_Code__c,
Area_District__c, Delivery_Area__c, SubArea__c, External_Rating__c, number_of_active_contracts__c, Feature_With_Competitor__c, Opps__c, Chain_ID_unique__c,
Saturday__c, Sunday__c, Delivery_Fee__c, Lead_Gen_Comments__c, Competitor_Name__c, Service_Charge__c,
AAA__c, Preferred_Contact_Language__c, Vendor_Backend_Code__c, Partner_Type__c, Delivery_Charge__c, Order_Email__c,
Role_of_Account_Owner__c, Other_Phone__c, Number_of_approved_contracts__c, Number_of_reviews__c,
Assigned_Date__c, Content_Completed__c, Number_of_active_Chain_Contracts__c, Chain_IDs__c, Target_Partner1__c, num_closed_tasks__c,
num_closed_events__c, Last_Activity_Date__c, Last_Activity_Subject__c, Next_Activity_Date__c,
Next_Activity_Subject__c, vendor_code_unique__c, Top_AAA__c, Corporate_Backend_Code__c, Company_Scope__c, Industry__c,
Customer_Type__c, Closed_Lost_Opps__c, New_Cuisine_Business_Characteristics__c, Welcome_call_on__c,
Content_completed_by__c, of_Reneg_Opps__c, of_AM_Opps__c, Sales_Opps__c, num_Contracts__c, Days_since_Activation__c,
Next_Activity_7_Days__c, Last_Assigned_7_Days__c, Vendor_Grade_Image__c, Vendor_Grade__c,
X18_Char_ID__c, No_of_Employees__c, Last_Activity_30_days__c, Last_Activity_7_Days__c, Status_change_Date__c,
open_Sales_Opps__c, Temp_Status__c, Vendor_Prioritization__c, Vendor_Location__Latitude__s,
Vendor_Location__Longitude__s, Vendor_Location__c, Content_Completed_Time_Stamp__c, Preview_Link__c, Activated_Date__c,
Title_of_Account_Owner__c, Facebook_URL__c, ClosedWon_Sales_Opps__c, Show_Nearby_Vendors__c, Account_Owner_Profile__c,
Price_Range__c, Similarweb_Hostname__c, Similarweb_Visits__c, Active_Bank_Account_Name__c, Active_Bank_Account_Number__c,
Active_Bank_Address__c, Active_Bank_Name__c, Active_Commission_Rate_Percentage__c, Active_IFSC__c, Active_Min_Order_Value__c,
Active_Tuesday__c, active_Friday__c, active_Monday__c, active_Saturday__c, active_Sunday__c, active_Thursday__c,
active_Wednesday__c, Meal_reimbursements__c, Coverage_Area__c, Vendor_has_Own_Delivery__c, Reason_for_Deactivation__c';



$params = $api_key;
$params .= '&fields='.urlencode($fields);

$fullURL = $URL.$params;


$response = $curl->get($fullURL);

$rows = $response->resource;

print_r("Rows: ".$response);
exit;

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
        $FilterForExisting = PodioItem::filter(17312257, array("filters" => array('sfid' => $SFID)));
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
