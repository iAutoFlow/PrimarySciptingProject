<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/28/2016
 * Time: 4:47 PM
 */


date_default_timezone_set('America/Denver');
//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);

    //Get Trigger Item Values
    $AppID = $item->fields['app-id']->values;
    $AppName = $item->fields['title']->values;
    $AppStatus = $item->fields['status']->values[0]['text'];
    $AppType = $item->fields['type']->values[0]['text'];
    $ItemName = $item->fields['item-name']->values;
    $Description = $item->fields['description']->values;
    $Usage = $item->fields['usage']->values;
    $Icon = $item->fields['icon-id']->values;
    $DefaultView = $item->fields['default-view-type']->values[0]['text'];
    $AllowEdit = $item->fields['allow-edit']->values[0]['text'];
    $AllowAttachments = $item->fields['allow-attachments']->values[0]['text'];
    $AllowComments = $item->fields['allow-comments']->values[0]['text'];
    $SilentCreates = $item->fields['silent-creates']->values[0]['text'];
    $SilentEdits = $item->fields['silent-edits']->values[0]['text'];
    $FiveStar = $item->fields['five-star']->values[0]['text'];
    $FiveStarLabel = $item->fields['five-star-label']->values;
    $Approved = $item->fields['approved']->values[0]['text'];
    $Thumbs = $item->fields['thumbs']->values[0]['text'];
    $ThumbsLabel = $item->fields['thumbs-label']->values;
    $RSVP = $item->fields['rsvp']->values[0]['text'];
    $RSVPLabal = $item->fields['rsvp-label']->values;
    $YesNo = $item->fields['yes-no']->values[0]['text'];
    $YesNoLabel = $item->fields['yes-no-label']->values;
    $Tasks = $item->fields['tasks']->values;


    //Get Current Config
    $App = PodioApp::get($AppID);
    $CurrentAppConfigArray = $App->config;


    //Create Configuration Array
    $ConfigArray = array(
        'config'=>array(
            'name'=>$AppName,
            'item_name'=>$ItemName,
            'icon'=>$Icon,
        ));
    
    if($AppType){$ConfigArray['config']['type'] = $AppType;}
    if($Description){$ConfigArray['config']['description'] = $Description;}
    if($Usage){$ConfigArray['config']['usage'] = $Usage;}
    if($AllowEdit){$ConfigArray['config']['allow_edit'] = $AllowEdit;}
    if($DefaultView){$ConfigArray['config']['default_view'] = $DefaultView;}
    if($AllowAttachments){$ConfigArray['config']['allow_attachments'] = $AllowAttachments;}
    if($AllowComments){$ConfigArray['config']['allow_comments'] = $AllowComments;}
    if($SilentCreates){$ConfigArray['config']['silent_creates'] = $SilentCreates;}
    if($SilentEdits){$ConfigArray['config']['silent_edits'] = $SilentEdits;}
    if($FiveStar){$ConfigArray['config']['fivestar'] = $FiveStar;}
    if($FiveStarLabel){$ConfigArray['config']['fivestar_label'] = $FiveStarLabel;}
    if($Approved){$ConfigArray['config']['approved'] = $Approved;}
    if($Thumbs){$ConfigArray['config']['thumbs'] = $Thumbs;}
    if($ThumbsLabel){$ConfigArray['config']['thumbs_label'] = $ThumbsLabel;}
    if($RSVP){$ConfigArray['config']['rsvp'] = $RSVP;}
    if($RSVPLabal){$ConfigArray['config']['rsvp_label'] = $RSVPLabal;}
    if($YesNo){$ConfigArray['config']['yesno'] = $YesNo;}
    if($YesNoLabel){$ConfigArray['config']['yesno_label'] = $YesNoLabel;}
    if($Tasks){$ConfigArray['config']['tasks'] = $Tasks;}



    $SizeofCurrent = sizeof($CurrentAppConfigArray);
    $SizeofUpdated = sizeof($ConfigArray);

    if($SizeofCurrent > $SizeofUpdated){
        $BiggerArray = $SizeofCurrent;
        $SmallArray = $SizeofUpdated;
    }
    if($SizeofCurrent < $SizeofUpdated){
        $BiggerArray = $ToIDsArray;
        $SmallArray = $SizeofUpdated;
    }

    $Difference = array_diff($BiggerArray, $SmallArray);





    //Update App Description
    $UpdateDescription = PodioApp::update($AppID, $ConfigArray);


    //Create Comment on Trigger Item
    $CreateComment = PodioComment::create('item', $itemID, array('value'=>"The App ".$AppName." has successfully been updated."));



    //RETURN / CATCH
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