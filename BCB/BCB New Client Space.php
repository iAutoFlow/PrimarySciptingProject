<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:53 PM
 */
class PodioSessionManager {
    private static $connection_id = 102;//102 //60
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
    //$appID = $requestParams['app_id'];

    //Trigger Item Info.
    $WorkspaceCreationItem = PodioItem::get($itemID);
    $WCClientItem = $WorkspaceCreationItem->fields['client']->values;
    $WCStatus = $WorkspaceCreationItem->fields['worksapce-status']->values[0]['text'];
    $WCSpaceName = $WorkspaceCreationItem->fields['workspace-name']->values;
    $WCClientProfiles = $WorkspaceCreationItem->fields['client-profile']->values;
    $WCSpaceID = $WorkspaceCreationItem->fields['title']->values;


    //If Trigger Item's status = "Create"
    if($WCStatus == "Create" && !$WCSpaceID) {
        //create workspace
        $ClientSpace = PodioSpace::create(array('org_id' => 145854, 'name' => 'IAP: ' . $WCSpaceName));
        $ClientSpaceID = $ClientSpace['space_id'];
        $ClientSpaceLink = $ClientSpace['url'];
        $result = $ClientSpaceID;

        //Update Trigger Item to "Creating"
        $UpdateTriggerItemStatus = PodioItem::update($itemID, array(
            'fields' => array(
                'title'=>(string)$ClientSpaceID,
                'worksapce-status' => 'Creating'
            )
        ));

        if ($WCClientItem) {
            $ClientItemID = $WCClientItem[0]->item_id;
            $ClientItem = PodioItem::get($ClientItemID);
            $ClientUserID = $ClientItem->fields['contact']->values->user_id;
            if ($ClientUserID) {
                PodioSpaceMember::add($ClientSpaceID, array('role' => 'admin', 'users' => array((int)$ClientUserID)));
            }
        }

        //Get Profile ID's of Related User
        if ($WCClientProfiles) {
            foreach ($WCClientProfiles as $User) {
                $UserID = $User->user_id;
                PodioSpaceMember::add($ClientSpaceID, array('role' => 'admin', 'users' => array((int)$UserID)));
            }
        }



        //get template apps
        $templateApps = PodioApp::get_for_space(1186950);
        foreach ($templateApps as $templateapp) {

            //Get App ID
            $AppID = $templateapp->app_id;
            $newApp = PodioApp::install($AppID, array('space_id' => $ClientSpaceID,'type'=>'standard','features' =>array('items', 'filters', 'widgets', 'votings')));

        }


        //Update Trigger Item
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'worksapce-status' => 'Created',
            ),
            array(
                'hook' => true
            ),
        ));

    }






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



