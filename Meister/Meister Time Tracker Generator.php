<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
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

    $triggerValue = $item->fields['create-time-trackers-trigger']->values[0]['text'];
    $billingType = $item->fields['billing-type']->values[0]['text'];


    if($triggerValue == 'Create'){
        $assignedTo = $item->fields['assigned-to']->values;

        foreach($assignedTo as $contact) {
            $userContact = PodioContact::get_for_user($contact->user_id);
            $assignedUserName = $userContact->name;
            $assignedUserProfileID = $userContact->profile_id;
            $employeeDBFilter = PodioItem::filter(15595671, array("filters"=>array('employee'=>$assignedUserProfileID)));
            $employeeDBItemID = $employeeDBFilter[0]->item_id;

            if(!$employeeDBItemID){
                $AddComment = PodioComment::create('item', $itemID, array(
                    'value' => $assignedUserName . " " . "does not exist in the employee database."
                ));
                continue;
            }


            $filterExistingTimeTrackers = PodioItem::filter(15595816, array('filters'=>array('deliverable'=>array((int)$itemID), 'employee'=>array((int)$employeeDBItemID))));
            $filterItemID = $filterExistingTimeTrackers[0]->item_id;

            if(!$filterItemID){
                $createTimeTracker = PodioItem::create(15595816, array(
                        'fields' => array(
                            'deliverable' => array((int)$itemID),
                            'employee' => array((int)$employeeDBItemID),

                        )
                    )
                );
            }

        }



    }


    $updateTiggerValue = PodioItem::update($itemID, array(
        'fields' => array(
            'create-time-trackers-trigger'=>array(
                'value' => '...'
            )),
        array(
            'hook' => false
        )));










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
