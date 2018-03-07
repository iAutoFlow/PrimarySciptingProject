<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/8/2017
 * Time: 10:17 AM
 */
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

// api/v2/JoshTEST?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

try{
    normalAuth();
    $payload = $event['request']['payload'];
//    $type = $payload['type'];
//    if($type && $type == 'hook.verify'){
//        $code = $payload['code'];
//        $hook_id = $payload['hook_id'];
//        // Validate the webhook
//        PodioHook::validate($hook_id, array('code' => $code));
//    }
//    $requestParams = $event['request']['parameters'];
//    $item_id = (int)$requestParams['item_id'];
//    if(!$item_id) {
//        $item_id = (int)$payload['item_id'];
//    }

//    $twilioAccountSid = 'ACf690895696c3103c7e7556db5ccb59dd';
//    $twilioAccountSid = 'PN81a837124a127cdfaee898e66ae1bce1';
//    $twilioAuthToken = '9b9fe457b874050349d2e9452f77b260';
//    $twilioPhoneNumber = "+16105462576";
//    $MessagingServiceSid = ;
//    $leadPhoneNumber = 8018651455;
//    $textBody = "This is a test sms for learning more about Twilio";
//    $recipientCellNumber = preg_replace("/[^0-9]/", "", $leadPhoneNumber);
//    $recipientCellNumber = filter_var($recipientCellNumber, FILTER_SANITIZE_NUMBER_INT);
//    $client = new Twilio\Rest\Client($twilioAccountSid, $twilioAuthToken);
//    $client->messages->create(8018651455,
//        array(
//            'from' => $twilioPhoneNumber,
//            'body' => $textToLeadBody,
//            'statusCallback' => 'https://hoist.thatapp.io/api/v2/facchineIncomingSMSHandler?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634',
//        )
//    );

    $leadContactAppID = 19833385;
    header('Content-Type: text/xml');


    
//    $smsStatus = $payload->messageStatus;
//    $smsDateSent = $payload->dateSent;
//    $smsDateUpdated = $payload->dateUpdated;
//    $smsDateCreated = $payload->dateCreated;
//    $smsSid = $payload->sid;
//    $smsMessageSid = $payload->messageSid;
//    $smsSmsSid = $payload->smsSid;
//    $smsAccountSid = $payload->accountSid;
//    $smsMessagingServiceSid = $payload->messagingServiceSid;
//    $smsFrom = $payload->from;
//    $smsTo = $payload->to;
//    $smsBody = $payload->body;
//    $smsNumMedia = $payload->numMedia;
    $smsSenderRaw = $payload['From'];
    $smsBody = $payload['Body'];
    $smsTo = $payload['To'];
//    PodioComment::create('item', 718372944, array('value'=>$smsStatus.  $smsDateSent.$smsDateUpdated.$smsDateCreated.$smsSid.$smsMessageSid.$smsSmsSid.$smsBody.$smsTo.$smsFrom.$smsNumMedia.$smsMessagingServiceSid));
//    exit;




    //$sid = 'SK33fd62d0cc3175111457089e600cc90b';
//    $sid = 'AP149c1f932ee19b4c28f8007512f13e49';
//    $friendlyName = 'IncomingSMSHandler';
//    $keyType = 'Standard';
//    $secret = 'bY3kRIpQdd3wzSAYY3gRjrP8PssgI69p';
//    $testAccountSid = 'ACda8bcc9f5611b738a139e5d4c736cd5f';
//    $testAuthToken = '98ed10c73e239693733365f413b19c02';
//
//    $client = new Client($testAccountSid, $testAuthToken);
//    $response = new Twilio\Twiml();
//    $response->say('Hello');
//    $response->play('https://api.twilio.com/cowbell.mp3', array("loop" => 5));


    $smsSender = substr($smsSenderRaw, -10);
    $leadclientFilter = PodioItem::filter($leadContactAppID, ['filters'=>['phone-number-2' => ['value'=>(int)$smsSenderRaw]]]);

    if(count($leadclientFilter) >= 1){
        $leadclientItemID = $leadclientFilter[0]->item_id;
        PodioComment::create('item', $leadclientItemID, array('value'=>" --- \n Incoming Message from ".$smsSenderRaw.": ".$smsBody." \n \n --- \n"));
    }




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

?>
