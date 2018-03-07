<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/1/2016
 * Time: 11:26 AM
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

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;



    //If Trigger Item App ID is not Equal to the 2-Projects Deliverable App ID, do this step.

    if($appID == 15595774){
        $title = $item->fields['title']->values;
        $relatedClientProjectID = $item->fields['project']->values[0]->item_id;
        //$approval = $item->fields['approval-status']->values[0]['text'];
        $delivDescription = $item->fields['client-facing-description']->values;
        $delivOrder = $item->fields['stage']->values[0]['text'];

        $draftDueDate = $item->fields['draft-deadline']->start_date->format('Y-m-d H:i:s');
        $finalDueDate = $item->fields['due-date']->start_date->format('Y-m-d H:i:s');
        $draftDeadline = new DateTime((string)$draftDueDate, new DateTimeZone('UTC'));
        $dueDate = new DateTime((string)$finalDueDate, new DateTimeZone('UTC'));

        $assignedContact = $item->fields['assigned-to']->values;
        $assignedContactsArray = array();

        foreach($assignedContact as $contact) {
            $userContact = PodioContact::get_for_user($contact->user_id);
            $assignedUserProfileID = $userContact->profile_id;
            array_push($assignedContactsArray, $assignedUserProfileID);
        }

        $DelivProjectItem = PodioItem::get($relatedClientProjectID);
        $clientItemID = $DelivProjectItem->fields['client']->values[0]->item_id;
        $projectUnique = $DelivProjectItem->fields['client-project-unique-identifier']->values;

        $ClientItem = PodioItem::get($clientItemID);
        $clientDelivAppID = $ClientItem->fields['deliverables-app-id']->values;
        $clientProjectAppID = $ClientItem->fields['projects-app-id']->values;




        $filterProject = PodioItem::filter($clientProjectAppID, array('filters'=>array('unique-project-identifier'=>$projectUnique)));

        $filterProjectItemID = $filterProject[0]->item_id;

        $filterDeliv = PodioItem::filter($clientDelivAppID, array('filters'=>array('database-job-item'=>array((int)$itemID))));
        $filterDelivID = $filterDeliv[0]->item_id;


        if($filterDelivID){
            $updateDeliv = PodioItem::update($filterDelivID, array(
                'fields' => array(
                    'title' => $title,
                    'project' => array((int)$filterProjectItemID),
                    'assigned-to' =>$assignedContactsArray,
                    'description' => $delivDescription,
                    'order' =>$delivOrder,
                    //'approval'=>$approval,
                    'due-date' => array('start' => $draftDeadline->format('Y-m-d H:i:s')),
                    'final-due-date' => array('start' => $dueDate->format('Y-m-d H:i:s')),
                ),
                array(
                    'hook' => false
                )
            ));
        }

        else{
            $createDeliv = PodioItem::create($clientDelivAppID, array(
                'fields' => array(
                    'title' => $title,
                    'project' => array((int)$filterProjectItemID),
                    'assigned-to' =>$assignedContactsArray,
                    'description' => $delivDescription,
                    'database-job-item' => array((int)$itemID),
                    'order' =>$delivOrder,
                    //'approval'=>$approval,
                    'due-date' => array('start' => $draftDeadline->format('Y-m-d H:i:s')),
                    'final-due-date' => array('start' => $dueDate->format('Y-m-d H:i:s')),
                ),
                array(
                    'hook' => false
                )
            ));
        }

    }


    else{


        //Get fields values from item
        $title = $item->fields['title']->values;
        $relatedClientProjectID = $item->fields['project']->values[0]->item_id;
        //$approval = $item->fields['approval']->values[0]['text'];
        $relatedDeliverableID = $item->fields['database-job-item']->values[0]->item_id;
        $delivDescription = $item->fields['description']->values;
        $delivOrder = $item->fields['order']->values[0]['text'];
        $UniqueIdentifier = $item->fields['unique-identifier']->values;

        $assignedContact = $item->fields['assigned-to']->values;
        $assignedContactsArray = array();

        foreach($assignedContact as $contact) {
            $userContact = PodioContact::get_for_user($contact->user_id);
            $assignedUserProfileID = $userContact->profile_id;
            array_push($assignedContactsArray, $assignedUserProfileID);
        }



        $draftDueDate = $item->fields['due-date']->start_date->format('Y-m-d H:i:s');
        $finalDueDate = $item->fields['final-due-date']->start_date->format('Y-m-d H:i:s');

        $draftDeadline = new DateTime((string)$draftDueDate, new DateTimeZone('UTC'));
        $dueDate = new DateTime((string)$finalDueDate, new DateTimeZone('UTC'));




        $ClientProjectItem = PodioItem::get($relatedClientProjectID);
        $projectItemID = $ClientProjectItem->fields['project']->values[0]->item_id;
        $projectItem = PodioItem::get($projectItemID);
        $projectClientItemID = $projectItem->fields['client']->values[0]->item_id;
        $projectClient = PodioItem::get($projectClientItemID);
        $clientDelivAppID = $projectClient->fields['projects-app-id']->values;

        if($relatedDeliverableID){
            $updateDeliv = PodioItem::update($relatedDeliverableID, array(
                    'fields' => array(
                        'client-facing-description'=>$delivDescription,
                        'title'=>$title,
                        'assigned-to'=>$assignedContactsArray,
                        //'approval-status'=>$approval,
                        'stage'=>$delivOrder,
                        'draft-deadline'=> array('start' => $draftDeadline->format('Y-m-d H:i:s')),
                        'due-date'=> array('start' => $dueDate->format('Y-m-d H:i:s')),
                    ),
                    array(
                        'hook' => false
                    )
                )
            );
        }

        else{
            $createDeliv = PodioItem::create(15595774, array(
                'fields' => array(
                    'title' => $title,
                    'project' => array((int)$projectItemID),
                    'assigned-to' =>$assignedContactsArray,
                    'client-facing-description' => $delivDescription,
                    'stage' =>$delivOrder,
                    //'approval-status'=>$approval,
                    'draft-deadline' => array('start' => $draftDeadline->format('Y-m-d H:i:s')),
                    'due-date' => array('start' => $dueDate->format('Y-m-d H:i:s')),
                ),
                array(
                    'hook' => false
                )
            ));

            $deliverableItemID = $createDeliv->item_id;
            $updateTiggerDeliv = PodioItem::update($itemID, array(
                    'fields' => array(
                        'database-job-item'=>array((int)$deliverableItemID)),
                    array(
                        'hook' => false
                    )
                )
            );

        }
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






//        //Get Client Space Info
//        $clientItem = PodioItem::get($clientID);
//        $clientSpaceID = $clientItem->fields['workspace-id']->values;
//        $clientProjectApp = $clientItem->fields['projects-app-id']->values;
//
//        $clientSpaceProjectsFilter = PodioItem::filter($clientProjectApp, $attributes = array('filters'=>array('project'=>(int)$item_id)));
//        $clientProjectsCount = sizeof($clientSpaceProjectsFilter);
//
//        if($clientProjectsCount == 0) {
//            PodioItem::create($clientProjectApp, $attributes = array(
//                'fields' => array(
//                    'project' => (int)$item_id,
//                )
//            )
//            );
//        }






