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
 * request
 */
$request = filter_input(INPUT_POST, 'item', FILTER_SANITIZE_STRING);
if(empty($request)){
    $response->setResponse(false, 'REQUEST_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

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
 * unlink
 */
$temp = explode('/',$request);
$file = array_pop($temp);

if (unlink( '../../../uploads/media/'.$file ) ){
    echo json_encode(true);
}else{
    echo json_encode(false);
}