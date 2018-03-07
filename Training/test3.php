//<?php

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function sendEmail($item_id, $item){



}

function testSendEmail($item_id, $item){

    $fromEmail = $item->fields['from']->values;
    $testTo = $item->fields['send-to']->values;
    $emailSubject = $item->fields['subject']->values;
    $greeting = $item->fields['greeting']->values;
    $greetingNamesList = $item->fields['greeting-name']->values;
    $mailingListItems = $item->fields['mailing-list-2']->values;
    $messageBody = $item->fields['body']->values;
    $signature = $item->fields['signature']->values;
    $cssStyle = $item->fields['style']->values;

    function sort_multi_cat($a, $b)
    {
        return strnatcmp($a['id'], $b['id']);
    }

    usort($greetingNamesList, 'sort_multi_cat');

    preg_match_all('/{(.*?)}/', $messageBody, $bodyVars);

    $stateFilter = PodioItem::filter_by_view(14448079, 27315098, array('limit'=>60));

    $sendCount = 0;
    try {
        foreach($stateFilter as $state) {
            //initialize vars
            $variables = array();

            $stateName = $state->fields['title']->values;

            $i = 0;

            foreach($bodyVars[1] as $bodyVar) {

                $splitVars = explode(",", $bodyVar);

                $appID = $splitVars[0];

                if($appID == "STATE") {
                    $variables[$i] = $stateName;
                    $i++;
                    continue;
                }

                if($appID == "CENTER") {
                    $variables[$i] = '<div style="text-align:center">';
                    $i++;
                    continue;
                }

                if($appID == "END CENTER") {
                    $variables[$i] = '</div>';
                    $i++;
                    continue;
                }

                $fieldID = $splitVars[1];

                $varFilter = PodioItem::filter($appID, array('filters' => array('state' => ($state->item_id))));

                foreach($varFilter[0]->fields as $field) {
                    if($field->field_id == $fieldID) {
                        $fieldType = $field->type;

                        if($fieldType == "text" || $fieldType == "calculation") {
                            $variables[$i] = $field->values;
                        } elseif($fieldType == "email" || $fieldType == "phone") {
                            $variables[$i] = $field->values[0]['value'];
                        } elseif($fieldType == "category") {
                            $variables[$i] = $field->values[0]['text'];
                        }
                        $i++;
                    }
                }
            }

            foreach($mailingListItems as $list){

                $listPrefix = "";
                $listFirst = "";
                $listMiddle   = "";
                $listLast = "";
                $listTitle = "";
                $listEmail = "";
                $appID = "";
                $stateField = "";
                $currentList = "";

                $currentList = PodioItem::get($list->item_id);

                $appID = $currentList->fields['app-id-calculated']->values;
                $stateField = $currentList->fields['state-calculated']->values;

                $listFilter = PodioItem::filter($appID, array(
                    'filters'=>array(
                        $stateField=>$state->item_id
                    )
                ));

                foreach($listFilter as $item) {

                    foreach($item->fields as $field) {
                        if($field->field_id == $currentList->fields['prefix-calculated']->values) {
                            $listPrefix = $field->values;
                        }
                        if($field->field_id == $currentList->fields['first-calculated']->values) {
                            $listFirst = $field->values;
                        }
                        if($field->field_id == $currentList->fields['middle-calculated']->values) {
                            $listMiddle = $field->values;
                        }
                        if($field->field_id == $currentList->fields['last-calculated']->values) {
                            $listLast = $field->values;
                        }
                        if($field->field_id == $currentList->fields['title-calculated']->values) {
                            $listTitle = $field->values;
                        }
                        if($field->field_id == $currentList->fields['email-calculated']->values) {
                            $listEmail = $field->values[0]['value'];
                        }

                    }

                    if(!$listEmail){
                        continue;
                    }

                    $emailMessage = "<div ".$cssStyle.">";
                    $emailMessage .= "WOULD BE SENT TO: ".$listEmail."<br/>";
                    $emailMessage .= $greeting." ";

                    $n = 0;
                    foreach($greetingNamesList as $name){
                        if($n > 0 && $n < count($greetingNamesList)){
                            $emailMessage .= " ";
                        }
                        if($name['text'] == "Prefix"){
                            $emailMessage.=$listPrefix;
                        }
                        if($name['text'] == "First"){
                            $emailMessage.=$listFirst;
                        }
                        if($name['text'] == "Middle"){
                            $emailMessage.=$listMiddle;
                        }
                        if($name['text'] == "Last"){
                            $emailMessage.=$listLast;
                        }
                        if($name['text'] == "Title"){
                            $emailMessage.="- ".$listTitle;
                        }
                        $n++;
                    }

                    $emailMessage .= ",<br/>".$messageBody;
                    $emailMessage .= $signature;
                    $emailMessage .= "</div>";

                    $j = 0;

                    foreach($bodyVars[0] as $bodyVar) {
                        $emailMessage = str_replace($bodyVar, $variables[$j], $emailMessage);
                        $j++;
                    }

                    $fields_string = "";

                    $url = 'https://api.mailgun.net/v3/ussyp.hearstfdn.org/messages';
                    $fields = array(
                        'from' => urlencode($fromEmail),
                        'to' => urlencode($testTo),
                        'subject' => urlencode($emailSubject),
                        'html' => urlencode($emailMessage)
                    );

//url-ify the data for the POST
                    foreach($fields as $key => $value) {
                        $fields_string .= $key . '=' . $value . '&';
                    }
                    rtrim($fields_string, '&');

        //open connection
                    $ch = curl_init();
                    $user = 'api:key-414be2331f79fbda528d51a18b253af0';

        //set the url, number of POST vars, POST data
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, count($fields));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                    curl_setopt($ch, CURLOPT_USERPWD, $user);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
                    $result = curl_exec($ch);

                    $sendCount++;

        //close connection
                    curl_close($ch);

                }


            }


        }//end state loop

        PodioItem::update($item_id, array('fields'=>array('send-email'=>array())));
        PodioComment::create('item', $item_id, array('value'=>$sendCount." Emails Sent!"));

    }
    catch(Exception $e){
        PodioItem::update($item_id, array('fields'=>array('send-email'=>array())));
        PodioComment::create('item', $item_id, array('value'=>"Error. ".$sendCount." Emails were sent before the error."));
        throw new Exception('Error Sending Emails: '.$e);
    }



}

try{

    $username = "podio@techego.com";
    $password = "hV91Kg$4!oJUxYZ[";
    $client_key = 'dreamfactory-ebqqb5';
    $client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';

    Podio::setup($client_key, $client_secret);
    Podio::authenticate_with_password($username, $password);

    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

    $item = PodioItem::get($item_id);

    $sendTrigger = $item->fields['send-email']->values[0]['text'];

    switch ($sendTrigger) {

        case 'Send':
            sendEmail($item_id, $item);
            break;
        case 'Test Send':
            testSendEmail($item_id, $item);
            break;
        default:
            throw new Exception('Category Field not set to "Send" or "Test Send"');
    }

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