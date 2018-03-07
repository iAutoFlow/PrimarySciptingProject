<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 10/18/2016
 * Time: 11:32 AM
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

try {
    Podio::setup(PodioSessionManager::getClientId(), PodioSessionManager::getClientSecret(), array( //client id and secret from connection service config
        "session_manager" => "PodioSessionManager"

    ));

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];

    //////Get Trigger Item/////////
    $item = PodioItem::get($itemID);
    $PersonContactedItemID = $item->fields['contact-2']->values[0]->item_id;
    $LeadItemID = $item->fields['lead-2']->values[0]->item_id;
    $Type = $item->fields['type']->values[0]['text'];
    $Summary = $item->fields['title']->values;



    //If Interaction has a type of "Email, Sent"////////////////////////////////////////////////////
    if($Type == "Email, Sent") {

        ///Get Person Contacted Contact Item
        $ContactItem = PodioItem::get($PersonContactedItemID);
        $ContactEmail = $ContactItem->fields['email-address']->values[0]['value'];
        $ContactName = $ContactItem->fields['name']->values;


        //Get Lead Item
        $LeadItem = PodioItem::get($LeadItemID);
        $AccountManagerName = $LeadItem->fields['account-manager']->values[0]->name;
        $AccountManagerEmail = $LeadItem->fields['account-manager']->values[0]->mail[0];


        //Email Content Info
        $Subject = "TECHeGO";

        //Signature Block
        $AmandaSAL = "<div dir=\"ltr\"><table style=\"font-family:'Times New Roman'\"><tbody><tr><td style=\"border-right-width:8px;border-right-style:solid;border-right-color:rgb(164,197,57);padding-right:15px;text-align:center\"><img alt=\"TECHeGO logo\" src=\"https://ci4.googleusercontent.com/proxy/UElzV9-VeUUeXNlh0VbKKxb_7Sg-GRtxYy2R4Aurfi3aZy5hfLOK4tZQbrSKH2E1IoHxP3tpDpWbOtKz0JLkZp18UstI3W__88GrZB0JP1HjMc5awAgevvBV_QC-d6tz=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-techego.jpg\" width=\"200\" height=\"53\" style=\"padding-bottom:15px\" class=\"CToWUd\"><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">10808 S. River Front Pkwy, Suite 314</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">South Jordan, UT, 84095</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:40px\"><a href=\"http://www.techego.com/\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=http://www.techego.com/&amp;source=gmail&amp;ust=1476901738637000&amp;usg=AFQjCNHoUVbQTzeezuvLbSKDGvliGBMnwA\">www.techego.com</a></span></td><td style=\"padding-left:15px;padding-top:10px;padding-bottom:10px\"><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:20px;color:rgb(16,54,91);font-weight:bold\">Amanda Campbell, MBA</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:13px\">Executive Vice President</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:35px\"><a href=\"mailto:amanda@techego.com\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\">amanda@techego.com</a></span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:11px;line-height:20px\"><a href=\"tel:+1-949-887-8422\" style=\"text-decoration:none;color:black\" target=\"_blank\">+1 949-887-8422</a></span><br><a href=\"https://www.facebook.com/TECHeGO/\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.facebook.com/TECHeGO/&amp;source=gmail&amp;ust=1476901738637000&amp;usg=AFQjCNHNfrZpR7XhRxZ_vqyRrfPVUpbxhw\"><img alt=\"Facebook icon\" src=\"https://ci3.googleusercontent.com/proxy/gpkMWUf-uEw3VvX49S-FDI9V2B3PkSg0sFJZ3-YmygUIlZVh_VTlGoKIuyKHSkxXuiQG1Bvc7pNwdtj0QSvoy4_ln61V_RhvPCKQQcE2FuwQ2CBpoDkCVGKbeTMzWf3-xw=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-facebook.jpg\" style=\"margin-top:10px\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://twitter.com/techego\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://twitter.com/techego&amp;source=gmail&amp;ust=1476901738637000&amp;usg=AFQjCNEiNpGArQy32FTfzcpJp7y0Xkk62A\"><img alt=\"Twitter icon\" src=\"https://ci4.googleusercontent.com/proxy/31USpyz6LxPjFfp2EJpVXG6P2YxOdJkZ5ySbzD1wWR6bujFoH_1JarVg8o3UU0fYH1wfI_FkHodxAR3tLUBSYNT0sG_B8hV-RJTtCWT8KM8HQ9zV3jE_PciA4uhsU83l=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-twitter.jpg\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://www.linkedin.com/in/amandaecampbell\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.linkedin.com/in/amandaecampbell&amp;source=gmail&amp;ust=1476901738637000&amp;usg=AFQjCNEmFw3iTmeAroVVaRrlxdx8KvRuew\"><img alt=\"LinkedIn icon\" src=\"https://ci4.googleusercontent.com/proxy/Ly7MZUuEb28EdjZ60lKxDjtzCp59erMBASXk7rv4-ctayBtGn6qA3IdnUZwPDKe2b7lN7_L272kEbjoPIilgsh_k5KwB40tIXNxPq4Nr0OxVnbUDltvCoKe48mafssl4GA=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-linkedin.jpg\" class=\"CToWUd\"></a></td></tr></tbody></table></div>";
        $SethSAL = "<div dir=\"ltr\"><table style=\"font-family:&quot;Times New Roman&quot;\"><tbody><tr><td style=\"border-right-width:8px;border-right-style:solid;border-right-color:rgb(164,197,57);padding-right:15px;text-align:center\"><img alt=\"TECHeGO logo\" src=\"https://ci4.googleusercontent.com/proxy/UElzV9-VeUUeXNlh0VbKKxb_7Sg-GRtxYy2R4Aurfi3aZy5hfLOK4tZQbrSKH2E1IoHxP3tpDpWbOtKz0JLkZp18UstI3W__88GrZB0JP1HjMc5awAgevvBV_QC-d6tz=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-techego.jpg\" width=\"200\" height=\"53\" style=\"padding-bottom:15px\" class=\"CToWUd\"><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">10808 S. River Front Pkwy, Suite 314</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">South Jordan, UT, 84095</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:40px\"><a href=\"http://www.techego.com/\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=http://www.techego.com/&amp;source=gmail&amp;ust=1476902088059000&amp;usg=AFQjCNFLgehkp2baY9ynxbIrB3utAdnzJQ\">www.techego.com</a></span></td><td style=\"padding-left:15px;padding-top:10px;padding-bottom:10px\"><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:20px;color:rgb(16,54,91);font-weight:bold\">Seth Helgeson</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:13px\">Founding CEO<br>Enterprise Architect</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:35px\"><a href=\"mailto:seth@techego.com\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\">seth@techego.com</a></span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:11px;line-height:20px\"><a href=\"tel:+1-801-800-8099\" style=\"text-decoration:none;color:black\" target=\"_blank\">+1 801-800-8099</a></span><br><a href=\"https://www.facebook.com/TECHeGO/\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.facebook.com/TECHeGO/&amp;source=gmail&amp;ust=1476902088059000&amp;usg=AFQjCNHmLRBh7tzAlgvbDWOlD95vhoojeA\"><img alt=\"Facebook icon\" src=\"https://ci3.googleusercontent.com/proxy/gpkMWUf-uEw3VvX49S-FDI9V2B3PkSg0sFJZ3-YmygUIlZVh_VTlGoKIuyKHSkxXuiQG1Bvc7pNwdtj0QSvoy4_ln61V_RhvPCKQQcE2FuwQ2CBpoDkCVGKbeTMzWf3-xw=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-facebook.jpg\" style=\"margin-top:10px\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://twitter.com/techego\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://twitter.com/techego&amp;source=gmail&amp;ust=1476902088059000&amp;usg=AFQjCNETSSuDmQDPTtAIv69XVO4arRZ2Xg\"><img alt=\"Twitter icon\" src=\"https://ci4.googleusercontent.com/proxy/31USpyz6LxPjFfp2EJpVXG6P2YxOdJkZ5ySbzD1wWR6bujFoH_1JarVg8o3UU0fYH1wfI_FkHodxAR3tLUBSYNT0sG_B8hV-RJTtCWT8KM8HQ9zV3jE_PciA4uhsU83l=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-twitter.jpg\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://www.linkedin.com/in/shelgeson\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.linkedin.com/in/shelgeson&amp;source=gmail&amp;ust=1476902088060000&amp;usg=AFQjCNGI_A9ULAjm87uIgLhVID2XpEsv8g\"><img alt=\"LinkedIn icon\" src=\"https://ci4.googleusercontent.com/proxy/Ly7MZUuEb28EdjZ60lKxDjtzCp59erMBASXk7rv4-ctayBtGn6qA3IdnUZwPDKe2b7lN7_L272kEbjoPIilgsh_k5KwB40tIXNxPq4Nr0OxVnbUDltvCoKe48mafssl4GA=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-linkedin.jpg\" class=\"CToWUd\"></a></td></tr></tbody></table></div>";
        $JoshSAL = "<div dir=\"ltr\"><table style=\"font-family:Tinos\"><tbody><tr><td style=\"border-right-width:8px;border-right-style:solid;border-right-color:rgb(164,197,57);padding-right:15px;text-align:center\"><img alt=\"TECHeGO logo\" src=\"https://ci4.googleusercontent.com/proxy/UElzV9-VeUUeXNlh0VbKKxb_7Sg-GRtxYy2R4Aurfi3aZy5hfLOK4tZQbrSKH2E1IoHxP3tpDpWbOtKz0JLkZp18UstI3W__88GrZB0JP1HjMc5awAgevvBV_QC-d6tz=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-techego.jpg\" width=\"200\" height=\"53\" style=\"padding-bottom:15px\" class=\"CToWUd\"><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">10808 S. River Front Pkwy, Suite 314</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:10px\">South Jordan, UT, 84095</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:40px\"><a href=\"http://www.techego.com/\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=http://www.techego.com/&amp;source=gmail&amp;ust=1476902483083000&amp;usg=AFQjCNFkkjtsUjWQIIrZ6wQKj5EiEQ0clQ\">www.techego.com</a></span></td><td style=\"padding-left:15px;padding-top:10px;padding-bottom:10px\"><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:20px;color:rgb(16,54,91);font-weight:bold\">Joshua McKinney</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:13px\">Project Manager</span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:12px;line-height:35px\"><a href=\"mailto:jmckinney@techego.com\" style=\"text-decoration:none;color:rgb(0,117,168)\" target=\"_blank\">jmckinney@techego.com</a></span><br><span style=\"font-family:Helvetica,Arial,sans-serif;font-size:11px;line-height:20px\"><a href=\"tel:+1-801-404-2973\" style=\"text-decoration:none;color:black\" target=\"_blank\">+1 801-404-2973</a></span><br><a href=\"https://www.facebook.com/TECHeGO/\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.facebook.com/TECHeGO/&amp;source=gmail&amp;ust=1476902483083000&amp;usg=AFQjCNEbTFW06KB2m0WS1mNg1oNlDJXGLA\"><img alt=\"Facebook icon\" src=\"https://ci3.googleusercontent.com/proxy/gpkMWUf-uEw3VvX49S-FDI9V2B3PkSg0sFJZ3-YmygUIlZVh_VTlGoKIuyKHSkxXuiQG1Bvc7pNwdtj0QSvoy4_ln61V_RhvPCKQQcE2FuwQ2CBpoDkCVGKbeTMzWf3-xw=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-facebook.jpg\" style=\"margin-top:10px\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://twitter.com/techego\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://twitter.com/techego&amp;source=gmail&amp;ust=1476902483083000&amp;usg=AFQjCNHjLpo-fkfH7JBVOrcORY1_8M8FYg\"><img alt=\"Twitter icon\" src=\"https://ci4.googleusercontent.com/proxy/31USpyz6LxPjFfp2EJpVXG6P2YxOdJkZ5ySbzD1wWR6bujFoH_1JarVg8o3UU0fYH1wfI_FkHodxAR3tLUBSYNT0sG_B8hV-RJTtCWT8KM8HQ9zV3jE_PciA4uhsU83l=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-twitter.jpg\" class=\"CToWUd\"></a>&nbsp;<a href=\"https://www.linkedin.com/in/jevansmckinney\" target=\"_blank\" data-saferedirecturl=\"https://www.google.com/url?hl=en&amp;q=https://www.linkedin.com/in/jevansmckinney&amp;source=gmail&amp;ust=1476902483083000&amp;usg=AFQjCNGs87fIuW7F_0g5t7mHQtU5ATbPbg\"><img alt=\"LinkedIn icon\" src=\"https://ci4.googleusercontent.com/proxy/Ly7MZUuEb28EdjZ60lKxDjtzCp59erMBASXk7rv4-ctayBtGn6qA3IdnUZwPDKe2b7lN7_L272kEbjoPIilgsh_k5KwB40tIXNxPq4Nr0OxVnbUDltvCoKe48mafssl4GA=s0-d-e1-ft#http://www.techego.com/storage/email/signatures/emailsignature-linkedin.jpg\" class=\"CToWUd\"></a></td></tr></tbody></table></div>";


        //Set Signature Block Depending on the Account Manager
        if($AccountManagerName == "Amanda Campbell"){$Signature = $AmandaSAL;}
        if($AccountManagerName == "Seth Helgeson"){$Signature = $SethSAL;}
        if($AccountManagerName == "Joshua McKinney"){$Signature = $JoshSAL;}


        //Create Message Body for Requests Submitted During Normal Business Hours
        $EmailBody = wordwrap($Summary,150,"<br/>",TRUE);


        //Formatt Email Message 1 (regular business hours)
        $emailMessage .= "<br/>".$EmailBody."<br/>";
        $emailMessage .= "</div>".$Signature."<br/>";

        //Set Email
        $EmailFormatted = $emailMessage;


        //Assemble and Send Email Through Mailgun
        $fields_string = "";

        $url = 'https://api.mailgun.net/v3/mg.techego.com/messages';
        $fields = array(
            'from' => urlencode($AccountManagerEmail),
            'to' => urlencode($ContactEmail),
            'subject' => urlencode($Subject),
            'html' => urlencode($EmailFormatted),
            'bcc' => urlencode($AccountManagerEmail),
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


        //Add Comment To Trigger Item
        $AddComment = PodioComment::create('item',$itemID, array('value'=>"An email has been sent to ".$ContactName."."));

    }


/////////////////END/////////////////////////////////////////////////////////////////////////////////////////////////////////////








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

