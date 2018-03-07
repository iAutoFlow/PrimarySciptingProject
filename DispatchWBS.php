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
    if($appName == "DB Products"){
        $appId = 17976737;
        $appToken = "068d69db94ce4acd9b52a582011b37bd";
    }
    if($appName == "DB Jobs"){
        $appId = 17976754;
        $appToken = "ce88942821b64c5694d6002532b11083";
    }
    if($appName == "DB Tasks"){
        $appId = 17976757;
        $appToken = "6e284528be3a41f2986d2b00ab9823b7";
    }
    if($appName == "Modals"){
        $appId = 17949241;
        $appToken = "b791e386d730478d9d995ff53e298315";
    }
    //Dispatch
    if($appName == "Dispatch"){
        $appId = 18070091;
        $appToken = "787d042932674c6787c39a87548b541b";
    }
    if($appName == "NTP"){
        $appId = 17978239;
        $appToken = "4029c0b883ff429fbe0d0488b6f58a9c";
    }
    if($appName == "Customers"){
        $appId = 17982828;
        $appToken = "d68e414ee4704e4f85e36b0099f0b205";
    }
    //Diagnostic Imaging
    if($appName == "Diagnostic Imaging Projects"){
        $appId = 17982988;
        $appToken = "30625d69f5ac4ea59c3a050aefd44547";
    }
    if($appName == "Diagnostic Imaging Jobs"){
        $appId = 17983011;
        $appToken = "d532168ac3af4e9b84f249f2b81037f9";
    }
    if($appName == "Diagnostic Imaging Tasks"){
        $appId = 17983050;
        $appToken = "656949ac54e9403dbdd57b44af1db82c";
    }
    //Monitoring Solutions
    if($appName == "Monitoring Solutions Projects"){
        $appId = 17983435;
        $appToken = "7e0ab391164a4d2295e8357783fb9a37";
    }
    if($appName == "Monitoring Solutions Jobs"){
        $appId = 17985677;
        $appToken = "864edc9daa764ce9bcb2b6af1c2847d6";
    }
    if($appName == "Monitoring Solutions Tasks"){
        $appId = 17985680;
        $appToken = "9ab669a17af94679b48575c0d41d309e";
    }
    //Linear Acceleration
    if($appName == "Linear Acceleration Projects"){
        $appId = 17983574;
        $appToken = "fdf7f729373640f5b07c62aab487eb5b";
    }
    if($appName == "Linear Acceleration Jobs"){
        $appId = 17983630;
        $appToken = "dacd36d302d0436689c28b19ad994e30";
    }
    if($appName == "Linear Acceleration Tasks"){
        $appId = 17983635;
        $appToken = "741a771213804547b2db315e3a5e8207";
    }
    //Contractors
    if($appName == "Contractors Projects"){
        $appId = 17983741;
        $appToken = "535ecda9987c4e4f91f6ed55199510f5";
    }
    if($appName == "Contractors Jobs"){
        $appId = 17983744;
        $appToken = "61a6cd318d0c488f8519b61c2c0a76e2";
    }
    if($appName == "Contractors Tasks"){
        $appId = 17983751;
        $appToken = "be21a3ad4e244457a2f1658139714d3f";
    }

    Podio::authenticate_with_app($appId, $appToken);
    return $appId;
}
function createPodioItem($appName, $fieldsArray){
    $appId = meiPodioAppAuth($appName);
    $createItem = PodioItem::create((int)$appId, $fieldsArray);
    $newItemId = $createItem->item_id;
    return $newItemId;
}
function updatePodioItem($appName, $itemId, $fieldsArray){
    $appId = meiPodioAppAuth($appName);
    PodioItem::update((int)$itemId, $fieldsArray, array('hook'=>false));
}

//START OF SCRIPT VIA PODIO FILE.CHANGE WEBHOOK ON PRODUCT ITEMS/////////////////////////////////
Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array());
try {
    $payload = $event['request']['payload'];
    $type = $payload['type'];
    if ($type && $type == 'hook.verify') {
        $code = $payload['code'];
        $hook_id = $payload['hook_id'];
        // Validate the webhook
        PodioHook::validate($hook_id, array('code' => $code));
    }
//Get Triggered Product Item. Title & FileManual
    $dispatchItemId = $payload['item_id'];

    meiPodioAppAuth("Dispatch");
    $dispatchItem = PodioItem::get($dispatchItemId);
    $dispatchStatus = $dispatchItem->fields['dispatch-project']->values[0]['text'];
    if($dispatchStatus !== "Dispatch"){exit;}
    $updateDispatchItem = array('fields'=>array('dispatch-project'=>"Currently Dispatching"));
    updatePodioItem("Dispatch", (int)$dispatchItemId, $updateDispatchItem);
    $projectItemId = $dispatchItem->fields['project']->values[0]->item_id;
    $ntpItemId = $dispatchItem->fields['ntp']->values[0]->item_id;
    $leadItemId = $dispatchItem->fields['lead']->values[0]->item_id;
    $assistItemId = $dispatchItem->fields['assist']->values[0]->item_id;
    $fieldEngineerItemId = $dispatchItem->fields['field-engineer']->values[0]->item_id;
    $productItemId = $dispatchItem->fields['product']->values[0]->item_id;
    $jobsItemId = $dispatchItem->fields['jobs']->values[0]->item_id;
    $optionsItemId = $dispatchItem->fields['options']->values[0]->item_id;


    //Get Product & Job/Task Info
    meiPodioAppAuth("DB Products");
    $productItem = PodioItem::get((int)$productItemId);
    $productTitle = $productItem->fields['title']->values;
    $productModalItemId = $productItem->fields['modality']->values[0]->item_id;
    //$productVendor = $productItem->fields['vendor']->values[0]->item_id;
    //$productRevision = $productItem->fields['revision']->values;
    $jobReferences = PodioItem::get_references($productItemId);
    $jobItemIdsArray = array();
    foreach($jobReferences as $job) {
        if($job['app']['name'] == "Jobs") {
            $jobItems = $job['items'];
            foreach($jobItems as $item){
                $jobItemId = $item['item_id'];
                array_push($jobItemIdsArray, $jobItemId);
            }
        }
    }

    sort($jobItemIdsArray);

    meiPodioAppAuth("Modals");
    $modalItem = PodioItem::get($productModalItemId);
    $modalDivision = $modalItem->fields['division']->values[0]['text'];
    if($modalDivision == "Diagnostic Imaging"){
        $targetSpaceId = 5207171;
        $targetSpaceName = "Diagnostic Imaging ";

    }
    if($modalDivision == "Monitoring Solutions"){
        $targetSpaceId = 5208923;
        $targetSpaceName = "Monitoring Solutions ";

    }
    if($modalDivision == "Linear Acceleration"){
        $targetSpaceId = 5208924;
        $targetSpaceName = "Linear Acceleration ";

    }
    if($modalDivision == "Contractors"){
        $targetSpaceId = 5208928;
        $targetSpaceName = "Contractors ";

    }

    meiPodioAppAuth("NTP");
    $NTPItem = PodioItem::get($ntpItemId);
    $ntpName = $NTPItem->fields['name']->values;
    $ntpOrder_number = $NTPItem->fields['order-number']->values;
    $ntpJob_type = $NTPItem->fields['job-type']->values[0]['text'];
    $ntpSchedule = $NTPItem->fields['schedule']->values[0]['text'];
    $ntpStart_date = $NTPItem->fields['start-date']->start_date->format('Y-m-d H:i:s');
    $ntpStart_date_status = $NTPItem->fields['start-date-status']->values[0]['text'];
    $ntpCompletionDate = $NTPItem->fields['required-completion-date']->start_date->format('Y-m-d H:i:s');
    $ntpRegion = $NTPItem->fields['region']->values[0]->item_id;
    $ntpGON = $NTPItem->fields['gon']->values;
    $ntpSystem_id = $NTPItem->fields['system-id']->values;
    $ntpCustomerItemId = $NTPItem->fields['customer']->values[0]->item_id;
    $ntpModality = $NTPItem->fields['modality']->values[0]->item_id;
    $ntpOptions = $NTPItem->fields['options']->values[0]->item_id;
    $ntpProduct = $NTPItem->fields['product']->values[0]->item_id;

    meiPodioAppAuth("Customers");
    $CustomerItem = PodioItem::get($ntpCustomerItemId);
    $customerName = $CustomerItem->fields['name']->values;
    $customerProjectManager = $CustomerItem->fields['field-engineer']->values[0]->item_id;

    $newProjectFieldsArray = array(
        'fields'=>array(
            'product'=>(int)$productItemId,
            'gon'=>$ntpGON,
            'system-id'=>$ntpSystem_id,
            'customer'=>(int)$ntpCustomerItemId,
            'pmi'=>(int)$customerProjectManager,
            'region'=>(int)$ntpRegion,
            'job-type'=>$ntpJob_type,
            'startend-date'=>array('start'=>$ntpStart_date,'end'=>$ntpCompletionDate),
            'lead'=>(int)$leadItemId,
            'assistant'=>(int)$assistItemId,
            'field-engineer'=>(int)$fieldEngineerItemId,
            'ntp'=>(int)$ntpItemId,
            'dispatch'=>(int)$dispatchItemId,
        ));

    $newProjectItemId = createPodioItem($targetSpaceName."Projects", $newProjectFieldsArray);
    $dependentItemId = null;
    foreach($jobItemIdsArray as $jobItemId){
        // $taskItemIdsArray = array();
        meiPodioAppAuth("DB Jobs");
        $jobItem = PodioItem::get((int)$jobItemId);
        $jobTitle = $jobItem->fields['title']->values;
        $jobTimeAllowed = $jobItem->fields['time-allocated']->values;
        $newJobFieldsArray = array(
            'fields'=>array(
                'parent-project'=>(int)$newProjectItemId,
                'db-job'=>(int)$jobItemId,
                'time-allocated'=>$jobTimeAllowed,
            )
        );

        if($dependentItemId){$newJobFieldsArray['fields']['dependency'] = $dependentItemId;}
        $newJobItemId = createPodioItem($targetSpaceName."Jobs", $newJobFieldsArray);
        $dependentItemId = (int)$newJobItemId;
        meiPodioAppAuth("DB Tasks");
        $taskReferences = PodioItem::get_references((int)$jobItemId);

        $taskReferencesArray = array();
        foreach($taskReferences as $task) {
            if ($task['app']['name'] == "Task WBS") {
                $taskItems = $task['items'];
                foreach ($taskItems as $item) {
                    $taskItemId = $item['item_id'];
                    array_push($taskReferencesArray, (int)$taskItemId);
                }
            }
        }

        sort($taskReferencesArray);
        $taskDependant = null;
        foreach($taskReferencesArray as $taskItemId){
            meiPodioAppAuth("DB Tasks");
            $task = PodioItem::get($taskItemId);
            $taskTitle = $task->fields['title']->values;
            $newTaskFieldsArray = array(
                'fields'=>array(
                    'db-task'=>(int)$taskItemId,
                    'job'=>(int)$newJobItemId,
                    'dependencies'=>$taskDependant,
                )
            );

            if($taskDependant){$newTaskFieldsArray['fields']['dependencies'] = $taskDependant;}
            $newTaskItemId = createPodioItem($targetSpaceName."Tasks", $newTaskFieldsArray);
            $taskDependant = (int)$newTaskItemId;
        }
    }

    $updateDispatchItem = array('fields'=>array('dispatch-project'=>"Dispatch Finished"));
    updatePodioItem("Dispatch", (int)$dispatchItemId, $updateDispatchItem);


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



