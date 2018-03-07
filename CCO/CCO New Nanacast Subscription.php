<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
 */

//O-AUTH
//
//class PodioSessionManager {
//    private static $connection_id = 3;
//    private static $connection;
//
//    public function __construct() {
//    }
//
//    public static function getConnection() {
//        if (!self::$connection) {
//            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
//        }
//        return self::$connection;
//    }
//
//    public static function getClientId () {
//        return self::getConnection()->connectionService->config['client_id'];
//    }
//
//    public static function getClientSecret () {
//        return self::getConnection()->connectionService->config['client_secret'];
//    }
//
//    public function get($authtype = null){
//        $connection = self::getConnection();
//        return new PodioOAuth(
//            $connection->access_token,
//            $connection->refresh_token
//        );
//    }
//    public function set($oauth, $auth_type = null){
//        $connection = self::getConnection();
//        $connection->access_token = $oauth->access_token;
//        $connection->save();
//        self::$connection = $connection;
//    }
//
//
//}

try {
//    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
//        "session_manager" => "PodioSessionManager"
//
//    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    $Mode = $requestParams['mode'] ;                                             //add|modify|payment|product|reactivate|decline|suspend|delete
    $ClientID = $requestParams['id'] ;                                           //This is the client's ID in our database.
    $ClientUniqueAccessCode = $requestParams['u_access_code'] ;                  //This is the client's unique 12-character code that is used to make up their RSS Feed or Podcast URL(Ex: http://nanacast.com/ac/123456789).
    $ProductID = $requestParams['u_list_id'] ;                                   //Your Product/Podcast/RSS Feed/Membership ID
    $ProductName = $requestParams['item_name'];                                  //Your Product/Podcast/RSS Feed/Membership Name
    $UnsubscribeReason = $requestParams['u_last_unsubscribe_reason '];           //blank|billing_failed|incoming_api_unsubscribe|refund_and_unsubscribe |client_cancelled|admin_unsubscribed|changed_membership_within_group
    $JoinDate = ['u_date_added '] ;                                              //This is the date/time that the client joined.(Ex: 2009-10-29 23:45:25)
    $EffectiveStartDate = ['u_start_date '] ;                                    //This is the effective start date used for the content delievery. (The date that determines when "Day 0, Day 1, Day 2" content should be shown.)
    $LastContact = ['u_last_contact '] ;                                         //This is the date/time that the client has last made contact with the server, either by logging into a membership and viewing the episodes or by hitting their Podcast/RSS Feed.
    $AccountID = ['account_id '] ;                                               //This is the numeric internal account ID of the client. This is different from the client id field above. If a client has purchased multiple memberships in the system, they will have multiple client IDs but only one account_id. The account_id ties all their memberships together into one account. The account_id is also the client's personal affiliate ID that they can use when promoting your products.
    $Quantity =  ['u_quantity '] ;                                               //This is the quantity they selected. (Defaults to 1 if quantity was not an option)
    $FistPaymentAmmount = ['u_first_price '] ;                                   //This is the amount they paid at checkout
    $RecurringPaymentAmmount = ['u_recurring_price '] ;                          //This is the amount they will be charged on a recurring basis
    $BillingInterval = ['u_billing_interval '] ;                                 //This is the number of days in their billing cycle.
    $InstallmentsNeeded = ['u_installments_needed '] ;                           //This is number of times they will be billed on a recurring basis. This will be 0 for unlimited installments.
    $InstallmentsCollected = ['u_installments_collected '] ;                     //This is number of installments collected so far. Trial periods do not count towards installments.













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