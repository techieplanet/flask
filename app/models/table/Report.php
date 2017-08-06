<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Report
 *
 * @author SWEDGE
 */

class Report {
    //put your code here
    //ini_set('max_execution_time', 0); 
    //TP: Getting the annual date range 
        public function getAnnualDateRange($start_year,$end_year){
        
        $starts_years = array();
                        $ends_years = array();
                        for($i=$start_year; $i<=$end_year; $i++){
                           $start_date = $i."-01-01";
                           $end_date = $i."-12-31";
                           array_push($starts_years,$start_date);
                           array_push($ends_years,$end_date);
                           }
                           return array($starts_years,$ends_years);
    }
    
       
        public function get_location_category_unique($category,$condition=""){
       // $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      if($category=="zone"){
        $needle = "geo_parent_id,geo_zone";
        $condi = "";
        $name = "geo_zone";
    }else if($category=="state"){
        $needle = "state_id,state";
        $name = "state";
        $condi = "WHERE geo_parent_id='$condition'";
    }else{
        $needle = "lga_id,lga";
        $name = "lga";
        $condi = "WHERE state_id='$condition'";
    }
    
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT DISTINCT  ".$needle." FROM facility_location_view ".$condi."  ORDER BY `$name` ASC";
  // echo $sql;exit;
    $result = $db->fetchAll($sql);
   
    
}

        public function getNameIdsUsingAlias($aliasCommodities){
    
    $nameids = array();
    foreach($aliasCommodities as $alias){
      
        $nameids[] = $this->getCommodityNameId($alias);
    }
   
    return $nameids;
}
    
        public function getCommodityNameId($alias){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $sql = "SELECT id FROM commodity_name_option WHERE commodity_alias='$alias' LIMIT 1";
        $result = $db->fetchAll($sql);
        return $result[0]['id'];
        }
    
        public function check_length_add_one($value){
           if(strlen($value)==1){
               $value = "0".$value;
               
           }
           return $value;
       }
       
        public function removeSelected($geog){
          if(isset($geog[0]) ){
           
           $size = sizeof($geog);
           $geography = array();
           if($size==1){
               if($geog[0]=="" || $geog[0]==" "){
                   return $geography;
               }else{
                 return $geog;  
               }
           }
          }else{
              return $geog;
          }
       }
       
       
        public function getSumConsumption($result){
           $conSum = array();
           $facility = array();
           $facilities = array();
           $typeArray = array();
           foreach($result as $res){
              $name_id = $res['name_id'];
              $facility_id = $res['facility_id'];
              $type = $res['commodity_type'];
              //----------------------------------------------------------check if the indexes exists---------------------------------
              if(!isset($conSum[$name_id])){
                  $conSum[$name_id] = array();
              }
              
              if(!isset($facilities[$type]) && $type!=""){
                  $facilities[$type] = array();
              }
              if(!isset($typeArray[$type])){
                  $typeArray[$type] = "";
              }
              
              if(!isset($conSum[$name_id]['consumption'])){
                  $conSum[$name_id]['consumption'] = 0;
                 // $facility[$name_id] = array();
              }
              
              if(!isset($conSum[$name_id]['facCount'])){
                  $conSum[$name_id]['facCount'] = 0;
              }
              
              if($type !=""){
              $facilities[$type][] = $facility_id; 
              $facilities[$type] = array_unique($facilities[$type]);
              $typeArray[$type] = sizeof($facilities[$type]);
              }
              //------------------------------------------------------attend to proper insertion of the database------------------------
              $conSum[$name_id]['consumption']= $conSum[$name_id]['consumption'] + $res['consumption'];
              if($res['consumption']!=0 ){
                // $facility[$name_id][] = $facility_id;
                  $conSum[$name_id]['facCount'] = $conSum[$name_id]['facCount'] + 1;
              }
              
              
                          
           }
        
           return array($typeArray,$conSum);
       }
       
        public function getTrainingAlQueriesDetails($tempStartDate,$tempEndDate,$trainingType,$trainingOrganizer,$facilities){
           $implodeFacilities = implode(",",$facilities);
           $count = array();
           if(!empty($trainingType) && !empty($trainingOrganizer)){
               foreach($trainingOrganizer as $torg){
                   foreach($trainingType as $ttype){
                       
                $result = $this->countHWFacility($tempStartDate,$tempEndDate,$ttype,$torg,$implodeFacilities);
                $count[$torg][$ttype] = $result;
                   }
               }
           }
           else if(!empty($trainingType)){
               foreach($trainingType as $ttype){
                $result = $this->countHWFacility($tempStartDate,$tempEndDate,$ttype,"",$implodeFacilities);
                $count[$ttype] = $result;
                   }
           }
           else if(!empty($trainingOrganizer)){
               foreach($trainingOrganizer as $torg){
                   
                $result = $this->countHWFacility($tempStartDate,$tempEndDate,"",$torg,$implodeFacilities);
                   $count[$torg] = $result;
               }
           }
           else{
               $result = $this->countHWFacility($tempStartDate,$tempEndDate,"","",$implodeFacilities);
               $count[] = $result;
           }
           
           return $count;
       }
       
        public function countHWFacility($tempStartDate,$tempEndDate,$ttype,$torg,$implodeFacilities){
           $where = "";
           if($ttype!=""){
               $where .="AND training_title_option_id='$ttype'";
           }
           if($torg!=""){
               $where .="AND training_organizer_option_id='$torg'";
           }
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT count(DISTINCT(person_id)) as pcount, count(DISTINCT(facility_id)) as fcount FROM `facility_summary_person_view` WHERE  training_end_date>='$tempStartDate' AND training_end_date<='$tempEndDate' AND facility_id IN ($implodeFacilities) $where";
           //Helper2::jLog($sql);
          
           $result = $db->fetchAll($sql);
           return $result;
       }
       
       //---------------------------------------count the HWs result method -------------------------------------------------------------------------------
        public function countHWsResult($whereStatement,$trainingType,$trainingOrganizer){
           $result = $this->selectPersonCountHWResultCount($whereStatement);
           
           $facility_id = array();
           $array = array();
            if(!empty($trainingType) && !empty($trainingOrganizer)){
           foreach($result as $res){
              $array = $this->HWCountTrainingTypeAndOrganizer($trainingOrganizer,$trainingType,$array,$res);
             
           }
            }
           else if(!empty($trainingType)){
               foreach($result as $res){
                 $array = $this->HWCountTrainingTypeAlone($trainingType,$array,$res);
               }
           }
           
           else if(!empty($trainingOrganizer)){
               foreach($result as $res){
               $array = $this->HWCountTrainingOrganizerAlone($trainingOrganizer,$array,$res);
            }
           }
           
      
           
           return $array;
       }
       
       //-------------------------------------------------------------------HW count when training organizer and training type is set---------------------
        public function HWCountTrainingTypeAndOrganizer($trainingOrganizer,$trainingType,$array,$res){
           foreach($trainingType as $ttype){
                   foreach($trainingOrganizer as $torg){
                      
                       if(!isset($array[$ttype][$torg]['pcount'])){
                           $array[$ttype][$torg]['pcount'] = 0;
                       }
                       if(!isset($array[$ttype][$torg]['fcount'])){
                           $array[$ttype][$torg]['fcount'] = 0;
                           $array[$ttype][$torg]['facilities'] = array();
                       }
                      //S print_r($array);exit;
                       if($res['training_title_option_id']==$ttype && $res['training_organizer_option_id']==$torg){
                           $array[$ttype][$torg]['pcount']  = $array[$ttype][$torg]['pcount'] + 1;
                         
                              $array[$ttype][$torg]['facilities'][] = $res['facility_id'];
                            $array[$ttype][$torg]['facilities'] = array_unique($array[$ttype][$torg]['facilities']);
                            $array[$ttype][$torg]['fcount'] = sizeof($array[$ttype][$torg]['facilities']);
                         }
                           
                       
                   }
               }
               return $array;
       }
       
       //--------------------------------------------------------HW count when training type alone is set ------------------------------------------------
        public function HWCountTrainingTypeAlone($trainingType,$array,$res){
           
           foreach($trainingType as $ttype){
                      if(!isset($array[$ttype]['pcount'])){
                           $array[$ttype]['pcount'] = 0;
                       }
                       if(!isset($array[$ttype]['fcount'])){
                           $array[$ttype]['fcount'] = 0;
                           $array[$ttype]['facilities'] = array();
                       }
                       
                       if($res['training_title_option_id']==$ttype){
                           $array[$ttype]['pcount']  = $array[$ttype]['pcount'] + 1;
                         
                              $array[$ttype]['facilities'][] = $res['facility_id'];
                            $array[$ttype]['facilities'] = array_unique($array[$ttype]['facilities']);
                            $array[$ttype]['fcount'] = sizeof($array[$ttype]['facilities']);
                         }
                 }
           return $array;
       }
       
       //--------------------------------------------------------HW count when training organizer alone is set---------------------------------------------
        public function HWCountTrainingOrganizerAlone($trainingOrganizer,$array,$res){
           
           foreach($trainingOrganizer as $torg){
                      if(!isset($array[$torg]['pcount'])){
                           $array[$torg]['pcount'] = 0;
                       }
                       if(!isset($array[$torg]['fcount'])){
                           $array[$torg]['fcount'] = 0;
                           $array[$torg]['facilities'] = array();
                       }
                       
                  if($res['training_organizer_option_id']==$torg){
                           $array[$torg]['pcount']  = $array[$torg]['pcount'] + 1;
                         
                              $array[$torg]['facilities'][] = $res['facility_id'];
                            $array[$torg]['facilities'] = array_unique($array[$torg]['facilities']);
                            $array[$torg]['fcount'] = sizeof($array[$torg]['facilities']);
                         }
                       
                 }
           return $array;
       }
       
        public function selectPersonCountHWResultCount($whereStatement){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           // $sql = "SELECT  FROM `facility_summary_person_view` WHERE  $whereStatement";
            $sql = "SELECT flv.id as facility_id,flv.state,flv.lga,t.id as training_id,t.training_title_option_id,t.training_organizer_option_id FROM person_to_training as ptt 
                    LEFT JOIN person as p on ptt.person_id = p.id 
                    LEFT JOIN facility_location_view as flv on flv.id=p.facility_id
                    LEFT JOIN  training as t on t.id = ptt.training_id
                    WHERE $whereStatement AND p.is_deleted='0' AND t.is_deleted='0' ";
           
          Helper2::jLog($sql);
          //  echo $sql;exit;
          // echo $sql;exit;
            $result = $db->fetchAll($sql);
           
            return $result;
           
       }
       
       public function countAllFacilities($loc){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $where = "";
           if($loc!="0"){
               $where = "WHERE (flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc))";
           }
           $sql = "SELECT count(*) as counter FROM facility_location_view as flv $where";
           $result = $db->fetchAll($sql);
             return $result[0]['counter'];
       }
       
       public function getTrainingIDFacilityID($facility_id,$tempStartDate,$tempEndDate){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $where = array();
            $where[] = "(training_end_date>='$tempStartDate' AND training_end_date<='$tempEndDate')";
            
             if(!empty($facility_id)){
                $where[]  = "(flv.id IN ($facility_id) )";
            
            }
            
            

            $implodedWhere = implode(" AND ",$where);
            $sqlImplode = "";
            if(!empty($implodedWhere)){
                $sqlImplode = "WHERE $implodedWhere";
            }
            
            $sql = "SELECT flv.id as facility_id,flv.facility_name,flv.state,flv.lga,t.id as training_id,tto.training_title_phrase,torg.training_organizer_phrase, count(DISTINCT(p.id)) as hwcount FROM training as t 
                    LEFT JOIN person_to_training as ptt on t.id = ptt.training_id LEFT JOIN person as p on ptt.person_id = p.id 
                    LEFT JOIN facility_location_view as flv on flv.id=p.facility_id LEFT JOIN training_title_option as tto on tto.id = t.training_title_option_id 
                    LEFT JOIN training_organizer_option as torg  on torg.id=t.training_organizer_option_id $sqlImplode GROUP BY flv.id,t.id ORDER BY state ASC";
           
            $result = $db->fetchAll($sql);
//            Helper2::jLog(print_r($result,true));
//            Helper2::jLog($sql);
            return $result;
           
       }

        public function selectAggregateFacDataWithTrainingID($trainingID,$tempStartDate,$tempEndDate,$implodeLocations){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $where = array();
            $where[] = "(training_end_date>='$tempStartDate' AND training_end_date<='$tempEndDate')";
            if(!empty($trainingID)){
                $where[] = "(t.id IN ($trainingID))";
            }else{
             if(!empty($implodeLocations)){
                $where[]  = "(flv.geo_parent_id IN ($implodeLocations) OR flv.parent_id IN ($implodeLocations) OR flv.location_id IN ($implodeLocations))";
            }
            }
            
            

            $implodedWhere = implode(" AND ",$where);
            $sqlImplode = "";
            if(!empty($implodedWhere)){
                $sqlImplode = "WHERE $implodedWhere";
            }
            
            $sql = "SELECT flv.id as facility_id,flv.facility_name,flv.state,flv.lga,t.id as training_id,tto.training_title_phrase,torg.training_organizer_phrase, count(DISTINCT(p.id)) as hwcount FROM training as t 
                    LEFT JOIN person_to_training as ptt on t.id = ptt.training_id LEFT JOIN person as p on ptt.person_id = p.id 
                    LEFT JOIN facility_location_view as flv on flv.id=p.facility_id LEFT JOIN training_title_option as tto on tto.id = t.training_title_option_id 
                    LEFT JOIN training_organizer_option as torg  on torg.id=t.training_organizer_option_id $sqlImplode GROUP BY flv.id,t.id ORDER BY state ASC";
           
            $result = $db->fetchAll($sql);
//            Helper2::jLog(print_r($result,true));
//            Helper2::jLog($sql);
            return $result;
          
        }
        
        public function formatTrainingResult($trainingResult,$facilities,$trainingType,$trainingOrganizer){
           $hws = sizeof($trainingResult);
           $person = array();
           foreach($trainingResult as $tresult){
               $facility_id = $tresult['facility_id'];
               $trainingType  = $tresult['training_title_option_id'];
               $trainingOrganizer = $tresult['training_organizer_option_id'];
               $person['facility_id'] = $facility_id;
               $person['trainingType'] = $trainingType;
               $person['trainingOrganizer'] = $trainingOrganizer;
               
           }
           
           
       }
       
        public function createAggregateHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period){
           $headers = array("Geography","Total Facilities","Total Reporting Facilities");
          
          
          //---------------------------check the category of the training organizer and training type to dynamically create headers----------------------------------------------
          if(!empty($trainingType) && !empty($trainingOrganizer)){
           foreach($trainingType as $ttype){
               $ttypeName = $this->get_training_title_option($ttype);
                   foreach($trainingOrganizer as $torg){
                       $torgName = $this->get_training_organizer_phrase_name($torg);
                       $torgName = str_replace(",", "@", $torgName);
                       $ttypeName = str_replace(",", "@", $ttypeName);
                       $headers[] = "Facilities with a HW Trained in $ttypeName by $torgName";
                       $headers[] = "HWs Trained in $ttypeName by $torgName";
                   }
              
          }
          }
          else if(!empty($trainingType)){
              foreach($trainingType as $ttype){
                  $ttypeName = $this->get_training_title_option($ttype);
                  $ttypeName = str_replace(",", "@", $ttypeName);
                  $headers[] = "Facilities with a HW Trained in $ttypeName";
                  $headers[] = "HWs Trained in $ttypeName";
              
              }
          }
         else if(!empty($trainingOrganizer)){
             foreach($trainingOrganizer as $torg){
                       $torgName = $this->get_training_organizer_phrase_name($torg);
                       $torgName = str_replace(",", "@", $torgName);
                       $headers[] = "Facilities with a HW Trained by $torgName";
                       $headers[] = "HWs Trained by $torgName";
                   }
         }
         
         
         //-------------------------------commodity providing---------------------------------------
         if(!empty($providing)){
          foreach($providing as $prov){
              $headers[] = "Facilities providing any ".strtoupper($prov);
          }
         }
          
         //------------------------------consumption formatting-------------------------------------
         if(!empty($consumption)){
          foreach($consumption as $cons){
              $consName = $this->get_commodity_option_name($cons);
              if($cons==37 || $cons==36 || $cons=="37" || $cons=="36"){
                 $headers[] = "$consName (Clients)"; 
              }else{
              $headers[] = "Consumption of $consName";
              }
          }
         }
          
        //------------------------------stock out formatting---------------------------------------
         if(!empty($stockOut)){
          foreach($stockOut as $stk){
              $stockName = $this->getStockOutName($stk);
              $headers[] = "Facilities $stockName";
          }
         }
        //-----------------------------------period formatting for the last row-------------------------
          if($period=="annual"){
          $headers[] = "Year";
          }
          else if($period=="monthly"){
              $headers[] = "Month";
              $headers[] = "Year";
          }
          return $headers;
       }
        
        public function createFacWithTrainingHeaders($providing,$consumption,$stockOut,$period){
             $headers = array("State","LGA","Facility");
          
          
          //---------------------------check the category of the training organizer and training type to dynamically create headers----------------------------------------------
         
                       $headers[] = "HWs Trained in training";
                       $headers[] = "Training ID";
                       $headers[] = "Training Organizer";
                       $headers[] = "Training Type";
        
         
         
          
         //------------------------------consumption formatting-------------------------------------
         if(!empty($consumption)){
          foreach($consumption as $cons){
              $consName = $this->get_commodity_option_name($cons);
              if($cons==37 || $cons==36 || $cons=="37" || $cons=="36"){
                 $headers[] = "$consName (Clients)"; 
              }else{
              $headers[] = "Consumption of $consName";
              }
          }
         }
          
         //-------------------------------commodity providing---------------------------------------
         if(!empty($providing)){
          foreach($providing as $prov){
              $headers[] = "Providing ANY ".strtoupper($prov);
          }
         }
        //------------------------------stock out formatting---------------------------------------
         if(!empty($stockOut)){
          foreach($stockOut as $stk){
              $stockName = $this->getStockOutName($stk);
              $headers[] = "$stockName";
          }
         }
        //-----------------------------------period formatting for the last row-------------------------
          if($period=="annual"){
          $headers[] = "Year";
          }
          else if($period=="monthly"){
              $headers[] = "Month";
              $headers[] = "Year";
          }
          $headers[] = "Reporting";
          return $headers;
       
        }
        
        public function createFacAloneHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period){
             $headers = array("State","LGA","Facility");
          
          
          //---------------------------check the category of the training organizer and training type to dynamically create headers----------------------------------------------
          if(!empty($trainingType) && !empty($trainingOrganizer)){
           foreach($trainingType as $ttype){
               $ttypeName = $this->get_training_title_option($ttype);
                   foreach($trainingOrganizer as $torg){
                       $torgName = $this->get_training_organizer_phrase_name($torg);
                       $ttypeName = str_replace(",", "@", $ttypeName);
                       $torgName = str_replace(",", "@", $torgName);
                       
                       $headers[] = "HWs Trained in $ttypeName by $torgName";
                   }
              
          }
          }
          else if(!empty($trainingType)){
              foreach($trainingType as $ttype){
                  $ttypeName = $this->get_training_title_option($ttype);
                  $ttypeName = str_replace(",", "@", $ttypeName);
                  $headers[] = "HWs Trained in $ttypeName";
              
              }
          }
         else if(!empty($trainingOrganizer)){
             foreach($trainingOrganizer as $torg){
                       $torgName = $this->get_training_organizer_phrase_name($torg);
                       $torgName = str_replace(",", "@", $torgName);
                       $headers[] = "HWs Trained by $torgName";
                   }
         }
         
         
         $headers[] = "Training Organizer";
         
          
         //------------------------------consumption formatting-------------------------------------
         if(!empty($consumption)){
          foreach($consumption as $cons){
              $consName = $this->get_commodity_option_name($cons);
              if($cons==37 || $cons==36 || $cons=="37" || $cons=="36"){
                 $headers[] = "$consName (Clients)"; 
              }else{
              $headers[] = "Consumption of $consName";
              }
          }
         }
          
         //-------------------------------commodity providing---------------------------------------
         if(!empty($providing)){
          foreach($providing as $prov){
              $headers[] = "Providing ANY ".strtoupper($prov);
          }
         }
        //------------------------------stock out formatting---------------------------------------
         if(!empty($stockOut)){
          foreach($stockOut as $stk){
              $stockName = $this->getStockOutName($stk);
              $headers[] = "$stockName";
          }
         }
        //-----------------------------------period formatting for the last row-------------------------
          if($period=="annual"){
          $headers[] = "Year";
          }
          else if($period=="monthly"){
              $headers[] = "Month";
              $headers[] = "Year";
          }
          $headers[] = "Reporting";
          return $headers;
       
        }
        public function processProvData($whereProviding,$whereProviding2){
            $provDataArray = array();
            $provDataArray['larc'] = $this->fetchProvidingData($whereProviding2);
            $provDataArray['fp'] = $this->fetchProvidingData($whereProviding);
            return $provDataArray;
        }
        
        public function processProvDataFac($whereProviding,$whereProviding2){
             $provDataArray = array();
            $provDataArray['larc'] = $this->fetchProvidingfacData($whereProviding2);
            $provDataArray['fp'] = $this->fetchProvidingfacData($whereProviding);
            //Helper2::jLog(print_r($provDataArray,true));
            return $provDataArray;
        }
        
        public function fetchProvidingfacData($whereProviding){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $sql = "SELECT count(DISTINCT(facility_id)) as fac FROM commodity as c LEFT JOIN facility_location_view as flv on flv.id=c.facility_id LEFT JOIN commodity_name_option as com on com.id = c.name_id WHERE  $whereProviding";
           $result = $db->fetchAll($sql);
            return $result[0]['fac'];
            
        }
        public function fetchProvidingData($whereProviding){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $sql = "SELECT count(DISTINCT(facility_id)) as fac FROM commodity as c LEFT JOIN facility_location_view as flv on flv.id=c.facility_id LEFT JOIN commodity_name_option as com on com.id = c.name_id WHERE  $whereProviding";
            $result = $db->fetchAll($sql);
            return $result[0]['fac'];
        }
        
        public function createAggregateResultData($loc,$trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period,$totalFacilities,$facilitiesReporting,$trainingResult,$provData,$consumptionData,$tempStartDate,$stockedOutFac){
                $resultArray = array();
                $resultArray[] = ($loc!=0)?$this->get_location_name($loc): "National";
                $resultArray[] = str_replace(",", "@", number_format($totalFacilities));
                $resultArray[] = str_replace(",", "@", number_format($facilitiesReporting));
                    
                if(!empty($trainingType) && !empty($trainingOrganizer)){
                 foreach($trainingType as $ttype){
                       foreach($trainingOrganizer as $torg){
                       $resultArray[] = (isset($trainingResult[$ttype][$torg]['fcount']))?str_replace(",", "@", number_format($trainingResult[$ttype][$torg]['fcount'])):0;
                       $resultArray[] = (isset($trainingResult[$ttype][$torg]['pcount']))?str_replace(",", "@", number_format($trainingResult[$ttype][$torg]['pcount'])):0;
                       
                       
                        }
              
                     }
                }
                else if(!empty($trainingType)){
                 foreach($trainingType as $ttype){
                  $resultArray[] = (isset($trainingResult[$ttype]['fcount']))? str_replace(",", "@", number_format($trainingResult[$ttype]['fcount'])): 0;
                  $resultArray[] = (isset($trainingResult[$ttype]['pcount']))?str_replace(",", "@", number_format($trainingResult[$ttype]['pcount'])):0;
              
                        }
                }
               else if(!empty($trainingOrganizer)){
                   foreach($trainingOrganizer as $torg){
                       $resultArray[] = (isset($trainingResult[$torg]['fcount']))?str_replace(",", "@", number_format($trainingResult[$torg]['fcount'])):0;
                       $resultArray[] = (isset($trainingResult[$torg]['pcount']))?str_replace(",", "@", number_format($trainingResult[$torg]['pcount'])):0;
                   }
                }
                
                if(!empty($providing)){
                foreach($providing as $prov){
               $resultArray[] = (isset($provData[$prov]))?str_replace(",", "@", number_format($provData[$prov])):0;
               }
                }
               
                if(!empty($consumption)){
               foreach($consumption as $cons){
                   $resultArray[] = (isset($consumptionData[$cons]['consumption']))?str_replace(",", "@", number_format($consumptionData[$cons]['consumption'])):0;
               }
                }
               
               
                if(!empty($stockOut)){
               foreach($stockOut as $stk){
                  
                   $resultArray[] = (isset($stockedOutFac[$stk]))?str_replace(",", "@", number_format($stockedOutFac[$stk])):0;
               }
                }
               
               if($period=="annual"){
                  $tempTimestamp = strtotime($tempStartDate);
                  $year = date('Y',$tempTimestamp);
                  $resultArray[] = $year;
          }
          else if($period=="monthly"){
              $tempTimestamp = strtotime($tempStartDate);
              $year = date('Y',$tempTimestamp);
              $month = date('M',$tempTimestamp);
              $resultArray[] = $month;
              $resultArray[] = $year;
          }
          return $resultArray;
        }
       
        public function createFacilityWithtrainingResultData($stateName,$lgaName,$facilityName,$hwcount,$training_id,$trainingOrganizerPhrase,$trainingTitlePhrase,$consumption,$consumptionData,$providing,$provData,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting){
            $resultArray = array();
               $resultArray[] = $stateName;
               $resultArray[] = $lgaName;
               $resultArray[] = $facilityName;
               
              // print_r($consumptionData);exit;
                    
               $resultArray[] = str_replace(",", "@", number_format($hwcount));
               $resultArray[] = $training_id;
               $resultArray[] = $trainingOrganizerPhrase;
               $resultArray[] = $trainingTitlePhrase;
                
                
                
               
                if(!empty($consumption)){
               foreach($consumption as $cons){
                   //echo (isset($consumptionData[$cons]['consumption']))?$consumptionData[$cons]['consumption']:"";
                   $resultArray[] = (isset($consumptionData[$cons]['consumption']))?str_replace(",", "@", number_format($consumptionData[$cons]['consumption'])):"";
               }
                }
                
               if(!empty($providing)){
                foreach($providing as $prov){
                    if(isset($provData[$prov])){
                        if($provData[$prov]>=1){
                            $resultArray[] = "Yes";
                        }else{
                            $resultArray[] = "No";
                        }
                    }else{
                        $resultArray[] = "No";
                    }
               
               }
                }
               
                if(!empty($stockOut)){
               foreach($stockOut as $stk){
                   if(isset($consumptionData[$stk]['consumption'])){
                       if($consumptionData[$stk]['consumption']=="1" || $consumptionData[$stk]['consumption']==1){
                           $resultArray[] = "Yes";
                       }
                       else{
                           $resultArray[] = "No";
                       }
                   }else{
                       $resultArray[] = "";
                   }
                   
               }
                }
                
                
                if($period=="annual"){
                  $tempTimestamp = strtotime($tempStartDate);
                  $year = date('Y',$tempTimestamp);
                  $resultArray[] = $year;
          }
          else if($period=="monthly"){
              $tempTimestamp = strtotime($tempStartDate);
              $year = date('Y',$tempTimestamp);
              $month = date('M',$tempTimestamp);
              $resultArray[] = $month;
              $resultArray[] = $year;
          }
          
          if($facilitiesReporting>=1){
              $resultArray[] = "Yes";
          }else{
              $resultArray[] = "No";
          }
          return $resultArray;
        }
        public function createFacilityResultData($stateName,$lgaName,$facilityName,$trainingType,$trainingOrganizer,$trainingResult,$consumption,$consumptionData,$providing,$provData,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting,$stringPhrase){
               $resultArray = array();
               $resultArray[] = $stateName;
               $resultArray[] = $lgaName;
               $resultArray[] = $facilityName;
               
              // print_r($consumptionData);exit;
                    
                 if(!empty($trainingType) && !empty($trainingOrganizer)){
                 foreach($trainingType as $ttype){
                       foreach($trainingOrganizer as $torg){
                      
                       $resultArray[] = (isset($trainingResult[$ttype][$torg]['pcount']))?str_replace(",", "@", number_format($trainingResult[$ttype][$torg]['pcount'])):0;
                       
                       
                        }
              
                     }
                }
                else if(!empty($trainingType)){
                 foreach($trainingType as $ttype){
                  
                  $resultArray[] = (isset($trainingResult[$ttype]['pcount']))?str_replace(",", "@", number_format($trainingResult[$ttype]['pcount'])):0;
              
                        }
                }
               else if(!empty($trainingOrganizer)){
                   foreach($trainingOrganizer as $torg){
                       
                       $resultArray[] = (isset($trainingResult[$torg]['pcount']))?str_replace(",", "@", number_format($trainingResult[$torg]['pcount'])):0;
                   }
                }
                
                
                $resultArray[] = $stringPhrase;
                
               
                if(!empty($consumption)){
               foreach($consumption as $cons){
                   //echo (isset($consumptionData[$cons]['consumption']))?$consumptionData[$cons]['consumption']:"";
                   $resultArray[] = (isset($consumptionData[$cons]['consumption']))?str_replace(",", "@", number_format($consumptionData[$cons]['consumption'])):"";
               }
                }
                
               if(!empty($providing)){
                foreach($providing as $prov){
                    if(isset($provData[$prov])){
                        if($provData[$prov]>=1){
                            $resultArray[] = "Yes";
                        }else{
                            $resultArray[] = "No";
                        }
                    }else{
                        $resultArray[] = "No";
                    }
               
               }
                }
               
                if(!empty($stockOut)){
               foreach($stockOut as $stk){
                   if(isset($consumptionData[$stk]['consumption'])){
                       if($consumptionData[$stk]['consumption']=="1" || $consumptionData[$stk]['consumption']==1){
                           $resultArray[] = "Yes";
                       }
                       else{
                           $resultArray[] = "No";
                       }
                   }else{
                       $resultArray[] = "";
                   }
                   
               }
                }
                
                
                if($period=="annual"){
                  $tempTimestamp = strtotime($tempStartDate);
                  $year = date('Y',$tempTimestamp);
                  $resultArray[] = $year;
          }
          else if($period=="monthly"){
              $tempTimestamp = strtotime($tempStartDate);
              $year = date('Y',$tempTimestamp);
              $month = date('M',$tempTimestamp);
              $resultArray[] = $month;
              $resultArray[] = $year;
          }
          
          if($facilitiesReporting>=1){
              $resultArray[] = "Yes";
          }else{
              $resultArray[] = "No";
          }
          return $resultArray;
        }
        public function getStockOutName($index){
            $array = array();
            $array[32] = "Stocked out of family planning commodities for 7 consecutive days";
            $array[31] = "Stocked out of Emergency Contraception";
            $array[38] = "Stocked out of Implants";
            $array[39] = "Stocked out of Female Condoms";
            return $array[$index];
        }

        public function getFacilitiesWithLocation($loc){
            if($loc!=0){
                $where = "WHERE (flv.geo_parent_id ='$loc' OR flv.parent_id='$loc' OR flv.location_id='$loc')";
            }else{
                $where = "";
            }
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT flv.id as facility_id FROM facility_location_view as flv  $where";
          
           $result = $db->fetchAll($sql);
           return $this->formatFacilities($result);
       }
       
        public function getFacilitiesWithLocationAndFacilities($implodeFacility,$implodeLocations){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $whereStatement = "";
           $where = array();
           if(!empty($implodeLocations)){
               $where[] = "(flv.geo_parent_id IN ($implodeLocations) OR flv.parent_id IN ($implodeLocations) OR flv.location_id IN ($implodeLocations))";
           }           
           if(!empty($implodeFacility)){
               $where[] = "flv.id IN ($implodeFacility)";
           }
           if(!empty($where)){
               $whereClause = implode(" AND ", $where);
               $whereStatement = "WHERE $whereClause";
           }
           
           $sql = "SELECT * FROM `facility_location_view` as flv $whereStatement";
           //echo $sql;exit;
           //Helper2::jLog($sql);
           $result = $db->fetchAll($sql);
           
           return $result;
       }
       
        public function getAggregateConsumptionResult($whereStatement){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $sql =  "SELECT c.consumption,c.stock_out,c.name_id,cm.commodity_type,c.date,c.facility_id,c.facility_reporting_status,flv.facility_name,flv.location_id,flv.location_name,flv.parent_id,flv.lga_id,flv.lga,flv.state,flv.state_id,flv.geo_parent_id,flv.geo_zone FROM commodity as c LEFT JOIN commodity_name_option as cm on c.name_id=cm.id LEFT JOIN facility_location_view as flv on c.facility_id=flv.id WHERE $whereStatement";
            Helper2::jLog($sql);
            $result = $db->fetchAll($sql); 
           
           
            return $result;
        }
       
        public  function fetchConsumptionData($whereStatementConsumption){ 
	$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT c.name_id,SUM(consumption) as cons, COUNT(DISTINCT(facility_id)) as facCount FROM commodity as c LEFT JOIN facility_location_view as flv on flv.id = c.facility_id WHERE $whereStatementConsumption GROUP BY c.name_id";
 //echo $sql;exit;
	$result = $db->fetchAll($sql);
	return $result;
}



    public function getFormattedTrainingOrganizer($implodeTrainingType,$implodeTrainingOrganizer,$tempStartDate,$tempEndDate,$facility_id){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $where = array();
        if($implodeTrainingType!=""){
            $where[] =" t.training_title_option_id IN ($implodeTrainingType)";
             }
    if($implodeTrainingOrganizer!=""){
        $where[] = "t.training_organizer_option_id IN ($implodeTrainingOrganizer)";
    }
        if($facility_id!=""){
            $where[] = "p.facility_id IN ($facility_id)";
        }
        $where[] = "t.training_end_date>='$tempStartDate' AND t.training_end_date<='$tempEndDate'";
        $whereStatement = implode(" AND ",$where);
        
        
        $sql = "SELECT GROUP_CONCAT(DISTINCT(training_organizer_phrase)) as tphase FROM person as p LEFT JOIN person_to_training  as ptt on ptt.person_id = p.id LEFT JOIN training as t on t.id = ptt.training_id LEFT JOIN training_organizer_option as torg on torg.id = t.training_organizer_option_id WHERE $whereStatement";
//        Helper2::jLog($sql);
//        echo 'Hello';exit;
        $result = $db->fetchAll($sql);
        return $result[0]['tphase'];
        
        }
    public function getConsumptionRawData($whereStatementConsumption){
	$result = $this->fetchConsumptionData($whereStatementConsumption);
        //echo 'THis is the end point';
        //print_r($result);
	$consumptionData = array();
	foreach($result as $res){
	$name_id = $res['name_id'];
	$cons = $res['cons'];
	$facCount = $res['facCount'];
		$consumptionData[$name_id]['consumption'] = $cons;
		$consumptionData[$name_id]['facCount'] = $facCount;
	}

	return $consumptionData;
}

    public function facilitiesStockedOut($whereStatementConsumption){
        $result = $this->fetchFacilitiesStockedOut($whereStatementConsumption);
        $stockedOutData = array();
        foreach($result as $res){
            $name_id = $res['name_id'];
            $stockedOutData[$name_id] = $res['facount'];
        }
        return $stockedOutData;
    }
    
    public function fetchFacilitiesStockedOut($whereStatementConsumption){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
         $sql = "SELECT c.name_id,SUM(consumption) as cons, COUNT(DISTINCT(facility_id)) as facount FROM commodity as c LEFT JOIN facility_location_view as flv on flv.id = c.facility_id WHERE $whereStatementConsumption GROUP BY c.name_id";
         $result = $db->fetchAll($sql);
         return $result;
    }

        public function get_all_facilities_reporte_rates($facility_ids,$tempStartDate,$tempEndDate){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            if(!empty($facility_ids)){
                $whereFac = "AND facility_id IN (".$facility_ids.")"; 
            }else{
               $whereFac = ""; 
            }

            $sql = "SELECT COUNT(DISTINCT(facility_id)) as counter FROM facility_report_rate WHERE date>='$tempStartDate' AND date<='$tempEndDate' $whereFac";

            $result = $db->fetchAll($sql);
            //print_r($result);exit;
            return $result[0]['counter'];

        }
        
        public function get_all_facilities_reporte_rate_aggregate($location_id,$tempStartDate,$tempEndDate){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $whereFac = array();
            $whereFac[] = "date>='$tempStartDate' AND date<='$tempEndDate'";
            $sqlWhere = "";
             //
            if($location_id!=0){
                $whereFac[] = "(flv.geo_parent_id IN ($location_id) OR flv.parent_id IN ($location_id) OR flv.location_id IN ($location_id))";
                
            }
            $implodeSql = implode(" AND ",$whereFac);
            $sqlWhere = "WHERE $implodeSql";
            //$sql = "SELECT COUNT(DISTINCT(facility_id)) as counter FROM facility_report_rate WHERE date>='$tempStartDate' AND date<='$tempEndDate' $whereFac";
            $sql = "SELECT COUNT(DISTINCT(facility_id)) as counter FROM facility_report_rate LEFT JOIN facility_location_view as flv on flv.id=facility_id $sqlWhere";
            //Helper2::jLog($sql);
            $result = $db->fetchAll($sql);
            
            //print_r($result);exit;
            return $result[0]['counter'];
        }
        public function createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,$implodeFacilityId=""){
            $whereClause = array();
            $whereClause[] = "(date>='$tempStartDate' AND date<='$tempEndDate' )";
          
        //---------------------------------------------------create a where cleause from the imploded parameters---------------------------------------------------------
          if($implodeStockOut!="" && $implodeConsumption!=""){
                 $whereClause[] = "((c.name_id IN ($implodeConsumption)) OR (c.name_id IN ($implodeStockOut)))";
            }
           else if($implodeStockOut!=""){
                $whereClause[] = "(c.name_id IN ($implodeStockOut))";
           }
           else if($implodeConsumption!=""){
                $whereClause[] = "(c.name_id IN ($implodeConsumption))";
           }
           
           if(!empty($implodeFacilityId)){
               $whereClause[] = "(facility_id IN ($implodeFacilityId))";
           }
           return $whereClause;
        }
        
        public function createWheretrainingArrayFromParameters($implodeTrainingType,$implodeTrainingOrganizer,$tempStartDate,$tempEndDate){
            $whereTraining = array();
            if($implodeTrainingType!=""){
               $whereTraining[] = "t.training_title_option_id IN ($implodeTrainingType)";
           }
           
           if($implodeTrainingOrganizer!=""){
               $whereTraining[] = "t.training_organizer_option_id IN ($implodeTrainingOrganizer)";
           }
           
           $whereTraining[] = "t.training_end_date>='$tempStartDate' AND t.training_end_date<='$tempEndDate'";
           return $whereTraining;
        }
       
        public function formatFacilities($result){
           $facility = array();
           foreach($result as $row){
               $facility[] = $row['facility_id'];
           }
           return $facility;
       }
       
        public function validatPeriodTimeRange($period,$startMonth,$startYear,$endMonth,$endYear){
           $error = array();
           
           if($period=="monthly"){
            if((empty($startMonth) || empty($startYear) || empty($endYear) || empty($endMonth))){
                $error[] = "Please fill the date range appropriately for monthly period selection";
            }
        }
        else if($period=="annual"){
            if( empty($startYear) || empty($endYear)){
                $error[] = "Please fill the date range appropriately for annual period selection";
            }
        }
       
        
        
        return $error;
       }
       
        public function getStartDateEndDateWithPeriod($period,$startMonth,$startYear,$endMonth,$endYear,$duration){
          $helper = new Helper2();
          $startYears = array();
          $endYears = array();
          if($period=="annual"){
             
              if($duration!="custom"){
              list($startMonth,$startYear,$endMonth,$endYear) =   $this->periodDurationFormat($startMonth,$startYear,$endMonth,$endMonth,$period,$duration);   
              }
            list($startYears,$endYears) = $this->getAnnualDateRange($startYear,$endYear);
          }
        else if($period=="monthly"){
                  if($duration!="custom"){
              list($startMonth,$startYear,$endMonth,$endYear) =   $this->periodDurationFormat($startMonth,$startYear,$endMonth,$endMonth,$period,$duration);   
              }
              //echo $startMonth.$startYear."end".$endMonth." year".$endYear;exit;
            list($startYears,$endYears) = $this->getMonthlyDateRange($startMonth,$startYear,$endMonth,$endYear);
          }
        else if($period=="total"){
                 $startYears[] = "01-01-1984";
              $lastPullDate  =  $helper->getLatestPullDate();
              $tempTime = strtotime($lastPullDate);
              $monthTemp = date('m',$tempTime);
              $yearTemp = date('Y',$tempTime);
              $endYears[] = "31-$monthTemp-$yearTemp";
          }
          //print_r($startYears);exit;
          return array($startYears,$endYears,$startMonth,$startYear,$endMonth,$endYear);
      }
      
        public function periodDurationFormat($startMonth,$startYear,$endMonth,$endMonth,$period,$duration){
          $helper = new Helper2();
        $lastPullDate = $helper->getLatestPullDate();
          $tempTime = strtotime($lastPullDate);
          if($period=="annual"){
              $endYear = date('Y',$tempTime);
              $startYear = date('Y',strtotime("-1 year",$tempTime));
              
              
          }
          else if($period=="monthly"){
              $endYear = date('Y',$tempTime);
              $endMonth = date('m',$tempTime);
              $dur = $duration -1;
                  $startYear = date('Y',strtotime("-$dur month",$tempTime));
                  $startMonth  = date('m',strtotime("-$dur month",$tempTime));
              //echo $startMonth;exit;
              
              
          }else{
              $startYear = "1984";
              $startMonth = "01";
              $endYear = date('Y',$tempTime);
              $endMonth = date('m',$tempTime);
          }
          return array($startMonth,$startYear,$endMonth,$endYear);
      }
       //TP: return the lowest selected geographies
        public function lowestSelectedGeog($localgovernment,$state,$zone){
         $locations = array();
         $filter = "";
         if(!empty($facility)){
             $locations = $facility;
         }
          else if(!empty($localgovernment)){
                foreach($localgovernment as $state){
                $state_gen =   explode("_",$state);
                $parent_id = $state_gen[0];
                $district = $state_gen[1];
                $location_id = $state_gen[2];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
                $filter = "location_id";
                //echo 'na here i dey';
           }
            else if(!empty($state)){
 // echo 'hi';
    
                foreach($state as $states){
                          $state_gen =   explode("_",$states);
                          $parent_id = $state_gen[0];
                        $location_id = $state_gen[1];
                        if($location_id!=""){
                        array_push($locations,$location_id);
                        }
                   }
                $filter = "parent_id";
             }
            else if(!empty($zone)){
                        $filter = "geo_zone";
                    //print_r($locations); echo 'here inside ';exit;
                    //array_push($locations,$zone);
                    if(is_array($zone)){
                    //print_r($zonal);
                    foreach($zone as $zonal){

                    $locations[] = $zonal;

                    }
                    }else{
                    $locations[] = $zone;
                    }
              }
                     return array($filter, $locations);  
     }
    //TP: Getting the monthly date range
        public function getMonthlyDateRange($start_month,$start_year,$end_month,$end_year){
        $year_diff = $end_year-$start_year;
                        $starts_years = array();
                        $ends_years = array();
                        if($year_diff==0){
                            $month_limit = $end_month;
                            for($i=$start_month;$i<=$month_limit;$i++){
                                $i = $this->check_length_add_one($i);
                                $start_date = $end_year."-".$i."-01";
                                $end_date = $end_year."-".$i."-31";
                                array_push($starts_years,$start_date);
                                array_push($ends_years,$end_date);
                                 
                            }
                            
                             }else{
                            for($r=$start_year;$r<=$end_year;$r++){
                                if($r==$start_year){
                                    $month_start = $start_month;
                                    
                                }else{
                                    $month_start = "01";
                                }
                                
                                if($r==$end_year){
                                    $month_limit = $end_month;
                                }
                                else{
                                    $month_limit = "12";
                                }
                                
                              for($i=$month_start;$i<=$month_limit;$i++){
                                  $i = $this->check_length_add_one($i);
                                  $start_date  = $r."-".$i."-01";
                                  $end_date = $r."-".$i."-31";
                                   array_push($starts_years,$start_date);
                                array_push($ends_years,$end_date);
                              }
                                
                            }
                            
                            
                        }
                        
                        
                        return array($starts_years,$ends_years);
        
    }
    
    
        public function formatTrainingIDs($trainingIDs){
            $trainingID = array();
            $trainIDs = array();
            $trainingID = explode(",",$trainingIDs);
            foreach($trainingID as $tid){
                if($tid!="" && !empty($tid)){
                    $trainIDs[] = $tid;
                }
            }
            $imploadedTrainingIDs = implode(",",$trainIDs);
            return $imploadedTrainingIDs;
        }
        public function formatSelection($geog){
       
        if(isset($geog[0])){
            if($geog[0]=="" || $geog[0]==" " || $geog[0]==null){
                       //print_r($localgovernment);
    //            echo 'THis is it';
                if(is_array(($geog))){
                    //$geog = array_slice($geog, 0,-1);
                    $diff = array_shift($geog);
                    //print_r($geog);
                }else{
                    $geog = array();
                }

                       // print_r($localgovernment);
                         if(sizeof($geog)==1){
                            $geog = array();
                        }
                    }
        }
//        print_r($geog);
//        echo 'Inside the loop';
                return $geog;
    }
    
        public function get_commodity_option_name($name_id){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT commodity_name FROM commodity_name_option WHERE id='$name_id' LIMIT 1";
   $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['commodity_name'];
    
    }
    
        public function get_training_title_option($trainingtype){
     $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql  = "SELECT * FROM training_title_option WHERE id='".$trainingtype."'";
    $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['training_title_phrase'];
    }
    
        public function get_training_organizer_phrase_name($torg){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $sql  = "SELECT * FROM training_organizer_option WHERE id='".$torg."'";
        $all_sql = $db->fetchAll($sql);
        return $all_sql[0]['training_organizer_phrase'];
    }
    
        public function get_location_name($location_id){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
         $sql = "SELECT location_name FROM location WHERE id=$location_id";
         $all_sql = $db->fetchAll($sql);
        
         
        return isset($all_sql[0]['location_name'])?$all_sql[0]['location_name']:"";

    }
    
    public function getMultipleLocationName($locationData){
        $locationNames = array();
        foreach($locationData as $location_id){
            if($location_id!=null && !empty($location_id)){
            $locationNames[] = $this->get_location_name($location_id);
            }
        }
        return array_unique($locationNames);
        
    }

    public function explodeGeogArray($geo,$tier){
        $locations = array();
        $key = $tier-1;
        if(!empty($geo)){
        
            if(!is_array($geo))
            {
                $geoArray = explode("_",$geo);
                $locations[] = $geoArray[$key];
            }
            else{
                foreach($geo as $geog){
                    $geoArray = explode("_",$geog);
                    $locations[] = $geoArray[$key];
                }
            }
        }
        $locations = array_unique($locations);
        return $locations;
    }
    
    
    public function removeEmptySelection($geography){
      //  print_r($geography);exit;
        
     $key = array_search("",$geography);
               $key2 = array_search(" ",$geography);
               if($key){
                   array_splice($geography,$key,1);
               }
               if($key2){
                  array_splice($geography,$key2,1); 
               }
               $geosize = sizeof($geography);
               if($geosize<=0){
                   $geography = array();
               }
               
               
               return $geography;
    }

}


?>
