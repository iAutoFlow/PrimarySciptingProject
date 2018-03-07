<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table


class PodioSessionManager {
    private static $connection_id = 3;
    private static $connection;
    private static $appConnection;
    private static $connectedAppID;
    private static $auth_type;

    public function __construct() {
    }

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
        }
        return self::$connection;
    }

    public static function getAppConnection($app_id) {

        if(self::$connectedAppID !== $app_id) {
            self::$connectedAppID = $app_id;
            self::$appConnection = null;
        }

        if (!self::$appConnection) {
            self::$appConnection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->where('app_id', $app_id)->first();
        }

        if (!self::$appConnection) {

            $connection = self::getConnection();

            Podio::$oauth = new PodioOAuth(
                $connection->access_token,
                $connection->refresh_token
            );

            $app = PodioApp::get(Podio::$auth_type['identifier']);

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'null']);

            $newAppAuth = Podio::authenticate_with_app(Podio::$auth_type['identifier'], $app->token);

            $connection = new \EnvireTech\OauthConnector\Models\OrganizationConnection();
            $connection->name = "App_".(str_replace(" ", "_", $app->config['name']));
            $connection->app_id = $app->app_id;
            $connection->service_id = 16;
            $connection->refresh_token = Podio::$oauth->refresh_token;
            $connection->access_token = Podio::$oauth->access_token;
            $connection->organization_id = 1;
            $connection->created_by_id = 5;
            $connection->private = 0;
            $connection->save();

            self::$appConnection = $connection;

            Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
        }

        return self::$appConnection;
    }

    public static function getClientId () {
        return self::getConnection()->connectionService->config['client_id'];
    }

    public static function getClientSecret () {
        return self::getConnection()->connectionService->config['client_secret'];
    }

    public static function authtypeUserAVA(){

        Podio::$auth_type = array(
            "type" => "user",
            "identifier" => 1406952
        );

    }

    public static function authtypeApp($app_id){

        Podio::$auth_type = array(
            "type" => "app",
            "identifier" => $app_id
        );

    }

    public function get(){

        if(Podio::$auth_type['type'] == "app"){
            $connection = self::getAppConnection(Podio::$auth_type['identifier']);
        }
        else {
            $connection = self::getConnection();
        }

        return new PodioOAuth(
            $connection->access_token,
            $connection->refresh_token
        );
    }


    public function set($oauth, $auth_type = null){

        //$auth_type = self::$authtype;

        if($auth_type['type'] == "app") {
            $connection = self::getAppConnection($auth_type['identifier']);

            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;

        }
        else {
            $connection = self::getConnection();
            $connection->access_token = $oauth->access_token;
            $connection->save();
            self::$connection = $connection;
        }


    }


}

function normalAuth(){
    PodioSessionManager::authtypeUserAVA();

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}

function appAuth($app_id){
    PodioSessionManager::authtypeApp($app_id);

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);
}

// api/v2/JoshTEST?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

try{

    normalAuth();

    $payload = $event['request']['payload'];
    $type = $payload['type'];

    if($type && $type == 'hook.verify'){

        $code = $payload['code'];
        $hook_id = $payload['hook_id'];

        // Validate the webhook
        PodioHook::validate($hook_id, array('code' => $code));

    }

    $requestParams = $event['request']['parameters'];
    $item_id = (int)$requestParams['item_id'];

    if(!$item_id) {
        $item_id = (int)$payload['item_id'];
    }

    $item = PodioItem::get($item_id);

    ///PODIO ID VARIABLES
    ///AUTOMATION START

    $objectCheck = true;
    $bigArray = [];
    $filePathToDelete = [];

    // api/v2/encompassXoraManualRuns?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634&item_id=628672830
    ///AUTOMATION START///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////start XORA

    $serviceUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl"; // asmx URL of WSDL
    $credentials = array();

    $credentials["username"] = "NYLOTTERYWEB@3016524"; //  username
    $credentials["password"] = "Lottery"; // password
    $xoraArrJobs = array();


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
        if ($responseXML) {
            return simplexml_load_string($responseXML);
        } else {
            return "No dice!";
        }
    }

    $podioTimeOne = $item->fields['date1']->start;
    $podioTimeForCountUp = $podioTimeOne->format("U");
    $podioTimeOne = $podioTimeOne->format("Y-m-d H:i:s.000");

    if($item->fields['xora-trigger']->values[0]['text'] == "Run"){

        $ohandthis = PodioItem::update($item_id, array(
                'fields' => array(
                    'xora-trigger' => 'Syncing'
                )
            )
        );

        $countBack = round($item->fields['count']->values);

        do{
            $timeOffsetStart = (15 * $countBack);
            $timeOffsetEnd = (15 * $countBack) + 15;

            $startTime = date("Y-m-d H:i:s.000", strtotime("+$timeOffsetStart minutes", $podioTimeForCountUp));
            $endTime = date("Y-m-d H:i:s.000", strtotime("+$timeOffsetEnd minutes", $podioTimeForCountUp));

            // api/v2/JoshTEST?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634
            //attributes for getJobs function
            $body = '<start>'.
                //  '<dateString>2017-06-05 23:45:00.000</dateString>'.
                '<dateString>' . $startTime . '</dateString>'.
                '</start>'.
                '<end>'.
                //   '<dateString>2017-06-06 00:00:00.000</dateString>'.
                '<dateString>' . $endTime . '</dateString>'.
                '</end>'.
                '<includeAttributes>false</includeAttributes>'.
                '<includeActions>true</includeActions>';

            $jobsXml = sendSOAPRequest($credentials, $serviceUrl, "getJobs", $body);


            $countPhotos = 1;

            if(is_object($jobsXml)) {
                //get all jobs per day
                $jobsArray = $jobsXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/JobService-wrapped-0.0.5?wsdl")->getJobsResponse;

                foreach($jobsArray as $jobsReturn => $valueJobs)	{

                    foreach ($valueJobs as $job) {

                        if($job->divisionName->value == "NY Lottery"){
                            //if(preg_match('/ny(\s*|.)lot[a-z]*\b/i', $job->divisionName->value)){

                            unset($currentJob);
                            $currentJob = array();
                            $currentJob["jobType"] = $job->divisionName->value;

                            $AGAINjobUnixTime = ($job->createdDateTime->dateString);
                            if(!$AGAINjobUnixTime){

                                $AGAINjobUnixTime = ($job->dateString);
                            }


                            //$jobUnixTime = strtotime($job->createdDateTime->dateString);
                            $jobUnixTime = strtotime($AGAINjobUnixTime);

                            $currentJob["year"] = date("Y", $jobUnixTime);
                            $currentJob["month"] = date("m", $jobUnixTime);
                            $currentJob["day"] = date("d", $jobUnixTime);
                            $currentJob["jobName"] = (string)$job->description->value;

                            //set tablet name
                            $bodyWorker = '<workerName>' . (string)$job->workerName->value . '</workerName>';
                            $serviceWorkerUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl";
                            $workerXml = sendSOAPRequest($credentials, $serviceWorkerUrl, "getWorkerByName", $bodyWorker);
                            $newWorkerName = "";
                            if (is_object($workerXml)) {
                                $workerFields = $workerXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl")->getWorkerByNameResponse->getWorkerByNameReturn;
                                $newWorker = $workerFields->firstName . " " . $workerFields->lastName;
                            }

                            $currentJob["tablet"] = ((strlen($newWorker) > 2) ? $newWorker : (string)$job->workerName->value);
                            $currentJob["tablet"] = str_replace("/", "_", $currentJob["tablet"]);


                            $yearA = $currentJob['year'];
                            $monthA = $currentJob['month'];
                            $dayA = $currentJob['day'];
                            $photoDate = $monthA . "-" . $dayA . "-" . $yearA;

                            $jobActions = $job->actions;
                            $targetAction = [];
                            foreach ($jobActions as $key => $jobAction) {

                                foreach ($jobAction as $object) {
                                    if ($object->name == "Job Details") {
                                        $targetAction = $object;
                                        break;
                                    }
                                }
                            }

                            $noImageCounter = 0;
                            $formData = $targetAction->form->formData->formData;

                            foreach ($formData as $anotherKey => $formValue) {

                                $valueCheck = $formValue->fieldValue;

                                if (strpos($valueCheck, "image/") !== false){
                                    $loopCount = 0;

                                    $currentPhoto = array();
                                    $photoLocation = $targetAction->location->address;
                                    $location = $photoLocation->streetAddress . " " . $photoLocation->city . ", " .
                                        $photoLocation->state . " " . $photoLocation->zip;

                                    list($fullMimeType, $imageStream) = explode('|', $formValue->fieldValue, 2);

                                    list($imageType, $imageExtension) = explode('/', $fullMimeType, 2);
                                    $currentPhoto["localPath"] = "/tmp/" . $countPhotos . "." . $imageExtension;
                                    $currentPhoto["photoName"] = $currentJob["jobName"] . "_" . $photoDate . "_" . $location;
                                    $currentPhoto["extension"] = $imageExtension;
                                    $currentPhoto["imageStream"] = $imageStream;

                                    //////////////////////////
                                    $currentJob["photos"][] = $currentPhoto;
                                    $countPhotos++;

                                }
                                else{
                                    $noImageCounter++;
                                    continue;
                                }

                                // break;
                            }

                            $xoraArrJobs[] = $currentJob;

                        }

                        else{

                            unset($currentJob);

                            $currentJob = array();
                            $currentJob["jobType"] = $job->divisionName->value;

                            $AGAINjobUnixTime = ($job->createdDateTime->dateString);
                            if(!$AGAINjobUnixTime){

                                $AGAINjobUnixTime = ($job->dateString);
                            }

                            $jobUnixTime = strtotime($AGAINjobUnixTime);

                            $currentJob["year"] = date("Y", $jobUnixTime);
                            $currentJob["month"] = date("m", $jobUnixTime);
                            $currentJob["day"] = date("d", $jobUnixTime);
                            $currentJob["jobName"] = (string)$job->description->value;

                            //set tablet name
                            $bodyWorker = '<workerName>' . (string)$job->workerName->value . '</workerName>';
                            $serviceWorkerUrl = "http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl";
                            $workerXml = sendSOAPRequest($credentials, $serviceWorkerUrl, "getWorkerByName", $bodyWorker);
                            $newWorkerName = "";
                            if (is_object($workerXml)) {
                                $workerFields = $workerXml->children("http://schemas.xmlsoap.org/soap/envelope/")->Body->children("http://webservices.streetsmart.clicksoftware.com/services/services/CompanyAdminService-wrapped-0.0.2?wsdl")->getWorkerByNameResponse->getWorkerByNameReturn;
                                $newWorker = $workerFields->firstName . " " . $workerFields->lastName;
                            }

                            $currentJob["tablet"] = ((strlen($newWorker) > 2) ? $newWorker : (string)$job->workerName->value);
                            $currentJob["tablet"] = str_replace("/", "_", $currentJob["tablet"]);

                            $yearA = $currentJob['year'];
                            $monthA = $currentJob['month'];
                            $dayA = $currentJob['day'];
                            $photoDate = $monthA . "-" . $dayA . "-" . $yearA;

                            $jobActions = $job->actions;


                            $targetAction = [];
                            foreach ($jobActions as $key => $jobAction) {
                                foreach ($jobAction as $object) {
                                    if ($object->name == "Take Pictures") {
                                        $targetAction = $object;
                                        break;
                                    }
                                }
                            }

                            $noImageCounter = 0;
                            $formData = $targetAction->form->formData->formData;

                            foreach ($formData as $anotherKey => $formValue) {

                                $valueCheck = $formValue->fieldValue;

                                if ($formValue->fieldName == "Pictures") {
                                    $currentPhoto = array();
                                    if (strpos($valueCheck, "image/") !== false) {
                                        //fill photo information
                                        $photoLocation = $action->location->address;
                                        $location = $photoLocation->streetAddress . " " . $photoLocation->city . ", " .
                                            $photoLocation->state . " " . $photoLocation->zip;
                                        //e.g. "image/jpeg" and "/9j/sfsdfsdfsdfwfq3243e..."
                                        list($fullMimeType, $imageStream) = explode('|', $formValue->fieldValue, 2);
                                        //e.g. image and jpeg
                                        list($imageType, $imageExtension) = explode('/', $fullMimeType, 2);
                                        $currentPhoto["localPath"] = "/tmp/" . $countPhotos . "." . $imageExtension;
                                        $currentPhoto["photoName"] = $currentJob["jobName"] . "_" . date("m-d-Y", $jobUnixTime) . "_" . $location;
                                        $currentPhoto["extension"] = $imageExtension;
                                        $currentPhoto["imageStream"] = $imageStream;

                                        //////////////////////////
                                        $currentJob["photos"][] = $currentPhoto;
                                        $countPhotos++;

                                    }
                                }

                                else{
                                    $noImageCounter++;
                                    continue;
                                }
                            }

                            $xoraArrJobs[] = $currentJob;
                        }
                    }
                }
            }	else {
                $objectCheck = false;
                $thisNow = PodioComment::create('item', $item_id, array(
                    "value"=>"This failed at: $startTime"));
                $butThis = PodioItem::update($item_id, array(
                        'fields' => array(
                            'xora-trigger' => '...'
                        )
                    )
                );
                dd("This failed at: $startTime");

            }

            //	dd($countPhotos); = 8

//////////////////End of Xora////////////////

            //put all pictures in folders in DropBox folder

            $rootFolder = "AVA pictures";
            $localDirectory = base_path('dropbox/Hoist Sync/');

            $countThis = 1;

            foreach ($xoraArrJobs as $job) {

                $photoIndex = 1;

                foreach ($job["photos"] as $photo){

                    $targetPath = $localDirectory. "/" .$rootFolder . "/" . $job["year"] . "/" . $job["month"] . "/" . $job["day"] . "/" . $job["tablet"] . "/";

                    $photoName = $photo["photoName"] . "_Photo " . $photoIndex . "." . $photo["extension"];

                    $imageItself = base64_decode($photo["imageStream"]);

                    if(is_dir($targetPath)){

                        file_put_contents($targetPath . $photoName, $imageItself); // $photoForReal
                        $photoIndex++;

                        $countThis++;

                    } else {

                        mkdir($targetPath, 0777, true);
                        file_put_contents($targetPath . $photoName, $imageItself); // $photoForReal
                        $photoIndex++;
                        $countThis++;
                    }

                }

            }

            //	dd($countThis);
            $alert = PodioComment::create('item', $item_id, array(
                "value"=>"Finished Count#  $countBack and uploaded $countThis pictures."));

            $result = $xoraArrJobs;

            $countBack++;
        } while($countBack <= 96);

        $thisNowAgain = PodioComment::create('item', $item_id, array(
            "value"=>"Finished the whole day: $podioTimeOne"));

        $ohandthis = PodioItem::update($item_id, array(
                'fields' => array(
                    'xora-trigger' => 'All done!'
                )
            )
        );

        return [
            'success' => true,
            'result' => $result,
        ];

    } else {return;}


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