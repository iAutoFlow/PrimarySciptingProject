//<?php

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
    $result = array();

    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];

    $item = PodioItem::get($item_id);

    $pd_id = $item->fields['title']->values; // text
    $manufacturer = $item->fields['energy-star-partner']->values[0]->item_id; // app
    $brand = $item->fields['brand-name']->values[0]->item_id; // app
    $modelName = $item->fields['model-name']->values; // text
    $modelNumber = $item->fields['model-number']->values; // text
    $additionalInformation = $item->fields['additional-model-information']->values; // text
    $intendedUse = $item->fields['intended-use']->values[0]['text']; // cat
    $fixtureType = $item->fields['fixture-type']->values[0]['text']; // cat
    $lightOutputLumens = $item->fields['light-output-lumens']->values; // num
    $totalInputPower = $item->fields['total-input-power-watts']->values; // num
    $sourceEfficacy = $item->fields['energy-efficiency-measured-at-the-source-lumenswatt']->values; // num
    $luminaireEfficacy = $item->fields['energy-efficiency-measured-outside-the-fixture-lumenswa']->values; // num
    $powerFactor = $item->fields['power-factor']->values; // num
    $cri = $item->fields['color-quality-cri']->values; // num
    $lightSourceLife = $item->fields['light-source-life-hrs']->values; // num
    $canRating = $item->fields['can-ratings']->values[0]['text']; // cat
    $lightSourceShape = $item->fields['light-source-shape']->values[0]['text']; // cat
    $lightSourceBaseType = $item->fields['light-source-connectionbase-type']->values[0]['text']; // cat
    $r9 = $item->fields['r9']->values; // num
    $specialFeatures = $item->fields['special-feature']->values; // multi cat
    $bulbDimmability = $item->fields['bulb-dimmability']->values[0]['text']; // cat
    $notes = $item->fields['notes']->values; // text
    $dateAvailable = $item->fields['date-available-on-market']->start; // date
    $qualificationStatus = $item->fields['qualification-status']->values[0]['text']; // category Single Select
    $dateQualified = $item->fields['date-qualified']->start; // date
    $markets = $item->fields['markets']->values; // multi cat
    $jsonInfo = $item->fields['additional-model-information-json-ml']->values; // text
    $dateLastQualified = $item->fields['date-last-qualified']->start; // date
    $lists = $item->fields['source-list']->values[0]->item_id[0]->item_id; // app
    //calcs
    $productClass = $item->fields['product-class']->values;
    $marketDomain = $item->fields['market-domain']->values;
    $endUse = $item->fields['end-use-application']->values;
    $useLocation = $item->fields['use-location']->values;
    $applicationSpecs = $item->fields['application-specsratings']->values;
    $mounting = $item->fields['mounting']->values;
    $formFactor = $item->fields['form-factor']->values;
    $nominalSize = $item->fields['nominal-size']->values;
    $lightDist = $item->fields['light-distributionoptics']->values;
    $driverType = $item->fields['driver-type']->values;
    $dimming = $item->fields['dimming']->values;
    $cctShifting = $item->fields['white-tunable-cct-shifitng']->values;
    $colorTuning = $item->fields['full-color-tuning']->values;
    $integControls = $item->fields['integrated-controls']->values;
    $commStandard = $item->fields['communication-standard']->values;

// end podio get Fixture Item



    $sourceProductCat = PodioItem::filter(15924503, array("filters"=>array('dlc-category-title'=>$fixtureType)));
    $sourceProductCatID = $sourceProductCat[0]->item_id;

    $equivLampShape = PodioItem::filter(15990980, array("filters"=>array('title'=>$lightSourceShape)));
    $equivLampShapeID = $equivLampShape[0]->item_id;

    $baseTypeRel = PodioItem::filter(15990979, array("filters"=>array('title'=>$lightSourceBaseType)));
    $baseTypeID = $baseTypeRel[0]->item_id;

    $marketDomainArray = array();
    if($intendedUse != "N/A") {
        array_push($marketDomainArray, $intendedUse);
    }
    if($intendedUse != $marketDomain){
        array_push($marketDomainArray, $marketDomain);
    }

    if($specialFeatures){
        $specFeaturesArray = array();
        foreach($specialFeatures as $feature){
            array_push($specFeaturesArray, $feature['text']);
        }
    }

    if($markets){
        $marketArray = array();
        foreach($markets as $market){
            array_push($marketArray, $market['text']);
        }
    }


// Begin podio create, update ES Fixture to Combined QPL Item
    $filterItem = PodioItem::filter(15755276, array('filters'=>array(121778353=>$pd_id)));

    $filterItemID = $filterItem[0]->item_id;


    if($pd_id){$fieldsArray['fields']['title'] =  $pd_id;} // text
    if($modelNumber){$fieldsArray['fields']['text'] =  $modelNumber;} // text
    if($manufacturer){$fieldsArray['fields']['manufacturer'] = array((int)$manufacturer);} // app
    if($brand){$fieldsArray['fields']['brand'] = array((int)$brand);} // app
    if($qualificationStatus){$fieldsArray['fields']['qualification-status'] =  $qualificationStatus;} // category Single Select
    if($lists){$fieldsArray['fields']['source-qpl'] = "ENERGY STAR Fixtures";} // app
    if($dateQualified){$fieldsArray['fields']['date-qualified'] =  $dateQualified->format("Y-m-d H:i:s");} // date
    if($dateLastQualified){$fieldsArray['fields']['date-last-confirmed-qualified-2'] =  $dateLastQualified->format("Y-m-d H:i:s");} // date
    if($sourceProductCatID){$fieldsArray['fields']['source-product-category-2'] = array((int)$sourceProductCatID);} // app
    if($equivLampShapeID){$fieldsArray['fields']['equivalent-lamp-shape'] = array((int)$equivLampShapeID);} // app
    if($productClass){$fieldsArray['fields']['product-class'] =  $productClass;} // category
    if($marketDomainArray[0]){$fieldsArray['fields']['market-domain'] =  $marketDomainArray;} // category
    if($baseTypeID){$fieldsArray['fields']['base-type'] = array((int)$baseTypeID);} // app
    if($totalInputPower){$fieldsArray['fields']['input-power-w'] =  $totalInputPower;} // number
    if($lightOutputLumens){$fieldsArray['fields']['light-output-lm'] =  $lightOutputLumens;} // number
    if($luminaireEfficacy || $sourceEfficacy){$fieldsArray['fields']['lumen-measurement-point'] =  $luminaireEfficacy ? "Outside the Fixture" : "At the Source";} // category single
    if($cri){$fieldsArray['fields']['cri'] =  $cri;} // number
    if($r9){$fieldsArray['fields']['r9-value'] =  $r9;} // number
    if($lightSourceLife){$fieldsArray['fields']['rated-life-hours'] =  $lightSourceLife;} // number
    if($powerFactor){$fieldsArray['fields']['power-factor'] =  $powerFactor;} // number
    if($notes){$fieldsArray['fields']['notes'] =  $notes;} // number
    if($specFeaturesArray[0]){$fieldsArray['fields']['special-features'] =  $specFeaturesArray;} // category MultiSelect
    if($additionalInformation){$fieldsArray['fields']['additional-model-info'] =  $additionalInformation;} // text
    if($marketArray[0]){$fieldsArray['fields']['markets'] =  $marketArray;} // category MultiSelect
    if($dateAvailable){$fieldsArray['fields']['date-available-on-market'] =  $dateAvailable->format("Y-m-d H:i:s");} // date
    if($endUse){$fieldsArray['fields']['end-use-application'] =  $endUse;} // category MultiSelect
    if($useLocation){$fieldsArray['fields']['use-location'] =  $useLocation;} // category MultiSelect
    if($applicationSpecs){$fieldsArray['fields']['application-specsratings'] =  $applicationSpecs;} // category MultiSelect
    if($formFactor){$fieldsArray['fields']['form-factor'] =  $formFactor;} // category MultiSelect
    if($nominalSize){$fieldsArray['fields']['nominal-size'] =  $nominalSize;} // category MultiSelect
    if($lightDist){$fieldsArray['fields']['light-distributionoptics'] =  $lightDist;} // category MultiSelect
    if($driverType){$fieldsArray['fields']['driver-type'] =  $driverType;} // category MultiSelect
    if($cctShifting){$fieldsArray['fields']['white-tunable-cct-shifting'] =  $cctShifting;} // text
    if($commStandard){$fieldsArray['fields']['communication-standard'] =  $commStandard;} // text
    if($integControls){$fieldsArray['fields']['integrated-controls'] =  $integControls;} // text
    if($dimming){$fieldsArray['fields']['dimming'] =  $dimming;} // text
    if($colorTuning){$fieldsArray['fields']['full-color-tuning'] =  $colorTuning;} // text

    //print_r($fieldsArray);exit;

    if($filterItemID){
        $combinedItem = PodioItem::update($filterItemID, $fieldsArray);
        $combinedItemID = $filterItemID;
    }
    else {
        $combinedItem = PodioItem::create(15755276, $fieldsArray);
        $combinedItemID = $combinedItem->item_id;
    }


    PodioItem::update($item_id, array('fields'=>array('omni'=>$combinedItemID)));




    array_push($result, "end");

    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/ESFixtureArchToCombined.log';
    array_push($result, "ran at: ");
    array_push($result,date("y:m:d:H:i:s"));
    array_push($result,"\n");
    file_put_contents($file, $result, FILE_APPEND);


    return [
        'success' => true,
        'result' => $result,
    ];
}catch(Exception $e)
{
    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/ESFixtureArchToCombined.log';
    array_push($result, "ran at: ");
    array_push($result,date("y:m:d:H:i:s"));
    array_push($result, "error: $e");
    array_push($result,"\n");
    file_put_contents($file, $result, FILE_APPEND);

    $requestParams = $event['request']['parameters'];
    $item_id = $requestParams['item_id'];
    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/rerun.log';
    $handle = fopen($file,'a');
    fwrite($handle,"DLC2Archive?item_id=$item_id \n");
    fclose($handle);

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