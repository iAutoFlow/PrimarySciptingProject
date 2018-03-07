<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/13/2016
 * Time: 12:50 PM
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
    $ContactAppID = 17330422;
    $ContactCreateViewID = 31582153;
    $ContactUpdateViewID = 0;

//    $GetListURL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_schema/Contact";
//    $APIKey = "?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";
//    $urlString = $GetListURL.$APIKey;
//
//    $curl = new \Curl\Curl();
//
//    $curl = $curl->get($urlString);
//
//    $FieldLabelsArray = array();
//    $FieldLNameArray = array();
//
//    $ContactFields = $curl->fields;
//    foreach($ContactFields as $field){
//        $FieldLabel = $field->label;
//        $FieldName = $field->name;
//        array_push($FieldLNameArray, "'".$FieldName."' => '',");
//        //array_push($FieldLNameArray, $FieldName);
//    }


    $offset = 0;
    $i = 0;

    do{
        $ContactItems = PodioItem::filter_by_view($ContactAppID, $ContactCreateViewID, array('limit' => 500, 'offset' => $offset));
        $count = count($ContactItems);
        foreach($ContactItems as $contact) {
            $NewContactItemID = $contact->item_id;
            $ContactItemID = $contact->fields['podio-contact-item-id']->values;
            if (!$ContactItemID) {
                continue;
            }
            $contact = PodioItem::get((int)$ContactItemID);
            $contactLastUpdate = $contact->current_revision->created_on;
            $DateFormated = $contactLastUpdate->format('Y-m-d H:i:s');

            $UpdateAcccountItem = PodioItem::update($NewContactItemID, array(
                'fields' => array(
                    'lastupdateddate-podio' => $DateFormated,
                )
            ));


            $CreatedDate = $contact->created_on->date;
            $Workspace = $contact->fields['workspace']->values[0]['text'];
            $SFtransferStatus = $contact->fields['sfstatus']->values[0]['text'];
            $Id = $contact->fields['contacts-podio-unique-id-2']->values;
            $IsDeleted = '';
            $MasterRecordId = '';
            $AccountId = '';
            $FirstName = $contact->fields['text']->values;
            $LastName = $contact->fields['last-name']->values;
            $Salutation = "";
            $Name = $contact->fields['text-4']->values;
            $RecordTypeId = 'Vendor';
            $OtherStreet = '';
            $OtherCity = '';
            $OtherState = '';
            $OtherPostalCode = '';
            $OtherCountry = '';
            $OtherLatitude = '';
            $OtherLongitude = '';
            $MailingStreet = '';
            $MailingCity = '';
            $MailingState = '';
            $MailingPostalCode = '';
            $MailingCountry = '';
            $MailingLatitude = '';
            $MailingLongitude = '';
            $Phone = $contact->fields['phone']->values;
            $Fax = '';
            $MobilePhone = $contact->fields['phone']->values;
            $HomePhone = '';
            $OtherPhone = '';
            $AssistantPhone = '';
            $ReportsToId = '';
            $Title = '';
            $Email = $contact->fields['email']->values;
            $Department = "";
            $AssistantName = '';
            $LeadSource = '';
            $Birthdate = '';
            $Description = $contact->fields['contact-item-comments']->values;
            $CurrencyIsoCode = '';
            $OwnerId = $contact->fields['owner-sfid-2']->values;
            //$CreatedDate = '';
            $CreatedById = '';
            $LastModifiedDate = '';
            $LastModifiedById = '';
            $SystemModstamp = '';
            $LastActivityDate = '';
            $LastCURequestDate = '';
            $LastCUUpdateDate = '';
            $LastViewedDate = '';
            $LastReferencedDate = '';
            $EmailBouncedReason = '';
            $EmailBouncedDate = '';
            $JigsawContactId = '';
            $Main_Contact__c = FALSE;
            $X18_Char_Contact_ID__c = '';
            $Contact_Role__c = $contact->fields['job-title']->values;
            $Mobile_Phone__c = '';

            if (!$Email) {$Email = "noemailexists@email.com";}
            if (!$Title) {$Title = "Contact";}
            if (!$Description) {$Description = "Contract transfered from Podio records.";}
//            if(!$StreetName){$StreetName = "";}
//            if(!$PostCode){$PostCode = "";}


            $NewContactItemFieldsArray = array(
                'Id' => $Id,
                'IsDeleted' => FALSE,
                'MasterRecordId' => $MasterRecordId,
                'AccountId' => $AccountId,
                'LastName' => $LastName,
                'FirstName' => $FirstName,
                'Salutation' => $Salutation,
                'Name' => $Name,
                'RecordTypeId' => $RecordTypeId,
                'OtherStreet' => $OtherStreet,
                'OtherCity' => $OtherCity,
                'OtherState' => $OtherState,
                'OtherPostalCode' => $OtherPostalCode,
                'OtherCountry' => $OtherCountry,
                'OtherLatitude' => $OtherLatitude,
                'OtherLongitude' => $OtherLongitude,
                'MailingStreet' => $MailingStreet,
                'MailingCity' => $MailingCity,
                'MailingState' => $MailingState,
                'MailingPostalCode' => $MailingPostalCode,
                'MailingCountry' => $MailingCountry,
                'MailingLatitude' => $MailingLatitude,
                'MailingLongitude' => $MailingLongitude,
                'Phone' => $Phone,
                'Fax' => $Fax,
                'MobilePhone' => $MobilePhone,
                'HomePhone' => $HomePhone,
                'OtherPhone' => $OtherPhone,
                'AssistantPhone' => $AssistantPhone,
                'ReportsToId' => $ReportsToId,
                'Email' => $Email,
                'Title' => $Title,
                'Department' => $Department,
                'AssistantName' => $AssistantName,
                'LeadSource' => $LeadSource,
                'Birthdate' => $Birthdate,
                'Description' => $Description,
                'CurrencyIsoCode' => $CurrencyIsoCode,
                'OwnerId' => $OwnerId,
                'CreatedDate' => $CreatedDate,
                'CreatedById' => $CreatedById,
                'LastModifiedDate' => $LastModifiedDate,
                'LastModifiedById' => $LastModifiedById,
                'SystemModstamp' => $SystemModstamp,
                'LastActivityDate' => $LastActivityDate,
                'LastCURequestDate' => $LastCURequestDate,
                'LastCUUpdateDate' => $LastCUUpdateDate,
                'LastViewedDate' => $LastViewedDate,
                'LastReferencedDate' => $LastReferencedDate,
                'EmailBouncedReason' => $EmailBouncedReason,
                'EmailBouncedDate' => $EmailBouncedDate,
                'JigsawContactId' => $JigsawContactId,
                'Main_Contact__c' => $Main_Contact__c,
                'X18_Char_Contact_ID__c' => $X18_Char_Contact_ID__c,
                'Contact_Role__c' => $Contact_Role__c,
                'Mobile_Phone__c' => $Mobile_Phone__c,
            );


            $ContactFields = [$NewContactItemFieldsArray];
            $ContactFieldsJSON = json_encode($ContactFields);


            $CreateContactCurl = curl_init();
            curl_setopt($CreateContactCurl, CURLOPT_URL, 'https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Contact');
            curl_setopt($CreateContactCurl, CURLOPT_HEADER, false);
            curl_setopt($CreateContactCurl, CURLOPT_POST, true);
            curl_setopt($CreateContactCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', "X-DreamFactory-Api-Key: 36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88", "X-DreamFactory-Session-Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjYsInVzZXJfaWQiOjYsImVtYWlsIjoiaXJvYmVydHNvbkB0ZWNoZWdvLmNvbSIsImZvcmV2ZXIiOmZhbHNlLCJpc3MiOiJodHRwczpcL1wvaG9pc3QudGhhdGFwcC5pb1wvYXBpXC92Mlwvc3lzdGVtXC9hZG1pblwvc2Vzc2lvbiIsImlhdCI6MTQ4MTU1ODk5NywiZXhwIjoxNDgyMTYzNzk3LCJuYmYiOjE0ODE1NTg5OTcsImp0aSI6Ijg4ZWU1OTFmMDdkNDA3NTllMWU0ZGZlNzFiYzM2NGRhIn0.CjgbSLTZT1D3FiI5lo9awACQIms3jWUTdJB_ZEgoPpY"));
            curl_setopt($CreateContactCurl, CURLOPT_POSTFIELDS, $ContactFieldsJSON);
            curl_setopt($CreateContactCurl, CURLOPT_RETURNTRANSFER, true);


            $CreateContactCurlResult = curl_exec($CreateContactCurl);
            $Result = json_decode($CreateContactCurlResult);

            print_r($Result);
            exit;


            curl_close($CreateContactCurl);


        }$i++;
    }while($count == 500);





    $HQBuildingNameField = 'Building NameNoHQ';
    $RecordOwnder = 'Record Owner';
    $Status = 'Contact Status';
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
    //Contact Salesforce Fields.....................................................
    $SFContactIDFieldName = 'Podio ID';
    $SFContactFirstNameFieldName = 'First Name';
    $SFContactLastNameFieldName = 'Last Name';
    $SFContactVendorNameFieldName = 'Company Name';
    $SFContactJobTitleFieldName = 'Job Title';
    $SFContactEmailFieldName = 'Email';
    $SFContactPhoneNumberFieldName = 'Phone Number';
    $SFContactMainContactFieldName = 'Main Contact';
    $SFContactRecordTypeFieldName = 'Record Type';
    $SFContactOwnerFieldName = 'Contact Owner';
    ////////////////////////////////////////////////////////////////////////////////////////////
    //Contract Salesforce Fields......................................................
    $SFContactIDFieldName = 'Podio ID';
    $SFContactOpportunityRecordTypeFieldName = 'Opportunity Record Type';
    $SFContactCompanyNameFieldName = 'Company Name';
    $SFContactOwnerFieldName = 'Contract Owner';
    $SFContactStatusFieldName = 'Status';
    $SFContactOrderMethodFieldName = 'order transmission method';
    $SFContactStartDateFieldName = 'Contract Start Date';
    $SFContactCommissionRateFieldName = 'Commission rate percentage';
    $SFContactOPFeeFieldName = 'Online Payment Transaction Fee';
    $SFContactMinOrderFieldName = 'Min. Order';
    $SFContactDeliveryTypeFieldName = 'Delivery Type';
    $SFContactDeliveryChargeFieldName = 'Delivery Charge';
    $SFContactActivationDateFieldName = 'Activation Date';
    $SFContactSpecialTermsFieldName = 'Special Terms';
    $SFContactApprovalDateFieldName = 'Approval Date';
    $SFContactAttachmentsFieldName = 'Attachments';
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