<?php
//Author: Josh via Codeanywhere
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
    $item = PodioItem::get($item_id);

///AUTOMATION START//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//Step zero: Upload Attachment Function

    public function uploadAttachment($attachmentBody, $attachmentName, $parentObjectID) {
        $createFields = array(
            'Body' => base64_encode($attachmentBody),
            //    'ContentType' => $contentType,
            'Name' => $attachmentName,
            'ParentID' => $parentObjectID,
            'IsPrivate' => 'false'
        );

        $sObject = new stdclass();
        $sObject->fields = $createFields;
        $sObject->type = 'Attachment';

        echo "Creating Attachment";
        $upsertResponse = $this->SFConnection->create(array($sObject));
        print_r($upsertResponse);
    }

//Step one: Loop items, 500 at a time, check for file count

    $pandaItemsAppID = "17330412";

    $pandaItemsRomaniaViewID = "31495677";
    $pandaItemsPakistanViewID = "31495679";
    $pandaItemsKazakhstanViewID = "31495680";
    $pandaItemsHungaryViewID = "31495681";
    $pandaItemsGeorgiaViewID = "31495682";
    $pandaItemsBulgariaViewID = "31495683";
    $pandaItemsBangladeshViewID = "31495686";

    $i = 0;
    $offset = $i * 500;
    $pandaItems = PodioItem::filter_by_view($pandaItemsAppID, $pandaItemsRomaniaViewID, array("limit" => 500, $offset));

    foreach($pandaItems as $item2Check){

        $files = $item2Check->files;
        $fileCount = count($files);

//Step two: If there are files, get them and upload them via uploadAttachment function
        if($fileCount > 0){

            $podioItemID = $item2Check->fields['accounts-podio-unique-id']->values;
            $salesforceItemID = $item2Check->fields['account-sf-id']->values;

            foreach ($files as $key => $fileForLoop){
                $fileID = $fileForLoop->file_id;
                $fileName = $fileForLoop->name;

                $file = PodioFile::get((int)$fileID);
                $file_content = $file->get_raw();

                function uploadAttachment($file_content, $fileName, $salesforceItemID);

            }

        }
        $i++;
    }



//END AUTOMATION//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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