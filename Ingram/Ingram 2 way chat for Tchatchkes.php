<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/6/2016
 * Time: 9:58 AM
 */

//O-AUTH

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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];
    $comment_id = $requestParams['comment_id'];

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $TchotchkeRFQAppID = 12162632;
    $RFQEstimatesAppID = 12834322;

    $Comment = PodioComment::get($comment_id);
    $CommentValue = $Comment->value;
    $CommentRichValue = $Comment->rich_value;




   if($appID == $TchotchkeRFQAppID) {

       $ReferencedRFQItem = PodioItem::get_references_by_field($itemID, 98209539);
       $ReferenceRFQItemID = $ReferencedRFQItem[0]->item_id;
       $AddComment = PodioComment::create('item', $ReferenceRFQItemID, array(
           'value' => $CommentRichValue
       ),                array(
           'hook' => false
       ));
   }

    if($appID == $RFQEstimatesAppID) {

        $ReferencedTchotchkeItemID = $item->fields['request']->values[0]->item_id;
        $AddComment = PodioComment::create('item', $ReferencedTchotchkeItemID, array(
            'value' => $CommentRichValue
        ),                array(
            'hook' => false
        ));

    }








//RETURN / CATCH
    return [
        'success' => true,
        'result' => $result,
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

    return;

}