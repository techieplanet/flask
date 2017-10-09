<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dashboard
 *
 * @author Swedge
 */
require_once 'Helper2.php';
require_once 'Facility.php';
require_once 'DashboardHelper.php';
require_once 'CacheManager.php';
require_once 'Coverage.php';
require_once 'Stockout.php';
require_once 'CoverageNationalHelper.php';

class Dashboard {
    //put your code here
    
    public function fetchConsumptionByMethod(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $output = array ();
        $helper = new Helper2();
        $cacheManager = new CacheManager();
        
        $latestDate = $helper->getLatestPullDate();
        $cacheValue = $cacheManager->getIndicator(CacheManager::CONSUMPTIONON_BY_METHOD,$latestDate);
        
        if(!$cacheValue){
            $where = "(commodity_type='fp' OR commodity_type = 'larc') AND date = '$latestDate'";
            $select = $db->select()
                         ->from(array('c'=>'commodity'), array('SUM(consumption) as consumption'))
                         ->joinRight(array('cno'=>'commodity_name_option'), 'cno.id = c.name_id', 
                                                    array('commodity_name as method'))
                         ->where($where)
                         ->group(array('commodity_name', 'display_order'))
                         ->order(array('display_order'));
            
            $result = $db->fetchAll($select);
            
            //do cache insert
            $dataArray = array(
                    'date_cached'=> $latestDate,
                    'indicator' => 'Consumption By Method',
                    'indicator_alias' => CacheManager::CONSUMPTIONON_BY_METHOD,
                    'value' => json_encode($result)
                );
            $cacheManager->setIndicator($dataArray);
        }
        else{ //data is cached. Retrieve it
            $result = json_decode($cacheValue, true);
        }
        return $result;               
    }
    
    
    /*TP: Rewriting the fetchCSDetails method
       * This time we will modularize and make use of views that will already filter the date
       * This method get the last DHIS2 download date and use it as argument for 
       * the 3 categories of calls: 
       */
      public function fetchCoverageSummary($geoList, $tierValue){          
           //$output = $this->fetchCSDetails1(null);
           //var_dump($output); 
           
           $db = Zend_Db_Table_Abstract::getDefaultAdapter();
	   $output = array(); $helper = new Helper2(); $params = array(); $fac = new Facility();
           $cacheManager = new CacheManager();
           $coverage = new Coverage(); $stockout = new Stockout();
           
           $latestDate = $helper->getLatestPullDate();
           $cacheValue = $cacheManager->getIndicator(CacheManager::HR_SUMMARY,$latestDate);
        
            if(!$cacheValue){            
                //echo 'no cache'; exit;
                $freshVisit = false;
                
                //facility with trained HW
                $output['cs_fp_trained_facility_count'] = $this->fetchPercentFacHWTrained('fp', $geoList, $tierValue, $freshVisit);
                $output['cs_larc_trained_facility_count'] = $this->fetchPercentFacHWTrained('larc', $geoList, $tierValue, $freshVisit);
                
                //facility with trained HW providing
                $output['cs_fp_consumption_facility_count'] = $this->fetchFacsWithHWProviding('fp', 'fp', $geoList, $tierValue, $freshVisit);
                $output['cs_larc_consumption_facility_count'] = $this->fetchFacsWithHWProviding('larc', 'larc', $geoList, $tierValue, $freshVisit);
                
                //$cs_fp_stock_out_facility_count = $this->fetchPercentStockOutFacsWithTrainedHW('fp', $geoList, $tierValue, $freshVisit);
                //$output['cs_fp_stock_out_facility_count'] = $cs_fp_stock_out_facility_count[0]['percent'];
                
                //facility with trained HW providing but stocked out
                $output['cs_fp_stock_out_facility_count'] = $this->fetchPercentStockOutFacsWithTrainedHW('fp', $geoList, $tierValue, $freshVisit);
                $output['cs_larc_stock_out_facility_count'] = $this->fetchPercentStockOutFacsWithTrainedHW('larc', $geoList, $tierValue, $freshVisit);
                
                //do cache insert
                $dataArray = array(
                    'date_cached'=> $latestDate,
                    'indicator' => 'HR Summary',
                    'indicator_alias' => CacheManager::HR_SUMMARY,
                    'value' => json_encode($output)
                );
                $cacheManager->setIndicator($dataArray);
            }
            else{ //data is cached. Retrieve it
                $output = json_decode($cacheValue, true);
            }
          
            //var_dump($output); exit;
          return $output;
            
   }//end fetchCoverageSummary
      
      
      public function fetchFacsProviding($commodity_type) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $output = array(); $helper = new Helper2();
            $cacheManager = new CacheManager();

            //$denominator = $this->getMonthlyFacilitiesReportingWithConsumption();
            $latestDate = $helper->getLatestPullDate();
            
            if($commodity_type == 'fp')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROV_OVERTIME_FP,$latestDate);
            if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROV_OVERTIME_LARC,$latestDate);
            
                if(!$cacheValue){
                        //where clauses
                        if($commodity_type == 'fp')
                            $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                        else if($commodity_type == 'larc')
                            $ct_where = "commodity_type = 'larc'";

                        $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                        $reportingWhere = 'facility_reporting_status = 1';
                        $consumptionWhere = 'consumption > 0';
                    
                        $nationalHelper = new CoverageNationalHelper();
                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                           $consumptionWhere . ' AND ' . $ct_where;
                        $numerators = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
                        
                        $denominators = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);
                        
                        foreach ($numerators as $i => $row){
                            $output[] = array(
                                "month" => $numerators[$i]['month_name'],
                                'numer' => $numerators[$i]['fid_count'],
                                'denom' => $denominators[$i]['fid_count'],
                                "year" => $numerators[$i]['year'],
                                "percent" => round($numerators[$i]['fid_count']/$denominators[$i]['fid_count']*100,1),
                            );
                        }
                        
                       
                        if($commodity_type == 'fp')
                            $alias = CacheManager::PERCENT_FACS_PROV_OVERTIME_FP;
                       if($commodity_type == 'larc')
                            $alias = CacheManager::PERCENT_FACS_PROV_OVERTIME_LARC;
                       
                        //do cache insert
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent Facs Providing Over Time',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                        );
                        $cacheManager->setIndicator($dataArray);
                }
                else{ //data is cached. Retrieve it
                    $output = json_decode($cacheValue, true);
                }
            

            //var_dump($output); exit;
            //return array_reverse($output, true);
            return $output;
        }	
        
        
        public function fetchFacsProvidingNumeratorDenominator($commodity_type) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $output = array(); $helper = new Helper2();
            $cacheManager = new CacheManager();

            //$denominator = $this->getMonthlyFacilitiesReportingWithConsumption();
            $latestDate = $helper->getLatestPullDate();
            
           
             
                        //where clauses
                        if($commodity_type == 'fp')
                            $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                        else if($commodity_type == 'larc')
                            $ct_where = "commodity_type = 'larc'";

                        $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                        $reportingWhere = 'facility_reporting_status = 1';
                        $consumptionWhere = 'consumption > 0';
                    
                        $nationalHelper = new CoverageNationalHelper();
                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                           $consumptionWhere . ' AND ' . $ct_where;
                        $numerators = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
                        
                        $denominators = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);
                        
                        $finalNumerator = array();
                        $finalDenominator = array();
                        
                        foreach ($numerators as $i => $row){
                            $month = $numerators[$i]['month_name'];
                            $finalNumerator[$month] = $numerators[$i]['fid_count'];
                            $finalDenominator[$month] = $denominators[$i]['fid_count'];
                          
                        }
                        
                       
                 return  array($finalNumerator,$finalDenominator);
          
        }
        
        /*
         * This method is now obsolete but keeping for some time just in case
         */
        public function fetchFacsProvidingStockedout($geoList, $tierValue, $freshVisit) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $output = array();  $helper = new Helper2();
            $cacheManager = new CacheManager();
            
            $latestDate = $helper->getLatestPullDate();
            $cacheValue = $cacheManager->getIndicator(CacheManager::STOCK_OUTS,$latestDate);
            //$cacheValue = null;
            
            if($cacheValue && $freshVisit){ //data is cached. Use it
                $output = json_decode($cacheValue, true);
            }
            else{
                /******************** BEGIN NEW QUERY ************************/
                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                
                $dashBoardHelper = new DashboardHelper();
                $numerators = $dashBoardHelper->getStockOutNumerators($locationWhere, $tierText, $tierFieldName);
                //var_dump($numerators); echo '<br><br>'; exit;
                $denominators = $dashBoardHelper->getStockOutDenominators($locationWhere);     
                //var_dump($denominators); exit;
                /******************** END NEW QUERY **************************/

                foreach ($numerators['fp'] as $key=>$row){
                    $output[] = array(
                        "month" => date('F', strtotime($key)),
                        "year" => date('Y', strtotime($key)),
                        "implant_percent" => $numerators['larc'][$key] / $denominators['larc'][$key], // implant
                        "seven_days_percent" => $row / $denominators['fp'][$key]
                    );
                }

                //check if to save month national data
                if(!$cacheValue){ //fresh in month...this will be always true if execution gets here
                    //do cache insert
                    $dataArray = array(
                        'date_cached'=> $latestDate,
                        'indicator' => 'Stock Outs',
                        'indicator_alias' => CacheManager::STOCK_OUTS,
                        'value' => json_encode($output)
                    );
                    $cacheManager->setIndicator($dataArray);
                }
            }
            

            //var_dump($output); exit;
            return array_reverse($output,true);

        }	
        
        
        
        //this method replaces the above method
        public function fetchFacsProvidingStockedoutOvertime($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
                $output = array(); 
                $helper = new Helper2();
                $latestDate = $helper->getLatestPullDate();
                
                $cacheManager = new CacheManager();
            
                if($commodity_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_FP, $latestDate);
                else if($commodity_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_LARC, $latestDate);
                
                //$cacheValue = null;
                //check if page is just being loaded
                //fresh session, month data already registered
                //just retrieve registered data
                if($cacheValue && $freshVisit){ 
                    $output = json_decode($cacheValue, true);
                }
                else {
                    //needed variables
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);


                    //where clauses
                    if($commodity_type == 'fp'){
                        $commodityTypeWhere = "commodity_type = 'fp'";
                        $commodityAliasWhere = "commodity_alias = 'so_fp_seven_days'";
                        $percentIndexText = 'seven_days_percent';
                    }
                    else if($commodity_type == 'larc'){
                        $commodityTypeWhere = "commodity_type = 'larc'";
                        $commodityAliasWhere = "commodity_alias = 'so_implants'";
                        $percentIndexText = 'implant_percent';
                    }


                    $dateWhere = "c.date = '$latestDate'";
                    //use 5 months interval because current month is inclusive
                    $date6MonthsIntervalWhere = "c.date >= DATE_SUB('$latestDate', INTERVAL 5 MONTH) AND c.date <= '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $stockoutWhere = "stock_out='Y'";
                    $consumptionWhere = 'consumption > 0';

                    $mainWhereClause = $reportingWhere . ' AND ' . 
                                        $commodityAliasWhere . ' AND ' . $stockoutWhere . ' AND ' .
                                        $locationWhere;
                    $subWhereClause = $commodityTypeWhere . ' AND ' . $consumptionWhere . ' AND ' .
                                      $locationWhere;;

                    $dashboardHelper = new DashboardHelper();                
                    $numerators = $dashboardHelper->getFacsProvidingButStockedout($mainWhereClause, $subWhereClause,$lastPullDatemultiple);

                    //change main where
                    $mainWhereClause = $reportingWhere . ' AND ' . $locationWhere;
                    $denominators = $dashboardHelper->getFacsProvidingButStockedout($mainWhereClause, $subWhereClause,$lastPullDatemultiple);

                    foreach ($numerators as $date=>$numer){
                        $output[] = array(
                                    'numer' => $numer,
                                    'denom' => $denominators[$date],
                                    'month' => date('F', strtotime($date)),
                                    'percent' => round($numer / $denominators[$date] * 100, 1)
                        );
                    }
                    
                    //print_r($numerators); echo '<br><br>';
                    //print_r($denominators); echo '<br><br>';
                    //print_r($output); echo '<br><br>';
                    //exit;
                    
                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert
                        if($commodity_type == 'fp')
                            $alias = CacheManager::STOCK_OUTS_OVERTIME_FP;
                        else if($commodity_type == 'larc')
                            $alias = CacheManager::STOCK_OUTS_OVERTIME_LARC;
                    
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Providing Stocked Out Over time',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
                }
                
                
                return $output;
        }
        
        
        //this method gets the numerators and the denominators of the method above
        public function fetchFacsProvidingStockedoutOvertimeNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
                $output = array(); 
                $helper = new Helper2();
                $latestDate = $helper->getLatestPullDate();
                
                $cacheManager = new CacheManager();
              if($commodity_type == 'fp'){
                    $cacheValueNumerator = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_FP_NUMERATOR, $latestDate);
                    $cacheValueDenominator = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_FP_DENOMINATOR, $latestDate);
              }
                else if($commodity_type == 'larc'){
                    $cacheValueNumerator = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_LARC_NUMERATOR, $latestDate);
                    $cacheValueDenominator = $cacheManager->getIndicator(CacheManager::STOCK_OUTS_OVERTIME_LARC_DENOMINATOR, $latestDate);
                } 
                
                //if this is a freshvisit
                if($cacheValueNumerator && $cacheValueDenominator && $freshVisit){ 
                    $finalNumerator = json_decode($cacheValueNumerator,true);
                    $finalDenominator = json_decode($cacheValueDenominator,true);
                    
                }else{
               
                    //needed variables
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);


                    //where clauses
                    if($commodity_type == 'fp'){
                        $commodityTypeWhere = "commodity_type = 'fp'";
                        $commodityAliasWhere = "commodity_alias = 'so_fp_seven_days'";
                        $percentIndexText = 'seven_days_percent';
                    }
                    else if($commodity_type == 'larc'){
                        $commodityTypeWhere = "commodity_type = 'larc'";
                        $commodityAliasWhere = "commodity_alias = 'so_implants'";
                        $percentIndexText = 'implant_percent';
                    }


                    $dateWhere = "c.date = '$latestDate'";
                    //use 5 months interval because current month is inclusive
                    $date6MonthsIntervalWhere = "c.date >= DATE_SUB('$latestDate', INTERVAL 5 MONTH) AND c.date <= '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $stockoutWhere = "stock_out='Y'";
                    $consumptionWhere = 'consumption > 0';

                    $mainWhereClause = $reportingWhere . ' AND ' . 
                                        $commodityAliasWhere . ' AND ' . $stockoutWhere . ' AND ' .
                                        $locationWhere;
                    $subWhereClause = $commodityTypeWhere . ' AND ' . $consumptionWhere . ' AND ' .
                                      $locationWhere;;

                    $dashboardHelper = new DashboardHelper();                
                    $numerators = $dashboardHelper->getFacsProvidingButStockedout($mainWhereClause, $subWhereClause,$lastPullDatemultiple);

                    //change main where
                    $mainWhereClause = $reportingWhere . ' AND ' . $locationWhere;
                    $denominators = $dashboardHelper->getFacsProvidingButStockedout($mainWhereClause, $subWhereClause,$lastPullDatemultiple);

                    foreach ($numerators as $date=>$numer){
                        $month = date('F', strtotime($date));
                        //$year = date('Y', strtotime($date));
                        //$month = $month . ", " . $year;
                        $finalNumerator[$month] = $numer;
                        $finalDenominator[$month] = $denominators[$date];
                        
                    }
                    
                    
                    
                
                    //print_r($finalNumerator); echo '<br><br>';
                    //print_r($finalDenominator); echo '<br><br>';
                    //print_r($output); echo '<br><br>';
                    //exit;
                    
                   
                
                
                
               $finalNumerator = array_reverse($finalNumerator);
               $finalDenominator = array_reverse($finalDenominator);
               
               if($commodity_type == 'fp'){
                        //$aliasNumerator = CacheManager::PERCENT_FACS_HW_PROVIDING_FP;
                        $aliasNumerator = CacheManager::STOCK_OUTS_OVERTIME_FP_NUMERATOR;
                        $aliasDenominator = CacheManager::STOCK_OUTS_OVERTIME_FP_DENOMINATOR;
               } else if($commodity_type == 'larc'){
                       
                        $aliasNumerator = CacheManager::STOCK_OUTS_OVERTIME_LARC_NUMERATOR;
                        $aliasDenominator = CacheManager::STOCK_OUTS_OVERTIME_LARC_DENOMINATOR;
               }
                    
                    //check if to save month national data
                    if((!$cacheValueNumerator OR !$cacheValueDenominator) && $freshVisit){ //fresh in month
                        
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Stock Outs Overtime FP/LARC Numerator/Denominator',
                            'indicator_alias' => $aliasNumerator,
                            'value' => json_encode($finalNumerator)
                        );
                        $cacheManager->setIndicator($dataArray);
                        
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Stock Outs Overtime FP/LARC Numerator/Denominator',
                            'indicator_alias' => $aliasDenominator,
                            'value' => json_encode($finalDenominator)
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
        }
                    
                 
                
                
                return array($finalNumerator,$finalDenominator);
           }
        
        
        
        //////////////////////////////////////////////////////////////////////////////////////
        /*
         * This method is a duplicate of the fetchPercentFacHWTrained in training but had to be duplicated 
         * because we need a version that sends the values directly as numbers instead of percentages.
         */
        public function fetchPercentFacHWTrained($training_type, $geoList, $tierValue, $freshVisit){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2(); $nationalAvg = 0;
                
                $cacheManager = new CacheManager();
            
                $latestDate = $helper->getLatestPullDate();
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_LARC, $latestDate);
                
                //$cacheValue = null;
                
                //echo 'after cache value<br>';
                //check if page is just being loaded
                //fresh session, month data already registered
                //just retrieve registered data
                if($cacheValue && $freshVisit){ 
                    $output = json_decode($cacheValue, true);
                }
                else {
                    //needed variables
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    $latestDate = $helper->getLatestPullDate();

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $highestDate = date('Y-m-t', strtotime($latestDate));
                    $endDateWhere = "t.training_end_date <= '" . $highestDate . "'";
                    
                    if($training_type == 'fp') 
                        $trainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc') AND tto.is_deleted=0";
                    else if($training_type == 'larc') 
                        $trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    
                    $trainingWhere = "t.is_deleted = 0";
                    $personWhere = "p.is_deleted = 0";
                    
                    $longWhereClause = $endDateWhere . ' AND ' . $trainingTypeWhere . ' AND ' . 
                                       $trainingWhere . ' AND ' . $personWhere . ' AND ' . $locationWhere;

                    $coverageHelper = new CoverageHelper();                
                    $facility = new Facility();
                    
                    $numerators = $coverageHelper->getFacWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                    
                    $nationalAvg = 0; $runningOutput = array();
                    foreach($numerators as $location=>$numer){
                        $output[] = array(
                              'location' => $location,
                              'value' => $numer
                        );
                        $nationalAvg += $numer;
                    }

                }
                
                //var_dump($output); exit;
                return $nationalAvg;
        }
        
    
        
        /*
        * This method is a duplicate of the fetchFacsWithHWProviding in training but had to be duplicated 
        * because we need a version that sends the values directly as numbers instead of percentages.
        */
        public function fetchFacsWithHWProviding($commodity_type, $training_type, $geoList, $tierValue, $freshVisit){
          
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2();
                $latestDate = $helper->getLatestPullDate();
                
                $cacheManager = new CacheManager();
            
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_LARC, $latestDate);


                //check if page is just being loaded
                //fresh session, month data already registered
                //just retrieve registered data
                if($cacheValue && $freshVisit){ 
                    $output = json_decode($cacheValue, true);
                }
                else{
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    $locationNames = $helper->getLocationNames($geoList);
                    $consumptionWhere = 'consumption > 0';
                    $reportingWhere = 'facility_reporting_status = 1';

                    $dateWhere = "c.date = '$latestDate'";

                    //commodity type where
                    if($commodity_type == 'fp')
                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    else if($commodity_type == 'larc')
                        $ct_where = "commodity_type = 'larc'";

                    //training type where
                    if($training_type == 'fp')
                        $tt_where = "fptrained > 0 OR larctrained > 0";
                    else if($commodity_type == 'larc')
                        $tt_where = "larctrained > 0";
                    

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    
                    $coverageHelper = new CoverageHelper();

                    //concatenate conditions for numerators
                    $longWhereClause = $consumptionWhere . ' AND ' . $reportingWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $tt_where . ' AND ' . $locationWhere . ' AND ' .
                                       $dateWhere;
                    $numerators = $coverageHelper->getCoverageCountFacWithHWProviding($longWhereClause, $locationNames, $geoList, $tierText, $tierFieldName);
                    
                    //set output                    
                    $nationalAvg = 0; 
                    foreach($numerators as $location=>$numer){
                        $output[] = array(
                              'location' => $location,
                              'value' => $numer
                        );
                        $nationalAvg += $numer;
                    }
                }
                    
                //var_dump($output); exit;
                return $nationalAvg;
     }
    
    
     
     /*
        * This method is a duplicate of the fetchPercentStockOutFacsWithTrainedHW in training but had to be duplicated 
        * because we need a version that sends the values directly as numbers instead of percentages.
        */
     public function fetchPercentStockOutFacsWithTrainedHW($training_type, $geoList, $tierValue, $freshVisit, $updateMode = false){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
                $output = array(array('location'=>'National', 'percent'=>0)); 
                $helper = new Helper2();
                $cacheManager = new CacheManager();
            
                $latestDate = $helper->getLatestPullDate();
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_STOCKED_OUT_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_STOCKED_OUT_LARC, $latestDate);
                
                //check if page is just being loaded
                //fresh session, month data already registered
                //just retrieve registered data
                if($cacheValue && $freshVisit){ 
                    $output = json_decode($cacheValue, true);
                }
                else {
                    //needed variables
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    $latestDate = $helper->getLatestPullDate();

                    //where clauses
                    if($training_type == 'fp'){
                        $tt_where = "fptrained > 0 OR larctrained > 0";
                        $commodityWhere = "commodity_alias = 'so_fp_seven_days'";
                    }
                    else if($training_type == 'larc'){
                        $tt_where = 'larctrained > 0';
                        $commodityWhere = "commodity_alias = 'so_implants'";
                    }


                    $dateWhere = "date = '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $stockoutWhere = "stock_out='Y'";
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                        $tt_where . ' AND ' . $commodityWhere . ' AND ' .
                                        $stockoutWhere. ' AND ' . $locationWhere;

                    $stockoutHelper = new StockoutHelper();                
                    $numerators = $stockoutHelper->getStockoutFacsWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                    
                    //set output                    
                    $nationalAvg = 0; 
                    foreach($numerators as $location=>$numer){
                        $output[] = array(
                              'location' => $location,
                              'value' => $numer
                        );
                        $nationalAvg += $numer;
                    }                    
                    
                }
                
                //var_dump($output); exit;
                return $nationalAvg;
    }
}


?>