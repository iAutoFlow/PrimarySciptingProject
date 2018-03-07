<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 4/6/2017
 * Time: 10:00 AM
 */
//OAuth with Podio
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
//START OF SCRIPT VIA PODIO FILE.CHANGE WEBHOOK ON PRODUCT ITEMS/////////////////////////////////
try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));
    $trainingTemplateAppId = 18288289;
    //Podio::authenticate_with_app(18288289, '6f155174d3694258890f8b4879b77bb6');
    $traingingApp = PodioApp::get(18288289);
    //$traingAppFeatures = PodioApp::features($trainingTemplateAppId);

    $coursesAppId = 17978024;

    $courseItems = PodioItem::filter(17978024);
    foreach($courseItems as $course) {
        $courseItem = PodioItem::get((int)$course->item_id);
        $courseTitle = $courseItem->fields['title']->values;
        $appAttributes = array(5294601, array('config'=>$traingingApp->config, array('name'=>$courseTitle)), array('fields'=>$traingingApp->fields));
        dd($appAttributes);
        $createApp = PodioApp::create();
    }





    return [
        'success' => true,
        'result' => $createApp,
    ];

}catch(Exception $e) {

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: " . $e,

        ]
    ];
}

?>