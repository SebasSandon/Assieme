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
            
            $optionQuery = $reportPDO->prepare('SELECT * FROM profit_centre ORDER BY name DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionProfitCentres = $optionResult;
            
            $optionQuery = $reportPDO->prepare('SELECT * FROM cost_centre ORDER BY name DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionCostCentres = $optionResult;
            
            $optionQuery = $reportPDO->prepare('SELECT * FROM income_category ORDER BY name DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionIncomeCategories = $optionResult;
            
            $optionQuery = $reportPDO->prepare('SELECT * FROM expense_category ORDER BY name DESC');
            $optionQuery->execute();
            $optionResult = $optionQuery->fetchAll(PDO::FETCH_OBJ);
            $optionExpenseCategories = $optionResult;
        } catch (PDOException $ex) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('dailyReport/begin.html.twig', array(
            'option' => array(
                'periods' => $optionPeriods,
                'profitCentres' => $optionProfitCentres,
                'costCentres' => $optionCostCentres,
                'incomeCategories' => $optionIncomeCategories,
                'expenseCategories' => $optionExpenseCategories
            )
        ));
        break;
    
    case 'result':
        /**
         * input
         */
        $movement = filter_input(INPUT_POST, 'movement', FILTER_SANITIZE_STRING);
        $period = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_POST, 'from', FILTER_SANITIZE_STRING);
        $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_STRING);
        $centre = filter_input(INPUT_POST, 'centre', FILTER_SANITIZE_STRING);
        $categories = filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        
        if($from){$from = date("Y-m-d", strtotime($from));}
        if($to){$to = date("Y-m-d", strtotime($to));}
        
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
        $begin = new DateTime($from);
        $end = new DateTime($to);
        $end->setTime(0,0,1);

        $interval = DateInterval::createFromDateString('1 day');
        $datesResult = new DatePeriod($begin, $interval, $end);

        /**
         * centre
         */
        if($centre){
        try {
            if($movement == 'income'){
                $centreQueryString = queryBuilder(array(
                    [true, 'SELECT profit_centre.* '],
                    [true, 'FROM profit_centre '],
                    [$centre, 'WHERE profit_centre.id = :centre '],
                    [true, 'LIMIT 1 ']
                ));
            }
            
            if($movement == 'expense'){
                $centreQueryString = queryBuilder(array(
                    [true, 'SELECT cost_centre.* '],
                    [true, 'FROM cost_centre '],
                    [$centre, 'WHERE cost_centre.id = :centre '],
                    [true, 'LIMIT 1 ']
                ));
            }
            
            $centreQuery = $reportPDO->prepare($centreQueryString);
            $centreQuery->bindValue(':centre', $centre, PDO::PARAM_INT);
            $centreQuery->execute();

            $centreResult = $centreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        }
        
        /**
          * categories
          */
        try {
            if($movement == 'income'){
                $categoriesQueryString = queryBuilder(array(
                    [true, 'SELECT income_category.* '],
                    [true, 'FROM income_category '],
                    [true, 'WHERE income_category.id IN ('.implode(',', $categories).') '],
                    [true, 'ORDER BY income_category.position ASC ']
                ));
            }
            
            if($movement == 'expense'){
                $categoriesQueryString = queryBuilder(array(
                    [true, 'SELECT expense_category.* '],
                    [true, 'FROM expense_category '],
                    [true, 'WHERE expense_category.id IN ('.implode(',', $categories).') '],
                    [true, 'ORDER BY expense_category.position ASC ']
                ));
            }
            
            $categoriesQuery = $reportPDO->prepare($categoriesQueryString);
            $categoriesQuery->execute();

            $categoriesResult = $categoriesQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * totals
         */
        $totals = array();
        $categoriesSum = array();
        $datesSum = array();
        
        /**
         * totals
         */
        try {
            foreach($categoriesResult AS $category){
                foreach ($datesResult as $date) {
                    if($movement == 'income'){
                        $totalQueryString = queryBuilder(array(
                            [true, 'SELECT SUM(income.amount) AS total '],
                            [true, 'FROM income '],
                            [true, 'WHERE income.period = :period '],
                            [true, 'AND income.date = :date '],
                            [true, 'AND income.category = :category '],
                            [$centre, 'AND income.centre = :centre ']
                        ));
                    }

                    if($movement == 'expense'){
                        $totalQueryString = queryBuilder(array(
                            [true, 'SELECT SUM(expense.amount) AS total '],
                            [true, 'FROM expense '],
                            [true, 'WHERE expense.period = :period '],
                            [true, 'AND expense.date = :date '],
                            [true, 'AND expense.category = :category '],
                            [$centre, 'AND expense.centre = :centre ']
                        ));
                    }
                    
                    $totalQuery = $reportPDO->prepare($totalQueryString);
                    $totalQuery->bindValue(':period', $period, PDO::PARAM_INT);
                    $totalQuery->bindValue(':date', $date->format('Y-m-d'), PDO::PARAM_STR);
                    $totalQuery->bindValue(':category', $category->id, PDO::PARAM_INT);
                    if($centre){$totalQuery->bindValue(':centre', $centre, PDO::PARAM_INT);}
                    $totalQuery->execute();

                    $totalResult = $totalQuery->fetch(PDO::FETCH_OBJ);
                    
                    $totals[$category->alias][$date->format('Y-m-d')] = (($totalResult && $totalResult->total)?$totalResult->total:0);
                    
                    $categoriesSum[$category->alias] += $totals[$category->alias][$date->format('Y-m-d')];
                    $datesSum[$date->format('Y-m-d')] += $totals[$category->alias][$date->format('Y-m-d')];               
                }
            }

        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        echo $twig->render('dailyReport/resume.html.twig', array(
            'period' => $periodResult,
            'dates' => $datesResult,
            'centre' => $centreResult,
            'categories' => $categoriesResult,
            'totals' => $totals,
            'categoriesSum' => $categoriesSum,
            'datesSum' => $datesSum,
            'input' => array(
                'movement' => $movement,
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'centre' => $centre,
                'categories' => $categories
            )
        ));
        break;
    
    case 'exportation':
        /**
         * input
         */
        $movement = filter_input(INPUT_POST, 'movement', FILTER_SANITIZE_STRING);
        $period = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_STRING);
        $from = filter_input(INPUT_POST, 'from', FILTER_SANITIZE_STRING);
        $to = filter_input(INPUT_POST, 'to', FILTER_SANITIZE_STRING);
        $centre = filter_input(INPUT_POST, 'centre', FILTER_SANITIZE_STRING);
        $categories = filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        
        if($from){$from = date("Y-m-d", strtotime($from));}
        if($to){$to = date("Y-m-d", strtotime($to));}
        
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
        $begin = new DateTime($from);
        $end = new DateTime($to);
        $end->setTime(0,0,1);

        $interval = DateInterval::createFromDateString('1 day');
        $datesResult = new DatePeriod($begin, $interval, $end);

        /**
         * centre
         */
        if($centre){
        try {
            if($movement == 'income'){
                $centreQueryString = queryBuilder(array(
                    [true, 'SELECT profit_centre.* '],
                    [true, 'FROM profit_centre '],
                    [$centre, 'WHERE profit_centre.id = :centre '],
                    [true, 'LIMIT 1 ']
                ));
            }
            
            if($movement == 'expense'){
                $centreQueryString = queryBuilder(array(
                    [true, 'SELECT cost_centre.* '],
                    [true, 'FROM cost_centre '],
                    [$centre, 'WHERE cost_centre.id = :centre '],
                    [true, 'LIMIT 1 ']
                ));
            }
            
            $centreQuery = $reportPDO->prepare($centreQueryString);
            $centreQuery->bindValue(':centre', $centre, PDO::PARAM_INT);
            $centreQuery->execute();

            $centreResult = $centreQuery->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        }
        
        /**
          * categories
          */
        try {
            if($movement == 'income'){
                $categoriesQueryString = queryBuilder(array(
                    [true, 'SELECT income_category.* '],
                    [true, 'FROM income_category '],
                    [true, 'WHERE income_category.id IN ('.implode(',', $categories).') '],
                    [true, 'ORDER BY income_category.position ASC ']
                ));
            }
            
            if($movement == 'expense'){
                $categoriesQueryString = queryBuilder(array(
                    [true, 'SELECT expense_category.* '],
                    [true, 'FROM expense_category '],
                    [true, 'WHERE expense_category.id IN ('.implode(',', $categories).') '],
                    [true, 'ORDER BY expense_category.position ASC ']
                ));
            }
            
            $categoriesQuery = $reportPDO->prepare($categoriesQueryString);
            $categoriesQuery->execute();

            $categoriesResult = $categoriesQuery->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * totals
         */
        $totals = array();
        $categoriesSum = array();
        $datesSum = array();
        
        /**
         * totals
         */
        try {
            foreach($categoriesResult AS $category){
                foreach ($datesResult as $date) {
                    if($movement == 'income'){
                        $totalQueryString = queryBuilder(array(
                            [true, 'SELECT SUM(income.amount) AS total '],
                            [true, 'FROM income '],
                            [true, 'WHERE income.period = :period '],
                            [true, 'AND income.date = :date '],
                            [true, 'AND income.category = :category '],
                            [$centre, 'AND income.centre = :centre ']
                        ));
                    }

                    if($movement == 'expense'){
                        $totalQueryString = queryBuilder(array(
                            [true, 'SELECT SUM(expense.amount) AS total '],
                            [true, 'FROM expense '],
                            [true, 'WHERE expense.period = :period '],
                            [true, 'AND expense.date = :date '],
                            [true, 'AND expense.category = :category '],
                            [$centre, 'AND expense.centre = :centre ']
                        ));
                    }
                    
                    $totalQuery = $reportPDO->prepare($totalQueryString);
                    $totalQuery->bindValue(':period', $period, PDO::PARAM_INT);
                    $totalQuery->bindValue(':date', $date->format('Y-m-d'), PDO::PARAM_STR);
                    $totalQuery->bindValue(':category', $category->id, PDO::PARAM_INT);
                    if($centre){$totalQuery->bindValue(':centre', $centre, PDO::PARAM_INT);}
                    $totalQuery->execute();

                    $totalResult = $totalQuery->fetch(PDO::FETCH_OBJ);
                    
                    $totals[$category->alias][$date->format('Y-m-d')] = (($totalResult && $totalResult->total)?$totalResult->total:0);
                    
                    $categoriesSum[$category->alias] += $totals[$category->alias][$date->format('Y-m-d')];
                    $datesSum[$date->format('Y-m-d')] += $totals[$category->alias][$date->format('Y-m-d')];               
                }
            }

        } catch (PDOException $e) {
            $response->setResponse(false,'DATABASE_QUERY_ERROR');
            $notification = new notificationClass('danger',$response->sysMessage);
            array_push($_SESSION[SYSSESSION.'_'.SYSMODULE.'_notifications'], $notification);
        }
        
        /**
         * export
         */
        
        $reportPHPExcel = new \PHPExcel();
        
        // document properties
        $reportPHPExcel->getProperties()->setCreator("ceres.caja")
            ->setLastModifiedBy("ceres.caja")
            ->setTitle($periodResult->name);
        
        $reportWorksheet = new \PHPExcel_Worksheet($reportPHPExcel, $periodResult->name);
        $reportPHPExcel->addSheet($reportWorksheet, 0);
        
        /**
         * begin
         */
        $rowNumber = 6;
        $colLetter = 'A';
        
        /**
         * dates
         */
        $reportWorksheet->setCellValue($colLetter.$rowNumber,'Categoría');
        $reportWorksheet->getColumnDimension($colLetter)->setWidth(45);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        $reportWorksheet->getStyle($colLetter.$rowNumber)->getFont()->setBold(true);
        $colLetter++;
        
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$date->format('d/m/Y'));
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
         * total
         */
        $rowNumber++;
        
        $firstRow = $rowNumber;

        /**
         * categories
         */
        foreach($categoriesResult AS $category){
            $colLetter = 'A';
            
            $firstCol = $colLetter;
            
            $reportWorksheet->setCellValue($colLetter.$rowNumber,$category->name);
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
            
            

            foreach($datesResult AS $date){
                $reportWorksheet->setCellValue($colLetter.$rowNumber,$totals[$category->alias][$date->format('Y-m-d')]);
                $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                
                $lastCol = $colLetter;
                
                $colLetter++;
            }
            
            /**
             * category sum
             */
            $reportWorksheet->setCellValue($colLetter.$rowNumber,'=SUM('.$firstCol.$rowNumber.':'.$lastCol.$rowNumber.')');
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
            
            $rowNumber++;
        }

        /**
         * date sum
         */
        $colLetter = 'B';
            
        foreach($datesResult AS $date){
            $reportWorksheet->setCellValue($colLetter.$rowNumber,'=SUM('.$colLetter.$firstRow.':'.$colLetter.($rowNumber - 1).')');
            $reportWorksheet->getStyle($colLetter.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $colLetter++;
        }
        
        /**
         * export file
         */
        $reportPHPExcel->setActiveSheetIndex(0);

        ob_end_clean();
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="daily-report.xls"');
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