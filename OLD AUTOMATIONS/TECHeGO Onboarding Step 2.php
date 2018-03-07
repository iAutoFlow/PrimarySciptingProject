<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/19/2016
 * Time: 2:30 PM
 */

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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"
    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $GenerateValue = $item->fields['generate-pbc-and-new-workspace']->values[0]['text'];
    $ClientStatus = $item->fields['status']->values[0]['text'];
    $CompanyTitle = $item->fields['title']->values;

    $WorkSpaceInfoAppID = 13941091;


    //Assemble Fields ARRAY
    $FieldsArray = array('fields'=>array());


    if($GenerateValue == "Generate"){

        //create workspace
        $ClientSpace = PodioSpace::create(array('org_id' => 145854, 'name' => 'P - ' . $CompanyTitle));
        $ClientSpaceID = $ClientSpace['space_id'];
        $ClientSpaceLink = $ClientSpace['url'];
        $result = $ClientSpaceID;


        //Get Template Client Space Members
        $templateMembers = PodioSpaceMember::get_all($TemplateProjectSpaceID);

        //Get Template Space Members
        $memberIDs = "";
        foreach ($templateMembers as $member) {
            $memberIDs .= $member->profile->user_id . ",";
        }
        rtrim($memberIDs, ",");

        //Add Memebers to Space
        $AddMembersToSpace = PodioSpaceMember::add($ClientSpaceID, array('role' => 'admin', array('users' => $memberIDs)));



        //get template apps
        $templateApps = PodioApp::get_for_space(4412608);


        $FieldsArray = array(
            'fields'=>array(
                'client-name'=>$CompanyTitle,
                'client'=>(int)$itemID,
                'workspace-id'=>(int)$ClientSpaceID,
            ));

        foreach ($templateApps as $templateapp) {
            $AppID = $templateapp->app_id;
            $AppName = $templateapp->config['name'];

            //Set Field EXternal ID
            if($AppName == "Projects"){$OrgExternalFieldID = 'projects-app-id';}
            if($AppName == "Deliverables"){$OrgExternalFieldID = 'milestones-app-id';}
            if($AppName == "Help Desk"){$OrgExternalFieldID = 'app-id';}


            //Install Each App
            $newApp = PodioApp::install($AppID, array('space_id' => $ClientSpaceID,'type'=>'standard'));
            $FieldsArray['fields'][$OrgExternalFieldID] = (string)$newApp;
        }

        //Update New Org Item With NEw Space ID
        $CreateWSInfoItem = PodioItem::create($WorkSpaceInfoAppID, $FieldsArray);

        $





    }














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