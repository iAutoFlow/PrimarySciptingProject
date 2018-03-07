<?php
/**
 * Created by PhpStorm.
 * User: mivie
 * Date: 9/20/2016
 * Time: 4:36 PM
 */

class SyncToPodio{

    public static function CreateItem($appID, $attributes){
        PodioItem::create($appID, $attributes);
        //todo use TECHeGO Podio Library
    }

    public static function UpdateItem($itemID, $attributes){
        PodioItem::update($itemID, $attributes);
        //todo use TECHeGO Podio Library
    }

    public static function DeleteItem($itemID){
        PodioItem::delete($itemID);
        //todo use TECHeGO Podio Library
    }

    public static function RestoreItem($appID, $deletedItemID){
        //todo Mongo Query - Get Deleted Item's values and save them to $attributes


        PodioItem::create($appID, $attributes);
        //todo use TECHeGO Podio Library

        //todo Update Sync DB - Update Deleted Item with new Restored Item's ItemID
    }

}