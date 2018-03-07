<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */


//<?php
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




try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));


//    $GetAllProjectItems = PodioItem::filter(16261915, array('limit'=>500));
//    foreach($GetAllProjectItems as $project){
//        $ProjectItemID = $project->item_id;
//        $ProjectItem = PodioItem::get($ProjectItemID);
//        $AssignedSFID = $ProjectItem->fields['assigned-to-sf-id']->values;
//
//
//        $FilterMembers = PodioItem::filter(15357642, array('filters'=>array('salesforce-id'=>$AssignedSFID)));
//        $MemberItemID = $FilterMembers[0]->item_id;
//        if($MemberItemID){
//            $UpdateProject = PodioItem::update($ProjectItemID, array(
//                'fields'=> array(
//                    'assigned-to-2'=>$MemberItemID
//                )
//            ));
//        }
//    }


//    $GetAllCampaignItems = PodioItem::filter(16261940, array('limit'=>500));
//    foreach($GetAllCampaignItems as $campaign){
//        $CampaignItemID = $campaign->item_id;
//        $CampaignItem = PodioItem::get($CampaignItemID);
//        $AssignedTo = $CampaignItem->fields['owner-id']->values;
//
//        $FilterAssignee = PodioItem::filter(15357642, array('filters'=>array('salesforce-id'=>$AssignedTo)));
//        $AssigneeItemID = $FilterAssignee[0]->item_id;
//        if($AssigneeItemID){
//            $UpdateClient = PodioItem::update($CampaignItemID, array(
//                'fields'=> array(
//                    'campaign-owner'=>$AssigneeItemID
//                )
//            ));
//        }
//    }

    $offset = 0;
    $i = 0;
    do {
        $offset = $i * 500;

        $GetAllAccountItems = PodioItem::filter(16307520, array('limit' => 500, 'offset' => $offset));
        $filteredNum = count($GetAllAccountItems);
        foreach ($GetAllAccountItems as $account) {
            $AccountItemID = $account->item_id;
            $AccountItem = PodioItem::get($AccountItemID);
            $AccountOwner = $AccountItem->fields['salesforce-account-owner']->values;

            $FilterOwner = PodioItem::filter(15357642, array('filters' => array('salesforce-id' => $AccountOwner)));
            $OwnerItemID = $FilterOwner[0]->item_id;
            if ($OwnerItemID) {
                $UpdateClient = PodioItem::update($AccountItemID, array(
                    'fields' => array(
                        'account-owner-2' => $OwnerItemID
                    )
                ));
            }
            $i++;
        }
    }


    while ($filteredNum == 500);



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