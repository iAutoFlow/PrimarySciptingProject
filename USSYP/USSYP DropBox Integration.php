//<?php

global $result ;
global $asstEmail;
global $mailgunURL;
global $leadFirstName;

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

function savePodioFileToDropbox($item_id){

    if($item_id == null)
        throw new Exception('Missing Required Parameter: fileID');


    $recentestDate;
    $item = PodioItem::get($item_id);
    $recentestDate = $item->files[0]->created_on;
    $file_id = $item->files[0] ->file_id;
    $file_mime = $item->files[0] ->mimetype;


    foreach ($item->files as $file){
        if(($file->created_on) > $recentestDate){
            $file_id = $file->file_id;
            $file_mime = $file->mimetype;
            $recentestDate = $file->created_on;
        }
    }



    // $file_id=$item->files[0]->file_id

    $asstEmail = ($item->fields['email-3']->values[0]['value']);
    //   var_dump($items->fields['first-name']); exit;
    $firstName = ($item->fields['first-name']->values);



    //   This is how you can get an accessToken to use below. (for future reference)
    //step 1:
    /*
        $appInfo = \Dropbox\AppInfo::loadFromJsonFile('/opt/bitnami/apps/dreamfactory/htdocs/storage/app/ussyp/USSYP-DB.txt');
        $webAuth = new \Dropbox\WebAuthNoRedirect($appInfo, "PHP-Example/1.0");
        $authorizeUrl = $webAuth->start();
        return($authorizeUrl);
        //go to that url and get the authorization code
    */
//step 2:
//curl https://api.dropbox.com/1/oauth2/token -d code=<authorization code> -d grant_type=authorization_code -d redirect_uri=<redirect URI> -u <app key>:<app secret>


//step 3:
//copy the access token to here (it's a permanent token)

    $accessToken = 'rKkzGxAXgsAAAAAAAAAADkeQ9crA_OLMcFCeDf_r6o8cQrZh3SVxghwaI_MZIy9U';
    $dbxClient = new \Dropbox\Client($accessToken, "PHP-Example/1.0");
    $accountInfo = $dbxClient->getAccountInfo();

//get podio file
    $file = PodioFile::get($file_id);
    $file_content = $file->get_raw();

//save file locally
    $path_to_file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/ussyp/'.$file_id.'.pdf';
    file_put_contents($path_to_file, $file_content);

//move to dropbox
    $f = fopen($path_to_file, "rb");
    $result = $dbxClient->uploadFile("/".$file_id.".pdf", \Dropbox\WriteMode::add(), $f);
    fclose($f);

//get sharable link
    $dropboxPath = $result['path'];
    $url = $dbxClient->createShareableLink($dropboxPath);

//write values
    $GLOBALS['result'] = 'success';
    $GLOBALS['asstEmail'] = $asstEmail;
    $GLOBALS['url'] = $url;
    $GLOBALS['firstName'] = $firstName;
    return;


}

function syncSopToCsa(){



    $filterSOP = PodioItem::filter(14716400, array('limit'=>40));

    foreach($filterSOP as $item){

        $itemID = $item->item_id;

        linkSopToCsa($itemID);

    }

}

function linkSopToCsa($item_id){
    //get the state referenced in the SOP

    $sopItem = PodioItem::get($item_id);

    $stateID = $sopItem->fields['state-6']->values[0]->item_id;

    //fields for SOP-CSA Sync
    $csaEmail = $sopItem->fields['email-3']->values[0]['value'];
    $csaEmailType = $sopItem->fields['email-3']->values[0]['type'];
    $csaPhone = $sopItem->fields['phone-3']->values[0]['value'];
    $csaPhoneType = $sopItem->fields['phone-3']->values[0]['type'];
    $csaAddress = $sopItem->fields['location-3']->values;
    $csaTitle = $sopItem->fields['title-3']->values;
    $csaPrefix = $sopItem->fields['prefix-2']->values[0]['text'];
    $csaFirst = $sopItem->fields['first-name']->values;
    $csaMiddle = $sopItem->fields['middle-initial']->values;
    $csaLast = $sopItem->fields['last-name-2']->values;

    $offEmail = $sopItem->fields['email']->values[0]['value'];
    $offEmailType = $sopItem->fields['email']->values[0]['type'];
    $offPhone = $sopItem->fields['phone-2']->values[0]['value'];
    $offPhoneType = $sopItem->fields['phone-2']->values[0]['type'];
    $offAddress = $sopItem->fields['location-2']->values;
    $offTitle = $sopItem->fields['title-2']->values;
    $offPrefix = $sopItem->fields['prefix']->values[0]['text'];
    $offFirst = $sopItem->fields['title']->values;
    $offMiddle = $sopItem->fields['middle']->values;
    $offLast = $sopItem->fields['last-name']->values;

    $stipendPayee = $sopItem->fields['1000-stipend-payee']->values;
    $website = $sopItem->fields['what-is-your-website-address']->values;


    $embed = PodioEmbed::create(array('url'=>$website));

    $websiteEmbed = $embed->embed_id;


    //filter the csa app by the found state
    $collection = PodioItem::filter(14848488, array('filters'=>array(114792090=>array((int)$stateID)), 'limit'=>500));


    //csa item for that state
    foreach($collection as $item){

        $attributes = array(
            'fields' => array(
                'sop-submission'=>array(
                    (int)$item_id
                ),
                'submissions'=>"SOP Webform",
                'cssassistant-email'=>array(
                    'type'=>$csaEmailType,
                    'value'=>$csaEmail
                ),
                'cssassistant-phone'=>array(
                    'type'=>$csaPhoneType,
                    'value'=>$csaPhone
                ),
                'assistant-address'=>$csaAddress,
                'assitant-title'=>$csaTitle,
                'assistant-prefix'=>$csaPrefix,
                'cssassistant'=>$csaFirst,
                'assistant-middle'=>$csaMiddle,
                'assistant-last'=>$csaLast,
                'cssofficer-email'=>array(
                    'type'=>$offEmailType,
                    'value'=>$offEmail
                ),
                'cssofficer-phone'=>array(
                    'type'=>$offPhoneType,
                    'value'=>$offPhone
                ),
                'officer-address'=>$offAddress,
                'csso-title'=>$offTitle,
                'officer-prefix'=>$offPrefix,
                'title'=>$offFirst,
                'officer-middle'=>$offMiddle,
                'officer-last'=>$offLast,
                'stipend-payee-name'=>$stipendPayee,
                'website'=>$websiteEmbed
            )
        );

        $GLOBALS['result'] = PodioItem::update($item->item_id, $attributes);


    }



}//end linkSopToCsa function

try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

    $requestParams = $event['request']['parameters'];
    $automation = $requestParams['automation'];


    switch ($automation){

        case 'savePodioFileToDropbox':
            savePodioFileToDropBox($requestParams['itemID']);
            break;
        case 'linkSopToCsa':
            linkSopToCsa($requestParams['item_id']);
            break;
        case 'syncSopToCsa':
            syncSopToCsa();
            break;
        default:
            throw new Exception('Missing Required Parameter: automation');

    }



    return [
        'success' => true,
        'result' => $result,
        'asstEmail' => $asstEmail,
        'url' => $mailgunURL,
        'firstName' => $leadFirstName
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