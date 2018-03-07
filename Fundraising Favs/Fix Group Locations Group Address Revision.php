<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 9/21/2016
 * Time: 10:21 AM
 */


date_default_timezone_set('America/Denver');
$Curl = new\Curl\Curl();
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

    $requestParams = $event['request']['parameters'];
    $appID = $requestParams['app_id'];

    $FormattedAddressArray = array();
    $PrintArray = array();
    $ReplaceArray = array();


    //Filter Groups App
    $FilterGroups = PodioItem::filter_by_view($appID, 30598165, array('limit'=>500));

    foreach($FilterGroups as $GroupItem) {
        $GroupItemID = $GroupItem->item_id;

        $ItemRevisions = PodioItemRevision::get_for($GroupItemID);

        foreach ($ItemRevisions as $revision) {
            $CreatedBy = $revision->created_by->name;
            $RevisionID = $revision->revision;


            if ($CreatedBy == "AVA" && $RevisionID > 1) {
                $PreviousRevision = abs($RevisionID) - 1;

                //Get Revision Difference
                $RevisionDifference = PodioItemDiff::get_for($GroupItemID, $PreviousRevision, $RevisionID);
                $FieldLabel = $RevisionDifference[0]->label;


                if ($FieldLabel == "Location") {
                    $From = $RevisionDifference[0]->from[0]['formatted'];

                    if ($From) {

                        if (strpos($From, ' St ') !== false) {$From = str_replace(' St ', ' Street', $From);}
                        if (strpos($From, ' St.') !== false) {$From = str_replace(' St.', ' Street', $From);}
                        if (strpos($From, ' St, ') !== false) {$From = str_replace(' St,', ' Street, ', $From);
                        }
                        if (strpos($From, ' St.,') !== false) {
                            $From = str_replace(' St.,', ' Street,', $From);
                        }
                        if (strpos($From, ' St.,,') !== false) {
                            $From = str_replace(' St.,,', ' Street,', $From);
                        }
                        if (strpos($From, ' St,,') !== false) {
                            $From = str_replace(' St,,', ' Street,', $From);
                        }


                        if (strpos($From, ' Ave.') !== false) {
                            $From = str_replace(' Ave.', ' Avenue', $From);
                        }
                        if (strpos($From, ' Ave,') !== false) {
                            $From = str_replace(' Ave,', ' Avenue,', $From);
                        }
                        if (strpos($From, ' Ave.,') !== false) {
                            $From = str_replace(' Ave.,', ' Avenue,', $From);
                        }
                        if (strpos($From, ' Ave,,') !== false) {
                            $From = str_replace(' Ave,,', ' Avenue,', $From);
                        }
                        if (strpos($From, ' Ave ') !== false) {
                            $From = str_replace(' Ave ', ' Avenue ', $From);
                        }

                        if (strpos($From, ' Dr, ') !== false) {
                            $From = str_replace(' Dr, ', ' Drive, ', $From);
                        }
                        if (strpos($From, ' Dr,,') !== false) {
                            $From = str_replace(' Dr,,', ' Drive,', $From);
                        }
                        if (strpos($From, ' Rd ') !== false) {
                            $From = str_replace(' Rd ', ' Road ', $From);
                        }
                        if (strpos($From, ' Rd, ') !== false) {
                            $From = str_replace(' Rd, ', ' Road, ', $From);
                        }
                        if (strpos($From, ' Rd,,') !== false) {
                            $From = str_replace(' Rd,,', ' Road,', $From);
                        }
                        if (strpos($From, ' Ln, ') !== false) {
                            $From = str_replace(' Ln, ', ' Lane, ', $From);
                        }
                        if (strpos($From, ' Ln,,') !== false) {
                            $From = str_replace(' Ln,,', ' Lane,', $From);
                        }


                        if (strpos($From, ' S.') !== false) {
                            $From = str_replace(' S.', ' South', $From);
                        }
                        if (strpos($From, ' N.') !== false) {
                            $From = str_replace(' N.', ' North', $From);
                        }
                        if (strpos($From, ' E.') !== false) {
                            $From = str_replace(' E.', ' East', $From);
                        }
                        if (strpos($From, ' W.') !== false) {
                            $From = str_replace(' W.', ' West', $From);
                        }
                        if (strpos($From, ' S ') !== false) {
                            $From = str_replace(' S ', ' South ', $From);
                        }
                        if (strpos($From, ' N ') !== false) {
                            $From = str_replace(' N ', ' North ', $From);
                        }
                        if (strpos($From, ' E ') !== false) {
                            $From = str_replace(' E ', ' East ', $From);
                        }
                        if (strpos($From, ' W ') !== false) {
                            $From = str_replace(' W ', ' West ', $From);
                        }
                        if (strpos($From, ' S,') !== false) {
                            $From = str_replace(' S,', ' South,', $From);
                        }
                        if (strpos($From, ' N,') !== false) {
                            $From = str_replace(' N,', ' North,', $From);
                        }
                        if (strpos($From, ' E,') !== false) {
                            $From = str_replace(' E,', ' East,', $From);
                        }
                        if (strpos($From, ' W,') !== false) {
                            $From = str_replace(' W,', ' West,', $From);
                        }

                        if (strpos($From, ' SW ') !== false) {
                            $From = str_replace(' SW ', ' Southwest ', $From);
                        }
                        if (strpos($From, ' SE ') !== false) {
                            $From = str_replace(' SE ', ' Southeast ', $From);
                        }
                        if (strpos($From, ' NW ') !== false) {
                            $From = str_replace(' NW ', ' Northwest ', $From);
                        }
                        if (strpos($From, ' NE ') !== false) {
                            $From = str_replace(' NE ', ' Northeast ', $From);
                        }

                        if (strpos($From, ' Sw ') !== false) {
                            $From = str_replace(' Sw ', ' Southwest ', $From);
                        }
                        if (strpos($From, ' Se ') !== false) {
                            $From = str_replace(' Se ', ' Southeast ', $From);
                        }
                        if (strpos($From, ' Nw ') !== false) {
                            $From = str_replace(' Nw ', ' Northwest ', $From);
                        }
                        if (strpos($From, ' Ne ') !== false) {
                            $From = str_replace(' Ne ', ' Northeast ', $From);
                        }

                        if (strpos($From, ',,') !== false) {
                            $From = str_replace(',,', ',', $From);
                        }


                        //Update Group Item Address
                        $UpdateGroupItem = PodioItem::update($GroupItemID, array(
                            'fields' => array(
                                'group-address' => array('formatted' => $From)
                            )));
                    }
                }
            }
        }
    }











    //Stop Coding
    return [
        'success' => true,
        'result' => $ReplaceArray,
    ];

}catch(Exception $e)
{

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $GroupItemID,
            'message' => "Error: ".$e,

        ]
    ];

    return;

}





