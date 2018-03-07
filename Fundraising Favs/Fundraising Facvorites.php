<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 4:12 PM
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

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;


    //FormatCurrent Date and Time
    $todaysDate = date("Y-m-d H:i:s", strtotime("now"));


    //Get All Values From Access Item
    $group = ucwords(strtolower($item->fields['title']->values));
    $coordinator = ucwords(strtolower($item->fields['coordinator']->values));
    $bus = ucwords(strtolower($item->fields['buss']->values));
    $chair = ucwords(strtolower($item->fields['chair']->values));
    $email = strtolower($item->fields['email']->values);
    $bphone = ucwords(strtolower($item->fields['bphone']->values));
    $cphone = ucwords(strtolower($item->fields['cphone']->values));
    $chairaddress = ucwords(strtolower($item->fields['addre']->values));
    $city = ucwords(strtolower($item->fields['city']->values));
    $state = $item->fields['state']->values;
    $zip = ucwords(strtolower($item->fields['zip']->values));
    $county = $item->fields['county']->values;
    $combo = $item->fields['combo']->values;
    $orderform = ucwords(strtolower($item->fields['orderf']->values));
    $prepack = ucwords(strtolower($item->fields['prepack']->values));
    $goal = ucwords(strtolower($item->fields['goal']->values));
    $sellers = ucwords(strtolower($item->fields['sel']->values));
    $incentive = ucwords(strtolower($item->fields['incentive']->values));
    $cash = ucwords(strtolower($item->fields['cash']->values));
    $salestart = $item->fields['salestart']->values;
    $salestop = $item->fields['salestop']->values;
    $totaldue = $item->fields['totaldue']->values;
    $totalsold = $item->fields['totalsold']->values;
    $deliverydate = $item->fields['deliverydate']->values;
    $departure = $item->fields['departure']->values;
    $arrival = $item->fields['arrival']->values;
    $pickuptime = ucwords(strtolower($item->fields['pickuptime']->values));
    $deliveryaddress = ucwords(strtolower($item->fields['deliveryaddress']->values));
    $deliveryaddressCity = ucwords(strtolower($item->fields['city-delivery-address']->values));
    $deliveryaddressState = $item->fields['in-state-delivery-address']->values;
    $cod = ucwords(strtolower($item->fields['cod']->values));
    $rebook = ucwords(strtolower($item->fields['rebook']->values));
    $driver = ucwords(strtolower($item->fields['driver']->values));
    $accessid = ucwords(strtolower($item->fields['accessid']->values));
    $PodioID = ucwords(strtolower($item->fields['podioid']->values));



    //FORMAT DATE VALUES
    if($salestart) {
        $salestartDateString = date_create_from_format("n/j/Y h:i:s A", $salestart);
        $salestartDateFormat = $salestartDateString->format('Y-m-d H:i:s');
    }
    if($salestop) {
        $salestopDateString = date_create_from_format("n/j/Y h:i:s A", $salestop);
        $salestopDateFormat = $salestopDateString->format('Y-m-d H:i:s');
    }
    if($totaldue) {
        $totaldueString = date_create_from_format("n/j/Y h:i:s A", $totaldue);
        $totaldueDateFormat = $totaldueString->format('Y-m-d H:i:s');
    }
    if($deliverydate) {
        $deliveryDateString = date_create_from_format("n/j/Y h:i:s A", $deliverydate);
        $deliveryDateFormat = $deliveryDateString->format('Y-m-d H:i:s');
    }


    //SET COORDINATORS PODIO CONTACT PROFILE ID
    if($coordinator == 'Elizabeth'){
        ($coordinatorID = (int)171154098);}
    if($coordinator == 'Cindy'){
        ($coordinatorID = (int)181115287);}
    if($coordinator == 'Courtney'){
        ($coordinatorID = (int)169086104);}


    //Break up Combo Values into an array of values
    if($combo) {
        list($combo1, $combo2, $combo3) = explode(";", $combo);
        echo $combo1;
        echo $combo2;
        echo $combo3;
    }



    //Create / Update GROUP Item
    if($group) {

        //Check for existing group item (with group Name).
        $groupfilter = PodioItem::filter(15613244, array("filters" => array('company-name' => $group)));
        $groupitemID = $groupfilter[0]->item_id;

        //Create Group Fields Array
        $GroupFieldsArray = array(
            'fields' => array());


        //IF NOT FOUND, CREATE.
        if (!$groupitemID) {

            //Format Address Fields
            if ($deliveryaddress && $deliveryaddressCity && $deliveryaddressState) {
                $GroupFieldsArray['fields']['group-address'] = array('street_address' => $deliveryaddress, 'city' => $deliveryaddressCity, 'state' => $deliveryaddressState);
            }
            if ($deliveryaddress && $deliveryaddressCity && !$deliveryaddressState) {
                $ContactFieldsArray['fields']['group-address'] = array('street_address' => $deliveryaddress, 'city' => $deliveryaddressCity);
            }
            if ($deliveryaddress && !$deliveryaddressCity && !$deliveryaddressState) {
                $ContactFieldsArray['fields']['group-address'] = array('street_address' => $deliveryaddress);
            }

            //Add Company Name to Fields Array
            $GroupFieldsArray['fields']['company-name'] = $group;

            //Create Group Item
            $CreateGroupItem = PodioItem::create(15613244, $GroupFieldsArray);

            //RETURN NEW GROUP ITEM ID
            $groupitemID = $CreateGroupItem->item_id;
        }
    }






    //Create / Update CONTACT Item
    if($chair) {
        $ContactItemIDArray = array();
        $ChairArray = array();
        list($chair1, $chair2, $chair3) = explode(";", $chair);
        if ($chair1) {
            array_push($ChairArray, $chair1);
        }
        if ($chair2) {
            echo $chair2;
        }
        array_push($ChairArray, $chair2);
        if ($chair3) {
            echo $chair3;
            array_push($ChairArray, $chair3);
        }

        foreach ($ChairArray as $contact) {
            $contactfilter = PodioItem::filter(15613914, array("filters" => array('name' => $contact)));
            $contactitemID = $contactfilter[0]->item_id;
            array_push($ContactItemIDArray, $contactitemID);
        }
    }


//            $ContactFieldsArray = array(
//                'fields' => array());
//
//            if ($group) {
//                $ContactFieldsArray['fields']['organization'] = array('type' => 'text', 'value' => $group);
//            }
//            if ($email) {
//                $ContactFieldsArray['fields']['email-address'] = array('type' => 'work', 'value' => $email);
//            }
//            if ($cphone) {
//                $ContactFieldsArray['fields']['phone-number'] = array('type' => 'work', 'value' => $cphone);
//            }
//
//            if ($chairaddress && $city && $state && $zip) {
//                $ContactFieldsArray['fields']['address'] = array('street_address' => $chairaddress, 'city' => $city, 'state' => $state, 'postal_code' => $zip);
//            } elseif ($chairaddress && $city && $state && !$zip) {
//                $ContactFieldsArray['fields']['address'] = array('street_address' => $chairaddress, 'city' => $city, 'state' => $state);
//            } elseif ($chairaddress && $city && !$state && !$zip) {
//                $ContactFieldsArray['fields']['address'] = array('street_address' => $chairaddress, 'city' => $city);
//            } elseif ($chairaddress && !$city && !$state && !$zip) {
//                $ContactFieldsArray['fields']['address'] = array('street_address' => $chairaddress);
//            } elseif (!$chairaddress && $city && $state && $zip) {
//                $ContactFieldsArray['fields']['address'] = array('city' => $city, 'state' => $state, 'postal_code' => $zip);
//            } elseif (!$chairaddress && $city && $state && !$zip) {
//                $ContactFieldsArray['fields']['address'] = array('city' => $city, 'state' => $state);
//            } elseif (!$chairaddress && !$city && $state && !$zip) {
//                $ContactFieldsArray['fields']['address'] = array('state' => $state);
//            }
//
//
//            if (!$contactitemID) {
//                $ContactFieldsArray['fields']['name'] = $contact;
//                $newcontactitem = PodioItem::create(15613914, $ContactFieldsArray);
//                $contactitemID = $newcontactitem->item_id;
//                array_push($ContactItemIDArray, $contactitemID);
//            }


    //Create / Update PROFILE Items
    if ($accessid) {

        //Filter For existing Profile Items
        $profilefilter = PodioItem::filter(15603619, array("filters" => array('accessid' => $accessid)));
        $profileitemID = $profilefilter[0]->item_id;

        //Create Profile Fields Array
        $ProfileFieldArray = array(
            'fields' => array());


        //Add Values to Fields Array
        if ($groupitemID) {
            $ProfileFieldArray['fields']['groups'] = array((int)$groupitemID);
        }
        if ($contactitemID) {
            $ProfileFieldArray['fields']['chairperson'] = $ContactItemIDArray;
        }
        if ($coordinatorID) {
            $ProfileFieldArray['fields']['coordinator-3'] = $coordinatorID;
        }
        if ($group) {
            $ProfileFieldArray['fields']['group-name'] = $group;
        }
        if ($bus) {
            $ProfileFieldArray['fields']['bus-type'] = $bus;
        }
        if ($bphone) {
            $ProfileFieldArray['fields']['lead-phone'] = array('type' => 'work', 'value' => $bphone);
        }
        if ($county) {
            $ProfileFieldArray['fields']['county'] = $county;
        }
        if ($combo) {
            $ProfileFieldArray['fields']['combo'] = array($combo1, $combo2, $combo3);
        }
        if ($orderform) {
            $ProfileFieldArray['fields']['order-form'] = $orderform;
        }
        if ($prepack) {
            $ProfileFieldArray['fields']['prepack'] = $prepack;
        }
        if ($goal) {
            $ProfileFieldArray['fields']['total-sold-last-year'] = $goal;
        }
        if ($sellers) {
            $ProfileFieldArray['fields']['sellers'] = $sellers;
        }
        if ($incentive) {
            $ProfileFieldArray['fields']['incentives'] = $incentive;
        }
        if ($cash) {
            $ProfileFieldArray['fields']['cash-machine'] = $cash;
        }
        if ($salestartDateFormat) {
            $ProfileFieldArray['fields']['next-follow-up'] = array('start' => $salestartDateFormat);
        }
        if ($salestopDateFormat) {
            $ProfileFieldArray['fields']['sale-stop'] = array('start' => $salestopDateFormat);
        }
        if ($totaldueDateFormat) {
            $ProfileFieldArray['fields']['totals-due'] = array('start' => $totaldueDateFormat);
        }
        if ($totalsold) {
            $ProfileFieldArray['fields']['total-sold'] = $totalsold;
        }
        if ($deliveryDateFormat) {
            $ProfileFieldArray['fields']['delivery-date'] = array('start' => $deliveryDateFormat);
        }
        if ($departure) {
            $ProfileFieldArray['fields']['country'] = $departure;
        }
        if ($arrival) {
            $ProfileFieldArray['fields']['arrival'] = $arrival;
        }
        if ($pickuptime) {
            $ProfileFieldArray['fields']['pick-up-time'] = $pickuptime;
        }
        if ($cod) {
            $ProfileFieldArray['fields']['cod'] = $cod;
        }
        if ($driver) {
            $ProfileFieldArray['fields']['driver-last-year'] = $driver;
        }
        if ($rebook) {
            $ProfileFieldArray['fields']['rebook'] = $rebook;
        }
        if ($accessid) {
            $ProfileFieldArray['fields']['accessid'] = $accessid;
        }
    }


    //Create a Fiels Array for updateing the Profile Item
    $UpdateArray = array(
        'fields' => array(
            'last-updated-date' => array('start' => $todaysDate),
        ));


    //If there is no Podio ID
    if (!$PodioID) {

        //Format Address and Add to Fields Array
        if ($deliveryaddress && $deliveryaddressCity && $deliveryaddressState) {
            $ProfileFieldArray['fields']['address'] = array('street_address' => $deliveryaddress, 'city' => $deliveryaddressCity, 'state' => $deliveryaddressState);
        }
        if ($deliveryaddress && $deliveryaddressCity && !$deliveryaddressState) {
            $ProfileFieldArray['fields']['address'] = array('street_address' => $deliveryaddress, 'city' => $deliveryaddressCity);
        }
        if ($deliveryaddress && !$deliveryaddressCity && !$deliveryaddressState) {
            $ProfileFieldArray['fields']['address'] = array('street_address' => $deliveryaddress);
        }


        //Create New Profile Item
        $newprofileitem = PodioItem::create(15603619, $ProfileFieldArray);
        $NewPodioID = $newprofileitem->item_id;
        $UpdateArray['fields']['podioid'] = (string)$NewPodioID;
    }


    //Update Profile Item
    else {
        $UpdateProfile = PodioItem::update($PodioID, $ProfileFieldArray);
    }


    //Update Trigger Access DB Item
    $UpdateTriggerItem = PodioItem::update($itemID, $UpdateArray,
        array('hook' => false));



    //End Code

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

