<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/2/2016
 * Time: 3:49 PM
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

    $PSalesType = 'New Business';
    $PRenegotiationType = 'Commission Renegotiation';
    $POnlinePaymentType = 'New Business';

    $SpaceArray = array($BangladeshSpaceID, $BulgariaSpaceID, $HungarySpaceID, $PakistanSpaceID, $RomaniaSpaceID, $GeorgiaSpaceID); //

    foreach($SpaceArray as $spaceID) {
        //For Bangladesh Workspace
        $ContactAppID = "";
        $VendorAppID = "";
        $VendorSavedViewID = "";
        $ContactSavedViewID = "";
        $ContractAppID = '';
        $ContractSavedViewID = '';
        $BankDetailsAppID = "";
        $BankDetailsSavedViewID = "";
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

        if ($spaceID == $BulgariaSpaceID) {
            //Vendor
            $VendorAppID = 15888560;
            $VendorSavedViewID = 30916884;
            $PContractSpaceCountry = "Bulgaria";
            $AccountCurrency = "BGN - Bulgarian Lev";
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
            //Bank Details
            $BankDetailsAppID = 16015918;
            $BankDetailsSavedViewID = 30916980;
            $PBankDetailsVendorExID = 'contract';
            $PBankDetailsStatusExID = 'bank-details-status';
            $PBankDetailsCompanyRegistrationNOExID = 'company-registration-no';
            $PBankDetailsOfficialCompanyNameExID = 'official-company-name';
            $PBankDetailsBankNameExID = 'bank-name';
            $PBankDetailsIBANExID = 'iban';
            $PBankDetailsBICNumberExID = 'bic';
            $PBankDetailsVendorBackendCodeExID = 'vendor-backend-code';
            $PBankDetailsSFIDExID = "sf-id";
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
            $PVendorAreaDistrictExID = 'areadistrict';
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
        }


        $BankDetailsItemFieldsArray = array('fields' => array());

        //Bank Detail Items
        if($spaceID == $BulgariaSpaceID || $spaceID == $PakistanSpaceID){
            $BankDetailItems = PodioItem::filter_by_view($BankDetailsAppID, $BankDetailsSavedViewID, array('limit' => 50));
            foreach ($BankDetailItems as $bankdetail) {
                $PBankDetailsItemID = '';
                $PBankDetailsUniqueID = '';
                $PBankDetailsVendorItemID = '';
                $PBankDetailsVendorName = '';
                $PBankDetailsStatus = '';
                $PBankDetailsCompanyRegistrationNO = '';
                $PBankDetailsOfficialCompanyName = '';
                $PBankDetailsBankName = '';
                $PBankDetailsIBAN = '';
                $PBankBIC = '';
                $PBankVendorBackendCode = '';
                $PBankSFID = '';

                $PBankDetailsItemID = $bankdetail->item_id;
                $BankDetailItem = PodioItem::get($PBankDetailsItemID);
                $PBankDetailsUniqueID = $BankDetailItem->app_item_id_formatted;
                if ($PBankDetailsVendorExID) {
                    $PBankDetailsVendorItemID = $BankDetailItem->fields[$PBankDetailsVendorExID]->values[0]->item_id;
                    $PBankDetailsVendorName = $BankDetailItem->fields[$PBankDetailsVendorExID]->values[0]->title;
                }
                if ($PBankDetailsStatusExID) {
                    $PBankDetailsStatus = $BankDetailItem->fields[$PBankDetailsStatusExID]->values[0]['text'];
                }
                if ($PBankDetailsCompanyRegistrationNOExID) {
                    $PBankDetailsCompanyRegistrationNO = $BankDetailItem->fields[$PBankDetailsCompanyRegistrationNOExID]->values;
                }
                if ($PBankDetailsOfficialCompanyNameExID) {
                    $PBankDetailsOfficialCompanyName = $BankDetailItem->fields[$PBankDetailsOfficialCompanyNameExID]->values;
                }
                if ($PBankDetailsBankNameExID) {
                    $PBankDetailsBankName = $BankDetailItem->fields[$PBankDetailsBankNameExID]->values;
                }
                if ($PBankDetailsIBANExID) {
                    $PBankDetailsIBAN = $BankDetailItem->fields[$PBankDetailsIBANExID]->values;
                }
                if ($PBankDetailsBICNumberExID) {
                    $PBankBIC = $BankDetailItem->fields[$PBankDetailsBICNumberExID]->values;
                }
                if ($PBankDetailsVendorBackendCodeExID) {
                    $PBankVendorBackendCode = $BankDetailItem->fields[$PBankDetailsVendorBackendCodeExID]->values;
                }
                if ($PBankDetailsSFIDExID) {
                    $PBankSFID = $BankDetailItem->fields[$PBankDetailsSFIDExID]->values;
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