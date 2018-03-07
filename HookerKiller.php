<?php
//Authentication
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
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


try{

    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), ['session_manager' => 'PodioSessionManager']);

//Get data from Webhook
    $requestParams = $event['request']['parameters'];
    $org_id = $requestParams['org_id'];
    $urlSearchString = $requestParams['urlSearchString'];

    if($urlSearchString == "http" || $urlSearchString == "https" || !$urlSearchString){
        $result="Invalid Url Search String";
    }



/////AUTOMATION START

    $result = "<!DOCTYPE HTML>\n<html><span style='text-decoration:underline; font-weight:bold; font-size:18px;'>Deleted Hooks List:</span><br>";

    $spacesOnOrg = PodioSpace::get_for_org( $org_id );

    foreach($spacesOnOrg as $spaceOrg) {

        $hooksOnSpace = PodioHook::get_for('space', $spaceOrg->space_id); //Array of Hooks

        $result.="<span style='text-decoration:underline; font-size:16px'>Hookers for Space ID: $spaceOrg->space_id </span><br>";

        foreach($hooksOnSpace as $space_hooker) {

            if(strpos($space_hooker->url, $urlSearchString) !== false) {

                $result .= "--Space Hook ID: $space_hooker->hook_id - Hook URL: $space_hooker->url<br>";

                //PodioHook::delete($space_hooker->hook_id);

            }

        }

        $spaceApps = PodioApp::get_for_space( $spaceOrg->space_id);

        foreach($spaceApps as $spaceApp) {

            $hooksOnApp = PodioHook::get_for('app', $spaceApp->app_id); //Array of Hooks

            $appHooks;

            foreach($hooksOnApp as $app_hooker) {

                if(strpos($app_hooker->url, $urlSearchString) !== false) {

                    $appHooks .= "--App Hook ID: $app_hooker->hook_id - Hook URL: $app_hooker->url<br>";

                    //PodioHook::delete($app_hooker->hook_id);

                }
            }

            $fullApp = PodioApp::get( $spaceApp->app_id, $attributes = array() );

            $fullAppFields = $fullApp->fields;

            $fieldsHooks="<span style='text-decoration:underline; font-size:16px'>Hookers for Fields on App ID: $spaceApp->app_id</span> <br>";

            $allFields;

            $allfieldsChecker = false;

            foreach($fullAppFields as $field){

                $hooksOnField = PodioHook::get_for('app_field', $field->field_id); //Array of Hooks

                $fieldHook = "Hookers for Field ID: $field->field_id <br>";

                $matchingFieldHooks;

                foreach($hooksOnField as $field_hooker) {

                    if(strpos($field_hooker->url, $urlSearchString) !== false) {

                        $matchingFieldHooks .= "--Field Hook ID: $field_hooker->hook_id - Hook URL: $field_hooker->url<br>";

                        $allFieldsChecker = true;

                        //PodioHook::delete($field_hooker->hook_id);

                    }
                }

                if(!empty($matchingFieldHooks)) {
                    $allFields .= $fieldHook . $matchingFieldHooks;
                }


            }


            if(!empty($appHooks) || !empty($allFields)) {
                $result.="<span style='text-decoration:underline; font-size:16px'>Hookers for App ID: $spaceApp->app_id </span><br>";
                if(!empty($appHooks)){
                    $result.=$appHooks;
                }
                if($allFields){
                    $result.=$fieldsHooks.$allFields;
                }
            }
        }
    }

    $rlr = Podio::rate_limit_remaining();

    $result.="<br><br>Rate Limit Remaining: $rlr </html>";

//END AUTOMATION

    $event['response'] = [
        'status_code' => 200,
        'content' => "$result",
        'content_type' => "html"
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

?>