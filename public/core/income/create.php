<?php 
/**
 *  File: income/create.php
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
$incomePeriod = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_NUMBER_INT);
$incomeDate = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_NUMBER_INT);
$incomeCategories = filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$incomeCentres = filter_input(INPUT_POST, 'centre', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$incomeComments = filter_input(INPUT_POST, 'comment', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$incomeAmounts = filter_input(INPUT_POST, 'amount', FILTER_DEFAULT, FILTER_FORCE_ARRAY);

if(empty($incomePeriod)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if(empty($incomeDate)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if(is_array($incomeCategories) && count($incomeCategories) > 0){
    $incomeCategories = filter_var_array($incomeCategories, FILTER_SANITIZE_STRING);
    if(count(array_filter($incomeCategories)) !== count($incomeCategories)){
       $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

if(is_array($incomeComments) && count($incomeComments) > 0){
    $incomeComments = filter_var_array($incomeComments, FILTER_SANITIZE_STRING);
    if(count(array_filter($incomeComments)) !== count($incomeComments)){
       $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

if(is_array($incomeAmounts) && count($incomeAmounts) > 0){
    $incomeAmounts = filter_var_array($incomeAmounts, FILTER_SANITIZE_NUMBER_INT);
    if(count(array_filter($incomeAmounts)) !== count($incomeAmounts)){
       $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

$incomeDate = date("Y-m-d", strtotime($incomeDate));

/**
 * connection
 */
try {
    $createPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    
    $createPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $createPDO->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_CONNECTION_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}

try {
    $createPDO->beginTransaction();
    
    if(count($incomeCategories) > 0){
        $createQueryString = queryBuilder(array(
            [true, 'INSERT INTO income '],
            [true, '(category, centre, period, date, comment, amount) '],
            [true, 'VALUES(:category, :centre, :period, :date, :comment, :amount) ']
        ));
        
        foreach($incomeCategories as $key => $incomeCategory){
            $createQuery = $createPDO->prepare($createQueryString);
            
            $createQuery->bindValue(':category', $incomeCategories[$key], PDO::PARAM_STR);
            $createQuery->bindValue(':centre', $incomeCentres[$key], PDO::PARAM_STR);
            
            $createQuery->bindValue(':period', $incomePeriod, PDO::PARAM_INT);
            $createQuery->bindValue(':date', $incomeDate, PDO::PARAM_INT);
            
            $createQuery->bindValue(':comment', $incomeComments[$key], PDO::PARAM_STR);
            $createQuery->bindValue(':amount', $incomeAmounts[$key], PDO::PARAM_INT);
            
            $createQuery->execute();
        
            $incomeId = $createPDO->lastInsertId();
        }
    }
    
    $createPDO->commit();

    $response->setResponse(true,'SUCCESS_UPDATE');
    $notification = new notificationClass('success',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    die;   
} catch (PDOException $e) {
    try { $createPDO->rollBack(); } catch (PDOException $e2) {}
    
    $response->setResponse(false,'DATABASE_QUERY_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}