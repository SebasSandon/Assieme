<?php 
/**
 *  File: expense/create.php
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

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('expense/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$expensePeriod = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_NUMBER_INT);
$expenseDate = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_NUMBER_INT);
$expenseCategories = filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$expenseCentres = filter_input(INPUT_POST, 'centre', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$expenseComments = filter_input(INPUT_POST, 'comment', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$expenseAmounts = filter_input(INPUT_POST, 'amount', FILTER_DEFAULT, FILTER_FORCE_ARRAY);

if(empty($expensePeriod)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if(empty($expenseDate)){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

if(is_array($expenseCategories) && count($expenseCategories) > 0){
    $expenseCategories = filter_var_array($expenseCategories, FILTER_SANITIZE_STRING);
    if(count(array_filter($expenseCategories)) !== count($expenseCategories)){
        $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

if(is_array($expenseComments) && count($expenseComments) > 0){
    $expenseComments = filter_var_array($expenseComments, FILTER_SANITIZE_STRING);
    if(count(array_filter($expenseComments)) !== count($expenseComments)){
        $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

if(is_array($expenseAmounts) && count($expenseAmounts) > 0){
    $expenseAmounts = filter_var_array($expenseAmounts, FILTER_SANITIZE_NUMBER_INT);
    if(count(array_filter($expenseAmounts)) !== count($expenseAmounts)){
        $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

$expenseDate = date("Y-m-d", strtotime($expenseDate));

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
    
    if(count($expenseCategories) > 0){
        $createQueryString = queryBuilder(array(
            [true, 'INSERT INTO expense '],
            [true, '(category, centre, period, date, comment, amount) '],
            [true, 'VALUES(:category, :centre, :period, :date, :comment, :amount) ']
        ));
        
        foreach($expenseCategories as $key => $expenseCategory){
            $createQuery = $createPDO->prepare($createQueryString);
            
            $createQuery->bindValue(':category', $expenseCategories[$key], PDO::PARAM_STR);
            $createQuery->bindValue(':centre', $expenseCentres[$key], PDO::PARAM_STR);
            
            $createQuery->bindValue(':period', $expensePeriod, PDO::PARAM_INT);
            $createQuery->bindValue(':date', $expenseDate, PDO::PARAM_INT);
            
            $createQuery->bindValue(':comment', $expenseComments[$key], PDO::PARAM_STR);
            $createQuery->bindValue(':amount', $expenseAmounts[$key], PDO::PARAM_INT);
            
            $createQuery->execute();
        
            $expenseId = $createPDO->lastInsertId();
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