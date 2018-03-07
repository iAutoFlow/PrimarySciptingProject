<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/30/2017
 * Time: 11:38 AM
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

    $confirmationItem = PodioItem::get($itemId);

    $confirmationsAppId = 18827372;
    $ntpAppId = 0;
    $expensesAppId = 0;

    $emailSubject = $confirmationItem->fields['subject']->values;
    $emailBody = $confirmationItem->fields['email-body']->values;
    //$emailReservation = $confirmationItem->fields['reservation']->values;

    $emailBody = strip_tags($emailBody);


    //Get Booking Website from Subject Line
    $strposOfHotelsCom = stripos($emailSubject, "Hotels.com");
    $strposOfExpedia = stripos($emailSubject, "Expedia");
    if($strposOfHotelsCom !== false){$bookingWebsite = 2;}
    else{$bookingWebsite = 1;}


    //Get Confirmation/Itinerary # from Subject Line
    preg_match('/[0-9]{10,20}\d/', $emailSubject, $confirmationNumber);

    //$newEmailBody = str_replace($emailSubject, ' ', $emailBody);


    //Get Confirmation/Itinerary # from Subject Line
    preg_match('/\d\w{5,10}$/', $emailBody, $npmId);


    $fieldsArray = array(
        'fields'=>array(
        )
    );

    if($bookingWebsite){$fieldsArray['fields']['booking-website'] = $bookingWebsite;}
    if($confirmationNumber[0]){$fieldsArray['fields']['confirmation-number'] = $confirmationNumber[0];}
    if($npmId[0]){

        $filterNTP = PodioItem::filter(17978239, array('filters' => array('gon-calc' => $npmId[0])));

        if($filterNTP[0]->item_id){$fieldsArray['fields']['ntp'] = $filterNTP[0]->item_id;}
        $fieldsArray['fields']['ntp-unique-value'] = $npmId[0];
    }

    PodioItem::update($itemId, $fieldsArray);






//END AUTOMATION

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