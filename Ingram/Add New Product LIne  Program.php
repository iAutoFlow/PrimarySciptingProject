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
    $itemID = $requestParams['item_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $DatabaseSpaceID = 2732169;
    $DBProductLinesAppID = 10250248;
    $WBSAppID = 25812261;

    $SalesSpaceID = 2732154;
    $SProductLinesAppID = 11259081;
    $SProgramsAppID = 12259082;


    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");

//WBS SECTION
    if($appID == $WBSAppID){
        $NewWBSItem = PodioItem::get($itemID);
        $WBSProgramManagerItemID = $NewWBSItem->fields['program-manager']->values[0]->item_id;

        $CreateNewProgram = PodioItem::create($SProgramsAppID, array(
            'fields'=>array(
                'wbs'=>array((int)$itemID),
                'program-manager-2'=>array((int)$WBSProgramManagerItemID),
                'status-year'=>$year,
            )
        ));
    }


//IF TRIGGER APP IS DATABASE PRODUCT LINES
    if($appID == $DBProductLinesAppID){
        $ProductLineItem = PodioItem::get($itemID);
        $ProductStatus = $ProductLineItem->fields['status']->values[0]['text'];
        $ProductMasterWBSItemID = $ProductLineItem->fields['master-wbs']->values[0]->item_id;

        if($ProductStatus == "Active"){
            $filterSalesProductLines = PodioItem::filter($SProductLinesAppID, array('filters'=>array('product'=>array((int)$itemID))));
            $SalesProductItemID = $filterSalesProductLines[0]->item_id;

            $filterSalesPrograms = PodioItem::filter($SProgramsAppID, array('filters'=>array('wbs-2'=>array((int)$ProductMasterWBSItemID))));
            $SalesProgramItemID = $filterSalesPrograms[0]->item_id;

            if(!$SalesProductItemID){
                $CreateSalesProductLineItem = PodioItem::create($SProductLinesAppID, array(
                    'fields'=>array(
                        'product' => array((int)$itemID),
                        'wbs-2' => array((int)$ProductMasterWBSItemID),
                        'program' => array((int)$SalesProgramItemID),
                    )
                ));
            }

            else{
                $UpdateSalesProductLineItem = PodioItem::update($SalesProductItemID, array(
                    'fields'=>array(
                        'product' => array((int)$itemID),
                        'wbs-2' => array((int)$ProductMasterWBSItemID),
                        'program' => array((int)$SalesProgramItemID),
                    )
                ));
            }
        }

        elseif($ProductStatus !== "Active"){
            $filterSalesProductLines = PodioItem::filter($SProductLinesAppID, array('filters'=>array('product'=>array((int)$itemID))));
            $SalesProductItemID = $filterSalesProductLines[0]->item_id;
            $DeleteInactiveProduct = PodioItem::delete($SalesProductItemID);

        }









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