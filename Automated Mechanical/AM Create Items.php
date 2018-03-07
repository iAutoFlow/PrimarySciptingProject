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

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];


    //Get Trigger Item
    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    //Format Current Date/Time
    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));
    $TriggerValue = "Created";





    //If Triggered from the Customers / Leads App
    if($appID == 15856024) {

        //Trigger Values
        $AccountManagerItemID = $item->fields['account-manager']->values[0]->item_id;
        $PrimaryPOC = $item->fields['primary-poc']->values[0]->item_id;
        $CreateItems = $item->fields['create-items']->values[0]['text'];

        //Check Trigger Value
        if ($CreateItems == "..." || $CreateItems == "Created") {
            exit;
        }


        //CREATE INTERATION
        if ($CreateItems == 'Interaction') {
            PodioItem::create(15856041, array(
                    'fields' => array(
                        'lead' => array((int)$itemID),
                        'rep' => array((int)$AccountManagerItemID),
                        'date' => array('start' => $todaysDate),
                    )
                )
            );
        }

        //CREATE PROPOSAL
        if ($CreateItems == 'Proposal') {
            PodioItem::create(15856042, array(
                    'fields' => array(
                        'customer' => array((int)$itemID),
                        'sales-rep' => array((int)$AccountManagerItemID),
                        'proposed-start-date' => array('start' => $todaysDate),
                        'approver' => 181646955,
                        'billing-code-2'=>422961798,
                        'tax-rate' => 7,
                    )
                )
            );
        }


        //CREATE SERVICE REQUEST
        if ($CreateItems == 'Service Request') {
            PodioItem::create(15856045, array(
                    'fields' => array(
                        'client' => array((int)$itemID),
                        'date-requested' => array('start' => $todaysDate),
                        'sales-rep-submitting-the-call' => array((int)$AccountManagerItemID),
                        'primay-poc' =>(int)$PrimaryPOC,
                        'source' => "Customer Called (created by employee)"
                    )
                )
            );
        }



        //Update Trigger Item to Completed
        $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'create-items' => $TriggerValue
                ),
                array(
                    'hook' => false
                )
            )
        );


        //Sleep 15 Seconds
        sleep(15);


        //Reset Trigger Value
        $TriggerValue = "...";
        $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'create-items' => $TriggerValue
                ),
                array(
                    'hook' => false
                )
            )
        );
    }

    elseif($appID == 15856042){

        //Trigger Values
        $CreateItems = $item->fields['create-line-item']->values[0]['text'];
        $TriggerValue = "Created";


        //Check Trigger Value
        if ($CreateItems == "..." || $CreateItems == "Created") {
            exit;
        }

        //Field Material Bid Sheet
        if ($CreateItems == 'Field Materials Bid List' || $CreateItems == 'Bid Calculation') {
            PodioItem::create(16557266, array(
                    'fields' => array(
                        'proposal' => array((int)$itemID),
                        'date' => array('start' => $todaysDate),
                    )
                )
            );
        }

        //Comp Sheet
        if ($CreateItems == 'Comp Sheet') {
            PodioItem::create(16566958, array(
                    'fields' => array(
                        'field-material-bid-list' => array((int)$itemID),
                        'date-created' => array('start' => $todaysDate),
                    )
                )
            );
        }

        //Contract
        if ($CreateItems == 'Contract') {
            //Create Fields Array
            $ContractFieldsArray = array(
                'fields' => array(
                    'proposal' => array((int)$itemID),
                    'create' => "Proposal",
                )
            );

            //Get Customer Item ID and Add to Fields Array
            $ClientItemID = $item->fields['customer']->values[0]->item_id;
            if ($ClientItemID) {
                $ContractFieldsArray['fields']['customer'] = array((int)$ClientItemID);
            }

            //Get Related Bid Sheet Item ID and Add to Fields Array
            $RelatedBidSheet = PodioItem::get_references($itemID);
            foreach ($RelatedBidSheet as $bidsheet) {
                if ($bidsheet['app']['app_id'] == 16557266) {
                    $BidSheetItemID = $bidsheet['items'][0]['item_id'];
                    $ContractFieldsArray['fields']['field-material-bid-list'] = array((int)$BidSheetItemID);
                }
            }

            //Create Contract Item
            $CreateContract = PodioItem::create(15856047, $ContractFieldsArray);
        }


        //Update Trigger Item to Completed
        $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'create-line-item' => $TriggerValue
                ),
                array(
                    'hook' => false
                )
            )
        );


        //Sleep 15 Seconds
        sleep(15);


        //Reset Trigger Value
        $TriggerValue = "...";
        $UpdateTriggerItem = PodioItem::update($itemID, array(
                'fields' => array(
                    'create-line-item' => $TriggerValue
                ),
                array(
                    'hook' => false
                )
            )
        );
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