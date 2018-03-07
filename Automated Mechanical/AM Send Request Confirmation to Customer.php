<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/22/2016
 * Time: 3:32 PM
 */


date_default_timezone_set('America/Denver');
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




try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    sleep(15);

    //Get Trigger Item
    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;


    //Current Date / Time
    $CurrentHour = date("H");
    $CurrentDayofWeek = date("l");


    //Set Variables
    $ServiceRequestStatus = $item->fields['status']->values[0]['text'];
    $RequestSource = $item->fields['source']->values[0]['text'];

    //IF Status == Dispatched, do this section.
    if($ServiceRequestStatus == "New" && $RequestSource == "Webform") {

        $ContactFirstName = $item->fields['first-name']->values;
        $ContactLastName = $item->fields['last-name']->values;
        $ContactPhone = $item->fields['phone-number']->values[0]['value'];
        $ContactEmail = $item->fields['email-address']->values[0]['value'];
        $ReasonforCall = $item->fields['reason-for-call']->values;
        $RequestDate = $item->fields['date-requested']->values->start;
        $Emergency = $item->fields['is-this-an-emergency']->values[0]['text'];

        $ServiceAddress = $item->fields['service-address']->values;
        $BuildingType = $item->fields['service-location-building-type']->values;
        $ServiceLocationName = $item->fields['service-location-building-name']->values;
        $OnsiteContactName = $item->fields['onsite-contact-name']->values;
        $OnesiteContactPhone = $item->fields['onsite-phone-number']->values;
        $OnsiteContactEmail = $item->fields['onsite-email-address']->values;


        //Format Request Date
        $dateTimeStamp = new DateTime((string)$RequestDate, new DateTimeZone('America/Denver'));
        $FormatRequestDate = $dateTimeStamp->format(' F j, o g:i a');


        //Arrange Request Info
        if($ContactFirstName){$RequestInfo1 = "Contact Name: ".$ContactFirstName." ".$ContactLastName;$RequestInfo .= "<li>".$RequestInfo1."<br/></li>";}
        if($ContactEmail){$RequestInfo2 = "Contact Email: ".$ContactEmail;$RequestInfo .= "<li>".$RequestInfo2."<br/></li>";}
        if($ContactPhone){$RequestInfo3 = "Contact Phone: ".$ContactPhone;$RequestInfo .= "<li>".$RequestInfo3."<br/></li>";}
        if($Emergency){$RequestInfo6 = "Is this an emergency?: ".$Emergency;$RequestInfo .= "<li>".$RequestInfo6."<br/></li>";}
        if($ReasonforCall){$RequestInfo5 = "Reason for Call: ".$ReasonforCall;$RequestInfo .= "<li>".$RequestInfo5."<br/></li>";}




        //Arrange Service Location Info
        if($ServiceAddress){$ServiceLocationInfo1 = "Service Address: ".$ServiceAddress;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo1."<br/></li>";}
        if($BuildingType){$ServiceLocationInfo2 = "Building Type: ".$BuildingType;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo2."<br/></li>";}
        if($ServiceLocationName){$ServiceLocationInfo3 = "Building Name: ".$ServiceLocationName;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo3."<br/></li>";}
        if($OnsiteContactName){$ServiceLocationInfo4 = "Onsite Contact Name: ".$OnsiteContactName;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo4."<br/></li>";}
        if($OnsiteContactEmail){$ServiceLocationInfo5 = "Onsite Email Address: ".$OnsiteContactEmail;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo5."<br/></li>";}
        if($OnesiteContactPhone){$ServiceLocationInfo6 = "Onsite Phone Number: ".$OnesiteContactPhone;$ServiceLocationInfo .= "<li>".$ServiceLocationInfo6."<br/></li>";}



        //Email Content Info
        $DispatchEmail = 'dispatch@automatedmechanical.com';
        $Subject = "Service Request Confirmation - Automated Mechanical";


        //Signature Block
        $sal2 = "1574 West 2650 South";
        $sal3 = "Ogden, UT  84401";
        $sal4 = '(801) 525-9500 (o)';
        $sal5 = "(801) 544-5750 (f)";
        $sal6 = "or visit us at www.automatedmechanical.com";
        $sal7 = wordwrap("THIS EMAIL AND ANY ATTACHED FILES ARE CONFIDENTIAL, PROTECTED BY COPYRIGHT AND MAY BE LEGALLY PRIVILEGED. If you are not the intended addressee or have received the e-mail in error, any use of the e-mail or any copying, distribution, or other dissemination of it is strictly prohibited. If you have received this transmission in error, please notify the sender immediately and then delete the e-mail. E-mail cannot be guaranteed to be secure, error free, or free from viruses. Automated Mechanical does not accept any liability whatsoever for any loss or damage that may be caused as a result of the transmission of this message by e-mail. If verification is required, please request a hard copy version.",50,"<br>",TRUE);

        //Append Signature Block Values
        $Salutation .= "<br/>".$sal2;
        $Salutation .= "<br/>".$sal3;
        $Salutation .= "<br/>".$sal4;
        $Salutation .= "<br/>".$sal5;
        $Salutation .= "<br/>".$sal6."<br/>";
        $Salutation .= "<br/>".$sal7;





        //Item ID from Trigger Item
        $ClientItemID = $item->fields['client']->values[0]->item_id;
        $ClientPOCItemID = $item->fields['primay-poc']->values[0]->item_id;

        //Get Primary POC Email Address
        $ContactItem = PodioItem::get($ClientPOCItemID);
        $POCEmail = $ContactItem->fields['email']->values[0]['value'];



        //Create Message Body for Requests Submitted During Normal Business Hours
        $msg1 = wordwrap($ContactFirstName.", thank you for submitting a service request in regard to a HVAC system at: ".$ServiceAddress.".  Here at Automated Mechanical, your business means everything to us. So if this is an emergency and you have not heard from our dispatch, please contact us at 801-525-9500 and follow the prompting for our emergency service dispatcher.  Please note that emails after hours are not monitored and the phone call is required for emergency service.",150,"<br>",TRUE);
        $msg2 = wordwrap("Our dispatchers are working to scheduled the next available technician.  The technician will be scheduled according to the time that was requested: ". (string)$FormatRequestDate. ".  You will be hearing from our dispatchers to confirm the available time slot.  If the following information does not reflect the reason for your call, please contact us at 801-525-9500 and ask for dispatch or email us at dispatch@automatedmechanical.com.",150,"<br>",TRUE);
        $msg3 = wordwrap("We look forward to showing you how we provide the best HVAC services available.",150,"<br>",TRUE);

        //Create Message Body for Requests Submitted During Normal Business Hours
        $msg4 = wordwrap($ContactFirstName.", thank you for submitting a service request in regard to a HVAC system at: ".$ServiceAddress.".  Here at Automated Mechanical, your business means everything to us. So if this is an emergency and you have not heard from our dispatch, please call us directly.",150,"<br>",TRUE);
        $msg5 = wordwrap("Our dispatchers are working to scheduled the next available technician according to the time that was requested: ". (string)$FormatRequestDate. ".  You will be hearing from our dispatchers when we return to the office during our normal business hours of Monday through Friday 8am to 5pm.  If for some reason there is an error in the description the Service Request or you need immediate emergency service please contact us at 801-525-9500 and follow the prompting for our emergency service dispatcher.  Please note that emails after hours are not monitored and the phone call is required for emergency service.",150,"<br>",TRUE);
        $msg6 = wordwrap("We look forward to showing you how we provide the best HVAC services available.",150,"<br>",TRUE);



        //Formatt Email Message 1 (regular business hours)
        $emailMessage1 .= "<br/>".$msg1."<br/>";
        $emailMessage1 .= "<br/>".$msg2."<br/>";
        $emailMessage1 .= "<br/>".$RequestInfo.$ServiceLocationInfo."<br/>";
        $emailMessage1 .= "<br/>".$msg3."<br/><br/>";
        $emailMessage1 .= "</div>".$Salutation."<br/>";

        //Formatt Email Message 2 (after hours)
        $emailMessage2 .= "<br/>".$msg4."<br/>";
        $emailMessage2 .= "<br/>".$msg5."<br/>";
        $emailMessage2 .= "<br/>".$RequestInfo.$ServiceLocationInfo."<br/>";
        $emailMessage2 .= "<br/>".$msg6."<br/><br/>";
        $emailMessage2 .= "</div>".$Salutation."<br/>";




        //Depending On current Date and Time use this Email Message
        if($CurrentDayofWeek == "Saturday" || $CurrentHour < 7 || $CurrentHour > 17 || $CurrentDayofWeek == "Sunday"){$EmailFormatted = $emailMessage2;}
        else{$EmailFormatted = $emailMessage1;}


        //Assemble and Send Email Through Mailgun
        $fields_string = "";

        $url = 'https://api.mailgun.net/v3/mg.techego.com/messages';
        $fields = array(
            'from' => urlencode($DispatchEmail),
            'to' => urlencode($POCEmail),
            'subject' => urlencode($Subject),
            'html' => urlencode($EmailFormatted),
            'bcc' => urlencode("irobertson@techego.com")
        );

        //url-ify the data for the POST
        foreach($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        //open connection
        $ch = curl_init();
        $user = 'api:key-11365fda6b34172b1185ec4804714680';

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_USERPWD, $user);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result .= curl_exec($ch);

        $sendCount++;

        //close connection
        curl_close($ch);



        //Update Trigger Item
        $UpdateStatus = PodioItem::update($itemID, array('fields' => array('status' => "Dispatched")));

        $CommentOnServiceRequestItem = PodioComment::create('item', $itemID, array(
            'value' => "A confirmation email has been sent to " . $POCEmail . " regarding this Service Request"
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