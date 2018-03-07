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



    return [
        'success' => true,
        'result' => $result
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

}