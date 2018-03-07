<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */

date_default_timezone_set('America/Denver');
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




    //Filter for "Previous" Forcasting Items, and update them to "Archived"
    $FilterPreviousForcastingItems = PodioItem::filter_by_view(15856001, 30134652);

    //For Each Previous Forcasting Item
    foreach($FilterPreviousForcastingItems as $previousforcasting){
        $PreviousForcastItemID = $previousforcasting->item_id;

        //Update Previous Forcasting Items to "Archived"
        $UpdatePreviousForcastings = PodioItem::update($PreviousForcastItemID, array(
            'fields'=>array(
                'status'=>"Archived",
            )
        ));
    }


    //Filter for "Active" Forcasting Items, and update them to "Previous"
    $FilterActiveForcastingItems = PodioItem::filter_by_view(15856001, 30134589);

    //For Each Active Forcasting Item
    foreach($FilterActiveForcastingItems as $forcasting){
        $ActiveForcastItemID = $forcasting->item_id;

        //Update Active Forcasting Item to "Previous"
        $UpdatePreviousForcastings = PodioItem::update($ActiveForcastItemID, array(
            'fields'=>array(
                'status'=>"Previous",
            )
        ));
    }






    //Set Current Date, Break out by Day, Month, Year
    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $year = date_format($todaysDate, "Y");

    //Set Week Number
    $WeekNumber = "Week ".(date("W") - date("W", strtotime(date("Y-m-01", time()))) + 1);



    //Filter Reps by Active Saved View
    $FilterActiveReps = PodioItem::filter_by_view(15855999, 30134458);

    //For Each Active Sales Rep Item
    foreach($FilterActiveReps as $rep){
        $RepItemID = $rep->item_id;

        //Filter Forcasting Items by RepItem ID and Status of "Previous"
        $Filter = PodioItem::filter(15856001, array('filters'=>array('sales-rep'=>(int)$RepItemID),array('status'=>array('value'=>'Previous'))));
        $ForcastItemID = $Filter[0]->item_id;

        //Set Default Improvement Ideas Value
        $ImprovementGoals = "No improvement plans were made last week. Please, identify how you can improve your score THIS week.";

        //Get Previous Forcast Item
        $PreviousForcastItem = PodioItem::get($ForcastItemID);
        $PreviousImprovement = $PreviousForcastItem->fields['how-can-you-improve-your-score-next-week']->values;
        if($PreviousImprovement){$ImprovementGoals = $PreviousImprovement;}


        //Get Rep Item by "$RepItemID" for Name
        $RepItem = PodioItem::get($RepItemID);
        $RepName = $RepItem->fields['title']->values;


        //Set Forcast Budget Values Specific to each Rep
        //Default if Rep has not been Established
        $CloverBudget = 40;
        $TouchesBudget = 40;
        $Appointments = 6;
        $NumofBidsSubmitted = 5;
        $RepairProposed = 100000;
        $PMAProposed = 50000;
        $RepairSold = 10000;
        $PMASold = 5000;

        //Thomas Budge
        if($RepName == "Thomas Mudge"){
            $CloverBudget = 40;
            $TouchesBudget = 40;
            $Appointments = 6;
            $NumofBidsSubmitted = 5;
            $RepairProposed = 27310;
            $PMAProposed = 10924;
            $RepairSold = 6828;
            $PMASold = 2731;
        }

        //Jerry Pace
        if($RepName == "Jerry Pace"){
            $CloverBudget = 40;
            $TouchesBudget = 40;
            $Appointments = 6;
            $NumofBidsSubmitted = 5;
            $RepairProposed = 170688;
            $PMAProposed = 68275;
            $RepairSold = 13655;
            $PMASold = 5462;
        }




        //Create Forcasting Item
        $CreateForcasting = PodioItem::create(15856001, array(
            'fields'=>array(
                'sales-rep'=>array((int)$RepItemID),
                'status'=>"Active",
                'year'=> $year,
                'month'=> $month,
                'week'=> $WeekNumber,
                'how-can-you-improve-your-score-this-week'=>$ImprovementGoals,
                'clover-budget-2' => $CloverBudget,
                'touches-budget-2'=>$TouchesBudget,
                'appointment-budget-2'=>$Appointments,
                'of-bids-budget'=>$NumofBidsSubmitted,
                'of-service-requests-budget-2'=>$Appointments,
                'pma-bid-budget'=>$PMAProposed,
                'pma-sold-budget-2'=>$PMASold,
                'repair-bid-budget'=>$RepairProposed,
                'repair-sold-budget-2'=>$RepairSold,
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



