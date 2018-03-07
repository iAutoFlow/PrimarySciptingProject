<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/22/2016
 * Time: 4:16 PM
 */


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
    $appID = $requestParams['app_id'];



//CODE HERE


    $item = PodioItem::get($itemID);

//get Email Address & Name of Submitter

    $submittedBYEMAIL = $item->fields['submitters-email-2']->values[0]['value'];
    $submittedBYNAME = $item->fields['name-2']->values;

    // print_r($submittedBYEMAIL);
    // exit;

//Filter Contacts App by Email and return the Contacts Item ID


    $contactsfilter = PodioItem::filter(14660191, array("filters" => array('email-address'=>array($submittedBYEMAIL))));
    $contactitemID = $contactsfilter[0]->item_id;

    if(!$contactitemID){
        $NewContactItem = PodioItem::create(14660191, array(
            'fields'=>array(
                'name'=>$submittedBYNAME,
                'email-address'=>array(
                    'type'=>'work',
                    'value'=>$submittedBYEMAIL
                )

            )
        ));

        $contactitemID = $NewContactItem->item_id;
    }






    PodioItem::update($itemID, array(
        'fields' => array(
            'submitter-podio-contact-item' => array('value' => (int)$contactitemID
            )
        )
    ));


//Update Webinar Request Topic Item with the Submitters Contact Item ID (Content Development Space)








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

