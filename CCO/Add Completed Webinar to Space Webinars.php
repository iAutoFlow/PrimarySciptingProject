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

    $CDWebinarItem = PodioItem::get($itemID);
    $triggerValue = $CDWebinarItem->fields['add-webinar-to']->values[0]['text'];
    $webinarType = $CDWebinarItem->fields['type']->values[0]['text'];

    $relatedCourses = $CDWebinarItem->fields['related-course']->values;
    foreach($relatedCourses as $course) {
        $courseItemID = $course->item_id;
        $courseITEM = PodioItem::get($courseItemID);

        //$result = $courseItemID;


        if ($webinarType == "Q&A Webinar" && $courseItemID == 403588860 || $webinarType == "Q&A Webinar" && $courseItemID == 439876454) {
            $WebinarAppID = $courseITEM->fields['user-form-app-id']->values;
        } elseif ($webinarType == "") {
            //$WebinarAppID = $courseITEM->fields['user-form-app-id']->values;}

            if ($triggerValue == "Publish") {
                PodioItem::create($WebinarAppID, array(
                    'fields' => array(
                        'webinar-item' => array(
                            'value' => (int)$itemID
                        )
                    )
                ));
            }
        };


    }




    //$WebinarFiles = $CDWebinarItem->files;
    //foreach($WebinarFiles);

    //$signatureCopy = PodioFile::copy($WebinarFileID);




        PodioItem::update($itemID, array(
            'fields' => array(
                'add-webinar-to' => array(
                    'value' => "..."
                )
            )
        ));

        return [
            'success' => true,
            'result' => $relatedCourses
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