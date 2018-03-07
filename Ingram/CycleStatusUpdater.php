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
    $item_id = $requestParams['item_id'];

    //Sleep to wait for calcs to update (if we go back to using the calc, uncomment this
    sleep(15);

//The Automation needs to be triggered when certain items are created that relate to an Opportunity (AppID: 9735424). When the calc field changes on the Opportunity, this automation will update the Sales Cycle Status field (exID: deal-stage) to be equal to the calculation field (exID: sales-cycle-status-calculation).
//This also probably needs to sleep for 5-10 seconds before running to make sure the calculation field on the Opportunity is actually updated (Since Podio calculation fields can be slow to update)
//Set an extra URL parameter for “trigger” on the hook.
//TRIGGER: Multiple:
//DocVerify field is set to “Out for Signature”
    //Trigger Parameter = docverify
//SAP Order # field (exID: sap-sales-order) updated on the Opportunity)
    //Trigger Parameter = sap
//Fix It Buttons
    //Trigger Parameter = fix
//List of relevant fields and external IDs organized  by App for this automation [Label (Type: Field ID) External ID]:
//Opportunity (AppID: 9735424)
    $triggerItem = PodioItem::get($item_id);

    $triggerAppID = $triggerItem->app->app_id;

    if($triggerAppID == 10702577){
        $aptItem = PodioItem::get($item_id);
        //APT Docverify field
        $APTDocverify = $aptItem->fields['docverify']->values[0]['text'];
        if($APTDocverify == "Out for Signature" || $APTDocverify == "Signature Received") {
            //APT Opp Field
            $APTOppItemID = $aptItem->fields['opportunity']->values[0]->item_id;
            $oppItem = PodioItem::get($APTOppItemID);
            $oppItemID = $oppItem->item_id;
            if($APTDocverify == "Out for Signature"){
                $cycleStatus = "Contract Out for Signature";
            }
            if($APTDocverify == "Signature Received"){
                $cycleStatus = "Signed Contract";
            }
        }
        else{
            $APTOppItemID = $aptItem->fields['opportunity']->values[0]->item_id;
            $oppItem = PodioItem::get($APTOppItemID);
            $oppItemID = $oppItem->item_id;
            $cycleStatus = "Queue SO Creation";
        }
    }
    if($triggerAppID == 10827874){
        $DelivOppItemID = $triggerItem->fields['opportunity']->values[0]->item_id;
        $oppItem = PodioItem::get($DelivOppItemID);
        $oppItemID = $oppItem->item_id;
        $cycleStatus = "Queue SO Creation";
    }
    if($triggerAppID == 9735424){
        $oppItem = PodioItem::get($item_id);
        $oppItemID = $oppItem->item_id;
        $cycleStatus = "Execute Deliverables";
    }

//Opportunity Values
    //Sales Cycle Status (category: 79136932) deal-stage
    $oppCycleStatusField = 'deal-stage';
    $oppCycleStatus = $oppItem->fields['deal-stage']->values[0]['text'];
    //Sales Cycle Status (calculation: 87396220) sales-cycle-status-calculation
    $oppCalcCycleStatus = $oppItem->fields['sales-cycle-status-calculation']->values;

//Automation Outline:
    //Get Opportunity Related to Trigger item
        //done Above
    //If Sales Cycle Status (category) is equal to “Deal Lost” OR Sales Cycle Status (category) is equal to “Opportunity Completed” - End Script
//    if($oppCycleStatus == "Deal Lost" || $oppCycleStatus == "Opportunity Completed"){
//        throw new Exception("Opportunity is already Completed or the Deal was Lost");
//        exit;
//    }
    //If Sales Cycle Status (calculation) is NOT equal to Sales Cycle Status (category) OR Sales Cycle Status (category) is BLANK Update the Opportunity Item by ID
    if($oppCycleStatus != $oppCalcCycleStatus || !$oppCycleStatus){
        //Set the Sales Cycle Status (category) field equal to the Sales Cycle Status (calculation) field’s value.
        PodioItem::update($oppItemID, array(
            'fields'=>array(
                'deal-stage'=>$oppCalcCycleStatus
            )
        ));
    }
//END Automation Outline



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