<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
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
//    $itemID = $requestParams['item_id'];

//    $item = PodioItem::get($itemID);
//    $appName = $item->app->name;
//    $appID = $item->app->app_id;


    $DeliverablesAppID = 10827874;
    $DelivNoScopeViewID = 29838695;

    $SubScopeAppID = 10411647;
    $SubScopeNoScopeViewID = 29838700;




    //Get ALL Deliverables with NO Related Scope Item

    $FilterDeliverables = PodioItem::filter_by_view($DeliverablesAppID, $DelivNoScopeViewID);
    foreach($FilterDeliverables as $deliverable) {

        $DeliverableItemID = $deliverable->item_id;
        $OpportunityItemID = $deliverable->fields['opportunity']->values[0]->item_id;

        $RelatedJobItemIDsArray = array();

        $RelatedJobItems = PodioItem::get_references($DeliverableItemID);
        foreach ($RelatedJobItems as $Job) {
            //Admin
            if ($Job['app']['app_id'] == 14269585) {
                $JobItems = $Job['items'];
                foreach ($JobItems as $jobitem) {
                    $JobItemID = $jobitem['item_id'];
                    $DispatchAppID = 14269587;
                    array_push($RelatedJobItemIDsArray, $JobItemID);

                }
            }

            //Creative
            if ($Job['app']['app_id'] == 13869166) {
                $JobItems = $Job['items'];
                foreach ($JobItems as $jobitem) {
                    $JobItemID = $jobitem['item_id'];
                    $DispatchAppID = 13869216;
                    array_push($RelatedJobItemIDsArray, $JobItemID);
                }
            }

            //Events
            if ($Job['app']['app_id'] == 14276642) {
                $JobItems = $Job['items'];
                foreach ($JobItems as $jobitem) {
                    $JobItemID = $jobitem['item_id'];
                    $DispatchAppID = 14277391;
                    array_push($RelatedJobItemIDsArray, $JobItemID);
                }
            }

            //Marketing Services
            if ($Job['app']['app_id'] == 14276675) {
                $JobItems = $Job['items'];
                foreach ($JobItems as $jobitem) {
                    $JobItemID = $jobitem['item_id'];
                    $DispatchAppID = 14276678;
                    array_push($RelatedJobItemIDsArray, $JobItemID);
                }
            }

            //Sales Engagement
            if ($Job['app']['app_id'] == 14276676) {
                $JobItems = $Job['items'];
                foreach ($JobItems as $jobitem) {
                    $JobItemID = $jobitem['item_id'];
                    $DispatchAppID = 14276679;
                    array_push($RelatedJobItemIDsArray, $JobItemID);
                }
            }


            foreach ($RelatedJobItemIDsArray as $RelatedJobItemID) {

                $RelatedMilestoneItemIDsArray = array();

//                $RelatedDispatchItem = PodioItem::filter($DispatchAppID, array('filters' => array('team-job' => array((int)$RelatedJobItemID))));
//                $RelatedDispatchItemID = $RelatedDispatchItem->item_id;

                $RelatedJobItem = PodioItem::get($RelatedJobItemID);
                $JobAppID = $RelatedJobItem->app_id;
                $RelatedMilestoneItems = PodioItem::get_references($RelatedJobItemID);
                foreach ($RelatedMilestoneItems as $Milestone) {
                    //Admin
                    if ($Milestone['app']['app_id'] == 14269597) {
                        $MilestoneItems = $Milestone['items'];
                        foreach ($MilestoneItems as $milestoneitem) {
                            $MilestoneItemID = $milestoneitem['item_id'];
                            array_push($RelatedMilestoneItemIDsArray, $MilestoneItemID);
                        }
                    }

                    //Creative
                    if ($Milestone['app']['app_id'] == 13869287) {
                        $MilestoneItems = $Milestone['items'];
                        foreach ($MilestoneItems as $milestoneitem) {
                            $MilestoneItemID = $milestoneitem['item_id'];
                            array_push($RelatedMilestoneItemIDsArray, $MilestoneItemID);
                        }
                    }

                    //Events
                    if ($Milestone['app']['app_id'] == 14277392) {
                        $MilestoneItems = $Milestone['items'];
                        foreach ($MilestoneItems as $milestoneitem) {
                            $MilestoneItemID = $milestoneitem['item_id'];
                            array_push($RelatedMilestoneItemIDsArray, $MilestoneItemID);
                        }
                    }

                    //Marketing Services
                    if ($Milestone['app']['app_id'] == 14276762) {
                        $MilestoneItems = $Milestone['items'];
                        foreach ($MilestoneItems as $milestoneitem) {
                            $MilestoneItemID = $milestoneitem['item_id'];
                            array_push($RelatedMilestoneItemIDsArray, $MilestoneItemID);
                        }
                    }

                    //Sales Engagement
                    if ($Milestone['app']['app_id'] == 14276766) {
                        $MilestoneItems = $Milestone['items'];
                        foreach ($MilestoneItems as $milestoneitem) {
                            $MilestoneItemID = $milestoneitem['item_id'];
                            array_push($RelatedMilestoneItemIDsArray, $MilestoneItemID);
                        }
                    }


                    $FinalStatus = "ZERO";
                    foreach ($RelatedMilestoneItemIDsArray as $milestone) {
                        $MilestoneItem = PodioItem::get($milestone);
                        $MilestoneStatus = $MilestoneItem->fields['status']->values[0]['text'];
                        if ($MilestoneStatus == "Completed") {
                            $FinalStatus = "Mark Job as Canceled";
                        }}

                    if($FinalStatus == "Mark Job as Canceled") {
                        foreach ($RelatedJobItemIDsArray as $jobID) {
                            $UpdateJobItem = PodioItem::update($jobID, array(
                                'fields' => array(
                                    'creative-status' => "Canceled"
                                )));
                        }}

                    elseif($FinalStatus == "ZERO"){
                        foreach($RelatedMilestoneItemIDsArray as $milestone) {
                            $DeleteMilestone = PodioItem::delete($milestone);
                        }
                        foreach($RelatedJobItemIDsArray as $RelatedJobItemID) {
                            $RelatedDispatchItem = PodioItem::filter($DispatchAppID, array('filters' => array('team-job' => array((int)$RelatedJobItemID))));
                            $RelatedDispatchItemID = $RelatedDispatchItem[0]->item_id;
                            $DeleteDispatch = PodioItem::delete($RelatedDispatchItemID);
                            $DeleteJob = PodioItem::delete($RelatedJobItemID);
                        }
                    }


                }
            }
        }

        $DeleteDeliverable = PodioItem::delete($DeliverableItemID);
    }



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