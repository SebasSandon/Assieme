<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $incomePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
            $optionQuery = $incomePDO->prepare('SELECT * FROM period ORDER BY id DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionPeriods = $optionResult;
            
            $optionQuery = $incomePDO->prepare('SELECT * FROM income_category ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCategories = $optionResult;
            
            $optionQuery = $incomePDO->prepare('SELECT * FROM profit_centre ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCentres = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('income/create.html.twig', array(
            'option' => array(
                'periods' => $optionPeriods,
                'categories' => $optionCategories,
                'centres' => $optionCentres,
            )
        ));
        break;
    
    case 'update':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeQuery = $incomePDO->prepare('SELECT * FROM income WHERE id = :id LIMIT 1');
            $incomeQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeQuery->execute();

            $incomeResult = $incomeQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'income.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'income.php'
            ));
            break;
        }
        
        /**
         *  option 
         */
        try {
            $optionQuery = $incomePDO->prepare('SELECT * FROM period ORDER BY id DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionPeriods = $optionResult;
            
            $optionQuery = $incomePDO->prepare('SELECT * FROM income_category ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCategories = $optionResult;
            
            $optionQuery = $incomePDO->prepare('SELECT * FROM profit_centre ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCentres = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('income/update.html.twig', array(
            'income' => $incomeResult,
            'option' => array(
                'periods' => $optionPeriods,
                'categories' => $optionCategories,
                'centres' => $optionCentres,
            )
        ));
        break; 
        
    case 'delete':
        /**
         * input
         */
        $id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
        
        try {
            $incomeQuery = $incomePDO->prepare('SELECT * FROM income WHERE id = :id LIMIT 1');
            $incomeQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $incomeQuery->execute();

            $incomeResult = $incomeQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$incomeResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'income.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'income.php'
            ));
            break;
        }
        
        echo $twig->render('income/delete.html.twig', array(
            'income' => $incomeResult
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
        $period = filter_input(INPUT_GET, 'period', FILTER_SANITIZE_STRING);
        $category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
        $centre = filter_input(INPUT_GET, 'centre', FILTER_SANITIZE_STRING);
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
        $amount = filter_input(INPUT_GET, 'amount', FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_GET, 'from', FILTER_SANITIZE_STRING);
        $to = filter_input(INPUT_GET, 'to', FILTER_SANITIZE_STRING);
        
        if($from){$from = date("Y-m-d", strtotime($from));}
        if($to){$to = date("Y-m-d", strtotime($to));}
	
        try {
            $countQueryString = queryBuilder(array(
                [true, 'SELECT COUNT(*) '],
                [true, 'FROM income '],
                [true, 'INNER JOIN income_category ON income_category.id = income.category '],
                [true, 'LEFT JOIN profit_centre ON profit_centre.id = income.centre '],
                [true, 'INNER JOIN period ON period.id = income.period '],
                [$period || $category || $centre || $search || $amount || ($from && $to), 'WHERE '],
                [$period, 'period.alias = :period '],
                [$period && ($category || $centre || $search || $amount || ($from && $to)), 'AND '],
                [$category, 'income_category.alias = :category '],
                [$category && ($centre || $search || $amount || ($from && $to)), 'AND '],
                [$centre, 'profit_centre.alias = :centre '],
                [$centre && ($search || $amount || ($from && $to)), 'AND '],
                [$search, 'income.comment LIKE :search '],
                [$search && ($amount || ($from && $to)), 'AND '],
                [$amount, 'income.amount = :amount '],
                [$amount && ($from && $to), 'AND '],
                [($from && $to), ' (income.date BETWEEN :from AND :to) ']
            ));
            
            $countQuery = $incomePDO->prepare($countQueryString);
            if($period){$countQuery->bindValue(':period', $period, PDO::PARAM_STR);}
            if($category){$countQuery->bindValue(':category', $category, PDO::PARAM_STR);}
            if($centre){$countQuery->bindValue(':centre', $centre, PDO::PARAM_STR);}
            if($search){$countQuery->bindValue(':search', '%'.$search.'%', PDO::PARAM_STR);}
            if($amount){$countQuery->bindValue(':amount', $amount, PDO::PARAM_STR);}
            if($date){$countQuery->bindValue(':date', $date, PDO::PARAM_STR);}
            if($from && $to){$countQuery->bindValue(':from', $from, PDO::PARAM_STR);}
            if($from && $to){$countQuery->bindValue(':to', $to, PDO::PARAM_STR);}
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $incomesQueryString = queryBuilder(array(
                [true, 'SELECT income.*, '],
                [true, 'income_category.name AS category_name, '],
                [true, 'profit_centre.name AS centre_name, '],
                [true, 'period.name AS period_name '],
                [true, 'FROM income '],
                [true, 'INNER JOIN income_category ON income_category.id = income.category '],
                [true, 'LEFT JOIN profit_centre ON profit_centre.id = income.centre '],
                [true, 'INNER JOIN period ON period.id = income.period '],
                [$period || $category || $centre || $search || $amount || ($from && $to), 'WHERE '],
                [$period, 'period.alias = :period '],
                [$period && ($category || $centre || $search || $amount || ($from && $to)), 'AND '],
                [$category, 'income_category.alias = :category '],
                [$category && ($centre || $search || $amount || ($from && $to)), 'AND '],
                [$centre, 'profit_centre.alias = :centre '],
                [$centre && ($search || $amount || ($from && $to)), 'AND '],
                [$search, 'income.comment LIKE :search '],
                [$search && ($amount || ($from && $to)), 'AND '],
                [$amount, 'income.amount = :amount '],
                [$amount && ($from && $to), 'AND '],
                [($from && $to), ' (income.date BETWEEN :from AND :to) '],
                [true, 'ORDER BY income.date DESC '],
                [true, 'LIMIT :limit OFFSET :start ']
            ));
            
            $incomesQuery = $incomePDO->prepare($incomesQueryString);
            if($period){$incomesQuery->bindValue(':period', $period, PDO::PARAM_STR);}
            if($category){$incomesQuery->bindValue(':category', $category, PDO::PARAM_STR);}
            if($centre){$incomesQuery->bindValue(':centre', $centre, PDO::PARAM_STR);}
            if($search){$incomesQuery->bindValue(':search', '%'.$search.'%', PDO::PARAM_STR);}
            if($amount){$incomesQuery->bindValue(':amount', $amount, PDO::PARAM_STR);}
            if($from && $to){$incomesQuery->bindValue(':from', $from, PDO::PARAM_STR);}
            if($from && $to){$incomesQuery->bindValue(':to', $to, PDO::PARAM_STR);}
            $incomesQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $incomesQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $incomesQuery->execute();

            $incomesResult = $incomesQuery->fetchAll(PDO::FETCH_OBJ);
            
            $itemsCount = $countResult;
            $pagesCount = ceil($itemsCount / PAGINATION);
            $itemsEnd = $itemsStart + PAGINATION;
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         *  filter 
         */
        try {
            $filterQuery = $incomePDO->prepare('SELECT * FROM period ORDER BY name DESC');
            $filterQuery->execute();
            $filterResult = $filterQuery->fetchAll(PDO::FETCH_OBJ);
            $filterPeriods = $filterResult;
            
            $filterQuery = $incomePDO->prepare('SELECT * FROM income_category ORDER BY name ASC');
            $filterQuery->execute();
            $filterResult = $filterQuery->fetchAll(PDO::FETCH_OBJ);
            $filterCategories = $filterResult;
            
            $filterQuery = $incomePDO->prepare('SELECT * FROM profit_centre ORDER BY name ASC');
            $filterQuery->execute();
            $filterResult = $filterQuery->fetchAll(PDO::FETCH_OBJ);
            $filterCentres = $filterResult;
        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if($from){$from = date("d-m-Y", strtotime($from));}
        if($to){$to = date("d-m-Y", strtotime($to));}
        
        echo $twig->render('income/index.html.twig', array(
            'incomes' => $incomesResult,
            'currentPage' => $page,
            'itemsCount' => $itemsCount,
            'pagesCount' => $pagesCount,
            'itemsStart' => $itemsStart,
            'itemsEnd' => $itemsEnd,
            'filter' => array(
                'search' => $search,
                'amount' => $amount,
                'from' => $from,
                'to' => $to,
                'periods' => $filterPeriods,
                'period' => $period,
                'categories' => $filterCategories,
                'category' => $category,
                'centres' => $filterCentres,
                'centre' => $centre
            )
        ));
        break;
}