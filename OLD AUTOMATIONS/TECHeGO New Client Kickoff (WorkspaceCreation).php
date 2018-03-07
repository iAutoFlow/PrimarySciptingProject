<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/14/2016
 * Time: 4:29 PM
 */
//O-AUTH

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

    $LeadsAppID = 2933904;
    $ClientAppID = 13940709;
    $ProjectsAppID = 3848224;
    $ClientWorkspaceInfoAppID = 13941091;
    $BillingCycleAppID = 4481866;
    $PMSAppID = 15555787;

    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");
    $day = date_format($todaysDate, "J");

    if($day < 15){$Ending = 15;}
    else{$Ending = }



    //Trigger Item Info.
    $CompanyName = $item->fields['company-name-in-podio']->values;
    $LeadStatus = $item->fields['status']->values[0]['text'];
    $LeadItemID = $itemID;


    //Main function block

    //Fields Array
    $FieldsArray = array();


    //Create New Project Item
    $CreateNewProject = PodioItem::create($ProjectsAppID, array(
        'fields' => array(
            'project-name' => $CompanyName,
            'company2' => array((int)$ClientItemID),
            'status' => "Active",
        )));
    $ProjectItemIDArray = $CreateNewProject->item_id;

    $CreateBillingCycle = PodioItem::create($BillingCycleAppID, array(
        'fields' => array(
            'project' => array((int)$ProjectItemIDArray),
            'billing-type' => "Billable",
            'status-2' => "Active",
            'client' => array((int)$ClientItemID),
            //'period' => array((int)$PMSItemID),
        )
    ));


    //Workspace Installation

    $TemplateProjectSpaceID = 3970804;
    $ClientSpace = PodioSpace::create(array('org_id' => 10685, 'privacy' => 'open', 'name' => 'P - ' . $CompanyName));
    $ClientSpaceID = $ClientSpace['space_id'];
    $ClientSpaceLink = $ClientSpace['url'];



    //Get Template Client Space Apps
    $templateApps = PodioApp::get_for_space($TemplateProjectSpaceID);
    $templateMembers = PodioSpaceMember::get_all($TemplateProjectSpaceID);

    //Get Template Space Members
    $memberIDs = "";
    foreach ($templateMembers as $member) {
        $memberIDs .= $member->profile->user_id . ",";
    }
    rtrim($memberIDs, ",");


    //Add Memebers to Space
    $AddMembersToSpace = PodioSpaceMember::add($ClientSpaceID, array('role' => 'admin', array('users' => $memberIDs)));


    //Get Each Template App, and Add to Space

    foreach ($templateApps as $app) {
        $newApp = PodioApp::install($app->app_id, array('space_id' => $ClientSpaceID));
        $newAppName = $newApp->app_name;
        if ($newAppName == "Dashboard") {
            $newDashboardAppID = $newApp->app_id;
        }
        if ($newAppName == "Projects") {
            $newProjectsAppID = $newApp->app_id;
        }
        if ($newAppName == "Deliverables") {
            $newDeliverableAppID = $newApp->app_id;
        }
        if ($newAppName == "Help Desk") {
            $newHelpDeskAppID = $newApp->app_id;
        }
    }

    //Create Client Workspace Info Item
    $NewCWIItem = PodioItem::create($ClientWorkspaceInfoAppID, array(
        'fields'=>array(
            'client' => array((int)$ClientItemID),
            'app-id' => $newHelpDeskAppID,
            'workspace-link' => $ClientSpaceLink,
            'workspace-id' => (int)$ClientSpaceID,
            'projects-app-id' => $newProjectsAppID,
            'milestones-app-id' => $newDeliverableAppID,
        )
    ));


    //Project Hooks
    //PodioHook::create( 'app', $newProjectsAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync', 'type'=>'item.create'));
    //PodioHook::create( 'app', $newProjectsAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync', 'type'=>'item.update'));


    //Deliverable Hooks
    //PodioHook::create( 'app', $newDeliverableAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync','type'=>'item.create'));
    //PodioHook::create( 'app', $newDeliverableAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync','type'=>'item.update'));

    //get Devliverables app fields
    $delivApp = PodioApp::get($newDeliverablesAppID);
    $delivAppFields = $delivApp->fields;

    foreach ($delivAppFields as $field) {
        if ($field->external_id == "project") {
            $delivProjectFieldID = $field->field_id;
        }
        if ($field->externa_id == "complex") {
            $complexityFieldID = $field->field_id;
        }
        if ($field->external_id == "actual-hours") {
            $actualHoursFieldID = $field->field_id;
        }
        if ($field->external_id == "complete") {
            $percentCompleteFieldID = $field->field_id;
        }
        if ($field->external_id == "estimated-cost") {
            $estimatedCostFieldID = $field->field_id;
        }
    }

//
//        $delivApp = PodioApp::get($newDeliverablesAppID);
//        $delivAppFields = $delivApp->fields;
//
//        foreach ($delivAppFields as $field) {
//            if ($field->external_id == "project") {
//                $delivProjectFieldID = $field->field_id;
//            }
//            if ($field->externa_id == "complex") {
//                $complexityFieldID = $field->field_id;
//            }
//            if ($field->external_id == "actual-hours") {
//                $actualHoursFieldID = $field->field_id;
//            }
//            if ($field->external_id == "complete") {
//                $percentCompleteFieldID = $field->field_id;
//            }
//            if ($field->external_id == "estimated-cost") {
//                $estimatedCostFieldID = $field->field_id;
//            }
//        }


    //Update Projects App with Calculation Fields

    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total Estimated Cost', 'delta' => 6, 'settings' => array('script' => '@[Sum of Estimated Cost](in_sum_' . $estimatedCostFieldID . '_' . $delivProjectFieldID . ')'))));
    //PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total ActuL Cost', 'delta' => 7, 'settings' => array('script' => '@[Sum of Actual Cost](in_sum_' . $actualHoursFieldID . '_' . $delivProjectFieldID . ')'))));
    //PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => '% Completion', 'delta' => 8, 'settings' => array('script' => '@[Avg of % Complete](in_avg_' . $percentCompleteFieldID . '_' . $delivProjectFieldID . ')'))));


    //add Base Project Items
    $newProjectID = PodioItem::create($newProjectAppID, array(
        'fields' => array(
            'project-2' => array((int)$ProjectItemIDArray),
            'stage' => "Discovery, Architecture",
            'approvers' => "Single",
    )));

    //add Base Deliverable(s)
    $deliverablesFieldArray = array(
        'fields' => array(
            'title' => 'Project Administration - ' . $CompanyName,
            'approval' => "Work Ready",
            'project' => array((int)$ProjectItemIDArray),
            'description' => 'This is the Deliverable used for setting up the project, or doing anything administration related on a project level (not directly related to a Deliverable)'
        ),
        array('hook' => false)
    );

    PodioItem::create($newDeliverablesAppID, $deliverablesFieldArray);






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

































































//
//    //Update Trigger Item With New Space Info
//
//    PodioItem::update($itemID, array(
//            'fields' => array(
//                'workspace-id' => (string)$ClientSpaceID,
//                'projects-app-id' => (string)$newProjectsAppID,
//                'deliverables-app-id' => (string)$newDeliverableAppID,
//                'help-desk-app-id' => (string)$newHelpDeskAppID,
//                120818828=>"..."
//            ))
//    );






















