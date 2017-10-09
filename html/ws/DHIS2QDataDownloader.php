<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DHIS2QDataDownloader
 *
 * @author Swedge
 */
require_once '../../sites/globals.php';
require_once 'CommodityDataProcessor.php';
require_once 'FRRDataProcessor.php';
require_once 'excel/ExcelUpdateManager.php';

class DHIS2QDataDownloader {
    //put your code here
    //private $datesArray = array();
    private $nearestPullMonth;
    private $nearestThreeMonths = "";
    
    const FRRDataUrlStart = "https://dhis2nigeria.org.ng/dhis/api/analytics.json?dimension=dx:lyVV9bPLlVy&dimension=ou:LEVEL-5;s5DPBsdoE8b&dimension=pe:";
    const FRRDataUrlEnd   = "&displayProperty=NAME";
    //const FRRDataUrlEnd   = "&displayProperty=NAME&outputIdScheme=ID";
    
    const COMMDataUrlStart = "https://dhis2nigeria.org.ng/dhis/api/analytics.json?dimension=dx:DiXDJRmPwfh;EIHpURrBm7K;G5mKWErswJ0;H8A8xQ9gJ5b;JyiR2cQ6DZT;QlroxgXpWTL;eChiJMwaOqm;ibHR9NQ0bKL;krVqq8Vk5Kw;mvBO08ctlWw;pYhpegHDt4x;vDnxlrIQWUo;w92UxLIRNTl;wNT8GGBpXKL;yJSLjbC9Gnr&dimension=ou:LEVEL-5;s5DPBsdoE8b&filter=pe:";
    const COMMDataUrlEnd = "&hierarchyMeta=true&displayProperty=NAME&ignoreLimit=true";
               
    const dhisUsername = "FP_Dashboard";
    const dhisPassword = "CHAI12345";
    
    public function downloadFRR(){
        $cacheFileNamePrepend = "json_frr/FacilityReportRate-";
        
        $this->log('Starting FRR download');
        
        for($i=0; $i<count($this->nearestThreeMonths); $i++){
            $iterMonth = $this->nearestThreeMonths[$i];
            
            $iterMonthUrl = self::FRRDataUrlStart . $iterMonth . self::FRRDataUrlEnd; //set up the URL
            $this->log('FRR URL for ' . $iterMonth . ': ' . $iterMonthUrl);
            
            $iterMonthCacheFileName = $cacheFileNamePrepend . $iterMonth . ".json"; //file to use as cache for $iterMonth data
            $this->downloadData($iterMonthUrl, $iterMonthCacheFileName);
            sleep(1);
        }
        
        $this->log('Completed FRR download');
        
    }
    
    public function downloadCommodityData(){        
        $cacheFileNamePrepend = "json_comm/FacilityCommodity-";
        
        $this->log('Starting Comm download');
        
        for($i=0; $i<count($this->nearestThreeMonths); $i++){ 
            $iterMonth = $this->nearestThreeMonths[$i];
            $iterMonthUrl = self::COMMDataUrlStart . $iterMonth . self::COMMDataUrlEnd; //set up the URL
            $this->log('Comm URL for ' . $iterMonth . ': ' . $iterMonthUrl);
            
            $iterMonthCacheFileName = $cacheFileNamePrepend . $iterMonth . ".json"; //file to use as cache for $iterMonth data
            $this->downloadData($iterMonthUrl, $iterMonthCacheFileName);
            sleep(1);
        }
        
        $this->log('Completed Comm download');
    }
    
    public function setDatesArray(){
        $this->nearestPullMonth = date("Ym",strtotime("-1 month")); 
        $this->nearestThreeMonths = array(date("Ym",strtotime("-1 month")), date("Ym",strtotime("-2 month")), date("Ym",strtotime("-3 month")));
    }
    
    public function getDatesArray(){
        return $this->nearestThreeMonths;
    }
    
    /**
     * Download the last three months FRR|Comm data
     * @param type $datesArray - array containing YearMonth values of the last 3 months.
     */
    private function downloadData($url, $cacheFileName){
        set_time_limit(0);
        $jsonData = $this->getWebServiceResult($url, self::dhisUsername, self::dhisPassword);

        $this->log('Now writing downloaded data to file!');
        //write the $FRRJsonData to file
        file_put_contents($cacheFileName, $jsonData); //do not use FILE_APPEND, we want to always overwrite        
    }
    
    /**
     * Download from DHIS web service with CURL
     * @param type $url - web servcice url
     * @param type $username
     * @param type $password
     * @return type
     */
    private function getWebServiceResult($url, $username, $password){
        ini_set("display_errors", "On");
        ini_set("memory_limit", "2048M");
        error_reporting(E_ALL);
        
		if (!function_exists('curl_init')){
                    $this->log('Aborting...cURL is not installed!');
			die('Sorry cURL is not installed!');
		}
                
                try{
                    $this->log('Now running CURL init!');
                    $ch = curl_init();
                    $this->log('Now running CURL utl!');
                    curl_setopt($ch, CURLOPT_URL, $url);
                    $this->log('Now running CURL returntransfer!');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $this->log('Now running CURL login!');
                    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
                    $this->log('Now running CURL verifypeer!');
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_VERBOSE, true);
                    $this->log('Now running CURL auth!');
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $this->log('Now running CURL timeout!');
                    curl_setopt($ch, CURLOPT_TIMEOUT, 0);

                    
                    $this->log('Now running CURL exec!');
                    $output = curl_exec($ch);
                    $this->log('After CURL exec: ' . print_r($output, true));
                    $info = @curl_getinfo($ch);
                    $this->log('CURL info: ' . print_r($info,true));

                    if($output === false){
                        $this->log('CURL ERROR: ' . curl_error($ch) . "\r\n");
                        echo 'CURL ERROR: ' . curl_error($ch);
                        exit;
                    }                    
                    else {
                        $this->log('CURL not false: ' . print_r($output, true));
                    }
                } catch (Exception $e){
                    echo $e->getMessage();
                }
                
		curl_close($ch);
                return $output;
	}
        
        private function log($logMessage){
                date_default_timezone_set('Africa/Lagos');
                $logMessage = date('Y-m-d H:i:s') . ': ' . $logMessage . "\r\n";
                file_put_contents("logs.txt", $logMessage, FILE_APPEND);
        }
        
}//end class



ini_set('display_errors', 'On');
ini_set("memory_limit","4095M");

try{
    
    set_time_limit(600);
    date_default_timezone_set('Africa/Lagos');

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $downloader = new DHIS2QDataDownloader();
    $downloader->setDatesArray();

    $downloader->downloadFRR();
    $downloader->downloadCommodityData();

    $datesArray = array_reverse($downloader->getDatesArray());
    
    foreach ($datesArray as $focusDate){
        //update the facility with any new facilities in WS data
        $commDataProc = new CommodityDataProcessor($focusDate, $db);
        $newFacilities = $commDataProc->updateFacilities();
        
        //upload FRR data
        $frrDataProc = new FRRDataProcessor($focusDate, $db);
        $frrDataProc->loadMonthData();

        //upload commodity data
        $commDataProc->loadMonthData();
        
        //Now update the execl files for states of any new facilities
        if(!empty($newFacilities)){
            $commDataProc->tempPersitNewFacilities($newFacilities);
            $fileNamePrefix = Globals::$BASE_PATH . Globals::$WEB_FOLDER . '/templates/ImportTrainingTemplate ';
            (new ExcelUpdateManager($fileNamePrefix))->run();
        }
    }
    
    

    //$cacheController = new CacheController();
    //$cacheController->setcacheAction();
} catch(Exception $e){
    echo $e->getMessage();
    echo $e->getTrace();
}