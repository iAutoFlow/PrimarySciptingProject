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

$tableName = 'Campaign';

$resource = '/salesforce/_table/'. $tableName;

$fields = 'ParentId, Purchase_Order_Number__c, Booth_Stand__c, Hotel_Name_Address__c, Location__c, StartDate, EndDate, DISCOUNT_DEADLINE_DATE__c, Company_Description_Submitted__c, Company_Logo_Submitted__c, Badge_Registration_Submitted__c, NumberOfConvertedLeads, RM_Contact_Due_Date__c, Advanced_Warehouse_Latest_Arrival_Date__c, Cabinet_Number__c, Cabinet_Return_Date__c, Tradeshow_Freight_Carrier__c, Shipment_Tracking_Number__c, On_Tradeshow_Budget_List__c, Approved_By__c, Passes_Available__c, Booth_Captain__c, Employees_Attending__c, BudgetedCost, Booth_Space_Cost__c, Carpet_Cost__c, Cleaning_Cost__c, Electricity_Cost__c, Extra_Passes_Cost__c, Lead_Retrieval_Cost__c, Miscelleanous_Charges__c, Material_Handling_Cost__c';

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

    $Array = array();

    foreach($rows->resource as $row) {

        $Project = $row->ParentId;
        $PONumber = $row->Purchase_Order_Number__c;
        $BoothStand = $row->Booth_Stand__c;
        $HotelName = $row->Hotel_Name_Address__c;
        $HotelAddress = $row->Hotel_Name_Address__c;
        $ConventionCenter = $row->Location__c;
        $ShowStartDates = $row->StartDate;
        $ShowEndDate = $row->EndDate;
        $DiscountDeadlineDate = $row->DISCOUNT_DEADLINE_DATE__c;
        $CompanyDescriptionSubmitted = $row->Company_Description_Submitted__c;
        $CompanyLogoSubmitted = $row->Company_Logo_Submitted__c;
        $BadgeRegistrationSubmitted = $row->Badge_Registration_Submitted__c;
        $LeadsEnteredinSalesforce = $row->NumberOfConvertedLeads;
        $RMContactDueDate = $row->RM_Contact_Due_Date__c;
        $ArrivalDates = $row->Advanced_Warehouse_Latest_Arrival_Date__c;
        $CabinetNumber = $row->Cabinet_Number__c;
        $CabinetShipDate = $row->Cabinet_Ship_Date__c;
        $CabinetReturnDate = $row->Cabinet_Return_Date__c;
        $TradeShowFreightCarrier = $row->Tradeshow_Freight_Carrier__c;
        $TrackingNumber = $row->Shipment_Tracking_Number__c;
        $OnTradeshowBudgetList = $row->On_Tradeshow_Budget_List__c;
        $ApprovedBy = $row->Approved_By__c;
        $PassesAvailable = $row->Passes_Available__c;
        $BoothCaptain = $row->Booth_Captain__c;
        $EmployeesAttending = $row->Employees_Attending__c;
        $BudgetedCost = $row->BudgetedCost;
        $BoothSpaceCost = $row->Booth_Space_Cost__c;
        $CarpetCost = $row->Carpet_Cost__c;
        $CleaningCost = $row->Cleaning_Cost__c;
        $ElectricityCost = $row->Electricity_Cost__c;
        $ExtraPassesCost = $row->Extra_Passes_Cost__c;
        $LeadRetrievalCost = $row->Lead_Retrieval_Cost__c;
        $MiscellaneousCharges = $row->Miscelleanous_Charges__c;
        $MaterialHandlingCost = $row->Material_Handling_Cost__c;
        $FreightInCost = $row->Freight_In_Cost__c;
        $FreightOutCost = $row->Freight_Out_Cost__c;
        $PartnerCost = $row->Partner_Cost__c;
        $SFID = $row->Id;

        if(!$Project){continue;};



        //Filter Tradeshow Planning Items for existing Item

        $FilterTradeshows = PodioItem::filter(16307493, array('filters' => array('sfid' => $SFID)));
        $TradeshowItemID = $FilterTradeshows[0]->item_id;




        //Set Approver User ItemID
        if ($ApprovedBy == '00560000001F8pQAAS') {
            $ApprovedByItemId = (int)397587255;
        }
        if ($ApprovedBy == '00560000004HbvJAAS') {
            $ApprovedByItemId = (int)397587252;
        }
        if ($ApprovedBy == '00560000001vBKkAAM') {
            $ApprovedByItemId = (int)397587251;
        }
        if ($ApprovedBy == '00560000004HEeZAAW') {
            $ApprovedByItemId = (int)397587250;
        }
        if ($ApprovedBy == '00560000001HzGxAAK') {
            $ApprovedByItemId = (int)397587248;
        }
        if ($ApprovedBy == '00560000001Hcff') {
            $ApprovedByItemId = (int)397587247;
        }
        if ($ApprovedBy == '005320000057ODXAA2') {
            $ApprovedByItemId = (int)397581011;
        }








        //Tradeshow Planning Item Fields Array
        $fieldsArray = array(
            'fields' => array(
                'dashboard' => 450150940
            ));


        if ($ApprovedByItemId) {
            $fieldsArray['fields']['approved-by'] = $ApprovedByItemId;
        }

        if ($HotelName) {
            $fieldsArray['fields']['hotel-name'] = $HotelName;
        }

        if ($HotelAddress) {
            $fieldsArray['fields']['hotel'] = $HotelAddress;
        }

        if ($ConventionCenter) {
            $fieldsArray['fields']['convention-center'] = $ConventionCenter;
        }

        if ($Project) {
            $FilterCampaign = PodioItem::filter(16261940, array('filters' => array('sfid' => $Project)));
            $CampaignItemID = $FilterCampaign[0]->item_id;
            if ($CampaignItemID) {
                $fieldsArray['fields']['strategy'] = array((int)$CampaignItemID);
            }
        }

        if ($PONumber) {
            $fieldsArray['fields']['title'] = $PONumber;
        }

        if ($BoothStand) {
            $fieldsArray['fields']['boothstand'] = $BoothStand;
        }


        if ($ShowStartDates && $ShowEndDate) {
            $FormatStartDate = new DateTime((string)$ShowStartDates);
            $FormatEndDate = new DateTime((string)$ShowEndDate);
            $fieldsArray['fields']['show-dates'] = array('start' => $FormatStartDate->format('Y-m-d H:i:s'), 'end' => $FormatEndDate->format('Y-m-d H:i:s'));
        }

        if ($DiscountDeadlineDate) {
            $FormatDeadlineDate = new DateTime((string)$DiscountDeadlineDate);
            $fieldsArray['fields']['discount-deadline-date'] = array('start' => $FormatDeadlineDate->format('Y-m-d H:i:s'));
        }

        if ($CompanyDescriptionSubmitted) {
            $FormatDescriptionSubmitted = new DateTime((string)$CompanyDescriptionSubmitted);
            $fieldsArray['fields']['company-description-submitted'] = array('start' => $FormatDescriptionSubmitted->format('Y-m-d H:i:s'));
        }

        if ($CompanyLogoSubmitted) {
            $FormatLogoSubmittedDate = new DateTime((string)$CompanyLogoSubmitted);
            $fieldsArray['fields']['company-logo-submitted'] = array('start' => $FormatLogoSubmittedDate->format('Y-m-d H:i:s'));
        }

        if ($BadgeRegistrationSubmitted) {
            $FormatRegistrationSubmittedDate = new DateTime((string)$BadgeRegistrationSubmitted);
            $fieldsArray['fields']['badge-registration-submitted'] = array('start' => $FormatRegistrationSubmittedDate->format('Y-m-d H:i:s'));
        }

        if($TradeShowFreightCarrier){
            $FilterAccount = PodioItem::filter(16307520, array('filters' => array('sfid' => $TradeShowFreightCarrier)));
            $AccountItemID = $FilterAccount[0]->item_id;
            if($AccountItemID){
                $fieldsArray['fields']['leads-entered'] = (int)$AccountItemID;
            }
        }


        if ($LeadsEnteredinSalesforce) {
            $fieldsArray['fields']['leads-entered'] = (int)$LeadsEnteredinSalesforce;
        }

        if ($RMContactDueDate) {
            $FormatRMContactDueDate = new DateTime((string)$RMContactDueDate);
            $fieldsArray['fields']['rm-contact-due-date'] = array('start' => $FormatRMContactDueDate->format('Y-m-d H:i:s'));
        }

        if ($ArrivalDates) {
            $FormatArrivalDates = new DateTime((string)$ArrivalDates);
            $fieldsArray['fields']['arrival-dates'] = array('start' => $FormatArrivalDates->format('Y-m-d H:i:s'));
        }

        if ($CabinetNumber) {
            $fieldsArray['fields']['cabinet-number'] = $CabinetNumber;
        }

        if ($CabinetShipDate) {
            $FormatCabinetShipDate = new DateTime((string)$CabinetShipDate);
            $fieldsArray['fields']['cabinet-ship-return-date'] = array('start' => $FormatCabinetShipDate->format('Y-m-d H:i:s'));
        }
        if ($CabinetReturnDate) {
            $FormatCabinetReturnDate = new DateTime((string)$CabinetReturnDate);
            $fieldsArray['fields']['cabinet-return-date'] = array('start' => $FormatCabinetReturnDate->format('Y-m-d H:i:s'));
        }

        if ($TrackingNumber) {
            $fieldsArray['fields']['tracking-number'] = $TrackingNumber;
        }

        if ($OnTradeshowBudgetList == 1) {
            $fieldsArray['fields']['on-tradeshow-budget-list'] = "True";
        }

        if (!$OnTradeshowBudgetList) {
            $fieldsArray['fields']['on-tradeshow-budget-list'] = "False";
        }

        if ($PassesAvailable) {
            $fieldsArray['fields']['passes'] = $PassesAvailable;
        }

        if ($BoothCaptain) {
            $fieldsArray['fields']['booth-captain-sfid'] = $BoothCaptain;
        }

        if ($EmployeesAttending) {
            $fieldsArray['fields']['employees-attending-sfid'] = $EmployeesAttending;
        }

        if ($BudgetedCost) {
            $fieldsArray['fields']['budgeted-cost'] = $BudgetedCost;
        }

        if ($BoothSpaceCost) {
            $fieldsArray['fields']['booth-space-cost'] = $BoothSpaceCost;
        }

        if ($CarpetCost) {
            $fieldsArray['fields']['carpet-cost'] = $CarpetCost;
        }

        if ($CleaningCost) {
            $fieldsArray['fields']['cleaning-cost'] = $CleaningCost;
        }

        if ($ElectricityCost) {
            $fieldsArray['fields']['electricity-cost'] = $ElectricityCost;
        }

        if ($ExtraPassesCost) {
            $fieldsArray['fields']['extra-passes-cost'] = $ExtraPassesCost;
        }

        if ($LeadRetrievalCost) {
            $fieldsArray['fields']['lead-retrieval-cost'] = $LeadRetrievalCost;
        }

        if ($MiscellaneousCharges) {
            $fieldsArray['fields']['miscellaneous-charges'] = $MiscellaneousCharges;
        }

        if ($MaterialHandlingCost) {
            $fieldsArray['fields']['material-handling-cost'] = $MaterialHandlingCost;
        }

        if ($FreightInCost) {
            $fieldsArray['fields']['freight-in-cost'] = $FreightInCost;
        }

        if ($FreightOutCost) {
            $fieldsArray['fields']['freight-out-cost'] = $FreightOutCost;
        }

        if ($PartnerCost) {
            $fieldsArray['fields']['partner-cost'] = $PartnerCost;
        }

        if ($SFID) {
            $fieldsArray['fields']['sfid'] = $SFID;
        }



        //Create New Item
        if(!$TradeshowItemID){
            $CreateTradeshowPlanningItem = PodioItem::create(16307493, $fieldsArray);
        }

        //Update Existing
        else{
            $UpdateItem = PodioItem::update($TradeshowItemID, $fieldsArray);
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














