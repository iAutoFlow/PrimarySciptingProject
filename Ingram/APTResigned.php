<?php
$df_api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
$curl = new \Curl\Curl();
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

    //Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];
//END Get data from Webhook

//Get APT Item that was triggered
    $apt_item = PodioItem::get($item_id);
//END get APT item

    //set Signature count to blank
    PodioItem::update($item_id, array('number-of-signatures'=>[]));

//Get APT Item field values
    //Opportunity Relationship field
    $OpportunityItemID = $apt_item->fields['opportunity']->values[0]->item_id;

    //Docverify field
    $DocVerify = $apt_item->fields['docverify']->values[0]['text'];


//END Get APT field values

//Check Trigger
    if($DocVerify != "Resigned"){
        $result ="Trigger was not 'Resigned', call ended";
        return $result;
    }
//END Check Trigger

//Get Opportunity Item and field values
    $opp_item = PodioItem::get($OpportunityItemID);

    //Opportunity Name
    $oppName = $opp_item->fields['title-2']->values;

    //Client Relationship field
    $ClientItemID = $opp_item->fields['client']->values[0]->item_id;

    //Opportunity Box Folder ID field
    $oppBoxID = $opp_item->fields['box-folder-id']->values;

    $oppReferences = PodioItem::get_references($OpportunityItemID);

    foreach($oppReferences as $oppReference){
        if($oppReference['app']['app_id'] == 13906476){
            $dashboardItemID = $oppReference['items'][0]['item_id'];
        }
    }

    PodioItem::update($dashboardItemID, array('fields'=>array('push-to-teams-2'=>"Review - Contract Amended")));

//END Get Opportunity Item and field values


//Get Client Item and field Values
    $client_item = PodioItem::get($ClientItemID);

    //client DnB Name
    $clientDNBName = $client_item->fields['client-company-name']->values;

    //get Client Box Folder ID
    $clientBoxID = $client_item->fields['box-folder-id']->values;

    //get Client Box Folder Link
    $clientBoxLink = $client_item->fields['box-link']->values[0]->original_url;

//END Get Client Item and field Values



//Box Folders Check
    if(!$clientBoxID) {
        //Look for Client Box Folder
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=$clientDNBName";
        $boxCurl4 = $curl->get($urlString);
        $boxResponse4 = json_decode($boxCurl4);

        $clientBoxID = $boxResponse4->id;
    }
    if(!$clientBoxLink){
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$clientBoxID";
        $boxCurl6 = $curl->get($urlString);

        $clientBoxLink = $boxCurl6;
    }
    if (!$oppBoxID) {
        //Look for Opportunity Box Folder
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=" . urlencode($oppName);
        $boxCurl = $curl->get($urlString);
        $boxResponse = json_decode($boxCurl);

        $oppBoxID = $boxResponse->id;

        if ($oppBoxID) {
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$oppBoxID";
            $boxCurl2 = $curl->get($urlString);

            $oppBoxLink = $boxCurl2;
        } else {
            $urlString3 = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolderGetLink?api_key=$df_api_key&connection_id=88&name=" . urlencode($oppName) . "&parent_id=$clientBoxID";
            $boxCurl3 = $curl->get($urlString3);
            $boxResponse3 = json_decode($boxCurl3);

            $oppBoxID = $boxResponse3->id;
            $oppBoxLink = $boxResponse3->url;
        }
    }
    else{
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$oppBoxID";
        $boxCurl6 = $curl->get($urlString);

        $oppBoxLink = $boxCurl6;
    }
//END Box Folders Check


    //Update Box ID/Folder on Opp
    $oppBoxLinkEmbed = PodioEmbed::create(array('url'=>$oppBoxLink));

    PodioItem::update($OpportunityItemID, array('fields'=>array('box-folder-link'=>$oppBoxLinkEmbed->embed_id,'box-folder-id'=>$oppBoxID)));


//Upload Signed Contract

    $signedContractFileID = $apt_item->files[sizeof($apt_item->files)-1]->file_id; //getting last uploaded file ID

    $getFile = PodioFile::get($signedContractFileID);

    $file_content = $getFile->get_raw();

    $attachedFileCheck = true;

    $filename = "AMEND_";
    $filename.=$getFile->name;

    $path_to_file = "/var/www/storage/app/temp/$filename";
    file_put_contents($path_to_file, $file_content);

    //Box Upload File
    $urlString = 'https://hoist.thatapp.io/api/v2/boxPHP/files/upload?api_key='.urlencode($df_api_key).'&connection_id=88&parent_id='.urlencode($oppBoxID).'&file_name='.urlencode($filename).'&path=/var/www/storage/app/temp/'.urlencode($filename);
    $boxCurl7 = $curl->get($urlString);
    $boxResponse7 = json_decode($boxCurl7);

    $uploadUrl = $boxResponse7->url;

    //End Box Upload File

//End upload signed Contract

    //Run Fix it Buttons
    PodioItem::update($OpportunityItemID, array('fields'=>array('fix-it'=>'Fix Actuals')));
    sleep(5);
    PodioItem::update($OpportunityItemID, array('fields'=>array('fix-it'=>'Fix Deliverables')));

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