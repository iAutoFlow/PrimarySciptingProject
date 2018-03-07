//<?php


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


$requestParams = $event['request']['parameters'];
$automationTrigger = $requestParams['automation'];

function scope_date_change($item_id){

    $triggerItem = PodioItem::get($item_id);

    $triggerItemID = $triggerItem->item_id;

    $triggerAppID = $triggerItem->app->app_id;

    if($triggerAppID == 10226461){

        $delivDates = $triggerItem->fields['breakout']->values;

        if($delivDates != "Yes") {

            $deliverable = PodioItem::filter(10827874, $attributes = array("filters" => array('project-scope' => $triggerItemID)), $options = array());

            $deliverableItemID = $deliverable[0]->item_id;

            $subActual = PodioItem::filter(12099103, $attributes = array("filters" => array('relationship' => $triggerItemID)), $options = array());

            $subActualItemID = $subActual[0]->item_id;
        }//end if has sub-scopes check
        else{
            throw new Exception('Scope with Sub-Scopes, End-Date changed. No run needed.');
            exit;
        }

    }//end if Scope app

    if($triggerAppID == 10411647){

        $deliverable = PodioItem::filter(10827874, $attributes = array("filters" => array('subscope' => $triggerItemID)), $options = array());

        $deliverableItemID = $deliverable[0]->item_id;

        $subActual = PodioItem::filter(12099103, $attributes = array("filters" => array('sub-scope' => $triggerItemID)), $options = array());

        $subActualItemID = $subActual[0]->item_id;

    }//end if Sub.Scope app

    //Deliverable / Job Section

    $deliverableItem = PodioItem::get($deliverableItemID);

    $teamName = $deliverableItem->fields['sku-type-team']->values;

    $endDateText = $deliverableItem->fields['end-date']->values;

    $endDate = new DateTime((string)$endDateText);

    $endDateFormat = $endDate->format('Y-m-d H:i:s');

    //Get Job related to Deliverable
    if($teamName == "Admin"){$teamJobAppID = 14269585;};
    if($teamName == "Creative"){$teamJobAppID = 13869166;};
    if($teamName == "Events"){$teamJobAppID = 14276642;};
    if($teamName == "Marketing Services"){$teamJobAppID = 14276675;};
    if($teamName == "Sales Engagement"){$teamJobAppID = 14276676;};
    if($teamName == "Non-Agency"){$teamJobAppID = 14535583;};

    $jobFilter = PodioItem::filter($teamJobAppID, array("filters" => array('deliverable' => $deliverableItemID)));

    $jobItemID = $jobFilter[0]->item_id;

    //Update Job Due Date

    PodioItem::update($jobItemID,array('fields' => array('job-due-date-2' => $endDateFormat)));


    //Sub-Actual / Breakout Section

    $breakout = PodioItem::filter(13016188, $attributes = array("filters" => array('sub-actual' => $subActualItemID)), $options = array());

    foreach($breakout as $item){
        PodioItem::delete($item->item_id);
    }



}//end scope_date_change function

function wufoo_links($item_id){

    $api = $platform['api'];
    $post = $api->post;

//test hook log
    $params = '{"resource":[{"error":"Call Run", "source":"Ingram Micro - Wufoo Links Automation"}]}';

    $payload = json_decode($params, true);

//$mysqlPost = $post('mysql/_table/error_log', $payload);



    try{

//Get trigger item

        $item = PodioItem::get($item_id);
        $triggerAppID = $item->app->app_id;

        $app = PodioApp::get($triggerAppID);

        $spaceID = $app->space_id;

        $space = PodioSpace::get($spaceID);

        $spaceName = $space->name;

        $executionTeam = str_replace("E - ", "", $spaceName);

//extract values
        $wufooLinksDB = strip_tags($item->fields['wufoo-links-db']->values);



        $relatedParentJob = $item->fields['parent-job']->values;
        foreach($relatedParentJob as $app){
            $parentJobID = $app->item_id;
        }

        $sendEmailCat = $item->fields['route-to-requestor']->values;
        foreach($sendEmailCat as $option){
            $sendEmailTrigger = $option['text'];
        }

        $triggerMC = $item->fields['materials-collection-contact']->values;
        foreach($triggerMC as $app){
            $triggerMC_Contact = $app->item_id;
        }

//End Call if SendEmailTrigger is not "Generate Pre-Filled Wufoo Link"
        if($sendEmailTrigger != "Generate Pre-Filled Wufoo Link"){
            exit;
        }


//check count of 'http' substring from the 'Wufoo Link(s) DB" field, exit if not exactly 1
        $linkHttpCount = substr_count($wufooLinksDB, 'http');

        if($linkHttpCount != 1){
            $fieldsArray = array(
                "route-to-requestor" => "Error Generating Wufoo Link",
            );

            PodioItem::update(
                $item_id,
                $attributes = array(
                    'fields' => $fieldsArray
                ),
                $options = array(
                    'hook' => false
                )
            );
            PodioComment::create('item', $item_id, array('value'=>"Error: There is not exactly one Wufoo Link in the 'Wufoo Link(s) DB' field. Pre-Filled Link could not be generated."));
            throw new Exception("There is not exactly one Wufoo Link in the 'Wufoo Link(s) DB' field");
        }


//extract form name from link
        $formName = substr($wufooLinksDB, 44);
        $formName = substr($formName, 0, -1);


//Get Parent Job item
        $parentJobItem = PodioItem::get($parentJobID);


//extract values
        $relatedDeliverable = $parentJobItem->fields['deliverable']->values;
        foreach($relatedDeliverable as $app){
            $parentJobDeliverableID = $app->item_id;
        }

        $parentJobUID = $parentJobItem->fields['unique-id']->values;


//Get Deliverable item
        $deliverableItem = PodioItem::get($parentJobDeliverableID);


//extract values
        $relatedScope = $deliverableItem->fields['project-scope']->values;
        foreach($relatedScope as $app){
            $scopeID = $app->item_id;
        }

        $relatedOpp = $deliverableItem->fields['opportunity']->values;
        foreach($relatedOpp as $app){
            $opportunityID = $app->item_id;
        }

//        $executionTeam = $deliverableItem->fields['sku-type-team']->values;


//Get Project Scope item
        $scopeItem = PodioItem::get($scopeID);


//extract values
        $scopeNotes = strip_tags($scopeItem->fields['notes']->values);

        $scopeStartDate = $scopeItem->fields['date-of-execution']->start_date->format('Y-m-d');

        $scopeEndDate = $scopeItem->fields['when-will-client-benefit-end']->start_date->format('Y-m-d');

        $scopeName = $scopeItem->fields['title-2']->values;


        $relatedProductLine = $scopeItem->fields['description']->values;
        foreach($relatedProductLine as $app){
            $productLineID = $app->item_id;
        }

        $materialsContact = $scopeItem->fields['materials-collection-contact']->values;
        foreach($materialsContact as $app){
            $materialsContactID = $app->item_id;
        }

        if(!$materialsContactID){
            $materialsContactID = $triggerMC_Contact;
        }


//Get Opportunity item
        $opportunityItem = PodioItem::get($opportunityID);

//extract values
        $opportunityUID = $opportunityItem->fields['unique-id']->values;

        $relatedPOC = $opportunityItem->fields['opportunity-primary-contact']->values;
        foreach($relatedPOC as $app){
            $clientPOC = $app->title;
        }

        $relatedClient = $opportunityItem->fields['client']->values;
        foreach($relatedClient as $app){
            $clientID = $app->item_id;
        }

        $relatedAM = $opportunityItem->fields['account-manager-2']->values;
        foreach($relatedAM as $app){
            $accountManagerID = $app->item_id;
        }


//Get Client item
        $clientItem = PodioItem::get($clientID);

//extract values
        $clientName = $clientItem->fields['title']->values;

        $relatedCSContact = $clientItem->fields['client-services-contact']->values;
        foreach($relatedCSContact as $app){
            $clientServicesContactID = $app->item_id;
        }


        if($clientServicesContactID){
//Get Client Services Contact item
            $clientServicesContactItem = PodioItem::get($clientServicesContactID);

//extract values
            $clientServicesContactName = $clientServicesContactItem->fields['title']->values;

            $clientServicesContactEmail = $clientServicesContactItem->fields['email']->values;
        }

        if($accountManagerID){
//Get Account Manager item
            $accountManagerItem = PodioItem::get($accountManagerID);

//extract values
            $accountManagerName = $accountManagerItem->fields['title']->values;

            $accountManagerEmail = $accountManagerItem->fields['email']->values;
        }

//Get Product Line item
        $productLineItem = PodioItem::get($productLineID);

//extract values
        $SKU = $productLineItem->fields['title']->values;


//get Ingram Wufoo form fields

//Authenticate with Wufoo

        $curl = curl_init('https://emarketingmaterials.wufoo.com/api/v3/forms/'.$formName.'/fields.json');        //1
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                                      //2
        curl_setopt($curl, CURLOPT_USERPWD, 'UMJZ-2VB7-FQDQ-LFDA:footastic');               //3
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);                                 //4
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Wufoo Sample Code');                         //5

        $response = curl_exec($curl);                                                       //6
        $resultStatus = curl_getinfo($curl);                                                //7


        $wufooResponse = json_decode($response);

//get field ids for form
        foreach($wufooResponse->Fields as $obj){
            if($obj->Title == "Scope / Campaign"){
                $scopeFieldID = $obj->ID;
            }
            if($obj->Title == "Scope/Campaign"){
                $scopeFieldID = $obj->ID;
            }
            if($obj->Title == "Client"){
                $clientFieldID = $obj->ID;
            }
            if($obj->Title == "Job ID"){
                $jobNumFieldID = $obj->ID;
            }
            if($obj->Title == "SKU"){
                $SKUFieldID = $obj->ID;
            }
            if($obj->Title == "OP ID #"){
                $opIDFieldID = $obj->ID;
            }
            if($obj->Title == "Scope Notes / Specs"){
                $scopeNotesFieldID = $obj->ID;
            }
            if($obj->Title == "Start Date"){
                $startDateFieldID = $obj->ID;
            }
            if($obj->Title == "Due Date"){
                $dueDateFieldID = $obj->ID;
            }
            if($obj->Title == "Client POC"){
                $clientPOCFieldID = $obj->ID;
            }
            if($obj->Title == "Account Manager"){
                $accountManagerFieldID = $obj->ID;
            }
            if($obj->Title == "Account Manager Email"){
                $accountManagerEmailFieldID = $obj->ID;
            }
            if($obj->Title == "Client Services"){
                $clientServicesFieldID = $obj->ID;
            }
            if($obj->Title == "Client Services Email"){
                $clientServicesEmailFieldID = $obj->ID;
            }
            if($obj->Title == "Execution Team"){
                $executionTeamFieldID = $obj->ID;
            }
            if($obj->Title == "Traffic Manager"){
                $trafficManagerFieldID = $obj->ID;
            }
            if($obj->Title == "DUE DATE for this materials collection"){
                $dueDate2FieldID = $obj->ID;
            }
        };


//build pre-filled link
        $prefilledLink = $wufooLinksDB."def/";



//add each field if value is not blank
        if($scopeName && $scopeFieldID)$prefilledLink .= $scopeFieldID."=".$scopeName."&";

        if($clientName && $clientFieldID)$prefilledLink .= $clientFieldID."=".$clientName."&";

        if($parentJobUID && $jobNumFieldID)$prefilledLink .= $jobNumFieldID."=".$parentJobUID."&";

        if($SKU && $SKUFieldID)$prefilledLink .= $SKUFieldID."=".$SKU."&";

        if($opportunityUID && $opIDFieldID)$prefilledLink .= $opIDFieldID."=".$opportunityUID."&";

        if($scopeNotes && $scopeNotesFieldID)$prefilledLink .= $scopeNotesFieldID."=".$scopeNotes."&";

        if($scopeStartDate && $startDateFieldID)$prefilledLink .= $startDateFieldID."=".$scopeStartDate."&";

        if($scopeEndDate && $dueDateFieldID)$prefilledLink .= $dueDateFieldID."=".$scopeEndDate."&";

        if($clientPOC && $clientPOCFieldID)$prefilledLink .= $clientPOCFieldID."=".$clientPOC."&";

        if($accountManagerName && $accountManagerFieldID)$prefilledLink .= $accountManagerFieldID."=".$accountManagerName."&";

        if($accountManagerEmail && $accountManagerEmailFieldID)$prefilledLink .= $accountManagerEmailFieldID."=".$accountManagerEmail."&";

        if($clientServicesContactName && $clientServicesFieldID)$prefilledLink .= $clientServicesFieldID."=".$clientServicesContactName."&";

        if($clientServicesContactEmail && $clientServicesEmailFieldID)$prefilledLink .= $clientServicesEmailFieldID."=".$clientServicesContactEmail."&";

        if($executionTeam && $executionTeamFieldID)$prefilledLink .= $executionTeamFieldID."=".$executionTeam."&";

        $prefilledLink = rtrim($prefilledLink, "&");

        $prefilledLink = urlencode($prefilledLink);

        //$prefilledLink = str_replace("%2F", "/", $prefilledLink);



//Shorten URL with Bitly

// Create a client with a base URI
        $client = new GuzzleHttp\Client();
// Send a request to https://api-ssl.bitly.com

        $bitlyResponse = $client->get('https://api-ssl.bitly.com/v3/shorten?access_token=4ca6fd3fb568db1fc81d87d1677c34b620b0b31a&longUrl='.$prefilledLink);
        $bitlyBody = json_decode($bitlyResponse->getBody());


        $bitlyURL = $bitlyBody->data->url;

//Update the Podio Item with the Bit.ly URL and add the Materials Collection Contact

//add params to fields array if they came in blank
        $fieldsArray = array(
            "materials-collection-wufoo-link" => $bitlyURL,
            "materials-collection-contact" => $materialsContactID,
            "route-to-requestor" => "Wufoo Link Generated",
        );




        PodioItem::update(
            $item_id,
            $attributes = array(
                'fields' => $fieldsArray
            ),
            $options = array(
                'hook' => false
            )
        );

        return [
            'success' => true,
            'result' => $result
        ];

    }catch(Exception $e){

        //make variable for MySQL Post to Error Log table for Error Catching

        $params = '{"resource":[{"error":"", "source":"Ingram Micro - Wufoo Links Automation"}]}';

        $payload = json_decode($params, true);

        $payload['resource'][0]['error'] = '"'.$e.'"';

        //$mysqlPost = $post('mysql/_table/error_log', $payload);

        $fieldsArray = array(
            "route-to-requestor" => "Error Generating Wufoo Link",
        );


        if($e != 'Send Materials Collection Email field not set to "Generate Pre-Filled Wufoo Link".'){
            PodioItem::update(
                $item_id,
                $attributes = array(
                    'fields' => $fieldsArray
                ),
                $options = array(
                    'hook' => false
                )
            );
            //PodioComment::create('item', $item_id, array('value'=>"Unknown Error, Contact Philip Warth for further assistance."));
            PodioComment::create('item', $item_id, array('value'=>$_SERVER));
        }

        $event['response'] = [
            'status_code' => 400,
            'content' => [
                'success' => false,
                'message' => "Error: ".$e
            ]
        ];
        return;


    }
};//end wufoo_links function

try{


    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"
    ));

    switch ($automationTrigger) {

        case 'wufoo-links':
            $item_id = $requestParams['item_id'];
            wufoo_links($item_id);
            break;
        case 'scope-date-change':
            $item_id = $requestParams['item_id'];
            scope_date_change($item_id);
            break;
        default:
            throw new Exception('Missing or Incorrect, Required Parameter: automationTrigger');

    }

    return [
        'success' => true,
        'result' => $result,
        'test' => $periodItem
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