<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/13/2016
 * Time: 5:09 PM
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

    //Get Podio Leads Item
    $item = PodioItem::get($itemID);
    $CreateInvoice = $item->fields['create-invoice']->values[0]['text'];
    $FreshbooksID = $item->fields['freshbooks-id']->values;

    if($CreateInvoice == "Yes") {

        $HourlyRate = $item->fields['hourly-rate']->values;
        $Discovery = $item->fields['d1']->values;
        $Architecture = $item->fields['a1']->values;
        $Buildout = $item->fields['b1']->values;
        $Automations = $item->fields['ava1']->values;
        $TestingAlterations	 = $item->fields['ta1']->values;
        $Development = $item->fields['dev1']->values;
        $Meetings = $item->fields['m1']->values;
        $Training = $item->fields['t1']->values;
        $Administration = $item->fields['pa1']->values;


        $domain = "techego";
        $token = "13a5208137c82f5696888c605b0afc2d";
        $fb = new Freshbooks\FreshBooksApi($domain, $token);
        $fb->setMethod('invoice.create');

        $fb->post(array(
            "invoice" => array(
                "client_id" => $FreshbooksID,
                "status" => "draft",
                "lines" => array(
                    "line" => array(
                        array(
                            "name" => "Discovery Hours",
                            "description" => "Discovery Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Discovery,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Architecture Hours",
                            "description" => "Architecture Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Architecture,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Buildout Hours",
                            "description" => "Buildout Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Buildout,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Automations Hours",
                            "description" => "Automations Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Automations,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Alterations Hours",
                            "description" => "Alterations Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$TestingAlterations,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Development Hours",
                            "description" => "Development Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Development,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Project Meetings Hours",
                            "description" => "Project Meetings Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Meetings,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Training Hours",
                            "description" => "Training Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Training,
                            "type" => "Time"
                        ),
                        array(
                            "name" => "Project Administration Hours",
                            "description" => "Project Administration Hours",
                            "unit_cost" => $HourlyRate,
                            "quantity" => (int)$Administration,
                            "type" => "Time"
                        )
                    )
                )
            )
        ));

        $fb->request();
        $response = $fb->getResponse();
        if ($fb->success()) {
            if ($response["invoice_id"]) {
                //Update FreshBookInvoiceId in Podio Account, and set Category to "Created"
                $UpdateTriggerItem = PodioItem::update($itemID, array(
                    'fields'=>array(
                        'create-invoice'=>"Created",
                        'invoice-number'=>$response['invoice_id'],
                    )
                ),
                    array('hook'=>false
                    )
                );
            }
        }


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