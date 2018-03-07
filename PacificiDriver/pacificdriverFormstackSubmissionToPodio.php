<?php

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

    $payload = $event['request']['payload'];
    $type = $payload['type'];

    if($type && $type == 'hook.verify'){

        $code = $payload['code'];
        $hook_id = $payload['hook_id'];

        PodioHook::validate($hook_id, array('code' => $code));

    }

    $requestParams = $event['request']['parameters'];
    $revision_id = $requestParams['item_revision_id'];
    $item_id = (int)$requestParams['item_id'];


    if(!$item_id) {
        $item_id = $payload['item_id'];
    }

    if(!$revision_id) {
        $revision_id = $payload['item_revision_id'];
    }

    //$item = PodioItem::get($item_id);

    // https://hoist.thatapp.io/api/v2/pacificdriverFormstackSubmissionToPodio?api_key=1e08b711302bcfa1096eb819b43737125e9550630ea0b98a7b59d9747e3bb634

    // Zweck@410&ox3

///AUTOMATION START///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Les Formstack Variables

    $FS_formID = $payload['FormID'];

    $formID_introToDriving = 2108291;
    $formID_odotCertified = 2106011;
    $formID_adultPrivateLessons = 2108292;

    $formID_contactUS = 2244405;
    $formID_employmentOpp = 2244419;
    $formID_quotes = 2242764;

    $formID_scheduleME = 2632017;

// Les Podio Variables
    $studentRecordsAppID = 18337523;
    $transactionssAppID = 18337544;

    // 	$thisNow = print_r($payload,true);
    //	PodioItem::update(651167200, array('fields'=>array('commenttext'=>$thisNow))); die;


    switch($FS_formID){

        // MHCC Intro To Driving
        case $formID_introToDriving:

            $newTransactionFields = ['fields' => [

            ]];
            $newStudentFields = ['fields' => [

            ]];

            $formName = "Intro to Driving";
            $FS_uniqueID = $payload['UniqueID'];

            $newStudentFields['fields']['form-name'] = $formName;
            $newStudentFields['fields']['formstack-uniqueid'] = $FS_uniqueID;
            $newStudentFields['fields']['formstack-id'] = $FS_formID;
            $newTransactionFields['fields']['formastack-uniqueid'] = $FS_uniqueID;
            $newTransactionFields['fields']['formstack-formid'] = $FS_formID;


            if($payload['UniqueID']){
                $FS_uniqueID = $payload['UniqueID'];
                $newStudentFields['fields']['formstack-uniqueid'] = $FS_uniqueID;
            }

            if($payload['Person Type']){
                $FS_personType = $payload['Person Type'];
                $newStudentFields['fields']['person-type'] = $FS_personType;

            }

            if($payload['Classroom Location and Term']){
                $FS_classLocationTerm = $payload['Classroom Location and Term'];
                $newStudentFields['fields']['title'] = $FS_classLocationTerm;
            }

            //if($payload['Student Name']){
            //$FS_studentName = $payload['Student Name']['first']." ".$payload['Student Name']['last'];
            //}

            if($payload['Student Name']){
                $FS_studentName = $payload['Student Name'];
                $newStudentFields['fields']['student-name'] = $FS_studentName;
            }

            if($payload['Student Birthdate']){
                $FS_studentBirthDateNOFormat = ($payload['Student Birthdate']);
                $birthDate = date_create_from_format("Y-m-d",$FS_studentBirthDateNOFormat);
                //	$birthDate = date_create_from_format("M d, Y",$FS_studentBirthDateNOFormat);
                $birthDateFormatted = $birthDate->format("Y-m-d H:i:s");
                $newStudentFields['fields']['birthday'] = $birthDateFormatted;
            }

            if($payload['Student Email']){
                $FS_studentEmail = $payload['Student Email'];
                $newStudentFields['fields']['studentemail'] = array('type' => "home", 'value' => (string)$FS_studentEmail);
            }

            if($payload['Student Phone Number']){
                $FS_studentPhone = $payload['Student Phone Number'];
                $newStudentFields['fields']['studentcell'][] = array('type' => "mobile", 'value' => (string)$FS_studentPhone); // double-check [0]
            }

            if($payload['Home Phone Number']){
                $FS_homePhone = $payload['Home Phone Number'];
                $newStudentFields['fields']['studentcell'][] = array('type' => "home", 'value' => (string)$FS_homePhone); //[0]
            }

            if($payload['Permit Number']){
                $FS_permitNumber = $payload['Permit Number'];
                $newStudentFields['fields']['permit-license'] = $FS_permitNumber;
            }

            if($payload['Permit Expiration']){
                $FS_permitExpirationNOFormat = $payload['Permit Expiration'];
                $expDate = date_create_from_format("Y-m-d",$FS_permitExpirationNOFormat);
                //$expDate = date_create_from_format("M d, Y",$FS_permitExpirationNOFormat);
                $expDateFormatted = $expDate->format("Y-m-d H:i:s");
                $newStudentFields['fields']['permit-expiration'] = $expDateFormatted;
            }

            if($payload['Home Address']){
                $FS_homeAddress = $payload['Home Address'];
                $newStudentFields['fields']['address'] = array('value' => implode(", ",$FS_homeAddress));
            }

            if($payload['How did you hear about us?']){
                $FS_source = $payload['How did you hear about us?'];
                $newStudentFields['fields']['how-did-you-hear-about-us'] = $FS_source;
            }

            if($payload['Other']){
                $FS_sourceOther = $payload['Other'];
                $newStudentFields['fields']['if-other'] = $FS_sourceOther;
            }

            if($payload['Type of Lesson']){
                $FS_lessonType = $payload['Type of Lesson'];
                $newStudentFields['fields']['number-of-lessons'] = $FS_lessonType;
            }

            if($payload['4 Lessons, Classroom and Drive Test']){
                $FS_cost = $payload['4 Lessons, Classroom and Drive Test'];
                $newTransactionFields['fields']['lesson-total'] = $FS_cost;
            }

            if($payload['Credit Card']){
                $FS_cardNumber = $payload['Credit Card'];
                $newTransactionFields['fields']['credit-card'] = $FS_cardNumber;
            }

            if($payload['Card Verification Code']){
                $FS_verificationCode = $payload['Card Verification Code'];
                $newTransactionFields['fields']['card-verification-code'] = $FS_verificationCode;
            }

            if($payload['Expiration Date']){
                $FS_cardExpirationNOFormat = ($payload['Expiration Date']);
                $cardExpDate = date_create_from_format("Y-m-d",$FS_cardExpirationNOFormat);
                $cardExpDateFormatted = $cardExpDate->format("Y-m-d H:i:s");
                $newTransactionFields['fields']['exp-date'] = $FS_cardExpiration;
                //	 $FS_cardExpiration = "31 ".$FS_cardExpirationNOFormat;
                //	$cardExpDate = date_create_from_format("d M Y",$FS_cardExpiration);
            }

            $theThing = PodioItem::create($transactionssAppID,$newTransactionFields);

            $theThingItemID = $theThing->item_id;

            $newStudentFields['fields']['transactions'] = [$theThingItemID];

            $studentRecordsFilter =	PodioItem::filter( $studentRecordsAppID, ['filters' => ['studentemail' => [$FS_studentEmail]]] );

            if(count($studentRecordsFilter) >= 1){

                foreach($studentRecordsFilter as $matchRecord){

                    $bigArray = [];

                    $matchItemID = $matchRecord->item_id;



                    $matchItem = PodioItem::get($matchItemID);

                    $currentTransactionItems = $matchItem->fields['transactions']->values;

                    foreach($currentTransactionItems as $loopItem){

                        $itemThingID = $itemThing->item_id;

                        array_push($bigArray,$itemThingID);

                    }

                    array_push($bigArray,$theThingItemID);

                    $newStudentFields['fields']['transactions'] = $bigArray;
                    $theThing = PodioItem::update($matchItemID,$newStudentFields);

                }

            } else {

                $theOtherThing = PodioItem::create($studentRecordsAppID,$newStudentFields);

            }

            break;

        // ODOTC ODOT Certified
        case $formID_odotCertified:

            $newTransactionFields = ['fields' => [

            ]];
            $newStudentFields = ['fields' => [

            ]];

            $formName = "ODOT Certified";
            $FS_uniqueID = $payload['UniqueID'];

            $newStudentFields['fields']['form-name'] = $formName;
            $newStudentFields['fields']['formstack-uniqueid'] = $FS_uniqueID;
            $newStudentFields['fields']['formstack-id'] = $FS_formID;
            $newStudentFields['fields']['number-of-lessons'] = "6 Drive Lessons, 30 Hours Classroom";
            $newTransactionFields['fields']['formastack-uniqueid'] = $FS_uniqueID;
            $newTransactionFields['fields']['formstack-formid'] = $FS_formID;


// 					if($payload['UniqueID']){
// 						$FS_uniqueID = $payload['UniqueID'];
// 						$newStudentFields['fields']['formstack-uniqueid'] = $FS_uniqueID;
// 					}

            if($payload['Person Type']){
                $FS_personType = $payload['Person Type'];
                $newStudentFields['fields']['person-type'] = $FS_personType;
            }

            if($payload['Classroom Location and Term']){
                $FS_classLocationTerm = $payload['Classroom Location and Term'];
                $newStudentFields['fields']['title'] = $FS_classLocationTerm;
            }

// 					if($payload['Student Name']){
// 					$FS_studentName = $payload['Student Name']['first']." ".$payload['Student Name']['last'];
// 			    }

            if($payload['Student Name']){
                $FS_studentName = $payload['Student Name'];
                $newStudentFields['fields']['student-name'] = $FS_studentName;
            }

            if($payload['Student Birthdate']){
                $FS_studentBirthDateNOFormat = ($payload['Student Birthdate']);
                //	$birthDate = date_create_from_format("M d, Y",$FS_studentBirthDateNOFormat);
                $birthDate = date_create_from_format("Y-m-d",$FS_studentBirthDateNOFormat);
                $birthDateFormatted = $birthDate->format("Y-m-d H:i:s");
                $newStudentFields['fields']['birthday'] = $birthDateFormatted;
            }

            if($payload['Student Email']){
                $FS_studentEmail = $payload['Student Email'];
                $newStudentFields['fields']['studentemail'] = ['type' => "home", 'value' => (string)$FS_studentEmail];
            }

            if($payload['Student Phone Number']){
                $FS_studentPhone = $payload['Student Phone Number'];
                $newStudentFields['fields']['studentcell'] = [['type' => "mobile", 'value' => (string)$FS_studentPhone]];
            }

            if($payload['Work or Home Number']){
                $FS_studentPhoneTwo = $payload['Work or Home Number'];

            }

            if($payload['Permit Number']){
                $FS_permitNumber = $payload['Permit Number'];
                $newStudentFields['fields']['permit-license'] = $FS_permitNumber;
            }

            if($payload['Permit Expiration']){
                $FS_permitExpirationNOFormat = $payload['Permit Expiration'];
                //	$expDate = date_create_from_format("M d, Y",$FS_permitExpirationNOFormat);
                $expDate = date_create_from_format("Y-m-d",$FS_permitExpirationNOFormat);
                $expDateFormatted = $expDate->format("Y-m-d H:i:s");

            }

            if($payload['Your High School']){
                $FS_highSchool = $payload['Your High School'];
                $newStudentFields['fields']['highschool'] = $FS_highSchool;
            }

            //	if($payload['Parent or Guardian']){
            //	$FS_parentName = $payload['Parent or Guardian']['first']." ".$payload['Parent or Guardian']['last'];
            //	}

            if($payload['Parent or Guardian']){
                $FS_parentName = $payload['Parent or Guardian'];
                $newStudentFields['fields']['parent-guardian-name'] = $FS_parentName;
            }

            if($payload['Parent Cell Phone']){
                $FS_parentCell = $payload['Parent Cell Phone'];
                $newStudentFields['fields']['parent-mobile-phone'] = ['type' => "mobile", 'value' => (string)$FS_parentCell];
            }

            if($payload['Parent Email']){
                $FS_parentEmail = $payload['Parent Email'];
                $newStudentFields['fields']['parent-email'] = ['type' => "home", 'value' => (string)$FS_parentEmail];
            }

            // 			if($payload['Parent #2']){
            // 				$FS_parentNameTwo = $payload['Parent #2']['first']." ".$payload['Parent #2']['last'];
            // 			}

            if($payload['Parent #2']){
                $FS_parentNameTwo = $payload['Parent #2'];
                $newStudentFields['fields']['secondary-parent-guardian-name'] = $FS_parentNameTwo;
            }

            if($payload['Parent#2 Email']){
                $FS_parentEmailTwo = $payload['Parent#2 Email'];
                $newStudentFields['fields']['secondary-parent-email'] = ['type' => "home", 'value' => (string)$FS_parentEmailTwo];
            }

            if($payload['Parent #2 Cell']){
                $FS_parentCellTwo = $payload['Parent #2 Cell'];
                $newStudentFields['fields']['secondary-parent-mobilephone'] = ['type' => "mobile", 'value' => (string)$FS_parentCellTwo];
            }

            if($payload['Home Address']){
                $FS_homeAddress = $payload['Home Address'];
                $newStudentFields['fields']['address'] = ['value' => implode(", ",$FS_homeAddress)];
            }

            if($payload['How did you hear about us?']){
                $FS_source = $payload['How did you hear about us?'];
                $newStudentFields['fields']['how-did-you-hear-about-us'] = $FS_source;
            }

            if($payload['Other']){
                $FS_sourceOther = $payload['Other'];
                $newStudentFields['fields']['if-other'] = $FS_sourceOther;
            }

            if($payload['Enter your discount code here']){
                $FS_discountCode = $payload['Enter your discount code here'];
                $newTransactionFields['fields']['discount-code'] = $FS_discountCode;
            }

            if($payload['Total Amount']){
                $FS_totalAmount = $payload['Total Amount'];
                $newTransactionFields['fields']['lesson-total'] = $FS_totalAmount;
            }

            if($payload['Credit Card']){
                $FS_cardNumber = $payload['Credit Card'];
                $newTransactionFields['fields']['credit-card'] = $FS_cardNumber;
            }

            if($payload['Card Verification Code']){
                $FS_verificationCode = $payload['Card Verification Code'];
                $newTransactionFields['fields']['card-verification-code'] = $FS_verificationCode;
            }

            if($payload['Expiration Date']){
                $FS_cardExpirationNOFormat = ($payload['Expiration Date']);
                // 				$FS_cardExpiration = "31 ".$FS_cardExpirationNOFormat;
                // 				$cardExpDate = date_create_from_format("d M Y",$FS_cardExpiration);
                $cardExpDate = date_create_from_format("Y-m-d",$FS_cardExpirationNOFormat);
                $cardExpDateFormatted = $cardExpDate->format("Y-m-d H:i:s");
                $newTransactionFields['fields']['exp-date'] = $cardExpDateFormatted;
            }

            //create transactions

            $theThing = PodioItem::create($transactionssAppID,$newTransactionFields);

            $theThingItemID = $theThing->item_id;

            $newStudentFields['fields']['transactions'] = [$theThingItemID];



            $studentRecordsFilter =	PodioItem::filter( $studentRecordsAppID, ['filters' => ['studentemail' => [$FS_studentEmail]]] );

            if(count($studentRecordsFilter) >= 1){

                foreach($studentRecordsFilter as $matchRecord){

                    $bigArray = [];

                    $matchItemID = $matchRecord->item_id;

                    //

                    $matchItem = PodioItem::get($matchItemID);

                    $currentTransactionItems = $matchItem->fields['transactions']->values; //[0]->item_id;

                    foreach($currentTransactionItems as $loopItem){

                        $itemThingID = $itemThing->item_id;

                        array_push($bigArray,$itemThingID);

                    }

                    array_push($bigArray,$theThingItemID);

                    $newStudentFields['fields']['transactions'] = $bigArray;

                    $theThing = PodioItem::update($matchItemID,$newStudentFields);


                }


            } else {

                $theOtherThing = PodioItem::create($studentRecordsAppID,$newStudentFields);

            }



            break;

        case $formID_adultPrivateLessons:


            $newTransactionFields = ['fields' => [

            ]];
            $newStudentFields = ['fields' => [

            ]];

            $formName = "Adult Private Lessons";
            $FS_uniqueID = $payload['UniqueID'];

            $newStudentFields['fields']['form-name'] = $formName;
            $newStudentFields['fields']['formstack-uniqueid'] = $FS_uniqueID;
            $newStudentFields['fields']['formstack-id'] = $FS_formID;
            $newTransactionFields['fields']['formastack-uniqueid'] = $FS_uniqueID;
            $newTransactionFields['fields']['formstack-formid'] = $FS_formID;


            if($payload['Number of Lessons']){
                $FS_numLessons = $payload['Number of Lessons'];
                $newStudentFields['fields']['number-of-lessons'] = $FS_numLessons;
            }

            if($payload['Person Type']){
                $FS_personType = $payload['Person Type'];
                $newStudentFields['fields']['person-type'] = $FS_personType;
            }

// 			if($payload['Student Name']){
// 				$FS_studentName = $payload['Student Name']['first']." ".$payload['Student Name']['last'];
// 			}

            if($payload['Student Name']){
                $FS_studentName = $payload['Student Name'];
                $newStudentFields['fields']['student-name'] = $FS_studentName;
            }

            if($payload['Birthdate']){
                $FS_studentBirthDateNOFormat = ($payload['Birthdate']);
                $birthDate = date_create_from_format("Y-m-d",$FS_studentBirthDateNOFormat);
                $birthDateFormatted = $birthDate->format("Y-m-d H:i:s");
                $newStudentFields['fields']['birthday'] = $birthDateFormatted;
            }

            if($payload['Student Email']){
                $FS_studentEmail = $payload['Student Email'];
                $newStudentFields['fields']['studentemail'] = ['type' => "home", 'value' => (string)$FS_studentEmail];
            }

            if($payload['Student Cell Number']){
                $FS_studentPhone = $payload['Student Cell Number'];
                $newStudentFields['fields']['studentcell'] = ['type' => "mobile", 'value' => (string)$FS_studentPhone];
            }

            if($payload['Work or Home Number'] && $payload['Student Cell Number']){
                $FS_studentPhoneTwo = $payload['Work or Home Number'];
                $newStudentFields['fields']['studentcell'] = [['type' => "mobile", 'value' => (string)$FS_studentPhone],['type' => "home", 'value' => (string)$FS_studentPhoneTwo]];
            }


            if($payload['What is your License/Permit Nbr?']){
                $FS_permitNumber = $payload['What is your License/Permit Nbr?'];
                $newStudentFields['fields']['permit-license'] = $FS_permitNumber;
            }

            if($payload['Home Address']){
                $FS_homeAddress = $payload['Home Address'];
                $newStudentFields['fields']['address'] = ['value' => implode(", ",$FS_homeAddress)];
            }

            if($payload['How did you hear about us?']){
                $FS_source = $payload['How did you hear about us?'];
                $newStudentFields['fields']['how-did-you-hear-about-us'] = $FS_source;
            }

            if($payload['Other']){
                $FS_sourceOther = $payload['Other'];
                $newStudentFields['fields']['if-other'] = $FS_sourceOther;
            }

            if($payload['Enter your discount code here']){
                $FS_discountCode = $payload['Enter your discount code here'];
                $newTransactionFields['fields']['discount-code'] = $FS_discountCode;
            }

            if($payload['Credit Card']){
                $FS_cardNumber = $payload['Credit Card'];
                $newTransactionFields['fields']['credit-card'] = $FS_cardNumber;
            }

            if($payload['Card Verification Code']){
                $FS_verificationCode = $payload['Card Verification Code'];
                $newTransactionFields['fields']['card-verification-code'] = $FS_verificationCode;
            }

            if($payload['Expiration Date']){
                $FS_cardExpirationNOFormat = ($payload['Expiration Date']);
                $FS_cardExpiration = date_create_from_format("Y-m-d",$FS_cardExpirationNOFormat);
                $cardExpDateFormatted = $FS_cardExpiration->format("Y-m-d H:i:s");
                $newTransactionFields['fields']['exp-date'] = $cardExpDateFormatted;
            }

            if($payload['1 Lesson Price'] != ""){
                $FS_totalAmount = $payload['1 Lesson Price'];
                $newTransactionFields['fields']['lesson-total'] = $FS_totalAmount;
            }

            if($payload['4 Lesson Price'] != ""){
                $FS_totalAmount = $payload['4 Lesson Price'];
                $newTransactionFields['fields']['lesson-total'] = $FS_totalAmount;
            }


            $theThing = PodioItem::create($transactionssAppID,$newTransactionFields);

            $theThingItemID = $theThing->item_id;

            if($theThingItemID){$newStudentFields['fields']['transactions'] = [$theThingItemID];}


            $studentRecordsFilter =	PodioItem::filter( $studentRecordsAppID, ['filters' => ['studentemail' => [$FS_studentEmail]]] );

            if(count($studentRecordsFilter) >= 1){

                foreach($studentRecordsFilter as $matchRecord){

                    $matchItemID = $matchRecord->item_id;

                    $bigArray = [];


                    $matchItem = PodioItem::get($matchItemID);

                    $currentTransactionItems = $matchItem->fields['transactions']->values;


                    foreach($currentTransactionItems as $loopItem){

                        $itemThingID = $itemThing->item_id;

                        array_push($bigArray,$itemThingID);

                    }

                    array_push($bigArray,$theThingItemID);

                    $newStudentFields['fields']['transactions'] = $bigArray;
                    $newStudentFields['fields']['lesson-status'] = 4;//['text'=>'Not Yet Scheduled'];

                    $theThing = PodioItem::update($matchItemID,$newStudentFields);


                }


            } else {

                $theOtherThing = PodioItem::create($studentRecordsAppID,$newStudentFields);

            }



            break;

    }





///END AUTOMATION/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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