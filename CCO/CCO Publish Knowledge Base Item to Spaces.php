<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 5/24/2016
 * Time: 5:27 PM
 */
try{

    $requestParams = $event['request']['parameters'];
    $itemID = $requestParams['item_id'];



// Client credentials
    $username = "podio@techego.com";
    $password = "hV91Kg$4!oJUxYZ[";
    $client_key = 'dreamfactory-ebqqb5';
    $client_secret = 'Un15q9YOvjxGT94l0sqSFSEpsnVe5e9uGQ2nPqtTdBuguKssOuWfWHKzof8r37KO';

// Authenticate Podio
    Podio::setup($client_key, $client_secret);
    Podio::authenticate_with_password($username, $password);

//Get Triger Item & Values

    $CDKnowledgeBaseItem = PodioItem::get($itemID);
    $triggerValue = $CDKnowledgeBaseItem->fields['add-knowledge-base-item-to']->values[0]['text'];
    $KnowledgeBaseType = $CDKnowledgeBaseItem->fields['category']->values[0]['text'];
    $KnowledgeBaseStatus = $CDKnowledgeBaseItem->fields['status']->values[0]['text'];
    //$KBImage = $CDKnowledgeBaseItem->fields['image']

    $relatedCourses = $CDKnowledgeBaseItem->fields['related-courses']->values;
    foreach($relatedCourses as $course){
        $courseItemID = $course->item_id;
        $courseITEM = PodioItem::get($courseItemID);

        //$result = $courseItemID;



        if($KnowledgeBaseStatus == "Active" && $courseItemID == 403588860 || $KnowledgeBaseStatus == "Active" && $courseItemID == 439876454){
            $KnowledgeBaseAppID = $courseITEM->fields['progress-tracker-app-id']->values;
        }

       // elseif($webinarType == ""){
            //$WebinarAppID = $courseITEM->fields['user-form-app-id']->values;}

            if($triggerValue == "Publish") {
                PodioItem::create($KnowledgeBaseAppID, array(
                    'fields' => array(
                        'knowledge-base-item' => array(
                            'value' => (int)$itemID
                        )
                    )
                ));
            }};







        //$WebinarFiles = $CDWebinarItem->files;
        //foreach($WebinarFiles);

        //$signatureCopy = PodioFile::copy($WebinarFileID);




        PodioItem::update($itemID, array(
            'fields' => array(
                'add-knowledge-base-item-to' => array(
                    'value' => "..."
                )
            )
        ));

        return [
            'success' => true,
            'result' => $result
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


}