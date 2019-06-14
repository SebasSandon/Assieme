<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $expenseGroupPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
} catch (PDOException $e) {
    $response->setResponse(false,'DATABASE_ERROR');
    $notification = new notificationClass('danger',$response->sysMessage);
    array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
}

/**
 * action
 */
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

switch($action){
    case 'create':
        echo $twig->render('expenseGroup/create.html.twig', array(
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $expenseGroupQuery = $expenseGroupPDO->prepare('SELECT * FROM expense_group WHERE id = :id LIMIT 1');
            $expenseGroupQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseGroupQuery->execute();

            $expenseGroupResult = $expenseGroupQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseGroupResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseGroup.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseGroup.php'
            ));
            break;
        }
        
        echo $twig->render('expenseGroup/update.html.twig', array(
            'expenseGroup' => $expenseGroupResult
        ));
        break; 
        
    case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $expenseGroupQuery = $expenseGroupPDO->prepare('SELECT * FROM expense_group WHERE id = :id LIMIT 1');
            $expenseGroupQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseGroupQuery->execute();

            $expenseGroupResult = $expenseGroupQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseGroupResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseGroup.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseGroup.php'
            ));
            break;
        }
        
        echo $twig->render('expenseGroup/delete.html.twig', array(
            'expenseGroup' => $expenseGroupResult
        ));
        break; 
    default:
        /**
         * input
         */
	
        try {
            $countQuery = $expenseGroupPDO->prepare('SELECT COUNT(*) FROM expense_group');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $expenseGroupQuery = $expenseGroupPDO->prepare('SELECT * FROM expense_group ORDER BY position ASC');
            $expenseGroupQuery->execute();

            $expenseGroupResult = $expenseGroupQuery->fetchAll(PDO::FETCH_OBJ);
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expenseGroup/index.html.twig', array(
            'expenseCategories' => $expenseGroupResult,
        ));
        break;
}