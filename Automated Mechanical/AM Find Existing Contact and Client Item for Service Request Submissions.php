<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/23/2016
 * Time: 2:15 PM
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


    //Get Values of Service Request
    $FirstName = $item->fields['first-name']->values;
    $LastName = $item->fields['last-name']->values;
    $CompanyName = $item->fields['company-name']->values;
    $EmailAddress = $item->fields['email-address']->values[0]['value'];
    $EmailAddressType = $item->fields['email-address']->values[0]['type'];
    $PhoneNumber = $item->fields['phone-number']->values[0]['value'];
    $PhoneNumberType = $item->fields['phone-number']->values[0]['type'];
    $NewClient = $item->fields['are-you-a-new-customer']->values[0]['text'];
    $Source = $item->fields['where-did-you-hear-about-automated-mechanical']->values[0]['text'];
    $Emergency = $item->fields['is-this-an-emergency']->values[0]['text'];

    $ServiceLocationItemID = $item->fields['service-location']->values[0]->item_id;


    //Get Related Contact and Client Item
    $ClientItemID = $item->fields['client']->values[0]->item_id;
    $ContactItemID = $item->fields['primay-poc']->values[0]->item_id;

    //Create Fields Array
    $ContactFieldsArray = array('fields'=>array());
    $ClientFieldsArray = array('fields'=>array());




    //If there is no Related Contact Item
    if(!$ContactItemID && !$ClientItemID && $NewClient ==  "Yes, I am a Potential new customer."){

        //Assemble New Contact Fields Array
        $ContactFieldsArray['fields']['title'] = $FirstName." ".$LastName;
        $ContactFieldsArray['fields']['type'] = 'Customer';
        $ContactFieldsArray['fields']['email'] = array('type'=>$EmailAddressType, 'value'=>$EmailAddress);
        if($CompanyName){$ContactFieldsArray['fields']['organization'] = $CompanyName;}
        if($PhoneNumber){$ContactFieldsArray['fields']['phone'] = array('type'=>$PhoneNumberType, 'value'=>$PhoneNumber);}

        //Create New Contact Item
        $CreateContact = PodioItem::create(15856025, $ContactFieldsArray);
        $ContactItemID = $CreateContact->item_id;

        //Assemble New Client Fields Array
        $Title = "";
        if($FirstName){$Title .= $FirstName;}
        if($LastName){$Title .= " ".$LastName;}
        if($CompanyName){$Title = $CompanyName;}

        $ClientFieldsArray['fields']['title'] = $Title;
        $ClientFieldsArray['fields']['primary-poc'] = (int)$ContactItemID;
        $ClientFieldsArray['fields']['type'] = "Prospect";
        $ClientFieldsArray['fields']['customer-source'] = array('value'=>$Source);


        //Create New Contact Item
        $CreateClient = PodioItem::create(15856024, $ClientFieldsArray);
        $ClientItemID = $CreateClient->item_id;


        //Update Trigger Service Request Item
        $UpdateServiceRequest = PodioItem::update($itemID, array(
            'fields'=>array(
                'client'=>(int)$ClientItemID,
                'primay-poc'=>(int)$ContactItemID,
            )
        ),
            array('hooks'=>false)
        );

        //Update Service Location Item
        if($ServiceLocationItemID){
            $UpdateServiceLocation = PodioItem::update($ServiceLocationItemID, array(
                'fields'=>array(
                    'client'=>(int)$ClientItemID,
                    'service-location-contant'=>(int)$ContactItemID,
                )
            ));
        }
        exit;
    }


    //Filter Contacts by Name
    if(!$ContactItemID && !$ClientItemID && $NewClient == "No, I am a current existing customer." || !$ContactItemID && !$ClientItemID &&  $NewClient == "I am neither.") {

        //Filter Contacts by Name
        $FilterContactsbyName = PodioItem::filter(15856025, array('filters' => array('title' => $FirstName . " " . $LastName)));
        $ContactItemID = $FilterContactsbyName[0]->item_id;

        //Filter Contacts by Email Address
        if (!$ContactItemID) {
            $FilterContactsbyEmail = PodioItem::filter(15856025, array('filters' => array('email'=>array($EmailAddress))));
            $ContactItemID = $FilterContactsbyEmail[0]->item_id;
        }

        //If there is still no Contact Record Found, Create a new one.
        if (!$ContactItemID) {

            //Assemble New Contact Fields Array
            $ContactFieldsArray['fields']['title'] = $FirstName . " " . $LastName;
            $ContactFieldsArray['fields']['type'] = 'Customer';
            $ContactFieldsArray['fields']['email'] = array('type' => $EmailAddressType, 'value' => $EmailAddress);
            if ($CompanyName) {$ContactFieldsArray['fields']['organization'] = $CompanyName;}
            if ($PhoneNumber) {$ContactFieldsArray['fields']['phone'] = array('type' => $PhoneNumberType, 'value' => $PhoneNumber);}

            //Create New Contact Item
            $CreateContact = PodioItem::create(15856025, $ContactFieldsArray);
            $ContactItemID = $CreateContact->item_id;

            //Assemble New Client Fields Array
            $Title = "";
            if ($FirstName) {$Title .= $FirstName;}
            if ($LastName) {$Title .= " " . $LastName;}
            if ($CompanyName) {$Title = $CompanyName;}

            $ClientFieldsArray['fields']['title'] = $Title;
            $ClientFieldsArray['fields']['primary-poc'] = (int)$ContactItemID;
            $ClientFieldsArray['fields']['type'] = "Prospect";
            $ClientFieldsArray['fields']['customer-source'] = array('value' => $Source);


            //Create New Contact Item
            $CreateClient = PodioItem::create(15856024, $ClientFieldsArray);
            $ClientItemID = $CreateClient->item_id;


            //Update Trigger Service Request Item
            $UpdateServiceRequest = PodioItem::update($itemID, array(
                'fields' => array(
                    'client' => (int)$ClientItemID,
                    'primay-poc' => (int)$ContactItemID,
                )
            ),
                array('hooks' => false)
            );

            //Update Service Location Item
            if($ServiceLocationItemID){
                $UpdateServiceLocation = PodioItem::update($ServiceLocationItemID, array(
                    'fields'=>array(
                        'client'=>(int)$ClientItemID,
                        'service-location-contant'=>(int)$ContactItemID,
                    )
                ));
            }
            exit;
        }




        //If a Contact Record was found, Get related Client Item
        if ($ContactItemID) {
            $RelatedItems = PodioItem::get_references($ContactItemID);
            foreach ($RelatedItems as $related) {
                if ($related['app']['name'] == "Customers / Leads") {
                    $ClientItemID = $related['items'][0]['item_id'];
                }
            }



            //If No Client Item ID is found, Create Client Item
            if (!$ClientItemID) {

                //Assemble New Client Fields Array
                $Title = "";
                if ($FirstName) {$Title .= $FirstName;}
                if ($LastName) {$Title .= " " . $LastName;}
                if ($CompanyName) {$Title = $CompanyName;}

                $ClientFieldsArray['fields']['title'] = $Title;
                $ClientFieldsArray['fields']['primary-poc'] = (int)$ContactItemID;
                $ClientFieldsArray['fields']['type'] = "Prospect";
                $ClientFieldsArray['fields']['customer-source'] = array('value' => $Source);

                //Create New Contact Item
                $CreateClient = PodioItem::create(15856024, $ClientFieldsArray);
                $ClientItemID = $CreateClient->item_id;

            }


            //Update Trigger Service Request Item
            $UpdateServiceRequest = PodioItem::update($itemID, array(
                'fields' => array(
                    'client' => (int)$ClientItemID,
                    'primay-poc' => (int)$ContactItemID,
                )
            ),
                array('hooks' => false)
            );

            //Update Service Location Item
            if($ServiceLocationItemID){
                $UpdateServiceLocation = PodioItem::update($ServiceLocationItemID, array(
                    'fields'=>array(
                        'client'=>(int)$ClientItemID,
                        'service-location-contant'=>(int)$ContactItemID,
                    )
                ));
            }
            exit;
        }
    }


    //If a Contact Record was found, Get related Client Item
    if ($ContactItemID && !$ClientItemID){
        $RelatedItems = PodioItem::get_references($ContactItemID);
        foreach ($RelatedItems as $related) {
            if ($related['app']['name'] == "Customers / Leads") {
                $ClientItemID = $related['items'][0]['item_id'];
            }
        }


        //If No Client Item ID is found, Create Client Item
        if (!$ClientItemID) {

            //Assemble New Client Fields Array
            $Title = "";
            if ($FirstName) {$Title .= $FirstName;}
            if ($LastName) {$Title .= " " . $LastName;}
            if ($CompanyName) {$Title = $CompanyName;}

            $ClientFieldsArray['fields']['title'] = $Title;
            $ClientFieldsArray['fields']['primary-poc'] = (int)$ContactItemID;
            $ClientFieldsArray['fields']['type'] = "Prospect";
            $ClientFieldsArray['fields']['customer-source'] = array('value' => $Source);

            //Create New Contact Item
            $CreateClient = PodioItem::create(15856024, $ClientFieldsArray);
            $ClientItemID = $CreateClient->item_id;

        }




        //Update Trigger Service Request Item
        $UpdateServiceRequest = PodioItem::update($itemID, array(
            'fields' => array(
                'client' => (int)$ClientItemID,
            )
        ),
            array('hooks' => false)
        );

        //Update Service Location Item
        if($ServiceLocationItemID){
            $UpdateServiceLocation = PodioItem::update($ServiceLocationItemID, array(
                'fields'=>array(
                    'client'=>(int)$ClientItemID,
                    'service-location-contant'=>(int)$ContactItemID,
                )
            ));
        }
        exit;
    }


    //If there is a Client Item
    if($ClientItemID && !$ContactItemID){
        $ClientItem = PodioItem::get_references($ContactItemID);
        $ClientPOC = $ClientItem->fields['primary-poc']->values[0]->item_id;

        //Update Trigger Service Request Item
        $UpdateServiceRequest = PodioItem::update($itemID, array(
            'fields' => array(
                'client' => (int)$ClientItemID,
            )
        ),
            array('hooks' => false)
        );

        //Update Service Location Item
        if($ServiceLocationItemID){
            $UpdateServiceLocation = PodioItem::update($ServiceLocationItemID, array(
                'fields'=>array(
                    'client'=>(int)$ClientItemID,
                    'service-location-contant'=>(int)$ContactItemID,
                )
            ));
        }
        exit;
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