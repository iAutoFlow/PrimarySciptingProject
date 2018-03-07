<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/14/2016
 * Time: 4:29 PM
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

    $LeadsAppID = 2933904;
    $ClientAppID = 13940709;
    $ProjectsAppID = 3848224;
    $ClientWorkspaceInfoAppID = 13941091;
    $BillingCycleAppID = 4481866;
    $PMSAppID = 15555787;

    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");
    $day = date_format($todaysDate, "J");
    $ending = date_format($todaysDate, "t");

    if((int)$day < 15){$ending = 15;}

    $CurrentPeriod = $month." ".$ending.", ".$year;

    //Trigger Item Info.
    $CompanyName = $item->fields['company-name-in-podio']->values;
    $LeadStatus = $item->fields['status']->values[0]['text'];
    $ContactItemID = $item->fields['company-contacts']->values[0]->item_id;
    $AccountManagerItemID = $item->fields['account-manager']->values[0]->item_id;
    $FreshbooksID = $item->fields['freshbooks-id']->value;
    $HourlyRate = $item->fields['hourly-rate']->values;



    //Main function block/
    if($LeadStatus == 'ENGAGED') {

        //Filter Period Items for Current Pay Period
        $FilterPeriods = PodioItem::filter($PMSAppID, array('filters'=>array('cycle'=>(string)$CurrentPeriod)));
        $CurrentPeriodItemID = $FilterPeriods[0]->item_id;


        //Duplicate Client Check
        $FilterManagementClients = PodioItem::filter($ClientAppID, array("filters" => array('company2' => array((int)$itemID))));
        $ClientItemID = $FilterManagementClients[0]->item_id;


        //Duplicate Project Check
        $FilterProjects = PodioItem::filter($ProjectsAppID, array("filters" => array('company2' => array((int)$itemID))));
        $ProjectsItemID = $FilterProjects[0]->item_id;


        //Create New Client Item
        if (!$ClientItemID) {
            $CreateNewClientItem = PodioItem::create($ClientAppID, array(
                'fields' => array(
                    'company2' => array((int)$itemID),
                    'title' => $CompanyName,
                    'invoicing-poc' => (int)$ContactItemID,
                )));
            $ClientItemID = $CreateNewClientItem->item_id;
        }


        //Create New Project Item
        if (!$ProjectsItemID) {
            $CreateNewProject = PodioItem::create($ProjectsAppID, array(
                'fields' => array(
                    'project-name' => $CompanyName,
                    'company2' => array((int)$ClientItemID),
                    'invoicing-poc' => (int)$ContactItemID,
                    'project-manager' => (int)$AccountManagerItemID,
                    'status' => "Active",
                    'freshbooks-id'=>$FreshbooksID,
                    'hourly-rate-2'=>$HourlyRate,
                )));
            $ProjectsItemID = $CreateNewProject->item_id;
        }

        //Create Biling Cycle
        $CreateBillingCycle = PodioItem::create($BillingCycleAppID, array(
            'fields' => array(
                'project' => (int)$ProjectsItemID,
                'billing-type' => "Billable",
                'status-2' => "Active",
                'client' => array((int)$ClientItemID),
                'period' => array((int)$CurrentPeriodItemID),
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




