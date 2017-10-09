<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportRate
 *
 * @author swedge-mac
 */
class ReportRate {
    //put your code here
    
    public function getLatestPullDate(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
                      ->from(array('frr' => 'facility_report_rate'), 'MAX(frr.date) as maxdate');
	
	    $result = $db->fetchRow($select); 
            
           return $result['maxdate'];
           //return '2016-02-01';
    }
    
    public function getLatestPullFullDate(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
                      ->from(array('frr' => 'facility_report_rate'), 'MAX(frr.timestamp_created) as maxdate');
	
	    $result = $db->fetchRow($select); 
            
            return $result['maxdate'];
    }
    
    public function getLatestPullDatePdf(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
                      ->from(array('frr' => 'facility_report_rate'), 'MAX(frr.date) as maxdate');
	
	    $result = $db->fetchRow($select); 
            
            return $result['maxdate'];
            //return '2015-06-01';
    }
    
    /*
     * gets the latest 12 DHIS@ pull dates
     * Args: numberOfMonths - number of months to backtrack
     */
    public function getPreviousMonthDates($numberOfMonths){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()
            ->from(array('c'=>'commodity'), 'DISTINCT(date) as dates')
            ->limit($numberOfMonths)
            ->order(array('dates DESC'))
            ->__toString();

        //echo $sql; exit;
        
        $result = $db->fetchAll($sql);
        
        //echo '1------: <br/>';
        //var_dump($result); 
        
        $dates = array();
        foreach($result as $key=>$date)
            $dates[] = $date['dates'];
        
        //echo '2------: <br/>';
        //var_dump($dates); 
        return $dates;
    }
    
    public function getPreviousMonthNames($numberOfMonths){
        $monthDates = $this->getPreviousMonthDates($numberOfMonths);
        return array_map(function($value){
                            return date('F', strtotime($value));
               }, $monthDates);
    }
    
    public function getPreviousMonthDatesPdf($numberOfMonths,$upperDate,$lowerDate){
        $db = $this->getDbAdapter();
        $sql = $db->select()
            ->from(array('c'=>'commodity'), 'DISTINCT(date) as dates')
            ->where("date<='$upperDate' AND date>='$lowerDate'")
            ->limit($numberOfMonths)
            ->order(array('dates DESC'))
            ->__toString();

        //echo $sql; exit;
        
        $result = $db->fetchAll($sql);
        
        //echo '1------: <br/>';
        //var_dump($result); 
        
        $dates = array();
        foreach($result as $key=>$date)
            $dates[] = $date['dates'];
        
        //echo '2------: <br/>';
        //var_dump($dates); 
        return $dates;
    }
    
    
    public function getLast12MonthsDate(){
        $report = new Report();
        $lastPullDate = $this->getLatestPullDate();
        $tempTime = strtotime($lastPullDate);

        $startYear = date('Y',strtotime("-11 month",$tempTime));
        $startMonth = date('m',strtotime("-11 month",$tempTime));

        $endYear = date('Y',$tempTime);
        $endMonth = date('m',$tempTime);

       list($startYears,$endYears) = $report->getMonthlyDateRange($startMonth,$startYear,$endMonth,$endYear);
       $monthNames = array();
       foreach($startYears as $startD){
           $formattedDate = date('M,Y',strtotime($startD));
           $monthNames[]  = $formattedDate;
       }

       $startYears = array_reverse($startYears);
       $monthNames = array_reverse($monthNames);
       return array($startYears,$monthNames);

    }
    
    
    
}
