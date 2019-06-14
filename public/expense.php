<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $expensePDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
            $optionQuery = $expensePDO->prepare('SELECT * FROM period ORDER BY id DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionPeriods = $optionResult;
            
            $optionQuery = $expensePDO->prepare('SELECT * FROM expense_category ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCategories = $optionResult;
            
            $optionQuery = $expensePDO->prepare('SELECT * FROM cost_centre ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCentres = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expense/create.html.twig', array(
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
            $expenseQuery = $expensePDO->prepare('SELECT * FROM expense WHERE id = :id LIMIT 1');
            $expenseQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseQuery->execute();

            $expenseResult = $expenseQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expense.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expense.php'
            ));
            break;
        }
        
        /**
         *  option 
         */
        try {
            $optionQuery = $expensePDO->prepare('SELECT * FROM period ORDER BY id DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionPeriods = $optionResult;
            
            $optionQuery = $expensePDO->prepare('SELECT * FROM expense_category ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCategories = $optionResult;
            
            $optionQuery = $expensePDO->prepare('SELECT * FROM cost_centre ORDER BY name ASC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCentres = $optionResult;

        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('expense/update.html.twig', array(
            'expense' => $expenseResult,
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
            $expenseQuery = $expensePDO->prepare('SELECT * FROM expense WHERE id = :id LIMIT 1');
            $expenseQuery->bindValue(':id', $id, PDO::PARAM_INT);
            $expenseQuery->execute();

            $expenseResult = $expenseQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        if(!$expenseResult){
            $response->setResponse(false,'DATABASE_NOT_FOUND_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expense.php'
            ));
            break;
        }
        
        if( $_SESSION[SYSSESSION.'_'.SYSMODULE.'_level'] === 'SIMPLE' ){
            
            $response->setResponse(false,'LOGIN_DENIED_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
            
            echo $twig->render('redirector/index.html.twig', array(
                'redirect' => 'expense.php'
            ));
            break;
        }
        
        echo $twig->render('expense/delete.html.twig', array(
            'expense' => $expenseResult
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
                [true, 'FROM expense '],
                [true, 'INNER JOIN expense_category ON expense_category.id = expense.category '],
                [true, 'LEFT JOIN cost_centre ON cost_centre.id = expense.centre '],
                [true, 'INNER JOIN period ON period.id = expense.period '],
                [$period || $category || $centre || $search || $amount || ($from && $to), 'WHERE '],
                [$period, 'period.alias = :period '],
                [$period && ($category || $centre || $search || $amount || ($from && $to)), 'AND '],
                [$category, 'expense_category.alias = :category '],
                [$category && ($centre || $search || $amount || ($from && $to)), 'AND '],
                [$centre, 'cost_centre.alias = :centre '],
                [$centre && ($search || $amount || ($from && $to)), 'AND '],
                [$search, 'expense.comment LIKE :search '],
                [$search && ($amount || ($from && $to)), 'AND '],
                [$amount, 'expense.amount = :amount '],
                [$amount && ($from && $to), 'AND '],
                [($from && $to), ' (expense.date BETWEEN :from AND :to) ']
            ));
            
            $countQuery = $expensePDO->prepare($countQueryString);
            if($period){$countQuery->bindValue(':period', $period, PDO::PARAM_STR);}
            if($category){$countQuery->bindValue(':category', $category, PDO::PARAM_STR);}
            if($centre){$countQuery->bindValue(':centre', $centre, PDO::PARAM_STR);}
            if($search){$countQuery->bindValue(':search', '%'.$search.'%', PDO::PARAM_STR);}
            if($amount){$countQuery->bindValue(':amount', $amount, PDO::PARAM_STR);}
            if($from && $to){$countQuery->bindValue(':from', $from, PDO::PARAM_STR);}
            if($from && $to){$countQuery->bindValue(':to', $to, PDO::PARAM_STR);}
            $countQuery->execute();
            $countResult = $countQuery->fetch(PDO::FETCH_COLUMN);
            
            $expensesQueryString = queryBuilder(array(
                [true, 'SELECT expense.*, '],
                [true, 'expense_category.name AS category_name, '],
                [true, 'cost_centre.name AS centre_name, '],
                [true, 'period.name AS period_name '],
                [true, 'FROM expense '],
                [true, 'INNER JOIN expense_category ON expense_category.id = expense.category '],
                [true, 'LEFT JOIN cost_centre ON cost_centre.id = expense.centre '],
                [true, 'INNER JOIN period ON period.id = expense.period '],
                [$period || $category || $centre || $search || $amount || ($from && $to), 'WHERE '],
                [$period, 'period.alias = :period '],
                [$period && ($category || $centre || $search || $amount || ($from && $to)), 'AND '],
                [$category, 'expense_category.alias = :category '],
                [$category && ($centre || $search || $amount || ($from && $to)), 'AND '],
                [$centre, 'cost_centre.alias = :centre '],
                [$centre && ($search || $amount || ($from && $to)), 'AND '],
                [$search, 'expense.comment LIKE :search '],
                [$search && ($amount || ($from && $to)), 'AND '],
                [$amount, 'expense.amount = :amount '],
                [$amount && ($from && $to), 'AND '],
                [($from && $to), ' (expense.date BETWEEN :from AND :to) '],
                [true, 'ORDER BY expense.date DESC '],
                [true, 'LIMIT :limit OFFSET :start ']
            ));
            
            $expensesQuery = $expensePDO->prepare($expensesQueryString);
            if($period){$expensesQuery->bindValue(':period', $period, PDO::PARAM_STR);}
            if($category){$expensesQuery->bindValue(':category', $category, PDO::PARAM_STR);}
            if($centre){$expensesQuery->bindValue(':centre', $centre, PDO::PARAM_STR);}
            if($search){$expensesQuery->bindValue(':search', '%'.$search.'%', PDO::PARAM_STR);}
            if($amount){$expensesQuery->bindValue(':amount', $amount, PDO::PARAM_STR);}
            if($from && $to){$expensesQuery->bindValue(':from', $from, PDO::PARAM_STR);}
            if($from && $to){$expensesQuery->bindValue(':to', $to, PDO::PARAM_STR);}
            $expensesQuery->bindValue(':limit', PAGINATION, PDO::PARAM_INT);
            $expensesQuery->bindValue(':start', $itemsStart, PDO::PARAM_INT);
            $expensesQuery->execute();

            $expensesResult = $expensesQuery->fetchAll(PDO::FETCH_OBJ);
            
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
            $filterQuery = $expensePDO->prepare('SELECT * FROM period ORDER BY name DESC');
            $filterQuery->execute();
            $filterResult = $filterQuery->fetchAll(PDO::FETCH_OBJ);
            $filterPeriods = $filterResult;
            
            $filterQuery = $expensePDO->prepare('SELECT * FROM expense_category ORDER BY name ASC');
            $filterQuery->execute();
            $filterResult = $filterQuery->fetchAll(PDO::FETCH_OBJ);
            $filterCategories = $filterResult;
            
            $filterQuery = $expensePDO->prepare('SELECT * FROM cost_centre ORDER BY name ASC');
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
        
        echo $twig->render('expense/index.html.twig', array(
            'expenses' => $expensesResult,
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