<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConsumptionController
 *
 * @author Swedge
 */
require_once ('ReportFilterHelpers.php');
require_once ('models/table/Helper2.php');
require_once ('models/table/Report.php');
require_once('models/table/Consumption.php');
require_once('models/table/Commodity.php');

class ConsumptionController extends ReportFilterHelpers {
    //put your code here

    public function init(){
        parent::init();
        
        $burl = Settings::$COUNTRY_BASE_URL;
        if (substr($burl, -1) != '/' && substr($burl, -1) != '\\')
            $this->baseUrl = $burl . '/';
    }
    
    public function preDispatch() {
            parent::preDispatch ();

            if (!$this->isLoggedIn ())
                    $this->doNoAccessError ();

            //if (! $this->setting('module_employee_enabled')){
                    //$_SESSION['status'] = t('The employee module is not enabled on this site.');
                    //$this->_redirect('select/select');
            //}

            //if (! $this->hasACL ( 'employees_module' )) {
                    //$this->doNoAccessError ();
            //}
    }
    
    
    public function showitAction(){
        //var_dump($_POST); exit;
        $helper = new Helper2();
        $this->view->assign('text', 'this is a method');
        $this->view->assign('comms', $helper->getCommodities());
        $this->viewAssignEscaped ('locations', Location::getAll(1) );
    }
    
    
    //All commodities, all locations - DONE
    //All commodities, selected locations - 
    //Selected commodities, all locations - DONE
    //Selected commodities, selected locations - DONE
    public function consumptionAction(){
        $helper = new Helper2();
        $cons = new Consumption();
        $commodity = new Commodity();
        $methodNames = array();
        
        //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
        list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
        
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
        $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
            
          //  list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDatemultiple',$monthDate);
            $this->view->assign('monthNamemultiple',$monthName);
            
            $lastPullDatemultiple = array();
            if(isset($_POST['lastPullDatemultiple'])){
                $lastPullDatemultiple = $_POST['lastPullDatemultiple'];
            }
            $this->view->assign('selectedDatemultiple',$lastPullDatemultiple); 
            
          
        if( !isset($_POST["comm_id"]) || $_POST["comm_id"] == 0)
            $commodityIDList = array();
        else
            $commodityIDList = $_POST['comm_id'];
        
        //get the parameters
        list($geoList, $tierValue) = $this->buildParameters();

        $all = false;        
                
        if(!empty($commodityIDList)){  //commodity selected
            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
              $consByCommodity = $cons->fetchConsumptionByCommodity($commodityIDList, $lastPullDate);
              $consOverTime = $cons->fetchConsumptionByCommodityOverTime($commodityIDList, $lastPullDatemultiple);
                            
              $methodNames = $commodity->getCommodityMap($commodityIDList);
              $this->view->assign('consumption_by_method', json_encode($consByCommodity));
              $this->view->assign('consumption_overtime', json_encode($consOverTime));
              
            } else { // geo selected
                if(empty($lastPullDatemultiple)){
                    list($methodNames, $consByMultipleCommodityAndLocation) = $cons->fetchConsumptionByCommodityAndLocationOverTime($commodityIDList, $geoList, $tierValue);
                    
                    //$this->view->assign('method', $methodName);
                    $this->view->assign('consumption_bmmandlocation', json_encode($consByMultipleCommodityAndLocation));
                }else{
                   list($methodNames, $consByMultipleCommodityAndLocation) = $cons->fetchConsumptionByCommodityAndLocationOverTime($commodityIDList, $geoList, $tierValue,$lastPullDatemultiple);  
                   //$this->view->assign('method', $methodName);
                   $this->view->assign('consumption_bmmandlocation', json_encode($consByMultipleCommodityAndLocation));
                }
                
                //get current month consumption 
                if(!empty($lastPullDate)){
                    $searchString = date('F',strtotime($lastPullDate));
                    end($consByMultipleCommodityAndLocation);
                    $endKey = key($consByMultipleCommodityAndLocation);
               
                    $this->view->assign('consumption_bmmandlocation_first', json_encode(array(
                                        'methods'=>$methodNames, 
                                        'consumption'=>($consByMultipleCommodityAndLocation[$searchString]))));
                } else {
                  $this->view->assign('consumption_bmmandlocation_first', json_encode(array('methods'=>$methodNames, 
                    'consumption'=>json_encode($consByMultipleCommodityAndLocation[$endKey])))); 
                }
            }    
            
                $updatedMonth = array_reverse($monthDate);
                
                $geoListArray = explode(',', $geoList);
                if(count($commodityIDList)>1 && count($geoListArray)>1)
                    $this->view->assign('showlinechart', FALSE);
                else if(count($commodityIDList)==1 && count($geoListArray)>=1)
                    $this->view->assign('showlinechart', TRUE);
                else if(count($commodityIDList)>=1 && count($geoListArray)==1){
                    $this->view->assign('commlinechart', TRUE);
                }
                
        }
        else{ //ALL: when ALL commodity and/or no geo option selected
            //echo 'no comm sel'; exit;
            $consByCommodity = $consOverTime = array();
          if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
                $consByCommodity = $cons->fetchConsumptiomPerCommodity(0, $geoList, $tierValue,$lastPullDate);
                $consOverTime = $cons->fetchConsumptionByCommodityOverTime([], $lastPullDatemultiple);
                
                //var_dump($consOverTime); exit;
                
                $this->view->assign('consumption_by_method', json_encode($consByCommodity));
                $this->view->assign('consumption_overtime', json_encode($consOverTime));
                //var_dump($consOverTime); exit;
          }
          else{ //geo selected
              $consByCommodity = $cons->fetchConsumptiomPerCommodity(0, $geoList, $tierValue,$lastPullDate);

              list($location,$consAllBySingleLocOverTime) = $cons->fetchAllConsumptionBySingleLocationOverTime($geoList, $tierValue,$lastPullDatemultiple);
              
              $this->view->assign('single_location', $location);
              $this->view->assign('consumption_by_method',json_encode($consByCommodity));
              $this->view->assign('cons_all_BSL_overtime',json_encode($consAllBySingleLocOverTime));
          }   
        }

        //send the array of selected commodities
        $this->view->assign('methods',$methodNames);
        
        //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
        //GNR:use max commodity date
        $sDate = $helper->fetchTitleDate($lastPullDate);
        $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
        
        $overTimeDates = $helper->getPreviousMonthDates(12);
        $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 
        $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
        
        //this will provide the commodities list for the drop down
        $this->view->assign('comms', $helper->getCommodities());
        
        $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
        $this->viewAssignEscaped ('locations', Location::getAll(1) );
        
        $this->view->assign('base_url', $this->baseUrl);

    }
    
    
    
    
    
    public function consbygeographyAction() {
        
        $helper = new Helper2();

        $this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));

        if( !isset($_POST["comm_id"]) || $_POST["comm_id"] == 0)
            $commodityID = 0;
        else
            $commodityID = $_POST['comm_id'];
        //echo $commodityID; exit;
        
        //get the parameters
        list($geoList, $tierValue) = $this->buildParameters();

        //get the location names
        //$locationNames = $helper->getLocationNames($geoList);

        $cons = new Consumption();
        if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
            $consByCommodity = $cons->fetchConsumptiomPerCommodity($commodityID, $geoList, $tierValue);
            $this->view->assign('consumption_by_method',$consByCommodity);
        }
        else{
            //echo 'cons cons'; exit;
            //$consByGeo
            $consByCommodity = $cons->fetchConsumptiomPerCommodity($commodityID, $geoList, $tierValue);
            //$consByGeo = $cons->fetchConsumptiomByGeography($commodityID, $geoList, $tierValue);
            //$methodName = $consByGeo['method'];
            //$consByGeoData = $consByGeo['locationdata'];
            
            //var_dump($consByCommodity);
            //var_dump($consByGeoData); exit;

            
            $this->view->assign('consumption_by_method',$consByCommodity);
            //$this->view->assign('consumption_by_geo',$consByGeoData);
            //$this->view->assign('method',$methodName);
      }
        
        

        //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
        //GNR:use max commodity date
        $sDate = $helper->fetchTitleDate();
        $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
            
        //this will provide the commodities list for the drop down
        $this->view->assign('comms', $helper->getCommodities());
        
        $this->viewAssignEscaped ('locations', Location::getAll(1) );
        
        $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);

  } // dash996Action
  
  /*
   * new acceptors - mvBO08ctlWw
     current users - G5mKWErswJ0

     Default - Grouped bar
   * 
   *  chart and Over time chart with 2 national lines, one for each indicator
     One location - Grouped bar chart and Over time chart with 3 lines, one for national, one for each indicator
     Multiple locations/single commodity - bar chart and commodity over time chart and with the national line

   */
  public function newandcurrentfpusersAction(){
        $helper = new Helper2();
        $consumption = new Consumption();
        //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
        $selectedMethods = array();
        
         list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
        $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
            
          //  list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDatemultiple',$monthDate);
            $this->view->assign('monthNamemultiple',$monthName);
            
            $lastPullDatemultiple = array();
            if(isset($_POST['lastPullDatemultiple'])){
                $lastPullDatemultiple = $_POST['lastPullDatemultiple'];
            }
            $this->view->assign('selectedDatemultiple',$lastPullDatemultiple); 
            
        $selectedMethods = ( !isset($_POST["comm_id"]) || $_POST["comm_id"] == 0) ? array() : $_POST['comm_id'];
        
        if(!empty($selectedMethods)){
            $this->view->assign('commodityObject',$helper->getCommodity($selectedMethods[0]));  
            $commodityObjectsArray = array();
            foreach($selectedMethods as $method)
                $commodityObjectsArray[] = $helper->getCommodity($method);
            
            //this is an array of all the selected commodities
            $this->view->assign('commodityObjectsArray',$commodityObjectsArray);  
        }
        
        
        $this->view->assign('methods',$selectedMethods);
        
        //get the parameters
        list($geoList, $tierValue) = $this->buildParameters();
        $freshVisit =  (!isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) && !isset($_POST['lastPullDatemultiple'])) ? true : false;
        
        //$all = false;
        if(empty($selectedMethods)){  //commodity NOT selected
            
            //$consumptionArrays = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit);
            
            if(!empty($lastPullDatemultiple)){
             $consumptionArray = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit,$lastPullDatemultiple); 
            }
            else {
             $consumptionArray = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit); 
            }
            $this->view->assign('default_option', true);
            $this->view->assign('consumptionArray', json_encode($consumptionArray));
            //$this->view->assign('consumptionArrays', json_encode($consumptionArrays));
            //var_dump('$consumptionArray', $consumptionArray); exit;
            
            //handle the array for the current month
            if(!empty($lastPullDate)){
                $pullMonthName = date('F', strtotime($lastPullDate));
                $currentMonthConsumptionArray = array_key_exists($pullMonthName, $consumptionArray) ?
                                                    $consumptionArray[$pullMonthName] :
                                                    end($consumptionArray);
            }
            else {
                $currentMonthConsumptionArray = end($consumptionArray);
            }
            $this->view->assign('currentMonthConsumptionArray', json_encode($currentMonthConsumptionArray));
            
                        
            $geoListSelectionMade =  !$freshVisit;
            if($geoListSelectionMade){
                $geoLocations = $helper->getLocationNames($geoList);
                $this->view->assign('geoLocations', $geoLocations);
                $this->view->assign('geoListSelectionMade', $geoListSelectionMade);
            }                
        } else { //commodity selected 
            //echo 'here 2'; exit;
            //$consumptionArrays = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit);
              if(!empty($lastPullDatemultiple)){
             $consumptionArray = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit,$lastPullDatemultiple); 
            }else{
             $consumptionArray = $consumption->fetchNewAcceptorsAndCurrentUsers($selectedMethods,$geoList, $tierValue, $freshVisit); 
            }
            
            if( $freshVisit ) {
                if(count($commodityObjectsArray) > 1) 
                //with this option, we have to revert to default visualizatoion bcos the presentation will need a grouped bar chart and two national lines on the line chart. 
                    $this->view->assign('default_option', true);
                else
                    $this->view->assign('commodityOnly', true);
            }
            else{ //geo selection made
                $geoLocations = $helper->getLocationNames($geoList);
                $this->view->assign('commodityAndLocation', true);
                $this->view->assign('geoLocations', $geoLocations);
            }
            
            //handle the array for the current month
            if(!empty($lastPullDate)){
                $pullMonthName = date('F', strtotime($lastPullDate));
                $currentMonthConsumptionArray = array_key_exists($pullMonthName, $consumptionArray) ?
                                                    $consumptionArray[$pullMonthName] :
                                                    end($consumptionArray);
            }
            else {
                $currentMonthConsumptionArray = end($consumptionArray);
            }
            
            $this->view->assign('consumptionArray', json_encode($consumptionArray));
            $this->view->assign('currentMonthConsumptionArray', json_encode($currentMonthConsumptionArray));
        }

        
//        //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
//        //GNR:use max commodity date
          $sDate = $helper->fetchTitleDate($lastPullDate);
          $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
        
          $overTimeDates = $helper->getPreviousMonthDates(12);
          $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 
          $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
        
          //this will provide the commodities list for the drop down
          $comm = array();
          $newAcceptors = $helper->getCommodityByAlias("new_acceptors"); 
          //$comm[] = array('id'=>$newAcceptors['id'], 'commodity_name'=>$newAcceptors['commodity_name']);
          $comm[] = array('id'=>$newAcceptors['id'], 'commodity_name'=>'New FP Acceptors');
          
          $currentUsers = $helper->getCommodityByAlias("current_users"); 
                    
          //$comm[] = array('id'=>$currentUsers['id'], 'commodity_name'=>$currentUsers['commodity_name']);
          $comm[] = array('id'=>$currentUsers['id'], 'commodity_name'=>'Current FP Users');
          //var_dump($comm); exit;
          
          $this->view->assign('comms', $comm);
           //var_dump($comm); exit;
        
          $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
          $this->viewAssignEscaped('locations', Location::getAll(1));
        
          $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);
          $this->view->assign('chart_module','newfpacceptors');
  }
}

?>