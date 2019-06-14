<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $incomeCategoryPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
        /**
         *  option 
         */
        try {
            $optionQuery = $incomeCategoryPDO->prepare('SELECT * FROM income_group ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionGroups = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('incomeCategory/create.html.twig', array(
            'option' => array(
                'groups' => $optionGroups,
            )
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeCategoryQuery = $incomeCategoryPDO->prepare('SELECT * FROM income_category WHERE id = :id LIMIT 1');
            $incomeCategoryQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeCategoryQuery->execute();

            $incomeCategoryResult = $incomeCategoryQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeCategoryResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeCategory.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeCategory.php',
            ));
            break;
        }
        
        /**
         *  option 
         */
        try {
            $optionQuery = $incomeCategoryPDO->prepare('SELECT * FROM income_group ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionGroups = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('incomeCategory/update.html.twig', array(
            'incomeCategory' => $incomeCategoryResult,
            'option' => array(
                'groups' => $optionGroups,
            )
        ));
        break; 
        
    case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeCategoryQuery = $incomeCategoryPDO->prepare('SELECT * FROM income_category WHERE id = :id LIMIT 1');
            $incomeCategoryQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeCategoryQuery->execute();

            $incomeCategoryResult = $incomeCategoryQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeCategoryResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeCategory.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'incomeCategory.php'
            ));
            break;
        }
        
        echo $twig->render('incomeCategory/delete.html.twig', array(
            'incomeCategory' => $incomeCategoryResult
        ));
        break; 
    default:
        /**
         * input
         */

        try {
            $countQuery = $incomeCategoryPDO->prepare('SELECT COUNT(*) FROM income_category');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $incomeCategoryQuery = $incomeCategoryPDO->prepare('SELECT income_category.*, income_group.name AS group_name FROM income_category LEFT JOIN income_group ON income_group.id = income_category.parent ORDER BY position ASC');
            $incomeCategoryQuery->execute();

            $incomeCategoryResult = $incomeCategoryQuery->fetchAll(PDO::FETCH_OBJ);
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('incomeCategory/index.html.twig', array(
            'incomeCategories' => $incomeCategoryResult,
        ));
        break;
}