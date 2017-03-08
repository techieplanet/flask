<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FRRDataProcessor
 *
 * @author Swedge
 */

ini_set("memory_limit","4095M");
class FRRDataProcessor {
    
    private $db = "";
    private $data_json_arr = "";
    private $fullDate = "";
    private $reportedFacs = "";
    private $existingFacs = "";
    //private $all_errors;
    
    public function FRRDataProcessor($focusDate, $dbObject){
        $this->fullDate = $this->getFullDate($focusDate);
        $this->data_json_arr = json_decode($this->getFileData($focusDate), TRUE);
        
        $this->db = $dbObject;
        $this->reportedFacs = $this->getReportedFacsList();
        $this->existingFacs = $this->getDBFacilitiesInfo();
    }
    
    public function processMonthData(){
        
    }
    
    private function getFullDate($focusDate){
        $date_year = substr ( $focusDate, 0, 4 );
	$date_month = substr ( $focusDate, - 2 );
        return $date_year . "-" . $date_month . "-01";
    }
    
    private function getFileData($focusDate){
        $iterMonthCacheFileName = 'json_frr/FacilityReportRate-' . $focusDate . ".json";
        $this->log('Loading FRR data from file: ' . $iterMonthCacheFileName . "\r\n");
        
        return file_get_contents($iterMonthCacheFileName);
    }
    
    public function loadMonthData() {
	$db_facility_info = $this->existingFacs;
        
	$error = '';        
	
	$this->log('Starting FRR data upload into database');
        
	unset($this->data_json_arr["metaData"]); // remove this huge object
        //print_r($this->data_json_arr["rows"]); exit;
        
	
	// ******************* PARSING DATA ***************************
	$count = 0;   $insertCounter = $updateCounter = 0;             
	foreach ( $this->data_json_arr["rows"] as $row) {
		$facility_external_id = $row[1];
                //var_dump($row); exit;
		
                $report = $row[3];
		if($report !== "100.0"){
            $error .= $facility_external_id . " error: " . $report;
			$error = $error . "ERROR: " . $db_facility_info[$facility_external_id]['facility_name'] . ': ' . $facility_external_id . " has value " . $report . "\r\n";
                        continue; //the Facility did not report for the month
		}
                
                //ensure one last time that the facility reporting is in our database
                if($db_facility_info && array_key_exists($facility_external_id, $db_facility_info))
                    $id = $db_facility_info[$facility_external_id]['id'];
                else
                    continue;
                
                $count++;
		$bind = array(
				'facility_external_id'	=> $facility_external_id,
				'date' => $this->fullDate,
                                'facility_id'=>$id,
                                'timestamp_created' => date("Y-m-d H:i:s")
		);
                
		try{
                    //echo "aboout to insert: " . $count . "<br/>";
                    if(isset($this->reportedFacs[$bind['facility_external_id']][$bind['facility_id']])){
                        continue; //skip this facility. it has been recorded earlier
                    }
                    else{
                        $this->db->insert("facility_report_rate", $bind);
                        $insertBoolean = $this->db->lastInsertId();
                        if($insertBoolean > 0) $insertCounter++;
                    }
                    
                    //if($insertBoolean > 0) var_dump($bind); if($count >= 10) exit;
                    
		}catch(Exception $e){
                    //echo "<br>Error Done Occur<br>";
                    $error = $error . "ERROR ADD DATA: " . $facility_external_id . " (" . $e->getMessage() . ")\r\n";
                        //echo $error; 
		}
		

		 echo '<br/><br/>'; echo '<br/><br/>';

	}
	
	print "\r\n=> REPORT RATE LOAD:\n" .  $count . " facilities have been processed.<br>";
        $this->log($count . 'facilities have been processed');
        
        print "\r\n=> REPORT RATE LOAD:\n" .  $insertCounter . " facilities were inserted.<br>";
        $this->log($insertCounter . 'facilities were inserted');
        
	
	//validate process
	$db_data_info_count = $this->db->fetchAll ("select count(*) as count from facility_report_rate where date='" . $this->fullDate . "'");
        $this->log($db_data_info_count[0]['count'] . 'facilities  in database');
              
	
        if(!empty($error)){
            //echo $error;
               //plog('Errors in the process: ' . $error);
               $file = fopen("json_frr/DHIS2Upload-FacilityReportRate-". $this->fullDate . ".errors","w");
               fwrite($file,$error);
               fclose($file);
        }
    }
    
    
    /**
    * @Description Gets all FRR records for this month
    * @return Array 
    * @structure array[$result['facility_external_id']][$result['facility_id']] = $result['id']
    */
   function getReportedFacsList(){
       // echo 'my friend the date is '.$date;
       $sql = "SELECT * FROM facility_report_rate WHERE date='" . $this->fullDate ."'";
                                             
       $result = $this->db->fetchAll($sql);
       $reportedFacs = array();
       
      if(!empty($result)) {
          foreach($result as $row){
              $reportedFacs[$row['facility_external_id']][$row['facility_id']] = $row['id'];
          }
          return $reportedFacs;
      }
      else 
          return array();
   }
   
   /**
     * @return Array Facilities in the database at the moment of call
     * @structure: array['facility_external_id'] = facilityRow['id', 'external_id', facility_name]
     */
    private function getDBFacilitiesInfo(){
	//echo "\n=>Get facility info from database...\n\n";
	 $db_facility_info = $this->db->fetchAll ("select id, external_id, facility_name from facility");
	 $db_facility_info_hash = array();
	 foreach ( $db_facility_info as $db_facility_row ) {
	 	$db_facility_info_hash[$db_facility_row['external_id']] = $db_facility_row;
	 }
	 return $db_facility_info_hash;
    }   
    
    private function log($logMessage){
        $logMessage = date('Y-m-d H:i:s') . ': ' . $logMessage . "\r\n";
        file_put_contents("logs.txt", $logMessage, FILE_APPEND);
    }

}