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



try {

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

//Filter Sub-scopes, Get Parent Scope, get parent Opp, Get APT item, if Contract is Signed, then don't delete anything at all.

//This automation triggers when [4 - Sales (workspace: 2732154) → Scope (appID: 10226461) → Fix It! (category: 105432249 | fix-it) is set to “Fix Sub-Scopes.”  This automation ensures the correct number of related Sub.Scope (appID: 10411647) items as outlined below:

//TRIGGER: [4 - Sales (workspace: 2732154) → Scope (appID: 10226461) → Fix It! (category: 105432249 | fix-it)] is set to “Fix Sub-Scopes”

//Automation Outline:
//Get Scope ID from Trigger Item [4 - Sales (workspace: 2732154) → Scope (appID: 10226461)]
    $scopeItem = PodioItem::get((int)$item_id);

    $scopeQuantity = $scopeItem->fields['quantity']->values;

    $scopeStartDate = $scopeItem->fields['date-of-execution']->start;

    $triggerFix = $scopeItem->fields['fix-it']->values[0]['text'];

    $startDateFormatted = $scopeStartDate->format('Y-m-d H:i:s');

    $scopePLItemID = $scopeItem->fields['description']->values[0]->item_id;

    $scopeOpportunityItemID = $scopeItem->fields['opportunity']->values[0]->item_id;

    $subScopeTrigger = $scopeItem->fields['breakout']->values;

    $aptFilter = PodioItem::filter(10702577, array('filters' => array('opportunity' => array((int)$scopeOpportunityItemID))));

    $aptItemID = $aptFilter[0]->item_id;

    if ($aptItemID) {

        $aptItem = PodioItem::get($aptItemID);

        $docverifyStatus = $aptItem->fields['docverify']->values[0]['text'];

    }




if($subScopeTrigger == "Yes" && $triggerFix == "Fix Sub-Scopes") {
//Filter Sub.Scope items [Sales (workspace: 2732154) → Sub.Scope (appID: 10411647)] for those related to Scope ID from Trigger item
    $subscopesFilter = PodioItem::filter(10411647, array('filters' => array('scope' => array((int)$item_id))));
//Check number of related Sub.Scope items [Sales (workspace: 2732154) → Sub.Scope (appID: 10411647)] against [4 - Sales (workspace: 2732154) → Scope (appID: 10226461) → Quantity (number: 79323725 | quantity)]
    //If difference between number of Sub.Scope items (see above) and the number in Quantity field (see above) is 0, STOP.
    if (sizeof($subscopesFilter) > $scopeQuantity) {
        if ($docverifyStatus == "Signature Received" || $docverifyStatus == "Resigned") {
            PodioComment::create('item', (int)$item_id, array('value' => "Contract has already been signed.\nDelete extra Sub Scopes Manually using the 'Delete Sub Scope and All Related Items' field on the Sub Scope item(s) you wish to delete."));
            throw new Exception('Cannot automatically delete Sub Scopes, the contract has already been signed.');
            exit;
        } else {
            $deleteNum = sizeof($subscopesFilter) - $scopeQuantity;

            for ($i = 0; $i < $deleteNum; $i++) {
                $deleteItemID = $subscopesFilter[$i]->item_id;

                PodioItem::delete($deleteItemID);
            }
        }
    } elseif (sizeof($subscopesFilter) < $scopeQuantity) {
        $addNum = $scopeQuantity - sizeof($subscopesFilter);

        for ($i = 0; $i < $addNum; $i++) {
            PodioItem::create(10411647, array(
                'fields' => array(
                    'scope' => (int)$item_id,
                    'opportunity' => $scopeOpportunityItemID,
                    'product-line' => $scopePLItemID,
                    'date-of-execution' => $startDateFormatted
                )
            ));
        }
        //Create New Sub.Scope items equal to the number of Breakouts needed populating the fields below with the following values:
        //Scope (app: 80535763 |scope) with Trigger Scope Item item_id
        //Opportunity (app: 80535764 | opportunity) with Opportunity (app: 80540014 | opportunity) by Opportunity Item ID
        //(app: 80535766 | product-line) with Product Line (app: 79323724 | description) by Product Line ID
        //Start Date (date: 80535765 | date-of-execution) with Start Date (date: 79323728 | date of execution)
    }
}
//Set [4 - Sales (workspace: 2732154) → Scope (appID: 10226461) → Fix It! (category: 105432249 | fix-it)] to “null”
    PodioItem::update((int)$item_id, array(
        'fields'=>array(
            'fix-it'=>null
        )
    ));


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