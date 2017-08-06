<?php
/*
 * Created on Feb 11, 2008
 *
 *  Built for web
 *  Fuse IQ -- todd@fuseiq.com
 *  Leke Seweje 
 *  Techie Planet
 *  
 *
 */
ini_set("display_errors", "On");
require_once ('ITechController.php');

require_once ('ReportFilterHelpers.php');
require_once ('models/table/Helper2.php');
//require_once('models/table/Dashboard-CHAI.php');
require_once('models/table/Dashboard.php');

class IndexController extends ReportFilterHelpers  {
        private $helper; // = new Helper2();
        
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		parent::__construct ( $request, $response, $invokeArgs );
                $this->helper = new Helper2();
    	}

		public function init(){
	        $burl = Settings::$COUNTRY_BASE_URL;
	        if (substr($burl, -1) != '/' && substr($burl, -1) != '\\')
	            $this->baseUrl = $burl . '/';
	    }
	
        public function indexoldAction(){
            //fetch consumption by method
            $dashboard = new Dashboard();
            list($geoList, $tierValue) = $this->buildParameters();                
            
            $consumptionbyMethod = $dashboard->fetchConsumptionByMethod();
            $coverageSummary = $dashboard->fetchCoverageSummary($geoList, $tierValue);
            
            $fp_facsProviding = $dashboard->fetchFacsProviding('fp');
            $larc_facsProviding = $dashboard->fetchFacsProviding('larc');
            
            
            //$facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedout($geoList, $tierValue, true);
            $fp_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('fp', $geoList, $tierValue, true);
            $larc_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('larc', $geoList, $tierValue, true);
            
            //var_dump($coverageSummary); exit;
            
            $this->view->assign('consumption_by_method', $consumptionbyMethod);
            $this->view->assign('csummary', $coverageSummary);
            $this->view->assign('fp_facs_providing', $fp_facsProviding);
            $this->view->assign('larc_facs_providing', $larc_facsProviding);
            //$this->view->assign('facs_providing_stockedout', $facsProvidingStockedout);
            $this->view->assign('fp_facs_providing_stockedout', $fp_facsProvidingStockedout);
            $this->view->assign('larc_facs_providing_stockedout', $larc_facsProvidingStockedout);
            
            
            $title_date = $this->helper->fetchTitleDate();
            $this->view->assign('title_date', $title_date['month_name'] . ' ' . $title_date['year']);
            
            $overTimeDates = $this->helper->getPreviousMonthDates(12);
            $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
            $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 
            
            $this->view->assign('base_url', $this->baseUrl);
        }
        
        
        public function indexAction(){
            //fetch consumption by method
            $dashboard = new Dashboard();
            list($geoList, $tierValue) = $this->buildParameters();                
            
            $consumptionbyMethod = $dashboard->fetchConsumptionByMethod();
            $coverageSummary = $dashboard->fetchCoverageSummary($geoList, $tierValue);
            
            $fp_facsProviding = $dashboard->fetchFacsProviding('fp');
            $larc_facsProviding = $dashboard->fetchFacsProviding('larc');
            
            list($fp_numeratorfacsprov,$fp_denominatorfacsprov) = $dashboard->fetchFacsProvidingNumeratorDenominator('fp');
            list($larc_numeratorfacsprov,$larc_denominatorfacsprov) = $dashboard->fetchFacsProvidingNumeratorDenominator('larc');
            
            
            //$facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedout($geoList, $tierValue, true);
            $fp_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('fp', $geoList, $tierValue, true);
            $larc_facsProvidingStockedout = $dashboard->fetchFacsProvidingStockedoutOvertime('larc', $geoList, $tierValue, true);
            
            list($fp_numeratorStockedOut,$fp_denominatorStockedOut) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('fp', $geoList, $tierValue, true);
            list($larc_numeratorStockedOut,$larc_denominatorStockedOut) = $dashboard->fetchFacsProvidingStockedoutOvertimeNumeratorDenominator('larc', $geoList, $tierValue, true);
            
            
            //var_dump($coverageSummary); exit;
            
            $this->view->assign('consumption_by_method', $consumptionbyMethod);
            $this->view->assign('csummary', $coverageSummary);
            $this->view->assign('fp_facs_providing', $fp_facsProviding);
            $this->view->assign('larc_facs_providing', $larc_facsProviding);
            //$this->view->assign('facs_providing_stockedout', $facsProvidingStockedout);
            $this->view->assign('fp_facs_providing_stockedout', $fp_facsProvidingStockedout);
            $this->view->assign('larc_facs_providing_stockedout', $larc_facsProvidingStockedout);
            
            
            
            $this->view->assign('fp_numerator_stockout',$fp_numeratorStockedOut);
            $this->view->assign('fp_denominator_stockout',$fp_denominatorStockedOut);
            
            $this->view->assign('larc_numerator_stockout',$larc_numeratorStockedOut);
            $this->view->assign('larc_denominator_stockout',$larc_denominatorStockedOut);
            
            $this->view->assign('fp_numerator_facsprov',$fp_numeratorfacsprov);
            $this->view->assign('fp_denominator_facsprov',$fp_denominatorfacsprov);
            
            $this->view->assign('larc_numerator_facsprov',$larc_numeratorfacsprov);
            $this->view->assign('larc_denominator_facsprov',$larc_denominatorfacsprov);
            
            
            $title_date = $this->helper->fetchTitleDate();
            $this->view->assign('title_date', $title_date['month_name'] . ' ' . $title_date['year']);
            
            $overTimeDates = $this->helper->getPreviousMonthDates(12);
            $this->view->assign('end_date', date('F', strtotime($overTimeDates[0])). ' '. date('Y', strtotime($overTimeDates[0]))); 
            $this->view->assign('start_date', date('F', strtotime($overTimeDates[11])). ' '. date('Y', strtotime($overTimeDates[11]))); 
            
            $this->view->assign('base_url', $this->baseUrl);
        }
        
        
        public function itestAction(){
            //fetch consumption by method
            $dashboard = new Dashboard();
            
            //fetch covergae summary
            $coverageSummary = $dashboard->fetchCoverageSummary();
            $this->view->assign('csummary', $coverageSummary);
        }


        public function indexAction2() {
	    // enclosing single quotes added later
	    $method = "w92UxLIRNTl','H8A8xQ9gJ5b','ibHR9NQ0bKL','DiXDJRmPwfh','yJSLjbC9Gnr','vDnxlrIQWUo','krVqq8Vk5Kw";
	    $request = $this->getRequest();
	     
	    //$title_method = new DashboardCHAI();
	    //$title_method = $title_method->fetchTitleMethod($method);
	    
	    $helper = new Helper2();
	    $title_date = $helper->fetchTitleDate();
	    
	    $this->view->assign('title_date',  $title_method[commodity_name].', '. $title_date[month_name].' '. $title_date[year]);
	     
	    $cln_data = new DashboardCHAI();
	    $pfp_data = new DashboardCHAI();
	    $pfso_data = new DashboardCHAI();
	    $cs_data = new DashboardCHAI();
	     
            
	    // geo selection includes "--choose--" or no selection
            
            /* TP:
             * Mind the !isset(...) part 
             */
	    if( ( isset($_POST["region_c_id"] ) && $_POST["region_c_id"][0] == "" ) ||
	        ( isset($_POST["district_id"] ) && $_POST["district_id"][0] == "" ) ||
	        ( isset($_POST["province_id"] ) && $_POST["province_id"][0] == "" ) ||
	        (!isset($_POST["region_c_id"] ) && !isset($_POST["district_id"] ) && !isset($_POST["province_id"] ) ) ){
	        
                //get national numbers from refresh
	        $cln_details = $cln_data->fetchDashboardData('national_consumption_by_method');
	        $pfp_details = $pfp_data->fetchDashboardData('national_percent_facilities_providing');
	        $pfso_details = $pfso_data->fetchDashboardData('national_percent_facilities_stock_out');
	        $cs_details = $cs_data->fetchDashboardData('national_coverage_summary');
	    }
	     
	    if(count($cln_details) > 0 && count($pfp_details) > 0 && count($pfso_details) > 0 && isset($cs_details[last_date])) { //got all
                //echo 'inside if stmt'; exit;
	        $this->view->assign('national_consumption_by_method', $cln_details);
	        $this->view->assign('national_percent_facilities_providing', $pfp_details);
	        $this->view->assign('national_percent_facilities_stock_out', $pfso_details);
	        
                $cs_calc = $this->coverageCalculations($cs_details);
                $this->view->assign('cs_fp_facility_count',$cs_calc['cs_fp_facility_count']);
	        $this->view->assign('cs_larc_facility_count',$cs_calc['cs_larc_facility_count']);
	        $this->view->assign('cs_fp_consumption_facility_count',$cs_calc['cs_fp_consumption_facility_count']);
	        $this->view->assign('cs_larc_consumption_facility_count',$cs_calc['cs_larc_consumption_facility_count']);
                $this->view->assign('cs_fp_stock_out_facility_count',$cs_calc['cs_fp_stock_out_facility_count']);
                $this->view->assign('cs_larc_stock_out_facility_count',$cs_calc['cs_larc_stock_out_facility_count']);
                $this->view->assign('cs_date',$cs_calc['cs_date']);
	         
	    } else {
                //echo 'inside else stmt'; exit;
	        $where = ' 1=1 ';
	         
	        if( isset($_POST["region_c_id"]) ){ // CHAINigeria LGA
                    //echo 'inside region_c_id stmt'; exit;
	            $where = $where.' and f.location_id in (';
	            foreach ($_POST['region_c_id'] as $i => $value){
	                $geo = explode('_',$value);
	                $where = $where.$geo[2].', ';
	            }
	            $where = $where.') ';
	            $group = new Zend_Db_Expr('L1_location_name, CNO_external_id');
	            $useName = 'L1_location_name';     
	        
                } else if( isset($_POST['district_id']) ){ // CHAINigeria state
                    //echo 'inside district_id stmt'; exit;
	            $where = $where.' and l2.id in (';
	            foreach ($_POST['district_id'] as $i => $value){
	                $geo = explode('_',$value);
	                $where = $where.$geo[1].', ';
	            }
	            $where = $where.') ';
	            $group = new Zend_Db_Expr('L2_location_name, CNO_external_id');
	            $useName = 'L2_location_name';
	             
	        } else if( isset($_POST['province_id']) ){ //province_id is a Trainsmart internal name, represents hightest CHAINigeria level = GPZ
                    //echo 'inside province_id stmt'; exit;
	            $where = $where.' and l2.parent_id in (';
	            foreach ($_POST['province_id'] as $i => $value){
	                $geo = explode('_',$value);
	                $where = $where.$geo[0].', ';
	            }
	            $where = $where.') ';
	            $group = new Zend_Db_Expr('L3_location_name, CNO_external_id');
	            $useName = 'L3_location_name';
	        } else { // no geo selection
                    //echo 'inside inner else stmt'; exit;
	            $group = 'CNO_external_id';
	            $useName = 'C_date';
	            $location = 'National';
	        }
	    
	        $where = str_replace(', )', ')', $where);
	        $whereClause = new Zend_Db_Expr($where);
	    
	        //$amc_details = $amc_data->fetchAMCDetails($whereClause);
	         
	        //file_put_contents('c:\wamp\logs\php_debug.log', 'dash996Action >'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
	        //var_dump('amc_details= ', $amc_details, 'END');
	        //$toss = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $toss .PHP_EOL, FILE_APPEND | LOCK_EX);
	         
	        // $method is external_id and must be single quoted, likely meant to be int but had to convert table id to external_id
	        if( "'$method'" != '' ) $where = $where . ' and cno.external_id in ( '."'$method'".' )';
	         
                //echo 'about to call CLNDetails'; exit;
                /* TP:
                 * this is for the consumption by method chart
                 */
	        $cln_details = $cln_data->fetchCLNDetails('location', $id, $where, $group, $useName);
	        
	        //any FP
	        //$where = " 1=1 and cno.external_id in ( 'w92UxLIRNTl', 'H8A8xQ9gJ5b', 'ibHR9NQ0bKL', 'DiXDJRmPwfh', 'yJSLjbC9Gnr', 'vDnxlrIQWUo', 'krVqq8Vk5Kw') and c.consumption > 0  ";
                //$where = " 1=1 AND c.consumption > 0";
	        //$pfp_any_details = $pfp_data->fetchPFPDetails(  );
	        
	        //larc
	        //$where = " 1=1 and cno.external_id in ( 'DiXDJRmPwfh', 'yJSLjbC9Gnr') and c.consumption > 0 ";
	        //$pfp_larc_details = $pfp_data->fetchPFPDetails( $where,'', '', 'larctrained' );
                
	        //file_put_contents('c:\wamp\logs\php_debug.log', 'indexAction >'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
	        //var_dump('$pfp_any_details= ', $pfp_any_details, 'END');
	        ////var_dump('$pfp_larc_details= ', $pfp_larc_details, 'END');
	        //$toss = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $toss .PHP_EOL, FILE_APPEND | LOCK_EX);
	        
                //TP: Percent Facilities Providing FP/LARC
                $pfp_details = $pfp_data->fetchPFPDetails();
                //var_dump($pfp_details); exit;
                
                foreach($pfp_details as $i => $row ){
	            //$national_percent_facilities_providing[] = array('month' => $row['month'], 'year' => $row['year'], 'fp_percent' => $row['percent'], 'larc_percent' => $pfp_larc_details[$i]['percent'] );
                    $national_percent_facilities_providing[] = array('month' => $row['month'], 'year' => $row['year'], 'fp_percent' => $row['fp_percent'], 'larc_percent' => $row['larc_percent'] );
	        }
                
//                foreach ($national_percent_facilities_providing as $row){
//                    echo "month: " . $row['month'] . ' ' .
//                         "year: " . $row['year'] . ' ' .
//                         "fp_percent: " . $row['fp_percent'] . ' ' .
//                         "larc_percent: " . $row['larc_percent'] . ' ' .
//                         "percent: " . $row['percent'] . "<br/>";
//                }
//                exit;
                
                
                //TP: STOCK OUTS
	        //$where = " 1=1 and (cno.external_id in ( 'DiXDJRmPwfh') and c.stock_out = 'Y') or  (cno.external_id in ( 'JyiR2cQ6DZT') and c.consumption = 1) ";
	        $pfso_details = $pfso_data->fetchPFSODetails( $where );

	        $total = 0;
	        
	        
	        
	        //file_put_contents('c:\wamp\logs\php_debug.log', 'indexAction >'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
	        //var_dump('$pfso_details= ', $pfso_details, 'END');
	        //var_dump('$method= ', $method, 'END');
	        //var_dump('$national_percent_facilities_providing= ', $national_percent_facilities_providing, 'END');
	        //$toss = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $toss .PHP_EOL, FILE_APPEND | LOCK_EX);
	        
	        foreach($pfso_details as $i => $row ){
	            $national_percent_facilities_stock_out[] = array('month' => $row['month'], 'year' => $row['year'], 'implant_percent' => $row['implant_percent'], 'seven_days_percent' => $row['seven_days_percent'] );
	        }
	        //var_dump($national_percent_facilities_stock_out); exit;
                 
                    
	        foreach($cln_details as $i => $row ){
	             
	            if ( $location != 'National' ) {
	                switch($useName){
	                    case 'L1_location_name' :
	                        $location = $row['L1_location_name'];
	                        break;
	                    case 'L2_location_name' :
	                        $location = $row['L2_location_name'];
	                        break;
	                    case 'L3_location_name' :
	                        $location = $row['L3_location_name'];
	                        break;
	                }
	            }
	             
	            $locationNames = $locationNames ? $locationNames.', '.$location : $locationNames.$location;
	    
	            // remove single quotes and explode method
	            $bad_chars = array("'");
	            $method = str_replace($bad_chars, "", $method);
	            $methods =  array( explode(',', $method) );
	            
	            // lookup commodity_names
	            $title_method = new DashboardCHAI(); 
	            $CNO[] = array ($title_method->fetchTitleMethod($methods[0][0]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][1]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][2]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][3]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][4]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][5]));
                $CNO[] = array ($title_method->fetchTitleMethod($methods[0][6]));
	             
                $national_consumption_by_method[] =array('method' => $CNO[0][0]['commodity_name'], 'consumption' => $row['consumption1'] );
                $national_consumption_by_method[] =array('method' => $CNO[1][0]['commodity_name'], 'consumption' => $row['consumption2'] );
                $national_consumption_by_method[] =array('method' => $CNO[2][0]['commodity_name'], 'consumption' => $row['consumption3'] );
                $national_consumption_by_method[] =array('method' => $CNO[3][0]['commodity_name'], 'consumption' => $row['consumption4'] );
                $national_consumption_by_method[] =array('method' => $CNO[4][0]['commodity_name'], 'consumption' => $row['consumption5'] );
                $national_consumption_by_method[] =array('method' => $CNO[5][0]['commodity_name'], 'consumption' => $row['consumption6'] );
                $national_consumption_by_method[] =array('method' => $CNO[6][0]['commodity_name'], 'consumption' => $row['consumption7'] );

                //file_put_contents('c:\wamp\logs\php_debug.log', 'dash996Action >'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
                //var_dump('$methods= ', $methods, 'END');
                //var_dump('$CNO= ', $CNO, 'END');
                //var_dump('$consumption_by_method= ', $consumption_by_method, 'END');
                //$toss = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $toss .PHP_EOL, FILE_APPEND | LOCK_EX);
                	             
	            $total = $total + $consumption_by_geo[$i]['consumption'];
	             
	        } // foreach cln
	         
	        if (is_null($national_consumption_by_method)) {
	            $consumption_by_geo[] = array('location' => 'No Data', 'consumption' => 0 );
	        }
	         
	        if ($total == 0) {
	            $total_consumption[] = array('location' => 'No Data', 'consumption' => 0 );
	        } else {
	            $total_consumption[] = array('location' => $locationNames, 'consumption' => $total );
	        }
	        
	        
                
                
	        $cs_data = new DashboardCHAI();
	        // specify date by "2014-12-01" or leave empty to get data for the last month
	        $cs_details = $cs_data->fetchCSDetails(null);
                
                $cs_calc = $this->coverageCalculations($cs_details);
                
                $this->view->assign('cs_fp_facility_count',$cs_calc['cs_fp_facility_count']);
	        $this->view->assign('cs_larc_facility_count',$cs_calc['cs_larc_facility_count']);
	        $this->view->assign('cs_fp_consumption_facility_count',$cs_calc['cs_fp_consumption_facility_count']);
	        $this->view->assign('cs_larc_consumption_facility_count',$cs_calc['cs_larc_consumption_facility_count']);
                $this->view->assign('cs_fp_stock_out_facility_count',$cs_calc['cs_fp_stock_out_facility_count']);
                $this->view->assign('cs_larc_stock_out_facility_count',$cs_calc['cs_larc_stock_out_facility_count']);
                $this->view->assign('cs_date',$cs_calc['cs_date']);                
                //var_dump($cs_calc); exit;
                
	        
	        //file_put_contents('c:\wamp\logs\php_debug.log', 'dash9bAction >'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
	        //var_dump('$cs_details= ', $cs_details, 'END');
	        //var_dump('$method= ', $method, 'END');
	        //$toss = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $toss .PHP_EOL, FILE_APPEND | LOCK_EX);
	        	    
	         
	        $this->view->assign('national_consumption_by_method', $national_consumption_by_method);
	        $this->view->assign('national_percent_facilities_providing', $national_percent_facilities_providing);
	        $this->view->assign('national_percent_facilities_stock_out', $national_percent_facilities_stock_out);
	         
	        if ($location == 'National') {
	            $cln_details = $cln_data->insertDashboardData($national_consumption_by_method, 'national_consumption_by_method');
	            $pfp_details = $pfp_data->insertDashboardData($national_percent_facilities_providing, 'national_percent_facilities_providing');
	            $pfso_details = $pfso_data->insertDashboardData($national_percent_facilities_stock_out, 'national_percent_facilities_stock_out');
	            $cs_details = $cs_data->insertDashboardData($cs_details, 'national_coverage_summary');
	        }
	    
	    }  // else
	    
	    $this->viewAssignEscaped ('locations', Location::getAll(1));
	}

        
        private function coverageCalculations($cs_details){
            $cs_calc = array(
                        'cs_fp_facility_count' => round($cs_details['fp_facility_count']/$cs_details['total_facility_count_month'], 2),
                        'cs_larc_facility_count' => round($cs_details['larc_facility_count']/$cs_details['total_facility_count_month'], 2),
                        'cs_fp_consumption_facility_count' => round($cs_details['fp_consumption_facility_count']/$cs_details['fp_facility_count'], 2),
        	        'cs_larc_consumption_facility_count' => round($cs_details['larc_consumption_facility_count']/$cs_details['larc_facility_count'], 2),
                        'cs_fp_stock_out_facility_count' => round($cs_details['fp_stock_out_facility_count']/$cs_details['fp_facility_count'], 2),
                        'cs_larc_stock_out_facility_count' => round($cs_details['larc_stock_out_facility_count']/$cs_details['larc_facility_count'], 2),
                        'cs_date' => date_format(date_create($cs_details['last_date']), 'F Y'),
                    );
            
            return $cs_calc;
        }
        
        
        
        public function testAction() {

	}

	public function languageAction() {
		require_once ('models/Session.php');
		require_once ('models/table/User.php');

		if ($this->isLoggedIn() and array_key_exists ( $this->getSanParam ( 'opt' ), ITechTranslate::getLanguages () )) {
			$user = new User ( );
			$userRow = $user->find ( Session::getCurrentUserId () )->current ();
			$user->updateLocale ( $this->getSanParam ( 'opt' ), Session::getCurrentUserId () );

			$auth = Zend_Auth::getInstance ();
			$identity = $auth->getIdentity ();
			$identity->locale = $this->getSanParam ( 'opt' );
			$auth->getStorage ()->write ( $identity );
			setcookie ( 'locale', $this->getSanParam ( 'opt' ), null, Globals::$BASE_PATH );
		}

		$this->_redirect ( $_SERVER ['HTTP_REFERER'] );

	}

	public function jsAggregateAction() {
		#$headers = apache_request_headers ();

		// Checking if the client is validating his cache and if it is current.
		/*
	    if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) > time() - 60*60*24)) {
	        // Client's cache IS current, so we just respond '304 Not Modified'.
	        header('Last-Modified: '.gmdate('D, d M Y H:i:s',  time()).' GMT', true, 304);
			$this->setNoRenderer();
	    }
		#echo Globals::$BASE_PATH.Globals::$WEB_FOLDER.$file;
		#exit;
		*/

		$response = $this->getResponse ();
		$response->clearHeaders ();

		//allow cache
		#$response->setHeader ( 'Expires', gmdate ( 'D, d M Y H:i:s', time () + 60 * 60 * 30 ) . ' GMT', true );
		#$response->setHeader ( 'Cache-Control', 'max-age=7200, public', true );
		#$response->setHeader ( 'Last-Modified', '', true );
		#$response->setHeader ( 'Cache-Control',  "public, must-revalidate, max-age=".(60*60*24*7), true ); // new ver TS new JS file
		$response->setHeader ( 'Cache-Control',  "must-revalidate, max-age=".(60*60*24*7), true ); // new ver TS new JS file
		#$response->setHeader ( 'Pragma', 'public', true );
		$response->setHeader ( 'Last-Modified',''.date('D, d M Y H:i:s', strtotime('18 March 2013 19:20')).' GMT', true ); // todo update this when thers a new javascript file to force re dl
		$response->setHeader ( 'Content-type', 'application/javascript' ); // should fix inspector warnings (was text/html)

	}

         public function requestaccessAction(){
            require_once ('models/table/User.php');
            require_once ('models/ValidationContainer.php');
            require_once ('Zend/Validate/EmailAddress.php');
            require_once ('Zend/Mail.php');
            require_once ('models/table/MultiOptionList.php');
            require_once ('models/table/Report.php');
            require_once('models/table/Helper.php');
            require_once('models/table/Location.php');
            require_once('models/table/requestUser.php');
                //$auth = Zend_Auth::getInstance ();
                //$read = $auth->getStorage()->read();
                $report = new Report();
                $requestUser = new requestUser();
		$request = $this->getRequest ();
		$validateOnly = $request->isXmlHttpRequest ();
		if ($validateOnly)
		$this->setNoRenderer ();

		$status = ValidationContainer::instance ();
		$req_id = $this->getSanParam('id');
                if(!empty($req_id)){
                    $where = 'req_id ='.$req_id.'';
                    $rawRequestUserData = $requestUser->selectRequestUser('request_access',$where);
                    $user = $rawRequestUserData[0];
                    $this->viewAssignEscaped('user', $user);
                }
                //var_dump($locations); exit;
                
		if ($request->isPost()) {
                    //echo 'post requesttttt'; exit;
                    
                    
                    
                    
                    
                    //validate
                    $status->checkRequired ( $this, 'designation', 'Designation' );
                    $status->checkRequired ($this, 'title', 'Title');
                    $status->checkRequired ( $this, 'first_name', 'First name' );
                    $status->checkRequired ( $this, 'last_name', 'Surname' );
                    $status->checkRequired ( $this, 'username', 'Login' );
                    $status->checkRequired ( $this, 'email', 'Email' );
                    
                    

			//valid email?
			$validator = new Zend_Validate_EmailAddress ( );

			if (!$validator->isValid ( $this->_getParam ( 'email' ) )) {
				$status->addError ( 'email', 'That email address does not appear to be valid.' );
			}

			if (strlen ( $this->_getParam ( 'username' ) ) < 3) {
				$status->addError ( 'username', 'Usernames should be at least 3 characters in length.' );
			}
                        
			$status->checkRequired ( $this, 'password', 'Password' );
                        
                        //confirm password
                        $status->checkUnique($this, 'password', 'password2', 'Confirm Password');
                        
			//check unique username and email
			if ($uniqueArray = User::isUnique ( $this->getSanParam ( 'username' ), $this->_getParam ( 'email' ) )) {
				if (isset ( $uniqueArray ['email'] )){
				$status->addError ( 'email', 'That email address is already in use. Please choose another one.' );
                                }
                                else{
                                    if(!empty($req_id)){
                                        $where = 'email ='.$this->_getParam ( 'email' ).' AND req_id != '.$req_id.'';
                                    }else{
                                        $where = 'email ='.$this->_getParam ( 'email' ).'';
                                    }
                                    
                                    $queryResult = $requestUser->selectRequestUser('request_access',$where);
                                   if(!empty($queryResult)){
                                       $status->addError ( 'email', 'That email address is already in use. Please choose another one.' );
                                   }
                                }
				if (isset ( $uniqueArray ['username'] ))
				$status->addError ( 'username', 'That username is already in use. Please choose another one.' );
			}

			if (strlen ( $this->_getParam ( 'password' ) ) < 6) {
				$status->addError ( 'password', 'Passwords should be at least 6 characters in length.' );
			}
                        
                       /* if($province_id=="" && empty($district_id) && empty($region_c_id)){
                            $status->addError ( 'province_id', 'The Location of the user must be completely filled' );
                        
                            
                        }*/
                         
                        $status->checkRequired ( $this,'province_id', 'Zone' );
                        $status->checkRequired ( $this,'district_id', 'State' );
                        $status->checkRequired ( $this,'region_c_id', 'LGA' );
                         
                       
                        
			if ($status->hasError ()) {
				$status->setStatusMessage ( 'The user could not be saved.' );
			} else {
                            
                            $designation = $this->getSanParam('designation');
                            $province_id = array();
                            $province_id = $this->getSanParam('province_id');
                            $province_id = $report->formatSelection($province_id);
                            $zone = $report->explodeGeogArray($province_id,"1");
                            $geo_zone = $zone[0];
                            
                            
                            $district_id = array();
                            $district_id = $this->getSanParam('district_id');
                            $district_id = $report->formatSelection($district_id);
                            $state = $report->explodeGeogArray($district_id, "2");
                            $geo_state = $state[0];
                            $multipleLocation = "";
                            if($designation=="partner_user"){
                                $multipleLocation = json_encode($district_id);
                            }
                            

                            $region_c_id = array();
                            $region_c_id = $this->getSanParam('region_c_id');
                            $region_c_id = $report->formatSelection($region_c_id);
                            $localgovernment = $report->explodeGeogArray($region_c_id, "3");
                            $geo_lga = $localgovernment[0];
                            $currDate = new Zend_Db_Expr('CURDATE()');
                    
                    
                            $data['first_name'] = $this->getSanParam('first_name');
                            $data['last_name'] = $this->getSanParam('last_name');
                            $data['title'] = $this->getSanParam('title');
                            $data['username'] = $this->getSanParam('username');
                            $data['password'] = $this->getSanParam('password');
                            $data['email'] = $this->getSanParam('email');
                            $data['mobile_number'] = $this->getSanParam('mobile_no');
                            $data['province_id'] = $geo_zone;
                            $data['district_id'] = $geo_state;
                            $data['region_c_id'] = $geo_lga;
                            $data['multiple_locations_id'] = $multipleLocation;
                            $data['designation'] = $designation;
                            $data['status'] = 0;
                            
                            if($this->getSanParam('designation') == "partner_user")
                                    $data['multiple_locations_id'] = json_encode(array_merge ($province_id, $district_id));
                            else {
                                    $data['multiple_locations_id'] = " ";
                                 }
                            
                           if(!empty($req_id)){
                           $data['timestamp_modified'] = $currDate;
                           }else {
                           $data['timestamp_created'] = $currDate;
                           }
                         //print_r($details);exit;
                           //print_r($userRow);exit;
                            //echo $zone.' state '.$details.' lga '.$lga;exit;
                             //print_r($this->_getAllParams ());exit;
				

//					$view = new Zend_View ( );
//					$view->setScriptPath ( Globals::$BASE_PATH . '/app/views/scripts/email' );
//					$view->assign ( 'first_name', $this->_getParam ( 'first_name' ) );
//					$view->assign ( 'username', $this->_getParam ( 'username' ) );
//					$view->assign ( 'password', $this->_getParam ( 'password' ) );
//					$text = $view->render ( 'text/new_account.phtml' );
//					$html = $view->render ( 'html/new_account.phtml' );
//
//					try {
//						$mail = new Zend_Mail ( );
//						$mail->setBodyText ( $text );
//						$mail->setBodyHtml ( $html );
//						$mail->setFrom ( Settings::$EMAIL_ADDRESS, Settings::$EMAIL_NAME );
//						$mail->addTo ( $this->_getParam ( 'email' ), $this->_getParam ( 'first_name' ) . " " . $this->_getParam ( 'last_name' ) );
//						$mail->setSubject ( 'New Account Created' );
//						$mail->send ();
//					} catch (Exception $e) {
//
//					}
                            
                             $last_name = $this->getSanParam('last_name');
                             $first_name = $this->getSanParam('first_name');
                             $title = $this->getSanParam('title');
				
                             if(!empty($req_id)){
                                 $where = 'req_id = '.$req_id.'';
                                 $id = $requestUser->updateRequestUser("request_access", $data, $where);
                             }else{
                                 $id = $requestUser->insertRequestUser("request_access", $data);
                             }
                             
				if (!empty($id)) {
					$status->setStatusMessage ( 'Access request sent' );
                                        $this->sendRequestAccessMailUser($this->getSanParam('email'), $first_name,$last_name,$title);
                                        $this->sendRequestAccessMailAdmin();
					
				} else {
					$status->setStatusMessage ( 'The user could not be saved.' );
				}

			}
		}

                $locations = Location::getAll("1");
                $this->viewAssignEscaped('locations', $locations);
        
                        $data = array();
			$data['status'] = $status;
                        $jsonData = json_encode($data);
                           if ($validateOnly){
                        echo $jsonData;
                        }
               
                
        }
        
        
         public function sendRequestAccessMailUser($to, $first_name,$last_name,$title)
        {
            require_once ('Zend/Mail.php');
            
            $view = new Zend_View ( );
            $view->setScriptPath ( Globals::$BASE_PATH . '/app/views/scripts/email' );
            $view->assign ( 'first_name', $first_name);
            $view->assign ( 'last_name', $last_name);
            $view->assign ( 'title', $title);
            $text = $view->render ( 'text/new_account_request.phtml' );
            $html = $view->render ( 'html/new_account_request.phtml' );

            try {
                    $mail = new Zend_Mail ( );
                    $mail->setBodyText ( $text );
                    $mail->setBodyHtml ( $html );
                    $mail->setFrom ( 'support@fpdashboard.ng', 'FP DashBoard Nigeria' );
                    $mail->addTo ( $to);
                    $mail->setSubject ( 'Confirmation of user registration in process for Family Planning Dashboard' );
                    $mail->send ();
            } catch (Exception $e) {

            }
        }
        
        public function sendRequestAccessMailAdmin()
        {
            require_once ('Zend/Mail.php');
            
            $view = new Zend_View ( );
            $view->setScriptPath ( Globals::$BASE_PATH . '/app/views/scripts/email' );
            $text = $view->render ( 'text/new_account_request_admin.phtml' );
            $html = $view->render ( 'html/new_account_request_admin.phtml' );

            try {
                    $mail = new Zend_Mail ( );
                    $mail->setBodyText ( $text );
                    $mail->setBodyHtml ( $html );
                    $mail->setFrom ( 'support@fpdashboard.ng', 'FP DashBoard Nigeria - User Request' );
                    //$mail->addTo ( 'support@fpdashboard.ng', 'FP DashBoard Nigeria' );
                    $mail->addTo ( 'support@fpdashboard.ng', 'FP DashBoard Nigeria' );
                    $mail->setSubject ( 'Notification of new user registration request for Family Planning Dashboard' );
                    $mail->send ();
            } catch (Exception $e) {

            }
        }
        
        

	

	
}
?>