<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $costCentrePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
        echo $twig->render('costCentre/create.html.twig', array(
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $costCentreQuery = $costCentrePDO->prepare('SELECT * FROM cost_centre WHERE id = :id LIMIT 1');
            $costCentreQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $costCentreQuery->execute();

            $costCentreResult = $costCentreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$costCentreResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'costCentre.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'costCentre.php'
            ));
            break;
        }
        
        echo $twig->render('costCentre/update.html.twig', array(
            'costCentre' => $costCentreResult
        ));
        break; 
        
        case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $costCentreQuery = $costCentrePDO->prepare('SELECT * FROM cost_centre WHERE id = :id LIMIT 1');
            $costCentreQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $costCentreQuery->execute();

            $costCentreResult = $costCentreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$costCentreResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'costCentre.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'costCentre.php'
            ));
            break;
        }
        
        echo $twig->render('costCentre/delete.html.twig', array(
            'costCentre' => $costCentreResult
        ));
        break; 
    default:
        /**
         * input
         */
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
        $page = (!empty($page))? $page : 1;
        $itemsStart = ($page > 0)? PAGINATION * ($page - 1) : 0;
	
        try {
            $countQuery = $costCentrePDO->prepare('SELECT COUNT(*) FROM cost_centre');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $costCentreQuery = $costCentrePDO->prepare('SELECT * FROM cost_centre ORDER BY id DESC LIMIT :limit OFFSET :start');
            $costCentreQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $costCentreQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $costCentreQuery->execute();

            $costCentreResult = $costCentreQuery->fetchAll(PDO::FETCH_OBJ);
            
            $itemsCount = $countResult;
            $pagesCount = ceil($itemsCount / PAGINATION);
            $itemsEnd = $itemsStart + PAGINATION;
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('costCentre/index.html.twig', array(
            'expenseCategories' => $costCentreResult,
            'currentPage' => $page,
            'itemsCount' => $itemsCount,
            'pagesCount' => $pagesCount,
            'itemsStart' => $itemsStart,
            'itemsEnd' => $itemsEnd
        ));
        break;
}