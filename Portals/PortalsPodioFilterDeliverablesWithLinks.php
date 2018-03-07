<?php
$curl = new \Curl\Curl();
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
class PodioSessionManager {
    private static $connection_id = 198;
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
    $app_id = 10827874;
    $attributesUrl = $requestParams['attributes'];

    $attributesJson = urldecode($attributesUrl);
    $attributesPHP = json_decode($attributesJson);
//    print_r($attributesPHP);exit;
///AUTOMATION START

    $filter = PodioItem::filter($app_id, $attributesPHP);
    //array('filters'=>array('client-dashboard'=>array(287444410))));

    $itemsArray = '[';

    foreach($filter as $item) {

        $itemsArray .= '{';

        $itemsArray .= '"end-date":"' . $item->fields['end-date']->values . '",';

        $itemsArray .= '"net-price-2":"' . number_format($item->fields['net-price-2']->values, 2) . '",';

        $itemsArray .= '"quantity":"' . str_replace(".0000", "", $item->fields['quantity']->values) . '",';

        $itemsArray .= '"sku-description":"' . $item->fields['sku-description']->values . '",';

        $itemsArray .= '"item_id":"' . $item->item_id . '"';

        ///////////

        $deliverable = PodioItem::get($item->item_id);

        $deliverableStatus = $deliverable->fields['status']->values;

        if($deliverableStatus == "In Progress" || $deliverableStatus == "Completed"){

            try {
                $itemReferences = PodioItem::get_references($itemId);
            }
            catch(Exception $e){
                $itemReferences = array();
            }


            foreach($itemReferences as $itemRef){

                if($itemRef['app']['app_id'] == 14269585 || $itemRef['app']['app_id'] == 13869166 || $itemRef['app']['app_id'] == 14276642 || $itemRef['app']['app_id'] == 14276675 || $itemRef['app']['app_id'] == 14276676){
                    $jobItemID = $itemRef['items'][0]['item_id'];
                }

            }

            if($jobItemID) {
                $jobReferences = PodioItem::get_references($jobItemID);

                foreach($jobReferences as $jobRef) {

                    if($jobRef['app']['app_id'] == 14269597 || $jobRef['app']['app_id'] == 13869287 || $jobRef['app']['app_id'] == 14277392 || $jobRef['app']['app_id'] == 14276762 || $jobRef['app']['app_id'] == 14276766) {

                        foreach($jobRef['items'] as $milestoneRefItem) {

                            $milestoneItem = PodioItem::get($milestoneRefItem['item_id']);

                            if($deliverableStatus == "In Progress" && strpos($milestoneItem->fields['milestone-name']->values, "Wufoo") !== false) {

                                $deliverableLink = $milestoneItem->fields['materials-collection-wufoo-link']->values;

                                $linkTitle = "Link to Data Collection Form";

                            }

                            if($deliverableStatus == "Completed" && strpos($milestoneItem->fields['milestone-name']->values, "Post POP") !== false) {

                                $popFileName = $milestoneItem->files[0]->name;

                                $jobUID = $milestoneItem->fields['job-number']->values;

                                if(empty($jobUID)) {
                                    $jobUID = $milestoneItem->fields['job-number-2']->values;
                                }

                                $jobItem = PodioItem::get($jobItemID);

                                $jobBoxFolderiD = $jobItem->fields['box-folder-id']->values;

                                $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/" . urlencode($jobBoxFolderiD) . "?api_key=$df_api_key&connection_id=88";
                                $boxCurl = $curl->get($urlString);
                                $boxResponse = json_decode($boxCurl);


                                foreach($boxResponse->item_collection->entries as $fileEntry) {

                                    $fileLoopName = "";
                                    $fileLoopName = $fileEntry->name;

                                    if($fileEntry->type == "file" && strpos($fileLoopName, $jobUID) !== false) {
                                        $popFileID = $fileEntry->id;
                                    }

                                }

                                $urlString2 = "https://hoist.thatapp.io/api/v2/boxPHP/files/createSharedLink?api_key=$df_api_key&connection_id=88&file_id=" . urlencode($popFileID);
                                $boxCurl2 = $curl->get($urlString2);
//                        $boxResponse2 = json_decode($boxCurl2);

                                $deliverableLink = $boxCurl2;

                                $linkTitle = "Link to POP";

                            }

                        }

                    }

                }


                if(!$deliverableLink->message) {

                    $deliverableLinkHtml = "<a href='$deliverableLink' title='$linkTitle'>$deliverableStatus</a>";

                    $itemsArray .= ', "status_link":"' . $deliverableLinkHtml . '"';


                } else {
                    $itemsArray .= ', "status_link":"' . $deliverableStatus . '"';
                }
            } else {
                $itemsArray .= ', "status_link":"' . $deliverableStatus . '"';
            }
        }
        else{
            $itemsArray .= ', "status_link":"' . $deliverableStatus . '"';
        }

        /////////
        $itemsArray .= '}';
        $itemsArray .= ',';
    }
    $itemsArray = rtrim($itemsArray, ",");
    $itemsArray.=']';


    //$itemsJSON = json_encode($itemsArray);

//END AUTOMATION
//    header('Content-type: application/json');
//    print_r($itemsArray);
//    exit;

    $event['response'] = [
        'status_code' => 200,
        'content' => $itemsArray,
        'content_type' => 'json'
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