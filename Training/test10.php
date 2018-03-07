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


    $PID = $item->fields['title']->values;
    $manufacturer = $item->fields['manufacturer-name']->values[0]->item_id;
    $brand = $item->fields['brand-name']->values[0]->item_id;
    $modelNum = $item->fields['model-number']->values;
    $category = $item->fields['category']->values[0]['text'];
    $generalApplication = $item->fields['general-application']->values[0]['text'];
    $primaryUse = $item->fields['primary-use']->values[0]['text'];
    $class = $item->fields['classification']->values[0]['text'];
    $parent = $item->fields['parent']->values[0]['text'];
    $M_lightOutput = $item->fields['measured-light-output-lm']->values;
    $M_LMW = $item->fields['measured-luminaire-efficacy-lmw']->values;
    $M_wattage = $item->fields['measured-wattage-w']->values;
    $M_THD = $item->fields['total-harmonic-distortion-thd']->values;
    $M_powerFactor = $item->fields['measured-power-factor']->values;
    $M_CCTk = $item->fields['measured-cct-k']->values;
    $M_CRI = $item->fields['measured-cri']->values;
    $warrantyYears = $item->fields['warranty-years']->values;
    $R_lifeHours = $item->fields['rated-lifetime-hours']->values;
    $dateQual = $item->fields['date-qualified']->start;
    $dimmingType = $item->fields['dimming-type']->values[0]['text'];
    $integControls = $item->fields['has-integrated-controls']->values[0]['text'];
    $notes = $item->fields['notes']->values;
    $familyCode = $item->fields['family-code']->values[0]->item_id;
    $R_lightOutput = $item->fields['rated-light-output-lm']->values;
    $R_LMW = $item->fields['rated-luminaire-efficacy-lmw']->values;
    $R_wattage = $item->fields['rated-wattage-w']->values;
    $R_THD = $item->fields['rated-total-harmonic-distortion-thd']->values;
    $R_powerFactor = $item->fields['rated-power-factor']->values;
    $R_CCTk = $item->fields['rated-cct-k']->values;
    $R_CRI = $item->fields['rated-cri']->values;
    $qualStatus = $item->fields['qualification-status']->values[0]['text'];
    $dateLastQual = $item->fields['date-last-confirmed-qualified']->start;
    $sourceList = $item->fields['source-list']->values[0]->item_id;

    //calcs
    $productClass = $item->fields['product-class']->values;
    $marketDomain = $item->fields['market-domain']->values;
    $lightSources = $item->fields['light-sources-per-fixture']->values;
    $endUse = $item->fields['end-use-application']->values;
    $useLocation = $item->fields['use-location']->values;
    $applicationSpecs = $item->fields['application-specsratings']->values;
    $mounting = $item->fields['mounting']->values;
    $baseType = $item->fields['base-type']->values;
    $formFactor = $item->fields['form-factor']->values;
    $nominalSize = $item->fields['nominal-size']->values;
    $lightDist = $item->fields['light-distributionoptics']->values;
    $driverType = $item->fields['driver-type']->values;


    $sourceProductCat = PodioItem::filter(15613547, array("filters"=>array('dlc-category-title'=>$category." | ".$generalApplication." | ".$primaryUse)));
    $sourceProductCatID = $sourceProductCat[0]->item_id;

    $baseTypeRel = PodioItem::filter(15990979, array("filters"=>array('title'=>$baseType)));
    $baseTypeID = $baseTypeRel[0]->item_id;

    //CCT Value
    $CCT_target = "";
    if($R_CCTk || $M_CCTk){
        $CCT_check = $R_CCTk || $M_CCTk;
        if($CCT_check > 6750 && $CCT_check <= 7100){
            $CCT_target = 7000;
        }
        if($CCT_check > 6250 && $CCT_check <= 6750){
            $CCT_target = 6500;
        }
        if($CCT_check > 5850 && $CCT_check <= 6250){
            $CCT_target = 6000;
        }
        if($CCT_check > 5600 && $CCT_check <= 5850){
            $CCT_target = 5700;
        }
        if($CCT_check > 5350 && $CCT_check <= 5600){
            $CCT_target = 7000;
        }
        if($CCT_check > 5100 && $CCT_check <= 5350){
            $CCT_target = 5200;
        }
        if($CCT_check > 4750 && $CCT_check <= 5100){
            $CCT_target = 5000;
        }
        if($CCT_check > 4250 && $CCT_check <= 4750){
            $CCT_target = 4500;
        }
        if($CCT_check > 3750 && $CCT_check <= 4250){
            $CCT_target = 4000;
        }
        if($CCT_check > 3250 && $CCT_check <= 3750){
            $CCT_target = 3500;
        }
        if($CCT_check > 2850 && $CCT_check <= 3250){
            $CCT_target = 3000;
        }
        if($CCT_check > 2600 && $CCT_check <= 2850){
            $CCT_target = 2700;
        }
        if($CCT_check > 2350 && $CCT_check <= 2600){
            $CCT_target = 2500;
        }
        if($CCT_check > 2100 && $CCT_check <= 2350){
            $CCT_target = 2200;
        }
    }
    //End CCT Value

// Begin podio create, update DLC to Combined QPL Item
    $filterItem = PodioItem::filter(15755276, array('filters'=>array(121778353=>$PID)));

    $filterItemID = $filterItem[0]->item_id;


    if($PID){$fieldsArray['fields']['title'] =  $PID;} // text
    if($modelNum){$fieldsArray['fields']['text'] =  $modelNum;} // text
    if($manufacturer){$fieldsArray['fields']['manufacturer'] = array((int)$manufacturer);} // app
    if($brand){$fieldsArray['fields']['brand'] = array((int)$brand);} // app
    if($qualStatus){$fieldsArray['fields']['qualification-status'] =  $qualStatus;} // category Single Select
    if($sourceList){$fieldsArray['fields']['source-qpl'] = "DLC";} // app
    if($dateQual){$fieldsArray['fields']['date-qualified'] =  $dateQual->format("Y-m-d H:i:s");} // date
    if($dateLastQual){$fieldsArray['fields']['date-last-confirmed-qualified'] =  $dateLastQual->format("Y-m-d H:i:s");} // date
    if($sourceProductCatID){$fieldsArray['fields']['source-product-category-2'] = array((int)$sourceProductCatID);} // app
    if($productClass){$fieldsArray['fields']['product-class'] =  $productClass;} // category
    if($marketDomain){$fieldsArray['fields']['market-domain'] =  $marketDomain;} // category
    if($baseTypeID){$fieldsArray['fields']['base-type'] = array((int)$baseTypeID);} // app
    if($lightSources){$fieldsArray['fields']['light-sources-per-fixture'] = array((int)$lightSources);} // app
    if($class){$fieldsArray['fields']['performance-tier'] =  $class;} // category
    if($R_wattage || $M_wattage){$fieldsArray['fields']['input-power-w'] =  $R_wattage || $M_wattage;;} // number
    if($R_lightOutput || $M_lightOutput){$fieldsArray['fields']['light-output-lm'] =  $R_lightOutput || $M_lightOutput;} // number
    if($R_LMW || $M_LMW){$fieldsArray['fields']['efficacy-lmw'] =  $R_LMW || $M_LMW;} // number
    if($R_CRI || $M_CRI){$fieldsArray['fields']['cri'] =  $R_CRI || $M_CRI;} // number
    if($CCT_target){$fieldsArray['fields']['nominal-cct-k'] =  $CCT_target;} // number
    if($R_lifeHours){$fieldsArray['fields']['rated-life-hours'] =  $R_lifeHours;} // number
    if($warrantyYears){$fieldsArray['fields']['warranty-years'] =  $warrantyYears;} // number
    if($R_powerFactor || $M_powerFactor){$fieldsArray['fields']['power-factor'] =  $R_powerFactor || $M_powerFactor;} // number
    if($R_THD || $M_THD){$fieldsArray['fields']['total-harmonic-distortion-thd'] =  $R_THD || $M_THD;} // number
    if($integControls){$fieldsArray['fields']['integrated-controls'] =  $integControls;} // category Single Select
    if($dimmingType){$fieldsArray['fields']['dimming'] =  $dimmingType;} // category Single Select
    if($notes){$fieldsArray['fields']['notes'] =  $notes;} // text
    if($SpecialFeatures){$fieldsArray['fields']['special-features'] =  $SpecialFeatures;} // category MultiSelect
    if($parent){$fieldsArray['fields']['parent'] =  $parent;} // category Single Select
    if($familyCode){$fieldsArray['fields']['family-code'] =  $familyCode;} // category Single Select
    if($endUse){$fieldsArray['fields']['end-use-application'] =  $endUse;} // category MultiSelect
    if($useLocation){$fieldsArray['fields']['use-location'] =  $useLocation;} // category MultiSelect
    if($mounting){$fieldsArray['fields']['mounting'] =  $mounting;} // category MultiSelect
    if($formFactor){$fieldsArray['fields']['form-factor'] =  $formFactor;} // category MultiSelect
    if($nominalSize){$fieldsArray['fields']['nominal-size'] =  $nominalSize;} // category MultiSelect
    if($lightDist){$fieldsArray['fields']['light-distributionoptics'] =  $lightDist;} // category MultiSelect
    if($driverType){$fieldsArray['fields']['driver-type'] =  $driverType;} // category MultiSelect


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

    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/DLCArchToCombined.log';
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
    $file = '/opt/bitnami/apps/dreamfactory/htdocs/storage/app/Seattle/DLCArchToCombined.log';
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
