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
require_once('IndicatorGroup.php');
require_once('CoverageHelper.php');
require_once('CoverageNationalHelper.php');
require_once('Stockout.php');
require_once('StockoutHelper.php');
require_once 'CacheManager.php';

class Coverage extends IndicatorGroup {
    //put your code here
    
    
    /*
     * TA:17:17: 01/15/2015
     * get trained persons details
     DB query to take number of HW trained in �LARC� in 2014

     select count(distinct person_to_training.person_id) from person_to_training
     left join training on training.id = person_to_training.training_id
     where training.training_title_option_id=1 and training.training_end_date like '2014%';
     */
    public function fetchCummulativeTrainedWorkers($year_amount, $geoList, $tierValue,$lastPullDate="") {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $output = array ();
        $helper = new Helper2();
        $trainingTypesArray = array('fp', 'larc');
        
        //get the last DHIS2 pull date from commodity table and use the year for year here
        //$latestDate = $helper->getPreviousMonthDates(1);
        
        if(empty($lastPullDate) || $lastPullDate==""){
            $latestDate = $helper->getLatestPullDate();
        }else{
            $latestDate = $lastPullDate;
        }
        
        //echo "This is the latest pull date".$latestDate;exit;
        $year = date('Y', strtotime($latestDate));
        
        $tierText = $helper->getLocationTierText($tierValue);
        $tierFieldName = $helper->getTierFieldName($tierText);
                

        for($i = $year_amount; $i > 0; $i--) {
            $data = array ();
            //$endDateWhere = "t.training_end_date like '" . $year . "%'";
            $endDateWhere = "YEAR(t.training_end_date) <= '" . $year . "'";
            
            foreach ($trainingTypesArray as $training_type){
                //$trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                if($training_type == 'fp') 
                    $trainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc') AND tto.is_deleted=0";
//                    //$trainingTypeWhere = "(tto.system_training_type = 'fp' AND tto.is_deleted=0) OR (tto.system_training_type = 'larc' AND tto.is_deleted=0)";
                else if($training_type == 'larc') 
                    $trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                
                
                
                //$trainingTypeWhere = "tto.system_training_type IN (" . $training_type . ") AND tto.is_deleted=0";
                $trainingWhere = "t.is_deleted = 0";
                $longWhereClause = $endDateWhere . ' AND ' . $trainingTypeWhere . ' AND ' . 
                                   $trainingWhere . ' AND ' . $tierFieldName . ' IN (' . $geoList . ')';
                
                $select = $db->select ()
                        ->from ( array ('p' => 'person' ), array ('COUNT(DISTINCT(p.id)) as count'))
                        ->joinInner(array('ptt'=>'person_to_training'), 'ptt.person_id=p.id', array())
                        ->joinInner(array ('t' => "training" ), "t.id = ptt.training_id", array())
                        ->joinInner(array('tto' => 'training_title_option' ), 'tto.id = t.training_title_option_id', array())
                        //->joinInner(array ('flv' => "facility_location_view" ), 'flv.id = p.facility_id', array('flv.lga', 'flv.state', 'flv.geo_zone') )
                        ->joinInner(array ('flv' => "facility_location_view" ), 'flv.id = p.facility_id', array())
                        ->where($longWhereClause)
                        ->order(array($tierText));
                
                //echo $select->__toString(); exit;
                //$helper->log($select->__toString());

                $result = $db->fetchAll( $select );
                $data = $result [0] ['count'];

                
                //if($training_type == 'fp')
                  //  $data = $data + $output[$year]['larc'];
                
                $output[$year][$training_type] = $data;
                
                //var_dump($output);
                //echo '<br/><br//>';
                
            }//end inner loop
            
            $year--;
        }//outer loop

        ksort($output); 
        
        //var_dump($output); exit;
        return $output;
}


        /* TP:
         * This method gets the count of coverage of trained workers in various 
         * geo-locations and tiers. Both FP and LARC
         */
        public function fetchCummulativeTrainedWorkersByLocation($training_type, $year_amount, $geoList, $tierValue,$lastPullDate=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $output = array (); 
            $helper = new Helper2();
            
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            $locationNames = $helper->getLocationNames($geoList);
            if($training_type == 'fp') $training_type = "'fp','larc'"; else if($training_type == 'larc') $training_type = "'larc'";
            //var_dump($locationNames); exit;

            //get the last DHIS2 pull date from commodity table and use the year for year here
            //$latestDate = $helper->getLatestPullDate();
            if(empty($lastPullDate) || $lastPullDate==""){
            $latestDate = $helper->getLatestPullDate();
            }else{
            $latestDate = $lastPullDate;
            }
            $year = date('Y', strtotime($latestDate));

            $coverageHelper = new CoverageHelper();
            //$larcCoverage = $coverageHelper->getTrainedHWCoverageCount('larc', $year,$year_amount, $locationWhereClause, $locationNames, $groupFieldName, $havingName, $geoList, $tierText);
            //$larcCoverage = $coverageHelper->getTrainedHWByLocationCount('larc', $year, $year_amount, $locationNames, $geoList, $tierText, $tierFieldName);
            //$fpCoverage = $coverageHelper->getTrainedHWByLocationCount('fp', $year, $year_amount, $locationNames, $geoList, $tierText, $tierFieldName);
            
            $coverageByLocation = $coverageHelper->getTrainedHWByLocationCount($training_type, $year, $year_amount, $locationNames, $geoList, $tierText, $tierFieldName);
            return $coverageByLocation;
        }
        
        
        
        public function fetchPercentFacHWTrained($training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){            
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2();
                
                $cacheManager = new CacheManager();
              // echo $lastPullDate;
                //$latestDate = $helper->getLatestPullDate();
                if(empty($lastPullDate) || $lastPullDate==""){
                $latestDate = $helper->getLatestPullDate();
                }else{
                $latestDate = $lastPullDate;
                 }
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_LARC, $latestDate);
                
//                echo $cacheValue;
//                $freshVisit=false;
//               $updateMode = true;
                
                //echo $latestDate;
               
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
                    
                    if(empty($lastPullDate) || $lastPullDate==""){
                    $latestDate = $helper->getLatestPullDate();
                    }else{
                    $latestDate = $lastPullDate;
                    }
                    //echo "This is the new fetch place not using the freshvisit".$lastPullDate.$latestDate;
                    //$latestDate = $helper->getLatestPullDate();

//                    //where clauses
//                    if($training_type == 'fp')
//                        $tt_where = "fptrained > 0";
//                    else if($training_type == 'larc')
//                        $tt_where = 'larctrained > 0';

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $highestDate = date('Y-m-t', strtotime($latestDate));
                    $endDateWhere = "t.training_end_date <= '" . $highestDate . "'";
                    
                    if($training_type == 'fp') 
                        $trainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc') AND tto.is_deleted=0";
                    else if($training_type == 'larc') 
                        $trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    
                    //$trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    $trainingWhere = "t.is_deleted = 0";
                    $personWhere = "p.is_deleted = 0";
                    
                    $longWhereClause = $endDateWhere . ' AND ' . $trainingTypeWhere . ' AND ' . 
                                       $trainingWhere . ' AND ' . $personWhere . ' AND ' . $locationWhere;
                    
                    //$longWhereClause = $tt_where . ' AND ' . $locationWhere;
               // echo $longWhereClause;
                    $coverageHelper = new CoverageHelper();                
                    $facility = new Facility();
                    
                    $numerators = $coverageHelper->getFacWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                   // var_dump($numerators); echo '<br><br>'; 
                    //echo 'after numerator<br>';
                    //$denominators = $coverageHelper->getFacWithTrainedHWCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                    $denominators = $facility->getFacilityCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                   // var_dump($denominators);
                    $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
                    $output = array_merge($output, $sumsArray['output']);
                    $output[0]['percent'] = $sumsArray['nationalAvg'];
                    //echo 'after sums<br>';  var_dump($output); exit;
                    
                    //do cache insert
                    if($training_type == 'fp')
                        $alias = CacheManager::PERCENT_FACS_TRAINED_FP;
                    else if($training_type == 'larc')
                        $alias = CacheManager::PERCENT_FACS_TRAINED_LARC;

                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month    
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities with a trained HW',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
                    else if($updateMode){
                        $dataArray = array('value' => json_encode($output));

                        $where = "indicator_alias='$alias'";

                        $cacheManager->updateIndicator($dataArray, $where);
                    }
                    else{
                        //get month national data and put in first array element
                        $cacheValue = json_decode($cacheValue, true);
                        if($cacheValue)
                            $output[0]['percent'] = $cacheValue[0]['percent'];
                    }
                }
                //echo $latestDate;
                
                //var_dump($output); exit;
                return $output;
        }
        
        
         public function fetchPercentFacHWTrainedNumeratorDenominator($training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){            
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $output = array(array('location'=>'National', 'percent'=>0));
               
                $helper = new Helper2();
                
                $cacheManager = new CacheManager();
              // echo $lastPullDate;
                //$latestDate = $helper->getLatestPullDate();
                if(empty($lastPullDate) || $lastPullDate==""){
                $latestDate = $helper->getLatestPullDate();
                }else{
                $latestDate = $lastPullDate;
                 }
           
//                $freshVisit=false;
//               $updateMode = true;
                
                //echo $latestDate;
              
                   
                    //needed variables
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    
                    if(empty($lastPullDate) || $lastPullDate==""){
                    $latestDate = $helper->getLatestPullDate();
                    }else{
                    $latestDate = $lastPullDate;
                    }
                    //echo "This is the new fetch place not using the freshvisit".$lastPullDate.$latestDate;
                    //$latestDate = $helper->getLatestPullDate();

//                    //where clauses
//                    if($training_type == 'fp')
//                        $tt_where = "fptrained > 0";
//                    else if($training_type == 'larc')
//                        $tt_where = 'larctrained > 0';

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    $highestDate = date('Y-m-t', strtotime($latestDate));
                    $endDateWhere = "t.training_end_date <= '" . $highestDate . "'";
                    
                    if($training_type == 'fp') 
                        $trainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc') AND tto.is_deleted=0";
                    else if($training_type == 'larc') 
                        $trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    
                    //$trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    $trainingWhere = "t.is_deleted = 0";
                    $personWhere = "p.is_deleted = 0";
                    
                    $longWhereClause = $endDateWhere . ' AND ' . $trainingTypeWhere . ' AND ' . 
                                       $trainingWhere . ' AND ' . $personWhere . ' AND ' . $locationWhere;
                    
                    //$longWhereClause = $tt_where . ' AND ' . $locationWhere;
               // echo $longWhereClause;
                    $coverageHelper = new CoverageHelper();                
                    $facility = new Facility();
                    
                    $numerators = $coverageHelper->getFacWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                    
                   // var_dump($numerators); echo '<br><br>'; 
                    //echo 'after numerator<br>';
                    //$denominators = $coverageHelper->getFacWithTrainedHWCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                    $denominators = $facility->getFacilityCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                   // var_dump($denominators);
                   //$sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
                   list($finalNum,$finalDenom) = $helper->addNationalNumersAndDenoms($numerators,$denominators);
                    
                return array($finalNum,$finalDenom);
        }
        
        
        
        
        public function fetchPercentFacHWTrainedPerState($training_type){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2();
                
                $tierValue = 2;
                $geoList = $helper->getLocationTierIDs($tierValue);
                $geoList = implode(',',$geoList);
                $freshVisit = false;
                
                $cacheManager = new CacheManager();
            
                $latestDate = $helper->getLatestPullDate();
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_TRAINED_LARC, $latestDate);
                
                //needed variables
                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);
                $highestDate = date('Y-m-t', strtotime($latestDate));
                $endDateWhere = "t.training_end_date <= '" . $highestDate . "'";
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                //$latestDate = $helper->getLatestPullDate();

                 if($training_type == 'fp') 
                        $trainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc') AND tto.is_deleted=0";
                 else if($training_type == 'larc') 
                        $trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    
                    //$trainingTypeWhere = "tto.system_training_type = '" . $training_type . "' AND tto.is_deleted=0";
                    $trainingWhere = "t.is_deleted = 0";
                    $personWhere = "p.is_deleted = 0";
                    
                    
                    
                $longWhereClause = $endDateWhere . ' AND ' . $trainingTypeWhere . ' AND ' . 
                                       $trainingWhere . ' AND ' . $personWhere . ' AND ' . $locationWhere;
                    

                $coverageHelper = new CoverageHelper();                
                $facility = new Facility();

                $numerators = $coverageHelper->getFacWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                //$denominators = $coverageHelper->getFacWithTrainedHWCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                $denominators = $facility->getFacilityCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
                    
                    
                $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
                
                //$arrayToSort = array_slice($sumsArray['output']);
                $sortedArray = $helper->msort($sumsArray['output']);

                //get month national data and put in first array element
                $cacheValue = json_decode($cacheValue, true);
                if($cacheValue)
                    $output[0]['percent'] = $cacheValue[0]['percent'];
                
                $output = array_merge($output, $sortedArray);
                
                //var_dump($output); exit;
                return $output;
        }

    
        
     /*
     * Percentage facilities providing FP, LARC and Injectables in the current month
     */
      public function fetchPercentFacsProviding($commodity_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $facility = new Facility();
            $output = array(array('location'=>'National', 'percent'=>0)); 
            $helper = new Helper2();
//            $updateMode = true;
//            $freshVisit = true;
            
            if(empty($lastPullDate) || $lastPullDate==""){
              $latestDate = $helper->getLatestPullDate();
             }else{
              $latestDate = $lastPullDate;
             }
            
            $cacheManager = new CacheManager();
            
            if($commodity_type == 'fp')
                $cacheValue =  $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_FP, $latestDate);
            else if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_LARC, $latestDate);
            else if($commodity_type == 'injectables')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_INJECTABLES, $latestDate);
            
            
            //$cacheValue = null;
            
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
                    //where clauses
                    if($commodity_type == 'fp')
                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    else if($commodity_type == 'larc')
                        $ct_where = "commodity_type = 'larc'";
                    else if ($commodity_type == 'injectables')
                        $ct_where = "commodity_alias = 'injectables'";

                    $dateWhere = "c.date = '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $consumptionWhere = 'consumption > 0';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                    $coverageHelper = new CoverageHelper();
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                    $numerators = $coverageHelper->getFacProvidingCount($longWhereClause, $geoList, $tierText, $tierFieldName);

//                    $dateWhere = "frr.date = '$latestDate'";
//                    $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                    
                    
                    
                    $reportingWhere = 'facility_reporting_status = 1';
//                  //  $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
//                    
                  
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere.' AND '.$locationWhere;
//                    //send only one month date range. 
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    //$dateWhere = "c_sub.date = '$latestDate'";
//                    $tierNameField = $helper->getLocationTierText($tierValue);
//                    $tierIDField = $helper->getTierFieldName($tierNameField);
                    
                    $FPFacsDenominatorsResult = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierText, 
                            $tierFieldName, 
                            $ct_where,
                            $latestDate
                    );
//                    
//                    
                    //$locationNames = $helper->getLocationNames($geoList);
                    $FPFacsDenominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $FPFacsDenominatorsResult, 
                            $tierText
                    );
                    
                  // print_r($FPFacsDenominatorsResult);
//                    echo '<br/><br/>';
                    $denominators = $FPFacsDenominators;
                    //send only one month date range. 
                    //$denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);

                    //set output                    
                    $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
                    $output = array_merge($output, $sumsArray['output']);
                    $output[0]['percent'] = $sumsArray['nationalAvg'];

                    //this is the test of the coverage file here .
                   // Helper2::jLog('THis is the output daa '.$output[0]['percent'].'inside the else');
                    
                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert
                        if($commodity_type == 'fp')
                            $alias = CacheManager::PERCENT_FACS_PROVIDING_FP;
                        else if($commodity_type == 'larc')
                            $alias = CacheManager::PERCENT_FACS_PROVIDING_LARC;
                        else if($commodity_type == 'injectables')
                            $alias = CacheManager::PERCENT_FACS_PROVIDING_INJECTABLES;
                        
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities providing LARC, FP or injectables',
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
                        $cacheValue = json_decode($cacheValue, true);
                        if($cacheValue)
                            $output[0]['percent'] = $cacheValue[0]['percent'];
                    }
            }

            //set national ave
            //var_dump($output); exit;
            return $output;

        }
      
      public function fetchPercentFacsProvidingNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $facility = new Facility();
            $output = array(array('location'=>'National', 'percent'=>0)); 
            $helper = new Helper2(); 
            
            if(empty($lastPullDate) || $lastPullDate==""){
              $latestDate = $helper->getLatestPullDate();
             }else{
              $latestDate = $lastPullDate;
             }
            
            $cacheManager = new CacheManager();
            
            if($commodity_type == 'fp')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_FP, $latestDate);
            else if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_LARC, $latestDate);
            else if($commodity_type == 'injectables')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_INJECTABLES, $latestDate);
            
            
            $cacheValue = null;
            
          
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                     $locationNames = $helper->getLocationNames($geoList);
                    //where clauses
                    if($commodity_type == 'fp')
                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    else if($commodity_type == 'larc')
                        $ct_where = "commodity_type = 'larc'";
                    else if ($commodity_type == 'injectables')
                        $ct_where = "commodity_alias = 'injectables'";

                    $dateWhere = "c.date = '$latestDate'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $consumptionWhere = 'consumption > 0';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                    $coverageHelper = new CoverageHelper();
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                    $numerators = $coverageHelper->getFacProvidingCount($longWhereClause, $geoList, $tierText, $tierFieldName);
$reportingWhere = 'facility_reporting_status = 1';
//                  //  $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
//                    
                  
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere.' AND '.$locationWhere;
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
//                    //send only one month date range. 
//                    
//                    $tierNameField = $helper->getLocationTierText($tierValue);
//                    $tierIDField = $helper->getTierFieldName($tierNameField);
                    
                    $FPFacsDenominatorsResult = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierText, 
                            $tierFieldName, 
                            $ct_where,
                            $latestDate
                    );
//                    
//                    
                    //$locationNames = $helper->getLocationNames($geoList);
                    $FPFacsDenominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $FPFacsDenominatorsResult, 
                            $tierText
                    );
                    
                  // print_r($FPFacsDenominatorsResult);
//                    echo '<br/><br/>';
                    $denominators = $FPFacsDenominators;
                   // $denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);

                    //set output                    
                   // $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
//                    $output = array_merge($output, $sumsArray['output']);
//                    $output[0]['percent'] = $sumsArray['nationalAvg'];

                     list($finalNum,$finalDenom) = $helper->addNationalNumersAndDenoms($numerators,$denominators);
                    
                return array($finalNum,$finalDenom);
                   
       }
       
        
        /*
         * MARKED FOR DELETION
         * Percentage facilities providing at least 3 modern methods in the current month
         */
//       public function fetchPercentFacsProvidingAllMethods($commodity_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
//            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
//
//            $output = array(array('location'=>'National', 'percent'=>0)); 
//            $helper = new Helper2();
//            if(empty($lastPullDate) || $lastPullDate==""){
//              $latestDate = $helper->getLatestPullDate();
//             }else{
//              $latestDate = $lastPullDate;
//             }
//            
//            $cacheManager = new CacheManager();
//            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_ALL_METHODS, $latestDate);
//           //$cacheValue =  null;
//           
//            if($cacheValue && $freshVisit){ 
//                $output = json_decode($cacheValue, true);
//            }
//            else{
//                    $tierText = $helper->getLocationTierText($tierValue);
//                    $tierFieldName = $helper->getTierFieldName($tierText);
//
//                    //where clauses
//                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
//                    
//
//                    $dateWhere = "c.date = '$latestDate'";
//                    $reportingWhere = 'facility_reporting_status = 1';
//                    $consumptionWhere = 'csum.sumcons >= 3';
//                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
//
//                    $coverageHelper = new CoverageHelper();
//                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
//                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
//                    $numerators = $coverageHelper->getFacProvidingAllMethodCount($longWhereClause, $geoList, $tierText, $tierFieldName,$latestDate);
//
//                    $dateWhere = "frr.date = '$latestDate'";
//                    $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
//                    
//                    //send only one month date range. 
//                    $denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
//                    
//                    //set output                    
//                    $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
//                    $output = array_merge($output, $sumsArray['output']);
//                    $output[0]['percent'] = $sumsArray['nationalAvg'];
//
//                    //this is the test of the coverage file here .
//                   // Helper2::jLog('THis is the output daa '.$output[0]['percent'].'inside the else');
//                    
//                    //check if to save month national data
//                    if(!$cacheValue && $freshVisit){ //fresh in month
//                        //do cache insert
//                       
//                            $alias = CacheManager::PERCENT_FACS_PROVIDING_ALL_METHODS;
//                     
//                        
//                        $dataArray = array(
//                            'date_cached'=> $latestDate,
//                            'indicator' => 'Percent of facilities providing all modern methods',
//                            'indicator_alias' => $alias,
//                            'value' => json_encode($output)
//                            //'timestamp_created' => date('');
//                        );
//                        $cacheManager->setIndicator($dataArray);
//                    }
//                    else if($updateMode){
//                        $dataArray = array('value' => json_encode($output));
//
//                        $where = "indicator_alias='$alias'";
//
//                        $cacheManager->updateIndicator($dataArray, $where);
//                    }
//                    else{ //inner if
//                        //get month national data and put in first array element
//                        $cacheValue = json_decode($cacheValue, true);
//                        if($cacheValue)
//                            $output[0]['percent'] = $cacheValue[0]['percent'];
//                    }
//            }
//
//            //set national ave
//            //var_dump($output); exit;
//            return $output;
//
//        }
        
       
    
       /**
        * MARKED FOR DELETION
        * Numerators and denominators for Facilities providing any 3
        * @param type $commodity_type
        * @param type $geoList
        * @param type $tierValue
        * @param type $freshVisit
        * @param type $updateMode
        * @param type $lastPullDate
        * @return type
        */
//       public function fetchPercentFacsProvidingAllMethodsNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
//            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
//
//            $output = array(array('location'=>'National', 'percent'=>0)); 
//            $helper = new Helper2();
//            if(empty($lastPullDate) || $lastPullDate==""){
//              $latestDate = $helper->getLatestPullDate();
//             }else{
//              $latestDate = $lastPullDate;
//             }
//            
//            $cacheManager = new CacheManager();
//            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_ALL_METHODS, $latestDate);
//           
//            
//            
//            $cacheValue = null;
//            
//          
//                    $tierText = $helper->getLocationTierText($tierValue);
//                    $tierFieldName = $helper->getTierFieldName($tierText);
//
//                    //where clauses
//                    
//                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc' OR commodity_alias = 'injectables' )";
//                   
//
//                    $dateWhere = "c.date = '$latestDate'";
//                    $reportingWhere = 'facility_reporting_status = 1';
//                    $consumptionWhere = 'csum.sumcons >= 3';
//                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
//
//                    $coverageHelper = new CoverageHelper();
//                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
//                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
//                    $numerators = $coverageHelper->getFacProvidingAllMethodCount($longWhereClause, $geoList, $tierText, $tierFieldName,$latestDate);
//
//                    $dateWhere = "frr.date = '$latestDate'";
//                    $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
//                    
//                    //send only one month date range. 
//                    $denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
//
//                    //set output                    
//                   // $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
////                    $output = array_merge($output, $sumsArray['output']);
////                    $output[0]['percent'] = $sumsArray['nationalAvg'];
//
//                     list($finalNum,$finalDenom) = $helper->addNationalNumersAndDenoms($numerators,$denominators);
//                    
//                return array($finalNum,$finalDenom);
//                   
//       }
       
        
     /*
     * Percentage facilities providing FP, LARC nationally per state
     */
      public function fetchPercentFacsProvidingPerState($commodity_type){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();

            $output = array(array('location'=>'National', 'percent'=>0)); 
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
            
            $tierValue = 2;
            $geoList = $helper->getLocationTierIDs($tierValue);
            $geoList = implode(',',$geoList);
            $freshVisit = false;
            
            $cacheManager = new CacheManager();
            
            if($commodity_type == 'fp')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_FP, $latestDate);
            else if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_LARC, $latestDate);            
            
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);

            //where clauses
            if($commodity_type == 'fp')
                $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
            else if($commodity_type == 'larc')
                $ct_where = "commodity_type = 'larc'";

            $dateWhere = "c.date = '$latestDate'";
            $reportingWhere = 'facility_reporting_status = 1';
            $consumptionWhere = 'consumption > 0';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

            $coverageHelper = new CoverageHelper();
            $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                               $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
            $numerators = $coverageHelper->getFacProvidingCount($longWhereClause, $geoList, $tierText, $tierFieldName);

            $dateWhere = "frr.date = '$latestDate'";
            $longWhereClause = $dateWhere . ' AND ' . $locationWhere;

            //send only one month date range. 
            $denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);

            //set output                    
            $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
            
            $arrayToSort = array_slice($sumsArray['output'], 1);
            $sortedArray = $helper->msort($arrayToSort);

            //get month national data and put in first array element
            $cacheValue = json_decode($cacheValue, true);
            if($cacheValue)
                $output[0]['percent'] = $cacheValue[0]['percent'];
            
            $output = array_merge($output, $sortedArray);

            //set national ave
            //var_dump($output); exit;
            return $output;

        }


      //public function fetchPercentFacHWTrainedProvidingDetails($commodity_type, $training_type, &$locationNames, $where, $groupFieldName, $havingName, $geoList, $tierValue){
      public function   fetchFacsWithHWProviding($commodity_type, $training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
          
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $facility = new Facility();
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2();
                if(empty($lastPullDate) || $lastPullDate==""){
                $latestDate = $helper->getLatestPullDate();
                }else{
                $latestDate = $lastPullDate;
                }
                
                $cacheManager = new CacheManager();
            
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_LARC, $latestDate);


                //$cacheValue = null;
                
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
                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')"; //this one has been since phase 1
                    else if($commodity_type == 'larc')
                        $ct_where = "commodity_type = 'larc'";

                    //training type where
                    if($training_type == 'fp')
                        $tt_where = "(fptrained > 0 OR larctrained > 0)";
                    else if($commodity_type == 'larc')
                        $tt_where = "(larctrained > 0)";
                    

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                    $coverageHelper = new CoverageHelper();

                    //concatenate conditions for numerators
                    $longWhereClause = $consumptionWhere . ' AND ' . $reportingWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $tt_where . ' AND ' . $locationWhere . ' AND ' .
                                       $dateWhere;
                    $numerators = $coverageHelper->getCoverageCountFacWithHWProviding($longWhereClause, $locationNames, $geoList, $tierText, $tierFieldName);
                   // print_r($numerators);exit;
                    //concatenate conditions for denominators
                   
                  //  $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                   $reportingWhere = 'facility_reporting_status = 1';
//                  //  $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
//                    
                  
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere.' AND '.$locationWhere;
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
//                    //send only one month date range. 
//                    
//                    $tierNameField = $helper->getLocationTierText($tierValue);
//                    $tierIDField = $helper->getTierFieldName($tierNameField);
                    
                    $FPFacsDenominatorsResult = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierText, 
                            $tierFieldName, 
                            $ct_where,
                            $latestDate
                    );
//                    
//                    
                    //$locationNames = $helper->getLocationNames($geoList);
                    $FPFacsDenominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $FPFacsDenominatorsResult, 
                            $tierText
                    );
                    
                  // print_r($FPFacsDenominatorsResult);
//                    echo '<br/><br/>';
                    $denominators = $FPFacsDenominators; //$helper->getReportingFacsWithTrainedHWOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                    
                   // print_r($denominators);exit;
                    //set output       
                    $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
                    $output = array_merge($output, $sumsArray['output']);
                    $output[0]['percent'] = $sumsArray['nationalAvg'];
                    
                    //do cache insert
                    if($training_type == 'fp')
                        $alias = CacheManager::PERCENT_FACS_HW_PROVIDING_FP;
                    else if($training_type == 'larc')
                        $alias = CacheManager::PERCENT_FACS_HW_PROVIDING_LARC;
                    
                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of Facilities with a trained HW providing FP/LARC',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
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
                        $cacheValue = json_decode($cacheValue, true);
                        if($cacheValue)
                            $output[0]['percent'] = $cacheValue[0]['percent'];
                    }
                }
                    
                //echo '<br/><br/>';
                //var_dump($output); exit;
                return $output;
     }
     
       //public function fetchPercentFacHWTrainedProvidingDetails($commodity_type, $training_type, &$locationNames, $where, $groupFieldName, $havingName, $geoList, $tierValue){
      public function   fetchFacsWithHWProvidingNumeratorDenominator($commodity_type, $training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
          
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                $facility = new Facility();
                $output = array(array('location'=>'National', 'percent'=>0));
                $helper = new Helper2();
                if(empty($lastPullDate) || $lastPullDate==""){
                $latestDate = $helper->getLatestPullDate();
                }else{
                $latestDate = $lastPullDate;
                }
                
                $cacheManager = new CacheManager();
            
                if($training_type == 'fp')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_FP, $latestDate);
                else if($training_type == 'larc')
                    $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_HW_PROVIDING_LARC, $latestDate);


                //$cacheValue = null;
                
            
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    $locationNames = $helper->getLocationNames($geoList);
                    $consumptionWhere = 'consumption > 0';
                    $reportingWhere = 'facility_reporting_status = 1';

                    $dateWhere = "c.date = '$latestDate'";

                    //commodity type where
                    if($commodity_type == 'fp')
                        $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')"; //this one has been since phase 1
                    else if($commodity_type == 'larc')
                        $ct_where = "commodity_type = 'larc'";

                    //training type where
                    if($training_type == 'fp')
                        $tt_where = "(fptrained > 0 OR larctrained > 0)";
                    else if($commodity_type == 'larc')
                        $tt_where = "(larctrained > 0)";
                    

                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                    $coverageHelper = new CoverageHelper();

                    //concatenate conditions for numerators
                    $longWhereClause = $consumptionWhere . ' AND ' . $reportingWhere . ' AND ' . 
                                       $ct_where . ' AND ' . $tt_where . ' AND ' . $locationWhere . ' AND ' .
                                       $dateWhere;
                    $numerators = $coverageHelper->getCoverageCountFacWithHWProviding($longWhereClause, $locationNames, $geoList, $tierText, $tierFieldName);
                    
                    //concatenate conditions for denominators
//                    $dateWhere = "frr.date = '$latestDate'";
//                    $longWhereClause = $tt_where . ' AND ' . $dateWhere . ' AND ' . $locationWhere;

                    //send only one month date range. 
                    
                    $reportingWhere = 'facility_reporting_status = 1';
//                  //  $locationWhere = $tierIDField . ' IN (' . $geoList . ')';
//                    
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere.' AND '.$locationWhere;
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
//                    //send only one month date range. 
//                    
//                    $tierNameField = $helper->getLocationTierText($tierValue);
//                    $tierIDField = $helper->getTierFieldName($tierNameField);
                    
                    $FPFacsDenominatorsResult = $facility->getFPFacilities(
                            $longWhereClause, 
                            $geoList, 
                            $tierText, 
                            $tierFieldName, 
                            $ct_where,
                            $latestDate
                    );
//                    
//                    
                    $locationNames = $helper->getLocationNames($geoList);
                    $FPFacsDenominators = $coverageHelper->filterLocations(
                            $locationNames, 
                            $FPFacsDenominatorsResult, 
                            $tierText
                    );
                    
                    $denominators = $FPFacsDenominators;
                   // $denominators = $helper->getReportingFacsWithTrainedHWOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);
                    
                     list($finalNum,$finalDenom) = $helper->addNationalNumersAndDenoms($numerators,$denominators);
                   
                   return array($finalNum,$finalDenom);
     }
     
    public function fetchHWCoverageOvertime($training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDatemultiple=array()){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        
        $ouput = array();
        $helper = new Helper2();
        $latestDate = $helper->getLatestPullDate();
                
        
        $cacheManager = new CacheManager();

        if($training_type == 'fp')
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_COVERAGE_OVERTIME_FP, $latestDate);
        else if($training_type == 'larc')
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_COVERAGE_OVERTIME_LARC, $latestDate);
        
        $cacheValue = null;
        
        //check if page is just being loaded
        //fresh session, month data already registered
        //just retrieve registered data
        if($cacheValue && $freshVisit){ 
            //echo 'cached and fresh'; exit;
            $output = json_decode($cacheValue, true);
        }
        else{
            //echo 'no cache'; exit;
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            
            //where clauses
            if($training_type == 'fp'){
                $systemTrainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc')";
                $tt_where = "(fptrained > 0 OR larctrained > 0)";
                $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                $so_alias_where = "commodity_alias = 'so_fp_seven_days'";
            }
            else if($training_type == 'larc'){
                $systemTrainingTypeWhere = "tto.system_training_type = 'larc'";
                $tt_where = 'larctrained > 0';
                $ct_where = "commodity_type = 'larc'";
                $so_alias_where = "commodity_alias = 'so_implants'";
            }
            //print 'after where<br>';
            

            $coverageHelper = new CoverageHelper();                
            //($lastPullDatemultiple);exit;
            if(empty($lastPullDatemultiple)){
                    
            $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
            }else{
                   
             $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
            
            
            $consmptionWhere = 'consumption > 0';
            $reportingWhere = 'facility_reporting_status = 1';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            $stockoutWhere = "stock_out='Y'";
            
            //hw                                        
            //numerator:
            //$facsWithTrainedHWMonthlyNumers = $coverageHelper->getCummulativeTrainedFacsMonthly(12, $training_type, $geoList, $tierText, $tierFieldName );
            $facsWithTrainedHWMonthlyNumers = $coverageHelper->getCummulativeTrainedFacsMonthly(12, $systemTrainingTypeWhere, $geoList, $tierText, $tierFieldName,$lastPullDatemultiple);
            //print_r($facsWithTrainedHWMonthlyNumers);exit;
            //Please note that all the denominators for HW line will be the same value
            //i.e. Number of facilities in the database
            //but we need this to go into an array that has month values so we can use
            //each month's values to divide the corresponding month value in the numerator result set
            $facility = new Facility();
            $totalFacilitysCount = 0;
            $locationFacilities = $facility->getFacilityCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
            foreach($locationFacilities as $key=>$row)
                $totalFacilitysCount += $locationFacilities[$key];

            $totalFacs12MonthArray = $facsWithTrainedHWMonthlyNumers;
            
            //overwrite the fid counts with total facility in the location
            for($i=0; $i < count($totalFacs12MonthArray); $i++)
                 $totalFacs12MonthArray[$i]['fid_count'] = $totalFacilitysCount;
            
            //echo 'HW Numeraor: <br/>';  var_dump($facsWithTrainedHWMonthlyNumers); echo '<br><br>'; 
            //echo 'HW Denom: <br/>';  var_dump($totalFacs12MonthArray); echo '<br><br>'; 
            
            //get the reporting facs count for providing and stockout
            $longWhereClause = $tt_where . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            $reportingFacsWithTrainedHWNumers =  $coverageHelper->getReportingFacsWithTrainedHWOvertime($longWhereClause,$lastPullDatemultiple);
            
            //providing            
            $longWhereClause = $reportingWhere . ' AND ' . $tt_where . ' AND ' . $ct_where . ' AND ' .
                               $consmptionWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            $facsWithHWAndConsumptionNumers = $coverageHelper->getFacWithHWProvidingOverTime($longWhereClause,$lastPullDatemultiple);
            $facsReportingWithHW = $reportingFacsWithTrainedHWNumers;
            
            //echo 'Prov Numeraor: <br/>'; var_dump($facsWithHWAndConsumptionNumers); echo '<br><br>';
            
            //stockout 
            $stockout = new StockoutHelper();
            $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                               $tt_where . ' AND ' . $so_alias_where . ' AND ' .
                               $stockoutWhere . ' AND ' . $locationWhere;
            $facsWithHWStockOutNumers = $stockout->getStockoutFacsWithTrainedHWOverTime($longWhereClause,$lastPullDatemultiple);
            
            //echo 'SO Numeraor: <br/>'; var_dump($facsWithHWStockOutNumers); echo '<br><br>';
            //echo 'ProvSo Denom: <br/>'; var_dump($facsReportingWithHW); echo '<br><br>';
            //exit;
            //$reportingFacsWithTrainedHWNumers is also denominator for this

            $hwOverTime = $helper->doOverTimePercents($facsWithTrainedHWMonthlyNumers, $totalFacs12MonthArray);
           
            $providingOverTime = $helper->doOverTimePercents($facsWithHWAndConsumptionNumers, $facsReportingWithHW);
          
            $stockoutOverTime = $helper->doOverTimePercents($facsWithHWStockOutNumers, $facsReportingWithHW);
            //var_dump($hwOverTime); echo '<br><br>';
            //var_dump($providingOverTime); echo '<br><br>';
            //var_dump($stockoutOverTime); echo '<br><br>';
            $hwOverTime = array_reverse($hwOverTime);
            $providingOverTime = array_reverse($providingOverTime);
            $stockoutOverTime = array_reverse($stockoutOverTime);
            $output = array($hwOverTime, $providingOverTime, $stockoutOverTime);
            
            //check if to save month national data
            if(!$cacheValue){ //fresh in month...this will be always true if execution gets here
                //do cache insert
                if($training_type == 'fp')
                    $alias = CacheManager::PERCENT_COVERAGE_OVERTIME_FP;
                else if($training_type == 'larc')
                    $alias = CacheManager::PERCENT_COVERAGE_OVERTIME_LARC;

                $dataArray = array(
                    'date_cached'=> $latestDate,
                    'indicator' => 'FP/LARC HR coverage over time',
                    'indicator_alias' => $alias,
                    'value' => json_encode($output)
                );
                $cacheManager->setIndicator($dataArray);
            }
        }
        
        return $output;
  }
    
    public function fetchHWCoverageOvertimeNumeratorDenominator($training_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDatemultiple=array()){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        
        $ouput = array();
        $helper = new Helper2();
        $latestDate = $helper->getLatestPullDate();
                
        
        $cacheManager = new CacheManager();

        if($training_type == 'fp')
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_COVERAGE_OVERTIME_FP, $latestDate);
        else if($training_type == 'larc')
            $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_COVERAGE_OVERTIME_LARC, $latestDate);
        
        $cacheValue = null;
        
       
            //echo 'no cache'; exit;
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            
            //where clauses
            if($training_type == 'fp'){
                $systemTrainingTypeWhere = "(tto.system_training_type = 'fp' OR tto.system_training_type = 'larc')";
                $tt_where = "(fptrained > 0 OR larctrained > 0)";
                $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                $so_alias_where = "commodity_alias = 'so_fp_seven_days'";
            }
            else if($training_type == 'larc'){
                $systemTrainingTypeWhere = "tto.system_training_type = 'larc'";
                $tt_where = 'larctrained > 0';
                $ct_where = "commodity_type = 'larc'";
                $so_alias_where = "commodity_alias = 'so_implants'";
            }
            //print 'after where<br>';
            

            $coverageHelper = new CoverageHelper();                
            //($lastPullDatemultiple);exit;
            if(empty($lastPullDatemultiple)){
                    
            $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
            }else{
                   
             $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
             }
            
            
            $consmptionWhere = 'consumption > 0';
            $reportingWhere = 'facility_reporting_status = 1';
            $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
            $stockoutWhere = "stock_out='Y'";
            
            //hw                                        
            //numerator:
            //$facsWithTrainedHWMonthlyNumers = $coverageHelper->getCummulativeTrainedFacsMonthly(12, $training_type, $geoList, $tierText, $tierFieldName );
            $facsWithTrainedHWMonthlyNumers = $coverageHelper->getCummulativeTrainedFacsMonthly(12, $systemTrainingTypeWhere, $geoList, $tierText, $tierFieldName,$lastPullDatemultiple);
            //print_r($facsWithTrainedHWMonthlyNumers);exit;
            //Please note that all the denominators for HW line will be the same value
            //i.e. Number of facilities in the database
            //but we need this to go into an array that has month values so we can use
            //each month's values to divide the corresponding month value in the numerator result set
            $facility = new Facility();
            $totalFacilitysCount = 0;
            $locationFacilities = $facility->getFacilityCountByLocation($locationWhere, $geoList, $tierText, $tierFieldName);
            foreach($locationFacilities as $key=>$row)
                $totalFacilitysCount += $locationFacilities[$key];

            $totalFacs12MonthArray = $facsWithTrainedHWMonthlyNumers;
            
            //overwrite the fid counts with total facility in the location
            for($i=0; $i < count($totalFacs12MonthArray); $i++)
                 $totalFacs12MonthArray[$i]['fid_count'] = $totalFacilitysCount;
            
            //echo 'HW Numeraor: <br/>';  var_dump($facsWithTrainedHWMonthlyNumers); echo '<br><br>'; 
            //echo 'HW Denom: <br/>';  var_dump($totalFacs12MonthArray); echo '<br><br>'; 
            
            //get the reporting facs count for providing and stockout
            $longWhereClause = $tt_where . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            $reportingFacsWithTrainedHWNumers =  $coverageHelper->getReportingFacsWithTrainedHWOvertime($longWhereClause,$lastPullDatemultiple);
            
            //providing            
            $longWhereClause = $reportingWhere . ' AND ' . $tt_where . ' AND ' . $ct_where . ' AND ' .
                               $consmptionWhere . ' AND ' . $dateWhere . ' AND ' . $locationWhere;
            $facsWithHWAndConsumptionNumers = $coverageHelper->getFacWithHWProvidingOverTime($longWhereClause,$lastPullDatemultiple);
            $facsReportingWithHW = $reportingFacsWithTrainedHWNumers;
            
            //echo 'Prov Numeraor: <br/>'; var_dump($facsWithHWAndConsumptionNumers); echo '<br><br>';
            
            //stockout 
            $stockout = new StockoutHelper();
            $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                               $tt_where . ' AND ' . $so_alias_where . ' AND ' .
                               $stockoutWhere . ' AND ' . $locationWhere;
            $facsWithHWStockOutNumers = $stockout->getStockoutFacsWithTrainedHWOverTime($longWhereClause,$lastPullDatemultiple);
            
            //echo 'SO Numeraor: <br/>'; var_dump($facsWithHWStockOutNumers); echo '<br><br>';
            //echo 'ProvSo Denom: <br/>'; var_dump($facsReportingWithHW); echo '<br><br>';
            //exit;
            //$reportingFacsWithTrainedHWNumers is also denominator for this

            $hwOverTimeNumDenom = $helper->doOverTimeNumeratorDenominator($facsWithTrainedHWMonthlyNumers, $totalFacs12MonthArray);
           
            $providingOverTimeNumDenoom = $helper->doOverTimeNumeratorDenominator($facsWithHWAndConsumptionNumers, $facsReportingWithHW);
          
            $stockoutOverTimeNumDenom = $helper->doOverTimeNumeratorDenominator($facsWithHWStockOutNumers, $facsReportingWithHW);
            
            
            $hwOverTimeNumDenom = array_reverse($hwOverTimeNumDenom);
            $providingOverTimeNumDenoom = array_reverse($providingOverTimeNumDenoom);
            $stockoutOverTimeNumDenom = array_reverse($stockoutOverTimeNumDenom);
            //var_dump($hwOverTime); echo '<br><br>';
            //var_dump($providingOverTime); echo '<br><br>';
            //var_dump($stockoutOverTime); echo '<br><br>';
            
            $output = array($hwOverTimeNumDenom, $providingOverTimeNumDenoom, $stockoutOverTimeNumDenom);
            
           
        
        
        return $output;
  }
          
    
     
        
         
        public function fetchProvidingOvertime($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            
            //$output = array(array('location'=>'National', 'percent'=>0)); 
            $output = array();
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
           
           // $implodedDate = implode('","',$lastPullDatemultiple); //implode("','",$lastPullDatemultiple);
            $cacheManager = new CacheManager();

            if($commodity_type == 'fp')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_FP, $latestDate);
            else if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_LARC, $latestDate);


            //check if page is just being loaded
            //fresh session, month data already registered
            //just retrieve registered data
            if($cacheValue && $freshVisit){ 
                $output = json_decode($cacheValue, true);
            }
            else{
                //echo 'second'; exit;
                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);

                //where clauses
                if($commodity_type == 'fp')
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                else if($commodity_type == 'larc')
                    $ct_where = "commodity_type = 'larc'";
                
              
                if(empty($lastPullDatemultiple)){
                    
                $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                }else{
                   
                    $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                }
                $reportingWhere = 'facility_reporting_status = 1';
                $consumptionWhere = 'consumption > 0';
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                //use coverage helper for this functions even though they have variants in the 
                //helper2 class but these do not filter and return more rows
                //appropriate for what we are doing here
                $coverageHelper = new CoverageHelper();
                $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                   $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                $numerators = $coverageHelper->getFacProvidingOverTime($longWhereClause, $geoList, $tierText, $tierFieldName);
               //echo $longWhereClause;exit;
                   
                $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                $denominators = $coverageHelper->getReportingFacsOvertimeByLocationNoFilter($longWhereClause, $geoList, $tierText, $tierFieldName);                    
                //echo 'denom<br/>';
                //var_dump($denominators); exit;
                   
                  //getprint_r($numerator);exit; the month names
                  $monthNames = array();  $i =0;
                  if(empty($lastPullDatemultiple)){
                  $monthNames = $helper->getPreviousMonthDates(12);
                  }else{
                      $monthNames = $lastPullDatemultiple;
                  }
                  sort($monthNames);
                  //convert to strings 
                  foreach ($monthNames as $key=>$date){
                      $monthNames[$key] = date('F', strtotime($date));
                  }              
                 
                  
                  $locationNames = $helper->getLocationNames($geoList);

                  //add all missing months for each location in the numerator list
                  $numerators = $this->addMissingMonths($numerators, $monthNames, $locationNames, $tierText);             
                  $denominators = $this->addMissingMonths($denominators, $monthNames, $locationNames, $tierText);
                  //echo 'numerator count: ' . count($numerators) . '<br/>'; 
                  //echo 'denominators count: ' . count($denominators) . '<br/>'; 
                  
//                  var_dump($numerators); echo '<br><br>';
//                  var_dump($denominators); echo '<br><br>';
//                  exit;


                  /*TP:
                   * This routine will arrange location values into month arrays
                   * Format:
                   * $output['April']['North Central'] = 1234;
                   * $output['April']['North East'] = 5678;
                   * ...
                   * $output['March']['North Central'] = 1234;
                   * $output['March']['North East'] = 5678;
                   */
               
                 
                  for($i=0; $i<count($monthNames); $i++){                
                        $monthName = $monthNames[$i];
                        $output[$monthName] = array();
                        $j = $i;

                        //$output = array();
                        //$output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                        $output[$monthName]['National'] = 0;
                        foreach($locationNames as $location){   
                            
                          
                            $output[$monthName][$location] = $numerators[$j]['fid_count'] / $denominators[$j]['fid_count'] * 100;
                            $j += sizeof($monthNames);
                        }
                  }
                    
                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert
                        if($commodity_type == 'fp')
                            $alias = CacheManager::PERCENT_PROVIDING_OVERTIME_FP;
                        else if($commodity_type == 'larc')
                            $alias = CacheManager::PERCENT_PROVIDING_OVERTIME_LARC;

                        //get national figures
                        $nationalHelper = new CoverageNationalHelper();
                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                           $consumptionWhere . ' AND ' . $ct_where;
                        $nationalNumerator = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
                        $nationalDenominator = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);

                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                            $output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                        }
                        
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities providing FP/LARC over time',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
                    else{ //else for inner if
                        //get national data for each month and put in national key for each month
                        $cacheValue = json_decode($cacheValue, true);
                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                            $output[$monthName]['National'] = $cacheValue[$monthName]['National'];
                        }
                    }
            }
            
            
            //set national ave
           // var_dump($output); exit;
            return $output;
     }
     
        public function fetchProvidingOvertimeAllMethods($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            
            //$output = array(array('location'=>'National', 'percent'=>0)); 
            $output = array();
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
           
           // $implodedDate = implode('","',$lastPullDatemultiple); //implode("','",$lastPullDatemultiple);
            $cacheManager = new CacheManager();

                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_ALL_METHODS, $latestDate);
                $cacheValue = null;

            //check if page is just being loaded
            //fresh session, month data already registered
            //just retrieve registered data
            if($cacheValue && $freshVisit){ 
                $output = json_decode($cacheValue, true);
            }
            else{
                //echo 'second'; exit;
                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);

                //where clauses
              
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                
                
              
                if(empty($lastPullDatemultiple)){
                    
                $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                }else{
                   
                    $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                }
                $reportingWhere = 'facility_reporting_status = 1';
                 //$consumptionWhere = "facility_reporting_status = 1";//csum.sumcons >= 3';
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                //use coverage helper for this functions even though they have variants in the 
                //helper2 class but these do not filter and return more rows
                //appropriate for what we are doing here
                $coverageHelper = new CoverageHelper();
                $longWhereClause = $reportingWhere . " AND " . $dateWhere . " AND " . 
                                   $ct_where . " AND " . $locationWhere;
                $numerators = $coverageHelper->getFacProvidingOverTimeAllMethods($longWhereClause, $geoList, $tierText, $tierFieldName,$lastPullDatemultiple);
               //echo $longWhereClause;exit;
                   //echo 'THis is the providing overtime place';
                $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                $denominators = $coverageHelper->getReportingFacsOvertimeByLocationNoFilter($longWhereClause, $geoList, $tierText, $tierFieldName);                    
                //echo 'denom<br/>';
                //var_dump($denominators); exit;
                   
                  //getprint_r($numerator);exit; the month names
                  $monthNames = array();  $i =0;
                  if(empty($lastPullDatemultiple)){
                  $monthNames = $helper->getPreviousMonthDates(12);
                  }else{
                      $monthNames = $lastPullDatemultiple;
                  }
                  sort($monthNames);
                  //convert to strings 
                  foreach ($monthNames as $key=>$date){
                      $monthNames[$key] = date('F', strtotime($date));
                  }              
                 
                  
                  $locationNames = $helper->getLocationNames($geoList);

                
                  //add all missing months for each location in the numerator list
                  $numerators = $this->addMissingMonths($numerators, $monthNames, $locationNames, $tierText);             
                  $denominators = $this->addMissingMonths($denominators, $monthNames, $locationNames, $tierText);
                  //echo 'numerator count: ' . count($numerators) . '<br/>'; 
                  //echo 'denominators count: ' . count($denominators) . '<br/>'; 
                  
//                  var_dump($numerators); echo '<br><br>';
//                  var_dump($denominators); echo '<br><br>';
//                  exit;


                  /*TP:
                   * This routine will arrange location values into month arrays
                   * Format:
                   * $output['April']['North Central'] = 1234;
                   * $output['April']['North East'] = 5678;
                   * ...
                   * $output['March']['North Central'] = 1234;
                   * $output['March']['North East'] = 5678;
                   */
               
                 
                  for($i=0; $i<count($monthNames); $i++){                
                        $monthName = $monthNames[$i];
                        $output[$monthName] = array();
                        $j = $i;

                        //$output = array();
                        //$output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                        $output[$monthName]['National'] = 0;
                        foreach($locationNames as $location){   
                            
                          
                            $output[$monthName][$location] = $numerators[$j]['fid_count'] / $denominators[$j]['fid_count'] * 100;
                            $j += sizeof($monthNames);
                        }
                  }
                    
                    //check if to save month national data
                    if(!$cacheValue && $freshVisit){ //fresh in month
                        //do cache insert
                        
                            $alias = CacheManager::PERCENT_PROVIDING_OVERTIME_ALL_METHODS;
                       

                        //get national figures
                        $nationalHelper = new CoverageNationalHelper();
                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                           $consumptionWhere . ' AND ' . $ct_where;
                        $nationalNumerator = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
                        $nationalDenominator = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);

                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                            $output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                        }
                        
                        $dataArray = array(
                            'date_cached'=> $latestDate,
                            'indicator' => 'Percent of facilities providing at least 3 modern methods over time',
                            'indicator_alias' => $alias,
                            'value' => json_encode($output)
                        );
                        $cacheManager->setIndicator($dataArray);
                    }
                    else{ //else for inner if
                        //get national data for each month and put in national key for each month
                        $cacheValue = json_decode($cacheValue, true);
                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                            $output[$monthName]['National'] = $cacheValue[$monthName]['National'];
                        }
                    }
            }
            
            
            //set national ave
           // var_dump($output); exit;
            return $output;
     }
     
//        public function fetchProvidingOvertimeAllMethodsNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()){
//            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
//            
//            //$output = array(array('location'=>'National', 'percent'=>0)); 
//            $output = array();
//            $helper = new Helper2();
//            $latestDate = $helper->getLatestPullDate();
//           
//           // $implodedDate = implode('","',$lastPullDatemultiple); //implode("','",$lastPullDatemultiple);
//            $cacheManager = new CacheManager();
//
//          
//                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_ALL_METHODS, $latestDate);
//                
//
//
//          
//                //echo 'second'; exit;
//                $tierText = $helper->getLocationTierText($tierValue);
//                $tierFieldName = $helper->getTierFieldName($tierText);
//
//                //where clauses
//              
//                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
//               
//                
//              
//                if(empty($lastPullDatemultiple)){
//                    
//                $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
//                }else{
//                   
//                    $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
//                }
//                $reportingWhere = 'facility_reporting_status = 1';
//                $consumptionWhere = 'countsum >= 3';//csum.sumcons >= 3';
//                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
//
//                //use coverage helper for this functions even though they have variants in the 
//                //helper2 class but these do not filter and return more rows
//                //appropriate for what we are doing here
//                $coverageHelper = new CoverageHelper();
//                $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
//                                   $reportingWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
//                $numerators = $coverageHelper->getFacProvidingOverTimeAllMethods($longWhereClause, $geoList, $tierText, $tierFieldName,$lastPullDatemultiple);
//               //echo $longWhereClause;exit;
//                   
//                $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
//                $denominators = $coverageHelper->getReportingFacsOvertimeByLocationNoFilter($longWhereClause, $geoList, $tierText, $tierFieldName);                    
//                //echo 'denom<br/>';
//                //var_dump($denominators); exit;
//                   
//                  //getprint_r($numerator);exit; the month names
//                  $monthNames = array();  $i =0;
//                  if(empty($lastPullDatemultiple)){
//                  $monthNames = $helper->getPreviousMonthDates(12);
//                  }else{
//                      $monthNames = $lastPullDatemultiple;
//                  }
//                  sort($monthNames);
//                  //convert to strings 
//                  foreach ($monthNames as $key=>$date){
//                      $monthNames[$key] = date('F', strtotime($date));
//                  }              
//                 
//                  
//                  $locationNames = $helper->getLocationNames($geoList);
//
//                
//                  //add all missing months for each location in the numerator list
//                  $numerators = $this->addMissingMonths($numerators, $monthNames, $locationNames, $tierText);             
//                  $denominators = $this->addMissingMonths($denominators, $monthNames, $locationNames, $tierText);
//                  //echo 'numerator count: ' . count($numerators) . '<br/>'; 
//                  //echo 'denominators count: ' . count($denominators) . '<br/>'; 
//                  
////                  var_dump($numerators); echo '<br><br>';
////                  var_dump($denominators); echo '<br><br>';
////                  exit;
//
//
//                  /*TP:
//                   * This routine will arrange location values into month arrays
//                   * Format:
//                   * $output['April']['North Central'] = 1234;
//                   * $output['April']['North East'] = 5678;
//                   * ...
//                   * $output['March']['North Central'] = 1234;
//                   * $output['March']['North East'] = 5678;
//                   */
//               
//                 $numeratorData = array();
//                 $denominatorData = array();
//                  for($i=0; $i<count($monthNames); $i++){                
//                        $monthName = $monthNames[$i];
//                        $output[$monthName] = array();
//                        $j = $i;
//
//                        //$output = array();
//                        //$output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
//                        $output[$monthName]['National'] = 0;
//                        foreach($locationNames as $location){   
//                            
//                          
//                            //$output[$monthName][$location] = $numerators[$j]['fid_count'] / $denominators[$j]['fid_count'] * 100;
//                            $numeratorData[$monthName][$location] = $numerators[$j]['fid_count'];
//                            $denominatorData[$monthName][$location] = $denominators[$j]['fid_count'];
//                            
//                            $j += sizeof($monthNames);
//                        }
//                  }
//                    
//                    //check if to save month national data
//                   
//                    
//                        //get national figures
//                        $nationalHelper = new CoverageNationalHelper();
//                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
//                                           $consumptionWhere . ' AND ' . $ct_where;
//                        $nationalNumerator = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
//                        $nationalDenominator = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);
//                         $nationalNumerators = array();
//                         $nationalDenominators = array(); 
//                        for($i=0; $i<count($monthNames); $i++){
//                            $monthName = $monthNames[$i];
//                           // $output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
//                            $nationalNumerators[$monthName]['National'] =  $nationalNumerator[$i]['fid_count'];
//                            $nationalDenominators[$monthName]['National'] = $nationalDenominator[$i]['fid_count'];
//                        }
//                        
//                      
//                   
//                        $finalNumerators = array();
//                        $finalDenominators = array();
//                        
//                        
//                        $finalNumerators = array_merge_recursive($nationalNumerators,$numeratorData);
//                        $finalDenominators = array_merge_recursive($nationalDenominators,$denominatorData);
//                        
//                       
//                        
//                return array($finalNumerators,$finalDenominators);
//     }
     
     
     
        public function fetchProvidingOvertimeNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit,$lastPullDatemultiple=array()){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            
            //$output = array(array('location'=>'National', 'percent'=>0)); 
            $output = array();
            $helper = new Helper2();
            $latestDate = $helper->getLatestPullDate();
           
           // $implodedDate = implode('","',$lastPullDatemultiple); //implode("','",$lastPullDatemultiple);
            $cacheManager = new CacheManager();

            if($commodity_type == 'fp')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_FP, $latestDate);
            else if($commodity_type == 'larc')
                $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_PROVIDING_OVERTIME_LARC, $latestDate);


          
                //echo 'second'; exit;
                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);

                //where clauses
                if($commodity_type == 'fp')
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                else if($commodity_type == 'larc')
                    $ct_where = "commodity_type = 'larc'";
                
              
                if(empty($lastPullDatemultiple)){
                    
                $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                }else{
                   
                    $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                }
                $reportingWhere = 'facility_reporting_status = 1';
                $consumptionWhere = 'consumption > 0';
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                //use coverage helper for this functions even though they have variants in the 
                //helper2 class but these do not filter and return more rows
                //appropriate for what we are doing here
                $coverageHelper = new CoverageHelper();
                $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                   $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                $numerators = $coverageHelper->getFacProvidingOverTime($longWhereClause, $geoList, $tierText, $tierFieldName);
               //echo $longWhereClause;exit;
                   
                $longWhereClause = $dateWhere . ' AND ' . $locationWhere;
                $denominators = $coverageHelper->getReportingFacsOvertimeByLocationNoFilter($longWhereClause, $geoList, $tierText, $tierFieldName);                    
                //echo 'denom<br/>';
                //var_dump($denominators); exit;
                   
                  //getprint_r($numerator);exit; the month names
                  $monthNames = array();  $i =0;
                  if(empty($lastPullDatemultiple)){
                  $monthNames = $helper->getPreviousMonthDates(12);
                  }else{
                      $monthNames = $lastPullDatemultiple;
                  }
                  sort($monthNames);
                  //convert to strings 
                  foreach ($monthNames as $key=>$date){
                      $monthNames[$key] = date('F', strtotime($date));
                  }              
                 
                  
                  $locationNames = $helper->getLocationNames($geoList);

                
                  //add all missing months for each location in the numerator list
                  $numerators = $this->addMissingMonths($numerators, $monthNames, $locationNames, $tierText);             
                  $denominators = $this->addMissingMonths($denominators, $monthNames, $locationNames, $tierText);
                  //echo 'numerator count: ' . count($numerators) . '<br/>'; 
                  //echo 'denominators count: ' . count($denominators) . '<br/>'; 
                  
//                  var_dump($numerators); echo '<br><br>';
//                  var_dump($denominators); echo '<br><br>';
//                  exit;


                  /*TP:
                   * This routine will arrange location values into month arrays
                   * Format:
                   * $output['April']['North Central'] = 1234;
                   * $output['April']['North East'] = 5678;
                   * ...
                   * $output['March']['North Central'] = 1234;
                   * $output['March']['North East'] = 5678;
                   */
               
                 $numeratorData = array();
                 $denominatorData = array();
                  for($i=0; $i<count($monthNames); $i++){                
                        $monthName = $monthNames[$i];
                        $output[$monthName] = array();
                        $j = $i;

                        //$output = array();
                        //$output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                        $output[$monthName]['National'] = 0;
                        foreach($locationNames as $location){   
                            
                          
                            //$output[$monthName][$location] = $numerators[$j]['fid_count'] / $denominators[$j]['fid_count'] * 100;
                            $numeratorData[$monthName][$location] = $numerators[$j]['fid_count'];
                            $denominatorData[$monthName][$location] = $denominators[$j]['fid_count'];
                            
                            $j += sizeof($monthNames);
                        }
                  }
                    
                    //check if to save month national data
                   
                    
                        //get national figures
                        $nationalHelper = new CoverageNationalHelper();
                        $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                           $consumptionWhere . ' AND ' . $ct_where;
                        $nationalNumerator = $nationalHelper->getNationalFacProvidingOverTime($longWhereClause);
                        $nationalDenominator = $nationalHelper->getNationalReportingFacsOvertime($dateWhere);
                         $nationalNumerators = array();
                         $nationalDenominators = array(); 
                        for($i=0; $i<count($monthNames); $i++){
                            $monthName = $monthNames[$i];
                           // $output[$monthName]['National'] = $nationalNumerator[$i]['fid_count'] / $nationalDenominator[$i]['fid_count'] * 100;
                            $nationalNumerators[$monthName]['National'] =  $nationalNumerator[$i]['fid_count'];
                            $nationalDenominators[$monthName]['National'] = $nationalDenominator[$i]['fid_count'];
                        }
                        
                      
                   
                        $finalNumerators = array();
                        $finalDenominators = array();
                        
                        
                        $finalNumerators = array_merge_recursive($nationalNumerators,$numeratorData);
                        $finalDenominators = array_merge_recursive($nationalDenominators,$denominatorData);
                        
                       
                        
                return array($finalNumerators,$finalDenominators);
     }
     
     
     
       
    public function updateCache(){
         $cacheManager = new CacheManager();
         $helper = new Helper2();
         
         $tierValue = 1;
         $geoIDsArray = $helper->getLocationTierIDs($tierValue);
         foreach($geoIDsArray as $key=>$geoid)
            $geoIDsArray[$key] = "'$geoid'";

         $geoList = implode(',', $geoIDsArray);
            
         //percentfacswithtrainedhw
         $this->fetchPercentFacHWTrained('fp', $geoList, $tierValue, false, true);
         $this->fetchPercentFacHWTrained('larc', $geoList, $tierValue, false, true);
         
         
         //facswithhwproviding
         $this->fetchFacsWithHWProviding('fp', 'fp', $geoList, $tierValue, false, true );
         $this->fetchFacsWithHWProviding('larc', 'larc', $geoList, $tierValue, false, true );
            
         //coverageovertime
         $this->fetchHWCoverageOvertime('fp', $geoList, $tierValue, false, true);
         $this->fetchHWCoverageOvertime('larc', $geoList, $tierValue, false, true);
         
     }
    
     
}

?>