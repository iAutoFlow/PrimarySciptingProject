<?php
/**
 * Created by PhpStorm.
 * User: captkirk
 * Date: 7/14/2016
 * Time: 1:36 PM
 */

//***************TOKEN MANAGEMENT*************************************************************************************//
//todo testing
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
print_r($response);exit;

}

//todo testing
function getAccessToken($connectionID){

    $connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find($connectionID);

    $refreshToken = $connection->refresh_token;
    $clientID = $connection->connectionService->config['client_id'];
    $clientSecret = $connection->connectionService->config['client_secret'];

    //refresh it
    $response = json_decode(refreshToken($refreshToken,$clientID,$clientSecret));

    //store it
    $access_token = $response->access_token;
    $refresh_token= $response->refresh_token;

    if(!empty($access_token))
        $connection->access_token =  $access_token;

    if(!empty($refresh_token))
        $connection->refresh_token = $refresh_token;

    $connection->save();

    return $connection->access_token;


}

//***************CURL UTILITIES***************************************************************************************//
//get curl call
function curlGet($url, $accessToken){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type:multipart/form-data'));
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    $response = curl_exec($ch);

    if (!$response) return "curl_error: " . curl_error($ch);

    curl_close($ch);

    return $response;

}

function curlPost($url, $fields, $accessToken){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

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

function curlPostFormData($url, $fields, $accessToken){

    $ch = curl_init();

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



    if (!$response) {
        return "curl_error: " . curl_error($ch);
    }else{
        curl_close($ch);
        return $response;
    }


}

function curlPut($url, $fields, $accessToken){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HEADER,true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   // return web page
    curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);

    $data_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken, 'Content-Type:application/json','Content-Length: ' . strlen($data_string)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);

    $response = curl_exec($ch);

    curl_close($ch);

    if (!$response) {
        return "curl_error: " . curl_error($ch);
    }else{
        return $response;
    }

}

//***************FOLDERS RESOURCES************************************************************************************//

//todo add fields, limit and offset option
function getFoldersItems($folderID, $accessToken){

    $url = "https://api.box.com/2.0/folders/$folderID/items?limit=1000";
    return curlGet($url,$accessToken);


}

//get json of folder info identified by folderid
function getFoldersInfo($folderID, $accessToken){

    $url = 'https://www.box.com/api/2.0/folders/'.$folderID;
    return curlGet($url,$accessToken);

}

//todo
function updateFoldersInfo(){

    //todo
    return "updateFoldersInfo not implemented";

}

//get a folder ID by name
//todo recursive search if not found by search()
function getFoldersIDByName($name, $accessToken){

    $response = json_decode(search($name, "folder", $accessToken));

    $note = "Note: If an item is added to Box, it will take 10 minutes before it will be accessible through the search endpoint";

    foreach($response->entries as $folder){

        if(strcmp($folder->name,$name)===0){

            $id = $folder->id;

        }

    }

    if(!empty($id)){
        return '{"id":"'.$id.'"}';
    }
    else{
        return '{"id":null, "note":"'.$note.'"}';
    }

}

//create folder
function createFolder($name, $parentID, $accessToken){

    $url = 'https://api.box.com/2.0/folders';

    $fields = array(
        'name' => $name,
        'parent' => array('id'=>$parentID)
    );

    return curlPost($url, $fields, $accessToken);

}

//get sharable link to folder
function getFolderLink($folderID, $accessToken){

    $url = 'https://www.box.com/api/2.0/folders/'.$folderID;
    $fields = array('shared_link'=>array('access'=>'open'));
    return json_decode(curlPut($url, $fields, $accessToken))->shared_link->url;


}

//create folder return link
function createFolderGetLink($name, $parentID, $accessToken){

    $response = createFolder($name, $parentID, $accessToken);
    $json = json_decode($response);
    if(!$json) return $response;
    $id = $json->id;
    $response2 = getFolderLink($json->id, $accessToken);
    return '{"id":"'.$id.'","url":"'.$response2.'"}';

}



//***************FILES RESOURCES**************************************************************************************//


function downloadFile($fileID, $accessToken){

    $url = 'https://www.box.com/api/2.0/files/'.$fileID.'/content';
    return curlGet($url, $accessToken);


}

function getFilesInfo($fileID, $accessToken){

    $url = 'https://www.box.com/api/2.0/files/'.$fileID;
    return curlGet($url,$accessToken);

}

//get a folder ID by name
function getFilesIDByName($name, $accessToken){

    $response = json_decode(search($name, "file", $accessToken));

    $id = "Note: If an item is added to Box, it will take 10 minutes before it will be accessible through the search endpoint";

    foreach($response->entries as $file){

        if(strcmp($file->name,$name)===0){

            $id = $file->id;

        }

    }

    if(!is_numeric($id)) $id = searchHarder($name, 0, $accessToken);

    return '{"id":"'.$id.'"}';

}

function uploadFile($fileName, $path_to_file, $boxFolderID, $accessToken){

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

    $url = 'https://upload.box.com/api/2.0/files/content';

    return curlPostFormData($url, $fields, $accessToken);

}

//***************SEARCH FUNCTION**************************************************************************************//
//query: string to search for
//type: folder
//todo other options
function search($query, $type, $accessToken){

    $parameters = "query=$query&type=$type";

    $url = 'https://www.box.com/api/2.0/search/?'.$parameters;
    return curlGet($url,$accessToken);

}

try{

    $connectionID = null;

    $parameters = $event['request']['parameters'];

    $connectionID = $parameters['connection_id'];
    if(!$connectionID) throw new Exception("Missing required parameter: connection_id");

    $accessToken = getAccessToken($connectionID);
    if(!$accessToken){
        sleep(2);
        $accessToken = getAccessToken($connectionID);
    }
    if(!$accessToken) throw new Exception("Unable to getAccessToken with that connection_id $accessToken");

    $method = $event['request']['method'];
    $resource = explode("/",$event['resource']);

    $result = null;

    switch($resource[0]){

        //custom, hidden functions
        case "checkToken":
            $result = $result."access token: ".$accessToken;
            break;

        case "refreshToken":
            $connection = \EnvireTech\OauthConnector\Models\OrganizationConnection::with('connectionService')->find($connectionID);

            $refreshToken = $connection->refresh_token;
            $clientID = $connection->connectionService->config['client_id'];
            $clientSecret = $connection->connectionService->config['client_secret'];

            //refresh it
            $result = refreshToken($refreshToken,$clientID,$clientSecret);
            print_r($result);exit;
            break;

        case "folders":

            switch($resource[1]){

                case "createSharedLink":
                    $folderID = $event['request']['parameters']['folder_id'];
                    if(!$folderID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
                    $result = getFolderLink($folderID, $accessToken);
                    break;

                case "createFolder":
                    $name = $event['request']['parameters']['name'];
                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
                    $parentID = $event['request']['parameters']['parent_id'];
                    if(!$parentID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
                    $result = createFolder($name,$parentID,$accessToken);
                    break;

                case "createFolderGetLink":
                    $name = $event['request']['parameters']['name'];
                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
                    $parentID = $event['request']['parameters']['parent_id'];
                    if(!$parentID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
                    $result = createFolderGetLink($name, $parentID, $accessToken);
                    break;

                case "getFolderIDByName":
                    $name = $event['request']['parameters']['name'];
                    $root = $event['request']['parameters']['root'];
                    $root = $root?$root:0;
                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
                    $result = getFoldersIDByName($name, $accessToken);
                    break;

                case "Permanently_Delete":
                    $result = $result."Permanently_Delete not implemented";
                    break;

                case "create_delete":
                    $result = $result. "create_delete not implemented";
                    break;

                case null:
                    $name = $event['request']['parameters']['name'];
                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
                    $parentID = $event['request']['parameters']['parent_id'];
                    if(!$parentID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
                    $result = createFolder($name, $parentID, $accessToken);
                    break;

                default:
                    if($resource[2]=='items') $result = getFoldersItems($resource[1],$accessToken);
                    switch($method){
                        case "GET":
                            $result = getFoldersInfo($resource[1],$accessToken);
                            break;
                        case "PUT":
                            $result = $result. updateFoldersInfo();
                            break;
                        default: $result = $result."Not implemented, not on radar";
                    }
                    break;
            }

            break;

        case "files":

            switch($resource[1]){

                case "createFilesGetLink":
//                    $name = $event['request']['parameters']['name'];
//                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
//                    $parentID = $event['request']['parameters']['parent_id'];
//                    if(!$parentID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
//                    $result = createFolderGetLink($name, $parentID, $accessToken);
                    break;

                case "getFileIDByName":
                    $name = $event['request']['parameters']['name'];
                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
                    $result = getFilesIDByName($name, $accessToken);
                    break;
                case "upload":
                    $fileName = $event['request']['parameters']['file_name'];
                    $parentID = $event['request']['parameters']['parent_id'];
                    $path = $event['request']['parameters']['path'];
                    if(!$fileName || !$parentID || !$path) throw new Exception("file name or parent id missing");
                    $result = uploadFile($fileName, $path, $parentID, $accessToken);
                    break;
                case "download":
                    $fileID = $event['request']['parameters']['file_id'];
                    if(!$fileID) throw new Exception("File ID is missing ");
                    $result = downloadFile($fileID, $accessToken);
                    break;
                case null:
//                    $name = $event['request']['parameters']['name'];
//                    if(!$name) throw new Exception("Folder Name Parameter is Required 'name' ");
//                    $parentID = $event['request']['parameters']['parent_id'];
//                    if(!$parentID) throw new Exception("Parent ID Parameter is Required 'parent_id' ");
//                    $result = createFolder($name, $parentID, $accessToken);
                    break;

                default:
                    //if($resource[2]) $result = getFoldersItems($resource[1],$accessToken);
                    switch($method){
                        case "GET":
                            $result = getFilesInfo($resource[1],$accessToken);
                            break;
//                        case "PUT":
//                            $result = $result. updateFoldersInfo();
//                            break;
                        default: $result = $result."Not implemented, not on radar";
                    }
                    break;
            }

            break;

        default: $result = $result."Not implemented, not on radar";

    }



    $event['response'] = [
        'status_code' => 200,
        'content' => "$result",
        'content_type' => "html"
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
