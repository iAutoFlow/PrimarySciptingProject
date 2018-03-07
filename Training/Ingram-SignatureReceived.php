<?php

//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
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
        self::getConnection()->connectionService->config->client_id;
    }

    public static function getClientSecret () {
        self::getConnection()->connectionService->config->client_secret;
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
//END Authentication



//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];
//END Get data from Webhook

//Get APT Item that was triggered
    $apt_item = PodioItem::get($item_id);
//END get APT item

//Get APT Item field values
    //Opportunity Relationship field
    $OpportunityItemID = $apt_item->fields['opportunity']->values[0]->item_id;

    //Docverify field
    $DocVerify = $apt_item->fields['docverify']->values[0]['text'];

    //Initial Signature Received Date field
    $InitialSignatureDate = $apt_item->fields['initial-signature-received-date']->start;

//END Get APT field values

//Check Trigger
    if($DocVerify != "Signature Received"){
        $result ="Trigger was not 'Signature Receieved', call ended";
        return $result;
    }
//END Check Trigger

//Check if Initial Date is set, set it if not, otherwise call APT - Resigned and end this call
    if(!$InitialSignatureDate){
        //set Initial Siganture Receieved Date to current Date/time
        $InitialSignatureDate = date("Y-m-d H:i:s");
        //Set Timezone to Mountain (because AVA's Podio account is set to Mountain time)
        $InitialSignatureDateMountain = $InitialSignatureDate->setTimezone(new DateTimeZone('America/Denver'));

        //Update APT Item with Initial Signature Received Date
        PodioItem::update($item_id, array('fields'=>array('initial-signature-received-date'=>$InitialSignatureDateMountain)));
    }
    else{
        //Update APT item to Resigned (to trigger that automation) and end this call
        PodioItem::update($item_id, array('fields'=>array('docverify'=>"Resigned")));
        $result ="APT has been signed before, Resigned call triggered instead. Call ended.";
        return $result;
    }
//END check initial date

//Get Opportunity Item and field values
    $opp_item = PodioItem::get($OpportunityItemID);

    //Client Relationship field
    $ClientItemID = $opp_item->fields['client']->values[0]->item_id;

    //Stage field
    $Stage = $opp_item->fields['deal-stage']->values[0]['text'];

    //Opportunity Box Folder ID field
    $OppBoxID = $opp_item->fields['box-folder-id'];

    //Plan Relationship field
    $PlanItemID = $opp_item->fields['plan']->values[0]->item_id;

//END Get Opportunity Item and field values

//Opportunity Box Check
    if(!$OppBoxID){
        //Create Box Folder Need Box.com Oauth Set up on Hoist
//        $OppBoxId = BOX.COM RESPONSE - BOX FOLDER ID
//        $OppBoxLink = BOX.COM RESPONSE - BOX LINK
    }

    if(!$OppBoxLink){
        //Box - Share Folder
//    $OppBoxLink = BOX.COM RESPONSE - SHARED LINK URL
    }
//END Opportunity Box Check

//Get year from Plan item
    $plan_item = PodioItem::get($PlanItemID);

    //Get Year field
    $PlanYear = $plan_item->fields['year']->values[0]['text'];

    //get Customer Segment 1
    $PlanSegment = $plan_item->fields['customer-segment-1']->values[0]->item_id;

//END Get year from Plan item

//Get Client Item and field Values
    $client_item = PodioItem::get($ClientItemID);

    //get Client Box Folder ID
    $ClientBoxID = $client_item->fields['box-folder-id']->values;

    //get Division field
    $ClientDivision = $client_item->fields['division-2']->values[0]->item_id;

    //get Business Unit field
    $ClientBU = $client_item->fields['alignment']->values[0]->item_id;

    //get Master Vendor Code
    $ClientMasterVendorCode = $client_item->fields['impulse-customer']->values;

    //get Client Services Contact
    $CS_Contact = $client_item->fields['client-services-contact']->values[0]->item_id;

    //get Account Manager
    $AccountManager = $client_item->fields['account-manager']->values[0]->item_id;

//END Get Client Item and field Values

//Get Client Projects item
    $ClientProjectsFilter = PodioItem::filter(11878795, array('filters'=>array('client'=>$ClientItemID)));

    //if no Client Projects item, create new
    if(sizeof($ClientProjectsFilter) == 0){
        $ClientProjectsItem = PodioItem::create(11878795, array(
            'fields'=>array(
                'client'=>$ClientItemID,
                'recognition-type'=>"All",
                'team'=>'All',
                'box-link'=>$ClientBoxLink
            )
        ));

        $ClientProjectsItemID = $ClientProjectsItem->item_id;
    }
    else{
        $ClientProjectsItemID = $ClientProjectsFilter[0]->item_id;
    }
    //end if no Client Projects item, create new
//END Get Client Projects item

//Client Box Check
    if(!$ClientBoxID){
        //Create Box Folder Need Box.com Oauth Set up on Hoist
//        $ClientBoxId = BOX.COM RESPONSE - BOX FOLDER ID
//        $ClientBoxLink = BOX.COM RESPONSE - BOX LINK
    }
    else{
        //Get Box Folder Information
//        $ClientBoxLink = BOX.COM RESPONSE -SHARED LINK URL
    }

    if(!$ClientBoxLink){
        //Box - Share Folder
//    $ClientBoxLink = BOX.COM RESPONSE - SHARED LINK URL
    }
//END Client Box Check

//Duplication Handling

    //Filter and Delete Existing Actuals
    $ActualFilter = PodioItem::filter(12099103, array('filters'=>array('opportunity'=>$OpportunityItemID)));

    foreach($ActualFilter as $actual_item){
        PodioItem::delete($actual_item);
    }
    //END Filter and Delete Existing Actuals

    //Filter and Delete Existing Actual Breakouts
    $BreakoutFilter = PodioItem::filter(13016188, array('filters'=>array('opportunity'=>$OpportunityItemID)));

    foreach($BreakoutFilter as $breakout_item){
        PodioItem::delete($breakout_item);
    }
    //END Filter and Delete Existing Actual Breakouts

    //Filter and Delete Existing Project Dashboards
    $DashboardFilter = PodioItem::filter(13906476, array('filters'=>array('project-title'=>$OpportunityItemID)));

    foreach($DashboardFilter as $dashboard_item){
        PodioItem::delete($dashboard_item);
    }
    //END Filter and Delete Existing Project Dashboards

    //Filter and Delete Existing Deliverables
    $DeliverableFilter = PodioItem::filter(10827874, array('filters'=>array('opportunity'=>$OpportunityItemID)));

    foreach($DeliverableFilter as $deliverable_item){
        PodioItem::delete($deliverable_item);
    }
    //END Filter and Delete Existing Deliverables

//END Duplication Handling

//Check for Client Financials Item
    $ClientFinancialsFilter = PodioItem::filter(10411991, array('filters'=>array('client'=>$ClientItemID)));

    //Create Client Financials Item if it doesn't exist
    if(sizeof($ClientFinancialsFilter) == 0){
        $ClientFinancialsItem = PodioItem::create(10411991, array(
            'fields'=>array(
                'client'=>$ClientItemID,
                'wbs'=>"All",
                'year-sort'=>"2016",
                'month'=>"All",
                'table-sort'=>"None"
            )
        ));

        $ClientFinancialsItemID = $ClientFinancialsItem->item_id;
    }
    else{
        $ClientFinancialsItemID = $ClientFinancialsFilter[0]->item_id;
    }

//END Check for Client Financials Item

//Filter & Loop Scopes
    //Initialize ScopeObject
    $ScopeObjectList = array();
    //End Initialize ScopeObject

    $ScopeFilter = PodioItem::filter(10226461, array('filters'=>array('opportunity'=>$OpportunityItemID)));

    //Loop Scopes
    foreach($ScopeFilter as $scope_item){
        //Filter Sub-Scopes
        $SubScopeFilter = PodioItem::filter(10411647, array('filters'=>array('scope'=>$scope_item->item_id)));
        //END Filter Sub-Scopes

        //Check SubScopes add proper info to Scope Object
        if(sizeof($SubScopeFilter) == 0){
            $ScopeObject = new stdClass();
            $ScopeObject->scope = $scope_item->item_id;
            $ScopeObject->subscope = null;
            array_push($ScopeObjectList, $ScopeObject);
        }
        else{
            foreach($SubScopeFilter as $sub_scope) {
                $ScopeObject = new stdClass();
                $ScopeObject->scope = $scope_item->item_id;
                $ScopeObject->subscope = $sub_scope->item_id;
                array_push($ScopeObjectList, $ScopeObject);
            }
        }
        //END Check SubScopes add proper info to Scope Object

    }

//END Filter & Loop Scopes

//Initialize $FinalBOMbListArray with text array bracket
    $FinalBOMbListArray = '[';

//Loop ScopeObjectList
    foreach($ScopeObjectList as $Scope){
        //Initialize some vars
        $ProductLineItemID = null;
        $WBSItemID = null;
        $TeamItemID = null;
        $BOMbElementsArray = array();
        //END Initialize some vars

        //Get Scope Item
        $scope_item = PodioItem::get($Scope->scope);
        //END Get Scope Item

        //Get Scope Field Values
        //Product Line field
        $ProductLineItemID = $scope_item->fields['description']->values[0]->item_id;

        //BOMbElements field
        $BOMbElementsArray =  $scope_item->fields['bomb-elements']->values; //Array of all BOMb Elements
        //END Get Scope Field Values

        //Get Product Line item and field values
        $product_line_item = PodioItem::get($ProductLineItemID);

        //Get WBS field
        $WBSItemID = $product_line_item->fields['master-wbs']->values[0]->item_id;

        //END Get Product Line item and field values

        //Get WBS item and field values
        $wbs_item = PodioItem::get($WBSItemID);

        //Get Team field
        $TeamItemID = $wbs_item->fields['team']->values[0]->item_id;
        //END Get WBS item and field values

    }

    //Upload Existing Files to Box.com
    //Do when we have Box.com access
    //END Upload Existing Files to Box.com

    //Create Project Dashboard
    $ProjectDashboardItem = PodioItem::create(13906476, array(
        'fields'=>array(
            'project-title'=>$OpportunityItemID,
            'client'=>$ClientItemID,
            'box-link'=>$OppBoxLink,
            'push-to-teams-2'=>"Not Yet Pushed",
            'client-services-contact'=>$CS_Contact,
            'account-manager'=>$AccountManager
        )
    ));

    $ProjectDashboardItemID = $ProjectDashboardItem->item_id;
    //END Create Project Dashboard

    //Build Unique BOMb Elements Array

    foreach($BOMbElementsArray as $BOM) {
        $FinalBOMbListArray .= $BOM.',';
    };

    //END Build Unique BOMb Elements Array

    //Create Deliverable for Scope
    $DeliverableItem = PodioItem::create(10827874, array(
        'fields'=>array(
            'opportunity'=>$OpportunityItemID,
            'project-scope'=>$Scope->scope,
            'subscope'=>$Scope->subscope,
            'client-dashboard'=>$ClientFinancialsItemID,
            'project-dashboard'=>$ProjectDashboardItemID,
            'team'=>$TeamItemID,
            'box-link'=>$OppBoxLink,
            'pm-report'=>285184720,
            'bomb-elements'=>$BOMbElementsArray
        )
    ));

    $DeliverableItemID = $DeliverableItem->item_id;
    //END Create Deliverable for Scope

    //Create Actual for Scope
    $ActualItem = PodioItem::create(12099103, array(
        'fields'=>array(
            'financial-dashboard'=>$ClientFinancialsItemID,
            'opportunity'=>$OpportunityItemID,
            'relationship'=>$Scope->scope,
            'sub-scope'=>$Scope->subscope,
            //'project-dashboard'=>$ProjectDashboardItemID,
            'team'=>$TeamItemID,
            'client'=>$ClientItemID,
            'wbs-4'=>$WBSItemID,
            'business-unit'=>$ClientBU,
            'division'=>$ClientDivision,
            'segment'=>$PlanSegment,
            //'vendor-code'=>$ClientMasterVendorCode,
            //'deliverable'=>$DeliverableItemID,
            'accounting-report'=>285161170,
            'program-rollup-report'=>431571953
        )
    ));
    //END Create Actual for Scope

//END Loop ScopeObject List

//Clean up FinalBOMbListArray
    //Trim trailing comma
    $FinalBOMbListArray = rtrim($FinalBOMbListArray, ',');

    //add closing square bracket (for text array)
    $FinalBOMbListArray.=']';
//END Clean up FinalBOMbListArray

//Add BOMb Elements Array to Project Dashboard
    PodioItem::update($ProjectDashboardItemID, array(
        'fields'=>array(
            'bomb-elements'=>$FinalBOMbListArray
        )
    ));
//END Add BOMb Elements Array to Project Dashboard


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