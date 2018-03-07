<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/22/2016
 * Time: 4:16 PM
 */

try{

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];


// Client credentials
    $username = "podio@techego.com";
    $password = "hV91Kg$4!oJUxYZ[";
    $client_key = 'dreamfactory-ebqqb5';
    $client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';

// Authenticate Podio
    Podio::setup($client_key, $client_secret);
    Podio::authenticate_with_password($username, $password);


//CODE HERE
//Triggered when the Status is Updated on a Request Topic Item in the Content Development Space, If Status = Reviewed & Ready for Voting,
//this automation will Create a new item in the Requested Topics app, in the workspace Indicated by the Source of the Trigger item.

    $requestitem = PodioItem::get($itemID);

//get Status & Source of Requested Topic Item,

    $requestStatus =  $requestitem->fields['status']->values[0]['text'];
    $requestSource = $requestitem->fields['source']->values[0]['text'];

// if Value = Reviewed & Ready for Voting, Create a new "Requested Webinars" item in the workspace indicated by the Source Value.

if($requestStatus == "Reviewed & Ready for Voting" && $requestSource == "Club Space Webform"){
    PodioItem::create(15778042, array(
        'fields' => array(
            'requested-topic-submission-item' => array(
                'value' => (int)$itemID))));
}




    return [
        'success' => true,
        'result' => $requestStatus
    ];



}
catch(Exception $e){

    $event['response'] = [

        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: ".$e,

        ]
    ];
    return;}