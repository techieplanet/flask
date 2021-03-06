<?php
require_once ('ReportFilterHelpers.php');
require_once ('FacilityController.php');
require_once ('models/table/Person.php');
require_once ('models/table/Facility.php');
require_once ('models/table/OptionList.php');
require_once ('models/table/MultiOptionList.php');
require_once ('models/table/Location.php');
require_once ('models/table/MultiAssignList.php');
require_once ('views/helpers/FormHelper.php');
require_once ('views/helpers/DropDown.php');
require_once ('views/helpers/Location.php');
require_once ('views/helpers/CheckBoxes.php');
require_once ('views/helpers/TrainingViewHelper.php');
require_once ('models/table/Helper.php');
require_once ('models/table/Partner.php');

class EmployeeController extends ReportFilterHelpers {

	public function init() {	}

	public function preDispatch() {
		parent::preDispatch ();

		if (! $this->isLoggedIn ())
			$this->doNoAccessError ();

		if (! $this->setting('module_employee_enabled')){
			$_SESSION['status'] = t('The employee module is not enabled on this site.');
			$this->_redirect('select/select');
		}

		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}
	}

	public function indexAction() {

		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}

		require_once('models/table/dash-employee.php');
		$this->view->assign('title', $this->translation['Application Name'].space.t('Employee').space.t('Tracking System'));

		// restricted access?? does this user only have acl to view some trainings or people
		// they dont want this, removing 5/01/13
		$org_allowed_ids = allowed_org_access_full_list($this); // doesnt have acl 'training_organizer_option_all'?
		$allowedWhereClause = $org_allowed_ids ? " partner.organizer_option_id in ($org_allowed_ids) " : "";
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		$allowedWhereClause .= $site_orgs ? " AND partner.organizer_option_id in ($site_orgs) " : "";

		$partners = new DashviewEmployee();
		$details = $partners->fetchdetails($allowedWhereClause);
		$this->view->assign('getins',$details);

		/****************************************************************************************************************/
		/* Attached Files */
		require_once('views/helpers/FileUpload.php');

		$PARENT_COMPONENT = 'employee';

		FileUpload::displayFiles ( $this, $PARENT_COMPONENT, 1, $this->hasACL ( 'admin_files' ) );
		// File upload form
		if ( $this->hasACL ( 'admin_files' ) ) {
			$this->view->assign ( 'filesForm', FileUpload::displayUploadForm ( $PARENT_COMPONENT, 1, FileUpload::$FILETYPES ) );
		}
		/****************************************************************************************************************/
	}

	/**
	 * AJAX course add/delete/edit ... for employee_edit
	 *
	 * see: employee_course_table.phtml
	 */
	public function coursesAction()
	{
		try {
			if (! $this->hasACL ( 'employees_module' )) {
				if($this->_getParam('outputType') == 'json') {
					$this->sendData(array('msg'=>'Not Authorized'));
					exit();
					return;
				}
				$this->doNoAccessError ();
			}

			$ret    = array();
			$params = $this->getAllParams();

			if ($params['mode'] == 'addedit') {
				// add or update a record based on $params[id]
				if( empty($params['id']) )
					unset( $params['id'] ); // unset ID (primary key) for Zend if 0 or '' (insert new record)
				$id = $this->_findOrCreateSaveGeneric('employee_to_course', $params); // wrapper for find or create
				$params['id'] = $id;
				if($id){
					// saved
					// reload the data
					$db = $this->dbfunc();
					$ret = $db->fetchRow("select * from employee_to_course where id = $id");
					$ret['msg'] = 'ok';
				} else {
					$ret['errored'] = true;
					$ret['msg']     = t('Error creating record.');
				}
			}
			else if($params['mode'] == 'delete' && $params['id']) {
				// delete a record
				try {
					$course_link_table = new ITechTable ( array ('name' => 'employee_to_course' ) );
					$num_rows = $course_link_table->delete('id = ' . $params['id']);
					if (! $num_rows )
						$ret['msg'] = t('Error finding that record in the database.');
					$ret['num_rows'] = $num_rows;
				} catch (Exception $e) {
					$ret['errored'] = true;
					$ret['msg']     = t('Error finding that record in the database.');
				}
			}

			if(strtolower($params['outputType']) == 'json'){
				$this->sendData($ret); // probably always json no need to check for output
			}

		}
		catch (Exception $e) {
			if($this->_getParam('outputType') == 'json') {
				$this->sendData(array('errored' => true, 'msg'=>'Error: ' . $e->getMessage()));
				return;
			} else {
				echo $e->getMessage();
			}
		}
	}

	private function getCourses($employee_id){
		if (!$employee_id)
			return;

		$db = $this->dbfunc();
		$sql = "SELECT * FROM employee_to_course WHERE employee_id = $employee_id";
		$rows = $db->fetchAll($sql);
		return $rows ? $rows : array();
	}
	
	public function generateMechanismList($employee_id) {
	    $db = $this->dbfunc();
	    if (!$employee_id) {
    	    // generate a list of all mechanisms
    
	       $sql = "SELECT mechanism_option.id, mechanism_phrase, partner_to_subpartner_to_funder_to_mechanism.id as psfmid
	               from mechanism_option, partner_to_subpartner_to_funder_to_mechanism 
	               where mechanism_option.is_deleted = '0' and partner_to_subpartner_to_funder_to_mechanism.is_deleted = '0'
	               order by mechanism_phrase";
	       $mechanisms = $db->fetchAll($sql);
	    }
	    else {
	        $helper = new Helper();
	        
	        $employee = $helper->getEmployee($employee_id);
	        require_once 'views/helpers/EditTableHelper.php';
	        $partner_id = $employee[0]['partner_id'];
	        $sql = "SELECT mechanism_option.id, mechanism_phrase, partner_to_subpartner_to_funder_to_mechanism.id as psfmid
	               from mechanism_option, partner_to_subpartner_to_funder_to_mechanism
	               where partner_id = $partner_id and mechanism_option.is_deleted = '0' and partner_to_subpartner_to_funder_to_mechanism.is_deleted = '0'
	               order by mechanism_phrase";
	        $mechanisms = $db->fetchAll($sql);
	    }
	    foreach ($mechanisms as $i => &$mech)
	    {
	        $mech['combined_id'] = $mech['id'].'_'.$mech['psfmid'];
	    }
	    return $mechanisms;
	}
	
	public function generateMechanismTable($employee_id){
	    if (!$this->view['mechanismList']) 
	    {
	       $mechanisms = $this->generateMechanismList($employee_id);
	       $this->view->assign("mechanismList", $mechanisms);
	    }
	    else {
	        $mechanisms = $this->view['mechanismList'];
	    }
	    if (!$employee_id)
	    {
	        return;
	    }
	    
		$db     = $this->dbfunc();
	    $helper = new Helper();
	         
	    //exclude current funders
	    $employee = $helper->getEmployee($employee_id);
	    	
	    require_once 'views/helpers/EditTableHelper.php';
	    	
	    $tableValues = array();
	    $columnNames = array('mechanism' => t('Mechanism'),
	        'percentage' => t('Percent'),
	    );
	    $partner_id = $employee[0]['partner_id'];
	    
	    $sql = "SELECT annual_cost from employee where id = $employee_id";
	    $annual_cost = $db->fetchOne($sql);
	    
	    if ($this->setting('display_hours_per_mechanism'))
	    {
	        $columnNames['hours'] = t('Hours');
	    }
	    if ($this->setting('display_annual_cost_to_mechanism'))
	    {
	        $columnNames['cost'] = t('Annual Cost');
	    }
	    $sql = "SELECT employee_to_partner_to_subpartner_to_funder_to_mechanism.id as epsfmid, percentage, mechanism_phrase
	    FROM employee_to_partner_to_subpartner_to_funder_to_mechanism, mechanism_option
	    WHERE employee_to_partner_to_subpartner_to_funder_to_mechanism.is_deleted = 0 and mechanism_option_id = mechanism_option.id and employee_id = $employee_id";
	    	
	    $funders = $db->fetchAll($sql);
	    
	    foreach ($funders as $i => $row)
	    {
	        $tableValues[$i] = array('mechanism' => $row['mechanism_phrase'], 'percentage' => $row['percentage'], 'id' => $row['epsfmid']);
	        $percent = $row['percentage']/100.0;
	        if($this->setting('display_hours_per_mechanism'))
	        {
	            $tableValues[$i]['hours'] = $percent * $employee[0]['funded_hours_per_week'];
	        }
	        if($this->setting('display_annual_cost_to_mechanism'))
	        {
	            $employee_mechanism_cost = sprintf('%0.2f', $percent * $employee[0]['annual_cost']);
	            $tableValues[$i]['cost'] = $employee_mechanism_cost;
	        }
	    }
	    	
	    return(EditTableHelper::generateHtml('employeeFunding', $tableValues, $columnNames, array(), array(), true));
	     
	}
	
	public function addFunderToEmployeeAction() {
		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}
	
		require_once('models/table/Partner.php');
		require_once('views/helpers/Location.php'); // funder stuff
		require_once('models/table/Employee.php');
	   
		$db     = $this->dbfunc();
		$status = ValidationContainer::instance ();
		$params = $this->getAllParams();
		$id     = $params['id'];
			
		if ($id) {
			$helper = new Helper();
				
			if ( $this->getRequest()->isPost() ) {

			    if (!$this->hasACL("edit_employee"))
			    {
    				if($this->_getParam('outputType') == 'json') {
					   $this->sendData(array('msg'=>'Not Authorized'));
					   exit();
				    }
			        $this->doNoAccessError();
			    }
			    			    
			    if ($params['employeeFunding_delete_data'])
			    {
			        Employee::disassociateMechanismFromEmployee($params['employeeFunding_delete_data']);
			    }
			    
			    if ($params['employeeFunding_new_data'])
			    {
			        $data_to_add = json_decode($params['employeeFunding_new_data'], true);
			        if (!Employee::saveMechanismAssociation($id, $data_to_add['data']))
			        {
			            $status->setStatusMessage(t('Error saving mechanism association.'));
			            return false;
			        }
			    }
				if ( $status->hasError() )
					$status->setStatusMessage( t('That funding mechanism could not be saved.') );
					
				else {
					if ($params['redirect'])
					{
					   $this->_redirect($params['redirect']);
					}
				}
			}
				
			//exclude current funders
			$employee = $helper->getEmployee($id);
			$this->viewAssignEscaped ( 'employee', $employee );
			$this->view->assign ( 'tableEmployeeFunding', $this->generateMechanismTable($id) );
			
		} // if ($id)
	
		//validate
		$this->view->assign ( 'status', $status );
	}

	public function addAction() {
		$this->view->assign('mode', 'add');
		$this->view->assign ( 'pageTitle', t ( 'Add New' ).' '.t( 'Employee' ) );
		return $this->editAction ();
	}

	public function deleteAction() {
		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}

		require_once('models/table/Employee.php');
		$status = ValidationContainer::instance ();
		$id = $this->getSanParam ( 'id' );

		if ($id) {
			$employee = new Employee ( );
			$rows = $employee->find ( $id );
			$row = $rows->current ();
			if ($row) {
				$employee->delete ( 'id = ' . $row->id );
			}
			$status->setStatusMessage ( t ( 'That employee was deleted.' ) );
		} else {
			$status->setStatusMessage ( t ( 'That employee could not be found.' ) );
		}

		//validate
		$this->view->assign ( 'status', $status );

	}
	
	public function deleteFunderAction() {
		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}
	
		require_once('models/table/Partner.php');
		require_once('views/helpers/Location.php'); // funder stuff
	
		$db     = $this->dbfunc();
		$status = ValidationContainer::instance ();
		$params = $this->getAllParams();
			
		if ($params['id']) {
			$recArr = explode('_', $params['id']);
			
				//find in epsfm, should find to delete
				$sql = 'SELECT * FROM employee_to_partner_to_subpartner_to_funder_to_mechanism  WHERE '; // .$id.space.$orgWhere;
				$where = "employee_id = $recArr[0] and  partner_id = $recArr[1] and subpartner_id = $recArr[2] and partner_funder_option_id = $recArr[3] and mechanism_option_id = $recArr[4] and is_deleted = false";
				$sql .= $where;
					
				$row = $db->fetchRow( $sql );
				if (! $row){
					$status->setStatusMessage ( t('Cannot find that record in the database.') );
					//file_put_contents('c:\wamp\logs\php_debug.log', 'That record could not be found.'.PHP_EOL, FILE_APPEND | LOCK_EX);
				}
					
				else { // found, safe to delete
	
					//file_put_contents('c:\wamp\logs\php_debug.log', 'Ready to delete '.$row['id'].PHP_EOL, FILE_APPEND | LOCK_EX);
					$update_result = $db->update('employee_to_partner_to_subpartner_to_funder_to_mechanism', array('is_deleted' => 1), 'id = '.$row['id']);
					var_dump($update_result);
	
					if($update_result){
						$status->setStatusMessage ( t ( 'That mechanism was deleted.' ) );
						//file_put_contents('c:\wamp\logs\php_debug.log', 'That record was deleted.'.PHP_EOL, FILE_APPEND | LOCK_EX);
					}
					else{
						$status->setStatusMessage ( t ( 'That mechanism was not deleted.' ) );
						//file_put_contents('c:\wamp\logs\php_debug.log', 'That record was not deleted.'.PHP_EOL, FILE_APPEND | LOCK_EX);
					}
				}
				
			//$result = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $result .PHP_EOL, FILE_APPEND | LOCK_EX);
		}
		$this->_redirect("employee/edit/id/" . $row['employee_id']);
	}
	

	public function editAction() {
		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}

		$db = $this->dbfunc();
		$status = ValidationContainer::instance ();
		$params = $this->getAllParams();
		$id = $params['id'];
		$partner_id = $params['partner_id'];

		// restricted access?? only show partners by organizers that we have the ACL to view
		$org_allowed_ids = allowed_org_access_full_list($this);               // doesnt have acl 'training_organizer_option_all'
		if ($org_allowed_ids && $this->view->mode != 'add' && $id != "") {    // doesnt have acl & is edit mode.
			if ($partnerID = $db->fetchOne("SELECT partner_id FROM employee WHERE employee.id = ?", $id)) {
				$validID = $db->fetchCol("SELECT partner.id FROM partner WHERE partner.id = ? AND partner.organizer_option_id in ($org_allowed_ids)", $partnerID); // check for both
				if(empty($validID))
					$this->doNoAccessError ();
			}
		}

		if ( $this->getRequest()->isPost() )
		{
		    if (!$this->hasACL('edit_employee'))
		    {
		        $this->doNoAccessError();
		    }
            else 
            {		    
    			//validate then save
    			$params['location_id'] = regionFiltersGetLastID( '', $params );
    			$params['dob']                      = $this->_euro_date_to_sql( $params['dob'] );
    			$params['agreement_end_date']       = $this->_euro_date_to_sql( $params['agreement_end_date'] );
    			$params['transition_date']          = $this->_euro_date_to_sql( $params['transition_date'] );
    			$params['transition_complete_date'] = $this->_euro_date_to_sql( $params['transition_complete_date'] );
    			$params['site_id']                  = $params['facilityInput'];
    			$params['option_nationality_id']    = $params['lookup_nationalities_id'];
    			$params['facility_type_option_id']  = $params['employee_site_type_option_id'];
    			$params['race_option_id']           = $params['person_race_option_id'];
    
    			// $status->checkRequired ( $this, 'first_name', t ( 'Frist Name' ) );
    			// $status->checkRequired ( $this, 'last_name',  t ( 'Last Name' ) );
    			
    			$status->checkRequired ( $this, 'employee_code', t('Employee').space.t('Code'));
    			
    			//$status->checkRequired ( $this, 'dob', t ( 'Date of Birth' ) );//TA:18: 08/28/2014 (DOB field is not required)
    			
    			if($this->setting('display_employee_nationality'))
    				$status->checkRequired ( $this, 'lookup_nationalities_id', t('Employee Nationality'));
    			
    			
    			
    			$status->checkRequired ( $this, 'employee_qualification_option_id', t ( 'Staff Cadre' ) );
    			
    			if($this->setting('display_gender'))
    				$status->checkRequired ( $this, 'gender', t('Gender') );
    			if($this->setting('display_employee_race'))
    				$status->checkRequired ( $this, 'person_race_option_id', t('Race') );
    			if($this->setting('display_employee_disability')) {
    				$status->checkRequired ( $this, 'disability_option_id', t('Disability') );
    				if ($params['disability_option_id'] == 1)
    					$status->checkRequired ( $this, 'disability_comments', t('Nature of Disability') );
    			}
    			if($this->setting('display_employee_salary'))
    				$status->checkRequired ( $this, 'salary', t('Salary') );
    			if($this->setting('display_employee_benefits'))
    				$status->checkRequired ( $this, 'benefits', t('Benefits') );
    			if($this->setting('display_employee_additional_expenses'))
    				$status->checkRequired ( $this, 'additional_expenses', t('Additional Expenses') );
    			if($this->setting('display_employee_stipend'))
    				$status->checkRequired ( $this, 'stipend', t('Stipend') );
    			if ( $this->setting('display_employee_partner') )
    				$status->checkRequired ( $this, 'partner_id', t ( 'Partner' ) );
    			//if($this->setting('display_employee_sub_partner'))
    				//$status->checkRequired ( $this, 'subpartner_id', t ( 'Sub Partner' ) );
    			if($this->setting('display_employee_intended_transition'))
    				$status->checkRequired ( $this, 'employee_transition_option_id', t ( 'Intended Transition' ) );
    			if(($this->setting('display_employee_base') && !$params['employee_base_option_id']) || !$this->setting('display_employee_base')) // either one is OK, javascript disables regions if base is on & has a value choice
    				$status->checkRequired ( $this, 'province_id', t ( 'Region A (Province)' ).space.t('or').space.t('Employee Based at') );
    			if($this->setting('display_employee_base') && !$params['province_id'])
    				$status->checkRequired ( $this, 'employee_base_option_id', t('Employee Based at').space.t('or').space.t('Region A (Province)') );
    			if($this->setting('display_employee_primary_role'))
    				$status->checkRequired ( $this, 'employee_role_option_id', t ( 'Primary Role' ) );
    
    			$status->checkRequired ( $this, 'funded_hours_per_week', t ( 'Funded hours per week' ) );
    			if($this->setting['display_employee_contract_end_date'])
    				$status->checkRequired ( $this, 'agreement_end_date', t ( 'Contract End Date' ) );
    			
    
    			$params['subPartner'] = $this->_array_me($params['subPartner']);
    			$params['partnerFunder'] = $this->_array_me($params['partnerFunder']);
    			$params['mechanism'] = $this->_array_me($params['mechanism']);
    			
    			$total_percent = 0;
    			foreach($params['percentage'] as $i => $val){
    				$total_percent = $total_percent + $params['percentage'][$i];
    			}
    			if ($total_percent > 100) $status->setStatusMessage ( t(' Warn: Total Funded Percentage > 100 ') );
    		
    			// set partner specific unique employee number. (auto-increment ID for each employee, starting at 1, per-partner)
    			if($id) { // reset if change partner_id
    				$oldPartnerId = $db->fetchOne("SELECT partner_id FROM employee WHERE id = ?", $id);
    				if ($params['partner_id'] != $oldPartnerId || $params['partner_id'] == "")
    					$params['partner_employee_number'] = null;
    			}
    			if ($params['partner_id'] && $params['partner_employee_number'] == "") { // generate a new id
    				$max = $db->fetchOne("SELECT MAX(partner_employee_number) FROM employee WHERE partner_id = ?", $params['partner_id']);
    				$params['partner_employee_number'] = $max ? $max + 1 : 1; // max+1 or default to 1
    			}
    			
    			 
    			// save
    			if (! $status->hasError() ) {
    			    require_once('models/table/Employee.php');
    				$id = $this->_findOrCreateSaveGeneric('employee', $params);
    				if ($params['employeeFunding_delete_data'])
    				{
    				    if (!Employee::disassociateMechanismFromEmployee($params['employeeFunding_delete_data'])) {
    				        $status->setStatusMessage(t('Error saving mechanism association.'));
    				    }
    				}
    				
    				if ($params['employeeFunding_new_data'])
    				{
    				    $data_to_add = json_decode($params['employeeFunding_new_data'], true);
    				    if (!Employee::saveMechanismAssociation($id, $data_to_add['data']))
    				    {
    				        $status->setStatusMessage(t('Error saving mechanism association.'));
    				    }
    				}
    				
    				if(!$id) {
    					$status->setStatusMessage( t('That person could not be saved.') );
    				} else {
    					$status->setStatusMessage( t('The person was saved.') );
    					if (array_key_exists('save', $params))
    					{
    					   $this->_redirect("employee/add_funder_to_employee/id/$id");
    					}
    					else 
    					{
    					    $this->_redirect("employee/edit/id/$id");
    					}
    				}
    			}
    			 
    		}
		}
		
		
		if ( $id && !$status->hasError() )  // read data from db
		{
			$sql = 'SELECT * FROM employee WHERE employee.id = '.$id;
			$row = $db->fetchRow( $sql );
			if ( ! $row)
			{
				$status->setStatusMessage ( t('Error finding that record in the database.') );
			}
			else 
        	{
            	$params = $row; // reassign form data
            	
            	$region_ids = Location::getCityInfo($params['location_id'], $this->setting('num_location_tiers'));
            	$region_ids = Location::regionsToHash($region_ids);
            	$params = array_merge($params, $region_ids);
            	#$params['roles'] = $db->fetchCol("SELECT employee_role_option_id FROM employee_to_role WHERE employee_id = $id");
            	
            	//get linked table data from option tables
            	$sql = "SELECT partner_to_subpartner_to_funder_to_mechanism_id, employee_id, partner_id, subpartner_id, partner_funder_option_id, mechanism_option_id, percentage
            	FROM employee_to_partner_to_subpartner_to_funder_to_mechanism WHERE is_deleted = false and employee_id = $id";
            	$params['funder'] = $db->fetchAll($sql);
            	
            	
            	$helper = new Helper();
            	
            	$subPartner = $helper->getEmployeeSubPartner($id);
            	$this->viewAssignEscaped ( 'subPartner', $subPartner );
            	
            	$partnerFunder = $helper->getEmployeeFunder($id);
            	$this->viewAssignEscaped ( 'partnerFunder', $partnerFunder );
            	
            	$mechanism = $helper->getEmployeeMechanism($id);
            	$this->viewAssignEscaped ( 'mechanism', $mechanism );
            	
            	//file_put_contents('c:\wamp\logs\php_debug.log', 'empCont 511>'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
            	//var_dump($params['funder']);
            	//var_dump($mechanism);
            	//$result = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $result .PHP_EOL, FILE_APPEND | LOCK_EX);
            	
			}
		}

		
		// make sure form data is valid for display
		if (empty($params['funder']))
			$params['funder'] = array(array());
		
		if (empty($params['subpartner']))
			$params['subpartner'] = array(array());
		if (empty($params['funder']))
			$params['funder'] = array(array());
		if (empty($params['mechanism_option_id']))
			$params['mechanism_option_id'] = array(array());
		

		// assign form drop downs
		$params['dob']                          = formhelperdate($params['dob']);
		$params['agreement_end_date']           = formhelperdate($params['agreement_end_date']);
		$params['transition_date']              = formhelperdate($params['transition_date']);
		$params['transition_complete_date']     = formhelperdate($params['transition_complete_date']);
		$params['courses']                      = $this->getCourses($id);
		$params['lookup_nationalities_id']      = $params['option_nationality_id'];
		$params['employee_site_type_option_id'] = $params['facility_type_option_id'];
		$params['person_race_option_id']        = $params['race_option_id'];
		$this->viewAssignEscaped ( 'employee', $params );
		$validCHWids = $db->fetchCol("select id from employee_qualification_option qual
										inner join (select id as success from employee_qualification_option where qualification_phrase in ('Community Based Worker','Community Health Worker','NC02 -Community health workers')) parentIDs
										on (parentIDs.success = qual.id)");
		$this->view->assign('validCHWids', $validCHWids);
		$this->view->assign('expandCHWFields', !(array_search($params['employee_qualification_option_id'],$validCHWids) === false)); // i.e $validCHWids.contains($employee[qualification])
		$this->view->assign('status', $status);
		$this->view->assign ( 'pageTitle', $this->view->mode == 'add' ? t ('Add').space.t('Employee' ) : t( 'Edit').space.t('Employee' ) );
		$this->viewAssignEscaped ( 'locations', Location::getAll () );
		$titlesArray = OptionList::suggestionList ( 'person_title_option', 'title_phrase', false, 9999);
		$this->view->assign ( 'titles',      DropDown::render('title_option_id', $this->translation['Title'], $titlesArray, 'title_phrase', 'id', $params['title_option_id'] ) );
		
		$this->view->assign ( 'partners',    DropDown::generateHtml   ( 'partner', 'partner', $params['partner_id'], false, !$this->hasACL("edit_employee"), false ) );
		
		//$this->view->assign ( 'funder_mechanisms', DropDown::generateHtml( $params['funder_mechanism'], 'funder_mechanism_option', $params['funder_mechanism'], false, $this->view->viewonly, false ) );
		/*
		$this->view->assign ( 'funders',
		DropDown::generateHtml (
		'partner_funder_option',
		'funder_phrase',
		$params['partner_funder_option_id'],
		false,
		$this->viewonly,
		false
		));
		*/
		
		//$this->view->assign ( 'subpartners', DropDown::generateHtml   ( 'partner', 'partner', $params['subpartner_id'], false, $this->view->viewonly, false, false, array('name' => 'subpartner_id'), true ) );
		$this->view->assign ( 'bases',       DropDown::generateHtml   ( 'employee_base_option', 'base_phrase', $params['employee_base_option_id'], false, !$this->hasACL("edit_employee")) );
		$this->view->assign ( 'site_types',  DropDown::generateHtml   ( 'employee_site_type_option', 'site_type_phrase', $params['facility_type_option_id'], false, !$this->hasACL("edit_employee")) );
		$this->view->assign ( 'cadres',      DropDown::generateHtml   ( 'employee_qualification_option', 'qualification_phrase', $params['employee_qualification_option_id'], false, !$this->hasACL("edit_employee")) );
		$this->view->assign ( 'categories',  DropDown::generateHtml   ( 'employee_category_option', 'category_phrase', $params['employee_category_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'fulltime',    DropDown::generateHtml   ( 'employee_fulltime_option', 'fulltime_phrase', $params['employee_fulltime_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'roles',       DropDown::generateHtml   ( 'employee_role_option', 'role_phrase', $params['employee_role_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		#$this->view->assign ( 'roles',       CheckBoxes::generateHtml ( 'employee_role_option', 'role_phrase', $this->view, $params['roles'] ) );
		$this->view->assign ( 'transitions', DropDown::generateHtml   ( 'employee_transition_option', 'transition_phrase', $params['employee_transition_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'transitions_complete', DropDown::generateHtml ( 'employee_transition_option', 'transition_phrase', $params['employee_transition_complete_option_id'], false, !$this->hasACL("edit_employee"), false, false, array('name' => 'employee_transition_complete_option_id'), true ) );
		$helper = new Helper();
		$this->viewAssignEscaped ( 'facilities', $helper->getFacilities() );
		$this->view->assign ( 'relationships', DropDown::generateHtml ( 'employee_relationship_option', 'relationship_phrase', $params['employee_relationship_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'referrals',     DropDown::generateHtml ( 'employee_referral_option', 'referral_phrase', $params['employee_referral_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'provided',      DropDown::generateHtml ( 'employee_training_provided_option', 'training_provided_phrase', $params['employee_training_provided_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		$employees = OptionList::suggestionList ( 'employee', array ('first_name' ,'CONCAT(first_name, CONCAT(" ", last_name)) as name' ), false, 99999 );
		$this->view->assign ( 'supervisors',   DropDown::render('supervisor_id', $this->translation['Supervisor'], $employees, 'name', 'id', $params['supervisor_id'] ) );
		$this->view->assign ( 'nationality',   DropDown::generateHtml ( 'lookup_nationalities', 'nationality', $params['lookup_nationalities_id'], false, !$this->hasACL("edit_employee"), false ) );
		$this->view->assign ( 'race',          DropDown::generateHtml ( 'person_race_option', 'race_phrase', $params['race_option_id'], false, !$this->hasACL("edit_employee"), false ) );
		
		$this->view->assign ( 'tableEmployeeFunding', $this->generateMechanismTable($id) );
		
	}

	public function searchAction()
	{
		$this->view->assign('pageTitle', 'Search Employees');
		if (! $this->hasACL ( 'employees_module' )) {
			$this->doNoAccessError ();
		}

		$criteria = $this->getAllParams();

		if ($criteria['go'])
		{
			// process search
			$where = array();

			list($a, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			list($locationFlds, $locationsubquery) = Location::subquery($this->setting('num_location_tiers'), $location_tier, $location_id, true);

			$sql = "SELECT DISTINCT
    employee.id,
    employee.employee_code,
    employee.gender,
    employee.national_id,
    employee.other_id,
    employee.location_id,
    ".implode(',',$locationFlds).",
    CONCAT(supervisor.first_name,
    CONCAT(' ', supervisor.last_name)) as supervisor,
    qual.qualification_phrase as staff_cadre,
    site.facility_name,
    category.category_phrase as staff_category,
GROUP_CONCAT(subp.partner) as subPartner,
GROUP_CONCAT( partner_funder_option.funder_phrase) as partnerFunder,
GROUP_CONCAT(mechanism_option.mechanism_phrase) as mechanism,
    GROUP_CONCAT(funders.percentage) as percentage
FROM    employee        
LEFT JOIN    ($locationsubquery) as l ON l.id = employee.location_id
LEFT JOIN   employee supervisor ON supervisor.id = employee.supervisor_id
LEFT JOIN   facility site ON site.id = employee.site_id
LEFT JOIN   employee_qualification_option qual ON qual.id = employee.employee_qualification_option_id
LEFT JOIN   employee_category_option category ON category.id = employee.employee_category_option_id
LEFT JOIN   partner ON partner.id = employee.partner_id
LEFT JOIN	employee_to_partner_to_subpartner_to_funder_to_mechanism funders on (funders.employee_id = employee.id and funders.partner_id = partner.id )
LEFT JOIN 	partner_funder_option on funders.partner_funder_option_id = partner_funder_option.id
LEFT JOIN 	mechanism_option on funders.mechanism_option_id = mechanism_option.id 
LEFT JOIN 	partner subp on subp.id = funders.subpartner_id 
					";

			#if ($criteria['partner_id']) $sql    .= ' INNER JOIN partner_to_subpartner subp ON partner.id = ' . $criteria['partner_id'];

			// restricted access?? only show partners by organizers that we have the ACL to view
			$org_allowed_ids = allowed_org_access_full_list($this); // doesnt have acl 'training_organizer_option_all'
			if($org_allowed_ids)
				$where[] = " partner.organizer_option_id in ($org_allowed_ids) ";

			if ($locationWhere = $this->getLocationCriteriaWhereClause($criteria, '', '')) {
				$where[] = $locationWhere;
			}

			// if ($criteria['first_name'])                        $where[] = "employee.first_name   = '{$criteria['first_name']}'";
			// if ($criteria['last_name'])                         $where[] = "employee.last_name    = '{$criteria['last_name']}'";
			if ($criteria['employee_code'])                        $where[] = "employee.employee_code    like '%{$criteria['employee_code']}%'";
			
			if ($criteria['partner_id'])                        $where[] = 'employee.partner_id   = '.$criteria['partner_id']; //todo
			
			if ($criteria['facilityInput'])                     $where[] = 'employee.site_id      = '.$criteria['facilityInput'];
			if ($criteria['employee_qualification_option_id'])  $where[] = 'employee.employee_qualification_option_id    = '.$criteria['employee_qualification_option_id'];
			if ($criteria['category_option_id'])                $where[] = 'employee.staff_category_id = '.$criteria['category_option_id'];

			if ( count ($where) )
				$sql .= ' WHERE ' . implode(' AND ', $where);
			
			$sql .= ' GROUP BY employee.id ';
			
			$db = $this->dbfunc();
			$rows = $db->fetchAll( $sql );

			$locations = Location::getAll();
			// hack #TODO - seems Region A -> ASDF, Region B-> *Multiple Province*, Region C->null Will not produce valid locations with Location::subquery
			// the proper solution is to add "Default" districts under these subdistricts, not sure if i can at this point the table is 12000 rows, todo later
			foreach ($rows as $i => $row) {
				if ($row['province_id'] == "" && $row['location_id']){ // empty province
					$updatedRegions = Location::getCityandParentNames($row['location_id'], $locations, $this->setting('num_location_tiers'));
					$rows[$i] = array_merge($row, $updatedRegions);
				}
			}

			$this->viewAssignEscaped('results', $rows);
			$this->viewAssignEscaped('count', count($rows));

			if ($criteria ['outputType'] && $rows) {
				$this->sendData ( $this->reportHeaders ( false, $rows ) );
			}
		}
		// assign form drop downs
		$helper = new Helper();
		$this->view->assign('status', $status);
		$this->viewAssignEscaped ( 'criteria', $criteria );
		$this->viewAssignEscaped ( 'locations', $locations );
		$this->view->assign ( 'partners',    DropDown::generateHtml ( 'partner', 'partner', $criteria['partner_id'], false, $this->view->viewonly, false ) );
		
		//$this->view->assign ( 'subpartners', DropDown::generateHtml ( 'partner', 'partner', $criteria['partner_id'], false, $this->view->viewonly, false, false, array('name' => 'subpartner_id'), true ) );
		$this->view->assign ( 'cadres',      DropDown::generateHtml ( 'employee_qualification_option', 'qualification_phrase', $criteria['employee_qualification_option_id'], false, $this->view->viewonly, false ) );
		$this->viewAssignEscaped ( 'sites', $helper->getFacilities() );
		$this->view->assign ( 'categories',  DropDown::generateHtml ( 'employee_category_option', 'category_phrase', $criteria['employee_category_option_id'], false, $this->view->viewonly, false ) );
	}
}

?>