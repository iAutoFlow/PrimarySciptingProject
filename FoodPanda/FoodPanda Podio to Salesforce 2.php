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
    $BASPID = 4728261;
    $BulgariaID = 4593534;
    $GeorgiaID = 4579970;
    $HungaryID = 4494456;
    $KazakhstanID = 4637806;
    $PakistanID = 4747955;
    $RomaniaID = 4562384;
    $BulgariaCorporateID = 4605925;

    $SpaceAR = array($BASPID, $BulgariaID, $GeorgiaID, $HungaryID, $PakistanID, $RomaniaID, $BulgariaCorporateID);//

    foreach($SpaceAR as $spaceID) {
        $VendorAppID = "";
        $VendorSVID = "";
        $ContactAppID = "";
        $ContactSVID = "";
        $ContractAppID = "";
        $ContractSVID = "";
        $BankDetailsAppID = "";
        $BankDetailsSVID = "";
        $DealsAppID = "";
        $DealsSVID = "";
        $SalesAppID = "";
        $SalesSVID = "";
        $RenegotiationAppID = "";
        $RenegotiationSVID = "";
        $OnlinePaymentAppID = "";
        $OPSVID = "";
        $DigitalizationAppID = "";
        $DLSVID = "";
        $AutomationsAppID = "";
        $AutomationsSVID = "";
        $RestaurantMarketingAppID = "";
        $RMSAVIID = "";
        $AdSalesAppID = "";
        $AdSalesSVID = "";
        $AccountCurrency = "";
        $SpaceEXID = 'workspace';
        $SFStatusXID = 'sfstatus';
        $PContractSpaceCountry = "";
        $CorporateSalesSVID = "";
        $CorporateSalesAppID = "";

        //Set Workspace App / Field ID's
        if ($spaceID == $BASPID) {
            //Vendor
            $PContractSpaceCountry = "Bangladesh";
            $AccountCurrency = "BDT - Bangladesh Taka";
            $VendorAppID = 16346509;
            $VendorSVID = 30917089;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 16346515;
            $ContactSVID = 30917094;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 16346514;
            $ContractSVID = 30917104;
            $PContractCompanyNameXID = 'vendor-name';
            //Sales
            $SalesAppID = 16396175;
            $SalesSVID = 30917102;
            $PSalesVendorNameXID = 'vendor-name';
            $PSalesOPOWXID = 'opportunity-owner';
            $PSalesStageXID = 'stage';
            $PSalesCDXID = 'created-date';
            $PSalesCloseDateXID = 'closed-date';
            $PSalesNewContractXID = 'sales-contract';
            $PSalesSFIDXID = 'rol';
            $PSalesROLXID = 'rol-2';
            //Deals / Promotions
            $DealsAppID = 16346506;
            $DealsSVID = 30917118;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsStageXID = 'stage';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsPromoTitleXID = 'promotion-title';
            $PDealsNotesXID = 'promotion-description';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCDXID = 'created-date';
            $PDealsCLDXID = 'closed-date';
            $PDealsSFIDXID = 'salesforce-id';
            //Renegotiation
            $RenegotiationAppID = 16396176;
            $RenegotiationSVID = 30917125;
            $PRenegotiationCompanyNameXID = 'vendor-name';
            $PRenegotiationOPOWXID = 'opportunity-owner';
            $PRenegotiationStageXID = 'stage';
            $PRenegotiationCDXID = 'created-date';
            $PRenegotiationCloseDateXID = 'closed-date';
            $PRenegotiationNewContractXID = 'sales-contract';
            $PRenegotiationReasonLostXID = 'roi';
            $PRenegotiationSFIDXID = 'sf-id';
            //OnlinePayment
            $OnlinePaymentAppID = 16346510;
            $OPSVID = 31409664;
            $POnlinePaymentCompanyNameXID = 'vendor-name';
            $POnlinePaymentOPOWXID = 'opportunity-owner';
            $POnlinePaymentStageXID = 'stage';
            $POnlinePaymentCDXID = 'commission-target';
            $POnlinePaymentCloseDateXID = 'created-date';
            $POnlinePaymentContractXID = 'closed-date';
            $POnlinePaymentROLXID = 'rol';
            //Online Marketing / Digitalization
            $DigitalizationAppID = 16346513;
            $DLSVID = 31409769;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationCloseDateXID = 'closed-date';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationROLXID = 'title';
            //Automations
            $AutomationsAppID = 16346511;
            $AutomationsSVID = 31409787;
            $PAutomationCompanyNameXID = 'vendor-name';
            $PAutomationOPOWXID = 'opportunity-owner';
            $PAutomationStageXID = 'stage';
            $PAutomationsDeviceTypeXID = 'device-type';
            $PAutomationCDXID = 'created-date';
            $PAutomationCloseDateXID = 'closed-date';
            $PAutomationROLXID = 'reason-for-refusal';
            //Restaurant Marketing
            $RestaurantMarketingAppID = 16346507;
            $RMSAVIID = 31409813;
            $PRestaurantMarketingOPOWXID = 'record-owner';
            $PRestaurantMarketingCompanyNameXID = 'vendor-name';
            $PRestaurantMarketingStageXID = 'stage';
            $PRestaurantMarketingCDXID = 'created-date';
            $PRestaurantMarketingCloseDateXID = 'close-date';
            $PRestaurantMarketingROLXID = 'rol';
            $PRestaurantMarketingSFIDXID = 'salesforce-id';
            //Ad Sales
            $AdSalesAppID = 16346516;
            $AdSalesSVID = 31409835;
        }
        if ($spaceID == $BulgariaID) {
            $AccountCurrency = "BGN - Bulgarian Lev";
            $PContractSpaceCountry = "Bulgaria";
            //Vendor
            $VendorAppID = 15888560;
            $VendorSVID = 30916884;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 15888563;
            $ContactSVID = 30916890;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 15888561;
            $ContractSVID = 30916948;
            $PContractCompanyNameXID = 'vendor-name';
            //Bank Details
            $BankDetailsAppID = 16015918;
            $BankDetailsSVID = 30916980;
            $PBankDetailsVendorXID = 'contract';
            $PBankDetailsStatusXID = 'bank-details-status';
            $PBankDetailsCompanyRegistrationNOXID = 'company-registration-no';
            $PBankDetailsOfficialCompanyNameXID = 'official-company-name';
            $PBankDetailsBankNameXID = 'bank-name';
            $PBankDetailsIBANXID = 'iban';
            $PBankDetailsBICNumberXID = 'bic';
            $PBankDetailsVendorBackendCodeXID = 'vendor-backend-code';
            $PBankDetailsSFIDXID = "sf-id";
            //Sales
            $SalesAppID = 15888558;
            $SalesSVID = 30917005;
            $PSalesVendorNameXID = 'vendor-name';
            $PSalesOPOWXID = 'opportunity-owner';
            $PSalesStageXID = 'stage';
            $PSalesCDXID = 'created-date';
            $PSalesCloseDateXID = 'closed-date';
            $PSalesNewContractXID = 'sales-contract';
            $PSalesROLXID = 'rol-2';
            $PSalesSFIDXID = 'rol';
            //Deals
            $DealsAppID = 15888559;
            $DealsSVID = 30916988;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsApprovalStatusXID = 'approval-status';
            $PDealsPromoTitleXID = 'promotion-title';
            $PDealsStageXID = 'stage';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCDXID = 'created-date';
            $PDealsCLDXID = 'closed-date';
            $PDealsDiscountValueXID = 'discount-value';
            $PDealsFixedAmountDiscountXID = 'fixed-amount-discount';
            $PDealsFreeItemTypeXID = 'free-item-type';
            $PDealsComboMenuXID = 'combo-menu';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsNotesXID = 'notes';
            //Renegotiation
            $RenegotiationAppID = 15888556;
            $RenegotiationSVID = 30917009;
            $PRenegotiationCompanyNameXID = 'vendor-name';
            $PRenegotiationOPOWXID = 'opportunity-owner';
            $PRenegotiationStageXID = 'stage';
            $PRenegotiationCDXID = 'created-date';
            $PRenegotiationCloseDateXID = 'closed-date';
            $PRenegotiationNewContractXID = 'sales-contract';
            $PRenegotiationReasonLostXID = 'roi';
            $PRenegotiationSFIDXID = 'sf-id';
            //OnlinePayment
            $OnlinePaymentAppID = 15888562;
            $OPSVID = 30916990;
            $POnlinePaymentCompanyNameXID = 'vendor-name';
            $POnlinePaymentOPOWXID = 'opportunity-owner';
            $POnlinePaymentStageXID = 'stage';
            $POnlinePaymentCDXID = 'commission-target';
            $POnlinePaymentCloseDateXID = 'created-date';
            $POnlinePaymentContractXID = 'closed-date';
            $POnlinePaymentROLXID = 'rol';
            //Digitalization
            $DigitalizationAppID = 1588557;
            $DLSVID = 30916989;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationCloseDateXID = 'closed-date';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationROLXID = 'title';
            //Restaurant Marketing
            $RestaurantMarketingAppID = 16273484;
            $RMSAVIID = 30917011;
            $PRestaurantMarketingOPOWXID = 'record-owner';
            $PRestaurantMarketingCompanyNameXID = 'vendor-name';
            $PRestaurantMarketingStageXID = 'stage';
            $PRestaurantMarketingCDXID = 'created-date';
            $PRestaurantMarketingCloseDateXID = 'close-date';
            $PRestaurantMarketingROLXID = 'rol';
            $PRestaurantMarketingSFIDXID = 'salesforce-id';
            $PRestaurantMarketingVendorBackendCodeXID = 'vendor-backend-code';
            //Automations
            $AutomationsAppID = 15888555;
            $AutomationsSVID = 30916998;
            $PAutomationCompanyNameXID = 'vendor-name';
            $PAutomationOPOWXID = 'opportunity-owner';
            $PAutomationStageXID = 'stage';
            $PAutomationsDeviceTypeXID = 'device-type';
            $PAutomationCDXID = 'created-date';
            $PAutomationCloseDateXID = 'closed-date';
            $PAutomationROLXID = 'reason-for-refusal';

        }
        if ($spaceID == $GeorgiaID) {
            $AccountCurrency = "GEL - Georgia Lari";
            $PContractSpaceCountry = "Georgia";
            //Vendor
            $VendorAppID = 15842653;
            $VendorSVID = 30919950;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 15842651;
            $ContactSVID = 31410101;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 15842654;
            $ContractSVID = 30919976;
            $PContractCompanyNameXID = 'vendor-name';
            //Sales
            $SalesAppID = 15842649;
            $SalesSVID = 31410107;
            $PSalesVendorNameXID = 'vendor-name';
            $PSalesOPOWXID = 'opportunity-owner';
            $PSalesStageXID = 'stage';
            $PSalesCDXID = 'created-date';
            $PSalesCloseDateXID = 'closed-date';
            $PSalesNewContractXID = 'sales-contract';
            $PSalesSFIDXID = 'rol';
            //Renegotiation
            $RenegotiationAppID = 15842657;
            $RenegotiationSVID = 29328565;
            $PRenegotiationCompanyNameXID = 'vendor-name';
            $PRenegotiationOPOWXID = 'opportunity-owner';
            $PRenegotiationStageXID = 'stage';
            $PRenegotiationCDXID = 'created-date';
            $PRenegotiationCloseDateXID = 'closed-date';
            $PRenegotiationNewContractXID = 'sales-contract';
            $PRenegotiationReasonLostXID = 'roi';
            //Deals
            $DealsAppID = 15842650;
            $DealsSVID = 31410134;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsStageXID = 'stage';
            $PDealsCDXID = 'created-date';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsDiscountValueXID = 'discount-value';
            $PDealsFixedAmountDiscountXID = 'fixed-amount-discount';
            $PDealsFreeItemTypeXID = 'free-item';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCLDXID = 'closed-date';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsNotesXID = 'notes';
            //OnlinePayment
            $OnlinePaymentAppID = 15842652;
            $OPSVID = 31410165;
            $POnlinePaymentCompanyNameXID = 'vendor-name';
            $POnlinePaymentOPOWXID = 'opportunity-owner';
            $POnlinePaymentStageXID = 'stage';
            $POnlinePaymentCDXID = 'commission-target';
            $POnlinePaymentCloseDateXID = 'created-date';
            $POnlinePaymentContractXID = 'closed-date';
            $POnlinePaymentROLXID = 'rol';
            //Digitalization
            $DigitalizationAppID = 15842648;
            $DLSVID = 31410297;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationCloseDateXID = 'closed-date';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationROLXID = 'title';
            //Automations
            $AutomationsAppID = 15842647;
            $AutomationsSVID = 31410341;
            $PAutomationCompanyNameXID = 'vendor-name';
            $PAutomationOPOWXID = 'opportunity-owner';
            $PAutomationStageXID = 'stage';
            $PAutomationsDeviceTypeXID = 'device-type';
            $PAutomationCDXID = 'created-date';
            $PAutomationCloseDateXID = 'closed-date';
            $PAutomationROLXID = 'reason-for-refusal';

        }
        if ($spaceID == $HungaryID) {
            //Vendor
            $AccountCurrency = "HUF - Hungarian Forint";
            $PContractSpaceCountry = "Hungary";
            $VendorAppID = 15554976;
            $VendorSVID = 30920018;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 15554975;
            $ContactSVID = 30920033;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 15554972;
            $ContractSVID = 29009123;
            $PContractCompanyNameXID = 'vendor-name';
            //Deals
            $DealsAppID = 15554973;
            $DealsSVID = 30898368;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsStageXID = 'stage';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsDiscountValueXID = 'discount-value';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCDXID = 'created-date';
            $PDealsNotesXID = 'notes';
            //Digitalization
            $DigitalizationAppID = 15706650;
            $DLSVID = 29012042;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationROLXID = 'title';
        }
        if ($spaceID == $KazakhstanID) {
            $AccountCurrency = "KZT - Kazakhstan Tenge";
            $PContractSpaceCountry = "Kazakhstan";
            $VendorAppID = 16037457;
            $ContactAppID = 16037452;
            $ContractAppID = 16037459;
            $BankDetailsAppID = 16037468;
            $DealsAppID = 16037456;
            $DigitalizationAppID = 16037455;
            $OnlinePaymentAppID = 16037460;
            $AutomationsAppID = 16037458;
            $VendorSVID = 29909277;
            $ContactSVID = 30920075;
            $ContractSVID = 30920083;
            $BankDetailsSVID = 30920102;
            $DealsSVID = 29885815;
            $DLSVID = 31411786;
            $OPSVID = 31411781;
            $AutomationsSVID = 31411773;
        }
        if ($spaceID == $PakistanID) {
            //Vendor
            $AccountCurrency = "PKR - Pakistani Rupee";
            $PContractSpaceCountry = "Pakistan";
            $VendorAppID = 16413624;
            $VendorSVID = 30920194;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 16513617;
            $ContactSVID = 30920202;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 16413616;
            $ContractSVID = 30059393;
            $PContractCompanyNameXID = 'vendor-name';
            //Bank Details
            $BankDetailsAppID = 16413669;
            $BankDetailsSVID = 30920216;
            $PBankDetailsVendorXID = 'contract';
            $PBankDetailsStatusXID = 'bank-details-status';
            $PBankDetailsCompanyRegistrationNOXID = 'company-registration-no';
            $PBankDetailsOfficialCompanyNameXID = 'official-company-name';
            $PBankDetailsBankNameXID = 'bank-name';
            $PBankDetailsIBANXID = 'iban';
            $PBankDetailsBICNumberXID = 'bic';
            $PBankDetailsSFIDXID = "sf-id";
            //Sales
            $SalesAppID = 16413660;
            $SalesSVID = 30920224;
            $PSalesVendorNameXID = 'vendor-name';
            $PSalesOPOWXID = 'opportunity-owner';
            $PSalesStageXID = 'stage';
            $PSalesCDXID = 'created-date';
            $PSalesCloseDateXID = 'closed-date';
            $PSalesNewContractXID = 'sales-contract';
            $PSalesROLXID = 'rol';
            $PSalesSFIDXID = 'sf-id';
            //Renegotiation
            $RenegotiationAppID = 16413661;
            $RenegotiationSVID = 31411592;
            $PRenegotiationCompanyNameXID = 'vendor-name';
            $PRenegotiationOPOWXID = 'opportunity-owner';
            $PRenegotiationStageXID = 'stage';
            $PRenegotiationCDXID = 'created-date';
            $PRenegotiationCloseDateXID = 'closed-date';
            $PRenegotiationNewContractXID = 'sales-contract';
            $PRenegotiationReasonLostXID = 'roi';
            $PRenegotiationSFIDXID = 'salesforce-id';
            //Deals
            $DealsAppID = 16413621;
            $DealsSVID = 31411629;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsStageXID = 'stage';
            $PDealsPromoTitleXID = 'promotion-title';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsPromoDetailsXID = 'promotion-description';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCDXID = 'created-date';
            $PDealsCLDXID = 'closed-date';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsSFIDXID = 'salesforce-id';
            //OnlinePayment
            $OnlinePaymentAppID = 16413618;
            $OPSVID = 30920242;
            $POnlinePaymentCompanyNameXID = 'vendor-name';
            $POnlinePaymentOPOWXID = 'opportunity-owner';
            $POnlinePaymentStageXID = 'stage';
            $POnlinePaymentCDXID = 'commission-target';
            $POnlinePaymentCloseDateXID = 'created-date';
            $POnlinePaymentContractXID = 'closed-date';
            $POnlinePaymentROLXID = 'rol';
            $POnlinePaymentSFIDXID = 'salesforce-id';
            //Digitalization
            $DigitalizationAppID = 16413619;
            $DLSVID = 31411666;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationCloseDateXID = 'closed-date';
            $PDigitalizationROLXID = 'title';
            $PDigitalizationSFIDXID = 'salesforce-id';
            //Automations
            $AutomationsAppID = 16413615;
            $AutomationsSVID = 31411670;
            $PAutomationCompanyNameXID = 'vendor-name';
            $PAutomationOPOWXID = 'opportunity-owner';
            $PAutomationStageXID = 'stage';
            $PAutomationsDeviceTypeXID = 'device-type';
            $PAutomationCDXID = 'created-date';
            $PAutomationCloseDateXID = 'closed-date';
            $PAutomationROLXID = 'reason-for-refusal';
            $PAutomationSFIDXID = 'salesforce-id';

        }
        if ($spaceID == $RomaniaID) {
            $AccountCurrency = "RON - Romanian Leu";
            $PContractSpaceCountry = "Romania";
            //Vendor
            $VendorAppID = 15758358;
            $VendorSVID = 30920291;
            $PVendorNameXID = 'title';
            //Contact
            $ContactAppID = 15785360;
            $ContactSVID = 30920347;
            $PContactNameXID = 'title';
            //Contract
            $ContractAppID = 15785359;
            $ContractSVID = 30920350;
            $PContractCompanyNameXID = 'vendor-name';
            //Sales
            $SalesAppID = 15785815;
            $SalesSVID = 30920303;
            $PSalesVendorNameXID = 'vendor-name';
            $PSalesOPOWXID = 'opportunity-owner';
            $PSalesStageXID = 'stage';
            $PSalesCDXID = 'created-date';
            $PSalesCloseDateXID = 'closed-date';
            $PSalesNewContractXID = 'sales-contract';
            $PSalesSFIDXID = 'rol';
            //Renegotiation
            $RenegotiationAppID = 15791979;
            $RenegotiationSVID = 31411843;
            $PRenegotiationCompanyNameXID = 'vendor-name';
            $PRenegotiationOPOWXID = 'opportunity-owner';
            $PRenegotiationStageXID = 'stage';
            $PRenegotiationCDXID = 'created-date';
            $PRenegotiationCloseDateXID = 'closed-date';
            $PRenegotiationNewContractXID = 'sales-contract';
            $PRenegotiationReasonLostXID = 'roi';
            //Deals
            $DealsAppID = 15785357;
            $DealsSVID = 30920328;
            $PDealsVendorNameXID = 'vendor-name';
            $PDealsOPOWXID = 'record-owner-2';
            $PDealsStageXID = 'stage';
            $PDealsCDXID = 'created-date';
            $PDealsPromoTypeXID = 'promo-type';
            $PDealsDiscountValueXID = 'discount-value';
            $PDealsPromoDurationXID = 'promo-duration';
            $PDealsCLDXID = 'closed-date';
            $PDealsROLXID = 'opportunity-lost-reason';
            $PDealsNotesXID = 'notes';
            //OnlinePayment
            $OnlinePaymentAppID = 15792855;
            $OPSVID = 30920308;
            $POnlinePaymentCompanyNameXID = 'vendor-name';
            $POnlinePaymentOPOWXID = 'opportunity-owner';
            $POnlinePaymentStageXID = 'stage';
            $POnlinePaymentCDXID = 'commission-target';
            $POnlinePaymentCloseDateXID = 'created-date';
            $POnlinePaymentContractXID = 'closed-date';
            $POnlinePaymentContractXID = 'op-contract';
            $POnlinePaymentROLXID = 'rol';
            //Digitalization
            $DigitalizationAppID = 15785356;
            $DLSVID = 31411822;
            $PDigitalizationCompanyNameXID = 'vendor-name';
            $PDigitalizationOPOWXID = 'record-owner-3';
            $PDigitalizationStageXID = 'stage';
            $PDigitalizationCDXID = 'created-date';
            $PDigitalizationCloseDateXID = 'closed-date';
            $PDigitalizationItemTypeXID = 'item-type';
            $PDigitalizationItemURLXID = 'item-url';
            $PDigitalizationROLXID = 'title';
            //Automations
            $AutomationsAppID = 15792076;
            $AutomationsSVID = 30920334;
            $PAutomationCompanyNameXID = 'vendor-name';
            $PAutomationOPOWXID = 'opportunity-owner';
            $PAutomationStageXID = 'stage';
            $PAutomationsDeviceTypeXID = 'device-type';
            $PAutomationCDXID = 'created-date';
            $PAutomationCloseDateXID = 'closed-date';
            $PAutomationROLXID = 'reason-for-refusal';

        }
        if ($spaceID == $BulgariaCorporateID) {
            //Vendor
            $VendorAppID = 15930760;
            $VendorSVID = 31021254;
            $PContractSpaceCountry = "Bulgaria Corporate";
            $AccountCurrency = "BGN - Bulgarian Lev";
            $PVendorNameXID = 'title';
            $PVendorRecordOWXID = 'record-owner-4';
            $PVendorStatusXID = 'vendor-status';
            $PVendorAddressXID = 'address';
            unset($PVendorAreaDistrictXID);
            $PVendorAreaDistrictTEXTXID = 'areadistrict';
            $PVendorCompanyScopeXID = 'company-scope';
            $VendorIndustryXID = 'industry';
            $VendorNoEmployeesXID = 'no-of-employees';
            $PVendorNotesXID = 'comments';
            //Contact
            $ContactAppID = 15930764;
            $ContactSVID = 31554455;
            $PContactNameXID = 'title';
            $PContactVendorItemXID = 'vendor-name';
            $PContactJobTitleXID = 'job-title-2';
            $PContactEmailXID = 'email';
            $PContactPhoneNumberXID = 'phone-number-2';
            //Corporate Sales
            $CorporateSalesAppID = 15930782;
            $CorporateSalesSVID = 31555307;
            $PCorporateSalesVendorXID = "vendor-name";
            $CSOOEXID = "opportunity-owner";
            $PCorporateSalesStageXID = "stage";
            $PCorporateSalesCDXID = "created-date";
            $CSCDEXID = "closed-date";
            $PCorporateSalesROLXID = "rol";
            $PCorporateSalesContractXID = "sales-contract";
        }
        
        $OPPFields = array('fields' => array());
        $PROMOFAR = array('fields' => array());


//        //Sales Items
//        if($spaceID == $BASPID || $spaceID == $BulgariaID || $spaceID == $PakistanID || $spaceID == $RomaniaID){
//            $SalesItems = PodioItem::filter_by_view($SalesAppID, $SalesSVID, array('limit'=>500));
//            foreach($SalesItems as $sale){
//                $OPPFields = array('fields' => array());
//                $SalesItem = '';
//                $PSalesItemUniqueID = '';
//                $PSalesItemID = '';
//                $PSalesVendorName = '';
//                $PSalesVendorItemID = '';
//                $SalesOPOW = '';
//                $SalesStage = '';
//                $SalesCD = '';
//                $SalesCLD = '';
//                $SalesContractItemID = '';
//                $SalesROL = '';
//                $SalesSFID = '';
//                $OPRecordType = 'Sales';
//                $SalesType = "New Business";
//
//                $PSalesItemID = $sale->item_id;
//                $SalesItem = PodioItem::get($PSalesItemID);
//                $PSalesItemUniqueID = $SalesItem->app_item_id_formatted;
//                if($PSalesVendorNameXID) {
//                    $PSalesVendorItemID = $SalesItem->fields[$PSalesVendorNameXID]->values[0]->item_id;
//                    $PSalesVendorName = $SalesItem->fields[$PSalesVendorNameXID]->values[0]->title;
//                }
//                if($PSalesOPOWXID){$SalesOPOW = $SalesItem->fields[$PSalesOPOWXID]->values[0]->name;}
//                if($PSalesStageXID){$SalesStage = $SalesItem->fields[$PSalesStageXID]->values[0]['text'];}
//                if($PSalesCDXID){$SalesCD = $SalesItem->fields[$PSalesCDXID]->start;}
//                if($PSalesCloseDateXID){$SalesCLD = $SalesItem->fields[$PSalesCloseDateXID]->start;}
//                if($PSalesNewContractXID){$SalesContractItemID = $SalesItem->fields[$PSalesNewContractXID]->values[0]->item_id;}
//                if($PSalesROLXID){$SalesROL = $SalesItem->fields[$PSalesROLXID]->values;}
//                if($PSalesSFIDXID){$SalesSFID = $SalesItem->fields[$PSalesSFIDXID]->values;}
//                $PSalesTags = $SalesItem->tags;
//
//
//                //Format Dates
//                if ($SalesCD) {
//                    $SalesCDFormatted = date_format($SalesCD, "m-d-Y");
//                    $SalesCDFormatted = str_replace("-", "/", $SalesCDFormatted);
//                }
//                if ($SalesCLD) {
//                    $SalesCLDFormatted = date_format($SalesCLD, "m-d-Y");
//                    $SalesCLDFormatted = str_replace("-", "/", $SalesCLDFormatted);
//                }
//
//                //Get / Set / Format Item CM
//                unset($SalesPodioComment);
//                unset($SalesItemCMAR);
//                unset($SalesTagsAR);
//                $SalesPodioComment = "";
//                $SalesTagsAR = "";
//                $SalesItemCMAR = "";
//                //Get / Set / Format Item Tags
//                foreach ($PSalesTags as $tag) {
//                    $TagValue = $tag['tag'];
//                    if ($TagValue) {
//                        $SalesTagsAR .= $TagValue;
//                    }
//                }
//                //Get / Set / Format Item CM
//                $SalesCM = PodioComment::get_for('item', (int)$PSalesItemID);
//                foreach ($SalesCM as $comment) {
//                    unset($CommentCreatedOn);
//                    unset($CommentValue);
//                    unset($CommentCreatedBy);
//                    unset($CommentID);
//                    unset($SalesCommentString);
//                    $CommentID = $comment->comment_id;
//                    $CommentValue = $comment->value;
//                    $CommentCreatedBy = $comment->created_by->name;
//                    $CommentCreatedOn = $comment->created_on;
//                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
//                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
//                    $SalesCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
//                    $SalesItemCMAR .= $SalesCommentString;
//                }
//                //Combine Tags, CM And Notes into Single Notes Value
//                if ($SalesItemCMAR) {
//                    $SalesPodioComment .= "**Podio Item Comments-**\n $SalesItemCMAR\n";
//                }
//                if ($SalesTagsAR) {
//                    $SalesPodioComment .= "**Podio Tags:** $SalesTagsAR\n";
//                }
//
//                //Get File Info
//                unset($SalesFileIDsAR);
//                unset($SalesItemFiles);
//                unset($SalesFileLinksAR);
//                unset($SalesFileIDsAR);
//                unset($NewSalesItemID);
//                $SalesFileIDsAR = array();
//                $SalesFileLinksAR = "";
//                $SalesItemFiles = $SalesItem->files;
//                foreach ($SalesItemFiles as $file) {
//                    unset($OrigFileID);
//                    unset($CopiedFile);
//                    $OrigFileID = $file->file_id;
//                    if ($OrigFileID) {
//                        $CopiedFile = PodioFile::copy($OrigFileID);
//                        $NewFileID = $CopiedFile->file_id;
//                        $NewFile = PodioFile::get($NewFileID);
//                        $NewFileName = $NewFile->name;
//                        $NewFileType = $NewFile->mimtype;
//                        $NewFileSize = $NewFile->size;
//                        $NewFileLink = $NewFile->link;
//                        $NewFileContents = $NewFile->get_raw();
//                        if ($NewFileLink) {
//                            $SalesFileLinksAR .= $NewFileLink . "\n";
//                        }
//                        if ($NewFileID) {
//                            array_push($SalesFileIDsAR, $NewFileID);
//                        }
//                    }
//                }
//
//                //Create SFOP Item for Sales Podio Item
//                if($PSalesItemID){
//                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$PSalesItemID;
//                    $OPPFields['fields']['sale-item'] = (int)$PSalesItemID;
//
//                }
//                if($PSalesItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$PSalesItemUniqueID;}
//                if($OPRecordType){$OPPFields['fields']['title'] = (string)$OPRecordType;}
//                if($SalesOPOW){$OPPFields['fields']['account-owne'] = (string)$SalesOPOW;}
//                if($PSalesVendorName){
//                    $OPPFields['fields']['text'] = (string)$PSalesVendorName;
//                    $OPPFields['fields']['opportunity-nam'] = (string)$PSalesVendorName;
//                }
//                if($SalesType){$OPPFields['fields']['type'] = (string)$SalesType;}
//                if($SalesStage){$OPPFields['fields']['text-2'] = (string)$SalesStage;}
//                if($SalesCDFormatted){$OPPFields['fields']['created-date'] = (string)$SalesCDFormatted;}
//                if($SalesCLDFormatted){$OPPFields['fields']['close'] = (string)$SalesCLDFormatted;}
//                if($SalesROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$SalesROL;}
//                if($PSalesVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$PSalesVendorItemID;}
//                if($SalesSFID){$OPPFields['fields']['sfid'] = (string)$SalesSFID;}
//                if($SalesPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$SalesPodioComment;}
//                if($SalesFileLinksAR){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$SalesFileLinksAR;}
//
//                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
//                $OPPFields['fields'][$SFStatusXID] = "2B";
//
//                //Create OP Item for Sales Apps
//                $NewSalesOPItem = PodioItem::create(17330425, $OPPFields);
//                $NewSalesOPItemID = $NewSalesOPItem->item_id;
//                if($SalesFileIDsAR) {
//                    foreach($SalesFileIDsAR as $file_id) {
//                        $AttachSalesOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewSalesOPItemID));
//                    }
//                }
//            }
//        }
//        //Renegotiation
//        if($spaceID == $BASPID || $spaceID == $BulgariaID || $spaceID == $PakistanID || $spaceID == $RomaniaID || $spaceID == $GeorgiaID){
//            $RenegotiationItems = PodioItem::filter_by_view($RenegotiationAppID, $RenegotiationSVID, array('limit'=>500));
//            foreach($RenegotiationItems as $renegotiation){
//                $OPPFields = array('fields' => array());
//                $RenegotiationItem = '';
//                $PRenegotiationItemUniqueID = '';
//                $PRenegotiationItemID = '';
//                $PRenegotiationVendorName = '';
//                $PRenegotiationVendorItemID = '';
//                $RenegotiationOPOW = '';
//                $RenegotiationStage = '';
//                $RenegotiationCD = '';
//                $RenegotiationCLD = '';
//                $RenegotiationContractItemID = '';
//                $RenegotiationROL = '';
//                $RenegotiationSFID = '';
//                $OPRecordType = 'Renegotiation';
//                $RenegotiationType = "Commission Renegotiation";
//
//                $PRenegotiationItemID = $renegotiation->item_id;
//                $RenegotiationItem = PodioItem::get($PRenegotiationItemID);
//                $PRenegotiationItemUniqueID = $RenegotiationItem->app_item_id_formatted;
//                if($PRenegotiationCompanyNameXID) {
//                    $PRenegotiationVendorItemID = $RenegotiationItem->fields[$PRenegotiationCompanyNameXID]->values[0]->item_id;
//                    $PRenegotiationVendorName = $RenegotiationItem->fields[$PRenegotiationCompanyNameXID]->values[0]->title;
//                }
//                if($PRenegotiationOPOWXID){$RenegotiationOPOW = $RenegotiationItem->fields[$PRenegotiationOPOWXID]->values[0]->name;}
//                if($PRenegotiationStageXID){$RenegotiationStage = $RenegotiationItem->fields[$PRenegotiationStageXID]->values[0]['text'];}
//                if($PRenegotiationCDXID){$RenegotiationCD = $RenegotiationItem->fields[$PRenegotiationCDXID]->start;}
//                if($PRenegotiationCloseDateXID){$RenegotiationCLD = $RenegotiationItem->fields[$PRenegotiationCloseDateXID]->start;}
//                if($PRenegotiationNewContractXID){$RenegotiationContractItemID = $RenegotiationItem->fields[$PRenegotiationNewContractXID]->values[0]->item_id;}
//                if($PRenegotiationReasonLostXID){$RenegotiationROL = $RenegotiationItem->fields[$PRenegotiationReasonLostXID]->values;}
//                if($PRenegotiationSFIDXID){$RenegotiationSFID = $RenegotiationItem->fields[$PRenegotiationSFIDXID]->values;}
//                $PRenegotiationTags = $RenegotiationItem->tags;
//
//
//
//                //Format Dates
//                if ($RenegotiationCD) {
//                    $RenegotiationCDFormatted = date_format($RenegotiationCD, "m-d-Y");
//                    $RenegotiationCDFormatted = str_replace("-", "/", $RenegotiationCDFormatted);
//                }
//                if ($RenegotiationCLD) {
//                    $RenegotiationCLDFormatted = date_format($RenegotiationCLD, "m-d-Y");
//                    $RenegotiationCLDFormatted = str_replace("-", "/", $RenegotiationCLDFormatted);
//                }
//
//                //Get / Set / Format Item CM
//                unset($RenegotiationPodioComment);
//                unset($RenegotiationItemCMAR);
//                unset($RenegotiationTagsAR);
//                $RenegotiationPodioComment = "";
//                $RenegotiationTagsAR = "";
//                $RenegotiationItemCMAR = "";
//                //Get / Set / Format Item Tags
//                foreach ($PRenegotiationTags as $tag) {
//                    $TagValue = $tag['tag'];
//                    if ($TagValue) {
//                        $RenegotiationTagsAR .= $TagValue;
//                    }
//                }
//                //Get / Set / Format Item CM
//                $RenegotiationCM = PodioComment::get_for('item', (int)$PRenegotiationItemID);
//                foreach ($RenegotiationCM as $comment) {
//                    unset($CommentCreatedOn);
//                    unset($CommentValue);
//                    unset($CommentCreatedBy);
//                    unset($CommentID);
//                    unset($RenegotiationCommentString);
//                    $CommentID = $comment->comment_id;
//                    $CommentValue = $comment->value;
//                    $CommentCreatedBy = $comment->created_by->name;
//                    $CommentCreatedOn = $comment->created_on;
//                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
//                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
//                    $RenegotiationCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
//                    $RenegotiationItemCMAR .= $RenegotiationCommentString;
//                }
//                //Combine Tags, CM And Notes into Single Notes Value
//                if ($RenegotiationItemCMAR) {
//                    $RenegotiationPodioComment .= "**Podio Item Comments-**\n $RenegotiationItemCMAR\n";
//                }
//                if ($RenegotiationTagsAR) {
//                    $RenegotiationPodioComment .= "**Podio Tags:** $RenegotiationTagsAR\n";
//                }
//
//                //Get File Info
//                unset($RenegotiationFileIDsAR);
//                unset($RenegotiationItemFiles);
//                unset($RenegotiationFileLinksAR);
//                unset($RenegotiationFileIDsAR);
//                unset($NewRenegotiationItemID);
//                $RenegotiationFileIDsAR = array();
//                $RenegotiationFileLinksAR = "";
//                $RenegotiationItemFiles = $RenegotiationItem->files;
//                foreach ($RenegotiationItemFiles as $file) {
//                    unset($OrigFileID);
//                    unset($CopiedFile);
//                    $OrigFileID = $file->file_id;
//                    if ($OrigFileID) {
//                        $CopiedFile = PodioFile::copy($OrigFileID);
//                        $NewFileID = $CopiedFile->file_id;
//                        $NewFile = PodioFile::get($NewFileID);
//                        $NewFileName = $NewFile->name;
//                        $NewFileType = $NewFile->mimtype;
//                        $NewFileSize = $NewFile->size;
//                        $NewFileLink = $NewFile->link;
//                        $NewFileContents = $NewFile->get_raw();
//                        if ($NewFileLink) {
//                            $RenegotiationFileLinksAR .= $NewFileLink . "\n";
//                        }
//                        if ($NewFileID) {
//                            array_push($RenegotiationFileIDsAR, $NewFileID);
//                        }
//                    }
//                }
//
//                //Create SFOP Item for Renegotiation Podio Item
//                if($PRenegotiationItemID){
//                    $OPPFields['fields']['opportunity-podio-item-id'] = (string)$PRenegotiationItemID;
//                    $OPPFields['fields']['renegotiation-item'] = (int)$PRenegotiationItemID;
//
//                }
//                if($PRenegotiationItemUniqueID){$OPPFields['fields']['opportunity-item-unique-id'] = (string)$PRenegotiationItemUniqueID;}
//                if($OPRecordType){$OPPFields['fields']['title'] = (string)$OPRecordType;}
//                if($RenegotiationOPOW){$OPPFields['fields']['account-owne'] = (string)$RenegotiationOPOW;}
//                if($PRenegotiationVendorName){
//                    $OPPFields['fields']['text'] = (string)$PRenegotiationVendorName;
//                    $OPPFields['fields']['opportunity-nam'] = (string)$PRenegotiationVendorName;
//                }
//                if($RenegotiationType){$OPPFields['fields']['type'] = (string)$RenegotiationType;}
//                if($RenegotiationStage){$OPPFields['fields']['text-2'] = (string)$RenegotiationStage;}
//                if($RenegotiationCDFormatted){$OPPFields['fields']['created-date'] = (string)$RenegotiationCDFormatted;}
//                if($RenegotiationCLDFormatted){$OPPFields['fields']['close'] = (string)$RenegotiationCLDFormatted;}
//                if($RenegotiationROL){$OPPFields['fields']['reason-opportunity-lost'] = (string)$RenegotiationROL;}
//                if($PRenegotiationVendorItemID){$OPPFields['fields']['vendor-item'] = (int)$PRenegotiationVendorItemID;}
//                if($RenegotiationSFID){$OPPFields['fields']['sfid'] = (string)$RenegotiationSFID;}
//                if($RenegotiationPodioComment){$OPPFields['fields']['opportunity-item-notes'] = (string)$RenegotiationPodioComment;}
//                if($RenegotiationFileLinksAR){$OPPFields['fields']['opportunity-podio-file-links'] = (string)$RenegotiationFileLinksAR;}
//                $OPPFields['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
//                $OPPFields['fields'][$SFStatusXID] = "2B";
//
//
//
//                //Create OP Item for Renegotiation Apps
//                $NewRenegotiationOPItem = PodioItem::create(17330425, $OPPFields);
//                $NewRenegotiationOPItemID = $NewRenegotiationOPItem->item_id;
//                if($RenegotiationFileIDsAR) {
//                    foreach($RenegotiationFileIDsAR as $file_id) {
//                        $AttachRenegotiationOppFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewRenegotiationOPItemID));
//                    }
//                }
//            }
//        }
        //Deals
        if($spaceID !== $KazakhstanID && $spaceID !== $BulgariaCorporateID){
            $DealsItems = PodioItem::filter_by_view($DealsAppID, $DealsSVID, array('limit'=>500));
            foreach($DealsItems as $deal){
                $PROMOFAR = array('fields' => array());
                $DealsItem = '';
                $DealItemID = '';
                $DealItemUniqueID = '';
                $DealVendorItemID = '';
                $DealVendorName = '';
                $DealOPOWName = '';
                $DealApprovalStatus = '';
                $DealPromoTitle = '';
                $DealStage = '';
                $DealPromoType = '';
                $DealPromoDuration = '';
                $DealCD = '';
                $DealCLD = '';
                $DealDiscountValue = '';
                $DealFixedAmountDiscount = '';
                $DealFreeItemType = '';
                $DealComboMenu = '';
                $DealDescription = '';
                $DealROL = '';
                $DealPromoDurationStart = '';
                $DealPromoDurationEnd = '';
                $DealTags = '';
                $DealNotes = '';
                $DealSFID = '';
                $DealPromoDurationStartFormatted = '';
                $DealPromoDurationEndFormatted = '';
                $DealCDFormatted = '';
                $DealCLDFormatted = '';
                $OPRecordType = 'Deal/Promotion';

                $DealItemID = $deal->item_id;
                $DealsItem = PodioItem::get($DealItemID);
                $DealItemUniqueID = $DealsItem->app_item_id_formatted;
                if($PDealsVendorNameXID){
                    $DealVendorItemID = $DealsItem->fields[$PDealsVendorNameXID]->values[0]->item_id;
                    $DealVendorName = $DealsItem->fields[$PDealsVendorNameXID]->values[0]->title;
                }
                if($PDealsOPOWXID){$DealOPOWName = $DealsItem->fields[$PDealsOPOWXID]->values[0]->name;}
                if($PDealsApprovalStatusXID)$DealApprovalStatus = $DealsItem->fields[$PDealsApprovalStatusXID]->values[0]['text'];
                if($PDealsPromoTitleXID){$DealPromoTitle = $DealsItem->fields[$PDealsPromoTitleXID]->values;}
                if($PDealsStageXID){$DealStage = $DealsItem->fields[$PDealsStageXID]->values[0]['text'];}
                if($PDealsPromoTypeXID){$DealPromoType = $DealsItem->fields[$PDealsPromoTypeXID]->values[0]['text'];}
                if($PDealsDiscountValueXID){$DealDiscountValue = $DealsItem->fields[$PDealsDiscountValueXID]->values;}
                if($PDealsFixedAmountDiscountXID){$DealFixedAmountDiscount = $DealsItem->fields[$PDealsFixedAmountDiscountXID]->values;}
                if($PDealsPromoDurationXID){
                    $DealPromoDurationStart = $DealsItem->fields[$PDealsPromoDurationXID]->start;
                    $DealPromoDurationEnd = $DealsItem->fields[$PDealsPromoDurationXID]->end;
                }
                if($PDealsCDXID){$DealCD = $DealsItem->fields[$PDealsCDXID]->start;}
                if($PDealsCLDXID){$DealCLD = $DealsItem->fields[$PDealsCLDXID]->start;}
                if($PDealsFreeItemTypeXID){$DealFreeItemType = $DealsItem->fields[$PDealsFreeItemTypeXID]->values;}
                if($PDealsComboMenuXID){$DealComboMenu = $DealsItem->fields[$PDealsComboMenuXID]->values;}
                if($PDealsROLXID){$DealROL = $DealsItem->fields[$PDealsROLXID]->values;}
                if($PDealsNotesXID){$DealNotes = $DealsItem->fields[$PDealsNotesXID]->values;}
                if($PDealsSFIDXID){$DealSFID = $DealsItem->fields[$PDealsSFIDXID]->values;}
                $DealTags = $DealsItem->tags;
                if($DealSFID){continue;}


                //Combine Notes
                if($DealPromoTitle){$DealDescription .= "Promo Title: ".$DealPromoTitle."\n";}
                if($DealComboMenu){$DealDescription .= "Combo Menu: ".$DealComboMenu."\n";}
                if($DealFreeItemType){$DealDescription .= "Free Item Type: ".$DealFreeItemType."\n";}
                if($DealNotes){$DealDescription .= "Promotion Notes: ".$DealNotes."\n";}


                //Format Dates
                if ($DealCD) {
                    $DealCDFormatted = date_format($DealCD, "m-d-Y");
                    $DealCDFormatted = str_replace("-", "/", $DealCDFormatted);
                }
                if ($DealCLD) {
                    $DealCLDFormatted = date_format($DealCLD, "m-d-Y");
                    $DealCLDFormatted = str_replace("-", "/", $DealCLDFormatted);
                }
                if ($DealPromoDurationStart) {
                    $DealPromoDurationStartFormatted = date_format($DealPromoDurationStart, "m-d-Y");
                    $DealPromoDurationStartFormatted = str_replace("-", "/", $DealPromoDurationStartFormatted);
                }
                if ($DealPromoDurationEnd) {
                    $DealPromoDurationEndFormatted = date_format($DealPromoDurationEnd, "m-d-Y");
                    $DealPromoDurationEndFormatted = str_replace("-", "/", $DealPromoDurationEndFormatted);
                }


                //Get / Set / Format Item CM
                unset($DealsPodioComment);
                unset($DealItemCMAR);
                unset($DealTagsAR);
                $DealsPodioComment = "";
                $DealTagsAR = "";
                $DealItemCMAR = "";

                //Get / Set / Format Item Tags
                foreach ($DealTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $DealTagsAR .= $TagValue;
                    }
                }

                //Get / Set / Format Item CM
                $DealCM = PodioComment::get_for('item', (int)$DealItemID);
                foreach ($DealCM as $comment) {
                    unset($CommentCreatedOn);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                    $DealCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $DealItemCMAR .= $DealCommentString;
                }

                //Combine Tags, CM And Notes into Single Notes Value
                if ($DealDescription) {
                    $DealsPodioComment .= "Notes: $DealDescription\n";
                }
                if ($DealItemCMAR) {
                    $DealsPodioComment .= "Podio Item Comments-\n $DealItemCMAR\n";
                }
                if ($DealTagsAR) {
                    $DealsPodioComment .= "Podio Tags: $DealTagsAR\n";
                }

                //Get File Info
                $PromotionFileIDsAR = array();
                $PromotionFileLinksAR = "";
                $PromotionItemFiles = $DealsItem->files;
                foreach ($PromotionItemFiles as $file) {
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
                            $PromotionFileLinksAR .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            $PromotionFileIDsAR .= $NewFileID;
                        }
                    }
                }

                //Add Values to Promotions AR
                if($OPRecordType){$PROMOFAR['fields']['record-type'] = $OPRecordType;}
                if($DealItemUniqueID){$PROMOFAR['fields']['title'] = (string)$DealItemUniqueID;}
                if($DealVendorName){$PROMOFAR['fields']['promotion-opportunity'] = (string)$DealVendorName;}
                if($DealOPOWName){$PROMOFAR['fields']['opportunity-owner'] = (string)$DealOPOWName;}
                if($DealApprovalStatus){$PROMOFAR['fields']['approved'] = (string)$DealApprovalStatus;}
                if($DealStage){$PROMOFAR['fields']['stage'] = (string)$DealStage;}
                if($DealPromoType){$PROMOFAR['fields']['promo-type'] = (string)$DealPromoType;}
                if($DealPromoDurationStartFormatted){$PROMOFAR['fields']['startdate-promo-duration'] = (string)$DealPromoDurationStartFormatted;}
                if($DealPromoDurationEndFormatted){$PROMOFAR['fields']['enddate-promo-duration'] = (string)$DealPromoDurationEndFormatted;}
                if($DealCDFormatted){$PROMOFAR['fields']['created-date'] = (string)$DealCDFormatted;}
                if($DealCLDFormatted){$PROMOFAR['fields']['closed-date'] = (string)$DealCLDFormatted;}
                if($DealDiscountValue){$PROMOFAR['fields']['discount-value'] = (string)$DealDiscountValue;}
                if($DealFixedAmountDiscount){$PROMOFAR['fields']['fixed-amount-discount-value'] = (string)$DealFixedAmountDiscount;}
                if($DealDescription){$PROMOFAR['fields']['promotion-item-notes'] = (string)$DealDescription;}
                if($DealsPodioComment){$PROMOFAR['fields']['promotion-item-tags'] = (string)$DealsPodioComment;}
                if($PromotionFileLinksAR){$PROMOFAR['fields']['promotion-podio-file-links'] = (string)$PromotionFileLinksAR;}
                if($DealItemID){$PROMOFAR['fields']['deal-podio-item-id'] = (string)$DealItemID;}
                if($DealSFID){$PROMOFAR['fields']['promotion-sf-id'] = (string)$DealSFID;}
                if($DealVendorItemID){
                    $PROMOFAR['fields']['vendor-item-podio-id'] = (string)$DealVendorItemID;
                    $PROMOFAR['fields']['vendor-item'] = (int)$DealVendorItemID;
                }
                $PROMOFAR['fields'][$SpaceEXID] = (string)$PContractSpaceCountry;
                $PROMOFAR['fields'][$SFStatusXID] = "2B";


                //Create Promotion Item
                $CreatePromotion = PodioItem::create(17330431, $PROMOFAR);
                $NewPromoItemID = $CreatePromotion->item_id;
                if($PromotionFileIDsAR) {
                    foreach($PromotionFileIDsAR as $file_id) {
                        $AttachPromoFiles = PodioFile::attach($file_id, array('item' => (int)$NewPromoItemID));
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



//Corporate Sales











//Corporate Sales



//Food Panda Salesforce List Types
//    $AccountsList = "Account";
//    $ContactsList = "Contact";
//    $ContractsList = "Contract";
//    $OPList = "OP";
//
//    //Salesforce Record Types
//    $VendorRecordType = 'Standard';
//    $ContactRecordType = 'Vendor';
//    $ContractRecordType = 'Standard/ Online Payment';
//    $BankDetailsRecordType = '-';
//    $SalesRecordType = 'Sales';
//    $RenegotiationRecordType = 'Renegotiation ';
//    $DealsPromotionsRecordType = 'Deal/Promotion';
//    $OnlinePaymentRecordType = 'Online Payment';
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
//    $OnlinePaymentSFObject = 'Opportunities';
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
//    $SFVendorRecordOwnderFieldName = 'Record OW';
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
//    $SFVendorCMFieldName = 'CM';
//    $SFVendorOPCurrencyFieldName = 'OP Currency';
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
//    $SFContactOWFieldName = 'Contact OW';
//    ////////////////////////////////////////////////////////////////////////////////////////////
//    //Contract Salesforce Fields......................................................
//    $SFContactIDFieldName = 'Podio ID';
//    $SFContactOPRecordTypeFieldName = 'OP Record Type';
//    $SFContactCompanyNameFieldName = 'Company Name';
//    $SFContactOWFieldName = 'Contract OW';
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
//    $SFSalesOPOWFieldName = 'OP OW';
//    $SFSalesStageFieldName = 'Stage';
//    $SFSalesCDFieldName = 'Created Date';
//    $SFSalesCloseDateFieldName = 'Close Date';
//    $SFSalesReasonLostFieldName = 'Reason opportunity lost';
//    $SFSalesFieldName = 'OP Record Type';
//    $SFSalesOppCurrencyFieldName = 'OP Currency';
//    //////////////////////////////////////////////////////////////////////////////////////////////
//    //Deals........................................................................................
//    $SFDealsVendorNameFieldName = 'Vendor Name';
//    $SFDealsOPOWFieldName = 'OP OW';
//    $SFDealsApprovalStatusFieldName = 'APPROVED';
//    $SFDealsPromoTitleFieldName = 'Description';
//    $SFDealsStageFieldName = 'Stage';
//    $SFDealsPromoTypeFieldName = 'Promo Type';
//    $SFDealsPromoDurationFieldName = 'Promo Duration';
//    $SFDealsCDFieldName = 'Created Date';
//    $SFDealsCLDFieldName = 'Closed Date';
//    $SFDealsDiscountValueFieldName = '% Discount Value';
//    $SFDealsFixedAmountDiscountFieldName = 'Fixed Amount Discount';
//    $SFDealsDescriptionFieldName = 'Description';
//    /////////////////////////////////////////////////////////////////////////////////////////////////
//    //Renegotiation.................................................................................
//    //Renegotiation ID
//    $SFRenegotiationIDFieldName = 'Podio ID';
//    $SFRenegotiationCompanyNameFieldName = 'Company Name';
//    $SFRenegotiationOPOWFieldName = 'OP OW';
//    $SFRenegotiationStageFieldName = 'Stage';
//    $SFRenegotiationCDFieldName = 'Created Date';
//    $SFRenegotiationCloseDateFieldName = 'Close Date';
//    $SFRenegotiationReasonLostFieldName = 'Reason opportunity lost';
//    $SFRenegotiationOPRecordTypeFieldName = 'OP Record Type';
//    $SFRenegotiationFieldName = 'OP Name';
//    $SFRenegotiationFieldName = 'Type';
//    $SFRenegotiationOPCurrencyFieldName = 'OP Currency';
//    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    //Online Payment********************************************************************************************
//    $SFOnlinePaymentIDFieldName = 'Podio ID';
//    $SFOnlinePaymentCompanyNameFieldName = 'Company Name';
//    $SFOnlinePaymentOPOWFieldName = 'OP OW';
//    $SFOnlinePaymentStageFieldName = 'Stage';
//    $SFOnlinePaymentCDFieldName = 'Created Date';
//    $SFOnlinePaymentCloseDateFieldName = 'Close Date';
//    $SFOnlinePaymentROLFieldName = 'Reason opportunity lost';
//    $SFOnlinePaymentRecordTypeFieldName = 'OP Record Type';
//    $SFOnlinePaymentOPNameFieldName = 'OP Name';
//    $SFOnlinePaymentTypeFieldName = 'Type';
//    $SFOnlinePaymentTypeFieldName = 'OP Currency';
//    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    //Online Marketing Digitalization*******************************************************************************
//    $SFDigitalizationIDFieldName = 'Podio ID';
//    $SFDigitalizationCompanynameFieldName = 'Company name';
//*******************************************************************************
