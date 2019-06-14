<?php 
/**
 *  File: incomeGroup/update.php
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
$request = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_STRING);
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
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}
        
/**
 * csrf
 */
$csrfToken = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('income-group/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$incomeGroupName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$incomeGroupAlias = filter_input(INPUT_POST, 'alias', FILTER_SANITIZE_STRING);
$incomeGroupDescription = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

if(empty($incomeGroupName)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if(empty($incomeGroupAlias)){
    $incomeGroupAlias = slugify($incomeGroupName);
}

/**
 * connection
 */
try {
    $updatePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    
    $updatePDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_CONNECTION_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}

try {
    $updateQuery = $updatePDO->prepare('UPDATE income_group SET name = :name, alias = :alias, description = :description WHERE id = :request');
    $updateQuery->bindValue(':name', $incomeGroupName, PDO::PARAM_STR);  
    $updateQuery->bindValue(':alias', $incomeGroupAlias, PDO::PARAM_STR);
    $updateQuery->bindValue(':description', $incomeGroupDescription, PDO::PARAM_STR);
    
    $updateQuery->bindValue(':request', $request, PDO::PARAM_STR);
            
    $updateQuery->execute();

    $response->setResponse(true,'SUCCESS_UPDATE');
    $notification = new notificationClass('success',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    die;   
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_QUERY_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}