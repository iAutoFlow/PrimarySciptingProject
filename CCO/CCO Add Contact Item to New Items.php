<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 5:08 PM
 */


try{

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    $appID = $requestParams['app_id'];


//CODE HERE


    $item = PodioItem::get($itemID);

//get Email Address & Name of Submitter

    $submitterEmail = $item->fields['email-address']->values;
    $submitterName = $item->fields['submitted-by']->values;

//Filter Contacts App by Email and return the Contacts Item ID

    $contactsfilter = PodioItem::filter(15833948,$attributes = array("filters"=>array('email-address'=>$submittedBYEMAIL)), $options = array());
    $contactitemID = $contactsfilter[0]->item_id;

//if New Help Desk Ticket Item
    if($appID == 15817104){
        $submitterEmail = $item->fields['submitters-email-2']->values;
    }

//if New Requested Topic Item
    elseif($appID == 16111838){
        $submitterEmail = $item->fields['email-address']->values;
    }

//filter Contact Items by Email Address
    $contactsfilter = PodioItem::filter(14660191,$attributes = array("filters"=>array('email-address'=>$submitterEmail)), $options = array(''));
    $contactitemID = $contactsfilter[0]->item_id;

//update Trigger Item

    if($appID == 15817104){
        PodioItem::update($itemID, array(
            'fields'=>array(
                'submitters-email-2' => array(
                    'value' => (int)$contactitemID
                )
            )
        ));
    }

    elseif($appID == 16111838){
        PodioItem::update($itemID, array(
            'fields'=>array(
                'submitted-by-2' => array(
                    'value' => (int)$contactitemID
                )
            )
        ));
    };

}