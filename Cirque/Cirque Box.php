<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/12/2016
 * Time: 7:48 PM
 */


//returns array of (folder_name => folder_id)
function getFoldersInDirectory($folderID, $accessToken){

    $ch = curl_init();

    $url = 'https://www.box.com/api/2.0/folders/'.$folderID;

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type:multipart/form-data'));
    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    $response = curl_exec($ch);

    if (!$response) array_push($result, "curl_error: " . curl_error($ch));

    $decodedResponse = json_decode($response);

    $entries = $decodedResponse->item_collection->entries;

    $folders = array();

    foreach ($entries as $entry) {

        if ($entry->type == "folder") {

            $folders[$entry->name]=$entry->id;

        }

    }

    curl_close($ch);

    return $folders;

}

function createFolder($folderName, $parentID, $accessToken){

    $ch = curl_init();

    $url = 'https://www.box.com/api/2.0/folders/';

    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    $fields = array(
        'name' => $folderName,
        'parent' => array('id'=>$parentID)
    );
    $data_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type:application/json','Content-Length: ' . strlen($data_string)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);

    $response = curl_exec($ch);

    curl_close($ch);

    if (!$response) {
        return "curl_error: " . curl_error($ch);
    }else{
        return $response;

    }
}

function refreshToken($refreshToken, $clientID, $clientSecret){

    $ch = curl_init();

    $url = 'https://api.box.com/oauth2/token';

    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    $fields = array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $refreshToken,
        'client_id' => $clientID,
        'client_secret' => $clientSecret


    );
    $data_string = http_build_query($fields);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);

    $response = curl_exec($ch);

    curl_close($ch);

    if (!$response) {
        return "curl_error: " . curl_error($ch);
    }else{
        return $response;

    }


}

function savePodioFileToBox($item_id, $boxFolderID, $accessToken){

//    if($item_id == null)
//        throw new Exception('Missing Required Parameter: itemID');
//
//    $item = PodioItem::get($item_id);
//
//    $file_id = $item->files[0] ->file_id;

//get podio file
//    $file = PodioFile::get($file_id);
//    $file_content = $file->get_raw();

//save file locally
//    $path_to_file = '/var/www/storage/app/cirque/'.$file_id;
    $path_to_file = '/var/www/storage/app/cirque/test.txt';
    $fileName = 'test.txt';
//    file_put_contents($path_to_file, $file_content);

//move to box

    $json = json_encode(
        array(
            'name' => $fileName,
            'parent' => array('id'=>$boxFolderID)
        )
    );

    $fields = array(
        'attributes' => $json,
        'file'=>new CurlFile($path_to_file)
    );

    $ch = curl_init();

    $url = 'https://upload.box.com/api/2.0/files/content';

    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type:multipart/form-data'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);

    $response = curl_exec($ch);

    curl_close($ch);

    if (!$response) {
        return "curl_error: " . curl_error($ch);
    }else{
        return $response;

    }

}

try{

    $requestParams = $event['request']['parameters'];

    $result = array();


    //get the box.com access_token************************************************************************************//

    $connection_id = 47;

    $connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find($connection_id);
    $accessToken = $connection->access_token;
    $refreshToken = $connection->refresh_token;
    $clientID = $connection->connectionService->config['client_id'];
    $clientSecret = $connection->connectionService->config['client_secret'];

    //refresh it
    $response = json_decode(refreshToken($refreshToken,$clientID,$clientSecret));

    //store it
    $connection->access_token = $response->access_token;
    $connection->refresh_token = $response->refresh_token;
    $connection->save();

    $accessToken = $connection->access_token;
    $refreshToken = $connection->refresh_token;

    if(!$accessToken)throw new Exception("No Box.com Access Token Found, Exiting!");

    //build the folders as needed*************************************************************************************//
    //$folderID = 0; //root
    $brandFolderRootID = 2337492723; //Podio Folder, use when live
    //$brandFolderRootID = 7519080285; //Podio Test Brand, use while testing

    $brand =  $requestParams['brand'];
    $campaign =  $requestParams['campaign'];
    $strategy = $requestParams['strategy'];

    $itemID = $requestParams['item_id'];
    $item = PodioItem::get($itemID);
    $appName = $item->app->name;
    $appID = $item->app->app_id;

    $StrategiesAppID = 8708019;
    $JobsApID = 9063521;
    $CampaignsAppID = 8699068;
    $BrandsAppID = 8780211;



    //Get Trigger Item & Field Values
    $BoxFolderIDFieldXID = 'box-folder-id';

    if($appID == $BrandsAppID){
        $BrandTitle = $item->fields['title']->values;
        $BrandFolerID = $item->fields['box-folder-id']->values;
    }

    if($appID == $JobsApID){
        $JobBoxFolerID = $item->fields['box-folder-id']->values;
        $StrategyFolderID = $item->fields['strategy-folder-id']->values;
        $CampaignFolderID = $item->fields['campaign-folder-id-2']->values;
        }

    if($appID == $CampaignsAppID){
        $CampaignTitle = $item->fields['title']->values;
        $CampaignFolderID = $item->fields['box-folder-id']->values;
        $BrandFolderID = $item->fields['brand-folder-id']->values;
        $BrandFolderName= $item->fields['brand-name']->values;
        $BrandAbbreviation = $item->fields['brand-abbreviation']->values;
    }

    if($appID == $StrategiesAppID){
        $StrategyTitle = $item->fields['title']->values;
        $StrategyFolderID = $item->fields['box-folder-id']->values;
        $StrategyFolderLink = $item->fields['box-folder-link']->values;
        $BrandFolderID = $item->fields['brand-folder-id']->values;
        $CampaignFolderID = $item->fields['campaign-folder-id']->values;
    }



    //Get New Files Attached to Trigger Item

    $FileIDsArray = array();
    $ItemFiles = $item->files;
    foreach($ItemFiles as $Files){
        $FileID = $Files->file_id;
        $FileCopy = PodioFile::copy($FileID);
        $NewFileID = $FileCopy->file_id;
        array_push($FileIDsArray, $NewFileID);
    }

    $FileInfoArray = array();
    foreach($FileIDsArray as $FileInfo) {
        $File = PodioFile::get($FileInfo);
        $FileName = $File->name;
        $FileDescription = $File->descrition;
        $FileType = $File->mimetype;
        $FileSize = $File->size;
        $FileLink = $File->link;
        $FileLinkTarget = $File->link_target;
        $FilePermaLink = $File->perma_link;
        $FileCreatedOn = $File->created_on;
        $FileReplaces = $File->replaces;
        array_push($FileInfoArray, $FileID, $FileName, $FileDescription, $FileType, $FileSize, $FileLink, $FileLinkTarget, $FilePermaLink, $FileCreatedOn, $FileReplaces);
    }




    //Update Trigger Item with Box Folder ID
    $UpdateTriggerItem = PodioItem::update($itemID,array(
        'fields' => array(
            $BoxFolderIDFieldXID => $NewBoxFolderID,
        ),
        array(
            'hook' => false
        )
    ));







    if(!$brand || !$campaign || !$strategy){

        throw new Exception("missing a required parameter, one of: brand, campaign, or strategy");
    }

    $targetFolderID = null;
    //check if brand folder exists
    $brandFolders = getFoldersInDirectory($brandFolderRootID,$accessToken);

    if(array_key_exists($brand, $brandFolders)){

        //"brand folder found"

        $campaignFolders = getFoldersInDirectory($brandFolders[$brand], $accessToken);

        if(array_key_exists($campaign, $campaignFolders)){

            //"campaign folder found"

            $strategyFolders = getFoldersInDirectory($campaignFolders[$campaign], $accessToken);

            if(array_key_exists($strategy, $strategyFolders)){


                //"strategy folder found, add the file here"
                //this is the target
                $targetFolderID = $strategyFolders[$strategy];


            }
            else{

                //"create strategy folder here";
                //the folder created is the target
                $response = json_decode(createFolder($strategy, $campaignFolders[$campaign], $accessToken));
                $targetFolderID = $response->id;

            }


        }
        else{

            //"create campaign folder here, and thus strategy folder"
            //the folder created is the target
            $response = json_decode(createFolder($campaign, $brandFolders[$brand], $accessToken));
            $response = json_decode(createFolder($strategy, $response->id, $accessToken));
            $targetFolderID = $response->id;

        }

    }
    else{

        //create brand folder here, and thus campaign and thus strategy folder
        //the folder created is the target
        $response = json_decode(createFolder($brand, $brandFolderRootID, $accessToken));
        $response = json_decode(createFolder($campaign, $response->id, $accessToken));
        $response = json_decode(createFolder($strategy, $response->id, $accessToken));
        $targetFolderID = $response->id;

    }

    if(!$targetFolderID) throw new Exception ("Target folder was not found or could not be created");

    //$response = savePodioFileToBox(null, $targetFolderID, $accessToken);

    /*Box only supports file names of 255 characters or less. Names that will not be supported are those that contain
    non-printable ascii, / or \, names with trailing spaces, and the special names “.” and “..”.*/


    return [
        'success' => true,
        'result' => $result,
    ];

}catch(Exception $e) {

    $event['response'] = [
        'status_code' => 400,
        'content' => [
            'success' => false,
            'result' => $result,
            'message' => "Error: " . $e,

        ]
    ];
}

