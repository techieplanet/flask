<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommodityManager
 *
 * @author Swedge
 */


ini_set("memory_limit","4095M");
class CommodityDataProcessor {
    //put your code here
    
    private $db = "";
    private $data_json_arr = "";
    private $fullDate = "";
    private $existingFacs = "";
    private $existingStates = "";
    private $existingCommodityNames = "";
    private $existingConsumptionRecords = "";
    private $commodityTable = 'commodity';
    
    private $all_errors;
    
    const   commodityIndex = 0;
    const   facilityIndex = 1;
    const   consumptionIndex = 2;
    const   stockedOutFor7ConsecDays = "JyiR2cQ6DZT";
    const   stockedOutOfImplants = "wNT8GGBpXKL";
    const   stockedOutOfFemaleCondoms = "pYhpegHDt4x";
    const   stockedOutOfEmergencyContraception = "QlroxgXpWTL";
    
    
    public function CommodityDataProcessor($focusDate, $dbObject){
        $this->fullDate = $this->getFullDate($focusDate);
        $this->data_json_arr = json_decode($this->getFileData($focusDate), TRUE);
        
        $this->db = $dbObject;
        $this->existingFacs = $this->getDBFacilitiesInfo();
        $this->existingStates = $this->getDBStateInfo();
        $this->existingCommodityNames = $this->getDBCommodityNamesInfo();
        $this->existingConsumptionRecords = $this->getDBCommoditiesDataInfo();
        
        echo 'FULL DATE: ' . $this->fullDate . '<br/>';
        //var_dump($this->existingConsumptionRecords);
        echo '<br/><br/><br/><br/>';
    }
    
    private function getFullDate($focusDate){
        $date_year = substr ( $focusDate, 0, 4 );
	$date_month = substr ( $focusDate, - 2 );
        return $date_year . "-" . $date_month . "-01";
    }
        
    private function getFileData($focusDate){
        $iterMonthCacheFileName = 'json_comm/FacilityCommodity-' . $focusDate . ".json";
        return file_get_contents($iterMonthCacheFileName);
    }
    
    public function updateFacilities(){     
            //set up the needed variables
            $data_json_arr = $this->data_json_arr;
            $hierarchy = $data_json_arr ["metaData"] ["ouHierarchy"];
            $names = $data_json_arr ["metaData"]["names"];
            $db_facility_info = $this->existingFacs;
            $db_state_info = $this->existingStates;
            
            $error = '';
    
            $count = 0;
            foreach ( $hierarchy as $facility_external_id=>$location_path ) {
                    if(!$location_path || empty($location_path)){
                            $error = $error . "ERROR: " . $facility_external_id . " (facility location is empty)\r\n";
                            continue;
                    }
                    $location = explode("/", $location_path);
                    // parse only for facilities.
                    //Path should be [facility UID]=>/[UID - skip]/[state UID]/[LGA UID]/[UID - skip]
                    if(count($location) != 5){
                            continue;
                    }
                    
                    $count++;
                    $state_external_id =  $location[2];
                    $lga_external_id =  $location[3];
                    
                    //remove prefix before name, like 'Kd Radiance clinic' - remove 'Kd'
                    $facility_name = substr(strstr(trim($names[$facility_external_id])," "), 1);
                    $state_name = substr(strstr(trim($names[$state_external_id])," "), 1);
                    $lga_name = substr(strstr(trim($names[$lga_external_id])," "), 1);
                    
                    // remove 'Local Government Area' from LGA names
                    /*or do this: UPDATE location SET location_name = REPLACE(location_name, 'Local Government Area', '') where tier=3 and location_name like '%Local Government Area';*/
                    $lga_name = trim(str_replace("Local Government Area","",$lga_name));
                    
                    // remove ' from names for LGA like "Jama'are"
                    $lga_name = trim(str_replace("'","",$lga_name));
                    
                    $facility_name = trim($facility_name);		
                    if(empty($facility_name)){
                            $error = $error . "ERROR: " . $facility_external_id . " (facility name is empty)\r\n";
                            continue;
                    }
                    
                    if($db_facility_info && array_key_exists($facility_external_id, $db_facility_info)){
                            // if facility name are different then update
                            if($facility_name !== $db_facility_info[$facility_external_id]['facility_name']){
                                    try{
                                            $this->db->query("UPDATE facility SET facility_name='" . $facility_name . "' WHERE external_id='" . $facility_external_id . "'");
                                    }catch(Exception $e){
                                            $error = $error . "ERROR: EDIT FACILITY: " . $facility_external_id . " (" . $e->getMessage() . ")\n";
                                    }
                            }
                    }else{  //ADD THE NEW FACILITY
                                    // remove '-' from state name
                                    $state_name = str_replace('-', ' ', trim($state_name));
                                    // remove 'state' word from $state_name
                                    $state_name = trim(ucwords(str_replace('state', '', strtolower($state_name))));
                                    //find state in database (hardcoded)
                                    if($db_state_info && array_key_exists($state_name, $db_state_info)){
                                            $state_id = $db_state_info[$state_name];
                                            if($state_id){
                                                    $lga_id = $this->isLocationExist($lga_external_id);
                                                    if($lga_id === NULL){
                                                            $lga_id = addLocation($lga_external_id, $lga_name, 3, $state_id);
                                                    }
                                                    $bind = array(
                                                                    'external_id'			=>	$facility_external_id,
                                                                    'facility_name'		=>	$facility_name,
                                                                    'location_id'=>	$lga_id,
                                                                    'timestamp_created' => $this->fullDate,
                                                    );
                                                    try{
                                                            //all value automatically will be removed white spaces at the END during insertion to DB
                                                            $this->db->insert("facility", $bind);
                                                            $facility_id=$this->db->lastInsertId();
                                                            
                                                    }catch(Exception $e){
                                                            $error = $error . "ERROR: ADD FACILITY: " . $facility_external_id . " does not have prefix (" . $e->getMessage() . ")\r\n";
                                                    }
                                            }else{
                                                    $error = $error . "ERROR: ADD FACILITY: cannot add new facility '" . $facility_name . "': state '" . $state_name . "' does not exist in database.\r\n";
                                            }
                                    }else{
                                            $error = $error . "ERROR: ADD FACILITY: cannot add new facility '" . $facility_name . "': state '" . $state_name . "' does not exist in database.\r\n";
                                    }
                    }
            }
            if(!empty($error)){
                    $this->all_errors .= "\r\n=> UPDATE FACILITIES:\n" . $error . "\r\n";
            }
            print "=> UPDATE FACILITIES END:\n" .  $count . " facilities have been processed.\n";

            //validate process
            $db_facility_info_count = $this->db->fetchAll ("select count(*) as count from facility");
            print $db_facility_info_count[0]['count'] . " facilities in database.\n\n";
    }
    
    
    /*TP: 
             * Commenting this block of code out
             * Adding a commodity will NOT be handled by this process anymore but 
             * will now be a deliberate action that will be done manually on the database
             * This is because there are now fields such as commodity type and alias that have to be entered 
             * for each commodity and this is not provided from DHIS2. 
             * 
             * Also, there may be code changes based on a commodity addition to the system. An automated
             * approach will not work for this anymore.
             */
    
    /**
     * 
     * @Function Loads the data for the focus month into the database 
     */
    public function loadMonthData() {
        
            $db_facility_info = $this->existingFacs;
            $db_commodity_info = $this->existingCommodityNames;
            $db_commodity_data_info = $this->existingConsumptionRecords;
                    
           /* -------------------------------------------
            * THIS WILL HELP TO STRIP OUT ALL COMMODITIES NOT WATCHED BY THIS SYSTEM
            */
           $external_id = array("DiXDJRmPwfh","G5mKWErswJ0","H8A8xQ9gJ5b","ibHR9NQ0bKL","JyiR2cQ6DZT","krVqq8Vk5Kw","mvBO08ctlWw","QlroxgXpWTL","vDnxlrIQWUo","w92UxLIRNTl","wNT8GGBpXKL","yJSLjbC9Gnr","pYhpegHDt4x");
           //WS:"DiXDJRmPwfh","G5mKWErswJ0","H8A8xQ9gJ5b","ibHR9NQ0bKL","JyiR2cQ6DZT","krVqq8Vk5Kw","mvBO08ctlWw","QlroxgXpWTL","vDnxlrIQWUo","w92UxLIRNTl","wNT8GGBpXKL","yJSLjbC9Gnr","Yw92UxLIRNTl"

            // remove this huge object (2Mb of size)
            // The object contains all facilities in UUID form
            unset($this->data_json_arr["metaData"]["ou"]); 
            
            if (sizeof ( $this->data_json_arr["rows"] ) == 0) {
                    global $all_errors;
                    $all_errors = $all_errors . "ERROR: Commodity data is empty in WS.\n";
                    exit ();
            }

            // ******************* UPDATE COMMODITIES DATA ***********************************************
            $this->updateCommoditiesData(); 
           
    }


    
    private function updateCommoditiesData(){        
        $this->log("\r\n\r\n-------------- BEGINNING LOGS FOR $this->fullDate ----------------------");
        
        $commodity_data = $this->data_json_arr["rows"];
        $db_commodity_info = $this->existingCommodityNames;
        $db_facility_info = $this->existingFacs;
        $db_commodity_data_info = $this->existingConsumptionRecords;
        
        $date = $this->fullDate;
        $db = $this->db;
        
        //get list of reporting facilities for the month
        $facilities_array = $this->getReportedFacsList();
        
        $stockedOutIndicators = array(
                                    self::stockedOutFor7ConsecDays, 
                                    self::stockedOutOfImplants, 
                                    self::stockedOutOfFemaleCondoms, 
                                    self::stockedOutOfEmergencyContraception
                                );
        
        $commodity_not_id = $not_facility = $commodity_not_inserted = $commodity_inserted = $commodity_updated = array(); 
        
	$error = '';


        $facFoundCount = 0; $commFoundCount = 0; $commNadaCount = 0; $facNadaCount = 0;
        $count = 0;
        $counter = 0; $facPassCounter = $loopCounter = $commPassCounter = $commPassCounter = $insertCounter = $updateCounter = 0; 
        echo 'The number of commodtiy reords from DHIS(including non-tracked commodities): ' . sizeof($commodity_data).'<br/><br/>';
        $counterArray = array($date=>array(
                                    'foundindb' => 0,
                                    'continue' => 0,
                                    'update' => 0,
                                    'insert' => 0
                              ));
        
        
        //echo '<br>------------- Commodity Rows -------------<br>';
	foreach ($commodity_data as $commodity) {
                $loopCounter++;
		$commodity_external_id = $commodity[self::commodityIndex];
                $consumption = $commodity[self::consumptionIndex];
                

                //TP: check if the commodity external id is registered in our system
		if($db_commodity_info && array_key_exists($commodity_external_id, $db_commodity_info)){
                    $commPassCounter++; 
                    
                    $commodity_id = $db_commodity_info[$commodity_external_id]['id'];

                    $facility_external_id = $commodity[self::facilityIndex];
                        
                                
			if($db_facility_info && array_key_exists($facility_external_id, $db_facility_info)){
                           $facPassCounter++; 
                                                        
				$count++;
				$facility_id = $db_facility_info[$facility_external_id]['id'];	

				//$consumption = $commodity[3];
                                $uuid = $commodity[self::commodityIndex];
                                

                                //$stock_out = (($uuid==$STOCK_OUT_7DAYS || $uuid==$STOCK_OUT_IMPLANT) && ($consumption=="1.0" || $consumption=="1" || $consumption=="100" || $consumption=="100.0"))?"Y":"N";
                                $consumptionArray = array("1", "1.0", "100", "100.0");
                                $stock_out = (in_array($uuid,$stockedOutIndicators) && (in_array($consumption,$consumptionArray))) ? "Y" : "N";
                                //$bringer = 0;
				try{
                                    $counter = $counter + 1;
                                        
                                          //if(array_key_exists($facility_id,$facilities_array)){
                                          if(isset($facilities_array[$facility_external_id][$facility_id])){
                                              $flag = 1;
                                          }else{
                                              $flag = 0;
                                          }
                                        
                                          $bind = array(
						'name_id'         =>  $commodity_id,
                                                'date'            =>  $date,
                                                'consumption'     => $consumption,
						'stock_out'       => $stock_out,
                                                'facility_id'     =>	$facility_id,
                                                'facility_reporting_status'=>$flag,
						'modified_by' => '0'
                                              );
                                          
                                          
                                          set_time_limit(600);
                                          
//                                          //var_dump($db_commodity_data_info[$facility_id][$commodity_id]); 
//                                          //var_dump($db_commodity_data_info[63102][15]); 
//                                            if(isset($db_commodity_data_info[$facility_id])){
//                                                $facFoundCount++;
//                                              $facilityCommodities = $db_commodity_data_info[$facility_id];
//                                              if(isset($facilityCommodities[$commodity_id]))
//                                                $commFoundCount++;
//                                              else
//                                                $commNadaCount++;
//                                            }
//                                            else{
//                                                $facNadaCount++;
//                                            }
                                          
                                          if(isset($db_commodity_data_info[$facility_id][$commodity_id])){
                                              $counterArray[$date]['foundindb'] += 1;
                                              if($db_commodity_data_info[$facility_id][$commodity_id]['consumption'] == $consumption && $db_commodity_data_info[$facility_id][$commodity_id]['stock_out'] == $stock_out ){
                                                  $counterArray[$date]['continue'] += 1;
                                                  continue;
                                              }
                                              else{
                                                    //$db->getProfiler()->setEnabled(true);
                                                    
                                                    $bind['timestamp_created'] = date("Y-m-d H:i:s");
                                                    
                                                    $where = array();
                                                    $where[] = "name_id = $commodity_id";
                                                    $where[] = "facility_id = $facility_id";
                                                    $where[] = "date = '$date'";
                                                    $updateBoolean = $db->update("$this->commodityTable", $bind, $where);
                                                    
//                                                    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
//                                                    Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
//                                                    $db->getProfiler()->setEnabled(false);
//                                                    //exit;
                                                    
                                                    //if($updateBoolean > 0) $updateCounter++;
                                                    $counterArray[$date]['update'] += 1;
                                              }
                                          }
                                          else{
                                              //echo 'Inserting...'; var_dump($bind); exit;
                                              
                                              //$db->getProfiler()->setEnabled(true);
                                                
                                              $insertBoolean = $db->insert("$this->commodityTable", $bind);
                                              
                                                //Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQuery());
                                                //Zend_Debug::dump($db->getProfiler()->getLastQueryProfile()->getQueryParams());
                                                //$db->getProfiler()->setEnabled(false);
                                                //exit;
                                              //if($db->lastInsertId() > 0) $insertCounter++;
                                              $counterArray[$date]['insert'] += 1;
                                          }

				}catch(Exception $e){
					$error = $error . "ERROR ADD COMMODITY DATA: " . $commodity_external_id . "=>" . $facility_external_id . "=" . $consumption . " (" . $e->getMessage() . ")\r\n";
				}
			}else{
                            $not_facility[] = $facility_external_id;
                        }
		}else{
                    $commodity_not_id[] = $commodity_external_id; 
                }
                
	}
//        
//        echo 'loopCounter: ' . $loopCounter . '<br>';
//        echo 'commPassCounter: ' . $commPassCounter . '<br>';
//        echo 'facPassCounter: ' . $facPassCounter . '<br>';
//        echo 'counter: ' . $counter . '<br>';
//        echo 'facFoundCount: ' . $facFoundCount . '<br>';
//        echo 'commFoundCount: ' . $commFoundCount . '<br>';
//        echo 'commNadaCount: ' . $commNadaCount . '<br>';
//        echo 'facNadaCount: ' . $facNadaCount . '<br>';
        
        
        //var_dump($counterArray);
        //exit;
        
        //$this->log("METRICS FOR DATE $date: \r\n" . print_r($counterArray, TRUE));
        
        print '------------------- COMMS NOT FOUND IN DB ---------------<br/>';
        print(count($commodity_not_id)); echo '<br/><br/>'; 
        $this->log("COMMODITIES NOT FOUND IN DB: " . count($commodity_not_id));
        
        print '------------------- FACS NOT FOUND IN DB ---------------<br/>';
        print(count($not_facility)); echo '<br/><br/>';
        $this->log("FACS NOT FOUND IN DB: " . count($not_facility));
                
        print "counter: $counter, facPassCounter: $facPassCounter, loopCounter: $loopCounter, commPassCounter: $commPassCounter, insertCounter: $insertCounter, updateCounter: $updateCounter<br>";
        $this->log("counter: $counter, facPassCounter: $facPassCounter, loopCounter: $loopCounter, commPassCounter: $commPassCounter, insertCounter: $insertCounter, updateCounter: $updateCounter");
        
	$db_commodity_info_count = $db->fetchAll ("select count(*) as count from $this->commodityTable where date='" . $date . "'");
	print $db_commodity_info_count[0]['count'] . " commodities data in database.<br>";
        $this->log("Number of commodities data in database: " . $db_commodity_info_count[0]['count']);
        
        if(!empty($error)) $this->log ("\r\nERRORS LIST: \r\n" . $error);
        
    }//end updateCommoditiesData

    
    /**
     * @return Array List of the commodities already in the database for the focus month
     * @structure: commodityRow['facility_id']['name_id'] = commodityRow['id']
     */
    private function getDBCommoditiesDataInfo(){
	//echo "\n=>Get commodity data info from database...\n\n";
        $sql = "select id, name_id, facility_id, consumption, stock_out from $this->commodityTable where date = '" . $this->fullDate . "'";
	$db_commodity_data_info = $this->db->fetchAll ($sql);
	$db_commodity_data_info_hash = array();
        $count = 0;
	foreach ( $db_commodity_data_info as $db_commodity_data_row ) {
		$db_commodity_data_info_hash[$db_commodity_data_row['facility_id']][$db_commodity_data_row['name_id']] = array(
                                                            'id' => $db_commodity_data_row['id'],
                                                            'consumption' => $db_commodity_data_row['consumption'],
                                                            'stock_out' => $db_commodity_data_row['stock_out']
                                                );
                $count++;
	}
        echo 'DB QUERY: ' . count($db_commodity_data_info) . '<br/>';
        echo 'LOOP: ' . $count . '<br/>'; 
        echo 'HASH: ' . count($db_commodity_data_info_hash) . '<br/>';
	return $db_commodity_data_info_hash;
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
    
    /**
     * 
     * @return Array List of states in the database at the time of call
     * @structure array['state_name'] = stateRow['id']
     */
    private function getDBStateInfo(){
	//echo "\n=>Get states info from database...\n\n";
	$db_state_info = $this->db->fetchAll ("select id, location_name, parent_id from location where tier=2");
	$db_state_info_hash = array();
	foreach ( $db_state_info as $db_state_row ) {
		$db_state_info_hash[$db_state_row['location_name']] = $db_state_row['id'];
	}
	return $db_state_info_hash;
    }
    
    //returns location id
    private function isLocationExist($external_location_id){
	$db_location_info = $this->db->fetchAll ("select id, location_name from location where external_id = '" . $external_location_id . "'");
	if($db_location_info){
		return $db_location_info[0]['id'];
	}
	return NULL; // not found
    }
    
    private function addLocation($external_location_id, $location_name, $location_tier, $location_parent_id){
	$bind = array(
			'external_id'			=>	$external_location_id,
			'location_name'		=>	$location_name,
			'tier'=>	$location_tier,
			'parent_id'=>	$location_parent_id,
			'timestamp_created' => $this->fullDate,
	);
	try{
            $this->db->insert("location", $bind);
            $location_id=$this->db->lastInsertId();
            return $location_id;
	}catch(Exception $e){
		throw new Exception("ERROR: " . $external_location_id . " (" . $e->getMessage() . ")\r\n");
	}
    }
    
    /**
     * 
     * @return Array List of commodities in the database
     * @structure array[commodity['external_id']] = commodity['id', 'external_id', 'commodity_name']
     */
    private function getDBCommodityNamesInfo(){
	//echo "\n=>Get commodity names info from database...\n\n";
	$db_commodity_info = $this->db->fetchAll ("select id, external_id, commodity_name from commodity_name_option");
	$db_commodity_info_hash = array();
	foreach ( $db_commodity_info as $db_commodity_row ) {
		$db_commodity_info_hash[$db_commodity_row['external_id']] = $db_commodity_row;
	}
	return $db_commodity_info_hash;
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
   
   private function log($logMessage){
        $logMessage = date('Y-m-d H:i:s') . ': ' . $logMessage . "\r\n";
        file_put_contents("logs.txt", $logMessage, FILE_APPEND);
    }
}
