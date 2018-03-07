<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:53 PM
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



//New Project Fields Array

    $projectFieldsArray = array(
        'fields'=>array(
            'title'=>"New Project for: ".$companyName,
            'client'=>(int)$itemID,
            'status-2'=>1,
            'billable-client'=>1,
            'dashboard'=>411301962,
            'project-manager'=>$accountLead
        )
    );



//Current DATE
    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");




//Trigger Item = Generate, Do this step. Else END
    if($generatePBC != "New Project & Billing Cycle") {
        throw new Exception("Trigger is NOT New Project & Billing Cycle, ending call");
        exit;
    }

    $createProjectItem = PodioItem::create(15595688, $projectFieldsArray);
    $projectItemID = $createProjectItem->item_id;



    $newBillingCycle = PodioItem::create(15595726, array(
            'fields' => array(
                'project' => (int)$projectItemID,
                'client-2' => (int)$itemID,
                'status' => 'Active',
                'billing-type' => 'Billable',
                'year' => $year,
                'month' => $month,
            )
        )
    );

    PodioItem::update($itemID, array('fields'=>array(120818828=>"...")));



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


