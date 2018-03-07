<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 12/2/2016
 * Time: 3:55 PM
 */



date_default_timezone_set('America/Denver');

class PodioSessionManager {
    private static $connection_id = 191;
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
    $spaceID = $requestParams['space_id'];

    //SpaceID's
    $BulgariaSpaceID = 4593534;
    $PakistanSpaceID = 4747955;

    $SpaceArray = array($PakistanSpaceID);//$BulgariaSpaceID

    foreach($SpaceArray as $spaceID) {
        $BankDetailsAppID = "";
        $BankDetailsSavedViewID = '';
        $AccountCurrency = "";
        $PBankDetailsVendorExID = 'contract';
        $PBankDetailsStatusExID = 'bank-details-status';
        $PBankDetailsCompanyRegistrationNOExID = 'company-registration-no';
        $PBankDetailsOfficialCompanyNameExID = 'official-company-name';
        $PBankDetailsBankNameExID = 'bank-name';
        $PBankDetailsIBANExID = 'iban';
        $PBankDetailsBICNumberExID = 'bic';
        $PBankDetailsSFIDExID = "sf-id";

        //Set Workspace App / Field ID's
        if ($spaceID == $BulgariaSpaceID) {
            //Bank Details
            $AccountCurrency = "BGN - Bulgarian Lev";
            $BankDetailsAppID = 16015918;
            $BankDetailsSavedViewID = 30916980;
        }

        if ($spaceID == $PakistanSpaceID) {
            //Bank Details
            $BankDetailsAppID = 16413669;
            $BankDetailsSavedViewID = 30920216;
            $AccountCurrency = "PKR - Pakistani Rupee";
        }


        $BankDetailItems = PodioItem::filter_by_view($BankDetailsAppID, $BankDetailsSavedViewID);
        foreach($BankDetailItems as $bank) {
            unset($BankItem);
            $BankItemUniqueID = "";
            $BankRegistrationNo = "";
            $BankVendorItemID = "";
            $BankStatus = "";
            $BankOffCompName = "";
            $BankName = "";
            $BankIBAN = "";
            $BankBIC = "";
            $BankVendorBackEndCode = "";
            $BankSFID = "";
            $BankVendorName = "";

            $BankDetailItemID = $bank->item_id;
            $BankItem = PodioItem::get($BankDetailItemID);
            $BankItemUniqueID = $BankItem->app_item_id_formatted;
            $BankVendorItemID = $BankItem->fields[$PBankDetailsVendorExID]->values[0]->item_id;
            $BankVendorName = $BankItem->fields[$PBankDetailsVendorExID]->values[0]->title;
            $BankStatus = $BankItem->fields[$PBankDetailsStatusExID]->values[0]['text'];
            $BankRegistrationNo = $BankItem->fields[$PBankDetailsCompanyRegistrationNOExID]->values;
            $BankOffCompName = $BankItem->fields[$PBankDetailsOfficialCompanyNameExID]->values;
            $BankName = $BankItem->fields[$PBankDetailsBankNameExID]->values;
            $BankIBAN = $BankItem->fields[$PBankDetailsIBANExID]->values;
            $BankBIC = $BankItem->fields[$PBankDetailsSFIDExID]->values;
            $BankSFID = $BankItem->fields[$PBankDetailsSFIDExID]->values;


            //Create Blank Array for Updating Account Items
            $BankDetailsFieldsArray = array('fields' => array());


            //Add Values to Array

            if ($BankName) {$BankDetailsFieldsArray['fields']['bank-name'] = $BankName;}
            if ($BankStatus) {$BankDetailsFieldsArray['fields']['bank-details-status'] = $BankStatus;}
            if ($BankRegistrationNo) {$BankDetailsFieldsArray['fields']['bank-registration-number'] = (string)$BankRegistrationNo;}
            if ($BankIBAN) {$BankDetailsFieldsArray['fields']['bank-account-number'] = (string)$BankIBAN;}
            if ($BankBIC) {$BankDetailsFieldsArray['fields']['bic-number'] = (string)$BankBIC;}

            //            if ($BankDetailItemID) {$BankDetailsFieldsArray['fields']['bank-details-podio-id'] = (string)$BankDetailItemID;}
            //if ($BankVendorName) {$BankDetailsFieldsArray['fields']['vendor-name'] = (string)$BankVendorName;}
//            if ($BankOffCompName) {$BankDetailsFieldsArray['fields']['official-company-name'] = (string)$BankOffCompName;}




            //Filter Account Items by Bank Vendor Name
            if ($BankVendorName) {
                $i = 0;
                $offset = 0;
                do {
                    $offset = $i * 500;
                    $FilterContracts = PodioItem::filter(17330430, array('filters' => array('companyu' => (string)$BankVendorName), array('limit' => 500, "offset"=>$offset)));
                    $count = count($FilterContracts);
                    if ($count < 1) {
                        continue;
                    }
                    foreach($FilterContracts as $contract) {
                        $BanKAccountVendorItemID = $contract->item_id;
                        $UpdateAccountItem = PodioItem::update($BanKAccountVendorItemID, $BankDetailsFieldsArray);
                    }
                    $i++;
                }while($count == 500);
            }
        }

        print_r($BankDetailsFieldsArray);
        exit;































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
