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

    $ESUnique = $item->fields['pdid']->values; // text
    $manufacturer = $item->fields['energy-star-partner']->values[0]->item_id; // app
    $brand = $item->fields['brand-name']->values[0]->item_id; // app
    $ModelName = $item->fields['title']->values; // text
    $ModelNumber = $item->fields['model-number']->values; // text
    $AdditionalModelInformation = $item->fields['additional-model-information']->values; // text
    $BulbType = $item->fields['bulb-type']->values[0]['text']; // cat
    $PFBulbType = $item->fields['product-finder-bulb-type']->values[0]['text']; // cat
    $BaseType = $item->fields['base-type']->values[0]['text']; // cat
    $LampCategory = $item->fields['lamp-category']->values[0]['text']; // category Single Select
    $Warranty = $item->fields['warranty-years']->values; // number
    $WattageEquivalency = $item->fields['wattage-equivalency-watts']->values; // number
    $MaxLength = $item->fields['maximum-overall-length-mm']->values; // number
    $MaxDiameter = $item->fields['maximum-overall-diameter-mm']->values; // number
    $CBIntensity = $item->fields['cbcp']->values; // number
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
    $Dimmable = $item->fields['dimmable']->values[0]['text']; // cat
    $DimsDown = $item->fields['dims-down-to']->values; // progress
    $SpecialFeatures = $item->fields['special-features']->values; // multi-cat
    $Availability = $item->fields['date-available-on-market']->start; // date
    $Qualified = $item->fields['date-qualified']->start; // date
    $Market = $item->fields['markets']->values; // text
    $QualificationStatus = $item->fields['qualification-status']->values[0]['text']; // category Single Select
    $DateLastQualified = $item->fields['date-last-qualified']->start; // date
    $SourceList = $item->fields['source-list']->values[0]->item_id; // app

    //calcs
    $productClass = $item->fields['product-class']->values;
    $marketDomain = $item->fields['market-domain']->values;
    $endUse = $item->fields['end-use-application']->values;
    $useLocation = $item->fields['use-location']->values;
    $applicationSpecs = $item->fields['application-specsratings']->values;
    $formFactor = $item->fields['form-factor']->values;
    $nominalSize = $item->fields['nominal-size']->values;
    $lightDist = $item->fields['light-distributionoptics']->values;
    $driverType = $item->fields['driver-type']->values;
    $cctShifting = $item->fields['white-tunable-cct-shifitng']->values;
    $commStandard = $item->fields['communication-standard']->values;
    $integControls = $item->fields['additional-integrated-controls']->values;



    $sourceProductCat = PodioItem::filter(15924559, array("filters"=>array('dlc-category-title'=>$LampCategory." | ".$PFBulbType)));
    $sourceProductCatID = $sourceProductCat[0]->item_id;

    $equivLampShape = PodioItem::filter(15990980, array("filters"=>array('title'=>$BulbType)));
    $equivLampShapeID = $equivLampShape[0]->item_id;

    $baseTypeRel = PodioItem::filter(15990979, array("filters"=>array('title'=>$BaseType)));
    $baseTypeID = $baseTypeRel[0]->item_id;

    if($SpecialFeatures){
        $specFeaturesArray = array();
        foreach($SpecialFeatures as $feature){
            array_push($specFeaturesArray, $feature['text']);
        }
    }

    if($Market){
        $marketArray = array();
        foreach($Market as $market){
            array_push($marketArray, $market['text']);
        }
    }

// Begin podio create, update ES Lamp to Combined QPL Item
    $filterItem = PodioItem::filter(15755276, array('filters'=>array(121778353=>$ESUnique)));


    $filterItemID = $filterItem[0]->item_id;


    if($ESUnique){$fieldsArray['fields']['title'] =  $ESUnique;} // text
    if($ModelNumber){$fieldsArray['fields']['text'] =  $ModelNumber;} // text
    if($manufacturer){$fieldsArray['fields']['manufacturer'] = array((int)$manufacturerID);} // app
    if($brand){$fieldsArray['fields']['brand'] = array((int)$brandID);} // app
    if($QualificationStatus){$fieldsArray['fields']['qualification-status'] =  $QualificationStatus;} // category Single Select
    if($SourceList){$fieldsArray['fields']['source-qpl'] = "ENERGY STAR Lamps";} // app
    if($Qualified){$fieldsArray['fields']['date-qualified'] =  $Qualified->format("Y-m-d H:i:s");} // date
    if($DateLastQualified){$fieldsArray['fields']['date-last-confirmed-qualified-2'] =  $DateLastQualified->format("Y-m-d H:i:s");} // date
    if($sourceProductCatID){$fieldsArray['fields']['source-product-category-2'] = array((int)$sourceProductCatID);} // app
    if($equivLampShapeID){$fieldsArray['fields']['equivalent-lamp-shape'] = array((int)$equivLampShapeID);} // app
    if($productClass){$fieldsArray['fields']['product-class'] =  $productClass;} // category
    if($marketDomain){$fieldsArray['fields']['market-domain'] =  $marketDomain;} // category
    if($baseTypeID){$fieldsArray['fields']['base-type'] = array((int)$baseTypeID);} // app
    if($WattageEquivalency){$fieldsArray['fields']['incandescent-wattage-equivalency-w-2'] =  (int)$WattageEquivalency;} // number
    if($EnergyUsed){$fieldsArray['fields']['input-power-w'] =  (int)$EnergyUsed;} // number
    if($Brightness){$fieldsArray['fields']['light-output-lm'] =  (int)$Brightness;} // number
    if($Efficacy){$fieldsArray['fields']['efficacy-lmw'] =  (int)$Efficacy;} // number
    if($ColorQuality){$fieldsArray['fields']['cri'] =  (int)$ColorQuality;} // number
    if($R9){$fieldsArray['fields']['r9-value'] =  (int)$R9;} // number
    if($LightAppearance){$fieldsArray['fields']['nominal-cct-k'] =  (string)((int)$LightAppearance);} // category
    if($CBIntensity){$fieldsArray['fields']['cbcp'] =  (int)$CBIntensity;} // number
    if($BeamAngle){$fieldsArray['fields']['beam-angle-deg'] =  (int)$BeamAngle;} // number
    if($Life){$fieldsArray['fields']['rated-life-hours'] =  (int)$Life;} // number
    if($Warranty){$fieldsArray['fields']['warranty-years'] =  (int)$Warranty;} // number
    if($PowerFactor){$fieldsArray['fields']['power-factor'] =  (int)$PowerFactor;} // number
    if($MinOpTemp){$fieldsArray['fields']['min-operating-temp-degc'] =  (int)$MinOpTemp;} // number
    if($MaxLength){$fieldsArray['fields']['max-overall-length-mm'] =  (int)$MaxLength;} // number
    if($MaxDiameter){$fieldsArray['fields']['max-overall-diameter-mm'] =  (int)$MaxDiameter;} // number
    if($ThreeWay){$fieldsArray['fields']['three-way'] =  $ThreeWay;} // category Single Select
    if($Dimmable){$fieldsArray['fields']['dimming'] =  $Dimmable;} // category Single Select
    if($DimsDown){$fieldsArray['fields']['percent-light-output-lumens-at-lowest-dimming-level'] =  $DimsDown;} // progress
    if($specFeaturesArray[0]){$fieldsArray['fields']['special-features'] =  $specFeaturesArray;} // category MultiSelect
    if($AdditionalModelInformation){$fieldsArray['fields']['additional-model-info'] =  $AdditionalModelInformation;} // text
    if($marketArray[0]){$fieldsArray['fields']['markets'] =  $marketArray;} // category MultiSelect
    if($Availability){$fieldsArray['fields']['date-available-on-market'] =  $Availability->format("Y-m-d H:i:s");} // date
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

//print_r($fieldsArray);exit;

    if($filterItemID){
        $combinedItem = PodioItem::update($filterItemID, $fieldsArray);
        $combinedItemID = $filterItemID;
    }
    else {
        $combinedItem = PodioItem::create(15755276, $fieldsArray);
        $combinedItemID = $combinedItem->item_id;
    }


    PodioItem::update($item_id, array('fields'=>array('omni-qpl-listing'=>$combinedItemID)));



    array_push($result, "end");

    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/ESLampArchToCombined.log';
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
    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/ESLampArchToCombined.log';
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