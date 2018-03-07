<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 3/28/2017
 * Time: 8:53 AM
 */
class PodioSessionManager {
    private static $connection_id = 191;
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
function meiPodioAppAuth($appName){
    $appId = 0;
    $appToken = "";
    //Databases
    if($appName == "Products"){
        $appId = 17976737;
        $appToken = "068d69db94ce4acd9b52a582011b37bd";
    }
    if($appName == "Jobs"){
        $appId = 17976754;
        $appToken = "ce88942821b64c5694d6002532b11083";
    }
    if($appName == "Tasks WBS"){
        $appId = 17976757;
        $appToken = "6e284528be3a41f2986d2b00ab9823b7";
    }


    Podio::authenticate_with_app($appId, $appToken);
    return $appId;
}
function createPodioItem($appName, $fieldsArray){
    $appId = meiPodioAppAuth($appName);
    $createItem = PodioItem::create($appId, $fieldsArray);
    $newItemId = $createItem->item_id;
    return $newItemId;
}
function updatePodioItem($appName, $itemId, $fieldsArray){
    $appId = meiPodioAppAuth($appName);
    PodioItem::update((int)$itemId, $fieldsArray, array('hook'=>false));
}
function searchTextInPDF($text, $search){
    preg_match_all('(.+'.$search.'.+)',$text ,$response);



    $count = count($response[0]);
    if($count >= 1) {
        $str = "";
        foreach ($response[0] as $found) {
            $str .= $found . "\n";
        }

        return $str;
    }
    else{return '<strong>'."No results found..".'Please check your spelling and try again.';}


}


function analyzeSentiment($fileText){
    $havenAPIKey = "da7c3d8c-467d-4b91-98bd-1cf62a73a7c3";
    $postParams = array(
        "text" => $fileText,
        "apikey"=>$havenAPIKey
    );

    $tagFileCurl = curl_init();
    curl_setopt($tagFileCurl, CURLOPT_URL, 'https://api.havenondemand.com/1/api/async/analyzesentiment/v2');//
    curl_setopt($tagFileCurl, CURLOPT_HEADER, false);
    curl_setopt($tagFileCurl, CURLOPT_POST, true);
    curl_setopt($tagFileCurl, CURLOPT_POSTFIELDS, $postParams);
    curl_setopt($tagFileCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tagFileCurl, CURLOPT_VERBOSE, true);
    $firstResponseToAsyncCall = curl_exec($tagFileCurl);
    $responseDecoded = json_decode($firstResponseToAsyncCall);
    $asyncJobID = $responseDecoded->jobID;
    curl_close($tagFileCurl);
    $analysis = checkCallStatus($asyncJobID);
    return $analysis->sentiment_analysis;
}


//START OF SCRIPT VIA PODIO FILE.CHANGE WEBHOOK ON PRODUCT ITEMS/////////////////////////////////

Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array());
try {
    $payload = $event['request']['payload'];
    $type = $payload['type'];
    if ($type && $type == 'hook.verify') {
        $code = $payload['code'];
        $hook_id = $payload['hook_id'];
        PodioHook::validate($hook_id, array('code' => $code));
    }

    $productItemId = $payload['item_id'];
    //$requestParams = $event['request']['parameters'];
    //$productItemId = $requestParams['item_id'];


    meiPodioAppAuth("Products");
    $productItem = PodioItem::get((int)$productItemId);
    $triggerStatus = $productItem->fields['process-instruction-manual']->values[0]['text'];
    if($triggerStatus == "Search for text"){
        $updateProductStatus = array('fields'=>array('process-instruction-manual'=>"Processing"));
        $search = $productItem->fields['search-pdf-manual']->values;
        $search = strip_tags($search);
        updatePodioItem("Products", $productItemId, $updateProductStatus);
    }else{exit;}


//Product Manual File
    $productFiles = $productItem->files;
    $productManuelFileId = $productFiles[0]->file_id;
    $productManual = PodioFile::get((int)$productManuelFileId);
    $fileName = $productManual->name;
    $fileData = $productManual->get_raw();

//Download / Save file locally
    $localFilePath = '/home/hoist/web/hoist.thatapp.io/public_html/public/img/clients/mei_product_manual-' . $productTitle . $productManuelFileId . '.pdf';
    file_put_contents($localFilePath, $fileData);

///Start Parse File////////////////////////////
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($localFilePath);
    $text = $pdf->getText();

    $sentiment = analyzeSentiment($text);

    dd($sentiment);


    unlink($localFilePath);
    $result = searchTextInPDF($text, $search);


    $updateProductStatus = array('fields'=>array('process-instruction-manual'=>"Done", "search-response"=>$result));
    updatePodioItem("Products", $productItemId, $updateProductStatus);

    return [
        'success' => true,
        'result' => $productItemId,
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