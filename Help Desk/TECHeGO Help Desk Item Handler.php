<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/3/2016
 * Time: 1:41 PM
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


    if($appID == 16426527) {$RelatedHDExID = 'related-42-help-desk-item-2';}
    else{$RelatedHDExID = 'related-42-help-desk-item';}



    //Get fields values from item
    $Title = $item->fields['task']->values;
    $Classification = $item->fields['classification']->values[0]['text'];
    $Status = $item->fields['status']->values[0]['text'];
    $DateSubmitted = $item->fields['date-submitted']->start_date->format('Y-m-d H:i:s');
    $SubmittedBy = $item->fields['who-submitted-ticket']->values[0]->profile_id;
    $Description = $item->fields['additional-information']->values;
    $Deliverable = $item->fields['deliverable']->values[0]->item_id;
    $Project = $item->fields['project']->values[0]->item_id;
    $LinkToItem = $item->fields['link-to-app-item-with-issue']->values[0]->original_url;
    $RelatedHDItemID = $item->fields[$RelatedHDExID]->values[0]->item_id;



    //Format Date Submitted Value
    $FormatDateSubmitted = new DateTime((string)$DateSubmitted, new DateTimeZone('America/Denver'));

    //Create Embed Link with to referenced item
    $CreateEmbedFile = PodioEmbed::create(array('url' => $LinkToItem));
    $LinkEmbedID = $CreateEmbedFile->embed_id;



    //Create New Intem in 4.2 if the is no Related HD Ticket Item
    if(!$RelatedHDItemID) {

        //Get Client Space Info ItemID
        $FilterClientSpaceInfo = PodioItem::filter(13941091, array('filters' => array('app-id' => (string)$appID)));
        $ClientSpaceInfoItemID = $FilterClientSpaceInfo[0]->item_id;

        //Get 4.2 Deliverable ItemID
        if ($Deliverable) {
            $DeliverableItem = PodioItem::get($Deliverable);
            $ProjectDeliverableItemID = $DeliverableItem->fields['action-item']->values[0]->item_id;
        }

        //Get 4.2 Project ItemID
        $ProjectItem = PodioItem::get($Project);
        $ProjectItemID = $ProjectItem->fields['project-2']->values[0]->item_id;

        //Get Account Owner Profile ID
        $MainProjectItem = PodioItem::get($ProjectItemID);
        $AccountManagerItemID = $MainProjectItem->fields['project-manager-2']->values[0]->item_id;
        if ($AccountManagerItemID) {
            $EmployeeItem = PodioItem::get($AccountManagerItemID);
            $EmployeeProfileID = $EmployeeItem->fields['employee']->values[0]->profile_id;
        }

        //Fields Array
        $FieldsArray = array(
            'fields' => array(
                'task' => $Title,
                'date-submitted' => array('start' => $FormatDateSubmitted->format('Y-m-d H:i:s')),
                'status' => $Status,
                'classification' => $Classification,
                'who-submitted-ticket' => $SubmittedBy,
                'additional-information' => $Description,
            )
        );

        //Add Values to Field Array if not blank
        if ($ProjectItemID) {
            $FieldsArray['fields']['project'] = array((int)$ProjectItemID);
        }

        if ($EmployeeProfileID) {
            $FieldsArray['fields']['assigned-to'] = $EmployeeProfileID;
        }

        if ($ClientSpaceInfoItemID) {
            $FieldsArray['fields']['client-workspace-info-item'] = array((int)$ClientSpaceInfoItemID);
        }

        if ($ProjectDeliverableItemID) {
            $FieldsArray['fields']['deliverable'] = array((int)$ProjectDeliverableItemID);
        }
        if(!$ProjectDeliverableItemID){$FieldsArray['fields']['deliverable'] = [];}

        if ($LinkEmbedID) {
            $FieldsArray['fields']['link-to-app-item-with-issue'] = $LinkEmbedID;
        }


        //Create Item in 4.2 Projects Space - Help Desk App
        $CreateHelpDeskItem = PodioItem::create(16417135, $FieldsArray);
        $NewHelpDeskItemID = $CreateHelpDeskItem->item_id;


        //Update Trigger Item with Newly Created Item Relationship
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                $RelatedHDExID => array((int)$NewHelpDeskItemID),
            ),
            array(
                'hook' => false
            )
        ));
    }




    else{
        //Get Related 4.2 Help Desk Item
        $RelatedHDItem = PodioItem::get($RelatedHDItemID);

        $RelatedDeliverable = $RelatedHDItem->fields['deliverable']->values[0]->item_id;
        $Link = $RelatedHDItem->fields['link-to-app-item-with-issue']->values['url'];
        $CurrentStatus = $RelatedHDItem->fields['status']->values[0]['text'];
        $CurrentClassification = $RelatedHDItem->fields['classification']->values[0]['text'];
        $AdditionalInfo = $RelatedHDItem->fields['additional-information']->values;
        $ItemTitle = $RelatedHDItem->fields['task']->values;
        $SubmittedDate = $RelatedHDItem->fields['date-submitted']->start_date->format('Y-m-d H:i:s');

        $TaskItemID = $item->fields['task-id']->values;

        //If Status is CLosed, and there is a Task Item ID, Delete Task Item
        if($Status == "Closed" && $TaskItemID){
            $DeleteTask = PodioTask::delete($TaskItemID);
        }

        //Fields Array
        $FieldsArray = array(
            'fields' => array(
            ),
        );

        if($Link !== $LinkToItem){
            $FieldsArray['fields']['link-to-app-item-with-issue'] = $LinkEmbedID;
        }
        if($AdditionalInfo !== $Description){
            $FieldsArray['fields']['additional-information'] = $Description;
        }
        if($CurrentClassification !== $Classification){
            $FieldsArray['fields']['classification'] = $Classification;
        }
        if($CurrentStatus !== $Status){
            $FieldsArray['fields']['status'] = $Status;
        }
        if($SubmittedDate !== $DateSubmitted){
            $FieldsArray['fields']['date-submitted'] = array('start' => $FormatDateSubmitted->format('Y-m-d H:i:s'));
        }
        if($ItemTitle !== $Title){
            $FieldsArray['fields']['task'] = $Title;
        }


        //Update Help Desk Ticket Item in 4.2 that is related to trigger Item
        $UpdateTriggerItem = PodioItem::update($RelatedHDItemID, $FieldsArray, array('hook'=>false));

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





