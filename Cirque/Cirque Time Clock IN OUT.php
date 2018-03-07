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

    $ProjectManagementSpaceID = 2337777;
    $PMTimeClockAppID = 8699095;
    $TimeClockActionFieldXID = 'action';
    $TimeClockInTimeFieldXID = 'in-time';
    $TimeClockOutTimeFieldXID = 'out-time';
    $TimeClockDeliverableFieldXID = 'deliverable';
    $TimeClockEmployeeFieldXID = 'employee';

    $PMJobsAppID = 9063521;
    $JobsPunchINOUTFieldXID = 'punch-inout';
    $JobsIDFieldXID = 'id';
    $JobsAssignedToFieldXID = 'assigned-to';

    $todaysDate = date_create("LLL");
    $TodaysDateFormatted = new DateTime((string)$todaysDate, new DateTimeZone('America/Denver'));


    $AssignedEmployeesArray = array();
    $AssignedTo = $item->fields[$JobsAssignedToFieldXID]->values;
    foreach($AssignedTo as $contact) {
        //$EmployeeContact = PodioContact::get_for_user($contact->user_id);
        $AssignedUserProfileID = $contact->profile_id;
        array_push($AssignedEmployeesArray, $AssignedUserProfileID);
    }

    $JobCurrentPunchID = $item->fields[$JobsIDFieldXID]->values;
    $JobActionValue = $item->fields[$JobsPunchINOUTFieldXID]->values[0]['text'];


    if($JobActionValue == "Punch In"){
        $CreateTimeClockItem = PodioItem::create($PMTimeClockAppID, array(
            'fields' => array(
                $TimeClockEmployeeFieldXID => $AssignedEmployeesArray,
                $TimeClockDeliverableFieldXID => array((int)$itemID),
                $TimeClockInTimeFieldXID => array('start' => $TodaysDateFormatted->format('Y-m-d H:i:s')),
                $TimeClockActionFieldXID => $JobActionValue,
            ),
            array(
                'hook' => true
            )
        ));

        $NewTimeClockItemID = $CreateTimeClockItem->item_id;
        $UpdateTrggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                $JobsPunchINOUTFieldXID => 'Completed',
                $JobsIDFieldXID => (string)$NewTimeClockItemID,
            ),
            array(
                'hook' => false
            )
        ));

        exit;
    }

    if($JobActionValue == "Punch Out"){
        $UpdateCurrentPunch = PodioItem::update($JobCurrentPunchID, array(
            'fields' => array(
                $TimeClockOutTimeFieldXID => array('start' => $TodaysDateFormatted->format('Y-m-d H:i:s')),
                $TimeClockActionFieldXID => $JobActionValue,
            ),
            array(
                'hook' => true
            )
        ));

        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                $JobsPunchINOUTFieldXID => "Completed",
                $JobsIDFieldXID => [],
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