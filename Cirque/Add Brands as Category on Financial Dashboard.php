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

    $FinancialDashboardAppID = 15311529;
    $DashboardBrandFieldID = 117993121;

    $BrandShowName = $item->fields['title']->values;

    $BrandCategoryArray = array();
    $FinancialBrandCategory = PodioAppField::get($FinancialDashboardAppID, $DashboardBrandFieldID);
    foreach($FinancialBrandCategory->config['settings']['options'] as $option){
        $CategoryOption = $option;
        array_push($BrandCategoryArray, $CategoryOption);
    }

//    array_push($BrandCategoryArray, $BrandShowName);

    $BrandCategoryArray[] = array('text'=>$BrandShowName,'color'=>'DCEBD8');



    $updateCategoryField = PodioAppField::update($FinancialDashboardAppID, $DashboardBrandFieldID, array(
        'label'=>"Brand",
        'delta'=>4,
        'required' => true,
        'settings'=> array(
            'options' => $BrandCategoryArray,
            'display' => 'dropdown',
        )));




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