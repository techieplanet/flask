<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CoverageNumDenom
 *
 * @author swedge-mac
 */
class CoverageNumDenom {
    //put your code here
    
    public function fetchPercentFacsProvidingAllMethodsNumeratorDenominator($commodity_type, $geoList, $tierValue, $freshVisit, $updateMode = false,$lastPullDate=""){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();

        $output = array(array('location'=>'National', 'percent'=>0)); 
        $helper = new Helper2();
        if(empty($lastPullDate) || $lastPullDate==""){
          $latestDate = $helper->getLatestPullDate();
         }else{
          $latestDate = $lastPullDate;
         }

        $cacheManager = new CacheManager();
        $cacheValue = $cacheManager->getIndicator(CacheManager::PERCENT_FACS_PROVIDING_ALL_METHODS, $latestDate);



        $cacheValue = null;


                $tierText = $helper->getLocationTierText($tierValue);
                $tierFieldName = $helper->getTierFieldName($tierText);

                //where clauses

                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc' OR commodity_alias = 'injectables' )";


                $dateWhere = "c.date = '$latestDate'";
                $reportingWhere = 'facility_reporting_status = 1';
                $consumptionWhere = 'csum.sumcons >= 3';
                $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';

                $coverageHelper = new CoverageHelper();
                $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                   $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                $numerators = $coverageHelper->getFacProvidingAllMethodCount($longWhereClause, $geoList, $tierText, $tierFieldName,$latestDate);

                $dateWhere = "frr.date = '$latestDate'";
                $longWhereClause = $dateWhere . ' AND ' . $locationWhere;

                //send only one month date range. 
                $denominators = $helper->getReportingFacsOvertimeByLocation($longWhereClause, $geoList, $tierText, $tierFieldName);

                //set output                    
               // $sumsArray = $helper->sumNumersAndDenoms($numerators, $denominators);
//                    $output = array_merge($output, $sumsArray['output']);
//                    $output[0]['percent'] = $sumsArray['nationalAvg'];

                 list($finalNum,$finalDenom) = $helper->addNationalNumersAndDenoms($numerators,$denominators);

            return array($finalNum,$finalDenom);

   }
}
