<?php 
/**
 *  File: expense-category/sort.php
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
$elementsId = filter_input(INPUT_POST, 'id', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
$elementsPosition = filter_input(INPUT_POST, 'position', FILTER_DEFAULT, FILTER_FORCE_ARRAY);

if(is_array($elementsId) && count($elementsId) > 0){
    $elementsId = filter_var_array($elementsId, FILTER_SANITIZE_NUMBER_INT);
    if(count(array_filter($elementsId)) !== count($elementsId)){
       $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

if(is_array($elementsPosition) && count($elementsPosition) > 0){
    $elementsPosition = filter_var_array($elementsPosition, FILTER_SANITIZE_NUMBER_INT);
    if(count(array_filter($elementsPosition)) !== count($elementsPosition)){
       $response->setResponse(false, 'INPUT_ERROR');
        $notification = new notificationClass('danger',$response->sysMessage);
        array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        echo json_encode($response);
        exit; 
    }
}

/**
 * connection
 */
try {
    $updatePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
    
    $updatePDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $updatePDO->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_CONNECTION_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}

try {
    /**
     * counts
     */
    $updateCount = 0;
    
    $updatePDO->beginTransaction();
    
    $elementsQueryString = queryBuilder(array(
        [true, 'SELECT expense_category.* '],
        [true, 'FROM expense_category '],
        [true, 'ORDER BY position ASC ']
    ));
            
    $elementsQuery = $updatePDO->prepare($elementsQueryString);
    $elementsQuery->execute();
            
    $elementsResult = $elementsQuery->fetchAll(PDO::FETCH_OBJ);
    
    foreach($elementsResult as $actualElement){
        if(in_array($actualElement->id, $elementsId)){
            $elementKey = array_search($actualElement->id, $elementsId);
            $elementPosition = $elementsPosition[$elementKey];
            
            $updateQuery = $updatePDO->prepare('UPDATE expense_category SET position = :position WHERE id = :id');
            $updateQuery->bindValue(':position', $elementPosition, PDO::PARAM_INT);

            $updateQuery->bindValue(':id', $actualElement->id, PDO::PARAM_STR);
            
            if( $updateQuery->execute()){
                $updateCount++;
            }
        }
    }
    
    $updatePDO->commit();

    $response->setResponse(true,'SUCCESS_UPDATE');
    $notification = new notificationClass('success','Actualizados: '.$updateCount);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    echo json_encode($response);
    die;   
} catch (PDOException $e) {
    try { $updatePDO->rollBack(); } catch (PDOException $e2) {}
    
    $response->setResponse(false,'DATABASE_QUERY_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
    $response->setException($e);
    echo json_encode($response);
    die;
}