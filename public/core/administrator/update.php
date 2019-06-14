<?php 
/**
 *  File: administrator/update.php
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
    $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] !== 'MASTER' ){
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

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('administrator/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$administratorSalt = random_bytes(16);
$administratorSalt = base64_encode($administratorSalt);
$administratorSalt = str_replace('+', '.', $administratorSalt);

$administratorUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$administratorPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
if($administratorPassword){
    $administratorPassword = crypt($administratorPassword, '$2y$10$'.$administratorSalt.'$');
}

$administratorLevel = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING);
$administratorName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$administratorEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if(empty($administratorUsername) ||
   empty($administratorLevel)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if($administratorLevel === 'ROOT'){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
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
    if(!empty($administratorPassword)){
        $updateQuery = $updatePDO->prepare('UPDATE administrator SET username = :username, password = :password, salt = :salt, email = :email, name = :name, level = :level WHERE id = :request');
        $updateQuery->bindValue(':password', $administratorPassword, PDO::PARAM_STR);
        $updateQuery->bindValue(':salt', $administratorSalt, PDO::PARAM_STR);
    }else{
        $updateQuery = $updatePDO->prepare('UPDATE administrator SET username = :username, email = :email, name = :name, level = :level WHERE id = :request');
    }
    $updateQuery->bindValue(':username', $administratorUsername, PDO::PARAM_STR);  
    $updateQuery->bindValue(':email', $administratorEmail, PDO::PARAM_STR);
    $updateQuery->bindValue(':name', $administratorName, PDO::PARAM_STR);
    $updateQuery->bindValue(':level', $administratorLevel, PDO::PARAM_STR);
    
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