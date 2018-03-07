<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/14/2016
 * Time: 12:29 PM
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

    ///AUTOMATION START
    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    $ContractAppID = 17330430;
    $ContractCreateViewID = 31585373;
    $ContractUpdateViewID = 0;

    $GetListURL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_schema/Contract";
    $APIKey = "?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";
    $urlString = $GetListURL.$APIKey;

    $curl = new \Curl\Curl();

    $curl = $curl->get($urlString);

    $FieldLabelsArray = array();
    $FieldLNameArray = array();

    $ContractFields = $curl->fields;
    foreach($ContractFields as $field){
        $FieldLabel = $field->label;
        $FieldName = $field->name;
        array_push($FieldLNameArray, "'".$FieldName."' => '';");
        //array_push($FieldLNameArray, $FieldName);
    }

    print_r($FieldLNameArray);
    exit;


    $offset = 0;
    $i = 0;

    do{
        $ContractItems = PodioItem::filter_by_view($ContractAppID, $ContractCreateViewID, array('limit' => 500, 'offset' => $offset));
        $count = count($ContractItems);
        foreach($ContractItems as $contract) {
            $NewContractItemID = $contract->item_id;
            $Workspace = $contract->fields['workspace']->values[0]['text'];
            $SFtransferStatus = $contract->fields['sfstatus']->values[0]['text'];
            $Id = $contract->fields['title']->values;
            $AccountId = '';
            $CurrencyIsoCode = '';
            $OwnerExpirationNotice = '';
            $StartDate = $contract->fields['contract-start-date']->values;
            $EndDate = $contract->fields['contract-end-date']->values;;
            $BillingStreet = '';
            $BillingCity = '';
            $BillingState = '';
            $BillingPostalCode = '';
            $BillingCountry = '';
            $BillingLatitude = '';
            $BillingLongitude = '';
            $ShippingStreet = '';
            $ShippingCity = '';
            $ShippingState = '';
            $ShippingPostalCode = '';
            $ShippingCountry = '';
            $ShippingLatitude = '';
            $ShippingLongitude = '';
            $ContractTerm = '';
            $OwnerId = $contract->fields['account-ownder-sfid']->values;
            $Status = $contract->fields['text']->values;
            $CompanySignedId = '';
            $CompanySignedDate = '';
            $CustomerSignedId = '';
            $CustomerSignedTitle = '';
            $CustomerSignedDate = '';
            $SpecialTerms = $contract->fields['special-terms']->values;;
            $ActivatedById = '';
            $ActivatedDate = '';
            $StatusCode = '';
            $Description = '';
            $RecordTypeId = $contract->fields['contract-record-type']->values;
            $IsDeleted = '';
            $ContractNumber = '';
            $LastApprovedDate = '';
            $CreatedDate = '';
            $CreatedById = '';
            $LastModifiedDate = '';
            $LastModifiedById = '';
            $SystemModstamp = '';
            $LastActivityDate = '';
            $LastViewedDate = '';
            $LastReferencedDate = '';
            $Preferred_order_contact__c = '';
            $X1st_Step_Approval_Date__c = '';
            $Service_Fee__c = '';
            $Commission_Type__c = 'Percentage';
            $Registered_Tax_Number__c = '';
            $Accept_Online_Payment__c = '';
            $Delivery_Type__c = '';
            $Welcome_Package__c = '';
            $Opportunity_Name__c = '';
            $Account_Country__c = '';
            $Contract_Validity_Period__c = 'Open End';
            $Contract_End_Date__c = '';
            $Bank_Name__c = $contract->fields['bank-name']->values;
            $Bank_Account_Name__c = "";
            $Bank_Account_Number__c = $contract->fields['bank-account-number']->values;
            $IFSC__c = '';
            $Bank_Address__c = '';
            $Commission_Rate_Percentage__c = $contract->fields['commission-rate-percentage']->values;
            $Commission_Rate_Flat_Fee__c = "";
            $Company_Registration_Number__c = $contract->fields['bank-registration-number']->values;
            $BIC_number__c = $contract->fields['bic-number']->values;
            $Activation_Time_Elapsed__c = '';
            $Approval_Date__c = $contract->fields['approval-date']->values;
            $APPROVED__c = '';
            $Role_of_Contract_Owner__c = '';
            $Official_Company_Name__c =  $contract->fields['companyu']->values;
            $Approved_By__c = '';
            $Submitted_Date__c = '';
            $Exclusivity__c = 'No';
            $CustomerSigned__c = '';
            $of_QC_Reactivation__c = '';
            $of_QC_Sales__c = '';
            $of_TRSL_Reactivation__c = '';
            $of_TRSL_Sales__c = '';
            $Number_of_Attached_Contracts__c = '';
            $Number_of_Attached_Delivery_Areas__c = '';
            $Number_of_Attached_Menus__c = '';
            $POS_Provider__c = '';
            $Number_of_Bank_Details__c = '';
            $Approver__c = '';
            $POS_System__c = '';
            $Version__c = '';
            $Min_order_value__c = '';
            $VAT__c = '';
            $Payment_Cycle__c = '';
            $Allow__c = '';
            $of_OP_fee_covered_by_vendor__c = '';
            $X18_Char_ID__c = '';
            $Delivery_Charge__c = '';
            $Service_Charge__c = '';
            $Delivery_Area_1__c = '';
            $Min_Order_Value_1__c = '';
            $Delivery_Charge_1__c = '';
            $Delivery_Charge_2__c = '';
            $Delivery_Charge_3__c = '';
            $Delivery_Area_2__c = '';
            $Delivery_Area_3__c = '';
            $Min_Order_Value_2__c = '';
            $Min_Order_Value_3__c = '';
            $Monday__c = '';
            $Tuesday__c = '';
            $Wednesday__c = '';
            $Thursday__c = '';
            $Friday__c = '';
            $Saturday__c = '';
            $Sunday__c = '';
            $Service_Tax__c = '';
            $Max_Orders_Tier_1__c = '';
            $Max_Orders_Tier_2__c = '';$Max_Orders_Tier_3__c = '';
            $Commission_Percentage_Tier_1__c = '';
            $Commission_Percentage_Tier_2__c = '';
            $Commission_Percentage_Tier_3__c = '';
            $RU_Approval_Status__c = '';
            $Tier_1__c = '';
            $Tier_2__c = '';
            $Tier_3__c = '';
            $Online_Payment_Commission_Rate_Percentag__c = '';
            $Online_payment_commission_Includes_trans__c = '';
            $Vendor_App__c = '';
            $Title_of_Contract_Owner__c = '';
            $Reimbursement_Amount__c = '';
            $Weekly_Day__c = '';
            $Weekend_Order_Limit__c = '';
            $Holiday_Order_Limit__c = '';
            $Last_Rejected_By__c = '';
            $Rejection_Count__c = '';
            $Rejection_comments__c = '';
            $X1st_Step_Approver__c = '';
            $Food_Licence_Number__c = '';

            if (!$Email) {$Email = "noemailexists@email.com";}
            if (!$Title) {$Title = "Contract";}
            if (!$Description) {$Description = "Contract transfered from Podio records.";}


            if($Status == "Approved"){$APPROVED__c = "YES";}
            if($Status == "Approved" || !$Status){$RU_Approval_Status__c = "Approved";}
            if($Status == "Rejected"){$APPROVED__c = "NO";}
            if($Status == "Submitted"){$APPROVED__c = "Open";}


            $NewContractItemFieldsArray = array(
                'Id' => '',
                'AccountId' => '',
                'CurrencyIsoCode' => '',
                'OwnerExpirationNotice' => '',
                'StartDate' => '',
                'EndDate' => '',
                'BillingStreet' => '',
                'BillingCity' => '',
                'BillingState' => '',
                'BillingPostalCode' => '',
                'BillingCountry' => '',
                'BillingLatitude' => '',
                'BillingLongitude' => '',
                'ShippingStreet' => '',
                'ShippingCity' => '',
                'ShippingState' => '',
                'ShippingPostalCode' => '',
                'ShippingCountry' => '',
                'ShippingLatitude' => '',
                'ShippingLongitude' => '',
                'ContractTerm' => '',
                'OwnerId' => '',
                'Status' => '',
                'CompanySignedId' => '',
                'CompanySignedDate' => '',
                'CustomerSignedId' => '',
                'CustomerSignedTitle' => '',
                'CustomerSignedDate' => '',
                'SpecialTerms' => '',
                'ActivatedById' => '',
                'ActivatedDate' => '',
                'StatusCode' => '',
                'Description' => '',
                'RecordTypeId' => '',
                'IsDeleted' => '',
                'ContractNumber' => '',
                'LastApprovedDate' => '',
                'CreatedDate' => '',
                'CreatedById' => '',
                'LastModifiedDate' => '',
                'LastModifiedById' => '',
                'SystemModstamp' => '',
                'LastActivityDate' => '',
                'LastViewedDate' => '',
                'LastReferencedDate' => '',
                'Preferred_order_contact__c' => '',
                'X1st_Step_Approval_Date__c' => '',
                'Service_Fee__c' => '',
                'Commission_Type__c' => '',
                'Registered_Tax_Number__c' => '',
                'Accept_Online_Payment__c' => '',
                'Delivery_Type__c' => '',
                'Welcome_Package__c' => '',
                'Opportunity_Name__c' => '',
                'Account_Country__c' => '',
                'Contract_Validity_Period__c' => '',
                'Contract_End_Date__c' => '',
                'Bank_Name__c' => '',
                'Bank_Account_Name__c' => '',
                'Bank_Account_Number__c' => '',
                'IFSC__c' => '',
                'Bank_Address__c' => '',
                'Commission_Rate_Percentage__c' => '',
                'Commission_Rate_Flat_Fee__c' => '',
                'Company_Registration_Number__c' => '',
                'BIC_number__c' => '',
                'Activation_Time_Elapsed__c' => '',
                'Approval_Date__c' => '',
                'APPROVED__c' => '',
                'Role_of_Contract_Owner__c' => '',
                'Official_Company_Name__c' => '',
                'Approved_By__c' => '',
                'Submitted_Date__c' => '',
                'Exclusivity__c' => '',
                'CustomerSigned__c' => '',
                'of_QC_Reactivation__c' => '',
                'of_QC_Sales__c' => '',
                'of_TRSL_Reactivation__c' => '',
                'of_TRSL_Sales__c' => '',
                'Number_of_Attached_Contracts__c' => '',
                'Number_of_Attached_Delivery_Areas__c' => '',
                'Number_of_Attached_Menus__c' => '',
                'POS_Provider__c' => '',
                'Number_of_Bank_Details__c' => '',
                'Approver__c' => '',
                'POS_System__c' => '',
                'Version__c' => '',
                'Min_order_value__c' => '',
                'VAT__c' => '',
                'Payment_Cycle__c' => '',
                'Allow__c' => '',
                'of_OP_fee_covered_by_vendor__c' => '',
                'X18_Char_ID__c' => '',
                'Delivery_Charge__c' => '',
                'Service_Charge__c' => '',
                'Delivery_Area_1__c' => '',
                'Min_Order_Value_1__c' => '',
                'Delivery_Charge_1__c' => '',
                'Delivery_Charge_2__c' => '',
                'Delivery_Charge_3__c' => '',
                'Delivery_Area_2__c' => '',
                'Delivery_Area_3__c' => '',
                'Min_Order_Value_2__c' => '',
                'Min_Order_Value_3__c' => '',
                'Monday__c' => '',
                'Tuesday__c' => '',
                'Wednesday__c' => '',
                'Thursday__c' => '',
                'Friday__c' => '',
                'Saturday__c' => '',
                'Sunday__c' => '',
                'Service_Tax__c' => '',
                'Max_Orders_Tier_1__c' => '',
                'Max_Orders_Tier_2__c' => '',
                'Max_Orders_Tier_3__c' => '',
                'Commission_Percentage_Tier_1__c' => '',
                'Commission_Percentage_Tier_2__c' => '',
                'Commission_Percentage_Tier_3__c' => '',
                'RU_Approval_Status__c' => $RU_Approval_Status__c,
                'Tier_1__c' => '',
                'Tier_2__c' => '',
                'Tier_3__c' => '',
                'Online_Payment_Commission_Rate_Percentag__c' => '',
                'Online_payment_commission_Includes_trans__c' => '',
                'Vendor_App__c' => '',
                'Title_of_Contract_Owner__c' => '',
                'Reimbursement_Amount__c' => '',
                'Weekly_Day__c' => '',
                'Weekend_Order_Limit__c' => '',
                'Holiday_Order_Limit__c' => '',
                'Last_Rejected_By__c' => '',
                'Rejection_Count__c' => '',
                'Rejection_comments__c' => '',
                'X1st_Step_Approver__c' => '',
                'Food_Licence_Number__c' => ''
            );


            $ContractFields = [$NewContractItemFieldsArray];
            $ContractFieldsJSON = json_encode($ContractFields);


            $CreateContractCurl = curl_init();
            curl_setopt($CreateContractCurl, CURLOPT_URL, 'https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Contract');
            curl_setopt($CreateContractCurl, CURLOPT_HEADER, false);
            curl_setopt($CreateContractCurl, CURLOPT_POST, true);
            curl_setopt($CreateContractCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', "X-DreamFactory-Api-Key: 36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88", "X-DreamFactory-Session-Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjYsInVzZXJfaWQiOjYsImVtYWlsIjoiaXJvYmVydHNvbkB0ZWNoZWdvLmNvbSIsImZvcmV2ZXIiOmZhbHNlLCJpc3MiOiJodHRwczpcL1wvaG9pc3QudGhhdGFwcC5pb1wvYXBpXC92Mlwvc3lzdGVtXC9hZG1pblwvc2Vzc2lvbiIsImlhdCI6MTQ4MTU1ODk5NywiZXhwIjoxNDgyMTYzNzk3LCJuYmYiOjE0ODE1NTg5OTcsImp0aSI6Ijg4ZWU1OTFmMDdkNDA3NTllMWU0ZGZlNzFiYzM2NGRhIn0.CjgbSLTZT1D3FiI5lo9awACQIms3jWUTdJB_ZEgoPpY"));
            curl_setopt($CreateContractCurl, CURLOPT_POSTFIELDS, $ContractFieldsJSON);
            curl_setopt($CreateContractCurl, CURLOPT_RETURNTRANSFER, true);


            $CreateContractCurlResult = curl_exec($CreateContractCurl);
            $Result = json_decode($CreateContractCurlResult);

            print_r($Result);
            exit;


            curl_close($CreateContractCurl);


        }$i++;
    }while($count == 500);





    $HQBuildingNameField = 'Building NameNoHQ';
    $RecordOwnder = 'Record Owner';
    $Status = 'Contract Status';
    $Grade = 'Vendor Grade';
    $StreetName = 'Street Name';
    $PostCode = 'Post Code';
    $City = 'City';
    $State = 'No';
    $Country = 'Country';
    $AreaDistrict = 'Area/District';
    $WebsiteURL = 'Website URL';
    $FacebookURL = 'Facebook URL';
    $Chain = 'Chain';
    $NoRestaurants = 'No. of restaurants';
    $BackendCode = 'Vendor Backend Code';
    $ChainCode = 'Chain Code';
    $Cuisine = 'Cuisine';
    $FrontendURL = 'Frontend URL';
    $ReasonforDeactivation = 'Reason for Deactivation';
    $ActivatedDate = 'Activated Date';
    $Comments = 'Comments';
    $OpportunityCurrency = 'Opportunity Currency';
    /////////////////////////////////////////////////////////////////////////////////
    //Contract Salesforce Fields.....................................................
    $SFContractIDFieldName = 'Podio ID';
    $SFContractFirstNameFieldName = 'First Name';
    $SFContractLastNameFieldName = 'Last Name';
    $SFContractVendorNameFieldName = 'Company Name';
    $SFContractJobTitleFieldName = 'Job Title';
    $SFContractEmailFieldName = 'Email';
    $SFContractPhoneNumberFieldName = 'Phone Number';
    $SFContractMainContractFieldName = 'Main Contract';
    $SFContractRecordTypeFieldName = 'Record Type';
    $SFContractOwnerFieldName = 'Contract Owner';
    ////////////////////////////////////////////////////////////////////////////////////////////
    //Contract Salesforce Fields......................................................
    $SFContractIDFieldName = 'Podio ID';
    $SFContractOpportunityRecordTypeFieldName = 'Opportunity Record Type';
    $SFContractCompanyNameFieldName = 'Company Name';
    $SFContractOwnerFieldName = 'Contract Owner';
    $SFContractStatusFieldName = 'Status';
    $SFContractOrderMethodFieldName = 'order transmission method';
    $SFContractStartDateFieldName = 'Contract Start Date';
    $SFContractCommissionRateFieldName = 'Commission rate percentage';
    $SFContractOPFeeFieldName = 'Online Payment Transaction Fee';
    $SFContractMinOrderFieldName = 'Min. Order';
    $SFContractDeliveryTypeFieldName = 'Delivery Type';
    $SFContractDeliveryChargeFieldName = 'Delivery Charge';
    $SFContractActivationDateFieldName = 'Activation Date';
    $SFContractSpecialTermsFieldName = 'Special Terms';
    $SFContractApprovalDateFieldName = 'Approval Date';
    $SFContractAttachmentsFieldName = 'Attachments';
    ///////////////////////////////////////////////////////////////////////////////////////////////



//END AUTOMATION
    return $result;


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

?>