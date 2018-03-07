<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 3/13/2017
 * Time: 3:54 PM
 */
include 'vendor/autoload.php';
date_default_timezone_set('America/Denver');
//OAuth with Podio
class PodioSessionManager {
    private static $connection_id = 3;
    private static $connection;

    public function __construct() {
    }

    public static function getConnection() {
        if (!self::$connection) {
            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
        }
        return self::$connection;
    }

    public static function getClientId () {
        return self::getConnection()->connectionService->config['client_id'];
    }

    public static function getClientSecret () {
        return self::getConnection()->connectionService->config['client_secret'];
    }

    public function get($authtype = null){
        $connection = self::getConnection();
        return new PodioOAuth(
            $connection->access_token,
            $connection->refresh_token
        );
    }
    public function set($oauth, $auth_type = null){
        $connection = self::getConnection();
        $connection->access_token = $oauth->access_token;
        $connection->save();
        self::$connection = $connection;
    }


}

//Authenticates with Podio and Returns the App_Id of the given App Name.
function meiPodioAppAuth($appName){
    $appId = 0;
    $appToken = "";
    //Databases
    if($appName == "Products"){
        $appId = 17976737;
        $appToken = "068d69db94ce4acd9b52a582011b37bd";
    }
    if($appName == "Jobs"){
        $appId = 17976754;
        $appToken = "ce88942821b64c5694d6002532b11083";
    }
    if($appName == "Tasks WBS"){
        $appId = 17976757;
        $appToken = "6e284528be3a41f2986d2b00ab9823b7";
    }


    Podio::authenticate_with_app($appId, $appToken);
    return $appId;
}
function createPodioItem($appName, $fieldsArray){
    $appId = meiPodioAppAuth($appName);
    $createItem = PodioItem::create($appId, $fieldsArray);
    $newItemId = $createItem->item_id;
    return $newItemId;
}

//Takes the full text from PFD and parses the following: " Step#  |  Step Name:  |  People Required: ##  |  Minutes to Complete: ## "
function findProcessJobs($fullText, $productItemId){

    $strippedTextResponse = stripOutHeaderJobsNTasks($fullText);
    $strippedText = $strippedTextResponse['text'];
    $jobHeaderType = $strippedTextResponse['job_header'];

    //$preg_match_jobs = '([0-9]{1,2}[A-Z].+People Required:\s\d'.$jobHeaderType.'.*\d)';
    //$preg_match_jobs = '[([0-9]{1,2}\s*[A-Z].+\s\n*.+People Required:\s\d'.$jobHeaderType.'\s\n*.*\d)]';
    $preg_match_jobs = '(\n[0-9].*\n{0,1}.+People Required:.*\n{0,1}'.$jobHeaderType.'.*\n{0,1}.*\d+\n)';

    preg_match_all("$preg_match_jobs", $strippedText, $jobMatches, PREG_SET_ORDER);

    $jobNumber = 1;
    $dependantItemId = null;
    foreach($jobMatches as $jobMatch){
        $jobMatchA = trim($jobMatch[0]);
        $jobMatchB = trim($jobMatches[$jobNumber][0]);
        //$newJobItemId = processJobValues($jobMatchA, $dependantItemId, $productItemId, $jobHeaderType);//
        //$dependantItemId = (int)$newJobItemId;
        $jobPositionA = strpos($strippedText, $jobMatchA);
        $stringTotalLength = strlen($strippedText);
        if($jobMatchB) {
            $jobPositionB = strpos($strippedText, $jobMatchB, $jobPositionA);
            $taskTextBody = substr($strippedText, $jobPositionA, $jobPositionB - $jobPositionA);//
        }
        else{
            $taskTextBody = substr($strippedText, $jobPositionA, $stringTotalLength - $jobPositionA);
        }

        $textBody = preg_replace('(^\d.+\n*.+\n*.+\d\n)', "", $taskTextBody);
        if($jobNumber == 16) {
            $newJobItemId = 0;
            processTaskValues($textBody, $newJobItemId, $productItemId);
        }
        $jobNumber++;
    }

    return "done";
}
function processJobValues($jobString, $dependantItemId, $productItemId, $jobHeaderType){
    $findJobNumber = substr($jobString, 0, 3);
    $jobNumber = preg_replace('/[^0-9]/','',$findJobNumber);
    $jobNumberLength = strlen((int)$jobNumber);
    $lengthOfMTC = strlen($jobHeaderType);
    $lengthOfPR = strlen("People Required:");
    $pos1 = strpos($jobString, 'People Required:');
    $pos2 = strpos($jobString, $jobHeaderType);

    if($jobHeaderType == "Minutes to Complete:"){
        $timeMultiplier1 = 60;
        $timeMultiplier2 = 1;
    }
    else{
        $timeMultiplier1 = 60;
        $timeMultiplier2 = 60;
    }

    $jobOrderNumber = substr($jobString,0,(int)$jobNumberLength);
    $jobName = substr($jobString, (int)$jobNumberLength, $pos1 - $jobNumberLength);

    $peopleRequired = substr($jobString, (int)$pos1, $lengthOfPR+2);
    $timeToComplete = substr($jobString, $pos2, $lengthOfMTC+4);
    $timeToComplete = rtrim($timeToComplete);
    $peopleRequired = rtrim($peopleRequired);

    $peopleRequiredNum = substr($peopleRequired, -2);
    $timeToCompleteNum = substr($timeToComplete, -3);
    $peopleRequiredNum = ltrim($peopleRequiredNum);

    $timeToCompleteNum = preg_replace('/[^0-9.0-9]/','',$timeToCompleteNum);//[^0-9]\.{0,1}[^0-9]
    $timeAllocated = $timeToCompleteNum * $timeMultiplier1 * $timeMultiplier2;
    $timeUnit = "Total";


    $jobItemArray = array(
        "fields"=>array(
            'product' => (int)$productItemId,
            'title' => $jobName,
            'order-2' => $jobOrderNumber,
            'people-required-2' => $peopleRequiredNum,
            'time-allocated' =>$timeAllocated,
            'time-unit' => $timeUnit
        )
    );

    if($dependantItemId){$jobItemArray['fields']['dependency'] = $dependantItemId;}
    $newJobItemId = createPodioItem("Jobs", $jobItemArray);
    return $newJobItemId;

}
function processTaskValues($taskTextBody,$parentJobItemId, $productItemId){//

    $lineShort    =  '\n+\[*[A-Z]+.{1,75}\s*\n';
    $line1Period  =  '.+\.\s*\n';
    $line1Period2 =  '.{,95}\s*\n';
    $line2Period  =  '.+\n.*\.\s*\n';
    $line2Period2 =  '.+\n.{,95}\s*\n';
    $line3Period  =  '.+\n.*\n.*\.\s*\n';
    $line3Period2 =  '.+\n.*\n.{,95}\s*\n';
    $line4Period  =  '.+\n.*\n.*\n.*\.\s*\n';
    $line5Period  =  '.+\n.*\n.*\n.*\n.*\.\s*\n';
    $line6Period  =  '.+\n.*\n.*\n.*\n.*\n.*\.\s*\n';
    $line7Period  =  '.+\n.*\n.*\n.*\n.*\n.*\n.*\.\s*\n';
    $line8Period  =  '.+\n.*\n.*\n.*\n.*\n.*\n.*\n.*\.\s*\n';

    preg_match_all("[($lineShort|$line1Period|$line1Period2|$line2Period|$line2Period2|$line3Period|$line3Period2|$line4Period|$line5Period|$line6Period|$line7Period|$line8Period)]", $taskTextBody, $tasks, PREG_SET_ORDER);


    dd($tasks, $taskTextBody);
    $orderCount = 1;
    $dependantItemId = null;
    foreach($tasks as $task){
        $taskTitle = $task[0];
        $taskTitleReady = preg_replace('/\s\s+/', ' ', $taskTitle);
        $taskItemArray = array(
            "fields" => array(
                'title' => (string)$taskTitleReady,
                'job' => (int)$parentJobItemId,
                'order-2' => (string)$orderCount,
                'product' => (int)$productItemId,
            )
        );
        if($dependantItemId){$taskItemArray['fields']['dependencies'] = $dependantItemId;}
        //$newTaskItemId = createPodioItem("Tasks WBS", $taskItemArray);
        //$dependantItemId = (int)$newTaskItemId;
        $orderCount++;
    }
}
function stripOutHeaderJobsNTasks($text){
    //$preg_match_minutes = '[0-9]{1,2}[A-Z].+\b\w*People Required:.*\b.*Minutes to Complete:.*\b.*\n'; ORIGINAL
    //$preg_match_minutes = '[0-9]{1,2}\s*[A-Z].+\s\n*.+\b\w*People Required:.*\b.*Minutes to Complete:\s\n*.*\b.*\n';
    $preg_match_minutes = '\n[0-9].*\n{0,1}.+People Required:.*\n{0,1}Minutes to Complete:.*\n{0,1}.*\d+\n';
    $job_preg_match = "[($preg_match_minutes)]";//
    preg_match_all($job_preg_match, $text, $jobMatches, PREG_SET_ORDER);
    $numOfJobsCount = count($jobMatches);

    if($numOfJobsCount < 1) {
        //$preg_match_hours = '[0-9]{1,2}[A-Z].+\b\w*People Required:.*\b.*Hours to Complete:.*\b.*\n'; ORIGINAL
        //$preg_match_hours = '[0-9]{1,2}\s*[A-Z].+\s\n*.+\b\w*People Required:.*\b.*Hours to Complete:\s\n*.*\b.*\n';
        $preg_match_hours = '\n[0-9].*\n{0,1}.+People Required:.*\n{0,1}Hours to Complete:.*\n{0,1}.*\d+\n';
        $job_preg_match = "[($preg_match_hours)]";//
        preg_match_all($job_preg_match, $text, $jobMatches, PREG_SET_ORDER);
        $numOfJobsCount = count($jobMatches);
        $jobHeaderType = "Hours to Complete:";
    }
    else{$jobHeaderType = "Minutes to Complete:";}

    $posOfFirstMatch = strpos($text, $jobMatches[0][0]);
    $posOfLastMatch = strpos($text, $jobMatches[$numOfJobsCount-1][0]);
    $posOfLastPage = strpos($text, "GE HEALTHCARE", $posOfLastMatch);
    $cutText1 = substr($text, $posOfFirstMatch, $posOfLastPage - $posOfFirstMatch);

    //strings to delete
//    $GEHEALTHCARE = 'GE HEALTHCARE.*';
//    $DIRECTION = '\n[A-Z]{0,2}RECTION.*\n{0,1}\.{0,1}\n';
//    $REVISION = '\nREVISION.*';
//    $PAGE = 'Page\s\d{1,3}\s*Section\s*\d{1,3}.*\n';
//    $singleLETTER = '\n{0,2}\s{0,3}[A-Z]{1}\n';
//    $CHAPTER = '\nChapter \d.Page \d*';
//    $SECTION = '\nSection \d\.\d.+';
//
//    $matchRemoveText2 = "[($DIRECTION|$GEHEALTHCARE|$REVISION|$PAGE)]";
//    $matchRemoveText1 = "[($SECTION|$CHAPTER|$singleLETTER)]";
//    $cutText2 = preg_replace($matchRemoveText1, "", $cutText1);
//    $cutText3 = preg_replace($matchRemoveText2, "", $cutText2);

    $patterns = array();
    $patterns[0] = '(GE HEALTHCARE.*)';
    $patterns[1] = '(\n[A-Z]{0,2}RECTION.*\n{0,1}\.{0,1}\n)';
    $patterns[2] = '(\nREVISION.*)';
    $patterns[3] = '(Page\s\d{1,3}\s*Section\s*\d{1,3}.*\n)';
    $patterns[4] = '(\n{0,2}\s{0,3}[A-Z]{1}\n)';
    $patterns[5] = '(\nChapter \d.Page \d*)';
    $patterns[6] = '(\nSection \d\.\d.+)';
    $patterns[7] = '(\n+s*\.)';
    $patterns[8] = '(\ns*\,)';
    $patterns[9] = '(\n\s+\n)';
    $patterns[10] = '(\nLead.*\n.*\.)';
    $patterns[11] = '(\nAll Class A.+\n*.+\.\n)';
    $patterns[12] = '(\nTable.*\n)';

    $replacements = array();
    $replacements[7] = '.';
    $replacements[8] = ',';
    $replacements[9] = '\n';
    $replacements[0-6] = '';
    $replacements[10-12] = '';
    //$replacements[5] = '';

    $textReady2Go1 = preg_replace($patterns, $replacements, $cutText1);
    return array("text"=>$textReady2Go1, "job_header"=>$jobHeaderType);

}



//START OF SCRIPT VIA PODIO FILE.CHANGE WEBHOOK ON PRODUCT ITEMS/////////////////////////////////
Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array());
try {

    $productItemId = 578651467;

    meiPodioAppAuth("Products");
    $productItem = PodioItem::get((int)$productItemId);
    $productTitle = $productItem->fields['title']->values;
    $productModal = $productItem->fields['modality']->values[0]->item_id;
    $productVendor = $productItem->fields['vendor']->values[0]->item_id;
    $productRevision = $productItem->fields['revision']->values;

//Product Manual File
    $productFiles = $productItem->files;
    $productManuelFileId = $productFiles[0]->file_id;
    $productManual = PodioFile::get((int)$productManuelFileId);
    $fileName = $productManual->name;
    $fileData = $productManual->get_raw();

//Download / Save file locally
    $localFilePath = '/home/hoist/web/hoist.thatapp.io/public_html/public/img/clients/mei_product_manual-' . $productTitle . $productManuelFileId . '.pdf';
    file_put_contents($localFilePath, $fileData);

///Start Parse File////////////////////////////
    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($localFilePath);
    $text = $pdf->getText();
    $details = $pdf->getDetails();
    dd($details);
    unlink($localFilePath);
    findProcessJobs($text, $productItemId);


    return [
        'success' => true,
        'result' => $productItemId,
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

?>



