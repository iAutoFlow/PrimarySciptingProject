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

    $productID = $item->fields['title']->values; // text
    $manufacturer = $item->fields['corrected-manufacturer-name']->values; // text
    $brand = $item->fields['corrected-brand-name']->values; // text
    $modelNumber = $item->fields['model-number']->values; // text
    $category = $item->fields['cat']->values; // text
    $generalApplication = $item->fields['application']->values; // text
    $primaryUsage = $item->fields['primary-usage']->values; // text
    $retrofit = $item->fields['retrofit']->values; // text
    $classification = $item->fields['classification']->values[0]['text']; // category Single Value
    $parentProduct = $item->fields['parent']->values[0]['text']; // category Single Value
    $measuredLight = $item->fields['measured-light-output-lm']->values; // number
    $measuredLuminaire = $item->fields['measured-luminaire-efficacy-lmw']->values; // number
    $measuredWattage = $item->fields['measured-wattage-w']->values; // number
    $measuredTHD = $item->fields['total-harmonic-distortion-thd']->values; // number
    $measuredPower = $item->fields['measured-power-factor']->values; // number
    $measuredCCT = $item->fields['measured-cct-k']->values; // number
    $measuredCRI = $item->fields['measured-cri']->values; // number
    $warranty = $item->fields['warranty-years']->values; // number
    $ratedHours = $item->fields['rated-lifetime-hours']->values; // number
    $dateQualified = $item->fields['qualified']->values; // text
    $dateDelisted = $item->fields['delisted']->values; // text
    $dimmingStatus = $item->fields['dimming-status-dimmable']->values[0]['text']; // category Single Value
    $dimmingType = $item->fields['dimming-type']->values[0]['text']; // category Single Value
    $IntegratedControls = $item->fields['has-integrated-controls']->values[0]['text']; // category Single Value
    $notes = $item->fields['notes']->values; // text
    $familyCode = $item->fields['fc']->values; // text
    $zl_090 = $item->fields['zonal-lumens-0-90deg']->values; // number
    $zl_8090 = $item->fields['zonal-lumens-80-90deg']->values; // number
    $zl_90110 = $item->fields['zonal-lumens-90-110deg']->values; // number
    $zl_110 = $item->fields['zonal-lumens-110deg']->values; // number
    $zl_2040 = $item->fields['zonal-lumens-20-40deg']->values; // number
    $zl_6070 = $item->fields['zonal-lumens-60-70deg']->values; // number
    $zl_7080 = $item->fields['zonal-lumens-70-80deg']->values; // number
    $zl_6080 = $item->fields['zonal-lumens-60-80deg']->values; // number
    $zl_040 = $item->fields['zonal-lumens-0-40deg']->values; // number
    $zl_4070 = $item->fields['zonal-lumens-40-70deg']->values; // number
    $zl_1090 = $item->fields['zonal-lumens-10-90deg']->values; // number
    $zl_080 = $item->fields['zonal-lumens-0-80deg']->values; // number
    $zl_2050 = $item->fields['zonal-lumens-20-50deg']->values; // number
    $zl_020 = $item->fields['zonal-lumens-0-20deg']->values; // number
    $zl_3060 = $item->fields['zonal-lumens-30-60deg']->values; // number
    $zl_060 = $item->fields['zonal-lumens-0-60deg']->values; // number
    $zl_90150 = $item->fields['zonal-lumens-90-150deg']->values; // number
    $sc_0180 = $item->fields['spacing-criteria-0-180deg']->values; // number
    $sc_90270 = $item->fields['spacing-criteria-90-270deg']->values; // number
    $sc_0180T1 = $item->fields['spacing-criteria-0-180deg-troffer-1']->values; // number
    $sc_90270T1 = $item->fields['spacing-criteria-90-270deg-troffer-1']->values; // number
    $sc_0180T2 = $item->fields['spacing-criteria-0-180deg-troffer-2']->values; // number
    $sc_90270T2 = $item->fields['spacing-criteria-90-270deg-troffer-2']->values; // number
    $nemaBeam_0180 = $item->fields['nema-beam-type-0-180deg']->values[0]['text']; // category Single Value
    $nemaBeam_90270 = $item->fields['nema-beam-type-90-270deg']->values[0]['text']; // category Single Value
    $technicalRequirements = $item->fields['tr']->values; // text
    $ratedLightOutput = $item->fields['rated-light-output-lm']->values; // number
    $ratedLuminaireEfficacy = $item->fields['rated-luminaire-efficacy-lmw']->values; // number
    $ratedWattage = $item->fields['rated-wattage-w']->values; // number
    $ratedTHD = $item->fields['rated-total-harmonic-distortion-thd']->values; // number
    $ratedPF = $item->fields['rated-power-factor']->values; // number
    $ratedCCT = $item->fields['rated-cct-k']->values; // number
    $ratedCRI = $item->fields['rated-cri']->values; // number
    $fidelity = $item->fields['fidelity-index-rf']->values; // number
    $gamut = $item->fields['gamut-index-rg']->values; // number
    $luminaireLength = $item->fields['luminaire-length']->values; // number
    $qualificationStatus = $item->fields['qualification-status']->values[0]['text']; // category Single Value
    $dateLastQualified = $item->fields['date-last-qualified']->start; // date
    $sourceList = $item->fields['source-list']->values[0]->item_id; // app relationship
    $archiveItemID = $item->fields['dlcarchive']->values[0]->item_id; // app relationship

    if($parentProduct == "1"){$parentProduct = "True";}
    if($parentProduct == "0"){$parentProduct = "False";}

    $dateQualified = DateTime::createFromFormat("m/d/Y", $dateQualified);

    $dateDelisted = DateTime::createFromFormat("m/d/Y", $dateDelisted);

    $dateLastQualified = DateTime::createFromFormat("m/d/Y", $dateLastQualified);

// end podio get DLC Item

//    //Get Manufacturer Name Item ID
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
//
//    //Get Family Code Item ID
//    if($familyCode) {
//        $familyCodeFilter = PodioItem::filter(15990836, array('filters' => array('title' => $familyCode)));
//        $familyCodeID = $familyCodeFilter[0]->item_id;
//        if(!$familyCodeID) {
//            $familyCodeNew = PodioItem::create(15990836, array('fields' => array('title' => $familyCode)));
//            $familyCodeID = $familyCodeNew->item_id;
//        }
//    }
//
//    //Get Technical Requirements Item ID
//    if($technicalRequirements) {
//        $TRFilter = PodioItem::filter(15990988, array('filters' => array('title' => $technicalRequirements)));
//        $TRID = $TRFilter[0]->item_id;
//        if(!$TRID) {
//            $TRNew = PodioItem::create(15990988, array('fields' => array('title' => $technicalRequirements, 'related-source-qpl' => $sourceList)));
//            $TRID = $TRNew->item_id;
//        }
//    }

// Begin podio create, update DLC Archive Item

    //Check for Existing Item in Archive

//    $filterItem = PodioItem::filter(15926747, array('filters'=>array('title'=>$productID)));
//
//    $filterItemID = $filterItem[0]->item_id;

    $fieldsArray=array(
        'fields'=>array()
    );

    if($productID){$fieldsArray['fields']['title'] = $productID;}
    if($manufacturerID){$fieldsArray['fields']['manufacturer-name'] = array((int)$manufacturerID);} // app relationship
    if($brandID){$fieldsArray['fields']['brand-name'] = array( (int)$brandID);} // app relationship
    if($modelNumber){$fieldsArray['fields']['model-number'] = $modelNumber;} // text
    if($category){$fieldsArray['fields']['category'] = $category;} // category Single Value
    if($generalApplication){$fieldsArray['fields']['general-application'] = $generalApplication;} // category Single Value
    if($primaryUsage){$fieldsArray['fields']['primary-use'] = $primaryUsage;} // category Single Value
    //if($retrofit){$fieldsArray['fields']['retrofit'] = $retrofit;} // category Single Value
    if($classification){$fieldsArray['fields']['classification'] = $classification;} // category Single Value
    if($parentProduct){$fieldsArray['fields']['parent'] = $parentProduct;} // category Single Value
    if($measuredLight){$fieldsArray['fields']['measured-light-output-lm'] = $measuredLight;} // number
    if($measuredLuminaire){$fieldsArray['fields']['measured-luminaire-efficacy-lmw'] = $measuredLuminaire;} // number
    if($measuredWattage){$fieldsArray['fields']['measured-wattage-w'] = $measuredWattage;} // number
    if($measuredTHD){$fieldsArray['fields']['total-harmonic-distortion-thd'] = $measuredTHD;} // number
    if($measuredPower){$fieldsArray['fields']['measured-power-factor'] = $measuredPower;} // number
    if($measuredCCT){$fieldsArray['fields']['measured-cct-k'] = $measuredCCT;} // number
    if($measuredCRI){$fieldsArray['fields']['measured-cri'] = $measuredCRI;} // number
    if($warranty){$fieldsArray['fields']['warranty-years'] = $warranty;} // number
    if($ratedHours){$fieldsArray['fields']['rated-lifetime-hours'] = $ratedHours;} // number
    if($dateQualified){$fieldsArray['fields']['date-qualified'] = $dateQualified->format("Y-m-d H:i:s");} // date
    if($dateDelisted){$fieldsArray['fields']['date-de-listed'] = $dateDelisted->format("Y-m-d H:i:s");} // date
    if($dimmingStatus){$fieldsArray['fields']['dimming-status-dimmable'] = $dimmingStatus;} // category Single Value
    if($dimmingType){$fieldsArray['fields']['dimming-type'] = $dimmingType;} // category Single Value
    if($IntegratedControls){$fieldsArray['fields']['has-integrated-controls'] = $IntegratedControls;} // category Single Value
    if($notes){$fieldsArray['fields']['notes'] = $notes;} // text
    if($familyCodeID){$fieldsArray['fields']['family-code'] = array( (int)$familyCodeID);} // app relationship
    if($zl_090){$fieldsArray['fields']['zonal-lumens-0-90deg'] = $zl_090;} // number
    if($zl_8090){$fieldsArray['fields']['zonal-lumens-80-90deg'] = $zl_8090;} // number
    if($zl_90110){$fieldsArray['fields']['zonal-lumens-90-110deg'] = $zl_90110;} // number
    if($zl_110){$fieldsArray['fields']['zonal-lumens-110deg'] = $zl_110;} // number
    if($zl_2040){$fieldsArray['fields']['zonal-lumens-20-40deg'] = $zl_2040;} // number
    if($zl_6070){$fieldsArray['fields']['zonal-lumens-60-70deg'] = $zl_6070;} // number
    if($zl_7080){$fieldsArray['fields']['zonal-lumens-70-80deg'] = $zl_7080;} // number
    if($zl_6080){$fieldsArray['fields']['zonal-lumens-60-80deg'] = $zl_6080;} // number
    if($zl_040){$fieldsArray['fields']['zonal-lumens-0-40deg'] = $zl_040;} // number
    if($zl_4070){$fieldsArray['fields']['zonal-lumens-40-70deg'] = $zl_4070;} // number
    if($zl_1090){$fieldsArray['fields']['zonal-lumens-10-90deg'] = $zl_1090;} // number
    if($zl_080){$fieldsArray['fields']['zonal-lumens-0-80deg'] = $zl_080;} // number
    if($zl_2050){$fieldsArray['fields']['zonal-lumens-20-50deg'] = $zl_2050;} // number
    if($zl_020){$fieldsArray['fields']['zonal-lumens-0-20deg'] = $zl_020;} // number
    if($zl_3060){$fieldsArray['fields']['zonal-lumens-30-60deg'] = $zl_3060;} // number
    if($zl_060){$fieldsArray['fields']['zonal-lumens-0-60deg'] = $zl_060;} // number
    if($zl_90150){$fieldsArray['fields']['zonal-lumens-90-150deg'] = $zl_90150;} // number
    if($sc_0180){$fieldsArray['fields']['spacing-criteria-0-180deg'] = $sc_0180;} // number
    if($sc_90270){$fieldsArray['fields']['spacing-criteria-90-270deg'] = $sc_90270;} // number
    if($sc_0180T1){$fieldsArray['fields']['spacing-criteria-0-180deg-troffer-1'] = $sc_0180T1;} // number
    if($sc_90270T1){$fieldsArray['fields']['spacing-criteria-90-270deg-troffer-1'] = $sc_90270T1;} // number
    if($sc_0180T2){$fieldsArray['fields']['spacing-criteria-0-180deg-troffer-2'] = $sc_0180T2;} // number
    if($sc_90270T2){$fieldsArray['fields']['spacing-criteria-90-270deg-troffer-2'] = $sc_90270T2;} // number
    if($nemaBeam_0180){$fieldsArray['fields']['nema-beam-type-0-180deg'] = $nemaBeam_0180;} // category Single Value
    if($nemaBeam_90270){$fieldsArray['fields']['nema-beam-type-90-270deg'] = $nemaBeam_90270;} // category Single Value
    if($TRID){$fieldsArray['fields']['technical-requirements'] = array( (int)$TRID);} // app relationship
    if($ratedLightOutput){$fieldsArray['fields']['rated-light-output-lm'] = $ratedLightOutput;} // number
    if($ratedLuminaireEfficacy){$fieldsArray['fields']['rated-luminaire-efficacy-lmw'] = $ratedLuminaireEfficacy;} // number
    if($ratedWattage){$fieldsArray['fields']['rated-wattage-w'] = $ratedWattage;} // number
    if($ratedTHD){$fieldsArray['fields']['rated-total-harmonic-distortion-thd'] = $ratedTHD;} // number
    if($ratedPF){$fieldsArray['fields']['rated-power-factor'] = $ratedPF;} // number
    if($ratedCCT){$fieldsArray['fields']['rated-cct-k'] = $ratedCCT;} // number
    if($ratedCRI){$fieldsArray['fields']['rated-cri'] = $ratedCRI;} // number
    if($fidelity){$fieldsArray['fields']['fidelity-index-rf'] = $fidelity;} // number
    if($gamut){$fieldsArray['fields']['gamut-index-rg'] = $gamut;} // number
    if($luminaireLength){$fieldsArray['fields']['luminaire-length'] = $luminaireLength;} // number
    if($qualificationStatus){$fieldsArray['fields']['qualification-status'] = $qualificationStatus;} // category Single Value
    if($dateLastQualified){$fieldsArray['fields']['date-last-confirmed-qualified'] = $dateLastQualified->format("Y-m-d H:i:s");} // date
    if($sourceList){$fieldsArray['fields']['source-list'] = array( (int)$sourceList);} // app relationship


//    if($filterItemID){
//        $archiveItem = PodioItem::update($filterItemID, $fieldsArray);
//    }
//    else {
//        $archiveItem = PodioItem::create(15926747, $fieldsArray);
//    }$archiveItem

    PodioItem::update($archiveItemID, $fieldsArray);

//    PodioItem::update($item_id, array('fields'=>array('dlcarchive'=>$archiveItem->item_id)));

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