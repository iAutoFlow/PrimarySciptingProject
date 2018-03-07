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
    $item_id = (int)$requestParams['item_id'];

///AUTOMATION START



//Get data from Webhook

    $requestParams = $event['request']['parameters'];

    $item_id = $requestParams['item_id'];




///AUTOMATION START
    $item = PodioItem::get($item_id);
    $linkEmail = "\"".$item->fields['materials-collection-wufoo-link']->values."\"";
    $linkPlaceholder = $item->fields['materials-collection-wufoo-link']->values;

    $materialsContactItemId = $item->fields['materials-collection-contact']->values[0]->item_id;
    if($materialsContactItemId == null){
        PodioItem::update($item_id, array('fields'=>array('route-to-requestor'=>"Error Sending Email")));
        PodioComment::create('item', $item_id, $attributes= array("value"=>"No Materials Collection Contact related.") );
        throw new Exception("Error: no email value for Materials Collection Contact");
    }

    $materialsContact = PodioItem::get($materialsContactItemId);

    $contactAppID = $materialsContact->app->app_id;

    if($contactAppID == 10326960) {
        $emailAddress = $materialsContact->fields['email']->values[0]['value'];
    }

    if($contactAppID == 10306619) {
        $emailAddress = $materialsContact->fields['email']->values;
    }

    if ($linkPlaceholder == null) {
        PodioItem::update($item_id, array('fields'=>array('route-to-requestor'=>"Error Sending Email")));
        PodioComment::create('item', $item_id, $attributes= array("value"=>"Failed to provide a Materials Collection Wufoo Link") );
        throw new Exception("Error: Wufoo link empty.");
    }
    $milestone = $item->fields['milestone-name']->values[0]->value;
    $jobName = $item->fields['parent-job']->values[0]->title;
    $subject = "Wufoo Link for ".$milestone." on Job ".$jobName.".";
    $emailBody = "<p>You are receiving this Wufoo link from Agency Ingram Micro.</p><p>Wufoo Link: <a href=".$linkEmail."></a>".$linkPlaceholder."</p><p>Please fill in the fields to the best of your ability and hit Submit.</p>";


    $fields = array(
        'from' => urlencode('Ingram <agency@ingrammicro.com>'),
        'to' => urlencode("recipient <".$emailAddress.">"),
        'subject' => urlencode($subject),
        'html' => urlencode($emailBody)
    );


    $fields_string = "";
    foreach($fields as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');


    if($item->fields['route-to-requestor']->values[0]['text'] == "Send Email"){
        PodioItem::update($item_id, array('fields'=>array('route-to-requestor'=>"Currently Sending")));

        $ch = curl_init();
        $user = 'api:key-a93db7328c377a788fa73ea0549422f4';
        $url = 'https://api.mailgun.net/v3/mg.ingrammicro.com/messages';
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_USERPWD, $user);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result .= curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);

        if ($result['message'] == "Queued. Thank you."){
            PodioItem::update($item_id, array('fields'=>array('route-to-requestor'=>"Link Sent Successfully")));

            return [

                'success' => true,

                'result' => $result,

            ];
        } else {
            PodioItem::update($item_id, array('fields'=>array('route-to-requestor'=>"Error Sending Email")));
            PodioComment::create('item', $item_id, $attributes= array("value"=>"Failed to send email.".$result) );
            throw new Exception("Error: Mailgun api status.");
        }

    } else {
        throw new Exception("Status not set to: Send Email");
    }



//END AUTOMATION

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

?>