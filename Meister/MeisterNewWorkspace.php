<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:53 PM
 */
class PodioSessionManager {
    private static $connection_id = 6;
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

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    //$appID = $requestParams['app_id'];

//Trigger Item Info.

    $item = PodioItem::get($itemID);
    $companyName = $item->fields['title']->values;
    $generatePBC = $item->fields['generate-pbc-new-workspace']->values[0]['text'];
    $clientStatus = $item->fields['status']->values;
    $clientSpaceID = $item->fields['workspace-id']->values;
    $projectAppID = $item->fields['projects-app-id']->values;
    $deliverablesAppID = $item->fields['deliverables-app-id']->values;
    $hasProject = $item->fields['has-project']->values;
    $hasCycle = $item->fields['has-billing-cycle']->values;
    $accountLead = $item->fields['account-manager']->values[0]->item_id;




//Current DATE
    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");




    if($generatePBC != "New Workspace") {
        throw new Exception("Trigger is NOT New Workspace, ending call");
        exit;
    }

    if (!$clientSpaceID) {

        $ClientSpace = PodioSpace::create(array('org_id' => 884140, 'privacy' => 'open', 'name' => 'P - ' . $companyName));

        $ClientSpaceID = $ClientSpace['space_id'];

        $ClientSpaceLink = $ClientSpace['url'];

    }
    else{
        throw new Exception("Already has Client Workspace");
        exit;
    }

    //Update Trigger Item With New Space Info

    PodioItem::update($itemID, array(
            'fields' => array(
                'workspace-id' => (string)$ClientSpaceID,
                120818828=>"Initial Kickoff"
            ))
    );



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