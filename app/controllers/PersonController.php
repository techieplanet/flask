<?php
/*
 * Created on Feb 27, 2008
 *
 *  Built for web
 *  Fuse IQ -- todd@fuseiq.com
 *
 */
require_once ('ReportFilterHelpers.php');
require_once ('models/table/OptionList.php');
require_once ('models/table/Person.php');
require_once ('models/table/User.php');
require_once ('models/table/Location.php');
require_once ('models/table/Facility.php');
require_once ('models/table/Training.php');
require_once ('models/table/TrainingRecommend.php');
require_once ('models/table/Trainer.php');
require_once("controllers/libs/simplexlsx.class.php"); //TP importing the simplexlsx.class.php which is to be used in reading our excel sheet later in the class import section of this code
require_once ('models/table/MultiOptionList.php');
require_once ('Zend/Validate/EmailAddress.php');
require_once ('Zend/Mail.php');
require_once('models/table/Helper.php');
require_once('models/table/Helper2.php');
require_once('models/table/Report.php');

class PersonController extends ReportFilterHelpers {

	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		parent::__construct ( $request, $response, $invokeArgs );
	}

	public function init() {
            parent::init();
	}

	public function preDispatch() {
		parent::preDispatch ();

		if (! $this->isLoggedIn ())
		$this->doNoAccessError ();

	}

	public function indexAction() {
		return $this->_redirect ( 'person/search' );
	}

	public function approveAction() {
		if (! $this->hasACL ( 'facility_and_person_approver' ) && ! $this->hasACL('edit_country_options') ) {
			$this->doNoAccessError ();
		}

		$user_id = $this->getSanParam( 'id' );
		$status = ValidationContainer::instance ();
		$user = new Person();
		$user_row = $user->find( $user_id )->current();

		if($user_row == null) {
			$status->setStatusMessage ( t( 'Error approving person: That account could not be found.' ) );
			$this->_redirect( 'admin/people-new-people' );
			return;
		}

		$user_row->approved = 1;
		$user_row->save();
		$status->setStatusMessage ( t( 'That person has been approved.' ) );
		$this->_redirect( 'admin/people-new-people' );
	}

	public function deleteAction() {
		if (! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$status = ValidationContainer::instance ();
		$id = $this->getSanParam ( 'id' );

		if ($id and ! Person::isReferenced ( $id )) {
			$trainer = new Trainer ( );
			$rows = $trainer->find ( $id );
			$row = $rows->current ();
			if ($row) {
				$trainer->delete ( 'person_id = ' . $row->person_id );
			}
			$person = new Person ( );
			$rows = $person->find ( $id );
			$row = $rows->current ();
			if ($row) {
				$person->delete ( 'id = ' . $row->id );
			}
			$status->setStatusMessage ( t ( 'That person was deleted.' ) );
		} else if (! $id) {
			$status->setStatusMessage ( t ( 'That person could not be found.' ) );
		} else {
			$status->setStatusMessage ( t ( 'That person is an active').' '.t('participant').' '.t('or').' '.t('trainer').' '.t( 'and could not be deleted.' ) );
		}

		//validate
		$this->view->assign ( 'status', $status );

	}

	public function viewAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
                        
		}

		if ($this->hasACL ( 'edit_people' )) {
			//redirect to edit mode
			$this->_redirect ( str_replace ( 'view', 'edit', 'http://' . $_SERVER ['SERVER_NAME'] . $_SERVER ['REQUEST_URI'] ) );
		}

		$this->view->assign ( 'mode', 'view' );
		$rtn = $this->doAddEditView ();
		return $rtn;
	}

	public function lhAction() {
		if (! $this->isLoggedIn ())
		$this->doNoAccessError ();

		if (! $this->hasACL ( 'add_edit_users' )) {
			$this->doNoAccessError ();
		}
		$person_id = $this->getSanParam ( 'id' );
		$this->view->assign ( 'person_id', $person_id );
		$s = $this->getSanParam ( 's' );
		$this->view->assign ( 's', $s );
		$e = $this->getSanParam ( 'e' );
		$this->view->assign ( 'e', $e );
		$this->view->assign ( 'mode', 'view' );
	}

	public function addAction() {
		if (! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}

		$this->view->assign ( 'mode', 'add' );
                
		$rtn = $this->doAddView ();
		return $rtn;
	}

	public function editcompetencyAction(){
		$person_id	= $this->getSanParam ( 'id' );
		$helper		= new Helper();
		$request	= $this->getRequest ();
		if ($request->isPost ()) {
			$helper->saveCompetencyAnswers($this->getSanParam ('question'), $person_id);
			$this->_redirect (  'person/edit/id/' . $person_id );
		}


		$person_id = $this->getSanParam ( 'id' );
		$this->view->assign ( 'person_id', $person_id );

		$comps = $helper->getPersonCompetenciesDetailed($person_id);
		$this->view->assign ( 'comps', $comps );
	}

	public function viewcompetencyAction(){
		$person_id = $this->getSanParam ( 'id' );
		$this->view->assign ( 'person_id', $person_id );

		$helper = new Helper();
		$comps = $helper->getPersonCompetenciesDetailed($person_id);
		$this->view->assign ( 'comps', $comps );
	}

	public function addcompAction() {
		$this->doRaman();
	}

	public function addcomcAction() {
		$this->doRaman();
	}

	public function addcomnAction() {
		$this->doRaman();
	}

	public function addcomdAction() {
		$this->doRaman();
	}

	public function viewcompAction() {
		$this->doRaman();
	}

	public function viewcomcAction() {
		$this->doRaman();
	}

	public function viewcomnAction() {
		$this->doRaman();
	}

	public function viewcomdAction() {
		$this->doRaman();
	}

	public function doRaman() {

		if (! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$id = $this->getSanParam ( 'id' );
		if ($id) {
			$person = new Person ( );
			$rows = $person->find ( $id );
			$row = $rows->current ();
			if ($row) {
				$this->view->assign ( 'mode', 'ok' );
				$status = ValidationContainer::instance ();
				$request = $this->getRequest ();
				$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
				if ($request->isPost()) {
					$ids1=split(",",$this->getSanParam ( 'ids1' ));
					$ids2=split(",",$this->getSanParam ( 'ids2' ));
					$ids3=split(",",$this->getSanParam ( 'ids3' ));
					$ids4=split(",",$this->getSanParam ( 'ids4' ));
					$vwvls=array();
					$sqls=array();
					$vwlscntr=0;
					$tmpvl;
					$complete=true;
					foreach ( $ids1 as $kys => $vls ) {
						$tmpvl=$this->getSanParam ( 'grp'.$vls );
						if(isset($tmpvl))
						{
							if(strlen($tmpvl)>0)
							{
								$vwvls[$vwlscntr]="".$vls.$tmpvl;
								if($tmpvl=="F")
								$complete=false;
								$sqls[$vwlscntr]='INSERT INTO `comp` ( `person` , `question` , `option` , `active` ) VALUES('.$id.', \''.$vls.'\', \''.$tmpvl.'\', \'Y\');';
								$vwlscntr++;
							}
						}
					}
					foreach ( $ids2 as $kys => $vls ) {
						$tmpvl=$this->getSanParam ( 'grp'.$vls );
						if(isset($tmpvl))
						{
							if(strlen($tmpvl)>0)
							{
								$vwvls[$vwlscntr]="".$vls.$tmpvl;
								$sqls[$vwlscntr]='INSERT INTO `comp` ( `person` , `question` , `option` , `active` ) VALUES('.$id.', \''.$vls.'\', \''.$tmpvl.'\', \'Y\');';
								$vwlscntr++;
							}
						}
					}
					foreach ( $ids3 as $kys => $vls ) {
						$tmpvl=$this->getSanParam ( 'grp'.$vls );
						if(isset($tmpvl))
						{
							if(strlen($tmpvl)>0)
							{
								$vwvls[$vwlscntr]="".$vls.$tmpvl;
								$sqls[$vwlscntr]='INSERT INTO `comp` ( `person` , `question` , `option` , `active` ) VALUES('.$id.', \''.$vls.'\', \''.$tmpvl.'\', \'Y\');';
								$vwlscntr++;
							}
						}
					}
					foreach ( $ids4 as $kys => $vls ) {
						$tmpvl=$this->getSanParam ( 'grp'.$vls );
						if(isset($tmpvl))
						{
							if(strlen($tmpvl)>0)
							{
								$vwvls[$vwlscntr]="".$vls.$tmpvl;
								$sqls[$vwlscntr]='INSERT INTO `comp` ( `person` , `question` , `option` , `active` ) VALUES('.$id.', \''.$vls.'\', \''.$tmpvl.'\', \'Y\');';
								$vwlscntr++;
							}
						}
					}
					$this->view->assign ( 'vwvls', $vwvls );
					foreach ( $ids1 as $kys => $vls ) {
						$status->checkRequired ( $this, 'grp'.$vls, '' );
						if ($status->hasError ()) {
							$status->changeError ( 'grp'.$vls, '<span style="color:Red">Answer all questions.</span>' );
							break;
						}
					}
					foreach ( $ids2 as $kys => $vls ) {
						$status->checkRequired ( $this, 'grp'.$vls, '' );
						if ($status->hasError ()) {
							$status->changeError ( 'grp'.$vls, '<span style="color:Red">Answer all questions.</span>' );
							break;
						}
					}
					if ($status->hasError ()) {
						$status->setStatusMessage ( t ( 'Data cannot be saved.' ) );
						$this->view->assign ( 'status', $status );
						return;
					} else {
						$db->query ( 'UPDATE `comp` SET `Active`=\'N\' WHERE `person`='.$id.' AND `Active`=\'Y\';' );
						foreach ( $sqls as $kys => $vls ) {
							$db->query ( $vls );
						}
						$db->query ( 'UPDATE `compres` SET `Active`=\'N\' WHERE `person`='.$id.' AND `Active`=\'Y\';' );
						if($complete==true)
						$db->query ( 'INSERT INTO `compres` ( `person` , `res` , `active` ) VALUES('.$id.', 1, \'Y\');' );
						else
						$db->query ( 'INSERT INTO `compres` ( `person` , `res` , `active` ) VALUES('.$id.', 0, \'Y\');' );
						$status->setStatusMessage ( t ( 'Data saved.' ) );
						$status->setRedirect ( '/person/search' );
						$this->view->assign ( 'status', $status );
					}
				}
				$rows = $db->fetchAll ( 'SELECT CONCAT(`question`,`option`) `A` FROM `comp` WHERE `person`='.$id.' AND `Active`=\'Y\' ORDER BY id ASC;' );
				$nvwvls=array();
				$nvwlscntr=0;
				foreach ( $rows as $rw ) {
					$nvwvls[$nvwlscntr]=$rw['A'];
					$nvwlscntr++;
				}
				$this->view->assign ( 'vwvls', $nvwvls );
				$perid=array();
				$perid['id']=$id;
				$this->view->assign ( 'person', $perid );
				$this->view->assign ( 'vis', 'none' );
			}
			else {
				$this->view->assign ( 'mode', 'no' );
				$perid=array();
				$perid['id']='0';
				$this->view->assign ( 'person', $perid );
				$this->view->assign ( 'vis', 'none' );
			}
		} else {
			$this->view->assign ( 'mode', 'no' );
			$perid=array();
			$perid['id']='0';
			$this->view->assign ( 'person', $perid );
			$this->view->assign ( 'vis', 'none' );
		}
	}

	public function editAction() {
		if (! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}

		$this->view->assign ( 'mode', 'edit' );
		$rtn = $this->doAddEditView();
		return $rtn;
	}
        public function clean($clear){
            $clear = strip_tags($clear);
// Clean up things like &amp;
$clear = html_entity_decode($clear);
// Strip out any url-encoded stuff
$clear = urldecode($clear);
// Replace non-AlNum characters with space
$clear = preg_replace('/[^A-Za-z0-9]/', '', $clear);
// Replace Multiple spaces with single space
$clear = preg_replace('/ +/', ' ', $clear);
// Trim the string of leading/trailing space
$clear = trim($clear);
return $clear;
            
        }
        public function editPerson(){
          // echo date_default_timezone_get();exit;
           
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $select = $db->select()
                    ->from(array('p' => 'person'),array('id', 'first_name','middle_name','last_name'));
            $rows = $db->fetchAll($select);
           //$rows = file_get_contents("personData.txt");
           //$rows = json_decode($rows);
           
           //$rows = file_get_contents("personData.txt");
            //$rows = $rows->toArray();
           foreach($rows as $row){
                $first_name = $row['first_name'];
                $middle_name = $row['middle_name'];
                $last_name = $row['last_name'];
                $row['first_name'] = ucfirst(strtolower($this->clean($first_name)));
                $row['middle_name'] = ucfirst(strtolower($this->clean($middle_name)));
                
                $row['last_name'] = ucfirst(strtolower($this->clean($last_name)));
                $id = $row['id'];
              
             //$update = $db->update("person", $row, $id);
              
                   /* $row['first_name'] = "A";
                    $row['middle_name'] = "O";
                    $row['last_name'] = "Ojobe";
                   */
             $update = $db->update('person', $row, 'id = '.$id.'');
           
                
             //$db->query($update);
             /*if($id==867){
                 Helper2::jLog("This is the initial data ".$last_name."this is the datae");
                 Helper2::jLog("This is the final data ".$row['last_name']."this is the datae");
                //Helper2::jLog(print_r($row,true));
             }*/
               
                //Helper2::jLog(print_r($row,true));
           }
           
        }
        
	public function doAddEditView() {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
	try {
                $this->editPerson();
		//validate
		$status = ValidationContainer::instance ();

		require_once ('models/table/OptionList.php');
		require_once ('models/table/MultiOptionList.php');
		require_once ('models/table/Location.php');

		$request = $this->getRequest ();
		$validateOnly = $request->isXmlHttpRequest ();
                $personObj = new Person();
		$person_id = $this->getSanParam ('id');
                $facility = new Facility();
                
                if($person_id){ //edit mode
                    $this->view->assign ( 'mode', 'edit' );
                  $personDetails = $personObj->getPersonName($person_id);
                  $personDetails =  $personDetails->toArray();
                 // Helper2::jLog(print_r($personDetails,true));
                  $personIsDeleted = $personDetails['is_deleted'];
                  if($personIsDeleted ==1 || $personIsDeleted=="1"){
                      $this->view->assign('is_deleted',true);
                      return;
                  }
                   $userfacility_id = $personDetails['facility_id'];
                   $facility_idPerson = $facility->selectUniqueFacility($userfacility_id);
                   
                   
                   $LGA_idPersonData  = (isset($facility_idPerson[0]['lga_id']))?$facility_idPerson[0]['lga_id']:"";
                   $STATE_idPersonData  = (isset($facility_idPerson[0]['state_id']))?$facility_idPerson[0]['state_id']:"";
                   $ZONE_idPersonData  = (isset($facility_idPerson[0]['geo_zone_id']))?$facility_idPerson[0]['geo_zone_id']:"";
                   
                   
                   $this->view->assign('facility_name',$facility_idPerson[0]['facility_name']);
                   $this->view->assign('facility_id',$facility_idPerson[0]['id']);
                   
                   //echo $location_idPersonData;
                 // print_r($facility_idPerson);
                // exit;
                 // echo '<br/><br/>';echo $facility_id;
                  // echo ' '.$facilityidPerson[0];
                  // exit;
                   $allPersonStatus = $personObj->getAllPersonStatus();
                   $this->viewAssignEscaped('person_status',$allPersonStatus);
                   
                $user = new User();
                
                $new_locations = array();
                $newLocation = "";
                if(!$user->UserAccessRoleAllowed()){
                  
                   $new_locations = Location::userAccessLocations();
                    //$newLocation = implode(",",$new_locations);
                }else{
                  $this->view->assign('userRange',true);  
                }
                
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $sql = "select * from inactive_reason";
                $inactiveReasonArray = $db->fetchAll($sql);
                //Load the Inactive Records
                $this->view->assign('inactive', $personDetails['active']);
                $this->view->assign('inactive_reason', $personDetails['inactive_reason']);
                $this->view->assign('inactive_description', $personDetails['inactive_description']);
                $this->view->assign('inactive_Reasons',$inactiveReasonArray);
                
                //TP: check of the user is an LGA so as to enable the user to be able to use the inactive checkbox for the retired HW
                if($user->isUserAnLga()){
                    $this->view->assign('showInactive',false);
                }else{
                    $this->view->assign('showInactive',true);
                }
                
                
                if($user->is_user_an_admin())
                {
                    $this->view->assign("isAdmin",true);
                }
                
                //TP: for LGA user
                if($user->isUserAnLga()){
                    
                    if(in_array($LGA_idPersonData,$new_locations)){
                       // print_r($new_locations);
                         //echo $LGA_idPersonData;
                    $this->view->assign('userRange',true);
                    
                    
                }else{
                     $this->view->assign('userRange',false);
                     return;
                }
              }
              else if($user->isUserAPartner() || $user->isUserAState()){
                    
                     if(in_array($STATE_idPersonData,$new_locations)){
                         
                    $this->view->assign('userRange',true);
                }
                else{
                     $this->view->assign('userRange',false);
                     
                    return;
                }
               
                }
                //exit;
                
               
               /* else if(in_array($ZONE_idPersonData,$new_locations)){
                    $this->view->assign('userRange',true);
                }
                else{
                    $this->view->assign('userRange',false);
                    return;
                }
               */
                }
                else{
                    $this->view->assign('userRange',true);
                }
                //echo 'hello user exiested bit';
                //exit;
                
                
		
		$personrow = $personObj->findOrCreate ( $person_id );
		$personArray = $personrow->toArray ();

		$helper = new Helper();
		$ssl = $helper->getSkillSmartLookups();
		$this->view->assign('skillsmart', $ssl);

		// nationality dropdown data
		$this->view->assign('lookupnationalities', $helper->getNationalities());

		$train = $helper->getPersonTraining($person_id);
		$this->view->assign('persontraining',$train);

		//locations
		$locations = Location::getAll();
		$this->viewAssignEscaped ( 'locations', $locations );

		if ($validateOnly){
			$this->setNoRenderer ();
		}
		// Figure out reason code "Other" for checking posted data,
		// and for enabling/disabling reason_other field
		$other_reason_option_id = -1;
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT * FROM person_attend_reason_option where LCASE(attend_reason_phrase) LIKE '%other%'";
		$rowArray = $db->fetchAll ( $sql );
		if ($rowArray) {
			$other_reason_option_id = $rowArray[0]['id'];
		}
		$this->viewAssignEscaped ( 'other_reason_option_id', $other_reason_option_id );
                    $personData = array();
		if ($request->isPost ()) {

			$errortext = "";
			if ($dupe_id = $this->getSanParam ( 'dupe_id' )) {
				if ($this->getSanParam ( 'maketrainer' )) {
					// require user to add trainer info
					$status->setRedirect ( '/person/edit/id/' . $dupe_id . '/trainingredirect/' . $this->getSanParam ( 'trainingredirect' ) . '/maketrainer/1' );
				} else if ($this->_getParam ( 'trainingredirect' )) {
					$status->setStatusMessage ( t ( 'The person was saved. Refreshing history...' ) );
					$_SESSION['status'] = t ( 'The person was saved.' );
					$this->trainingRedirect ( $dupe_id );
				} else {
					$status->setRedirect ( '/person/edit/id/' . $dupe_id );
				}
				return;
			}

			$status->checkRequired ( $this, 'first_name', $this->tr ( 'First Name' ) );
			$status->checkRequired ( $this, 'last_name', $this->tr ( 'Surname' ) );
                        
                        if( $this->getSanParam('active')=="inactive"){
                        $status->checkRequired ( $this, 'inactive_reason', $this->tr ( 'Reason' ) );
			//$status->checkRequired ( $this, 'inactive_desc', $this->tr ( 'Description' ) );
                        }
			//$status->checkRequired ( $this, 'primary_qualification_option_id', t ( 'Professional qualification' ) );
// 			if ($this->setting('display_gender') != '0') //TA:17:16:
// 				$status->checkRequired ( $this, 'gender', t ( 'Gender' ) );

			if ($this->setting('display_mod_skillsmart')){
				//$status->checkRequired ( $this, 'occupational_category_id', t ( 'Occupational category' ) );
				if($this->getSanParam ( 'govemp_option_id' )) {
					//$status->checkRequired ( $this, 'govemp_option_id', t ( 'Government Employee' ) );
					//$status->checkRequired ( $this, 'occupational_category_id', t ( 'Occupational category' ) );
					$status->checkRequired ( $this, 'persal_number', t ( 'Persal Number' ) );
 				}
			} else {

				//$status->checkRequired ( $this, 'primary_qualification_option_id', t ( 'Professional qualification' ) );
			}

			$birthParam = (@$this->getSanParam ( 'birth-year' )) . '-' . (@$this->getSanParam ( 'birth-month' )) . '-' . (@$this->getSanParam ( 'birth-day' ));
			
                        
                        if ($birthParam !== '--' and $birthParam !== '0000-00-00')
			$status->isValidDate ( $this, 'birth-day', t ( 'Birthdate' ), $birthParam );


			//trainer only
			if ($this->getSanParam ( 'is_trainer' ) || $this->getSanParam ( 'active_trainer_option_id' ) || $this->getSanParam ( 'trainer_type_option_id' )) {
				$status->checkRequired ( $this, 'trainer_type_option_id', t('Trainer').' '.t('type' ) );
				if ($this->setting ( 'require_trainer_skill' ) ) {
					$status->checkRequired ( $this, 'trainer_skill_id', t ( 'Trainer').' '.t('skill' ) );
				}
			}

			// Check for manual reason for attending entry (if pulldown reason is 'other')
			if ( $this->setting('display_attend_reason')  ) {
				//if ($status->checkRequired ( $this, 'attend_reason_option_id', t ( 'Reason For Attending' ))) {
				$reason_id = $this->getSanParam ( 'attend_reason_option_id' );
				$other_reason = $this->getSanParam ( 'attend_reason_other' );
				if (($reason_id || $reason_id == 0) && $other_reason_option_id >= 0) // id zero is the 'Other' reason in the pulldown.
				{
					if ($reason_id == $other_reason_option_id) {
						if ($other_reason == "") {
							$status->addError ( 'attend_reason_other', t ( 'Enter a reason, or select a different reason above.' ) );
							$errortext .= "Enter a reason, or select a different reason above.<br>";
							error_log("Enter a reason, or select a different reason above.");
						}
					} else {
						if ($other_reason != "") {
							$status->addError ( 'attend_reason_other', t ( 'You can not type in a reason with the reason selected above.' ) );
						$errortext .= "You can not type in a reason with the reason selected above.<br>";
						error_log("You can not type in a reason with the reason selected above.");
						}
					}
				}
				//}
			}


			//check facility
			if ($status->checkRequired ( $this, 'facilityInput', t ( 'Facility' ) )) {
				$facility_id = $this->getSanParam ( 'facilityInput' );

				if( is_array($facility_id) ){
					$fac_arr = array();
					foreach($facility_id as $fac_id){
						if ($strrpos = strrpos ( $fac_id, '_' )) {
							$fac_arr[] = substr ( $fac_id, $strrpos + 1 );
						}
					}
					$personrow->multi_facility_ids = json_encode($fac_arr);

					$facility_id = current($facility_id);
				} else {
					$personrow->multi_facility_ids = '';
				}

				if ($strrpos = strrpos ( $facility_id, '_' )) {
                                        $facilityExplode = explode('_', $facility_id);
					$facility_id = array_pop($facilityExplode);
				}

				//find by name
				if ($facility_id) {
					$facilityByName = new Facility ( );
					$row = $facilityByName->fetchRow ( 'id = ' . $facility_id );
					//$row = $facilityByName->fetchRow($facilityByName->select()->where('facility_name = ?', $this->getSanParam('facilityInput')));
				}
				if (@$row->id) {
					$personrow->facility_id = $row->id;

				} else {
					$status->addError ( 'facilityInput', t ( 'That facility name could not be found.' ) );
					$errortext .= "That facility name could not be found.<br>";
					error_log("That facility name could not be found.");
				}

			}

			//get home city name
			$city_id = false;
			$criteria = $this->_getAllParams ();
			require_once 'views/helpers/Location.php';
			$home_city_parent_id = regionFiltersGetLastID('home', $criteria);
			if ( isset($criteria['home_city']) && !isset($criteria[ 'is_new_home_city' ]) ) {
				$city_id = Location::verifyHierarchy ( $criteria['home_city'], $home_city_parent_id, $this->setting('num_location_tiers') );
				if ( $city_id === false ) {
					$status->addError ( 'home_city', t ( "That city does not appear to be located in the chosen region. If you want to create a new city, check the new city box." ) );
					$errortext .= "That city does not appear to be located in the chosen region. If you want to create a new city, check the new city box.<br>";
					error_log("That city does not appear to be located in the chosen region. If you want to create a new city, check the new city box." );
				}
			}


			if ($status->hasError ()) {
				foreach ($status->messages as $k=>$v){
					$errortext .= $v . "<br>";
				}
				$status->setStatusMessage ( t ( 'The person could not be saved. <br>' . $errortext ) );
			} else {
                                
				$personrow = self::fillFromArray ( $personrow, $this->_getAllParams () );
				if ( ($city_id === false) && $this->getSanParam ( 'is_new_home_city' )) {
				$city_id = Location::insertIfNotFound ( $criteria['home_city'], $home_city_parent_id, $this->setting('num_location_tiers')  );
					if ($city_id === false)
					$status->addError ( 'home_city', t ( 'Could not save that city.' ) );
				}
				if ( $city_id ) {
					$personrow->home_location_id = $city_id;
				} else {
                                    if(isset($criteria['home_city'])){
                                        $home_location_id = Location::verifyHierarchy ( $criteria['home_city'], $home_city_parent_id, $this->setting('num_location_tiers') );

                                    }
				
					if ( isset($home_location_id))
					$personrow->home_location_id = $home_location_id;
				}
				//these are transitionary database fields, will go away soon
				//  $personrow->home_district_id = null;
				//  $personrow->home_province_id = null;
                                $personrow->timestamp_updated = date("Y-m-d H:i:s",time());
				if (!$personrow->home_city)
					$personrow->home_city = ''; // bugfix, field cannot be null.


				if ($this->getSanParam ( 'active' ))
				$personrow->active = 'active';
				else
				$personrow->active = 'inactive';

				$personrow->birthdate = (@$this->getSanParam ( 'birth-year' )) . '-' . (@$this->getSanParam ( 'birth-month' )) . '-' . (@$this->getSanParam ( 'birth-day' ));

				//lookup custom 1 and 2
				if ($this->getSanParam ( 'custom1Input' )) {
					$id = OptionList::insertIfNotFound ( 'person_custom_1_option', 'custom1_phrase', $this->getSanParam ( 'custom1Input' ) );
					$personrow->person_custom_1_option_id = $id;
				} else {
					$personrow->person_custom_1_option_id = null;
				}

				if ($this->getSanParam ( 'custom2Input' )) {
					$id = OptionList::insertIfNotFound ( 'person_custom_2_option', 'custom2_phrase', $this->getSanParam ( 'custom2Input' ) );
					$personrow->person_custom_2_option_id = $id;
				} else {
					$personrow->person_custom_2_option_id = null;
				}
                                
                                      
                                        
				if ($this->setting('display_mod_skillsmart')){

					$personrow->govemp_option_id             = $this->getSanParam('govemp_option_id');
					$personrow->occupational_category_id     = $this->getSanParam('occupational_category_id');
					$personrow->persal_number                = $this->getSanParam('persal_number');
					$personrow->bodies_id                    = $this->getSanParam('professionalbodies_id');
					$personrow->race_option_id               = $this->getSanParam('race_option_id');
					$personrow->disability_option_id         = $this->getSanParam('disability_option_id');

					$personrow->professional_reg_number      = $this->getSanParam('professional_reg_number');
					$personrow->nationality_id               = $this->getSanParam('nationality_id');
					$personrow->nurse_training_id            = $this->getSanParam('nurse_training_id');
					$personrow->care_start_year              = $this->getSanParam('care_start_year');
					$personrow->timespent_rank_pregnant      = $this->getSanParam('timespent_rank_pregnant');
					$personrow->timespent_rank_adults        = $this->getSanParam('timespent_rank_adults');
					$personrow->timespent_rank_children      = $this->getSanParam('timespent_rank_children');
					$personrow->timespent_rank_pregnant_pct  = $this->getSanParam('timespent_rank_pregnant_pct');
					$personrow->timespent_rank_adults_pct    = $this->getSanParam('timespent_rank_adults_pct');
					$personrow->timespent_rank_children_pct  = $this->getSanParam('timespent_rank_children_pct');
					$personrow->supervised_id                = $this->getSanParam('supervised_id');
					$personrow->supervision_frequency_id     = $this->getSanParam('supervision_frequency_id');
					$personrow->supervisors_profession       = $this->getSanParam('supervisors_profession');
					$personrow->facilitydepartment_id        = $this->getSanParam('facilitydepartment_id');
                                        //$currDate = new Zend_Db_Expr('CURDATE()');
                                       
                                        //$personrow->timestamp_created = $currDate;
                                        //$personrow->created_by = $user->get_userid();
                                        
					$training_recieved_arr = array();
					$training_recieved_data = '';
					$training_recieved_results = array();

					foreach($ssl['training'] as $trainingnode){
						$training_recieved_arr[$trainingnode['id']] = $trainingnode['label'];
					}
					// build and insert training recieved vars in json
					foreach($_POST as $postvar => $postval){
						if( strstr($postvar, 'trainingrecieved_') && (isset($_POST[$postvar]) && $_POST[$postvar] != '') )
                                                {
							$recv_str = '';
							$recv_num = explode('_', $postvar);
							if( isset($training_recieved_arr[intval($recv_num[1])]) )
                                                        {
								$recv_str = $training_recieved_arr[intval($recv_num[1])];
								$training_recieved_results[$recv_str][$recv_num[2]] = $postval;
							}
						}
					}
					if( !empty($training_recieved_results) ){
						$training_recieved_data = json_encode($training_recieved_results);
					}
					$personrow->training_recieved_data = $training_recieved_data;

					//$personrow->comments_skillsmart = $this->getSanParam('govemp_option_id');//0
					/*
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('custom1Input');//1
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('race_option_id');//2
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('disability_option_id');//3
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('disability');//4
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('custom2Input');//5
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('rank1_option_id');//6
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('rank2_option_id');//7
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('rank3_option_id');//8
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('percent1');//9
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('percent2');//10
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('percent3');//11
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('supervised_option_id');//12
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('sfreq_option_id');//13
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('specifyfreqinput');//14
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('svoccu');//15
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother');//16
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother');//17
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother');//18
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother');//19
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('specifyoccupationinput');//20
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('trainer_type_option_id1');//21
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother0');//22
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother0');//23
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother0');//24
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother0');//25
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother1');//26
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother1');//27
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother1');//28
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother1');//29
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother2');//30
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother2');//31
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother2');//32
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother2');//33
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother3');//34
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother3');//35
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother3');//36
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother3');//37
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother4');//38
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother4');//39
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother4');//40
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother4');//41
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother5');//42
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother5');//43
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother5');//44
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother5');//45
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother6');//46
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother6');//47
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother6');//48
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother6');//49
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother7');//50
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother7');//51
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother7');//52
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother7');//53
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainother8');//54
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('checktrainother8');//55
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('selecttrainother8');//56
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('texttrainotherother8');//57
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('bodies_id');//58
					$personrow->comments_skillsmart = $personrow->comments_skillsmart."?".$this->getSanParam('nationality_id');//59
					*/

				if ( $person_id ) {
					$trainsaved = array();
					$train = $this->getSanParam('train');
					foreach ($train as $key=>$t){
						if (isset ($t['check'])){
							if ($t['originalid'] != 0){
								// UPDATING EXISTING // moving this - cant really use $person_id before $personrow->save on adding a new user... probably should happen later or only on edits... im not sure
								$trainid = $t['originalid'];
								$query = "UPDATE link_person_training SET
									personid = '" . $person_id . "',
									year = '" . addslashes($t['year']) . "',
									institution = '" . addslashes($t['text']) . "',
									othername = '' WHERE id = " . $trainid;
								error_log($query);
								$db->query ($query);
							} else {
								// ADDING NEW
								$query = "INSERT INTO link_person_training SET
											personid = '" . $person_id . "',
											trainingid = '" . addslashes($key) . "',
											year = '" . addslashes($t['year']) . "',
											institution = '" . addslashes($t['text']) . "',
											othername = ''";
								error_log($query);
								$db->query ($query);
								$trainid = $db->lastInsertId();
							}
							$trainsaved[] = $trainid;
						}
					}
					if (count ($trainsaved) > 0){
						// REMOVE ALL NON-SAVED TRAINING IDS FOR PERSON
						$query = "DELETE FROM link_person_training WHERE personid = " . $person_id . " AND id NOT IN (" . implode(",", $trainsaved) . ")";
						error_log($query);
						$db->query ($query);
					} else {
						// REMOVE ALL TRAINING IDS FOR PERSON
						$query = "DELETE FROM link_person_training WHERE personid = " . $person_id;
						error_log($query);
						$db->query ($query);
					}
				}
				}
                                    if(trim($personrow->title_option_id)==""){
                                        $personrow->title_option_id = 0;
                    
                }
                
                if(trim($personrow->birthdate)=="--"){
                                        $personrow->birthdate = "0000-00-00";
                    
                }
                
                if(trim($personrow->primary_qualification_option_id)==""){
                                        $personrow->primary_qualification_option_id = "0";
                    
                }
                
                //TP: Adding the Inactive, Inactive_Reason, and Inactive Description value
               
//                echo 'This is the checkbox value = ' . $this->getSanParam('inactive');
//                return;
                if($this->getSanParam('active') === 'inactive')
                {
                    $personrow->active = 'inactive';
                    $personrow->inactive_reason = $this->getSanParam('inactive_reason');
                    $personrow->inactive_description = $this->getSanParam('inactive_desc');
                }
                else
                {
                    $personrow->active = 'active';
                    $personrow->inactive_reason = 0;
                    $personrow->inactive_description = '';
                }
                                        //date_default_timezone_set('Africa/Lagos');
                                        $today = date("Y-m-d H:i:s");
                                        $personrow->timestamp_updated  = $today;
                                        
                                        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                                        $personSave = $personObj->update($personrow->toArray(), 'id  ='.$personrow->id);
                                        //$db->
				if ($personSave) {
                                      
                                    file_put_contents("personlogger.txt", print_r($personrow, true));
                                            
					$status->setStatusMessage ( t ( 'The person was saved. Refreshing change history...'));
					$_SESSION['status'] = t ( 'The person was saved.' );

					$person_id = $personrow->id;

					if ($this->setting('display_mod_skillsmart')){
						if(strlen($this->getSanParam('Facilities'))>0)
						{
							$db->query ( 'UPDATE `facs` SET `Active`=\'N\' WHERE `person`='.$person_id.' AND `Active`=\'Y\';' );
							$Facs=split('\$',$this->getSanParam('Facilities'));
							foreach ( $Facs as $kys => $vls ) {
								$Locs=split("~",$vls);
								$Fac=$Locs[0];
								if ($strrpos = strrpos ( $Fac, '_' )) {
									$Fac = substr ( $Fac, $strrpos + 1 );
								}
								$db->query ( 'INSERT INTO `facs` ( `person`, `facility`, `facstring`, `active`) VALUES ('.$person_id.', '.$Fac.', \''.$vls.'\', \'Y\');' );
							}
						}

						$co=0;
						for($co=1;$co<=20;$co++) {
							$db->query ( 'UPDATE `trans` SET `Active`=\'N\' WHERE `person`='.$person_id.' AND `id`='.$co.' AND `Active`=\'Y\';' );
							if($this->getSanParam('checktrain'.$co)=='on') {
								$db->query ( 'INSERT INTO `trans` ( `person`, `id`, `chk`, `yr`, `transstring`, `active`) VALUES ('.$person_id.', '.$co.', \''.$this->getSanParam('checktrain'.$co).'\', \''.$this->getSanParam('selecttrain'.$co).'\', \''.$this->getSanParam('texttrain'.$co).'\', \'Y\');' );
							}
						}
					}
					//get trainer information
					$trainerTable = new Trainer ( );
					$trainerRow = $trainerTable->fetchRow ( 'person_id = ' . $person_id );
					if ((!$trainerRow) and ($this->getSanParam('active_trainer_option_id') or $this->getSanParam('trainer_type_option_id'))) { // add trainer
						$trainerRow = $trainerTable->createRow ();
						$trainerRow->person_id = $personrow->id;
					}

					if ($trainerRow) {
						//trainer info
						$trainerRow->is_active = 1; //deprecated //($this->getSanParam ( 'is_trainer' ) ? 1 : 0);
						$trainerRow->active_trainer_option_id = $this->getSanParam ( 'active_trainer_option_id' );
						$trainerRow->type_option_id = $this->getSanParam ( 'trainer_type_option_id' );
						$trainerRow->affiliation_option_id = $this->setting ( 'display_trainer_affiliations' ) ? ($this->getSanParam ( 'trainer_affiliation_option_id' ) ? $this->getSanParam ( 'trainer_affiliation_option_id' ) : 0) : 0;
						if (! $trainerRow->save ()) {
							$status->setStatusMessage ( t ( 'The').' '.t('trainer').' '.t('information could not be saved.' ) );
						} else {
							MultiOptionList::updateOptions ( 'trainer_to_trainer_skill_option', 'trainer_skill_option', 'trainer_id', $person_id, 'trainer_skill_option_id', $this->getSanParam ( 'trainer_skill_id' ) );

							$language_ids = $this->getSanParam ( 'trainer_language_id' );
							$planguage_id = $this->getSanParam ( 'primary_language_id' );
							$primary_settings = array ();
							$found = false;
							if  ( $language_ids ) {
								foreach ( $language_ids as $lid ) {
									if ($lid == $planguage_id) {
										$primary_settings [] = 1;
										$found = true;
									} else {
										$primary_settings [] = 0;
									}
								}
							}
							if (! $found) {
								$primary_settings [] = 1;
								$language_ids [] = $planguage_id;
							}

							MultiOptionList::updateOptions ( 'trainer_to_trainer_language_option', 'trainer_language_option', 'trainer_id', $person_id, 'trainer_language_option_id', $language_ids, 'is_primary', $primary_settings );
						}
					}
                                        $trainingRecommend = new Trainingrecommend();
					$trainingRecommend->saveRecommendedforPerson ( $person_id, $this->getSanParam ( 'training_recommend' ) );

					if ($this->_getParam ( 'redirectUrl' )) {
						$status->redirect = $this->_getParam ( 'redirectUrl' );
					} else if ($this->_getParam ( 'trainingredirect' )) { // redirect back to training session
						$this->trainingRedirect ( $person_id );
					} else { //if it's an add then redirect to the view page
						if ($this->setting('display_mod_skillsmart')){
							//$status->setRedirect ( '/person/view/id/' . $person_id );
							$qs = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
							$parent_id = $this->getSanParam ( 'primary_qualification_option_id' );
							foreach ( $qs as $k => $v ) {
								if ($v ['id'] == $parent_id) {
									$parent_id = $v ['parent_id'];
									break;
								}
							}

							$comps = $helper->getPersonCompetenciesDetailed($person_id);
							// Disabled redirect to competency page as per Robert's request, 11/19/2012, CDL
//							if (count($comps) > 0){
//								$status->setRedirect ( '/person/editcompetency/id/' . $person_id );
//							} else {
								$status->setRedirect ( '/person/edit/id/' . $person_id );
//							}

/*
							if($parent_id=="6")
							{
								$status->setRedirect ( '/person/addcomc/id/' . $person_id );
							}
							if($parent_id=="7")
							{
								$status->setRedirect ( '/person/addcomd/id/' . $person_id );
							}
							if($parent_id=="8")
							{
								$status->setRedirect ( '/person/addcomn/id/' . $person_id );
							}
							if($parent_id=="9")
							{
								$status->setRedirect ( '/person/addcomp/id/' . $person_id );
							}
*/
						} else {
							$status->setRedirect ( '/person/edit/id/' . $person_id );
						}
					}

				} else {
					$status->setStatusMessage ( t ( 'ERROR: The person could not be saved. ' . __LINE__ ) );
				}
			}

			if ($validateOnly) {
                                
				$this->sendData ( $status );
                                
			} else {
				$this->view->assign ( 'status', $status );
			}
		}

		if ($this->setting('display_mod_skillsmart')){
			if($person_id) {
				$rows = $db->fetchAll ( 'SELECT `facstring` FROM `facs` INNER JOIN `facility` ON `facs`.`facility` = `facility`.`id` WHERE `facility`.`is_deleted`=0 AND `facs`.`person`='.$person_id.' AND `facs`.`Active`=\'Y\' ORDER BY `facs`.`sno` ASC;' );
				$Fcs="";
				foreach ( $rows as $rw ) {
					$Fcs=$Fcs.$rw['facstring'].'$';
				}
				$Fcs=trim($Fcs,'$');
				$this->view->assign ( 'Fcs', $Fcs );

				$rows = $db->fetchAll ( 'SELECT `id`, `chk`, `yr`, `transstring` FROM `trans` WHERE `person`='.$person_id.' AND `Active`=\'Y\' ORDER BY sno ASC;' );
				$Trs=array();
				$cok=0;
				for($cok=1;$cok<=20;$cok++) {
					$Trs[$cok]=NULL;
				}
				foreach ( $rows as $rw ) {
					$Trs[$rw['id']]=$rw;
				}
				$this->view->assign ( 'Trs', $Trs );
			}
		}

		//view it
		$facilityObj = new Facility ( );
		$facilityrow = $facilityObj->findOrCreate ( $personrow->facility_id );
		$personArray ['facility'] = $facilityrow->toArray ();
		//facility location
		$region_ids = Location::getCityInfo($facilityrow->location_id, $this->setting('num_location_tiers'));
		$region_ids = Location::regionsToHash($region_ids, 'person_facility');
		$personArray = array_merge($personArray, $region_ids);
		//audit history
		$creatorObj = new User ( );
		$updaterObj = new User ( );
		$creatorrow = $creatorObj->findOrCreate ( $personrow->created_by );
		$personArray ['creator'] = addslashes(($creatorrow->first_name) . ' ' . ($creatorrow->last_name));
		$updaterrow = $updaterObj->findOrCreate ( $personrow->modified_by );
		$personArray ['updater'] = addslashes(($updaterrow->first_name) . ' ' . ($updaterrow->last_name));

		$personArray ['birthdate-year'] = '';
		$personArray ['birthdate-month'] = '';
		$personArray ['birthdate-day'] = '';

		//split birthdate fields
		if ($person_id and $personrow->birthdate) {
			$parts = explode ( ' ', $personrow->birthdate );
			$parts = explode ( '-', $parts [0] );
			$personArray ['birthdate-year'] = $parts [0];
			$personArray ['birthdate-month'] = $parts [1];
			$personArray ['birthdate-day'] = $parts [2];
		}

		//custom fields
		if ($person_id) {
			$personArray ['custom1'] = ITechTable::getCustomValue ( 'person_custom_1_option', 'custom1_phrase', $personArray ['person_custom_1_option_id'] );
			$personArray ['custom2'] = ITechTable::getCustomValue ( 'person_custom_2_option', 'custom2_phrase', $personArray ['person_custom_2_option_id'] );
		}

		//qualifications
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$personQualificationId = $personArray ['primary_qualification_option_id'];

		// get parent qualification id, if user has sub qualification selected
		$personArray ['primary_qualification_option_id_parent'] = $personQualificationId;
		foreach ( $qualificationsArray as $k => $qualArray ) {
			if ($qualArray ['parent_phrase'] == 'unknown') {
				unset ( $qualificationsArray [$k] ); //remove unknown as an option
			}
			if ($qualArray ['id'] == $personQualificationId) {
				$personArray ['primary_qualification_option_id_parent'] = $qualArray ['parent_id'];
			}
		}
                
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );


		// occupational categories
		$occupationalsArray = OptionList::suggestionListHierarchical ( 'occupational_categories', 'category_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$personQualificationId = $personArray ['primary_qualification_option_id'];

		// get parent occupational category id, if user has sub qualification selected
		$personArray ['occupational_category_id_parent'] = $personQualificationId;
		foreach ( $occupationalsArray as $k => $catArray ) {
			if ($catArray ['category_phrase'] == 'unknown') {
				unset ( $qualificationsArray [$k] ); //remove unknown as an option
			}
			if ($catArray ['id'] == $personQualificationId) {
				$personArray ['occupational_category_id'] = $catArray ['parent_id'];
			}
                       
		}
		$this->viewAssignEscaped ( 'occupationalcats', $occupationalsArray );

		// get recommended trainings class topics
                $trainingRecommend = new TrainingRecommend();
		$training_recommend = $trainingRecommend->getRecommendedTrainingTopics ( $personArray ['primary_qualification_option_id_parent'] );
		$this->viewAssignEscaped ( 'training_recommend', $training_recommend );

		// get saved recommended trainings class titles
		$training_recommend_saved = $trainingRecommend->getRecommendedforPerson ( $person_id );
		$this->viewAssignEscaped ( 'training_recommend_saved', $training_recommend_saved );

		//$classes = TrainingRecommend::getRecommendedClassesforPerson ( $person_id );

		//responsibilities
		if ($this->setting('display_mod_skillsmart')){
			$responsibilitiesArray = OptionList::suggestionList ( 'person_responsibility_option', 'responsibility_phrase', false, false );
			$this->viewAssignEscaped ( 'responsibilities', $responsibilitiesArray );
		}
		$primaryResponsibilitiesArray  = OptionList::suggestionList ( 'person_primary_responsibility_option', 'responsibility_phrase', false, false );
		$this->viewAssignEscaped ( 'primaryResponsibilities', $primaryResponsibilitiesArray );
		$secondaryResponsibilitiesArray = OptionList::suggestionList ( 'person_secondary_responsibility_option', 'responsibility_phrase', false, false );
		$this->viewAssignEscaped ( 'secondaryResponsibilities', $secondaryResponsibilitiesArray );



		$educationlevelsArray = OptionList::suggestionList ( 'person_education_level_option', 'education_level_phrase', false, false );
		$this->viewAssignEscaped ( 'educationlevels', $educationlevelsArray );

		$attendreasonsArray = OptionList::suggestionList ( 'person_attend_reason_option', 'attend_reason_phrase', false, false );
		$this->viewAssignEscaped ( 'attendreasons', $attendreasonsArray );

		$activeTrainerArray = OptionList::suggestionList ( 'person_active_trainer_option', 'active_trainer_phrase', false, false );
		$this->viewAssignEscaped ( 'active_trainer', $activeTrainerArray );

		$titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, false );
		$this->viewAssignEscaped ( 'titles', $titlesArray );

		$suffixesArray = OptionList::suggestionList ( 'person_suffix_option', 'suffix_phrase', false, false );
		$this->viewAssignEscaped ( 'suffixes', $suffixesArray );

		//training types
		//attach Trainer attributes

		$trainerTable = new Trainer ( );
		$trainerrow = $trainerTable->findOrCreate ( $person_id );

		$personArray ['trainer_is_active']             = $this->getSanParam ( 'maketrainer' ) ? 1 : $trainerrow->is_active;
		$personArray ['active_trainer_option_id']      = $trainerrow->active_trainer_option_id;
		$personArray ['trainer_type_option_id']        = $trainerrow->type_option_id;
		$personArray ['trainer_affiliation_option_id'] = $trainerrow->affiliation_option_id;

		$titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, false );
		$this->viewAssignEscaped ( 'titles', $titlesArray );

		$trainerTypesArray = OptionList::suggestionList ( 'trainer_type_option', 'trainer_type_phrase', false, false );
		$this->viewAssignEscaped ( 'trainer_types', $trainerTypesArray );
		$trainerAffiliationArray = OptionList::suggestionList ( 'trainer_affiliation_option', 'trainer_affiliation_phrase', false, false );
		$this->viewAssignEscaped ( 'trainer_affiliations', $trainerAffiliationArray );

		$trainerSkillsArray = MultiOptionList::choicesList ( 'trainer_to_trainer_skill_option', 'trainer_id', $person_id, 'trainer_skill_option', 'trainer_skill_phrase' );
		$this->viewAssignEscaped ( 'trainer_skills', $trainerSkillsArray );

		$trainerLanguagesArray = MultiOptionList::choicesList ( 'trainer_to_trainer_language_option', 'trainer_id', $person_id, 'trainer_language_option', 'language_phrase', 'is_primary' );
		$this->viewAssignEscaped ( 'trainer_languages', $trainerLanguagesArray );
		foreach ( $trainerLanguagesArray as $lang ) {
			if ($lang ['is_primary'])
			$personArray ['primary_language_id'] = $lang ['id'];
		}

		//has training history
		$hasTrainerHistory = false;
		if ($trainerrow->person_id) {
			$hasTrainerHistory = true;
			//we could also look up any history now, but we'll be lazy
		}
		$this->view->assign ( 'hasTrainerHistory', $hasTrainerHistory );

		//facility types
		$typesArray = OptionList::suggestionList ( 'facility_type_option', 'facility_type_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_types', $typesArray );

		//get home city name
		if ( $personrow->home_location_id ) {
			$region_ids = Location::getCityInfo($personrow->home_location_id, $this->setting('num_location_tiers'));
			$personArray['home_city'] = $region_ids[0];
			$region_ids = Location::regionsToHash($region_ids, 'home');
			$personArray = array_merge($personArray, $region_ids);
		}

		//sponsor types
		$sponsorsArray = OptionList::suggestionList ( 'facility_sponsor_option', 'facility_sponsor_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_sponsors', $sponsorsArray );
                if(trim($personArray['title_option_id'])==""){
                    $personArray['title_option_id'] = 0;
                }
		$this->viewAssignEscaped ( 'person', $personArray );

		//facilities list
		//$rowArray = OptionList::suggestionList('facility',array('facility_name','id'),false,9999);
		$rowArray = Facility::selectAllFacilities ( $this->setting('num_location_tiers') );
		
		$this->viewAssignEscaped ( 'facilities', $rowArray ); 
		if ($this->hasACL( 'edit_facility' )){
			$this->view->assign( 'insertFacilityLink', '<a href="#" id="show">'. str_replace(' ','&nbsp;',t('Insert new facility')) .'</a>' );
		}

		//see if it is referenced anywhere
		$this->view->assign ( 'okToDelete', ((! $person_id) or (! Person::isReferenced ( $person_id ))) );

		// create reference for GET paramaters
		if ($this->_getParam ( 'trainingredirect' )) {
			$this->view->assign ( 'trainingredirect', $this->_getParam ( 'trainingredirect' ) );
		}
		if ($this->_getParam ( 'maketrainer' )) {
			$this->view->assign ( 'maketrainer', $this->_getParam ( 'maketrainer' ) );
		}
		if ($this->_getParam ( 'days' )) {
			$this->view->assign ( 'days', $this->_getParam ( 'days' ) );
		}

	}
	catch (Exception $e) {
		print $e->getMessage();
	}
    }
        
        
    
    public function doAddView() {
//echo date_default_timezone_get();exit;
	try {
                //$this->editPerson();
		
		$status = ValidationContainer::instance ();

		require_once ('models/table/OptionList.php');
		require_once ('models/table/MultiOptionList.php');
		require_once ('models/table/Location.php');

		$request = $this->getRequest();
		if($request->isXmlHttpRequest ())
			$this->setNoRenderer ();
		
                
                //$personObj = new Person();
                $personData = array();
                $user = new User();
                $facility = new Facility();     
                 $personObj = new Person();

		//locations
		$locations = Location::getAll();
		$this->viewAssignEscaped ( 'locations', $locations );
                
                $titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, false );
		$this->viewAssignEscaped ( 'titles', $titlesArray );
                
                $qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                
		if ($request->isPost()) {

			$errortext = "";
			if ($dupe_id = $this->getSanParam ( 'dupe_id' )) {
				if ($this->getSanParam ( 'maketrainer' )) {
					// require user to add trainer info
					$status->setRedirect ( '/person/edit/id/' . $dupe_id . '/trainingredirect/' . $this->getSanParam ( 'trainingredirect' ) . '/maketrainer/1' );
				} else if ($this->_getParam ( 'trainingredirect' )) {
					$status->setStatusMessage ( t ( 'The person was saved. Refreshing history...' ) );
					$_SESSION['status'] = t ( 'The person was saved.' );
					$this->trainingRedirect ( $dupe_id );
				} else {
					$status->setRedirect ( '/person/edit/id/' . $dupe_id );
				}
				return;
			}

                        //check if fisrt name and last name have been entered
			$status->checkRequired ( $this, 'first_name', $this->tr ( 'First Name' ) );
			$status->checkRequired ( $this, 'last_name', $this->tr ( 'Surname' ) );
                        $status->checkRequired($this, 'primary_qualification_option_id', 'Qualification');
                        $status->checkRequired($this, 'facilityInput', 'Facility Name');

                        /****************************************************************
                         * WE REALLY NEED TO LOOK INTO THE CONSTRUCTIOIN AND VALIDATIOIN OF BIRTH DATES
                         * USING A DATE PICKER ON THE VIEW SHOULD BE CONSIDERED
                         */
                        $birthYear = $this->getSanParam ( 'birth-year' ) ? $this->getSanParam ( 'birth-year' ) : '0000';
                        $birthMonth = $this->getSanParam ( 'birth-month' ) ? $this->getSanParam ( 'birth-month' ) : '00';
                        $birthDay = $this->getSanParam ( 'birth-day' ) ? $this->getSanParam ( 'birth-day' ) : '00';
                        $birthParam = $birthYear . '-' . $birthMonth . '-' . $birthDay;
			
                        
                        //$birthParam = (@$this->getSanParam ( 'birth-year' )) . '-' . (@$this->getSanParam ( 'birth-month' )) . '-' . (@$this->getSanParam ( 'birth-day' ));
                        if ($birthParam !== '--' and $birthParam !== '0000-00-00')
			$status->isValidDate ( $this, 'birth-day', t ( 'Birthdate' ), $birthParam );
                        /****************************************************************/
                        
			//check facility
                        $facility_id = '';
                        $facilityExplode ='';
                        
			if ($status->checkRequired ( $this, 'facilityInput', t ( 'Facility' ) )) {
				$facility_id = $this->getSanParam ( 'facilityInput' );

				if ($strrpos = strrpos ( $facility_id, '_' )) {
                                        $facilityExplode = explode('_', $facility_id);
					$facility_id = array_pop($facilityExplode); 
				}
			}


			if ($status->hasError ()) {
                            $pData = array();
                            $pData = $this->_getAllParams();
                            $pData['birthdate-day'] = $pData['birth-day'];
                            $pData['birthdate-month'] = $pData['birth-month'];
                            $pData['birthdate-year'] = $pData['birth-year'];
                            $pData['province_id'] = (isset($pData['person_facility_province_id']))?$pData['person_facility_province_id']:"";
                            $pData['district_id'] = (isset($pData['person_facility_district_id']))?$pData['person_facility_district_id']:"";
                            $pData['region_c_id'] = (isset($pData['person_facility_region_c_id']))?$pData['person_facility_region_c_id']:"";
                            $facility_id = $pData['facilityInput'];
                            if(!empty($facility_id)){
                            
                            $pData['facility_id'] = $facility_id;
                            $facilityObj = new Facility ( );
		            $facilityrow = $facilityObj->findOrCreate (  $facility_id );
		            $pData ['facility'] = $facilityrow->toArray ();
                            }
                            
                            //Helper2::jLog(print_r($pData,true));
                             $this->view->assign('person',$pData);
				foreach ($status->messages as $k=>$v){
					$errortext .= $v . "<br>";
				}
				$status->setStatusMessage ( t ( 'The person could not be saved. <br>' . $errortext ) );
                                return;
			} else {
                                
				//$personrow = self::fillFromArray ( $personrow, $this->_getAllParams () );
                                //Add the variables you have into the bind array
                                $title = $this->getSanParam ( 'title_option_id' );
                                
                                if($title != null && $title != ""){
                                    $personData['title_option_id'] = $this->getSanParam ( 'title_option_id' );
                                }
                                $personData['first_name'] = $this->getSanParam ( 'first_name' );
                                $personData['last_name'] = $this->getSanParam ( 'last_name' );
                                $personData['birthdate'] =  $birthParam ;
                                $personData['facility_id'] = $facility_id;
				$personData['person_status_id'] = $this->getSanParam ( 'person_status_id' );
                                $personData['active'] = 'active';
                                $personData['primary_qualification_option_id'] = $this->getSanParam ( 'primary_qualification_option_id' );
                               
                                $user = new User();
                                $personData['created_by'] = $user->getUserID();
                                $currDate = new Zend_Db_Expr('CURDATE()');
                                $today = date("Y-m-d H:i:s");
                               // Helper2::jLog("This is the current date".$currDate);
                                $personData['timestamp_created'] = $today;
                        }
                
                        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                        if ($db->insert('person', $personData)) {
                            $person_id = $db->lastInsertId();
                            //file_put_contents("personlogger.txt", print_r($personrow, true));
                            
                            //TP: Push the person data entry event to SOAP Server
                            
                            if($this->_getParam ( 'action' ) == 'add'){
                                        $metricClient = new MetricClient();
                                        $userId = Zend_Auth::getInstance()->getIdentity()->id;
                                        $metricClient->handleDCMetrics($person_id, 'pa', $userId);
                                    }
                                
                                /***
                                 * Dont fully understand whats going on here, but i think is an ACL 
                                 */
                            
                                $status->setStatusMessage ( t ( 'The person was saved. Refreshing change history...'));
                                $_SESSION['status'] = t ( 'The person was saved.' );
                                
                                //if it's an add then redirect to the view page
                                $status->setRedirect ( '/person/edit/id/' . $person_id );

                        } else {
                                $this->view->assign('userRange',true);
                                $status->setStatusMessage ( t ( 'ERROR: The person could not be saved. ' . __LINE__ ) );
                        }
                }

                $this->view->assign ( 'status', $status );
                $this->view->assign('userRange',true);
                
                 $allPersonStatus = $personObj->getAllPersonStatus();
                 $this->viewAssignEscaped('person_status',$allPersonStatus);
                   
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $sql = "select * from inactive_reason";
                $inactiveReasonArray = $db->fetchAll($sql);
                //Load the Inactive Records
                $this->view->assign('inactive', true);
               
                
                //TP: check of the user is an LGA so as to enable the user to be able to use the inactive checkbox for the retired HW
                if($user->isUserAnLga()){
                    $this->view->assign('showInactive',false);
                }else{
                    $this->view->assign('showInactive',true);
                }
                
                
                if($user->is_user_an_admin())
                {
                    $this->view->assign("isAdmin",true);
                }
                
//		//view it
//		$facilityObj = new Facility ( );
//		$facilityrow = $facilityObj->findOrCreate ( $personrow->facility_id );
//		$personArray ['facility'] = $facilityrow->toArray ();
//		//facility location
//		$region_ids = Location::getCityInfo($facilityrow->location_id, $this->setting('num_location_tiers'));
//		$region_ids = Location::regionsToHash($region_ids, 'person_facility');
//		$personArray = array_merge($personArray, $region_ids);
//		//audit history
//		$creatorObj = new User ( );
//		$updaterObj = new User ( );
//		$creatorrow = $creatorObj->findOrCreate ( $personrow->created_by );
//		$personArray ['creator'] = addslashes(($creatorrow->first_name) . ' ' . ($creatorrow->last_name));
//		$updaterrow = $updaterObj->findOrCreate ( $personrow->modified_by );
//		$personArray ['updater'] = addslashes(($updaterrow->first_name) . ' ' . ($updaterrow->last_name));
//
//		$personArray ['birthdate-year'] = '';
//		$personArray ['birthdate-month'] = '';
//		$personArray ['birthdate-day'] = '';
//
//		//split birthdate fields
//		if ($person_id and $personrow->birthdate) {
//			$parts = explode ( ' ', $personrow->birthdate );
//			$parts = explode ( '-', $parts [0] );
//			$personArray ['birthdate-year'] = $parts [0];
//			$personArray ['birthdate-month'] = $parts [1];
//			$personArray ['birthdate-day'] = $parts [2];
//		}
//
//		//custom fields
//		if ($person_id) {
//			$personArray ['custom1'] = ITechTable::getCustomValue ( 'person_custom_1_option', 'custom1_phrase', $personArray ['person_custom_1_option_id'] );
//			$personArray ['custom2'] = ITechTable::getCustomValue ( 'person_custom_2_option', 'custom2_phrase', $personArray ['person_custom_2_option_id'] );
//		}
//
//		//qualifications
//		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
//		$personQualificationId = $personArray ['primary_qualification_option_id'];
//
//		// get parent qualification id, if user has sub qualification selected
//		$personArray ['primary_qualification_option_id_parent'] = $personQualificationId;
//		foreach ( $qualificationsArray as $k => $qualArray ) {
//			if ($qualArray ['parent_phrase'] == 'unknown') {
//				unset ( $qualificationsArray [$k] ); //remove unknown as an option
//			}
//			if ($qualArray ['id'] == $personQualificationId) {
//				$personArray ['primary_qualification_option_id_parent'] = $qualArray ['parent_id'];
//			}
//		}
//		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
//
//
//		// occupational categories
//		$occupationalsArray = OptionList::suggestionListHierarchical ( 'occupational_categories', 'category_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
//		$personQualificationId = $personArray ['primary_qualification_option_id'];
//
//		// get parent occupational category id, if user has sub qualification selected
//		$personArray ['occupational_category_id_parent'] = $personQualificationId;
//		foreach ( $occupationalsArray as $k => $catArray ) {
//			if ($catArray ['category_phrase'] == 'unknown') {
//				unset ( $qualificationsArray [$k] ); //remove unknown as an option
//			}
//			if ($catArray ['id'] == $personQualificationId) {
//				$personArray ['occupational_category_id'] = $catArray ['parent_id'];
//			}
//                       
//		}
//		$this->viewAssignEscaped ( 'occupationalcats', $occupationalsArray );
//
//		// get recommended trainings class topics
//                $trainingRecommend = new TrainingRecommend();
//		$training_recommend = $trainingRecommend->getRecommendedTrainingTopics ( $personArray ['primary_qualification_option_id_parent'] );
//		$this->viewAssignEscaped ( 'training_recommend', $training_recommend );
//
//		// get saved recommended trainings class titles
//		$training_recommend_saved = $trainingRecommend->getRecommendedforPerson ( $person_id );
//		$this->viewAssignEscaped ( 'training_recommend_saved', $training_recommend_saved );
//
//		//$classes = TrainingRecommend::getRecommendedClassesforPerson ( $person_id );
//
//		//responsibilities
//		if ($this->setting('display_mod_skillsmart')){
//			$responsibilitiesArray = OptionList::suggestionList ( 'person_responsibility_option', 'responsibility_phrase', false, false );
//			$this->viewAssignEscaped ( 'responsibilities', $responsibilitiesArray );
//		}
//		$primaryResponsibilitiesArray  = OptionList::suggestionList ( 'person_primary_responsibility_option', 'responsibility_phrase', false, false );
//		$this->viewAssignEscaped ( 'primaryResponsibilities', $primaryResponsibilitiesArray );
//		$secondaryResponsibilitiesArray = OptionList::suggestionList ( 'person_secondary_responsibility_option', 'responsibility_phrase', false, false );
//		$this->viewAssignEscaped ( 'secondaryResponsibilities', $secondaryResponsibilitiesArray );
//
//
//
//		$educationlevelsArray = OptionList::suggestionList ( 'person_education_level_option', 'education_level_phrase', false, false );
//		$this->viewAssignEscaped ( 'educationlevels', $educationlevelsArray );
//
//		$attendreasonsArray = OptionList::suggestionList ( 'person_attend_reason_option', 'attend_reason_phrase', false, false );
//		$this->viewAssignEscaped ( 'attendreasons', $attendreasonsArray );
//
//		$activeTrainerArray = OptionList::suggestionList ( 'person_active_trainer_option', 'active_trainer_phrase', false, false );
//		$this->viewAssignEscaped ( 'active_trainer', $activeTrainerArray );
//
//		$titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, false );
//		$this->viewAssignEscaped ( 'titles', $titlesArray );
//
//		$suffixesArray = OptionList::suggestionList ( 'person_suffix_option', 'suffix_phrase', false, false );
//		$this->viewAssignEscaped ( 'suffixes', $suffixesArray );
//
//		//training types
//		//attach Trainer attributes
//
//		$trainerTable = new Trainer ( );
//		$trainerrow = $trainerTable->findOrCreate ( $person_id );
//
//		$personArray ['trainer_is_active']             = $this->getSanParam ( 'maketrainer' ) ? 1 : $trainerrow->is_active;
//		$personArray ['active_trainer_option_id']      = $trainerrow->active_trainer_option_id;
//		$personArray ['trainer_type_option_id']        = $trainerrow->type_option_id;
//		$personArray ['trainer_affiliation_option_id'] = $trainerrow->affiliation_option_id;
//
//		$titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, false );
//		$this->viewAssignEscaped ( 'titles', $titlesArray );
//
//		$trainerTypesArray = OptionList::suggestionList ( 'trainer_type_option', 'trainer_type_phrase', false, false );
//		$this->viewAssignEscaped ( 'trainer_types', $trainerTypesArray );
//		$trainerAffiliationArray = OptionList::suggestionList ( 'trainer_affiliation_option', 'trainer_affiliation_phrase', false, false );
//		$this->viewAssignEscaped ( 'trainer_affiliations', $trainerAffiliationArray );
//
//		$trainerSkillsArray = MultiOptionList::choicesList ( 'trainer_to_trainer_skill_option', 'trainer_id', $person_id, 'trainer_skill_option', 'trainer_skill_phrase' );
//		$this->viewAssignEscaped ( 'trainer_skills', $trainerSkillsArray );
//
//		$trainerLanguagesArray = MultiOptionList::choicesList ( 'trainer_to_trainer_language_option', 'trainer_id', $person_id, 'trainer_language_option', 'language_phrase', 'is_primary' );
//		$this->viewAssignEscaped ( 'trainer_languages', $trainerLanguagesArray );
//		foreach ( $trainerLanguagesArray as $lang ) {
//			if ($lang ['is_primary'])
//			$personArray ['primary_language_id'] = $lang ['id'];
//		}
//
//		//has training history
//		$hasTrainerHistory = false;
//		if ($trainerrow->person_id) {
//			$hasTrainerHistory = true;
//			//we could also look up any history now, but we'll be lazy
//		}
//		$this->view->assign ( 'hasTrainerHistory', $hasTrainerHistory );
//
//		//facility types
//		$typesArray = OptionList::suggestionList ( 'facility_type_option', 'facility_type_phrase', false, false );
//		$this->viewAssignEscaped ( 'facility_types', $typesArray );
//
//		//get home city name
//		if ( $personrow->home_location_id ) {
//			$region_ids = Location::getCityInfo($personrow->home_location_id, $this->setting('num_location_tiers'));
//			$personArray['home_city'] = $region_ids[0];
//			$region_ids = Location::regionsToHash($region_ids, 'home');
//			$personArray = array_merge($personArray, $region_ids);
//		}
//
//		//sponsor types
//		$sponsorsArray = OptionList::suggestionList ( 'facility_sponsor_option', 'facility_sponsor_phrase', false, false );
//		$this->viewAssignEscaped ( 'facility_sponsors', $sponsorsArray );
//                if(trim($personArray['title_option_id'])==""){
//                    $personArray['title_option_id'] = 0;
//                }
//		$this->viewAssignEscaped ( 'person', $personArray );
//
//		//facilities list
//		//$rowArray = OptionList::suggestionList('facility',array('facility_name','id'),false,9999);
//		$rowArray = Facility::selectAllFacilities ( $this->setting('num_location_tiers') );
//		
//		$this->viewAssignEscaped ( 'facilities', $rowArray ); 
//		if ($this->hasACL( 'edit_facility' )){
//			$this->view->assign( 'insertFacilityLink', '<a href="#" id="show">'. str_replace(' ','&nbsp;',t('Insert new facility')) .'</a>' );
//		}
//
//		//see if it is referenced anywhere
//		$this->view->assign ( 'okToDelete', ((! $person_id) or (! Person::isReferenced ( $person_id ))) );
//
//		// create reference for GET paramaters
//		if ($this->_getParam ( 'trainingredirect' )) {
//			$this->view->assign ( 'trainingredirect', $this->_getParam ( 'trainingredirect' ) );
//		}
//		if ($this->_getParam ( 'maketrainer' )) {
//			$this->view->assign ( 'maketrainer', $this->_getParam ( 'maketrainer' ) );
//		}
//		if ($this->_getParam ( 'days' )) {
//			$this->view->assign ( 'days', $this->_getParam ( 'days' ) );
//		}

	}
	catch (Exception $e) {
		print $e->getMessage();
	}
    }

	public function searchAction() {
		require_once ('models/table/Person.php');
                
                    if ($this->setting('display_mod_skillsmart')){
                        // SKILLSMART-ENABLED SYSTEM USES DIFFERENT SEARCH FOR ADDITIONAL FIELDS
                        return $this->doMySearch();
                    }
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
                $person = new Person();
                $reports = new Report();
                $locationWhereQuery = "";
                $user = new User();
                $new_locations = array();
                $newLocation = "";
                if(!$user->UserAccessRoleAllowed()){
                    
                    $new_locations = Location::userAccessLocations();
                    $newLocation = implode(",",$new_locations);
                }
                if(!empty($newLocation) || $newLocation!=""){
                   $whereLocation = "f.location_id IN (".$newLocation.")";
                }else{
                    $whereLocation = "";
                }
                
                //echo $whereLocation.'<br/>';
                //print_r($new_locations);
                //exit;
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
        //var_dump($location_id);exit;
		
		        $zone = array();
		        $zoneTemp = $this->getSanParam('province_id');
				if(is_array($zoneTemp)){
				$zone = $zoneTemp;
				}else{
				$zone[] = $zoneTemp;
				}
				$zone = $reports->formatSelection($zone);
                //echo type($zone);exit;
                $zone = $reports->removeEmptySelection($zone);
              
			    $state = array();
                $stateTemp  = $this->getSanParam('district_id');
                if(is_array($stateTemp)){
				$state = $stateTemp;
				}else{
				$state[] = $stateTemp;
				}
                
                $state = $reports->formatSelection($state);
                //print_r($state);exit;
				$localgovernment = array();
                $localgovernmentTemp  = $this->getSanParam('region_c_id');
				 if(is_array($localgovernmentTemp)){
				$localgovernment = $localgovernmentTemp;
				}else{
				$localgovernment[] = $localgovernmentTemp;
				}
				
                $localgovernment = $reports->formatSelection($localgovernment);
				$locationsCriteria= array();
			if(!empty($localgovernment)){
			$locationsCriteria = $reports->explodeGeogArray($localgovernment,"3");
			}else if(!empty($state)){
			$locationsCriteria = $reports->explodeGeogArray($state,"2");
			}else if(!empty($zone)){
			$locationsCriteria = $reports->explodeGeogArray($zone,"1");
			}
			if(!empty($locationsCriteria)){
			$locationImplodeList = implode(",",$locationsCriteria);
			$locationWhereQuery = "( region_c_id IN ($locationImplodeList) OR district_id IN ($locationImplodeList) OR province_id IN ($locationImplodeList) )";
			}
			//Helper2::jLog($locationWhereQuery);
		$criteria ['facility_name'] = $this->getSanParam ( 'facility_name' );
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['first_name'] = $this->getSanParam ( 'first_name' );
		$criteria ['last_name'] = $this->getSanParam ( 'last_name' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );

		$criteria ['person_type'] = $this->getSanParam ( 'person_type' );
		if (! $criteria ['person_type']){
			$criteria ['person_type'] = 'is_participant';
		}

		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['outputType'] = $this->getSanParam ( 'outputType' );


		$criteria ['go'] = $this->getSanParam ( 'go' );

		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

		$loc = true; // showing region for all people now, ukraines request
		/*if(!empty($criteria['province_id']))
		/	$loc = $criteria['province_id'];
		*/
               
			if ($loc){
				if ($criteria ['person_type'] == 'is_everyone' || $criteria['person_type']=='is_participant') {
					// left join instead of inner for everyone
					$sql = '
					SELECT DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, q.qualification_phrase,p.primary_qualification_option_id,p.phone_mobile,p.email, f.facility_name, province_name,province_id,district_name,district_id,region_c_name,region_c_id,ps.title as active,p.person_status_id

					FROM person as p
                                        LEFT JOIN person_status as ps on ps.id = p.person_status_id
                                        LEFT JOIN person_to_training as ptt on p.id = ptt.person_id
					LEFT JOIN person_qualification_option as q ON p.primary_qualification_option_id = q.id
					LEFT JOIN (SELECT flv.facility_name as facility_name,flv.location_id, flv.id as facility_id,flv.lga_id as region_c_id,flv.lga as region_c_name,flv.state_id as district_id, flv.state as district_name, flv.geo_zone_id as province_id, flv.geo_zone as province_name FROM facility_location_view as flv

) as f on f.facility_id = p.facility_id   ';
                                        //echo $sql;
                                        
				} 
                                else {
				$sql = '
					SELECT DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, q.qualification_phrase,p.primary_qualification_option_id,p.phone_mobile,p.email,f.facility_name, province_name,province_id,district_name,district_id,region_c_name,region_c_id,ps.title as active,p.person_status_id

					FROM person AS p LEFT JOIN (SELECT flv.facility_name as facility_name,flv.location_id, flv.id as facility_id,flv.lga_id as region_c_id,flv.lga as region_c_name,flv.state_id as district_id, flv.state as district_name, flv.geo_zone_id as province_id, flv.geo_zone as province_name FROM facility_location_view as flv
                                        
) as f on f.facility_id = p.facility_id LEFT JOIN person_qualification_option AS q on q.id=p.primary_qualification_option_id 
LEFT JOIN person_status as ps on ps.id = p.person_status_id';
				
                                
                                }
                               
			} else {
                            
				if ($criteria ['person_type'] == 'is_everyone') {
					// left join instead of inner for everyone
					$sql = 'SELECT DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender,p.phone_mobile,p.email, q.qualification_phrase, f.facility_name,p.primary_qualification_option_id,ps.title as active,p.person_status_id

					FROM person as p
                                        LEFT JOIN person_status as ps on ps.id = p.person_status_id
					LEFT JOIN person_qualification_option as q ON p.primary_qualification_option_id = q.id
					LEFT JOIN (SELECT flv.facility_name as facility_name,flv.location_id, flv.id as facility_id,flv.lga_id as region_c_id,flv.lga as region_c_name,flv.state_id as district_id, flv.state as district_name, flv.geo_zone_id as province_id, flv.geo_zone as province_name FROM facility_location_view as flv

) as f on f.facility_id = p.facility_id ';
                                        
                                        

				} else {
					// Removed $field_name from SQL query. person table does
				// not have province_name, district_name, or city_name columns. - its supposed to be from the facility
				$sql = '
                                        SELECT DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, q.qualification_phrase,p.primary_qualification_option_id,p.phone_mobile,p.email, f.facility_name,ps.title as active,p.person_status_id
					FROM person AS p,
                                        LEFT JOIN person_status as ps on ps.id = p.person_status_id
					LEFT JOIN person_qualification_option AS q ON p.primary_qualification_option_id = q.id,
					LEFT JOIN (SELECT flv.facility_name as facility_name,flv.location_id, flv.id as facility_id,flv.lga_id as region_c_id,flv.lga as region_c_name,flv.state_id as district_id, flv.state as district_name, flv.geo_zone_id as province_id, flv.geo_zone as province_name FROM facility_location_view as flv

) as f on f.facility_id = p.facility_id ';
				}
			}

			if ($criteria ['person_type'] == 'is_participant'){
				//$sql .= ', person_to_training as ptt ';
			}
			if ($criteria ['person_type'] == 'is_trainer') {
				//$sql.="";
                                
                        }

			

                         // $qual = $criteria ['qualification_id'];
                        //$where []= 'p.primary_qualification_option_id = 2' ;
			/*if ($criteria ['person_type'] != 'is_everyone') { // was left joined (see above)
				if ($loc){
					//$where []= ' (p.facility_id = f.id and f.location_id = l.id) ';
				} else {
					$where []= 'p.primary_qualification_option_id = q.id  ';
				}
			}*/

			//$locationWhere = $this->getLocationCriteriaWhereClause($criteria, '', '');
                        //
			//TP: Initialize array to hold where clause
                        
                        $where = array();
                        
			if ($locationWhereQuery) {
				$where[] = $locationWhereQuery;
			}
                        
                        if ($loc){
				$where[] = 'p.is_deleted = 0';
#				$where = array('p.is_deleted = 0'); //bugfix - is shrinking results we always want to display region, whatever tho TODO
			} else {
				$where[] = 'p.is_deleted = 0';
			}
			if ($criteria ['person_type'] == 'is_participant'){
				 $where []= 'p.id = ptt.person_id ';
                                //$where [] = 'p.id IN (select person_id from person_to_training)';
			}
			if ($criteria ['person_type'] == 'is_trainer'){
				//$where []= 'p.id = trn.person_id '; //old code
				//TA:14: should check if person 'active' 
				
                            $where[] = "p.id IN (SELECT p.id FROM person as p  LEFT JOIN person_to_training as ptt on ptt.person_id = p.id INNER JOIN training as t on ptt.training_id=t.id INNER JOIN training_level as tl on tl.id= t.training_level_option_id WHERE is_trainer_training='1')";
			}

			if ($criteria ['person_type'] == 'is_unattached_person'){
				$where []= 'p.id NOT IN (SELECT person_id FROM trainer) AND  p.id NOT IN (SELECT person_id FROM person_to_training)  ';
			}

			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}
/*if ($criteria ['qualification_id'] or $criteria ['qualification_id'] === '0') {
				//$where []= '(primary_qualification_option_id = ' . $criteria ['qualification_id'] . ' OR primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ')) ';
			$where []= '(p.primary_qualification_option_id = ' . $criteria ['qualification_id'].')' ;
			
                                
                        }
                        */
                      
			if ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$sql .= ', training as tr  ';
				if ($criteria ['person_type'] == 'is_participant')
					$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
//				if ($criteria ['person_type'] == 'is_trainer')
//                                    $where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
//					//$where []= ' p.id = ptt.person_id AND trn.person_id = ttt.trainer_id AND ttt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
			}

			
			if ($criteria ['first_name']) {
				$where []= 'p.first_name LIKE "' . $criteria ['first_name'] . '%"';
			}
			if ($criteria ['last_name']) {
				$where []= 'p.last_name LIKE "' . $criteria ['last_name'] . '%"';
			}
                        if($whereLocation!=""){
                             $where[] = $whereLocation;
                        }
			if ($where){
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}

			//$sql .= "ORDER BY " . " `p`.`last_name` ASC, " . " `p`.`first_name` ASC ";
                        $sql .= " GROUP BY p.id ORDER BY " . " `p`.`last_name` ASC, " . " `p`.`first_name` ASC ";
                        //var_dump($sql); exit;
                          //echo $sql;exit;
			//print($sql);
                        
                     //  Helper2::jLog("This is the new person query ".PHP_EOL.$sql.PHP_EOL);
			$rowArray = $db->fetchAll ( $sql );
                       
                        $resultData = array();
                        $qualification = $criteria['qualification_id'];
                        if($criteria['qualification_id']){
                            $count = 0;
                        foreach($rowArray as $row){
                            $count++;
                               // Helper2::jLog("$count primary qual selected is ".$qualification.' the one we want is '.$row['primary_qualification_option_id'].PHP_EOL);
                           
                            if($row['primary_qualification_option_id'] === $qualification){
                                $resultData[] = $row;
                                }
                        }
                        }else{
                            $resultData = $rowArray;
                        }
                        $rowArray = $resultData;
			if ($criteria ['outputType']) {
				$this->sendData ( $rowArray );
			}
                         //Helper2::jLog(print_r($rowArray,true));

			foreach($rowArray as $key => $row) {
				if ( isset( $row['gender'] ) and $row['gender'] ) {
					$rowArray[$key]['gender'] = t(trim($row['gender']));
				}
			}

			$this->viewAssignEscaped ( 'results', $rowArray );
                      
			$this->view->assign ( 'count', count ( $rowArray ) );
			
		}

		$this->view->assign ( 'criteria', $criteria );

		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll() );


		//training titles
		require_once ('models/table/TrainingTitleOption.php');
		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $titleArray );
		//types
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );

                $allPersonStatus = $person->getAllPersonStatus();
                //print_r($allPersonStatus);exit;
                $this->viewAssignEscaped('person_status',$allPersonStatus);
		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 999999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}


	public function reportsAction() {
		$this->doMySearch();
	}

	public function doMySearch() {
		$helper = new Helper();
		require_once ('models/table/Person.php');

		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}

		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facility_name']				= $this->getSanParam ( 'facility_name' );
		$criteria ['facilityInput']				= $this->getSanParam ( 'facilityInput' );
		$criteria ['first_name']				= $this->getSanParam ( 'first_name' );
		$criteria ['last_name']					= $this->getSanParam ( 'last_name' );
		$criteria ['training_title_option_id']	= $this->getSanParam ( 'training_title_option_id' );
		$criteria ['persal']					= $this->getSanParam ( 'persal' );

		$criteria ['person_type']				= $this->getSanParam ( 'person_type' );
		$criteria ['is_complete']				= $this->getSanParam ( 'is_complete' );
		if (! $criteria ['person_type']){
			$criteria ['person_type'] = 'is_participant';
		}

		// $criteria['type_id'] = $this->getSanParam('trainer_type_id');
		$criteria ['qualification_id']			= $this->getSanParam ( 'qualification_id' );
		$criteria ['outputType']				= $this->getSanParam ( 'outputType' );
		// $criteria['language_id'] = $this->getSanParam('trainer_language_id');

		$criteria ['go']						= $this->getSanParam ( 'go' );

		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			if ($criteria ['person_type'] == 'is_everyone') {
				// left join instead of inner for everyone
				$sql = '
				SELECT DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, p.birthdate, q.qualification_phrase,p.active, '.implode(',',$field_name).'
				,q.parent_id, (SELECT COUNT(`comp`.`id`) FROM `comp` WHERE `comp`.`person` = `p`.`id` AND `comp`.`active` = \'Y\') `cmp`,p.comments as "persal",IFNULL(cmpr.res,10) `res` FROM person as p
				LEFT JOIN person_qualification_option as q ON p.primary_qualification_option_id = q.id
				LEFT JOIN facility as f ON p.facility_id = f.id
				LEFT JOIN compres as cmpr ON cmpr.person = p.id AND cmpr.active=\'Y\'
				LEFT JOIN ('.$location_sub_query.') as l ON f.location_id = l.id  ';

			} else {
				# removing from query, filtering happens on front-end
				#if($criteria ['is_complete']=='is_complete'){
				#	$sql = 'select DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, p.birthdate, q.qualification_phrase, '.implode(',',$field_name).',q.parent_id, (SELECT COUNT(`comp`.`id`) FROM `comp` WHERE `comp`.`person` = `p`.`id` AND `comp`.`active` = \'Y\') `cmp`,p.comments as "persal", cmpr.res from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, compres as cmpr';
				#} else {
					$sql = 'select DISTINCT p.id, p.last_name, p.middle_name, p.first_name, p.gender, p.birthdate, q.qualification_phrase,p.active, '.implode(',',$field_name).',q.parent_id, (SELECT COUNT(`comp`.`id`) FROM `comp` WHERE `comp`.`person` = `p`.`id` AND `comp`.`active` = \'Y\') `cmp`,p.comments as "persal", IFNULL((SELECT `res` FROM `compres` WHERE `compres`.`person` = `p`.`id` AND `compres`.`active` = \'Y\'),10) `res` from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l';
				#}
			}

			if ($criteria ['person_type'] == 'is_participant'){
				$sql .= ', person_to_training as ptt ';
			}
			if ($criteria ['person_type'] == 'is_trainer') {
				$sql .= ', trainer as trn';
				if ( $criteria['training_title_option_id'] ){
					$sql .= ', training_to_trainer as ttt ';
				}
			}

			$where = array('p.is_deleted = 0');/* 'f.location_id = l.id'); */ // bugfix - is shrinkings results

			if ($criteria ['person_type'] != 'is_everyone') { // was left joined (see above)
				$where []= 'p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
			}

			//$locationWhere = $this->getLocationCriteriaWhereClause($criteria, '', 'pt'); //gnr: May 9, 2014 drop pt 
			$locationWhere = $this->getLocationCriteriaWhereClause($criteria, '', '');
			if ($locationWhere) {
				$where[] = $locationWhere;
			}
                       // $where[] = "p.is_deleted=0";

			# removing from query, filtering happens on front-end
			#if($criteria ['is_complete']=='is_complete'){
			#	$where []= 'cmpr.person = p.id';
			#}
			if ($criteria ['person_type'] == 'is_participant'){
				$where []= 'p.id = ptt.person_id ';
			}
			if ($criteria ['person_type'] == 'is_trainer'){
				$where []= 'p.id = trn.person_id ';
			}

			if ($criteria ['person_type'] == 'is_unattached_person'){
				$where []= 'p.id NOT IN (SELECT person_id FROM trainer) AND  p.id NOT IN (SELECT person_id FROM person_to_training)  ';
			}

			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}

			if ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$sql .= ', training as tr  ';
				if ($criteria ['person_type'] == 'is_participant'){
					$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
				}
				if ($criteria ['person_type'] == 'is_trainer'){
					$where []= ' p.id = trn.person_id AND trn.person_id = ttt.trainer_id AND ttt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
				}
			}

			if ($criteria ['qualification_id'] or $criteria ['qualification_id'] === '0') {
				$where []= '(primary_qualification_option_id = ' . $criteria ['qualification_id'] . ' OR primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ')) ';
			}
			if ($criteria ['first_name']) {
				$where []= 'p.first_name LIKE "' . $criteria ['first_name'] . '%"';
			}
			if ($criteria ['last_name']) {
				$where []= 'p.last_name LIKE "' . $criteria ['last_name'] . '%"';
			}

			# removing from query, filtering happens on front-end
			#if($criteria ['is_complete']=='is_complete'){
			#	$where []= 'cmpr.active = \'Y\'';
			#}
			#if($criteria ['is_complete']=='is_complete'){
			#	$where []= 'cmpr.res = 0';
			#}

			if ($where){
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}

			$sql .= " ORDER BY " . " `p`.`last_name` ASC, " . " `p`.`first_name` ASC";
                         //Helper2::jLog($logMessage);

			#echo "Search query = " . $sql . "<br>";

			$rowArray = $db->fetchAll ( $sql );

			foreach($rowArray as $key => $row) {
				if (strpos($rowArray[$key]['qualification_phrase'],"_Ot")==1){
					$rowArray[$key]['qualification_phrase'] = substr($rowArray[$key]['qualification_phrase'],2);
				}
				if (strpos(strrev($rowArray[$key]['qualification_phrase']),"_")==1){
					$rowArray[$key]['qualification_phrase'] = strrev(substr(strrev($rowArray[$key]['qualification_phrase']),2));
				}
			}

			$_arr = array();
			foreach ($rowArray as $key=>$row){
				$comps = $helper->getPersonCompetencies($row['id'], true);
				$row['comps'] = $comps;
				$_arr[$key] = $row;
			}
			$rowArray = $_arr;

			foreach($rowArray as $key => $row) {
				$splt=split("?",$rowArray[$key]['persal']);
				if(strlen($splt[1])>0) {
					$rowArray[$key]['persal'] = $splt[1];
					if(strlen($criteria['persal'])>0){
						if(strcasecmp($splt[1],$criteria['persal'])==0){
						} else {
							unset($rowArray[$key]);
						}
					}
				} else {
					$rowArray[$key]['persal'] = '';
				}
			}

			if ($criteria ['outputType']) {
				foreach($rowArray as $key => $row) {
					unset($rowArray[$key]['cmp']);
					unset($rowArray[$key]['parent_id']);
				}

				$this->sendData ( $rowArray );
			}


			foreach($rowArray as $key => $row) {
				if ( isset( $row['gender'] ) and $row['gender'] ) {
					$rowArray[$key]['gender'] = t(trim($row['gender']));
				}
			}


			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->view->assign ( 'count', count ( $rowArray ) );
		}

		$this->view->assign ( 'criteria', $criteria );

		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll() );


		//training titles
		require_once ('models/table/TrainingTitleOption.php');
		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $titleArray );
		//types
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );

		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}


	public function historyAction() {
		$person_id = $this->getSanParam ( 'id' );
		//history
		require_once ('models/table/History.php');
		$history = new History ( 'person' );
		$changes = $history->fetchAllPerson ( $person_id );
		foreach ( $changes as $ck => $crow ) {
			$c = $crow ['changes'];
			$keys = array ();
			$values = array ();
			if (!isset($this->setting['display_mod_skillsmart']) || ($this->setting['display_mod_skillsmart'] !== 1)){
				unset ( $c ['pvid']);
			}
			unset ( $c ['vid'] );
			unset ( $c ['id'] );
			unset ( $c ['timestamp_created'] );
			unset ( $c ['timestamp_updated'] );
			unset ( $c ['created_by'] );
			unset ( $c ['modified_by'] );
			unset ( $c ['active_trainer_option_id'] );
			unset ( $c ['is_active'] );
			unset ( $c ['secondary_responsibility_option_id'] );
			unset ( $c ['primary_responsibility_option_id'] );
			unset ( $c ['primary_qualification_option_id'] );
			unset ( $c ['facility_id'] );
			unset ( $c ['affiliation_option_id']);
			if ($this->setting('display_mod_skillsmart')){
				if(count($c)==0)
				{
					unset($changes[$ck]);
					continue;
				}
			}
			foreach ( $c as $k => $v ) {
				$translated = htmlentities ( ITechTranslate::db_tr ( $k ) );
				//	if ((strstr ( $translated, '_' ) == false)) {
				$keys [] = $translated;
				if ($this->setting('display_mod_skillsmart')){
					if(strpos($v,"_Oth")>0)
					{
						$rmi=strrev($v);
						if(strpos($rmi,"_")==1)
						{
							$v=strrev(substr($rmi,2));
						}
					}
					$values [] = htmlentities ( str_replace("Z_Other (", "Other (", $v) );
				} else {
					$values [] = htmlentities ( $v );
				}
				//	}
			}
			$changes [$ck] ['translated_keys'] = implode ( ', ', $keys );
			$changes [$ck] ['translated_values'] = implode ( ', ', $values );
		}

		if ($this->setting('display_mod_skillsmart')){
			$changes=array_values($changes);
		}
		$this->sendData ( $changes );
	}

	public function custom1ListAction() {
		require_once ('models/table/OptionList.php');
		$rowArray = OptionList::suggestionList ( 'person_custom_1_option', 'custom1_phrase', $this->getSanParam ( 'query' ) );

		$this->sendData ( $rowArray );
	}

	public function custom2ListAction() {
		require_once ('models/table/OptionList.php');
		$rowArray = OptionList::suggestionList ( 'person_custom_2_option', 'custom2_phrase', $this->getSanParam ( 'query' ) );

		$this->sendData ( $rowArray );
	}

	private function _attach_locations($rowArray) {
		if ( $rowArray ) {
			$num_tiers = $this->setting('num_location_tiers');
			$locations = Location::getAll();
			foreach($rowArray as $id => $row) {
				$region_ids = Location::getCityInfo($row['location_id'], $num_tiers); //todo rUserious? ::getcityinfo is such an expensive call(I think), it loads the entire Location table every call... todo investigate
				$rowArray[$id]['province_name'] = $locations[$region_ids['province_id']]['name'];
				$rowArray[$id]['district_name'] = $locations[$region_ids['district_id']]['name'];
				$rowArray[$id]['region_c_name'] = $locations[$region_ids['region_c_id']]['name'];
				$rowArray[$id]['region_d_name'] = $locations[$region_ids['region_d_id']]['name'];
				$rowArray[$id]['region_e_name'] = $locations[$region_ids['region_e_id']]['name'];
				$rowArray[$id]['region_f_name'] = $locations[$region_ids['region_f_id']]['name'];
				$rowArray[$id]['region_g_name'] = $locations[$region_ids['region_g_id']]['name'];
				$rowArray[$id]['region_h_name'] = $locations[$region_ids['region_h_id']]['name'];
				$rowArray[$id]['region_i_name'] = $locations[$region_ids['region_i_id']]['name'];
			}
		}

		return $rowArray;
	}


	/**
	* autocomplete ajax (person)
	*/
	public function lastListAction() {
		require_once ('models/table/Person.php');
		$rowArray = Person::suggestionQuery ( $this->_getParam ( 'query' ), 100, 'last_name', array ('p.last_name','p.facility_id' ) )->toArray ();
		//$rowArray = $this->_attach_locations($rowArray);

		$this->sendData ( $rowArray );
	}

	/**
	* autocomplete ajax (person)
	*/
	public function firstListAction() {
            
		require_once ('models/table/Person.php');
                 
		$rowArray = Person::suggestionQuery ( $this->_getParam ( 'query' ), 100, 'first_name', array ('p.first_name','p.facility_id' ) )->toArray ();
		
//$rowArray = $this->_attach_locations($rowArray);
		$this->sendData ( $rowArray );
	}

	/**
	* dupe check
	*/
	public function dupeListAction() {
		require_once ('models/table/Person.php');
		$fieldAnd = array ();

		if ($this->_getParam ( 'first_name' ))
			$fieldAnd ["p.first_name"] = trim ( $this->_getParam ( 'first_name' ) );

		if ($this->_getParam ( 'last_name' ))
			$fieldAnd ["p.last_name"] = trim ( $this->_getParam ( 'last_name' ) );

		if ($this->_getParam ( 'gender' ))
			$fieldAnd ["p.gender"] = $this->_getParam ( 'gender' );

		if ($this->_getParam ( 'national_id' ))
			$fieldAnd ["p.national_id"] = trim ( $this->_getParam ( 'national_id' ) );

		if ($this->_getParam ( 'file_number' ))
			$fieldAnd ["p.file_number"] = trim ( $this->_getParam ( 'file_number' ) );

		if ($this->_getParam ( 'primary_qualification_option_id' ))
			$fieldAnd ["p.primary_qualification_option_id"] = trim ( $this->_getParam ( 'primary_qualification_option_id' ) );

		$rowArray = Person::suggestionFindDupes ( $this->_getParam ( 'last_name' ), 50, $this->setting ( 'display_middle_name_last' ), $fieldAnd );

		foreach ( $rowArray as $key => $row ) {
			//$rowArray [$key] = array_merge ( array ('input' => '<a href="'.Settings::$COUNTRY_BASE_URL.'/person/edit/id/'.$rowArray [$key] ['id'].'">'.$rowArray[$key]['id'].'</a>' ), $row );
			$rowArray [$key] = array_merge ( array ('input' => '<input type="radio" name="dupe_id" value="' . $rowArray [$key] ['id'] . '">' ), $row );
		}

		$this->sendData ( $rowArray );
	}

	// redirect back to training session
	function trainingRedirect($person_id) {
		$training_id = $this->_getParam ( 'trainingredirect' );

		// first, add trainer/person to training session
		if ($this->view->mode == 'add' || $this->getSanParam ( 'maketrainer' )) {

			if ($this->getSanParam ( 'is_trainer' ) || $this->getSanParam ( 'trainer_type_option_id' ) || $this->getSanParam ( 'active_trainer_option_id' )) { // trainer


				require_once ('models/table/TrainingToTrainer.php');
				$result = TrainingToTrainer::addTrainerToTraining ( $person_id, $training_id, $days = ($this->getSanParam ( 'days' ) ? $this->getSanParam ( 'days' ) : 0) );
                            
			} else { // person
				require_once ('models/table/PersonToTraining.php');
				$tableObj = new PersonToTraining ( );
                               // echo $person_id.' training id '.$training_id;
				$result = $tableObj->addPersonToTraining ( $person_id, $training_id,"");
                                //echo 'hello person to training';exit;
			}
		}

		$status = ValidationContainer::instance ();
		$status->setRedirect ( '/training/edit/id/' . $training_id );
	}

	/**
	* List trainings and allow person to be assigned
	*/
	public function assignTrainingAction() {
		$id = $this->getSanParam ( 'id' );
		$this->view->assign ( 'id', $id );
		$maxAge = $this->getSanParam ( 'maxage' );
		$this->view->assign ( 'maxage', $maxAge );

		$person = new Person ( );
		$rows = $person->find ( $id );
		$row = $rows->current ();
		$this->view->assign ( 'person_row', $row );

		require_once 'models/table/Training.php';
		$tableObj = new Training ( );
		$age = "training_start_date < NOW() AND training_start_date > DATE_SUB(NOW(), INTERVAL 30 DAY)";
		switch($maxAge){
			case '60days':
				$age = "training_start_date < NOW() AND training_start_date > DATE_SUB(NOW(), INTERVAL 60 DAY)";
			break;
			case '1quarter':
				$age = "training_start_date < NOW() AND training_start_date > DATE_SUB(NOW(), INTERVAL 96 DAY)";
			break;
			case '6months':
				$age = "training_start_date < NOW() AND training_start_date > DATE_SUB(NOW(), INTERVAL 185 DAY)";
			break;
			case 'all':
				$age = '1';
			break;
		}
		$trainings = $tableObj->getTrainingsAssign ( $id , $age);
		foreach ( $trainings as $k => $r ) {
			$trainings [$k] ['input_checkbox'] = '<input type="checkbox" name="training_ids[]" value="' . $r ['training_id'] . '">';
		}
		$this->view->assign ( 'trainings', $trainings );
		
		//TA:17: 08/28/2014
		require_once 'models/table/System.php';
		$sysTable = new System();
		$sysRows = $sysTable->fetchAll()->toArray();
		foreach($sysRows as $row) {
			foreach($row as $column=>$value) {
				if($column == 'display_training_category' && $value != '0'){
					$this->view->assign( 'display_training_category', 'display_training_category' );
				}
				if($column == 'display_training_start_date' && $value != '0'){
					$this->view->assign( 'display_training_start_date', 'display_training_start_date' );
				}
			}
		}
		

		$request = $this->getRequest ();
		// save
		if ($request->isPost ()) {
			$status = ValidationContainer::instance ();
			$training_ids = $this->getSanParam ( 'training_ids' );
			if ($training_ids) {
				require_once ('models/table/PersonToTraining.php');

				foreach ( $training_ids as $training_id ) {
					$tableObj = new PersonToTraining ( );
					$result = $tableObj->addPersonToTraining ( $id, $training_id,"" );
				}

				$status->setStatusMessage ( t ('The').' '.t('Training').t('(s) have been assigned.' ) );
				$status->setRedirect ( '/person/edit/id/' . $id );
			} else {
				$status->setStatusMessage ( t ( 'No').' '.t('Trainings').' '.t('selected').'.' );
			}

			$this->sendData ( $status );
		}
	}

	/********************************************/
	/* Import a person                          */
	/********************************************/
     public function importAction() {

		$this->view->assign('pageTitle', t( 'Import a person' ));
		require_once('models/table/TrainingToTrainer.php');

		// template redirect
		if ( $this->getSanParam('download') )
			return $this->importTrainingTemplateAction();

		if( ! $this->hasACL('import_person') )
			$this->doNoAccessError ();

		//CSV STUFF
		$filename = ($_FILES['upload']['tmp_name']);
		if ( $filename )
		{
			$db = $this->dbfunc();
			$personObj = new Person ();
			$errs = array();
			while ($row = $this->_csv_get_row($filename) )
			{
				$values = array();
				if (! is_array($row) )
					continue;		   // sanity?
				if (! isset($cols) ) { // set headers (field names)
					$cols = $row;	   // first row is headers (field names)
					continue;
				}
				$countValidFields = 0;
				if (! empty($row) ) {  // add
					foreach($row as $i=>$v){ // proccess each column
						if ( empty($v) && $v !== '0' )
							continue;
						if ( $v == 'n/a') // has to be able to process values from a data export
							$v = NULL;
						$countValidFields++;
						$delimiter = strpos($v, ','); // is this field a comma seperated list too (or array)?
						if ($delimiter && $v[$delimiter - 1] != '\\')	// handle arrays as field values(Export), and comma seperated values(import manual entry), and strings or int
							$values[$cols[$i]] = explode(',', $this->sanitize($v));
						else
							$values[$cols[$i]] = $this->sanitize($v);
					}
				}
				// done now all fields are named and in $values[my_field]
				if ( $countValidFields ) {
					//validate
					if ( isset($values['uuid']) ){ unset($values['uuid']); }
					if ( isset($values['id']) )  { unset($values['id']); }
					if ( isset($values['is_deleted']) ) { unset($values['is_deleted']); }
					if ( isset($values['created_by']) ) { unset($values['created_by']); }
					if ( isset($values['modified_by']) ){ unset($values['modified_by']); }
					if ( isset($values['timestamp_created']) ){ unset($values['timestamp_created']); }
					if ( isset($values['timestamp_updated']) ){ unset($values['timestamp_updated']); }
					if ( ! $this->hasACL('approve_trainings') ){ unset($values['approved']); }
					$values['birthdate'] = $this->_date_to_sql($values['birthdate']);
					$values['facility_id'] = $values['facility_id'] ? $values['facility_id'] : 0;

					//locations
					$regionNames = array (t('Region A (Province)'), t('Region B (Health District)'), t('Region C (Local Region)'), t('Region D'), t('Region E'), t('Region F'), t('Region G'), t('Region H'), t('Region I') );
					$num_location_tiers = $this->setting('num_location_tiers');
					$bSuccess = true;
					$facility_id = null;
					$fac_location_id = null;

					if ( $values['facility_name'] ) { // something set for facility (name or id) (id is duplicated to name to support importing from a data export.... TODO clean this up now that both fields are supported in this function)

						if (! $values['facility_id']) { // get the id somehow

							if(is_array($values['facility_name']))
								$values['facility_id'] = $values['facility_name'][0]; //
							else if ( is_numeric($values['facility_name']) && !trim( $values[ t('Region A (Province)') ] ) ) // bugfix: numbers w/ no province = ID, numbers + location data = Fac Name all numbers... its in facility_name b/c of data export
								$values['facility_id'] = $values['facility_name']; // support export'ed values. (remap)
							else // lookup id
							{
								// verify location, do not allow insert
								$tier = 1;
								for ($i=0; $i <= $num_location_tiers; $i++) { // find locations
									$regionName = $regionNames[$i]; // first location field in csv row // could use this too: $values[t('Region A (Province)')]
									if ( empty($values[$regionName]) || $bSuccess == false )
										continue;
									$fac_location_id = $db->fetchOne(
										"SELECT id FROM location WHERE location_name = '". $values[$regionName] . "'"
										. ($fac_location_id ? " AND parent_id = $fac_location_id " : '')
										. " LIMIT 1");
									if (! $fac_location_id) {
										$bSuccess = false;
										break;
									}
									$tier++;
								}

								// lookup facility
								if ($fac_location_id) {
									$facility_id = $db->fetchOne( "SELECT id FROM facility WHERE location_id = $fac_location_id AND facility_name = '".$values['facility_name']."' LIMIT 1" );
									$values['facility_id'] = $facility_id ? $facility_id : 0;
								} else {
									$errs[] = t('Error locating region or city:').' '.$values[$regionName].' '.t('Facility').': '.$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
								}
								if (! $values['facility_id'] && $bSuccess) { // found region(bSuccess) but not facility
									$errs[] = t('Error locating facility:').space.$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
								}
							}
						}
					} else {
						if (! $values['facility_id'])
							$errs[] = t('Error locating facility:').$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
					}
					$bSuccess = true; //reset, we allow saving with no facility.

					//dupecheck
					$dupe = new Person();
					$select = $dupe->select()->where('facility_id = "' . $values['facility_id'] . '" and first_name = "' . $values['first_name'] . '" and last_name = "'.$values['last_name'].'"');
					if( $values['facility_id'] && $dupe->fetchRow($select) ) {
						$errs[] = t ( 'A person with this name already exists in the database, the user was not added.' ).space.t('Name').': '.$values['first_name'].space.$values['last_name'];
						$bSuccess = false;
					}
					if(! $bSuccess)
						continue;

					//field mapping (Export vs import)
					if ( isset($values["qualification_phrase"]) )            $values["primary_qualification_option_id"] = $values["qualification_phrase"];
					if ( isset($values["primary_qualification_phrase"]) )    $values["primary_qualification_option_id"] = $values["primary_qualification_phrase"];
					if ( isset($values["primary_responsibility_phrase"]) )   $values["primary_responsibility_option_id"] = $values["primary_responsibility_phrase"];
					if ( isset($values["secondary_responsibility_phrase"]) ) $values["secondary_responsibility_option_id"] = $values["secondary_responsibility_phrase"];
					if ( isset($values["highest_edu_level_phrase"]) )        $values["highest_edu_level_option_id"] = $values["highest_edu_level_phrase"];
					if ( isset($values["attend_reason_phrase"]) )            $values["attend_reason_option_id"] = $values["attend_reason_phrase"];
					if ( isset($values["custom_1"]) )                        $values["person_custom_1_option_id"] = $values["custom_1"];
					if ( isset($values["custom_2"]) )                        $values["person_custom_2_option_id"] = $values["custom_2"];
					//save
					try {
						//$values['title_option_id']                    = $this->_importHelperFindOrCreate('person_title_option',           'title_phrase',           $values['title_option_id']); //title_option_id multiAssign (insert via helper)
						//$values['suffix_option_id']                   = $this->_importHelperFindOrCreate('person_suffix_option',          'suffix_phrase',          $values['suffix_option_id']);
						$values['primary_qualification_option_id']    = $this->_importHelperFindOrCreate('person_qualification_option',   'qualification_phrase',   $values['primary_qualification_option_id']);
						$values['primary_responsibility_option_id']   = $this->_importHelperFindOrCreate('person_responsibility_option',  'responsibility_phrase',  $values['primary_responsibility_option_id']);
						$values['secondary_responsibility_option_id'] = $this->_importHelperFindOrCreate('person_secondary_responsibility_option',  'responsibility_phrase', $values['secondary_responsibility_option_id']);
						$values['attend_reason_option_id']            = $this->_importHelperFindOrCreate('person_attend_reason_option',   'attend_reason_phrase',   $values['attend_reason_option_id']);
						$values['person_custom_1_option_id']          = $this->_importHelperFindOrCreate('person_custom_1_option',        'custom1_phrase',         $values['person_custom_1_option_id']);
						$values['person_custom_2_option_id']          = $this->_importHelperFindOrCreate('person_custom_2_option',        'custom2_phrase',         $values['person_custom_2_option_id']);
						$values['highest_level_option_id']            = $this->_importHelperFindOrCreate('person_education_level_option', 'education_level_phrase', $values['highest_level_option_id']);
						//$values['courses']                            = $this->_importHelperFindOrCreate('???',         '?????', null, $values['courses']);
                                                
						$personrow = $personObj->createRow();
						$personrow = ITechController::fillFromArray($personrow, $values);
						$row_id = $personrow->save();
					} catch (Exception $e) {
						$errored = 1;
						$errs[]  = nl2br($e->getMessage()).' '.t ( 'ERROR: The person could not be saved.' );
					}
					if(! $row_id){
						$errored = 1;
						$errs[] = t('That person could not be saved.').space.t("Name").": ".$values['first_name'].space.$values['last_name'];
					}
					//sucess - done
				}//loop
			}
			// done processing rows
			$_POST['redirect'] = null;
			if( empty($errored) && empty($errs) )
				$stat = t ('Your changes have been saved.');
			else
				$stat = t ('Error importing data. Some data may have been imported and some may not have.');

			foreach($errs as $errmsg)
				$stat .= '<br>'.'Error: '.htmlspecialchars($errmsg, ENT_QUOTES);

			$status = ValidationContainer::instance();
			$status->setStatusMessage($stat);
			$this->view->assign('status', $status);
		}
		// done with import
	}

	/**
	* A template for importing a training
	*/
	public function importTrainingTemplateAction() {
			$sorted = array (
				array (
				"id"                          => '',
				"uuid"                        => '',
				"first_name"                  => '',
				"middle_name"                 => '',
				"last_name"                   => '',
				"national_id"                 => '',
				"file_number"                 => '',
				"birthdate"                   => '',
				"gender"                      => '',
				"phone_work"                  => '',
				"phone_mobile"                => '',
				"fax"                         => '',
				"phone_home"                  => '',
				"email"                       => '',
				"email_secondary"             => '',
				"comments"                    => '',
				"home_address_1"              => '',
				"home_address_2"              => '',
				"home_postal_code"            => '',
				"active"                      => '',
				"timestamp_created"           => '',
				"timestamp_updated"           => '',
				"created_by"                  => '',
				"modified_by"                 => '',
				"is_deleted"                  => '',
				"qualification_phrase"        => '',
				"me_responsibility"           => '',
				"primary_responsibility_phrase"    => '',
				"secondary_responsibility_phrase"  => '',
				"highest_edu_level_phrase"    => '',
				"attend_reason_phrase"        => '',
				"attend_reason_other"         => '',
				"custom_1"                    => '',
				"custom_2"                    => '',
				"custom_3"                    => '',
				"custom_4"                    => '',
				"custom_5"                    => '',
				"facility_name"               => ''
				));

		// add some regions
		$num_location_tiers = $this->setting('num_location_tiers');
		$regionNames = array ('', t('Region A (Province)'), t('Region B (Health District)'), t('Region C (Local Region)'), t('Region D'), t('Region E'), t('Region F'), t('Region G'), t('Region H'), t('Region I') );
		for ($i=1; $i < $num_location_tiers; $i++) {
			//add regions
			$sorted[0][$regionNames[$i]] = '';
		}
		// add city region
		$sorted[0][t('City')] = '';


		//done, output a csv
		if( $this->getSanParam('outputType') == 'csv' )
			$this->sendData ( $this->reportHeaders ( false, $sorted ) );

	}   
        
        
        
        
        
        
        
        
        
        
        
        
        //-------------------------------------------old way of doing things
        /*
	public function importAction() {

		$this->view->assign('pageTitle', t( 'Import a person' ));
		require_once('models/table/TrainingToTrainer.php');

		// template redirect
		if ( $this->getSanParam('download') )
			return $this->importTrainingTemplateAction();

		if( ! $this->hasACL('import_person') )
			$this->doNoAccessError ();
 //echo  'This is the file we need'.file_get_contents('libs/simplexlsx.class.php');
             //'This is the file we need'.file_get_contents('libs/simplexlsx.class.php');
		//CSV STUFF
		$filename = ($_FILES['upload']['tmp_name']); //TP: getting the filename from the tmp folder of the system
		if ( $filename )
		{
			$db = $this->dbfunc();
			$personObj = new Person ();
			$errs = array();
                         $xlsx = new SimpleXLSX($filename); // creating a new instance of the SimpleXLSX class for reading our xlsx which has been uploaded
                         
                         $rows = $xlsx->rows(); //TP: reading the rows in the excel sheet into an array called rows
                         $length = sizeof($rows); //TP:  getting the size of the $rows to be used in the for loop
                        
                        
                         /*
                          * TP: Creating an array of the header names of the excel sheet. This array also contains values that are to be used as index in $values array. These values are also the same as the database fieldnames of these cell names
                          *//*
                        $check = array("sn","first_name","middle_name","last_name","gender","qualification_phrase","facility_name","Local Government Area","State","phone_mobile","email","certification","years_of_service");
                            
                        /*
                         * TP: A for loop is being initiated. This for loop starts from 35. This is because the value in the excel sheet starts from row 35. We are trying to skip other non-relevant rows
                         */
        /*
			for($p=35;$p<=$length; $p++ )
			{
                            //TP collecing pushing the value of each row into another array which is to be used later in the code
                            $row= $rows[$p];
                            //TP: checking a compulsory field (gender). If it is not filled, the system jumps this rows and proceeds to the next row
                            if($row[4]=="" || $row[4]==" "){
                               
                               continue;
                               
                           }
                           //TP: setting up a countValidFields variables to stand as a checker for the see if there are valid fields.
                            $countValidFields = "";
                          $lt = sizeof($check);
                          $values = array();
                          for($i=0;$i<$lt;$i++){
                              //TP: assinging values to array $values with index which is equivalent to the names of the fieldname used to store them in the database
                              $values[$check[$i]] = $row[$i];
                               $countValidFields = 0;
                          }
                          
                           //print_r($values);
                           
			if ( $countValidFields==0 ) {
                            //echo 'Hello We are here';
					//validate
					if ( isset($values['uuid']) ){ unset($values['uuid']); }
					if ( isset($values['id']) )  { unset($values['id']); }
					if ( isset($values['is_deleted']) ) { unset($values['is_deleted']); }
					if ( isset($values['created_by']) ) { unset($values['created_by']); }
					if ( isset($values['modified_by']) ){ unset($values['modified_by']); }
					if ( isset($values['timestamp_created']) ){ unset($values['timestamp_created']); }
					if ( isset($values['timestamp_updated']) ){ unset($values['timestamp_updated']); }
					if ( ! $this->hasACL('approve_trainings') ){ unset($values['approved']); }
					$values['birthdate'] = $this->_date_to_sql($values['birthdate']);
					$values['facility_id'] = $values['facility_id'] ? $values['facility_id'] : 0;
                                         
					//locations
					$regionNames = array (t('Region A (Province)'), t('Region B (Health District)'), t('Region C (Local Region)'), t('Region D'), t('Region E'), t('Region F'), t('Region G'), t('Region H'), t('Region I') );
					$num_location_tiers = $this->setting('num_location_tiers');
					$bSuccess = true;
					$facility_id = null;
					$fac_location_id = null;

					if ( $values['facility_name'] ) { // something set for facility (name or id) (id is duplicated to name to support importing from a data export.... TODO clean this up now that both fields are supported in this function)

						if (! $values['facility_id']) { // get the id somehow

							if(is_array($values['facility_name']))
								$values['facility_id'] = $values['facility_name'][0]; //
							else if ( is_numeric($values['facility_name']) && !trim( $values[ t('Region A (Province)') ] ) ) // bugfix: numbers w/ no province = ID, numbers + location data = Fac Name all numbers... its in facility_name b/c of data export
								$values['facility_id'] = $values['facility_name']; // support export'ed values. (remap)
							else // lookup id
							{
								// verify location, do not allow insert
								$tier = 1;
								for ($i=0; $i <= $num_location_tiers; $i++) { // find locations
									$regionName = $regionNames[$i]; // first location field in csv row // could use this too: $values[t('Region A (Province)')]
									if ( empty($values[$regionName]) || $bSuccess == false )
										continue;
									$fac_location_id = $db->fetchOne(
										"SELECT id FROM location WHERE location_name = '". $values[$regionName] . "'"
										. ($fac_location_id ? " AND parent_id = $fac_location_id " : '')
										. " LIMIT 1");
									if (! $fac_location_id) {
										$bSuccess = false;
										break;
									}
									$tier++;
								}

								// lookup facility
								if ($fac_location_id) {
									$facility_id = $db->fetchOne( "SELECT id FROM facility WHERE location_id = $fac_location_id AND facility_name = '".$values['facility_name']."' LIMIT 1" );
									$values['facility_id'] = $facility_id ? $facility_id : 0;
								} else {
									$errs[] = t('Error locating region or city:').' '.$values[$regionName].' '.t('Facility').': '.$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
								}
								if (! $values['facility_id'] && $bSuccess) { // found region(bSuccess) but not facility
									$errs[] = t('Error locating facility:').space.$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
								}
							}
						}
					} else {
						if (! $values['facility_id'])
							$errs[] = t('Error locating facility:').$values['facility_name'].space.t("This person will have no assigned facility if the save is successful.");
					}
					$bSuccess = true; //reset, we allow saving with no facility.

					//dupecheck
					$dupe = new Person();
					$select = $dupe->select()->where('facility_id = "' . $values['facility_id'] . '" and first_name = "' . $values['first_name'] . '" and last_name = "'.$values['last_name'].'"');
					if( $values['facility_id'] && $dupe->fetchRow($select) ) {
						$errs[] = t ( 'A person with this name already exists in the database, the user was not added.' ).space.t('Name').': '.$values['first_name'].space.$values['last_name'];
						$bSuccess = false;
					}
					if(! $bSuccess)
						continue;

					//field mapping (Export vs import)
					if ( isset($values["qualification_phrase"]) )            $values["primary_qualification_option_id"] = $values["qualification_phrase"];
					if ( isset($values["primary_qualification_phrase"]) )    $values["primary_qualification_option_id"] = $values["primary_qualification_phrase"];
					if ( isset($values["primary_responsibility_phrase"]) )   $values["primary_responsibility_option_id"] = $values["primary_responsibility_phrase"];
					if ( isset($values["secondary_responsibility_phrase"]) ) $values["secondary_responsibility_option_id"] = $values["secondary_responsibility_phrase"];
					if ( isset($values["highest_edu_level_phrase"]) )        $values["highest_edu_level_option_id"] = $values["highest_edu_level_phrase"];
					if ( isset($values["attend_reason_phrase"]) )            $values["attend_reason_option_id"] = $values["attend_reason_phrase"];
					if ( isset($values["custom_1"]) )                        $values["person_custom_1_option_id"] = $values["custom_1"];
					if ( isset($values["custom_2"]) )                        $values["person_custom_2_option_id"] = $values["custom_2"];
					//save
					try {
						//$values['title_option_id']                    = $this->_importHelperFindOrCreate('person_title_option',           'title_phrase',           $values['title_option_id']); //title_option_id multiAssign (insert via helper)
						//$values['suffix_option_id']                   = $this->_importHelperFindOrCreate('person_suffix_option',          'suffix_phrase',          $values['suffix_option_id']);
						$values['primary_qualification_option_id']    = $this->_importHelperFindOrCreate('person_qualification_option',   'qualification_phrase',   $values['primary_qualification_option_id']);
						$values['primary_responsibility_option_id']   = $this->_importHelperFindOrCreate('person_responsibility_option',  'responsibility_phrase',  $values['primary_responsibility_option_id']);
						$values['secondary_responsibility_option_id'] = $this->_importHelperFindOrCreate('person_secondary_responsibility_option',  'responsibility_phrase', $values['secondary_responsibility_option_id']);
						$values['attend_reason_option_id']            = $this->_importHelperFindOrCreate('person_attend_reason_option',   'attend_reason_phrase',   $values['attend_reason_option_id']);
						$values['person_custom_1_option_id']          = $this->_importHelperFindOrCreate('person_custom_1_option',        'custom1_phrase',         $values['person_custom_1_option_id']);
						$values['person_custom_2_option_id']          = $this->_importHelperFindOrCreate('person_custom_2_option',        'custom2_phrase',         $values['person_custom_2_option_id']);
						$values['highest_level_option_id']            = $this->_importHelperFindOrCreate('person_education_level_option', 'education_level_phrase', $values['highest_level_option_id']);
						//$values['courses']                            = $this->_importHelperFindOrCreate('???',         '?????', null, $values['courses']);
						$personrow = $personObj->createRow();
						$personrow = ITechController::fillFromArray($personrow, $values);
						$row_id = $personrow->save();
					} catch (Exception $e) {
						$errored = 1;
						$errs[]  = nl2br($e->getMessage()).' '.t ( 'ERROR: The person could not be saved.' );
					}
					if(! $row_id){
						$errored = 1;
						$errs[] = t('That person could not be saved.').space.t("Name").": ".$values['first_name'].space.$values['last_name'];
					}
					//sucess - done
                                }	
                            
                          //$row[4];
                          }//loop
			
			// done processing rows
			$_POST['redirect'] = null;
			if( empty($errored) && empty($errs) )
				$stat = t ('Your changes have been saved.');
			else
				$stat = t ('Error importing data. Some data may have been imported and some may not have.');

			foreach($errs as $errmsg)
				$stat .= '<br>'.'Error: '.htmlspecialchars($errmsg, ENT_QUOTES);

			$status = ValidationContainer::instance();
			$status->setStatusMessage($stat);
			$this->view->assign('status', $status);
		}
		// done with import
	}

	/**
	* A template for importing a training
	*//*
	public function importTrainingTemplateAction() {
            
            /*
             * TP: We are changing the template format. We make sure that the users download already created format.
             */
		/*	$sorted = array (
				array (
				
				"first_name"                  => '',
				"middle_name"                 => '',
				"last_name"                   => '',
				"gender"                      => '',
                                "qualification_phrase"        => '',
                                "facility_name"               => '',
                                "Local Government Area"       => '',
                                "State"                       => '',
				"phone_mobile"                => '',
				"email"                       => '',
                                "certification"               => '',  
                                "years_left"                  => '',
							
				));
                        */
                        /*
                         * TP: This part is being commented because we do not need them in the format that will be generated
                         */
/*
		// add some regions
		$num_location_tiers = $this->setting('num_location_tiers');
		$regionNames = array ('', t('Region A (Province)'), t('Region B (Health District)'), t('Region C (Local Region)'), t('Region D'), t('Region E'), t('Region F'), t('Region G'), t('Region H'), t('Region I') );
		for ($i=1; $i < $num_location_tiers; $i++) {
			//add regions
			$sorted[0][$regionNames[$i]] = '';
		}
		// add city region
		$sorted[0][t('City')] = '';
*/
/*
		//done, output a csv
		if( $this->getSanParam('outputType') == 'csv' )
			$this->sendData ( $this->reportHeaders ( false, $sorted ) );
                */
            /*
             * TP: redirecting to where the template is located in html/templates folder
             */
                /*$this->_redirect ( "../sites/Hr_upload_template.xlsx" ) ;
	}
*/
        

}