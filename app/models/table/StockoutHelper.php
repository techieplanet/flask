<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StockoutHelper
 *
 * @author Swedge
 */
require_once 'Helper2.php';
class StockoutHelper {
    //put your code here
    
        public function getStockoutFacsWithTrainedHWCountByLocation($longWhereClause, $geoList, $tierText, $tierFieldName){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $helper = new Helper2();
                
                $select = $db->select()
                            ->from(array('c' => 'commodity'),
                              array('COUNT(DISTINCT(c.facility_id)) AS fid_count'))
                            ->joinInner(array('cno'=>'commodity_name_option'), 'cno.id = c.name_id', array())
                            ->joinInner(array('fwtc'=>'facility_worker_training_counts_view'), 'c.facility_id = facid', array())
                            ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array('lga', 'state',  'geo_zone'))
                            ->where($longWhereClause)
                            ->group($tierFieldName)
                            ->order(array($tierText));
                        
              //echo 'CS: ' . $select->__toString() . '<br/>'; exit;

              $result = $db->fetchAll($select);
              
              $locationNames = $helper->getLocationNames($geoList);
              $locationDataArray = $helper->filterLocations($locationNames, $result, $tierText);
               
            //var_dump($locationDataArray); exit;
            return $locationDataArray;
       }
       
       public function getFacilitiesprovidingButStockkedOut($mainWhereClause, $subWhereClause, $geoList, $tierText, $tierFieldName){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $helper = new Helper2();
                
                $subselect = $db->select()
                              ->from(array('c' => 'commodity'), array('DISTINCT(c.facility_id) AS providingfacs'))

                              ->joinInner(array('cno' => 'commodity_name_option'), 'c.name_id = cno.id', array())
                              ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array())
                              ->where($subWhereClause);
                
                $select = $db->select()
                            ->from(array('c' => 'commodity'))

                            ->joinInner(array('cno' => 'commodity_name_option'), 'cno.id = c.name_id', array())
                            ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array('lga', 'state',  'geo_zone'))
                            ->where($mainWhereClause . ' AND c.facility_id IN (' . $subselect . ')')
                            ->group($tierFieldName)
                            ->order(array($tierText));                          

              //echo $select->__toString() . '<br/><br/>'; exit;

               $result = $db->fetchAll($select);
               
            return $result;
       }
       
        public function getFacilitiesListProvidingButStockedout($mainWhereClause, $subWhereClause, $geoList, $tierText, $tierFieldName){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $helper = new Helper2();
                
                $subselect = $db->select()
                              ->from(array('c' => 'commodity'), array('DISTINCT(c.facility_id) AS providingfacs'))

                              ->joinInner(array('cno' => 'commodity_name_option'), 'c.name_id = cno.id', array())
                              ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array())
                              ->where($subWhereClause);
                
                $select = $db->select()
                            ->from(array('c' => 'commodity'))

                            ->joinInner(array('cno' => 'commodity_name_option'), 'cno.id = c.name_id', array())
                            ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array('facility_name','lga', 'state',  'geo_zone'))
                            ->where($mainWhereClause . ' AND c.facility_id IN (' . $subselect . ')')
                            ->group("c.facility_id")
                            ->order(array($tierText));                          

             // echo $select->__toString() . '<br/><br/>'; exit;

               $result = $db->fetchAll($select);
               
              
            //var_dump($locationDataArray); exit;
            return $result;
       }
       
       public function getFacsProvidingButStockedout($mainWhereClause, $subWhereClause, $geoList, $tierText, $tierFieldName){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $helper = new Helper2();
                
                $subselect = $db->select()
                              ->from(array('c' => 'commodity'), array('DISTINCT(c.facility_id) AS providingfacs'))
                              ->joinInner(array('cno' => 'commodity_name_option'), 'c.name_id = cno.id', array())
                              ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array())
                              ->where($subWhereClause);
                
                $select = $db->select()
                            ->from(array('c' => 'commodity'),
                              array('COUNT(DISTINCT(c.facility_id)) AS fid_count'))
                            ->joinInner(array('cno' => 'commodity_name_option'), 'cno.id = c.name_id', array())
                            ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array('lga', 'state',  'geo_zone'))
                            ->where($mainWhereClause . ' AND c.facility_id IN (' . $subselect . ')')
                            ->group($tierFieldName)
                            ->order(array($tierText));

              //echo $select->__toString() . '<br/><br/>'; exit;

               $result = $db->fetchAll($select);
               
              //filter for only valid values
              $locationNames = $helper->getLocationNames($geoList);
              $locationDataArray = $helper->filterLocations($locationNames, $result, $tierText);
               
            //var_dump($locationDataArray); exit;
            return $locationDataArray;
       }
       
       
       public function getStockoutFacsWithTrainedHWOverTime($longWhereClause,$lastPullDatemultiple=array()){
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $helper = new Helper2();
                
                if(empty($lastPullDatemultiple)){
                    
                $dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                }else{
                   
                $dateWhere = 'date IN ("'.implode('", "', $lastPullDatemultiple).'")';
                }
                //$dateWhere = '(date <= (SELECT MAX(date) FROM facility_report_rate) AND date >= DATE_SUB((SELECT MAX(date) FROM facility_report_rate), INTERVAL 11 MONTH))';
                
                //the facility_location_view is used for overtime by location calls
//                $select = $db->select()
//                            ->from(array('c' => 'commodity'),
//                              array('COUNT(DISTINCT(c.facility_id)) AS fid_count', 'MONTHNAME(date) as month_name', 'YEAR(date) as year'))
//                            ->joinInner(array('cno'=>'commodity_name_option'), 'cno.id = c.name_id', array())
//                            ->joinInner(array('fwtc'=>'facility_worker_training_counts_view'), 'c.facility_id = facid', array())
//                            ->joinInner(array('flv'=>'facility_location_view'), 'c.facility_id = flv.id', array())
//                            ->where($longWhereClause)
//                            ->group('date')
//                            ->order(array('date'));
                
                
                    $sql = 'SELECT DISTINCT(date),MONTHNAME(date), COALESCE(fid_count,0)  as fid_count, month_name, year, date_so ' .
                            'FROM facility_report_rate frr ' .
                            'LEFT JOIN ( ' .
                                            'SELECT ' .
                                            'COUNT(DISTINCT(c.facility_id)) AS `fid_count`, MONTHNAME(date) AS month_name, YEAR(date) AS year, date as date_so ' .
                                            'FROM commodity AS c ' .
                                            'INNER JOIN `commodity_name_option` AS `cno` ON cno.id = c.name_id ' .
                                            'INNER JOIN `facility_worker_training_counts_view` AS `fwtc` ON c.facility_id = facid ' .
                                            'INNER JOIN `facility_location_view` AS `flv` ON c.facility_id = flv.id ' .
                                            'WHERE (' . $longWhereClause . ') ' .
                                            'GROUP BY date  ' .
                                            'ORDER BY date  ' .
                                        ') as fac_so ' .
                            'ON frr.date = date_so ' .
                            'WHERE (' . $dateWhere . ') ' .
                            'GROUP BY date ' .
                            'ORDER BY date';

                //echo $select->__toString(); exit;
                Helper2::log($sql);
                
              $result = $db->fetchAll($sql);
                             
            //var_dump($result); exit;
            return $result;
       }
}
?>
