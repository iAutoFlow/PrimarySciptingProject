<?php
/**
 * Created by PhpStorm.
 * User: mivie
 * Date: 9/20/2016
 * Time: 12:08 PM
 */

class PodioHookHandler {

    public static function process($syncOrgID, $hookType, $hookID, $itemRevisionID){

        if($hookType == "space"){
            MongoUtil::spaceHook($syncOrgID, $hookID);
        }
        if($hookType == "app"){
            MongoUtil::appHook($syncOrgID, $hookID);
        }
        if($hookType == "item"){
            MongoUtil::itemHook($syncOrgID, $hookID);
        }
        if($hookType == "itemUpdate"){
            MongoUtil::itemUpdateHook($syncOrgID, $hookID, $itemRevisionID);
        }

    }


}