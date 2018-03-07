<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/1/2016
 * Time: 8:59 AM
 */


date_default_timezone_set('America/Denver');

class PodioSessionManager {
    private static $connection_id = 191;
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
    $spaceID = $requestParams['space_id'];

    //SpaceID's
    $BangladeshSpaceID = 4728261;
    $BulgariaSpaceID = 4593534;
    $GeorgiaSpaceID = 4579970;
    $HungarySpaceID = 4494456;
    $KazakhstanSpaceID = 4637806;
    $PakistanSpaceID = 4747955;
    $RomaniaSpaceID = 4562384;


    $SpaceArray = array($BangladeshSpaceID, $BulgariaSpaceID, $GeorgiaSpaceID, $HungarySpaceID, $PakistanSpaceID, $RomaniaSpaceID);

    foreach($SpaceArray as $spaceID) {
        $VendorAppID = "";
        $VendorSavedViewID = "";
        $ContactAppID = "";
        $ContactSavedViewID = "";
        $ContractAppID = "";
        $ContractSavedViewID = "";
        $BankDetailsAppID = "";
        $BankDetailsSavedViewID = "";
        $DealsAppID = "";
        $DealsSavedViewID = "";
        $SalesAppID = "";
        $SalesSavedViewID = "";
        $RenegotiationAppID = "";
        $RenegotiationSavedViewID = "";
        $OPAppID = "";
        $OPSavedViewID = "";
        $DigitalizationAppID = "";
        $DigitalizationSavedViewID = "";
        $AutomationsAppID = "";
        $AutomationsSavedViewID = "";
        $RestaurantMarketingAppID = "";
        $RestaurantMarketingSavedViewID = "";
        $AdSalesAppID = "";
        $AdSalesSavedViewID = "";
        $AccountCurrency = "";
        $SFStatusExID = 'sfstatus';
        $SpaceEXID = 'workspace';
        $PContractSpaceCountry = "";

        //Set Workspace App / Field ID's
        if ($spaceID == $BangladeshSpaceID) {
            //Vendor
            $PContractSpaceCountry = "Bangladesh";
            $AccountCurrency = "BDT - Bangladesh Taka";
            $VendorAppID = 16346509;
            $VendorSavedViewID = 30917089;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 16346515;
            $ContactSavedViewID = 30917094;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 16346514;
            $ContractSavedViewID = 30917104;
            $PContractCompanyNameExID = 'vendor-name';
            //Sales
            $SalesAppID = 16396175;
            $SalesSavedViewID = 30917102;
            $PSalesVendorNameExID = 'vendor-name';
            $PSalesOpportunityOwnerExID = 'opportunity-owner';
            $PSalesStageExID = 'stage';
            $PSalesCreatedDateExID = 'created-date';
            $PSalesCloseDateExID = 'closed-date';
            $PSalesNewContractExID = 'sales-contract';
            $PSalesSFIDExID = 'rol';
            $PSalesROLExID = 'rol-2';
            //Deals / Promotions
            $DealsAppID = 16346506;
            $DealsSavedViewID = 30917118;
            $PDealsVendorNameExID = 'vendor-name';
            $PDealsOpportunityOwnerExID = 'record-owner-2';
            $PDealsStageExID = 'stage';
            $PDealsROLExID = 'opportunity-lost-reason';
            $PDealsPromoTitleExID = 'promotion-title';
            $PDealsNotesExID = 'promotion-description';
            $PDealsPromoTypeExID = 'promo-type';
            $PDealsPromoDurationExID = 'promo-duration';
            $PDealsCreatedDateExID = 'created-date';
            $PDealsClosedDateExID = 'closed-date';
            $PDealsSFIDExID = 'salesforce-id';
            //Renegotiation
            $RenegotiationAppID = 16396176;
            $RenegotiationSavedViewID = 30917125;
            $PRenegotiationCompanyNameExID = 'vendor-name';
            $PRenegotiationOpportunityOwnerExID = 'opportunity-owner';
            $PRenegotiationStageExID = 'stage';
            $PRenegotiationCreatedDateExID = 'created-date';
            $PRenegotiationCloseDateExID = 'closed-date';
            $PRenegotiationNewContractExID = 'sales-contract';
            $PRenegotiationReasonLostExID = 'roi';
            $PRenegotiationSFIDExID = 'sf-id';
            //OnlinePayment
            $OPAppID = 16346510;
            $OPSavedViewID = 31409664;
            $POPCompanyNameExID = 'vendor-name';
            $POPOpportunityOwnerExID = 'opportunity-owner';
            $POPStageExID = 'stage';
            $POPCreatedDateExID = 'commission-target';
            $POPCloseDateExID = 'created-date';
            $POPContractExID = 'closed-date';
            $POPROLExID = 'rol';
            //Online Marketing / Digitalization
            $DigitalizationAppID = 16346513;
            $DigitalizationSavedViewID = 31409769;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationCloseDateExID = 'closed-date';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationROLExID = 'title';
            //Automations
            $AutomationsAppID = 16346511;
            $AutomationsSavedViewID = 31409787;
            $PAutomationCompanyNameExID = 'vendor-name';
            $PAutomationOpportunityOwnerExID = 'opportunity-owner';
            $PAutomationStageExID = 'stage';
            $PAutomationsDeviceTypeExID = 'device-type';
            $PAutomationCreatedDateExID = 'created-date';
            $PAutomationCloseDateExID = 'closed-date';
            $PAutomationROLExID = 'reason-for-refusal';
            //Restaurant Marketing
            $RestaurantMarketingAppID = 16346507;
            $RestaurantMarketingSavedViewID = 31409813;
            $PRestaurantMarketingOpportunityOwnerExID = 'record-owner';
            $PRestaurantMarketingCompanyNameExID = 'vendor-name';
            $PRestaurantMarketingStageExID = 'stage';
            $PRestaurantMarketingCreatedDateExID = 'created-date';
            $PRestaurantMarketingCloseDateExID = 'close-date';
            $PRestaurantMarketingROLExID = 'rol';
            $PRestaurantMarketingSFIDExID = 'salesforce-id';
            //Ad Sales
            $AdSalesAppID = 16346516;
            $AdSalesSavedViewID = 31409835;


        }
        if ($spaceID == $BulgariaSpaceID) {
            $AccountCurrency = "BGN - Bulgarian Lev";
            $PContractSpaceCountry = "Bulgaria";
            //Vendor
            $VendorAppID = 15888560;
            $VendorSavedViewID = 30916884;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 15888563;
            $ContactSavedViewID = 30916890;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 15888561;
            $ContractSavedViewID = 30916948;
            $PContractCompanyNameExID = 'vendor-name';
            //Bank Details
            $BankDetailsAppID = 16015918;
            $BankDetailsSavedViewID = 30916980;
            //Sales
            $SalesAppID = 15888558;
            $SalesSavedViewID = 30917005;
            //Deals
            $DealsAppID = 15888559;
            $DealsSavedViewID = 30916988;
            //Renegotiation
            $RenegotiationAppID = 15888556;
            $RenegotiationSavedViewID = 30917009;
            $PRenegotiationCompanyNameExID = 'vendor-name';
            $PRenegotiationOpportunityOwnerExID = 'opportunity-owner';
            $PRenegotiationStageExID = 'stage';
            $PRenegotiationCreatedDateExID = 'created-date';
            $PRenegotiationCloseDateExID = 'closed-date';
            $PRenegotiationNewContractExID = 'sales-contract';
            $PRenegotiationReasonLostExID = 'roi';
            $PRenegotiationSFIDExID = 'sf-id';
            //OnlinePayment
            $OPAppID = 15888562;
            $OPSavedViewID = 30916990;
            $POPCompanyNameExID = 'vendor-name';
            $POPOpportunityOwnerExID = 'opportunity-owner';
            $POPStageExID = 'stage';
            $POPCreatedDateExID = 'commission-target';
            $POPCloseDateExID = 'created-date';
            $POPContractExID = 'closed-date';
            $POPROLExID = 'rol';
            //Digitalization
            $DigitalizationAppID = 15888557;
            $DigitalizationSavedViewID = 30916989;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationCloseDateExID = 'closed-date';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationROLExID = 'title';
            //Restaurant Marketing
            $RestaurantMarketingAppID = 16273484;
            $RestaurantMarketingSavedViewID = 30917011;
            $PRestaurantMarketingOpportunityOwnerExID = 'record-owner';
            $PRestaurantMarketingCompanyNameExID = 'vendor-name';
            $PRestaurantMarketingStageExID = 'stage';
            $PRestaurantMarketingCreatedDateExID = 'created-date';
            $PRestaurantMarketingCloseDateExID = 'close-date';
            $PRestaurantMarketingROLExID = 'rol';
            $PRestaurantMarketingSFIDExID = 'salesforce-id';
            $PRestaurantMarketingVendorBackendCodeExID = 'vendor-backend-code';
            $PRestaurantMarketingTypeExID = 'marketing-item';
            //Automations
            $AutomationsAppID = 15888555;
            $AutomationsSavedViewID = 30916998;
            $PAutomationCompanyNameExID = 'vendor-name';
            $PAutomationOpportunityOwnerExID = 'opportunity-owner';
            $PAutomationStageExID = 'stage';
            $PAutomationsDeviceTypeExID = 'device-type';
            $PAutomationCreatedDateExID = 'created-date';
            $PAutomationCloseDateExID = 'closed-date';
            $PAutomationROLExID = 'reason-for-refusal';

        }
        if ($spaceID == $GeorgiaSpaceID) {
            $AccountCurrency = "GEL - Georgia Lari";
            $PContractSpaceCountry = "Georgia";
            //Vendor
            $VendorAppID = 15842653;
            $VendorSavedViewID = 30919950;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 15842651;
            $ContactSavedViewID = 31410101;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 15842654;
            $ContractSavedViewID = 30919976;
            $PContractCompanyNameExID = 'vendor-name';
            //Sales
            $SalesAppID = 15842649;
            $SalesSavedViewID = 31410107;
            $PSalesVendorNameExID = 'vendor-name';
            $PSalesOpportunityOwnerExID = 'opportunity-owner';
            $PSalesStageExID = 'stage';
            $PSalesCreatedDateExID = 'created-date';
            $PSalesCloseDateExID = 'closed-date';
            $PSalesNewContractExID = 'sales-contract';
            $PSalesSFIDExID = 'rol';
            //Renegotiation
            $RenegotiationAppID = 15842657;
            $RenegotiationSavedViewID = 29328565;
            $PRenegotiationCompanyNameExID = 'vendor-name';
            $PRenegotiationOpportunityOwnerExID = 'opportunity-owner';
            $PRenegotiationStageExID = 'stage';
            $PRenegotiationCreatedDateExID = 'created-date';
            $PRenegotiationCloseDateExID = 'closed-date';
            $PRenegotiationNewContractExID = 'sales-contract';
            $PRenegotiationReasonLostExID = 'roi';
            //Deals
            $DealsAppID = 15842650;
            $DealsSavedViewID = 31410134;
            $PDealsVendorNameExID = 'vendor-name';
            $PDealsOpportunityOwnerExID = 'record-owner-2';
            $PDealsStageExID = 'stage';
            $PDealsCreatedDateExID = 'created-date';
            $PDealsPromoTypeExID = 'promo-type';
            $PDealsDiscountValueExID = 'discount-value';
            $PDealsFixedAmountDiscountExID = 'fixed-amount-discount';
            $PDealsFreeItemTypeExID = 'free-item';
            $PDealsPromoDurationExID = 'promo-duration';
            $PDealsClosedDateExID = 'closed-date';
            $PDealsROLExID = 'opportunity-lost-reason';
            $PDealsNotesExID = 'notes';
            //OnlinePayment
            $OPAppID = 15842652;
            $OPSavedViewID = 31410165;
            $POPCompanyNameExID = 'vendor-name';
            $POPOpportunityOwnerExID = 'opportunity-owner';
            $POPStageExID = 'stage';
            $POPCreatedDateExID = 'commission-target';
            $POPCloseDateExID = 'created-date';
            $POPContractExID = 'closed-date';
            $POPROLExID = 'rol';
            //Digitalization
            $DigitalizationAppID = 15842648;
            $DigitalizationSavedViewID = 31410297;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationCloseDateExID = 'closed-date';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationROLExID = 'title';
            //Automations
            $AutomationsAppID = 15842647;
            $AutomationsSavedViewID = 31410341;
            $PAutomationCompanyNameExID = 'vendor-name';
            $PAutomationOpportunityOwnerExID = 'opportunity-owner';
            $PAutomationStageExID = 'stage';
            $PAutomationsDeviceTypeExID = 'device-type';
            $PAutomationCreatedDateExID = 'created-date';
            $PAutomationCloseDateExID = 'closed-date';
            $PAutomationROLExID = 'reason-for-refusal';

        }
        if ($spaceID == $HungarySpaceID) {
            //Vendor
            $AccountCurrency = "HUF - Hungarian Forint";
            $PContractSpaceCountry = "Hungary";
            $VendorAppID = 15554976;
            $VendorSavedViewID = 30920018;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 15554975;
            $ContactSavedViewID = 30920033;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 15554972;
            $ContractSavedViewID = 29009123;
            $PContractCompanyNameExID = 'vendor-name';
            //Deals
            $DealsAppID = 15554973;
            $DealsSavedViewID = 30898368;
            $PDealsVendorNameExID = 'vendor-name';
            $PDealsOpportunityOwnerExID = 'record-owner-2';
            $PDealsStageExID = 'stage';
            $PDealsROLExID = 'opportunity-lost-reason';
            $PDealsPromoTypeExID = 'promo-type';
            $PDealsDiscountValueExID = 'discount-value';
            $PDealsPromoDurationExID = 'promo-duration';
            $PDealsCreatedDateExID = 'created-date';
            $PDealsNotesExID = 'notes';
            //Digitalization
            $DigitalizationAppID = 15706650;
            $DigitalizationSavedViewID = 29012042;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationROLExID = 'title';
        }
        if ($spaceID == $KazakhstanSpaceID) {
            $AccountCurrency = "KZT - Kazakhstan Tenge";
            $PContractSpaceCountry = "Kazakhstan";
            $VendorAppID = 16037457;
            $ContactAppID = 16037452;
            $ContractAppID = 16037459;
            $BankDetailsAppID = 16037468;
            $DealsAppID = 16037456;
            $DigitalizationAppID = 16037455;
            $OPAppID = 16037460;
            $AutomationsAppID = 16037458;
            $VendorSavedViewID = 29909277;
            $ContactSavedViewID = 30920075;
            $ContractSavedViewID = 30920083;
            $BankDetailsSavedViewID = 30920102;
            $DealsSavedViewID = 29885815;
            $DigitalizationSavedViewID = 31411786;
            $OPSavedViewID = 31411781;
            $AutomationsSavedViewID = 31411773;
        }
        if ($spaceID == $PakistanSpaceID) {
            //Vendor
            $AccountCurrency = "PKR - Pakistani Rupee";
            $PContractSpaceCountry = "Pakistan";
            $VendorAppID = 16413624;
            $VendorSavedViewID = 30920194;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 16513617;
            $ContactSavedViewID = 30920202;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 16413616;
            $ContractSavedViewID = 30059393;
            $PContractCompanyNameExID = 'vendor-name';
            //Bank Details
            $BankDetailsAppID = 16413669;
            $BankDetailsSavedViewID = 30920216;
            $PBankDetailsVendorExID = 'contract';
            $PBankDetailsStatusExID = 'bank-details-status';
            $PBankDetailsCompanyRegistrationNOExID = 'company-registration-no';
            $PBankDetailsOfficialCompanyNameExID = 'official-company-name';
            $PBankDetailsBankNameExID = 'bank-name';
            $PBankDetailsIBANExID = 'iban';
            $PBankDetailsBICNumberExID = 'bic';
            $PBankDetailsSFIDExID = "sf-id";
            //Sales
            $SalesAppID = 16413660;
            $SalesSavedViewID = 30920224;
            $PSalesVendorNameExID = 'vendor-name';
            $PSalesOpportunityOwnerExID = 'opportunity-owner';
            $PSalesStageExID = 'stage';
            $PSalesCreatedDateExID = 'created-date';
            $PSalesCloseDateExID = 'closed-date';
            $PSalesNewContractExID = 'sales-contract';
            $PSalesROLExID = 'rol';
            $PSalesSFIDExID = 'sf-id';
            //Renegotiation
            $RenegotiationAppID = 16413661;
            $RenegotiationSavedViewID = 31411592;
            $PRenegotiationCompanyNameExID = 'vendor-name';
            $PRenegotiationOpportunityOwnerExID = 'opportunity-owner';
            $PRenegotiationStageExID = 'stage';
            $PRenegotiationCreatedDateExID = 'created-date';
            $PRenegotiationCloseDateExID = 'closed-date';
            $PRenegotiationNewContractExID = 'sales-contract';
            $PRenegotiationReasonLostExID = 'roi';
            $PRenegotiationSFIDExID = 'salesforce-id';
            //Deals
            $DealsAppID = 16413621;
            $DealsSavedViewID = 31411629;
            $PDealsVendorNameExID = 'vendor-name';
            $PDealsOpportunityOwnerExID = 'record-owner-2';
            $PDealsStageExID = 'stage';
            $PDealsPromoTitleExID = 'promotion-title';
            $PDealsPromoTypeExID = 'promo-type';
            $PDealsPromoDetailsExID = 'promotion-description';
            $PDealsPromoDurationExID = 'promo-duration';
            $PDealsCreatedDateExID = 'created-date';
            $PDealsClosedDateExID = 'closed-date';
            $PDealsROLExID = 'opportunity-lost-reason';
            $PDealsSFIDExID = 'salesforce-id';
            //OnlinePayment
            $OPAppID = 16413618;
            $OPSavedViewID = 30920242;
            $POPCompanyNameExID = 'vendor-name';
            $POPOpportunityOwnerExID = 'opportunity-owner';
            $POPStageExID = 'stage';
            $POPCreatedDateExID = 'commission-target';
            $POPCloseDateExID = 'created-date';
            $POPContractExID = 'closed-date';
            $POPROLExID = 'rol';
            $POPSFIDExID = 'salesforce-id';
            //Digitalization
            $DigitalizationAppID = 16413619;
            $DigitalizationSavedViewID = 31411666;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationCloseDateExID = 'closed-date';
            $PDigitalizationROLExID = 'title';
            $PDigitalizationSFIDExID = 'salesforce-id';
            //Automations
            $AutomationsAppID = 16413615;
            $AutomationsSavedViewID = 31411670;
            $PAutomationCompanyNameExID = 'vendor-name';
            $PAutomationOpportunityOwnerExID = 'opportunity-owner';
            $PAutomationStageExID = 'stage';
            $PAutomationsDeviceTypeExID = 'device-type';
            $PAutomationCreatedDateExID = 'created-date';
            $PAutomationCloseDateExID = 'closed-date';
            $PAutomationROLExID = 'reason-for-refusal';
            $PAutomationSFIDExID = 'salesforce-id';

        }
        if ($spaceID == $RomaniaSpaceID) {
            $AccountCurrency = "RON - Romanian Leu";
            $PContractSpaceCountry = "Romania";
            //Vendor
            $VendorAppID = 15758358;
            $VendorSavedViewID = 30920291;
            $PVendorNameExID = 'title';
            //Contact
            $ContactAppID = 15785360;
            $ContactSavedViewID = 30920347;
            $PContactNameExID = 'title';
            //Contract
            $ContractAppID = 15785359;
            $ContractSavedViewID = 30920350;
            $PContractCompanyNameExID = 'vendor-name';
            //Sales
            $SalesAppID = 15785815;
            $SalesSavedViewID = 30920303;
            $PSalesVendorNameExID = 'vendor-name';
            $PSalesOpportunityOwnerExID = 'opportunity-owner';
            $PSalesStageExID = 'stage';
            $PSalesCreatedDateExID = 'created-date';
            $PSalesCloseDateExID = 'closed-date';
            $PSalesNewContractExID = 'sales-contract';
            $PSalesSFIDExID = 'rol';
            //Renegotiation
            $RenegotiationAppID = 15791979;
            $RenegotiationSavedViewID = 31411843;
            $PRenegotiationCompanyNameExID = 'vendor-name';
            $PRenegotiationOpportunityOwnerExID = 'opportunity-owner';
            $PRenegotiationStageExID = 'stage';
            $PRenegotiationCreatedDateExID = 'created-date';
            $PRenegotiationCloseDateExID = 'closed-date';
            $PRenegotiationNewContractExID = 'sales-contract';
            $PRenegotiationReasonLostExID = 'roi';
            //Deals
            $DealsAppID = 15785357;
            $DealsSavedViewID = 30920328;
            $PDealsVendorNameExID = 'vendor-name';
            $PDealsOpportunityOwnerExID = 'record-owner-2';
            $PDealsStageExID = 'stage';
            $PDealsCreatedDateExID = 'created-date';
            $PDealsPromoTypeExID = 'promo-type';
            $PDealsDiscountValueExID = 'discount-value';
            $PDealsPromoDurationExID = 'promo-duration';
            $PDealsClosedDateExID = 'closed-date';
            $PDealsROLExID = 'opportunity-lost-reason';
            $PDealsNotesExID = 'notes';
            //OnlinePayment
            $OPAppID = 15792855;
            $OPSavedViewID = 30920308;
            $POPCompanyNameExID = 'vendor-name';
            $POPOpportunityOwnerExID = 'opportunity-owner';
            $POPStageExID = 'stage';
            $POPCreatedDateExID = 'commission-target';
            $POPCloseDateExID = 'created-date';
            $POPContractExID = 'closed-date';
            $POPContractExID = 'op-contract';
            $POPROLExID = 'rol';
            //Digitalization
            $DigitalizationAppID = 15785356;
            $DigitalizationSavedViewID = 31411822;
            $PDigitalizationCompanyNameExID = 'vendor-name';
            $PDigitalizationOpportunityOwnerExID = 'record-owner-3';
            $PDigitalizationStageExID = 'stage';
            $PDigitalizationCreatedDateExID = 'created-date';
            $PDigitalizationCloseDateExID = 'closed-date';
            $PDigitalizationItemTypeExID = 'item-type';
            $PDigitalizationItemURLExID = 'item-url';
            $PDigitalizationROLExID = 'title';
            //Automations
            $AutomationsAppID = 15792076;
            $AutomationsSavedViewID = 30920334;
            $PAutomationCompanyNameExID = 'vendor-name';
            $PAutomationOpportunityOwnerExID = 'opportunity-owner';
            $PAutomationStageExID = 'stage';
            $PAutomationsDeviceTypeExID = 'device-type';
            $PAutomationCreatedDateExID = 'created-date';
            $PAutomationCloseDateExID = 'closed-date';
            $PAutomationROLExID = 'reason-for-refusal';

        }


        //Create Field Arrays for New Podio Items
        $OPPFields = array('fields' => array());
        $PromotionItemFieldsArray = array('fields' => array());
        $BankDetailsItemFieldsArray = array('fields' => array());

        //Online Payment
        if($spaceID == $BangladeshSpaceID || $spaceID == $BulgariaSpaceID || $spaceID == $PakistanSpaceID || $spaceID == $RomaniaSpaceID || $spaceID == $GeorgiaSpaceID){
            $OPItems = PodioItem::filter_by_view($OPAppID, $OPSavedViewID, array('limit'=>500));
            foreach($OPItems as $renegotiation){
                $OPPFields = array('fields' => array());
                $OPItem = '';
                $POPItemUniqueID = '';
                $POPItemID = '';
                $POPVendorName = '';
                $POPVendorItemID = '';
                $OPOpportunityOwner = '';
                $OPStage = '';
                $OPCreatedDate = '';
                $OPClosedDate = '';
                $OPContractItemID = '';
                $OPROL = '';
                $OPSFID = '';
                $OpportunityRecordType = 'Online Payment';
                $OPType = "New Business";
                $OPCreatedDateFormatted = "";
                $OPClosedDateFormatted = "";

                $POPItemID = $renegotiation->item_id;
                $OPItem = PodioItem::get($POPItemID);
                $POPItemUniqueID = $OPItem->app_item_id_formatted;
                if($POPCompanyNameExID) {
                    $POPVendorItemID = $OPItem->fields[$POPCompanyNameExID]->values[0]->item_id;
                    $POPVendorName = $OPItem->fields[$POPCompanyNameExID]->values[0]->title;
                }
                if($POPOpportunityOwnerExID){$OPOpportunityOwner = $OPItem->fields[$POPOpportunityOwnerExID]->values[0]->name;}
                if($POPStageExID){$OPStage = $OPItem->fields[$POPStageExID]->values[0]['text'];}
                if($POPCreatedDateExID){$OPCreatedDate = $OPItem->fields[$POPCreatedDateExID]->start;}
                if($POPCloseDateExID){$OPClosedDate = $OPItem->fields[$POPCloseDateExID]->start;}
                if($POPContractExID){$OPContractItemID = $OPItem->fields[$POPContractExID]->values[0]->item_id;}
                if($POPROLExID){$OPROL = $OPItem->fields[$POPROLExID]->values;}
                if($POPSFIDExID){$OPSFID = $OPItem->fields[$POPSFIDExID]->values;}
                $POPTags = $OPItem->tags;


                //Format Dates
                if ($OPCreatedDate) {
                    $OPCreatedDateFormatted = date_format($OPCreatedDate, "m-d-Y");
                    $OPCreatedDateFormatted = str_replace("-", "/", $OPCreatedDateFormatted);
                }
                if ($OPClosedDate) {
                    $OPClosedDateFormatted = date_format($OPClosedDate, "m-d-Y");
                    $OPClosedDateFormatted = str_replace("-", "/", $OPClosedDateFormatted);
                }

                //Get / Set / Format Item Comments
                unset($OPPodioComment);
                unset($OPItemCommentsArray);
                unset($OPTagsArray);
                $OPPodioComment = "";
                $OPTagsArray = "";
                $OPItemCommentsArray = "";
                //Get / Set / Format Item Tags
                foreach ($POPTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $OPTagsArray .= $TagValue;
                    }
                }
                //Get / Set / Format Item Comments
                $OPComments = PodioComment::get_for('item', (int)$POPItemID);
                foreach ($OPComments as $comment) {
                    unset($CommentCreatedOn);
                    unset($CommentValue);
                    unset($CommentCreatedBy);
                    unset($CommentID);
                    unset($OPCommentString);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                    $OPCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $OPItemCommentsArray .= $OPCommentString;
                }
                //Combine Tags, Comments And Notes into Single Notes Value
                if ($OPItemCommentsArray) {
                    $OPPodioComment .= "**Podio Item Comments-**\n $OPItemCommentsArray\n";
                }
                if ($OPTagsArray) {
                    $OPPodioComment .= "**Podio Tags:** $OPTagsArray\n";
                }

                //Get File Info
                unset($OPFileIDsArray);
                unset($OPItemFiles);
                unset($OPFileLinksArray);
                unset($OPFileIDsArray);
                unset($NewOnlinePaymentItemID);
                $OPFileIDsArray = array();
                $OPFileLinksArray = "";
                $OPItemFiles = $OPItem->files;
                foreach ($OPItemFiles as $file) {
                    unset($OrigFileID);
                    unset($CopiedFile);
                    $OrigFileID = $file->file_id;
                    if ($OrigFileID) {
                        $CopiedFile = PodioFile::copy($OrigFileID);
                        $NewFileID = $CopiedFile->file_id;
                        $NewFile = PodioFile::get($NewFileID);
                        $NewFileName = $NewFile->name;
                        $NewFileType = $NewFile->mimtype;
                        $NewFileSize = $NewFile->size;
                        $NewFileLink = $NewFile->link;
                        $NewFileContents = $NewFile->get_raw();
                        if ($NewFileLink) {
                            $OPFileLinksArray .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            array_push($OPFileIDsArray, $NewFileID);
                        }
                    }
                }

                //Create SFOpportunity Item for OnlinePayment Podio Item
                if($POPItemID){
                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$POPItemID;
                    $OPPFields['fields']['online-payment-item'] = (int)$POPItemID;

                }
                if($POPItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$POPItemUniqueID;}
                if($OpportunityRecordType){$OPPFields['fields']['title'] = (string)$OpportunityRecordType;}
                if($OPOpportunityOwner){$OPPFields['fields']['account-owne'] = (string)$OPOpportunityOwner;}
                if($POPVendorName){
                    $OPPFields['fields']['text'] = (string)$POPVendorName;
                    $OPPFields['fields']['opportunity-nam'] = (string)$POPVendorName;
                }
                if($OPType){$OPPFields['fields']['type'] = (string)$OPType;}
                if($OPStage){$OPPFields['fields']['text-2'] = (string)$OPStage;}
                if($OPCreatedDateFormatted){$OPPFields['fields']['created-date'] = (string)$OPCreatedDateFormatted;}
                if($OPClosedDateFormatted){$OPPFields['fields']['close'] = (string)$OPClosedDateFormatted;}
                if($OPROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$OPROL;}
                if($POPVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$POPVendorItemID;}
                if($OPSFID){$OPPFields['fields']['sfid'] = (string)$OPSFID;}
                if($OPPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$OPPodioComment;}
                if($OPFileLinksArray){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$OPFileLinksArray;}
                $OPPFields['fields']['account-currency'] = $AccountCurrency;
                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
                $OPPFields['fields'][$SFStatusExID] = "2B";

                //Create Opportunity Item for OnlinePayment Apps
                $NewOnlinePaymentOpportunityItem = PodioItem::create(17330425, $OPPFields);
                $NewOnlinePaymentOpportunityItemID = $NewOnlinePaymentOpportunityItem->item_id;
                if($OPFileIDsArray) {
                    foreach($OPFileIDsArray as $file_id) {
                        $AttachOnlinePaymentOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewOnlinePaymentOpportunityItemID));
                    }
                }
            }
        }

        //Digitalization (Online Marketing)
        if($spaceID !== $KazakhstanSpaceID){
            $DigitalizationItems = PodioItem::filter_by_view($DigitalizationAppID, $DigitalizationSavedViewID, array('limit'=>500));
            foreach($DigitalizationItems as $renegotiation){
                $OPPFields = array('fields' => array());
                $DigitalizationItem = '';
                $PDigitalizationItemUniqueID = '';
                $PDigitalizationItemID = '';
                $PDigitalizationVendorName = '';
                $PDigitalizationVendorItemID = '';
                $DigitalizationOpportunityOwner = '';
                $DigitalizationStage = '';
                $DigitalizationCreatedDate = '';
                $DigitalizationClosedDate = '';
                $DigitalizationContractItemID = '';
                $DigitalizationROL = '';
                $DigitalizationSFID = '';
                $OpportunityRecordType = 'Online Marketing';
                $DigitalizationCreatedDateFormatted = "";
                $DigitalizationClosedDateFormatted = "";


                $PDigitalizationItemID = $renegotiation->item_id;
                $DigitalizationItem = PodioItem::get($PDigitalizationItemID);
                $PDigitalizationItemUniqueID = $DigitalizationItem->app_item_id_formatted;
                if($PDigitalizationCompanyNameExID) {
                    $PDigitalizationVendorItemID = $DigitalizationItem->fields[$PDigitalizationCompanyNameExID]->values[0]->item_id;
                    $PDigitalizationVendorName = $DigitalizationItem->fields[$PDigitalizationCompanyNameExID]->values[0]->title;
                }
                if($PDigitalizationOpportunityOwnerExID){$DigitalizationOpportunityOwner = $DigitalizationItem->fields[$PDigitalizationOpportunityOwnerExID]->values[0]->name;}
                if($PDigitalizationStageExID){$DigitalizationStage = $DigitalizationItem->fields[$PDigitalizationStageExID]->values[0]['text'];}
                if($PDigitalizationCreatedDateExID){$DigitalizationCreatedDate = $DigitalizationItem->fields[$PDigitalizationCreatedDateExID]->start;}
                if($PDigitalizationCloseDateExID){$DigitalizationClosedDate = $DigitalizationItem->fields[$PDigitalizationCloseDateExID]->start;}
                if($PDigitalizationROLExID){$DigitalizationROL = $DigitalizationItem->fields[$PDigitalizationROLExID]->values;}
                if($PDigitalizationSFIDExID){$DigitalizationSFID = $DigitalizationItem->fields[$PDigitalizationSFIDExID]->values;}
                $PDigitalizationTags = $DigitalizationItem->tags;

                //Format Dates
                if ($DigitalizationCreatedDate) {
                    $DigitalizationCreatedDateFormatted = date_format($DigitalizationCreatedDate, "m-d-Y");
                    $DigitalizationCreatedDateFormatted = str_replace("-", "/", $DigitalizationCreatedDateFormatted);
                }
                if ($DigitalizationClosedDate) {
                    $DigitalizationClosedDateFormatted = date_format($DigitalizationClosedDate, "m-d-Y");
                    $DigitalizationClosedDateFormatted = str_replace("-", "/", $DigitalizationClosedDateFormatted);
                }

                //Get / Set / Format Item Comments
                unset($DigitalizationPodioComment);
                unset($DigitalizationItemCommentsArray);
                unset($DigitalizationTagsArray);
                $DigitalizationPodioComment = "";
                $DigitalizationTagsArray = "";
                $DigitalizationItemCommentsArray = "";
                //Get / Set / Format Item Tags
                foreach ($PDigitalizationTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $DigitalizationTagsArray .= $TagValue;
                    }
                }
                //Get / Set / Format Item Comments
                $DigitalizationComments = PodioComment::get_for('item', (int)$PDigitalizationItemID);
                foreach ($DigitalizationComments as $comment) {
                    unset($CommentCreatedOn);
                    unset($CommentValue);
                    unset($CommentCreatedBy);
                    unset($CommentID);
                    unset($DigitalizationCommentString);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                    $DigitalizationCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $DigitalizationItemCommentsArray .= $DigitalizationCommentString;
                }
                //Combine Tags, Comments And Notes into Single Notes Value
                if ($DigitalizationItemCommentsArray) {
                    $DigitalizationPodioComment .= "**Podio Item Comments-**\n $DigitalizationItemCommentsArray\n";
                }
                if ($DigitalizationTagsArray) {
                    $DigitalizationPodioComment .= "**Podio Tags:** $DigitalizationTagsArray\n";
                }

                //Get File Info
                unset($DigitalizationFileIDsArray);
                unset($DigitalizationItemFiles);
                unset($DigitalizationFileLinksArray);
                unset($DigitalizationFileIDsArray);
                unset($NewDigitalizationItemID);
                $DigitalizationFileIDsArray = array();
                $DigitalizationFileLinksArray = "";
                $DigitalizationItemFiles = $DigitalizationItem->files;
                foreach ($DigitalizationItemFiles as $file) {
                    unset($OrigFileID);
                    unset($CopiedFile);
                    $OrigFileID = $file->file_id;
                    if ($OrigFileID) {
                        $CopiedFile = PodioFile::copy($OrigFileID);
                        $NewFileID = $CopiedFile->file_id;
                        $NewFile = PodioFile::get($NewFileID);
                        $NewFileName = $NewFile->name;
                        $NewFileType = $NewFile->mimtype;
                        $NewFileSize = $NewFile->size;
                        $NewFileLink = $NewFile->link;
                        $NewFileContents = $NewFile->get_raw();
                        if ($NewFileLink) {
                            $DigitalizationFileLinksArray .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            array_push($DigitalizationFileIDsArray, $NewFileID);
                        }
                    }
                }

                //Create SFOpportunity Item for Digitalization Podio Item
                if($PDigitalizationItemID){
                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$PDigitalizationItemID;
                    $OPPFields['fields']['online-marketing-digitalization-item'] = (int)$PDigitalizationItemID;
                }
                if($PDigitalizationItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$PDigitalizationItemUniqueID;}
                if($OpportunityRecordType){$OPPFields['fields']['title'] = (string)$OpportunityRecordType;}
                if($DigitalizationOpportunityOwner){$OPPFields['fields']['account-owne'] = (string)$DigitalizationOpportunityOwner;}
                if($PDigitalizationVendorName){
                    $OPPFields['fields']['text'] = (string)$PDigitalizationVendorName;
                    $OPPFields['fields']['opportunity-nam'] = (string)$PDigitalizationVendorName;
                }
                if($DigitalizationStage){$OPPFields['fields']['text-2'] = (string)$DigitalizationStage;}
                if($DigitalizationCreatedDateFormatted){$OPPFields['fields']['created-date'] = (string)$DigitalizationCreatedDateFormatted;}
                if($DigitalizationClosedDateFormatted){$OPPFields['fields']['close'] = (string)$DigitalizationClosedDateFormatted;}
                if($DigitalizationROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$DigitalizationROL;}
                if($PDigitalizationVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$PDigitalizationVendorItemID;}
                if($DigitalizationSFID){$OPPFields['fields']['sfid'] = (string)$DigitalizationSFID;}
                if($DigitalizationPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$DigitalizationPodioComment;}
                if($DigitalizationFileLinksArray){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$DigitalizationFileLinksArray;}
                $OPPFields['fields']['account-currency'] = $AccountCurrency;
                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
                $OPPFields['fields'][$SFStatusExID] = "2B";

                //Create Opportunity Item for Digitalization Apps
                $NewDigitalizationOpportunityItem = PodioItem::create(17330425, $OPPFields);
                $NewDigitalizationOpportunityItemID = $NewDigitalizationOpportunityItem->item_id;
                if($DigitalizationFileIDsArray) {
                    foreach($DigitalizationFileIDsArray as $file_id) {
                        $AttachDigitalizationOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewDigitalizationOpportunityItemID));
                    }
                }
            }
        }

        //Restaurant Marketing
        if($spaceID == $BangladeshSpaceID || $spaceID == $BulgariaSpaceID){
            $RestaurantMarketingItems = PodioItem::filter_by_view($RestaurantMarketingAppID, $RestaurantMarketingSavedViewID, array('limit'=>500));
            foreach($RestaurantMarketingItems as $renegotiation){
                $OPPFields = array('fields' => array());
                $RestaurantMarketingItem = '';
                $PRestaurantMarketingItemUniqueID = '';
                $PRestaurantMarketingItemID = '';
                $PRestaurantMarketingVendorName = '';
                $PRestaurantMarketingVendorItemID = '';
                $RestaurantMarketingOpportunityOwner = '';
                $RestaurantMarketingStage = '';
                $RestaurantMarketingCreatedDate = '';
                $RestaurantMarketingClosedDate = '';
                $RestaurantMarketingROL = '';
                $RestaurantMarketingSFID = '';
                $OpportunityRecordType = 'Online Marketing';
                $RestaurantMarketingCreatedDateFormatted = "";
                $RestaurantMarketingClosedDateFormatted = "";
                $RestaurantMarketingType = '';

                $PRestaurantMarketingItemID = $renegotiation->item_id;
                $RestaurantMarketingItem = PodioItem::get($PRestaurantMarketingItemID);
                $PRestaurantMarketingItemUniqueID = $RestaurantMarketingItem->app_item_id_formatted;
                if($PRestaurantMarketingCompanyNameExID) {
                    $PRestaurantMarketingVendorItemID = $RestaurantMarketingItem->fields[$PRestaurantMarketingCompanyNameExID]->values[0]->item_id;
                    $PRestaurantMarketingVendorName = $RestaurantMarketingItem->fields[$PRestaurantMarketingCompanyNameExID]->values[0]->title;
                }
                if($PRestaurantMarketingOpportunityOwnerExID){$RestaurantMarketingOpportunityOwner = $RestaurantMarketingItem->fields[$PRestaurantMarketingOpportunityOwnerExID]->values[0]->name;}
                if($PRestaurantMarketingStageExID){$RestaurantMarketingStage = $RestaurantMarketingItem->fields[$PRestaurantMarketingStageExID]->values[0]['text'];}
                if($PRestaurantMarketingCreatedDateExID){$RestaurantMarketingCreatedDate = $RestaurantMarketingItem->fields[$PRestaurantMarketingCreatedDateExID]->start;}
                if($PRestaurantMarketingCloseDateExID){$RestaurantMarketingClosedDate = $RestaurantMarketingItem->fields[$PRestaurantMarketingCloseDateExID]->start;}
                if($PRestaurantMarketingROLExID){$RestaurantMarketingROL = $RestaurantMarketingItem->fields[$PRestaurantMarketingROLExID]->values;}
                if($PRestaurantMarketingSFIDExID){$RestaurantMarketingSFID = $RestaurantMarketingItem->fields[$PRestaurantMarketingSFIDExID]->values;}
                if($PRestaurantMarketingTypeExID){$RestaurantMarketingType = $RestaurantMarketingItem->fields[$PRestaurantMarketingTypeExID]->values[0]['text'];}
                $PRestaurantMarketingTags = $RestaurantMarketingItem->tags;

                //Format Dates
                if ($RestaurantMarketingCreatedDate) {
                    $RestaurantMarketingCreatedDateFormatted = date_format($RestaurantMarketingCreatedDate, "m-d-Y");
                    $RestaurantMarketingCreatedDateFormatted = str_replace("-", "/", $RestaurantMarketingCreatedDateFormatted);
                }
                if ($RestaurantMarketingClosedDate) {
                    $RestaurantMarketingClosedDateFormatted = date_format($RestaurantMarketingClosedDate, "m-d-Y");
                    $RestaurantMarketingClosedDateFormatted = str_replace("-", "/", $RestaurantMarketingClosedDateFormatted);
                }

                //Get / Set / Format Item Comments
                unset($RestaurantMarketingPodioComment);
                unset($RestaurantMarketingItemCommentsArray);
                unset($RestaurantMarketingTagsArray);
                $RestaurantMarketingPodioComment = "";
                $RestaurantMarketingTagsArray = "";
                $RestaurantMarketingItemCommentsArray = "";
                //Get / Set / Format Item Tags
                foreach ($PRestaurantMarketingTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $RestaurantMarketingTagsArray .= $TagValue;
                    }
                }
                //Get / Set / Format Item Comments
                $RestaurantMarketingComments = PodioComment::get_for('item', (int)$PRestaurantMarketingItemID);
                foreach ($RestaurantMarketingComments as $comment) {
                    unset($CommentCreatedOn);
                    unset($CommentValue);
                    unset($CommentCreatedBy);
                    unset($CommentID);
                    unset($RestaurantMarketingCommentString);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                    $RestaurantMarketingCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $RestaurantMarketingItemCommentsArray .= $RestaurantMarketingCommentString;
                }
                //Combine Tags, Comments And Notes into Single Notes Value
                if ($RestaurantMarketingItemCommentsArray) {
                    $RestaurantMarketingPodioComment .= "**Podio Item Comments-**\n $RestaurantMarketingItemCommentsArray\n";
                }
                if ($RestaurantMarketingTagsArray) {
                    $RestaurantMarketingPodioComment .= "**Podio Tags:** $RestaurantMarketingTagsArray\n";
                }

                //Get File Info
                unset($RestaurantMarketingFileIDsArray);
                unset($RestaurantMarketingItemFiles);
                unset($RestaurantMarketingFileLinksArray);
                unset($RestaurantMarketingFileIDsArray);
                unset($NewRestaurantMarketingItemID);
                $RestaurantMarketingFileIDsArray = array();
                $RestaurantMarketingFileLinksArray = "";
                $RestaurantMarketingItemFiles = $RestaurantMarketingItem->files;
                foreach ($RestaurantMarketingItemFiles as $file) {
                    unset($OrigFileID);
                    unset($CopiedFile);
                    $OrigFileID = $file->file_id;
                    if ($OrigFileID) {
                        $CopiedFile = PodioFile::copy($OrigFileID);
                        $NewFileID = $CopiedFile->file_id;
                        $NewFile = PodioFile::get($NewFileID);
                        $NewFileName = $NewFile->name;
                        $NewFileType = $NewFile->mimtype;
                        $NewFileSize = $NewFile->size;
                        $NewFileLink = $NewFile->link;
                        $NewFileContents = $NewFile->get_raw();
                        if ($NewFileLink) {
                            $RestaurantMarketingFileLinksArray .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            array_push($RestaurantMarketingFileIDsArray, $NewFileID);
                        }
                    }
                }

                //Create SFOpportunity Item for RestaurantMarketing Podio Item
                if($PRestaurantMarketingItemID){
                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$PRestaurantMarketingItemID;
                    $OPPFields['fields']['restaurant-marketing-item'] = (int)$PRestaurantMarketingItemID;
                }
                if($PRestaurantMarketingItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$PRestaurantMarketingItemUniqueID;}
                if($OpportunityRecordType){$OPPFields['fields']['title'] = (string)$OpportunityRecordType;}
                if($RestaurantMarketingOpportunityOwner){$OPPFields['fields']['account-owne'] = (string)$RestaurantMarketingOpportunityOwner;}
                if($PRestaurantMarketingVendorName){
                    $OPPFields['fields']['text'] = (string)$PRestaurantMarketingVendorName;
                    $OPPFields['fields']['opportunity-nam'] = (string)$PRestaurantMarketingVendorName;
                }
                if($RestaurantMarketingStage){$OPPFields['fields']['text-2'] = (string)$RestaurantMarketingStage;}
                if($RestaurantMarketingCreatedDateFormatted){$OPPFields['fields']['created-date'] = (string)$RestaurantMarketingCreatedDateFormatted;}
                if($RestaurantMarketingClosedDateFormatted){$OPPFields['fields']['close'] = (string)$RestaurantMarketingClosedDateFormatted;}
                if($RestaurantMarketingROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$RestaurantMarketingROL;}
                if($PRestaurantMarketingVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$PRestaurantMarketingVendorItemID;}
                if($RestaurantMarketingSFID){$OPPFields['fields']['sfid'] = (string)$RestaurantMarketingSFID;}
                if($RestaurantMarketingPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$RestaurantMarketingPodioComment;}
                if($RestaurantMarketingFileLinksArray){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$RestaurantMarketingFileLinksArray;}
                if($RestaurantMarketingType){$OPPFields['fields']['text-3'] = (string)$RestaurantMarketingType;}
                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
                $OPPFields['fields'][$SFStatusExID] = "2B";


                //Create Opportunity Item for RestaurantMarketing Apps
                $NewRestaurantMarketingOpportunityItem = PodioItem::create(17330425, $OPPFields);
                $NewRestaurantMarketingOpportunityItemID = $NewRestaurantMarketingOpportunityItem->item_id;
                if($RestaurantMarketingFileIDsArray) {
                    foreach($RestaurantMarketingFileIDsArray as $file_id) {
                        $AttachRestaurantMarketingOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewRestaurantMarketingOpportunityItemID));
                    }
                }
            }
        }

        //Automation
        if($spaceID == $BangladeshSpaceID || $spaceID == $BulgariaSpaceID || $spaceID == $PakistanSpaceID || $spaceID == $RomaniaSpaceID || $spaceID == $GeorgiaSpaceID){
            $AutomationItems = PodioItem::filter_by_view($AutomationsAppID, $AutomationsSavedViewID, array('limit'=>500));
            foreach($AutomationItems as $renegotiation){
                $OPPFields = array('fields' => array());
                $AutomationItem = '';
                $PAutomationItemUniqueID = '';
                $PAutomationItemID = '';
                $PAutomationVendorName = '';
                $PAutomationVendorItemID = '';
                $AutomationOpportunityOwner = '';
                $AutomationStage = '';
                $AutomationCreatedDate = '';
                $AutomationClosedDate = '';
                $AutomationContractItemID = '';
                $AutomationROL = '';
                $AutomationSFID = '';
                $OpportunityRecordType = 'Automation';
                $AutomationDeviceType = "";
                $AutomationCreatedDateFormatted = "";
                $AutomationClosedDateFormatted = "";
                $PAutomationItemID = $renegotiation->item_id;
                $AutomationItem = PodioItem::get($PAutomationItemID);
                $PAutomationItemUniqueID = $AutomationItem->app_item_id_formatted;
                if($PAutomationCompanyNameExID) {
                    $PAutomationVendorItemID = $AutomationItem->fields[$PAutomationCompanyNameExID]->values[0]->item_id;
                    $PAutomationVendorName = $AutomationItem->fields[$PAutomationCompanyNameExID]->values[0]->title;
                }
                if($PAutomationOpportunityOwnerExID){$AutomationOpportunityOwner = $AutomationItem->fields[$PAutomationOpportunityOwnerExID]->values[0]->name;}
                if($PAutomationStageExID){$AutomationStage = $AutomationItem->fields[$PAutomationStageExID]->values[0]['text'];}
                if($PAutomationCreatedDateExID){$AutomationCreatedDate = $AutomationItem->fields[$PAutomationCreatedDateExID]->start;}
                if($PAutomationCloseDateExID){$AutomationClosedDate = $AutomationItem->fields[$PAutomationCloseDateExID]->start;}
                if($PAutomationROLExID){$AutomationROL = $AutomationItem->fields[$PAutomationROLExID]->values;}
                if($PAutomationSFIDExID){$AutomationSFID = $AutomationItem->fields[$PAutomationSFIDExID]->values;}
                if($PAutomationsDeviceTypeExID){$AutomationDeviceType = $AutomationItem->fields[$PAutomationsDeviceTypeExID]->values[0]['text'];}
                $PAutomationTags = $AutomationItem->tags;

                //Format Dates
                if ($AutomationCreatedDate) {
                    $AutomationCreatedDateFormatted = date_format($AutomationCreatedDate, "m-d-Y");
                    $AutomationCreatedDateFormatted = str_replace("-", "/", $AutomationCreatedDateFormatted);
                }
                if ($AutomationClosedDate) {
                    $AutomationClosedDateFormatted = date_format($AutomationClosedDate, "m-d-Y");
                    $AutomationClosedDateFormatted = str_replace("-", "/", $AutomationClosedDateFormatted);
                }

                //Get / Set / Format Item Comments
                unset($AutomationPodioComment);
                unset($AutomationItemCommentsArray);
                unset($AutomationTagsArray);
                $AutomationPodioComment = "";
                $AutomationTagsArray = "";
                $AutomationItemCommentsArray = "";
                //Get / Set / Format Item Tags
                foreach ($PAutomationTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $AutomationTagsArray .= $TagValue;
                    }
                }
                //Get / Set / Format Item Comments
                $AutomationComments = PodioComment::get_for('item', (int)$PAutomationItemID);
                foreach ($AutomationComments as $comment) {
                    unset($CommentCreatedOn);
                    unset($CommentValue);
                    unset($CommentCreatedBy);
                    unset($CommentID);
                    unset($AutomationCommentString);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $AutomationCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $AutomationItemCommentsArray .= $AutomationCommentString;
                }
                //Combine Tags, Comments And Notes into Single Notes Value
                if ($AutomationItemCommentsArray) {
                    $AutomationPodioComment .= "**Podio Item Comments-**\n $AutomationItemCommentsArray\n";
                }
                if ($AutomationTagsArray) {
                    $AutomationPodioComment .= "**Podio Tags:** $AutomationTagsArray\n";
                }

                //Get File Info
                unset($AutomationFileIDsArray);
                unset($AutomationItemFiles);
                unset($AutomationFileLinksArray);
                unset($AutomationFileIDsArray);
                unset($NewAutomationItemID);
                $AutomationFileIDsArray = array();
                $AutomationFileLinksArray = "";
                $AutomationItemFiles = $AutomationItem->files;
                foreach ($AutomationItemFiles as $file) {
                    unset($OrigFileID);
                    unset($CopiedFile);
                    $OrigFileID = $file->file_id;
                    if ($OrigFileID) {
                        $CopiedFile = PodioFile::copy($OrigFileID);
                        $NewFileID = $CopiedFile->file_id;
                        $NewFile = PodioFile::get($NewFileID);
                        $NewFileName = $NewFile->name;
                        $NewFileType = $NewFile->mimtype;
                        $NewFileSize = $NewFile->size;
                        $NewFileLink = $NewFile->link;
                        $NewFileContents = $NewFile->get_raw();
                        if ($NewFileLink) {
                            $AutomationFileLinksArray .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            array_push($AutomationFileIDsArray, $NewFileID);
                        }
                    }
                }

                //Create SFOpportunity Item for Automation Podio Item
                if($PAutomationItemID){
                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$PAutomationItemID;
                    $OPPFields['fields']['automation-item'] = (int)$PAutomationItemID;
                }
                if($PAutomationItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$PAutomationItemUniqueID;}
                if($OpportunityRecordType){$OPPFields['fields']['title'] = (string)$OpportunityRecordType;}
                if($AutomationOpportunityOwner){$OPPFields['fields']['account-owne'] = (string)$AutomationOpportunityOwner;}
                if($PAutomationVendorName){
                    $OPPFields['fields']['text'] = (string)$PAutomationVendorName;
                    $OPPFields['fields']['opportunity-nam'] = (string)$PAutomationVendorName;
                }
                if($AutomationDeviceType){$OPPFields['fields']['device-type'] = (string)$AutomationDeviceType;}
                if($AutomationStage){$OPPFields['fields']['text-2'] = (string)$AutomationStage;}
                if($AutomationCreatedDateFormatted){$OPPFields['fields']['created-date'] = (string)$AutomationCreatedDateFormatted;}
                if($AutomationClosedDateFormatted){$OPPFields['fields']['close'] = (string)$AutomationClosedDateFormatted;}
                if($AutomationROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$AutomationROL;}
                if($PAutomationVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$PAutomationVendorItemID;}
                if($AutomationSFID){$OPPFields['fields']['sfid'] = (string)$AutomationSFID;}
                if($AutomationPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$AutomationPodioComment;}
                if($AutomationFileLinksArray){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$AutomationFileLinksArray;}
                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
                $OPPFields['fields'][$SFStatusExID] = "2B";

                //Create Opportunity Item for Automation Apps
                $NewAutomationOpportunityItem = PodioItem::create(17330425, $OPPFields);
                $NewAutomationOpportunityItemID = $NewAutomationOpportunityItem->item_id;
                if($AutomationFileIDsArray) {
                    foreach($AutomationFileIDsArray as $file_id) {
                        $AttachAutomationOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewAutomationOpportunityItemID));
                    }
                }
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

//Bank Detail Items
//        if($spaceID == $BulgariaID || $spaceID == $PakistanID){
//            $BankDetailItems = PodioItem::filter_by_view($BankDetailsAppID, $BankDetailsSavedViewID, array('limit' => 50));
//            foreach ($BankDetailItems as $bankdetail) {
//                $PBankDetailsItemID = '';
//                $PBankDetailsUniqueID = '';
//                $PBankDetailsVendorItemID = '';
//                $PBankDetailsVendorName = '';
//                $PBankDetailsStatus = '';
//                $PBankDetailsCompanyRegistrationNO = '';
//                $PBankDetailsOfficialCompanyName = '';
//                $PBankDetailsBankName = '';
//                $PBankDetailsIBAN = '';
//                $PBankBIC = '';
//                $PBankVendorBackendCode = '';
//                $PBankSFID = '';
//
//                $PBankDetailsItemID = $bankdetail->item_id;
//                $BankDetailItem = PodioItem::get($PBankDetailsItemID);
//                $PBankDetailsUniqueID = $BankDetailItem->app_item_id_formatted;
//                if ($PBankDetailsVendorExID) {
//                    $PBankDetailsVendorItemID = $BankDetailItem->fields[$PBankDetailsVendorExID]->values[0]->item_id;
//                    $PBankDetailsVendorName = $BankDetailItem->fields[$PBankDetailsVendorExID]->values[0]->title;
//                }
//                if ($PBankDetailsStatusExID) {
//                    $PBankDetailsStatus = $BankDetailItem->fields[$PBankDetailsStatusExID]->values[0]['text'];
//                }
//                if ($PBankDetailsCompanyRegistrationNOExID) {
//                    $PBankDetailsCompanyRegistrationNO = $BankDetailItem->fields[$PBankDetailsCompanyRegistrationNOExID]->values;
//                }
//                if ($PBankDetailsOfficialCompanyNameExID) {
//                    $PBankDetailsOfficialCompanyName = $BankDetailItem->fields[$PBankDetailsOfficialCompanyNameExID]->values;
//                }
//                if ($PBankDetailsBankNameExID) {
//                    $PBankDetailsBankName = $BankDetailItem->fields[$PBankDetailsBankNameExID]->values;
//                }
//                if ($PBankDetailsIBANExID) {
//                    $PBankDetailsIBAN = $BankDetailItem->fields[$PBankDetailsIBANExID]->values;
//                }
//                if ($PBankDetailsBICNumberExID) {
//                    $PBankBIC = $BankDetailItem->fields[$PBankDetailsBICNumberExID]->values;
//                }
//                if ($PBankDetailsVendorBackendCodeExID) {
//                    $PBankVendorBackendCode = $BankDetailItem->fields[$PBankDetailsVendorBackendCodeExID]->values;
//                }
//                if ($PBankDetailsSFIDExID) {
//                    $PBankSFID = $BankDetailItem->fields[$PBankDetailsSFIDExID]->values;
//                }
//
//            }
//        }


//Food Panda Salesforce List Types
//    $AccountsList = "Account";
//    $ContactsList = "Contact";
//    $ContractsList = "Contract";
//    $OpportunityList = "Opportunity";
//
//    //Salesforce Record Types
//    $VendorRecordType = 'Standard';
//    $ContactRecordType = 'Vendor';
//    $ContractRecordType = 'Standard/ Online Payment';
//    $BankDetailsRecordType = '-';
//    $SalesRecordType = 'Sales';
//    $RenegotiationRecordType = 'Renegotiation ';
//    $DealsPromotionsRecordType = 'Deal/Promotion';
//    $OPRecordType = 'Online Payment';
//    $DigitalizationRecordType = 'Online Marketing';
//    $AutomationsRecordType = 'Automation';
//    $RestaurantMarketingRecordType = 'Restaurant Marketing';
//    $AdSalesRecordType = 'Ad Sales';
//    $CompaniesRecordType = 'Corporate';
//    $CorporateSalesRecordType = 'Corporate';
//
//    //Salesforce Object Types
//    $VendorSFObject = 'Accounts';
//    $ContactSFObject = 'Contacts';
//    $ContractSFObject = 'Contracts';
//    $BankDetailsSFObject = 'Bank Details';
//    $SalesSFObject = 'Opportunities';
//    $RenegotiationSFObject = 'Opportunities';
//    $DealsPromotionsSFObject = 'Opportunities/Promotions';
//    $OPSFObject = 'Opportunities';
//    $DigitalizationSFObject = 'Opportunities';
//    $AutomationsSFObject = 'Opportunities';
//    $RestaurantMarketingSFObject = 'Opportunities';
//    $AdSalesSFObject = 'Opportunities';
//    $CompaniesSFObject = 'Accounts';
//    $CorporateSalesSFObject = 'Opportunities';



///Vendor Salesforce Fields..........................................
//    $PVendorRecordType = "Vendor";
//    $SFVendorIDFieldName = 'Podio ID';
//    $SFVendorRecordTypeFieldName = 'Record Type';
//    $SFVendorCreatedOnFieldName = 'Created Date';
//    $SFVendorCreatedByFieldName = 'Created by';
//    $SFVendorCompanyNameFieldName = 'Company Name';
//    $SFVendorHQBuildingNameField = 'Building NameNoHQ';
//    $SFVendorRecordOwnderFieldName = 'Record Owner';
//    $SFVendorStatusFieldName = 'Account Status';
//    $SFVendorGradeFieldName = 'Vendor Grade';
//    $SFVendorStreetNameFieldName = 'Street Name';
//    $SFVendorPostCodeFieldName = 'Post Code';
//    $SFVendorCityFieldName = 'City';
//    $SFVendorStateFieldName = 'No';
//    $SFVendorCountryFieldName = 'Country';
//    $SFVendorAreaDistrictFieldName = 'Area/District';
//    $SFVendorWebsiteURLFieldName = 'Website URL';
//    $SFVendorFacebookURLFieldName = 'Facebook URL';
//    $SFVendorChainFieldName = 'Chain';
//    $SFVendorNoRestaurantsFieldName = 'No. of restaurants';
//    $SFVendorBackendCodeFieldName = 'Vendor Backend Code';
//    $SFVendorChainCodeFieldName = 'Chain Code';
//    $SFVendorCuisineFieldName = 'Cuisine';
//    $SFVendorFrontendURLFieldName = 'Frontend URL';
//    $SFVendorReasonforDeactivationFieldName = 'Reason for Deactivation';
//    $SFVendorActivatedDateFieldName = 'Activated Date';
//    $SFVendorCommentsFieldName = 'Comments';
//    $SFVendorOpportunityCurrencyFieldName = 'Opportunity Currency';
//    /////////////////////////////////////////////////////////////////////////////////
//    //Contact Salesforce Fields.....................................................
//    $SFContactIDFieldName = 'Podio ID';
//    $SFContactFirstNameFieldName = 'First Name';
//    $SFContactLastNameFieldName = 'Last Name';
//    $SFContactVendorNameFieldName = 'Company Name';
//    $SFContactJobTitleFieldName = 'Job Title';
//    $SFContactEmailFieldName = 'Email';
//    $SFContactPhoneNumberFieldName = 'Phone Number';
//    $SFContactMainContactFieldName = 'Main Contact';
//    $SFContactRecordTypeFieldName = 'Record Type';
//    $SFContactOwnerFieldName = 'Contact Owner';
//    ////////////////////////////////////////////////////////////////////////////////////////////
//    //Contract Salesforce Fields......................................................
//    $SFContactIDFieldName = 'Podio ID';
//    $SFContactOpportunityRecordTypeFieldName = 'Opportunity Record Type';
//    $SFContactCompanyNameFieldName = 'Company Name';
//    $SFContactOwnerFieldName = 'Contract Owner';
//    $SFContactStatusFieldName = 'Status';
//    $SFContactOrderMethodFieldName = 'order transmission method';
//    $SFContactStartDateFieldName = 'Contract Start Date';
//    $SFContactCommissionRateFieldName = 'Commission rate percentage';
//    $SFContactOPFeeFieldName = 'Online Payment Transaction Fee';
//    $SFContactMinOrderFieldName = 'Min. Order';
//    $SFContactDeliveryTypeFieldName = 'Delivery Type';
//    $SFContactDeliveryChargeFieldName = 'Delivery Charge';
//    $SFContactActivationDateFieldName = 'Activation Date';
//    $SFContactSpecialTermsFieldName = 'Special Terms';
//    $SFContactApprovalDateFieldName = 'Approval Date';
//    $SFContactAttachmentsFieldName = 'Attachments';
//    ///////////////////////////////////////////////////////////////////////////////////////////////
//    //Bank Details.................................................................................
//    $SFBankDetailsFieldName = 'Podio ID';
//    $SFBankDetailsStatusFieldName = 'Status';
//    $SFBankDetailsCompanyRegistrationNOFieldName = '';//Contact Object
//    $SFBankDetailsOfficialCompanyNameFieldName = '';//Contact Object
//    $SFBankDetailsBankNameFieldName = 'Bank Name';//Bank Name
//    $SFBankDetailsIBANFieldName = 'Bank Account Number';
//    $SFBankDetailsBICNumberFieldName = 'BIC Number';
//    $SFBankDetailsVendorBackendCodeFieldName = 'Vendor Backend Code';
//    $SFBankDetailsAttachmentsFieldName = 'Attachments';
//    $SFBankDetailsContractNumberFieldName = 'Contract Number ';
//    //////////////////////////////////////////////////////////////////////////////////////////////
//    //Sales......................................................................................
//    $SFSalesIDFieldName = 'Podio ID';
//    $SFSalesCompanyNameFieldName = 'Company Name';
//    $SFSalesOpportunityOwnerFieldName = 'Opportunity Owner';
//    $SFSalesStageFieldName = 'Stage';
//    $SFSalesCreatedDateFieldName = 'Created Date';
//    $SFSalesCloseDateFieldName = 'Close Date';
//    $SFSalesReasonLostFieldName = 'Reason opportunity lost';
//    $SFSalesFieldName = 'Opportunity Record Type';
//    $SFSalesOppCurrencyFieldName = 'Opportunity Currency';
//    //////////////////////////////////////////////////////////////////////////////////////////////
//    //Deals........................................................................................
//    $SFDealsVendorNameFieldName = 'Vendor Name';
//    $SFDealsOpportunityOwnerFieldName = 'Opportunity Owner';
//    $SFDealsApprovalStatusFieldName = 'APPROVED';
//    $SFDealsPromoTitleFieldName = 'Description';
//    $SFDealsStageFieldName = 'Stage';
//    $SFDealsPromoTypeFieldName = 'Promo Type';
//    $SFDealsPromoDurationFieldName = 'Promo Duration';
//    $SFDealsCreatedDateFieldName = 'Created Date';
//    $SFDealsClosedDateFieldName = 'Closed Date';
//    $SFDealsDiscountValueFieldName = '% Discount Value';
//    $SFDealsFixedAmountDiscountFieldName = 'Fixed Amount Discount';
//    $SFDealsDescriptionFieldName = 'Description';
//    /////////////////////////////////////////////////////////////////////////////////////////////////
//    //Renegotiation.................................................................................
//    //Renegotiation ID
//    $SFRenegotiationIDFieldName = 'Podio ID';
//    $SFRenegotiationCompanyNameFieldName = 'Company Name';
//    $SFRenegotiationOpportunityOwnerFieldName = 'Opportunity Owner';
//    $SFRenegotiationStageFieldName = 'Stage';
//    $SFRenegotiationCreatedDateFieldName = 'Created Date';
//    $SFRenegotiationCloseDateFieldName = 'Close Date';
//    $SFRenegotiationReasonLostFieldName = 'Reason opportunity lost';
//    $SFRenegotiationOpportunityRecordTypeFieldName = 'Opportunity Record Type';
//    $SFRenegotiationFieldName = 'Opportunity Name';
//    $SFRenegotiationFieldName = 'Type';
//    $SFRenegotiationOpportunityCurrencyFieldName = 'Opportunity Currency';
//    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    //Online Payment********************************************************************************************
//    $SFOnlinePaymentIDFieldName = 'Podio ID';
//    $SFOnlinePaymentCompanyNameFieldName = 'Company Name';
//    $SFOnlinePaymentOpportunityOwnerFieldName = 'Opportunity Owner';
//    $SFOnlinePaymentStageFieldName = 'Stage';
//    $SFOnlinePaymentCreatedDateFieldName = 'Created Date';
//    $SFOnlinePaymentCloseDateFieldName = 'Close Date';
//    $SFOnlinePaymentROLFieldName = 'Reason opportunity lost';
//    $SFOnlinePaymentRecordTypeFieldName = 'Opportunity Record Type';
//    $SFOnlinePaymentOpportunityNameFieldName = 'Opportunity Name';
//    $SFOnlinePaymentTypeFieldName = 'Type';
//    $SFOnlinePaymentTypeFieldName = 'Opportunity Currency';
//    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    //Online Marketing Digitalization*******************************************************************************
//    $SFDigitalizationIDFieldName = 'Podio ID';
//    $SFDigitalizationCompanynameFieldName = 'Company name';
//*******************************************************************************
