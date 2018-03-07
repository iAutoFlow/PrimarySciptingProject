<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/1/2016
 * Time: 3:47 PM
 */


//<?php
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


try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    define("USERNAME", "seth@techego.com");
    define("PASSWORD", "O@thK33p3r");
    define("SECURITY_TOKEN", "Q2zTPM5yxVlhXcNpxWTOJODQ8");

    //require_once ('soapclient/SforceEnterpriseClient.php');

    //$mySforceConnection = new SforceEnterpriseClient();
    //$mySforceConnection->createConnection("soapclient/enterprise.wsdl.xml");
    $mySforceConnection = login(USERNAME.PASSWORD.SECURITY_TOKEN);

    $SecurityToken = 'Q2zTPM5yxVlhXcNpxWTOJODQ8';

    $BaseURL = 'https://hoist.thatapp.io/api/v2/salesforce/_table/Campaign/';


    $curl = new \Curl\Curl();

    //Get Campaign Item
    if($appID == 16261940){

        $CampTitle = $item->fields['title']->values;
        $CampStartDate = $item->fields['date']->start;
        $CampEndDate = $item->fields['date']->end;
        $CampDivision = $item->fields['division']->values[0]['text'];
        $CampStatus = $item->fields['status']->values[0]['text'];
        $CampOwnerItemID = $item->fields['campaign-owner']->values[0]->item_id;
        $CampSFID = $item->fields['sfid']->values;



        //Set Campaign Ownder SFID
        if ($CampOwnerItemID == 397587255) {
            $OwnerSFID = '00560000001F8pQAAS';
        }
        if ($CampOwnerItemID == 397587252) {
            $OwnerSFID = '00560000004HbvJAAS';
        }
        if ($CampOwnerItemID == 397587251) {
            $OwnerSFID = '00560000001vBKkAAM';
        }
        if ($CampOwnerItemID == 397587250) {
            $OwnerSFID = '00560000004HEeZAAW';
        }
        if ($CampOwnerItemID == 397587248) {
            $OwnerSFID = '00560000001HzGxAAK';
        }
        if ($CampOwnerItemID == 397587247) {
            $OwnerSFID = '00560000001Hcff';
        }
        if ($CampOwnerItemID == 397581011) {
            $OwnerSFID = '005320000057ODXAA2';
        }


        //Campaign Fields Array
        $FieldsArray = array(
            'Name'=>$CampTitle,
            'StartDate'=>$CampStartDate,
            'EndDate'=>$CampEndDate,
            'Division__c'=>$CampDivision,
            'Status'=>$CampStatus,
            'OwnerID'=>$OwnerSFID,
            'sfid'=>$CampSFID,
        );


        //Object Type
        $ObjectType = "Campaign";

        //If there is a SFID, Update Item in Salesforce
        if($CampSFID){
            $FullURL = $BaseURL.$CampSFID.'?fields='.$FieldsArray;
            $response = $curl->patch($fullURL);
            print_r($response);
            exit;
        }

        //If there NOT is a SFID, Create Item in Salesforce
        if(!$CampSFID){}
    }



    //Get Project Item
    if($appID == 16261915){

        $ProjectTitle = $CampTitle = $item->fields['title']->values;
        $ProjectStartDate = $item->fields['start-date']->start;
        $ProjectEndDate = $item->fields['end-date']->start;
        $ProjectStatus = $item->fields['status']->values[0]['text'];
        $ProjectSFID = $item->fields['sfid']->values;
        $ProjectDescription = $item->fields['project-description']->values;
        $ProjectProviderSFID = $item->fields['provider-id']->values;

        //Set Project Requestor SFID
        $ProjectRequestorItemID = $item->fields['project-requestor']->values[0]->item_id;
        if($ProjectRequestorItemID){
            if ($ProjectRequestorItemID == 397587255) {
                $RequestorSFID = '00560000001F8pQAAS';
            }
            if ($ProjectRequestorItemID == 397587252) {
                $RequestorSFID = '00560000004HbvJAAS';
            }
            if ($ProjectRequestorItemID == 397587251) {
                $RequestorSFID = '00560000001vBKkAAM';
            }
            if ($ProjectRequestorItemID == 397587250) {
                $RequestorSFID = '00560000004HEeZAAW';
            }
            if ($ProjectRequestorItemID == 397587248) {
                $RequestorSFID = '00560000001HzGxAAK';
            }
            if ($ProjectRequestorItemID == 397587247) {
                $RequestorSFID = '00560000001Hcff';
            }
            if ($ProjectRequestorItemID == 397581011) {
                $RequestorSFID = '005320000057ODXAA2';
            }
        }

        //Set Project Owner SFID
        $ProjectOwnerItemId = $item->fields['assigned-to-2']->values[0]->item_id;
        if($ProjectOwnerItemId){
            if ($ProjectOwnerItemId == 397587255) {
                $OwnerId = '00560000001F8pQAAS';
            }
            if ($ProjectOwnerItemId == 397587252) {
                $OwnerId = '00560000004HbvJAAS';
            }
            if ($ProjectOwnerItemId == 397587251) {
                $OwnerId = '00560000001vBKkAAM';
            }
            if ($ProjectOwnerItemId == 397587250) {
                $OwnerId = '00560000004HEeZAAW';
            }
            if ($ProjectOwnerItemId == 397587248) {
                $OwnerId = '00560000001HzGxAAK';
            }
            if ($ProjectOwnerItemId == 397587247) {
                $OwnerId = '00560000001Hcff';
            }
            if ($ProjectOwnerItemId == 397581011) {
                $OwnerId = '005320000057ODXAA2';
            }
        }

        //Get Project Type Title from Market Management
        $ProjectTypeItemID = $item->fields['job-type-2']->values[0]->item_id;
        if($ProjectTypeItemID) {
            $ProjectTypeItem = PodioItem::get($ProjectTypeItemID);
            $ProjectTypeTitle = $ProjectTypeItem->fields['title']->values;
        }

        //Get Parent Campaign SFID
        $ProjectParentCampaignItemID = $item->fields['campaign']->values[0]->item_id;
        if($ProjectParentCampaignItemID) {
            $ParentCampaignItem = PodioItem::get($ProjectParentCampaignItemID);
            $ParentCampaignSFID = $ParentCampaignItem->fields['sfid']->values;
        }


        //Project FieldsArray
        $FieldsArray = array(
            'Name'=>$ProjectTitle,
            'StartDate'=>$ProjectStartDate,
            'EndDate'=>$ProjectEndDate,
            'Status'=>$ProjectStatus,
            'Id'=>$ProjectSFID,
            'Description'=>$ProjectDescription,
            'Campaign_Requestor__c'=>$RequestorSFID,
            'OwnerId'=>$OwnerId,
            'Type'=>$ProjectTypeTitle,
            'ParentId'=>$ParentCampaignSFID,
        );

        //Object Type
        $ObjectType = "Campaign";

        //If there is a SFID, Update Item in Salesforce
        if($ProjectSFID){}

        //If there NOT is a SFID, Create Item in Salesforce
        if(!$ProjectSFID){}



    }





    //Get Tradeshow Planning Item
    if($appID == 16307493){

        $TSPONumber = $item->fields['title']->values;
        $TSBoothStand = $item->fields['boothstand']->values;
        $TSHotelName = $item->fields['hotel-name']->values;
        $TSConventionCenter = $item->fields['convention-center']->values;
        $TSShowStartDates = $item->fields['show-dates']->start;
        $TSShowEndDate = $item->fields['show-dates']->end;
        $TSDiscountDeadlineDate = $item->fields['discount-deadline-date']->start;
        $TSCompanyDescriptionSubmitted = $item->fields['company-description-submitted']->start;
        $TSCompanyLogoSubmitted = $item->fields['company-logo-submitted']->start;
        $TSBadgeRegistrationSubmitted = $item->fields['badge-registration-submitted']->start;
        $TSLeadsEnteredinSalesforce = $item->fields['leads-entered']->values;
        $TSRMContactDueDate = $item->fields['rm-contact-due-date']->start;
        $TSArrivalDates = $item->fields['arrival-dates']->start;
        $TSCabinetNumber = $item->fields['cabinet-number']->values;
        $TSCabinetShipDate = $item->fields['cabinet-ship-return-date']->start;
        $TSCabinetReturnDate = $item->fields['cabinet-return-date']->start;
        $TSTrackingNumber = $item->fields['tracking-number']->values;
        $TSOnTradeshowBudgetList = $item->fields['on-tradeshow-budget-list']->values[0]['text'];
        $TSPassesAvailable = $item->fields['passes']->values;
        $TSBoothCaptain = $item->fields['booth-captain-sfid']->values;
        $TSEmployeesAttending = $item->fields['employees-attending-sfid']->values;
        $TSBudgetedCost = $item->fields['budgeted-cost']->values;
        $TSBoothSpaceCost = $item->fields['booth-space-cost']->values;
        $TSCarpetCost = $item->fields['carpet-cost']->values;
        $TSCleaningCost = $item->fields['cleaning-cost']->values;
        $TSElectricityCost = $item->fields['electricity-cost']->values;
        $TSExtraPassesCost = $item->fields['extra-passes-cost']->values;
        $TSLeadRetrievalCost = $item->fields['lead-retrieval-cost']->values;
        $TSMiscellaneousCharges = $item->fields['miscellaneous-charges']->values;
        $TSMaterialHandlingCost = $item->fields['material-handling-cost']->values;
        $TSFreightInCost = $item->fields['freight-in-cost']->values;
        $TSFreightOutCost = $item->fields['freight-out-cost']->values;
        $TSPartnerCost = $item->fields['partner-cost']->values;
        $TSSFID = $item->fields['sfid']->values;

        //Get Parent SFID
        $TSProjectItemID = $item->fields['strategy']->values[0]->item_id;
        if($TSProjectItemID){
            $ParentItem = PodioItem::get($TSProjectItemID);
            $ParentSFID = $ParentItem->fields['sfid']->values;
        }

        //Set Approved By SFID
        $TSApprovedByItemID = $item->fields['approved-by']->values;
        if($TSApprovedByItemID){
            if ($TSApprovedByItemID == 397587255) {
                $ApprovedBySFID = '00560000001F8pQAAS';
            }
            if ($TSApprovedByItemID == 397587252) {
                $ApprovedBySFID = '00560000004HbvJAAS';
            }
            if ($TSApprovedByItemID == 397587251) {
                $ApprovedBySFID = '00560000001vBKkAAM';
            }
            if ($TSApprovedByItemID == 397587250) {
                $ApprovedBySFID = '00560000004HEeZAAW';
            }
            if ($TSApprovedByItemID == 397587248) {
                $ApprovedBySFID = '00560000001HzGxAAK';
            }
            if ($TSApprovedByItemID == 397587247) {
                $ApprovedBySFID = '00560000001Hcff';
            }
            if ($TSApprovedByItemID == 397581011) {
                $ApprovedBySFID = '005320000057ODXAA2';
            }
        }

        //Get SFID of Freight Carrier from related Account Item
        $TSTradeShowFreightCarrierItemID = $item->fields['trade-show-freight-carrier']->values[0]->item_id;
        if($TSTradeShowFreightCarrierItemID){
            $FreightCarrierItem = PodioItem::get($TSTradeShowFreightCarrierItemID);
            $FreightCarrierSFID = $FreightCarrierItem->fields['sfid']->values;
        }


        //Tradeshow FieldsArray
        $FieldsArray = array(
            'ParentId'=>$ParentSFID,
            'Purchase_Order_Number__c'=>$TSPONumber,
            'Booth_Stand__c'=>$TSBoothStand,
            'Hotel_Name_Address__c'=>$TSHotelName,
            'Location__c'=>$TSConventionCenter,
            'StartDate'=>$TSShowStartDates,
            'EndDate'=>$TSShowEndDate,
            'DISCOUNT_DEADLINE_DATE__c'=>$TSDiscountDeadlineDate,
            'Company_Description_Submitted__c'=>$TSCompanyDescriptionSubmitted,
            'Company_Logo_Submitted__c'=>$TSCompanyLogoSubmitted,
            'Badge_Registration_Submitted__c'=>$TSBadgeRegistrationSubmitted,
            'NumberOfConvertedLeads'=>$TSLeadsEnteredinSalesforce,
            'RM_Contact_Due_Date__c'=>$TSRMContactDueDate,
            'Advanced_Warehouse_Latest_Arrival_Date__c'=>$TSArrivalDates,
            'Cabinet_Number__c'=>$TSCabinetNumber,
            'Cabinet_Ship_Date__c'=>$TSCabinetShipDate,
            'Cabinet_Return_Date__c'=>$TSCabinetReturnDate,
            'Shipment_Tracking_Number__c'=>$TSTrackingNumber,
            'On_Tradeshow_Budget_List__c'=>$TSOnTradeshowBudgetList,
            'Passes_Available__c'=>$TSPassesAvailable,
            'Booth_Captain__c'=>$TSBoothCaptain,
            'Employees_Attending__c'=>$TSEmployeesAttending,
            'BudgetedCost'=>$TSBudgetedCost,
            'Booth_Space_Cost__c'=>$TSBoothSpaceCost,
            'Carpet_Cost__c'=>$TSCarpetCost,
            'Cleaning_Cost__c'=>$TSCleaningCost,
            'Electricity_Cost__c'=>$TSElectricityCost,
            'Extra_Passes_Cost__c'=>$TSExtraPassesCost,
            'Lead_Retrieval_Cost__c'=>$TSLeadRetrievalCost,
            'Miscelleanous_Charges__c'=>$TSMiscellaneousCharges,
            'Material_Handling_Cost__c'=>$TSMaterialHandlingCost,
            'Freight_In_Cost__c'=>$TSFreightInCost,
            'Freight_Out_Cost__c'=>$TSFreightOutCost,
            'Partner_Cost__c'=>$TSPartnerCost,
            'Id'=>$TSSFID,
            'Approved_By__c'=>$ApprovedBySFID,
            'Tradeshow_Freight_Carrier__c'=>$FreightCarrierSFID,

        );

        //Object Type
        $ObjectType = "Campaign";

        //If there is a SFID, Update Item in Salesforce
        if($TSSFID){}

        //If there NOT is a SFID, Create Item in Salesforce
        if(!$TSSFID){}


    }




    //Account Items
    if($appID == 16307520){

        $AccountTitle = $item->fields['title']->values;
        $AccountOwner = $item->fields['account-owner-3']->values[0]->item_id;
        $AccountShortName = $item->fields['account-short-name']->values;
        $AccountPhone = $item->fields['phone']->values;
        $AccountWebsite = $item->fields['website']->values;
        $AccountDivision = $item->fields['division']->values[0]['text'];
        $AccountRegion = $item->fields['region']->values[0]['text'];
        $AccountBillingStreet = $item->fields['billing-address']->values->street_address;
        $AccountBillingCity = $item->fields['billing-address']->values->city;
        $AccountBillingState = $item->fields['billing-address']->values->state;
        $AccountBillingPostalCode = $item->fields['billing-address']->values->postal_code;
        $AccountBillingCountry = $item->fields['billing-address']->values->country;
        $AccountShippingStreet = $item->fields['shipping-address']->valuesstreet_address;
        $AccountShippingCity = $item->fields['shipping-address']->values->city;
        $AccountShippingState = $item->fields['shipping-address']->values->state;
        $AccountShippingPostalCode = $item->fields['shipping-address']->values->postal_code;
        $AccountShippingCountry = $item->fields['shipping-address']->values->country;
        $AccountType = $item->fields['account-type']->values[0]['text'];
        $AccountDescription = $item->fields['account-description']->values;
        $AutotaskAccountID = $item->fields['autotask-account-id']->values;
        $AccountSFID = $item->fields['sfid']->values;

        //Get Parent Account SFID
        $ParentAccountItemID = $item->fields['parent-account']->values[0]->item_id;
        if($ParentAccountItemID){
            $ParentAccountItem = PodioItem::get($ParentAccountItemID);
            $ParentSFID = $ParentAccountItem->fields['sfid']->values;
        }

        //Hardcode SF Owner ID with Member Item ID
        if ($AccountOwner == 397587255) {
            $AccountOwnerSFID = '00560000001F8pQAAS';
        }
        if ($AccountOwner == 397587252) {
            $AccountOwnerSFID = '00560000004HbvJAAS';
        }
        if ($AccountOwner == 397587251) {
            $AccountOwnerSFID = '00560000001vBKkAAM';
        }
        if ($AccountOwner == 397587250) {
            $AccountOwnerSFID = '00560000004HEeZAAW';
        }
        if ($AccountOwner == 397587248) {
            $AccountOwnerSFID = '00560000001HzGxAAK';
        }
        if ($AccountOwner == 397587247) {
            $AccountOwnerSFID = '00560000001Hcff';
        }
        if ($AccountOwner == 397581011) {
            $AccountOwnerSFID = '005320000057ODXAA2';
        }


        //Account FieldsArray
        $FieldsArray = array(
            'Name'=>$AccountTitle,
            'OwnerId'=>$AccountOwnerSFID,
            'Account_Short_Name__c'=>$AccountShortName,
            'Phone'=>$AccountPhone,
            'Website'=>$AccountWebsite,
            'Division__c'=>$AccountDivision,
            'Region__c'=>$AccountRegion,
            'BillingStreet'=>$AccountBillingStreet,
            'BillingCity'=>$AccountBillingCity,
            'BillingState'=>$AccountBillingState,
            'BillingPostalCode'=>$AccountBillingPostalCode,
            'BillingCountry'=>$AccountBillingCountry,
            'ShippingStreet'=>$AccountShippingStreet,
            'ShippingCity'=>$AccountShippingCity,
            'ShippingState'=>$AccountShippingState,
            'ShippingPostalCode'=>$AccountShippingPostalCode,
            'ShippingCountry'=>$AccountShippingCountry,
            'Type'=>$AccountType,
            'Description'=>$AccountDescription,
            'Autotask_Account_ID__c'=>$AutotaskAccountID,
            'ParentId'=>$ParentSFID,
        );

        //Object Type
        $ObjectType = "Account";


        //If there is a SFID, Update Item in Salesforce
        if($AccountSFID){}

        //If there NOT is a SFID, Create Item in Salesforce
        if(!$AccountSFID){}

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