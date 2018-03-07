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

    //Triggers on the BOM Element Field changing on the Scope

///AUTOMATION START

    $scopeItem = PodioItem::get($item_id);

    $opportunityItemID = $scopeItem->fields['opportunity']->values[0]->item_id;

    $bomElementsValue = $scopeItem->fields['bomb-elements']->values;

    $bomElementsArray = array();
    foreach($bomElementsValue as $bomElement){
        $bomElementsArray[] = $bomElement->item_id;
    }

    $referencedItems = PodioItem::get_references($item_id);

    foreach($referencedItems as $referencedItem){
        if($referencedItem['app']['app_id'] == 10827874){
            $deliverablesList = $referencedItem['items'];
        }
    }

    foreach($deliverablesList as $deliverable){
        PodioItem::update($deliverable['item_id'], array('fields'=>array('bomb-elements'=>$bomElementsArray)));

        PodioComment::create('item', $item_id, array('value'=>"BOM Elements updated on related Deliverable"));
    }

    $referencedOppItems = PodioItem::get_references($opportunityItemID);

    foreach($referencedOppItems as $referencedItem){
        if($referencedItem['app']['app_id'] == 13906476){
            $dashboardItemID = $referencedItem['items'][0]['item_id'];
        }
        if($referencedItem['app']['app_id'] == 10226461){
            $scopeItems = $referencedItem['items'];
        }
    }

    $oppBomElementsArray = array();
    foreach($scopeItems as $item){
        $loopscopeItem = PodioItem::get($item['item_id']);

        $loopScopeBoms = $loopscopeItem->fields['bomb-elements']->values;
        foreach($loopScopeBoms as $loopScopeBom) {
            if(!in_array($loopScopeBom->item_id, $oppBomElementsArray)) {
                $oppBomElementsArray[] = $loopScopeBom->item_id;
            }
        }
    }
//    print_r($oppBomElementsArray);exit;
//    $projectDashboardItem = PodioItem::get($dashboardItemID);

//    $initialDashBoms = $projectDashboardItem->fields['bomb-elements']->values;

//    $initialBomArray = array();
//    foreach($initialDashBoms as $dashBom){
//        $initialBomArray[] = $dashBom->item_id;
//    }
//
//    $newBomArray = $initialBomArray;
//    foreach($oppBomElementsArray as $oppBom){
//        if(in_array($oppBom, $initialBomArray))
//
//        $newBomArray[] = $scopeBom;
//    }

    PodioItem::update($dashboardItemID, array('fields'=>array('bomb-elements'=>$oppBomElementsArray)));

    PodioComment::create('item', $item_id, array('value'=>"BOM Elements updated on related Project Dashboard"));

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