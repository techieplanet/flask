<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CIPCoverage
 *
 * @author swedge-mac
 */

require_once('Facility.php');
require_once('Helper2.php');
require_once('IndicatorGroup.php');

class CIPCoverage extends IndicatorGroup {    
    /**
     * Calculates the percentage of facilities that provided up to a number of products in a period
     * e.g. percentage of facilities providing at least 3 commodities in a period
     * 
     * @param type $commodity_type
     * @param type $numberOfMethods
     * @param type $geoList
     * @param type $tierValue
     * @param type $freshVisit
     * @param type $updateMode
     * @param type $lastPullDate
     * @return type
     */
    public function fetchFacsProvidingNumberOfMethods($commodity_type, $numberOfMethods, 
            $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();

            //$output = array(array('location'=>'National', 'percent'=>0)); 
            $output['allfacs']['National'] = array();
            $output['fpfacs']['National'] = array();
            
            $helper = new Helper2();
            
            if(empty($lastPullDate) || $lastPullDate==""){
              $latestDate = $helper->getLatestPullDate();
             }else{
              $latestDate = $lastPullDate;
             }
                    
            $cacheManager = new CacheManager();
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_3_METHODS, $latestDate);
            
            if($cacheValue && $freshVisit){
                $output = json_decode($cacheValue, true);
            }
            else{
                    $tierNameField = $helper->getLocationTierText($tierValue);
                    $tierIDField = $helper->getTierFieldName($tierNameField);

                    //where clauses
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    $dateWhere = "c.date = '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $locationWhere;
                    
                    $coverageHelper = new CoverageHelper();
                    $facility = new Facility();
                    
                    $numeratorsResult = $facility->getFacsProvidingNumberOfMethods(
                            $numberOfMethods, 
                            $longWhereClause,
                            $geoList,
                            $tierNameField,
                            $tierIDField,
                            $latestDate
                    );
                    
                    //filter for only valid location values and set the nulls to 0s
                    $locationNames = $helper->getLocationNames($geoList);
                    $numerators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $numeratorsResult, 
                            $tierNameField
                    );
                    
                    //denominator section
                    $dateWhere = "frr.date = '$latestDate'";
                    $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                                        
                    $denominatorsResult = $facility->getReportingFacsOvertimeByLocation(
                            $longWhereClause, 
                            $geoList, 
                            $tierNameField, 
                            $tierIDField
                    );
                    
                    $locationNames = $helper->getLocationNames($geoList);
                    $denominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $denominatorsResult, 
                            $tierNameField
                    );
                    
                    /*********************************************************************************
                     * denominator for FP facilites: consumed 1 FP commodity in last 6 months
                     ********************************************************************************/
                    $sixMonthsDateWhere = "(date BETWEEN '" . 
                            date("Y-m-d", strtotime("$latestDate -5 months")) . "' AND '$latestDate')";
                    $dateWhere = "c.date = '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $locationWhere;
                    
                    $FPFacsDenominatorsResult = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierNameField, 
                            $tierIDField, 
                            $ct_where,
                            $latestDate
                    );
                    $locationNames = $helper->getLocationNames($geoList);
                    $FPFacsDenominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $FPFacsDenominatorsResult, 
                            $tierNameField
                    );
                    //var_dump($FPFacsDenominators); exit;
                    
                    //set output - num/denom(All facs)                 
                    $sumsArray = $helper->sumNumersAndDenomsWithValues($numerators, $denominators);
                    $output['allfacs'] = array_merge($output['allfacs'], $sumsArray['output']);
                    $output['allfacs']['National'] = $sumsArray['national'];
                    
                    //set fpfacsoutput - num/denom(fp facs only)                 
                    $sumsArray = $helper->sumNumersAndDenomsWithValues($numerators, $FPFacsDenominators);
                    $output['fpfacs'] = array_merge($output['allfacs'], $sumsArray['output']);
                    $output['fpfacs']['National'] = $sumsArray['national'];
                    
                    //check if to save month national data
                    $alias = CacheManager::PERCENT_FACS_PROVIDING_3_METHODS;
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert                    
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities providing three methods',
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
                        //get month national data and put in first array element
                        /**
                         * Helps to get the cached national value for older months. 
                         * We get the latest DHIS download date as pull date not filter date
                         * This will help us to get the national values even for months that there are no cached data
                         * if all the previous 12 months have cached data for this indicator, then we can use the 
                         * selected filter date for this call
                         */
                        $cacheValue = $cacheManager->getIndicator(
                                CacheManager::PERCENT_FACS_PROVIDING_3_METHODS_OVERTIME, 
                                $helper->getLatestPullDate()
                        );
                        $cacheValue = json_decode($cacheValue, true);
                        $monthToSelect = date('F', strtotime($latestDate));
                        if($cacheValue){
                            $output['allfacs']['National']['percent'] = $cacheValue['allfacs'][$monthToSelect]['National']['percent'];
                            $output['fpfacs']['National']['percent'] = $cacheValue['fpfacs'][$monthToSelect]['National']['percent'];
                        }
                    }
            }

            //set national ave
            //var_dump($output); exit;
            return $output;
            
    }
    
    
    
    
    /**
     * Calculates the percentage of facilities that provided up to a number of products in a period
     * e.g. percentage of facilities providing at least 3 commodities in a period
     * 
     * @param type $commodity_type
     * @param type $numberOfMethods
     * @param type $geoList
     * @param type $tierValue
     * @param type $freshVisit
     * @param type $updateMode
     * @param type $lastPullDate
     * @return array of results by month
     */
    public function fetchFacsProvidingNumberOfMethodsOvertime($commodity_type, $numberOfMethods, 
            $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDatemultiple=[]){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();

            //$output = array(array('location'=>'National', 'percent'=>0)); 
            //$output['allfacs'][$monthName]['National']['percent'] = 0;
            //$output['allfacs'][$monthName]['National']['numer'] = 0;
            //$output['allfacs'][$monthName]['National']['denom'] = 0; 
            
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
            
//            if(empty($lastPullDatemultiple) || $lastPullDatemultiple==""){
//              $lastPullDatemultiple = $helper->getPreviousMonthDates(12);
//             }else{
//              $latestDate = $lastPullDatemultiple;
//             }
                    
            $cacheManager = new CacheManager();
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_3_METHODS_OVERTIME, $latestDate);

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
                    
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $locationWhere;
                    
                    $coverageHelper = new CoverageHelper();
                    $facility = new Facility();
                    
                    $numeratorsResult = $facility->getFacsProvidingNumberOfMethodsOvertime(
                            $numberOfMethods, 
                            $longWhereClause,
                            $geoList,
                            $tierNameField,
                            $tierIDField,
                            $subDateWhere, 
                            $ct_where
                    );

                    $locationNames = $helper->getLocationNames($geoList); 
                    $monthNames = !empty($lastPullDatemultiple) ? 
                                    array_reverse($helper->formatMonthName($lastPullDatemultiple)) : 
                                    array_reverse($helper->getPreviousMonthNames(12)); 
                    
                    //add all missing months for each location in the numerator list
                    $numerators = $this->addMissingMonths($numeratorsResult, $monthNames, $locationNames, $tierNameField); 
                                     
                    /*********************************************************************************
                     * denominator for ALL Reporting facilites
                     ********************************************************************************/
                    if(empty($lastPullDatemultiple)){  
                        $FRRDateWhere = "frr.date BETWEEN '" . 
                                date("Y-m-d", strtotime("$latestDate -11 months")) . "' AND '$latestDate'";
                    }else{
                        $FRRDateWhere = 'frr.date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                    }
                    //$dateWhere = "frr.date BETWEEN '" . 
                      //          date("Y-m-d", strtotime("$latestDate -11 months")) . "' AND '$latestDate'";
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
                    $sixMonthsDateWhere = "(date BETWEEN '" . 
                                date("Y-m-d", strtotime("$latestDate -5 months")) . "' AND '$latestDate')";
                    //$dateWhere = "c.date BETWEEN '" . 
                      //          date("Y-m-d", strtotime("$latestDate -11 months")) . "' AND '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
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
                    $alias = CacheManager::PERCENT_FACS_PROVIDING_3_METHODS_OVERTIME;
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert                    
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities providing three methods overtime',
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
                                CacheManager::PERCENT_FACS_PROVIDING_3_METHODS_OVERTIME, 
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
            //var_dump($output['fpfacs']); exit;
            return $output;
            
    }
    
    
}
