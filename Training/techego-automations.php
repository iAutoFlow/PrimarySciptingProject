//<?php

date_default_timezone_set('America/Denver');


function lead_contract_creator($item_id){

    $item = PodioItem::get($item_id);

    $createContract = $item->fields['create-contract']->values[0]['text'];

    if($createContract == "Engagement & MSA"){

        //engagement contract
        PodioItem::create(15471802,
            array(
                'fields'=>array(
                    'lead'=>$item_id,
                    'type-of-contract'=>array(
                        403989261
                    ),
                    'billing-type'=>1,
                    'portion-upfront-percentage'=>'100%'
                )
            )
        );
    }

    if($createContract == "SOW"){

        //SOW contract
        PodioItem::create(15471802,
            array(
                'fields'=>array(
                    'lead'=>$item_id,
                    'type-of-contract'=>array(
                        403990539
                    )
                )
            )

        );

    }

    if($createContract == "Non-Workflow with T's & C's"){

        //SOW contract
        PodioItem::create(15471802,
            array(
                'fields'=>array(
                    'lead'=>$item_id,
                    'type-of-contract'=>array(
                        403990793
                    )
                )
            )

        );

    }

    if($createContract == "SLA"){

        //SOW contract
        PodioItem::create(15471802,
            array(
                'fields'=>array(
                    'lead'=>$item_id,
                    'type-of-contract'=>array(
                        403990703
                    )
                )
            )

        );

    }

}//end lead_contract_creator function

function new_lead_trigger($item_id){

    $item = PodioItem::get($item_id);

    $newInteraction = PodioItem::create(15471929,
        array(
            'fields'=>array(
                'what-lead-is-this-tied-to'=>$item_id,
                'medium-of-interaction'=>8,
                'drip-campaign-stage'=>1,
                'email-tracking-status'=>6,
                'purpose'=>1
            )
        )
    );

}//end new_lead_trigger

function new_signature_received($item_id){

    $item = PodioItem::get($item_id);

    $docverify_status = $item->fields['docverify-status']->values[0]['text'];

    $billingType = $item->fields['billing-type']->values[0]['text'];

    $leadID = $item->fields['lead']->values[0]->item_id;

    if($docverify_status == "Signature Received"){

        $leadItem = PodioItem::get($leadID);

        PodioItem::update($leadItem,
            array(
                'fields'=>array(
                    'status'=>11
                )
            )
        );

    }

    if(strpos($billingType, 'Up Front') !== false){

        //Freshbooks connection

    }

}//end new_signature_received

function drip_campaign_send_email2($item_id){


    $drip_campaign_item_ID = PodioItem::get($item_id);
    $drip_Stage = $drip_campaign_item_ID->fields['stage']->values[0]['text'];
    $drip_Status = $drip_campaign_item_ID->fields['status']->values;
    $meeting_Set = $drip_campaign_item_ID->fields['meeting-set']->values[0]['text'];
    $client_Name = $drip_campaign_item_ID->fields['name']->values;
    $client_Email = $drip_campaign_item_ID->fields['email-address']->values;
    $path_to_file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Template Emails/Thank You Drip Email.html';
    $thank_you_html = file_get_contents($path_to_file);

    if($drip_Stage == "Thank You Email" && $meeting_Set == "No"){
//key-11365fda6b34172b1185ec4804714680

        $payloadJSON = '{
                  "template": "",           // (Optional) Email template name if any.
                  "template_id": 0,         // (Optional) Email template ID if any.
                  "to": [
                    {
                      "name": "Matt Ivie",
                      "email": "mivie@techego.com"
                    }
                  ],
                  "cc": [
                    {
                      "name": "",
                      "email": ""
                    }
                  ],
                  "bcc": [
                    {
                      "name": "",
                      "email": ""
                    }
                  ],
                  "subject": "TECHeGO",
                  "body_text": "",
                  "body_html": '.$thank_you_html.',
                  "from_name": "TECHeGO Support",
                  "from_email": "support@techego.com",
                  "reply_to_name": "",
                  "reply_to_email": ""
                }';

        $payload = json_decode($payloadJSON);

        $result = $platform['api']->post->__invoke("techego_mailgun", $payload);



    }

    if($drip_Stage == "Personal Introduction" && $meeting_Set == "No"){

    }

    if($drip_Stage == "1 Business Day after Intro" && $meeting_Set == "No"){

    }

    if($drip_Stage == "72 Hour Bump" && $meeting_Set == "No"){

    }

}//end drip_campaign_send_email

function drip_campaign_send_email($item_id){


    $drip_campaign_item_ID = PodioItem::get($item_id);
    $drip_Stage = $drip_campaign_item_ID->fields['stage']->values[0]['text'];
    $drip_Status = $drip_campaign_item_ID->fields['status']->values;
    $meeting_Set = $drip_campaign_item_ID->fields['meeting-set']->values[0]['text'];
    $client_Name = $drip_campaign_item_ID->fields['name']->values;
    $client_Email = $drip_campaign_item_ID->fields['email-address']->values;
    $path_to_file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Template Emails/Thank You Drip Email.html';
    $thank_you_html = file_get_contents($path_to_file);

    if($drip_Stage == "Thank You Email" && $meeting_Set == "No"){
//key-11365fda6b34172b1185ec4804714680


        //$mgClient = new Mailgun('key-11365fda6b34172b1185ec4804714680');

        $client = new Guzzle\Http\Client();
        $mgClient = new \Mailgun\Mailgun('key-11365fda6b34172b1185ec4804714680', $client);

        $domain = "mailgun.techego.com";


        $email_result = $mgClient->sendMessage($domain, array(
            'from'    => 'TECHeGO Support <support@techego.com>',
            'to'      => $client_Name.' <'.'irobertson@techego.com'.'>',
            'subject' => 'TECHeGO',
            'html'    => $thank_you_html,
        ));
    }

    if($drip_Stage == "Personal Introduction" && $meeting_Set == "No"){

    }

    if($drip_Stage == "1 Business Day after Intro" && $meeting_Set == "No"){

    }

    if($drip_Stage == "72 Hour Bump" && $meeting_Set == "No"){

    }

}//end drip_campaign_send_email

function client_onboarding($item_id){

    $item = PodioItem::get($item_id);

    $companyName = $item->fields['company-name-in-podio']->values;

    $invoiceEmail = $item->fields['email-test']->values;

    $leadStatus = $item->fields['status']->values;

    //if($leadStatus != 'ENGAGED'){
    //    throw new Exception('Lead Status Not "ENGAGED". Call ended.');
    //}

    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $day = date_format($todaysDate, "j");
    $year = date_format($todaysDate, "Y");

    if((int)$day > 1 && (int)$day < 15){
        $day = '15';

        $filterDateText = date_create($year."-".$month."-".$day);
        $filterDate = date_format($filterDateText,"F j, Y");
    }
    elseif((int)$day > 15){
        $filterDate = date_format($todaysDate, "F t, Y");
    }

    //Filter for Period
    $periodFilter = PodioItem::filter( 15555787, $attributes = array("filters"=>array('cycle'=>$filterDate)), $options = array() );

    $periodItemID = $periodFilter[0]->item_id;

//Duplicate Checking

    //Check for Client item
    $clientCheck = PodioItem::filter( 13940709, array("filters"=>array('company2'=>array((int)$item_id))));

    $clientCount = $clientCheck->filtered;

    if($clientCount != 0){
        $clientItemID = $clientCheck[0]->item_id;
    }

    //Check for Project
    $projectCheck = PodioItem::filter( 3848224, array('filters'=>array('company2'=>array((int)$clientItemID))));

    $projectCount = $projectCheck->filtered;

    if($projectCount == 1){
        $projectItemId = $projectCheck[0]->item_id;
    }
    elseif($projectCount > 1){
        $projectItemId = array();
        foreach($projectCheck as $item){
            array_push($projectItemId, $item->item_id);
        }
    };

    //Check for Client Workspace Info
    $spaceInfoCheck = PodioItem::filter( 13941091, array('filters'=>array('client-name'=>$companyName)));

    $spaceInfoCount = $spaceInfoCheck->filtered;

//Create Missing Items

    //Create Client if Missing
    if(!$clientItemID){

        $fieldsArray = array(
            'fields'=>array(
                'title'=>$companyName,
                'company2'=>array(
                    (int)$item_id
                )
            )
        );

        $newClient = PodioItem::create( 13940709, $fieldsArray);

        $clientItemID = $newClient->item_id;
    }

    //Create Project if Missing
    if(!$projectItemId){

        $fieldsArray2 = array(
            'fields'=>array(
                'project-name'=>$companyName,
                'company2'=>array(
                    (int)$clientItemID
                ),
                'invoicing-email'=>$invoiceEmail,
                'project-manager-2'=>array(
                    124038313
                )
            )
        );

        $newProject = PodioItem::create( 3848224, $fieldsArray2);

        $projectItemId = $newProject->item_id;

        $fieldsArray3 = array(
            'fields'=>array(
                'status-2'=>1,
                'period'=>array(
                    (int)$periodItemID
                ),
                'client'=>$clientItemID,
                'project'=>$projectItemId,
                'billable'=>1
            )
        );

        $newBillingCycle = PodioItem::create( 4481866, $fieldsArray3);

        $newBillingCycle = $newBillingCycle->item_id;
    }

//Create Client Workspace Section

    //Create the Space if it doesn't exist

    //Make Company Name into External ID format
    //Lower case Company Name
    $externalIdFormat = strtolower($companyName);
    //Make alphanumeric (removes all other characters)
    $externalIdFormat = preg_replace("/[^a-z0-9_\s-]/", "", $externalIdFormat);
    //Clean up multiple dashes or whitespaces
    $externalIdFormat = preg_replace("/[\s-]+/", " ", $externalIdFormat);
    //Convert whitespaces and underscore to dash
    $externalIdFormat = preg_replace("/[\s_]/", "-", $externalIdFormat);



    try{

        //Check for existing space
        $spaceCheck = PodioSpace::get_for_url(array('url'=>'https://podio.com/techego/p-'.$externalIdFormat));


    }
    catch(Exception $e){

        $newClientSpace = PodioSpace::create( array('org_id'=>10685,'privacy'=>'open','name'=>'P - '.$companyName));

        $newClientSpaceID = $newClientSpace['space_id'];

        $newClientSpaceLink = $newClientSpace['url'];

    }

    if($spaceCheck){
        Throw new Exception('Client already has a P - Space Generated. Cancelling call.');
    }


    //Update Client Item with Space ID
    PodioItem::update($clientItemID, array('fields'=>array('workspace-id'=>(string)$newClientSpaceID)));

    //Get Template Client Space Apps
    $templateApps = PodioApp::get_for_space(3970804);

    $templateMembers = PodioSpaceMember::get_all(3970804);

    $memberIDs = "";

    foreach($templateMembers as $member){
        $memberIDs.=$member->user->user_id.',';
    }

    $memberIDs = rtrim($memberIDs, ",");

    //add members from Template to new Client Space - commented out for now as the Trust Level on the API key will not allow for this
    //PodioSpaceMember::add($newClientSpaceID, array('role'=>'admin','users'=>$memberIDs));

    //loop template apps and add them to new space (
    foreach($templateApps as $app){

        $newAppID = PodioApp::install($app->app_id, array('space_id' => $newClientSpaceID));

        //$insertJSONPayload = json_decode('{"automation":"insertJSON","app_id":'.$newAppID.'}');
//"api_key":"b756519370386bbf9e43b044ada92a44662a77a13febaefc7faf8fc9760d6b51",
        //$installJSON = platform.api.get("dashboard", $insertJSONPayload);

        // Create a client with a base URI
        $client = new GuzzleHttp\Client();
        // Send a request to DF service

        $insertJSON = $client->get('http://hoist.thatapp.io/api/v2/dashboard?api_key=b756519370386bbf9e43b044ada92a44662a77a13febaefc7faf8fc9760d6b51&automation=insertJSON&app_id='.$newAppID);

        switch($app->config['name']) {
            case 'Projects':
                $newProjectAppID = $newAppID;
                break;
            case 'Deliverables':
                $newDeliverablesAppID = $newAppID;
                break;
            case 'Help Desk':
                $newHelpDeskAppID = $newAppID;
            case 'API Outlines':
                $newApiOutlinesAppID = $newAppID;
        }
    }

    //Update Projects App with Calculation Fields

    //get Devliverables app fields
    $delivApp = PodioApp::get( $newDeliverablesAppID);
    $delivAppFields = $delivApp->fields;

    foreach($delivAppFields as $field){
        if($field->external_id == "project"){
            $delivProjectFieldID = $field->field_id;
        }
        if($field->external_id == "estimated-cost"){
            $estimatedCostFieldID = $field->field_id;
        }
        if($field->external_id == "actual-cost"){
            $actualCostFieldID = $field->field_id;
        }
        if($field->external_id == "complete"){
            $percentCompleteFieldID = $field->field_id;
        }
    }

    PodioAppField::create( $newProjectAppID, array('type'=>'calculation','config'=>array('label'=>'Total Estimated Cost','delta'=>6,'settings'=>array('script'=>'@[Sum of Estimated Cost](in_sum_'.$estimatedCostFieldID.'_'.$delivProjectFieldID.')'))));
    PodioAppField::create( $newProjectAppID, array('type'=>'calculation','config'=>array('label'=>'Total Actual Cost','delta'=>7,'settings'=>array('script'=>'@[Sum of Estimated Cost](in_sum_'.$actualCostFieldID.'_'.$delivProjectFieldID.')'))));
    PodioAppField::create( $newProjectAppID, array('type'=>'calculation','config'=>array('label'=>'% Completion','delta'=>8,'settings'=>array('script'=>'@[Avg of % Complete](in_avg_'.$percentCompleteFieldID.'_'.$delivProjectFieldID.')'))));

    //add new Client Workspace Info item if Missing otherwise add it, but add incremental number on the name
    $embedSpaceLink = PodioEmbed::create(array('url'=>$newClientSpaceLink));

    $fieldsArray4 = array(
        'fields'=>array(
            'client'=>array(
                $clientItemID
            ),
            'workspace-id'=>(string)$newClientSpaceID,
            'workspace-link'=>$embedSpaceLink->embed_id
        )
    );

    $newClientInfoItem = PodioItem::create( 13941091, $fieldsArray4);



    //add Base Project(s)
    foreach($projectItemId as $project){
        $newProjectID = PodioItem::create($newProjectAppID, array('fields'=>array('project-2'=>array((int)$project))));

        //add Base Deliverable(s)
        $fieldsArray5 = array(
            'fields'=>array(
                'title'=>'Project Administration - '.$companyName,
                'approval'=>2,
                'project'=>array(
                    (int)$newProjectID->item_id
                ),
                //can't add Seth as assigned till we can add him to the space /*'assigned-to'=>475335*/,
                'description'=>'This is the Milestone used for setting up the project, or doing anything administration related on a project level (not directly related to a Deliverable)'
            )
        );

        PodioItem::create($newDeliverablesAppID, $fieldsArray5);
    }//end foreach project

    //change Lead Status to "Project Created"
    PodioItem::update($item_id, array('fields'=>array('status'=>11)));

}//end client_onboarding function

function meeting_to_punch($item_id){

    $item = PodioItem::get($item_id);

    $meetingTitle = $item->fields['meeting-title']->values;

    $start = $item->fields['date']->start_date->format('Y-m-d H:i:s');

    $startUTC = new DateTime((string)$start, new DateTimeZone('UTC'));

    $startDate = $startUTC->setTimezone(new DateTimeZone('America/Denver'));

    $end = $item->fields['date']->end_date->format('Y-m-d H:i:s');

    $endUTC = new DateTime((string)$end, new DateTimeZone('UTC'));

    $endDate = $endUTC->setTimezone(new DateTimeZone('America/Denver'));

    $meetingDuration = $item->fields['meeting-duration']->values;

    $meetingStatus = $item->fields['meeting-status-2']->values[0]['text'];

    if($meetingStatus != 'Completed'){
        throw new Exception('Meeting is NOT Complete, cancelling call');
    }

    $milestoneItemID = $item->fields['regarding']->values[0]->item_id;

    $deliverableItemID = $item->fields['action-item']->values[0]->item_id;

    $deliverableItem = PodioItem::get($deliverableItemID);

    $projectItemID = $deliverableItem->fields['project']->values[0]->item_id;

    $billingCycleFilter = PodioItem::filter( 4481866, $attributes = array("filters"=>array('project'=>$projectItemID,'status-2'=>1)), $options = array() );

    $billingCycleItemID = $billingCycleFilter[0]->item_id;

    $billingCycleItem = PodioItem::get($billingCycleItemID);

    $billingType = $billingCycleItem->fields['billable']->values[0]['text'];

    switch ($billingType) {

        case 'Billable':
            $billingTypeID = 1;
            break;
        case 'Non-Billable':
            $billingTypeID = 2;
            break;
        case 'In House':
            $billingTypeID = 3;
            break;

    }

    $attendeesArray = array();

    $participants = $item->participants;
    foreach($participants as $key=>$value){
        if($value['status'] == "accepted"){
            array_push($attendeesArray, $key);
        }
    }

    $employeeDBArray = array();
    $timeCardArray = array();

    foreach($attendeesArray as $attendeeID){
        $employeeDBItem = PodioItem::filter( 7099698, $attributes = array("filters"=>array('employee'=>$attendeeID)), $options = array());

        $employeeDBItemID = $employeeDBItem[0]->item_id;

        array_push($employeeDBArray, $employeeDBItemID);

        $timecardItem = PodioItem::filter( 4177108, $attributes = array("filters"=>array('employee-2'=>$employeeDBItemID,'status'=>1)), $options = array());

        $timecardItemID = $timecardItem[0]->item_id;

        array_push($timeCardArray, $timecardItemID);

    }

    $punchFieldsArray = array(
        'employee-2' => $employeeDBArray,
        'pay-period' => $timeCardArray,
        'billing-cycle' => array(
            $billingCycleItemID
        ),
        'action-item' => array(
            $deliverableItemID
        ),
        'deliverable' => array(
            $milestoneItemID
        ),
        'time-in-out' => array(
            'start' => $startDate->format('Y-m-d H:i:s')
        ),
        'time-out' => array(
            'start' => $endDate->format('Y-m-d H:i:s')
        ),
        'total-duration' => array(
            (int)$meetingDuration
        ),
        'project' => array(
            $projectItemID
        ),
        'punch-billing-type' => $billingTypeID,
        'dashboard' => array(
            406828105
        ),
        'related-meeting' => array(
            (int)$item_id
        )
    );



    PodioItem::create(
        4177143,
        $attributes = array(
            'fields' => $punchFieldsArray
        ),
        $options = array()
    );



}//end meeting_to_punch function

function add_dashboard($item_id, $dashboard_item){

    PodioItem::update($item_id, array('fields'=>array('dashboard'=>(int)$dashboard_item)));

}//end add_dashboard function

function dashboard_hooker($item_id, $app_id){

    try {

        $dashboardFieldCheck = PodioAppField::get($app_id, 'dashboard');

    }
    catch(Exception $e) {

        if(strpos($e, 'No field with field_id or external_id \'dashboard\' found')) {

            $dashboardFieldCheck = PodioAppField::create(
                $app_id,
                array(
                    'type' => 'app',
                    'config' => array(
                        'label' => 'Dashboard',
                        'delta' => 99,
                        'settings' => array(
                            'referenced_apps' => array(
                                array(
                                    'app_id' => (int)$app_id
                                )
                            )
                        )
                    )
                )
            );
        }
    }

    if($dashboardFieldCheck){

        PodioHook::create( 'app', $app_id, array('url'=>'http://hoist.thatapp.io/podio_catcher.php?service=techego&automation=add-dashboard&dashboard_item='.$item_id,'type'=>'item.create'));

    }

}//end dashboard_hooker function

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
    $periodFilter = PodioItem::filter( 15555787, $attributes = array("filters"=>array('cycle'=>$filterDate), 'limit'=>500), $options = array() );

    $periodItem = $periodFilter[0]->item_id;

    if(!$periodItem)
        throw new Exception('Could not find Period Item for date: '.$filterDate);

    //Filter for Previous Timecards - Change to Archived
    $previousTimecards = PodioItem::filter_by_view(4177108, 28999192, array('limit'=>500));

    foreach($previousTimecards as $timecard){

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields'=>array(
                'status'=>2,
                'audit-status'=>1
            )
        ));

    }

    //Filter for Current Timecards - Change to Previous
    $currentTimecards = PodioItem::filter_by_view(4177108, 24119090, array('limit'=>500));

    foreach($currentTimecards as $timecard){

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields'=>array(
                'status'=>4,
                'audit-status'=>4
            )
        ));

    }

    //Filter for Active Employees
    $activeEmployees = PodioItem::filter_by_view( 7099698, 22882436, array('limit'=>500) );

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

    //Filter for Previous Billing Cycle - Change to Archived
    $previousCycles = PodioItem::filter_by_view(4481866, 28999246, array('limit'=>500));

    foreach($previousCycles as $cycle){

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields'=>array(
                'status-2'=>2
            )
        ));
    }

    //Filter for Active Billing Cycle - Change to Previous
    $previousCycles = PodioItem::filter_by_view(4481866, 5990459, array('limit'=>500));

    foreach($previousCycles as $cycle){

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields'=>array(
                'status-2'=>4
            )
        ));
    }

    //Filter for Active Projects
    $activeProjects = PodioItem::filter_by_view( 3848224, 22866007, array('limit'=>500) );

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


}//end card_cycle_generator function

function card_cycle_generator_manual($current_date){

    if($current_date == null)
        throw new Exception('Missing Required Parameter: Current Date');


    $todaysDate = date_create("now");
    $month = date_format($todaysDate, "F");
    $day = date_format($todaysDate, "j");
    $year = date_format($todaysDate, "Y");



    // if($day == '16'){

    //     date_add($todaysDate, date_interval_create_from_date_string('1 month'));
    //     date_sub($todaysDate,date_interval_create_from_date_string("16 days"));

    // }

    // if($day == '1'){

    //     date_add($todaysDate, date_interval_create_from_date_string('14 days'));

    // }

    // if($day != '1' && $day != '16'){
    //     throw new Exception('Not the 1st or the 16th. Run the call manually to create Timecards and Billing Cycles if not a scheduled run.');
    // }

    // $filterDate = date_format($todaysDate, "F j, Y");

    //Manual Filter Date for Manual runs (when the scheduled doesnt work etc.) Uncomment to use this instead of going off the scheduled date
    $manualDate = date_create("2016-07-31");
    $filterDate = date_format($manualDate, "F j, Y");

    //Filter for Period
    $periodFilter = PodioItem::filter( 15555787, $attributes = array("filters"=>array('cycle'=>$filterDate), 'limit'=>500), $options = array() );

    $periodItem = $periodFilter[0]->item_id;

    if(!$periodItem)
        throw new Exception('Could not find Period Item for date: '.$filterDate);

    //Filter for Previous Timecards - Change to Archived
    $previousTimecards = PodioItem::filter_by_view(4177108, 28999192, array('limit'=>500));

    foreach($previousTimecards as $timecard){

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields'=>array(
                'status'=>2,
                'audit-status'=>1
            )
        ));

    }

    //Filter for Current Timecards - Change to Previous
    $currentTimecards = PodioItem::filter_by_view(4177108, 24119090, array('limit'=>500));

    foreach($currentTimecards as $timecard){

        $timecardItemID = $timecard->item_id;

        PodioItem::update($timecardItemID, array(
            'fields'=>array(
                'status'=>4,
                'audit-status'=>4
            )
        ));

    }

    //Filter for Active Employees
    $activeEmployees = PodioItem::filter_by_view( 7099698, 22882436, array('limit'=>500) );

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

    //Filter for Previous Billing Cycle - Change to Archived
    $previousCycles = PodioItem::filter_by_view(4481866, 28999246, array('limit'=>500));

    foreach($previousCycles as $cycle){

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields'=>array(
                'status-2'=>2
            )
        ));
    }

    //Filter for Active Billing Cycle - Change to Previous
    $previousCycles = PodioItem::filter_by_view(4481866, 5990459, array('limit'=>500));

    foreach($previousCycles as $cycle){

        $cycleItemID = $cycle->item_id;

        PodioItem::update($cycleItemID, array(
            'fields'=>array(
                'status-2'=>4
            )
        ));
    }

    //Filter for Active Projects
    $activeProjects = PodioItem::filter_by_view( 3848224, 22866007, array('limit'=>500) );

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


}//end card_cycle_generator_manual function

function pm_time_puncher($item_id, $revision_id){

    $previousRevisionID = $revision_id - 1;

    $revisionDifference = PodioItemDiff::get_for( $item_id, $previousRevisionID, $revision_id );

    $revisionToVal = $revisionDifference[0]->to[0]['value']['text'];

    if($revisionToVal != 'Punch In' && $revisionToVal != 'Punch Out'){
        throw new Exception('Trigger not Punch In or Punch Out');
    }

    $item = PodioItem::get($item_id);

    $deliverableItemID = $item->item_id;

    $relatedProject = $item->fields['project']->values;
    foreach($relatedProject as $app){
        $projectItemID = $app->item_id;
    }

    $approvalStatusCat = $item->fields['approval-status']->values;
    foreach($approvalStatusCat as $option){
        $approvalStatusVal = $option['text'];
    }

    $statusCat = $item->fields['status']->values;
    foreach($statusCat as $option){
        $statusVal = $option['text'];
    }

    $billingTypeCat = $item->fields['billing-type']->values;
    foreach($billingTypeCat as $option){
        $billingTypeVal = $option['text'];
    }

    switch ($billingTypeVal) {

        case 'Billable':
            $billingTypeNum = 1;
            break;
        case 'Non-Billable':
            $billingTypeNum = 2;
            break;
        case 'In House':
            $billingTypeNum = 3;
            break;
        case 'Duece':
            $billingTypeNum = 4;
            break;
        default:
            throw new Exception('Missing Billing Type on Deliverable');
    }

    if($approvalStatusVal != 'Work Ready' || $statusVal != 'Active'){
        throw new Exception('Deliverable is not Active and Work Ready. Could not Punch In.');
    }

    $billingCycleFilter = PodioItem::filter( 4481866, $attributes = array("filters"=>array('project'=>$projectItemID)), $options = array() );

    $billingCycleItemID = $billingCycleFilter[0]->item_id;


    $delivRevision = PodioItemRevision::get( $item_id, $revision_id );

    $triggerUserID = $delivRevision->created_by->id;

    $userContact = PodioContact::get_for_user( $triggerUserID );

    $triggerUserProfileID = $userContact->profile_id;

    $triggerUserName = $userContact->name;

    $triggerTimeStamp = $delivRevision->created_on;

    $dateTimeStamp = new DateTime((string)$triggerTimeStamp, new DateTimeZone('America/Denver'));

    $podioFormatTimeStamp = $dateTimeStamp->format("Y-m-d H:i:s");


    $employeeDBFilter = PodioItem::filter( 7099698, $attributes = array("filters"=>array('employee'=>$triggerUserProfileID)), $options = array() );

    $employeeDBItemID = $employeeDBFilter[0]->item_id;


    $employeeTimeCard = PodioItem::filter( 4177108, $attributes = array("filters"=>array('employee-2'=>$employeeDBItemID,'status'=>array(1))), $options = array() );

    $timeCardItemID = $employeeTimeCard[0]->item_id;


    $timeCardItem = PodioItem::get($timeCardItemID);

    $relatedMilestone = $timeCardItem->fields['milestone']->values;
    foreach($relatedMilestone as $app){
        $milestoneItemID = $app->item_id;
    }

    $currentPunch = PodioItem::filter( 4177143, $attributes = array("filters"=>array('employee-2'=>$employeeDBItemID,'action-item'=>$deliverableItemID,'status'=>'Working')), $options = array() );

    if($revisionToVal == 'Punch In') {

        if(!$milestoneItemID){
            PodioComment::create('item', $timeCardItemID, array('value' => 'Punch Error: No Milestone on Timecard'));

            throw new Exception('Punch Error: No Milestone on Timecard');
        }

        foreach($currentPunch as $punch){

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in-out']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));

            $timeInDateFormat = $timeInDate->format('Y-m-d H:i:s');

            PodioItem::update(
                $punchID,
                $attributes = array(
                    'fields' => array(
                        'time-out' => $timeInDateFormat
                    )
                ),
                $options = array(
                    'hook' => false
                )
            );

        }

        $punchFieldsArray = array(
            'employee-2' => array(
                $employeeDBItemID
            ),
            'pay-period' => array(
                $timeCardItemID
            ),
            'billing-cycle' => array(
                $billingCycleItemID
            ),
            'action-item' => array(
                $deliverableItemID
            ),
            'deliverable' => array(
                $milestoneItemID
            ),
            'time-in-out' => array(
                'start' => $podioFormatTimeStamp
            ),
            'project' => array(
                $projectItemID
            ),
            'punch-billing-type' => $billingTypeNum,
            'dashboard' => array(
                406828105
            )
        );

        PodioItem::create(
            4177143,
            $attributes = array(
                'fields' => $punchFieldsArray
            ),
            $options = array()
        );

        PodioItem::update(
            $timeCardItemID,
            $attributes = array(
                'fields' => array(
                    'milestone'=>array()
                )
            ),
            $options = array(
                'hook' => false
            )
        );

        PodioItem::update(
            $item_id,
            $attributes = array(
                'fields' => array(
                    'time-clock'=>2
                )
            ),
            $options = array(
                'hook' => false
            )
        );


    }

    if($revisionToVal == 'Punch Out'){

        foreach($currentPunch as $punch) {

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in-out']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));



            $totalDuration = $timeInDate->diff($dateTimeStamp);

            $totalDays = $totalDuration->d;
            $totalHours = $totalDuration->h;
            $totalMinutes = $totalDuration->i;
            $totalSeconds = $totalDuration->s;

            $totalDurationMinutes = $totalDays * 24 * 60;
            $totalDurationMinutes += $totalHours * 60;
            $totalDurationMinutes += $totalMinutes;
            $totalDurationMinutes += $totalSeconds / 60;

            if($billingTypeVal == "Billable"){
                $roundedMinutes = ceil($totalDurationMinutes / 15) * 15;

                $totalDurationSeconds = $roundedMinutes * 60;
            }
            else{
                $totalDurationSeconds = $totalDurationMinutes * 60;
            }


            PodioItem::update(
                $punchID,
                $attributes = array(
                    'fields' => array(
                        'time-out' => $podioFormatTimeStamp,
                        'total-duration' => array(
                            (int)$totalDurationSeconds
                        )
                    )
                ),
                $options = array(
                    'hook' => false
                )
            );
        }

        PodioItem::update(
            $item_id,
            $attributes = array(
                'fields' => array(
                    'time-clock'=>4
                )
            ),
            $options = array(
                'hook' => false
            )
        );

    }



}//end pm_time_puncher function

function dev_time_puncher($item_id, $revision_id){

    $previousRevisionID = (int)$revision_id - 1;

    $revisionDifference = PodioItemDiff::get_for( $item_id, $previousRevisionID, $revision_id );

    $revisionToVal = $revisionDifference[0]->to[0]['value']['text'];

    if($revisionToVal != 'Punch In' && $revisionToVal != 'Punch Out'){
        throw new Exception('Trigger not Punch In or Punch Out');
    }

    $item = PodioItem::get($item_id);

    $issueItemID = $item->item_id;

    $relatedProject = $item->fields['relationship-3']->values[0]->item_id;

    $relatedMilestone = $item->fields['milestone']->values[0]->item_id;

    //TECHeGO Current Cycle view (private to Ivie)
    $billingCycleFilter = PodioItem::filter_by_view( 4481866, 29149805);

    $billingCycleItemID = $billingCycleFilter[0]->item_id;

    $issueRevision = PodioItemRevision::get( $item_id, $revision_id );

    $triggerUserID = $issueRevision->created_by->id;

    $userContact = PodioContact::get_for_user( $triggerUserID );

    $triggerUserProfileID = $userContact->profile_id;

    $triggerUserName = $userContact->name;

    $triggerTimeStamp = $issueRevision->created_on;

    $dateTimeStamp = new DateTime((string)$triggerTimeStamp, new DateTimeZone('America/Denver'));

    $podioFormatTimeStamp = $dateTimeStamp->format("Y-m-d H:i:s");


    $employeeDBFilter = PodioItem::filter( 7099698, $attributes = array("filters"=>array('employee'=>$triggerUserProfileID)), $options = array() );

    $employeeDBItemID = $employeeDBFilter[0]->item_id;


    $devTimePuncher = PodioItem::filter( 15796397, $attributes = array("filters"=>array('employee'=>$employeeDBItemID)), $options = array() );

    $timePuncherItemID = $devTimePuncher[0]->item_id;

    $employeeTimeCard = PodioItem::filter( 4177108, $attributes = array("filters"=>array('employee-2'=>$employeeDBItemID,'status'=>array(1))), $options = array() );

    $timeCardItemID = $employeeTimeCard[0]->item_id;


    $timePuncherItem = PodioItem::get($timePuncherItemID);

    $dbMilestone = $timePuncherItem->fields['milestone-type']->values;
    foreach($dbMilestone as $app){
        $milestoneItemID = $app->item_id;
    }

    $currentPunch = PodioItem::filter( 14514304, $attributes = array("filters"=>array('employee'=>$employeeDBItemID,'issues'=>$issueItemID,'status'=>'Working')), $options = array() );

    if($revisionToVal == 'Punch In') {

        if(!$milestoneItemID){
            PodioComment::create('item', $timePuncherItemID, array('value' => 'Punch Error: No Milestone on Time Puncher'));

            throw new Exception('Punch Error: No Milestone Type on Time Puncher');
        }

        foreach($currentPunch as $punch){

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in-out']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));

            $timeInDateFormat = $timeInDate->format('Y-m-d H:i:s');

            PodioItem::update(
                $punchID,
                $attributes = array(
                    'fields' => array(
                        'time-out' => $timeInDateFormat
                    )
                ),
                $options = array(
                    'hook' => false
                )
            );

        }

        $punchFieldsArray = array(
            'employee' => array(
                (int)$employeeDBItemID
            ),
            'pay-period' => array(
                (int)$timeCardItemID
            ),
            '42-billing-cycle-2' => array(
                (int)$billingCycleItemID
            ),
            'issues' => array(
                (int)$item_id
            ),
            'milestone-type' => array(
                (int)$milestoneItemID
            ),
            'time-in-out' => array(
                'start' => $podioFormatTimeStamp
            ),
            'project' => array(
                (int)$relatedProject
            ),
            'milestones'=>array(
                (int)$relatedMilestone
            ),
            'employee-puncher'=>array(
                (int)$timePuncherItemID
            )
        );

        PodioItem::create(
            14514304,
            $attributes = array(
                'fields' => $punchFieldsArray
            ),
            $options = array()
        );

        PodioItem::update(
            $timePuncherItemID,
            $attributes = array(
                'fields' => array(
                    'milestone-type'=>array()
                )
            ),
            $options = array(
                'hook' => false
            )
        );

        PodioItem::update(
            $item_id,
            $attributes = array(
                'fields' => array(
                    'time-clock'=>2
                )
            ),
            $options = array(
                'hook' => false
            )
        );


    }

    if($revisionToVal == 'Punch Out'){

        foreach($currentPunch as $punch) {

            $punchID = $punch->item_id;

            $punchItem = PodioItem::get($punchID);

            $timeIn = $punchItem->fields['time-in-out']->start_date->format('Y-m-d H:i:s');

            $timeInUTC = new DateTime((string)$timeIn, new DateTimeZone('UTC'));

            $timeInDate = $timeInUTC->setTimezone(new DateTimeZone('America/Denver'));



            $totalDuration = $timeInDate->diff($dateTimeStamp);

            $totalDays = $totalDuration->d;
            $totalHours = $totalDuration->h;
            $totalMinutes = $totalDuration->i;
            $totalSeconds = $totalDuration->s;

            $totalDurationMinutes = $totalDays * 24 * 60;
            $totalDurationMinutes += $totalHours * 60;
            $totalDurationMinutes += $totalMinutes;
            $totalDurationMinutes += $totalSeconds / 60;

            $totalDurationSeconds = $totalDurationMinutes * 60;



            PodioItem::update(
                $punchID,
                $attributes = array(
                    'fields' => array(
                        'time-out' => $podioFormatTimeStamp,
                        'total-duration' => array(
                            (int)$totalDurationSeconds
                        )
                    )
                ),
                $options = array(
                    'hook' => false
                )
            );
        }

        PodioItem::update(
            $item_id,
            $attributes = array(
                'fields' => array(
                    'time-clock'=>4
                )
            ),
            $options = array(
                'hook' => false
            )
        );

    }



}//end dev_time_puncher


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
        case 'pm-time-clock':
            $item_id = $requestParams['item_id'];
            $revision_id = $requestParams['item_revision_id'];
            pm_time_puncher($item_id, $revision_id);
            break;
        case 'dev-time-clock':
            $item_id = $requestParams['item_id'];
            $revision_id = $requestParams['item_revision_id'];
            dev_time_puncher($item_id, $revision_id);
            break;
        case 'dashboard-hooker':
            $item_id = $requestParams['item_id'];
            $app_id = $requestParams['app_id'];
            dashboard_hooker($item_id, $app_id);
            $successMessage = "The hook to add the Dashboard item: ".$item_id." to app: ".$app_id." was successfully added, and the Dashboard field exists/was created.";
            break;
        case 'add-dashboard':
            $item_id = $requestParams['item_id'];
            $dashboard_item = $requestParams['dashboard_item'];
            add_dashboard($item_id, $dashboard_item);
            break;
        case 'meeting-to-punch':
            $item_id = $requestParams['item_id'];
            meeting_to_punch($item_id);
            break;
        case 'client-onboarding':
            $item_id = $requestParams['item_id'];
            client_onboarding($item_id);
            break;
        case 'drip-campaign-send-email':
            $item_id = $requestParams['item_id'];
            drip_campaign_send_email($item_id);
            break;
        case 'drip-campaign-send-email2':
            $item_id = $requestParams['item_id'];
            drip_campaign_send_email2($item_id);
            break;
        default:
            throw new Exception('Missing Required Parameter: automation');



    }

    return [
        'success' => true,
        'result' => $result,
        'test' => $periodItem,
        'message' => $successMessage
    ];


}catch(Exception $e){

    if($automation == 'pm-time-clock'){
        if($e != 'Trigger not Punch In or Punch Out') {
            PodioItem::update(
                $item_id,
                $attributes = array(
                    'fields' => array(
                        'time-clock'=>5
                    )
                ),
                $options = array(
                    'hook' => false
                )
            );
            $commentMessage = substr($e, 0, strpos($e, "' in /"));
            $commentMessage = str_replace("exception 'Exception' with message '","",$commentMessage);
            PodioComment::create('item', $item_id, array('value' => 'Error: '.$commentMessage));
        }
    }

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'message' => "techego service Error: ".$e
        ]
    ];
    return;

}


