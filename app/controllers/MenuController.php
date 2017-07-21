<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once ('ReportFilterHelpers.php');
require_once ('models/table/OptionList.php');
//require_once('models/table/Course.php');
require_once ('models/table/CoverageHelper.php');
require_once ('models/table/Facility.php');
require_once ('views/helpers/CheckBoxes.php');
require_once ('models/table/MultiAssignList.php');
require_once ('models/table/TrainingTitleOption.php');
require_once ('models/table/Helper.php');
require_once ('models/table/Helper2.php');
require_once ('models/table/Report.php');

class MenuController extends ReportFilterHelpers {

	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		parent::__construct ( $request, $response, $invokeArgs );
	}

	public function init() {
	}

	public function indexAction() {
$this->_forward ( 'info' );
	}

	public function preDispatch() {
		$rtn = parent::preDispatch ();
		$allowActions = array ('info' );

		if (! $this->isLoggedIn ())
		$this->doNoAccessError ();

		
		return $rtn;
	}

	public function dataAction() { 	}
        
        public function rratefacilitiesAction(){
            
            if ($this->getRequest()->isXmlHttpRequest()){
            $date_format = $this->getSanParam('lastPullDate');
            $LGAID = $this->getSanParam('lga');
            
             $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $facility = new Facility();
                $helper = new Helper2();
		
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                
                 list($result,$facilities) = $this->get_all_facilities_with_location("lga",$LGAID);
                  list($resultProv,$allFacilitiesProv) = $this->getAllFacilitiesProvidingFPWithLocation("lga",$LGAID,$date_format);
                 
                 $facilityData = array();
                  foreach($result as $facData){
           $facilityID = $facData['id'];
           $facilityName = $facData['facility_name'];
           $counter  = $this->get_all_facilities_reporte_rates($facilityID,$date_format);
           $allFacilities = "Yes";
           $faciityReportingAll = "";
           if($counter<=0){
             $faciityReportingAll = "No";
           }else{
             $faciityReportingAll = "Yes";
           }
            $facProv = (in_array($facilityID,$allFacilitiesProv))?"Yes":"No";
            $reportRatesProv = $this->getAllFacilitiesProvFPReportingRates($facilityID,$date_format);
            $facprovReport = ($reportRatesProv>0)?"Yes":"No";
            
                $rowData = array();
$rowData['id'] = $facilityID;
$rowData['parent_id'] = $lga_id;
$rowData['name'] = $facilityName;
$rowData['allfac'] = $allFacilities;
$rowData['facrep'] = $faciityReportingAll;
$rowData['allfacrrate'] = "";
$rowData['allfacprovfp'] = $facProv;
$rowData['facprovfprep'] = $facprovReport;
$rowData['facprovfprrate'] = "";

$facilityData[] = $rowData;
       
                  }
           
           
           echo json_encode($facilityData);
        }else{
            return true;
            
        }
    }
        public function rratedemoAction(){
             $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $facility = new Facility();
                $helper = new Helper2();
		
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
$db = Zend_Db_Table_Abstract::getDefaultAdapter ();

$current_month = "03";
$current_year = date('Y');
//$date_format = $current_year.'-'.$current_month.'-'.'01';
list($monthDate,$monthName) = $helper->getLast12MonthsDate();  
            $this->view->assign('monthDate',$monthDate);
            $this->view->assign('monthName',$monthName);
            

if(isset($_POST['lastPullDate'])){
                $date_format = $_POST['lastPullDate'];
 }else{
$date_format = $helper->getLatestPullDate();
 }
 
 $this->view->assign('selectedDate',$date_format);
$time = strtotime($date_format);
$month = date('F',$time);
$year = date('Y',$time);
$format = "for $month, $year";
list($result,$allFacilitiesNational) = $this->get_all_facilities_with_location("","");
list($resultProv,$allFacilitiesNationalProv) = $this->getAllFacilitiesProvidingFPWithLocation("","",$date_format);

$facility_idsNational = implode(",",$allFacilitiesNational);
$reportRatesNational = $this->getAllFacilitiesReportRate("","",$date_format); //$this->get_all_facilities_reporte_rates($facility_idsNational,$date_format);
$reportRatesNationalProv = $this->getAllFacilitiesProvFPReportingRates($facility_idsNational,$date_format);

$totalFacilities = sizeof($allFacilitiesNational);
$reportRateNationalPercent = round(($reportRatesNational/$totalFacilities) * 100,2);

$totalFacilitiesProv = sizeof($allFacilitiesNationalProv);
 if($totalFacilitiesProv==0){
        $reportRateProvLGAPercent = 0;
    }else{
$reportRateNationalPercentProv = round(($reportRatesNationalProv/$totalFacilitiesProv) * 100,2);
    }
$rrateArray = array();
$rowData = array();
$rowData['id'] = 0;
$rowData['parent_id'] = 0;
$rowData['name'] = 'National';
$rowData['allfac'] = number_format($totalFacilities);
$rowData['facrep'] = number_format($reportRatesNational);
$rowData['allfacrrate'] = $reportRateNationalPercent;
$rowData['allfacprovfp'] = number_format($totalFacilitiesProv);
$rowData['facprovfprep'] = number_format($reportRatesNationalProv);
$rowData['facprovfprrate'] = $reportRateNationalPercentProv;
$rrateArray[] = $rowData;
 
//echo $totalFacilities." ".$reportRatesNational." ".$reportRateNationalPercent." ".$totalFacilitiesProv." ".$reportRatesNationalProv." ".$reportRateNationalPercentProv;

//$displayMessage ="";
//  $displayMessage.= '<h2 align="left" style="color:green;margin-left:50px;width:80%;font-size:14px !important;min-height:34px !important"><span style="width:50%;">National Reporting Rate:</span><span style="float:right;width:50%;"><div id="resultd">'.$totalFacilities.'</div><div id="resultd">'.$reportRatesNational.'</div><div id="resultd">'.$reportRateNationalPercent.'%</div><div id="resultd">'.$totalFacilitiesProv.'</div><div id="resultd">'.$reportRatesNationalProv.'</div><div id="resultd">'.$reportRateNationalPercentProv.'%</div></span></h2>';
////
//$displayMessage.= '<div id="accordion">';

$zones = $this->get_location_category_unique("zone");
//print_r($zones);exit;
foreach($zones as $zone){
    $zoneArray = array();
    $zone_name = $zone['geo_zone'];
    $zone_id = $zone['geo_parent_id'];
   
    list($result,$facilities) = $this->get_all_facilities_with_location("zone",$zone_id);
  //  list($resultProv,$allFacilitiesProv) = $this->getAllFacilitiesProvidingFPWithLocation("zone",$zone_id,$date_format);
    list($numerator,$denominator) = $this->getAllFacilitiesProvFPReportingRatesSpecial(1,$zone_id,$date_format);
        $reportRatesProv = $numerator[$zone_name];
        $totalFacilitiesProv = $denominator[$zone_name];
   // print_r($facilities);exit;
    $facility_ids = implode(",",$facilities);
    
   $report_rates = $this->getAllFacilitiesReportRate(1,$zone_id,$date_format); //$this->get_all_facilities_reporte_rates($facility_ids,$date_format);
  
   
   $totalFacilities = sizeof($facilities);
  
   
    $reportRateZonePercent = round(($report_rates/$totalFacilities) * 100,2);
     if($totalFacilitiesProv==0){
        $reportRateProvLGAPercent = 0;
    }else{
    $reportRateProvZonePercent = round(($reportRatesProv/$totalFacilitiesProv)* 100, 2);
    }
//    
//
$zoneArray = array();
$zoneArray['id'] = $zone_id;
$zoneArray['name'] = $zone_name;
$zoneArray['parent_id'] = 0;
$zoneArray['allfac'] = number_format($totalFacilities);
$zoneArray['facrep'] = number_format($report_rates);
$zoneArray['allfacrrate'] = $reportRateZonePercent;
$zoneArray['allfacprovfp'] = number_format($totalFacilitiesProv);
$zoneArray['facprovfprep'] = number_format($reportRatesProv);
$zoneArray['facprovfprrate'] = $reportRateProvZonePercent;
$zoneArray['states'] = array();


//$rrateArray[$zone_id]['states'] = array();
    
// $displayMessage.=' <h3 align=""><span class="tableft">'.$zone_name.':</span><span class="tabright"><div id="resultd">'.$totalFacilities.'</div><div id="resultd">'.$report_rates.'</div><div id="resultd">'.$reportRateZonePercent.'%</div><div id="resultd">'.$totalFacilitiesProv.'</div><div id="resultd">'.$reportRatesProv.'</div><div id="resultd">'.$reportRateProvZonePercent.'%</div></span>&nbsp;&nbsp;</h3>'
//         . '<div class="accordion1">';
//  //  echo '<li><a href=#"><span></span>'.$zone_name.'::'.$report_rates.'</a>';
    $states = $this->get_location_category_unique("state",$zone_id);
   // echo '<ul>';
    foreach($states as $state){
        $state_name = $state['state'];
        $state_id = $state['state_id'];
        list($result,$facilities) = $this->get_all_facilities_with_location("state",$state_id);
        list($numerator,$denominator) = $this->getAllFacilitiesProvFPReportingRatesSpecial(2,$state_id,$date_format);
        $reportRatesProv = $numerator[$state_name];
        $totalFacilitiesProv = $denominator[$state_name];
        
    $facility_ids = implode(",",$facilities);
    
    $report_rates_state = $this->getAllFacilitiesReportRate(2,$state_id,$date_format); //$this->get_all_facilities_reporte_rates($facility_ids,$date_format);
   
    
    $totalFacilities = sizeof($facilities);
   
    
    $reportRateStatePercent = round(($report_rates_state/$totalFacilities) * 100,2);
     if($totalFacilitiesProv==0){
        $reportRateProvLGAPercent = 0;
    }else{
    $reportRateProvStatePercent = round(($reportRatesProv/$totalFacilitiesProv)* 100, 2);
    }
    $stateArray = array();
$stateArray['id'] = $state_id;
$stateArray['name'] = $state_name;
$stateArray['parent_id'] = $zone_id;
$stateArray['allfac'] = number_format($totalFacilities);
$stateArray['facrep'] = number_format($report_rates_state);
$stateArray['allfacrrate'] = $reportRateStatePercent;
$stateArray['allfacprovfp'] = number_format($totalFacilitiesProv);
$stateArray['facprovfprep'] = number_format($reportRatesProv);
$stateArray['facprovfprrate'] = $reportRateProvStatePercent;
$stateArray['lga'] = array();
//$rrateArray[$zone_id]['states'][$state_id] =  $rowData;
////print_r($rrateArray);exit;
//    
//    $displayMessage.='<h3 align=""><span class="tabstateleft" >'.$state_name.':</span></span><span class="tabright"><div id="resultd">'.$totalFacilities.'</div><div id="resultd">'.$report_rates_state.'</div><div id="resultd">'.$reportRateStatePercent.'%</div><div id="resultd">'.$totalFacilitiesProv.'</div><div id="resultd">'.$reportRatesProv.'</div><div id="resultd">'.$reportRateProvStatePercent.'%</div></span></h3>';
//        //echo '<li><a href="#"><span></span>'.$state_name.'::'.$report_rates_state.'</a>';
//       // echo '<ul>';
//    
//   $displayMessage.='<div class="accordion2">';
    $lgas = $this->get_location_category_unique("lga",$state_id);
    foreach($lgas as $lga){
        
        $lga_name = $lga['lga'];
        $lga_id = $lga['lga_id'];
       list($result,$facilities) = $this->get_all_facilities_with_location("lga",$lga_id);
       list($numerator,$denominator) = $this->getAllFacilitiesProvFPReportingRatesSpecial(3,$lga_id,$date_format);
//       echo $lga_name.'<br/>';
//       print_r($numerator);
//       print_r($denominator);
//    
         $reportRatesProv = $numerator[$lga_name];
        $totalFacilitiesProv = $denominator[$lga_name];
//        echo $reportRatesProv." ".$totalFacilitiesProv;
//         echo '<br/><br/><br/>';
       list($resultProv,$allFacilitiesProv) = $this->getAllFacilitiesProvidingFPWithLocation("lga",$lga_id,$date_format);
       
       $totalFacilities = sizeof($facilities);
       //$totalFacilitiesProv = sizeof($allFacilitiesProv);
       
    $facility_ids = implode(",",$facilities);
    
    $report_rates_lga = $this->getAllFacilitiesReportRate(3,$lga_id,$date_format); //$this->get_all_facilities_reporte_rates($facility_ids,$date_format);
  
     
    $reportRateLgaPercent = round(($report_rates_lga/$totalFacilities) * 100,2);
    if($totalFacilitiesProv==0){
        $reportRateProvLGAPercent = 0;
    }else{
    $reportRateProvLGAPercent = round(($reportRatesProv/$totalFacilitiesProv)* 100, 2);
    }
    $lgaArray = array();
$lgaArray['id'] = $lga_id;
$lgaArray['name'] = $lga_name;
$lgaArray['parent_id'] = $state_id;
$lgaArray['allfac'] = number_format($totalFacilities);
$lgaArray['facrep'] = number_format($report_rates_lga);
$lgaArray['allfacrrate'] = $reportRateLgaPercent;
$lgaArray['allfacprovfp'] = number_format($totalFacilitiesProv);
$lgaArray['facprovfprep'] = number_format($reportRatesProv);
$lgaArray['facprovfprrate'] = $reportRateProvLGAPercent;
$lgaArray['facilities'] = array();
//$rrateArray[] =  $rowData;
//    print_r($rrateArray);
//    continue;
//     $displayMessage.='<h4 align=""><span class="tabstateleft" >'.$lga_name.':</span></span><span class="tabright"><div id="resultd">'.$totalFacilities.'</div><div id="resultd">'.$report_rates_lga.'</div><div id="resultd">'.$reportRateLgaPercent.'%</div><div id="resultd">'.$totalFacilitiesProv.'</div><div id="resultd">'.$reportRatesProv.'</div><div id="resultd">'.$reportRateProvLGAPercent.'%</div></span></h4>';
//   $displayMessage .= '<div>';
      //  $displayMessage.= '<p><span class="tableft">'.$lga_name.':</span><span class="tabright">'.$reportRateLgaPercent.'%</span></p>'; 
//       $counter = 0;


//        foreach($result as $facData){
//           $facilityID = $facData['id'];
//           $facilityName = $facData['facility_name'];
//           $counter  = $this->get_all_facilities_reporte_rates($facilityID,$date_format);
//           $allFacilities = "Yes";
//           $faciityReportingAll = "";
//           if($counter<=0){
//             $faciityReportingAll = "No";
//           }else{
//             $faciityReportingAll = "Yes";
//           }
//            $facProv = (in_array($facilityID,$allFacilitiesProv))?"Yes":"No";
//            $reportRatesProv = $this->getAllFacilitiesProvFPReportingRates($facilityID,$date_format);
//            $facprovReport = ($reportRatesProv>0)?"Yes":"No";
//            
//                $rowData = array();
//$rowData['id'] = $facilityID;
//$rowData['parent_id'] = $lga_id;
//$rowData['name'] = $facilityName;
//$rowData['allfac'] = $allFacilities;
//$rowData['facrep'] = $faciityReportingAll;
//$rowData['allfacrrate'] = "";
//$rowData['allfacprovfp'] = $facProv;
//$rowData['facprovfprep'] = $facprovReport;
//$rowData['facprovfprrate'] = "";
//
//$lgaArray['facilities'][] = $rowData;
////         // $displayMessage .= '<p style="width:80% !important; float:left !important"><span class="tableft">'.$facilityName.':</span><span class="tabright"> <b>Yes</b> fac--> <b>'.$faciityReportingAll.'</b></span></p>';
////          //$displayMessage.= '<p><span class="tableft">'.$facilityName.':</span><span class="tabright">'.$allFacilities.' '.$faciityReportingAll.' &nbsp;&nbsp'.$facProv.' '.$facprovReport.' &nbsp;&nbsp;</span></p>'; 
////          $displayMessage.= '<h5><span class="tableft">'.$facilityName.':</span><span class="tabright"><div id="resultd">'.$allFacilities.'</div><div id="resultd">'.$faciityReportingAll.'</div><div id="resultd">&nbsp;&nbsp;</div><div id="resultd">'.$facProv.'</div><div id="resultd">'.$facprovReport.'</div><div id="resultd">&nbsp;&nbsp;</div></span></h5>'; 
////        
//          
//           }
//           
           
           $stateArray['lga'][] = $lgaArray;
       //$displayMessage.= '</div>'; 
    }
    $zoneArray['states'][] = $stateArray;
  //$displayMessage.= '</div>';
      
    }
   $rrateArray[] = $zoneArray;
   // $displayMessage.= '</div>';
    
}

//
//for($i=0;$i<sizeof($rrateArray); $i++){
//    
//    //this is for the national and zones
//   $name = $rrateArray[$i]['name'];
//   $allfac = $rrateArray[$i]['allfac'];
//   $facrep = $rrateArray[$i]['facrep'];
//   $allfacrrate = $rrateArray[$i]['allfacrrate'];
//   $allfacprovfp = $rrateArray[$i]['allfacprovfp'];
//   $facprovfprep = $rrateArray[$i]['facprovfprep'];
//   $facprovfprrate = $rrateArray[$i]['facprovfprrate'];
//   $zones = $rrateArray[$i]['states'];
//   
//  
//    
//   
//}
//$displayMessage.= '</div>';
 
//print_r($rrateArray);exit;
$this->view->assign('reporting_rate',json_encode($rrateArray));
$this->view->assign('date_format',$format);
        }
        public function rrateAction(){
             $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $facility = new Facility();
                $helper = new Helper2();
		$this->view->assign ( 'mode', 'search' );
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
$db = Zend_Db_Table_Abstract::getDefaultAdapter ();

$current_month = "03";
$current_year = date('Y');
//$date_format = $current_year.'-'.$current_month.'-'.'01';
$date_format = $helper->getLatestPullDate();
$time = strtotime($date_format);
$month = date('F',$time);
$year = date('Y',$time);
$format = "for $month, $year";
list($result,$allFacilitiesNational) = $this->get_all_facilities_with_location("","");

$facility_idsNational = implode(",",$allFacilitiesNational);
$reportRatesNational = $this->get_all_facilities_reporte_rates($facility_idsNational,$date_format);

$totalFacilities = sizeof($allFacilitiesNational);
$reportRateNationalPercent = round(($reportRatesNational/$totalFacilities) * 100,2);

$displayMessage ="";
  $displayMessage.= '<h1 align="left" style="color:green;margin-left:60px;width:50%;"><span style="width:70%;">National Reporting Rate:</span><span style="float:right;width:29%;">'.$reportRateNationalPercent.'% </span></h1>';

$displayMessage.= '<div id="accordion">';

$zones = $this->get_location_category_unique("zone");

foreach($zones as $zone){
    $zone_name = $zone['geo_zone'];
    $zone_id = $zone['geo_parent_id'];
   
    list($result,$facilities) = $this->get_all_facilities_with_location("zone",$zone_id);
  
    $facility_ids = implode(",",$facilities);
    
   $report_rates = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
  
   
   $totalFacilities = sizeof($facilities);
 
   
    $reportRateZonePercent = round(($report_rates/$totalFacilities) * 100,2);

    
 $displayMessage.=' <h3 align=""><span class="tableft">'.$zone_name.':</span><span class="tabright">'.$reportRateZonePercent.'%</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3>
<div class="accordion1">';
  //  echo '<li><a href=#"><span></span>'.$zone_name.'::'.$report_rates.'</a>';
    $states = $this->get_location_category_unique("state",$zone_id);
   // echo '<ul>';
    foreach($states as $state){
        $state_name = $state['state'];
        $state_id = $state['state_id'];
        list($result,$facilities) = $this->get_all_facilities_with_location("state",$state_id);
       
        
    $facility_ids = implode(",",$facilities);
    
    $report_rates_state = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
    
    
    $totalFacilities = sizeof($facilities);
   
    
    $reportRateStatePercent = round(($report_rates_state/$totalFacilities) * 100,2);
    

    
    $displayMessage.='<h3 align=""><span class="tabstateleft" >'.$state_name.':</span></span><span class="tabright"> '.$reportRateStatePercent.'%</span></h3>';
       
    
   $displayMessage.='<div>';
    $lgas = $this->get_location_category_unique("lgs",$state_id);
    foreach($lgas as $lga){
        $lga_name = $lga['lga'];
        $lga_id = $lga['lga_id'];
       list($result,$facilities) = $this->get_all_facilities_with_location("lga",$lga_id);
       
       $totalFacilities = sizeof($facilities);
       
       
    $facility_ids = implode(",",$facilities);
    
    $report_rates_lga = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
   
     
    $reportRateLgaPercent = round(($report_rates_lga/$totalFacilities) * 100,2);

        $displayMessage.= '<p><span class="tableft">'.$lga_name.':</span><span class="tabright">'.$reportRateLgaPercent.'%</span></p>'; 
      
    }
  $displayMessage.= '</div>';
      
    }
   
    $displayMessage.= '</div>';
    
}


$displayMessage.= '</div>';
 

$this->view->assign('reporting_rate',$displayMessage);
$this->view->assign('date_format',$format);
        }
        public function importAction(){
            $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
if ( $this->getSanParam('download') )
			return $this->download();
		
		$this->view->assign ( 'mode', 'search' );
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                
                 $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                 $status = ValidationContainer::instance();
                 $filename = "";
                 $stat = "";
                 if(isset($_FILES['upload']['tmp_name'])){
                    $filename = ($_FILES['upload']['tmp_name']);
                 }
		if ( $filename!="" ){
			$rows = $this->_excel_parser($filename,1);
                       
                        $json = json_encode($rows);
                        
                       // $result = true;
                       // print_r($json);
   
  //$result = true;
                        
                       $result =  $this->update_json_file($json);
                       if($result){
                         $stat = t ('Your changes have been saved. The new DHS Data has been uploaded to the database');
                       }else{
                          $stat =  t("Unable to update DHS database. Make sure file content is not the same as the one already in the database"); 
                       }
                }else{
                $errs[] = "Select the DHS Static Excel File";
                }
                
                foreach($errs as $errmsg){
								$stat .= '<br>'.''.htmlspecialchars($errmsg, ENT_QUOTES);
        }                                                          
								$status->setStatusMessage($stat);
								$this->view->assign('status',$status);
                
                
        }
        public function infoAction(){
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();

		$this->view->assign ( 'mode', 'search' );
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');

                $file = $this->get_json_file_from_db();
                $json_array = json_decode($file,true);
            //starting from index 5
                $this->view->assign('json_array',$json_array);
}

        public function userguideAction(){
            
        }
        public function update_json_file($json){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $data = array(
            'json'      => $json

        );


        $result = $db->update('dhs_static_data', $data, 'id = 1');
        return $result;
        }
        public function get_json_file_from_db(){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
         $sql = "SELECT * FROM `dhs_static_data` WHERE id='1'";
         $result = $db->fetchAll($sql);
         $file = $result[0]['json'];
        return $file;
        }
        public function get_location_category_unique($category,$condition=""){
              if($category=="zone"){
                $needle = "geo_parent_id,geo_zone";
                $condi = "";
                $name = "geo_zone";
            }else if($category=="state"){
                $needle = "state_id,state";
                $name = "state";
                $condi = "WHERE geo_parent_id='$condition'";
            }else{
                $needle = "lga_id,lga";
                $name = "lga";
                $condi = "WHERE state_id='$condition'";
            }

            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $sql = "SELECT DISTINCT  ".$needle." FROM facility_location_view ".$condi."  ORDER BY `$name` ASC";
          // echo $sql;exit;
            $result = $db->fetchAll($sql);
            return $result;

        }

        public function getAllFacilitiesReportRate($tierValue,$geoList,$date_format){
            $helper = new Helper2();
            $tierText = $helper->getLocationTierText($tierValue);
            $tierFieldName = $helper->getTierFieldName($tierText);
            
            
            if($geoList!=''){
                $locationWhere = ' AND '.$tierFieldName . ' IN (' . $geoList . ')';
            }
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $sql = "SELECT COUNT(*) as counter FROM facility_report_rate as frr LEFT JOIN facility_location_view  as flv ON flv.id = frr.facility_id WHERE  frr.date ='$date_format' $locationWhere";
            
            $result = $db->fetchAll($sql);
            //print_r($result);exit;
            return $result[0]['counter'];
            
            }
        public function get_all_facilities_reporte_rates($facility_ids,$date_format){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $sql = "SELECT COUNT(*) as counter FROM facility_report_rate WHERE facility_id IN (".$facility_ids.") AND date='$date_format'";

            $result = $db->fetchAll($sql);
            //print_r($result);exit;
            return $result[0]['counter'];

        }
        
        public function getAllFacilitiesProvFPReportingRates($facility_ids,$date_format){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            
            $sql = "SELECT COUNT(DISTINCT(c.facility_id)) as counter FROM commodity as c LEFT JOIN commodity_name_option as cno ON cno.id = c.name_id WHERE (cno.commodity_type = 'fp' OR cno.commodity_type = 'larc') AND c.date  = '$date_format' AND c.facility_id IN ($facility_ids) AND facility_reporting_status = 1";

            $result = $db->fetchAll($sql);
            //print_r($result);exit;
            return $result[0]['counter'];

        }
        
        public function getAllFacilitiesProvFPReportingRatesDem($facilityID,$date_format){
              $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
             $helper = new Helper2();
                  
                    $tierFieldName = 'c.facility_id';
                    $tierText = 'c.facility_id';
                    $dateWhere = "c.date = '$date_format'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $consumptionWhere = 'consumption > 0';
                    $locationWhere = 'c.facility_id  IN (' . $facilityID . ')';
                    
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";
                    
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                  $select = $db->select()
                            ->from(array('c' => 'commodity'),
                              array('COUNT(DISTINCT(c.facility_id)) AS fid_count'))
                            ->joinInner(array('cno' => 'commodity_name_option'), 'cno.id = c.name_id', array())
                            ->joinInner(array('flv' => 'facility_location_view'), 'flv.id = c.facility_id', array('lga', 'state',  'geo_zone','location_id'))
                            ->where($longWhereClause)
                            ->group('c.facility_id');
                  
                   $result = $db->fetchAll($select);
                   print_r($result);
                             
        }
        
        public function getAllFacilitiesProvFPReportingRatesSpecial($tierValue,$geoList,$date_format){
                    $helper = new Helper2();
                    $tierText = $helper->getLocationTierText($tierValue);
                    $tierFieldName = $helper->getTierFieldName($tierText);
                    
                    $dateWhere = "c.date = '$date_format'";
                    $reportingWhere = 'facility_reporting_status = 1';
                    $consumptionWhere = 'consumption > 0';
                    $locationWhere = $tierFieldName . ' IN (' . $geoList . ')';
                    
                    $ct_where = "(commodity_type = 'fp' OR commodity_type = 'larc')";

                    $coverageHelper = new CoverageHelper();
                    $longWhereClause = $reportingWhere . ' AND ' . $dateWhere . ' AND ' . 
                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                    $numerators = $coverageHelper->getFacProvidingCount($longWhereClause, $geoList, $tierText, $tierFieldName);

                     $longWhereClause = $dateWhere . ' AND ' . 
                                       $consumptionWhere . ' AND ' . $ct_where . ' AND ' . $locationWhere;
                    $denominators = $coverageHelper->getFacProvidingCount($longWhereClause, $geoList, $tierText, $tierFieldName);
                    return array($numerators,$denominators);
              
                // exit;
        }
        
       
        public function getAllFacilitiesProvidingFPWithLocation($category,$id,$date_format){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $whereClause = "";
            $whereClauseArray[] = "id IN (SELECT c.facility_id FROM commodity as c LEFT JOIN commodity_name_option as cno ON cno.id = c.name_id WHERE (cno.commodity_type = 'fp' OR cno.commodity_type = 'larc') AND c.date  = '$date_format')";
            
            if($category=="zone"){
                $needle = "geo_parent_id";
                $whereClauseArray[] = "`$needle`='$id'";
            }else if($category=="state"){
                $needle = "state_id";
                $whereClauseArray[] = "`$needle`='$id'";
            }else if($category=="lga"){
                $needle = "lga_id";
                $whereClauseArray[] = "`$needle`='$id'";
            }
            else{
               //$whereClauseArray[] = "";
            }
            $whereList = implode("AND",$whereClauseArray);
            $whereClause = "WHERE $whereList";
            
            $sql  = "SELECT id,facility_name FROM facility_location_view ".$whereClause." ";
           // echo $sql;exit;
            $result = $db->fetchAll($sql);
            $facilities = array();
            foreach($result as $facility){
                $facility_id = $facility['id'];
                $facilities[] = $facility_id;
            }
            
            return array($result,$facilities);
        }
        
        
        
        public function get_all_facilities_with_location($category,$id){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            if($category=="zone"){
                $needle = "geo_parent_id";
                $whereClause = "WHERE `$needle`='$id'";
            }else if($category=="state"){
                $needle = "state_id";
                $whereClause = "WHERE `$needle`='$id'";
            }else if($category=="lga"){
                $needle = "lga_id";
                $whereClause = "WHERE `$needle`='$id'  ";
            }
            else{
               $whereClause = "";
            }

            $sql  = "SELECT id,facility_name FROM facility_location_view ".$whereClause."";
            $result = $db->fetchAll($sql);
            $facilities = array();
            foreach($result as $facility){
                $facility_id = $facility['id'];
                $facilities[] = $facility_id;
            }
            return array($result,$facilities);
        }

        public function downloaduserguideAction(){
            
             header('Content-Type: application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
 			header('Content-Disposition: attachment; filename="user_guide.pdf"');
 			header("Content-Type: application/force-download");
 			readfile(Globals::$BASE_PATH . '/html/user_guide.pdf');
  			$this->view->layout()->disableLayout();
         	$this->_helper->viewRenderer->setNoRender(true);
        }
        public function download(){
            header('Content-Type: application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                                header('Content-Disposition: attachment; filename="Dashboard_static_page_indicators.xlsx"');
                                header("Content-Type: application/force-download");
                                readfile(Globals::$BASE_PATH . '/html/templates/Dashboard_static_page_indicators.xlsx');
                                $this->view->layout()->disableLayout();
                        $this->_helper->viewRenderer->setNoRender(true);
        }

        public function definitionsPageAction(){

        }
}
?>
