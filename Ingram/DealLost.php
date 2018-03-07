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

///AUTOMATION START

    $opportunityItem = PodioItem::get($item_id);

    $status = $opportunityItem->fields['deal-stage']->values[0]['text'];

    if($status !== "Deal Lost"){

        return;

    }

    $oppReferences = PodioItem::get_references($item_id);

    $scopeArray = array();
    $fullScopeArray = array();

    foreach($oppReferences as $reference){

        if($reference['app']['app_id'] == 10226461){

            foreach($reference['items'] as $scopeItem){

                $fullScopeArray[] = $scopeItem['item_id'];

                $subscopeFlag = false;

                $scopeReferences = PodioItem::get_references($scopeItem['item_id']);

                foreach($scopeReferences as $scopeReference){

                    if($scopeReference['app']['app_id'] == 10411647){

                        foreach($scopeReference['items'] as $subscopeItem){

                            $fullScopeArray[] = $subscopeItem['item_id'];

                            $scopeArray[] = $subscopeItem['item_id'];

                        }

                        $subscopeFlag = true;
                    }

                }//end scopeReference

                if($subscopeFlag == false){

                    $scopeArray[] = $scopeItem['item_id'];

                }

            }//end referencedScope Loop

        }//end scope items

        if($reference['app']['app_id'] == 13906476){

            $projectDashboardItemID = $reference['items'][0]['item_id'];

        }//end dashboard items

        if($reference['app']['app_id'] == 12099103){

            foreach($reference['items'] as $actualItem){

                PodioItem::delete($actualItem['item_id']);

            }

        }

        if($reference['app']['app_id'] == 10702577){

            $aptItemID = $reference['items'][0]['item_id'];

            PodioItem::update($aptItemID, array('fields'=>array('docverify'=>"Deal Lost")));

        }

    }//end oppReference loop

    $deliverableArray = array();

    foreach($scopeArray as $scope){

        $scopeReferences2 = PodioItem::get_references($scope);

        foreach($scopeReferences2 as $scopeReference2){

            if($scopeReference2['app']['app_id'] == 10827874){

                foreach($scopeReference2['items'] as $deliverableItemReference){

                    $deliverableArray[] = $deliverableItemReference['item_id'];

                }

            }

        }

    }

    $jobsArray = array();

    foreach($deliverableArray as $deliverableItemID){

        $deliverableReferences = PodioItem::get_references($deliverableItemID);

        foreach($deliverableReferences as $deliverableReference){

            if($deliverableReference['app']['app_id'] == 14269585 || $deliverableReference['app']['app_id'] == 13869166 || $deliverableReference['app']['app_id'] == 14276642 || $deliverableReference['app']['app_id'] == 14276675 || $deliverableReference['app']['app_id'] == 14535583 || $deliverableReference['app']['app_id'] == 14276676){

                foreach($deliverableReference['items'] as $jobItemReference){

                    $jobsArray[] = $jobItemReference['item_id'];

                }

            }

        }

    }

    foreach($jobsArray as $jobItemID){

        unset($milestonesArray);

        $completedCheck = false;

        $jobItem = PodioItem::get($jobItemID);

        $jobDeliverables = $jobItem->fields['deliverable']->values;

        if(sizeof($jobDeliverables) > 1){
            continue;
        }

        $jobReferences = PodioItem::get_references($jobItemID);

        $milestonesArray = array();

        foreach($jobReferences as $jobReference){

            if($jobReference['app']['app_id'] == 14269597 || $jobReference['app']['app_id'] == 13869287 || $jobReference['app']['app_id'] == 14277392 || $jobReference['app']['app_id'] == 14276762 || $jobReference['app']['app_id'] == 14276766){

                foreach($jobReference['items'] as $milestoneItem){

                    $milestonesArray[] = $milestoneItem['item_id'];

                    $milestoneItem = PodioItem::get($milestoneItem['item_id']);

                    $milestoneStatus = $milestoneItem->fields['status']->values[0]['text'];

                    if($milestoneStatus == "Completed"){

                        $completedCheck = true;

                    }

                }

            }//end milestone references

            if($jobReference['app']['app_id'] == 14269587 || $jobReference['app']['app_id'] == 13869216 || $jobReference['app']['app_id'] == 14277391 || $jobReference['app']['app_id'] == 14276678 || $jobReference['app']['app_id'] == 14276679){

                $dispatchItemID = $jobReference['items'][0]['item_id'];

            }

        }//end jobReferences

        if($completedCheck == false){

            if($dispatchItemID) {
                PodioItem::delete($dispatchItemID);
            }

            foreach($milestonesArray as $milestoneItemID){

                PodioItem::delete($milestoneItemID);

            }

            PodioItem::delete($jobItemID);

        }
        else{

            PodioItem::update($jobItemID, array('fields'=>array('creative-status'=>"Canceled")));

            PodioComment::create('item', $jobItemID, array('value'=>"Deal Lost on Opportunity. Job marked as Canceled"));

        }

    }//end jobsArray loop

    foreach($deliverableArray as $deliverableItemID){

        PodioItem::delete($deliverableItemID);

    }

    if($projectDashboardItemID) {
        PodioItem::delete($projectDashboardItemID);
    }

    foreach($fullScopeArray as $scopeItemID){

        PodioItem::delete($scopeItemID);

    }

//END AUTOMATION

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

?>