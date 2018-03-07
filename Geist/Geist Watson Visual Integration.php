<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/17/2016
 * Time: 1:06 PM
 */
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

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $Status = $item->fields['approval-status']->values[0]['text'];

    if($Status == "Approved") {


        $Image = $item->fields['screenshot-images']->values;
        $ImageID = $Image[0]->file_id;
        $ImageLINK = $Image[0]->link;
        $ImageThumbNailLink = $Image[0]->thumbnail_link;


        $LocalFilePath = '/home/hoist/web/hoist.thatapp.io/public_html/public/img/clients/geist/' . $ImageID . '.jpg';

        $File = PodioFile::get($ImageID);

        $ImageData = $File->get_raw();

        file_put_contents($LocalFilePath, $ImageData);

        $ImageURL = '&url=https://hoist.thatapp.io/public/img/clients/geist/' . $ImageID . '.jpg';

        $api_key = "?api_key=1101cb067243a04783f35e85a329fe8eec1c75aa";
        $requesturl = "https://watson-api-explorer.mybluemix.net/visual-recognition/api/v3/classify";
        $curl = new \Curl\Curl();


        $version = "&version=2016-05-19";

        $params = $api_key;

        $fullURL = $requesturl . $api_key . $ImageURL . $version;

        $response = $curl->get($fullURL);
        $classification = $response;


        //$ClassArray = array();
        foreach ($classification->images[0]->classifiers[0]->classes as $class) {
            $ClassTitle = $class->class;
            $ClassScore = $class->score;
            $ClassHierarchy = $class->hierarchy;

            $CreateTag = PodioTag::create('item', $itemID, array($ClassTitle));

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