<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/27/2016
 * Time: 10:52 AM
 */
$df_api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
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

    Podio::setup("mysql", "7dGCtjlc6VKU1u71769TTDrQsKSnpZri85WXTwxmIBKCUNVnV8etVTCdQI8qhBbM");

    $AppOauth = Podio::authenticate_with_app("14387546", "b3fd61a364fc42ff96a2ffb4fef5f6d3");



    //Get TRIGGER Item//////////////////////////////////////////////////////////
    $requestParams = $event['request']['parameters'];
    $appID = 17126806;

    $app = PodioApp::get(14387546);

    print_r($app);
    exit;

    foreach($appFields as $field){
        if($field->type == "image"){
            $fieldExternalID = $field->external_id;

            $appItems = \PodioItem::filter($appID);
            foreach($appItems as $item){
                $itemImageFile = $item->fields[$fieldExternalID]->values;
                if($itemImageFile){
                   $imageFile = PodioFile::get($itemImageFileID);
                   $imageFile['item_id'] = $item->item_id;
                   $imageFile['app_id'] = $appID;
                   $imageFile['space_id'] = $app->space_id;

                }
            }

            print_r($itemImage);
            exit;
        }


    }



    print_r($appFields);
    exit;



















//RETURN / CATCH
    return [
        'success' => true,
        'result' => $images,
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $images,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}