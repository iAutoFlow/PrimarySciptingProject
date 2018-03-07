<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/12/2017
 * Time: 9:08 AM
 */


try{

    //print_r("are we here?");exit;
    //dd("are we here?");

//////////////////////////////lottery actions to dropbox

    $results = [];
//$payload = $event['request']['payload'];


    $payload= array (
        'year' => '2017',
        'month' => '05',
        'day' => '09',
        'jobName' => 'Quick Stop on First Ave',
        'referenceNumber' => '61552',
        'tablet' => 'CheungL L40-6466508918',
    );

//  dd($payload);
// logger()->error("here we go buddy");
// logger()->error($payload);

//function send file to DropBox
    function sendFileToDropBox($oauth, $localPath, $dropBoxPath, $imageStream) {
        //save file to local server
        file_put_contents($localPath, base64_decode($imageStream));
        $guzzle = new \GuzzleHttp\Client();
        $params["headers"] = $oauth->getConnection()->getHeader();
        $params["body"] = file_get_contents($localPath);
        //sending file to dropbox
        $response = $guzzle->request("put", "https://content.dropboxapi.com/1/files_put/auto/" . $dropBoxPath . "?param=val", $params);
        //delete file from local server
        unlink($localPath);
    }

//function for sending SOAP request to XORA API
    function sendSOAPRequest($credentials, $serviceUrl, $functionName, $body) {
        $xmlPostString = '<?xml version="1.0" encoding="utf-8"?>'.
            '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'.
            '<soap:Body>'.
            '<'.$functionName.' xmlns="'.$serviceUrl.'">'.
            $body.
            '</'.$functionName.'>'.
            '</soap:Body>'.
            '</soap:Envelope>';
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ".$serviceUrl,
            "Content-length: ".strlen($xmlPostString),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $serviceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $credentials["username"].":".$credentials["password"]);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1800);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlPostString); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $responseXML = curl_exec($ch);
        if (strlen($responseXML)) {
            return simplexml_load_string($responseXML);
        } else {
            logger()->error('curl error');
            logger()->error(curl_error($ch));
            return null;
        }
    }

    $serviceUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl"; // asmx URL of WSDL
    $credentials = array(

        "username" => "NYLOTTERYWEB@3016524",
        "password" => "Lottery"

    );

    $xoraArrJobs = array();

    $body = '<referenceNumber>'.$payload['referenceNumber'].'</referenceNumber>';

    // dd($body);

    $jobXml = sendSOAPRequest($credentials, $serviceUrl, "getJobByReferenceNumber", $body);

//dd($jobXml->asXML());

    $countPhotos = 1;

    $actionsArray = $jobXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl")->getJobByReferenceNumberResponse->getJobByReferenceNumberReturn;

    //dd($actionsArray->asXML());

// logger()->error($actionsArray->asXML());


//  $printFields = ['fields' => [

// 													'source' => "Xora",
// 													'commenttext' => $actionsArray->asXML()

// 											]];

// $theThing = PodioItem::create(18545529,$printFields);



    while (list($jobsReturn, $valueJobs) = each($actionsArray)) {
        //dd("first while");

//   logger()->error("first while");
        foreach ($valueJobs as $job) {

            //	dd("first foreach");
// logger()->error("first foreach");
// logger()->error($job);
// logger()->error(($job)->asXML());
//         $jobActionsArray = $job;


//         while (list($actions, $valueActions) = each($jobActionsArray)) {
// logger()->error("second while");$p
//             foreach ($valueActions as $action) {
//               logger()->error("second foreach");


            $currentJob = array();
            $currentJob["year"] = $payload['year'];
            $currentJob["month"] = $payload['month'];
            $currentJob["day"] = $payload['day'];
            $currentJob["jobName"] = $payload['jobName'];
            $currentJob["tablet"] = $payload['tablet'];

            $yearA = $payload['year'];
            $monthA = $payload['month'];
            $dayA = $payload['day'];

            $photoDate = $monthA."-".$dayA."-".$yearA;




            while (list($forms, $formValues) = each($job->form->formData)) {
                //	dd("second while");
//                   logger()->error("3rd while");

                //	dd($formValues);
                foreach ($formValues as $formValue) {
                    //		dd("foreach formvalues");
//                         logger()->error("one thing:".$formValue->fieldName);

                    if ( strpos($formValue->fieldValue, "image/") !== false)  {
                        $currentPhoto = array();
//fill photo information
                        $photoLocation = $action->location->address;
                        $location = $photoLocation->streetAddress . " " . $photoLocation->city . ", " .
                            $photoLocation->state . " " . $photoLocation->zip;
//e.g. "image/jpeg" and "/9j/sfsdfsdfsdfwfq3243e..."
                        list($fullMimeType, $imageStream) = explode('|', $formValue->fieldValue, 2);
//e.g. image and jpeg
                        list($imageType, $imageExtension) = explode('/', $fullMimeType, 2);
                        $currentPhoto["localPath"] = "/tmp/" . $countPhotos . "." . $imageExtension;
                        $currentPhoto["photoName"] = $currentJob["jobName"] . "_" . $photoDate . "_" . $location;
                        $currentPhoto["extension"] = $imageExtension;
                        $currentPhoto["imageStream"] = $imageStream;

                        //	dd($currentPhoto);


                        $countPhotos++;
                        if($currentPhoto) {
                            $currentJob["photos"][] = $currentPhoto;
                        }
                    }
                }
                break;
//                 }
//             }

//             break;

            }
            //	dd($countPhotos);

            $xoraArrJobs[] = $currentJob;
        }
//	dd("no foreach");

        break;
    }
    //dd("no nothing");

// logger()->error("hai kirk");
// logger()->error($countPhotos);
// logger()->error("current Job photos Array count");
// logger()->error(count($xoraArrJobs));

// $event['response']['code'] = 200;

// return;


//////////////////finish xora////////////////

//////////////////////////////////Dropbox//////////
////////////////


    $rootFolder = "/AVA pictures2";

    $oauth = new \DreamFactory\Models\Oauth\OauthFactory('dropbox', 165);
    $http = new \DreamFactory\Models\AvaHttp($oauth);

    $newPath = $rootFolder . "/" . $currentJob["year"] . "/" . $currentJob["month"] . "/" . $currentJob["day"] . "/" . $currentJob["tablet"];

//create folders aren't existed
    foreach ($xoraArrJobs as $job) {
//     logger()->error($rootFolder . "/" . $job["year"]);
        $requestParams = [];
        $requestParams["root"] = "dropbox";
        //check on existing year


        $photoIndex = 1;
        foreach ($job["photos"] as $photo) {
            sleep(10);

            //put files to dropbox folders
            sendFileToDropBox($oauth, $newPath . "/" .
                $photo["photoName"] . "_Photo " . $photoIndex . "." . $photo["extension"], $photo["imageStream"]);
            $photoIndex++;
        }
    }
















// $oauth = new \DreamFactory\Models\Oauth\OauthFactory('dropbox', 165);
// $http = new \DreamFactory\Models\AvaHttp($oauth);

// //get all folders in DropBox folder
// $rootFolder = "/AVA pictures";
// $http->sendRequest("get", "http://api.dropbox.com/1/metadata/auto" . $rootFolder, null);
// $metaDataYear = $http->getResponseBody();
// $folders = array();
// foreach ($metaDataYear["contents"] as $year) {
//     if ($year["is_dir"]) {
//         //get all years
//         $folders[] = $year["path"];

//         $http->sendRequest("get", "http://api.dropbox.com/1/metadata/auto" . $year["path"], null);
//         $metaDataMonth = $http->getResponseBody();
//         foreach ($metaDataMonth["contents"] as $month) {
//             if ($month["is_dir"]) {
//                 //get all months
//                 $folders[] = $month["path"];

//                 $http->sendRequest("get", "http://api.dropbox.com/1/metadata/auto" . $month["path"], null);
//                 $metaDataDay = $http->getResponseBody();
//                 foreach ($metaDataDay["contents"] as $day) {
//                     if ($day["is_dir"]) {
//                         //get all days
//                         $folders[] = $day["path"];

//                         $http->sendRequest("get", "http://api.dropbox.com/1/metadata/auto" . $day["path"], null);
//                         $metaDataTablet = $http->getResponseBody();
//                         foreach ($metaDataTablet["contents"] as $tablet) {
//                             if ($tablet["is_dir"]) {
//                                 //get all tablets
//                                 $folders[] = $tablet["path"];
//                             }
//                         }
//                     }
//                 }
//             }
//         }
//     }
// }

//create folders aren't existed
// foreach ($xoraArrJobs as $job) {
// //     logger()->error($rootFolder . "/" . $job["year"]);
//     $requestParams = [];
//     $requestParams["root"] = "dropbox";
//     //check on existing year
//     if (!in_array($rootFolder . "/" . $job["year"], $folders)) {
//         $requestParams["path"] = $rootFolder . "/" . $job["year"];
//         $http->sendRequest("post", "http://api.dropbox.com/1/fileops/create_folder", $requestParams);
//         $folders[] = $requestParams["path"];
//     }
//     //check on existing month
//     if (!in_array($rootFolder . "/" . $job["year"] . "/" . $job["month"], $folders)) {
//         $requestParams["path"] = $rootFolder . "/" . $job["year"] . "/" . $job["month"];
//         $http->sendRequest("post", "http://api.dropbox.com/1/fileops/create_folder", $requestParams);
//         $folders[] = $requestParams["path"];
//     }
//     //check on existing day
//     if (!in_array($rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"], $folders)) {
//         $requestParams["path"] = $rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"];
//         $http->sendRequest("post", "http://api.dropbox.com/1/fileops/create_folder", $requestParams);
//         $folders[] = $requestParams["path"];
//     }
//     //check on existing tablet
//     if (!in_array($rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"] . "/" . $job["tablet"], $folders)) {
//         $requestParams["path"] = $rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"] . "/" . $job["tablet"];
//         $http->sendRequest("post", "http://api.dropbox.com/1/fileops/create_folder", $requestParams);
//         $folders[] = $requestParams["path"];
//     }





//     $photoIndex = 1;
//     foreach ($job["photos"] as $photo) {
//       sleep(30);

//         //put files to dropbox folders
//         sendFileToDropBox($oauth, $photo["localPath"],  $rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"] . "/" . $job["tablet"] . "/" .
//             $photo["photoName"] . "_Photo " . $photoIndex . "." . $photo["extension"], $photo["imageStream"]);
//         $photoIndex++;
//     }
// }


    return $photoIndex;
//die;
//BLOCKLY END CODE//BLOCKLY END CODE

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

