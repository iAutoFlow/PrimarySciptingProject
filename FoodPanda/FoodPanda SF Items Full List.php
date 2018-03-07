<?php

try {

///AUTOMATION START


    $country = $event['request']['parameters']['country'];

    $TableType = "Account";
    $APIKey = '&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634';

    $curl = new \Curl\Curl();
    $offset = 0;

    $BaseURL = "https://hoist.thatapp.io/api/v2/fpsalesforce/_table/Account";
    $Fields =   "?fields=Id%2CName%2COwnerId%2Cvendor_code_unique__c%2CStreet_Name__c%2CCity__c%2CCountry__c%2CBillingLatitude%2CBillingLongitude%2CLastModifiedDate&filter=Country__c%3D'$country'&offset=0&include_count=true&include_schema=false&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";//

    $urlString = $BaseURL.$Fields;

    $curl = $curl->get($urlString);
    $firstResponse = $curl->resource;
    //$result = $firstResponse;
    curl_close($curl);

    $curl = new \Curl\Curl();

    $offset=2000;
    $Fields = "?fields=Id%2CName%2COwnerId%2Cvendor_code_unique__c%2CStreet_Name__c%2CCity__c%2CCountry__c%2CBillingLatitude%2CBillingLongitude&filter=Country__c%3D'$country'&offset=$offset&include_count=true&include_schema=false&api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634";//
    $urlString = $BaseURL.$Fields;

    $curl = $curl->get($urlString);
    $secondResponse = $curl->resource;

    curl_close($curl);

    foreach($secondResponse as $insertMe){

        array_push($firstResponse, $insertMe);
    }

    $result = $firstResponse;



//END AUTOMATION
    return $result;


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

?>