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

    $BudgetingAccountingSpaceID = 2337776;
    $BADashboardFieldXID = 'financial-dashboard';
    $BABrandsBudgetAppID = 12887982;
    //Brand Budgets are related to Financial Dashboard
    $BAMediaBuysAppID = 12850497;
    //Media Buys are related to Financial Dashboard
    $SalesTeamSpaceID = 2337779;
    $STDashboardFieldXID = 'financial-dashboard';
    $STPromotionsAppID = 14758750;
    //Promotions are related to Financial Dashboard
    $STTicketSalesAppID = 12822028;
    //Ticket Sales are related to Financial Dashboard
    $VendorsBrandsSpaceID = 2337781;
    $VBDashboardFieldXID = 'dashboard';
    $VBBrandsAppID = 8780211;
    //Brands are related to Financial Dashboard
    $PMJobsAppID = 9063521;
    //Jobs are related to PM-Dashboard
    $ProjectManagementSpaceID = 2337777;
    $PMDashboardFieldXID = 'dashboard';
    $PMCampaignsAppID = 8699068;
    //Campaigns are related to PM-Dashboard & Financial Dashboard
    $PMStrategiesAppID = 8708019;
    //Strategies are related to PM-Dashboard & Financial Dashboard


    $PMDashboardItemID = 390230050;
    $BAFinancialDashboardItemID = 395274127;

    if($appID == $STTicketSalesAppID || $appID == $STPromotionsAppID || $appID == $BABrandsBudgetAppID || $appID == $BAMediaBuysAppID){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'financial-dashboard'=>array((int)$BAFinancialDashboardItemID),
            ),
            array(
                'hook' => false
            )));
    }

    if($appID == $VBBrandsAppID){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'dashboard'=>array((int)$BAFinancialDashboardItemID),
            ),
            array(
                'hook' => false
            )));
    }

    if($appID == $PMJobsAppID){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'dashboard'=>array((int)$PMDashboardItemID),
            ),
            array(
                'hook' => false
            )));
    }

    if($appID == $PMCampaignsAppID || $appID == $PMStrategiesAppID){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'dashboard'=>array((int)$PMDashboardItemID,(int)$BAFinancialDashboardItemID),
            ),
            array(
                'hook' => false
            )
        ));

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