<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $expenseCategoryPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
            $optionQuery = $expenseCategoryPDO->prepare('SELECT * FROM expense_group ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionGroups = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expenseCategory/create.html.twig', array(
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
            $expenseCategoryQuery = $expenseCategoryPDO->prepare('SELECT * FROM expense_category WHERE id = :id LIMIT 1');
            $expenseCategoryQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseCategoryQuery->execute();

            $expenseCategoryResult = $expenseCategoryQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseCategoryResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseCategory.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseCategory.php'
            ));
            break;
        }
        
        /**
         *  option 
         */
        try {
            $optionQuery = $expenseCategoryPDO->prepare('SELECT * FROM expense_group ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionGroups = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expenseCategory/update.html.twig', array(
            'expenseCategory' => $expenseCategoryResult,
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
            $expenseCategoryQuery = $expenseCategoryPDO->prepare('SELECT * FROM expense_category WHERE id = :id LIMIT 1');
            $expenseCategoryQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseCategoryQuery->execute();

            $expenseCategoryResult = $expenseCategoryQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseCategoryResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseCategory.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expenseCategory.php'
            ));
            break;
        }
        
        echo $twig->render('expenseCategory/delete.html.twig', array(
            'expenseCategory' => $expenseCategoryResult
        ));
        break; 
    default:
        /**
         * input
         */

        try {
            $countQuery = $expenseCategoryPDO->prepare('SELECT COUNT(*) FROM expense_category');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $expenseCategoryQuery = $expenseCategoryPDO->prepare('SELECT expense_category.*, expense_group.name AS group_name FROM expense_category LEFT JOIN expense_group ON expense_group.id = expense_category.parent ORDER BY position ASC');
            $expenseCategoryQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $expenseCategoryQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $expenseCategoryQuery->execute();

            $expenseCategoryResult = $expenseCategoryQuery->fetchAll(PDO::FETCH_OBJ);
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expenseCategory/index.html.twig', array(
            'expenseCategories' => $expenseCategoryResult,
        ));
        break;
}