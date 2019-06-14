<?php 
/**
 *  File: expenseCategory/create.php
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

if(!$_SESSION[SYSSESSION.'_'.SYSMODULE.'_csrf']->validateToken('expense-category/'.basename($_SERVER['REQUEST_URI']),$csrfToken)){
    $response->setResponse(false, 'TOKEN_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

/**
 * input
 */
$expenseCategoryGroup = filter_input(INPUT_POST, 'group', FILTER_SANITIZE_STRING);
$expenseCategoryName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$expenseCategoryDescription = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

if( empty($expenseCategoryGroup) ||
    empty($expenseCategoryName)  ){
    $response->setResponse(false, 'INPUT_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    exit;
}

$expenseCategoryAlias = slugify($expenseCategoryName);

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
    $createQuery = $createPDO->prepare('INSERT INTO expense_category (parent, name, alias, description) VALUES (:group, :name, :alias, :description)');
    $createQuery->bindValue(':group', $expenseCategoryGroup, PDO::PARAM_STR);
    $createQuery->bindValue(':name', $expenseCategoryName, PDO::PARAM_STR);
    $createQuery->bindValue(':alias', $expenseCategoryAlias, PDO::PARAM_STR);
    $createQuery->bindValue(':description', $expenseCategoryDescription, PDO::PARAM_STR);
            
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