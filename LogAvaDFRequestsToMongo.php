<?php

//function findMongoCollection($serviceId){};
//function logAvaRequest($avaRequest){
//    $requestURL = 'ava_mongodb/_table/df_requests';
//};
//function logAvaResponse($avaResponse){
//    $requestURL = 'ava_mongodb/_table/ava_responses';
//};
//function logDreamFactoryRequest($dfRequest){
//    $requestURL = 'ava_mongodb/_table/df_requests';
//};
//function logDreamFactoryResponse($dfResponse){
//    $requestURL = 'ava_mongodb/_table/df_responses';
//};
//function createAVAMongoDBRecords($tableName, $recordDetails){
//    $requestURL = 'ava_mongodb/_table/'.$tableName;
//};


$payload = $event['request']['payload'];
$serviceId = $payload['service_id'];
$requestBody = $payload['body'];


//Hoist API Service Connection
$get = $platform['api']->get;
$post = $platform['api']->post;

$basePath = 'ava_mongodb/_table/';
$dbCollection = 'ava_requests/';
$fullURL = $basePath.$dbCollection;

$requestArray = ['resource' => array(
    'service_id' => $serviceId,
    'request_body'=> $requestBody,
)];



$newRecord = $post($fullURL, $requestArray);

dd($newRecord);

