<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
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



try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Hook Verify and payload
//    $payload = $event['request']['payload'];
//    $type = $payload['type'];
//
//    if($type && $type == 'hook.verify'){
//
//        $code = $payload['code'];
//        $hook_id = $payload['hook_id'];
//
//        // Validate the webhook
//        PodioHook::validate($hook_id, array('code' => $code));
//
//    }
//
//    $requestParams = $event['request']['parameters'];
//    $item_id = (int)$requestParams['item_id'];
//
//    if(!$item_id) {
//        $item_id = $payload['item_id'];
//    }

///AUTOMATION START

    //Variables
    $customersAppID = 17982828;
    $ntpAppID = 17978239;

    //Code
    for($c = 0; $c < 4; $c++) {

        $offset = $c * 500;

        $customers = PodioItem::filter_by_view($customersAppID, 32957358, ['limit' => 500, 'offset' => $offset]);

        foreach($customers as $customer) {

            $references = PodioItem::get_references($customer->item_id);



            foreach($references as $reference) {

                if($reference['app']['app_id'] == $ntpAppID) {

                    $ntpItem = PodioItem::get($reference['items'][0]['item_id']);

                    $regionItemID = $ntpItem->fields['region']->values[0]->item_id;
                    break;

                }

            }

            PodioItem::update($customer->item_id, ['fields' => ['region' => [$regionItemID]]]);

        }

    }


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