<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
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

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;


    // $DashboardArray = array();


    //Proposals
    if($appID == 15856042) {
        $DashboardArray = array();
        $SalesRepItemID = $item->fields['sales-rep']->values[0]->item_id;

        if ($SalesRepItemID) {

            $FilterForcasting = PodioItem::filter_by_view(15856001, 30134589);
            foreach ($FilterForcasting as $forcast) {
                $ForcastItemID = $forcast->item_id;
                $GetForcastItem = PodioItem::get($ForcastItemID);
                $ForcastSalesRep = $GetForcastItem->fields['sales-rep']->values[0]->item_id;
                if ((int)$ForcastSalesRep == (int)$SalesRepItemID) {
                    array_push($DashboardArray, (int)$ForcastItemID);
                    array_push($DashboardArray, 422959675);
                }
            }

            $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'dashboards' => $DashboardArray,
                )
            ));
        }
        else{
            $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'dashboards' => 422959675,
                )
            ));
        }
    }

    //Interactions
    if($appID == 15856041) {
        $DashboardArray = array();
        $SalesRepItemID = $item->fields['rep']->values[0]->item_id;

        if ($SalesRepItemID) {

            $FilterForcasting = PodioItem::filter_by_view(15856001, 30134589);
            foreach ($FilterForcasting as $forcast) {
                $ForcastItemID = $forcast->item_id;
                $GetForcastItem = PodioItem::get($ForcastItemID);
                $ForcastSalesRep = $GetForcastItem->fields['sales-rep']->values[0]->item_id;
                if ((int)$ForcastSalesRep == (int)$SalesRepItemID) {
                    array_push($DashboardArray, (int)$ForcastItemID);
                    array_push($DashboardArray, 422959675);
                }
            }

            $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'dashboards' => $DashboardArray,
                )
            ));
        }

        else{
            $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'dashboards' => 422959675,
            )
        ));
        }
    }





    //Service Requests
    if($appID == 15856045) {
        $DashboardArray = array();
        $SalesRepItemID = $item->fields['sales-rep-submitting-the-call']->values[0]->item_id;

        if ($SalesRepItemID) {

        $FilterForcasting = PodioItem::filter_by_view(15856001, 30134589);
        foreach ($FilterForcasting as $forcast) {
            $ForcastItemID = $forcast->item_id;
            $GetForcastItem = PodioItem::get($ForcastItemID);
            $ForcastSalesRep = $GetForcastItem->fields['sales-rep']->values[0]->item_id;
            if ((int)$ForcastSalesRep == (int)$SalesRepItemID) {
                array_push($DashboardArray, (int)$ForcastItemID);
                array_push($DashboardArray, 422959675);
            }
        }

        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'dashboards' => $DashboardArray,
            )
        ));
    }

    else{
            $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'dashboards' => 422959675,
                )
            ));
        }
    }


    //Leads
    if($appID == 15856024){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'sales-dashboard'=>422959675,
            )
        ));
    }

    //Forcasting
    if($appID == 15856001){
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'dashboards'=>464421741,
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

