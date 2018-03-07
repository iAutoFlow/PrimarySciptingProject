<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/1/2016
 * Time: 11:26 AM
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

    $opportunity = PodioItem::get($itemID);
    $appName = $opportunity->app->name;
    $appID = $newEmailItem->app->app_id;

    $SalesSpaceID = 2732154;
    $SalesScopeAppID = 10226461;
    $SalesAPTAppID = 10702577;
    $SalesSubScopeAppID = 10411647;

    $SegmentationProfileSpaceID = 2732148;
    $SEGPROClientsAppID = 9735161;

    $AccountingFinanceSpaceID = 2732170;
    $AFActualsAppID = 12099103;
    $AFClientFinancialsAppID = 10411991;

    $DatabasesSpaceID = 2732169 ;
    $DBProductLinesAppID = 10250248;
    $DBWBSAppID = 10400257;

    $FixItValue = $opportunity->fields['fix-it']->values[0]['text'];
    $RelatedClientID = $opportunity->fields['client']->values[0]->item_id;
    $ActualVariance = $opportunity->fields['scopesub-actual-variance']->values;
    $SaleCycleStatus = $opportunity->fields['sales-cycle-status-calculation']->values;
    $NumOfSubActuals = $opportunity->fields['of-sub-actuals']->values;
    $APTStatus = $opportunity->fields['docverify-status-from-apt']->values;

//    //CHECK APT STATUS
//    if($APTStatus == "Out for Signature" || $APTStatus == "Deal Lost"){
//        $UpdateTriggerItem = PodioItem::update($itemID, array(
//                'fields' => array(
//                    'fix-it' => null
//                ),
//                array(
//                    'hook' => false
//                )
//            )
//        );
//
//        $AddComment = PodioComment::create('item', $itemID, array(
//                'value' => "Contract not yet signed."
//            )
//        );
//
//
//    }


    //Fix Deliverables
    if($FixItValue == 'Fix Deliverables'){

        if($APTStatus == "Out for Signature" || $APTStatus == "Deal Lost"){
            $UpdateTriggerItem = PodioItem::update($itemID, array(
                    'fields' => array(
                        'fix-it' => null,

                    ),
                    array(
                        'hook' => false
                    )
                )
            );

        }

    }

    //Fix Sub-Actuals
    elseif($FixItValue == 'Fix Actuals'){



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