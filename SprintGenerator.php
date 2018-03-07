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

///AUTOMATION START

    $sprintCycleExID = 'sprint-period';
    $statusExID = 'status';
    $standupExID = 'daily-standup-meeting';

    $controllers = PodioItem::filter(16619718);

    foreach($controllers as $controller) {

        if($controller->fields['space']->values[0]['text'] == "4.2 - Projects"){
            $sprint_app_id = 16619499;
        }

        if($controller->fields['space']->values[0]['text'] == "5.6 - Product Development"){
            $sprint_app_id = 16276213;
            $sprintCycleExID = 'sprint-cycle-2';
            $standupExID = 'daily-standup';
        }

        $sprintLength = $controller->fields['sprint-length-days']->values;

        $standupStart = $controller->fields['daily-standup-meeting']->start;

        $standupEnd = $controller->fields['daily-standup-meeting']->end;

        $currentDay = date("l");

        if($controller->fields['sprint-start-day']->values[0]['text'] == $currentDay) {

            $currentDate = date("Y-m-d");

            $previousDay = date('Y-m-d', strtotime($currentDate . " - 1 day"));

            $previousDay .= " 00:00:00";

            $endDate = date('Y-m-d', strtotime($currentDate . " + ".str_replace(".0000", "", (string)$sprintLength)." days"));

            $existingSprintsFilter = PodioItem::filter($sprint_app_id, array('filters'=>array($statusExID=>2)));

            foreach($existingSprintsFilter as $existingSprint){

                $existingItem = PodioItem::get($existingSprint->item_id);

                $existingEndDate = $existingItem->fields[$sprintCycleExID]->end;

                if($existingEndDate->format('Y-m-d H:i:s') == $previousDay){

                    PodioItem::update($existingSprint->item_id, array('fields'=>array($statusExID=>"Former")));

                }

            }

            $currentDate .= " 00:00:00";
            $endDate .= " 00:00:00";


            PodioItem::create($sprint_app_id, array(
                'fields' =>
                    array(
                        $sprintCycleExID => array(
                            'start' => $currentDate,
                            'end' => $endDate,
                        ),
                        $statusExID => "Active",
                        $standupExID => array(
                            'start' => $standupStart->format('Y-m-d H:i:s'),
                            'end' => $standupEnd->format('Y-m-d H:i:s'),
                        )
                    )
                )
            );
        }

    }

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