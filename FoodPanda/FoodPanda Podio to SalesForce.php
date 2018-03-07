<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/21/2016
 * Time: 12:05 PM
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


    //Food Panda Salesforce List Types
    $AccountsList = "Account";
    $ContactsList = "Contact";
    $ContractsList = "Contract";
    $OpportunityList = "Opportunity";

    //Salesforce Record Types
    $VendorRecordType = 'Standard';
    $ContactRecordType = 'Vendor';
    $ContractRecordType = 'Standard/ Online Payment';
    $BankDetailsRecordType = '-';
    $SalesRecordType = 'Sales';
    $RenegotiationRecordType = 'Renegotiation ';
    $DealsPromotionsRecordType = 'Deal/Promotion';
    $OnlinePaymentRecordType = 'Online Payment';
    $DigitalizationRecordType = 'Online Marketing';
    $AutomationsRecordType = 'Automation';
    $RestaurantMarketingRecordType = 'Restaurant Marketing';
    $AdSalesRecordType = 'Ad Sales';
    $CompaniesRecordType = 'Corporate';
    $CorporateSalesRecordType = 'Corporate';

    //Salesforce Object Types
    $VendorSFObject = 'Accounts';
    $ContactSFObject = 'Contacts';
    $ContractSFObject = 'Contracts';
    $BankDetailsSFObject = 'Bank Details';
    $SalesSFObject = 'Opportunities';
    $RenegotiationSFObject = 'Opportunities';
    $DealsPromotionsSFObject = 'Opportunities/Promotions';
    $OnlinePaymentSFObject = 'Opportunities';
    $DigitalizationSFObject = 'Opportunities';
    $AutomationsSFObject = 'Opportunities';
    $RestaurantMarketingSFObject = 'Opportunities';
    $AdSalesSFObject = 'Opportunities';
    $CompaniesSFObject = 'Accounts';
    $CorporateSalesSFObject = 'Opportunities';

    //SpaceID's
    $BangladeshSpaceID = 4728261;
    $BulgariaSpaceID = 4593534;
    $GeorgiaSpaceID = 4579970;
    $HungarySpaceID = 4494456;
    $KazakhstanSpaceID = 4637806;
    $PakistanSpaceID = 4747955;
    $RomaniaSpaceID = 4562384;
    $BulgariaCorporateID = 4605925;

    $PSalesType = 'New Business';
    $PRenegotiationType = 'Commission Renegotiation';
    $POnlinePaymentType = 'New Business';

    $SpaceArray = array($BulgariaCorporateID); //$BangladeshSpaceID, $BulgariaSpaceID, $HungarySpaceID, $PakistanSpaceID, $RomaniaSpaceID, $GeorgiaSpaceID

    foreach($SpaceArray as $spaceID) {
        //For Bangladesh Workspace
        $ContactAppID = "";
        $VendorAppID = "";
        $VendorSavedViewID = "";
        $ContactSavedViewID = "";
        $ContractAppID = '';
        $ContractSavedViewID = '';
        $PContractSpaceCountry = "";
        unset($AccountCurrency);
        unset($PVendorNameEx);
        unset($VendorHQBuildingNameExID);
        unset($PVendorRecordOwnerExID);
        unset($PVendorStatusExID);
        unset($PVendorAPPriorityExID);
        unset($PVendorGradeExID);
        unset($PVendorAreaDistrictExID);
        unset($PVendorMenuQualityExID);
        unset($PVendorWebsiteExID);
        unset($PVendorFacebookExID);
        unset($PVendorPartnerTypeExID);
        unset($PVendorChainExID);
        unset($PVendorNoRestaurantsExID);
        unset($PVendorBackendCodeExID);
        unset($PVendorChainCodeExID);
        unset($PVendorCuisineExID);
        unset($PVendorFrontendURLExID);
        unset($PVendorReasonforDeactivationExID);
        unset($PVendorNotesExID);
        unset($PVendorActivationDateExID);
        unset($PVendorAreaDistrictTEXTExID);
        unset($PVendorSFID);
        unset($PContactNameExID);
        unset($PContactVendorItemExID);
        unset($PContactJobTitleExID);
        unset($PContactEmailExID);
        unset($PContactPhoneNumberExID);
        unset($PVendorSFIDExID);
        unset($PContractCompanyNameExID);
        unset($PContractRecordOwnerExID);
        unset($PContractTypeExID);
        unset($PContractStatusExID);
        unset($PContractOrderMethodExID);
        unset($PContractStartDateExID);
        unset($PContractCommissionRateExID);
        unset($PContractOPFeeExID);
        unset($PContractDeliveryTypeExID);
        unset($PContractDeliveryChargeExID);
        unset($PContractActivationDateExID);
        unset($PContractSpecialTermsExID);
        unset($PContractApprovalDateExID);
        unset($PContractSFIDExID);

        if ($spaceID == $BangladeshSpaceID) {
            //Vendor
            $AccountCurrency = "BDT - Bangladesh Taka";
            $VendorAppID = 16346509;
            $VendorSavedViewID = 30917089;
            $PVendorNameExID = 'title';
            $VendorHQBuildingNameExID = 'building-hq-name';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorAPPriorityExID = 'am-priority';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            $PVendorAreaDistrictExID = 'areadistrict-2';
            $PVendorMenuQualityExID = 'caracteristics';
            $PVendorWebsiteExID = 'website';
            $PVendorFacebookExID = 'facebook';
            $PVendorPartnerTypeExID = 'partner-type';
            $PVendorChainExID = 'is-this-a-chain';
            $PVendorNoRestaurantsExID = 'number-of-restaurants-in-the-chain';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorChainCodeExID = 'chain-code';
            $PVendorCuisineExID = 'cuisine-3';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorNotesExID = 'comments';
            $PVendorActivationDateExID = 'vendor-activation-date';
            $PVendorSFIDExID = 'sf-id';
            //Contact
            $ContactAppID = 16346515;
            $ContactSavedViewID = 30917094;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            $PVendorSFIDExID = 'salesforce-id';
            //Contract
            $ContractAppID = 16346514;
            $ContractSavedViewID = 30917104;
            $PContractSpaceCountry = "Bangladesh";
            $PContractCompanyNameExID = 'vendor-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractTypeExID = 'contract-type-2';
            $PContractStatusExID = 'contract-status';
            $PContractOrderMethodExID = 'order-method';
            $PContractStartDateExID = 'contract-start-date';
            $PContractEndDateExID = 'contract-end-date';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractOPFeeExID = 'online-payment-transaction-fee';
            $PContractDeliveryTypeExID = 'delivery-type';
            $PContractDeliveryChargeExID = 'delivery-charge';
            $PContractActivationDateExID = 'activation-date';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractApprovalDateExID = 'approval-date';
            $PContractSFIDExID = 'salesforce-id';
        }
        if ($spaceID == $BulgariaSpaceID) {
            //Vendor
            $VendorAppID = 15888560;
            $VendorSavedViewID = 30916884;
            $PContractSpaceCountry = "Bulgaria";
            $AccountCurrency = "BGN - Bulgarian Lev";
            $PVendorNameExID = 'title';
            $VendorHQBuildingNameExID = 'hq-building-name';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            unset($PVendorAreaDistrictExID);
            $PVendorAreaDistrictTEXTExID = 'areadistrict';
            $PVendorWebsiteExID = 'website';
            $PVendorFacebookExID = 'facebook';
            $PVendorPartnerTypeExID = 'vendor-type';
            $PVendorChainExID = 'chain';
            $PVendorNoRestaurantsExID = 'number-of-branches';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorChainCodeExID = 'chain-code';
            $PVendorCuisineExID = 'cuisine-2';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorActivationDateExID = 'activation-date';
            $PVendorNotesExID = 'comments';
            $PVendorAPPriorityExID = 'am-priority';
            //Contact
            $ContactAppID = 15888563;
            $ContactSavedViewID = 30916890;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            //Contract
            $ContractAppID = 15888561;
            $ContractSavedViewID = 30916948;
            $PContractCompanyNameExID = 'vendor-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractTypeExID = 'contract-type-2';
            $PContractStatusExID = 'contract-status';
            $PContractOrderMethodExID = 'order-method';
            $PContractStartDateExID = 'contract-start-date';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractMinimumOrderExID = 'minimum-order-value';
            $PContractOPFeeExID = 'online-payment-transaction-fee';
            $PContractDeliveryTypeExID = 'delivery-type';
            $PContractDeliveryChargeExID = 'delivery-charge';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractApprovalDateExID = 'approval-date';
            $PContractActivationDateExID = 'contract-activation-date';
        }
        if ($spaceID == $BulgariaCorporateID) {
            //Vendor
            $VendorAppID = 15930760;
            $VendorSavedViewID = 31021254;
            $PContractSpaceCountry = "Bulgaria Corporate";
            $AccountCurrency = "BGN - Bulgarian Lev";
            $PVendorNameExID = 'title';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorAddressExID = 'address';
            unset($PVendorAreaDistrictExID);
            $PVendorAreaDistrictTEXTExID = 'areadistrict';
            $PVendorCompanyScopeExID = 'company-scope';
            $VendorIndustryExID = 'industry';
            $VendorNoEmployeesExID = 'no-of-employees';
            $PVendorNotesExID = 'comments';
            //Contact
            $ContactAppID = 15930764;
            $ContactSavedViewID = 31554455;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
        }
        if ($spaceID == $GeorgiaSpaceID) {
            //Vendor
            $VendorAppID = 15842653;
            $VendorSavedViewID = 30919950;
            $PContractSpaceCountry = "Georgia";
            $AccountCurrency = "GEL - Georgia Lari";
            $PVendorNameExID = 'title';
            $VendorHQBuildingNameExID = 'hq-building-name';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            unset($PVendorAreaDistrictExID);
            $PVendorAreaDistrictTEXTExID = 'areadistrict';
            $PVendorWebsiteExID = 'website';
            $PVendorFacebookExID = 'facebook';
            $PVendorPartnerTypeExID = 'vendor-type';
            $PVendorCuisineExID = 'cuisine-2';
            $PVendorChainExID = 'chain';
            $PVendorNoRestaurantsExID = 'number-of-branches';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorChainCodeExID = 'chain-code';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorActivationDateExID = 'vendor-activation-date';
            $PVendorNotesExID = 'comments';
            //Contact
            $ContactAppID = 15842651;
            $ContactSavedViewID = 31410101;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            //Contract
            $ContractAppID = 15842654;
            $ContractSavedViewID = 30919976;
            $PContractCompanyNameExID = 'vendor-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractTypeExID = 'contract-type-2';
            $PContractStatusExID = 'contract-status';
            $PContractStartDateExID = 'contract-start-date';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractOPFeeExID = 'online-payment-transaction-fee';
            $PContractExclusivityExID = 'exclusivity';
            $PContractOrderMethodExID = 'order-method';
            $PContractDeliveryTypeExID = 'delivery-type';
            $PContractDeliveryChargeExID = 'delivery-charge';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractApprovalDateExID = 'approval-date';
            $PContractActivationDateExID = 'contract-activation-date';
        }
        if ($spaceID == $HungarySpaceID) {
            //Vendor
            $VendorAppID = 15554976;
            $VendorSavedViewID = 30920018;
            $PContractSpaceCountry = "Hungary";
            $AccountCurrency = "HUF - Hungarian Forint";
            $PVendorNameExID = 'title';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorNewItemExID = 'new-item';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            unset($PVendorAreaDistrictExID);
            $PVendorAreaDistrictTEXTExID = 'areadistrict';
            $PVendorWebsiteExID = 'website';
            $PVendorFacebookExID = 'facebook';
            $PVendorCuisineExID = 'cuisine-2';
            $PVendorNoRestaurantsExID = 'number-of-branches';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorActivationDateExID = 'vendor-activation-date';
            $PVendorNotesExID = 'comments';
            //Contact
            $ContactAppID = 15554975;
            $ContactSavedViewID = 30920033;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            //Contract
            $ContractAppID = 15554972;
            $ContractSavedViewID = 29009123;
            $PContractTypeExID = 'contract-type-2';
            $PContractCompanyNameExID = 'vendor-name';
            $PContractCompanyLegalNameExID = 'company-legal-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractStatusExID = 'contract-status';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractStartDateExID = 'contract-start-date';
            $PContractOPFeeExID = 'online-payment-transaction-fee';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractApprovalDateExID = 'approval-date';
            $PContractActivationDateExID = 'contract-activation-date';
        }
        if ($spaceID == $PakistanSpaceID) {
            //Vendor
            $AccountCurrency = "PKR - Pakistani Rupee";
            $VendorAppID = 16413624;
            $VendorSavedViewID = 30920194;
            $PContractSpaceCountry = "Pakistan";
            $PVendorNameExID = 'title';
            $VendorHQBuildingNameExID = 'building-hq-name';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorAPPriorityExID = 'am-priority';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            unset($PVendorAreaDistrictExID);
            $PVendorAreaDistrictTEXTExID = 'areadistrict';
            $PVendorOpeningTimeExID = 'opening-times';
            $PVendorFacebookExID = 'facebook';
            $PVendorOrderCapacityExID = 'order-capacity';
            $PVendorVATincludedExID = 'is-vat-included-in-the-menu-prices';
            $PVendorPartnerTypeExID = 'partner-type';
            $PVendorCuisineExID = 'cuisine-2';
            $PVendorChainExID = 'is-this-restaurant-part-of-a-chain';
            $PVendorNoRestaurantsExID = 'number-of-restaurants-in-the-chain';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorChainCodeExID = 'chain-code';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorNotesExID = 'comments';
            $PVendorActivationDateExID = 'vendor-activation-date';
            $PVendorSFIDExID = 'sf-id';
            //Contact
            $ContactAppID = 16413617;
            $ContactSavedViewID = 30920202;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            $PContactSFIDExID = 'salesforce-id';
            //Contract
            $ContractAppID = 16413616;
            $ContractSavedViewID = 30059393;
            $PContractCompanyNameExID = 'vendor-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractTypeExID = 'contract-type-2';
            $PContractStatusExID = 'contract-status';
            $PContractStartDateExID = 'contract-start-date';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractOrderMethodExID = 'order-method';
            $PContractOPFeeExID = 'online-payment-transaction-fee';
            $PContractDeliveryTypeExID = 'delivery-type';
            $PContractDeliveryChargeExID = 'delivery-charge';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractCreatedDateExID = 'created-date';
            $PContractApprovalDateExID = 'approval-date';
            $PContractActivationDateExID = 'activation-date';
            $PContractSFIDExID = 'salesforce-id';
        }
        if ($spaceID == $RomaniaSpaceID) {
            //Vendor
            $AccountCurrency = "RON - Romanian Leu";
            $PContractSpaceCountry = "Romania";
            $VendorAppID = 15758358;
            $VendorSavedViewID = 30920291;
            $PVendorNameExID = 'title';
            $VendorHQBuildingNameExID = 'hq-building-name';
            $PVendorRecordOwnerExID = 'record-owner-4';
            $PVendorStatusExID = 'vendor-status';
            $PVendorGradeExID = 'vendor-grade';
            $PVendorAddressExID = 'address';
            $PVendorWebsiteExID = 'website';
            $PVendorFacebookExID = 'facebook';
            $PVendorChainExID = 'chain';
            $PVendorPartnerTypeExID = 'vendor-type';
            $PVendorNoRestaurantsExID = 'number-of-branches';
            $PVendorOnlinePaymentExID = 'online-payment';
            $PVendorCuisineExID = 'cuisine-2';
            $PVendorBackendCodeExID = 'vendor-backend-code';
            $PVendorChainCodeExID = 'chain-code';
            $PVendorFrontendURLExID = 'frontend-url';
            $PVendorReasonforDeactivationExID = 'reason-for-deactivation';
            $PVendorActivationDateExID = 'vendor-activation-date';
            $PVendorNotesExID = 'comments';
            //Contact
            $ContactAppID = 15785360;
            $ContactSavedViewID = 30920347;
            $PContactNameExID = 'title';
            $PContactVendorItemExID = 'vendor-name';
            $PContactJobTitleExID = 'job-title-2';
            $PContactEmailExID = 'email';
            $PContactPhoneNumberExID = 'phone-number-2';
            //Contract
            $ContractAppID = 15785359;
            $ContractSavedViewID = 30920350;
            $PContractCompanyNameExID = 'vendor-name';
            $PContractRecordOwnerExID = 'record-owner';
            $PContractTypeExID = 'contract-type-2';
            $PContractStatusExID = 'contract-status';
            $PContractStartDateExID = 'contract-start-date';
            $PContractCommissionRateExID = 'commission-percentage-rate';
            $PContractOPFeeExID = 'online-payment-commission';
            $PContractOrderMethodExID = 'order-method';
            $PContractDeliveryTypeExID = 'delivery-type';
            $PContractDeliveryChargeExID = 'delivery-charge';
            $PContractSpecialTermsExID = 'special-terms';
            $PContractApprovalDateExID = 'approval-date';
            $PContractActivationDateExID = 'contract-activation-date';
        }


        //Create Field Arrays for New Podio Items
        $AccountItemFieldsArray = array('fields' => array('preferred-contact-language' => "English"));
        $ContactItemFieldsArray = array('fields' => array());
        $ContractsItemFieldsArray = array('fields' => array());

        $VendorItems = PodioItem::filter_by_view((int)$VendorAppID, (int)$VendorSavedViewID, array('limit' => 500));
        foreach ($VendorItems as $vendoritem) {
            $AccountItemFieldsArray = array('fields' => array('preferred-contact-language' => "English"));
            //Podio Vendor Item Values;
            $SFStatus = "";
            $PVendorUniqueID = '';
            $PVendorCreatedOnFormatted = '';
            $PVendorCreatedByName = '';
            $PVendorName = '';
            $PVendorHQBuildingName = '';
            $PVendorRecordOwner = '';
            $PVendorStatus = '';
            $PVendorGrade = '';
            $PVendorAddressStreet = '';
            $PVendorAddressPostalCode = '';
            $PVendorAddressCity = '';
            $PVendorAddressState = '';
            $PVendorAddressCountry = '';
            $PVendorAreaDistrict = '';
            $PVendorWebsite = '';
            $PVendorFacebook = '';
            $PVendorPartnerType = '';
            $PVendorChain = '';
            $PVendorNumRestaurants = '';
            $PVendorBackendCode = '';
            $PVendorChainCode = '';
            $PVendorCuisine = '';
            $PVendorFrontendURL = '';
            $PVendorDeactivationReason = '';
            $PVendorActivationDateFormatted = '';
            $PVendorNotes = '';
            $PVendorTags = '';
            $PVendorPodioComments = '';
            $VendorTagsArray = "";
            $PVendorAPPriority = "";
            $VendorCuisineString = "";
            $VendorItemID = "";
            $VendorLocationLatitude = "";
            $VendorLocationLongitude = "";
            $PVendorOnlinePayment = "";
            $PVendorVATIncluded = "";
            $PVendorOrderCapacity = "";
            $PVendorActivationDate = "";
            $PVendorSFID = "";
            $PVendorMenuQuality = "";
            $VendorCompanyScope = "";
            $VendorIndustry = "";
            $VendorNoEmployees = "";


            //Get Vendor Item
            $VendorItemID = $vendoritem->item_id;
            $VendorItem = PodioItem::get((int)$VendorItemID);
            $PVendorUniqueID = $VendorItem->app_item_id_formatted;
            $PVendorCreatedOn = $VendorItem->created_on;
            $PVendorCreatedByUserID = $VendorItem->created_by->user_id;
            $PVendorCreatedByName = $VendorItem->created_by->name;
            if ($PVendorNameExID) {
                $PVendorName = $VendorItem->fields[$PVendorNameExID]->values;
            }
            if ($VendorHQBuildingNameExID) {
                $PVendorHQBuildingName = $VendorItem->fields[$VendorHQBuildingNameExID]->values[0]->title;
            }
            if ($PVendorRecordOwnerExID) {
                $PVendorRecordOwner = $VendorItem->fields[$PVendorRecordOwnerExID]->values[0]->name;
            }
            if ($PVendorStatusExID) {
                $PVendorStatus = $VendorItem->fields[$PVendorStatusExID]->values[0]['text'];
            }
            if ($PVendorAPPriorityExID) {
                $PVendorAPPriority = $VendorItem->fields[$PVendorAPPriorityExID]->values[0]['text'];
            }
            if ($PVendorGradeExID) {$PVendorGrade = $VendorItem->fields[$PVendorGradeExID]->values[0]['text'];}
            ///////
            if ($VendorIndustryExID) {$VendorIndustry = $VendorItem->fields[$VendorIndustryExID]->values[0]['text'];}
            if ($PVendorCompanyScopeExID) {$VendorCompanyScope = $VendorItem->fields[$PVendorCompanyScopeExID]->values[0]['text'];}
            if ($VendorNoEmployeesExID) {$VendorNoEmployees = $VendorItem->fields[$VendorNoEmployeesExID]->values[0]['text'];}
            ////////
            if ($PVendorAddressExID) {
                $PVendorFullAddress = $VendorItem->fields[$PVendorAddressExID]->values['formatted'];
                $PVendorAddressStreet = $VendorItem->fields[$PVendorAddressExID]->values['street_address'];
                $PVendorAddressPostalCode = $VendorItem->fields[$PVendorAddressExID]->values['postal_code'];
                $PVendorAddressCity = $VendorItem->fields[$PVendorAddressExID]->values['city'];
                $PVendorAddressState = $VendorItem->fields[$PVendorAddressExID]->values['state'];
                $PVendorAddressCountry = $VendorItem->fields[$PVendorAddressExID]->values['country'];
                //Format Address Get Longitude & Latitude
                $prepAddr = str_replace(' ', '+', $PVendorFullAddress);
                $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
                $output = json_decode($geocode);
                $VendorLocationLatitude = $output->results[0]->geometry->location->lat;
                $VendorLocationLongitude = $output->results[0]->geometry->location->lng;
            }
            if ($PVendorAreaDistrictExID) {
                $PVendorAreaDistrict = $VendorItem->fields[$PVendorAreaDistrictExID]->values[0]['text'];
            }
            if ($PVendorMenuQualityExID) {
                $PVendorMenuQuality = $VendorItem->fields[$PVendorMenuQualityExID]->values[0]['text'];
            }
            if ($PVendorWebsiteExID) {
                $PVendorWebsite = $VendorItem->fields[$PVendorWebsiteExID]->values[0]->resolved_url;
            }
            if ($PVendorFacebookExID) {
                $PVendorFacebook = $VendorItem->fields[$PVendorFacebookExID]->values[0]->resolved_url;
            }
//            if ($PVendorOrderCapacityExID) {
//                $PVendorOrderCapacity = $VendorItem->fields[$PVendorOrderCapacityExID]->values;
//            }
//            if ($PVendorVATincludedExID) {
//                $PVendorVATIncluded = $VendorItem->fields[$PVendorVATincludedExID]->values[0]['text'];
//            }
//            if ($PVendorOnlinePaymentExID) {
//                $PVendorOnlinePayment = $VendorItem->fields[$PVendorOnlinePaymentExID]->values[0]['text'];
//            }
            if ($PVendorPartnerTypeExID) {
                $PVendorPartnerType = $VendorItem->fields[$PVendorPartnerTypeExID]->values[0]['text'];
            }
            if ($PVendorChainExID) {
                $PVendorChain = $VendorItem->fields[$PVendorChainExID]->values[0]['text'];
            }
            if ($PVendorNoRestaurantsExID) {
                $PVendorNumRestaurants = $VendorItem->fields[$PVendorNoRestaurantsExID]->values;
            }
            if ($PVendorBackendCodeExID) {
                $PVendorBackendCode = $VendorItem->fields[$PVendorBackendCodeExID]->values;
            }
            if ($PVendorChainCodeExID) {
                $PVendorChainCode = $VendorItem->fields[$PVendorChainCodeExID]->values;
            }
            if ($PVendorCuisineExID) {
                $PVendorCuisine = $VendorItem->fields[$PVendorCuisineExID]->values;
                $VendorCuisineString = "";
                $count = 0;
                foreach ($PVendorCuisine as $cuisine) {
                    $count++;
                    $CuisineValue = $cuisine['text'];
                    $VendorCuisineString .= $CuisineValue . "; ";
                    if($count = 5){break;}
                }
            }
            if ($PVendorFrontendURLExID) {
                $PVendorFrontendURL = $VendorItem->fields[$PVendorFrontendURLExID]->values[0]->resolved_url;
            }
            if ($PVendorReasonforDeactivationExID) {
                $PVendorDeactivationReason = $VendorItem->fields[$PVendorReasonforDeactivationExID]->values[0]['text'];
            }
            if ($PVendorActivationDateExID) {
                $PVendorActivationDate = $VendorItem->fields[$PVendorActivationDateExID]->start;
            }
            if ($PVendorNotesExID) {
                $PVendorNotes = $VendorItem->fields[$PVendorNotesExID]->values;
            }
            if ($PVendorSFIDExID) {$PVendorSFID = $VendorItem->fields[$PVendorSFIDExID]->values;}
            if ($PVendorAreaDistrictTEXTExID) {$PVendorAreaDistrict = $VendorItem->fields[$PVendorAreaDistrictTEXTExID]->values;}
            $PVendorTags = $VendorItem->tags;


            //Chain N Backend Code
//            if($PVendorPartnerType == "Headquarters" && $PVendorChain == "Yes"){$PVendorBackendCode = "";}
//            if($PVendorPartnerType == "Restaurant" && $PVendorChain == "No"){$PVendorChainCode = "";}
//            if(stripos($PVendorFacebook, "facebook") == FALSE);{$PVendorFacebook = "";}
//            if(stripos($PVendorFrontendURL, "facebook") !== FALSE || strpos($PVendorFrontendURL, "foodpanda") !== FALSE);{unset($PVendorFrontendURL);}




            //Format Dates
            if ($PVendorCreatedOn) {
                $PVendorCreatedOnFormatted = date_format($PVendorCreatedOn, "m-d-Y");
                $PVendorCreatedOnFormatted = str_replace("-", "/", $PVendorCreatedOnFormatted);
            }
            if ($PVendorActivationDate) {
                $PVendorActivationDateFormatted = date_format($PVendorActivationDate, "m-d-Y");
                $PVendorActivationDateFormatted = str_replace("-", "/", $PVendorActivationDateFormatted);
            }



            //Get / Set / Format Item Comments
            unset($VendorPodioComment);
            unset($VendorItemCommentsArray);
            unset($VendorTagsArray);
            $VendorPodioComment = "";
            $VendorTagsArray = "";
            $VendorItemCommentsArray = "";

            //Get / Set / Format Item Tags
            foreach ($PVendorTags as $tag) {
                $TagValue = $tag['tag'];
                if ($TagValue) {
                    $VendorTagsArray .= $TagValue;
                }
            }

            //Get / Set / Format Item Comments
            $VendorComments = PodioComment::get_for('item', (int)$VendorItemID);
            foreach ($VendorComments as $comment) {
                unset($CommentCreatedOn);
                unset($CommentValue);
                unset($CommentCreatedBy);
                unset($CommentID);
                unset($VendorCommentString);
                $CommentID = $comment->comment_id;
                $CommentValue = $comment->value;
                $CommentCreatedBy = $comment->created_by->name;
                $CommentCreatedOn = $comment->created_on;
                $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                $VendorCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                $VendorItemCommentsArray .= $VendorCommentString;
            }

            //Combine Tags, Comments And Notes into Single Notes Value
            if ($PVendorNotes) {
                $VendorPodioComment .= "**Notes:** $PVendorNotes\n";
            }
            if ($VendorItemCommentsArray) {
                $VendorPodioComment .= "**Podio Item Comments-**\n $VendorItemCommentsArray\n";
            }
            if ($VendorTagsArray) {
                $VendorPodioComment .= "**Podio Tags:** $VendorTagsArray\n";
            }


            //Get File Info
            unset($AccountFileIDsArray);
            unset($AccountItemFiles);
            unset($AccountFileLinksArray);
            unset($AccountFileIDsArray);
            unset($NewAccountItemID);

            $AccountFileIDsArray = array();
            $AccountFileLinksArray = "";
            $AccountItemFiles = $VendorItem->files;
            foreach ($AccountItemFiles as $file) {
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
                        $AccountFileLinksArray .= $NewFileLink . "\n";
                    }
                    if ($NewFileID) {
                        array_push($AccountFileIDsArray, $NewFileID);
                    }
                }
            }


            //Add Values to Account Item Array
            if ($PVendorUniqueID) {
                $AccountItemFieldsArray['fields']['accounts-podio-unique-id'] = (string)$PVendorUniqueID;
            }
            if ($PVendorName) {
                $AccountItemFieldsArray['fields']['title'] = $PVendorName;
            }
            if ($PVendorHQBuildingName) {
                $AccountItemFieldsArray['fields']['building-name-hq'] = $PVendorHQBuildingName;
            }
            if ($PVendorCreatedOnFormatted) {
                $AccountItemFieldsArray['fields']['created-date'] = (string)$PVendorCreatedOnFormatted;
            }
            if ($PVendorCreatedByName) {
                $AccountItemFieldsArray['fields']['created-by'] = $PVendorCreatedByName;
            }
            if ($PVendorAddressCountry) {
                $AccountItemFieldsArray['fields']['country'] = $PVendorAddressCountry;
            }
            if ($PVendorAddressCity) {
                $AccountItemFieldsArray['fields']['text'] = $PVendorAddressCity;
            }
            if ($PVendorStatus) {$AccountItemFieldsArray['fields']['account-status'] = $PVendorStatus;}
            if ($VendorIndustry) {$AccountItemFieldsArray['fields']['industry'] = $VendorIndustry;}
            if ($VendorCompanyScope) {$AccountItemFieldsArray['fields']['company-scope'] = $VendorCompanyScope;}
            if ($VendorNoEmployees) {$AccountItemFieldsArray['fields']['no-of-employees'] = $VendorNoEmployees;}
            if ($PVendorGrade) {$AccountItemFieldsArray['fields']['aaa'] = $PVendorGrade;}
            if ($PVendorAPPriority) {$AccountItemFieldsArray['fields']['am-priority'] = $PVendorAPPriority;}
            if ($PVendorDeactivationReason) {$AccountItemFieldsArray['fields']['reason-for-deactivation'] = $PVendorDeactivationReason;}
            if ($PVendorChainCode) {
                $PVendorChainCode = preg_replace('/[^\w]/', '', $PVendorChainCode);
                $PVendorChainCode = substr($PVendorChainCode, 0, 5);
                $AccountItemFieldsArray['fields']['text-2'] = $PVendorChainCode;
            }
            if ($PVendorBackendCode) {
                $PVendorBackendCode = preg_replace('/[^\w]/', '', $PVendorBackendCode);
                $PVendorBackendCode = substr($PVendorBackendCode, 0, 4);
                $AccountItemFieldsArray['fields']['vendor-backend-code'] = $PVendorBackendCode;}
            if ($PVendorAddressStreet) {$AccountItemFieldsArray['fields']['street-name'] = $PVendorAddressStreet;}
            if ($PVendorAreaDistrict) {$AccountItemFieldsArray['fields']['areadistrict'] = $PVendorAreaDistrict;}
            if ($PVendorAddressPostalCode) {$AccountItemFieldsArray['fields']['post-code'] = $PVendorAddressPostalCode;}
            if ($PVendorWebsite) {$AccountItemFieldsArray['fields']['website-url'] = $PVendorWebsite;}
            if ($PVendorFacebook) {$AccountItemFieldsArray['fields']['facebook-url'] = $PVendorFacebook;}
            if ($PVendorPartnerType) {$AccountItemFieldsArray['fields']['partn'] = $PVendorPartnerType;}
            if ($PVendorChain) {$AccountItemFieldsArray['fields']['chain'] = $PVendorChain;}
            if ($VendorCuisineString) {$AccountItemFieldsArray['fields']['cuisine'] = (string)$VendorCuisineString;}
            if ($PVendorNumRestaurants) {
                $PVendorNumRestaurants = round($PVendorNumRestaurants, 2);
                $AccountItemFieldsArray['fields']['no-of-restaurants'] = (string)$PVendorNumRestaurants;}
            if ($VendorPodioComment) {$AccountItemFieldsArray['fields']['comments'] = (string)$VendorPodioComment;}
            if ($AccountFileLinksArray) {$AccountItemFieldsArray['fields']['account-item-file-links'] = (string)$AccountFileLinksArray;}
            if ($PVendorSFID) {$AccountItemFieldsArray['fields']['account-sf-id'] = (string)$PVendorSFID;}
            if ($VendorItemID) {
                $AccountItemFieldsArray['fields']['podio-account-item-id'] = (string)$VendorItemID;
                $AccountItemFieldsArray['fields']['vendor-item'] = (int)$VendorItemID;
            }
            if ($AccountCurrency) {$AccountItemFieldsArray['fields']['account-currency'] = $AccountCurrency;}
            if ($PVendorActivationDateFormatted) {$AccountItemFieldsArray['fields']['activation-date'] = (string)$PVendorActivationDateFormatted;}
            if ($VendorLocationLatitude) {$AccountItemFieldsArray['fields']['vendor-location-latitude'] = (string)$VendorLocationLatitude;}
            if ($VendorLocationLongitude) {$AccountItemFieldsArray['fields']['vendor-location-latitude-2'] = (string)$VendorLocationLongitude;}
            if ($PVendorOrderCapacity) {$AccountItemFieldsArray['fields']['vendor-order-capacity'] = (string)$PVendorOrderCapacity;}
            if($PVendorVATIncluded){$AccountItemFieldsArray['fields']['vat-included-in-menu-prices'] = (string)$PVendorVATIncluded;}
            if($PVendorOnlinePayment){$AccountItemFieldsArray['fields']['vendor-online-payment'] = $PVendorOnlinePayment;}
            if($PVendorMenuQuality){$AccountItemFieldsArray['fields']['vendor-menu-quality'] = $PVendorMenuQuality;}
            $AccountItemFieldsArray['fields']['workspace'] = (string)$PContractSpaceCountry;
            $AccountItemFieldsArray['fields']['sfstatus'] = "2B";

            $CreateAccountItem = PodioItem::create(17330412, $AccountItemFieldsArray);
            $NewAccountItemID = $CreateAccountItem->item_id;
            if($AccountFileIDsArray) {
                foreach($AccountFileIDsArray as $file_id) {
                    $AttachAccountFiles = PodioFile::attach($file_id, array('ref_type'=>'item', 'ref_id'=>(int)$NewAccountItemID));
                }
            }
        }





       // Contract Items
        $offset = 0;
        $i = 0;
        do {
            $offset = $i * 500;
            $ContractItems = PodioItem::filter_by_view($ContractAppID, $ContractSavedViewID, array('limit' => 500, 'offset'=>$offset));
            $count = count($ContractItems);
            foreach ($ContractItems as $contract) {
                $ContractsItemFieldsArray = array('fields' => array());
                //Podio Contract Item Values;
                unset($CreateContractItem);
                $PContractUniqueID = '';
                $ContractItem = '';
                $PContractItemID = '';
                $PContractType = '';
                $PContractVendorName = '';
                $PContractVendorItemID = '';
                $PContractRecordOwner = '';
                $PContractStatus = '';
                $PContractOrderMethod = '';
                $PContractStartDate = '';
                $PContractEndDate = '';
                $PContractCommissionRate = '';
                $PContractOPCommissionRate = '';
                $PContractMinimumOrder = '';
                $PContractDeliveryType = '';
                $PContractDeliveryCharge = '';
                $PContractActivationDate = '';
                $PContractSpecialTerms = '';
                $PContractApprovalDate = '';
                $PContractFiles = '';
                $PContractSFID = '';
                $PContractActivationDateFormatted = '';
                $PContractApprovalDateFormatted = '';
                $PContractStartDateFormatted = '';
                $PContractEndDateFormatted = '';
                $NewContractItemID = '';
                $ContractApproval = "";
                $AcceptOnlinePayment = "";
                //$SFStatus = "";


                $PContractItemID = $contract->item_id;
                $ContractItem = PodioItem::get($PContractItemID);
                $PContractUniqueID = $ContractItem->app_item_id_formatted;

                if ($PContractCompanyNameExID) {
                    $PContractVendorName = $ContractItem->fields[$PContractCompanyNameExID]->values[0]->title;
                    $PContractVendorItemID = $ContractItem->fields[$PContractCompanyNameExID]->values[0]->item_id;
                }
                if ($PContractRecordOwnerExID) {
                    $PContractRecordOwner = $ContractItem->fields[$PContractRecordOwnerExID]->values[0]->name;
                }
                if ($PContractTypeExID) {
                    $PContractType = $ContractItem->fields[$PContractTypeExID]->values[0]['text'];
                }
                if ($PContractStatusExID) {
                    $PContractStatus = $ContractItem->fields[$PContractStatusExID]->values[0]['text'];
                }
                if ($PContractStartDateExID) {
                    $PContractStartDate = $ContractItem->fields[$PContractStartDateExID]->start;
                }
                if ($PContractEndDateExID) {
                    $PContractEndDate = $ContractItem->fields[$PContractEndDateExID]->start;
                }
                if ($PContractOrderMethodExID) {
                    $PContractOrderMethod = $ContractItem->fields[$PContractOrderMethodExID]->values[0]['text'];
                }
                if ($PContractCommissionRateExID) {
                    $PContractCommissionRate = $ContractItem->fields[$PContractCommissionRateExID]->values;
                }
                if ($PContractOPFeeExID) {
                    $PContractOPCommissionRate = $ContractItem->fields[$PContractOPFeeExID]->values;
                }
                if ($PContractDeliveryTypeExID) {
                    $PContractDeliveryType = $ContractItem->fields[$PContractDeliveryTypeExID]->values[0]['text'];
                }
                if ($PContractDeliveryChargeExID) {
                    $PContractDeliveryCharge = $ContractItem->fields[$PContractDeliveryChargeExID]->values;
                }
                if ($PContractActivationDateExID) {
                    $PContractActivationDate = $ContractItem->fields[$PContractActivationDateExID]->start;
                }
                if ($PContractSpecialTermsExID) {
                    $PContractSpecialTerms = $ContractItem->fields[$PContractSpecialTermsExID]->values;
                }
                if ($PContractMinimumOrderExID) {
                    $PContractMinimumOrder = $ContractItem->fields[$PContractMinimumOrderExID]->values;
                }
                if ($PContractApprovalDateExID) {
                    $PContractApprovalDate = $ContractItem->fields[$PContractApprovalDateExID]->start;
                }
                if ($PContractSFIDExID) {$PContractSFID = $ContractItem->fields[$PContractSFIDExID]->values;}
                $ContractItemTags = $ContractItem->tags;


                //Format Dates
                if ($PContractActivationDate) {
                    $PContractActivationDateFormatted = date_format($PContractActivationDate, "m-d-Y");
                    $PContractActivationDateFormatted = str_replace("-", "/", $PContractActivationDateFormatted);
                }
                if ($PContractApprovalDate) {
                    $PContractApprovalDateFormatted = date_format($PContractApprovalDate, "m-d-Y");
                    $PContractApprovalDateFormatted = str_replace("-", "/", $PContractApprovalDateFormatted);
                }
                if ($PContractStartDate) {
                    $PContractStartDateFormatted = date_format($PContractStartDate, "m-d-Y");
                    $PContractStartDateFormatted = str_replace("-", "/", $PContractStartDateFormatted);
                }
                if ($PContractEndDate) {
                    $PContractEndDateFormatted = date_format($PContractEndDate, "m-d-Y");
                    $PContractEndDateFormatted = str_replace("-", "/", $PContractEndDateFormatted);
                }



                //Determin Approval Status
                if($PContractStatus = "Approved"){$ContractApproval = "YES";}
                if($PContractStatus = "Rejected"){$ContractApproval = "NO";}
                if($PContractStatus = "Submitted"){$ContractApproval = "Open";}

                //Set Default Variables
                $Exclusivity = "No";
                $ContractValidityPeriod = "Open End";
                $CommissionType = "Percentage";

                //Online Payment?
               if(!$PContractOPCommissionRate){$AcceptOnlinePayment =  "No";}
               if($PContractOPCommissionRate){$AcceptOnlinePayment =  "Yes";}


                //Format Status
                if ($PContractStatus == "Rejected" || $PContractStatus == "Submitted" || $PContractStatus == "Approved") {$PContractStatus = "Not Active";}
                if ($PContractStatus == "Inactive") {$PContractStatus = "Deactivated";}
                if ($PContractStatus == "Active") {$PContractStatus = "Active";}

                //Format Order Method
                if ($PContractOrderMethod == "GPRS Printer") {$PContractOrderMethod = "Order GPRS";}
                if ($PContractOrderMethod == "Vendor App") {$PContractOrderMethod = "Vendor App";}
                if ($PContractOrderMethod == "Phone") {$PContractOrderMethod = "Call center";}
                if ($PContractOrderMethod == "Notifier") {$PContractOrderMethod = "Order notifier";}
                if ($PContractOrderMethod == "Dispatcher") {$PContractOrderMethod = "Order Dispatcher";}
                if ($PContractOrderMethod == "Email") {$PContractOrderMethod = "Order GPRS";}
                if ($PContractOrderMethod == "Call center") {$PContractOrderMethod = "Call center";}
                if ($PContractOrderMethod == "SMS") {$PContractOrderMethod = "Order sms";}
                if ($PContractOrderMethod == "POS") {$PContractOrderMethod = "Order POS";}
                if ($PContractOrderMethod == "Fax") {$PContractOrderMethod = "Order  fax";}


                //Get / Set / Format Item Comments
                unset($ContractPodioComment);
                unset($ContractItemCommentsArray);
                unset($ContractTagsArray);
                $ContractPodioComment = "";
                $ContractTagsArray = "";
                $ContractItemCommentsArray = "";

                //Get / Set / Format Item Tags
                foreach ($ContractItemTags as $tag) {
                    $TagValue = $tag['tag'];
                    if ($TagValue) {
                        $ContractTagsArray .= $TagValue;
                    }
                }

                //Get / Set / Format Item Comments
                $ContractComments = PodioComment::get_for('item', (int)$PContractItemID);
                foreach ($ContractComments as $comment) {
                    unset($CommentCreatedOn);
                    unset($CommentID);
                    unset($CommentCreatedBy);
                    unset($CommentValue);
                    $CommentID = $comment->comment_id;
                    $CommentValue = $comment->value;
                    $CommentCreatedBy = $comment->created_by->name;
                    $CommentCreatedOn = $comment->created_on;
                    $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
                    $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
                    $ContractCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
                    $ContractItemCommentsArray .= $ContractCommentString;
                }

                //Combine Tags, Comments And Notes into Single Notes Value
                if ($ContractItemCommentsArray) {
                    $ContractPodioComment .= "**Podio Item Comments-**\n $ContractItemCommentsArray\n";
                }
                if ($ContractTagsArray) {
                    $ContractPodioComment .= "**Podio Tags:** $ContractTagsArray\n";
                }

                //Get File Info
                unset($ContractItemFiles);
                unset($ContractFileIDsArray);
                $ContractFileIDsArray = array();
                $ContractFileLinksArray = "";
                $ContractItemFiles = $ContractItem->files;
                foreach ($ContractItemFiles as $file) {
                    unset($OrigFileID);
                    $OrigFileID = $file->file_id;
                    if ($OrigFileID) {
                        unset($CopiedFile);
                        $CopiedFile = PodioFile::copy($OrigFileID);
                        $NewFileID = $CopiedFile->file_id;
                        $NewFile = PodioFile::get($NewFileID);
                        $NewFileName = $NewFile->name;
                        $NewFileType = $NewFile->mimtype;
                        $NewFileSize = $NewFile->size;
                        $NewFileLink = $NewFile->link;
                        $NewFileContents = $NewFile->get_raw();
                        if ($NewFileLink) {
                            $ContractFileLinksArray .= $NewFileLink . "\n";
                        }
                        if ($NewFileID) {
                            array_push($ContractFileIDsArray, $NewFileID);
                        }
                    }
                }

                if ($PContractUniqueID) {
                    $ContractsItemFieldsArray['fields']['title'] = (string)$PContractUniqueID;
                }
                if ($PContractVendorName) {
                    $ContractsItemFieldsArray['fields']['companyu'] = (string)$PContractVendorName;
                }
                if ($PContractVendorItemID) {
                    $ContractsItemFieldsArray['fields']['vendor-item'] = (int)$PContractVendorItemID;
                }
                if ($PContractRecordOwner) {
                    $ContractsItemFieldsArray['fields']['contract-owner'] = $PContractRecordOwner;
                }
                if ($PContractType) {
                    $ContractsItemFieldsArray['fields']['opportunity-record-type'] = (string)$PContractType;
                }
                if ($PContractStatus) {
                    $ContractsItemFieldsArray['fields']['text'] = (string)$PContractStatus;
                }
                if ($PContractStartDateFormatted) {
                    $ContractsItemFieldsArray['fields']['contract-start-date'] = (string)$PContractStartDateFormatted;
                }
                if ($PContractEndDateFormatted) {
                    $ContractsItemFieldsArray['fields']['contract-end-date'] = (string)$PContractEndDateFormatted;
                }
                if ($PContractOrderMethod) {
                    $ContractsItemFieldsArray['fields']['order-transmission-method'] = (string)$PContractOrderMethod;
                }
                if ($PContractCommissionRate) {
                    $PContractCommissionRate = round($PContractCommissionRate, 2);
                    $ContractsItemFieldsArray['fields']['commission-rate-percentage'] = (string)$PContractCommissionRate;
                }
                if ($PContractOPCommissionRate) {
                    $PContractOPCommissionRate = round($PContractOPCommissionRate, 2);
                    $ContractsItemFieldsArray['fields']['online-pa'] = (string)$PContractOPCommissionRate;
                }
                if ($PContractMinimumOrder) {
                    $ContractsItemFieldsArray['fields']['min-order-value-1'] = (string)$PContractMinimumOrder;}
                if ($PContractDeliveryType) {
                    $ContractsItemFieldsArray['fields']['delivery-type'] = (string)$PContractDeliveryType;
                }
                if ($PContractDeliveryCharge) {
                    $PContractDeliveryCharge = round($PContractDeliveryCharge, 2);
                    $ContractsItemFieldsArray['fields']['delivery-charge-1'] = (string)$PContractDeliveryCharge;
                }
                if ($PContractActivationDateFormatted) {
                    $ContractsItemFieldsArray['fields']['contract-activation-date'] = (string)$PContractActivationDateFormatted;
                }
                if ($PContractSpecialTerms) {
                    $ContractsItemFieldsArray['fields']['special-terms'] = $PContractSpecialTerms;
                }
                if ($PContractApprovalDateFormatted) {
                    $ContractsItemFieldsArray['fields']['approval-date'] = (string)$PContractApprovalDateFormatted;
                }
                if ($ContractFileLinksArray) {
                    $ContractsItemFieldsArray['fields']['contract-file-links'] = $ContractFileLinksArray;
                }
                if ($PContractSFID) {
                    $ContractsItemFieldsArray['fields']['contracts-sf-id'] = (string)$PContractSFID;
                }
                if ($PContractItemID) {
                    $ContractsItemFieldsArray['fields']['contract-podio-item-id'] = (string)$PContractItemID;
                    $ContractsItemFieldsArray['fields']['contract-item'] = (int)$PContractItemID;

                }
                if ($ContractPodioComment) {
                    $ContractsItemFieldsArray['fields']['contract-item-notes'] = $ContractPodioComment;
                }
                $ContractsItemFieldsArray['fields']['workspace'] = (string)$PContractSpaceCountry;
                $ContractsItemFieldsArray['fields']['sfstatus'] = "2B";
                if($AcceptOnlinePayment){$ContractsItemFieldsArray['fields']['accept-online-payment'] = $AcceptOnlinePayment;}
                if($ContractApproval){$ContractsItemFieldsArray['fields']['approved'] = $ContractApproval;}


                //Create Contract Item
                $CreateContractItem = PodioItem::create(17330430, $ContractsItemFieldsArray);
                $NewContractItemID = $CreateContractItem->item_id;
                if ($ContractFileIDsArray) {
                    foreach ($ContractFileIDsArray as $file_id) {
                        $AttachContractFiles = PodioFile::attach($file_id, array('ref_type' => 'item', 'ref_id' => (int)$NewContractItemID));
                    }
                }
            } $i++;
        }while($count == 500);

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




//Vendor Items

//Contact Items
//        $offset = 0;
//        $i = 0;
//        do {
//            $offset = $i * 500;
//            if ($ContactAppID !== 15842651) {
//                $ContactItems = PodioItem::filter_by_view($ContactAppID, (int)$ContactSavedViewID, array('limit' => 500, 'offset'=>$offset));
//                $counts = count($ContactItems);
//                foreach ($ContactItems as $contactItem) {
//                    $ContactItemFieldsArray = array('fields' => array());
//                    $ContactItem = '';
//                    $ContactItemID = '';
//                    $PContactUniqueID = '';
//                    $PContactName = '';
//                    $PContactVendorItemID = '';
//                    $PContactVendorAccountOwner = '';
//                    $ContactVendorItem = '';
//                    $PContactJobTitle = '';
//                    $PContactEmail = '';
//                    $PContactPhoneNumber = '';
//                    $PContactVendorName = '';
//                    $ContactPodioSFID = '';
//                    $EmailAddresses = "";
//                    $PhoneNumbers = "";
//                    $ContactItemTags = '';
//                    $ContactPodioComment = '';
//                    $ContactItemFiles = '';
//                    $ContactTagsArray = '';
//                    $ContactItemCommentsArray = '';
//                    $ContactComments = '';
//                    $ContactFileIDsArray = '';
//                    $NewContactItemID = '';
//                    unset($ContactItemFieldsArray);
//                    unset($CreateContactItem);
//
//                    $ContactItemID = $contactItem->item_id;
//                    $ContactItem = PodioItem::get($ContactItemID);
//                    $PContactUniqueID = $ContactItem->app_item_id_formatted;
//                    if ($PContactNameExID) {
//                        $PContactName = $ContactItem->fields[$PContactNameExID]->values;
//                    }
//                    if ($PContactVendorItemExID) {
//                        $PContactVendorItemID = $ContactItem->fields[$PContactVendorItemExID]->values[0]->item_id;
//                        $PContactVendorName = $ContactItem->fields[$PContactVendorItemExID]->values[0]->title;
//                    }
//                    if ($PContactJobTitleExID) {
//                        $PContactJobTitle = $ContactItem->fields[$PContactJobTitleExID]->values;
//                    }
//                    if ($PContactEmailExID) {
//                        $PContactEmail = $ContactItem->fields[$PContactEmailExID]->values[0]['value'];
//                    }
//                    if ($PContactPhoneNumberExID) {
//                        $PContactPhoneNumber = $ContactItem->fields[$PContactPhoneNumberExID]->values[0]['value'];
//                    }
//                    if ($PContactSFIDExID) {
//                        $ContactPodioSFID = $ContactItem->fields[$PContactSFIDExID]->values;
//                    }
//                    $ContactItemTags = $ContactItem->tags;
//
//
//                    if ($PContactPhoneNumber) {
//                        $PContactPhoneNumber = str_replace("+", "", $PContactPhoneNumber);
//                        $PContactPhoneNumber = str_replace("-", "", $PContactPhoneNumber);
//                        $PContactPhoneNumber = str_replace("(", "", $PContactPhoneNumber);
//                        $PContactPhoneNumber = str_replace(")", "", $PContactPhoneNumber);
//                        $PContactPhoneNumber = str_replace(" ", "", $PContactPhoneNumber);
//                    }
//                    if ($PContactName) {
//                        $parts = explode(' ', $PContactName); // $meta->post_title
//                        $ContactFirstName = array_shift($parts);
//                        $ContactLastName = array_pop($parts);
//                        $ContactMiddleName = trim(implode(' ', $parts));
//                    }
//
//
//                    //Get / Set / Format Item Comments
//                    unset($ContactPodioComment);
//                    unset($ContactItemCommentsArray);
//                    unset($ContactTagsArray);
//                    $ContactPodioComment = "";
//                    $ContactTagsArray = "";
//                    $ContactItemCommentsArray = "";
//
//                    //Get / Set / Format Item Tags
//                    foreach ($ContactItemTags as $tag) {
//                        $TagValue = $tag['tag'];
//                        if ($TagValue) {
//                            $ContactTagsArray .= $TagValue;
//                        }
//                    }
//
//
//                    //Get / Set / Format Item Comments
//                    $ContactComments = PodioComment::get_for('item', (int)$ContactItemID);
//                    foreach ($ContactComments as $comment) {
//                        unset($CommentCreatedOn);
//                        $CommentID = $comment->comment_id;
//                        $CommentValue = $comment->value;
//                        $CommentCreatedBy = $comment->created_by->name;
//                        $CommentCreatedOn = $comment->created_on;
//                        $CommentCreatedOn = date_format($CommentCreatedOn, "m-d-Y");
//                        $CommentCreatedOn = str_replace("-", "/", $CommentCreatedOn);
//                        $ContactCommentString = "Created By: $CommentCreatedBy\n" . "Created On: $CommentCreatedOn\n" . "Comment: $CommentValue";
//                        $ContactItemCommentsArray .= $ContactCommentString;
//                    }
//
//                    //Combine Tags, Comments And Notes into Single Notes Value
//                    if ($ContactItemCommentsArray) {
//                        $ContactPodioComment .= "**Podio Item Comments-**\n $ContactItemCommentsArray\n";
//                    }
//                    if ($ContactTagsArray) {
//                        $ContactPodioComment .= "**Podio Tags:** $ContactTagsArray\n";
//                    }
//
//
//                    //Get File Info
//                    unset($ContactItemFiles);
//                    $ContactFileIDsArray = array();
//                    $ContactFileLinksArray = "";
//                    $ContactItemFiles = $ContactItem->files;
//                    foreach ($ContactItemFiles as $file) {
//                        unset($OrigFileID);
//                        $OrigFileID = $file->file_id;
//                        if ($OrigFileID) {
//                            unset($CopiedFile);
//                            $CopiedFile = PodioFile::copy($OrigFileID);
//                            $NewFileID = $CopiedFile->file_id;
//                            $NewFile = PodioFile::get($NewFileID);
//                            $NewFileName = $NewFile->name;
//                            $NewFileType = $NewFile->mimtype;
//                            $NewFileSize = $NewFile->size;
//                            $NewFileLink = $NewFile->link;
//                            $NewFileContents = $NewFile->get_raw();
//                            if ($NewFileLink) {
//                                $ContactFileLinksArray .= $NewFileLink . "\n";
//                            }
//                            if ($NewFileID) {
//                                array_push($ContactFileIDsArray, $NewFileID);
//                            }
//                        }
//                    }
//
//
//                    if ($PContactUniqueID) {
//                        $ContactItemFieldsArray['fields']['contacts-podio-unique-id-2'] = $PContactUniqueID;
//                    }
//                    if ($ContactItemID) {
//                        $ContactItemFieldsArray['fields']['podio-contact-item-id'] = (string)$ContactItemID;
//                    }
//                    if ($ContactFirstName && $ContactMiddleName) {
//                        $ContactFirstName = $ContactFirstName . " " . $ContactMiddleName;
//                    }
//                    if ($ContactFirstName) {
//                        $ContactItemFieldsArray['fields']['text'] = (string)$ContactFirstName;
//                    }
//                    if ($ContactLastName) {
//                        $ContactItemFieldsArray['fields']['last-name'] = $ContactLastName;
//                    }
//                    if ($PContactVendorName) {$ContactItemFieldsArray['fields']['text-4'] = $PContactVendorName;}
//                    if ($PContactJobTitle) {
//                        $ContactItemFieldsArray['fields']['job-title'] = $PContactJobTitle;
//                    }
//                    if ($PContactPhoneNumber) {
//                        $ContactItemFieldsArray['fields']['phone'] = (string)$PContactPhoneNumber;
//                    }
//                    if ($PContactEmail) {
//                        $ContactItemFieldsArray['fields']['email'] = $PContactEmail;
//                    }
//                    if ($PContactVendorItemID) {
//                        $ContactItemFieldsArray['fields']['related-vendor-item-id'] = (string)$PContactVendorItemID;
//                        $ContactItemFieldsArray['fields']['vendor-item'] = (int)$PContactVendorItemID;
//                    }
//                    if ($ContactPodioSFID) {
//                        $ContactItemFieldsArray['fields']['contact-sf-id'] = (string)$ContactPodioSFID;
//                    }
//                    if ($ContactFileLinksArray) {
//                        $ContactItemFieldsArray['fields']['contact-item-file-links'] = $ContactFileLinksArray;
//                    }
//                    if ($ContactPodioComment) {
//                        $ContactItemFieldsArray['fields']['contact-item-comments'] = (string)$ContactPodioComment;
//                    }
//                    $ContactItemFieldsArray['fields']['workspace'] = (string)$PContractSpaceCountry;
//                    $ContactItemFieldsArray['fields']['sfstatus'] = "2B";
//
//
//                    //Create Podio Contact Item
//                    $CreateContactItem = PodioItem::create(17330422, $ContactItemFieldsArray);
//                    $NewContactItemID = $CreateContactItem->item_id;
//                    if ($ContactFileIDsArray) {
//                        foreach ($ContactFileIDsArray as $file_id) {
//                            $AttachContactFiles = PodioFile::attach($file_id, array('ref_type' => 'item', 'ref_id' => (int)$NewContactItemID));
//                        }
//                    }
//                }
//            }$i++;
//        }while($counts == 500);







