<?php
//Authentication
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



try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = (int)$requestParams['item_id'];

    $testDeleteVar = "";

    $subscopeItem = PodioItem::get((int)$item_id);

    $deleteTrigger = $subscopeItem->fields['delete-sub-scope-trigger']->values[0]['text'];

    if($deleteTrigger !== "Delete this Sub Scope Item"){
        throw new Exception("Incorrect Trigger Value");
    }

    $parentScopeItemID = $subscopeItem->fields['scope']->values[0]->item_id;

    $parentScopeItem = PodioItem::get($parentScopeItemID);

    $parentScopeDelivDates = $parentScopeItem->fields['breakout']->values;

    if($parentScopeDelivDates == "Yes") {

        $parentScopeQuantity = (int)$parentScopeItem->fields['quantity']->values;

        $parentScopeReferences = PodioItem::get_references($parentScopeItemID);

        $subScopeCount = 0;
        foreach($parentScopeReferences as $scopeReference) {
            if($scopeReference['app']['app_id'] == 10411647){
                $subScopeCount = (int)sizeof($scopeReference['items']);
            }
        }

    }

    if($parentScopeDelivDates == "No" || $parentScopeQuantity < $subScopeCount){

        $subscopeOpportunityItemID = $subscopeItem->fields['opportunity']->values[0]->item_id;

        $aptFilter = PodioItem::filter(10702577, array('filters'=>array('opportunity' => array((int)$subscopeOpportunityItemID))));

        $aptItemID = $aptFilter[0]->item_id;

        $aptItem = PodioItem::get($aptItemID);

        $docverifyStatus = $aptItem->fields['docverify']->values[0]['text'];

        if($docverifyStatus !== "Signature Received" && $docverifyStatus !== "Resigned" && !empty($docverifyStatus)) {
            PodioItem::delete($item_id);
            $testDeleteVar = "Only SubScope Deleted.";
            return;
        }

        $deliverableFilter = PodioItem::filter(10827874, array('filters'=>array('subscope' => array($item_id))));

        foreach($deliverableFilter as $deliverableItem) {

            $deliverableItemID = $deliverableItem->item_id;

            $referencedItems = PodioItem::get_references($deliverableItemID);

            foreach($referencedItems as $referencedItem) {
                $referencedItemAppID = $referencedItem['app']['app_id'];

                if($referencedItemAppID == 14269585 || $referencedItemAppID == 13869166 || $referencedItemAppID == 14276642 || $referencedItemAppID == 14276675 || $referencedItemAppID == 14535583 || $referencedItemAppID == 14276676) {
                    $referencedJobs = $referencedItem['items'];

                    foreach($referencedJobs as $job) {
                        $jobItemID = $job['item_id'];

                        $jobItem = PodioItem::get($jobItemID);

                        $jobStatus = $jobItem->fields['creative-status']->values[0]['text'];

                        $jobDelivs = $jobItem->fields['deliverable']->values;

                        if(sizeof($jobDelivs) > 1) {
                            continue;
                        }

                        if($jobStatus == "In Progress" || $jobStatus == "Executed" || $jobStatus == "Ready to Bill" || $jobStatus == "Completed" || $jobStatus == "Canceled") {
                            PodioItem::update($jobItemID, array(
                                'fields' => array(
                                    'creative-status' => "Canceled"
                                )
                            ));
                            continue;
                        } else {
                            $referencedJobItems = PodioItem::get_references($jobItemID);

                            foreach($referencedJobItems as $jobReference) {
                                $jobReferenceAppID = $jobReference['app']['app_id'];

                                //Dispatch Items
                                if($jobReferenceAppID == 14269587 || $jobReferenceAppID == 13869216 || $jobReferenceAppID == 14277391 || $jobReferenceAppID == 14276678 || $jobReferenceAppID == 14276679) {
                                    $referencedDispatchItems = $jobReference['items'];

                                    foreach($referencedDispatchItems as $dispatchItem) {
                                        $dispatchItemID = $dispatchItem['item_id'];

                                        PodioItem::delete($dispatchItemID);
                                        $testDeleteVar.= " | Dispatch Deleted";
                                    }

                                }

                                //Milestones
                                if($jobReferenceAppID == 14269597 || $jobReferenceAppID == 13869287 || $jobReferenceAppID == 14277392 || $jobReferenceAppID == 14276762 || $jobReferenceAppID == 14276766) {
                                    $referencedMilestoneItems = $jobReference['items'];

                                    foreach($referencedMilestoneItems as $milestoneItem) {
                                        $milestoneItemID = $milestoneItem['item_id'];

                                        PodioItem::delete($milestoneItemID);
                                        $testDeleteVar.= " | Milestone Deleted";
                                    }

                                }


                            }

                        }//end else

                        PodioItem::delete($jobItemID);
                        $testDeleteVar.= " | Job Deleted";

                    }//end job loop

                }//end if job app

            }//end reference loop

            PodioItem::delete($deliverableItemID);
            $testDeleteVar.= " | Deliverable Deleted";

        }//end deliverable/job/dispatch/milestones

        $actualsFilter = PodioItem::filter(12099103, array('filters' => array('sub-scope' => array($item_id))));

        foreach($actualsFilter as $actualItem) {
            $actualItemID = $actualItem->item_id;

            PodioItem::delete($actualItemID);
            $testDeleteVar.= " | Actual Deleted";
        }

        PodioItem::delete($item_id);
        $testDeleteVar.= " | SubScope Deleted";

    }
    else{
        PodioComment::create($item_id, array('value' => "Quantity on Scope must be less than total number of Sub Scopes to delete a Sub Scope."));
        return;
    }




    return [
        'success' => true,
        'result' => $testDeleteVar,
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

?>