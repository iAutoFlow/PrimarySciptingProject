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

//    // Client credentials
//    $username = "podio@techego.com";
//    $password = "hV91Kg$4!oJUxYZ[";
//    $client_key = 'hoistpodiolevel2';
//    $client_secret = 'AwxPc41rfhJJZR8fXskKUou0SBJMRd9NqDKwjAREjk4o7BfMaQ8hYcwYMnSGkzSY';
//
//
//// Authenticate Podio
//    Podio::setup($client_key, $client_secret);
//
//    Podio::authenticate_with_password($username, $password);

    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

    $item = PodioItem::get($item_id);

    $ESUnique = $item->fields['pdid']->values; // text
    $manufacturer = $item->fields['corrected-partner-manufacturer']->values; // text
    $brand = $item->fields['corrected-brand']->values; // text
    $ModelName = $item->fields['title']->values; // text
    $ModelNumber = $item->fields['model-number']->values; // text
    $AdditionalModelInformation = $item->fields['additional-model-information']->values; // text
    $BulbType = $item->fields['corrected-bulb-type']->values; // text
    $PFBulbType = $item->fields['product-finder']->values; // text
    $BaseType = $item->fields['base-type-2']->values; // text
    $LampCategory = $item->fields['lamp-category']->values[0]['text']; // category Single Select
    $Technology = $item->fields['technology']->values[0]['text']; // category Single Select
    $Warranty = $item->fields['warranty-years']->values; // number
    $WattageEquivalency = $item->fields['wattage-equivalency-watts']->values; // number
    $MaxLength = $item->fields['maximum-overall-length-mm']->values; // number
    $MaxDiameter = $item->fields['maximum-overall-diameter-mm']->values; // number
    $CBIntensity = $item->fields['cbcp']->values; // number
    $Wattage = $item->fields['hg-mg']->values; // number
    $BeamAngle = $item->fields['beam-angle']->values; // number
    $Brightness = $item->fields['brightness-lumens']->values; // number
    $EnergyUsed = $item->fields['energy-used-watts']->values; // number
    $Efficacy = $item->fields['efficacy-lumenswatt']->values; // number
    $ThreeWay = $item->fields['three-way']->values[0]['text']; // category Single Select
    $Life = $item->fields['life-hrs']->values; // number
    $LightAppearance = $item->fields['light-appearance-kelvin']->values; // number
    $ColorQuality = $item->fields['color-quality-cri']->values; // number
    $R9 = $item->fields['r9']->values; // number
    $PowerFactor = $item->fields['power-factor']->values; // number
    $MinOpTemp = $item->fields['min-operating-temp-c']->values; // number
    $Dimmable = $item->fields['dim']->values; // text
    $DimsDown = $item->fields['dims-down']->values; // text
    $SpecialFeatures = $item->fields['special-features-cleaned']->values; // text
    $Availability = $item->fields['available-on-market']->values; // text
    $Qualified = $item->fields['qualified']->values; // text
    $Market = $item->fields['corrected-markets']->values; // text
    $CBModel = $item->fields['energy-star-model-identifier']->values; // text
    $QualificationStatus = $item->fields['qualification-status']->values[0]['text']; // category Single Select
    $DateLastQualified = $item->fields['date-last-qualified']->start; // date
    $SourceList = $item->fields['source-list']->values[0]->item_id; // app
    $archiveItemID = $item->fields['lamp-archive']->values[0]->item_id; // app relationship

    $Availability = DateTime::createFromFormat("m/d/Y H:i:s A", $Availability);

    $Qualified = DateTime::createFromFormat("m/d/Y H:i:s A", $Qualified);

    $SpecialFeatures = explode(",", $SpecialFeatures);

    $Market = str_replace(";", ",", $Market);
    $Market = explode(",", $Market);

// end podio get Lamps Item

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
//    //Get Brand Name Item ID
//    if($brand) {
//        $brandFilter = PodioItem::filter(15990835, array('filters' => array('title' => $brand)));
//        $brandID = $brandFilter[0]->item_id;
//        if(!$brandID) {
//            $brandNew = PodioItem::create(15990835, array('fields' => array('title' => $brand)));
//            $brandID = $brandNew->item_id;
//        }
//    }

// Begin podio create, update ES Lamp Archive Item

    //Check for Existing Item in Archive

//    $filterItem = PodioItem::filter(15926757, array('filters'=>array('pdid'=>$ESUnique)));
//
//    $filterItemID = $filterItem[0]->item_id;

    $fieldsArray=array(
        'fields'=>array()
    );

    if($ESUnique){$fieldsArray['fields']['pdid'] =  $ESUnique;} // text
    if($manufacturer){$fieldsArray['fields']['energy-star-partner'] = array((int)$manufacturerID);} // app
    if($brand){$fieldsArray['fields']['brand-name'] = array((int)$brandID);} // app
    if($ModelName){$fieldsArray['fields']['title'] =  $ModelName;} // text
    if($ModelNumber){$fieldsArray['fields']['model-number'] =  $ModelNumber;} // text
    if($AdditionalModelInformation){$fieldsArray['fields']['additional-model-information'] =  $AdditionalModelInformation;} // text
    if($BulbType){$fieldsArray['fields']['bulb-type'] =  $BulbType;} // category Single Select
    if($PFBulbType){$fieldsArray['fields']['product-finder-bulb-type'] =  $PFBulbType;} // category Single Select
    if($BaseType){$fieldsArray['fields']['base-type'] =  $BaseType;} // category Single Select
    if($LampCategory){$fieldsArray['fields']['lamp-category'] =  $LampCategory;} // category Single Select
    if($Technology){$fieldsArray['fields']['technology'] =  $Technology;} // category Single Select
    if($Warranty){$fieldsArray['fields']['warranty-years'] =  $Warranty;} // number
    if($WattageEquivalency){$fieldsArray['fields']['wattage-equivalency-watts'] =  $WattageEquivalency;} // number
    if($MaxLength){$fieldsArray['fields']['maximum-overall-length-mm'] =  $MaxLength;} // number
    if($MaxDiameter){$fieldsArray['fields']['maximum-overall-diameter-mm'] =  $MaxDiameter;} // number
    if($CBIntensity){$fieldsArray['fields']['cbcp'] =  $CBIntensity;} // number
    if($Wattage){$fieldsArray['fields']['hg-mg'] =  $Wattage;} // number
    if($BeamAngle){$fieldsArray['fields']['beam-angle'] =  $BeamAngle;} // number
    if($Brightness){$fieldsArray['fields']['brightness-lumens'] =  $Brightness;} // number
    if($EnergyUsed){$fieldsArray['fields']['energy-used-watts'] =  $EnergyUsed;} // number
    if($Efficacy){$fieldsArray['fields']['efficacy-lumenswatt'] =  $Efficacy;} // number
    if($ThreeWay){$fieldsArray['fields']['three-way'] =  $ThreeWay;} // category Single Select
    if($Life){$fieldsArray['fields']['life-hrs'] =  $Life;} // number
    if($LightAppearance){$fieldsArray['fields']['light-appearance-kelvin'] =  $LightAppearance;} // number
    if($ColorQuality){$fieldsArray['fields']['color-quality-cri'] =  $ColorQuality;} // number
    if($R9){$fieldsArray['fields']['r9'] =  $R9;} // number
    if($PowerFactor){$fieldsArray['fields']['power-factor'] =  $PowerFactor;} // number
    if($MinOpTemp){$fieldsArray['fields']['min-operating-temp-c'] =  $MinOpTemp;} // number
    if($Dimmable){$fieldsArray['fields']['dimmable'] =  $Dimmable;} // category Single Select
    if($DimsDown){$fieldsArray['fields']['dims-down-to'] =  $DimsDown;} // progress
    if($SpecialFeatures){$fieldsArray['fields']['special-features'] =  $SpecialFeatures;} // category MultiSelect
    if($Availability){$fieldsArray['fields']['date-available-on-market'] =  $Availability->format("Y-m-d H:i:s");} // date
    if($Qualified){$fieldsArray['fields']['date-qualified'] =  $Qualified->format("Y-m-d H:i:s");} // date
    if($Market){$fieldsArray['fields']['markets'] =  $Market;} // category MultiSelect
    if($CBModel){$fieldsArray['fields']['energy-star-model-identifier'] =  $CBModel;} // text
    if($QualificationStatus){$fieldsArray['fields']['qualification-status'] =  $QualificationStatus;} // category Single Select
    if($DateLastQualified){$fieldsArray['fields']['date-last-qualified'] =  $DateLastQualified->format("Y-m-d H:i:s");} // date
    if($SourceList){$fieldsArray['fields']['source-list'] = array((int)$SourceList);} // app


//    if($filterItemID){
//        $archiveItem = PodioItem::update($filterItemID, $fieldsArray);
//        $archiveItemID = $filterItemID;
//    }
//    else {
//        $archiveItem = PodioItem::create(15926757, $fieldsArray);
//        $archiveItemID = $archiveItem->item_id;
//    }

    PodioItem::update($archiveItemID, $fieldsArray);

//    PodioItem::update($item_id, array('fields'=>array('lamp-archive'=>$archiveItemID)));

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
            'message' => "Rate Limit: ".Podio::rate_limit()." | Rate Limit Remaining: " .Podio::rate_limit_remaining()." | Error: ".$e,

        ]
    ];

    return;

}