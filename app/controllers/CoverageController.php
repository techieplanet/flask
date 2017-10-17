<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CoverageController
 *
 * @author Swedge
 */
require_once ('ReportFilterHelpers.php');
require_once ('models/table/Helper2.php');
require_once ('models/table/Coverage.php');
require_once ('models/table/CIPCoverage.php');
require_once ('models/table/Report.php');

class CoverageController extends ReportFilterHelpers {
    
    public function init(){
        parent::init();
        
        $burl = Settings::$COUNTRY_BASE_URL;
        if (substr($burl, -1) != '/' && substr($burl, -1) != '\\')
            $this->baseUrl = $burl . '/';
        
    }
    
    public function testDataDumpAction(){
        $this->_helper->viewRenderer->setNoRender(TRUE);
        $metric = new MetricClient();
        //$a = new MongoDB\BSON\UTCDateTime(floor(microtime(true) * 1000));
        //var_dump($a);
                
        /**
         * Testing for location dump
         */
//        $arr = array(
//                array('_id'=>1, 'location_name'=>'AA', 'tier'=>2),
//                array('_id'=>2, 'location_name'=>'B', 'tier'=>3)
//            );
//        $metric->handleDataDump($arr, 'locations');
           
        
        /**
         * Testing for users dump
         */
//        $arr = array(
//                array('_id' => 1, 'username' => 'af', 'email' => 'a@b.com', 'first_name' => 'fn', 'last_name' => 'ln', 'role_id' => 1, 
//                      'province_id' => 56, 'district_id' => 577, 'region_c_id' => 59, 'multiple_locations_id' => array('1817_11','1818_88'),
//                      'role' => 'Administrator'),
//                array('_id' => 2, 'username' => 'bg', 'email' => 'c@d.com', 'first_name' => 'nf', 'last_name' => 'nl', 'role_id' => 2, 
//                      'province_id' => 67, 'district_id' => 68, 'region_c_id' => 69, 'multiple_locations_id' => array('1817_67','1818_69'),
//                      'role' => 'FMOH')
//            );
//        $metric->handleDataDump($arr, 'location');
        
        /**
         * Testing metric querying
         */
        
        //$arr = array('userid'=>"22", 'module_name'=>'dc', 'mtimestamp'=>floor(microtime(true) * 1000) );
//        $arr = array('userid'=>array('$gt' => "19"), 'mtimestamp'=>array('$lt'=>1467893599226));
//        //$optionsArray['projection'] = array("location_name"=>1);
//        $optionsArray = array();
      
//        $keyword = 'find';
//        
//        $result = $metric->handleDataGet($arr, $optionsArray, $keyword, 'metrics');
//        
//        var_dump(count(json_decode($result))); exit;
        //var_dump($result); exit;
    }
    
    //put your code here
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
            
            //$this->sendMetricDetails();
            
    }

//    private function sendMetricDetails(){                
//        if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ){
//            $module = MetricClient::METRIC_MODULES_CHART;
//            $pageId = strtolower(MetricClient::METRIC_MODULES_CHART) . "_" .
//                      $this->getRequest()->getControllerName() . "_" . 
//                      $this->getRequest()->getActionName();
//            
//            $methods = get_class_methods(ucfirst($this->getRequest()->getControllerName()) . 'Controller');
//            foreach($methods as $method)
//                print $method . '<br>';
//            exit;
//            
//            $pageUrl = $this->getRequest()->getRequestUri(); 
//            $userId = Zend_Auth::getInstance()->getIdentity()->id;
//            $metric = new MetricClient(); 
//            $metric->handleVisitMetrics($module, $pageId, $userId);
//        }
//        else
//            $client = new MetricClient(MetricClient::METRIC_MODULES_CHART, $_POST, MetricClient::ACTION_TYPE_SEARCH,'','');
//    }
    
    
    //TP: rewrote most part of this method 2/4/2015
	public function cummhwtrainedAction() {
            
            $coverage = new Coverage();
            $helper = new Helper2();
            
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();
            list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            
            $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
            //echo $lastPullDate;exit;
            //If no GEO selection made 
	    if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"])){ 
                $cumm_data = $coverage->fetchCummulativeTrainedWorkers(3, $geoList, $tierValue,$lastPullDate);
                $this->view->assign('cumm_data', $cumm_data);
	    }
            else{
                $cumm_data = $coverage->fetchCummulativeTrainedWorkers(3, $geoList, $tierValue,$lastPullDate);
                $this->view->assign('cumm_data', $cumm_data);
                //var_dump($cumm_data); exit;
                
                $locationNames = $helper->getLocationNames($geoList);
                $larc_cumm_location = $coverage->fetchCummulativeTrainedWorkersByLocation('larc',3, $geoList, $tierValue,$lastPullDate);
                $fp_cumm_location = $coverage->fetchCummulativeTrainedWorkersByLocation('fp',3, $geoList, $tierValue,$lastPullDate);
                
                $this->view->assign('fp_cumm_location', $fp_cumm_location);
                $this->view->assign('larc_cumm_location', $larc_cumm_location);
                $this->view->assign('cumm_locations', $helper->getLocationNames($geoList));
            }
                
            //GNR:use max commodity date
            $sDate = $helper->fetchTitleDate($lastPullDate);
            $this->view->assign('tp_date', $sDate['month_name'] . ' '.$sDate['year']);
            
            $this->view->assign('base_url', $this->baseUrl);            

            //locations
            $this->view->assign('cumm_data', $cumm_data);
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
            $this->viewAssignEscaped ('locations', Location::getAll(1));
                        
            //$this->sendMetricDetails();
            //file_put_contents("vvlogs.txt", print_r(debug_backtrace()) . "\r\n", FILE_APPEND ); 

    }
    
    public function cdAction() {
            
            $coverage = new Coverage();
            $helper = new Helper2();
            
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();
            list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            
            $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
            //echo $lastPullDate;exit;
            //If no GEO selection made 
	    if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"])){ 
                $cumm_data = $coverage->fetchCummulativeTrainedWorkers(3, $geoList, $tierValue,$lastPullDate);
                $this->view->assign('cumm_data', $cumm_data);
	    }
            else{
                $cumm_data = $coverage->fetchCummulativeTrainedWorkers(3, $geoList, $tierValue,$lastPullDate);
                $this->view->assign('cumm_data', $cumm_data);
                //var_dump($cumm_data); exit;
                
                $locationNames = $helper->getLocationNames($geoList);
                $larc_cumm_location = $coverage->fetchCummulativeTrainedWorkersByLocation('larc',3, $geoList, $tierValue,$lastPullDate);
                $fp_cumm_location = $coverage->fetchCummulativeTrainedWorkersByLocation('fp',3, $geoList, $tierValue,$lastPullDate);
                
                $this->view->assign('fp_cumm_location', $fp_cumm_location);
                $this->view->assign('larc_cumm_location', $larc_cumm_location);
                $this->view->assign('cumm_locations', $helper->getLocationNames($geoList));
            }
                
            //GNR:use max commodity date
            $sDate = $helper->fetchTitleDate($lastPullDate);
            $this->view->assign('tp_date', $sDate['month_name'] . ' '.$sDate['year']);
            
            $this->view->assign('base_url', $this->baseUrl);            

            //locations
            $this->view->assign('cumm_data', $cumm_data);
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
            $this->viewAssignEscaped ('locations', Location::getAll(1));
                        
            //$this->sendMetricDetails();
            //file_put_contents("vvlogs.txt", print_r(debug_backtrace()) . "\r\n", FILE_APPEND ); 

    }
    
    
    
    public function facswithhwprovidingAction() {
	    $coverage = new Coverage();
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
            
            //set date limit
            //$dateWhere = 'c.date = \'' . $helper->getLatestPullDate() . '\'';
            //$dateWhere = $helper->getLatestPullDate();
            
            //get the location names
            //$locationNames = $helper->getLocationNames($geoList);
            
            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
                $fp_coverage = $coverage->fetchFacsWithHWProviding('fp', 'fp', $geoList, $tierValue, true,false,$lastPullDate);
                $larc_coverage = $coverage->fetchFacsWithHWProviding('larc', 'larc', $geoList, $tierValue, true,false,$lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $coverage->fetchFacsWithHWProvidingNumeratorDenominator('fp', 'fp', $geoList, $tierValue, true,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $coverage->fetchFacsWithHWProvidingNumeratorDenominator('larc', 'larc', $geoList, $tierValue, true,false,$lastPullDate);
                
                
            }
            else{
                $fp_coverage = $coverage->fetchFacsWithHWProviding('fp', 'fp', $geoList, $tierValue, false,false,$lastPullDate);
                $larc_coverage = $coverage->fetchFacsWithHWProviding('larc', 'larc', $geoList, $tierValue, false,false,$lastPullDate);
                
                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
               
               list($generalNumFP,$generalDenomFP) =  $coverage->fetchFacsWithHWProvidingNumeratorDenominator('fp', 'fp', $tempGeoList, 1, false,false,$lastPullDate);
               list($generalNumLARC,$generalDenomLARC) = $coverage->fetchFacsWithHWProvidingNumeratorDenominator('larc', 'larc', $tempGeoList, 1, false,false,$lastPullDate);
              
               
                
                list($fp_numerator,$fp_denominator) = $coverage->fetchFacsWithHWProvidingNumeratorDenominator('fp', 'fp', $geoList, $tierValue, false,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $coverage->fetchFacsWithHWProvidingNumeratorDenominator('larc', 'larc', $geoList, $tierValue, false,false,$lastPullDate);
                
                
               $fp_numerator['National'] = $generalNumFP['National'];
               $fp_denominator['National'] = $generalDenomFP['National'];
               
               $larc_numerator['National'] = $generalNumLARC['National'];
               $larc_denominator['National'] = $generalDenomLARC['National'];
            }
            
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
            
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('fp_data',$fp_coverage);
            $this->view->assign('larc_data',$larc_coverage);
            	
	    //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
	    //GNR:use max commodity date
	    $sDate = $helper->fetchTitleDate($lastPullDate);
	    $this->view->assign('date', $sDate['month_name'] . ' ' .$sDate['year']); 
	    
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
	    $this->viewAssignEscaped ('locations', Location::getAll(1) );
	      
            $this->view->assign('base_url', $this->baseUrl);
            
	} //dashAction13
        
//        public function percentfacsprovidingallmethodsAction(){
//            $coverage =  new Coverage();
//            $helper = new Helper2();
//            
//            list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
//            $this->view->assign('monthDate',$monthDate);
//            $this->view->assign('monthName',$monthName);
//            
//            $lastPullDate = "";
//            if(isset($_POST['lastPullDate'])){
//                $lastPullDate = $_POST['lastPullDate'];
//            }
//            $this->view->assign('selectedDate',$lastPullDate);
//            
//            
//              list($geoList, $tierValue) = $this->buildParameters();
//
//            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
//                
//                $fp_coverage = $coverage->fetchPercentFacsProvidingAllMethods('fp', $geoList, $tierValue, true,false,$lastPullDate);
//                $larc_coverage = $coverage->fetchPercentFacsProvidingAllMethods('larc', $geoList, $tierValue, true,false,$lastPullDate);
//                $inj_coverage = $coverage->fetchPercentFacsProvidingAllMethods('injectables', $geoList, $tierValue, true,false,$lastPullDate);
//                
//                list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDate);
//                list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('larc', $geoList, $tierValue, true,false,$lastPullDate);
//                list($inj_numerator,$inj_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('injectables', $geoList, $tierValue, true,false,$lastPullDate);
//             }
//            else{
//                $fp_coverage = $coverage->fetchPercentFacsProvidingAllMethods('fp', $geoList, $tierValue, false,false,$lastPullDate);
//                $larc_coverage = $coverage->fetchPercentFacsProvidingAllMethods('larc', $geoList, $tierValue, false,false,$lastPullDate);
//                $inj_coverage = $coverage->fetchPercentFacsProvidingAllMethods('injectables', $geoList, $tierValue, false,false,$lastPullDate);
//                
//                
//                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
//               
//               list($generalNumFP,$generalDenomFP) =  $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $tempGeoList, 1, false,false,$lastPullDate);
//               list($generalNumLARC,$generalDenomLARC) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('larc', $tempGeoList, 1, false,false,$lastPullDate);
//               list($generalNumINJ,$generalDenomINJ) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('injectables', $tempGeoList, 1, false,false,$lastPullDate);
//               
//                
//                list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDate);
//                list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('larc', $geoList, $tierValue, false,false,$lastPullDate);
//                list($inj_numerator,$inj_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('injectables', $geoList, $tierValue, false,false,$lastPullDate);
//
//                
//               $fp_numerator['National'] = $generalNumFP['National'];
//               $fp_denominator['National'] = $generalDenomFP['National'];
//               
//               $larc_numerator['National'] = $generalNumLARC['National'];
//               $larc_denominator['National'] = $generalDenomLARC['National'];
//               
//               $inj_numerator['National'] = $generalNumINJ['National'];
//               $inj_denominator['National'] = $generalDenomINJ['National'];
//            }
//
//            
//            $this->view->assign('fp_numerator',$fp_numerator);
//            $this->view->assign('fp_denominator',$fp_denominator);
//            
//            
//            $this->view->assign('larc_numerator',$larc_numerator);
//            $this->view->assign('larc_denominator',$larc_denominator);
//            
//            $this->view->assign('inj_numerator',$inj_numerator);
//            $this->view->assign('inj_denominator',$inj_denominator);
//
//            $this->view->assign('fp_data',$fp_coverage);
//            $this->view->assign('larc_data',$larc_coverage);
//            $this->view->assign('inj_data',$inj_coverage);
//
//            //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
//            //GNR:use max commodity date
//            $sDate = $helper->fetchTitleDate($lastPullDate);
//            $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 
//
//            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
//	    $this->viewAssignEscaped('locations', Location::getAll(1));
//
//            $this->view->assign('base_url', $this->baseUrl);
//            
//        }
        
        public function percentfacsprovidingAction() {
            $coverage = new Coverage(); $cipCoverage = new CIPCoverage();
            $helper = new Helper2();
	    list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            
            $lastPullDate = "";
            if(isset($_POST['lastPullDate'])){
                $lastPullDate = $_POST['lastPullDate'];
            }
            $this->view->assign('selectedDate',$lastPullDate);
	    //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));

            
            //get the parameters
            list($geoList, $tierValue) = $this->buildParameters();

            if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ) { 
                
                $fp_coverage = $coverage->fetchPercentFacsProviding('fp', $geoList, $tierValue, true,false,$lastPullDate);
                $larc_coverage = $coverage->fetchPercentFacsProviding('larc', $geoList, $tierValue, true,false,$lastPullDate);
                $inj_coverage = $coverage->fetchPercentFacsProviding('injectables', $geoList, $tierValue, true,false,$lastPullDate);
                //$modern_method_coverage = $coverage->fetchPercentFacsProvidingAllMethods('fp', $geoList, $tierValue, true,false,$lastPullDate);
                $modern_method_coverage = $cipCoverage->fetchFacsProvidingNumberOfMethods('', 3, $geoList, $tierValue, true, false, $lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('larc', $geoList, $tierValue, true,false,$lastPullDate);
                list($inj_numerator,$inj_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('injectables', $geoList, $tierValue, true,false,$lastPullDate);
                //list($modern_method_numerator,$modern_method_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDate);
               
                
             }
            else{
                $fp_coverage = $coverage->fetchPercentFacsProviding('fp', $geoList, $tierValue, false,false,$lastPullDate);
                $larc_coverage = $coverage->fetchPercentFacsProviding('larc', $geoList, $tierValue, false,false,$lastPullDate);
                $inj_coverage = $coverage->fetchPercentFacsProviding('injectables', $geoList, $tierValue, false,false,$lastPullDate);
                //$modern_method_coverage = $coverage->fetchPercentFacsProvidingAllMethods('fp', $geoList, $tierValue, false,false,$lastPullDate);
                $modern_method_coverage = $cipCoverage->fetchFacsProvidingNumberOfMethods('', 3, $geoList, $tierValue, false, false, $lastPullDate);
                
                
                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
               
               list($generalNumFP,$generalDenomFP) =  $coverage->fetchPercentFacsProvidingNumeratorDenominator('fp', $tempGeoList, 1, false,false,$lastPullDate);
               list($generalNumLARC,$generalDenomLARC) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('larc', $tempGeoList, 1, false,false,$lastPullDate);
               list($generalNumINJ,$generalDenomINJ) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('injectables', $tempGeoList, 1, false,false,$lastPullDate);
               //list($generalModern_method_numerator,$generalModern_method_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $tempGeoList, 1, false,false,$lastPullDate);
               
                
                list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('larc', $geoList, $tierValue, false,false,$lastPullDate);
                list($inj_numerator,$inj_denominator) = $coverage->fetchPercentFacsProvidingNumeratorDenominator('injectables', $geoList, $tierValue, false,false,$lastPullDate);
                //list($modern_method_numerator,$modern_method_denominator) = $coverage->fetchPercentFacsProvidingAllMethodsNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDate);
               
                
               $fp_numerator['National'] = $generalNumFP['National'];
               $fp_denominator['National'] = $generalDenomFP['National'];
               
               $larc_numerator['National'] = $generalNumLARC['National'];
               $larc_denominator['National'] = $generalDenomLARC['National'];
               
               $inj_numerator['National'] = $generalNumINJ['National'];
               $inj_denominator['National'] = $generalDenomINJ['National'];
               
               //$modern_method_numerator['National'] = $generalModern_method_numerator['National'];
               //$modern_method_denominator['National'] = $generalModern_method_denominator['National'];
            }

            
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
            
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('inj_numerator',$inj_numerator);
            $this->view->assign('inj_denominator',$inj_denominator);
            
            //$this->view->assign('modern_method_numerator',$modern_method_numerator);
            //$this->view->assign('modern_method_denominator',$modern_method_denominator);
            
            

            $this->view->assign('fp_data',$fp_coverage);
            $this->view->assign('larc_data',$larc_coverage);
            $this->view->assign('inj_data',$inj_coverage);
            $this->view->assign('modern_method_data',$modern_method_coverage);
            
            //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
            //GNR:use max commodity date
            $sDate = $helper->fetchTitleDate($lastPullDate);
            $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 

            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
	    $this->viewAssignEscaped('locations', Location::getAll(1));

            $this->view->assign('base_url', $this->baseUrl);
       }
   
   
   
        
        public function percentfacswithtrainedhwAction() {
            $coverage = new Coverage();
            $helper = new Helper2();
	    
            //$ids = $helper->getTierLocationsIds(1); var_dump($ids); exit;
            
	    //$this->view->assign('title', $this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
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
                $fp_coverage = $coverage->fetchPercentFacHWTrained('fp', $geoList, $tierValue, true,false,$lastPullDate);
                $larc_coverage = $coverage->fetchPercentFacHWTrained('larc', $geoList, $tierValue, true,false,$lastPullDate);
                
                list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacHWTrainedNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDate);
                list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacHWTrainedNumeratorDenominator('larc', $geoList, $tierValue, true,false,$lastPullDate);
            }
            
            else{
             
                $fp_coverage = $coverage->fetchPercentFacHWTrained('fp', $geoList, $tierValue, false,false,$lastPullDate);
                $larc_coverage = $coverage->fetchPercentFacHWTrained('larc', $geoList, $tierValue, false,false,$lastPullDate);
                
                $tempGeoList = implode(",",$helper->getLocationTierIDs(1));
               
               list($generalNumFP,$generalDenomFP) =  $coverage->fetchPercentFacHWTrainedNumeratorDenominator('fp', $tempGeoList, 1, false,false,$lastPullDate);
               list($fp_numerator,$fp_denominator) = $coverage->fetchPercentFacHWTrainedNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDate);
               
               
               list($generalNumLARC,$generalDenomLARC) =  $coverage->fetchPercentFacHWTrainedNumeratorDenominator('larc', $tempGeoList, 1, false,false,$lastPullDate);
               list($larc_numerator,$larc_denominator) = $coverage->fetchPercentFacHWTrainedNumeratorDenominator('larc', $geoList, $tierValue, false,false,$lastPullDate);
               
               
               $fp_numerator['National'] = $generalNumFP['National'];
               $fp_denominator['National'] = $generalDenomFP['National'];
               
               $larc_numerator['National'] = $generalNumLARC['National'];
               $larc_denominator['National'] = $generalDenomLARC['National'];
            }
            
//            var_dump($fp_coverage);
//            echo '<br/><br/>';
//            var_dump($larc_coverage); exit;
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
            
//            Helper2::jLog("-------------------- Numerator TEST --------------");
//            Helper2::jLog(print_r($larc_numerator,true));
//            Helper2::jLog("-------------------Denominator Test---------------");
//            Helper2::jLog(print_r($larc_denominator,true));
            $this->view->assign('fp_data',$fp_coverage);
            $this->view->assign('larc_data',$larc_coverage);
            
            //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
	    //GNR:use max commodity date
	    //$tDate = new DashboardCHAI();
	    $sDate = $helper->fetchTitleDate($lastPullDate);
	    $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']);
	   
//            $zone = $this->getSanParam('province_id');
//            $state  = $this->getSanParam('district_id');
//            $localgovernment  = $this->getSanParam('region_c_id');
            
//            $criteria = array();
//            $criteria['province_id'] = $zone;
//            $criteria['district_id'] = $state;
//            $criteria['region_c_id'] = $localgovernment;
//            $criteria['error'] = $error;
//            
//            $this->viewAssignEscaped('criteria',$criteria);            
            
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
	    $this->viewAssignEscaped('locations', Location::getAll(1));

            $this->view->assign('base_url', $this->baseUrl);$this->view->assign('base_url', $this->baseUrl);
	}
        
        
        function coverageovertimeAction(){
            $helper = new Helper2();
            $coverage = new Coverage();
            $freshVisit = true;
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
                $fp_overtime = $coverage->fetchHWCoverageOvertime('fp', $geoList, $tierValue, true,false,$lastPullDatemultiple);
                $numeratorDenominatorsFP = $coverage->fetchHWCoverageOvertimeNumeratorDenominator('fp', $geoList, $tierValue, true,false,$lastPullDatemultiple);
                
                $larc_overtime = $coverage->fetchHWCoverageOvertime('larc', $geoList, $tierValue, true,false,$lastPullDatemultiple);
                $numeratorDenominatorsLARC = $coverage->fetchHWCoverageOvertimeNumeratorDenominator('larc', $geoList, $tierValue, true,false,$lastPullDatemultiple);
            }
            else { 
                $fp_overtime = $coverage->fetchHWCoverageOvertime('fp', $geoList, $tierValue, false,false,$lastPullDatemultiple);
                $numeratorDenominatorsFP = $coverage->fetchHWCoverageOvertimeNumeratorDenominator('fp', $geoList, $tierValue, false,false,$lastPullDatemultiple);
                
                $larc_overtime = $coverage->fetchHWCoverageOvertime('larc', $geoList, $tierValue, false,false,$lastPullDatemultiple);
                $numeratorDenominatorsLARC = $coverage->fetchHWCoverageOvertimeNumeratorDenominator('larc', $geoList, $tierValue, false,false,$lastPullDatemultiple);
                $freshVisit = false;
            }
            
            $locationName = $helper->getLocationNames($geoList);
            
            $this->view->assign('fp_overtime', $fp_overtime); 
            $this->view->assign('larc_overtime', $larc_overtime); 
            
            $this->view->assign('fp_numerator_denominator',$numeratorDenominatorsFP);
            $this->view->assign('larc_numerator_denominator',$numeratorDenominatorsLARC);

           
            //$this->view->assign('date', date('F Y', strtotime("-1 months"))); //TA:17:18: take last month
            //GNR:use max commodity date
            $sDate = $helper->fetchTitleDate();
            $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 

            $overTimeDates = $helper->getPreviousMonthDates(12);
            $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 
            $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
            $this->view->assign('freshvisit', $freshVisit); 
            
            reset($locationName); $key = key($locationName);  $location_name = $locationName[$key];
            $this->view->assign('location_name', $location_name);
            
            $this->view->assign('base_url', $this->baseUrl);
            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
            $this->viewAssignEscaped('locations', Location::getAll(1) );
        }
        
        
        function providingovertimeAction(){
            $helper = new Helper2();
            $coverage = new Coverage();
            $cipCoverage = new CIPCoverage();
            
            //$this->view->assign('title',$this->t['Application Name'].space.t('CHAI').space.t('Dashboard'));
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
                $fp_overtime = $coverage->fetchProvidingOvertime('fp', $geoList, $tierValue, true,$lastPullDatemultiple);
                $larc_overtime = $coverage->fetchProvidingOvertime('larc', $geoList, $tierValue, true,$lastPullDatemultiple);
                list($fp_numerator,$fp_denominator) = $coverage->fetchProvidingOvertimeNumeratorDenominator('fp', $geoList, $tierValue, true,$lastPullDatemultiple);
                list($larc_numerator,$larc_denominator) = $coverage->fetchProvidingOvertimeNumeratorDenominator('larc', $geoList, $tierValue, true,$lastPullDatemultiple);
                $modern_method_coverage = $cipCoverage->fetchFacsProvidingNumberOfMethodsOvertime('', 3, $geoList, $tierValue, true, false, $lastPullDatemultiple);
                
            }
            else {
                $fp_overtime = $coverage->fetchProvidingOvertime('fp', $geoList, $tierValue, false,$lastPullDatemultiple);
                $larc_overtime = $coverage->fetchProvidingOvertime('larc', $geoList, $tierValue, false,$lastPullDatemultiple);
                list($fp_numerator,$fp_denominator) = $coverage->fetchProvidingOvertimeNumeratorDenominator('fp', $geoList, $tierValue, true,$lastPullDatemultiple);
                list($larc_numerator,$larc_denominator) = $coverage->fetchProvidingOvertimeNumeratorDenominator('larc', $geoList, $tierValue, true,$lastPullDatemultiple);
                $modern_method_coverage = $cipCoverage->fetchFacsProvidingNumberOfMethodsOvertime('', 3, $geoList, $tierValue, false, false, $lastPullDatemultiple); 
            }
            
            $monthNameDisplay = $helper->formatMonthName($lastPullDatemultiple);
            $this->view->assign('fp_numerator',$fp_numerator);
            $this->view->assign('fp_denominator',$fp_denominator);
            $this->view->assign('modern_method_data', json_encode($modern_method_coverage));
            
            $this->view->assign('larc_numerator',$larc_numerator);
            $this->view->assign('larc_denominator',$larc_denominator);
            
            $this->view->assign('fp_overtime', $fp_overtime); 
            $this->view->assign('larc_overtime', $larc_overtime); 
           // $this->view->assign('modern_method_overtime',$modern_method_overtime);
            $this->view->assign('latestPullDatemultipleMonthName',$monthNameDisplay);
            $sDate = $helper->fetchTitleDate();
           
            $this->view->assign('date', $sDate['month_name'].' '.$sDate['year']); 

            $overTimeDates = $helper->getPreviousMonthDates(12);
            
            if(!empty($lastPullDatemultiple)){
               // echo 'It is not empty';
             $overTimeDates = $lastPullDatemultiple;   
            }
            $this->view->assign('start_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
            $this->view->assign('end_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 

            $this->viewAssignEscaped('criteria', $this->getLocationCriteria());
            $this->viewAssignEscaped ('locations', Location::getAll(1) );
            
            $this->view->assign('base_url', $this->baseUrl);
        }
        
        
        public function updatecacheAction(){
            echo 'starting updates';
            $coverage = new Coverage();
            $stockout = new Stockout();
            
            $coverage->updateCache();
            $stockout->updateCache();
            
            echo 'done updating';
        }
}

?>