<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndicatorGroup
 *
 * @author swedge-mac
 */

//require_once('Facility.php');
require_once('Helper2.php');

class IndicatorGroup {
    //put your code here
    
    //add all missing months for each location.
     public  function addMissingMonths($numerators, $monthNames, $locationNames, $tierText ){
         //$helper = new Helper2();
         $numeratorsArray = array();

          //get all the records for  each location and set missing locations to 0
          foreach($locationNames as $location){
              $locationArray = array();
              foreach ($numerators as $numer){
                  if($numer[$tierText] == $location)
                      $locationArray[] = $numer;
                  else
                      continue;
              }
               
              //var_dump($locationArray); exit;
              //now we have all the rows for the current location.
              //we have to ensure it has all the month names represented
              $monthArray = $this->filterMonths($monthNames, $locationArray, $location, $tierText,  'month_name');
              $numeratorsArray = array_merge($numeratorsArray,$monthArray);
          }
          
          //var_dump($numeratorsArray); exit;          
          return $numeratorsArray;
    }
    
    
    //add all missing months for each location.
    public function filterMonths($monthNames, $locationArray, $focusLocation, $tierText, $monthField){
           $monthDataArray = array(); $monthValue = 0;
           if(!empty($locationArray)){
                foreach($monthNames as $key=>$monthName){
                    $monthValue = '';
                    foreach($locationArray as $entry){
                        if($monthName == $entry[$monthField]){ 
                            $monthValue = $entry['fid_count']; 
                            break;
                        }
                    }

                    if($monthValue == '')
                        $monthValue = 0;

                    $monthDataArray[] = array(
                                'month_name' => $monthName,
                                $tierText => $focusLocation,
                                'fid_count' => $monthValue
                        );
                }
            }
            else{
                //echo 'empty: ' . $tierText; exit;
                foreach($monthNames as $key=>$monthName)
                    $monthDataArray[] = array(
                                'month_name' => $monthName,
                                $tierText => $focusLocation,
                                'fid_count' => $monthValue
                        );
            }
            
            return $monthDataArray;
    }
    
    
    /**
     * sets up the necessary format for overtime data with numerators, denominators, percents and locations
     * Format: Array[month][location] = array(percent, numer, denom, location_name);
     * 
     * @param type $monthNames
     * @param type $locationNames
     * @param type $numerators
     * @param type $denominators
     * @return string
     */
    public function setUpOvertimeOutput($monthNames, $locationNames, $numerators, $denominators){
        $nationalNumeratorTotal = 0; $nationalDenomTotal = 0;
        $output = array();
        
        for($i=0; $i<count($monthNames); $i++){ 
            $nationalNumeratorTotal = 0; $nationalDenomTotal = 0;
            $monthName = $monthNames[$i];
            $output[$monthName] = array();
            $j = $i;

            foreach($locationNames as $location){   
                $output[$monthName][$location]['percent'] = round($numerators[$j]['fid_count'] / $denominators[$j]['fid_count'] * 100, 1);
                $output[$monthName][$location]['numer'] = $numerators[$j]['fid_count'];
                $output[$monthName][$location]['denom'] = $denominators[$j]['fid_count'];
                //$output[$monthName][$location]['location_name'] = $location;
                $nationalNumeratorTotal += $numerators[$j]['fid_count'];
                $nationalDenomTotal += $denominators[$j]['fid_count'];


                $j += sizeof($monthNames);
            }

            //at month end, set national figures
            $output[$monthName]['National']['percent'] = round($nationalNumeratorTotal / $nationalDenomTotal * 100, 1);
            $output[$monthName]['National']['numer'] = $nationalNumeratorTotal;
            $output[$monthName]['National']['denom'] = $nationalDenomTotal;
            //$output[$monthName]['National']['location_name'] = 'National';
        }
        
        return $output;
    }
}