//<?php

date_default_timezone_set('America/Denver');

function card_cycle_generator($current_date){

    if($current_date == null)
        throw new Exception('Missing Required Parameter: Current Date');


    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $day = date_format($todaysDate, "j");
    $year = date_format($todaysDate, "Y");



    if($day == '16'){

        date_add($todaysDate, date_interval_create_from_date_string('1 month'));
        date_sub($todaysDate,date_interval_create_from_date_string("16 days"));

    }

    if($day == '1'){

        date_add($todaysDate, date_interval_create_from_date_string('14 days'));

    }

    if($day != '1' && $day != '16'){
        throw new Exception('Not the 1st or the 16th. Run the call manually to create Timecards and Billing Cycles if not a scheduled run.');
    }

    $filterDate = date_format($todaysDate, "F j, Y");

    //Manual Filter Date for Manual runs (when the scheduled doesnt work etc.) Uncomment to use this instead of going off the scheduled date
    //$manualDate = date_create("2016-04-30");
    //$filterDate = date_format($manualDate, "F j, Y");

    //Filter for Period
    $periodFilter = PodioItem::filter( 15555787, $attributes = array("filters"=>array('cycle'=>$filterDate)), $options = array() );

    $periodItem = $periodFilter[0]->item_id;

    if(!$periodItem)
        throw new Exception('Could not find Period Item for date: '.$filterDate);


    //Filter for Active Employees
    $activeEmployees = PodioItem::filter_by_view( 7099698, 22882436, $attributes = array() );

    foreach($activeEmployees as $employeeItem){

        $employeeItemID = $employeeItem->item_id;

        $generateTimeCard = PodioItem::create( 4177108, $attributes = array(
            'fields'=>array(
                'status'=>1,
                'period'=>array(
                    'value'=>(int)$periodItem
                ),
                'employee-2'=>array(
                    'value'=>(int)$employeeItemID
                )
            )
        ),
            $options = array() );
    }

    //Filter for Active Clients
    $activeProjects = PodioItem::filter_by_view( 3848224, 22866007, $attributes = array() );

    foreach($activeProjects as $projectItem){

        $projectItemID = $projectItem->item_id;
        $projectClientID = $projectItem->fields['company2']->values[0]->item_id;


        $generateBillingCycle = PodioItem::create( 4481866, $attributes = array(
            'fields'=>array(
                'status'=>1,
                'period'=>array(
                    'value'=>(int)$periodItem
                ),
                'project'=>array(
                    'value'=>(int)$projectItemID
                ),
                'client'=>array(
                    'value'=>(int)$projectClientID
                )
            )
        ),
            $options = array() );
    }



    return 'Timecards and Billing Cycles Generated for '.$current_date;


}


try{

    $requestParams = $event['request']['parameters'];
    $automation = $requestParams['automation'];

// Client credentials
    $username = "podio@techego.com";
    $password = "hV91Kg$4!oJUxYZ[";
    $client_key = 'dreamfactory-ebqqb5';
    $client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';

// Authenticate Podio
    Podio::setup($client_key, $client_secret);
    Podio::authenticate_with_password($username, $password);

    switch ($automation){

        case 'card-cycle-generator':
            $generate = card_cycle_generator(date("Y-m-d"));
            break;
        default:
            throw new Exception('Missing Required Parameter: automation');



    }

    return [
        'success' => true,
        'result' => $result,
        'test' => $periodItem
    ];


}catch(Exception $e){

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'message' => "Error: ".$e
        ]
    ];
    return;

}


