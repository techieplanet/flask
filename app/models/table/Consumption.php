<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Coverage
 *
 * @author Swedge
 */
require_once('Facility.php');
require_once('Helper2.php');
require_once('ConsumptionHelper.php');
require_once 'CacheManager.php';
require_once 'IndicatorGroup.php';

class Consumption extends IndicatorGroup {
    //put your code here

  
        public function fetchConsumptiomPerCommodity($commodity_id=0, $geoList, $tierValue,$lastPullDate=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            if(empty($lastPullDate)){
            $latestDate = $helper->getLatestPullDate();
            }else{
            $latestDate = $lastPullDate;
            }
            
            //where clauses
            if($commodity_id > 0)
                $commodityWhere = "c.name_id = $commodity_id";
            else{
                $commIDs = $helper->getCommodityNames('',true);
                $commodityWhere = "c.name_id IN (" . $commIDs . ')';
            }
            $dateWhere = 'c.date =\'' . $latestDate . '\'';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            
            //where c.name_id IN ('10', '11', '15', '18', '24', '27', '30') AND date = '2014-12-01' AND flv.geo_parent_id IN ('1811', '1812', '1813', '1814', '1815', '1816')
            $longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            //echo $longWhereClause; exit;
            
            $consHelper = new ConsumptionHelper();
            $consByCommodity = $consHelper->getCommConsumptionByCommodity($commodity_id, $longWhereClause, $geoList);
            
            return $consByCommodity;
        }

       
        
        public function fetchConsumptionBySingleCommodityOverTime($commodity_id=0, $geoList, $tierValue){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            $latestDate = $helper->getLatestPullDate();
            $methodName = '';
            
            //where clauses
            if($commodity_id > 0){
                $commodityWhere = "c.name_id = $commodity_id";
                $methodName = $helper->getCommodityName($commodity_id);
            }
            else{
                $commIDs = implode(',',$helper->getCommodityNames('', true));
                $commodityWhere = "c.name_id IN (" . $commIDs . ')';
            }
            $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            
            $longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            //echo 'geo: ' . $longWhereClause; exit;
            
            $consHelper = new ConsumptionHelper();
            $consByGeo = $consHelper->getConsumptionBySingleCommodityOverTime($longWhereClause, $geoList, $tierText, $tierFieldName);
            
            //var_dump($consByGeo); exit;
            return array($methodName,$consByGeo);
        }
        
        
        public function fetchConsumptionByCommodityOverTime($lastPullDatemultiple = array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
           if(empty($lastPullDatemultiple)){
                    
             $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            }else{
                   
             $dateWhere = 'c.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
            //$dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            
            $commodityWhere = "(commodity_type = 'fp' OR commodity_type = 'larc')";
            
            $longWhereClause = $dateWhere . ' AND ' . $commodityWhere;
            $commNames = explode(',',$helper->getCommodityNames('', false));

            $consHelper = new ConsumptionHelper();
            $consOverTime = $consHelper->getConsumptionByCommodityOverTime($longWhereClause, $commNames);
            
            return $consOverTime;
        }
        
        
        public function fetchConsumptionByCommodityOverTimePdf($upperDate,$lowerDate){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
            $dateWhere = "c.date <= '$upperDate' AND c.date >='$lowerDate'";
            
            $commodityWhere = "(commodity_type = 'fp' OR commodity_type = 'larc')";
            
            $longWhereClause = $dateWhere . ' AND ' . $commodityWhere;
            $commNames = explode(',',$helper->getCommodityNames('', false));

            $consHelper = new ConsumptionHelper();
            $consOverTime = $consHelper->getConsumptionByCommodityOverTime($longWhereClause, $commNames);
            
            return $consOverTime;
        }
        
        public function fetchAllConsumptionBySingleLocationOverTime($geoList, $tierValue,$lastPullDatemultiple = array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            //$latestDate = $helper->getLatestPullDate();
            //$methodName = '';
            
            $methodName = '';
            $commodityWhere = "(commodity_type = 'fp' OR commodity_type = 'larc')";
            
             if(empty($lastPullDatemultiple)){
                    
             $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            }else{
                   
             $dateWhere = 'c.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
             
           // $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            $longWhereClause = $dateWhere . ' AND ' . $commodityWhere . ' AND ' . $locationWhere;
            

            $consHelper = new ConsumptionHelper();
            $consOverTime = $consHelper->getAllConsumptionBySingleLocationOverTime($dateWhere, $commodityWhere, $locationWhere);
            
            //return first element in single element location array and the over time details
            return array(current($locationNames), $consOverTime);  
        }
        
        public function fetchAllConsumptionBySingleLocationOverTimePdf($geoList, $tierValue,$upperDate,$lowerDate){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            //$latestDate = $helper->getLatestPullDate();
            //$methodName = '';
            
            $methodName = '';
            $commodityWhere = "(commodity_type = 'fp' OR commodity_type = 'larc')";
            
            $dateWhere = "c.date <= '$upperDate' AND c.date >='$lowerDate'";
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            $longWhereClause = $dateWhere . ' AND ' . $commodityWhere . ' AND ' . $locationWhere;
            

            $consHelper = new ConsumptionHelper();
            $consOverTime = $consHelper->getAllConsumptionBySingleLocationOverTimePdf($dateWhere, $commodityWhere, $locationWhere,$upperDate,$lowerDate);
            
            //return first element in single element location array and the over time details
            return array(current($locationNames), $consOverTime);  
        }
        
        public function fetchConsumptionByCommodityAndLocationOverTime($commodityIDList, $geoList, $tierValue,$lastPullDatemultiple = array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); $methodNames = array(); 
            $helper = new Helper2();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            $latestDate = $helper->getLatestPullDate();
            
            //where clauses
            if(!empty($commodityIDList)){
                $commodityIDListString = '';
                foreach ($commodityIDList as $commodityID){
                    $commodityIDListString .= "'$commodityID'" . ",";
                    $commodity = $helper->getCommodity($commodityID);
                    $methodNames[$commodity['id']] = $commodity['commodity_name'];
                }
                $commodityIDListString = substr($commodityIDListString,0, strlen($commodityIDListString)-1);
                        
                $commodityWhere = "c.name_id IN (" . $commodityIDListString . ")";
                
            }
            if(empty($lastPullDatemultiple)){
                    
             $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            }else{
                   
             $dateWhere = 'c.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
            
            //$dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            
            $longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            //echo 'geo: ' . $longWhereClause; exit;
            
            $consHelper = new ConsumptionHelper();
            $consByGeo = $consHelper->getConsumptionByCommodityAndLocationOverTime($commodityIDList, $longWhereClause, $locationNames, $geoList, $tierText, $tierFieldName);
            
            //var_dump($methodNames); exit;
            return array($methodNames,$consByGeo);
            //return array($consByGeo);
        }
        
        
        /*
         * SELECT `cno`.`commodity_name` AS `method`, SUM(consumption),  c.geo_zone, date
                   FROM  commodity_name_option  cno 
                   LEFT JOIN ( 
                                SELECT c.*, flv.geo_zone FROM commodity c 
                                INNER JOIN facility_location_view AS flv ON flv.id = c.facility_id 
                                WHERE ( flv.geo_parent_id IN ('1811','1812','1813','1814','1815','1816') AND  c.date <='2016-02-01' AND c.date >= '2015-03-01' )
                               ) as c
                   ON cno.id = c.name_id WHERE (cno.id IN ('36','37')) 
            GROUP BY cno.id, geo_zone, date                   
            ORDER BY `display_order`, date DESC, geo_zone ASC
         */
        public function fetchNewAcceptorsAndCurrentUsers($selectedMethods, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array(); $commNames = array();
            $helper = new Helper2(); $cacheManager = new CacheManager();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = str_replace("flv.", "", $helper->getTierFieldName($tierText));
            $locationNames = $helper->getLocationNames($geoList);
            
            //where clauses
            if(!empty($selectedMethods)){
                //$commodityWhere = "cno.id = $commodity_id";
                foreach($selectedMethods as $method){
                    $commodity = $helper->getCommodity($method);
                    $commodityID = $commodity["id"];
                    $commNames[] =  $commodity["commodity_alias"];
                }
                $commodityWhere = "cno.id IN (" . implode(',',$selectedMethods) . ")";
                //echo $commodityWhere; exit;
            }
            else{ //default
                $newAcceptors = $helper->getCommodityByAlias("new_acceptors");
                $newAcceptorsID = $newAcceptors["id"];
                $commNames[] =  $newAcceptors["commodity_alias"];
                        
                $currentUsers = $helper->getCommodityByAlias("current_users");
                $currentUsersID = $currentUsers["id"];
                $commNames[] =  $currentUsers["commodity_alias"];
                
                $commodityWhere = "cno.id IN (" . "'$newAcceptorsID','$currentUsersID'" . ")";
                //echo $commodityWhere; exit;
            }
            
            $latestDate = $helper->getLatestPullDate();
            
            $cacheValue = $cacheManager->getIndicator(CacheManager::USERINDICATORS, $latestDate);
            
            //$cacheValue = null;
            
            if($cacheValue && $freshVisit){ 
                $output = json_decode($cacheValue, true);
            }
            else{
            if(empty($lastPullDatemultiple)){
                    
             $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            }else{
                   
             $dateWhere = 'c.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
                
                //$dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
                
                
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                $locationNames = $helper->getLocationNames($geoList);

                //where c.name_id IN ('10', '11', '15', '18', '24', '27', '30') AND date = '2014-12-01' AND flv.geo_parent_id IN ('1811', '1812', '1813', '1814', '1815', '1816')
                //$longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
                $groupArray = array('c.name_id', $tierFieldName, 'date');
                $orderArray = array('display_order', 'date DESC', 'geo_zone ASC');

                //echo $longWhereClause; exit;

                $consHelper = new ConsumptionHelper();
                $output = $consHelper->getNewAcceptorsAndCurrentUsers($commNames, $commodityWhere, $locationWhere, $locationNames, $dateWhere, $tierText, $groupArray, $orderArray,$lastPullDatemultiple);
                //echo var_dump($output);
                
                //echo "<br><br><br><br>";
                //sum indicators for national figures
                if(!$freshVisit && !empty($selectedMethods)){
                    $cacheArray = json_decode($cacheValue);
                    foreach($cacheArray as $monthName=>$monthArray){
                         $totalNewAcceptors = $totalCurrentUsers = 0;
                         foreach($monthArray as $location=>$indicators){
                             $totalNewAcceptors += (int)$indicators->new_acceptors;
                             $totalCurrentUsers += (int)$indicators->current_users;
                         }
                         //$output[$monthName]['National'] = array('new_acceptors'=>$totalNewAcceptors, 'current_users'=>$totalCurrentUsers);
                         //$output[$monthName] = array('National' => $output[$monthName]['National']) + $output[$monthName];
                         
                         $arr = array('new_acceptors'=>$totalNewAcceptors, 'current_users'=>$totalCurrentUsers);
                         if(is_array($output[$monthName])){
                         $output[$monthName] = array_merge(array('National' => $arr), $output[$monthName]);
                         }else{
                         //$output[$monthName] = array_merge(array('National' => $arr), array());    
                         }
                    }
                    //echo var_dump($output); exit;
                }
                
                //check if to save month national data
                $alias = CacheManager::USERINDICATORS;
                if(!$cacheValue && $freshVisit){ //fresh in month
                    //do cache insert
                    $dataArray = array(
                        'date_cached'=> $latestDate,
                        'indicator' => 'New FP Acceptors and Current FP Users',
                        'indicator_alias' => $alias,
                        'value' => json_encode($output)
                    );
                    $cacheManager->setIndicator($dataArray);
                }
//                else if($updateMode){
//                    $dataArray = array('value' => json_encode($output));
//                    $where = "'alias=$alias'";
//                    $cacheManager->updateIndicator($dataArray, $where);
//                }
//                else{ //inner if
//                    echo json_encode($output); exit;
//                    //get month national data and put in first array element
//                    $cacheValue = json_decode($cacheValue, true);
//                    if($cacheValue)
//                        $output[0]['percent'] = $cacheValue[0]['percent'];
                }
                    
            return $output;
        }
        
        
     /**
     * Facilities reporting and providing over time
     * With respect to all reporting facilities and 
     * 2. To FP facilities
      * 
     * @param type $commodity_type
     * @param type $geoList
     * @param type $tierValue
     * @param type $freshVisit
     * @param type $updateMode
     * @param type $lastPullDate
     * @return array of results by month
     */
    public function fetchFacsReportingRateOvertime($geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDatemultiple=[]){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
                    
            $cacheManager = new CacheManager();
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_REPORTING_RATE_OVERTIME, $latestDate);

            if($cacheValue && $freshVisit){
                $output = json_decode($cacheValue, true);
            }
            else{
                    $tierNameField = $helper->getLocationTierText($tierValue);
                    $tierIDField = $helper->getTierFieldName($tierNameField);

                    //where clauses
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    $dateWhere = '';
                    if(empty($lastPullDatemultiple)){  
                        $dateWhere = "c.date BETWEEN '" . 
                                date("Y-m-d", strtotime("$latestDate -11 months")) . "' AND '$latestDate'";
                        $subDateWhere = str_replace('c.', 'c_sub.', $dateWhere);
                    }else{
                        $dateWhere = 'c.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                        $subDateWhere = str_replace('c.', 'c_sub.', $dateWhere);
                    }
                    
                    $sixMonthsDateWhere = "(date BETWEEN '" . 
                                date("Y-m-d", strtotime("$latestDate -5 months")) . "' AND '$latestDate')";
                    
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $locationWhere;
                    
                    $facility = new Facility();
                    
                    $numerators = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierNameField, 
                            $tierIDField, 
                            $ct_where, 
                            $sixMonthsDateWhere
                    );
                    
                    $locationNames = $helper->getLocationNames($geoList); 
                    $monthNames = !empty($lastPullDatemultiple) ? 
                                    array_reverse($helper->formatMonthName($lastPullDatemultiple)) : 
                                    array_reverse($helper->getPreviousMonthNames(12)); 
                    
                    //add all missing months for each location in the numerator list
                    //$numerators = $this->addMissingMonths($numeratorsResult, $monthNames, $locationNames, $tierNameField); 
                                     
                    /*********************************************************************************
                     * denominator for ALL Reporting facilites
                     ********************************************************************************/
                    if(empty($lastPullDatemultiple)){  
                        $FRRDateWhere = "frr.date BETWEEN '" . 
                                date("Y-m-d", strtotime("$latestDate -11 months")) . "' AND '$latestDate'";
                    }else{
                        $FRRDateWhere = 'frr.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                    }
                    
                    $longWhereClause = $FRRDateWhere . ' AND ' . $locationWhere;
                                        
                    $denominators = $facility->getReportingFacsOvertimeByLocation(
                            $longWhereClause, 
                            $geoList, 
                            $tierNameField, 
                            $tierIDField
                    );
                    
                    /*********************************************************************************
                     * denominator for FP facilites: consumed 1 FP commodity in last 6 months
                     ********************************************************************************/
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $dateWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $locationWhere;
                    
                    $FPFacsDenominators = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierNameField, 
                            $tierIDField, 
                            $ct_where,
                            $sixMonthsDateWhere
                    );
                    //var_dump($FPFacsDenominators); exit;
                    $output['allfacs'] = $this->setUpOvertimeOutput($monthNames, $locationNames, $numerators, $denominators);
                    $output['fpfacs'] = $this->setUpOvertimeOutput($monthNames, $locationNames, $numerators, $FPFacsDenominators);
                    
                    //check if to save month national data
                    $alias = CacheManager::PERCENT_FACS_REPORTING_RATE_OVERTIME;
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert                    
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent facilities reporting rate overtime',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                            //'timestamp_created' => date('');
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
                    else if($updateMode){
                        $dataArray = array('value' => json_encode($output));

                        $where = "indicator_alias='$alias'";

                        $cacheManager->updateIndicator($dataArray, $where);
                    }
                    else{ //inner if
                        /**
                         * Helps to get the cached national value for older months. 
                         * We get the latest DHIS download date as pull date not filter date
                         * This will help us to get the national values even for months that there are no cached data
                         * if all the previous 12 months have cached data for this indicator, then we can use the 
                         * selected filter date for this call
                         */
                        $cacheValue = $cacheManager->getIndicator(
                                CacheManager::PERCENT_FACS_REPORTING_RATE_OVERTIME, 
                                $helper->getLatestPullDate()
                        );
                        $cacheValue = json_decode($cacheValue, true);
                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                            $output['allfacs'][$monthName]['National']['percent'] = $cacheValue['allfacs'][$monthName]['National']['percent'];
                            $output['fpfacs'][$monthName]['National']['percent'] = $cacheValue['fpfacs'][$monthName]['National']['percent'];
                        }
                    }
            }

            //set national ave
            //var_dump($output); exit;
            return $output;
            
    }
}

?>