<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:53 PM
 */
class PodioSessionManager {
    private static $connection_id = 76;
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
    $ClientSpaceID = $item->fields['workspace-id']->values;
    $accountLead = $item->fields['account-manager']->values[0]->item_id;







    if (!$ClientSpaceID && $generatePBC == "New Workspace") {

        //create workspace
        $ClientSpace = PodioSpace::create(array('org_id' => 884140, 'privacy' => 'open', 'name' => 'P - ' . $companyName));
        $ClientSpaceID = $ClientSpace['space_id'];
        $ClientSpaceLink = $ClientSpace['url'];


        //get template apps
        $templateApps = PodioApp::get_for_space(4488715);

        $listOfInstalledApps = array();

        foreach ($templateApps as $app) {
            $AppID = $app->app_id;
            //$AppName = $app->config['name']
            $newApp = PodioApp::install($AppID, array('space_id' => $ClientSpaceID, 'type' => 'standard'));
            array_push($listOfInstalledApps, $newApp);
        }


        $newProjectsAppID = 0;
        $newDeliverableAppID = 0;
        $newHelpDeskAppID = 0;



        foreach($listOfInstalledApps as $appitem){
            $GetAPP = PodioApp::get($appitem);

            $NewAppName = $GetAPP->config['name'];

            if ($NewAppName == "Projects") {
                $newProjectsAppID = $appitem;
            }

            if ($NewAppName == "Deliverables") {
                $newDeliverableAppID = $appitem;
            }

            if ($NewAppName == "Help Desk") {
                $newHelpDeskAppID = $appitem;
            }
        }

        $NewDeliverableApp = PodioApp::get($newDeliverableAppID);
        $delivAppFields = $NewDeliverableApp->fields;

        foreach ($delivAppFields as $field) {
            if ($field->external_id == "approval") {
                $delivApprovalFieldID = $field->field_id;
            }
//            if ($field->external_id == "actual-hours") {
//                $actualHoursFieldID = $field->field_id;
//            }
//            if ($field->external_id == "complete") {
//                $percentCompleteFieldID = $field->field_id;
//            }
        }



        $templateMembers = PodioSpaceMember::get_all(4488715);
        foreach ($templateMembers as $member) {
            $memberIDs = $member->user->user_id;
            $AddMember = PodioSpaceMember::add($ClientSpaceID, array('role' => 'admin', 'users' => array((int)$memberIDs)));
        }


        PodioHook::create( 'app', $newProjectsAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync",'type'=>'item.create'));
        PodioHook::create( 'app', $newProjectsAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync",'type'=>'item.update'));


        PodioHook::create( 'app', $newDeliverableAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync",'type'=>'item.create'));
        PodioHook::create( 'app', $newDeliverableAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync",'type'=>'item.update'));

        PodioHook::create( 'app', $newDeliverableAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_add_dashboard_relationship",'type'=>'item.create'));
        PodioHook::create( 'app', $newProjectsAppID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=meister_add_dashboard_relationship",'type'=>'item.create'));

        PodioHook::create( 'app_field', $delivApprovalFieldID, array('url'=>"https://hoist.thatapp.io/podio_catcher.php?service=deliverable_approval_check",'type'=>'item.update'));







        //Update Projects App with Calculation Fields

//    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total Estimated Duration', 'delta' => 6, 'settings' => array('script' => '@[Sum of Estimated Duration](in_sum_' . $actualHoursFieldID . '_' . $delivProjectFieldID . ')'))));
//    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total Estimated Cost', 'delta' => 7, 'settings' => array('script' => '@[Sum of Estimated Cost](in_sum_' . $actualHoursFieldID . '_' . $delivProjectFieldID . ')'))));
//    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => '% Completion', 'delta' => 8, 'settings' => array('script' => '@[Avg of % Complete](in_avg_' . $percentCompleteFieldID . '_' . $delivProjectFieldID . ')'))));



        $UpdateClientItem = PodioItem::update($itemID, array(
            'fields' => array(
                'workspace-id' => (string)$ClientSpaceID,
                'projects-app-id' => (string)$newProjectsAppID,
                'deliverables-app-id' => (string)$newDeliverableAppID,
                'help-desk-app-id' => (string)$newHelpDeskAppID,
                'generate-pbc-new-workspace' => "...",
            ),
            array(
                'hook' => false
            )
        ));

    }

    else{}


    return [
        'success' => true,
        'result' => $NewAppName,
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

