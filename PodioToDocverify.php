<?php
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
    $item_id = $requestParams['item_id'];

///AUTOMATION START

//This automation triggers when 4 - Sales → APT (app: 10702577) → DocVerify (category: 82664650) is set to “Send Document.”  This automation gets relevant APT item field values, most recent file, and email from related Opportunity item, and calls the DocVerify API as outlined below:
//TRIGGER: 4 - Sales → APT (app: 10702577) → DocVerify (category: 82664650) is set to “Send Document”

//Automation Outline
    //Get Relevant Contract Item Values
    $contractItem = PodioItem::get($item_id);

    $docverify = $contractItem->fields['send-to-docverify']->values[0]['text'];


        //Check that 4 - Sales (workspace: 2732154) → APT (app: 10702577) → DocVerify (category: 82664650) is still set to “Send Document”
        if($docverify != "Send"){
            exit;
        }
    //Get Most Recent File attached to APT item by:
        //PodioItem:file_link
        //PodioItem:file_id
        //PodioItem:file_name
        $contractFiles = $contractItem->files;
        $lastFileNumber = sizeof($contractFiles)-1;
        $lastFileLink = $contractFiles[$lastFileNumber]->link;
        $lastFileID = $contractFiles[$lastFileNumber]->file_id;
        $lastFileName = $contractFiles[$lastFileNumber]->name;

    //Get Email from related Opportunity by Opportunity ID (from 2.a.ii)
    $signerItemID = $contractItem->fields['authorized-signer']->values[0]->item_id;

    $signerItem = PodioItem::get($signerItemID);

    $signerEmail = $signerItem->fields['email-address']->values[0]['value'];

                if(!$signerEmail){
                    PodioComment::create('item', $item_id, array('value'=>$contractItem->title.', "No Email Address on Contact, or no Contact in the Authorized Signer field.'));
                }
    //Call DocVerify API to Send APT
        //Call DocVerify API at ( http://apps.techego.com/docVerify/workspace.php?action=DOCUMENTESIGN&Document=[[File]]&DocumentName=[[Document Name]]&Description=[[Document Description]]&Emails=[[Email]]&MessageToSigners=[[Message to Senders]] )
    $urlString = 'http://apps.techego.com/docVerify/techego.php?action=DOCUMENTESIGN&Document='.urlencode($lastFileLink).'&DocumentName='.urlencode($lastFileName).'&Description=Placeholder+Description&Emails='.urlencode($signerEmail).'&MessageToSigners=There+is+a+new+document+waiting+for+your+Esignature.';

    $docverifyResponse = $curl->get($urlString);

//    print_r($docverifyResponse);exit;

//    preg_match('/"([^"]+)"/', $docverifyResponse, $docverifyID);

    //Update Category Fields to “Out for Signature”
    if($docverifyResponse) {
        PodioItem::update($item_id, array('fields' => array('send-to-docverify' => "Out for Signature", 'docverify-id' => $docverifyResponse)));
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