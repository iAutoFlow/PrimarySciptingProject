<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 8/3/2016
 * Time: 1:41 PM
 */


//<?php
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

Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
    "session_manager" => "PodioSessionManager"

));

$requestParams = $event['request']['parameters'];






$offset = 0;
$i = 0;
do{
    $offset = $i * 500;
    $ALLUsersArray = PodioContact::get_all(array('limit'=>500, 'offset' => $offset));
    $UserCount = count($ALLUsersArray);


    foreach ($ALLUsersArray as $user) {
        try {

            $ProfileID = $user->profile_id;
            $UserID = $user->user_id;
            $Name = $user->name;
            $Title = $user->title[0];
            $Organization = $user->organization;
            $Link = $user->link;
            $Address = $user->address[0];
            $City = $user->city;
            $State = $user->state;
            $Zip = $user->zip;
            $Country = $user->country;
            $Location = $user->location[0];
            $Email = $user->mail[0];
            $Phone = $user->phone[0];
            $About = $user->about;
            $URL = $user->url;
            $Twitter = $user->twitter;
            $LinkedIn = $user->linkedin;
            $LastSeenOn = $user->last_seen_on;
            $Image = $user->image->link;


            //Assemble Fields Array
            $FieldsArray = array(
                'fields' => array()
            );



            if ($Name) {$FieldsArray['fields']['name'] = (string)$Name;}
            if ($Title) {$FieldsArray['fields']['job-title'] = $Title;}
            if ($Organization) {$FieldsArray['fields']['organization'] = (string)$Organization;}
            if ($Email){$FieldsArray['fields']['email-address'] = array('type'=>'work','value'=>(string)$Email);}
            if ($Phone) {$FieldsArray['fields']['phone-number'] = array('type' => 'work', 'value' => (string)$Phone);}
            if ($LastSeenOn) {
                $FormatLastSeen = new DateTime((string)$LastSeenOn, new DateTimeZone('America/Denver'));
                $FieldsArray['fields']['last-seen-on'] = array('start' => $FormatLastSeen->format('Y-m-d H:i:s'));
            }
            if ($Address) {$FieldsArray['fields']['address']['street_address'] = (string)$Address;}
            if ($State) {$FieldsArray['fields']['address']['state'] = $State;}
            if ($Country) {$FieldsArray['fields']['address']['country'] = $Country;}
            if ($Zip) {$FieldsArray['fields']['address']['postal_code'] =  $Zip;}
            if ($Location) {$FieldsArray['fields']['location-2'] = $Location;}
            if ($ProfileID) {$FieldsArray['fields']['profile-id'] = (string)$ProfileID;}
            if ($UserID) {$FieldsArray['fields']['user-id'] = (string)$UserID;}
            if ($About) {$FieldsArray['fields']['notes'] = (string)$About;}
            if ($Twitter) {$FieldsArray['fields']['twitter'] = (string)$Twitter;}
            if ($LinkedIn) {$FieldsArray['fields']['link-in'] = (string)$LinkedIn;}


            if ($URL) {
                foreach ($URL as $url) {
                    $CreateEmbedFile = PodioEmbed::create(array('url' => $url));
                    $EmbedID = $CreateEmbedFile->embed_id;
                    $FieldsArray['fields']['link'] = (int)$EmbedID;
                }
            }

            if ($Link) {
                $CreateEmbed = PodioEmbed::create(array('url' => $Link));
                $LinkEmbedID = $CreateEmbed->embed_id;
                $FieldsArray['fields']['website'] = (int)$LinkEmbedID;
            }

            if ($Image) {
                $CreateImageFile = PodioEmbed::create(array('url' => $Image));
                $ImageEmbedID = $CreateImageFile->embed_id;
                $FieldsArray['fields']['image'] = (int)$ImageEmbedID;
            }


            //Create Contact ITem
            $CreateContact = PodioItem::create(16971895, $FieldsArray);


        }
        catch
        (Exception $e) {

            $event['response'] = [

                'status_code' => 400,
                'content' => [
                    'success' => false,
                    'result' => $result,
                    'message' => "Error: " . $e,

                ]
            ];
            return;
        }


    }

    $i++;
}
while ($UserCount == 500);


return [
    'success' => true,
    'result' => $result
];









