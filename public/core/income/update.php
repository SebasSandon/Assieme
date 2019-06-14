<?php 
/**
 *  File: income/update.php
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

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('income/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$incomePeriod = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_STRING);
$incomeCategory = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
$incomeCentre = filter_input(INPUT_POST, 'centre', FILTER_SANITIZE_STRING);
$incomeDate = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$incomeComment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
$incomeAmount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);

if( empty($incomePeriod)    ||
    empty($incomeCategory)  ||
    empty($incomeDate)      ||
    empty($incomeComment)   ||
    empty($incomeAmount)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

$incomeDate = date("Y-m-d", strtotime($incomeDate));

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
    $updateQuery = $updatePDO->prepare('UPDATE income SET category = :category, centre = :centre, period = :period, date = :date, comment = :comment, amount = :amount WHERE id = :request');
    $updateQuery->bindValue(':category', $incomeCategory, PDO::PARAM_STR);
    $updateQuery->bindValue(':centre', $incomeCentre, PDO::PARAM_STR);  
    $updateQuery->bindValue(':period', $incomePeriod, PDO::PARAM_STR);
    $updateQuery->bindValue(':date', $incomeDate, PDO::PARAM_STR);
    $updateQuery->bindValue(':comment', $incomeComment, PDO::PARAM_STR);
    $updateQuery->bindValue(':amount', $incomeAmount, PDO::PARAM_INT);
    
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