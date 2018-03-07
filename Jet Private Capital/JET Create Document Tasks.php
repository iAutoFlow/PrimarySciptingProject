<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/14/2016
 * Time: 11:00 AM
 */


date_default_timezone_set('America/Denver');

//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
class PodioSessionManager {
    private static $connection_id = 191;
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

    //Get Values from Trigger Item///////////////////////
    $AdminItem = PodioItem::get($itemID);
    $PropertyItemID = $AdminItem->fields['property']->values[0]->item_id;
    $Trigger = $AdminItem->fields['trigger']->values[0]['text'];
    $Documents = $AdminItem->fields['documents']->values;
    $AssignedTo = $AdminItem->fields['assigned-to']->values;
    $DueDate = $AdminItem->fields['due-date']->start;

    //If Values are Blank, Comment and End
    if(!$PropertyItemID || !$Documents || !$AssignedTo || !$DueDate){
        $ErrorComment = PodioComment::create('item', $itemID, array('value'=>"Please fill in all require values."));
        $Update = PodioItem::update($itemID, array('fields'=>array('trigger'=>"Error")),array('hook'=>false));
        $Trigger = "Error";
    }



    if($Trigger == "Run") {

        //Formate Date///
        $TodaysDateFormatted = new DateTime((string)$DueDate, new DateTimeZone('America/Denver'));
        $FomatDate = $TodaysDateFormatted->format('Y-m-d');

        //Get Assignee(s) User ID
        $AssigneeArray = array();
        foreach ($AssignedTo as $user) {
            $AssigneeUserID = $user->user_id;
            array_push($AssigneeArray, $AssigneeUserID);
        }


        //For Each document Selected in Trigger Item. Create Task
        foreach ($Documents as $doc) {
            $DocText = $doc['text'];


            //Set Text Description for Task
            if($DocText == "EARNEST MONEY RECEIPT"){$Note = "Please turn in a Receipt of Deposit from the party holding the EM funds as required per WA State Law.";}
            if($DocText == "TRANSACTION MANAGEMENT REPORT"){$Note = " Your TMR was either incomplete or not provided. Please turn in to Skyline or commissions may be delayed.";}
            if($DocText == "FORM 17 WVR (SELLER)"){$Note = "Pg 1 must have Seller/Prop address; pgs 1-5 must be initialed/dated by Buyer(s) & pg 6 needs 3rd option signed by Buyer(s).";}
            if($DocText == "FORM 22J"){$Note = "Built in 1942. Buyer must remove their initials from either waiving their right to a risk assessment or accepting a risk assessment.";}
            if($DocText == "FORM 42"){$Note = "WA Law req's Brokers to provide Agency Pamphlets to clients. This form is needed on NON NWMLS P&S.";}
            if($DocText == "SKY-LNTB"){$Note = "Skyline Notice to Buyer of Commonly Required Legal Forms & Notifications Required. Needed for Form 17.";}
            if($DocText == "COMMISSION DISBURSEMENT"){$Note = "Please turn in a completed CD Form (NWMLS Form 40) or commission payment may be delayed.";}
            if($DocText == "SELLING OFFICE TO OPEN ESCROW"){$Note = "You did not indicate on your TMR that you sent your P&S and CD to escrow. Please submit paperwork accordingly.";}
            if($DocText == "FORM 89"){$Note = "Receipt for Earnest Money (To be used when Selling Broker receives earnest money, prior to delivery to escrow/third party).";}



            //Set Task Item Attributes
            $AttributesArray = array(
                'text' => $DocText,
                'responsible' => $AssigneeArray,
                'description' => (string)$Note,
                'due_date'=>$FomatDate,
            );


            //Create and assign Task
            $CreateTask = PodioTask::create_for('item', $PropertyItemID, $AttributesArray);


        }


        //Update Trigger Item//////////////
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields'=>array(
                'property'=> [],
                'trigger'=> "Created",
                'documents'=> [],
                'assigned-to'=> [],
                'due-date'=> [],
            )
        ),
            array('hook'=>false)
        );


    }



    sleep(10);

    //Reset Trigger Value
    $UpdateTrigger = PodioItem::update($itemID, array(
        'fields'=>array(
            'trigger'=>"..."
        )
    ),
        array('hook'=>false)
    );

















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