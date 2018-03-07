
<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 4/6/2017
 * Time: 10:00 AM
 */


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

Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array("session_manager" => "PodioSessionManager"));
try {

//    $payload = $event['request']['payload'];
//    $type = $payload['type'];
//    if ($type && $type == 'hook.verify') {
//        $code = $payload['code'];
//        $hook_id = $payload['hook_id'];
//        PodioHook::validate($hook_id, array('code' => $code));
//    }
//
//    $itemID = $payload['item_id'];
//    $item = PodioItem::get((int)$itemID);

    require_once 'vendor/autoload.php';
    //Auth with Redmine
    $client = new Redmine\Client('http://redmine.example.com', 'API_ACCESS_KEY');

    



    return [
        'success' => true,
        'result' => "success result",
    ];

}catch(Exception $e) {

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => "error result",
            'message' => "Error: " . $e,

        ]
    ];
}
//if($issueParentIssueItemID){
//    $parentIssue = PodioItem::get($issueParentIssueItemID);
//    $parentIssueTitle = $parentIssue->fields['name-it']->values;
//    $parentIssueBranchId = $parentIssue->fields['gitlab-branch-id']->values;
//    if($parentIssueTitle && $parentIssueBranchId){
//        $titleParent = str_replace('.', '', $parentIssueTitle);
//        $issueParentTitleReady = str_replace(' ', '', $titleParent);
//        $ref = (string)$issueParentTitleReady;
//    }
//}
//if($issueTriggerValue == "Create New Branch") {
//    //Create Git-lab Branch
//    try {
//        $branch = $client->api('repositories')->createBranch((int)$productGitlabId, $issueTitleReady, $ref);
//        $branchId = $branch['commit']['id'];
//        $branchMessage = $branch['commit']['message'];
//        $branchCommited = $branch['commit']['committed_date'];
//        $branchCommitedFormatted = date("Y-m-d H:i:s", strtotime($branchCommited));
//
//        PodioItem::update((int)$issueItemID, array(
//            'fields' => array(
//                'gitlab-branch-id' => (string)$branchId,
//                'response-message' => $branchMessage,
//                'date-commited' => array('start' => $branchCommitedFormatted),
//                'category-2' => "Branch Created")),
//            array('hook' => false)
//        );
//
//    } catch (Exception $e) {
//        PodioComment::create('item', $issueItemID,
//            array('value' => "Branch Could not be Created. $e")
//        );
//        PodioItem::update((int)$issueItemID, array(
//            'fields' => array(
//                'category-2' => "ERROR"
//            )
//        ), array('hook' => false));
//        exit;
//    }
//}
//if($issueTriggerValue == "Delete Branch"){
//    try{
//        $branch = $client->api('repositories')->deleteBranch((int)$productGitlabId, $issueTitleReady);
//        PodioItem::update((int)$issueItemID, array(
//            'fields' => array(
//                'category-2' => "Branch Deleted")),
//            array('hook' => false)
//        );
//    }catch(Exception $e) {
//        PodioComment::create('item', $issueItemID,
//            array('value' => "Branch Could not be Deleted. $e")
//        );
//        PodioItem::update((int)$issueItemID, array(
//            'fields' => array(
//                'category-2' => "ERROR"
//            )
//        ), array('hook' => false));
//        exit;
//    }
//    exit;
//}
?>


