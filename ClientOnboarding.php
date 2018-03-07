<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:53 PM
 */
class PodioSessionManager {
    private static $connection_id = 4;
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
            'title'=>$companyName,
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


    if (!$clientSpaceID) {

        $ClientSpace = PodioSpace::create(array('org_id' => 884140, 'privacy' => 'open', 'name' => 'P - ' . $companyName));

        $ClientSpaceID = $ClientSpace['space_id'];

        $ClientSpaceLink = $ClientSpace['url'];

    }
    else{
        throw new Exception("Already has Client Workspace");
        exit;
    }

//Trigger Item = Generate, Do this step. Else END
    if($generatePBC != "Initial Kickoff") {
        throw new Exception("Trigger is NOT Initial Kickoff, ending call");
        exit;
    }
    if($hasProject == "No") {
        $createProjectItem = PodioItem::create(15595688, $projectFieldsArray);
        $projectItemID = $createProjectItem->item_id;
    }

    if($hasCycle == "No") {
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
    }




    //Get Template Client Space Apps
    $templateApps = PodioApp::get_for_space(4488715);

    $templateMembers = PodioSpaceMember::get_all(4488715);

    $memberIDs = array();

    foreach ($templateMembers as $member) {
        array_push($memberIDs, $member->user->user_id);
    }


    // add members from Template to new Client Space - commented out for now as the Trust Level on the API key will not allow for this
    PodioSpaceMember::add($clientSpaceID, array('role'=>'admin','users'=>$memberIDs));


    //loop template apps and add them to new space

    foreach ($templateApps as $app) {

        $newApp = PodioApp::install($app->app_id, array('space_id' => $clientSpaceID));


        $newAppName = $newApp->app_name;

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

    //Project Hooks
    PodioHook::create( 'app', $newProjectsAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync', 'type'=>'item.create'));
    PodioHook::create( 'app', $newProjectsAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_project_items_sync', 'type'=>'item.update'));

    //Deliverable Hooks
    PodioHook::create( 'app', $newDeliverableAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync','type'=>'item.create'));
    PodioHook::create( 'app', $newDeliverableAppID, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=meister_deliverables_sync','type'=>'item.update'));

    //get Devliverables app fields
    $delivApp = PodioApp::get($newDeliverablesAppID);
    $delivAppFields = $delivApp->fields;

    foreach ($delivAppFields as $field) {
        if ($field->external_id == "project") {
            $delivProjectFieldID = $field->field_id;
        }
        if ($field->external_id == "actual-hours") {
            $actualHoursFieldID = $field->field_id;
        }
        if ($field->external_id == "complete") {
            $percentCompleteFieldID = $field->field_id;
        }
    }


    //Update Projects App with Calculation Fields

    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total Estimated Duration', 'delta' => 6, 'settings' => array('script' => '@[Sum of Estimated Duration](in_sum_' . $actualHoursFieldID . '_' . $delivProjectFieldID . ')'))));
    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => 'Total Estimated Cost', 'delta' => 7, 'settings' => array('script' => '@[Sum of Estimated Cost](in_sum_' . $actualHoursFieldID . '_' . $delivProjectFieldID . ')'))));
    PodioAppField::create($newProjectAppID, array('type' => 'calculation', 'config' => array('label' => '% Completion', 'delta' => 8, 'settings' => array('script' => '@[Avg of % Complete](in_avg_' . $percentCompleteFieldID . '_' . $delivProjectFieldID . ')'))));


    //add Base Project Items
    $newProjectID = PodioItem::create($newProjectAppID, array('fields' => array(
        'project' => array((int)$projectItemID),
        'project-name' => $companyName
    )));

    //add Base Deliverable(s)
    $deliverablesFieldArray = array(
        'fields' => array(
            'title' => 'Project Administration - ' . $companyName,
            'approval' => "Work Ready",
            'project' => array(
                (int)$projectItemID),
            'description' => 'This is the Deliverable used for setting up the project, or doing anything administration related on a project level (not directly related to a Deliverable)'
        )
    );

    PodioItem::create($newDeliverablesAppID, $deliverablesFieldArray);



    //Update Trigger Item With New Space Info

    PodioItem::update($itemID, array(
            'fields' => array(
                'projects-app-id' => (string)$newProjectsAppID,
                'deliverables-app-id' => (string)$newDeliverableAppID,
                'help-desk-app-id' => (string)$newHelpDeskAppID,
                120818828=>"..."
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