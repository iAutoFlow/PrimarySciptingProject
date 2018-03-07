<?php

$df_api_key = '1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';
$curl = new \Curl\Curl();
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

    $opportunityItemID = $opportunityItem->item_id;

    $oppName = $opportunityItem->fields['title-2']->values;

    $boxOppName = str_replace('/', '_', $oppName);

    $oppBoxID = $opportunityItem->fields['box-folder-id']->values;

    $oppBoxLink = $opportunityItem->fields['box-folder-link']->values[0]->original_url;

    $fixitTrigger = $opportunityItem->fields['fix-it']->values[0]['text'];

    $clientItemID = $opportunityItem->fields['client']->values[0]->item_id;

//Get Client Item and field Values
    $client_item = PodioItem::get($clientItemID);

    //client DnB Name
    $clientDNBName = $client_item->fields['client-company-name']->values;

    //get Client Box Folder ID
    $clientBoxID = $client_item->fields['box-folder-id']->values;
//End Client Values

    $numThereShouldBe = $opportunityItem->fields['of-scopes']->values;

    $SDVariance = (int)$opportunityItem->fields['scopedeliv-variance']->values;

    $ActualVariance = $opportunityItem->fields['scopesub-actual-variance']->values;

    $oppStatus = $opportunityItem->fields['sales-cycle-status-calculation']->values;

    $delivCount = $opportunityItem->fields['of-sap-reports']->values;

    $aptStatus = $opportunityItem->fields['docverify-status-from-apt']->values[0]['text'];

    $projectDashboardFilter = PodioItem::filter(13906476, array('filters'=>array('project-title'=>array($item_id))));

    $projectDashboardItemID = $projectDashboardFilter[0]->item_id;

    //Box Folders Check
    if(!$clientBoxID) {
        //Look for Client Box Folder
        $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=$clientDNBName";
        $boxCurl4 = $curl->get($urlString);
        $boxResponse4 = json_decode($boxCurl4);

        $clientBoxID = $boxResponse4->id;
    }
    if(!$oppBoxLink) {
        if(!$oppBoxID) {
            //Look for Opportunity Box Folder
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/getFolderIDByName?api_key=$df_api_key&connection_id=88&name=".urlencode($boxOppName);
            $boxCurl = $curl->get($urlString);
            $boxResponse = json_decode($boxCurl);

            $oppBoxID = $boxResponse->id;


        }
        if($oppBoxID) {
            $urlString = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createSharedLink?api_key=$df_api_key&connection_id=88&folder_id=$oppBoxID";
            $boxCurl2 = $curl->get($urlString);

            $oppBoxLink = $boxCurl2;
        }else {
            $urlString3 = "https://hoist.thatapp.io/api/v2/boxPHP/folders/createFolderGetLink?api_key=$df_api_key&connection_id=88&name=" . urlencode($boxOppName) . "&parent_id=$clientBoxID";
            $boxCurl3 = $curl->get($urlString3);
            $boxResponse3 = json_decode($boxCurl3);

            $oppBoxID = $boxResponse3->id;
            $oppBoxLink = $boxResponse3->url;
        }

        $boxLinkEmbedMissing = PodioEmbed::create(array('url'=>$oppBoxLink));
        PodioItem::update($item_id, array('fields'=>array('box-folder-link'=>$boxLinkEmbedMissing->embed_id,'box-folder-id'=>$oppBoxID)));
    }



//END Box Folders Check

    $clientProjectsFilter = PodioItem::filter(11878795, array('filters'=>array('client'=>array($clientItemID))));

    $clientProjectsItemID = $clientProjectsFilter[0]->item_id;

    //CHECK APT STATUS
    if($aptStatus == "Out for Signature" || $aptStatus == "Deal Lost"){
        $UpdateTriggerItem = PodioItem::update($item_id, array(
                'fields' => array(
                    'fix-it' => null
                ),
                array(
                    'hook' => false
                )
            )
        );

        $AddComment = PodioComment::create('item', $item_id, array(
                'value' => "Contract not yet signed."
            )
        );
        exit;
    }

    //Fix Deliverables

    if($fixitTrigger == "Fix Deliverables") {

        if($SDVariance == 0){
            $UpdateTriggerItem = PodioItem::update($item_id, array(
                    'fields' => array(
                        'fix-it' => null
                    ),
                    array(
                        'hook' => false
                    )
                )
            );

            $AddComment = PodioComment::create('item', $item_id, array(
                    'value' => "No variance on Deliverables."
                )
            );
            exit;
        }

        $scopeItemsFilter = PodioItem::filter(10226461, array('filters'=>array('opportunity'=>array($item_id))));

        $deliverableFilterLoop = array();

        foreach($scopeItemsFilter as $scope) {

            $WBS;
            $WBSTeam;

            $scopeItemID = $scope->item_id;

            $scopeDescription = $scope->fields['description-2']->values;

            $scopeBOMElements = $scope->fields['bomb-elements']->values;

            $productLineItemID = $scope->fields['description']->values[0]->item_id;

            $producLineItem = PodioItem::get($productLineItemID);

            $WBSItemID = $producLineItem->fields['master-wbs']->values[0]->item_id;

            $WBSItem = PodioItem::get($WBSItemID);

            $WBSTeam = $WBSItem->fields['team']->values[0]->item_id;

            $subscopeItemsFilter = PodioItem::filter(10411647, array('filters'=>array('scope'=>array($scopeItemID))));

            $subscopeCount = (int)sizeof($subscopeItemsFilter);

            //unset($deliverableFilterLoop);
            //$deliverableFilterLoop = array();

            if($subscopeCount > 0) {

                foreach($subscopeItemsFilter as $subscope) {

                    $subscopeProductLineItemID = $subscope->fields['product-line']->values[0]->item_id;

                    $deliverableFilterLoop[] = array('scopeItemID'=>$scopeItemID,'productLineItemId'=>$subscopeProductLineItemID,'subscopeItemID'=>$subscope->item_id,'description'=>$scopeDescription,'WBSItemID'=>$WBSItemID,'WBSTeam'=>$WBSTeam,'BOMElements'=>$scopeBOMElements);

                }
            }
            else{

                $deliverableFilterLoop[] = array('scopeItemID'=>$scopeItemID,'productLineItemId'=>$productLineItemID,'subscopeItemID'=>null,'description'=>$scopeDescription,'WBSItemID'=>$WBSItemID,'WBSTeam'=>$WBSTeam,'BOMElements'=>$scopeBOMElements);

            }

        }//end scope loop

        foreach($deliverableFilterLoop as $scopeLoopItem){

            $boxLinkEmbed = PodioEmbed::create(array('url'=>$oppBoxLink));

            if($scopeLoopItem->subscopeItemID){

                $subscopeLoopItemID = $scopeLoopItem['subscopeItemID'];

                $deliverableFilter = PodioItem::filter(10827874, array('filters'=>array('subscope' => array($subscopeLoopItemID))));

            }
            else {
//if($scopeLoopItem->scopeItemID)
                $scopeLoopItemID = $scopeLoopItem['scopeItemID'];

                $deliverableFilter = PodioItem::filter(10827874, array('filters'=>array('project-scope' => array($scopeLoopItemID))));

            }

            if($SDVariance < 0) {

                if(sizeof($deliverableFilter) > 1) {

                    for($i = 1; $i < sizeof($deliverableFilter); $i++) {

                        $deliverableItemID = $deliverableFilter->item_id;

                        $referencedItems = PodioItem::get_references($deliverableItemID);

                        foreach($referencedItems as $referencedItem){
                            $referencedItemAppID = $referencedItem->app->app_id;

                            if($referencedItemAppID == 14269585 || $referencedItemAppID == 13869166 || $referencedItemAppID == 14276642 || $referencedItemAppID == 14276675 || $referencedItemAppID == 14535583 || $referencedItemAppID == 14276676){
                                $referencedJobs = $referencedItem->items;

                                foreach($referencedJobs as $job){
                                    $jobItemID = $job->item_id;

                                    $jobItem = PodioItem::get($jobItemID);

                                    $jobStatus = $jobItem->fields['creative-status']->values[0]['text'];

                                    $jobDelivs = $jobItem->fields['deliverable']->values;

                                    if(sizeof($jobDelivs) > 1){
                                        continue;
                                    }

                                    if($jobStatus == "In Progress" || $jobStatus == "Executed" || $jobStatus == "Ready to Bill" || $jobStatus == "Completed" || $jobStatus == "Canceled"){
                                        PodioItem::update($jobItemID, array(
                                            'fields'=>array(
                                                'creative-status'=>"Canceled"
                                            )
                                        ));
                                        continue;
                                    }
                                    else{
                                        $referencedJobItems = PodioItem::get_references($jobItemID);

                                        foreach($referencedJobItems as $jobReference){
                                            $jobReferenceAppID = $jobReference->app->app_id;

                                            //Dispatch Items
                                            if($jobReferenceAppID == 14269587 || $jobReferenceAppID == 13869216 || $jobReferenceAppID == 14277391 || $jobReferenceAppID == 14276678 || $jobReferenceAppID == 14276679){
                                                $referencedDispatchItems = $jobReference->items;

                                                foreach($referencedDispatchItems as $dispatchItem) {
                                                    $dispatchItemID = $dispatchItem->item_id;

                                                    PodioItem::delete($dispatchItemID);
                                                }

                                            }

                                            //Milestones
                                            if($jobReferenceAppID == 14269597 || $jobReferenceAppID == 13869287 || $jobReferenceAppID == 14277392 || $jobReferenceAppID == 14276762 || $jobReferenceAppID == 14276766){
                                                $referencedMilestoneItems = $jobReference->items;

                                                foreach($referencedMilestoneItems as $milestoneItem) {
                                                    $milestoneItemID = $milestoneItem->item_id;

                                                    PodioItem::delete($milestoneItemID);
                                                }

                                            }


                                        }

                                    }//end else

                                    PodioItem::delete($jobItemID);

                                }//end job loop

                            }//end if job app

                        }//end reference loop

                        PodioItem::delete($deliverableFilter[$i]->item_id);

                    }

                }
            }

            if($SDVariance > 0) {

                if(sizeof($deliverableFilter) == 0){
//                    print_r(sizeof($deliverableFilter));exit;

                    $delivScopeItem = $scopeLoopItem['scopeItemID'];

                    $fieldsArrayDeliv = array(
                        'fields'=>array(
                            'opportunity'=>array(
                                $item_id
                            ),
                            'project-dashboard'=>array(
                                $projectDashboardItemID
                            ),
                            'project-scope'=>array(
                                $delivScopeItem
                            ),
                            'client-dashboard'=>array(
                                $clientProjectsItemID
                            ),
                            'team'=>array(
                                $WBSTeam
                            ),
                            'so-status'=>"SO Needed",
                            'pm-report'=>285184720,
                        )
                    );

                    if($boxLinkEmbed->embed_id){
                        $fieldsArrayDeliv['fields']['box-link'] = $boxLinkEmbed->embed_id;
                    }

                    if($scopeLoopItem->subscopeItemID){

                        $fieldsArrayDeliv['fields']['subscope'] = $scopeLoopItem->subscopeItemID;

                    }

                    if($scopeLoopItem->scopeBOMElements){

                        $fieldsArrayDeliv['fields']['bomb-elements'] = $scopeLoopItem->scopeBOMElements;

                    }

                    $newDeliverable = PodioItem::create(10827874, $fieldsArrayDeliv);

                }

            }
        }

        sleep(10);

        $delivCheckFilter = PodioItem::filter(10827874, array('filters'=>array('opportunity'=>array($item_id))));

        if(sizeof($delivCheckFilter) == $numThereShouldBe){
            $UpdateTriggerItem = PodioItem::update($item_id, array(
                    'fields' => array(
                        'fix-it' => null
                    ),
                    array(
                        'hook' => false
                    )
                )
            );

            $AddComment = PodioComment::create('item', $item_id, array(
                    'value' => "Deliverables fixed"
                )
            );
            return;
        }
        else{
            $UpdateTriggerItem = PodioItem::update($item_id, array(
                    'fields' => array(
                        'fix-it' => null
                    ),
                    array(
                        'hook' => false
                    )
                )
            );

            $AddComment = PodioComment::create('item', $item_id, array(
                    'value' => "Deliverables not correctly fixed, try again."
                )
            );
            return;
        }


    }//end fix deliverables


    //Fix Actuals

    if($fixitTrigger == "Fix Actuals") {

//        if($ActualVariance == 0){
//            $UpdateTriggerItem = PodioItem::update($item_id, array(
//                    'fields' => array(
//                        'fix-it' => null
//                    ),
//                    array(
//                        'hook' => false
//                    )
//                )
//            );
//
//            $AddComment = PodioComment::create('item', $item_id, array(
//                    'value' => "No variance on Actuals."
//                )
//            );
//            return;
//        }

        $clientItem = PodioItem::get($clientItemID);

        $BU = $clientItem->fields['alignment']->values[0]->item_id;

        $division = $clientItem->fields['division-2']->values[0]->item_id;

        $clientFinancialsFilter = PodioItem::filter(10411991, array('filters'=>array('client'=>array($clientItemID))));

        $clientFinancialsItemID = $clientFinancialsFilter[0]->item_id;

        $aptFilter = PodioItem::filter(10702577, array('filters'=>array('opportunity'=>array($item_id))));

        $initialSRDate = $aptFilter[0]->fields['initial-signature-received-date']->start;

        if($initialSRDate) {
            $formattedISRDate = $initialSRDate->format('Y-m-d H:i:s');
        }

        $newISRDateUTC = new DateTime((string)$formattedISRDate, new DateTimeZone('UTC'));

        $newISRDateMST = $newISRDateUTC->setTimezone(new DateTimeZone('America/Denver'));

        if($newISRDateMST){
            $newISRDateMSTFormatted = $newISRDateMST->format('Y-m-d H:i:s');
        }

        $scopeItemsFilter = PodioItem::filter(10226461, array('filters'=>array('opportunity'=>array((int)$item_id))));



        foreach($scopeItemsFilter as $scope) {

            $WBS;
            $WBSTeam;

            $scopeItemID = $scope->item_id;

            $scopeDescription = $scope->fields['description-2']->values;

            $scopeBOMElements = $scope->fields['bomb-elements']->values;

            $productLineItemID = $scope->fields['description']->values[0]->item_id;

            $producLineItem = PodioItem::get($productLineItemID);

            $WBSItemID = $producLineItem->fields['master-wbs']->values[0]->item_id;

            $WBSItem = PodioItem::get($WBSItemID);

            $WBSTeam = $WBSItem->fields['team']->values[0]->item_id;

            $subscopeItemsFilter = PodioItem::filter(10411647, array('filters'=>array('scope'=>array($scopeItemID))));

            $subscopeCount = sizeof($subscopeItemsFilter);

            //$actualsFilterLoop = array();

            if($subscopeCount > 0) {

                foreach($subscopeItemsFilter as $subscope) {

                    $subscopeProductLineItemID = $subscope->fields['product-line']->values[0]->item_id;

                    $actualsFilterLoop[] = array('scopeItemID'=>$scopeItemID,'productLineItemId'=>$subscopeProductLineItemID,'subscopeItemID'=>$subscope->item_id,'description'=>$scopeDescription,'WBSItemID'=>$WBSItemID,'WBSTeam'=>$WBSTeam,'BOMElements'=>$scopeBOMElements);

                }
            }
            else{

                $actualsFilterLoop[] = array('scopeItemID'=>$scopeItemID,'productLineItemId'=>$productLineItemID,'subscopeItemID'=>null,'description'=>$scopeDescription,'WBSItemID'=>$WBSItemID,'WBSTeam'=>$WBSTeam,'BOMElements'=>$scopeBOMElements);

            }

        }


        $actualsFilter = PodioItem::filter(12099103, array('filters' => array('opportunity' => array($item_id))));

        foreach($actualsFilter as $actualItem) {
            $actualItemID = $actualItem->item_id;

            PodioItem::delete($actualItemID);
        }


        foreach($actualsFilterLoop as $scopeLoopItem){

            $programFinancialFilter = PodioItem::filter(15114635, array('filters'=>array('client'=>array($scopeLoopItem['WBSItemID']))));

            $fieldsArrayActual = array(
                'fields'=>array(
                    'opportunity'=>array(
                        $item_id
                    ),
                    'financial-dashboard'=>array(
                        $clientFinancialsItemID
                    ),
                    'relationship'=>array(
                        $scopeLoopItem['scopeItemID']
                    ),
                    'team'=>array(
                        $scopeLoopItem['WBSTeam']
                    ),
                    'client'=>array(
                        $clientItemID
                    ),
                    'wbs-4'=>array(
                        $scopeLoopItem['WBSItemID']
                    ),
                    'product-line'=>array(
                        $scopeLoopItem['productLineItemId']
                    ),
                    'business-unit'=>array(
                        $BU
                    ),
                    'division'=>array(
                        $division
                    ),
                    'accounting-report' => 285161170,
                    'program-rollup-report' => 431571953,
                    'program-dashboard' => $programFinancialFilter[0]->item_id
                )
            );

            if($scopeLoopItem['subscopeItemID']){

                $fieldsArrayActual['fields']['sub-scope'] = $scopeLoopItem['subscopeItemID'];

            }

            if($newISRDateMST){

                $fieldsArrayActual['fields']['initial-signature-received-date'] = array('start'=>$newISRDateMSTFormatted);

            }


            $newActual = PodioItem::create(12099103, $fieldsArrayActual);

        }


        sleep(10);

        $actualsFilter = PodioItem::filter(12099103, array('filters' => array('opportunity' => array($item_id))));

        if(count($actualsFilter) == $numThereShouldBe){
            $UpdateTriggerItem = PodioItem::update($item_id, array(
                    'fields' => array(
                        'fix-it' => null
                    ),
                    array(
                        'hook' => false
                    )
                )
            );

            $AddComment = PodioComment::create('item', $item_id, array(
                    'value' => "Actuals fixed!"
                )
            );
            return;
        }
        else{

            $UpdateTriggerItem = PodioItem::update($item_id, array(
                    'fields' => array(
                        'fix-it' => null
                    ),
                    array(
                        'hook' => false
                    )
                )
            );

            $AddComment = PodioComment::create('item', $item_id, array(
                    'value' => "Actuals not correctly fixed, try again."
                )
            );
            return;

        }

    }//end Fix Actuals

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