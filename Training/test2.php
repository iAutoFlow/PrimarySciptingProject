//<?php

    // Client credentials
    $username = "podio@techego.com";
    $password = "hV91Kg$4!oJUxYZ[";

    $requestParams = $event['request']['parameters'];
    $client_key = $requestParams['client_key'];
    $client_secret = $requestParams['client_secret'];

//    $client_key = "hoistpodiolevel2";
//    $client_secret = "AwxPc41rfhJJZR8fXskKUou0SBJMRd9NqDKwjAREjk4o7BfMaQ8hYcwYMnSGkzSY";

    //$platform['api']->get->__invoke("files/rate_limit.log");

    //$testing = $event['response']['content'];

//print_r($testing);exit;

    $result = $testing;

    $result.="Client Key: ".$client_key." | Client Secret: ".$client_secret."<br>";
// Authenticate Podio
    Podio::setup($client_key, $client_secret);

    Podio::authenticate_with_password($username, $password);

    $result.="Podio::authenticate_with_password Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::get(436237677);

    $result.="PodioItem::get Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::filter(4177108);

    $result.="PodioItem::filter Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::create(8773933, array('fields'=>array('duration'=>1)));

    $result.="PodioItem::create Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    PodioItem::update(438367795, array('fields'=>array('duration'=>2)));

    $result.="PodioItem::update Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()."<br>";

    $result.="<br><br>";

    //$test = $platform['api']->put->__invoke("files/rate_limit.log", $result);

    return [
        'success' => true,
        'result' => $result,
        'test' => $test,
    ];