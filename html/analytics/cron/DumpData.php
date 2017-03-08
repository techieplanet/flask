<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('error_reporting', E_ALL);
require_once '../../../sites/settings.php';



class DumpData {
    
    public $DB_SERVER;
    public $DB_USERNAME;
    public $DB_PWD;
    public $DB_DATABASE;
    
    private $WSDL_URL = '';
    
    public function __construct()
    {
        $this->DB_SERVER = Settings::$DB_SERVER;
        $this->DB_USERNAME = Settings::$DB_USERNAME;
        $this->DB_PWD = Settings::$DB_PWD;
        $this->DB_DATABASE = Settings::$DB_DATABASE;
        
        $this->WSDL_URL = 'http://fpdashboard.ng/analytics/MetricCollectorService.wsdl';
    }
    
    public function getUsers(){
        
         $db = new mysqli($this->DB_SERVER, $this->DB_USERNAME, $this->DB_PWD, $this->DB_DATABASE);
         $sql = "select * from user where district_id = '961'";
         
         $query = $db->query($sql);
         $records = $query->fetch_all(MYSQLI_ASSOC);
         
         foreach ($records as $record){
             var_dump($record);
             echo "<br /> <br />";
         }
    }
    
    public function dumpAllUsers()
    {
        
        
        $locationsArray = array(); 
        $usersArray = array();
        
        
        $db = new mysqli($this->DB_SERVER, $this->DB_USERNAME, $this->DB_PWD, $this->DB_DATABASE);
        
        //get all locations
        
        $sql1 = 'select id, location_name from location';
        $resultSet = $db->query($sql1);
        $result = $resultSet->fetch_all(MYSQLI_ASSOC);
        
        
        foreach ($result as $location) {
            $locationsArray[$location['id']] = $location['location_name'];
        }
        
        
        
        //get all users
        //$db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql2 = 'select * from user';
        $resultSet2 = $db->query($sql2);
        $usersArray = $resultSet2->fetch_all(MYSQLI_ASSOC);
        
        $runArray = array();
        $multipleLocaitonsString = "";
        
        foreach($usersArray as $key=>$user){
            $multipleLocaitonsString = "";
            
            if($user['role'] == "3"){
                
                $user['geozone'] = array();
                $user['state'] = array();
                
                $compoundLocationsArray = json_decode($user['multiple_locations_id']);
                
                //no multiple_locations_id entry for this PARTNER user
                if(empty($compoundLocationsArray)) { 
                    unset($usersArray[$key]);
                    continue; 
                }
                
                
                if(!is_array($compoundLocationsArray)){
                    
                    $location_string = $compoundLocationsArray;
                    $compoundLocationsArray = array();
                    $compoundLocationsArray[] = $location_string;
                }
                
                foreach ($compoundLocationsArray as $compoundLocation){
                    $locations = explode('_', $compoundLocation);
                    //ensure you are not repeating the zone. Some users have multiple states but one geozone
                    if(!in_array($locationsArray[$locations[0]], $user['geozone'])) 
                            $user['geozone'][] = $locationsArray[$locations[0]];
                    
                    $user['state'][] = $locationsArray[$locations[1]];
                    
                    $multipleLocaitonsString .= $locationsArray[$locations[0]] . "_" . $locationsArray[$locations[1]] . ",";
                }
            }
            else
            {
                if($user['province_id'] != 0) {$user['geozone'] = $locationsArray[$user['province_id']]; }
                if($user['district_id'] != 0) $user['state'] = $locationsArray[$user['district_id']];
                if($user['region_c_id'] != 0) $user['lga'] = $locationsArray[$user['region_c_id']];
            }
            
            $multipleLocaitonsString = substr($multipleLocaitonsString, 0, -1);
            $user['multiplelocation'] = $multipleLocaitonsString;
            
            $runArray[] = $user;
        }
        
        //var_dump($runArray); exit;
        
        $response = $this->handleDataDump($runArray, 'users');
        var_dump($response);  
        
    }
    
    public function dumpAllLocation()
    {
        
        
        $sql = "select id 'id', location_name, tier, parent_id from location order by location_name asc";
        
        $db = new mysqli($this->DB_SERVER, $this->DB_USERNAME, $this->DB_PWD, $this->DB_DATABASE);
        
        $resultSet = $db->query($sql);
        
        if($resultSet)
        {
            
            $resultArray = $resultSet->fetch_all(MYSQLI_ASSOC);
            
            $response = $this->handleDataDump($resultArray, 'locations');
            var_dump($response);
             
        }
    }
    
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
    
    public function toString()
    {
        echo $this->DB_SERVER . ' - ' . $this->DB_USERNAME ;
    }
}

$dump = new DumpData();

$dump->dumpAllUsers();
$dump->dumpAllLocation();
//$dump->getUsers();
