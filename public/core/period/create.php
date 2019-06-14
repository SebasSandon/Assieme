<?php 
/**
 *  File: period/create.php
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

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('period/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$periodName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$periodDescription = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
$periodBalance = filter_input(INPUT_POST, 'balance', FILTER_SANITIZE_NUMBER_INT);

if( empty($periodName) ){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

$periodAlias = slugify($periodName);

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
    $createQuery = $createPDO->prepare('INSERT INTO period (name, alias, description, balance) VALUES (:name, :alias, :description, :balance)');
    $createQuery->bindValue(':name', $periodName, PDO::PARAM_STR);
    $createQuery->bindValue(':alias', $periodAlias, PDO::PARAM_STR);
    $createQuery->bindValue(':description', $periodDescription, PDO::PARAM_STR);
    $createQuery->bindValue(':balance', $periodBalance, PDO::PARAM_INT);
            
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