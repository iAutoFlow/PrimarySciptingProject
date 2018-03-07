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

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $newEmailItem = PodioItem::get($itemID);

    $appName = $newEmailItem->app->name;

    $appID = $newEmailItem->app->app_id;

    if($appID == 15595688){


        //Get fields values from item
        $projectName = $newEmailItem->fields['title']->values;
        $clientID = $newEmailItem->fields['client']->values[0]->item_id;
        $projectScope = $newEmailItem->fields['project-scope']->values;
        $projectUnique = $newEmailItem->fields['client-project-unique-identifier']->values;
        $billingID = $newEmailItem->fields['billing-id']->values;



        //Get Client Space Info
        $clientItem = PodioItem::get($clientID);
        $clientSpaceID = $clientItem->fields['workspace-id']->values;
        $clientProjectApp = $clientItem->fields['projects-app-id']->values;

        $clientSpaceProjectsFilter = PodioItem::filter(
            $clientProjectApp, array(
                'filters' => array(
                    'project' => (int)$itemID
                )
            )
        );

        $clientProjectsItemID = $clientSpaceProjectsFilter[0]->item_id;

        if(!$clientProjectsItemID){
            PodioItem::create($clientProjectApp, array(
                    'fields' => array(
                        'project-name'=> $projectName,
                        'project' => (int)$itemID,
                        'project-scope'=> $projectScope,
                        'billing-id'=>$billingID,
                    ),
                    array(
                        'hook' => false
                    )
                )
            );
        }
        else{
            $updateClientProject = PodioItem::update($clientProjectsItemID, array(
                    'fields' => array(
                        'project-name'=> $projectName,
                        'project-scope'=> $projectScope,
                        'billing-id'=>$billingID,
                    ),
                    array(
                        'hook' => false
                    )
                )
            );
        }

    }

    else{
        $projectName = $newEmailItem->fields['project-name']->values;
        $projectScope = $newEmailItem->fields['project-scope']->values;
        $projectUnique = $newEmailItem->fields['unique-project-identifier']->values;
        $billingID = $newEmailItem->fields['billing-id']->values;

        $filterClientApp = PodioItem::filter(15595578, array('filters'=>array('projects-app-id'=>(string)$appID)));
        $clientID = $filterClientApp[0]->item_id;


        $projectsFilter = PodioItem::filter(15595688, array('filters'=>array('client-project-unique-identifier'=>$projectUnique)));
        $projectItemID = $projectsFilter[0]->item_id;

        if(!$projectItemID) {
            $newProject = PodioItem::create(15595688, array(
                    'fields' => array(
                        'title'=> $projectName,
                        'project-scope'=> $projectScope,
                        'status-2'=>"Active",
                        'priority'=>"Future Project",
                        'billing-id'=>$billingID,
                        'client'=>(int)$clientID,
                    ),
                    array(
                        'hook' => false
                    )
                )
            );
            PodioItem::update($itemID, array(
                'fields'=> array(
                    'project'=>(int)($newProject->item_id)
                ),
                array(
                    'hook' => false
                )
            ));
        }
        else{
            $updateProject = PodioItem::update($projectItemID, array(
                    'fields' => array(
                        'title'=> $projectName,
                        'project-scope'=> $projectScope,
                        'billing-id'=>$billingID,
                    ),
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





