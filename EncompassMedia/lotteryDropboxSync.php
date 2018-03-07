<?php

///////////////////////////////////////////////lottery Dropbox Sync

$results = [];
$tracker = "";

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
    dd(curl_getinfo($ch));

    return simplexml_load_string($responseXML);

}

$serviceUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl"; // asmx URL of WSDL
$credentials = array(

    "username" => "NYLOTTERYWEB@3016524",
    "password" => "Lottery"
);

$xoraArrJobs = array();

//time counting
$currentDate = new DateTime('now',(new DateTimeZone('America/New_York')));
$currentDate = $currentDate->format("Y-m-d H:i:s.000");
$prevDate = date("Y-m-d H:i:s.000", strtotime('-6 hours', time())); //this is really a 2 hour time-period



//attributes for getJobs function
$body = '<start>'.
    // '<dateString>2017-05-04 04:00:00.000</dateString>'.
    '<dateString>' . $prevDate . '</dateString>'.
    '</start>'.
    '<end>'.
    // '<dateString>2017-05-04 12:00:00.000</dateString>'.
    '<dateString>' . $currentDate . '</dateString>'.
    '</end>'.
    '<includeAttributes>false</includeAttributes>'.
    '<includeActions>true</includeActions>';


$jobsXml = sendSOAPRequest($credentials, $serviceUrl, "getJobs", $body);




$countPhotos = 1;
if(is_object($jobsXml)) {
    //get all jobs per day
    $tracker = "We have an object!";
    $jobsArray = $jobsXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl")->getJobsResponse;
    while (list($jobsReturn, $valueJobs) = each($jobsArray)) {

        foreach ($valueJobs as $job) {

            //reference number as jobId
            if ($job->referenceNumber) {

                //fill job information
                $currentJob = array();
                $jobUnixTime = strtotime($job->createdDateTime->dateString);
                $currentJob["year"] = date("Y", $jobUnixTime);
                $currentJob["month"] = date("m", $jobUnixTime);
                $currentJob["day"] = date("d", $jobUnixTime);
                $currentJob["jobName"] = (string)$job->description->value;
                $currentJob["referenceNumber"] = $job->referenceNumber->__toString();

                //set tablet name
                $bodyWorker = '<workerName>' . (string)$job->workerName->value . '</workerName>';
                $serviceWorkerUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl";
                $workerXml = sendSOAPRequest($credentials, $serviceWorkerUrl, "getWorkerByName", $bodyWorker);
                $newWorkerName = "";
                if(is_object($workerXml)) {
                    $workerFields = $workerXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl")->getWorkerByNameResponse->getWorkerByNameReturn;
                    $newWorker = $workerFields->firstName . " " . $workerFields->lastName;
                }
                $currentJob["tablet"] = ((strlen($newWorker) > 2) ? $newWorker : (string)$job->workerName->value);
                $currentJob["tablet"] = str_replace("/", "_", $currentJob["tablet"]);

                $xoraArrJobs[] = $currentJob;
            } else {
                print "Couldn't find job: " . $job->referenceNumber . " ";
            }
        }
        break;
    }

    $api = $platform['api'];
    $post = $api->post;

    $rackUp = 1;

    foreach($xoraArrJobs as $xoraJob) {

        $tracker = "--".$rackUp." Jobs sent to Connect";
        $rackUp++;



        $curl = new Curl\Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Accept', 'application/json');
        $url = 'https://connect.thatapp.io/api/v2/lotteryActionDelay?api_key=00dc6e60531e959cfeb116cec2c5fd38b95f67047d5a7c3f612cdab65ae550b3';
        $result = $curl->post($url, $xoraJob);

        dd($result);

    }

    print($tracker);

} else {
    throw new \Exception("There are not any jobs.");
}

exit;
