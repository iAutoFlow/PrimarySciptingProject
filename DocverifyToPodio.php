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
    $docid = $requestParams['docid'];
    $status = $requestParams['status'];
    $datetime = $requestParams['datetime'];
    $signer = $requestParams['signer'];
    $data = $requestParams['data'];
    $filename = $requestParams['filename'];
    $templateid = $requestParams['templateid'];
    $http_request_body = $requestParams['http-request-body'];

///AUTOMATION START
    if($status != "completed"){
        $result = "Document Not Complete";
        return;
    }

    $contractFilter = PodioItem::filter(13838677, array('filters'=>array('docverify-id'=>$docid)));

    $contractItemID = $contractFilter[0]->item_id;

    $clientTitle = $contractFilter[0]->fields['client']->values[0]->title;

    $filenameField = $contractFilter[0]->fields['file-name']->values[0]['text'];

    $documentName = "$clientTitle $filenameField Agreement";

    $documentName = str_replace('/', '', $documentName);

    $curlURL = 'http://apps.techego.com/docVerify/techego.php?action=GETDOCUMENT&DocVerifyID='.urlencode($docid).'&ref_type=item&ref_id='.urlencode($contractItemID).'&docName='.urlencode($documentName);

    $appstechegoResponse = $curl->get($curlURL);

    PodioComment::create('item', $contractItemID, array('value'=>'Contract was signed by Authorized Signer'));

    PodioItem::update($contractItemID, array('fields'=>array('send-to-docverify'=>'Signature Received')));

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