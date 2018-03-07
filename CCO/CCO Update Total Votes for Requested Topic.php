<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/24/2016
 * Time: 5:27 PM
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

    $votingID = 3427451;

//Get Trigger Item Values

    $CDRequestItem = PodioItem::get($itemID);
    $RequestSource = $CDRequestItem->fields['source']->values[0]['text'];
    $TriggerValue = $CDRequestItem->fields['field-3']->values[0]['text'];

    //if TriggerValue = "Update Star Rating of Request" || $TriggerValue == "Update # of Votes in Favor of Request" filter the Requested Webinar items in the Club Workspace

    if($TriggerValue == "Update Star Rating of Request"){
        $filterClubRequests = PodioItem::filter(15778042, $attributes = array("filters"=>array('requested-topic-submission-item'=>array((int)$itemID))), $options = array());
        $RequestITEMID = $filterClubRequests[0]->item_id;
    }

   // $CSRequestItem = PodioItem::get($RequestITEMID);
    $CSStarVoteInfo = PodioVoting::get_result_for_item($RequestITEMID, $votingID);
    $voteCount = $CSStarVoteInfo['count'];
    $voteAverage = $CSStarVoteInfo['average'];




    //Update Trigger Item with the Voting Average, and the number of Votes.

    PodioItem::update($itemID, array(
        'fields'=>array(
            'total-of-votes' =>(string)$voteCount,
            'average-star-rating-of-request' =>(string)$voteAverage,
            'field-3' => array(
                'value' => "..."
            )
        )
    ));




    return [
        'success' => true,
        'result' => $result
    ];

}catch(Exception $e) {

    $event['response'] = [

        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: " . $e,

        ]
    ];

}