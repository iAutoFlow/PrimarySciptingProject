<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table


class PodioSessionManager {
    private static $connection_id = 3;
    private static $connection;
    private static $appConnection;
    private static $connectedAppID;
    private static $auth_type;
    public function __construct() {
    }
    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
        }
        return self::$connection;
    }
    public static function getAppConnection($app_id) {

        if(self::$connectedAppID !== $app_id) {
            self::$connectedAppID = $app_id;
            self::$appConnection = null;
        }

        if (!self::$appConnection) {
            self::$appConnection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->where('app_id', $app_id)->first();
        }

        if (!self::$appConnection) {

            $connection = self::getConnection();

            Podio::$oauth = new PodioOAuth(
                $connection->access_token,
                $connection->refresh_token
            );

            $app = PodioApp::get(Podio::$auth_type['identifier']);

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'null']);

            $newAppAuth = Podio::authenticate_with_app(Podio::$auth_type['identifier'], $app->token);

            $connection = new \EnvireTech\OauthConnector\Models\OrganizationConnection();
            $connection->name = "App_".(str_replace(" ", "_", $app->config['name']));
            $connection->app_id = $app->app_id;
            $connection->service_id = 16;
            $connection->refresh_token = Podio::$oauth->refresh_token;
            $connection->access_token = Podio::$oauth->access_token;
            $connection->organization_id = 1;
            $connection->created_by_id = 5;
            $connection->private = 0;
            $connection->save();

            self::$appConnection = $connection;

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
        }

        return self::$appConnection;
    }
    public static function getClientId () {
        return self::getConnection()->connectionService->config['client_id'];
    }
    public static function getClientSecret () {
        return self::getConnection()->connectionService->config['client_secret'];
    }
    public static function authtypeUserAVA(){

        Podio::$auth_type = array(
            "type" => "user",
            "identifier" => 1406952
        );

    }
    public static function authtypeApp($app_id){

        Podio::$auth_type = array(
            "type" => "app",
            "identifier" => $app_id
        );

    }
    public function get(){

        if(Podio::$auth_type['type'] == "app"){
            $connection = self::getAppConnection(Podio::$auth_type['identifier']);
        }
        else {
            $connection = self::getConnection();
        }

        return new PodioOAuth(
            $connection->access_token,
            $connection->refresh_token
        );
    }
    public function set($oauth, $auth_type = null){

        //$auth_type = self::$authtype;

        if($auth_type['type'] == "app") {
            $connection = self::getAppConnection($auth_type['identifier']);

            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;

        }
        else {
            $connection = self::getConnection();
            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;
        }


    }
}
function normalAuth(){
    PodioSessionManager::authtypeUserAVA();

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}
function appAuth($app_id){
    PodioSessionManager::authtypeApp($app_id);

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}
function addCatOptIfMissing($appID, $fieldXID, $newCatValue){

//    appAuth( $appID );

    $condition = false;

    $appUpdateArray = [];

    $appToGet = PodioApp::get( $appID );

    foreach( $appToGet->fields[$fieldXID]->config['settings']['options'] as $field ){

        if($field['text'] == $newCatValue){

            normalAuth();
            return $newCatValue;
            break;

        }

    }

    $fieldID = $appToGet->fields[$fieldXID]->field_id;

    $fieldLabel = $appToGet->fields[$fieldXID]->label;

    $fieldSettings = $appToGet->fields[$fieldXID]->config['settings'];

    $fieldDelta = $appToGet->fields[$fieldXID]->config['delta'];

    $fieldSettings['options'][] = ['text' => $newCatValue];

    normalAuth();

    $printThis = PodioAppField::update( $appID, $fieldID, ['settings' => $fieldSettings,'label' => $fieldLabel, 'delta' => $fieldDelta] );

    return $newCatValue;

}
try{
    normalAuth();
    $payload = $event['request']['payload'];
    $type = $payload['type'];
    if($type && $type == 'hook.verify'){

        $code = $payload['code'];
        $hook_id = $payload['hook_id'];

        // Validate the webhook
        PodioHook::validate($hook_id, array('code' => $code));

    }
    $requestParams = $event['request']['parameters'];
    $itemId = (int)$requestParams['item_id'];
    if(!$itemId) {
        $itemId = (int)$payload['item_id'];
    }

    $newEmailItem = PodioItem::get($itemId);
    $newLeadItemLink = $newEmailItem->link;
    $itemCreatedOn = $newEmailItem->created_on;
    $newLeadItemTitle = $newEmailItem->title;
    $emailBodyWithHtml = $newEmailItem->fields['body']->values;
    $leadFirstName = " ";
    $emailBody = strip_tags($emailBodyWithHtml);
    $emailLength = strlen($emailBody);

    $mailgunURL = 'https://api.mailgun.net/v3/mg.ethreerealestate.com/messages';
    $mailgunActiveApiKey = "api:key-ce906b994ef46a5d1b234c93e14425c1";
    $emailValidationKey = "api:pubkey-16e52fefda59d08535b19f125b9e0af1";
    $twilioAccountSid = 'ACf690895696c3103c7e7556db5ccb59dd';
    $twilioAuthToken = '9b9fe457b874050349d2e9452f77b260';
    $twilioPhoneNumber = "+16105462576";
    $twilioTESTAccountSid = 'ACda8bcc9f5611b738a139e5d4c736cd5f';
    $twilioTESTAuthToken = '98ed10c73e239693733365f413b19c02';

    //Agent
    $agentErnnie = preg_match('/Ernie Facchine/', $emailBody);
    $agentErnnieEmail = preg_match('/efacchine11@kw.com/', $emailBody);
    $agentAndrew = preg_match('/Andrew Virostek/', $emailBody);
    $agentAndrewEmail = preg_match('/avirostek@kw.com/', $emailBody);
    $agentLauren = preg_match('/Lauren Virostek/', $emailBody);
    $agentLaurenEmail = preg_match('/laurenvirostek@gmail.com/', $emailBody);

    //Source of Lead
    $fromZillow = preg_match('/Zillow/', $emailBody);
    $fromRealtor = preg_match('/Realtor.com/', $emailBody);
    $fromRealtors = preg_match('/REALTOR.com/', $emailBody);

    //Array to update the Trigger Item
    $fieldsArray = array();
    $sendMessagesToClient = false;
    //From Realtor.com
    if($fromRealtor > 0 || $fromRealtors > 0){

        $sourceOfLead = 3;
        $sourceOfLeadName = "Realtor.com";
        $leadPhoneNumber = $newEmailItem->fields['phone-number-2']->values;
        $leadEmailAddress = $newEmailItem->fields['email-address-2']->values;
        $leadFirstName = $newEmailItem->fields['first-name']->values;
        $leadLastName = $newEmailItem->fields['text']->values;
        $comment = $newEmailItem->fields['comment']->values;
        $propertyAddress = $newEmailItem->fields['property-address-2']->values;
        $propertyAddress = $newEmailItem->fields['property-address']->values;

        $endOfLeadInfo = strpos($emailBody,'View this listing on REALTOR.com');
        $emailBody = substr_replace($emailBody, "", $endOfLeadInfo, $emailLength);
        $emailBody = str_replace("This is an automated inquiry sent by a REALTOR.com® consumer. Please do not reply to this email. ", " ", $emailBody);

        preg_match('/This consumer inquired about:(.*?)Property Address:/', $emailBody, $inquiredAbout);
        $inquiredAbout = trim($inquiredAbout[1], " ");
        if($inquiredAbout)$fieldsArray['this-consumer-inquired-about'] = $inquiredAbout;
        if(!$propertyAddress){
            preg_match('/Property Address:(.*?)MLSID # /', $emailBody, $propertyAddress);
            $propertyAddress = trim($propertyAddress[1], " ");
        }
        if($propertyAddress){
            $fieldsArray['property-address'] = $propertyAddress;
            $fieldsArray['property-address-2'] = $propertyAddress;
        }
        preg_match('/MLSID #(.*?)Email target:/', $emailBody, $mlsNumber);
        $mlsNumber = trim($mlsNumber[1], " ");
        if($mlsNumber)$fieldsArray['mls'] = $mlsNumber;
//        preg_match('/Basic Property Attributes:(.*?)Bed:/', $emailBody, $propertyAttributes);
//        $propertyAttributes = trim($propertyAttributes[1], " ");
//        if($propertyAttributes)$fieldsArray['listing-price'] = $propertyAttributes;
//        preg_match('/\$[0-9]{0,3}\,{0,1}[0-9]{3}\,[0-9]{3}/', $emailBody, $listingPrice);
//        $listingPrice = $listingPrice[0];
//        $listingPrice = str_replace("$", "", $listingPrice);
//        $listingPrice = str_replace(",", "", $listingPrice);

    }
    //From Zillow.com
    if($fromZillow > 0){
        $sourceOfLead = 2;
        $sourceOfLeadName = "Zillow";
        $mlsNumber = $newEmailItem->fields['mls']->values;
        $propertyAddressSubject = $newEmailItem->fields['subject']->values;
        $exploded = explode('<br />', $emailBodyWithHtml);
        $explodedArray = [];
        foreach($exploded as $value){
            $value = strip_tags($value);
            if (!$emailAddress && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $emailAddress = strip_tags($value);
                $leadEmailAddress = trim($emailAddress, " ");
            }
            if(!$leadPhoneNumber) {
                preg_match('/\([0-9]{3}\)\s+[0-9]{3}.*[0-9]{4}/', $value, $phone);
                if ($phone > 0) {
                    preg_replace('/\([0-9]{3}\)\s+[0-9]{3}.*[0-9]{4}/', '', $phone);
                    $leadPhoneNumber = $phone[0];
                    $leadPhoneNumber = trim($leadPhoneNumber, " ");
                }
            }
        }
        $emailBody = preg_replace('/Premier Agent Logo/', '', $emailBody);

        preg_match('/A message from (.*?):/', $emailBody, $names);
        $names = trim($names[1], " ");

        preg_match('/A message from.*\:(.*?)2016 Zillow Group or its companies/', $emailBody, $comment);
        $comment = trim($comment[1], " ");

        preg_match('/\$[0-9]{0,3}\,{0,1}[0-9]{3}\,[0-9]{3}/', $emailBody, $listingPrice);
        //preg_match('/\$(.*?)\s/', $emailBody, $listingPrice);
        $listingPrice = $listingPrice[0];
        $listingPrice = str_replace("$", "", $listingPrice);
        $listingPrice = str_replace(",", "", $listingPrice);

        preg_match('/ABOUT THIS PROPERTY(.*?)A message from/', $emailBody, $propertyAttributes);
        $propertyAttributes = trim($propertyAttributes[1], " ");

        preg_match("/I am interested in (.*?)\./", $emailBody, $propertyAddress);
        $propertyAddress = trim($propertyAddress[1], " ");

        $endOfLeadInfo = strpos($emailBody,'2016 Zillow Group or its companies');
        $emailBody = substr_replace($emailBody, "", $endOfLeadInfo, $emailLength);

//        $inquiredAbout = trim($inquiredAbout[1], " ");
//        $propertyAddress = trim($propertyAddress[1], " ");
//        $bedCount = trim($bedCount[1], " ");
//        $bathCount = trim($bathCount[1], " ");
        $namesArray = explode(" ", $names);
        if($namesArray[0]){
            $leadFirstName = $namesArray[0];
            $fieldsArray['first-name'] = $leadFirstName;
        }
        if($namesArray[1]){
            $leadLastName = $namesArray[1];
            $fieldsArray['text'] = $leadLastName;
        }
        if($leadEmailAddress)$fieldsArray['email-address-2'] = $leadEmailAddress;
        if($leadPhoneNumber)$fieldsArray['phone-number-2'] = $leadPhoneNumber;
        if($propertyAddress)$fieldsArray['property-address'] = $propertyAddressSubject;
        if($comment)$fieldsArray['comment'] = $comment;
        if($listingPrice)$fieldsArray['listing-price'] = (int)$listingPrice;
        if($propertyAddress)$fieldsArray['property-address-2'] = $propertyAddress;
        if($emailBody)$fieldsArray['body'] = $emailBody;
        //if($inquiredAbout)$fieldsArray['this-consumer-inquired-about'] = $inquiredAbout;
    }

    //Set Necessary Values for Email and Text Specific to the Listing Agent
    if($agentAndrew > 0 || $agentAndrewEmail > 0 ){
        $agentFirstName = 'Andrew';
        $agentFullName = 'Andrew Virostek';
        $agentEmail = 'avirostek@kw.com';
        $facchineAgentId = 191297910;
        $agentCellNumber = '610-470-9618';
        $agentEmailSignature = '<html><head><meta https-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8"><title>The Facchine Group</title><style type="text/css" media="all"> p.ecxMsoNormal {Margin:0px;Margin-bottom:0px;} .aBn { border-bottom: none; text-decoration: none; } .gc-cs-link { text-decoration:none !important; } div.gt a, div.ii a[href] { } .widen{width:100%;} </style></head><body> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 14px; line-height: 16px; font-weight: normal; color: #000000; text-align: left; border-spacing: 0px;"><tbody><tr><td width="8"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td valign="middle"> <img width="125" height="123" src="https://esr-storage.s3.amazonaws.com/images/9339/53349/default/images/2407558f7a4e8898c2.jpeg?override=1492636338" style="border: none; display: block; width: 125px; height: 123px;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td> <td width="5" style="background-color: #000000;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"> <span style="text-decoration:none;"> <font face="Verdana, Geneva, sans-serif" style="text-decoration:none !important;"> Andrew Virostek </font> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="5"></td> <td valign="middle" width="0" style="font-size: 12px; line-height: 14px; color: #000000; white-space: nowrap;"> <span style="display:inline-block"> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"><span style="text-decoration:none;"><font face="Verdana, Geneva, sans-serif" style="text-decoration:none !important;">Realtor</font></span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> The Facchine Group </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> Keller Williams Real Estate </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Office 610-828-2224 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Cell 610-470-9618 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> <a href="mailto:avirostek@kw.com" target="_blank" style="color: #000000; text-decoration: none;">avirostek@kw.com</a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> <span style="font-weight: bold; color: #000000;"> <span style="font-weight:normal;font-style:normal"></span> </span> <span style="color: #000000;"> <a ng-href="https://esig.ly/links/246035" target="_blank" href="https://esig.ly/links/246035" style="color: #000000; text-decoration: none;">Visit Our Website</a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> 625 West Ridge Pike Bldg F, Conshohocken, Pa 19428 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246034" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246034"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53349/icons/58880dff6a5ed.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246036" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246036"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53349/icons/58880dff885bc.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246037" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246037"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53349/icons/58880dffaee30.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-family:Arial, Helvetica, sans-serif;font-size:10px;color:#a1a1a1;line-height:12px;"> <font face="Arial, Helvetica, sans-serif" style="font-style: normal !important;font-weight: normal !important;"> If you have received this email in error please notify the system manager. This message contains confidential information and is intended only for the individual named. If you are not the named addressee you should not disseminate, distribute or copy this e-mail. Please notify the sender immediately by e-mail if you have received this e-mail by mistake and delete this e-mail from your system. If you are not the intended recipient you are notified that disclosing, copying, distributing or taking any action in reliance on the contents of this information is strictly prohibited. </font> <font face="Arial, Helvetica, sans-serif"> </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-style: normal; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #a1a1a1; line-height: 12px;"> <font face="Arial, Helvetica, sans-serif"> <img style="border:none;display:inline-block;float:left;margin-right:4px;width:10px;height:10px;" width="10" height="10" src="https://esr-storage.s3.amazonaws.com/images/9339/53349/icons/leaf-icon-sml.gif"> Think before you print. </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-size: 17px; font-weight: normal; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></body></html>';
    }
    elseif($agentLauren > 0 || $agentLaurenEmail > 0 ){
        $agentFirstName = 'Lauren';
        $agentFullName = 'Lauren Virostek';
        $agentEmail = 'laurenvirostek@gmail.com';
        $facchineAgentId = 191277710;
        $agentCellNumber = '267-733-3416';
        $agentEmailSignature = '<html><head><meta https-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8"><title>The Facchine Group</title><style type="text/css" media="all"> p.ecxMsoNormal {Margin:0px;Margin-bottom:0px;} .aBn { border-bottom: none; text-decoration: none; } .gc-cs-link { text-decoration:none !important; } div.gt a, div.ii a[href] { } .widen{width:100%;} </style></head><body> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 14px; line-height: 16px; font-weight: normal; color: #000000; text-align: left; border-spacing: 0px;"><tbody><tr><td width="8"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td valign="middle"> <img width="125" height="123" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/default/images/4046358f7a4e8898c2.jpeg?override=1492636338" style="border: none; display: block; width: 125px; height: 123px;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td> <td width="5" style="background-color: #000000;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"> <span style="text-decoration:none;"> <font style="text-decoration: none !important; font-family: Verdana, Geneva, sans-serif; font-size: 12px;"> Lauren Virostek </font> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="5"></td> <td valign="middle" width="0" style="font-size: 12px; line-height: 14px; color: #000000; white-space: nowrap;"> <span style="display:inline-block"> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"><span style="text-decoration:none;"><font face="Verdana, Geneva, sans-serif" style="text-decoration:none !important;">Realtor/Client Care Coordinator</font></span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font style="font-family: Verdana, Geneva, sans-serif; line-height: 16px; font-size: 12px;"> The Facchine Group </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> Keller Williams Real Estate </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Office 610-828-2224 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Cell 267-733-3416 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> <a href="mailto:Facchinegroup@gmail.com" target="_blank" style="color: #000000; text-decoration: none;">Facchinegroup@gmail.com</a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> <span style="font-weight:normal;font-style:normal"></span> </span> <span style="color: #000000;"> <a target="_blank" href="https://esig.ly/links/247777" style="color: #000000; text-decoration: none;">Visit Our Website</a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> 625 West Ridge Pike Bldg F, Conshohocken, Pa 19428 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/247774"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/icons/5967bf74474f3.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/247775"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/icons/5967bf74530da.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/247778"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/icons/5967bf745d435.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/247776"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/icons/5967bf74695fc.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-family:Arial, Helvetica, sans-serif;font-size:10px;color:#a1a1a1;line-height:12px;"> <font face="Arial, Helvetica, sans-serif" style="font-style: normal !important;font-weight: normal !important;"> <span>If you have received this email in error please notify the system manager. This message contains confidential information and is intended only for the individual named. If you are not the named addressee you should not disseminate, distribute or copy this e-mail. Please notify the sender immediately by e-mail if you have received this e-mail by mistake and delete this e-mail from your system. If you are not the intended recipient you are notified that disclosing, copying, distributing or taking any action in reliance on the contents of this information is strictly prohibited.</span> </font> <font face="Arial, Helvetica, sans-serif"> </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-style: normal; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #a1a1a1; line-height: 12px;"> <font face="Arial, Helvetica, sans-serif"> <img style="border:none;display:inline-block;float:left;margin-right:4px;width:10px;height:10px;" width="10" height="10" src="https://esr-storage.s3.amazonaws.com/images/9339/64262/icons/leaf-icon-sml.gif"> Think before you print. </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-size: 17px; font-weight: normal; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></body></html>';
    }
    else{
        $sendMessagesToClient = true;
        $agentFirstName = 'Ernie';
        $agentFullName = 'Ernie Facchine';
        $agentEmail = 'efacchine11@kw.com';
        $facchineAgentId = 191297909;
        $agentCellNumber = '610-721-7599';
        $agentEmailSignature = '<html><head><meta https-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="UTF-8"><title>The Facchine Group</title><style type="text/css" media="all"> p.ecxMsoNormal {Margin:0px;Margin-bottom:0px;} .aBn { border-bottom: none; text-decoration: none; } .gc-cs-link { text-decoration:none !important; } div.gt a, div.ii a[href] { } .widen{width:100%;} </style></head><body> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 14px; line-height: 16px; font-weight: normal; color: #000000; text-align: left; border-spacing: 0px;"><tbody><tr><td width="8"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td valign="middle"> <img width="125" height="123" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/default/images/3209558f7a4e8898c2.jpeg?override=1492636338" style="border: none; display: block; width: 125px; height: 123px;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td> <td width="5" style="background-color: #000000;"></td><td style="font-size:1px;white-space: nowrap;width:20px;" width="20"></td><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; line-height: 2px; border-collapse: collapse; border-spacing: 0px; margin: 0px; padding: 0px;"><tbody><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"> <span style="text-decoration:none;"> <font ng-style="{\'font-family\': nameFace, \'font-size\': design.textSize + design.nameTextSize + \'px\'}" style="text-decoration: none !important; font-family: Verdana, Geneva, sans-serif; font-size: 12px;"> Ernie L. Facchine III </font> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="5"></td> <td valign="middle" width="0" style="font-size: 12px; line-height: 14px; color: #000000; white-space: nowrap;"> <span style="display:inline-block"> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; line-height: 16px; color: #000000; white-space: nowrap;"><span style="text-decoration:none;"><font face="Verdana, Geneva, sans-serif" style="text-decoration:none !important;">Realtor / Investing Partner</font></span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 12px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 16px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font ng-style="{\'font-family\': companyFace, \'line-height\': (design.textSize + design.companyTextSize + 4) + \'px\', \'font-size\': design.textSize + design.companyTextSize + \'px\'}" style="font-family: Verdana, Geneva, sans-serif; line-height: 16px; font-size: 12px;"> The Facchine Group </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">•</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> Keller Williams Real Estate </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Office 610-828-2224 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> Cell 610-721-7599 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> <a href="mailto:efacchine@comcast.net%20" target="_blank" style="color: #000000; text-decoration: none;">efacchine@comcast.net </a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> <font face="Verdana, Geneva, sans-serif">|</font> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap; font-weight: bold;"> <font face="Verdana, Geneva, sans-serif"> <span style="font-weight: bold; color: #000000;"> <span style="font-weight:normal;font-style:normal"></span> </span> <span style="color: #000000;"> <a ng-href="https://esig.ly/links/246598" target="_blank" href="https://esig.ly/links/246598" style="color: #000000; text-decoration: none;">Visit Our Website</a> </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-size: 1px; font-family: Verdana, Geneva, sans-serif; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td valign="top" height="16" style="font-size: 12px; color: #000000; line-height: 16px; white-space: nowrap;"> <font face="Verdana, Geneva, sans-serif"> <span style="color: #000000;"> 625 West Ridge Pike Bldg F, Conshohocken, Pa 19428 </span> </font> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td valign="middle" width="0" style="font-size: 12px; color: #000000; line-height: 14px; white-space: nowrap;"> <span> <span> </span> </span> </td> <td valign="top" style="line-height:1px;font-size:1px;" width="6"></td> <td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td> <table cellpadding="0" cellspacing="0" border="0" style="font-family: Verdana, Geneva, sans-serif; font-size: 1px; font-weight: normal; color: #000000; text-align: left; line-height: 1px; border-spacing: 0px;"><tbody><tr><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246602" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246602"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/icons/58861b1432979.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246599" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246599"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/icons/588619514c737.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246600" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246600"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/icons/588619510d5ff.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td><td style="font-size: 12px; line-height: 16px; white-space: nowrap;"> <a ng-href="https://esig.ly/links/246601" target="_blank" style="border:none;text-decoration:none;display:inline-block;" href="https://esig.ly/links/246601"> <img width="24" height="24" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/icons/58861b1e1af3d.png" style="border: none; display: block; width: 24px; height: 24px;"></a> </td> <td style="line-height:1px;font-size:1px;" width="4"></td> </tr></tbody></table></td></tr><tr><td style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 10px; line-height: 10px; height: 10px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></td></tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-family:Arial, Helvetica, sans-serif;font-size:10px;color:#a1a1a1;line-height:12px;"> <font face="Arial, Helvetica, sans-serif" style="font-style: normal !important;font-weight: normal !important;"> If you have received this email in error please notify the system manager. This message contains confidential information and is intended only for the individual named. If you are not the named addressee you should not disseminate, distribute or copy this e-mail. Please notify the sender immediately by e-mail if you have received this e-mail by mistake and delete this e-mail from your system. If you are not the intended recipient you are notified that disclosing, copying, distributing or taking any action in reliance on the contents of this information is strictly prohibited. </font> <font face="Arial, Helvetica, sans-serif"> </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-style: normal; font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #a1a1a1; line-height: 12px;"> <font face="Arial, Helvetica, sans-serif"> <img style="border:none;display:inline-block;float:left;margin-right:4px;width:10px;height:10px;" width="10" height="10" src="https://esr-storage.s3.amazonaws.com/images/9339/53234/icons/leaf-icon-sml.gif"> Think before you print. </font> </td> </tr><tr><td style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr><tr><td style="font-size: 17px; font-weight: normal; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"><div style="font-size: 17px; line-height: 17px; height: 17px; margin: 0px; padding: 0px; display: block;"></div></td></tr></tbody></table></td></tr></tbody></table></body></html>';
    }
    $fieldsArray['owner'] = $facchineAgentId;
    $fieldsArray['source'] = $sourceOfLead;

    $inquiryResponseEmailItem = PodioItem::get(712177523);
    $inquiryResponseTextItem = PodioItem::get(712169435);
    $newLeadEmailToAgentItem = PodioItem::get(712159603);
    $newLeadTextToAgentlItem = PodioItem::get(712163625);

    $emailToLeadBody = $inquiryResponseEmailItem->fields['email-html']->values;
    $emailToLeadSubject = $inquiryResponseEmailItem->fields['email-subject']->values;
    $emailToLeadRecipients = $inquiryResponseEmailItem->fields['recipients']->values;
    $textToLeadBody = $inquiryResponseTextItem->fields['email-html']->values;
    $textToLeadRecipients = $inquiryResponseTextItem->fields['recipients']->values;
    $emailToAgentBody = $newLeadEmailToAgentItem->fields['email-html']->values;
    $emailToAgentSubject = $newLeadEmailToAgentItem->fields['email-subject']->values;
    $textToAgentBody = $newLeadTextToAgentlItem->fields['email-html']->values;

    $emailToLeadSubject = str_replace('{{mlsNumber}}', $mlsNumber, $emailToLeadSubject);
    $emailToLeadSubject = str_replace('{{propertyAddress}}', $propertyAddress, $emailToLeadSubject);
    $emailToLeadBody = str_replace('{{leadFirstName}}', $leadFirstName, $emailToLeadBody);
    $emailToLeadBody = str_replace('{{agentCellNumber}}', $agentCellNumber, $emailToLeadBody);
    $emailToLeadBody = str_replace('{{agentFirstName}}', $agentFirstName, $emailToLeadBody);
    $emailToLeadBody = str_replace('{{agentEmail}}', $agentEmail, $emailToLeadBody);
    $emailToLeadBody = str_replace('{{mlsNumber}}', $mlsNumber, $emailToLeadBody);
    $textToLeadBody = str_replace('{{leadFirstName}}', $leadFirstName, $textToLeadBody);
    $textToLeadBody = str_replace('{{agentFullName}}', $agentFullName, $textToLeadBody);
    $textToLeadBody = str_replace('{{sourceOfLeadName}}', $sourceOfLeadName, $textToLeadBody);
    $textToLeadBody = str_replace('{{mlsNumber}}', $mlsNumber, $textToLeadBody);
    $textToLeadBody = str_replace('{{agentCellNumber}}', $agentCellNumber, $textToLeadBody);
    $textToLeadBody = str_replace('{{propertyAddress}}', $propertyAddress, $textToLeadBody);
    $emailToAgentBody = str_replace('{{agentFirstName}}', $agentFirstName, $emailToAgentBody);
    $emailToAgentBody = str_replace('{{sourceOfLeadName}}', $sourceOfLeadName, $emailToAgentBody);
    $emailToAgentSubject = str_replace('{{mlsNumber}}', $mlsNumber, $emailToAgentSubject);
    $emailToAgentBody = str_replace('{{newLeadItemTitle}}', $newLeadItemTitle, $emailToAgentBody);
    $emailToAgentBody = str_replace('{{newLeadItemLink}}', $newLeadItemLink, $emailToAgentBody);
    $textToAgentBody = str_replace('{{agentFirstName}}', $agentFirstName, $textToAgentBody);
    $textToAgentBody = str_replace('{{sourceOfLeadName}}', $sourceOfLeadName, $textToAgentBody);
    $textToAgentBody = str_replace('{{newLeadItemTitle}}', $newLeadItemTitle, $textToAgentBody);
    $textToAgentBody = str_replace('{{newLeadItemLink}}', $newLeadItemLink, $textToAgentBody);
    $textToAgentBody = str_replace('{{mlsNumber}}', $mlsNumber, $textToAgentBody);
    $textToAgentBody = str_replace('{{mlsNumber}}', $mlsNumber, $textToAgentBody);
    $textToAgentBody = str_replace('{{propertyAddress}}', $propertyAddress, $textToAgentBody);
    $fields = array(
        'from' => 'support@techego.com',
        'to' => $agentEmail,
        'subject' => $emailToAgentSubject,
        'html' => nl2br($emailToAgentBody),
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $mailgunURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_USERPWD, $mailgunActiveApiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result);
    PodioComment::create('item', $itemId, array('value' => "Email sent to Facchine Agent. ($agentEmail)"));

    $recipientAgentCellNumber = preg_replace("/[^0-9]/", "", $agentCellNumber);
    $recipientAgentCellNumber = filter_var($recipientAgentCellNumber, FILTER_SANITIZE_NUMBER_INT);

    $client = new Twilio\Rest\Client($twilioAccountSid, $twilioAuthToken);
    $client->messages->create((int)$recipientAgentCellNumber,
        array(
            'from' => $twilioPhoneNumber,
            'body' => $textToAgentBody
        )
    );
    PodioComment::create('item', $itemId, array('value' => "Text message sent to Facchine Agent. ($agentCellNumber)"));


    if($sendMessagesToClient == true) {
        //TWILIO Send Welcome Text Message to Client
        if ($leadPhoneNumber) {
            $recipientCellNumber = preg_replace("/[^0-9]/", "", $leadPhoneNumber);
            $recipientCellNumber = filter_var($recipientCellNumber, FILTER_SANITIZE_NUMBER_INT);
            $client = new Twilio\Rest\Client($twilioAccountSid, $twilioAuthToken);
            $client->messages->create($recipientCellNumber,
                array(
                    'from' => $twilioPhoneNumber,
                    'body' => $textToLeadBody
                )
            );
            $fieldsArray['text-message-status'] = 2;
        } else {$fieldsArray['text-message-status'] = 5;}


        //  MAILGUN   Send Welcome Email
        if ($leadEmailAddress) {
            $emailBodyHtml = nl2br($emailToLeadBody.$agentEmailSignature);
            $fields = array(
                'from' => $agentEmail,
                'to' => $leadEmailAddress,
                'subject' => $emailToLeadSubject,
                'html' => $emailBodyHtml,
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $mailgunURL);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_USERPWD, $mailgunActiveApiKey);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($result);
            $fieldsArray['sent-email-status'] = 2;
        } else {$fieldsArray['sent-email-status'] = 12;}
    }

    PodioItem::update($itemId, array(
        'fields' => $fieldsArray
    ));

    return [
        'success' => true,
        'result' => $result,
    ];
}catch(Exception $e) {

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => 'Error: ' . $e,

        ]
    ];
}

//$textToAgentBody = str_replace('{{leadFirstName}}', $leadFirstName, $textToAgentBody);
//$textToAgentBody = str_replace('{{leadEmailAddress}}', $leadEmailAddress, $textToAgentBody);
//$textToAgentBody = str_replace('{{leadPhoneNumber}}', $leadPhoneNumber, $textToAgentBody);
//$textToAgentBody = str_replace('{{agentFullName}}', $agentFullName, $textToAgentBody);
//$textToAgentBody = str_replace('{{agentEmail}}', $agentEmail, $textToAgentBody);
//$textToAgentBody = str_replace('{{agentCellNumber}}', $agentCellNumber, $textToAgentBody);
//$textToAgentBody = str_replace('{{mlsNumber}}', $mlsNumber, $textToAgentBody);
//$textToAgentBody = str_replace('{{propertyAddress}}', $propertyAddress, $textToAgentBody);
//
//$textToLeadBody = str_replace('{{leadEmailAddress}}', $leadEmailAddress, $textToLeadBody);
//$textToLeadBody = str_replace('{{leadPhoneNumber}}', $leadPhoneNumber, $textToLeadBody);
//$textToLeadBody = str_replace('{{agentEmail}}', $agentEmail, $textToLeadBody);
//$textToLeadBody = str_replace('{{newLeadItemTitle}}', $newLeadItemTitle, $textToLeadBody);
//$textToLeadBody = str_replace('{{newLeadItemLink}}', $newLeadItemLink, $textToLeadBody);
//$textToLeadBody = str_replace('{{propertyAddress}}', $propertyAddress, $textToLeadBody);
//
//$emailToLeadSubject = str_replace('{{leadFirstName}}', $leadFirstName, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{agentFullName}}', $agentFullName, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{sourceOfLeadName}}', $sourceOfLeadName, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{agentCellNumber}}', $agentCellNumber, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{leadEmailAddress}}', $leadEmailAddress, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{leadPhoneNumber}}', $leadPhoneNumber, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{agentEmail}}', $agentEmail, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{newLeadItemTitle}}', $newLeadItemTitle, $emailToLeadSubject);
//$emailToLeadSubject = str_replace('{{newLeadItemLink}}', $newLeadItemLink, $emailToLeadSubject);

//
//$emailToLeadBody = str_replace('{{leadEmailAddress}}',