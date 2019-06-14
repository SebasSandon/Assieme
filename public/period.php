<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $periodPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
        echo $twig->render('period/create.html.twig', array(
        ));
        break;
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $periodQuery = $periodPDO->prepare('SELECT * FROM period WHERE id = :id LIMIT 1');
            $periodQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $periodQuery->execute();

            $periodResult = $periodQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$periodResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'period.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'period.php'
            ));
            break;
        }
        
        /**
         *  splits
         */
        try {
            $splitsQueryString = queryBuilder(array(
                [true, 'SELECT period_split.* '],
                [true, 'FROM period_split '],
                [true, 'WHERE period = :id '],
                [true, 'ORDER BY position ASC ']
            ));
            
            $splitsQuery = $periodPDO->prepare($splitsQueryString);
            $splitsQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $splitsQuery->execute();
            
            $splitsResult = $splitsQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        $periodResult->splits = $splitsResult;
        
        echo $twig->render('period/update.html.twig', array(
            'period' => $periodResult,
        ));
        break; 
        
        case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $periodQuery = $periodPDO->prepare('SELECT * FROM period WHERE id = :id LIMIT 1');
            $periodQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $periodQuery->execute();

            $periodResult = $periodQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$periodResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'period.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'period.php'
            ));
            break;
        }
        
        echo $twig->render('period/delete.html.twig', array(
            'period' => $periodResult
        ));
        break; 
    default:
        /**
         * input
         */
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
        $page = (!empty($page))? $page : 1;
        $itemsStart = ($page > 0)? PAGINATION * ($page - 1) : 0;
        
        /**
         * filter
         */
	
        try {
            $countQueryString = queryBuilder(array(
                [true, 'SELECT COUNT(*) '],
                [true, 'FROM period ']
            ));
            $countQuery = $periodPDO->prepare($countQueryString);
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $periodQueryString = queryBuilder(array(
                [true, 'SELECT period.* '],
                [true, 'FROM period '],
                [true, 'ORDER BY id ASC '],
                [true, 'LIMIT :limit OFFSET :start '],
            ));
            
            $periodQuery = $periodPDO->prepare($periodQueryString);
            $periodQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $periodQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $periodQuery->execute();

            $periodResult = $periodQuery->fetchAll(PDO::FETCH_OBJ);
            
            $itemsCount = $countResult;
            $pagesCount = ceil($itemsCount / PAGINATION);
            $itemsEnd = $itemsStart + PAGINATION;
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('period/index.html.twig', array(
            'periods' => $periodResult,
            'currentPage' => $page,
            'itemsCount' => $itemsCount,
            'pagesCount' => $pagesCount,
            'itemsStart' => $itemsStart,
            'itemsEnd' => $itemsEnd,
        ));
        break;
}