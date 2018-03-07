<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/13/2016
 * Time: 12:50 PM
 */


date_default_timezone_set('America/Denver');

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

    ///AUTOMATION START
    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    $AccountAppID = 17330412;
    $AccountCreateViewID = 31597500;
    $AccountUpdateViewID = 31598441;
    $SDFAsdf = 31599900;
    $asdfasdf = 31600221;





    ///Vendor Salesforce Fields..........................................
//
//    $GetListURL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_schema/Account";
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
//    $AccountFields = $curl->fields;
//    foreach($AccountFields as $field) {
//        $FieldLabel = $field->label;
//        $FieldName = $field->name;
//        array_push($FieldLNameArray, $FieldName . ",");
//        //array_push($FieldLNameArray, $FieldName);
//    }
//
//
//
//    $Fields = "Id, IsDeleted, MasterRecordId, Name, Type, RecordTypeId, ParentId, BillingStreet, BillingCity, BillingState,  BillingPostalCode,  BillingCountry,  BillingLatitude,  BillingLongitude,  ShippingStreet,  ShippingCity,  ShippingState,  ShippingPostalCode,  ShippingCountry,  ShippingLatitude,  ShippingLongitude,  Phone,  Fax,  Website,  Industry,  AnnualRevenue,  NumberOfEmployees,  Description,  CurrencyIsoCode,  OwnerId,  CreatedDate,  CreatedById,  LastModifiedDate,  LastModifiedById,  SystemModstamp,  LastActivityDate,  LastViewedDate,  LastReferencedDate,  JigsawCompanyId,  AccountSource,  Order_Phone_Number__c,  New_Terms_Conditions_Opps__c,  Country__c,  City__c,  Source__c,  Account_Status__c,  Chain__c,  No_of_restaurants__c,  Street_Name__c,  House_number__c,  Post_Code__c,  Area_District__c,  Delivery_Area__c,  SubArea__c,  External_Rating__c,  number_of_active_contracts__c,  Feature_With_Competitor__c,  Opps__c,  Chain_ID_unique__c,  Saturday__c,  Sunday__c,  Delivery_Fee__c,  Lead_Gen_Comments__c,  Competitor_Name__c,  Service_Charge__c,  AAA__c,  Preferred_Contact_Language__c,  Vendor_Backend_Code__c,  Partner_Type__c,  Delivery_Charge__c,  Order_Email__c,  Role_of_Account_Owner__c,  Other_Phone__c,  Number_of_approved_contracts__c,  Number_of_reviews__c,  Assigned_Date__c,  Content_Completed__c,  Number_of_active_Chain_Contracts__c,  Chain_IDs__c,  Target_Partner1__c,  num_closed_tasks__c,  num_closed_events__c,  Last_Activity_Date__c,  Last_Activity_Subject__c,  Next_Activity_Date__c,  Next_Activity_Subject__c,  vendor_code_unique__c,  Top_AAA__c,  Corporate_Backend_Code__c,  Company_Scope__c,  Industry__c,  Customer_Type__c,  Closed_Lost_Opps__c,  New_Cuisine_Business_Characteristics__c,  Welcome_call_on__c,  Content_completed_by__c,  of_Reneg_Opps__c,  of_AM_Opps__c,  Sales_Opps__c,  num_Contracts__c,  Days_since_Activation__c,  Next_Activity_7_Days__c,  Last_Assigned_7_Days__c,  Vendor_Grade_Image__c,  Vendor_Grade__c,  X18_Char_ID__c,  No_of_Employees__c,  Last_Activity_30_days__c,  Last_Activity_7_Days__c,  Status_change_Date__c,  open_Sales_Opps__c,  Temp_Status__c,  Vendor_Prioritization__c,  Vendor_Location__Latitude__s,  Vendor_Location__Longitude__s,  Vendor_Location__c,  Content_Completed_Time_Stamp__c,  Preview_Link__c,  Activated_Date__c,  Title_of_Account_Owner__c,  Facebook_URL__c,  ClosedWon_Sales_Opps__c,  Show_Nearby_Vendors__c,  Account_Owner_Profile__c,  Price_Range__c,  Similarweb_Hostname__c,  Similarweb_Visits__c,  Active_Bank_Account_Name__c,  Active_Bank_Account_Number__c,  Active_Bank_Address__c,  Active_Bank_Name__c,  Active_Commission_Rate_Percentage__c,  Active_IFSC__c,  Active_Min_Order_Value__c,  Active_Tuesday__c,  active_Friday__c,  active_Monday__c,  active_Saturday__c,  active_Sunday__c,  active_Thursday__c,  active_Wednesday__c,  Meal_reimbursements__c,  Coverage_Area__c,  Vendor_has_Own_Delivery__c,  Reason_for_Deactivation__c";
//    $FieldsEncoded = urlencode($Fields);





    $offset = 0;
    $i = 0;

    do {
        $offset = $i * 100;
        $AccountItems = PodioItem::filter_by_view($AccountAppID, $AccountCreateViewID, array('limit' => 100, 'offset' => $offset));//$AccountCreateViewID,
        $count = count($AccountItems);
        foreach ($AccountItems as $account) {

            //Unset Variables
            $LastModifiedDateINSalesforce = "";
            $LasetEditedSFDateFormated = "";
            $VendorLastUpdate = "";
            $LasetEditedPodioDateFormated = "";
            $AccountItemID = "";
            $VendorCreatedBy = "";
            $VendorItem = "";
            $VendorItemID = "";
            $SFID = "";
            $CreatedByMartina = "No";
            $SFStatus1 = "";
            $SFStatus2 = "";

            $AccountFieldsArray = array('fields' => array());
            $AccountItemID = $account->item_id;
            //$AccountOwner = $account->fields['owner']->values;
            $SFStatus1 = $account->fields['sfstatus']->values[0]['text'];//"2B Created - New Salesforce Item";
            $SFID = $account->fields['account-sf-id']->values;
            $VendorItemID = $account->fields['vendor-item']->values[0]->item_id;
            $SFStatus1 = $account->fields['sfstatus']->values[0]['text'];

            //$LastModifiedDateINSalesforce = $account->fields['lastmodifieddate']->start;
            //if ($LastModifiedDateINSalesforce) {$LasetEditedSFDateFormated = $LastModifiedDateINSalesforce->format('Y-m-d H:i:s');}
            //$VendorLastUpdate = $account->fields['lastmodifieddate-podio']->start;
            //if ($VendorLastUpdate) {$LasetEditedPodioDateFormated = $VendorLastUpdate->format('Y-m-d H:i:s');}
            //if ($LasetEditedSFDateFormated && $LasetEditedPodioDateFormated && $LasetEditedPodioDateFormated > $LasetEditedSFDateFormated) {$SFStatus2 = "2B Updated - Updated more recently in Podio";}
            //if ($LasetEditedSFDateFormated && $LasetEditedPodioDateFormated && $LasetEditedSFDateFormated > $LasetEditedPodioDateFormated) {$SFStatus2 = "NO2B Updated - Updated more recently in Salesforce";}

            if ($VendorItemID) {
                $VendorItem = PodioItem::get($VendorItemID);
                $VendorCreatedBy = $VendorItem->created_by->name;
                if ($VendorCreatedBy == "Martina") {
                    $CreatedByMartina = "Yes";
                }
            }

            $VendorRevisions = PodioItemRevision::get_for($VendorItemIDjmkymgjnh)


            if($SFID && $CreatedByMartina == "Yes"){
                $Salesforce = "";
                $Podio = "";
                if($LasetEditedPodioDateFormated > $LasetEditedSFDateFormated){$Podio = "Yes";}
                if($LasetEditedSFDateFormated > $LasetEditedPodioDateFormated){$Salesforce = "Yes";}
                if($Podio == "Yes"){$SFStatus2 = "2B Updated - Updated more recently in Podio";}
                if($Salesforce == "Yes"){$SFStatus2 = "NO2B Updated - Updated more recently in Salesforce";}

            }

            if($SFStatus1 !== $SFStatus2) {
                $AccountFieldsArray['fields']['sfstatus'] = $SFStatus2;
                $UpdateAccount = PodioItem::update($AccountItemID, $AccountFieldsArray);
            }

        } $i++;
    }while($count == 100);

    print_r("Done");
    exit;


//
//                } elseif (!$AccountOwner) {
//                    $SFStatus = "NO2B Created - No Owner";
//                } else {
//                    $VendorItemRevisions = PodioItemRevision::get_for($VendorItemID);
//                    foreach ($VendorItemRevisions as $revision) {
//                        $VendorRevisionCreatedBy = $revision->created_by->name;
//                        if ($VendorRevisionCreatedBy !== "AVA" && $VendorRevisionCreatedBy !== "Martina") {
//                            $VendorLastUpdate = $revision->created_on;
//                            $LasetEditedPodioDateFormated = $VendorLastUpdate->format('Y-m-d H:i:s');
//                            if ($LastModifiedDateINSalesforce && $LasetEditedPodioDateFormated > $LasetEditedSFDateFormated) {
//                                $SFStatus = "2B Updated - Updated more recently in Podio";
////                            $ResultsArray = array($VendorItemID, $SFStatus, $revision);
////                            print_r($ResultsArray);
////                            exit;
//
//                            }
//                            if ($LastModifiedDateINSalesforce && $LasetEditedSFDateFormated > $LasetEditedPodioDateFormated) {
//                                $SFStatus = "NO2B Updated - Updated more recently in Salesforce";
////                            $ResultsArray = array($VendorItemID, $SFStatus, $revision);
////                            print_r($ResultsArray);
////                            exit;
//
//                            }
//                            if (!$LastModifiedDateINSalesforce) {
//                                $SFStatus = "2B Created - New Salesforce Item";
////                            $ResultsArray = array($VendorItemID, $SFStatus, $revision);
////                            print_r($ResultsArray);
////                            exit;
//
//                            }
//                        }
//                    }
//                    if ($LasetEditedPodioDateFormated) {
//                        $AccountFieldsArray['fields']['lastmodifieddate-podio'] = $LasetEditedPodioDateFormated;
//                    }
//                }
//
//                if ($SFStatus) {
//                    $AccountFieldsArray['fields']['sfstatus'] = $SFStatus;
//                }
//
////            $ResultsArray = array($SFStatus, $VendorItem);
////            print_r($ResultsArray);
////            exit;
//
//                $UpdateAccount = PodioItem::update($AccountItemID, $AccountFieldsArray);
//            }
//        }
//        $i++;
//    }while($count == 500);




            //$SFStatus = $account->fields['sfstatus']->values[0]['text'];
            $RecordOwner = $account->fields['owner']->values;
            $PodioUniqueID = $account->fields['accounts-podio-unique-id']->values;
            $CreatedOn = $account->fields['created-date']->values;
            $CreatedBy = $account->fields['created-by']->values;
            $CompanyName = $account->fields['title']->values;
            $BuildingNameHQ = $account->fields['building-name-hq']->values;
            $OwnerID = $account->fields['owner-sfid-2']->values;
            $AccountStatus = $account->fields['account-status']->values;
            $Industry = $account->fields['industry']->values;
            $PartnerType = $account->fields['partn']->values;
            //$VendorGrade = $account->fields['title']->values;
            $StreetName = $account->fields['street-name']->values;
            $PostCode = $account->fields['post-code']->values;
            $City = $account->fields['text']->values;
            $Country = $account->fields['country']->values[0]['text'];
            $AreaDistrict = $account->fields['areadistrict']->values;
            $WebsiteURL = $account->fields['website-url-2']->values;
            $FacebookURL = $account->fields['facebook-url-2']->values;
            $Chain = $account->fields['chain']->values;
            $NoRestaurants = $account->fields['no-of-restaurants']->values;
            $VendorBackendCode = $account->fields['vendor-backend-code-2']->values;
            $ChainCode = $account->fields['calculation-2']->values;
            $Cuisine = $account->fields['cuisine-2']->values;
            //$FrontendURL = $account->fields['title']->values;
            $ReasonforDeactivation = $account->fields['reason-for-deactivation']->values;
            $ActivatedDate = $account->fields['activated-date-2']->values;
            $Comments = $account->fields['comments']->values;
            $AccountCurrency = $account->fields['account-currency-2']->values;
            $AAA = $account->fields['aaa-2']->values;
            $VendorLocationLongitude = $account->fields['vendor-location-latitude-2']->values;
            $VendorLocationLatitude = $account->fields['vendor-location-latitude']->values;
            $VendorSFID = $account->fields['account-sf-id']->values;
            $RecordType = "Vendor";
            $PreferredContactLanguage = "English";
            $AccountSource = "Internet/Web";
            $FeatureWithCompetitor = "No";


            $todaysDate = date_create("now");


            $LastActivityDate = $account->fields['lastmodifieddate-podio']->start;
            if(!$LastActivityDate) {$LastActivityDate = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));}
            if($LastActivityDate){$LastActivityDate = $LastActivityDate->format('Y-m-d');}

            // $CreatedOn = str_replace("/", ".", $CreatedOn);
            $VendorItemID = $account->fields['vendor-item']->values[0]->item_id;
            $VendorItem = PodioItem::get($VendorItemID);
            $CreatedOnDate = $VendorItem->created_on;
            $CreatedOnFormatted = $CreatedOnDate->format('Y-m-d');

            if($AccountCurrency == "BDT - Bangladesh Taka"){$AccountCurrency = "BDT";}



            $CuisineList = explode("; ", $Cuisine);
            if($Cuisine){list($Cuisine1, $Cuisine2, $Cuisine3, $Cuisine4, $Cuisine5) = explode(";", $Cuisine);
                $CuisineArray = "";
                if($Cuisine1){$CuisineArray .= $Cuisine1;}
                if($Cuisine2){$CuisineArray .= $Cuisine2;}
                if($Cuisine3){$CuisineArray .= $Cuisine3;}
                if($Cuisine4){$CuisineArray .= $Cuisine4;}
                if($Cuisine5){$CuisineArray .= $Cuisine5;}
            }

            if($PartnerType == "Holding Company"){$PartnerType = "Holding Company";}
            if($PartnerType == "HQ"){$PartnerType = "Headquarters";}
            if($PartnerType == "Outlet" || $PartnerType == "Restaurant"){$PartnerType = "Restaurant";}
            if(!$PartnerType){$PartnerType = "Restaurant";}

            if($ActivatedDate){$ActivatedDate = strtotime($ActivatedDate);
                $ActivationDateFormat = date('Y-m-d', $ActivatedDate);
            }
            if(!$ActivatedDate){$ActivationDateFormat = $CreatedOnFormatted;}
            if(!$Industry){$Industry = "Restaurant";}
            if(!$WebsiteURL){$WebsiteURL = "https://www.foodpanda.com/";}
            if(!$City){$City = "City";}
            if(!$NoRestaurants){$NoRestaurants = "1";}
            if(!$StreetName){$StreetName = "";}
            if(!$PostCode){$PostCode = "";}
            if(!$AreaDistrict){$AreaDistrict = "";}
            if(!$FeatureWithCompetitor){$FeatureWithCompetitor = "No";}
            if(!$Comments){$Comments = "";}
            if(!$AAA || $AAA !== "AAA"){$AAA = "No";}
            if(!$VendorBackendCode){$VendorBackendCode = "";}
            if(!$ChainCode){$ChainCode = "";}
            if(!$Cuisine){$CuisineArray = "Other";}
           // if(!$VendorGrade){$VendorGrade = "";}
            if(!$VendorLocationLatitude){$VendorLocationLatitude = "";}
            if(!$VendorLocationLongitude){$VendorLocationLongitude = "";}

            if(!$FacebookURL){$FacebookURL = "https://www.facebook.com/";}
            if(!$ReasonforDeactivation){$ReasonforDeactivation = "";}

            $NewAccountItemFieldsArray = array(
                'RecordTypeId'=> "012b0000000Cwe4AAC",
                'MasterRecordId'=> "",
                'Id'=>"",
                'ParentId'=> "",
                'AccountID'=>"",
                'IsDeleted' => false,
                'Name' => $CompanyName,
                'Type' => $RecordType,
                'BillingStreet' => "",
                'BillingCity' => "",
                'BillingState' => "",
                'BillingPostalCode' => "",
                'BillingCountry' => "",
                'BillingLatitude' => "",
                'BillingLongitude' => "",
                'ShippingStreet' => "",
                'ShippingCity' => "",
                'ShippingState' => "",
                'ShippingPostalCode' => "",
                'ShippingCountry' => "",
                'ShippingLatitude' => "",
                'ShippingLongitude' => "",
                'Phone' => "",
                'Fax' => "",
                'Website' => $WebsiteURL,
                'Industry' => $Industry,
                'AnnualRevenue' => "",
                'NumberOfEmployees' => "",
                'Description' => "",
                'CurrencyIsoCode' => $AccountCurrency,
                'OwnerId' => $OwnerID,
                'CreatedDate' => $CreatedOnFormatted,
                'CreatedById' => "",
                'LastModifiedDate' => $LastActivityDate,
                'LastModifiedById' => "",
                'SystemModstamp' => "",
                'LastActivityDate' => $LastActivityDate,
                'LastViewedDate' => $LastActivityDate,
                'LastReferencedDate' => $CreatedOnFormatted,
                'JigsawCompanyId' => "",
                'AccountSource' => "",
                'Order_Phone_Number__c' => 1112223333,
                'New_Terms_Conditions_Opps__c' => "",
                'Country__c' => $Country,
                'City__c' => $City,
                'Source__c' => $AccountSource,
                'Account_Status__c' => $AccountStatus,
                'Chain__c' => $Chain,
                'No_of_restaurants__c' => $NoRestaurants,
                'Street_Name__c' => $StreetName,
                'House_number__c' => "",
                'Post_Code__c' => $PostCode,
                'Area_District__c' => $AreaDistrict,
                'Delivery_Area__c' => "",
                'SubArea__c' => "",
                'External_Rating__c' => 'N/A',
                'number_of_active_contracts__c' => "",
                'Feature_With_Competitor__c' => $FeatureWithCompetitor,
                'Opps__c' => "",
                'Chain_ID_unique__c' => "",
                'Saturday__c' => "",
                'Sunday__c' => "",
                'Delivery_Fee__c' => "",
                'Lead_Gen_Comments__c' => $Comments,
                'Competitor_Name__c' => "",
                'Service_Charge__c' => "",
                'AAA__c' => $AAA,
                'Preferred_Contact_Language__c' => $PreferredContactLanguage,
                'Vendor_Backend_Code__c' => $VendorBackendCode,
                'Partner_Type__c' => $PartnerType,
                'Delivery_Charge__c' => "",
                'Order_Email__c' => "",
                'Role_of_Account_Owner__c' => "",
                'Other_Phone__c' => "",
                'Number_of_approved_contracts__c' => "",
                'Number_of_reviews__c' => "",
                'Assigned_Date__c' => $ActivationDateFormat,
                'Content_Completed__c' => "",
                'Number_of_active_Chain_Contracts__c' => "",
                'Chain_IDs__c' => $ChainCode,
                'Target_Partner1__c' => 'Basic Lead (BL)',
                'num_closed_tasks__c' => "",
                'num_closed_events__c' => "",
                'Last_Activity_Date__c' => $LastActivityDate,
                'Last_Activity_Subject__c' => "",
                'Next_Activity_Date__c' => $LastActivityDate,
                'Next_Activity_Subject__c' => "",
                'vendor_code_unique__c' => "",
                'Top_AAA__c' => "",
                'Corporate_Backend_Code__c' => "",
                'Company_Scope__c' => "",
                'Industry__c' => "",
                'Customer_Type__c' => 'Vendor',
                'Closed_Lost_Opps__c' => "",
                'New_Cuisine_Business_Characteristics__c' => $CuisineArray,
                'Welcome_call_on__c' => $ActivationDateFormat,
                'Content_completed_by__c' => "",
                'of_Reneg_Opps__c' => "",
                'of_AM_Opps__c' => "",
                'Sales_Opps__c' => "",
                'num_Contracts__c' => "",
                'Days_since_Activation__c' => "",
                'Next_Activity_7_Days__c' => "",
                'Last_Assigned_7_Days__c' => "",
                'Vendor_Grade_Image__c' => "",
                'Vendor_Grade__c' => "",
                'X18_Char_ID__c' => "",
                'No_of_Employees__c' => "",
                'Last_Activity_30_days__c' => "",
                'Last_Activity_7_Days__c' => "",
                'Status_change_Date__c' => $LastActivityDate,
                'open_Sales_Opps__c' => "",
                'Temp_Status__c' => "",
                'Vendor_Prioritization__c' => "",
                'Vendor_Location__Latitude__s' => $VendorLocationLatitude,
                'Vendor_Location__Longitude__s' => $VendorLocationLongitude,
                'Vendor_Location__c' => "",
                'Content_Completed_Time_Stamp__c' => $ActivationDateFormat,
                'Preview_Link__c' => "",
                'Activated_Date__c' => $ActivationDateFormat,
                'Title_of_Account_Owner__c' => "",
                'Facebook_URL__c' => $FacebookURL,
                'ClosedWon_Sales_Opps__c' => "",
                'Show_Nearby_Vendors__c' => "",
                'Account_Owner_Profile__c' => "",
                'Price_Range__c' => "",
                'Similarweb_Hostname__c' => "",
                'Similarweb_Visits__c' => "",
                'Active_Bank_Account_Name__c' => "",
                'Active_Bank_Account_Number__c' => "",
                'Active_Bank_Address__c' => "",
                'Active_Bank_Name__c' => "",
                'Active_Commission_Rate_Percentage__c' => "",
                'Active_IFSC__c' => "",
                'Active_Min_Order_Value__c' => "",
                'Active_Tuesday__c' => "",
                'active_Friday__c' => "",
                'active_Monday__c' => "",
                'active_Saturday__c' => "",
                'active_Sunday__c' => "",
                'active_Thursday__c' => "",
                'active_Wednesday__c' => "",
                'Meal_reimbursements__c' => "",
                'Coverage_Area__c' => "",
                'Vendor_has_Own_Delivery__c' => "",
                'Reason_for_Deactivation__c' => $ReasonforDeactivation,
            );



            $AccountFields = [$NewAccountItemFieldsArray];
            $AccountFieldsJSON = json_encode($AccountFields);

    print_r($AccountFieldsJSON);
    exit;
//
//
//            $CreateAccountCurl = curl_init();
//            curl_setopt($CreateAccountCurl, CURLOPT_URL, "https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Account?fields=Id&id_field=$FieldsEncoded");
//            curl_setopt($CreateAccountCurl, CURLOPT_HEADER, false);
//            curl_setopt($CreateAccountCurl, CURLOPT_POST, true);
//            curl_setopt($CreateAccountCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', "X-HTTP-METHOD: POST","X-DreamFactory-Api-Key: 1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634", "X-DreamFactory-Session-Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjYsInVzZXJfaWQiOjYsImVtYWlsIjoiaXJvYmVydHNvbkB0ZWNoZWdvLmNvbSIsImZvcmV2ZXIiOmZhbHNlLCJpc3MiOiJodHRwczpcL1wvaG9pc3QudGhhdGFwcC5pb1wvYXBpXC92Mlwvc3lzdGVtXC9hZG1pblwvc2Vzc2lvbiIsImlhdCI6MTQ4MTU1ODk5NywiZXhwIjoxNDgyMTYzNzk3LCJuYmYiOjE0ODE1NTg5OTcsImp0aSI6Ijg4ZWU1OTFmMDdkNDA3NTllMWU0ZGZlNzFiYzM2NGRhIn0.CjgbSLTZT1D3FiI5lo9awACQIms3jWUTdJB_ZEgoPpY"));
//            curl_setopt($CreateAccountCurl, CURLOPT_POSTFIELDS, $AccountFieldsJSON);
//            curl_setopt($CreateAccountCurl, CURLOPT_RETURNTRANSFER, true);
//
//
//
//            $CreateAccountCurlResult = curl_exec($CreateAccountCurl);
//            $Result = json_decode($CreateAccountCurlResult);
//
//
//            $Info = curl_getinfo($CreateAccountCurl);
//
//
//
//
//            print_r($Result);
//            exit;
//
//            curl_close($CreateAccountCurlResult);
//
//
//
//
        }$i++;
    }while($count == 500);
//
//
//
//    $HQBuildingNameField = 'Building NameNoHQ';
//    $RecordOwnder = 'Record Owner';
//    $Status = 'Account Status';
//    $Grade = 'Vendor Grade';
//    $StreetName = 'Street Name';
//    $PostCode = 'Post Code';
//    $City = 'City';
//    $State = 'No';
//    $Country = 'Country';
//    $AreaDistrict = 'Area/District';
//    $WebsiteURL = 'Website URL';
//    $FacebookURL = 'Facebook URL';
//    $Chain = 'Chain';
//    $NoRestaurants = 'No. of restaurants';
//    $BackendCode = 'Vendor Backend Code';
//    $ChainCode = 'Chain Code';
//    $Cuisine = 'Cuisine';
//    $FrontendURL = 'Frontend URL';
//    $ReasonforDeactivation = 'Reason for Deactivation';
//    $ActivatedDate = 'Activated Date';
//    $Comments = 'Comments';
//    $OpportunityCurrency = 'Opportunity Currency';
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
    ///////////////////////////////////////////////////////////////////////////////////////////////

//
//
//    $TableType = "Account";
//    $APIKey = '&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
//
//    $curl = new \Curl\Curl();
//    $offset = 0;
//
//    $BaseURL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Account";
//    $Fields =   "?fields=Id%2CName%2COwnerId%2Cvendor_code_unique__c%2CStreet_Name__c%2CCity__c%2CCountry__c%2CBillingLatitude%2CBillingLongitude%2CLastModifiedDate&filter=Country__c%3D'$country'&offset=0&include_count=true&include_schema=false&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";//
//
//    $urlString = $BaseURL.$Fields;
//
//    $curl = $curl->get($urlString);
//    $firstResponse = $curl->resource;
//    //$result = $firstResponse;
//    curl_close($curl);
//
//    $curl = new \Curl\Curl();
//
//    $offset=2000;
//    $Fields = "?fields=Id%2CName%2COwnerId%2Cvendor_code_unique__c%2CStreet_Name__c%2CCity__c%2CCountry__c%2CBillingLatitude%2CBillingLongitude&filter=Country__c%3D'$country'&offset=$offset&include_count=true&include_schema=false&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";//
//    $urlString = $BaseURL.$Fields;
//
//    $curl = $curl->get($urlString);
//    $secondResponse = $curl->resource;
//
//    curl_close($curl);
//
//    foreach($secondResponse as $insertMe){
//
//        array_push($firstResponse, $insertMe);
//    }
//
//    $result = $firstResponse;



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