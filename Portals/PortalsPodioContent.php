<?php
$curl = new \Curl\Curl();
$df_api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
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

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = (int)$requestParams['item_id'];

///AUTOMATION START

    $item = PodioItem::get($item_id);

    $imageFileID = $item->fields['images']->values[0]->file_id;

    $header = $item->fields['header']->values;

    $content = $item->fields['content']->values;

    $type = $item->fields['type']->values[0]['text'];

    $page = $item->fields['page']->values[0]['text'];

    $section = $item->fields['section']->values[0]['text'];

    $settings = $item->fields['settings']->values[0]['text'];

    $hoistCurl = "https://hoist.thatapp.io/api/v2/PortalsPodioGetFile?api_key=$df_api_key&file_id=" . $imageFileID;
    if($imageFileID){$imageLink = $curl->post($hoistCurl);

    $data = array(
        'item_id' => $item_id,
        'page' => $page,
        'section' => $section,
        'type' => $type,
        'imageLink' => $imageLink,
        'header' => $header,
        'content' => $content
    );

    $dataJson = json_encode($data);

    if($settings == "Portal Ready") {

        $urlString = "https://portalsivie.thatapp.io/add-content?data=" . urlencode($dataJson);
        $curl = $curl->post($urlString);

    }

    if($settings == "Update Content") {

        $urlString = "https://portalsivie.thatapp.io/add-content?data=" . urlencode($dataJson);
        $curl = $curl->post($urlString);

        PodioItem::update($item_id, array('fields'=>array('settings'=>2)));

    }

    if($settings == "Archived") {

        $urlString = "https://portalsivie.thatapp.io/remove-content?data=" . urlencode($dataJson);
        $curl = $curl->post($urlString);

    }

//END AUTOMATION

    return [
        'success' => true,
        'result' => $curl,
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