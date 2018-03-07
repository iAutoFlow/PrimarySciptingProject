<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 6/29/2016
 * Time: 3:47 PM
 */


//<?php
//First you need create Connection and copy id to connection_id variable
//Now you can show a log activity in the synapp_activity table
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

    $JSONSTRING = json_decode('{

"name": "KickStartPortfolio",
"count": 68,
"version": 1,
"newdata": false,
"lastrunstatus": "success",
"thisversionstatus": "success",
"thisversionrun": "Thu Aug 25 2016 22:31:40 GMT+0000 (UTC)",
"results": {
"collection1": [
{"name": {"href": "http://kickstartseedfund.com/index.php?id=56","text": "Alianza"},"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/alianza_logo.8d29d50c.png"},
{"name": {"href": "http://kickstartseedfund.com/index.php?id=73","text": "Alliance Health"},"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/alliancehealth_logo.8d29d50c.png"},
{"name": {"href": "http://kickstartseedfund.com/index.php?id=57",
"text": "Amiigo"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/amiigo_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=58",
"text": "Artemis"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/artemis_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=146",
"text": "Avatech"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/AvaTech-Logo-Small.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=170",
"text": "BrainStorm"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/brainstorm_logo1.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=59",
"text": "C7 Data Centers"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/C7.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=60",
"text": "Capshare"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/capshare_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=61",
"text": "Catheter Connections"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Cath%20Conn%20logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=62",
"text": "Chargeback.com"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/chargebackguardian_logo2.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=64",
"text": "Chatbooks"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/chatbookslogo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=120",
"text": "Converus"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/converus_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=66",
"text": "Cotopaxi"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/cotopaxi_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=128",
"text": "Cranium Cafe"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Cranium.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=164",
"text": "Direct Scale"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/directscale%20color%20logo%20400.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=68",
"text": "Dropship Commerce"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/dropshipcommerce_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=69",
"text": "EcoScraps"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Ecoscraps.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=165",
"text": "Eventboard"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/EB_logo%20sq.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=71",
"text": "Fuze Network"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/fuzenetwork_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=72",
"text": "GroSocial"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/grosocial_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=107",
"text": "Grow"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/grow_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=155",
"text": "Havenly"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/havenly.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=14",
"text": "HireVue"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/HireVueLogo2.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=143",
"text": "HitLabs"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/HitLabs.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=175",
"text": "Idaciti"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/idaciti.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=74",
"text": "JackRabbit Systems"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Jackrabbit.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=76",
"text": "Juxta Labs Inc"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/juxtalabs_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=77",
"text": "Lineagen"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/lineagen_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=78",
"text": "LucidSoftware"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/lucidsoftware_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=173",
"text": "MarketDial"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/MarketDial.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=88",
"text": "Movement Ventures"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Movement%20cropped.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=67",
"text": "Nav"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/nav.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=80",
"text": "Needle"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/needle_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=121",
"text": "NUVI"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Nuvi.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=157",
"text": "Omadi"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/omadilogo400.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=87",
"text": "Outro"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Outro.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=82",
"text": "Panoptic Security"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/panopticsecurity_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=122",
"text": "Pebble Post"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Pebblepost%20logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=83",
"text": "Peer60"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/peer60_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=84",
"text": "PenBlade"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/penblade_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=85",
"text": "PhotoPharmics"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/photopharmics_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=91",
"text": "Podium"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/PodiumSQ.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=86",
"text": "Power Practical"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/powerpractical_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=89",
"text": "RackWare"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Rackware.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=90",
"text": "Radiate Media"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/radiatemedia_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=123",
"text": "Repiscore"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Repiscore.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=112",
"text": "Room Choice"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/roomchoice_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=65",
"text": "Ryver"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Ryver.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=92",
"text": "Sagebin"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Sagebin.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=149",
"text": "Sales Bridge"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Salesbridge%20logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=93",
"text": "Sales Rabbit"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/sales-rabbit_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=156",
"text": "Self Lender"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/SelfLender400.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=160",
"text": "Simple Citizen"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/SimpleCitizen%20logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=148",
"text": "Social Dental"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Social%20Dental.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=94",
"text": "Spatch"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Spatch.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=145",
"text": "SpinGo"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/spingologo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=95",
"text": "STANCE"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/stance_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=111",
"text": "Studio Design"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/studio_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=144",
"text": "Suralink"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/suralinklogo.7aee3489.jpg"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=96",
"text": "TaskEasy"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/taskeasy_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=97",
"text": "Tax Alli"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/tax-alli_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=98",
"text": "Veritract"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/veritract_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=99",
"text": "VidAngel"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/VidAngel.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=100",
"text": "Vutara"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/vutara_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=102",
"text": "WAVE"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/wave_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=153",
"text": "Zane Benefits"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Zane%20Square400.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=103",
"text": "ZenPrint"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/zenprint_logo.8d29d50c.png"
},
{
"name": {
"href": "http://kickstartseedfund.com/index.php?id=104",
"text": "Zerista"
},
"logo": "http://kickstartseedfund.com/core/cache/phpthumb/companyLogos/Zerista.8d29d50c.png"
}
]
}
}');


    $PHPARRAY = $JSONSTRING;

    //$Array = array();
    //FIELDS ARRAY
    $fieldsarray = array('fields'=>array('dash'=>(int)472184813));

    foreach($PHPARRAY->results->collection1 as $item) {

        //Get Vaules from Array
        $Name = $item->name->text;
        $link = $item->name->href;
        $logo = $item->logo;

        $FilterVenturItems = PodioItem::filter(16584700, array("filters" => array('title' => $Name)));
        $ItemID = $FilterVenturItems[0]->item_id;


        //Create Image / Add to Array
        if ($link) {
            $LINK = PodioEmbed::create(array('url' => $link));
            $LinkEmbedID = $LINK->embed_id;

            PodioItem::update($ItemID, array('fields'=>array('link'=>$LinkEmbedID)));
            //$fieldsarray['fields']['link'] = $LinkEmbedID;
        }









        //Create Image / Add to Array
//        if ($logo) {
//            $Image =PodioEmbed::create(array('url' => $logo));
//            $EmbedID = $Image->embed_id;
//            $EmbedImageFile = $Image->files[0]->file_id;
//            $fieldsarray['fields']['logo'] = $EmbedImageFile;
//        }

        //Add Name to Array
//        if ($Name) {
//            $fieldsarray['fields']['title'] = $Name;
//        }

        //Create Item

       // $CreateItem = PodioItem::create(16589868, $fieldsarray);

    }



//RETURN / CATCH
    return [
        'success' => true,
        'result' => $array,
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $array,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}