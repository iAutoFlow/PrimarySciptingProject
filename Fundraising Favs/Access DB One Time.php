
<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/8/2016
 * Time: 10:59 AM
 */

$username = "podio@techego.com";
$password = "hV91Kg$4!oJUxYZ[";
$client_key = 'dreamfactory-ebqqb5';
$client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';

// Authenticate Podio
Podio::setup($client_key, $client_secret);
Podio::authenticate_with_password($username, $password);



$offset = 0;
$i = 0;
do {
    $offset = $i * 500;
    $bomFilter = PodioItem::filter(15619339, array('limit'=>500,'offset'=>$offset));
    $filteredNum = count($bomFilter);
    foreach($bomFilter as $item) {

        try{
//DO THING HERE FOR EACH ITEM


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

            //FORMAT DATE VALUES

            $salestartDateString = date_create_from_format("n/j/Y h:i:s A", $salestart);
            $salestartDateFormat = date_format($salestartDateString,'Y-m-d H:i:s');

            $salestopDateString = date_create_from_format("n/j/Y h:i:s A", $salestop);
            $salestopDateFormat = date_format($salestopDateString,'Y-m-d H:i:s');

            $totaldueString = date_create_from_format("n/j/Y h:i:s A", $totaldue);
            $totaldueDateFormat = date_format($totaldueString,'Y-m-d H:i:s');

            $deliveryDateString = date_create_from_format("n/j/Y h:i:s A", $deliverydate);
            $deliveryDateFormat = date_format($deliveryDateString,'Y-m-d H:i:s');

            //SET COORDINATORS PODIO CONTACT PROFILE ID

            if ($coordinator == 'Elizabeth') {
                ($coordinatorID = '171154098');
            }
            if ($coordinator == 'Cindy') {
                ($coordinatorID = '181115287');
            }
            if ($coordinator == 'Courtney') {
                ($coordinatorID = '169086104');
            }


            list($combo1, $combo2, $combo3) = explode("; ", $combo);
            echo $combo1;
            echo $combo2;
            echo $combo3;


//Check for existing group item (with group Name). IF NOT FOUND, CREATE. RETURN NEW GROUP ITEM ID

            $groupfilter = PodioItem::filter(15613244, $attributes = array("filters" => array('title' => $group)), $options = array());
            $groupitemID = $groupfilter[0]->item_id;


            if (!$groupitemID) {
                $newgroupitem = PodioItem::create(15613244, $attributes = array(
                    'fields' => array(
                        'company-name' => $group,
                        'location-of-company-hq' => array(
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $zip,
                            'street_address' => $chairaddress,

                        ))
                ));
                $groupitemID = $newgroupitem->item_id;
            } //If found, Update.


            else {
                PodioItem::update($groupitemID, $attributes = array(
                    'fields' => array(
                        'company-name' => $group,
                        'location-of-company-hq' => array(
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $zip,
                            'street_address' => $chairaddress,

                        ))
                ));
            }

            //Check for existing Contact Item, BY 'NAME.' IF NOT FOUND, CREATE. RETURN NEW CONTACT ITEM ID

            $contactfilter = PodioItem::filter(15613914, $attributes = array("filters" => array('name' => $chair)));
            $options = array();

            $contactitemID = $contactfilter[0]->item_id;

            //If not found, create new and return new item id

            if (!$contactitemID) {
                $newcontactitem = PodioItem::create(15613914, $attributes = array(
                    'fields' => array(
                        'name' => $chair,
                        'organization' => array('value' => $group),
                        'email-address' => array('type' => 'work', 'value' => $email),
                        'phone-number' => array('type' => 'work', 'value' => $cphone),
                        'address' => array(
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $zip,
                            'street_address' => $chairaddress,

                        )
                    )));
                $contactitemID = $newcontactitem->item_id;
            }

            //If already exists, Update.

            else {
                PodioItem::update($contactitemID, $attributes = array(
                    'fields' => array(
                        'name' => $chair,
                        'organization' => array('value' => $group),
                        'email-address' => array('type' => 'work', 'value' => $email),
                        'phone-number' => array('type' => 'work', 'value' => $cphone),
                        'address' => array(
                            'city' => $city,
                            'state' => $state,
                            'postal_code' => $zip,
                            'street_address' => $chairaddress,

                        )
                    )));

            }


            //Check for existing Profile Item with Access ID.

            $profilefilter = PodioItem::filter(15603619, $attributes = array("filters" => array('accessid' => $accessid)), $options = array());
            $profileitemID = $profilefilter[0]->item_id;

            //If not found, create new profile item and return item id.

            $attributes = array(
                'fields' => array());

            if ($groupitemID) {$attributes['fields']['groups'] = array('value' => (int)$groupitemID);}
            if ($group) {$attributes['fields']['group-name'] = $group;}
            if ($bus) {$attributes['fields']['bus-type'] = $bus;}
            if ($contactitemID) {$attributes['fields']['chairperson'] = array('value' => (int)$contactitemID);}
            if ($coordinatorID) {$attributes['fields']['coordinator-3'] = array('value' => (int)$coordinatorID);}
            if ($bphone) {$attributes['fields']['lead-phone'] = array('type' => 'work', 'value' => $bphone);}
            if ($county) {$attributes['fields']['county'] = array($county);}
            if ($combo1) {$attributes['fields']['combo'] = array($combo1, $combo2, $combo3);}
            if ($orderform) {$attributes['fields']['order-form'] = $orderform;}
            if ($prepack){$attributes['fields']['prepack'] = $prepack;}
            if ($goal){$attributes['fields']['total-sold-last-year'] = $goal;}
            if ($sellers){$attributes['fields']['sellers'] = $sellers;}
            if ($incentive) {$attributes['fields']['incentives'] = $incentive;}
            if ($cash) {$attributes['fields']['cash-machine'] = $cash;}
            if ($salestartDateFormat) {$attributes['fields']['next-follow-up'] = array('start' => $salestartDateFormat);}
            if ($salestopDateFormat) {$attributes['fields']['sale-stop'] = array('start' => $salestopDateFormat);}
            if ($totaldueDateFormat) {$attributes['fields']['totals-due'] = array('start' => $totaldueDateFormat);}
            if ($totalsold) {$attributes['fields']['total-sold'] = $totalsold;}
            if ($deliveryDateFormat) {$attributes['fields']['delivery-date'] = array('start' => $deliveryDateFormat);}
            if ($departure) {$attributes['fields']['country'] = $departure;}
            if ($arrival) {$attributes['fields']['arrival'] = $arrival;}
            if ($pickuptime) {$attributes['fields']['pick-up-time'] = $pickuptime;}
            if ($cod) {$attributes['fields']['cod'] = $cod;}
            if ($driver) {$attributes['fields']['driver-last-year'] = $driver;}
            if ($rebook) {$attributes['fields']['rebook'] = $rebook;}
            if ($accessid) {$attributes['fields']['accessid'] = $accessid;}
            if ($deliveryaddress) {$attributes['fields']['address'] = array('city' => $deliveryaddressCity, 'state' => $deliveryaddressState, 'street_address' => $deliveryaddress);

                if (!$profileitemID) {
                    $newprofileitem = PodioItem::create(15603619, $attributes);;
                } //If already exists, Update.

                else {
                    PodioItem::update($profileitemID, $attributes);;
                }};


        }
        catch(Exception $e) {

            $event['response'] = [

                'status_code' => 400,
                'content' => [
                    'success' => false,
                    'result' => $result,
                    'message' => "Error: " . $e,

                ]
            ];
            return;
        }

        $i++;
    }
}

while ($filteredNum == 500);

return [
    'success' => true,
    'result' => $result
];




$offset = 0;
$i = 0;
do {
    $offset = $i * 500;
    $bomFilter = PodioItem::filter(15619339, array('limit'=>500,'offset'=>$offset));
    $filteredNum = count($bomFilter);
    foreach($bomFilter as $item) {

        try{

$i++;



while ($filteredNum == 500);