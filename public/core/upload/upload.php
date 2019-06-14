<?php 
/**
 *  File: upload/upload.php
 *  Author: Crece Consultores
 */
header('Content-Type: application/json');

require_once '../sys/init.php';

/**
 * response
 */
$response = new responseClass();

/**
 * control
 */
if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] !== 'ROOT' &&
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] !== 'MASTER' &&
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] !== 'SIMPLE' ){
    $response->setResponse(false,'LOGIN_DENIED_ERROR');
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$uploadFiles = $_FILES['files'];

if(empty($uploadFiles)){   
    $response->setResponse(false, 'INPUT_ERROR');
    echo json_encode($response);
    exit;
}

/**
 * extensions.
 */
$allowedExts = array("gif", "jpeg", "jpg", "png", "JPG", "JPEG", "PNG");

/**
 * files
 */
foreach($uploadFiles['name'] as $i => $uploadFile){
    $extension = pathinfo($uploadFile, PATHINFO_EXTENSION);

    if ((($uploadFiles["type"][$i] == "image/gif")
	|| ($uploadFiles["type"][$i] == "image/jpeg")
	|| ($uploadFiles["type"][$i] == "image/jpg")
	|| ($uploadFiles["type"][$i] == "image/pjpeg")
	|| ($uploadFiles["type"][$i] == "image/x-png")
	|| ($uploadFiles["type"][$i] == "image/png"))
	&& in_array($extension, $allowedExts)) {

        $fileName = uniqid().'.'.strtolower($extension);
        
        $uploadPath = '../../../uploads/media/'.$fileName;
        
        if( move_uploaded_file($uploadFiles["tmp_name"][$i], $uploadPath) ){
           $uploadedFiles[] = 'http://'.MEDIASUBDOMAIN.'.'.MEDIADOMAIN.'/media/'.$fileName;
        }else{
            echo false;
        }
    } 
}

echo json_encode(array(
    'uploads' => $uploadedFiles
)); 