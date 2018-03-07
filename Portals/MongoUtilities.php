<?php
/**
 * Created by PhpStorm.
 * User: mivie
 * Date: 9/20/2016
 * Time: 3:22 PM
 */

class MongoUtil {

    public static function getDBsByOrgID($syncOrgID){
        $syncOrganizations = //todo MONGOQUERY;
        return $syncOrganizations;//array
    }

    public static function spaceHook($syncOrgID, $spaceID){
        $syncOrganizations = MongoUtil::getDBsByOrgID($syncOrgID);

        foreach($syncOrganizations as $organization){

            $apiKey = //todo get Podio Authentication credentials

            //Podio Authentication

            //END Podio Authentication



            $space = PodioSpace::get($spaceID);
            //todo use TECHeGO Podio Library

            $privacy = $space->privacy;
            $autoJoin = $space->auto_join;
            $spaceName = $space->name;
            $spaceUrl = $space->url;
            $postOnNewApp = $space->post_on_new_app;
            $postOnNewMember = $space->post_on_new_member;
            $spaceSubscribed = $space->subscribed;

            //todo Add/Update Space to Sync User's Mongo DB - Spaces collection

        }

    }

    public static function appHook($syncOrgID, $appID){
        $syncOrganizations = MongoUtil::getDBsByOrgID($syncOrgID);

        foreach($syncOrganizations as $organization) {

            $apiKey = //todo get Podio Authentication credentials

                //Podio Authentication

                //END Podio Authentication


            $app = PodioApp::get($appID);
            //todo use TECHeGO Podio Library

            $status = $app->status;
            $rights = $app->rights;
            $defaultViewId = $app->default_view_id;
            $config = $app->config;
            $integration = $app->integration;
            $appSubscribed = $app->subscribed;

            //todo Add/Update App to Sync User's Mongo DB -  Apps collection

            $fields = $app->fields;

            foreach($fields as $field) {
                //todo Add/Update Field to Sync User's Mongo DB - Fields Collection
            }
        }

    }

    public static function itemHook($syncOrgID, $itemID){
        $syncOrganizations = MongoUtil::getDBsByOrgID($syncOrgID);

            foreach($syncOrganizations as $organization) {

            $apiKey = //todo get Podio Authentication credentials

                //Podio Authentication

                //END Podio Authentication


            $item = PodioItem::get($itemID);
            //todo use TECHeGO Podio Library


            //todo Add new Item to Sync User's Mongo DB - Items Collection

            //todo Add new Revision item to Sync User's Mongo DB - Revisions Collection

        }
    }

    public static function itemUpdateHook($syncOrgID, $itemID, $itemRevisionID){
        $syncOrganizations = MongoUtil::getDBsByOrgID($syncOrgID);

        foreach($syncOrganizations as $organization) {

            $apiKey = //todo get Podio Authentication credentials

                //Podio Authentication

                //END Podio Authentication


            $item = PodioItem::get($itemID);
            //todo use TECHeGO Podio Library

            $noRevision = true;

            //todo check Sync User's Mongo DB for a revision with this Number, if exists set $noRevision to false;

            if($noRevision) {

                $previousRevision = (int)$itemRevisionID - 1;

                $revisionDifference = PodioItemDiff::get_for($itemID, $previousRevision, $itemRevisionID);

                //todo Add new Revision item to Sync User's Mongo DB - Revisions Collection
            }
        }


    }


}//end Class MongoUtil