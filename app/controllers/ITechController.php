<?php
/*
 * Created on Feb 27, 2008
 *
 *  Built for web
 *  Fuse IQ -- todd@fuseiq.com
 *
 */
require_once('Zend/View.php');
require_once('Zend/Auth.php');
require_once('models/ValidationContainer.php');
require_once('models/table/Translation.php');
require_once('models/table/System.php');
require_once('views/helpers/ITechTranslate.php');
require_once('models/table/Location.php');
require_once('models/analytics/MetricClient.php');

class ITechController extends Zend_Controller_Action
{
    private $_sanitizeChain = null;
        static protected $_translations = null;
        public $_countrySettings = null;
    
    const LOADINGMESSAGE = 'Fetching Data';
    const POPULATINGMESSAGE = 'Populating';
    
    public function init(){
        return;
        if (!$this->isLoggedIn ()) return;
        
        $chartsArray = array('CoverageController','ConsumptionController', 'StockoutController');
        $dataCollectionArray = array('TrainingController', 'PersonController', 'FacilityController');
        $reportsArray = array('ReportsController', 'PdfController');
        $usersArray = array('UserController');
        
        $moduleName = '';
        $controllerFullName = ucfirst($this->getRequest()->getControllerName()) . 'Controller';
        if(in_array($controllerFullName, $chartsArray)) $moduleName = 'charts';
        if(in_array($controllerFullName, $dataCollectionArray)) $moduleName = 'dc';
        if(in_array($controllerFullName, $reportsArray)) $moduleName = 'reports';
        
        if( !isset($_POST["region_c_id"]) && !isset($_POST["district_id"]) && !isset($_POST["province_id"]) ){ //visit
            $pageId = $moduleName . "_" .
                      $this->getRequest()->getControllerName() . "_" . 
                      $this->getRequest()->getActionName();

            $actionMethods = array();
            $methods = get_class_methods($controllerFullName);
           // var_dump($methods); exit;
            foreach($methods as $method)
                if(preg_match("/^[A-Za-z]+[0-9]*[A-Za-z]*Action$/", $method))
                        $actionMethods[] = strtolower($method);
                
            $actionFullName = $this->getRequest()->getActionName().'action';
            if(!in_array($actionFullName, $actionMethods)) 
                    return;

            $pageUrl = $this->getRequest()->getRequestUri(); 
            $userId = Zend_Auth::getInstance()->getIdentity()->id;
            $metric = new MetricClient(); 
            $metric->handleVisitMetrics($moduleName, $pageId, $userId);
        }
        else{ //search
            $pageId = $moduleName . "_" .
                      $this->getRequest()->getControllerName() . "_" . 
                      $this->getRequest()->getActionName();
            
            $actionMethods = array();
            $methods = get_class_methods($controllerFullName); 
            //var_dump($methods); exit;
            foreach($methods as $method)
                if(preg_match("/^[A-Za-z]+[0-9]*[A-Za-z]*Action$/", $method))
                        $actionMethods[] = strtolower($method);
                
            $actionFullName = $this->getRequest()->getActionName().'action';
            if(!in_array($actionFullName, $actionMethods)) 
                    return;
            
            $pageUrl = $this->getRequest()->getRequestUri(); 
            $userId = Zend_Auth::getInstance()->getIdentity()->id;
    
            //set geozone, state and lga indices in POST array
            $this->sortSearchLocations();
            
            $searchDetails = $_POST;
            $metric = new MetricClient(); 
            $metric->handleSeacrhMetrics($moduleName, $pageId, $userId, $searchDetails);
        }
    }
    
    
    
    /*TP:
         * This method will operate on the post variable from form and 
         * extract the lowest level location IDs. 
         * For example if LGA is selected, it will operate on the select box value of the format zone_state_lga 
         * And produce only the lga part.
         * This method is used to extract the location values for the search metrics recording
         * Region --> LGA
         * District --> State
         * Province --> Geo Zone
         */
        public function sortSearchLocations(){
            $selectionLimit = 6;
            $geoList=array();
            $tierValue = 0;
            $helper = new Helper2();
            
            if( isset($_POST["region_c_id"]) && 
                (count($_POST["region_c_id"])>1 ||
                (count($_POST["region_c_id"])==1 AND !empty($_POST["region_c_id"][0]))))
            { // CHAINigeria LGA: TP changed this if statement to be more robust
                //if(count($_POST['region_c_id']) > $selectionLimit) 
                    //$_POST['region_c_id'] = array_slice ($_POST['region_c_id'], 0, $selectionLimit);
              // echo 'Region id : '; var_dump($_POST['region_c_id']); exit;
                
                if(!is_array($_POST['region_c_id']))
                {
                    $value = $_POST['region_c_id'];
                    if($value != '') 
                    {
                        $geo = explode('_',$value);
                        $geoList[] = $geo[2];
                    }
                }
                else {
                    foreach ($_POST['region_c_id'] as $i => $value){
                        if($value == '') continue;
                        $geo = explode('_',$value);
                        $geoList[] = $geo[2];
                    }
                }

                //$geoList = substr(trim($geoList), 0, -1);  //remove trailing comma
                //$tierValue = 3;
                //$tierText = 'lga';
                $_POST['lga'] = $geoList;

            } else if( isset($_POST["district_id"]) && 
                (count($_POST["district_id"])>1 || 
                (count($_POST["district_id"])==1 AND !empty($_POST["district_id"][0]))))
            {
                //if(count($_POST['district_id']) > $selectionLimit) 
                    //$_POST['district_id'] = array_slice ($_POST['district_id'], 0, $selectionLimit);
                if(!is_array($_POST['district_id']))
                {
                    $value = $_POST['district_id'];
                    if($value != '') 
                    {
                        $geo = explode('_',$value);
                        $geoList[] = $geo[2];
                    }
                }
                else{
                    foreach ($_POST['district_id'] as $i => $value){
                        if($value == '') continue;
                        $geo = explode('_',$value);
                        $geoList[] = $geo[1];
                    }
                }

                //$geoList = substr(trim($geoList), 0, -1);  //remove trailing comma
                //$tierValue = 2;
                //$tierText = 'state';
                $_POST['state'] = $geoList;
                
            } else if( isset($_POST["province_id"]) && 
                (count($_POST["province_id"])>1 || 
                (count($_POST["province_id"])==1 AND !empty($_POST["province_id"][0]))))
            {
                //if(count($_POST['province_id']) > $selectionLimit) 
                    //$_POST['province_id'] = array_slice ($_POST['province_id'], 0, $selectionLimit);

                //$where .= 'AND flv.geo_parent_id IN (';
                
                if(!is_array($_POST['province_id']))
                {
                    $value = $_POST['province_id'];
                    if($value != '') 
                    {
                        $geo = explode('_',$value);
                        $geoList[] = $geo[2];
                    }
                }
                else{
                    foreach ($_POST['province_id'] as $i => $value){
                        if($value == '') continue;
                        $geo = explode('_',$value);
                        $geoList[] = $geo[0];
                    }
                }

                //$geoList = substr(trim($geoList), 0, -1);  //remove trailing comma
                //$tierValue = 1;
                //$tierText = 'geozone';
                $_POST['geozone'] = $geoList;
            }
//            else { //no geo selection
//                $tierValue = 1;
//                $geoIDsArray = $helper->getLocationTierIDs($tierValue);
//                foreach($geoIDsArray as $key=>$geoid)
//                    $geoIDsArray[$key] = "'$geoid'";
//
//                //var_dump($geoIDsArray); exit;
//                $geoList = implode(',', $geoIDsArray);
//            }
            
            //return array($geoList, $tierText);
      }
      
      
	protected function dbfunc()
	{
		return Zend_Db_Table_Abstract::getDefaultAdapter();
	}

    public static function translations() {
    	return self::$_translations;
    }
    /** __construct
     *
     * create the filters needed for this controller.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param Array $invokeArgs
     *
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
		//$renderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		//$view = new ITechView(array('basePath' => Globals::$BASE_PATH.'/app/views'));
		//$renderer->setView($view);

        parent::__construct($request, $response, $invokeArgs);

        //not sure if we need this stuff
    	require_once('Zend/Filter/Digits.php');
   		require_once('Zend/Filter/Alpha.php');
        $this->digitsFilter = new Zend_Filter_Digits(false); //no whitespace
        $this->alphaFilter  = new Zend_Filter_Alpha(false); //no whitespace

        //Zend_Json::$useBuiltinEncoderDecoder = false;

         //set default template variables
	    $this->view->assign('base_url',Settings::$COUNTRY_BASE_URL);
	    $this->view->setHelperPath(Globals::$BASE_PATH.'/app/views/helpers');


		// get Country-specific settings
		try {

		  $this->_countrySettings = array();
		  $this->_countrySettings = System::getAll();
      $systemSettings = System::getAll();
      //var_dump($systemSettings);exit;
      //$this->mmplog('From inside ITC: ' . print_r($this->_countrySettings,true));


       // $this->_countrySettings['num_location_tiers'] = 2 //including city
        // + $this->_countrySettings['display_region_b']
        // + $this->_countrySettings['display_region_c']
        // + $this->_countrySettings['display_region_d']
        // + $this->_countrySettings['display_region_e']
        // + $this->_countrySettings['display_region_f']
        // + $this->_countrySettings['display_region_g']
        // + $this->_countrySettings['display_region_h']
        // + $this->_countrySettings['display_region_i'];

      /*
        TP commenting the above bcos the code seemed to work
        only when System::getAll() was found only to return value
        if the returned values is assigned to a scalar variable
      */
		  $systemSettings['num_location_tiers'] = 2 //including city
        + $systemSettings['display_region_b']
        + $systemSettings['display_region_c']
        + $systemSettings['display_region_d']
        + $systemSettings['display_region_e']
        + $systemSettings['display_region_f']
        + $systemSettings['display_region_g']
        + $systemSettings['display_region_h']
        + $systemSettings['display_region_i'];


       

	    $this->view->assign('setting', $systemSettings);
	    $this->view->assign('languages', ITechTranslate::getLanguages());
	    $this->view->assign('languages_enabled', ITechTranslate::getLocaleEnabled());

		} catch (exception $e) {

      throw new Exception('Could not connect to a database associated with this country. Please double check that you have the correct URL and that the site is configured correctly.');

		}


# TRY loop is not returning values on system::getall()
# Adding settings outside loop
# CDL, 5.25.2012
		$sys = System::getAll();
		foreach ($sys as $key=>$val){
			$this->_countrySettings[$key] = $val;
		}
    $this->_countrySettings['num_location_tiers'] = 2 //including city
      + $this->_countrySettings['display_region_b']
      + $this->_countrySettings['display_region_c']
      + $this->_countrySettings['display_region_d']
      + $this->_countrySettings['display_region_e']
      + $this->_countrySettings['display_region_f']
      + $this->_countrySettings['display_region_g']
      + $this->_countrySettings['display_region_h']
      + $this->_countrySettings['display_region_i'];

		$response->setHeader('Content-Type', 'text/html; charset=utf-8', true);

    }

    /**
     * String replacement (non-translated)
     *
     * @param unknown_type $keyPhrase
     * @return unknown
     */
     //cached translations per request
  protected function tr($keyPhrase) {
  	$translations = self::translations();
  	if ( !isset($translations[$keyPhrase]) ) return $keyPhrase;
  	return str_replace("'",'\'',($translations[$keyPhrase]));
  }

  //cached settings per request
  protected function setting($settingKey) {
  	return $this->_countrySettings[$settingKey];
  }

	protected function setNoRenderer() {
     	Zend_Controller_Front::getInstance()->setParam('noViewRenderer',1);
 	}

   /** send
     *
     * Takes the output processor and generates the proper output. Hands the
     * output off to the response object for processing.
     *
     */
    protected function send(Output_Abstract $processor)
    {
        /*
         * Output
         */
        $response = $this->getResponse();
        /*
         * Each processor can have multiple headers that need to be set upon
         * output. This way we get all of them.
         */
        foreach($processor->getHeaders() as $header=>$value) {

             $response->setHeader($header,$value);
        } // foreach($this->processor->getHeaders() as $header)
         $response->appendBody($processor->getPayload());
         Helper2::jLog("This is the response that is being sent ".print_r($response,true));
        return;
    } // protected function send($payload, $type)


    /**
     * getProcessorClass
     *
     * figures out which class to use as the processor and hands back the
     * class name to instantiate.
     *
     *
     */
    protected function getProcessorClass($output)
    {
        $output = ucfirst(strtolower($output));
        $outputType   = 'Output_'.$output;
        $fileName     = str_replace('_', DIRECTORY_SEPARATOR, $outputType) . '.php';
        $fullFileName = null;

        foreach(explode(PATH_SEPARATOR,ini_get('include_path'))as $dirToCheck) {
            if (is_null($fullFileName) and
                file_exists($dirToCheck.DIRECTORY_SEPARATOR.$fileName)) {
                $fullFileName = $dirToCheck.DIRECTORY_SEPARATOR.$fileName;
            }
        }

        if (is_null($fullFileName)) {
            throw new Exception($fileName.' is not a valid Output processor.');
        }

        require_once $fullFileName;

        return $outputType;
    } // protected function getProcessorClass($output)

  /**
  * Converts or returns header labels. Since the export CSV must use header
  * labels instead of database fields, define headers here.
  *
  * @param $fieldname = database field name to convert
  * @param $rowRay = will add CSV headers to array
  *
  * @todo modify all report phtml files to use these headers
  */
  public function reportHeaders($fieldname = false, $rowRay = false) {

    require_once ('models/table/Translation.php');
    $translation = Translation::getAll ();

    #$headers = array (// fieldname => label
    # 'id' => 'ID','pcnt' => 'Participants'
    # );
    // action => array(field => label)
    #$headersSpecific = array ('peopleByFacility' => array ('qualification_phrase' => t ( 'Qualification' ) ), 'participantsByCategory' => array ('cnt' => t ( 'Participants' ), 'person_cnt' => t ( 'Unique participants' ) ) );

    if ($rowRay) {
      $keys = array_keys ( reset ( $rowRay ) );
      foreach ( $keys as $k ) {
        $csvheaders [] = $this->reportHeaders ( $k );
      }

      return array_merge ( array ('csvheaders' => $csvheaders ), $rowRay );

    } elseif ($fieldname) {

      // check report specific headers first
      $action = $this->getRequest ()->getActionName ();
      if (isset ( $headersSpecific [$action] ) && isset ( $headersSpecific [$action] [$fieldname] )) {
        return $headersSpecific [$action] [$fieldname];
      }

      return (isset ( $headers [$fieldname] )) ? $headers [$fieldname] : $fieldname;
    } else {
      return $headers;
    }

  }

protected function sendData($data) {
	$this->setNoRenderer();

	if ( !$data )
		return false;

	$outputType     = $this->alphaFilter->filter($this->_getParam('outputType'));
	$processorClass = $this->getProcessorClass($outputType);

	$processor      = new $processorClass($data);
        Helper2::jLog("This is the processor class ".print_r($processorClass,true));
	$this->send($processor);
	return true;

}


    /**
     * remove HTML from text
     */
    public function sanitize($value)
    {
    	require_once('Zend/Filter/StripTags.php');
    	require_once('Zend/Filter/StringTrim.php');

		//don't sanitize arrays
		if ( is_array($value) )
			return $value;

            if (!$this->_sanitizeChain instanceof Zend_Filter) {
                $this->_sanitizeChain = new Zend_Filter();
                $this->_sanitizeChain->addFilter(new Zend_Filter_StringTrim())
                                     ->addFilter(new Zend_Filter_StripTags());
            }

            // filter out any line feeds / carriage returns
            $ret = preg_replace('/[\r\n]+/', ' ', $value);

            // filter using the above chain
            return $this->_sanitizeChain->filter($ret);
    }

	/**
	 * return a sanitized parameter
	 */
	public function getSanParam($param) {

		return $this->sanitize($this->_getParam($param));
	}

    public function preDispatch()
    {
    	require_once('models/table/User.php');

        	//add identity to view variables
        	$auth = Zend_Auth::getInstance();
        	$identity = null;
        	if ($auth->hasIdentity() ) {
        		//get ACLs and add to identity
        		$acls = User::getACLs($auth->getIdentity()->id);
        		$identity = $auth->getIdentity();
        		$identity->acls = $acls;
	            $auth->getStorage()->write($identity);
        		$this->view->assign('identity',$identity);

  	}

   	//set up localization
   	//get country default locale, then check user settings
   	if ( isset($_COOKIE['locale']) and array_key_exists($_COOKIE['locale'], ITechTranslate::getLanguages()))
   		$locale = $_COOKIE['locale'];
   	else
   		$locale = $this->_countrySettings['locale'];

	if ( !$locale )
		$locale = 'en_EN.UTF-8';

   	if ( $auth->hasIdentity() and $auth->getIdentity()->locale )
   		$locale = $auth->getIdentity()->locale;

  		//set up localization
		ITechTranslate::init($locale);

		// get Country-specific phrases for fields
	  self::$_translations = Translation::getAll();
		$this->view->assign('translation', self::translations());


		//look for any status messages in the session and put the validation container in the view scope
  		$statusObj = ValidationContainer::instance();
 		if ( isset($_SESSION['status']) ) {
 			$statusObj->setStatusMessage($_SESSION['status']);
 			unset($_SESSION['status']);
		}
		$this->view->assign('status',$statusObj);
     }

    public function _getACLs() {
        $auth = Zend_Auth::getInstance();
        if ( $auth and $auth->getIdentity() ) {
        	return $auth->getIdentity()->acls;
        }

        return null;
    }

    public function hasACL($level) {
    	$acls = $this->_getACLs();
    	if ( $acls and (array_search( $level, $acls) !== false) ) {
    		return true;
    	}

    	return false;
    }

    public function isLoggedIn() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() )
        	return $auth->getIdentity()->id;

        return false;
    }

    public function doNoAccessError() {
    	$status = ValidationContainer::instance();
  		$status->setStatusMessage(t('You do not have access rights to the requested action.'));

  		if ( !$this->isLoggedIn() ) {
  			$this->_redirect('user/login/?redirect='.urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']));
  		}

  		$this->_redirect('index');
    }

    /**
     * cheezy way of handling exceptions for all actions
     */
    public function dispatch($action)
    {
    	parent::dispatch($action);
    	return;
        try {
         	parent::dispatch($action);


 	  	} catch (exception $e) {
			ob_start();
			var_export($e);
			error_log(ob_get_clean());
     	}

    }

	/**
	 * Fill a data table row from a post array
	 * If a key exists in both the post array and the data table then it will set the row value from the array
	 */
   static public function fillFromArray($dataRow, $postData) {

   	foreach($postData as $key => $val) {
   		if ( isset($dataRow->$key) ) {
   			$dataRow->$key = $val;
   		}
   	}

   	return $dataRow;
   }

   /**
    * Return an array of sanitized data, post and get.
    *
    * do not use this on post or get variables with names: action,controller or module
    */
	public function getAllParams() {
  	// this might not work on arrays with arrays in them, TODO, might be a security flaw, sanitize() wont handle arrays we should array_walk_recursive here. (but will probably just say: ARRAY)
		$ret = array_merge($_GET,$_POST,$this->getRequest()->getParams());
		foreach ($ret as $key => $value) {
			$ret[$key] = $this->getSanParam($key);
		}

		return $ret;
	}

   /**
    * Putting this here since we can't get the Zend function to work correctly
    * $path is from the base path beginning with the action, such as 'user/login'
    */
   protected function _redirect($url, array $options = array()) {
  		$msg = ValidationContainer::instance()->status;
 		if ( $msg ) {
 			$_SESSION['status'] = $msg;
 		}

   	  if ( strstr($url, 'http://') !== false )
   	  	header('Location: '.$url);
   	  else
   	  	header('Location: '.Settings::$COUNTRY_BASE_URL.'/'.$url);
   	  exit();
   }

    public function viewAssignEscaped($spec, $value) {

 		if ( is_string($value) ) {
 			$value = $this->view->escape($value);
 		}

 		//just 2 dim array
 		if ( is_array($value) ) {
 			foreach($value as $key=>$val) {
 				if ( is_string($val) ) {
 					$value[$key] = $this->view->escape($val);
 				} else if ( is_array($val) ) {
 					foreach($val as $key2 =>$val2) {
 						if ( is_string($val2) )
 							$value[$key][$key2] = $this->view->escape($val2);
 					}
 				}

 			}
 		}


 		return $this->view->assign($spec,$value);
 	}
 	
 	/*
 	 * TA:17: 10/08/2014
 	 */
 	protected function _excel_parser($filepath,$sheet=2){
 		//http://www.phpkode.com/scripts/item/simple-xlsx/
 		require_once "libs/simplexlsx.class.php";
 		$xlsx = new SimpleXLSX( $filepath);
               return $xlsx->rows($sheet);
 		//return $xlsx->rows(); //take all rows
 	}

  protected function _csv_get_row($filepath, $reset = FALSE) {
    ini_set('auto_detect_line_endings',true);

    if ($filepath == '') {
      $this->_csvHandle = null;
      return FALSE;
    }

    if (!$this->_csvHandle || $reset) {
      if ($this->_csvHandle) {
        fclose($this->_csvHandle);
      }
      $this->_csvHandle = fopen($filepath, 'r');
    }


   // print_r(fgetcsv($this->_csvHandle, 10000, ','));
    return fgetcsv($this->_csvHandle, 50000, ',');
  }

  /**
   * string or comma seperated list to array
   */
  protected function _array_me(&$var)
  {
    if ( is_array($var) )
      return $var;

    $comma = strpos($var, ',');
    if ($comma)
      return explode(',', $var);

    #else
    return array($var);
  }

  /**
   * map array to hash
   */
  protected function _map_me(&$var, $keyCol = 0, $valCol = 1)
  {
    $output = array();
    foreach ($var as $row) {
      $output[$row[$keyCol]] = $row[$valCol]; // logic: $output[$rowid] = $row['phrase']
    }
    return $output;
  }

  /**
   *  lazy date parsing
   *  accepts (euro: d-m-y, us: m/d/y, sql: yyyy-mm-dd)
   *  returns yyyy-mm-dd 
   */
  protected function _date_to_sql ($val)
  {
      $val = trim($val);

      if (preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
        return $val;
      }
      if (preg_match('/^\d+-\d+-\d+/', $val)) { //D-M-Y
        list($day, $month, $year) = explode('-', $val);
        if (checkdate($month, $day, $year))
          return date ('Y-m-d', mktime (0, 0, 0, $month, $day, $year));
      }
      if (preg_match('/^\d+\/\d+\/\d+/', $val)) { //M/D/Y
        list($month, $day, $year) = explode('/', $val);
        if (checkdate($month, $day, $year))
          return date ('Y-m-d', mktime (0, 0, 0, $month, $day, $year));
      }

      return $val;
  }

  /**
   * lazy date parsing *d-m-y only version*
   * accepts (euro: d-m-y, euro: d/m/y, sql: yyyy-mm-dd)
   * returns yyyy-mm-dd
   */ 
  protected function _euro_date_to_sql ($val)
  {
      $val = trim($val);

      if (preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
        return $val;
      }
      if (preg_match('/^\d+-\d+-\d+/', $val)) { //M-Y-D
        list($day, $month, $year) = explode('-', $val);
        if (checkdate($month, $day, $year))
          return date ('Y-m-d', mktime (0, 0, 0, $month, $day, $year));
      }
      if (preg_match('/^\d+\/\d+\/\d+/', $val)) { //M/D/Y
        list($day, $month, $year) = explode('/', $val);
        if (checkdate($month, $day, $year))
          return date ('Y-m-d', mktime (0, 0, 0, $month, $day, $year));
      }

      return $val;
  }

  protected function _money_to_int ($str)
  {
    return ereg_replace("[^0-9]", "", $str);
  }

  /**
   * is an array array empty
   * hack to work with getSanParam on select_name[] fields, if empty the array will contain multiple ""
   */
  protected function _is_empty_input_array($arr)
  {
    if (!is_array($arr))
      return $arr;

    if (!count($arr))
      return true;

    $foundSomething = false;
    foreach ($arr as $row)
      if ($row != '')
        $foundSomething = true;

    return ! $foundSomething;
  }

  /**
   * array_empty_string_to_zero
   * hack to work with getSanParam on select_name[] fields, if empty the array will contain multiple ""
   * sets "" in arrays to 0. removes dupes.
   */
  protected function _array_empty_to_zero($arr)
  {
    if (!is_array($arr))
      return $arr;

    if (!count($arr))
      return true;

    foreach ($arr as $i => $row)
      if ($row == '')
        $arr[$i] = "0";

    $arr = array_unique($arr);
    return count($arr) ? $arr : array();
  }

  /**
   * _array_no_empties
   * remove all empty strings from an input array
   */ 
  protected function _array_no_empties($arr)
  {
    if (!is_array($arr))
      return $arr;
    for ($cnt=count($arr); $i > 0 ; --$i) { 
      if ( ! trim($arr[$i] ))
        unset($arr[$i]);
    }
    return $arr;
  }

  /**
   *  return either just a number or comma seperated list if $val is array or not, also strips empty "" array values from implode
   */
  protected function _sql_implode($val)
  {
    return (is_array($val) ? implode(',', $this->_array_no_empties( $val )) : $val);
  }

  /**
   * return either just a number or comma seperated list if $val is array or not
   * used to support: array <select> inputs, whether they are ints or 1_2_34 values
   * !!! not used.  - an attempt at combining, pop_all (if necessary), has_real_values(), empty "" values to 0, and array_unique in one function. works good.
   */
  protected function _trainsmart_implode($arr)
  {
    $arr = $this->_pop_all($arr);                         // get last id eg, 1_2_34 returns 34
    if ( ! $this->_is_empty_input_array( $arr ) ) {       // will skip a value of 0, because this is usually used for all, we dont want to do any SQL where queries with that

      $arr = $this->_array_empty_to_zero( $arr );         // these multiple select lists have "", which we will convert to 0 to show rows with nothing set for that sql column
      return (is_array($arr) ? implode(',', $arr) : $arr);
    
    } else {
      // empty array
      return $arr;
    }
  }

  /**
   * user has passed an 123_55_89  style value or array of these values
   * return value: only the last value after the last _ 
   */
 
  protected function _pop_all($arr)
  {
    if (!$arr && $arr !== '0')
      return null;
    if (is_array($arr)) {
      foreach ($arr as $i => $val) {
        if (strpos($val, '_') !== false){
            $temp1 = explode('_', $val);
          $arr[$i] = array_pop($temp1);
        }
        
      }
      return $arr;
    } else {
        $temp = explode('_', $arr);
      return strpos($arr, '_') !== false ? array_pop($temp) : $arr;
    }
  }

  /** 
   * create option phrase and return option ID
   */
  protected function _importHelperFindOrCreate($table, $col, $val)
  {
  	require_once('models/table/MultiOptionList.php');

	$val = $this->sanitize($val);
  	if(!is_array($val))
		$val = trim($val); // todo check acls
	if(empty($val)) return '';

	$tableCustom = new ITechTable ( array ('name' => $table ) );
	$row = $tableCustom->fetchRow($tableCustom->select()->where( "$col = ?", $val ));
	if (! $row) {
		$row = $tableCustom->createRow();
		$row->{$col} = $val;
		if(isset($row->is_default)) $row->is_default = 0;
		$option_id = $row->save();
	}else{
		$option_id = $row->id;
	}

	if($option_id)
		return $option_id;

  }

  /**
   * create option phrase and return option ID
   * second 2 to last params only required if multi option table to save option ids to training
   * last param is extra data, ie # days for pepfar trainings, funding amount for funding
   */
  protected function _importHelperFindOrCreateMOLT($table, $col, $val, $multiAssignTable = null, $training_id = null, $extra = null)
  {
  	require_once('models/table/MultiOptionList.php');

	$val = $this->sanitize($val); // todo check acls
	if(empty($val)) return true;

	$option_id = '';
	$results = array();

	if (! is_array($val) )
		$val = array($val); // handle single values (string input) too

	foreach ($val as $key => $value) {
		$option_id = '';
		$tableCustom = new ITechTable ( array ('name' => $table ) );
		$value = trim($value);
		$row = $tableCustom->fetchRow($tableCustom->select()->where( "$col = ?", $value ));
		if (! $row) {
			$row = $tableCustom->createRow();
			$row->{$col} = $value;
			if(isset($row->is_default)) $row->is_default = 0; //unset on import, we prob dont want this.
			$option_id = $row->save();
		}else{
			$option_id = $row->id;
		}
		$results[] = $option_id;
	}

	if (! $training_id)
		return (count($results) ? true : false);

	try {
		switch($multiAssignTable) {
			case 'funding':
				MultiOptionList::updateOptions ( 'training_to_training_funding_option', 'training_funding_option', 'training_id', $training_id, 'training_funding_option_id', $results, 'funding_amount', $extra );
			break;
			case 'pepfar':
				MultiOptionList::updateOptions ( 'training_to_training_pepfar_categories_option', 'training_pepfar_categories_option', 'training_id', $training_id, 'training_pepfar_categories_option_id', $results, 'duration_days', $extra );
			break;
			case 'topic':
				MultiOptionList::updateOptions ( 'training_to_training_topic_option', 'training_topic_option', 'training_id', $training_id, 'training_topic_option_id', $results );
			break;
			case 'refresher':
				MultiOptionList::updateOptions ( 'training_to_training_refresher_option', 'training_refresher_option', 'training_id', $training_id, 'training_refresher_option_id', $results );
			break;
		}

	} catch (Exception $e) {
		return false;
	}

	return true;
  }

  /**
   * find or create a row, then save it with values from $valueArray
   *
   * basically an extension of existing fuctions like fillFromArray
   */
	protected function _findOrCreateSaveGeneric($tableName, $valueArray, $whereCol = 'id', $whereVal = false)
	{
		$tableCustom = new ITechTable ( array ('name' => $tableName ) );
		$whereVal = $whereVal ? $whereVal : $valueArray['id'];                                 // usually looking for id
		$row = $whereVal ? $tableCustom->fetchRow($tableCustom->select()->where( "$whereCol = ?", $whereVal )) : false; // lookup or set to false which will cause row to be created
		if (! $row) {                                                                          // created row or if not found
			$row = $tableCustom->createRow();
		}

		$this->fillFromArray($row, $valueArray);                                               // fill data from array
		if(isset($row->is_default) && !isset($valueArray['is_default']))                       // breaks otherwise
			$row->is_default = 0;

		$id = $row->save();
		return $id;
	}

  public function mmplog($logMessage){
            $logMessage = date('Y-m-d H:i:s') . ' ' . $logMessage . "\n";
            file_put_contents("settinglogs.txt", $logMessage, FILE_APPEND);
        }
}
?>
