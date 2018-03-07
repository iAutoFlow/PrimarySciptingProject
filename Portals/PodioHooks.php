<?php
/**
 * Created by PhpStorm.
 * User: mivie
 * Date: 9/20/2016
 * Time: 4:36 PM
 */


/* List of Hook Types for the refType var (only ones used by Sync):
item.create
item.update
item.delete
comment.create
comment.delete
file.change
app.update
app.delete
app.create
 */

class PodioHooks{

    public static function create($refType, $refID, $hookType, $url){
        $attributes = array('type'=>$hookType, 'url'=>$url);

        PodioHook::create($refType, $refID, $attributes);
        //todo use TECHeGO Podio Library
    }

    public static function delete($hookID){
        PodioHook::delete($hookID);
        //todo use TECHeGO Podio Library
    }

    public static function get($refType, $refID){
        PodioHook::get($refType, $refID);
        //todo use TECHeGO Podio Library
    }
}