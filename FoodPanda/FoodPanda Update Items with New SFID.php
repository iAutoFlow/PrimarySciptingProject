<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/16/2016
 * Time: 2:50 PM
 */


date_default_timezone_set('America/Denver');

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
    $spaceID = $requestParams['space_id'];

    $i = 0;
    $offset = 0;

    do {
        $offset = $i * 500;
        $ContactItems = PodioItem::filter(17330422, array('limit' => 500, 'offset' => $offset));
        $count = count($ContactItems);
        foreach ($ContactItems as $contact) {
            $OrigContactID = "";
            $ContactName = "";
            $AccountRefItemID = "";
            $RefAppName = "";

            $ContactFields = array('fields' => array());
            $ContactItemID = $contact->item_id;
            $AccountID = $contact->fields['vendor-item']->values[0]->item_id;
            if(!$AccountID){continue;}
            else{
                $AccountItem = PodioItem::get($AccountID);
                $AccountItemID = $AccountItem->fields['account-item-id']->values;
                if($AccountItemID){
                    $ContactFields['fields']['account-item'] = (int)$AccountItemID;
                    PodioItem::update($ContactItemID, $ContactFields);
                }

            }

        }$i++;
    }while($count == 500);



//    $i = 0;
//    $offset = 0;
//
//    do {
//        $offset = $i * 500;
//        $AccountItems = PodioItem::filter_by_view(17330412, 31597500, array('limit' => 500, 'offset' => $offset));
//        $count = count($AccountItems);
//        foreach ($AccountItems as $account) {
//            $AccountItemID = $account->item_id;
//            $AccountSFID = $account->fields['account-sf-id']->values;
//            if($AccountSFID) {
//                $UpdateAccount = PodioItem::update($AccountItemID, array(
//                    'fields' => array(
//                        'account-sf-id' => [],
//                        'sandbox-account-sf-id' => $AccountSFID
//                    )
//                ));
//            }
//
//
//        }$i++;
//    }while($count == 500);



//    $ContactItemID = "";
//    $VendorItemID = "";
//    $ContactItemID = $contact->item_id;
//    $VendorItemID = $contact->fields['related-vendor-item-id']->values;//related-vendor-item-id
//    if (!$VendorItemID) {continue;}
//
//    $i2 = 0;
//    $offset2 = 0;
//    $thing = "No";
//    $AccountSFID = "";
//    $AccountItem = "";
//    $AccountItemID = "";
//    $UpdateContact = "";
//    $UpdateContactFields = array('fields'=>array());
//
//    do{
//        $offset2 = $i2 * 500;
//        $AccountItems = PodioItem::filter(17330412, array('filters'=>array('podio-account-item-id' => (string)$VendorItemID),'limit' => 500,'offset' => $offset2));
//        $AccountItemID = $AccountItems[0]->item_id;
//        if ($AccountItemID) {
//            $thing = "Yes";
//            $UpdateContactFields['fields']['account-item'] = (int)$AccountItemID;
//            $AccountItem = PodioItem::get($AccountItemID);
//            $AccountSFID = $AccountItem->fields['account-sf-id']->values;
//            if ($AccountSFID) {
//                $UpdateContactFields['fields']['parent-account-sfid'] = (string)$AccountSFID;
//            }
//
//
//        }$i2++;
//    }while($thing == "No" || $offset2 < 14000) ;
//    $UpdateContact = PodioItem::update($ContactItemID, $UpdateContactFields);
//
//    print_r($UpdateContact);
//    exit;
//
//
//}$i++;
//    }while($count == 500);














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