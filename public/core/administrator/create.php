<?php 
/**
 *  File: administrator/create.php
 *  Author: Crece Consultores
 */
header('Content-Type: application/json');

require_once '../sys/init.php';

/**
 * response
 */
$response = new responseClass();

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
$administratorPassword = crypt($administratorPassword, '$2y$10$'.$administratorSalt.'$');

$administratorLevel = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING);
$administratorName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$administratorEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if(empty($administratorUsername) ||
   empty($administratorPassword) ||
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
    $createPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    
    $createPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_CONNECTION_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}

try {
    $createQuery = $createPDO->prepare('INSERT INTO administrator (username, password, salt, email, name, level) VALUES (:username, :password, :salt, :email, :name, :level)');
    $createQuery->bindValue(':username', $administratorUsername, PDO::PARAM_STR);
    $createQuery->bindValue(':password', $administratorPassword, PDO::PARAM_STR);
    $createQuery->bindValue(':salt', $administratorSalt, PDO::PARAM_STR);
    $createQuery->bindValue(':email', $administratorEmail, PDO::PARAM_STR);
    $createQuery->bindValue(':name', $administratorName, PDO::PARAM_STR);
    $createQuery->bindValue(':level', $administratorLevel, PDO::PARAM_STR);
            
    $createQuery->execute();

    $response->setResponse(true,'SUCCESS_CREATE');
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