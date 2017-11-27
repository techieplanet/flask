<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DateFunctions
 *
 * @author swedge-mac
 */
class DateFunctions {
    //put your code here
    
    public function getDbAdapter(){
        
        return $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }
    
    public function getLatestPullDate(){
        $db = $this->getDbAdapter();
        $select = $db->select()
                      ->from(array('frr' => 'facility_report_rate'), 'MAX(frr.date) as maxdate');
	
	    $result = $db->fetchRow($select); 
            
           return $result['maxdate'];
           //return '2016-02-01';
    }
    
    /*
     * gets the latest 12 DHIS2 pull dates
     * You can also specify the latest month to start from
     * Args: numberOfMonths - number of months to backtrack
     */
    public function getPreviousMonthDates($numberOfMonths, $startDate=null){
        $db = $this->getDbAdapter();
        if(empty($startDate)){
            $sql = $db->select()
                ->from(array('c'=>'commodity'), 'DISTINCT(date) as dates')
                ->limit($numberOfMonths)
                ->order(array('dates DESC'))
                ->__toString();
        } else {
             $sql = $db->select()
                ->from(array('c'=>'commodity'), 'DISTINCT(date) as dates')
                ->where("date <= '$startDate'")
                ->limit($numberOfMonths)
                ->order(array('dates DESC'))
                ->__toString();
        }

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
    
    /**
     * Sort a multidimensional array by month key in internal arrays
     * @param type $array multidimensional array to sort
     * @param type $controlArray array with sorted values
     */
    public function multiSortMonths($array, $controlArray){
        $sortedArray = [];
        foreach($controlArray as $controlelement){
            $monthName = date('F', strtotime($controlelement));
            
            foreach($array as $element){
                if($element['month_name']==$monthName)
                    $sortedArray[] = $element;
            }
        }
        return $sortedArray;
    }
}
