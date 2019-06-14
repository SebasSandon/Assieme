<?php
require_once 'core/sys/init.php';
require_once 'core/sys/verification.php';

/**
 * response
 */
$response = new responseClass();

try {
    $reportPDO = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME.'', DBUSER, DBPASS);
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
    case 'begin':
        /**
         *  option 
         */
        try {
            $optionQuery = $reportPDO->prepare('SELECT * FROM period ORDER BY name DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionPeriods = $optionResult;
        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('flowReport/begin.html.twig', array(
            'option' => array(
                'periods' => $optionPeriods
            )
        ));
        break;
    
    case 'result':
        /**
         * input
         */
        $period = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_STRING);
        
        /**
         * period
         */
        try {
            $periodQuery = $reportPDO->prepare('SELECT * FROM period WHERE id = :id LIMIT 1');
            $periodQuery->bindValue(':id', $period, PDO::PARAM_STR);
            $periodQuery->execute();

            $periodResult = $periodQuery->fetch(PDO::FETCH_OBJ);
            
            $balance = $periodResult->balance;
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * dates
         */
        try {
            $datesQueryString = queryBuilder(array(
                [true, '(SELECT income_total.month, income_total.year '],
                [true, 'FROM income_total '],
                [true, 'WHERE income_total.period = :i_period '],
                [true, 'GROUP BY year, month) '],
                [true, 'UNION '],
                [true, '(SELECT expense_total.month, expense_total.year '],
                [true, 'FROM expense_total '],
                [true, 'WHERE expense_total.period = :e_period '],
                [true, 'GROUP BY year, month) '],
                [true, 'ORDER BY year ASC, month ASC ']
            ));
            
            $datesQuery = $reportPDO->prepare($datesQueryString);
            $datesQuery->bindValue(':i_period', $period, PDO::PARAM_INT);
            $datesQuery->bindValue(':e_period', $period, PDO::PARAM_INT);
            $datesQuery->execute();

            $datesResult = $datesQuery->fetchAll(PDO::FETCH_OBJ);
            
            foreach($datesResult as $date){
                $date->label = strftime('%B', mktime(0, 0, 0, $date->month, 10)).' '.$date->year;
            }
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * incomes
         */
        $incomes = array();
        $incomesTotals = array(
            'category' => array(),
            'date' => array()
        );
        
        /**
         * groups
         */
        try {
            $incomeGroupsQueryString = queryBuilder(array(
                [true, 'SELECT income_group.* '],
                [true, 'FROM income_group '],
                [true, 'ORDER BY income_group.position ASC ']
            ));
            
            $incomeGroupsQuery = $reportPDO->prepare($incomeGroupsQueryString);
            $incomeGroupsQuery->execute();

            $incomeGroupsResult = $incomeGroupsQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
          * categories
          */
        $incomeCategoriesQueryString = queryBuilder(array(
            [true, 'SELECT income_category.* '],
            [true, 'FROM income_category '],
            [true, 'WHERE income_category.parent = :group '],
            [true, 'ORDER BY income_category.position ASC ']
        ));
            
        foreach($incomeGroupsResult as $incomeGroup){
            $incomeCategoriesQuery = $reportPDO->prepare($incomeCategoriesQueryString);
            $incomeCategoriesQuery->bindValue(':group', $incomeGroup->id, PDO::PARAM_INT);
            $incomeCategoriesQuery->execute();

            $incomeCategoriesResult = $incomeCategoriesQuery->fetchAll(PDO::FETCH_OBJ);
            
            $incomeGroup->categories = $incomeCategoriesResult;
        }
         
        /**
         * incomes total
         */
        try {
            foreach($incomeGroupsResult as $incomeGroup){
                foreach($incomeGroup->categories AS $incomeCategory){
                    foreach($datesResult AS $date){
                        /**
                         * incomes
                         */
                        $incomesQueryString = queryBuilder(array(
                            [true, 'SELECT income_total.* '],
                            [true, 'FROM income_total '],
                            [true, 'WHERE income_total.category = :category '],
                            [true, 'AND income_total.month = :month '],
                            [true, 'AND income_total.year = :year '],
                            [true, 'AND income_total.period = :period '],
                            [true, 'LIMIT 1 ']
                        ));
                        
                        $incomesQuery = $reportPDO->prepare($incomesQueryString);
                        $incomesQuery->bindValue(':category', $incomeCategory->id, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':period', $period, PDO::PARAM_INT);
                        $incomesQuery->execute();

                        $incomesResult = $incomesQuery->fetch(PDO::FETCH_OBJ);
                        
                        $incomes[$incomeCategory->id][$date->year][$date->month] = $incomesResult->income_sum;
                        
                        /**
                         * totals
                         */
                        if(!array_key_exists($incomeCategory->id, $incomesTotals['category'])){
                            $incomesTotals['category'][$incomeCategory->id] = 0;
                        }
                        $incomesTotals['category'][$incomeCategory->id] += $incomesResult->income_sum;
                        
                        if(!array_key_exists($date->year, $incomesTotals['date'])){
                            $incomesTotals['date'][$date->year] = array();
                            
                            if(!array_key_exists($date->month, $incomesTotals['date'][$date->year])){
                                $incomesTotals['date'][$date->year][$date->month] = 0;
                            }
                        }
                        
                        $incomesTotals['date'][$date->year][$date->month] += $incomesResult->income_sum;
                    }
                }
            }         
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * expenses
         */
        $expenses = array();
        $expensesTotals = array(
            'category' => array(),
            'date' => array()
        );
        
        /**
         * groups
         */
        try {
            $expenseGroupsQueryString = queryBuilder(array(
                [true, 'SELECT expense_group.* '],
                [true, 'FROM expense_group '],
                [true, 'ORDER BY expense_group.position ASC ']
            ));
            
            $expenseGroupsQuery = $reportPDO->prepare($expenseGroupsQueryString);
            $expenseGroupsQuery->execute();

            $expenseGroupsResult = $expenseGroupsQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
          * categories
          */
        $expenseCategoriesQueryString = queryBuilder(array(
            [true, 'SELECT expense_category.* '],
            [true, 'FROM expense_category '],
            [true, 'WHERE expense_category.parent = :group '],
            [true, 'ORDER BY expense_category.position ASC ']
        ));
            
        foreach($expenseGroupsResult as $expenseGroup){
            $expenseCategoriesQuery = $reportPDO->prepare($expenseCategoriesQueryString);
            $expenseCategoriesQuery->bindValue(':group', $expenseGroup->id, PDO::PARAM_INT);
            $expenseCategoriesQuery->execute();

            $expenseCategoriesResult = $expenseCategoriesQuery->fetchAll(PDO::FETCH_OBJ);
            
            $expenseGroup->categories = $expenseCategoriesResult;
        }
         
        /**
         * expenses total
         */
        try {
            foreach($expenseGroupsResult as $expenseGroup){
                foreach($expenseGroup->categories AS $expenseCategory){
                    foreach($datesResult AS $date){
                        /**
                         * expenses
                         */
                        $expensesQueryString = queryBuilder(array(
                            [true, 'SELECT expense_total.* '],
                            [true, 'FROM expense_total '],
                            [true, 'WHERE expense_total.category = :category '],
                            [true, 'AND expense_total.month = :month '],
                            [true, 'AND expense_total.year = :year '],
                            [true, 'AND expense_total.period = :period '],
                            [true, 'LIMIT 1 ']
                        ));
                        
                        $expensesQuery = $reportPDO->prepare($expensesQueryString);
                        $expensesQuery->bindValue(':category', $expenseCategory->id, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':period', $period, PDO::PARAM_INT);
                        $expensesQuery->execute();

                        $expensesResult = $expensesQuery->fetch(PDO::FETCH_OBJ);
                        
                        $expenses[$expenseCategory->id][$date->year][$date->month] = $expensesResult->expense_sum;
                        
                        /**
                         * totals
                         */
                        if(!array_key_exists($expenseCategory->id, $expensesTotals['category'])){
                            $expensesTotals['category'][$expenseCategory->id] = 0;
                        }
                        $expensesTotals['category'][$expenseCategory->id] += $expensesResult->expense_sum;
                        
                        if(!array_key_exists($date->year, $expensesTotals['date'])){
                            $expensesTotals['date'][$date->year] = array();
                            
                            if(!array_key_exists($date->month, $expensesTotals['date'][$date->year])){
                                $expensesTotals['date'][$date->year][$date->month] = 0;
                            }
                        }
                        
                        $expensesTotals['date'][$date->year][$date->month] += $expensesResult->expense_sum;
                    }
                }
            }

            /**
             * balances
             */
            $balances = array(
                'initial' => array(),
                'date' => array(),
            );
            
            $aggregates = array(
                'incomes' => array(),
                'balance' => array(),
            );
            
            foreach($datesResult as $key => $date){
                if($key == 0){
                    // saldo inicial
                    $balances['initial'][$date->year][$date->month] = intval($balance);
                }else{
                    $prevDate = $datesResult[($key - 1)];
                    
                    // saldo inicial
                    $balances['initial'][$date->year][$date->month] = $aggregates['balance'][$prevDate->year][$prevDate->month];
                }
                
                // ingreso acumulado
                $aggregates['incomes'][$date->year][$date->month] = intval($incomesTotals['date'][$date->year][$date->month] + $balances['initial'][$date->year][$date->month]);
                    
                // saldo periodo
                $balances['date'][$date->year][$date->month] = intval($incomesTotals['date'][$date->year][$date->month] - $expensesTotals['date'][$date->year][$date->month]);
                    
                // saldo acumulado
                $aggregates['balance'][$date->year][$date->month] =  intval($aggregates['incomes'][$date->year][$date->month] - $expensesTotals['date'][$date->year][$date->month]);
            }
            
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('flowReport/resume.html.twig', array(
            'period' => $periodResult,
            'dates' => $datesResult,
            'incomeGroups' => $incomeGroupsResult,
            'incomes' => $incomes,
            'incomesTotals' => $incomesTotals,
            'expenseGroups' => $expenseGroupsResult,
            'expenses' => $expenses,
            'expensesTotals' => $expensesTotals,
            'balances' => $balances,
            'aggregates' => $aggregates,
        ));
        break;
    
    case 'exportation':
        /**
         * input
         */
        $period = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_STRING);
        $balance = filter_input(INPUT_POST, 'balance', FILTER_SANITIZE_STRING);
        
        /**
         * period
         */
        try {
            $periodQuery = $reportPDO->prepare('SELECT * FROM period WHERE id = :id LIMIT 1');
            $periodQuery->bindValue(':id', $period, PDO::PARAM_STR);
            $periodQuery->execute();

            $periodResult = $periodQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * dates
         */
        try {
            $datesQueryString = queryBuilder(array(
                [true, '(SELECT income_total.month, income_total.year '],
                [true, 'FROM income_total '],
                [true, 'WHERE income_total.period = :i_period '],
                [true, 'GROUP BY year, month) '],
                [true, 'UNION '],
                [true, '(SELECT expense_total.month, expense_total.year '],
                [true, 'FROM expense_total '],
                [true, 'WHERE expense_total.period = :e_period '],
                [true, 'GROUP BY year, month) '],
                [true, 'ORDER BY year ASC, month ASC ']
            ));
            
            $datesQuery = $reportPDO->prepare($datesQueryString);
            $datesQuery->bindValue(':i_period', $period, PDO::PARAM_INT);
            $datesQuery->bindValue(':e_period', $period, PDO::PARAM_INT);
            $datesQuery->execute();

            $datesResult = $datesQuery->fetchAll(PDO::FETCH_OBJ);
            
            foreach($datesResult as $date){
                $date->label = strftime('%B', mktime(0, 0, 0, $date->month, 10)).' '.$date->year;
            }
            
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * incomes
         */
        $incomes = array();
        $incomesTotals = array(
            'category' => array(),
            'date' => array()
        );
        
        /**
         * groups
         */
        try {
            $incomeGroupsQueryString = queryBuilder(array(
                [true, 'SELECT income_group.* '],
                [true, 'FROM income_group '],
                [true, 'ORDER BY income_group.position ASC ']
            ));
            
            $incomeGroupsQuery = $reportPDO->prepare($incomeGroupsQueryString);
            $incomeGroupsQuery->execute();

            $incomeGroupsResult = $incomeGroupsQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
          * categories
          */
        $incomeCategoriesQueryString = queryBuilder(array(
            [true, 'SELECT income_category.* '],
            [true, 'FROM income_category '],
            [true, 'WHERE income_category.parent = :group '],
            [true, 'ORDER BY income_category.position ASC ']
        ));
            
        foreach($incomeGroupsResult as $incomeGroup){
            $incomeCategoriesQuery = $reportPDO->prepare($incomeCategoriesQueryString);
            $incomeCategoriesQuery->bindValue(':group', $incomeGroup->id, PDO::PARAM_INT);
            $incomeCategoriesQuery->execute();

            $incomeCategoriesResult = $incomeCategoriesQuery->fetchAll(PDO::FETCH_OBJ);
            
            $incomeGroup->categories = $incomeCategoriesResult;
        }
         
        /**
         * incomes total
         */
        try {
            foreach($incomeGroupsResult as $incomeGroup){
                foreach($incomeGroup->categories AS $incomeCategory){
                    foreach($datesResult AS $date){
                        /**
                         * incomes
                         */
                        $incomesQueryString = queryBuilder(array(
                            [true, 'SELECT income_total.* '],
                            [true, 'FROM income_total '],
                            [true, 'WHERE income_total.category = :category '],
                            [true, 'AND income_total.month = :month '],
                            [true, 'AND income_total.year = :year '],
                            [true, 'AND income_total.period = :period '],
                            [true, 'LIMIT 1 ']
                        ));
                        
                        $incomesQuery = $reportPDO->prepare($incomesQueryString);
                        $incomesQuery->bindValue(':category', $incomeCategory->id, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                        $incomesQuery->bindValue(':period', $period, PDO::PARAM_INT);
                        $incomesQuery->execute();

                        $incomesResult = $incomesQuery->fetch(PDO::FETCH_OBJ);
                        
                        $incomes[$incomeCategory->id][$date->year][$date->month] = $incomesResult->income_sum;
                        
                        /**
                         * totals
                         */
                        if(!array_key_exists($incomeCategory->id, $incomesTotals['category'])){
                            $incomesTotals['category'][$incomeCategory->id] = 0;
                        }
                        $incomesTotals['category'][$incomeCategory->id] += $incomesResult->income_sum;
                        
                        if(!array_key_exists($date->year, $incomesTotals['date'])){
                            $incomesTotals['date'][$date->year] = array();
                            
                            if(!array_key_exists($date->month, $incomesTotals['date'][$date->year])){
                                $incomesTotals['date'][$date->year][$date->month] = 0;
                            }
                        }
                        
                        $incomesTotals['date'][$date->year][$date->month] += $incomesResult->income_sum;
                    }
                }
            }         
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * expenses
         */
        $expenses = array();
        $expensesTotals = array(
            'category' => array(),
            'date' => array()
        );
        
        /**
         * groups
         */
        try {
            $expenseGroupsQueryString = queryBuilder(array(
                [true, 'SELECT expense_group.* '],
                [true, 'FROM expense_group '],
                [true, 'ORDER BY expense_group.position ASC ']
            ));
            
            $expenseGroupsQuery = $reportPDO->prepare($expenseGroupsQueryString);
            $expenseGroupsQuery->execute();

            $expenseGroupsResult = $expenseGroupsQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
          * categories
          */
        $expenseCategoriesQueryString = queryBuilder(array(
            [true, 'SELECT expense_category.* '],
            [true, 'FROM expense_category '],
            [true, 'WHERE expense_category.parent = :group '],
            [true, 'ORDER BY expense_category.position ASC']
        ));
            
        foreach($expenseGroupsResult as $expenseGroup){
            $expenseCategoriesQuery = $reportPDO->prepare($expenseCategoriesQueryString);
            $expenseCategoriesQuery->bindValue(':group', $expenseGroup->id, PDO::PARAM_INT);
            $expenseCategoriesQuery->execute();

            $expenseCategoriesResult = $expenseCategoriesQuery->fetchAll(PDO::FETCH_OBJ);
            
            $expenseGroup->categories = $expenseCategoriesResult;
        }
         
        /**
         * expenses total
         */
        try {
            foreach($expenseGroupsResult as $expenseGroup){
                foreach($expenseGroup->categories AS $expenseCategory){
                    foreach($datesResult AS $date){
                        /**
                         * expenses
                         */
                        $expensesQueryString = queryBuilder(array(
                            [true, 'SELECT expense_total.* '],
                            [true, 'FROM expense_total '],
                            [true, 'WHERE expense_total.category = :category '],
                            [true, 'AND expense_total.month = :month '],
                            [true, 'AND expense_total.year = :year '],
                            [true, 'AND expense_total.period = :period '],
                            [true, 'LIMIT 1 ']
                        ));
                        
                        $expensesQuery = $reportPDO->prepare($expensesQueryString);
                        $expensesQuery->bindValue(':category', $expenseCategory->id, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                        $expensesQuery->bindValue(':period', $period, PDO::PARAM_INT);
                        $expensesQuery->execute();

                        $expensesResult = $expensesQuery->fetch(PDO::FETCH_OBJ);
                        
                        $expenses[$expenseCategory->id][$date->year][$date->month] = $expensesResult->expense_sum;
                        
                        /**
                         * totals
                         */
                        if(!array_key_exists($expenseCategory->id, $expensesTotals['category'])){
                            $expensesTotals['category'][$expenseCategory->id] = 0;
                        }
                        $expensesTotals['category'][$expenseCategory->id] += $expensesResult->expense_sum;
                        
                        if(!array_key_exists($date->year, $expensesTotals['date'])){
                            $expensesTotals['date'][$date->year] = array();
                            
                            if(!array_key_exists($date->month, $expensesTotals['date'][$date->year])){
                                $expensesTotals['date'][$date->year][$date->month] = 0;
                            }
                        }
                        
                        $expensesTotals['date'][$date->year][$date->month] += $expensesResult->expense_sum;
                    }
                }
            }         
            
            /**
             * balances
             */
            $balances = array(
                'initial' => array(),
                'date' => array(),
            );
            
            $aggregates = array(
                'incomes' => array(),
                'balance' => array(),
            );
            
            foreach($datesResult as $key => $date){
                if($key == 0){
                    // saldo inicial
                    $balances['initial'][$date->year][$date->month] = intval($balance);
                }else{
                    $prevDate = $datesResult[($key - 1)];
                    
                    // saldo inicial
                    $balances['initial'][$date->year][$date->month] = $aggregates['balance'][$prevDate->year][$prevDate->month];
                }
                
                // ingreso acumulado
                $aggregates['incomes'][$date->year][$date->month] = intval($incomesTotals['date'][$date->year][$date->month] + $balances['initial'][$date->year][$date->month]);
                    
                // saldo periodo
                $balances['date'][$date->year][$date->month] = intval($incomesTotals['date'][$date->year][$date->month] - $expensesTotals['date'][$date->year][$date->month]);
                    
                // saldo acumulado
                $aggregates['balance'][$date->year][$date->month] =  intval($aggregates['incomes'][$date->year][$date->month] - $expensesTotals['date'][$date->year][$date->month]);
            }
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * incomes details
         */
        $incomesDetails = array();
        
        foreach($incomeGroupsResult as $incomeGroup){
            foreach($incomeGroup->categories AS $incomeCategory){
                foreach($datesResult AS $date){
                    /**
                     * incomes
                     */
                    $incomesDetailsQueryString = queryBuilder(array(
                        [true, 'SELECT income.*, '],
                        [true, 'income_category.name AS category_name, '],
                        [true, 'income_group.name AS group_name, '],
                        [true, 'period.name AS period_name '],
                        [true, 'FROM income '],
                        [true, 'INNER JOIN income_category ON income_category.id = income.category '],                
                        [true, 'INNER JOIN income_group ON income_group.id = income_category.parent '],
                        [true, 'INNER JOIN period ON period.id = income.period '],
                        [true, 'WHERE income.period = :period '],
                        [true, 'AND MONTH(income.date) = :month '],
                        [true, 'AND YEAR(income.date) = :year '],
                        [true, 'AND income.category = :category '],
                        [true, 'ORDER BY income.date ASC ']
                    ));

                    $incomesDetailsQuery = $reportPDO->prepare($incomesDetailsQueryString);
                    $incomesDetailsQuery->bindValue(':period', $period, PDO::PARAM_INT);
                    $incomesDetailsQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                    $incomesDetailsQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                    $incomesDetailsQuery->bindValue(':category', $incomeCategory->id, PDO::PARAM_INT);
                    $incomesDetailsQuery->execute();

                    $incomesDetailsResult = $incomesDetailsQuery->fetchAll(PDO::FETCH_OBJ);

                    foreach($incomesDetailsResult as $incomeDetailsResult){
                        if(!isset($incomesDetails[$incomeCategory->id][$date->year][$date->month])){
                            $incomesDetails[$incomeCategory->id][$date->year][$date->month] = array();
                        }

                        array_push($incomesDetails[$incomeCategory->id][$date->year][$date->month], $incomeDetailsResult);
                    }        
                }
            }
        }
        
        /**
         * expenses details
         */
        $expensesDetails = array();
        
        foreach($expenseGroupsResult as $expenseGroup){
            foreach($expenseGroup->categories AS $expenseCategory){
                foreach($datesResult AS $date){
                    /**
                     * expenses
                     */
                    $expensesDetailsQueryString = queryBuilder(array(
                        [true, 'SELECT expense.*, '],
                        [true, 'expense_category.name AS category_name, '],
                        [true, 'expense_group.name AS group_name, '],
                        [true, 'cost_centre.name AS centre_name, '],
                        [true, 'period.name AS period_name '],
                        [true, 'FROM expense '],
                        [true, 'INNER JOIN expense_category ON expense_category.id = expense.category '],
                        [true, 'INNER JOIN expense_group ON expense_group.id = expense_category.parent '],
                        [true, 'LEFT JOIN cost_centre ON cost_centre.id = expense.centre '],
                        [true, 'INNER JOIN period ON period.id = expense.period '],
                        [true, 'WHERE expense.period = :period '],
                        [true, 'AND MONTH(expense.date) = :month '],
                        [true, 'AND YEAR(expense.date) = :year '],
                        [true, 'AND expense.category = :category '],
                        [true, 'ORDER BY expense.date ASC ']
                    ));

                    $expensesDetailsQuery = $reportPDO->prepare($expensesDetailsQueryString);
                    $expensesDetailsQuery->bindValue(':period', $period, PDO::PARAM_INT);
                    $expensesDetailsQuery->bindValue(':month', $date->month, PDO::PARAM_INT);
                    $expensesDetailsQuery->bindValue(':year', $date->year, PDO::PARAM_INT);
                    $expensesDetailsQuery->bindValue(':category', $expenseCategory->id, PDO::PARAM_INT);
                    $expensesDetailsQuery->execute();

                    $expensesDetailsResult = $expensesDetailsQuery->fetchAll(PDO::FETCH_OBJ);

                    foreach($expensesDetailsResult as $expenseDetailsResult){
                        if(!isset($expensesDetails[$expenseCategory->id][$date->year][$date->month])){
                            $expensesDetails[$expenseCategory->id][$date->year][$date->month] = array();
                        }

                        array_push($expensesDetails[$expenseCategory->id][$date->year][$date->month], $expenseDetailsResult);
                    }
                }
            }
        }
        
        /**
         * export
         */
        
        $reportPHPExcel = new \PHPExcel();
        
        // document properties
        $reportPHPExcel->getProperties()->setCreator("ceres.flujo")
            ->setLastModifiedBy("ceres.flujo")
            ->setTitle($periodResult->name);
        
        $reportWorksheet = new \PHPExcel_Worksheet($reportPHPExcel, $periodResult->name);
        $reportPHPExcel->addSheet($reportWorksheet, 0);
        
        /**
         * incomes - title
         */
        $rowNumber = 6;
        $colLetter = 'A';
        $reportWorksheet->setCellValue($colLetter.$rowNumber, '1. INGRESOS');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $rowNumber++;
        
        $colLetter = 'A';
        
        /**
         * incomes - headers
         */
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'#');
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(5);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $colLetter++;
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Glosa');
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(45);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$date->label);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
            $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
            $colLetter++;    
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Total');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
        $colLetter++;
        
        /**
         * incomes
         */
        $rowNumber++;
        
        /**
         * initial
         */
        $initialRow = $rowNumber;
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Saldo inicial');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$balances['initial'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,$balances['initial'][$datesResult[0]->year][$datesResult[0]->month]);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        /**
         * groups
         */
        $rowNumber++;
        $groupCount = 1;
        
        $firstRow = $rowNumber;
        foreach($incomeGroupsResult as $incomeGroup){
            $colLetter = 'A';
            
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$groupCount);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;

            $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeGroup->name);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
            
            $rowNumber++;
            
            /**
             * categories
             */
            foreach($incomeGroup->categories AS $incomeCategory){
                $colLetter = 'A';
            
                $reportWorksheet->setCellValue($colLetter.$rowNumber,'');
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;

                $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeCategory->name);
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;
                
                foreach($datesResult AS $date){
                    $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomes[$incomeCategory->id][$date->year][$date->month]);
                    $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $colLetter++;
                }
                
                $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomesTotals['category'][$incomeCategory->id]);
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;
                
                $rowNumber++;
            }
            
            $groupCount++;
        }
        
        /**
         * incomes - totals
         */
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'TOTAL INGRESOS MES');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber, $incomesTotals['date'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;    
        }
        
        $incomesTotalsRow = $rowNumber;
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'=SUM('.$colLetter.$firstRow.':'.$colLetter.($rowNumber - 1).')');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $rowNumber++;
        
        /**
         * incomes - aggregates
         */
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'TOTAL INGRESOS ACUMULADO');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $incomesAgreggatesRow = $rowNumber;
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$aggregates['incomes'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'='.$colLetter.$initialRow.'+'.$colLetter.($rowNumber - 1));
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $rowNumber++;

        /**
         * expenses - title
         */
        $rowNumber = $rowNumber + 1;
        $colLetter = 'A';
        $reportWorksheet->setCellValue($colLetter.$rowNumber, '2. EGRESOS');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $rowNumber++;
        
        $colLetter = 'A';
        
        /**
         * expenses - headers
         */
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'#');
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(5);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $colLetter++;
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Glosa');
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(45);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$date->label);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
            $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
            $colLetter++;    
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Total');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
        $colLetter++;
        
        /**
         * expenses
         */
        $rowNumber++;
        $firstRow = $rowNumber;
        
        $groupCount = 1;
        /**
         * groups
         */
        foreach($expenseGroupsResult as $expenseGroup){
            $colLetter = 'A';
            
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$groupCount);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;

            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseGroup->name);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
            
            $rowNumber++;
            
            /**
             * categories
             */
            foreach($expenseGroup->categories AS $expenseCategory){
                $colLetter = 'A';
            
                $reportWorksheet->setCellValue($colLetter.$rowNumber,'');
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;

                $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseCategory->name);
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;
                
                foreach($datesResult AS $date){
                    $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenses[$expenseCategory->id][$date->year][$date->month]);
                    $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $colLetter++;
                }
                
                $reportWorksheet->setCellValue($colLetter.$rowNumber,$expensesTotals['category'][$expenseCategory->id]);
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $colLetter++;
                
                $rowNumber++;
            }
            
            $groupCount++;
        }
        
        /**
         * expenses - totals
         */
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'TOTAL EGRESOS MES');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber, $expensesTotals['date'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;    
        }
        
        $expensesTotalsRow = $rowNumber;
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'=SUM('.$colLetter.$firstRow.':'.$colLetter.($rowNumber - 1).')');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $rowNumber++;
        
        /**
         * balance - date
         */
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'SALDO DEL PERIODO');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$balances['date'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'='.$colLetter.$incomesTotalsRow.'-'.$colLetter.$expensesTotalsRow);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $rowNumber++;
        
        /**
         * aggregates - balance
         */
        $colLetter = 'B';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'SALDO ACUMULADO');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$aggregates['balance'][$date->year][$date->month]);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
        }
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'='.$colLetter.$incomesAgreggatesRow.'-'.$colLetter.$expensesTotalsRow);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $colLetter++;
        
        $rowNumber++;
        
        /**
         * titles
         */
        $reportWorksheet->setCellValue('A5','FLUJO DE CAJA '.strtoupper($periodResult->name));
        $reportWorksheet->getStyle('A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $reportWorksheet->getStyle('A5')->getFont()->setSize(16);
        $reportWorksheet->getStyle('A5')->getFont()->setBold(true);
        $reportWorksheet->mergeCells('A5:'.$colLetter.'5');
        
        /**
         * details sheet
         */
        $reportWorksheet = new \PHPExcel_Worksheet($reportPHPExcel, 'Detalle');
        $reportPHPExcel->addSheet($reportWorksheet, 1);
        
        /**
         * details - incomes - title
         */
        $rowNumber = 2;
        $colLetter = 'A';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber, 'INGRESOS');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $rowNumber++;
        
        /**
         * incomes - headers
         */
        foreach($incomeGroupsResult as $incomeGroup){
            foreach($incomeGroup->categories AS $incomeCategory){
                foreach($datesResult AS $date){
                    if(count($incomesDetails[$incomeCategory->id][$date->year][$date->month]) > 0){
                        $colLetter = 'A';

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Fecha');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(15);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Grupo');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(25);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Categoria');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(25);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Glosa');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(35);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Importe');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $rowNumber++;

                        $dateTotal = 0;

                        foreach($incomesDetails[$incomeCategory->id][$date->year][$date->month] as $incomeDetails){
                            $colLetter = 'A';

                            $reportWorksheet->setCellValue($colLetter.$rowNumber, date('d-m-Y', strtotime($incomeDetails->date)));
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeDetails->group_name);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeDetails->category_name);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeDetails->comment);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$incomeDetails->amount);
                            $colLetter++;

                            $dateTotal = $dateTotal + $incomeDetails->amount;

                            $rowNumber++; 
                        }

                        $colLetter = 'A';

                        $colLetter++;
                        $colLetter++;
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Total '.$date->label);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,$dateTotal);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $rowNumber++;
                        $rowNumber++;
                    } 
                }
            }
        }
        
        /**
         * details - expenses - title
         */
        $rowNumber++;
        $rowNumber++;
        $colLetter = 'A';
        
        $reportWorksheet->setCellValue($colLetter.$rowNumber, 'EGRESOS');
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setSize(14);
        $rowNumber++;
        
        /**
         * expenses - headers
         */
        foreach($expenseGroupsResult as $expenseGroup){
            foreach($expenseGroup->categories AS $expenseCategory){
                foreach($datesResult AS $date){
                    if(count($expensesDetails[$expenseCategory->id][$date->year][$date->month]) > 0){
                        $colLetter = 'A';

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Fecha');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(15);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Grupo');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(25);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Categoria');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(25);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Glosa');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(35);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Importe');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(20);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Centro de Costo');
                        $reportWorksheet->getColumnDimension($colLetter)->setWidth(25);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $rowNumber++;

                        $dateTotal = 0;

                        foreach($expensesDetails[$expenseCategory->id][$date->year][$date->month] as $expenseDetails){
                            $colLetter = 'A';

                            $reportWorksheet->setCellValue($colLetter.$rowNumber, date('d-m-Y', strtotime($expenseDetails->date)));
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseDetails->group_name);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseDetails->category_name);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseDetails->comment);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseDetails->amount);
                            $colLetter++;

                            $reportWorksheet->setCellValue($colLetter.$rowNumber,$expenseDetails->centre_name);
                            $colLetter++;

                            $dateTotal = $dateTotal + $expenseDetails->amount;

                            $rowNumber++; 
                        }

                        $colLetter = 'A';

                        $colLetter++;
                        $colLetter++;
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Total '.$date->label);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $reportWorksheet->setCellValue($colLetter.$rowNumber,$dateTotal);
                        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
                        $colLetter++;

                        $rowNumber++;
                        $rowNumber++;
                    }
                }
            }
        }
        
        /**
         * export file
         */
        $reportPHPExcel->setActiveSheetIndex(0);

        ob_end_clean();
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="flow-report.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        
        $objWriter = PHPExcel_IOFactory::createWriter($reportPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        break;
    
    default:
        echo $twig->render('redirector/index.html.twig', array(
            'redirect' => 'home.php'
        ));
        break;
}