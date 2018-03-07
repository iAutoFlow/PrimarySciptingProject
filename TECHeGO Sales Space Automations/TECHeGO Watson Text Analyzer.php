<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/29/2016
 * Time: 9:32 AM
 */

//Analyzes the tone of a piece of text. The message is analyzed for several tones
//social, emotional, and language. For each tone, various traits are derived. For example, conscientiousness, agreeableness, and openness.


//class PodioSessionManager {
//    private static $connection_id = 3;
//    private static $connection;
//
//    public function __construct() {
//    }
//
//    public static function getConnection() {
//        if (!self::$connection) {
//            self::$connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find(self::$connection_id);
//        }
//        return self::$connection;
//    }
//
//    public static function getClientId () {
//        return self::getConnection()->connectionService->config['client_id'];
//    }
//
//    public static function getClientSecret () {
//        return self::getConnection()->connectionService->config['client_secret'];
//    }
//
//    public function get($authtype = null){
//        $connection = self::getConnection();
//        return new PodioOAuth(
//            $connection->access_token,
//            $connection->refresh_token
//        );
//    }
//    public function set($oauth, $auth_type = null){
//        $connection = self::getConnection();
//        $connection->access_token = $oauth->access_token;
//        $connection->save();
//        self::$connection = $connection;
//    }
//
//
//}
try {
//    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
//        "session_manager" => "PodioSessionManager"
//
//    ));



//        $Image = $item->fields['screenshot-images']->values;
//        $ImageID = $Image[0]->file_id;
//        $ImageLINK = $Image[0]->link;
//        $ImageThumbNailLink = $Image[0]->thumbnail_link;



    $TEXT = '&text=After reviewing the original scope of the project, it\'s obvious that we strayed / gone above and beyond in a couple aspects such as the databases, bid calculations,
        and interaction algorithms. Together we have explored each department\'s role in the development of a Sale. Some of this was not in the scope, but was necessary
         to accurately define the Sales Rep\'s role. In doing so, we simultaneously have built the system to incorporate the role of those departments'.'test/plain';


    $password = "YPmFVbbafRjD";
    $username = "744ceadc-f9ba-4a6c-97f2-88e830df4b0c";
    $requesturl = "https://watson-api-explorer.mybluemix.net/tone-analyzer/api/v3/tone";

    $curl = new \Curl\Curl($username.$password);

    $version = "?version=2016-05-19";

    $fullURL = $requesturl.$version.$TEXT;

    $response = $curl->get($fullURL);

    $TextTone = $response;

    print_r($TextTone);
    exit;

    //$ClassArray = array();
    foreach ($classification->images[0]->classifiers[0]->classes as $class) {
        $ClassTitle = $class->class;
        $ClassScore = $class->score;
        $ClassHierarchy = $class->hierarchy;

        $CreateTag = PodioTag::create('item', $itemID, array($ClassTitle));

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