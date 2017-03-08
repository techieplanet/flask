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

class Consumption {
    //put your code here

  
        public function fetchConsumptiomPerCommodity($commodity_id=0, $geoList, $tierValue){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            $latestDate = $helper->getLatestPullDate();
            
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
        
        
        public function fetchConsumptionByCommodityOverTime(){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
            $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            
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
        
        public function fetchAllConsumptionBySingleLocationOverTime($geoList, $tierValue){
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
            
            $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
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
        
        public function fetchConsumptionByCommodityAndLocationOverTime($commodity_id, $geoList, $tierValue){
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
            
            $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            
            $longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            //echo 'geo: ' . $longWhereClause; exit;
            
            $consHelper = new ConsumptionHelper();
            $consByGeo = $consHelper->getConsumptionByCommodityAndLocationOverTime($longWhereClause, $locationNames, $geoList, $tierText, $tierFieldName);
            
            //var_dump($consByGeo); exit;
            return array($methodName,$consByGeo);
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
        public function fetchNewAcceptorsAndCurrentUsers($commodity_id, $geoList, $tierValue, $freshVisit){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array(); $commNames = array();
            $helper = new Helper2(); $cacheManager = new CacheManager();

            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = str_replace("flv.", "", $helper->getTierFieldName($tierText));
            $locationNames = $helper->getLocationNames($geoList);
            
            //where clauses
            if($commodity_id > 0){
                $commodityWhere = "cno.id = $commodity_id";
                $commodity = $helper->getCommodity($commodity_id);
                $commodityID = $commodity["id"];
                $commNames[] =  $commodity["commodity_alias"];
                $commodityWhere = "cno.id IN (" . "'$commodityID'" . ")";
            }
            else{
                $newAcceptors = $helper->getCommodityByAlias("new_acceptors");
                $newAcceptorsID = $newAcceptors["id"];
                $commNames[] =  $newAcceptors["commodity_alias"];
                        
                $currentUsers = $helper->getCommodityByAlias("current_users");
                $currentUsersID = $currentUsers["id"];
                $commNames[] =  $currentUsers["commodity_alias"];
                
                $commodityWhere = "cno.id IN (" . "'$newAcceptorsID','$currentUsersID'" . ")";
            }
            
            $latestDate = $helper->getLatestPullDate();
            
            $cacheValue = $cacheManager->getIndicator(CacheManager::USERINDICATORS, $latestDate);
            
//            if($cacheValue && $freshVisit){ 
//                $output = json_decode($cacheValue, true);
//            }
//            else{
            
                $dateWhere = 'c.date <= (SELECT MAX(date) FROM facility_report_rate) AND c.date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH)';
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                $locationNames = $helper->getLocationNames($geoList);

                //where c.name_id IN ('10', '11', '15', '18', '24', '27', '30') AND date = '2014-12-01' AND flv.geo_parent_id IN ('1811', '1812', '1813', '1814', '1815', '1816')
                //$longWhereClause = $commodityWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
                $groupArray = array('c.name_id', $tierFieldName, 'date');
                $orderArray = array('display_order', 'date DESC', 'geo_zone ASC');

                //echo $longWhereClause; exit;

                $consHelper = new ConsumptionHelper();
                $output = $consHelper->getNewAcceptorsAndCurrentUsers($commNames, $commodityWhere, $locationWhere, $locationNames, $dateWhere, $tierText, $groupArray, $orderArray);
                //echo var_dump($output);
                //echo "<br><br><br><br>";
                //sum indicators for national figures
                if(!$freshVisit && $commodity_id > 0){
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
                         $output[$monthName] = array_merge(array('National' => $arr), $output[$monthName]);
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
//                }
                    
            return $output;
        }
}

?>