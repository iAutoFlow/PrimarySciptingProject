//<?php
$requestParams = $event['request']['parameters'];
$automationTrigger = $requestParams['automation'];



if($automationTrigger == "wufoo-links"){
    $api = $platform['api'];
    $post = $api->post;

//test hook log
    $params = '{"resource":[{"error":"Call Run", "source":"Ingram Micro - Wufoo Links Automation"}]}';

    $payload = json_decode($params, true);

//$mysqlPost = $post('mysql/_table/error_log', $payload);



    try{
// Client credentials
        $username = "podio@techego.com";
        $password = "hV91Kg$4!oJUxYZ[";
        $client_key = 'dreamfactory-ebqqb5';
        $client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';


// Authenticate Podio
        Podio::setup($client_key, $client_secret);

        Podio::authenticate_with_password($username, $password);


//Get trigger item
        $item_id = $requestParams['item_id'];

        $item = PodioItem::get($item_id);
        $triggerAppID = $item->app->app_id;

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
            throw new Exception('Send Materials Collection Email field not set to "Generate Pre-Filled Wufoo Link".');
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

        $executionTeam = $deliverableItem->fields['sku-type-team']->values;


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
};//end wufoo-links