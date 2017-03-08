<?php

ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 1000);
require_once '../../../sites/settings.php';

class CacheData {
    
    public $DB_SERVER;
    public $DB_USERNAME;
    public $DB_PWD;
    public $DB_DATABASE;
    public $DOMAIN_URL;
    private $WSDL_URL = '';
    
    public function __construct()
    {
        $this->DB_SERVER = Settings::$DB_SERVER;
        $this->DB_USERNAME = Settings::$DB_USERNAME;
        $this->DB_PWD = Settings::$DB_PWD;
        $this->DB_DATABASE = Settings::$DB_DATABASE;
        $this->DOMAIN_URL = Settings::$COUNTRY_BASE_URL;
        
        $this->WSDL_URL = 'http://fpdashboard.ng/analytics/MetricCollectorService.wsdl';
    }
    
    public function cacheAllOverTimeChart(){
        
        $urls = array();
        $urls[] = "cacheLocationByLoginsData";
        $urls[] = "cacheDailySessionsLastMonthsByCharts";
        $urls[] = "cacheDailySessionsLastMonthsByQueries";
        $urls[] = "cacheDailySessionsLastMonthsByDc";
        $urls[] = "cacheSumTotalUsersLast12Months";
        
        foreach($urls as $url){
            
            $response = file_get_contents($this->DOMAIN_URL ."/analyticsquery/" . $url);
            var_dump($response);
            
            echo '<br/><br />';
        }
        
    }
   
}

$cacheDataObj = new CacheData();
$cacheDataObj->cacheAllOverTimeChart();
