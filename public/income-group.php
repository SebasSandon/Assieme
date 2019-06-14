<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $incomeGroupPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
        echo $twig->render('incomeGroup/create.html.twig', array(
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeGroupQuery = $incomeGroupPDO->prepare('SELECT * FROM income_group WHERE id = :id LIMIT 1');
            $incomeGroupQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeGroupQuery->execute();

            $incomeGroupResult = $incomeGroupQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeGroupResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeGroup.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeGroup.php'
            ));
            break;
        }
        
        echo $twig->render('incomeGroup/update.html.twig', array(
            'incomeGroup' => $incomeGroupResult
        ));
        break; 
        
    case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeGroupQuery = $incomeGroupPDO->prepare('SELECT * FROM income_group WHERE id = :id LIMIT 1');
            $incomeGroupQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeGroupQuery->execute();

            $incomeGroupResult = $incomeGroupQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeGroupResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeGroup.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeGroup.php'
            ));
            break;
        }
        
        echo $twig->render('incomeGroup/delete.html.twig', array(
            'incomeGroup' => $incomeGroupResult
        ));
        break; 
    default:
        /**
         * input
         */
        
        try {
            $countQuery = $incomeGroupPDO->prepare('SELECT COUNT(*) FROM income_group');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $incomeGroupQuery = $incomeGroupPDO->prepare('SELECT * FROM income_group ORDER BY position ASC');
            $incomeGroupQuery->execute();

            $incomeGroupResult = $incomeGroupQuery->fetchAll(PDO::FETCH_OBJ);
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('incomeGroup/index.html.twig', array(
            'incomeCategories' => $incomeGroupResult,
        ));
        break;
}