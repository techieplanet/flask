<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StockoutController
 *
 * @author Swedge
 */
require_once ('ReportFilterHelpers.php');
require_once ('models/table/Helper2.php');
require_once('models/table/Stockout.php');
require_once  ('models/table/Report.php');
require_once('models/table/Dashboard.php');

class StockoutController extends ReportFilterHelpers {
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
    
    public function percentStockoutWithTrainedHWAction() {
	    $stockout = new Stockout();
	    $helper = new Helper2();
            
            //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
	      list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            
            $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
            
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();
            
            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
                $fp_stockout = $stockout->fetchPercentStockOutFacsWithTrainedHW('fp', $geoList, $tierValue, true,false,$lastPullDate);
                $larc_stockout = $stockout->fetchPercentStockOutFacsWithTrainedHW('larc', $geoList, $tierValue, true,false,$lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('larc', $geoList, $tierValue, true,false,$lastPullDate);
            }
            else{
                $fp_stockout = $stockout->fetchPercentStockOutFacsWithTrainedHW('fp', $geoList, $tierValue, false,false,$lastPullDate);
                $larc_stockout = $stockout->fetchPercentStockOutFacsWithTrainedHW('larc', $geoList, $tierValue, false,false,$lastPullDate);
                
                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
               
               list($generalNumFP,$generalDenomFP) = $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('fp', $tempGeoList, 1, false,false,$lastPullDate);
               list($generalNumLARC,$generalDenomLARC) =  $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('larc', $tempGeoList, 1, false,false,$lastPullDate);
               
               
                list($fp_numerator,$fp_denominator) = $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $stockout->fetchPercentStockOutFacsWithTrainedHWNumeratorDenominator('larc', $geoList, $tierValue, false,false,$lastPullDate);
                
               $fp_numerator['National'] = $generalNumFP['National'];
               $fp_denominator['National'] = $generalDenomFP['National'];
               
               $larc_numerator['National'] = $generalNumLARC['National'];
               $larc_denominator['National'] = $generalDenomLARC['National'];
            }
            
            $this->view->assign('larc_data', $larc_stockout);
	    $this->view->assign('fp_data', $fp_stockout);
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
	    
	    //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
	    //GNR:use max commodity date	    
	    $sDate = $helper->fetchTitleDate($lastPullDate);
	    $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
	    
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
	    $this->viewAssignEscaped ('locations', Location::getAll(1));
	
            $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);
	
	} //dashAction15
        
        
        public function percentFacsProvidingButStockedoutAction(){
            $stockout = new Stockout();
	    $helper = new Helper2();
            
            //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
	     list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            
            $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate); 
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();
            
            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
                $fp_stockout = $stockout->fetchPercentFacsProvidingButStockedOut('fp', $geoList, $tierValue, true, false,$lastPullDate);
                $larc_stockout = $stockout->fetchPercentFacsProvidingButStockedOut('larc', $geoList, $tierValue, true, false,$lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('fp', $geoList, $tierValue, true, false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('larc', $geoList, $tierValue, true, false,$lastPullDate);
            }
            else{
                $fp_stockout = $stockout->fetchPercentFacsProvidingButStockedOut('fp', $geoList, $tierValue, false, false,$lastPullDate);
                $larc_stockout = $stockout->fetchPercentFacsProvidingButStockedOut('larc', $geoList, $tierValue, false, false,$lastPullDate);
                
                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
                
                list($generalNumFP,$generalDenomFP) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('fp', $tempGeoList, 1, false, false,$lastPullDate);
                list($generalNumLARC,$generalDenomLARC) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('larc', $tempGeoList, 1, false, false,$lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('fp', $geoList, $tierValue, false, false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $stockout->fetchPercentFacsProvidingButStockedOutNumeratorDenominator('larc', $geoList, $tierValue, false, false,$lastPullDate);
                
                
               $fp_numerator['National'] = $generalNumFP['National'];
               $fp_denominator['National'] = $generalDenomFP['National'];
               
               $larc_numerator['National'] = $generalNumLARC['National'];
               $larc_denominator['National'] = $generalDenomLARC['National'];
               
            }
            
            $this->view->assign('larc_data', $larc_stockout);
	    $this->view->assign('fp_data', $fp_stockout);
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
            
	    
	    //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
	    //GNR:use max commodity date	    
	    $sDate = $helper->fetchTitleDate($lastPullDate);
	    $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
	    
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
	    $this->viewAssignEscaped ('locations', Location::getAll(1));
            
            $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);
        }
        
        public function stockoutsAction(){
            $dashboard = new Dashboard();
            $helper = new Helper2();
            
            //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
	     list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDatemultiple',$monthDate);
            $this->view->assign('monthNamemultiple',$monthName);
            
            $lastPullDatemultiple = array();
            if(isset($_POST['lastPullDatemultiple'])){
                $lastPullDatemultiple = $_POST['lastPullDatemultiple'];
            }
            $this->view->assign('selectedDatemultiple',$lastPullDatemultiple); 
            
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();
            
            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) && !isset($_POST['lastPullDatemultiple']) ) { 
                //$facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedout($geoList, $tierValue, true);
                $fp_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('fp', $geoList, $tierValue, true,$lastPullDatemultiple);
               // print_r($fp_facsProvidingStockedout);exit;
                
                $larc_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('larc', $geoList, $tierValue, true,$lastPullDatemultiple);
                
                //list($fp_numerator,$fp_denominator) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('fp', $geoList, $tierValue, true,$lastPullDatemultiple);
                //list($larc_numerator,$larc_denominator) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('larc', $geoList, $tierValue, true,$lastPullDatemultiple);
                $freshVisit = true;
            }
            else{
                //$facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedout($geoList, $tierValue, false);
                $fp_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('fp', $geoList, $tierValue, false,$lastPullDatemultiple);
                $larc_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('larc', $geoList, $tierValue, false,$lastPullDatemultiple);
                
                //list($fp_numerator,$fp_denominator) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('fp', $geoList, $tierValue, false,$lastPullDatemultiple);
                //list($larc_numerator,$larc_denominator) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('larc', $geoList, $tierValue, false,$lastPullDatemultiple);
                
                
                
                $freshVisit = false;
            }
                
            //$this->view->assign('facs_providing_stockedout', $facsProvidingStockedout);
            $this->view->assign('fp_facs_providing_stockedout', json_encode($fp_facsProvidingStockedout));
            $this->view->assign('larc_facs_providing_stockedout', json_encode($larc_facsProvidingStockedout));
            
            //$this->view->assign('fp_numerator',$fp_numerator);
            //$this->view->assign('fp_denominator',$fp_denominator);
            
            //$this->view->assign('larc_numerator',$larc_numerator);
            //$this->view->assign('larc_denominator',$larc_denominator);
            
            
            $title_date = $helper->fetchTitleDate();
            $monthNameDisplay = $helper->formatMonthNameYear($lastPullDatemultiple);
            $implodeDates = implode(",",$monthNameDisplay);
            
            if(empty($lastPullDatemultiple)){
            $this->view->assign('title_date', $title_date['month_name'] . ' ' . $title_date['year']);
            }else{
               $this->view->assign('title_date', $implodeDates);  
            }
            
            
            
            $overTimeDates = $helper->getPreviousMonthDates(12);
            
            if(!empty($lastPullDatemultiple)){
               // echo 'It is not empty';
             $overTimeDates = $lastPullDatemultiple;   
            }
           // echo $implodeDates;exit;
            //print_r($overTimeDates);exit;
            $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' ' . date('Y', strtotime($overTimeDates[0]))); 
            $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])) . ' ' . date('Y', strtotime($overTimeDates[11])));
            //print_r($lastPullDatemultiple);exit;
            $locationName = $helper->getLocationNames($geoList);            
            reset($locationName); $key = key($locationName);  $location_name = $locationName[$key];
            $this->view->assign('location_name', $location_name); 
            $this->view->assign('freshvisit', $freshVisit); 
            
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
            $this->viewAssignEscaped ('locations', Location::getAll(1));
            
            $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);
        }
        
}

?>
