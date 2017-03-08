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
    
    
    
    public function fetchUserLoginsByLocation($geoList, $tierText, $withLocation = false){
        //unwind users by location level of $tierText
        //match users that are in $geolist
        //check that metric is login
        //check that login is last month
        //group the records by user location at $tiertext level, counting number of records per group
         
        list($year, $month) = $this->getLastMonthAndYear();
         
         $query_date =   $year . "-" . $month . "-01";
         //$query_date2 =   $year . "-" . $month . "01";
         
         $start = "" .strtotime(date('Y-m-d', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
         
        $metricClient = new MetricClient();
        
        $query = array(
                    array('$match'=>
                        array(
                            '$and'=>array(
                                        array('action_type'=>MetricClient::ACTION_TYPE_LOGIN,
                                              'month'=>$month,'year'=>$year.'','millidate' => array('$gte'=>$start, '$lte'=>$end)
                                             )
                                         )   
                             )
                        ),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    //array('$unwind'=>'$users'),
                    //array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
        
        
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'metrics');
        
           //var_dump(count(json_decode($cursorJson))); exit;
        
           
           $jsonData = json_decode($cursorJson,TRUE);
           
           
           //var_dump($jsonData); exit();
           
           sort($geoList);
           
           $loginCount = array();
           $locationLogin = array();
           
           //Loop through all the geo list
            foreach($geoList as $k=>$location)
            {
                $logins = 0;
                
                //Loop through all the hits from metrics table
                 for($i=0; $i < count($jsonData); $i++)
                 {
                     
                         if($jsonData[$i]['users'][0][$tierText] == $location)
                         {
                             //var_dump("start : " . $start . ", Millidate : " . $jsonData[$i]['millidate']); exit();
                             
                             if($jsonData[$i]['action_type'] == 3 || $jsonData[$i]['action_type'] == '3'){
                                 $logins++;
                             }
                             
                         }
                     
                 }
                 $locationLogin[] = array("location_name"=>$location,"logins"=>$logins);
                 $loginCount[] = $logins;
            }
        
            if($withLocation){
                return json_encode($locationLogin);
            }
            
            return $loginCount;
    }
    
    public function fetchUserprofile($geoList,$tierText,$mode)
    {
        $metricClient = new MetricClient();
        
        if($mode !== "all")
        {
            $query = array(
                array('$unwind'=>'$'.$tierText),
                array('$match'=>array(''.$tierText=>array('$in'=>$geoList))),
                array('$sort'=>array('last_name'=>1))
            );
        }
        else
        {
            $query = array(
                array('$sort'=>array('last_name'=>1))
            );
        }
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        
        return $cursorJson;
    }
    
    public function fetchUserLoginsLastMonthsByLocation2($geoList, $tierText){
        //unwind users by location level of $tierText
        //match users that are in $geolist
        //check that metric is login
        //check that login is last month
        //group the records by user location at $tiertext level, counting number of records per group
         
         date_default_timezone_set("Africa/Lagos");
     
        
         $monthYear = $this->getPrevious12Months();
         $loginsCount12MonthsArray = array();
         
         foreach($monthYear as $key => $myear) {
         
         $query_date =   $myear['year'] . "-" . $myear['month'] . "-01";
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
         
         $query = array(
                    array('$match'=>
                        array(
                            '$and'=>array(
                                        array('action_type'=>MetricClient::ACTION_TYPE_LOGIN,
                                              'month'=>$myear['month'],'year'=>$myear['year'].'','millidate' => array('$gte'=>$start, '$lte'=>$end)
                                             )
                                         )   
                             )
                        ),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    //array('$unwind'=>'$users'),
                    //array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
        
        
        $metricClient = new MetricClient();
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'metrics');
        
           //var_dump(count(json_decode($cursorJson))); exit;
        
           
           $jsonData = json_decode($cursorJson,TRUE);
           
           
           //var_dump($jsonData); exit();
           
           sort($geoList);
           
           $loginsCountArray = array();
           
           //Loop through all the geo list
            foreach($geoList as $k=>$location)
            {
                $logins = 0;
                
                //Loop through all the hits from metrics table
                 for($i=0; $i < count($jsonData); $i++)
                 {
                     
                         if($jsonData[$i]['users'][0][$tierText] == $location)
                         {
                             //var_dump("start : " . $start . ", Millidate : " . $jsonData[$i]['millidate']); exit();
                             
                             if($jsonData[$i]['action_type'] == 3 || $jsonData[$i]['action_type'] == '3'){
                                 $logins++;
                             }
                             
                         }
                     
                 }
                 $loginsCountArray[] = array('location_name'=>$location, 'logins'=>$logins);
                 
            }
        
        $loginsCountArray['timeline'] = substr($myear['month'], 0, 3) . ' ' . $myear['year'];
        
        $loginsCount12MonthsArray[] =  $loginsCountArray;
        
     }   // End of 12 months loop
       
     
         //Process array and get TimeLine
         $timeline = array();
         $seriesContainer = array(); 
         foreach($loginsCount12MonthsArray as $k1=>$v1)
         {
             foreach($v1 as $k2=>$v2)
             {
                 $timeline[] = $v1['timeline'];
                 break;
             }
             
             continue;
         }
         
         $loginsCount12MonthsArray2 = $loginsCount12MonthsArray;
         
         /**
          * Iterate over the data to get the series and data
          * for plotting the line chart. NB: series is location name.
          */
         
         foreach ($loginsCount12MonthsArray as $k1=>$v1) {
               //Loop through the first record in the array called v1 of type Array
               foreach($v1 as $k2=>$v2) {   
                   
                  if(isset($v2['location_name']))
                   {
                        $location = $v2['location_name'];
                       
                   
                      
                       $arr = array();
                   
                       
                       foreach ($loginsCount12MonthsArray2 as $k3=>$v3)
                         {
                            
                            foreach($v3 as $k4=>$v4)
                            {
                                
                              if(isset($v4['location_name']))
                              {     
                                if($location == $v4['location_name'])
                                {
                                    $arr[] = $v4['logins'];
                                    break;
                                }
                              }
                            }
                         }
                         
                         $seriesContainer[] = array('name'=>$v2['location_name'],'data'=>$arr);
                   }
               }
              
               break;
         }
         
//         $loginsCount12MonthsArray['categories'] = $timeline;
//         $loginsCount12MonthsArray['series'] = $seriesContainer;
//         var_dump($seriesContainer); exit;
         
         
         $dataToCache = array('categories'=>$timeline,'series'=>$seriesContainer);
         
         file_put_contents("overview_linechart_cache.json", json_encode($dataToCache));
         
         
         
         return json_encode(array('categories'=>$timeline,'series'=>$seriesContainer));
    }
    
    
    public function fetchUserLoginsLastMonthsByLocationFilter($geoList, $tierText){
        //unwind users by location level of $tierText
        //match users that are in $geolist
        //check that metric is login
        //check that login is last month
        //group the records by user location at $tiertext level, counting number of records per group
         
         date_default_timezone_set("Africa/Lagos");
     
        
         $monthYear = $this->getPrevious12Months();
         $loginsCount12MonthsArray = array();
         
         foreach($monthYear as $key => $myear) {
         
         $query_date =   $myear['year'] . "-" . $myear['month'] . "-01";
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
       
         
         $query = array(
                    array('$match'=>
                        array(
                            '$and'=>array(
                                        array('action_type'=>MetricClient::ACTION_TYPE_LOGIN,
                                              'month'=>$myear['month'],'year'=>$myear['year'].'','millidate' => array('$gte'=>$start, '$lte'=>$end)
                                             )
                                         )   
                             )
                        ),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    //array('$unwind'=>'$users'),
                    //array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
        
        
        $metricClient = new MetricClient();
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'metrics');
        
           //var_dump(count(json_decode($cursorJson))); exit;
        
           
           $jsonData = json_decode($cursorJson,TRUE);
           
           
           //var_dump($jsonData); exit();
           
           sort($geoList);
           
           $loginsCountArray = array();
           
           //Loop through all the geo list
            foreach($geoList as $k=>$location)
            {
                $logins = 0;
                
                //Loop through all the hits from metrics table
                 for($i=0; $i < count($jsonData); $i++)
                 {
                     
                         if($jsonData[$i]['users'][0][$tierText] == $location)
                         {
                             //var_dump("start : " . $start . ", Millidate : " . $jsonData[$i]['millidate']); exit();
                             
                             if($jsonData[$i]['action_type'] == 3 || $jsonData[$i]['action_type'] == '3'){
                                 $logins++;
                             }
                             
                         }
                     
                 }
                 $loginsCountArray[] = array('location_name'=>$location, 'logins'=>$logins);
                 
            }
        
        $loginsCountArray['timeline'] = substr($myear['month'], 0, 3) . ' ' . $myear['year'];
        
        $loginsCount12MonthsArray[] =  $loginsCountArray;
        
     }
     
         //Process array and get TimeLine
         $timeline = array();
         $seriesContainer = array(); 
         foreach($loginsCount12MonthsArray as $k1=>$v1)
         {
             foreach($v1 as $k2=>$v2)
             {
                 $timeline[] = $v1['timeline'];
                 break;
             }
             
             continue;
         }
         
         $loginsCount12MonthsArray2 = $loginsCount12MonthsArray;
         
         /**
          * Iterate over the data to get the series and data
          * for plotting the line chart. NB: series is location name.
          */
         
         foreach ($loginsCount12MonthsArray as $k1=>$v1) {
               //Loop through the first record in the array called v1 of type Array
               foreach($v1 as $k2=>$v2) {   
                   
                  if(isset($v2['location_name']))
                   {
                        $location = $v2['location_name'];
                       
                   
                      
                       $arr = array();
                   
                       
                       foreach ($loginsCount12MonthsArray2 as $k3=>$v3)
                         {
                            
                            foreach($v3 as $k4=>$v4)
                            {
                                
                              if(isset($v4['location_name']))
                              {     
                                if($location == $v4['location_name'])
                                {
                                    $arr[] = $v4['logins'];
                                    break;
                                }
                              }
                            }
                         }
                         
                         $seriesContainer[] = array('name'=>$v2['location_name'],'data'=>$arr);
                   }
               }
              
               break;
         }
         
//         $loginsCount12MonthsArray['categories'] = $timeline;
//         $loginsCount12MonthsArray['series'] = $seriesContainer;
//         var_dump($seriesContainer); exit;
         

         return json_encode(array('categories'=>$timeline,'series'=>$seriesContainer));
    }
    
    public function fetchUserLoginsLastMonthsByLocation($geoList, $tierText){
        //unwind users by location level of $tierText
        //match users that are in $geolist
        //check that metric is login
        //check that login is last month
        //group the records by user location at $tiertext level, counting number of records per group
         
        if($tierText != 'geozone'){
            return $this->fetchUserLoginsLastMonthsByLocationFilter($geoList, $tierText);
        }
        
         date_default_timezone_set("Africa/Lagos");
     
        
         $monthYear = $this->getPrevious12Months();
         $loginsCount12MonthsArray = array();
         
         $len = count($monthYear);
         
         $query_date =   $monthYear[11]['year'] . "-" . $monthYear[11]['month'] . "-01";
         
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
//         $queryArray = array(
//                        array('$unwind' => '$'.$tierText),
//                        array('$match'  => array($tierText => array('$in'=>$geoList))),
//                        array('$lookup' => array('from'=>"metrics", 'localField'=>"id", 'foreignField'=>"userid", 'as'=>"metrics")),
//                        array('$match'  => array('metrics.action_type'=>  MetricClient::ACTION_TYPE_LOGIN, 'metrics.month'=>$monthYear[11]['month'].'','metrics.year'=>$monthYear[11]['year'].'','metrics.millidate' => array('$gte'=>$start, '$lt'=>$end))),
//                        array('$group'  => array('_id'=> array('location'=>'$'.$tierText), 'totalLogins' => array('$sum'=>1))),
//                        
//                    );
//        
//         $metricClient = new MetricClient();
//         $jsonData = $metricClient->handleDataGet($queryArray, array(), 'aggregate', 'users');
//         
//         
//         //add missing locations to the result i.e. locations with no recordsd from the database
//         $loginsCountArray = array();
//         $jsonArray = json_decode($jsonData, TRUE);
//         
//         sort($geoList);
//         
//         
//         /**
//          * This section iterates over location array, and checks the total login
//          * for a particular location in that month of year 
//          */
//         foreach($geoList as $location){
//             $locationFound = FALSE;
//             foreach($jsonArray as $record){
//                if($record['_id']['location'] == $location){
//                    $loginsCountArray[] = array('location_name'=>$location, 'logins'=>$record['totalLogins']);
//                    $locationFound = TRUE;
//                    break;
//                }
//             }
//             
//             //if matching value not found at end of loop, insert 0s for unfound location
//             if($locationFound == FALSE ){
//                 $loginsCountArray[] = array('location_name'=>$location, 'logins'=>0);
//             }
//            
//        }
         
         
//        
//        $loginsCountArray['timeline'] = substr($monthYear[11]['month'], 0, 3) . ' ' . $monthYear[11]['year'];
//        
//        $loginsCount12MonthsArray[] =  $loginsCountArray;
         
         
         $query = array(
                    array('$match'=>
                        array(
                            '$and'=>array(
                                        array('action_type'=>MetricClient::ACTION_TYPE_LOGIN,
                                              'month'=>$monthYear[11]['month'],'year'=>$monthYear[11]['year'].'','millidate' => array('$gte'=>$start, '$lte'=>$end)
                                             )
                                         )   
                             )
                        ),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    //array('$unwind'=>'$users'),
                    //array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
        
        
        $metricClient = new MetricClient();
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'metrics');
        
           //var_dump(count(json_decode($cursorJson))); exit;
        
           
           $jsonData = json_decode($cursorJson,TRUE);
           
           
           //var_dump($jsonData); exit();
           
           sort($geoList);
           
           $loginsCountArray = array();
           
           //Loop through all the geo list
            foreach($geoList as $k=>$location)
            {
                $logins = 0;
                
                //Loop through all the hits from metrics table
                 for($i=0; $i < count($jsonData); $i++)
                 {
                     
                         if($jsonData[$i]['users'][0][$tierText] == $location)
                         {
                             //var_dump("start : " . $start . ", Millidate : " . $jsonData[$i]['millidate']); exit();
                             
                             if($jsonData[$i]['action_type'] == 3 || $jsonData[$i]['action_type'] == '3'){
                                 $logins++;
                             }
                             
                         }
                     
                 }
                 $loginsCountArray[] = array('location_name'=>$location, 'logins'=>$logins);
                 
            }
        
        $loginsCountArray['timeline'] = substr($monthYear[11]['month'], 0, 3) . ' ' . $monthYear[11]['year'];
        
        $loginsCount12MonthsArray[] =  $loginsCountArray;
        
     
        
        // End of 12 months loop
       
     
         //Process array and get TimeLine
         $timeline = array();
         $seriesContainer = array(); 
         foreach($loginsCount12MonthsArray as $k1=>$v1)
         {
             foreach($v1 as $k2=>$v2)
             {
                 $timeline[] = $v1['timeline'];
                 break;
             }
             
             continue;
         }
         
         $loginsCount12MonthsArray2 = $loginsCount12MonthsArray;
         
         /**
          * Iterate over the data to get the series and data
          * for plotting the line chart. NB: series is location name.
          */
         
         foreach ($loginsCount12MonthsArray as $k1=>$v1) {
               //Loop through the first record in the array called v1 of type Array
               foreach($v1 as $k2=>$v2) {   
                   
                  if(isset($v2['location_name']))
                   {
                        $location = $v2['location_name'];
                       
                   
                      
                       $arr = array();
                   
                       
                       foreach ($loginsCount12MonthsArray2 as $k3=>$v3)
                         {
                            
                            foreach($v3 as $k4=>$v4)
                            {
                                
                              if(isset($v4['location_name']))
                              {     
                                if($location == $v4['location_name'])
                                {
                                    $arr[] = $v4['logins'];
                                    break;
                                }
                              }
                            }
                         }
                         
                         $seriesContainer[] = array('name'=>$v2['location_name'],'data'=>$arr);
                   }
               }
              
               break;
         }
         

         
          $cachedData = json_decode(file_get_contents("overview_linechart_cache.json"),true);
          
          $newLineChartData = array();
          
          foreach($cachedData['series'] as $k1=>$val){
              
              foreach($seriesContainer as $k2 => $series){
                  if($cachedData['series'][$k1]['name'] == $series['name'] ){
                      //edit the value in the last index of the series array, with the recent month series value
                      $cachedData['series'][$k1]['data'][11] = $series['data'][0];
                      $newLineChartData['series'][] = $cachedData['series'][$k1];
                      //echo $series['name'] . '<br />';
                  }
              }
              
          }
          
          $newLineChartData['categories'] = $cachedData['categories'];
          
         // var_dump($newLineChartData);
          
         return json_encode($newLineChartData);
    }
    
    public function fetchUserSessionsByLocation($geoList, $tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        
        list($year, $month) = $this->getLastMonthAndYear();
         
         $query_date =   $year . "-" . $month . "-01";
         //$query_date2 =   $year . "-" . $month . "01";
         
         $start = "" .strtotime(date('Y-m-d', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
         
        $metricClient = new MetricClient();
        
        $query = array(
                    array('$match'=>
                        array(
                            '$and'=>array(
                                        array('action_type'=>array('$in'=>array(1,2,3,4,5)),
                                              'month'=>$month,'year'=>$year.'','millidate' => array('$gte'=>$start, '$lte'=>$end)
                                             )
                                         )   
                             )
                        ),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    //array('$unwind'=>'$users'),
                    //array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
        
        
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'metrics');
        
           //var_dump(count(json_decode($cursorJson))); exit;
        
           
           $jsonData = json_decode($cursorJson,TRUE);
           
           
           //var_dump($jsonData); exit();
           
           sort($geoList);
           $activeSessionsByLocation = array();
           
           $len = count($jsonData);
           
           //Loop through all the geo list
            foreach($geoList as $k=>$location)
            {
                $sessions = 0;
                
                //Loop through all the hits from metrics table
                 for($i=0; $i < count($jsonData); $i++)
                 {
                     
                     
                         if($jsonData[$i]['users'][0][$tierText] == $location)
                         {
                             //$diff = $jsonData[$i]['millidate'] - (($i == $len -1) ? $jsonData[$i+1]['millidate'] : $jsonData[$i]['millidate']) ;
                             
                             if($jsonData[$i]['action_type'] == 3 || $jsonData[$i]['action_type'] == '3'){
                                 $sessions++;
                             }
                             else{
                                   if(isset($jsonData[$i+1]['userid'])){
                                       
                                       if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid']){
                                           $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];
                                           if($diff >= 60000 * 15){
                                               $sessions++;
                                           }
                                       }
                                   }
                             }
                         }
                     
                 }
                 
                 $activeSessionsByLocation[] = $sessions;
            }
        
        return $activeSessionsByLocation;
    }
    
    public function fetchActiveUsersByLocation($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 23:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 23:59:59")) * 1000;
        
        
        list($year, $month) = $this->getYearMonth();
        
        
        $metricClient = new MetricClient();
        
        $now = time() * 1000;
        $diff = $now - 900000;
        $diff = $diff . '';
        $query2 = array(
                    array('$match'=>array('month'=>$month,'year'=>$year . '','millidate' => array('$gte'=>$yday, '$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );
       
        $cursorJson = $metricClient->handleDataGet($query2, array(), 'aggregate', 'metrics');
        
        $currentActive = json_decode($cursorJson,TRUE);
        
        $activeUsersByLocation = array();
        sort($geoList);
        
        foreach($geoList as $location)
        {
                $count = 0;
                $match = null;
                
                foreach($currentActive as $k=>$val)
                {
                       if($location == $val['users'][$tierText])
                       {
                           //$count += 1;

                                $userid = $val['userid'];

                                if($userid == $match)
                                    continue;
                                else
                                {
                                    if(!isset($currentActive[$k+1]['userid']))
                                        continue;
                                    if($userid == $currentActive[$k+1]['userid'])
                                    {

                                        $match = $userid;

                                        $diff = $now - $val['millidate'];

                                        if($diff <= 1800000)
                                        {
                                            $count += 1;
                                        }
                                    }
                                }
                       }
                }
                
                $activeUsersByLocation[] = $count; 
        }
        
        return $activeUsersByLocation;
        
    }
    
    public function fetchDetailsByLocation($geoList,$tierText)
    {
        
        $userLoginsByLocation = $this->fetchUserLoginsByLocation($geoList, $tierText);
        $activeUsersByLocation = $this->fetchActiveUsersByLocation($geoList, $tierText);
        $activeSessionsByLocation = $this->fetchUserSessionsByLocation($geoList, $tierText);
        
        
        
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
        
        list($year, $month) = $this->getLastMonthAndYear();
        // Adding different Table fields 
        for($i=0;$i<count($newLocationCountArr);$i++)
        {
            $newLocationCountArr[$i]['logins'] = $userLoginsByLocation[$i];
            $newLocationCountArr[$i]['active_users'] = $activeUsersByLocation[$i]; 
            
            if($userLoginsByLocation[$i]['logins'] == 1 && $activeSessionsByLocation[$i] == 0){
                $newLocationCountArr[$i]['total_sessions'] = 1;
            }else{
                $newLocationCountArr[$i]['total_sessions'] = $activeSessionsByLocation[$i];
            }
        }
       
        $newLocationCountArr['timeline'] = $month . ", " . $year;
        
        return $newLocationCountArr;
        
    }
    
    /**
     * Functions for Modules sections starts here {Charts, Queries, DataCollection}
     */
    
    
    
    //This function returns json data for bar chart in Modules-Charts Section
    public function fetchDailySessionsByChartSubButton($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $firstDay = date("Y-m-d",strtotime("first day of last month"));
        $start = "" .strtotime(date($firstDay . " 00:00:00")) * 1000;
        $lastDay = date("Y-m-d",strtotime("last day of last month"));
        $end = "" .strtotime(date($lastDay . " 00:00:00")) * 1000;
        
         $chartPages = array(
            'Trained HWs'=>'charts_coverage_cummhwtrained',
            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
            'Commodity Consumption'=>'charts_consumption_consumption',
            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
        );
        
        $pages = array();
        
        list($year, $month) = $this->getLastMonthAndYear();
        
        $metricClient = new MetricClient();
        
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'charts','page_id'=>$val,'month'=>$month,'year'=>$year.'','millidate' => array('$gte'=>$start),'millidate' => array('$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                         if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                         {
                             $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                             if($diff >= 900000)
                             {
                                 $sessions++;
                             }
                         }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        
        $pages['year'] = $year;
        $pages['month'] = $month;
        
       return json_encode($pages);
    }
    
    
    
    //This function caches json data for Linechart in Modules-Charts Section
    public function cacheDailySessionsLastMonthsByCharts($geoList,$tierText)
    {
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
                
         $monthsYear = $this->getPrevious12Months();
        
         $chartPages = array(

             'Trained HWs'=>'charts_coverage_cummhwtrained',
            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
            'Commodity Consumption'=>'charts_consumption_consumption',
            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'charts','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
       file_put_contents("charts_total_daily_sessions.json", json_encode($perMonthPage));  
       return json_encode($perMonthPage);
    }
    
    
    //This function returns json data for Linechart in Modules-Charts Section
    public function fetchDailySessionsLastMonthsByChartsFilter($geoList,$tierText)
    {
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
                
         $monthsYear = $this->getPrevious12Months();
         
         
        
         $chartPages = array(

             'Trained HWs'=>'charts_coverage_cummhwtrained',
            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
            'Commodity Consumption'=>'charts_consumption_consumption',
            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'charts','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
         
       return json_encode($perMonthPage);
    }
    
    
    //This function returns json data for Linechart in Modules-Charts Section
    public function fetchDailySessionsLastMonthsByCharts($geoList,$tierText)
    {
        
        if($tierText != 'geozone'){
            return $this->fetchDailySessionsLastMonthsByChartsFilter($geoList, $tierText);
        }

        
        date_default_timezone_set("Africa/Lagos");
        
                
         $monthYear = $this->getPrevious12Months();
         
         $query_date =   $monthYear[11]['year'] . "-" . $monthYear[11]['month'] . "-01";
         
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
         $chartPages = array(

             'Trained HWs'=>'charts_coverage_cummhwtrained',
            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
            'Commodity Consumption'=>'charts_consumption_consumption',
            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
            
        );
        
        
        
        
        
        $metricClient = new MetricClient();
       
   
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'charts','page_id'=>$val,'month'=>$monthYear[11]['month'].'','year'=>$monthYear[11]['year'].'','millidate' => array('$gte'=>$start,'$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($monthYear[11]['month'],0,3) . ' ' . $monthYear[11]['year'];
        
        $cacheData = json_decode(file_get_contents("charts_total_daily_sessions.json"));
        
        $cacheData[11] = $pages;
    
         
       return json_encode($cacheData);
    }
    
    
    
    //This function returns json data for bar-chart in Modules-Queries Section
    public function fetchDailySessionsByQueries($geoList,$tierText)
    {
        date_default_timezone_set("Africa/Lagos");
        
       
        $monthYear = $this->getPrevious12Months();

        
         $query_date =   $monthYear[10]['year'] . "-" . $monthYear[10]['month'] . "-01";
         
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;        
        
         $queryPages = array(
            'All Data'=>'reports_reports_alldata',
             'Training Data'=>'reports_reports_trainingrep',
             'Facility Data'=>'reports_reports_facilitysummary',
             'View/Edit Training'=>'reports_training_view',
             'View/Edit Person'=>'reports_person_view',
             'View/Edit Facility'=>'reports_facility_search',
             'View/Edit Training Location'=>'reports_facility_searchLocation',
             'Archived PDF Report'=>'reports_pdf_archivedreports'
        );
        
        $pages = array();
        
        list($year,$month) = $this->getLastMonthAndYear();
        
        $metricClient = new MetricClient();
        
        foreach($queryPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'reports','page_id'=>$val,'month'=>$month,'year'=>$year,'millidate' => array('$gte'=>$start,'$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
         
       $pages['year'] = $year;
       $pages['month'] = $month;
       
       return json_encode($pages);
    }
    
    
    //This function caches json data for Linechart in Modules-Queries Section
    public function cacheDailySessionsLastMonthsByQueries($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
        
         $monthsYear = $this->getPrevious12Months();
        
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
             
             'All Data'=>'reports_reports_alldata',
             'Training Data'=>'reports_reports_trainingrep',
             'Facility Data'=>'reports_reports_facilitysummary',
             'View/Edit Training'=>'reports_training_view',
             'View/Edit Person'=>'reports_person_view',
             'View/Edit Facility'=>'reports_facility_search',
             'View/Edit Training Location'=>'reports_facility_searchLocation',
             'Archived PDF Report'=>'reports_pdf_archivedreports'
//             'All Queries Report'=>'reports_reports_allqueriesresult',
//             'Training Report'=>'reports_reports_training_result',
//             'Reports Summary'=>'reports_reports_summaryresult',
//             'Facility Summary Report'=>'reports_reports_facilitysummarydemo'
             
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'reports','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
    
       file_put_contents("query_daily_sessions_by_query.json", json_encode($perMonthPage));
         
       return json_encode($perMonthPage);
    }
    
    //This function returns filtered json data for Linechart in Modules-Queries Section
    public function fetchDailySessionsLastMonthsByQueriesFilter($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
        
         $monthsYear = $this->getPrevious12Months();
        
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
             
             'All Data'=>'reports_reports_alldata',
             'Training Data'=>'reports_reports_trainingrep',
             'Facility Data'=>'reports_reports_facilitysummary',
             'View/Edit Training'=>'reports_training_view',
             'View/Edit Person'=>'reports_person_view',
             'View/Edit Facility'=>'reports_facility_search',
             'View/Edit Training Location'=>'reports_facility_searchLocation',
             'Archived PDF Report'=>'reports_pdf_archivedreports'
//             'All Queries Report'=>'reports_reports_allqueriesresult',
//             'Training Report'=>'reports_reports_training_result',
//             'Reports Summary'=>'reports_reports_summaryresult',
//             'Facility Summary Report'=>'reports_reports_facilitysummarydemo'
             
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'reports','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
         
       return json_encode($perMonthPage);
    }
    
    //This function returns json data for Linechart in Modules-Queries Section
    public function fetchDailySessionsLastMonthsByQueries($geoList,$tierText)
    {
        
        if($tierText != 'geozone'){
            return $this->fetchDailySessionsLastMonthsByQueriesFilter($geoList, $tierText);
        }
        
        date_default_timezone_set("Africa/Lagos");
        
        
        
         $monthYear = $this->getPrevious12Months();
 
         $query_date =   $monthYear[11]['year'] . "-" . $monthYear[11]['month'] . "-01";
         
         
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
         
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
             
             'All Data'=>'reports_reports_alldata',
             'Training Data'=>'reports_reports_trainingrep',
             'Facility Data'=>'reports_reports_facilitysummary',
             'View/Edit Training'=>'reports_training_view',
             'View/Edit Person'=>'reports_person_view',
             'View/Edit Facility'=>'reports_facility_search',
             'View/Edit Training Location'=>'reports_facility_searchLocation',
             'Archived PDF Report'=>'reports_pdf_archivedreports'
//             'All Queries Report'=>'reports_reports_allqueriesresult',
//             'Training Report'=>'reports_reports_training_result',
//             'Reports Summary'=>'reports_reports_summaryresult',
//             'Facility Summary Report'=>'reports_reports_facilitysummarydemo'
             
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
    
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'reports','page_id'=>$val,'month'=>$monthYear[11]['month'].'','year'=>$monthYear[11]['year'].'','millidate' => array('$gte'=>$start,'$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($monthYear[11]['month'],0,3) . ' ' . $monthYear[11]['year'];
        
        $cacheData = json_decode(file_get_contents("query_daily_sessions_by_query.json"));
        
        $cacheData[11] = $pages;
         
       return json_encode($cacheData);
    }
    
    
     public function fetchDailySessionsByDataCollection($geoList,$tierText)
    {
        date_default_timezone_set("Africa/Lagos");
        
        $firstDay = date("Y-m-d",strtotime("first day of last month"));
        $start = "" .strtotime(date($firstDay . " 00:00:00")) * 1000;
        $lastDay = date("Y-m-d",strtotime("last day of last month"));
        $end = "" .strtotime(date($lastDay . " 00:00:00")) * 1000;
         
         $dcPages = array(
//            'Add New Training'=>'dc_training_add',
            'Import Training'=>'dc_training_import',
            'Add New Person'=>'dc_person_add',
//            'Edit Person'=>'dc_person_edit',
            'Add Training Location'=>'dc_facility_addLocation'
        );
        
        $pages = array();
        
        list($year,$month) = $this->getLastMonthAndYear();
        
        $metricClient = new MetricClient();
        
        foreach($dcPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'dc','page_id'=>$val,'month'=>$month,'year'=>$year .'','millidate' => array('$gte'=>$start),'millidate' => array('$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        
        $pages['year'] = $year;
        $pages['month'] = $month;
        $pages['day'] = $this->getDay();
         
       return json_encode($pages);
    }
    
    
    
    //This function caches json data for Linechart in Modules-Queries Section
    public function cacheDailySessionsLastMonthsByDC($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
        
         $monthsYear = $this->getPrevious12Months();
        
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
//            'Add New Training'=>'dc_training_add',
            'Import Training'=>'dc_training_import',
            'Add New Person'=>'dc_person_add',
//            'Edit Person'=>'dc_person_edit',
            'Add Training Location'=>'dc_facility_addLocation'
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'dc','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
         
      file_put_contents("dc_total_daily_sessions.json",json_encode($perMonthPage));
    
       return json_encode($perMonthPage);
    }
    
    
    //This function returns filtered json data for Linechart in Modules-Queries Section
    public function fetchDailySessionsLastMonthsByDCFilter($geoList,$tierText)
    {
        
        date_default_timezone_set("Africa/Lagos");
        
        $yday = date("Y-m-d",strtotime("-1 days"));
        $yday = "" .strtotime(date($yday . " 00:00:00")) * 1000;
        $today = "" . strtotime(date("Y-m-d 00:00:00")) * 1000;
        
         $monthsYear = $this->getPrevious12Months();
        
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
//            'Add New Training'=>'dc_training_add',
            'Import Training'=>'dc_training_import',
            'Add New Person'=>'dc_person_add',
//            'Edit Person'=>'dc_person_edit',
            'Add Training Location'=>'dc_facility_addLocation'
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    foreach ($monthsYear as $k1=>$dates)
    {
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'dc','page_id'=>$val,'month'=>$dates['month'].'','year'=>$dates['year'].'','millidate' => array('$lte'=>$today))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($dates['month'],0,3) . ' ' . $dates['year'];
        $perMonthPage[] = $pages;
        
    }
         
       return json_encode($perMonthPage);
    }
    
    //This function returns json data for Linechart in Modules-Queries Section
    public function fetchDailySessionsLastMonthsByDC($geoList,$tierText)
    {

        if($tierText != 'geozone'){
            return $this->fetchDailySessionsLastMonthsByDCFilter($geoList, $tierText);
        }
        
        
        
         date_default_timezone_set("Africa/Lagos");
         
         $monthYear = $this->getPrevious12Months();
        
         $query_date =   $monthYear[11]['year'] . "-" . $monthYear[11]['month'] . "-01";
         $start = "" .strtotime(date('Y-m-01', strtotime($query_date))) * 1000;
         $end = "" .strtotime(date('Y-m-t', strtotime($query_date))) * 1000;
        
         
        
         $chartPages = array(
//            'Trained HWs'=>'charts_coverage_cummhwtrained',
//            'Facilities with Trained HWs'=>'charts_coverage_percentfacswithtrainedhw',
//            'Facilities Providing FP'=>'charts_coverage_percentfacsproviding',
//            'Facilities providing FP over time'=>'charts_coverage_providingovertime',
//            'Facilities with trained HWs providing FP'=>'charts_coveragefacswithhwproviding',
//            'Facilities with Trained HWs providing FP over time'=>'charts_coverage_coverageovertime',
//            'Commodity Consumption'=>'charts_consumption_consumption',
//            'New FP acceptors and current FP users'=>'charts_consumption_newandcurrentfpusers',
//            'stock outs at facilities with trained HWS'=>'charts_stockout_percentstockoutwithtrainedhw',
//            'Stock out at facilities providing FP'=>'charts_stockout_percentfacsprovidingbutstockedout',
//            'stock outs at facilities providing FP over time'=>'charts_stockout_stockouts'
//            'Add New Training'=>'dc_training_add',
            'Import Training'=>'dc_training_import',
            'Add New Person'=>'dc_person_add',
//            'Edit Person'=>'dc_person_edit',
            'Add Training Location'=>'dc_facility_addLocation'
            
        );
        
        $perMonthPage = array();
        
        
        
        $metricClient = new MetricClient();
       
    
        $pages = array();
        foreach($chartPages as $k=>$val)
        {
                $query = array(
                    array('$match'=>array('module_name'=>'dc','page_id'=>$val,'month'=>$monthYear[11]['month'].'','year'=>$monthYear[11]['year'].'','millidate' => array('$gte'=>$start,'$lte'=>$end))),
                    array('$lookup'=>array(
                       'from'=>'users',
                        'localField'=>'userid',
                        'foreignField'=>'id',
                        'as'=>'users'
                    )),
                    array('$unwind'=>'$users'),
                    array('$unwind'=>'$users.'.$tierText),
                    array('$match'=>array('users.'.$tierText=>array('$in'=>$geoList))),
                    array('$sort'=>array('userid'=>1,'millidate'=>-1))
                );

                $cursorJson = $metricClient->handleDataGet($query,array(),'aggregate','metrics');

                $jsonData = json_decode($cursorJson,TRUE);

                 $sessions = 0;
                 for($i=0; $i < count($jsonData)-2; $i++)
                 {
                     if(isset($jsonData[$i]['userid']) && isset($jsonData[$i+1]['userid'])
                             && isset($jsonData[$i]['millidate']) && isset($jsonData[$i+1]['millidate'])){
                     if($jsonData[$i]['userid'] == $jsonData[$i+1]['userid'])
                     {
                         $diff = $jsonData[$i]['millidate'] - $jsonData[$i+1]['millidate'];


                         if($diff >= 900000)
                         {
                             $sessions++;
                         }
                     }
                     }
                 }
                 
            $pages[$k] = $sessions; 
        }
        $pages['timeline'] = substr($monthYear[11]['month'],0,3) . ' ' . $monthYear[11]['year'];
        
        $cacheData = json_decode(file_get_contents("dc_total_daily_sessions.json"));
        $cacheData[11] = $pages;
         
       return json_encode($cacheData);
    }
    
    //This function returns json data for BarChart in Users Activity Modules 
    function fetchSumTotalUsersByGeo($geoList,$tierText)
    {
        
        $metricClient = new MetricClient();
      
        $query = array(
            array('$unwind'=>'$'.$tierText),
            array('$match'=>array(''.$tierText => array('$in'=>$geoList)))
        );
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        $arrData = json_decode($cursorJson,TRUE);
        list($year,$month) = $this->getLastMonthAndYear('m');
        
        $filteredCursor = array();
        
        foreach ($arrData as $k=>$val)
        {
            $dateString = explode(" ",$val['timestamp_created']);
            $dateString = explode("-",$dateString[0]);
            $y = $dateString[0];
            $m = $dateString[1];
            
            
                if($y <= $year && $m <= $month)
                {
                    if(isset($val[$tierText]))
                      $filteredCursor[] = array(''.$tierText=>$val[$tierText]);
                }
            
        }
        
        
        sort($geoList);
        $userByLocation = array();
        
        foreach ($geoList as $location)
        {
            $count = 0;
            
            foreach ($filteredCursor as $cursor)
            {
                if($cursor[$tierText] == $location)
                {
                    $count++;
                }
            }
            
            $userByLocation[$location] = $count;
        }
        
        list($newYear, $month) = $this->getLastMonthAndYear();
        $userByLocation['date'] = $month . ', ' . $year;
        
        return $userByLocation;
        
    }
    
    
    //This function caches json data for LineChart in User's Activity Module
    function cacheSumTotalUsersLast12Months($geoList,$tierText)
    {
        $metricClient = new MetricClient();
       
        $monthYear = $this->getPrevious12Months('m');
       
        
        $query = array(
            array('$unwind'=>'$'.$tierText),
            array('$match'=>array(''.$tierText => array('$in'=>$geoList)))
        );
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        sort($geoList);
        $cursorArray = json_decode($cursorJson,TRUE);
        
        $series = array();
        
        
            foreach ($geoList as $location)
            {
              $tempSeries = array(); //structure {'SE'=>[22,1,3,4,3,6,5,8,9,0,0,0]}
              
               foreach ($monthYear as $my)
                {
                    $count = 0;
                    
                    foreach($cursorArray as $cursor)
                    {
                        $dateString = explode(" ",$cursor['timestamp_created']);
                        $dateString = explode("-",$dateString[0]);
                        $y = $dateString[0];
                        $m = $dateString[1];

                        if( $y <= $my['year'] && $m <= $my['month']  && $cursor[$tierText] == $location)
                        {
                                $count++;
                        }
                    }//Cursor iteration ends here
                    
                    $tempSeries[] = $count;
                    
                } // Date iteration ends here
                
                $series[$location] = $tempSeries;
            }// location iteration ends here
            
            
            //prepare category by concatenating month and year
            
            $newMonthYear = $this->getPrevious12Months();
            $monthYearStringArr = array();
            
            foreach ($newMonthYear as $my)
            {
                $monthYearStringArr[] = ucfirst(substr($my['month'],0,3)) . ' ' . $my['year'];
            }
            
            $series['categories'] = $monthYearStringArr;
            
            $newMonthYear = $this->getPrevious12Months();
            $len = count($newMonthYear);
            
            $newDateString = $newMonthYear[0]['month'] . ', ' . $newMonthYear[0]['year'] . ' to ' . $newMonthYear[$len-1]['month'] . ', '.
                    $newMonthYear[$len -1]['year'];
            
            $series['date'] = $newDateString;
            
            
            file_put_contents("user_details_by_user.json", json_encode($series));
            
            return $series;
    }
    
    //This function returns json data for LineChart in User's Activity Module
    
    function fetchSumTotalUsersLast12MonthsFilter($geoList,$tierText)
    {
        $metricClient = new MetricClient();
       
        $monthYear = $this->getPrevious12Months('m');
       
        
        $query = array(
            array('$unwind'=>'$'.$tierText),
            array('$match'=>array(''.$tierText => array('$in'=>$geoList)))
        );
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        sort($geoList);
        $cursorArray = json_decode($cursorJson,TRUE);
        
        $series = array();
        
        
            foreach ($geoList as $location)
            {
              $tempSeries = array(); //structure {'SE'=>[22,1,3,4,3,6,5,8,9,0,0,0]}
              
               foreach ($monthYear as $my)
                {
                    $count = 0;
                    
                    foreach($cursorArray as $cursor)
                    {
                        $dateString = explode(" ",$cursor['timestamp_created']);
                        $dateString = explode("-",$dateString[0]);
                        $y = $dateString[0];
                        $m = $dateString[1];

                        if( $y <= $my['year'] && $m <= $my['month']  && $cursor[$tierText] == $location)
                        {
                                $count++;
                        }
                    }//Cursor iteration ends here
                    
                    $tempSeries[] = $count;
                    
                } // Date iteration ends here
                
                $series[$location] = $tempSeries;
            }// location iteration ends here
            
            
            //prepare category by concatenating month and year
            
            $newMonthYear = $this->getPrevious12Months();
            $monthYearStringArr = array();
            
            foreach ($newMonthYear as $my)
            {
                $monthYearStringArr[] = ucfirst(substr($my['month'],0,3)) . ' ' . $my['year'];
            }
            
            $series['categories'] = $monthYearStringArr;
            
            $newMonthYear = $this->getPrevious12Months();
            $len = count($newMonthYear);
            
            $newDateString = $newMonthYear[0]['month'] . ', ' . $newMonthYear[0]['year'] . ' to ' . $newMonthYear[$len-1]['month'] . ', '.
                    $newMonthYear[$len -1]['year'];
            
            $series['date'] = $newDateString;
            return $series;
    }
    
    //This function returns json data for LineChart in User's Activity Module
    function fetchSumTotalUsersLast12Months($geoList,$tierText)
    {
        
        if($tierText != 'geozone'){
            return $this->fetchSumTotalUsersLast12MonthsFilter($geoList, $tierText);
        }

        $metricClient = new MetricClient();
       
        $monthYear = $this->getPrevious12Months('m');
       
        
        $query = array(
            array('$unwind'=>'$'.$tierText),
            array('$match'=>array(''.$tierText => array('$in'=>$geoList)))
        );
        
        $cursorJson = $metricClient->handleDataGet($query, array(), 'aggregate', 'users');
        
        sort($geoList);
        $cursorArray = json_decode($cursorJson,TRUE);
        
        $series = array();
        
        
            foreach ($geoList as $location)
            {
              
                    $count = 0;
                    
                    foreach($cursorArray as $cursor)
                    {
                        $dateString = explode(" ",$cursor['timestamp_created']);
                        $dateString = explode("-",$dateString[0]);
                        $y = $dateString[0];
                        $m = $dateString[1];

                        if( $y <= $monthYear[11]['year'] && $m <= $monthYear[11]['month']  && $cursor[$tierText] == $location)
                        {
                                $count++;
                        }
                    }//Cursor iteration ends here
                    
                   
                $series[$location] = $count;
            }// location iteration ends here
            
            
            //prepare category by concatenating month and year
            $monthYearStringArr = array();
            
            
            $monthYearStringArr = ucfirst(substr($monthYear[11]['month'],0,3)) . ' ' . $monthYear[11]['year'];
            
            
            $series['categories'] = $monthYearStringArr;
            
            $newDateString = $monthYear[0]['month'] . ', ' . $monthYear[0]['year'] . ' to ' . $monthYear[11]['month'] . ', '.
                    $monthYear[11]['year'];
            
            $series['date'] = $newDateString;
            
            $cacheData = json_decode(file_get_contents("user_details_by_user.json"),true);
            
            
            
            foreach($cacheData as $loc=>$sessionCount){
                
                if($loc == "categories" || $loc == "date")
                    continue;
                
                $sessions = $cacheData[$loc];
                $sessions[11] = $series[$loc] + $sessions[10];
                $cacheData[$loc] = $sessions;
                
            }
            
            //$cacheData['date'] = $newDateString;
            
            return $cacheData;
    }
    
    
    /**
     * Model helper method
     * This method gets the last 12 months starting from the current month
     * @return Array Returns an associative array of month and year
     */
    
    public function getPrevious12Months($mType = 'n')
    {
       
       
       $months = array("January","February","March","April","May","June","July","August","September","October","November","December");
       $monthsDigit = array('01','02','03','04','05','06','07','08','09','10','11','12');
        date_default_timezone_set("Africa/Lagos");
        
        $date = date("Y-n");
        $dateString = explode('-', $date);
        $year = $dateString[0] ;
        $month = $dateString[1];
        
        $monthYear = array();
        
        $counter = $month;
        
        for($i=0;$i<12;$i++)
        {
            if($counter == 0)
            {
                $year -= 1;
                $counter = 12;
            }
            $counter -= 1; // shift the month Array pointer head back one step
            
            if($mType == 'n')
            {
                $monthYear[] = array('month'=>$months[$counter],'year'=>$year);
            }
            else
            {
                $monthYear[] = array('month'=>$monthsDigit[$counter],'year'=>$year);
            }
        }
        
        
        return array_reverse($monthYear);
    }
    
    
    /**
     * 
     * @return Array This method returns an array containg the current year and month
     */
    public function getYearMonth($mType = 'n')
    {
        /**
         * If $mType variable is m, month will be returned as digit
         * but if its "n", month will be returned as name e.g "August"
         */
        $months = array("January","February","March","April","May","June","July","August","September","October","November","December");
       
        date_default_timezone_set("Africa/Lagos");
        
        if($mType == 'm')
        {
            $time = strtotime("first day of last month");
            $date = date('Y-m',$time);
            $dateString = explode('-', $date);
            $year = $dateString[0] ;
            $month = $dateString[1];
            
            return array($year, $month);
        }
        else 
        {
            $date = date('Y-n');
            $dateString = explode('-', $date);
            $year = $dateString[0] ;
            $month = $dateString[1];
            
            return array($year,$months[$month-1]);
        }
        
        
        
    }
    
    public function getLastMonthAndYear($monthType = 'n'){
        
        /**
         * If $mType variable is m, month will be returned as digit
         * but if its "n", month will be returned as name e.g "August"
         */
        $months = array("January","February","March","April","May","June","July","August","September","October","November","December");
       
        date_default_timezone_set("Africa/Lagos");
        
        $time = strtotime("first day of last month");
        $date = date('Y-n',$time);
        
        $dateString = explode("-",$date);
        
        $year = $dateString[0];
        $month = $dateString[1];
        
        if($monthType == 'm'){
            return array($year,$month);
        }
        
        return array($year,$months[$month-1]);
    }
    
    public function getDay(){
        
        date_default_timezone_set("Africa/Lagos");
        
        $timestamp = time();
        $day = date('d',$timestamp);
        return $day;
    }
    
    public function toString()
    {
        return "This is AnalyticsDashboard class";
    }
    
}
