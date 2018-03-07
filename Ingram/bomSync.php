<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 7/14/2016
 * Time: 4:29 PM
 */
//O-AUTH

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

    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $AnnualPlanningSpaceID = 3549934;
    $ActivityDetailAppID = 12712807;

    $DatabasesSpaceID = 2732169;
    $DBBombElementsAppID = 14384203;


    $CampaignItemID = $item->fields['campaign']->values[0]->item_id;
    $SKUTypeItemID = $item->fields['sku-type-leave-blank']->values[0]->item_id;
    $InvestmentLevel = $item->fields['investment-level']->values[0]['text'];
    $ElementName = $item->fields['detail-around-investment-one-off-activities']->values;
    $MVorCS = $item->fields['multi-vendor-or-client-specific']->values[0]['text'];
    $Budget = $item->fields['total-revenues']->values;
    $ActivityDetails = $item->fields['milestones-of-activity']->values;

    $DeliveryStartDate = $item->fields['delivery-date']->start;
    $FormatDeliveryStartDate = new DateTime((string)$DeliveryStartDate, new DateTimeZone('America/Denver'));





    if($appID == $ActivityDetailAppID){

        $DeliveryEndDate = $item->fields['delivery-date']->end;
        $FormatDeliveryEndDate = new DateTime((string)$DeliveryEndDate, new DateTimeZone('America/Denver'));

        $finalStartDate =  $FormatDeliveryStartDate ? $FormatDeliveryStartDate->format('Y-m-d H:i:s') : null;
        $finalEndDate =  $FormatDeliveryEndDate ? $FormatDeliveryEndDate->format('Y-m-d H:i:s') : null;

        $filterBombElements = PodioItem::filter($DBBombElementsAppID, array('filters' => array('activity-detail-item' => array((int)$itemID))));
        $ActivityDetailItemID = $filterBombElements->item_id;
        if(!$ActivityDetailItemID){
            $CretaeActivityDetailItem = PodioItem::create($DBBombElementsAppID, array(
                'fields' => array(
                    'campaign' => array((int)$CampaignItemID),
                    'status' => 'Active',
                    'investment-level' => $InvestmentLevel,
                    'detail-around-investment-one-off-activities' => $ElementName,
                    'sku-type-leave-blank' => array((int)$SKUTypeItemID),
                    'multi-vendor-or-client-specific' => $MVorCS,
                    'total-revenues' => $Budget,
                    'milestones-of-activity' => $ActivityDetails,
                    'delivery-date' => $finalStartDate,
                    'activity-delivery-end-date' => $finalEndDate,
                    'activity-detail-item'=>array((int)$itemID)
                ),
                array(
                    'hook' => false
                )
            ));
        }

        else{
            $UpdateActivityDetailItem = PodioItem::update($ActivityDetailItemID, array(
                'fields' => array(
                    'campaign' => array((int)$CampaignItemID),
                    'investment-level' => $InvestmentLevel,
                    'detail-around-investment-one-off-activities' => $ElementName,
                    'sku-type-leave-blank' => array((int)$SKUTypeItemID),
                    'multi-vendor-or-client-specific' => $MVorCS,
                    'total-revenues' => $Budget,
                    'delivery-date' => $finalStartDate,
                    'activity-delivery-end-date' => $finalEndDate,
                    'milestones-of-activity' => $ActivityDetails,
                ),
                array(
                    'hook' => false
                )
            ));
        }


    }

    elseif($appID == $DBBombElementsAppID){
        $ActivityDetailItemID = $item->fields['activity-detail-item']->values[0]->item_id;
        $UpdateActivityDetailItem = PodioItem::update($ActivityDetailItemID, array(
            'fields' => array(
                'campaign' => array((int)$CampaignItemID),
                'investment-level' => $InvestmentLevel,
                'detail-around-investment-one-off-activities' => $ElementName,
                'sku-type-leave-blank' => array((int)$SKUTypeItemID),
                'multi-vendor-or-client-specific' => $MVorCS,
                'total-revenues' => $Budget,
                'milestones-of-activity' => $ActivityDetails,
            ),
            array(
                'hook' => false
            )
        ));
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