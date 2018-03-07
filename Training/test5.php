//<?php

class PodioSessionManager {

    public function __construct() {
    }

    public function get($authtype = null){
        $filename = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/PodioAuth/ava_auth_token_launch'; ///opt/bitnami/apps/dreamfactory/htdocs/storage/app is where the files are stored
        $access_token = file_get_contents($filename);

        return new PodioOAuth(
            $access_token,
            '3379ce1447f146e8bd110aedfeee3507'
        );
    }
    public function set($oauth, $auth_type = null){
        $filename = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/PodioAuth/ava_auth_token_launch'; ///opt/bitnami/apps/dreamfactory/htdocs/storage/app is where the files are stored
        $access_token = file_put_contents($oauth->access_token);
    }


}


try{

    $client_id = 'launch';
    $client_secret = 'bd3RIfNpJRULcqiZLJb1BIV9Hi944gCOxngylIXeREEx2DSCG35jByI8RgyAtS4M';

    Podio::setup($client_id, $client_secret, array(
        "session_manager" => "PodioSessionManager"
    ));

    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

    $item = PodioItem::get($item_id);

    $pd_id = $item->fields['title']->values; // text
    $manufacturer = $item->fields['corrected-mfr-name']->values; // text
    $brand = $item->fields['corrected-brand-name']->values; // text
    $modelName = $item->fields['modelname']->values; // text
    $modelNumber = $item->fields['modelnumber']->values; // text
    $additionalInformation = $item->fields['additionalmodelinformation']->values; // text
    $intendedUse = $item->fields['intendeduse']->values; // text
    $inOut = $item->fields['intended-usage']->values; // text
    $lightingTechnology = $item->fields['lightingtechnologyused']->values; // text
    $fixtureType = $item->fields['fixturetype']->values; // text
    $lightOutputLumens = $item->fields['lightoutputlumens']->values; // text
    $totalInputPower = $item->fields['totalinputpowerwatts']->values; // text
    $sourceEfficacy = $item->fields['sourceefficacy']->values; // text
    $luminaireEfficacy = $item->fields['luminaireefficacy']->values; // text
    $powerFactor = $item->fields['powerfactor']->values; // text
    $cctk = $item->fields['corrected-cct']->values; // text
    $cri = $item->fields['colorrenderingindexcri']->values; // text
    $lightSourceLife = $item->fields['lightsourcelifehours']->values; // text
    $lighSourcesFixture = $item->fields['lightsourcesperfixture']->values; // text
    $canSize = $item->fields['cansize']->values; // text
    $canRating = $item->fields['canrating']->values; // text
    $lightSourceShape = $item->fields['lightsourceshape']->values; // text
    $lightSourceBaseType = $item->fields['lightsourceconnectionbasetype']->values; // text
    $r9 = $item->fields['r9']->values; // text
    $specialFeatures = $item->fields['corrected-special-feature']->values; // text
    $lightSourceOption = $item->fields['lightsourceoption']->values; // text
    $bulbESPartner = $item->fields['bulbenergystarpartner']->values; // text
    $bulbModelNumber = $item->fields['bulbmodelnumber']->values; // text
    $bulbDimmability = $item->fields['bulbdimmability']->values; // text
    $bulbLightAppearance = $item->fields['bulblightappearancekelvin']->values; // text
    $ESLampUID = $item->fields['energystarlampesuid']->values; // text
    $altESLampUID = $item->fields['alternateenergystarlampsesuid']->values; // text
    $notes = $item->fields['notes']->values; // text
    $dateAvailable = $item->fields['dateavailableonmarket']->values; // text
    $qualificationStatus = $item->fields['qualification-status']->values[0]['text']; // category Single Select
    $dateQualified = $item->fields['datequalified']->values; // text
    $markets = $item->fields['corrected-markets']->values; // text
    $ESModelID = $item->fields['energystarmodelidentifier']->values; // text
    $jsonInfo = $item->fields['jsonadditionalmodelinformation']->values; // text
    $dateLastQualified = $item->fields['date-last-qualified']->start; // date
    $lists = $item->fields['lists']->values[0]->item_id; // app
    $archiveItemID = $item->fields['fixtures-archive']->values[0]->item_id; // app relationship

    $dateAvailable = DateTime::createFromFormat("m/d/Y H:i:s A", $dateAvailable);

    $dateQualified = DateTime::createFromFormat("m/d/Y H:i:s A", $dateQualified);

    $specialFeatures = explode(",", $specialFeatures);

    $lightSourceOption ? $lightSourceOption = explode(",", $lightSourceOption) : $lightSourceOption = null;

    $inOut = str_replace(";", ",", $inOut);
    $inOut = explode(",", $inOut);

    $markets = str_replace(";", ",", $markets);
    $markets = explode(",", $markets);

    $intendedUse == "N/A" ? $intendedUse = null : $intendedUse = $intendedUse;
    $lightSourceShape == "N/A" ? $lightSourceShape = null : $lightSourceShape = $lightSourceShape;
    $lightSourceBaseType == "N/A" ? $lightSourceBaseType = null : $lightSourceBaseType = $lightSourceBaseType;

// end podio get Fixture Item

    //Get Manufacturer Name Item ID
//    if($manufacturer) {
//        $manufacturerFilter = PodioItem::filter(15990820, array('filters' => array('title' => $manufacturer)));
//        $manufacturerID = $manufacturerFilter[0]->item_id;
//        if(!$manufacturerID) {
//            $manufacturerNew = PodioItem::create(15990820, array('fields' => array('title' => $manufacturer)));
//            $manufacturerID = $manufacturerNew->item_id;
//        }
//    }
//
//    //Get Manufacturer Name Item ID
//    if($bulbESPartner) {
//        $bulbESPartnerFilter = PodioItem::filter(15990820, array('filters' => array('title' => $bulbESPartner)));
//        $bulbESPartnerID = $bulbESPartnerFilter[0]->item_id;
//        if(!$bulbESPartnerID) {
//            $bulbESPartnerNew = PodioItem::create(15990820, array('fields' => array('title' => $bulbESPartner)));
//            $bulbESPartnerID = $bulbESPartnerNew->item_id;
//        }
//    }
//
//    //Get Brand Name Item ID
//    if($brand) {
//        $brandFilter = PodioItem::filter(15990835, array('filters' => array('title' => $brand)));
//        $brandID = $brandFilter[0]->item_id;
//        if(!$brandID) {
//            $brandNew = PodioItem::create(15990835, array('fields' => array('title' => $brand)));
//            $brandID = $brandNew->item_id;
//        }
//    }

// Begin podio create, update ES Fixture Archive Item

    //Check for Existing Item in Archive

//    $filterItem = PodioItem::filter(15926758, array('filters'=>array('title'=>$pd_id)));
//
//    $filterItemID = $filterItem[0]->item_id;

    $fieldsArray=array(
        'fields'=>array()
    );

    if($pd_id){$fieldsArray['fields']['title'] =  $pd_id;} // text
    if($manufacturerID){$fieldsArray['fields']['energy-star-partner'] = array((int)$manufacturerID);} // app
    if($brandID){$fieldsArray['fields']['brand-name'] = array((int)$brandID);} // app
    if($modelName){$fieldsArray['fields']['model-name'] =  $modelName;} // text
    if($modelNumber){$fieldsArray['fields']['model-number'] =  $modelNumber;} // text
    if($additionalInformation){$fieldsArray['fields']['additional-model-information'] =  $additionalInformation;} // text
    if($intendedUse){$fieldsArray['fields']['intended-use'] =  $intendedUse;} // category Single Select
    if($inOut){$fieldsArray['fields']['indooroutdoor'] =  $inOut;} // category Single Select
    if($lightingTechnology){$fieldsArray['fields']['technology'] =  $lightingTechnology;} // category Single Select
    if($fixtureType){$fieldsArray['fields']['fixture-type'] =  $fixtureType;} // category Single Select
    if($lightOutputLumens){$fieldsArray['fields']['light-output-lumens'] =  $lightOutputLumens;} // number
    if($totalInputPower){$fieldsArray['fields']['total-input-power-watts'] =  $totalInputPower;} // number
    if($sourceEfficacy){$fieldsArray['fields']['energy-efficiency-measured-at-the-source-lumenswatt'] =  $sourceEfficacy;} // number
    if($luminaireEfficacy){$fieldsArray['fields']['energy-efficiency-measured-outside-the-fixture-lumenswa'] =  $luminaireEfficacy;} // number
    if($powerFactor){$fieldsArray['fields']['power-factor'] =  $powerFactor;} // number
    if($cctk){$fieldsArray['fields']['appearancecorrelated-color-temperature-k-2'] =  $cctk;} // category Single Select
    if($cri){$fieldsArray['fields']['color-quality-cri'] =  $cri;} // number
    if($lightSourceLife){$fieldsArray['fields']['light-source-life-hrs'] =  $lightSourceLife;} // number
    if($lighSourcesFixture){$fieldsArray['fields']['light-sources-per-fixture'] =  $lighSourcesFixture;} // number
    if($canSize){$fieldsArray['fields']['can-sizes'] =  $canSize;} // category Single Select
    if($canRating){$fieldsArray['fields']['can-ratings'] =  $canRating;} // category Single Select
    if($lightSourceShape){$fieldsArray['fields']['light-source-shape'] =  $lightSourceShape;} // category Single Select
    if($lightSourceBaseType){$fieldsArray['fields']['light-source-connectionbase-type'] =  $lightSourceBaseType;} // category Single Select
    if($r9){$fieldsArray['fields']['r9'] =  $r9;} // number
    if($specialFeatures){$fieldsArray['fields']['special-features'] =  $specialFeatures;} // category MultiSelect
    if($lightSourceOption){$fieldsArray['fields']['light-source-options'] =  $lightSourceOption;} // category MultiSelect
    if($bulbESPartnerID){$fieldsArray['fields']['bulb-energy-star-partner'] = array((int)$bulbESPartnerID);} // app
    if($bulbModelNumber){$fieldsArray['fields']['bulb-model-number'] =  $bulbModelNumber;} // text
    if($bulbDimmability){$fieldsArray['fields']['bulb-dimmability'] =  $bulbDimmability;} // category Single Select
    if($bulbLightAppearance){$fieldsArray['fields']['bulb-light-appearance-kelvin'] =  $bulbLightAppearance;} // category Single Select
    if($ESLampUID){$fieldsArray['fields']['energy-star-lamp-esuid'] =  $ESLampUID;} // text
    if($altESLampUID){$fieldsArray['fields']['alternate-energy-star-lamps-esuids'] =  $altESLampUID;} // text
    if($notes){$fieldsArray['fields']['notes'] =  $notes;} // text
    if($dateAvailable){$fieldsArray['fields']['date-available-on-market'] =  $dateAvailable->format("Y-m-d H:i:s");} // date
    if($qualificationStatus){$fieldsArray['fields']['qualification-status'] =  $qualificationStatus;} // category Single Select
    if($dateQualified){$fieldsArray['fields']['date-qualified'] =  $dateQualified->format("Y-m-d H:i:s");} // date
    if($markets){$fieldsArray['fields']['markets'] =  $markets;} // category MultiSelect
    if($ESModelID){$fieldsArray['fields']['energy-star-model-identifier'] =  $ESModelID;} // text
    if($jsonInfo){$fieldsArray['fields']['additional-model-information-json-ml'] =  $jsonInfo;} // text
    if($dateQualified){$fieldsArray['fields']['date-last-qualified'] =  $dateQualified->format("Y-m-d H:i:s");} // date
    if($lists){$fieldsArray['fields']['source-list'] = array((int)$lists);} // app


//    if($filterItemID){
//        $archiveItem = PodioItem::update($filterItemID, $fieldsArray);
//        $archiveItemID = $filterItemID;
//    }
//    else {
//        $archiveItem = PodioItem::create(15926758, $fieldsArray);
//        $archiveItemID = $archiveItem->item_id;
//    }

    PodioItem::update($archiveItemID, $fieldsArray);
//
//    PodioItem::update($item_id, array('fields'=>array('fixtures-archive'=>$archiveItemID)));

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