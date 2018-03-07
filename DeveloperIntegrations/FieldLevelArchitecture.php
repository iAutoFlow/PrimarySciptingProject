<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/10/2017
 * Time: 10:12 PM
 */
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
    $app_id = (int)$requestParams['app_id'];
    $space_id = (int)$requestParams['space_id'];
    $org_id = (int)$requestParams['org_id'];
    $orgName = $requestParams['org_name'];


///PODIO ID VARIABLES



///AUTOMATION START
//    header('Content-type: text/html');
    $csvResponse = "<html><body><p>";
    if(!$orgName) {
        $org = PodioOrganization::get($org_id);
        $orgName = $org->name;
    }
    if($app_id){

        $fieldCounter = 2;

        $app = PodioApp::get($app_id);

        $appName = $app->config['name'];

        $appSpaceID = $app->space_id;

        $space = PodioSpace::get($appSpaceID);

        $spaceName = $space->name;

        $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"AppID-'.$app_id.'";"'.$fieldCounter.'";"Int";NULL;"PRIMARY KEY";;;'."<br/>";

        $fieldCounter++;

        $appFields = $app->fields;


        foreach($appFields as $field){

            $fieldStatus = $field->status;

            if($fieldStatus == 'active') {

                $fieldName = $field->config['label'];

                $fieldDelta = (int)$field->config['delta'] + 1;

                $fieldType = $field->type;

                if($fieldType !== 'app') {

                    $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"'.$fieldName.'";"'.$fieldCounter.'";"'.$fieldType.'";;;;;'."<br/>";

                    $fieldCounter++;

                } else {

                    $relatedApps = $field->config['settings']['referenced_apps'];

                    foreach($relatedApps as $appKey => $app) {

                        $appCount = $appKey+1;

                        $appID = $app['app_id'];

                        $subApp = PodioApp::get($appID);

                        $subAppSpace = PodioSpace::get($subApp->space_id);

                        $subAppSpaceName = $subAppSpace->name;

                        $subAppName = $subApp->config['name'];

                        $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"'.$fieldName.'-App'.$appCount.'";"'.$fieldCounter.'";"'.$fieldType.'";NULL;"FOREIGN KEY";"'.$subAppSpaceName.'";"'.$subAppSpaceName.':'.$subAppName.'";"AppID-'.$appID.'"'."<br/>";

                        $fieldCounter++;

                    }

                }
            }

        }

    }
    elseif($space_id){

        $space = PodioSpace::get($space_id);

        $spaceName = $space->name;

        $spaceApps = PodioApp::get_for_space($space_id);

        foreach($spaceApps as $spaceKey => $spaceApp) {

            $appStatus = $spaceApp->status;

            if($appStatus == 'active') {

                $fieldCounter = 2;

                $app_id = $spaceApp->app_id;

                $appName = $spaceApp->config['name'];

                $app = PodioApp::get($app_id);

                $csvResponse .= 'postgresql;"' . $orgName . '";"' . $spaceName . '";"' . $spaceName.':'.$appName . '";"AppID-' . $app_id . '";"' . $fieldCounter . '";"Int";NULL;"PRIMARY KEY";;;' . "<br/>";

                $fieldCounter++;

                $appFields = $app->fields;


                foreach($appFields as $field) {

                    $fieldStatus = $field->status;

                    if($fieldStatus == 'active') {

                        $fieldName = $field->config['label'];

                        $fieldDelta = (int)$field->config['delta'] + 1;

                        $fieldType = $field->type;

                        if($fieldType !== 'app') {

                            $csvResponse .= 'postgresql;"' . $orgName . '";"' . $spaceName . '";"' . $spaceName.':'.$appName . '";"' . $fieldName . '";"' . $fieldCounter . '";"' . $fieldType . '";;;;;' . "<br/>";

                            $fieldCounter++;

                        } else {

                            $relatedApps = $field->config['settings']['referenced_apps'];

                            foreach($relatedApps as $appKey => $app) {

                                $subAppStatus = $app['app']['status'];

                                if($subAppStatus == 'active') {

                                    $appCount = $appKey + 1;

                                    $appID = $app['app_id'];

                                    $subApp = PodioApp::get($appID);

                                    $subAppSpace = PodioSpace::get($subApp->space_id);

                                    $subAppSpaceName = $subAppSpace->name;

                                    $subAppName = $subApp->config['name'];

                                    $csvResponse .= 'postgresql;"' . $orgName . '";"' . $spaceName . '";"' . $spaceName.':'.$appName . '";"' . $fieldName . '-App' . $appCount . '";"' . $fieldCounter . '";"' . $fieldType . '";NULL;"FOREIGN KEY";"' . $subAppSpaceName . '";"' . $subAppSpaceName.':'.$subAppName . '";"AppID-' . $appID . '"' . "<br/>";

                                    $fieldCounter++;
                                }

                            }

                        }
                    }

                }

            }
        }

    }

    elseif($org_id){

        $orgSpaces = PodioSpace::get_for_org($org_id);

        foreach($orgSpaces as $orgSpace){

            $space = PodioSpace::get($orgSpace->space_id);

            $spaceName = $space->name;
            $spaceId = $space->name;

            $spaceApps = PodioApp::get_for_space($orgSpace->space_id);

            foreach($spaceApps as $spaceKey => $spaceApp) {

                $appStatus = $spaceApp->status;

                if($appStatus == 'active') {

                    $fieldCounter = 2;

                    $app_id = $spaceApp->app_id;

                    $appName = $spaceApp->config['name'];

                    try {
                        $app = PodioApp::get($app_id);
                    }catch(Exception $e){
                        continue;
                    }

                    $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"AppID-'.$app_id.'";"'.$fieldCounter.'";"Int";NULL;"PRIMARY KEY";;;'."<br/>";
                    //$csvResponse .=  $orgName.' - '.$org_id. ';' .$spaceName.' - '.$orgSpace->space_id. ';'.$appName.' - '.$app_id. ';'."<br/>";

                    $fieldCounter++;

                    $appFields = $app->fields;


                    foreach($appFields as $field) {

                        $fieldStatus = $field->status;

                        if($fieldStatus == 'active') {

                            $fieldName = $field->config['label'];
                            $fieldName = str_replace("➪", "Styling Calc.", $fieldName);

                            $fieldExId = $field->config['external_id'];

                            $fieldDelta = (int)$field->config['delta'] + 1;

                            $fieldType = $field->type;

                            if($fieldType !== 'app') {

                                $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"'.$fieldName.'";"'.$fieldCounter.'";"'.$fieldType.'";;;;;'."<br/>";
                                //$csvResponse .= $orgName.' - '.$org_id. ';' . $spaceName.' - '.$orgSpace->space_id. ';' .$appName.' - '.$app_id. ';' . $fieldName.' - '.$field->field_id.' - '.$field->external_id.' - '.$fieldType.';'. "<br/>";

                                $fieldCounter++;

                            } else {

                                $relatedApps = $field->config['settings']['referenced_apps'];

                                foreach($relatedApps as $appKey => $app) {

                                    $subAppStatus = $app['app']['status'];

                                    if($subAppStatus == 'active') {

                                        $appCount = $appKey + 1;

                                        $appID = $app['app_id'];

                                        try {
                                            $subApp = PodioApp::get($appID);
                                        }catch(Exception $e){
                                            continue;
                                        }

                                        $subAppSpace = PodioSpace::get($subApp->space_id);

                                        $subAppSpaceName = $subAppSpace->name;

                                        $subAppName = $subApp->config['name'];

                                        $csvResponse .= 'postgresql;"'.$orgName.'";"'.$spaceName.'";"'.$spaceName.':'.$appName.'";"'.$fieldName.'-App'.$appCount.'";"'.$fieldCounter.'";"'.$fieldType.'";NULL;"FOREIGN KEY";"'.$subAppSpaceName.'";"'.$subAppSpaceName.':'.$subAppName.'";"AppID-'.$appID.'"'."<br/>";
                                        //$csvResponse .= $orgName.' - '.$org_id. ';' . $spaceName.' - '.$orgSpace->space_id. ';' .$appName.' - '.$app_id. ';' . $fieldName.' - '.$field->field_id.' - '.$field->external_id.' - '.$fieldType.';'.$subAppSpaceName.' - '.$subApp->space_id . ';' .$subAppName . ' - ' . $appID . ';'. "<br/>";

                                        $fieldCounter++;
                                    }

                                }

                            }
                        }

                    }

                }
            }

        }


    }


//END AUTOMATION

    $csvResponse.="</p></body></html>";

//    echo $csvResponse;

    return $event['response'] = [
        'status_code' => 200,
        'content' => $csvResponse,
        'content_type' => 'text/html'
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => print_r($result, true),
            'message' => "Error: ".$e,

        ]
    ];

    return;

}

?>