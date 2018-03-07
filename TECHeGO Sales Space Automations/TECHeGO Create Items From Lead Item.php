<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/3/2016
 * Time: 1:41 PM
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
    $CreateItems = $item->fields['create-items']->values[0]['text'];
    $AccountManagerItemID = $item->fields['account-manager']->values[0]->item_id;
    $LeadContactItemID = $item->fields['company-contacts']->values[0]->item_id;
    $FreshbooksID = $item->fields['freshbooks-id']->values;



    //Format Current Date/Time
    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));
    $TriggerValue = "Created";


    //Trigger Values
    $CreateItems = $item->fields['create-items']->values[0]['text'];

    //Check Trigger Value
    if ($CreateItems == "..." || $CreateItems == "Created") {
        exit;
    }


    //////////IF Trigger Value == "FRESHBOOKS"//////////////////////////////////////////////////////////////////////////
    if ($CreateItems == "Freshbooks" && !$FreshbooksID) {
        $CompanyName = $item->fields['company-name-in-podio']->values;



        //Get Customer Item
        $Customer = PodioItem::get($LeadContactItemID);
        $CustomerName = $Customer->fields['name']->values;

        //Prepare PodioName to FreshBook
        $FullName = explode(" ", $CustomerName);
        $FirstName = $FullName[0];
        $LastName = end($FullName);


        $EmailAddress = $Customer->fields['email-address']->values[0]['value'];
        $PhoneNumber = $Customer->fields["phone-number"]->values[0]['value'];

        $StreetAddress = $Customer->fields['address']->values[0]->street_address;
        $City = $Customer->fields['address']->values[0]->city;
        $State = $Customer->fields['address']->values[0]->state;
        $Country = $Customer->fields['address']->values[0]->country;
        $Zip = $Customer->fields['address']->values[0]->postal_code;


        //Make request to FreshBook
        $domain = "techego";
        $token = "13a5208137c82f5696888c605b0afc2d";
        $fb = new Freshbooks\FreshBooksApi($domain, $token);
        $fb->setMethod('client.create');

        $fb->post(array(
            "client" => array(
                "first_name" => $FirstName,
                "last_name" => $LastName,
                "organization" => $CompanyName,
                "email" => $EmailAddress,
                "mobile" => $PhoneNumber,
                "p_street1" => $StreetAddress,
                "p_city" => $City,
                "p_state" => $State,
                "p_country" => $Country,
                "p_code" => $Zip,
            )
        ));

        $fb->request();
        $response = $fb->getResponse();
        if ($fb->success()) {
            if ($response["client_id"]) {
                //Update FreshBookId in Podio Account
                $UpdateLeadItem = PodioItem::update($itemID, array(
                    'fields' => array(
                        'freshbooks-id' => $response["client_id"],
                        'create-items' => $TriggerValue,
                    )),
                    array('hook'=>false
                    )
                );
            }
        }
    }

    ////////////END Freshbooks Event////////////////////////////////////////////////////////////////////////////////////////////


    ///////////CreateInteraction///////////////////////////////////////////////////////////////////////////////////////////////

    //CREATE INTERATION
    if ($CreateItems == 'Interaction') {
        $PersonContactedItemID = $item->fields['persons-contacted']->values[0]->item_id;
        $InteractionType = $item->fields['type-2']->values[0]['text'];
        $InteractionPurpose = $item->fields['purpose']->values[0]['text'];
        $InteractionNurturing = $item->fields['nurturing']->values;
        $InteractionNotes = $item->fields['summary-notes']->values;
        $InteractionGut = $item->fields['gut']->values;
        $InteractionFollowUpDate = $item->fields['follow-up-date-2']->start;


        if ($InteractionNurturing) {
            $InteractionNurturingArray = array();

            foreach ($InteractionNurturing as $nurture) {
                $NutureValue = $nurture['text'];
                array_push($InteractionNurturingArray, $NutureValue);
            }
        }

        //Create Interactions Field Array
        $InteractionFieldsArray = array(
            'fields' => array(
                'lead-2' => array((int)$itemID),
                'date' => array('start' => $todaysDate),

            ));

        if ($PersonContactedItemID) {
            $InteractionFieldsArray['fields']['contact-2'] = array((int)$PersonContactedItemID);
        }
        if (!$PersonContactedItemID && $LeadContactItemID) {
            $InteractionFieldsArray['fields']['contact-2'] = array((int)$LeadContactItemID);
        }

        if ($InteractionType) {
            $InteractionFieldsArray['fields']['type'] = $InteractionType;
        }
        if ($InteractionPurpose) {
            $InteractionFieldsArray['fields']['purpose'] = $InteractionPurpose;
        }
        if ($InteractionNurturing) {
            $InteractionFieldsArray['fields']['nurturing'] = $InteractionNurturingArray;
        }
        if ($InteractionNotes) {
            $InteractionFieldsArray['fields']['title'] = $InteractionNotes;
        }
        if ($InteractionGut) {
            $InteractionFieldsArray['fields']['gut'] = (int)$InteractionGut;
        }
        if ($InteractionFollowUpDate) {
            $InteractionFieldsArray['fields']['follow-up-date'] = array('start' => $InteractionFollowUpDate->format('Y-m-d H:i:s'));
        }


        //Create Interaction Item
        $CreateInteraction = PodioItem::create(14919370, $InteractionFieldsArray);

        //Update Trigger Item to Completed
        $UpdateTriggerItem = PodioItem::update($itemID, array(
            'fields' => array(
                'create-items' => $TriggerValue,
                'persons-contacted' => [],
                'type-2' => [],
                'purpose' => [],
                'nurturing' => [],
                'summary-notes' => [],
                'gut' => 0,
                'follow-up-date-2' => [],
            )),
            array(
                'hook' => false
            )
        );

    }





    /////////////END INTERACTION GENERATOR/////////////////////////////////////////////////////////////////////////////////////


    sleep(10);

    //UpdateTrigger item
    $UpdateTrigger = PodioItem::update($itemID, array(
        'fields'=>array(
            'create-items'=>"..."
        )
    ),
        array('hook'=>false
        )
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