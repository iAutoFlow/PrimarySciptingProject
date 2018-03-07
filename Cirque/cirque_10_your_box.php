<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/12/2016
 * Time: 7:48 PM
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

function get($url, $param){

    $api_key='?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
    $curl = new \Curl\Curl();
    return $curl->get($url.$api_key.$param);
}

function createFolder($folderName, $root, $connection_id){

    $url = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolder/";
    $param = "&name=".urlencode($folderName)."&parent_id=$root&connection_id=$connection_id";

    return get($url,$param);

}

function getFolderIDByName($folderName, $connection_id){

    $url = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName/";
    $param = "&name=".urlencode($folderName)."&connection_id=$connection_id";

    return get($url, $param);
}

function getPodioBoxIDCreateIfNotFound($itemID, $root, $connection_id, $titleVar = 'title'){

    $BoxFolderIDFieldXID = 'box-folder-id';

    $item = PodioItem::get($itemID);
    $title = $item->fields[$titleVar]->values;

    $folderID = $item->fields['box-folder-id']->values;

    if (!$folderID || !is_numeric($folderID)) {

        $response = createFolder($title, $root, $connection_id);

        $id = json_decode($response)->id;

        if(!$id){

            $response = getFolderIDByName($title, $connection_id);

            $id = json_decode($response)->id;

        }

        if(!$id) $id = "Couldn't locate Box Folder: [".json_encode($title)."]";

        PodioItem::update($itemID, array('fields' => array($BoxFolderIDFieldXID => $id)), array('hooks' => false));

        $event['response'] = [
            'status_code' => 200,
            'content' => $response,
            'content_type' => "html"
        ];
        $response = '{"id":"' . $id . '"}';
        return $response;

    } else {

        $response = '{"id":"' . $folderID . '"}';

        $event['response'] = [
            'status_code' => 200,
            'content' => $response,
            'content_type' => "json"
        ];
        return $response;

    }

}

try{

    $requestParams = $event['request']['parameters'];

    $result = array();

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

    //get the box.com access_token************************************************************************************//

    $connection_id = 47;

    //get the hook details
    $filename = $requestParams['name'];
    $id = $requestParams['id'];
    $type = $requestParams['type'];
    $description = $requestParams['description'];
    $parent_id = $requestParams['parent_id'];

    $url = 'https://hoist.thatapp.io/api/v2/boxPHP/files/download/';

    $params = '&connection_id='.$connection_id;
    $params .= '&file_id='.$id;

    $file_content = get($url, $params);

    $path_to_log = "/home/hoist/web/hoist.thatapp.io/public_html/storage/app/cirque/cirque10out";

    $path_to_file = "/home/hoist/web/hoist.thatapp.io/public_html/storage/app/cirque/$filename";
    file_put_contents($path_to_file, $file_content);


    $source = new CurlFile(realpath($path_to_file));

    $response = Podio::post("/file/v2/", array('source' => $source, 'filename' => $filename), array('upload' => TRUE, 'filesize' => filesize($file_path)));

    $fileID = json_decode($response->body)->file_id;

    array_push($result, $fileID);

    $BrandsAppID = 8780211;
    $CampaignsAppID = 8699068;
    $StrategiesAppID = 8708019;
    $JobsAppID = 9063521;

    $boxFolderFieldID = 'box-folder-id';

    //filter $JobsAppID by field 'box-folder-id' = $parent_id
    $filterItems = PodioItem::filter($JobsAppID, array('filters'=>array('box-folder-id'=>$parent_id)));
    $itemID = $filterItems[0]->item_id;

    if(!$itemID){

        $filterItems = PodioItem::filter($StrategiesAppID, array('filters'=>array('box-folder-id'=>$parent_id)));
        $itemID = $filterItems[0]->item_id;

    }

    if(!$itemID){

        $filterItems = PodioItem::filter($CampaignsAppID, array('filters'=>array('box-folder-id'=>$parent_id)));
        $itemID = $filterItems[0]->item_id;

    }

    if(!$itemID){

        $filterItems = PodioItem::filter($BrandsAppID, array('filters'=>array('box-folder-id'=>$parent_id)));
        $itemID = $filterItems[0]->item_id;

    }

    if(!$itemID) $itemID = 454554528;

    $item = PodioItem::get($itemID);

    $itemFiles = $item->files;

    $existsCheck = false;
    foreach($itemFiles as $file){

        if($file->name == $filename){
            $existsCheck = true;
        }

    }

    if($existsCheck == false) {
        PodioFile::attach($fileID, array('ref_type' => 'item', 'ref_id' => $itemID));
    }

    $event['response'] = [
        'status_code' => 200,
        'content' => "file_id: $fileID",
        'content_type' => "json"
    ];

    return;

}catch(Exception $e) {

    file_put_contents($path_to_log, $e);

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: " . $e,

        ]
    ];
}

