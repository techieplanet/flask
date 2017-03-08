<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MetricClient
 *
 * @author Swedge
 */
require_once ('models/table/User.php');

class MetricClient{
    
    const METRIC_MODULES_CHART = "CHART";
    const METRIC_MODULES_QUERIES = "QUERIES";
    const METRIC_MODULES_DATACOLLECTION = "DATA_COLLLECTION";
    
    const ACTION_TYPE_VISIT = 1;
    const ACTION_TYPE_SEARCH = 2;
    const ACTION_TYPE_LOGIN = 3;
    const ACTION_TYPE_LOGOUT = 4;
    const ACTION_TYPE_DC = 5;
    
    private $_moduleName ='';
    private $_selectionArray = array();
    private $_actionType ='';
    private $_pageUrl ='';
    private $_pageId ='';
    private $_userDetailsArray = '';
    private $WSDL_URL = '';
    
//    { 
//        Module_id,
//        Selection:[ArrayOfSelection],
//        User : [user_id, name, role, location: [[zone:location1], [state:[state1,state2,...stateN]], [LGA: lga]]
//        timestamp: [date, time]
//        actiontype: 1|0
//    }

    public function MetricClient(){
        $this->WSDL_URL = 'http://fpdashboard.ng/analytics/MetricCollectorService.wsdl';
    }
    
    public function handleSeacrhMetrics($moduleName, $pageId, $userId, $searchDetails){
        date_default_timezone_set("Africa/Lagos"); 
        $documentArray = array(
              "module_name" => $moduleName, 
              "page_id" => $pageId,
              'action_type' => MetricClient::ACTION_TYPE_SEARCH,
              "userid" => $userId,
              "timestamp" => date("Y-m-d H:i:s"),
              "month" => date('F'),
              "year" => date('Y'),
              "millidate" => "" . strtotime(date('Y-m-d H:i:s')) * 1000,
              "ip" => $_SERVER["REMOTE_ADDR"],
              "user_agent" => $_SERVER["HTTP_USER_AGENT"],
              "details" => $searchDetails
          );       
                                          
        $this->logAction($documentArray);
    }

    public function handleVisitMetrics($moduleName, $pageId, $userId){
        date_default_timezone_set("Africa/Lagos");
        $documentArray = array( 
              "module_name" => $moduleName, 
              "page_id" => $pageId,
              'action_type' => MetricClient::ACTION_TYPE_VISIT,
              "userid" => $userId,
              "timestamp" => date("Y-m-d H:i:s"),
              "month" => date('F'),
              "year" => date('Y'),
              "millidate" => "" . strtotime(date('Y-m-d H:i:s')) * 1000,
              "ip" => $_SERVER["REMOTE_ADDR"],
              "user_agent" => $_SERVER["HTTP_USER_AGENT"]
          );       

        $this->logAction($documentArray);
        
    }
    
    
    public function handleAuthMetrics($userId, $actionType){
        date_default_timezone_set("Africa/Lagos");
        $documentArray = array( 
              'action_type' => $actionType,
              "userid" => $userId,
              "timestamp" => date("Y-m-d H:i:s"),
              "millidate" => "" . strtotime(date('Y-m-d H:i:s')) * 1000,
              "month" => date('F'),
              "year" => date('Y'),
              "ip" => $_SERVER["REMOTE_ADDR"],
              "user_agent" => $_SERVER["HTTP_USER_AGENT"]
          );       
                                          
        $this->logAction($documentArray);
    }
    
    public function handleDCMetrics($trainingId, $activityType, $userId){
        date_default_timezone_set("Africa/Lagos");
        /*
         * eu => Excel Upload
         * pa => Person Add
         * ta => Training Add
         */
        $documentArray = array( 
              'training_id' => $trainingId,
              'action_type' => MetricClient::ACTION_TYPE_DC,
              'activity_type' => $activityType,
              "userid" => $userId,
              "timestamp" => date("Y-m-d H:i:s"),
              "month" => date('F'),
              "year" => date('Y'),
              "millidate" => "" . strtotime(date('Y-m-d H:i:s')) * 1000,
              "ip" => $_SERVER["REMOTE_ADDR"],
              "user_agent" => $_SERVER["HTTP_USER_AGENT"]
          );       
                                          
        $this->logAction($documentArray);
        
    }
    
    function logAction($documentArray){
        $client = "";
        try{          
          ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
          
          //$client = new SoapClient('http://localhost/trainsmart/html/analytics/mongodb/MetricCollectorService.wsdl', array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => TRUE));
          $client = new SoapClient(
                  $this->WSDL_URL, 
                  array('cache_wsdl' => WSDL_CACHE_NONE, 
                    'trace' => TRUE, 
                    "connection_timeout" => 60, 
                    'keep_alive' => false)
            );
          //var_dump($client); var_dump($client->__getFunctions()); exit;
          
          $client->setMetric(json_encode($documentArray));
            
        } catch (SoapFault $e) {
            echo $e->getMessage() . "<br>";
            echo "<br><br>" . $e->getTraceAsString();
            echo "<br><br>" . '__getLastRequest: '; var_dump($client->__getLastRequest());
            echo "<br><br>" . '__getLastResponse: '; var_dump($client->__getLastResponse());
            echo "<br><br>" . '__getLastRequestHeaders: '; var_dump($client->__getLastRequestHeaders());
            echo "<br><br>" . '__getLastResponseHeaders: '; var_dump($client->__getLastResponseHeaders());
            exit;
        }
    }
    
    
    
    /**
     * @Description Retrieves metric data from the SOAP service
     * @param type $queryArray contains the query parameters in array format 
     * @param type $operationKeyword keyword to use for the GET operation e.g. find 
     * @return type BSON string
     */
    public function handleDataGet($queryArray, $optionsArray, $operationKeyword, $tableName){
        date_default_timezone_set("Africa/Lagos");   
        
        if(!is_array($queryArray)) return "First parameter must be an array";
        
        $client = "";
        try{          
          ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
          $client = new SoapClient($this->WSDL_URL, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => TRUE));
          //var_dump($client); var_dump($client->__getFunctions()); exit;
          
          $resultSet = $client->getMetric(json_encode($queryArray), json_encode($optionsArray), $operationKeyword, $tableName); 
          
          return $resultSet;
            
        } catch (SoapFault $e) {
            echo $e->getMessage() . "<br>";
            echo "<br><br>" . $e->getTraceAsString();
            echo "<br><br>" . '__getLastRequest: '; var_dump($client->__getLastRequest());
            echo "<br><br>" . '__getLastResponse: '; var_dump($client->__getLastResponse());
            echo "<br><br>" . '__getLastRequestHeaders: '; var_dump($client->__getLastRequestHeaders());
            echo "<br><br>" . '__getLastResponseHeaders: '; var_dump($client->__getLastResponseHeaders());
            exit;
        }
    }
    
    /**
     * 
     * @param type $dataArray the array contauning the list of data to push
     * @param type $tableName the table to push data into
     */
    public function handleDataDump($dataArray, $tableName){
        date_default_timezone_set("Africa/Lagos");   
        
        $client = "";
        try{          
          ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
          $client = new SoapClient($this->WSDL_URL, array('cache_wsdl' => WSDL_CACHE_NONE, 'trace' => TRUE));
          //var_dump($client); var_dump($client->__getFunctions()); exit;
          
          return $client->dumpData(json_encode($dataArray), $tableName); 
            
        } catch (SoapFault $e) {
            echo $e->getMessage() . "<br>";
            echo "<br><br>" . $e->getTraceAsString();
            echo "<br><br>" . '__getLastRequest: '; var_dump($client->__getLastRequest());
            echo "<br><br>" . '__getLastResponse: '; var_dump($client->__getLastResponse());
            echo "<br><br>" . '__getLastRequestHeaders: '; var_dump($client->__getLastRequestHeaders());
            echo "<br><br>" . '__getLastResponseHeaders: '; var_dump($client->__getLastResponseHeaders());
            exit;
        }
    }
    
    function getUserDetails(){
        $auth = Zend_Auth::getInstance();
        $user = new User();
        //$user = 
        if ($auth->hasIdentity()) {
            // Identity exists; get it
            $identity = $auth->getIdentity();
            //var_dump($identity);
            
            //getting loggedin user solely because this helped during development to test with various USER IDs with various roles
            //$loggedInUser = $user->getUserById($identity->id);
            $loggedInUser = $user->getUserById(234);
            //User : [user_id, name, role, location: [location1, location2,...locationN] ]
            $arr = array(
                'id' => $loggedInUser['id'],
                'username' => $loggedInUser['username'],
                'firstname' => $loggedInUser['first_name'],
                'lastname' => $loggedInUser['last_name'],
                'role' => $user->getRoleTitle($loggedInUser['role']),
                'location' => $this->getLocationArray((int)$loggedInUser['id'], (int)$loggedInUser['role']),
            );
            
            return $arr;
        }
    }
    
    private function getLocationArray($userId, $roleId){
        $user = new User(); $locationObj = new Location();
        $geoZoneString = ""; $stateString = "";
        $locationsString = "";
        $locationDetails = $user->getUserLocationDetails($userId);
        
        if($roleId <= 2)
            ;
        else if($roleId == 3){  //partner
            if(!empty($locationDetails['multiple_locations_id'])){
                $multipleLocationsArray = json_decode($locationDetails['multiple_locations_id']);
                foreach($multipleLocationsArray as $mLocation){
                    $partnerLocationsArray = explode("_", $mLocation);
                    if(!empty($partnerLocationsArray)){
                        $province = $locationObj->getLocationById($partnerLocationsArray[0]);
                        $geoZoneString .= '"' . $province['location_name'] . '",';
                        
                        $state = $locationObj->getLocationById($partnerLocationsArray[1]);
                        $stateString .= '"' . $state['location_name'] . '",';
                    }
                }
            }
        } else {
            $province = $locationObj->getLocationById($locationDetails['province_id']);
            if(!empty($province)) $locationArray['geozone'] =  array($province['location_name']) ;
            
            $state = $locationObj->getLocationById($locationDetails['district_id']);
            if(!empty($state)) $locationArray['state'] = array($state['location_name']);
            
            $lga = $locationObj->getLocationById($locationDetails['region_c_id']);
            if(!empty($lga)) $locationArray['lga'] = $lga['location_name'];
        }
        
        $geoZoneString = '[' . substr($geoZoneString, 0, strlen($geoZoneString)-1) . ']';
        $stateString = '[' . substr($stateString, 0, strlen($stateString)-1) . ']';
        
        $locationArray = array('geozones' => $geoZoneString, 'states' => $stateString);
        
        return $locationArray;
    }
}