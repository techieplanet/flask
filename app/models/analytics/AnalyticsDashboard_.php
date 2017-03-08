<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Overview
 *
 * @author Swedge
 */

class AnalyticsDashboard {
    
    /**
     * 
     * @param type $geoList array of selected locations. Defaults is the 6 zones
     * @param type $tierText location tier textual name e.g. geozone is 1
     * @return json containing the result array(location, totallogins)
     */
    public function fetchUserLoginsByLocation($geoList, $tierText){
        //unwind users by location level of $tierText
        //match users that are in $geolist
        //check that metric is login
        //check that login is last month
        //group the records by user location at $tiertext level, counting number of records per group
        
         $queryArray = array(
                        array('$unwind' => '$'.$tierText),
                        array('$match'  => array($tierText => array('$in'=>$geoList))),
                        array('$lookup' => array('from'=>"metrics", 'localField'=>"id", 'foreignField'=>"userid", 'as'=>"metrics")),
                        array('$match'  => array('metrics.action_type'=>  MetricClient::ACTION_TYPE_LOGIN, 'metrics.month'=>"July")),
                        array('$group'  => array('_id'=> array('location'=>'$'.$tierText), 'totalLogins' => array('$sum'=>1))),
                        //array('$sort'=>array('_id.location'=>-1))
                        //array('$group'  => array('_id'=> array('location'=>'$'.$tierText,'userid'=>'$metrics.userid','actiontype'=>'$metrics.action_type', 'month'=>'$metrics.month'), 'totalLogins' => array('$sum'=>1))),
                    );
        
         $metricClient = new MetricClient();
         $jsonData = $metricClient->handleDataGet($queryArray, array(), 'aggregate', 'users');
         
         //add missing locations to the result i.e. locations with no recordsd from the database
         $loginsCountArray = array();
         $jsonArray = json_decode($jsonData, TRUE);
         
         sort($geoList);
         
         foreach($geoList as $location){
             $locationFound = FALSE;
             foreach($jsonArray as $record){
                if($record['_id']['location'] == $location){
                    $loginsCountArray[] = array('location_name'=>$location, 'logins'=>$record['totalLogins']);
                    $locationFound = TRUE;
                    break;
                }
             }
             
             //if matching value not found at end of loop, insert 0s for unfound location
             if($locationFound == FALSE ){
                 $loginsCountArray[] = array('location_name'=>$location, 'logins'=>0);
             }
                 
        }
            
         return json_encode($loginsCountArray);
    }
    
    public function fetchDetailsByLocation($geoList,$tierText)
    {
        $userLoginsByLocation = $this->fetchUserLoginsByLocation($geoList, $tierText);
        $userLoginsByLocation = json_decode($userLoginsByLocation,TRUE);
        
        $metricClient = new MetricClient();
        
        $query = array(
            array('$unwind'=>'$'.$tierText),
            array('$match'=>array($tierText=>array('$in'=>$geoList))),
            array('$group'=>array
                    ('_id'=>array(
                    'location'=>'$'.$tierText),
                    'count'=>array('$sum'=>1))
                    )
        );
        
        $cursor = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        $locationCountArr = json_decode($cursor,TRUE);
        
        $newLocationCountArr = array();
        
        sort($geoList);
        
        foreach($geoList as $location)
        {
            
            $locationFound = FALSE;
            
            foreach($locationCountArr as $record)
            {
                
                
                if($location == $record['_id']['location'])
                {
                    
                    $newLocationCountArr[] = array('location_name'=>$location,'users_count'=>$record['count']);
                    $locationFound = TRUE;
                    break;
                }
            }
            
            if($locationFound == FALSE)
            {
                $newLocationCountArr[] = array('location_name'=>$location,'users_count'=>0);
            }
        }
        
        for($i=0;$i<count($newLocationCountArr);$i++)
        {
            $newLocationCountArr[$i]['logins'] = $userLoginsByLocation[$i]['logins'];
        }
       
        
        return $newLocationCountArr;
        
    }
}
