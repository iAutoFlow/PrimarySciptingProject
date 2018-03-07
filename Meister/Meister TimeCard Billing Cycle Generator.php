<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/28/2016
 * Time: 2:23 PM
 */

//<?php

class PodioSessionManager {
    private static $connection_id = 76;
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




try {


    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));


    $requestParams = $event['request']['parameters'];
    $automation = $requestParams['automation'];
    $current_date = date("Y-m-d");



    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");




    //Filter for Previous Timecards - Change to Archived
    $previousTimecards = PodioItem::filter_by_view(16154196, 29675274);

    foreach ($previousTimecards as $timecard) {

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields' => array(
                'status' => 2,
                'audit-status' => 2
            )
        ));

    }

    //Filter for Current Timecards - Change to Previous
    $currentTimecards = PodioItem::filter_by_view(16154196, 29675284);

    foreach ($currentTimecards as $timecard) {

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields' => array(
                'status' => 3,
                'audit-status' => 4
            )
        ));

    }

    //Filter for Active Employees
    $activeEmployees = PodioItem::filter_by_view(15595671, 29675334, $attributes = array());

    foreach ($activeEmployees as $employeeItem) {

        $employeeItemID = $employeeItem->item_id;

        $generateTimeCard = PodioItem::create(16154196, $attributes = array(
            'fields' => array(
                'status' => 1,
                'audit-status' => 1,
                'month' => array('value' => $month),
                'year' => array('value' => $year),
                'employee' => array(
                    'value' => (int)$employeeItemID
                ),
                'dashboard'=> array(
                    'value'=>(int)'411301962'
                )
            )
        ),

            $options = array());
    }

    //Filter for Previous Billing Cycle - Change to Archived
    $previousCycles = PodioItem::filter_by_view(16223395, 29774533);

    foreach ($previousCycles as $cycle) {

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields' => array(
                'status' => 2
            )
        ));
    }

    //Filter for Active Billing Cycle - Change to Previous
    $previousCycles = PodioItem::filter_by_view(16223395, 29774555);

    foreach ($previousCycles as $cycle) {

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields' => array(
                'status' => 3
            )
        ));
    }

    //Filter for Active Projects
    $activeProjects = PodioItem::filter_by_view(15595688, 29675468, $attributes = array());

    foreach ($activeProjects as $projectItem) {

        $projectItemID = $projectItem->item_id;
        $projectClientID = $projectItem->fields['client']->values[0]->item_id;


        $generateBillingCycle = PodioItem::create(16223395, $attributes = array(
            'fields' => array(
                'status' => 1,
                'year' => array('value' => $year),
                'month' => array('value' => $month),
                'project' => array(
                    'value' => (int)$projectItemID
                ),
                'client-2' => array(
                    'value' => (int)$projectClientID
                ),
                'dashboard'=> array(
                    'value'=>(int)'411301962'
                )
            )
        ),
            $options = array());
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