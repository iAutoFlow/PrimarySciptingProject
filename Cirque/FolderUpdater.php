<?php

$curl = new \Curl\Curl();
$df_api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
$boxConnID = 47;
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

    $appID = $item->app->app_id;

    $updateTrigger = $item->fields['sync-files']->values[0]['text'];

    $boxFolderID = $item->fields['box-folder-id']->values;

    if($updateTrigger == "Update Folder IDs" || $updateTrigger == "Run"){

        //jobs
        if($appID == 9063521) {

            $strategyBoxID = $item->fields['strategy-folder-id']->values;

            $strategyItemID = $item->fields['strategy']->values[0]->item_id;

            if(!is_numeric($strategyBoxID) || empty($strategyBoxID)) {

                $urlString = "https://hoist.thatapp.io/api/v2/cirque_1_box?item_id=$strategyItemID&api_key=$df_api_key";
                $boxCurl = $curl->get($urlString);

                sleep(10);

                $item = PodioItem::get($item_id);

            }


            if(!is_numeric($boxFolderID) || empty($strategyBoxID)) {

                $urlString = "https://hoist.thatapp.io/api/v2/cirque_1_box?item_id=$item_id&api_key=$df_api_key";
                $boxCurl = $curl->get($urlString);


            }

            sleep(10);

            $item2 = PodioItem::get($item_id);

            $appID = $item2->app_id;

            $boxFolderID = $item2->fields['box-folder-id']->values;


            if(is_numeric($boxFolderID)) {

                PodioComment::create('item', $item_id, array('value'=>"Folder IDs Updated Successfully"));

            }
            else{

                PodioComment::create('item', $item_id, array('value'=>"Problem Updating Folder IDs, check that the Parent Items have valid Folder IDs"));

            }

            $urlString = "https://hoist.thatapp.io/api/v2/cirque_1_box?item_id=$item_id&api_key=$df_api_key";
            $boxCurl = $curl->get($urlString);
        }

        //strategies
        if($appID == 8708019) {

            if(!is_numeric($boxFolderID) || empty($boxFolderID)) {

                $urlString = "https://hoist.thatapp.io/api/v2/cirque_1_box?item_id=$item_id&api_key=$df_api_key";
                $boxCurl = $curl->get($urlString);

            }

            sleep(10);

            $item2 = PodioItem::get($item_id);

            $appID = $item2->app_id;

            $boxFolderID = $item2->fields['box-folder-id']->values;


            if(is_numeric($boxFolderID) || empty($boxFolderID)) {

                PodioComment::create('item', $item_id, array('value'=>"Folder IDs Updated Successfully"));

                PodioItem::update($item_id, array('fields'=>array('sync-files'=>"Updated")));

            }
            else{

                PodioComment::create('item', $item_id, array('value'=>"Problem Updating Folder IDs, check that the Parent Items have valid Folder IDs"));

                PodioItem::update($item_id, array('fields'=>array('sync-files'=>[])));

            }
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