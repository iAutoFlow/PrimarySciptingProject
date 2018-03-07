<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
$curl = new \Curl\Curl();
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



try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

///AUTOMATION START

    $triggerItem = PodioItem::get($item_id);

    $triggerAppName = $triggerItem->app->config['name'];

    $triggerFiles = $triggerItem->files;

    if(strpos($triggerAppName, 'Milestone') !== false){

        $milestoneName = $triggerItem->fields['milestone-name']->values;

        $jobItemID = $triggerItem->fields['parent-job']->values[0]->item_id;

        $status = $triggerItem->fields['status']->values[0]['text'];

        if(!($status == "Completed" && strpos($milestoneName, "POP") !== false)){
            exit;
        }
    }
    elseif(strpos($triggerAppName, "Job") !== false){

        $jobItemID = $item_id;

        $status = $triggerItem->fields['creative-status']->values[0]['text'];

//        if($status != "Posted POP"){
//            exit;
//        }

    }

    $jobItem = PodioItem::get($jobItemID);

    $jobAppID = $jobItem->app_id;

    $dateNow = date('Y-m-d H:i:s');

    $folderLink = $jobItem->fields['box-folder']->values;

    $jobStatus = $jobItem->fields['creative-status']->values[0]['text'];

    $deliverables = $jobItem->fields['deliverable']->values; //array

    $jobBOMs = $jobItem->fields['bomb-element']->values; //array

    $jobDueDate = $jobItem->fields['job-due-date-2']->start;

    //$jobDueDateTime = strtotime($jobDueDate);

    if($jobDueDate) {
        $jobDueDateMonth = $jobDueDate->format('F');
    }

    $productLineItemID = $jobItem->fields['product']->values[0]->item_id;

    $jobUID = $jobItem->fields['unique-id']->values;

    $jobType = $jobItem->fields['job-type']->values;

    foreach($deliverables as $deliverable){

        $oppBoxID;
        $oppBoxLink;
        $oppName;
        $clientDNBName;
        $clientBoxID;

        $deliverableItem = PodioItem::get($deliverable->item_id);

        $delivEndDate = $deliverableItem->fields['end-date']->values;

        $PA = $deliverableItem->fields['pa']->values;

        $projectDashboardItemID = $deliverableItem->fields['project-dashboard']->values[0]->item_id;

        $scopeItemID = $deliverableItem->fields['project-scope']->values[0]->item_id;

        $teamName = $deliverableItem->fields['team-name']->values;

        if($teamName = "Channel Comm/Vertical Markets"){
            $teamName = "Channel Comm & Vertical Markets";
        }

        $delivSkuName = $deliverableItem->fields['im-sku']->values;

        $teamItemID = $deliverableItem->fields['team']->values[0]->item_id;

        $scopeItem = PodioItem::get($scopeItemID);

        $productLineItemID = $scopeItem->fields['description']->values[0]->item_id;

        $projectDashboardItem = PodioItem::get($projectDashboardItemID);

        $opportunityItemID = $projectDashboardItem->fields['project-title']->values[0]->item_id;

        $oppID = $projectDashboardItem->fields['opportunity-id']->values;

        $opportunityItem = PodioItem::get($opportunityItemID);

        $oppUniqueID = $opportunityItem->fields['unique-id']->values;

        $oppName = $opportunityItem->fields['title-2']->values;

        $soNumber = $opportunityItem->fields['sap-sales-order']->values;

        $oppBoxID = $opportunityItem->fields['box-folder-id']->values;

        $clientItemID = $opportunityItem->fields['client']->values[0]->item_id;

        $productLineItem = PodioItem::get($productLineItemID);

        $skuNum = $productLineItem->fields['title']->values;

        $clientItem = PodioItem::get($clientItemID);

        $clientName = $clientItem->fields['client-company-name']->values;

        $clientDNBName = $clientItem->fields['client-company-name']->values;

        $clientBoxID = $clientItem->fields['box-folder-id']->values;

        //Error Catching for Box ID
        if(!$oppBoxID){
            //Look for Opportunity Box Folder
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=".urlencode($oppName);
            $boxCurl = $curl->get($urlString);
            $boxResponse = json_decode($boxCurl);

            $oppBoxID = $boxResponse->id;

            if(!$oppBoxID){
                if($clientBoxID) {
                    $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolderGetLink?api_key=$df_api_key&connection_id=88&name=$oppUniqueID"."_".urlencode($oppName)."&parent_id=$clientBoxID";

                    $boxCurl3 = $curl->get($urlString);
                    $boxResponse3 = json_decode($boxCurl3);

                    $oppBoxID = $boxResponse3->id;
                    $oppBoxLink = $boxResponse3->url;
                }
                else{
                    //Look for Client Box Folder
                    $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=".urlencode($clientDNBName);
                    $boxCurl4 = $curl->get($urlString);
                    $boxResponse4 = json_decode($boxCurl4);

                    $clientBoxID = $boxResponse4->id;

                    if(!empty($clientBoxID)){
                        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolderGetLink?api_key=$df_api_key&connection_id=88&name=$oppUniqueID"."_".urlencode($oppName)."&parent_id=$clientBoxID";

                        $boxCurl5 = $curl->get($urlString);
                        $boxResponse5 = json_decode($boxCurl5);

                        $oppBoxID = $boxResponse5->id;
                        $oppBoxLink = $boxResponse5->url;
                    }
                }
            }
            else{
                $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$oppBoxID";
                $boxCurl2 = $curl->get($urlString);

                $oppBoxLink = $boxCurl2;
            }
        }
        else{

            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$oppBoxID";
            $boxCurl6 = $curl->get($urlString);

            $oppBoxLink = $boxCurl6;
        }
        //end Opp Box error catching


        if(!$oppBoxID){
            PodioComment::create('item', $deliverable->item_id, array('value'=>"Issue Finding/Creating Box Folder on Opportunity for this item. POP Not Posted."));
        }
        else{
            $boxLinkEmbed = PodioEmbed::create(array('url'=>$oppBoxLink));

            PodioItem::update($opportunityItemID, array('fields'=>array('box-folder-link'=>$boxLinkEmbed->embed_id, 'box-folder-id'=>$oppBoxID)));

            $boxLinkEmbed2 = PodioEmbed::create(array('url'=>$oppBoxLink));

            PodioItem::update($deliverable->item_id, array('fields'=>array('box-link'=>$boxLinkEmbed2->embed_id)));

        }

        //Add POP Folder to Opp Box Folder
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/$oppBoxID/items?api_key=$df_api_key&connection_id=88";
        $boxCurl9 = $curl->get($urlString);
        $boxResponse9 = json_decode($boxCurl9);

        $itemsEntries = $boxResponse9->entries;

        foreach($itemsEntries as $item){
            if($item->type == "folder" && $item->name == "POP"){
                $POPFolderID = $item->id;
            }
        }

        if(!$POPFolderID) {
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolderGetLink?api_key=$df_api_key&connection_id=88&name=POP&parent_id=$oppBoxID";
            $boxCurl8 = $curl->get($urlString);
            $boxResponse8 = json_decode($boxCurl8);

            $POPFolderID = $boxResponse8->id;
            $POPFolderLink = $boxResponse8->url;
        }
//        if(empty($boxCurl8)){
//            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/$oppBoxID/items?api_key=$df_api_key&connection_id=88";
//            $boxCurl9 = $curl->get($urlString);
//            $boxResponse9 = json_decode($boxCurl9);
//
//            $itemsEntries = $boxResponse9->item_collection->entries;
//
//            foreach($itemsEntries as $item){
//                if($item->type == "folder" && $item->name == "POP"){
//                    $POPFolderID = $item->id;
//                }
//            }
//        }
        if(!$POPFolderLink) {
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$POPFolderID";
            $boxCurl10 = $curl->get($urlString);

            $POPFolderLink = $boxCurl10;
        }
        $POPFolderEmbed = PodioEmbed::create(array('url'=>$POPFolderLink));

        PodioItem::update($jobItemID, array('fields'=>array('box-folder'=>$POPFolderEmbed->embed_id,'box-folder-id'=>$POPFolderID)));
        //END POP Folder

        $attachedFileCheck = false;

        $filename = "";

        $fileCounter = 0;

        foreach($triggerFiles as $file){
            $getFile = PodioFile::get($file->file_id);

            $file_content = $getFile->get_raw();



            $attachedFileCheck = true;

            $appendFilename = pathinfo($file->name);
            $appendExtension = $appendFilename['extension'];


            $filename="POP_".$oppID."_".$soNumber."_".$clientName."_".$oppName."_".$skuNum."_".$jobUID."_".$PA;

            if($delivEndDate && !$jobBOMs){
                $filename.="_".$delivEndDate;
            }
            if($jobBOMs && $jobDueDateMonth){
                $filename.="_".$jobDueDateMonth;
            }



            if(sizeof($triggerFiles) > 1) {
                $fileCounter .= 1;

                $filename .= "_" . $fileCounter;
            }

            $filename = str_replace( "/", "-", $filename);

            $filename.="." . $appendExtension;

            $path_to_file = "/home/hoist/web/hoist.thatapp.io/public_html/storage/app/temp/$filename";
            file_put_contents($path_to_file, $file_content);

            //Box Upload File
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/files/upload?api_key=$df_api_key&connection_id=88&parent_id=$POPFolderID&file_name=".urlencode($filename)."&path=/home/hoist/web/hoist.thatapp.io/public_html/storage/app/temp/".urlencode($filename);
            $boxCurl7 = $curl->get($urlString);
            $boxResponse7 = json_decode($boxCurl7);

            $uploadSuccess = $boxResponse7->total_count;

            //End Box Upload File

            if(empty($boxCurl7)){

                PodioComment::create('item', $item_id, array('value'=>"The following file was unable to be uploaded to Box folder with ID: $POPFolderID. This usually occurs when the file has already been uploaded, or a file with the same name exists in the Box folder.\n\nAttempted File: $filename"));

            }

            if($deliverable->item_id){
                PodioComment::create('item', $deliverable->item_id, array('value'=>"POP Milestone marked as Complete."));
            }

            if($jobStatus !== "Completed"){

                if(!empty($boxCurl7) || $attachedFileCheck != true){
                    PodioItem::update($jobItemID, array('fields'=>array('creative-status'=>"Ready to Bill")));
                }

            }

        }



    }//end Deliverable Loop


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