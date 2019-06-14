<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $profitCentrePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
        echo $twig->render('profitCentre/create.html.twig', array(
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $profitCentreQuery = $profitCentrePDO->prepare('SELECT * FROM profit_centre WHERE id = :id LIMIT 1');
            $profitCentreQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $profitCentreQuery->execute();

            $profitCentreResult = $profitCentreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$profitCentreResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'profitCentre.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'profitCentre.php'
            ));
            break;
        }
        
        echo $twig->render('profitCentre/update.html.twig', array(
            'profitCentre' => $profitCentreResult
        ));
        break; 
        
        case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $profitCentreQuery = $profitCentrePDO->prepare('SELECT * FROM profit_centre WHERE id = :id LIMIT 1');
            $profitCentreQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $profitCentreQuery->execute();

            $profitCentreResult = $profitCentreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$profitCentreResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'profitCentre.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'profitCentre.php'
            ));
            break;
        }
        
        echo $twig->render('profitCentre/delete.html.twig', array(
            'profitCentre' => $profitCentreResult
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
            $countQuery = $profitCentrePDO->prepare('SELECT COUNT(*) FROM profit_centre');
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $profitCentreQuery = $profitCentrePDO->prepare('SELECT * FROM profit_centre ORDER BY id DESC LIMIT :limit OFFSET :start');
            $profitCentreQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $profitCentreQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $profitCentreQuery->execute();

            $profitCentreResult = $profitCentreQuery->fetchAll(PDO::FETCH_OBJ);
            
            $itemsCount = $countResult;
            $pagesCount = ceil($itemsCount / PAGINATION);
            $itemsEnd = $itemsStart + PAGINATION;
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('profitCentre/index.html.twig', array(
            'expenseCategories' => $profitCentreResult,
            'currentPage' => $page,
            'itemsCount' => $itemsCount,
            'pagesCount' => $pagesCount,
            'itemsStart' => $itemsStart,
            'itemsEnd' => $itemsEnd
        ));
        break;
}