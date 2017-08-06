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
//require_once('models/table/Course.php');
require_once ('views/helpers/CheckBoxes.php');
require_once ('models/table/MultiAssignList.php');
require_once ('models/table/TrainingTitleOption.php');
require_once ('models/table/Helper.php');
require_once ('models/table/Helper2.php');
require_once ('models/table/User.php');
require_once ('models/table/Report.php');
require_once ('models/table/Person.php');
require_once ('models/table/Training.php');

class ReportsController extends ReportFilterHelpers {

	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		parent::__construct ( $request, $response, $invokeArgs );
	}

	public function init() {
           // parent::init();
        $burl = Settings::$COUNTRY_BASE_URL;
        if (substr($burl, -1) != '/' && substr($burl, -1) != '\\')
            $this->baseUrl = $burl . '/';
	}

	public function indexAction() {

$this->_forward ( 'allqueries' );

	}
        public function archivedreportsAction(){
            $this->_redirect('pdf/archivedreports');
        }

	public function preDispatch() {
		$rtn = parent::preDispatch ();
		$allowActions = array ('trainingSearch' );

		if (! $this->isLoggedIn ())
		$this->doNoAccessError ();

		//if (! $this->hasACL ( 'view_create_reports' ) && ! in_array ( $this->getRequest ()->getActionName (), $allowActions )) {
			//$this->doNoAccessError ();
		//}

		return $rtn;
	}

	public function dataAction() { 	}

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

		if ($this->setting('display_mod_skillsmart')){
			$headers = array (// fieldname => label
			'id' => 'ID',
			'pcnt' => 'Participants',
			'has_known_participants' => 'Known participants',
			'region_c_name' => 'Sub-district',
			'training_method_phrase' => 'Training method',
			'is_refresher' => 'Refresher',
			'secondary_language_phrase' => 'Secondary language',
			'primary_language_phrase' => 'Primary language',
			'got_comments' => 'National curriculum comment',
			'training_got_curriculum_phrase' => 'National curriculum',
			'training_category_phrase' => 'Training category',
			'age' => 'Age',
			'comments1' => 'Professional registration number',
			'comments2' => 'Race',
			'comments3' => 'Experience',
			'score_pre' => 'Pre-test',
			'score_post' => 'Post-test',
			'score_percent_change' => '% change',
			'custom1_phrase' => 'Professional registration number',
			'city_name' => 'City',
			'cnt' => t ( 'Count' ), 'active' => @$translation ['Is Active'], 'first_name' => @$translation ['First Name'], 'middle_name' => @$translation ['Middle Name'], 
					'last_name' => @$translation ['Surname'], 'training_title' => t('Training').' '.t('Name'), 'province_name' => @$translation ['Region A (Province)'], 'region_c_name' => @$translation ['Region C (Local Region)'],
					'district_name' => @$translation ['Region B (Health District)'], 'pepfar_category_phrase' => @$translation ['PEPFAR Category'], 'training_organizer_phrase' => t('Training').' '.t('Organizer'), 'training_level_phrase' => t('Training').' '.t('Level'), 'trainer_language_phrase' => t ( 'Language' ), 'training_location_name' => t ( 'Location' ), 'training_start_date' => t ( 'Date' ), 'training_topic_phrase' => t ('Training').' '.t('topic'), 'funding_phrase' => t ( 'Funding' ), 'is_tot' => t ( 'TOT' ), 'facility_name' => t ('Facility').' '.t('name'), 'facility_type_phrase' => t ('Facility').' '.t('type'), 'facility_sponsor_phrase' => t ('Facility').' '.t('sponsor'), 'course_recommended' => t ( 'Recommended classes' ), 'recommended' => t ( 'Recommended' ), 'qualification_phrase' => t ( 'Qualification' ) . ' ' . t ( '(primary)' ), 'qualification_secondary_phrase' => t ( 'Qualification' ) . ' ' . t ( '(secondary)' ), 'gender' => t ( 'Gender' ), 'name' => t ( 'Name' ), 'email' => t ( 'Email' ), 'phone' => t ( 'Phone' ), 'cat' => t ( 'Category' ), 'language_phrase' => t ( 'Language' ), 'trainer_type_phrase' => t ( 'Type' ), 'trainer_skill_phrase' => t ( 'Skill' ), 'trainer_language_phrase' => t ( 'Language' ), 'trainer_topic_phrase' => t ( 'Topics taught' ), 'phone_work' => t ( 'Work phone' ), 'phone_home' => t ( 'Home phone' ), 'phone_mobile' => t ( 'Mobile phone' ), 'type_option_id' => 'Type' );

			// action => array(field => label)
			$headersSpecific = array ('peopleByFacility' => array ('qualification_phrase' => t ( 'Qualification' ) ), 'participantsByCategory' => array ('cnt' => t ( 'Participants' ), 'person_cnt' => t ( 'Unique participants' ) ) );
		} else {
			$headers = array (// fieldname => label
			'id' => 'ID', 'commodity_name'=>'Method', 'training_title_phrase' => @$translation ['Training Name'], 'facility_id' => 'ID','cnt' => t ( 'Count' ), 'active' => @$translation ['Is Active'], 'first_name' => @$translation ['First Name'], 'middle_name' => @$translation ['Middle Name'], 
					'last_name' => @$translation ['Surname'], 'training_title' => t('Training').' '.t('Name'), 'province_name' => @$translation ['Region A (Province)'], 'region_c_name' => @$translation ['Region C (Local Region)'],
					'district_name' => @$translation ['Region B (Health District)'], 'pepfar_category_phrase' => @$translation ['PEPFAR Category'], 'training_organizer_phrase' => t('Training').' '.t('Organizer'), 'training_level_phrase' => t('Training').' '.t('Level'), 'trainer_language_phrase' => t ( 'Language' ), 'training_location_name' => t ( 'Location' ), 'training_start_date' => t ( 'Date' ), 'training_topic_phrase' => t ('Training').' '.t('Topic'), 'funding_phrase' => t ( 'Funding' ), 'is_tot' => t ( 'TOT' ), 'facility_name' => t ('Facility').' '.t('Name'), 'facility_type_phrase' => t ('Facility').' '.t('Type'), 'facility_sponsor_phrase' => t ('Facility').' '.t('Sponsor'), 'course_recommended' => t ( 'Recommended classes' ), 'recommended' => t ( 'Recommended' ), 'qualification_phrase' => t ( 'Qualification' ) . ' ' . t ( '(primary)' ), 'qualification_secondary_phrase' => t ( 'Qualification' ) . ' ' . t ( '(secondary)' ), 'gender' => t ( 'Gender' ), 'name' => t ( 'Name' ), 'email' => t ( 'Email' ), 'phone' => t ( 'Phone' ), 'cat' => t ( 'Category' ), 'language_phrase' => t ( 'Language' ), 'trainer_type_phrase' => t ( 'Type' ), 'trainer_skill_phrase' => t ( 'Skill' ), 'trainer_language_phrase' => t ( 'Language' ), 'trainer_topic_phrase' => t ( 'Topics Taught' ), 'phone_work' => t ( 'Work Phone' ), 'phone_home' => t ( 'Home Phone' ), 'phone_mobile' => t ( 'Mobile Phone' ), 'type_option_id' => 'Type' );

			// action => array(field => label)
			$headersSpecific = array ('peopleByFacility' => array ('qualification_phrase' => t ( 'Qualification' ) ), 'participantsByCategory' => array ('cnt' => t ( 'Participants' ), 'person_cnt' => t ( 'Unique Participants' ) ) );
		}

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

	public function compcsvAction() {
		$v1=split("~",$this->getSanParam ( 'v1' ));
		$v2=split("~",$this->getSanParam ( 'v2' ));
		$p=$this->getSanParam ( 'p' );
		$d=$this->getSanParam ( 'd' );
		$s=$this->getSanParam ( 's' );
		$f=$this->getSanParam ( 'f' );
		$this->viewAssignEscaped ( 'v1', $v1 );
		$this->viewAssignEscaped ( 'v2', $v2 );
		$this->viewAssignEscaped ( 'p',  $p);
		$this->viewAssignEscaped ( 'd',  $d);
		$this->viewAssignEscaped ( 's',  $s);
		$this->viewAssignEscaped ( 'f',  $f);
	}

	public function profcsvAction() {
		$v1=split("~",$this->getSanParam ( 'v1' ));
		$v2=split("~",$this->getSanParam ( 'v2' ));
		$v3=split("~",$this->getSanParam ( 'v3' ));
		$v4=split("~",$this->getSanParam ( 'v4' ));
		$v5=split("~",$this->getSanParam ( 'v5' ));
		$v6=split("~",$this->getSanParam ( 'v6' ));
		$p=$this->getSanParam ( 'p' );
		$d=$this->getSanParam ( 'd' );
		$s=$this->getSanParam ( 's' );
		$f=$this->getSanParam ( 'f' );
		$this->viewAssignEscaped ( 'v1', $v1 );
		$this->viewAssignEscaped ( 'v2', $v2 );
		$this->viewAssignEscaped ( 'v3', $v3 );
		$this->viewAssignEscaped ( 'v4', $v4 );
		$this->viewAssignEscaped ( 'v5', $v5 );
		$this->viewAssignEscaped ( 'v6', $v6 );
		$this->viewAssignEscaped ( 'p',  $p);
		$this->viewAssignEscaped ( 'd',  $d);
		$this->viewAssignEscaped ( 's',  $s);
		$this->viewAssignEscaped ( 'f',  $f);
	}

	public function detailAction() {
		$helper = new Helper();
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['score_id'] = $this->getSanParam ( 'score_id' );
		$criteria ['primarypatients'] = $this->getSanParam ( 'primarypatients' );
		$criteria ['hivInput'] = $this->getSanParam ( 'hivInput' );
		$criteria ['trainer_type_option_id1'] = $this->getSanParam ( 'trainer_type_option_id1' );
		$criteria ['grp1'] = $this->getSanParam ( 'grp1' );
		$criteria ['grp2'] = $this->getSanParam ( 'grp2' );
		$criteria ['grp3'] = $this->getSanParam ( 'grp3' );
		$criteria ['go'] = $this->getSanParam ( 'go' );

		$complist = $helper->getQualificationCompetencies();

		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
#			$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
			$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp';
			if ( $criteria['training_title_option_id'] ) {
				$sql .= ', person_to_training as ptt ';
				$sql .= ', training as tr  ';
			}
			$where = array('p.is_deleted = 0');
			$whr = array();
#			$where []= 'cmpr.person = p.id';
			$where []= 'cmp.person = p.id';
#			$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}
			if ( $criteria['training_title_option_id'] ) {
				$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
			}
			if( isset($criteria ['qualification_id']) && $criteria ['qualification_id'] != ''){
	 			$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
#			$where []= 'cmpr.active = \'Y\'';
#			$where []= 'cmpr.res = 1';
			$where []= 'cmp.active = \'Y\'';

			// OLD METHOD - FOR HARDCODED COMPETENCIES
			/*
				if($criteria ['qualification_id']=="6")
				{
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
				}
				if($criteria ['qualification_id']=="7")
				{
					$qs=split(",",$this->getSanParam ( 'ques' ));
					$nms=split("~",$this->getSanParam ( 'listdq' ));
					foreach ( $qs as $kys => $vls ) {
						$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
					}
				}
				if($criteria ['qualification_id']=="8")
				{
					$qs=split(",",$this->getSanParam ( 'ques' ));
					$nms=split("~",$this->getSanParam ( 'listnq' ));
					foreach ( $qs as $kys => $vls ) {
						$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
					}
				}
				if($criteria ['qualification_id']=="9")
				{
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
				}
			*/

			// GETTING QUESTIONS TIED TO THE SELECTED COMPETENCIES
			$questionids = $helper->getCompQuestions($this->getSanParam ( 'complist' ));

			$whr []= 'cmp.question IN ('."'".str_replace(",","','", implode(",", $questionids)) ."'".')';

			if( !empty($where) ){ $sql .= ' WHERE ' . implode(' AND ', $where); }
			if( !empty($whr) ){ $sql .= ' AND (' . implode(' OR ', $whr) . ')'; }


			$return = array();
			// For each competency, we loop through this block
			foreach ($this->getSanParam('complist') as $cid){
				// Getting competency details
				$thiscomp = $helper->getSkillSmartCompetencies($cid);

				// Getting ids for questions that are in this competency
				$curids = $helper->getCompQuestions(array($cid));

				$count = 0;
				$total = 0;
				foreach ( $rowArray as $k => $v ) {
					// Check if the question belongs to this competency
					if (in_array($v['question'], $curids)){
						switch (strtoupper($v['option'])){
							case "A":
								$total += 4;
								$count++;
							break;
							case "B":
								$total += 3;
								$count++;
							break;
							case "C":
								$total += 2;
								$count++;
							break;
							case "D":
								$total += 1;
								$count++;
							break;
						}
					}
				}
				if ($count > 0){
					$total = number_format((($total/(4*$ount))*100),2);
				}
				$return[$thiscomp['label']] = $total;
			}
			$this->viewAssignEscaped("reportoutput",$return);

#echo $sql;
#echo "<br><br>" . $sql . "<br><br>";

die ("OK");
			$rowArray = $db->fetchAll ( $sql );
			$qss=array();
			$nmss=array();
/*
			if($criteria ['qualification_id']=="6")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listcq' ));
			}
			if($criteria ['qualification_id']=="7")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listdq' ));
			}
			if($criteria ['qualification_id']=="8")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listnq' ));
			}
			if($criteria ['qualification_id']=="9")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listpq' ));
			}
			$ct;
			$ct=0;
			$rss=array();
			$ctt;
			foreach ( $qss as $kys => $vls ) {
				$rss[$ct]=0;
				$ctt=0;
				$wss=split(",",$nmss[$vls]);
				foreach ( $wss as $kyss => $vlss ) {
					foreach ( $rowArray as $kss => $vss ) {
						if($vlss." " == $vss['question']." ")
						{
							if($vss['option']=="A")
							{
								$rss[$ct]=$rss[$ct]+4;
							}
							else
							{
								if($vss['option']=="B")
								{
									$rss[$ct]=$rss[$ct]+3;
								}
								else
								{
									if($vss['option']=="C")
									{
										$rss[$ct]=$rss[$ct]+2;
									}
									else
									{
										if($vss['option']=="D")
										{
											$rss[$ct]=$rss[$ct]+1;
										}
									}
								}
							}
							$ctt=$ctt+1;
						}
					}
				}
				if($ctt>0)
				$rss[$ct]=number_format((($rss[$ct]/(4*$ctt))*100),2);
				$ct=$ct+1;
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->viewAssignEscaped ( 'rss', $rss );
*/
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

#		require_once ('models/table/TrainingTitleOption.php');
#		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
#		$this->viewAssignEscaped ( 'courses', $titleArray );
#		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
#		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$this->viewAssignEscaped ( 'complist', $complist );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function compAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
			$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
			$where = array('p.is_deleted = 0');
			$whr = array();
			$where []= 'cmpr.person = p.id';
			$where []= 'cmp.person = p.id';
			$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}
			$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
			$where []= 'cmpr.active = \'Y\'';
			$where []= 'cmpr.res = 1';
			$where []= 'cmp.active = \'Y\'';
			if($criteria ['qualification_id']=="6")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
			}
			if($criteria ['qualification_id']=="7")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listdq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="8")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listnq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="9")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
			}
			$sql .= ' WHERE ' . implode(' AND ', $where);
			$sql .= ' AND (' . implode(' OR ', $whr) . ')';
			$rowArray = $db->fetchAll ( $sql );
			$qss=array();
			$nmss=array();
			if($criteria ['qualification_id']=="6")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listcq' ));
			}
			if($criteria ['qualification_id']=="7")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listdq' ));
			}
			if($criteria ['qualification_id']=="8")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listnq' ));
			}
			if($criteria ['qualification_id']=="9")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listpq' ));
			}
			$ct;
			$ct=0;
			$rss=array();
			$ctt;
			foreach ( $qss as $kys => $vls ) {
				$rss[$ct]=0;
				$ctt=0;
				$wss=split(",",$nmss[$vls]);
				foreach ( $wss as $kyss => $vlss ) {
					foreach ( $rowArray as $kss => $vss ) {
						if($vlss." " == $vss['question']." ")
						{
							if($vss['option']=="A")
							{
								$rss[$ct]=$rss[$ct]+4;
							}
							else
							{
								if($vss['option']=="B")
								{
									$rss[$ct]=$rss[$ct]+3;
								}
								else
								{
									if($vss['option']=="C")
									{
										$rss[$ct]=$rss[$ct]+2;
									}
									else
									{
										if($vss['option']=="D")
										{
											$rss[$ct]=$rss[$ct]+1;
										}
									}
								}
							}
							$ctt=$ctt+1;
						}
					}
				}
				if($ctt>0)
				$rss[$ct]=number_format((($rss[$ct]/(4*$ctt))*100),2);
				$ct=$ct+1;
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->viewAssignEscaped ( 'rss', $rss );
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}


	public function profAction() {
		$helper = new Helper();
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		$criteria ['all'] = $this->getSanParam ( 'all' );

		$complist = $helper->getQualificationCompetencies();

		if ($criteria ['go']) {
			if ($criteria ['all']) {
				$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
				$num_locs = $this->setting('num_location_tiers');
				list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
				$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
				if ( $criteria['training_title_option_id'] ) {
					$sql .= ', person_to_training as ptt ';
					$sql .= ', training as tr  ';
				}
				$where = array('p.is_deleted = 0');
				$where []= 'cmpr.person = p.id';
				$where []= 'cmp.person = p.id';
				$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
				if ($criteria ['facilityInput']) {
					$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
				}
				if ( $criteria['training_title_option_id'] ) {
					$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
				}
				$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id IN (6, 7, 8, 9) ) ';
				$where []= 'cmpr.active = \'Y\'';
				$where []= 'cmpr.res = 1';
				$where []= 'cmp.active = \'Y\'';
				$sql .= ' WHERE ' . implode(' AND ', $where);

echo $sql . "<br>";

				$rowArray = $db->fetchAll ( $sql );
				$qss=array();
				$nmss=array();
				$qss=split(",","0,1,2,3,4,5,6,7");
				$nmss=split("~","1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,200~01,02,03,04,05,06,07,08,09~31,32,33,34,35,36,37,38~41,42,43,44,45~51,52,53,54,55,56,57,58,59,510,511,512,513,514,515,516,517,518~61,62,63,64,65,66,67~71,72,73,74,75,76,77,78,79,710,711~21,22,23");
				$ct;
				$ct=0;
				$rssA=array();
				$rssB=array();
				$rssC=array();
				$rssD=array();
				$rssE=array();
				$ctt;
				foreach ( $qss as $kys => $vls ) {
					$rssA[$ct]=0;
					$rssB[$ct]=0;
					$rssC[$ct]=0;
					$rssD[$ct]=0;
					$rssE[$ct]=0;
					$ctt=0;
					$wss=split(",",$nmss[$vls]);
					foreach ( $wss as $kyss => $vlss ) {
						foreach ( $rowArray as $kss => $vss ) {
							if($vlss." " == $vss['question']." ")
							{
								if($vss['option']=="A")
								{
									$rssA[$ct]=$rssA[$ct]+1;
								}
								else
								{
									if($vss['option']=="B")
									{
										$rssB[$ct]=$rssB[$ct]+1;
									}
									else
									{
										if($vss['option']=="C")
										{
											$rssC[$ct]=$rssC[$ct]+1;
										}
										else
										{
											if($vss['option']=="D")
											{
												$rssD[$ct]=$rssD[$ct]+1;
											}
											else
											{
												if($vss['option']=="E")
												{
													$rssE[$ct]=$rssE[$ct]+1;
												}
											}
										}
									}
								}
								$ctt=$ctt+1;
							}
						}
					}
					if($ctt>0) {
						$rssA[$ct]=number_format((($rssA[$ct]/$ctt)*100),2);
						$rssB[$ct]=number_format((($rssB[$ct]/$ctt)*100),2);
						$rssC[$ct]=number_format((($rssC[$ct]/$ctt)*100),2);
						$rssD[$ct]=number_format((($rssD[$ct]/$ctt)*100),2);
						$rssE[$ct]=number_format((($rssE[$ct]/$ctt)*100),2);
					}
					$ct=$ct+1;
				}
				$this->viewAssignEscaped ( 'results', $rowArray );
				$this->viewAssignEscaped ( 'rssA', $rssA );
				$this->viewAssignEscaped ( 'rssB', $rssB );
				$this->viewAssignEscaped ( 'rssC', $rssC );
				$this->viewAssignEscaped ( 'rssD', $rssD );
				$this->viewAssignEscaped ( 'rssE', $rssE );
			}
			else
			{
				$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
				$num_locs = $this->setting('num_location_tiers');
				list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
				$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
				if ( $criteria['training_title_option_id'] ) {
					$sql .= ', person_to_training as ptt ';
					$sql .= ', training as tr  ';
				}
				$where = array('p.is_deleted = 0');
				$whr = array();
				$where []= 'cmpr.person = p.id';
				$where []= 'cmp.person = p.id';
				$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
				if ($criteria ['facilityInput']) {
					$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
				}
				if ( $criteria['training_title_option_id'] ) {
					$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
				}
				$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
				$where []= 'cmpr.active = \'Y\'';
				$where []= 'cmpr.res = 1';
				$where []= 'cmp.active = \'Y\'';

/*
				// REMOVING OLD HARDCODED VALUES
				if($criteria ['qualification_id']=="6")
				{
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
				}
				if($criteria ['qualification_id']=="7")
				{
					$qs=split(",",$this->getSanParam ( 'ques' ));
					$nms=split("~",$this->getSanParam ( 'listdq' ));
					foreach ( $qs as $kys => $vls ) {
						$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
					}
				}
				if($criteria ['qualification_id']=="8")
				{
					$qs=split(",",$this->getSanParam ( 'ques' ));
					$nms=split("~",$this->getSanParam ( 'listnq' ));
					foreach ( $qs as $kys => $vls ) {
						$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
					}
				}
				if($criteria ['qualification_id']=="9")
				{
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
				}
*/

				// GETTING QUESTIONS TIED TO THE SELECTED COMPETENCIES
				$questionids = $helper->getCompQuestions($this->getSanParam ( 'complist' ));

				$whr []= 'cmp.question IN ('."'".str_replace(",","','", implode(",", $questionids)) ."'".')';


				if( !empty($where) ){ $sql .= ' WHERE ' . implode(' AND ', $where); }
				if( !empty($whr) ){ $sql .= ' AND (' . implode(' OR ', $whr) . ')'; }

				$rowArray = $db->fetchAll ( $sql );

				$return = array();
				// For each competency, we loop through this block
				foreach ($this->getSanParam('complist') as $cid){
					// Getting competency details
					$thiscomp = $helper->getSkillSmartCompetencies($cid);

					// Getting ids for questions that are in this competency
					$curids = $helper->getCompQuestions(array($cid));

					$count = 0;
					$totala = 0;
					$totalb = 0;
					$totalc = 0;
					$totald = 0;
					$totale = 0;
					foreach ( $rowArray as $k => $v ) {
						// Check if the question belongs to this competency
						if (in_array($v['question'], $curids)){
							switch (strtoupper($v['option'])){
								case "A":
									$totala++;
									$count++;
								break;
								case "B":
									$totalb++;
									$count++;
								break;
								case "C":
									$totalc++;
									$count++;
								break;
								case "D":
									$totald++;
									$count++;
								break;
								case "D":
									$totale++;
									$count++;
								break;
							}
						}
					}
					if ($count > 0){
						number_format((($rssA[$ct]/$ctt)*100),2);
						$return[$thiscomp['label']] = array(
							"A" => number_format((($totala / $count) * 100), 2),
							"B" => number_format((($totalb / $count) * 100), 2),
							"C" => number_format((($totalc / $count) * 100), 2),
							"D" => number_format((($totald / $count) * 100), 2),
							"E" => number_format((($totale / $count) * 100), 2),
						);
					} else {
						$return[$thiscomp['label']] = array(
							"A" => 0,
							"B" => 0,
							"C" => 0,
							"D" => 0,
							"E" => 0,
						);
					}
				}
				$this->viewAssignEscaped("reportoutput",$return);



				$qss=array();
				$nmss=array();
				if($criteria ['qualification_id']=="6")
				{
					$qss=split(",",$this->getSanParam ( 'ques' ));
					$nmss=split("~",$this->getSanParam ( 'listcq' ));
				}
				if($criteria ['qualification_id']=="7")
				{
					$qss=split(",",$this->getSanParam ( 'ques' ));
					$nmss=split("~",$this->getSanParam ( 'listdq' ));
				}
				if($criteria ['qualification_id']=="8")
				{
					$qss=split(",",$this->getSanParam ( 'ques' ));
					$nmss=split("~",$this->getSanParam ( 'listnq' ));
				}
				if($criteria ['qualification_id']=="9")
				{
					$qss=split(",",$this->getSanParam ( 'ques' ));
					$nmss=split("~",$this->getSanParam ( 'listpq' ));
				}
				$ct;
				$ct=0;
				$rssA=array();
				$rssB=array();
				$rssC=array();
				$rssD=array();
				$rssE=array();
				$ctt;
				foreach ( $qss as $kys => $vls ) {
					$rssA[$ct]=0;
					$rssB[$ct]=0;
					$rssC[$ct]=0;
					$rssD[$ct]=0;
					$rssE[$ct]=0;
					$ctt=0;
					$wss=split(",",$nmss[$vls]);
					foreach ( $wss as $kyss => $vlss ) {
						foreach ( $rowArray as $kss => $vss ) {
							if($vlss." " == $vss['question']." ")
							{
								if($vss['option']=="A")
								{
									$rssA[$ct]=$rssA[$ct]+1;
								}
								else
								{
									if($vss['option']=="B")
									{
										$rssB[$ct]=$rssB[$ct]+1;
									}
									else
									{
										if($vss['option']=="C")
										{
											$rssC[$ct]=$rssC[$ct]+1;
										}
										else
										{
											if($vss['option']=="D")
											{
												$rssD[$ct]=$rssD[$ct]+1;
											}
											else
											{
												if($vss['option']=="E")
												{
													$rssE[$ct]=$rssE[$ct]+1;
												}
											}
										}
									}
								}
								$ctt=$ctt+1;
							}
						}
					}
					if($ctt>0) {
						$rssA[$ct]=number_format((($rssA[$ct]/$ctt)*100),2);
						$rssB[$ct]=number_format((($rssB[$ct]/$ctt)*100),2);
						$rssC[$ct]=number_format((($rssC[$ct]/$ctt)*100),2);
						$rssD[$ct]=number_format((($rssD[$ct]/$ctt)*100),2);
						$rssE[$ct]=number_format((($rssE[$ct]/$ctt)*100),2);
					}
					$ct=$ct+1;
				}
				$this->viewAssignEscaped ( 'results', $rowArray );
				$this->viewAssignEscaped ( 'rssA', $rssA );
				$this->viewAssignEscaped ( 'rssB', $rssB );
				$this->viewAssignEscaped ( 'rssC', $rssC );
				$this->viewAssignEscaped ( 'rssD', $rssD );
				$this->viewAssignEscaped ( 'rssE', $rssE );
			}
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		require_once ('models/table/TrainingTitleOption.php');
		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $titleArray );
		$this->viewAssignEscaped ( 'complist', $complist );
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function compcompAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['Questions'] = $this->getSanParam ( 'Questions' );
		$criteria ['outputType'] = $this->getSanParam ( 'outputType' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$prsns=array();
			$prsnscnt=0;
			if($criteria ['qualification_id']=="6")
			{
				$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
				$whr = array();
				$whr []= '`question` IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
				$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
				$sql .= ' GROUP BY `person`';
				$rowArray = $db->fetchAll ( $sql );
				$tlques=split(",",$this->getSanParam ( 'listcq' ));
				$ttlques=count($tlques);
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				foreach ( $qs as $kys => $vls ) {
					$fr=split('\^',$vls);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="7")
			{
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				$nms=split("~",$this->getSanParam ( 'listdq' ));
				foreach ( $qs as $kys => $vls ) {
					$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
					$whr = array();
					$fr=split('\^',$vls);
					$whr []= '`question` IN ('."'".str_replace(",","','",$nms[$fr[1]])."'".')';
					$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
					$sql .= ' GROUP BY `person`';
					$rowArray = $db->fetchAll ( $sql );
					$tlques=split(",",$nms[$fr[1]]);
					$ttlques=count($tlques);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="8")
			{
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				$nms=split("~",$this->getSanParam ( 'listnq' ));
				foreach ( $qs as $kys => $vls ) {
					$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
					$whr = array();
					$fr=split('\^',$vls);
					$whr []= '`question` IN ('."'".str_replace(",","','",$nms[$fr[1]])."'".')';
					$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
					$sql .= ' GROUP BY `person`';
					$rowArray = $db->fetchAll ( $sql );
					$tlques=split(",",$nms[$fr[1]]);
					$ttlques=count($tlques);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="9")
			{
				$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
				$whr = array();
				$whr []= '`question` IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
				$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
				$sql .= ' GROUP BY `person`';
				$rowArray = $db->fetchAll ( $sql );
				$tlques=split(",",$this->getSanParam ( 'listpq' ));
				$ttlques=count($tlques);
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				foreach ( $qs as $kys => $vls ) {
					$fr=split('\^',$vls);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
			$sql = 'SELECT  DISTINCT p.`id`, p.`first_name` ,  p.`last_name` ,  p.`gender` FROM `person` as p, facility as f, ('.$location_sub_query.') as l, `person_qualification_option` as q WHERE p.`primary_qualification_option_id` = q.`id` and p.facility_id = f.id and f.location_id = l.id AND p.`primary_qualification_option_id` IN (SELECT `id` FROM `person_qualification_option` WHERE `parent_id` = ' . $criteria ['qualification_id'] . ') AND p.`is_deleted` = 0 AND p.`id` IN (';
			if(count($prsns)>0)
			{
				foreach ( $prsns as $k => $v ) {
					$sql = $sql . $v . ',';
				}
			}
			$sql = $sql . '0';
			if ($criteria ['facilityInput']) {
				$sql = $sql . ') AND p.facility_id = "' . $criteria ['facilityInput'] . '";';
			}
			else {
				$sql = $sql . ');';
			}
			$rowArray = $db->fetchAll ( $sql );
			if ($criteria ['outputType']) {
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function quescompAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['Questions'] = $this->getSanParam ( 'Questions' );
		$criteria ['outputType'] = $this->getSanParam ( 'outputType' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$qs=split('\$',$criteria ['Questions']);
			$sql='SELECT `person` FROM `comp`';
			$sql .= ' WHERE `active` = \'Y\'';
			$whr = array();
			foreach ( $qs as $k => $v ) {
				$qss=split('\^',$v);
				$whr[]='(`question`=\''.$qss[2].'\' AND `option`=\''.$qss[3].'\')';
			}
			if( !empty($whr) )
				$sql .= ' AND (' . implode(' OR ', $whr) . ')';

			$rowArray = $db->fetchAll ( $sql );

			$sql = 'SELECT  DISTINCT p.`id`, p.`first_name` ,  p.`last_name` ,  p.`gender` FROM `person` as p, `person_qualification_option` as q WHERE p.`primary_qualification_option_id` = q.`id` AND p.`primary_qualification_option_id` IN (SELECT `id` FROM `person_qualification_option` WHERE `parent_id` = ' . $criteria ['qualification_id'] . ') AND p.`is_deleted` = 0 AND p.`id` IN (';
			foreach ( $rowArray as $k => $v ) {
				$sql = $sql . $v['person'] . ',';
			}
			$sql = $sql . '0);';
			$rowArray = $db->fetchAll ( $sql );
			if ($criteria ['outputType']) {
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
		}
		$this->view->assign ( 'criteria', $criteria );
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
	}

	public function trainingsAction() {
		$this->view->assign ( 'mode', 'name' );

		return $this->trainingReport ();
	}

	public function trainingSearchAction() {
		$this->_countrySettings = array();
		$this->_countrySettings = System::getAll();

		$this->view->assign ( 'mode', 'search' );

		return $this->trainingReport ();
	}

	public function trainingbyParticipantsAction() {
		$this->view->assign ( 'mode', 'count' );

		return $this->trainingReport ();
	}

	public function trainingbyTitleAction() {
		$this->view->assign ( 'mode', 'name' );
		$this->view->assign ( 'expand_lists', 1 );

		return $this->trainingReport ();
	}

	public function trainingsMissingInformationAction(){
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'missing_info', 1 );

		return $this->trainingReport ();
	}

	public function trainingReport() {
		$this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $report = new Report();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');

		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) ? true : false;
                $province_id = $report->formatSelection($this->getSanParam('province_id'));
               
                $district_id = $report->formatSelection($this->getSanParam('district_id'));
                if(!empty($district_id)){
                    $locationTraining = $report->explodeGeogArray($district_id, "2");
                }else if(!empty($province_id)){
                    
                    $locationTraining = array();
                    foreach($province_id as $trainingLoc){
                        $statesLocations = array();
                        $statesLocations = Location::getLocationByCategory("state",$trainingLoc);
                       
                        $locationTraining = array_merge($locationTraining, $statesLocations);
                        $locationTraining = array_unique($locationTraining);
                    }
                }
                if(!empty($locationTraining)){
                    $statesLocationsImplode = implode(",",$locationTraining);
                    
                    $geoTrainingLocationWhere = "pt.location_id IN (".$statesLocationsImplode.")";
                }
                
                
                //echo $geoTrainingLocationWhere;exit;
                
		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_end_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = @$parts [0];
		$criteria ['start-month'] = @$parts [1];
		$criteria ['start-day'] = @$parts [2];

                
                $minimum_year = $parts [0];
                $minimum_month = $parts [1];
                $minimum_day = $parts [2];;
                
                $sql = "SELECT MAX(training_end_date) as \"start\" FROM training WHERE is_deleted=0 ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['start'];
			$parts = explode ( '-', $end_default );
			$criteria ['end-year'] = @$parts [0];
			$criteria ['end-month'] = @$parts [1];
			$criteria ['end-day'] = @$parts [2];
                        
                        
                $maximum_year = $parts[0];
                $maximum_month = $parts[1];
                $maximum_day = $parts[2];
                
                 
                $defaultTrainingEndDateStart = $minimum_month."/".$minimum_day."/".$minimum_year;
                $defaultTrainingEndDateEnd = $maximum_month."/".$maximum_day."/".$maximum_year;
                
                
               
                
		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			
                
                $defaultTrainingEndDateStart = $minimum_month."/".$minimum_day."/".$minimum_year;
                $defaultTrainingEndDateEnd = $maximum_month."/".$maximum_day."/".$maximum_year;
                
               
                
                
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}

                $criteria['trainingEndDateStart'] = $defaultTrainingEndDateStart;
                $criteria['trainingEndDateEnd'] = $defaultTrainingEndDateEnd;
                
//                if($this->getSanParam('trainingEndDateStart')){
//                $trainingEndDateStartValue = $this->getSanParam('trainingEndDateStart');
//                $criteria['trainingEndDateStart'] = $trainingEndDateStartValue;
//                $trainingEndDatesStart = array();
//                $trainingEndDatesStart = explode("/",$trainingEndDateStartValue);
//                
//                
//                $criteria ['start-day'] = $this->check_length_add_one($trainingEndDatesStart[1]);
//                $criteria ['start-month'] = $this->check_length_add_one($trainingEndDatesStart[0]);
//                $criteria ['start-year'] = $trainingEndDatesStart[2];
//                
//               
//                }
                //echo $this->getSanParam('trainingEndDateEnd');exit;
//                if($this->getSanParam('trainingEndDateEnd')){
//                     $trainingEndDateEndValue = $this->getSanParam('trainingEndDateEnd');
//                     $criteria['trainingEndDateEnd'] = $trainingEndDateEndValue;
//                      $trainingEndDatesEnd = array();
//                      $trainingEndDatesEnd = explode("/",$trainingEndDateEndValue);
//                    
//                      $criteria ['end-day'] = $this->check_length_add_one($trainingEndDatesEnd[1]);
//                      $criteria ['end-month'] = $this->check_length_add_one($trainingEndDatesEnd[0]);
//                      $criteria ['end-year'] = $trainingEndDatesEnd[2];
//                }
                
                if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
                
               
		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		// find training name from new category/title format: categoryid_titleid
		$ct_ids = $criteria ['training_category_and_title_id'] = $this->getSanParam ( 'training_category_and_title_id' );
		$criteria ['training_title_option_id'] = $this->_pop_all($ct_ids);

		$criteria ['training_location_id'] =                     $this->getSanParam ( 'training_location_id' );
		$criteria ['training_organizer_id'] =                    $this->getSanParam ( 'training_organizer_id' );
		$criteria ['training_pepfar_id'] =                       $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_method_id'] =                       $this->getSanParam ( 'training_method_id' );
		$criteria ['mechanism_id'] =                             $this->getSanParam ( 'mechanism_id' );
		$criteria ['training_topic_id'] =                        $this->getSanParam ( 'training_topic_id' );
		$criteria ['training_level_id'] =                        $this->getSanParam ( 'training_level_id' );
		$criteria ['training_primary_language_option_id'] =      $this->getSanParam ( 'training_primary_language_option_id' );
		$criteria ['training_secondary_language_option_id'] =    $this->getSanParam ( 'training_secondary_language_option_id' );
		$criteria ['training_category_id'] =                     $this->getSanParam ( 'training_category_id' ); //reset(explode('_',$ct_ids));//
		$criteria ['training_got_curric_id'] =                   $this->getSanParam ( 'training_got_curric_id' );
		$criteria ['is_tot'] =                                   $this->getSanParam ( 'is_tot' );
		$criteria ['funding_id'] =                               $this->getSanParam ( 'funding_id' );
		$criteria ['custom_1_id'] =                              $this->getSanParam ( 'custom_1_id' );
		$criteria ['custom_2_id'] =                              $this->getSanParam ( 'custom_2_id' );
		$criteria ['custom_3_id'] =                              $this->getSanParam ( 'custom_3_id' );
		$criteria ['custom_4_id'] =                              $this->getSanParam ( 'custom_4_id' );
		$criteria ['created_by'] =                               $this->getSanParam ( 'created_by' );
		$criteria ['creation_dates'] =                           $this->getSanParam ( 'creation_dates' );
		$criteria ['funding_min'] =                              $this->getSanParam ( 'funding_min' );
		$criteria ['funding_max'] =                              $this->getSanParam ( 'funding_max' );
		$criteria ['refresher_id'] =                             $this->getSanParam ( 'refresher_id' );
		$criteria ['person_to_training_viewing_loc_option_id'] = $this->getSanParam('person_to_training_viewing_loc_option_id');
		$criteria ['primary_responsibility_option_id'] =         $this->getSanParam ( 'primary_responsibility_option_id' );
		$criteria ['secondary_responsibility_option_id'] =       $this->getSanParam ( 'secondary_responsibility_option_id' );
		$criteria ['highest_edu_level_option_id'] =              $this->getSanParam ( 'highest_edu_level_option_id' );
		//$criteria ['attend_reason_option_id'] = $this->getSanParam ( 'attend_reason_option_id' );
		$criteria ['qualification_id'] =                         $this->getSanParam ( 'qualification_id' );
		$criteria ['qualification_secondary_id'] =               $this->getSanParam ( 'qualification_secondary_id' );
		$criteria ['doCount'] =       ($this->view->mode == 'count');
		$criteria ['doName'] =       ($this->view->mode == 'name');
		
		if($criteria['doCount'] || $criteria ['doName']) {
			$criteria ['age_max'] =                                $this->getSanParam ( 'age_max' );
			$criteria ['age_min'] =                                $this->getSanParam ( 'age_min' );
			$criteria ['training_gender'] =                       $this->getSanParam ( 'training_gender' );
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );
		$criteria ['showProvince'] =  ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or ! empty ( $criteria ['province_id'] ))));
		$criteria ['showDistrict'] =  ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or ! empty ( $criteria ['district_id'] ))));
		$criteria ['showRegionC'] =   ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or ! empty ( $criteria ['region_c_id'] ))));
		$criteria ['showRegionD'] =   ($this->getSanParam ( 'showRegionD' ) or ($criteria ['doCount'] and ($criteria ['region_d_id'] or ! empty ( $criteria ['region_d_id'] ))));
		$criteria ['showRegionE'] =   ($this->getSanParam ( 'showRegionE' ) or ($criteria ['doCount'] and ($criteria ['region_e_id'] or ! empty ( $criteria ['region_e_id'] ))));
		$criteria ['showRegionF'] =   ($this->getSanParam ( 'showRegionF' ) or ($criteria ['doCount'] and ($criteria ['region_f_id'] or ! empty ( $criteria ['region_f_id'] ))));
		$criteria ['showRegionG'] =   ($this->getSanParam ( 'showRegionG' ) or ($criteria ['doCount'] and ($criteria ['region_g_id'] or ! empty ( $criteria ['region_g_id'] ))));
		$criteria ['showRegionH'] =   ($this->getSanParam ( 'showRegionH' ) or ($criteria ['doCount'] and ($criteria ['region_h_id'] or ! empty ( $criteria ['region_h_id'] ))));
		$criteria ['showRegionI'] =   ($this->getSanParam ( 'showRegionI' ) or ($criteria ['doCount'] and ($criteria ['region_i_id'] or ! empty ( $criteria ['region_i_id'] ))));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0')));
		$criteria ['showLocation'] =  ($this->getSanParam ( 'showLocation' ) or ($criteria ['doCount'] and $criteria ['training_location_id']));
		$criteria ['showOrganizer'] = ($this->getSanParam ( 'showOrganizer' ) or ($criteria ['doCount'] and ($criteria ['training_organizer_id'])));
		$criteria ['showMechanism'] = ($this->getSanParam ( 'showMechanism' ) or ($criteria ['doCount'] and $criteria ['mechanism_id']));
		$criteria ['showPepfar'] =    ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0')));
		$criteria ['showMethod'] =    ($this->getSanParam ( 'showMethod' ) or ($criteria ['doCount'] and ($criteria ['training_method_id'] or $criteria ['training_method_id'] === '0')));
		$criteria ['showTopic'] =     ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showLevel'] =     ($this->getSanParam ( 'showLevel' ) or ($criteria ['doCount'] and $criteria ['training_level_id']));
		$criteria ['showTot'] =       ($this->getSanParam ( 'showTot' ) or ($criteria ['doCount'] and $criteria ['is_tot'] or $criteria ['is_tot'] === '0'));
		$criteria ['showRefresher'] = ($this->getSanParam ( 'showRefresher' ));
		$criteria ['showGotComment'] = ($this->getSanParam ( 'showGotComment' ));
		$criteria ['showPrimaryLanguage'] = ($this->getSanParam ( 'showPrimaryLanguage' ) or ($criteria ['doCount'] and $criteria ['training_primary_language_option_id'] or $criteria ['training_primary_language_option_id'] === '0'));
		$criteria ['showSecondaryLanguage'] = ($this->getSanParam ( 'showSecondaryLanguage' ) or ($criteria ['doCount'] and $criteria ['training_secondary_language_option_id'] or $criteria ['training_secondary_language_option_id'] === '0'));
		$criteria ['showFunding'] =   ($this->getSanParam ( 'showFunding' ) or ($criteria ['doCount'] and $criteria ['funding_id'] or $criteria ['funding_id'] === '0' or $criteria ['funding_min'] or $criteria ['funding_max']));
		$criteria ['showCategory'] =  ($this->getSanParam ( 'showCategory' ) or ($criteria ['doCount'] and $criteria ['training_category_id'] or $criteria ['training_category_id'] === '0'));
		$criteria ['showGotCurric'] = ($this->getSanParam ( 'showGotCurric' ) or ($criteria ['doCount'] and $criteria ['training_got_curric_id'] or $criteria ['training_got_curric_id'] === '0'));
		$criteria ['showCustom1'] =   ($this->getSanParam ( 'showCustom1' ));
		$criteria ['showCustom2'] =   ($this->getSanParam ( 'showCustom2' ));
		$criteria ['showCustom3'] =   ($this->getSanParam ( 'showCustom3' ));
		$criteria ['showCustom4'] =   ($this->getSanParam ( 'showCustom4' ));
		$criteria ['showCreatedBy'] = ($this->getSanParam ( 'showCreatedBy' ));
		$criteria['showCreationDate']=($this->getSanParam ( 'showCreationDate' ));
		$criteria ['showStartDate'] =   ($this->getSanParam ( 'showStartDate')); //TA:17: 9/3/2014
		$criteria ['showEndDate'] =   ($this->getSanParam ( 'showEndDate'));
		$criteria ['showRespPrim'] =  ($this->getSanParam ( 'showRespPrim' ));
		$criteria ['showRespSecond'] =($this->getSanParam ( 'showRespSecond' ));
		$criteria ['showHighestEd'] = ($this->getSanParam ( 'showHighestEd' ));
		//$criteria ['showReason'] =  ($this->getSanParam ( 'showReason' ));
		$criteria ['showAge'] =       ($this->getSanParam ( 'showAge' ) && $criteria ['doCount']) || ($this->getSanParam ( 'showAge' ) && $criteria ['doName']);
		$criteria ['showGender'] =    ($this->getSanParam ( 'showGender' ) && $criteria ['doCount']) || ($this->getSanParam ( 'showGender' ) && $criteria ['doName']);
		$criteria ['showViewingLoc'] = $this->getSanParam ( 'showViewingLoc');
		$criteria ['showQualPrim']   = $this->getSanParam ( 'showQualPrim');
		$criteria ['showQualSecond'] = $this->getSanParam ( 'showQualSecond');

		$criteria ['training_participants_type'] = $this->getSanParam ( 'training_participants_type' );

		// row creation dates - explaination: server might be in NYC and client in Africa, server needs to check for trainings created at the day selected, minus the time difference (or plus it), accomplished by hidden input field storing clients javascript time. testing this (bugfix)
		$criteria['date_added'] = array();
		$userTime = $this->getSanParam('date_localtime') ? strtotime($this->getSanParam('date_localtime')) : time();
                if(isset($criteria['creation_dates'][0])){
                    if ( $criteria['creation_dates'][0] && !empty($criteria['creation_dates'][0]) ) {
			$difference = time() - $userTime;
			$date1 = strtotime( $criteria['creation_dates'][0]);
			$criteria['date_added'][0] = date( 'Y-m-d H:i:s', $date1 + $difference ); // keep the original date in same format for template
		}
                }
                
                
		if(isset($criteria['creation_dates'][1])){
                    if ( $criteria['creation_dates'][1] && !empty($criteria['creation_dates'][1]) ) {
			$difference = time() - $userTime;
			$date2 = strtotime( $criteria['creation_dates'][1]);
			$date2 = strtotime("+1 day", $date2); // 11:59
			$criteria['date_added'][1] = date ('Y-m-d H:i:s', $date2 + $difference );
		}
                }
		

		/////////////////////////////////
		// missing fields report
		//
		/////////////////////////////////
		if ($this->view->missing_info)
		{
			$flds = array(
				'Training name'          =>			'training_title_option_id',
				'Training end date'      =>			'training_end_date',
				'Training organizer'     =>			'training_organizer_option_id',
				'Training location'      =>			'training_location_id',
				'Training level'         =>			'training_level_option_id',
				'PEPFAR category'        =>			'tpep.training_pepfar_categories_option_id',
				'Training Method'        =>			'training_method_option_id',
				'Training topic'         =>			'ttopic.training_topic_option_id',
				'Training of Trainers'   =>			'is_tot',
				'Refresher course'       =>			'is_refresher',
				'Funding'                =>			'tfund.training_funding_option_id',
				'National curriculum'    =>			'training_got_curriculum_option_id',
				'National curriculum comment' =>	'got_comments',
				'Training Comments'      =>			'comments',
				'Course Objectives'      =>			'course_id', //objectives
				'Primary Language'       =>			'training_primary_language_option_id',
				'Secondary Language'     =>			'training_secondary_language_option_id',
				'No Trainers'            =>			'report_no_trainers',
				'No Participant'         =>			'report_no_participants',
				'No Scores for Participants' =>		'report_no_scores',
				'Pre Test Average'       =>			'pre',
				'Post Test Averages'     =>			'post',
				'Custom 1'               =>			'training_custom_1_option_id',
				'Custom 2'               =>			'training_custom_2_option_id',
				'Custom 3'               =>			'custom_3',
				'Custom 4'               =>			'custom_4',
				'Approval Status'        =>			'is_approved',
				'Approved Trainings'     =>			'report_is_approved1',
				'Rejected Trainings'     =>			'report_is_approved0',
				'With Attached Documents' =>		'report_with_attachments',
				'WithOut Attached Documents' =>		'report_without_attachments'
				);
			$this->view->assign('flds', $flds); // we'll use these again in the view to print our options

			$criteria['searchflds'] = $this->getSanParam('searchflds'); // user selected these fields
			$w = array();	// temporary placeholder for our where clauses
			$normalFields = array(); // we can just use a 'where [normalField] is null' here
			// criteria and joins
			foreach ($criteria['searchflds'] as $i => $v) {
				if ( $v == 'tpep.training_pepfar_categories_option_id' ) { $criteria ['showPepfar'] = 'on'; continue; }
				if ( $v == 'ttopic.training_topic_option_id' ) { $criteria ['showTopic'] = 'on'; continue; }
				if ( $v == 'tfund.training_funding_option_id' ) { $criteria ['showFunding'] = 'on'; continue; }
				if ( $v == 'report_no_trainers' ) {         $w[] = 'pt.has_known_participants = 1 and pt.id not in (select distinct training_id from training_to_trainer)'; continue; }
				if ( $v == 'report_no_participants' ) {     $w[] = 'pt.has_known_participants = 1 and pt.id not in (select distinct training_id from person_to_training )'; continue; }
				if ( $v == 'report_no_scores' ) {           $w[] = 'pt.id not in (select distinct training_id from person_to_training inner join score on person_to_training_id = person_to_training.id)'; continue; }
				if ( $v == 'report_is_approved1' ) {        $w[] = 'is_approved = 1'; continue; }
				if ( $v == 'report_is_approved0' ) {        $w[] = 'is_approved = 0'; continue; }
				if ( $v == 'report_with_attachments' ) {    $w[] = "pt.id   in   (select distinct parent_id from file where parent_table = 'training')"; continue; }
				if ( $v == 'report_without_attachments' ) { $w[] = "pt.id not in (select distinct parent_id from file where parent_table = 'training')"; continue; }
				$normalFields[] = $v;
			}

			// wheres
			foreach($normalFields as $row){
				$w[] = "($row is null or $row = 0 or $row = '')";
			}
			if ( count($w) && $criteria['go'] )
				$where[] = '(' . implode(' or ', $w) . ')';
		} // end missing fields report

		// defaults
		if (! isset($criteria ['go'])) {
			$criteria ['showTrainingTitle'] = 1;
		}

		// run report
		if (isset($criteria ['go'])) {

			$sql = 'SELECT ';

			if (!empty($criteria ['doCount'])) {
				$sql .= ' COUNT(pt.person_id) as "cnt" ';
			} else {
				$sql .= ' DISTINCT pt.id as "id", ptc.pcnt, pt.training_start_date, pt.training_end_date, pt.has_known_participants  ';
			}

			if (!empty($criteria ['showTrainingTitle'])) {
				$sql .= ', training_title ';
			}
			
			

//			
			if (!empty($criteria ['showDistrict'])) {
				$sql .= ',district_name ';
			}
			if (!empty($criteria ['showProvince'])) {
				$sql .= ', province_name ';
			}

			if (!empty($criteria ['showOrganizer'])) {
				$sql .= ', torg.training_organizer_phrase ';
			}

			if (!empty($criteria ['showMechanism']) && isset($display_training_partner)) {
				$sql .= ', organizer_partners.mechanism_id ';
			}

			if (!empty($criteria ['showLevel'])) {
				$sql .= ', tlev.level_title as training_level_phrase ';
			}

			if (!empty($criteria ['showCategory'])) {
				$sql .= ', tcat.training_category_phrase ';
			}

			if (!empty($criteria ['showPepfar']) || !empty($criteria ['training_pepfar_id']) || $criteria ['training_pepfar_id'] === '0') {
				if (!empty($criteria ['doCount'])) {
					$sql .= ', tpep.pepfar_category_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tpep.pepfar_category_phrase) as "pepfar_category_phrase" ';
				}
			}

			if (!empty($criteria ['showMethod'])) {
				$sql .= ', tmeth.training_method_phrase ';
			}

			if (!empty($criteria ['showTopic'])) {
				if (!empty($criteria ['doCount'])) {
					$sql .= ', ttopic.training_topic_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT ttopic.training_topic_phrase ORDER BY training_topic_phrase) AS "training_topic_phrase" ';
				}
			}

			if (!empty($criteria ['showTot'])) {
				$sql .= ", IF(is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot";
			}

			if (!empty($criteria ['showRefresher'])) {
				$sql .= ", IF(is_refresher,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_refresher";
			}

			if (!empty($criteria ['showSecondaryLanguage'])) {
				$sql .= ', tlos.language_phrase as "secondary_language_phrase" ';
			}
			if (!empty($criteria ['showPrimaryLanguage'])) {
				$sql .= ', tlop.language_phrase as "primary_language_phrase" ';
			}
			if (!empty($criteria ['showGotComment'])) {
				$sql .= ", pt.got_comments";
			}
			if (!empty($criteria ['showGotCurric'])) {
				$sql .= ', tgotc.training_got_curriculum_phrase ';
			}

			if (!empty($criteria ['showFunding'])) {
				if (!empty($criteria ['doCount'])) {
					$sql .= ', tfund.funding_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tfund.funding_phrase ORDER BY funding_phrase) as "funding_phrase" ';
				}
			}
//			if (isset( $criteria['showCustom1']) ) {
//				$sql .= ', tqc.custom1_phrase ';
//			} // todo custom2-4
			if ( isset($criteria['showCreatedBy']) && !empty($criteria['showCreatedby']) ) {
				$sql .= ", CONCAT(user.first_name, CONCAT(' ', user.last_name)) as created_by_user ";
			}
			if (isset($criteria['showCreationDate']) && !empty($criteria['showCreationDate']) ) {
				$sql .= ", DATE_FORMAT(pt.timestamp_created, '%Y-%m-%d') as created_date  ";
			}
			if (isset($criteria ['showGender']) && !empty($criteria['showGender'])) {
				$sql .= ', gender ';
			}
			if (isset($criteria ['showAge']) && !empty($criteria['showAge'])) {
				$sql .= ', age ';
			}
			if (isset($criteria ['showActive']) && !empty($criteria['showActive'])) {
				$sql .= ', pt.active ';
			}
			if (isset( $criteria['showViewingLoc']) && !empty($criteria['showViewingLoc']) ) {
				$sql .= ', location_phrase, GROUP_CONCAT(DISTINCT location_phrase ORDER BY location_phrase) as "location_phrases" ';
			}
//			if ( isset($criteria['showCustom1']) ) {
//				$sql .= ', tqc.custom1_phrase ';
//			}
//			if ( isset( $criteria['showCustom2'] )) {
//				$sql .= ', tqc2.custom2_phrase';
//			}
//			if (isset( $criteria['showCustom3']) ) {
//				$sql .= ', pt.custom_3';
//			}
//			if ( isset($criteria['showCustom4']) ) {
//				$sql .= ', pt.custom_4';
//			}
			if ( ( !empty($criteria['doCount']) && !empty($criteria ['showQualPrim']) ) || (!empty($criteria['doName']) && !empty($criteria ['showQualPrim'])) ) {
				$sql .= ', pq.qualification_phrase ';
			}
			if ( (!empty($criteria['doCount']) && !empty($criteria ['showQualSecond'])) || (!empty($criteria['doName']) && !empty($criteria ['showQualSecond']))) {
				$sql .= ', pqs.qualification_phrase AS qualification_secondary_phrase';
			}
                       if(isset($geoLocationWhere) && $geoLocationWhere!=""){
                            $sql .=", pt.location_id";
                        }
                        
                        
			// prepare the location sub query
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			//if we're doing a participant count, then LEFT JOIN with the participants
			//otherwise just select the core training info
                           // SELECT * FROM training_location LEFT JOIN  (SELECT location.id,location.location_name as dstrict_name,loc.location_name as province_name FROM `location` LEFT JOIN location as loc on location.parent_id = loc.id WHERE location.tier =2 or location.tier= 1) as location ON training_location.location_id = location.id
			if (!empty($criteria ['doCount'] )|| !empty($criteria ['doName']) ) {
				$sql .= ' FROM (SELECT training.*, pers.person_id as "person_id",location_temp.*, tto.training_title_phrase AS training_title, training_location.training_location_name,training_location.location_id, primary_qualification_option_id, pers.location_phrase as location_phrase'.
				'         FROM training ' .
				'         LEFT JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id)' .
				'         LEFT JOIN training_location ON training.training_location_id = training_location.location_id ' .
				'         LEFT JOIN  (SELECT location.id as geog_location_id,location.location_name as district_name,loc.location_name as province_name FROM `location` LEFT JOIN location as loc on location.parent_id = loc.id WHERE location.tier =2 or location.tier= 1) location_temp ON training_location.location_id = location_temp.geog_location_id ' .
				'         LEFT JOIN (SELECT person_id,training_id, person_to_training_viewing_loc_option.location_phrase,primary_qualification_option_id,
											person.custom_3 as person_custom_3, person.custom_4 as person_custom_4, person.custom_5 as person_custom_5
										FROM person
										JOIN person_to_training ON person_to_training.person_id = person.id
										LEFT JOIN person_to_training_viewing_loc_option ON person_to_training.viewing_location_option_id = person_to_training_viewing_loc_option.id
									) as pers ON training.id = pers.training_id WHERE training.is_deleted=0) as pt ';
			} else {
				$sql .= ' FROM (SELECT training.*,location_temp.*, tto.training_title_phrase AS training_title,training_location.training_location_name,training_location.location_id'.
				'       FROM training  ' .
				'         LEFT JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id) ' .
				'         LEFT JOIN training_location ON training.training_location_id = training_location.id ' .
				'         LEFT JOIN  (SELECT location.id as geog_location_id,location.location_name as district_name,loc.location_name as province_name FROM `location` LEFT JOIN location as loc on location.parent_id = loc.id WHERE location.tier =2 or location.tier= 1) location_temp ON training_location.location_id = location_temp.geog_location_id ' .
				'  WHERE training.is_deleted=0) as pt ';
				$sql .= " LEFT JOIN (SELECT COUNT(id) as `pcnt`,training_id FROM person_to_training GROUP BY training_id) as ptc ON ptc.training_id = pt.id ";
			}
			if (!empty($criteria ['doName'])) {
				$sql .= " LEFT JOIN (SELECT COUNT(id) as `pcnt`,training_id FROM person_to_training GROUP BY training_id) as ptc ON ptc.training_id = pt.id ";
			}
			if (!(!empty($criteria['doCount']) || !empty($criteria['doName'])) && (!empty($criteria['showViewingLoc']) || !empty($criteria['person_to_training_viewing_loc_option_id']))) {
				$sql .= ' LEFT JOIN person_to_training ON pt.person_id = person_to_training.person_id AND person_to_training.training_id = pt.id ';
				$sql .= ' LEFT JOIN person_to_training_viewing_loc_option ON person_to_training.viewing_location_option_id = person_to_training_viewing_loc_option.id ';
			}

			if (isset($criteria ['showOrganizer']) or !empty($criteria ['training_organizer_id']) || !empty($criteria ['showMechanism'])  || !empty($criteria ['mechanism_id'])) {
				$sql .= '	LEFT JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';
			}

			if (!empty($criteria ['showMechanism']) || !empty($criteria ['mechanism_id']) && !empty( $display_training_partner)) {
				$sql .= ' LEFT JOIN organizer_partners ON organizer_partners.organizer_id = torg.id';
			}

			if (!empty($criteria ['showLevel'] ) || !empty($criteria ['training_level_id'])) {
				$sql .= '	JOIN training_level as tlev ON tlev.id = pt.training_level_option_id ';
			}

			if (!empty($criteria ['showMethod']) || !empty($criteria ['training_method_id'])) {
				$sql .= ' JOIN training_method_option as tmeth ON tmeth.id = pt.training_method_option_id ';
			}

			if (!empty($criteria ['showPepfar']) || !empty($criteria ['training_pepfar_id']) ||  $criteria ['training_pepfar_id'] === '0') {
				$sql .= '	LEFT JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if (!empty($criteria ['showTopic']) || !empty($criteria ['training_topic_id'])) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
			}

			if (!empty($criteria ['showPrimaryLanguage']) || !empty($criteria ['training_primary_language_option_id'])) {
				$sql .= ' LEFT JOIN trainer_language_option as tlop ON tlop.id = pt.training_primary_language_option_id ';
			}

			if (!empty($criteria ['showSecondaryLanguage']) || !empty($criteria ['training_secondary_language_option_id'])) {
				$sql .= ' LEFT JOIN trainer_language_option as tlos ON tlos.id = pt.training_secondary_language_option_id ';
			}

			if (!empty($criteria ['showFunding']) || (intval ( $criteria ['funding_min'] ) or intval ( $criteria ['funding_max'] ))) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttfo.training_funding_option_id, funding_phrase, ttfo.funding_amount FROM training_to_training_funding_option as ttfo JOIN training_funding_option as tfo ON ttfo.training_funding_option_id = tfo.id) as tfund ON tfund.training_id = pt.id ';
			}

			if (!empty($criteria ['showGotCurric']) || !empty($criteria ['training_got_curric_id'])) {
				$sql .= '	LEFT JOIN training_got_curriculum_option as tgotc ON tgotc.id = pt.training_got_curriculum_option_id';
			}

			if (!empty($criteria ['showCategory']) or !empty ( $criteria ['training_category_id'] )) {
				$sql .= '
				LEFT JOIN training_category_option_to_training_title_option tcotto ON (tcotto.training_title_option_id = pt.training_title_option_id)
				LEFT JOIN training_category_option tcat ON (tcotto.training_category_option_id = tcat.id)
				';
			}
			if (!empty( $criteria['showCustom1']) || !empty($criteria ['custom_1_id'] )) {
				$sql .= ' LEFT JOIN training_custom_1_option as tqc ON pt.training_custom_1_option_id = tqc.id  ';
			}
			if ( !empty($criteria['showCustom2']) || !empty($criteria ['custom_2_id'] )) {
				$sql .= ' LEFT JOIN training_custom_2_option as tqc2 ON pt.training_custom_2_option_id = tqc2.id  ';
			}
			#not multi opt fields yet - no join required
			#if ( $criteria['showCustom3'] || $criteria ['custom_3_id'] ) {
			#	//$sql .= ' LEFT JOIN training_custom_3_option as custom_3_id ON pt.training_custom_3_option_id = tq3c.id  ';
			#}
			#if ( $criteria['showCustom4'] || $criteria ['custom_4_id'] ) {
			#	//$sql .= ' LEFT JOIN training_custom_4_option as tqc ON pt.training_custom_4_option_id = tqc4.id  ';
			#}

			if ( !empty($criteria['showCreatedBy'] ) || !empty($criteria ['created_by']) ) {
				$sql .= ' LEFT JOIN user ON user.id = pt.created_by  ';
			}
                       

			if ( !empty($criteria['showGender']) || !empty($criteria['showAge']) || !empty($criteria['training_gender']) || !empty($criteria['age_min']) || !empty($criteria['age_max'])) {
				$personAlias  = ($criteria['doCount'] || $criteria['doName']) ? 'pt.person_id'  : 'pt.person_id';

				$sql .= " LEFT JOIN person_to_training as ptt on ptt.training_id = pt.id AND $personAlias = ptt.person_id AND pt.is_deleted = 0 ";
				$sql .= ' LEFT JOIN (SELECT id as pid, gender
								,CASE WHEN birthdate  IS NULL OR birthdate = \'0000-00-00\' THEN NULL ELSE ((date_format(now(),\'%Y\') - date_format(birthdate,\'%Y\')) - (date_format(now(),\'00-%m-%d\') < date_format(birthdate,\'00-%m-%d\')) ) END as "age"
								FROM person where is_deleted = 0) as perssexage ON perssexage.pid = ptt.person_id ';
			
                           
                        }
                        //echo 'This is outside the thing';exit;

			if (( !empty($criteria['doCount']) || !empty($criteria['doName'])) && ( !empty($criteria ['showQualPrim']) || !empty($criteria ['showQualSecond']) || !empty($criteria ['qualification_id'])  || !empty($criteria ['qualification_secondary_id'])) ) {
				// primary qualifications
				$sql .= 'LEFT JOIN person_qualification_option as pq ON (
							(pt.primary_qualification_option_id = pq.id AND pq.parent_id IS NULL)
							OR
							pq.id = (SELECT parent_id FROM person_qualification_option WHERE id = pt.primary_qualification_option_id LIMIT 1))';

				// secondary qualifications
				$sql .= 'LEFT JOIN person_qualification_option as pqs ON (pt.primary_qualification_option_id = pqs.id AND pqs.parent_id IS NOT NULL)';
			}

			$where [] = ' pt.is_deleted=0 ';

			// restricted access?? only show trainings we have the ACL to view
			$org_allowed_ids = allowed_organizer_access($this);
			if (!empty($org_allowed_ids)) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where [] = " pt.training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)
				$where []= " pt.training_organizer_option_id in ($site_orgs) ";

			// criteria
			if (!empty($criteria ['training_participants_type'])) {
				if ($criteria ['training_participants_type'] == 'has_known_participants') {
					$where [] = ' pt.has_known_participants = 1 ';
				}
				if ($criteria ['training_participants_type'] == 'has_unknown_participants') {
					$where [] = ' pt.has_known_participants = 0 ';

				}
			}


			if ($this->_is_not_filter_all($criteria['training_title_option_id']) && ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0')) {
				$where [] = 'pt.training_title_option_id in (' . $this->_sql_implode($criteria ['training_title_option_id']) . ')';
			}
			if (!empty($criteria ['training_location_id']) && !empty($criteria['training_location_id'])) {
				$where [] = ' pt.training_location_id = \'' . $criteria ['training_location_id'] . '\'';
			}

			if ($this->_is_not_filter_all($criteria['training_organizer_id']) && !empty($criteria ['training_organizer_id']) or $criteria ['training_organizer_id'] === '0') {
				$where [] = ' pt.training_organizer_option_id in (' . $this->_sql_implode($criteria ['training_organizer_id']) . ')';
			}

			if (!empty($criteria ['mechanism_id']) or $criteria ['mechanism_id'] === '0' && $display_training_partner) {
				$where [] = ' organizer_partners.mechanism_id = \'' . $criteria ['mechanism_id'] . '\'';
			}

			if ($this->_is_not_filter_all($criteria['training_topic_id']) && !empty($criteria ['training_topic_id']) or $criteria ['training_topic_id'] === '0') {
				$where [] = ' ttopic.training_topic_option_id in (' . $this->_sql_implode($criteria ['training_topic_id']) . ')';
			}

			if (!empty($criteria ['training_level_id']) && !empty($criteria['training_level_id'])) {
				$where [] = ' pt.training_level_option_id = \'' . $criteria ['training_level_id'] . '\'';
			}

			if (!empty($criteria ['training_pepfar_id']) or $criteria ['training_pepfar_id'] === '0') {
				$where [] = ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if (!empty($criteria ['training_method_id']) or $criteria ['training_method_id'] === '0') {
				$where [] = ' tmeth.id = \'' . $criteria ['training_method_id'] . '\'';
			}

			if (!empty($criteria ['training_primary_language_option_id']) or $criteria ['training_primary_language_option_id'] === '0') {
				$where [] = ' pt.training_primary_language_option_id = \'' . $criteria ['training_primary_language_option_id'] . '\'';
			}

			if (!empty($criteria ['training_secondary_language_option_id']) or $criteria ['training_secondary_language_option_id'] === '0') {
				$where [] = ' pt.training_secondary_language_option_id = \'' . $criteria ['training_secondary_language_option_id'] . '\'';
			}

//			if ($criteria ['province_id'] && ! empty ( $criteria ['province_id'] )) {
//				$where [] = ' pt.province_id IN (' . implode ( ',', $criteria ['province_id'] ) . ')';
//			}
//
//			if ($criteria ['district_id'] && ! empty ( $criteria ['district_id'] )) {
//				$where [] = ' pt.district_id IN (' . implode ( ',', $criteria ['district_id'] ) . ')';
//			}
//
//			if ($criteria ['region_c_id'] && ! empty ( $criteria ['region_c_id'] )) {
//				$where [] = ' pt.region_c_id IN (' . implode ( ',', $criteria ['region_c_id'] ) . ')';
//			}
                              /*
			if ($criteria ['region_d_id'] && ! empty ( $criteria ['region_d_id'] )) {
				$where [] = ' pt.region_d_id IN (' . implode ( ',', $criteria ['region_d_id'] ) . ')';
			}

			if ($criteria ['region_e_id'] && ! empty ( $criteria ['region_e_id'] )) {
				$where [] = ' pt.region_e_id IN (' . implode ( ',', $criteria ['region_e_id'] ) . ')';
			}

			if ($criteria ['region_f_id'] && ! empty ( $criteria ['region_f_id'] )) {
				$where [] = ' pt.region_f_id IN (' . implode ( ',', $criteria ['region_f_id'] ) . ')';
			}

			if ($criteria ['region_g_id'] && ! empty ( $criteria ['region_g_id'] )) {
				$where [] = ' pt.region_g_id IN (' . implode ( ',', $criteria ['region_g_id'] ) . ')';
			}

			if ($criteria ['region_h_id'] && ! empty ( $criteria ['region_h_id'] )) {
				$where [] = ' pt.region_h_id IN (' . implode ( ',', $criteria ['region_h_id'] ) . ')';
			}

			if ($criteria ['region_i_id'] && ! empty ( $criteria ['region_i_id'] )) {
				$where [] = ' pt.region_i_id IN (' . implode ( ',', $criteria ['region_i_id'] ) . ')';
			}
*/
                        if(!empty($criteria['end-year'])){
			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where [] = ' training_end_date >= \'' . $startDate . '\'  AND training_end_date <= \'' . $endDate . '\'  ';
			}
                        }
                        if( isset($geoTrainingLocationWhere) && $geoTrainingLocationWhere!="" ){
                            $where[] = $geoTrainingLocationWhere;
                        }

			if (intval ( $criteria ['funding_min'] ) or intval ( $criteria ['funding_max'] )) {
				if (intval ( $criteria ['funding_min'] ))
				$where [] = 'tfund.funding_amount >= \'' . $criteria ['funding_min'] . '\' ';
				if (intval ( $criteria ['funding_max'] ))
				$where [] = 'tfund.funding_amount <= \'' . $criteria ['funding_max'] . '\' ';
			}

			if (intval ( $criteria ['is_tot'] )) {
				$where [] = ' is_tot = ' . $criteria ['is_tot'];
			}

			if (!empty($criteria ['funding_id']) or $criteria ['funding_id'] === '0') {
				$where [] = ' tfund.training_funding_option_id = \'' . $criteria ['funding_id'] . '\'';
			}

			if (!empty($criteria ['training_category_id']) or $criteria ['training_category_id'] === '0') {
				$where [] = ' tcat.id = \'' . $criteria ['training_category_id'] . '\'';
			}

			if ((!empty($criteria ['training_got_curric_id']) or $criteria ['training_got_curric_id'] === '0') && !empty($criteria['training_got_curric_id'])) {
				$where [] = ' tgotc.id = \'' . $criteria ['training_got_curric_id'] . '\'';
			}

			if ((!empty($criteria ['custom_1_id']) or $criteria ['custom_1_id'] === '0') && !empty($criteria['custom_1_id'])) {
				$where [] = ' pt.training_custom_1_option_id = \'' . $criteria ['custom_1_id'] . '\'';
			}
			if ((!empty($criteria ['custom_2_id']) or $criteria ['custom_2_id'] === '0') && !empty($criteria['custom_2_id'])) {
				$where [] = ' pt.training_custom_2_option_id = \'' . $criteria ['custom_2_id'] . '\'';
			}
			if ((!empty($criteria ['custom_3_id']) or $criteria ['custom_3_id'] === '0')&& !empty($criteria['custom_3_id'])) {
				$where [] = ' pt.custom_3 = \'' . $criteria ['custom_3_id'] . '\'';
			}
			if ((!empty($criteria ['custom_4_id']) or $criteria ['custom_4_id'] === '0') && !empty($criteria['custom_4_id'])) {
				$where [] = ' pt.custom_4 = \'' . $criteria ['custom_4_id'] . '\'';
			}

			if ((!empty($criteria ['created_by']) or $criteria ['created_by'] === '0') && !empty($criteria['created_by'])) {
				$where [] = ' pt.created_by in (' . $this->_trainsmart_implode($criteria ['created_by']) . ')';
			}

			if (!empty($criteria ['date_added'])) {
				if ( isset( $criteria['date_added'][0] ) && !empty( $criteria['date_added'][0] ) ){
					$where [] = " pt.timestamp_created >= '".$criteria['date_added'][0]."' ";
				}
				if ( isset( $criteria['date_added'][1] ) && !empty( $criteria['date_added'][1] ) ){
					$where [] = " pt.timestamp_created <= '".$criteria['date_added'][1]."' ";
				}
			}
			if (isset($criteria ['training_gender']) && !empty($criteria['training_gender'])) {
				$where [] = " gender = '{$criteria['training_gender']}'";
			}

			if (isset($criteria ['age_min']) && !empty($criteria['age_min'])) {
				$where [] = " age >= {$criteria['age_min']}";
			}

			if (isset($criteria ['age_max'])&& !empty($criteria['age_max'])) {
				$where [] = " age <= {$criteria['age_max']}";
			}

			if (isset($criteria ['person_to_training_viewing_loc_option_id']) && !empty($criteria['person_to_training_viewing_loc_option_id'])) {
				$where [] = 'person_to_training.viewing_location_option_id = ' . $criteria['person_to_training_viewing_loc_option_id'];
			}
                        
                        if(!empty($criteria['qualification_id'])){
			if (isset($criteria['doCount'] , $criteria ['qualification_id']) || isset($criteria['doName'] , $criteria ['qualification_id'])) {
				$where [] = ' (pq.id = ' . $criteria ['qualification_id'] . ' OR pqs.parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
                        }
                        if(!empty($criteria['qualification_secondary_id'])){
			if (isset($criteria['doCount'] , $criteria ['qualification_secondary_id']) || isset($criteria['doName'] , $criteria ['qualification_secondary_id'])) {
				$where [] = ' pqs.id = ' . $criteria ['qualification_secondary_id'];
			}
                        }

			if ($where)
				$sql .= ' WHERE ' . implode ( ' AND ', $where );

			if ($criteria ['doCount']) {

				$groupBy = array();

				if (!empty($criteria ['showTrainingTitle']))     $groupBy []=  '  pt.training_title_option_id';
				if (!empty($criteria ['showProvince']))          $groupBy []=  '  pt.province_id';
				if (!empty($criteria ['showDistrict']))          $groupBy []=  '  pt.district_id';
				if (!empty($criteria ['showRegionC']))           $groupBy []=  '  pt.region_c_id';
				if (!empty($criteria ['showRegionD']))           $groupBy []=  '  pt.region_d_id';
				if (!empty($criteria ['showRegionE']))           $groupBy []=  '  pt.region_e_id';
				if (!empty($criteria ['showRegionF']) )          $groupBy []=  '  pt.region_f_id';
				if (!empty($criteria ['showRegionG']))           $groupBy []=  '  pt.region_g_id';
				if (!empty($criteria ['showRegionH']))           $groupBy []=  '  pt.region_h_id';
				if (!empty($criteria ['showRegionI']))           $groupBy []=  '  pt.region_i_id';
				if (!empty($criteria ['showLocation']))          $groupBy []=  '  pt.training_location_id';
				if (!empty($criteria ['showOrganizer']) )        $groupBy []=  '  pt.training_organizer_option_id';
				if (!empty($criteria ['showMechanism'] ) && $display_training_partner) $groupBy []=  '  organizer_partners.mechanism_id';
				if (!empty($criteria ['showCustom1']))           $groupBy []=  '  pt.training_custom_1_option_id';
				if (!empty($criteria ['showCustom2']))           $groupBy []=  '  pt.training_custom_2_option_id';
				if (!empty($criteria ['showCustom3']))           $groupBy []=  '  pt.custom_3';
				if (!empty($criteria ['showCustom4']))           $groupBy []=  '  pt.custom_4';
				if (!empty($criteria ['showTopic']))             $groupBy []=  '  ttopic.training_topic_option_id';
				if (!empty($criteria ['showLevel']))             $groupBy []=  '  pt.training_level_option_id';
				if (!empty($criteria ['showPepfar']))            $groupBy []=  '  tpep.training_pepfar_categories_option_id';
				if (!empty($criteria ['showMethod']))            $groupBy []=  '  tmeth.id';
				if (!empty($criteria ['showTot']))               $groupBy []=  '  pt.is_tot';
				if (!empty($criteria ['showRefresher']))         $groupBy []=  '  pt.is_refresher';
				if (!empty($criteria ['showGotCurric']))         $groupBy []=  '  pt.training_got_curriculum_option_id';
				if (!empty($criteria ['showPrimaryLanguage']))   $groupBy []=  '  pt.training_primary_language_option_id';
				if (!empty($criteria ['showSecondaryLanguage'])) $groupBy []=  '  pt.training_secondary_language_option_id';
				if (!empty($criteria ['showFunding']))           $groupBy []=  '  tfund.training_funding_option_id';
				if (!empty($criteria ['showCreatedBy']))         $groupBy []=  '  pt.created_by';
				if (!empty($criteria ['showCreationDate']))      $groupBy []=  '  pt.timestamp_created';
				if (!empty($criteria ['showGender']))            $groupBy []=  '  gender';
				if (!empty($criteria ['showAge']))               $groupBy []=  '  age';
				if (!empty($criteria ['showViewingLoc']))        $groupBy []=  '  location_phrase';
				if (!empty($criteria ['showQualPrim']))          $groupBy []=  '  pq.qualification_phrase';
				if (!empty($criteria ['showQualSecond']))        $groupBy []=  '  pqs.qualification_phrase';

				if (!empty($groupBy)) {
					$sql .= ' GROUP BY ' . implode(',',$groupBy);
				}

				if (!empty($criteria['showAge']) || !empty($criteria['showGender'])) {
					$sql .= ' HAVING count(pt.person_id) > 0 ';
				}
			} else {

				$sql .= ' GROUP BY pt.id';

			}
                        
			if ($this->view->mode == 'search') {
				$sql .= ' ORDER BY training_start_date DESC';
			}
                                                                
                       // print_r($sql);exit; 
                        Helper2::jLog($sql);
			$rowArray = $db->fetchAll ( $sql );
                        Helper2::jLog(print_r($rowArray,true));
			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
			}

			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );
//var_dump($rowArray); exit;
		$this->viewAssignEscaped ( 'results', $rowArray );

		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );


		$locations = Location::getAll("2");

		$this->viewAssignEscaped('locations', $locations);
		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		//organizers
		// restricted access?? only show trainings we have the ACL to view
		$org_allowed_ids = allowed_organizer_access($this);
		$orgWhere = '';
		if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
			$org_allowed_ids = implode(',', $org_allowed_ids);
			$orgWhere = " id in ($org_allowed_ids) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		if ($site_orgs) {
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}

		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, 1000, false, $orgWhere );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );

		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, 1000, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//levels
		$levelArray = OptionList::suggestionList ( 'training_level', 'level_title', false, 1000 );
		$this->viewAssignEscaped ( 'levels', $levelArray );
                
		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, 1000, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//refresher
		if($this->setting('multi_opt_refresher_course')){
			$refresherArray = OptionList::suggestionList ( 'training_refresher_option', 'refresher_phrase_option', false, false, false );
			$this->viewAssignEscaped ( 'refresherArray', $refresherArray );
		}
		//funding
		$fundingArray = OptionList::suggestionList ( 'training_funding_option', 'funding_phrase', false, false, false );
		$this->viewAssignEscaped ( 'funding', $fundingArray );
		//category
		$categoryArray = OptionList::suggestionList ( 'training_category_option', 'training_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'category', $categoryArray );
		//primary language
		$langArray = OptionList::suggestionList ( 'trainer_language_option', 'language_phrase', false, false, false );
		$this->viewAssignEscaped ( 'language', $langArray );
		//category
		$categoryArray = OptionList::suggestionList ( 'training_category_option', 'training_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'category', $categoryArray );
		//category+titles
		$categoryTitle = MultiAssignList::getOptions ( 'training_title_option', 'training_title_phrase', 'training_category_option_to_training_title_option', 'training_category_option' );
		$this->view->assign ( 'categoryTitle', $categoryTitle );
		//training methods
		$methodTitle = OptionList::suggestionList ( 'training_method_option', 'training_method_phrase', false, false, false );
		$this->view->assign ( 'methods', $methodTitle );

		$customArray = OptionList::suggestionList ( 'training_custom_1_option', 'custom1_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom1', $customArray );
		$customArray2 = OptionList::suggestionList ( 'training_custom_2_option', 'custom2_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom2', $customArray2 );
		$customArray3 = OptionList::suggestionList ( 'training', 'custom_3', false, false, false , "custom_3 != ''" );
		$this->viewAssignEscaped ( 'custom3', $customArray3 );
		$customArray4 = OptionList::suggestionList ( 'training', 'custom_4', false, false, false , "custom_4 != ''" );
		$this->viewAssignEscaped ( 'custom4', $customArray4 );

		$createdByArray = $db->fetchAll("select id,CONCAT(first_name, CONCAT(' ', last_name)) as name from user where is_blocked = 0");
		$this->viewAssignEscaped ( 'createdBy', $createdByArray );

		$qualsArray = OptionList::suggestionList ( 'person_primary_responsibility_option', 'responsibility_phrase', false, false, false );
		$this->viewAssignEscaped ( 'responsibility_primary', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_secondary_responsibility_option', 'responsibility_phrase', false, false, false );
		$this->viewAssignEscaped ( 'responsibility_secondary', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_attend_reason_option', 'attend_reason_phrase', false, false, false );
		$this->viewAssignEscaped ( 'attend_reason', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_education_level_option', 'education_level_phrase', false, false, false);
		$this->viewAssignEscaped ( 'highest_education_level', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NULL' );
		$this->viewAssignEscaped ( 'qualifications_primary', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NOT NULL' );
		$this->viewAssignEscaped ( 'qualifications_secondary', $qualsArray );


		//mechanisms (aka training partners, organizer_partners table)
		$mechanismArray = array();
		if($display_training_partner){
			$mechanismArray = OptionList::suggestionList ( 'organizer_partners', 'mechanism_id', false, false, false, "mechanism_id != ''");
		}
		$this->viewAssignEscaped ( 'mechanisms', $mechanismArray );

		// find category based on title
		$catId = NULL;
		if ($criteria ['training_category_id']) {
			foreach ( $categoryTitle as $r ) {
				if ($r ['id'] == $criteria ['training_category_id']) {
					$catId = $r ['training_category_option_id'];
					break;
				}
			}
		}
		$this->view->assign ( 'catId', $catId );

		//got curric
		$gotCuriccArray = OptionList::suggestionList ( 'training_got_curriculum_option', 'training_got_curriculum_phrase', false, false, false );
		$this->viewAssignEscaped ( 'gotcurric', $gotCuriccArray );

		//viewing location
		$viewingLocArray = OptionList::suggestionList ( 'person_to_training_viewing_loc_option', 'location_phrase', false, false, false );
		$this->viewAssignEscaped ( 'viewing_loc', $viewingLocArray );


	}


	public function trainingUnknownAction() {

		require_once ('models/table/TrainingLocation.php');

		$this->view->assign('mode', 'unknown');

		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = @$parts [0];
		$criteria ['start-month'] = @$parts [1];
		$criteria ['start-day'] = @$parts [2];

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			$sql = "SELECT MAX(training_start_date) as \"start\" FROM training ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['start'];
			$parts = explode ( '-', $end_default );
			$criteria ['end-year'] = @$parts [0];
			$criteria ['end-month'] = @$parts [1];
			$criteria ['end-day'] = @$parts [2];
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}

		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		//  $criteria['training_title_option_id'] = $this->getSanParam('training_title_option_id'); // legacy


		// find training name from new category/title format: categoryid_titleid
		$ct_ids = $criteria ['training_category_and_title_id'] = $this->getSanParam ( 'training_category_and_title_id' );
		$criteria ['training_title_option_id'] = substr ( $ct_ids, strpos ( $ct_ids, '_' ) + 1 );

		$criteria ['training_location_id'] = $this->getSanParam ( 'training_location_id' );
		$criteria ['training_organizer_id'] = $this->getSanParam ( 'training_organizer_id' );
		//$criteria['training_organizer_option_id'] = $this->getSanParam('training_organizer_option_id');
		$criteria ['training_pepfar_id'] = $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_method_id'] = $this->getSanParam ( 'training_method_id' );
		$criteria ['training_topic_id'] = $this->getSanParam ( 'training_topic_id' );
		$criteria ['training_level_id'] = $this->getSanParam ( 'training_level_id' );
		$criteria ['training_primary_language_option_id'] = $this->getSanParam ( 'training_primary_language_option_id' );
		$criteria ['training_secondary_language_option_id'] = $this->getSanParam ( 'training_secondary_language_option_id' );
		$criteria ['training_category_id'] = $this->getSanParam ( 'training_category_id' ); //reset(explode('_',$ct_ids));//
		$criteria ['training_got_curric_id'] = $this->getSanParam ( 'training_got_curric_id' );
		$criteria ['is_tot'] = $this->getSanParam ( 'is_tot' );
		$criteria ['funding_id'] = $this->getSanParam ( 'funding_id' );
		$criteria ['custom_1_id'] = $this->getSanParam ( 'custom_1_id' );
		$criteria ['custom_2_id'] = $this->getSanParam ( 'custom_2_id' );
		$criteria ['custom_3_id'] = $this->getSanParam ( 'custom_3_id' );
		$criteria ['custom_4_id'] = $this->getSanParam ( 'custom_4_id' );
		$criteria ['qualification_option_id'] = $this->getSanParam ( 'qualification_option_id' );
		$criteria ['age_range_option_id'] = $this->getSanParam ( 'age_range_option_id' );
		$criteria ['gender_option_id'] = $this->getSanParam ( 'gender_option_id' );

		$criteria ['funding_min'] = $this->getSanParam ( 'funding_min' );
		$criteria ['funding_max'] = $this->getSanParam ( 'funding_max' );

		$criteria ['go'] = $this->getSanParam ( 'go' );
		$criteria ['doCount'] = ($this->view->mode == 'count');
		$criteria ['showProvince'] = ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or ! empty ( $criteria ['province_id'] ))));
		$criteria ['showDistrict'] = ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or ! empty ( $criteria ['district_id'] ))));
		$criteria ['showRegionC'] = ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or ! empty ( $criteria ['region_c_id'] ))));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0')));
		$criteria ['showLocation'] = ($this->getSanParam ( 'showLocation' ) or ($criteria ['doCount'] and $criteria ['training_location_id']));
		$criteria ['showOrganizer'] = ($this->getSanParam ( 'showOrganizer' ) or ($criteria ['doCount'] and ($criteria ['training_organizer_id'])));
		$criteria ['showPepfar'] = ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0')));
		$criteria ['showMethod'] = ($this->getSanParam ( 'showMethod' ) or ($criteria ['doCount'] and ($criteria ['training_method_id'] or $criteria ['training_method_id'] === '0')));
		$criteria ['showTopic'] = ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showLevel'] = ($this->getSanParam ( 'showLevel' ) or ($criteria ['doCount'] and $criteria ['training_level_id']));
		$criteria ['showTot'] = ($this->getSanParam ( 'showTot' ) or ($criteria ['doCount'] and $criteria ['is_tot'] or $criteria ['is_tot'] === '0'));
		$criteria ['showRefresher'] = ($this->getSanParam ( 'showRefresher' ));
		$criteria ['showGotComment'] = ($this->getSanParam ( 'showGotComment' ));
		$criteria ['showPrimaryLanguage'] = ($this->getSanParam ( 'showPrimaryLanguage' ) or ($criteria ['doCount'] and $criteria ['training_primary_language_option_id'] or $criteria ['training_primary_language_option_id'] === '0'));
		$criteria ['showSecondaryLanguage'] = ($this->getSanParam ( 'showSecondaryLanguage' ) or ($criteria ['doCount'] and $criteria ['training_secondary_language_option_id'] or $criteria ['training_secondary_language_option_id'] === '0'));
		$criteria ['showFunding'] = ($this->getSanParam ( 'showFunding' ) or ($criteria ['doCount'] and $criteria ['funding_id'] or $criteria ['funding_id'] === '0' or $criteria ['funding_min'] or $criteria ['funding_max']));
		$criteria ['showCategory'] = ($this->getSanParam ( 'showCategory' ) or ($criteria ['doCount'] and $criteria ['training_category_id'] or $criteria ['training_category_id'] === '0'));
		$criteria ['showGotCurric'] = ($this->getSanParam ( 'showGotCurric' ) or ($criteria ['doCount'] and $criteria ['training_got_curric_id'] or $criteria ['training_got_curric_id'] === '0'));
		$criteria ['showCustom1'] = ($this->getSanParam ( 'showCustom1' ));
		$criteria ['showCustom2']              = ($this->getSanParam ( 'showCustom2' ));
		$criteria ['showCustom3']              = ($this->getSanParam ( 'showCustom3' ));
		$criteria ['showCustom4']              = ($this->getSanParam ( 'showCustom4' ));
		$criteria ['showEndDate'] = ($this->getSanParam('showEndDate'));
		$criteria ['showQualification'] = ($this->getSanParam('showQualification'));
		$criteria ['showAgeRange'] = ($this->getSanParam('showAgeRange'));
		$criteria ['showGender'] = ($this->getSanParam('showGender'));

		$criteria ['training_participants_type'] = $this->getSanParam ( 'training_participants_type' );

		// defaults
		if (! $criteria ['go']) {
			$criteria ['showTrainingTitle'] = 1;
		}

		if ($criteria ['go']) {

			$sql = 'SELECT ';

			if ($criteria ['doCount']) {
				$sql .= ' COUNT(pt.person_id) as "cnt" ';
			} else {

				$sql .= ' DISTINCT pt.id as "id", SUM(person_count_male + person_count_female + person_count_na) as pcnt, SUM(person_count_male) as male_pcnt, SUM(person_count_female) as female_pcnt, SUM(person_count_na) as na_pcnt, pt.training_start_date, pt.training_end_date, pt.has_known_participants  ';
			}

			if (!empty($criteria ['showTrainingTitle'])) {
				$sql .= ', training_title ';
			}

			if (!empty($criteria ['showRegionC'])) {
				$sql .= ', pt.region_c_name ';
			}
			if (!empty($criteria ['showDistrict'])) {
				$sql .= ', pt.district_name ';
			}
			if (!empty($criteria ['showProvince'])) {
				$sql .= ', pt.province_name ';
			}

			if (!empty($criteria ['showLocation'])) {
				$sql .= ', pt.training_location_name ';
			}

			if ( !empty($criteria ['showQualification'] )) {
				$sql .= ', pqo.qualification_phrase';
				$sql .= ', ppqo.qualification_phrase as parent_qualification_phrase';
			}

			if ( !empty($criteria ['showAgeRange'] )) {
				$sql .= ', aro.age_range_phrase';
			}

			if (!empty($criteria ['showOrganizer'])) {
				$sql .= ', torg.training_organizer_phrase ';
			}

			if (!empty($criteria ['showLevel'])) {
				$sql .= ', tlev.level_title as training_level_phrase ';
			}

			if (!empty($criteria ['showCategory'])) {
				$sql .= ', tcat.training_category_phrase ';
			}

			if (!empty($criteria ['showPepfar']) || !empty($criteria ['training_pepfar_id']) || $criteria ['training_pepfar_id'] === '0') {
				if ($criteria ['doCount']) {
					$sql .= ', tpep.pepfar_category_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tpep.pepfar_category_phrase) as "pepfar_category_phrase" ';
				}
			}

			if (!empty($criteria ['showMethod'])) {
				$sql .= ', tmeth.training_method_phrase ';
			}

			if (!empty($criteria ['showTopic'])) {
				if (!empty($criteria ['doCount'])) {
					$sql .= ', ttopic.training_topic_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT ttopic.training_topic_phrase ORDER BY training_topic_phrase) AS "training_topic_phrase" ';
				}
			}

			if (!empty($criteria ['showTot'])) {
				$sql .= ", IF(is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot";
			}

			if (!empty($criteria ['showRefresher'])) {
				$sql .= ", IF(is_refresher,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_refresher";
			}

			if (!empty($criteria ['showSecondaryLanguage'])) {
				$sql .= ', tlos.language_phrase as "secondary_language_phrase" ';
			}
			if (!empty($criteria ['showPrimaryLanguage'])) {
				$sql .= ', tlop.language_phrase as "primary_language_phrase" ';
			}
			if (!empty($criteria ['showGotComment'])) {
				$sql .= ", pt.got_comments";
			}
			if (!empty($criteria ['showGotCurric'])) {
				$sql .= ', tgotc.training_got_curriculum_phrase ';
			}

			if (!empty($criteria ['showFunding'])) {
				if (!empty($criteria ['doCount'])) {
					$sql .= ', tfund.funding_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tfund.funding_phrase ORDER BY funding_phrase) as "funding_phrase" ';
				}
			}
//			if (isset( $criteria['showCustom1'] )) {
//				$sql .= ', tqc.custom1_phrase ';
//			}
//			if ( isset($criteria['showCustom2'] )) {
//				$sql .= ', tqc2.custom2_phrase ';
//			}
//			if (isset( $criteria['showCustom3'] )) {
//				$sql .= ', pt.custom_3 ';
//			}
//			if ( isset($criteria['showCustom4']) ) {
//				$sql .= ', pt.custom_4 ';
//			}

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			//if we're doing a participant count, then LEFT JOIN with the participants
			//otherwise just select the core training info


			if (!empty($criteria ['doCount'])) {
				$sql .= ' FROM (SELECT training.*, pers.person_id as "person_id", tto.training_title_phrase AS training_title, training_location.training_location_name, '.implode(',',$field_name).
				'       FROM training ' .
				'         LEFT JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id)' .
				'         LEFT JOIN training_location ON training.training_location_id = training_location.id ' .
				'         LEFT JOIN ('.$location_sub_query.') as l ON training_location.location_id = l.id ' .
				'         LEFT JOIN (SELECT person_id,training_id FROM person JOIN person_to_training ON person_to_training.person_id = person.id) as pers ON training.id = pers.training_id WHERE training.is_deleted=0  AND training.has_known_participants = 0) as pt ';
			} else {
				$sql .= ' FROM (SELECT training.*, tto.training_title_phrase AS training_title,training_location.training_location_name, '.implode(',',$field_name).
				'       FROM training  ' .
				'         LEFT JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id) ' .
				'         LEFT JOIN training_location ON training.training_location_id = training_location.id ' .
				'         LEFT JOIN ('.$location_sub_query.') as l ON training_location.location_id = l.id ' .
				'  WHERE training.is_deleted=0 AND training.has_known_participants = 0) as pt ';

				$sql .= ' LEFT JOIN training_to_person_qualification_option as ttpqo ON ttpqo.training_id = pt.id ';
			}

			if (!empty($criteria ['showQualification'] )) {
				$sql .= ' LEFT JOIN person_qualification_option as pqo ON ttpqo.person_qualification_option_id = pqo.id';
				$sql .= ' LEFT JOIN person_qualification_option as ppqo ON pqo.parent_id = ppqo.id';
			}

			if (!empty($criteria ['showAgeRange'] ) ) {
				$sql .= ' LEFT JOIN age_range_option as aro ON ttpqo.age_range_option_id = aro.id';
			}

			if ($criteria ['showOrganizer'] or $criteria ['training_organizer_id']) {
				$sql .= ' LEFT JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';
			}
			if ($criteria ['showLevel'] || $criteria ['training_level_id']) {
				$sql .= ' LEFT JOIN training_level_option as tlev ON tlev.id = pt.training_level_option_id ';
			}

			if ($criteria ['showMethod'] || $criteria ['training_method_id']) {
				$sql .= ' LEFT JOIN training_method_option as tmeth ON tmeth.id = pt.training_method_option_id ';
			}

			if ($criteria ['showPepfar'] || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0') {
				$sql .= ' LEFT JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if ($criteria ['showTopic'] || $criteria ['training_topic_id']) {
				$sql .= ' LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
			}

			if ($criteria ['showPrimaryLanguage'] || $criteria ['training_primary_language_option_id']) {
				$sql .= ' LEFT JOIN trainer_language_option as tlop ON tlop.id = pt.training_primary_language_option_id ';
			}

			if ($criteria ['showSecondaryLanguage'] || $criteria ['training_secondary_language_option_id']) {
				$sql .= ' LEFT JOIN trainer_language_option as tlos ON tlos.id = pt.training_secondary_language_option_id ';
			}

			if ($criteria ['showFunding'] || (intval ( $criteria ['funding_min'] ) or intval ( $criteria ['funding_max'] ))) {
				$sql .= ' LEFT JOIN (SELECT training_id, ttfo.training_funding_option_id, funding_phrase, ttfo.funding_amount FROM training_to_training_funding_option as ttfo JOIN training_funding_option as tfo ON ttfo.training_funding_option_id = tfo.id) as tfund ON tfund.training_id = pt.id ';
			}

			if ($criteria ['showGotCurric'] || $criteria ['training_got_curric_id']) {
				$sql .= ' LEFT JOIN training_got_curriculum_option as tgotc ON tgotc.id = pt.training_got_curriculum_option_id';
			}

			if ($criteria ['showCategory'] or ! empty ( $criteria ['training_category_id'] )) {
				$sql .= '
				LEFT JOIN training_category_option_to_training_title_option tcotto ON (tcotto.training_title_option_id = pt.training_title_option_id)
				LEFT JOIN training_category_option tcat ON (tcotto.training_category_option_id = tcat.id)
				';
			}
			if ( $criteria['showCustom1'] ) {
				$sql .= ' LEFT JOIN training_custom_1_option as tqc ON pt.training_custom_1_option_id = tqc.id  ';
			}
			if ( $criteria['showCustom2'] ) {
				$sql .= ' LEFT JOIN training_custom_2_option as tqc2 ON pt.training_custom_2_option_id = tqc2.id  ';
			}

			$where = array ();
			$where [] = ' pt.is_deleted=0 ';

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where []= " pt.training_organizer_option_id in ($org_allowed_ids) ";
			}


			if ( $criteria['qualification_option_id']) {
				$where []= ' ttpqo.person_qualification_option_id = '.$criteria['qualification_option_id'];
			}
			if ( $criteria['age_range_option_id']) {
				$where []= ' ttpqo.age_range_option_id = '.$criteria['age_range_option_id'];
			}

			if ($criteria ['training_participants_type']) {
				if ($criteria ['training_participants_type'] == 'has_known_participants') {
					$where [] = ' pt.has_known_participants = 1 ';
				}
				if ($criteria ['training_participants_type'] == 'has_unknown_participants') {
					$where [] = ' pt.has_known_participants = 0 ';

				}
			}

			if ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$where [] = 'pt.training_title_option_id = ' . $criteria ['training_title_option_id'];
			}

			if ($criteria ['training_location_id']) {
				$where [] = ' pt.training_location_id = \'' . $criteria ['training_location_id'] . '\'';
			}

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0') {
				$where [] = ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				$where [] = ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}

			if ($criteria ['training_level_id']) {
				$where [] = ' pt.training_level_option_id = \'' . $criteria ['training_level_id'] . '\'';
			}

			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0') {
				$where [] = ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if ($criteria ['training_method_id'] or $criteria ['training_method_id'] === '0') {
				$where [] = ' tmeth.id = \'' . $criteria ['training_method_id'] . '\'';
			}

			if ($criteria ['training_primary_language_option_id'] or $criteria ['training_primary_language_option_id'] === '0') {
				$where [] = ' pt.training_primary_language_option_id = \'' . $criteria ['training_primary_language_option_id'] . '\'';
			}

			if ($criteria ['training_secondary_language_option_id'] or $criteria ['training_secondary_language_option_id'] === '0') {
				$where [] = ' pt.training_secondary_language_option_id = \'' . $criteria ['training_secondary_language_option_id'] . '\'';
			}

			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where [] = ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			if (intval ( $criteria ['funding_min'] ) or intval ( $criteria ['funding_max'] )) {
				if (intval ( $criteria ['funding_min'] ))
				$where [] = 'tfund.funding_amount >= \'' . $criteria ['funding_min'] . '\' ';
				if (intval ( $criteria ['funding_max'] ))
				$where [] = 'tfund.funding_amount <= \'' . $criteria ['funding_max'] . '\' ';
			}

			if (intval ( $criteria ['is_tot'] )) {
				$where [] = ' is_tot = ' . $criteria ['is_tot'];
			}

			if ($criteria ['funding_id'] or $criteria ['funding_id'] === '0') {
				$where [] = ' tfund.training_funding_option_id = \'' . $criteria ['funding_id'] . '\'';
			}

			if ($criteria ['training_category_id'] or $criteria ['training_category_id'] === '0') {
				$where [] = ' tcat.id = \'' . $criteria ['training_category_id'] . '\'';
			}

			if ($criteria ['training_got_curric_id'] or $criteria ['training_got_curric_id'] === '0') {
				$where [] = ' tgotc.id = \'' . $criteria ['training_got_curric_id'] . '\'';
			}

			if ($criteria ['custom_1_id'] or $criteria ['custom_1_id'] === '0') {
				$where [] = ' pt.training_custom_1_option_id = \'' . $criteria ['custom_1_id'] . '\'';
			}
			if ($criteria ['custom_2_id'] or $criteria ['custom_2_id'] === '0') {
				$where [] = ' pt.training_custom_2_option_id = \'' . $criteria ['custom_2_id'] . '\'';
			}
			if ($criteria ['custom_3_id'] or $criteria ['custom_3_id'] === '0') {
				$where [] = ' pt.custom_3 = \'' . $criteria ['custom_3_id'] . '\'';
			}
			if ($criteria ['custom_4_id'] or $criteria ['custom_4_id'] === '0') {
				$where [] = ' pt.custom_4 = \'' . $criteria ['custom_4_id'] . '\'';
			}
			if ($where)
			$sql .= ' WHERE ' . implode ( ' AND ', $where );

			if ($criteria ['doCount']) {

				$groupBy = array();

				if ($criteria ['showTrainingTitle'])     $groupBy [] = 'pt.training_title_option_id';
				if ($criteria ['showProvince'])          $groupBy [] = 'pt.province_id';
				if ($criteria ['showDistrict'])          $groupBy [] = 'pt.district_id';
				if ($criteria ['showRegionC'])           $groupBy [] = 'pt.region_c_id';
				if ($criteria ['showLocation'])          $groupBy [] = 'pt.training_location_id';
				if ($criteria ['showOrganizer'])         $groupBy [] = 'pt.training_organizer_option_id';
				if ($criteria ['showCustom1'])           $groupBy [] = 'pt.training_custom_1_option_id';
				if ($criteria ['showCustom2'])           $groupBy [] = 'pt.training_custom_2_option_id';
				if ($criteria ['showCustom3'])           $groupBy [] = 'pt.custom_3';
				if ($criteria ['showCustom4'])           $groupBy [] = 'pt.custom_4';
				if ($criteria ['showTopic'])             $groupBy [] = 'ttopic.training_topic_option_id';
				if ($criteria ['showLevel'])             $groupBy [] = 'pt.training_level_option_id';
				if ($criteria ['showPepfar'])            $groupBy [] = 'tpep.training_pepfar_categories_option_id';
				if ($criteria ['showMethod'])            $groupBy [] = 'tmeth.id';
				if ($criteria ['showTot'])               $groupBy [] = 'pt.is_tot';
				if ($criteria ['showRefresher'])         $groupBy [] = 'pt.is_refresher';
				if ($criteria ['showGotCurric'])         $groupBy [] = 'pt.training_got_curriculum_option_id';
				if ($criteria ['showPrimaryLanguage'])   $groupBy [] = 'pt.training_primary_language_option_id';
				if ($criteria ['showSecondaryLanguage']) $groupBy [] = 'pt.training_secondary_language_option_id';
				if ($criteria ['showFunding'])           $groupBy [] = 'tfund.training_funding_option_id';

				if ($groupBy) {
					$sql .= ' GROUP BY ' . implode(',',$groupBy);
				}

			} else {
				$groupBy = array();
				$groupBy []= 'pt.id';

				if ($criteria ['showQualification']) $groupBy []= ' ttpqo.person_qualification_option_id';
				if ($criteria ['showAgeRange']) $groupBy []= ' ttpqo.age_range_option_id';

				$sql .= ' GROUP BY '.implode(',',$groupBy);

			}

			if ($this->view->mode == 'search') {
				$sql .= ' ORDER BY training_start_date DESC';
			}

			$rowArray = $db->fetchAll ( $sql );

			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
			}

			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );

		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );


		$locations = Location::getAll("2");

		$this->viewAssignEscaped('locations', $locations);
		//course
		//$courseArray = Course::suggestionList(false,10000);
		//$this->viewAssignEscaped('courses',$courseArray);
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		//organizers
		// restricted access?? only show trainings we have the ACL to view
		require_once('views/helpers/TrainingViewHelper.php');
		$org_allowed_ids = allowed_organizer_access($this);
		$orgWhere = '';
		if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
			$org_allowed_ids = implode(',', $org_allowed_ids);
			$orgWhere .= " id in ($org_allowed_ids) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		if ($site_orgs) {
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}

		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, false, false, $orgWhere );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//levels
		$levelArray = OptionList::suggestionList ( 'training_level_option', 'training_level_phrase', false, false );
		$this->viewAssignEscaped ( 'levels', $levelArray );
		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//funding
		$fundingArray = OptionList::suggestionList ( 'training_funding_option', 'funding_phrase', false, false, false );
		$this->viewAssignEscaped ( 'funding', $fundingArray );
		//category
		$categoryArray = OptionList::suggestionList ( 'training_category_option', 'training_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'category', $categoryArray );
		//primary language
		$langArray = OptionList::suggestionList ( 'trainer_language_option', 'language_phrase', false, false, false );
		$this->viewAssignEscaped ( 'language', $langArray );
		//category
		$categoryArray = OptionList::suggestionList ( 'training_category_option', 'training_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'category', $categoryArray );
		//category+titles
		$categoryTitle = MultiAssignList::getOptions ( 'training_title_option', 'training_title_phrase', 'training_category_option_to_training_title_option', 'training_category_option' );
		$this->view->assign ( 'categoryTitle', $categoryTitle );
		//training methods
		$methodTitle = OptionList::suggestionList ( 'training_method_option', 'training_method_phrase', false, false, false );
		$this->view->assign ( 'methods', $methodTitle );

		// custom fields
		$customArray = OptionList::suggestionList ( 'training_custom_1_option', 'custom1_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom1', $customArray );
		$customArray2 = OptionList::suggestionList ( 'training_custom_2_option', 'custom2_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom2', $customArray2 );
		$customArray3 = OptionList::suggestionList ( 'training', 'custom_3', false, false, false, "custom_3 != ''");
		$this->viewAssignEscaped ( 'custom3', $customArray3 );
		$customArray4 = OptionList::suggestionList ( 'training', 'custom_4', false, false, false, "custom_4 != ''" );
		$this->viewAssignEscaped ( 'custom4', $customArray4 );

		$customArray = OptionList::suggestionList ( 'age_range_option', 'age_range_phrase', false, false, false );
		$this->viewAssignEscaped ( 'age_range', $customArray );

		//$customArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NULL');
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );

		$this->viewAssignEscaped ( 'gender', array(
		array('id'=>1,'name'=>t('Unknown')),
		array('id'=>2,'name'=>t('Female')),
		array('id'=>3,'name'=>t('Male')) ));

		// find category based on title
		$catId = NULL;
		if ($criteria ['training_category_id']) {
			foreach ( $categoryTitle as $r ) {
				if ($r ['id'] == $criteria ['training_category_id']) {
					$catId = $r ['training_category_option_id'];
					break;
				}
			}
		}
		$this->view->assign ( 'catId', $catId );

		//got curric
		$gotCuriccArray = OptionList::suggestionList ( 'training_got_curriculum_option', 'training_got_curriculum_phrase', false, false, false );
		$this->viewAssignEscaped ( 'gotcurric', $gotCuriccArray );

	}

	public function budgetCodeAction()
	{
		require_once ('views/helpers/FormHelper.php');
		require_once ('models/table/TrainingLocation.php');
		require_once ('views/helpers/DropDown.php');
		require_once ('views/helpers/Location.php');
		$criteria = $this->getAllParams();

		if ($criteria ['go']) {

			list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			list($field_name,$location_sub_query) = Location::subquery($this->setting('num_location_tiers'), $location_tier, $location_id, true);

			$sql = 'SELECT  training_id, training_title_phrase, training_start_date, budget_code_phrase, training_location_name, total_participants, num_participants,
							l.'.implode(', l.',$field_name).',
							num_participants / total_participants * 100 as percentage
						FROM person_to_training
						LEFT JOIN training ON training_id = training.id
						LEFT JOIN training_title_option tto ON training.training_title_option_id = tto.id
						LEFT JOIN training_location ON training_location.id = training.training_location_id
						LEFT JOIN ('.$location_sub_query.') AS l ON training_location.location_id = l.id
						LEFT JOIN person_to_training_budget_option budget on person_to_training.budget_code_option_id = budget.id
						LEFT JOIN (SELECT COUNT(ptt.id) as total_participants, ptt.training_id as tid FROM person_to_training ptt GROUP BY ptt.training_id) stat1 ON stat1.tid = person_to_training.training_id
						LEFT JOIN (SELECT COUNT(ptt.id) as num_participants, ptt.training_id as tid, budget_code_option_id as budget_code_id FROM person_to_training ptt GROUP BY budget_code_option_id,ptt.training_id) stat2 on stat2.tid = person_to_training.training_id and stat2.budget_code_id = person_to_training.budget_code_option_id
					';

			$where = array( 'training.is_deleted = 0' );

			if ($locWhere = $this->getLocationCriteriaWhereClause($criteria)) {
				$where [] = $locWhere;
			}
			if ($criteria ['training_location_id']) {
				$where [] = 'training.training_location_id = ' . $criteria['training_location_id'];
			}
			if ($criteria ['budget_code_option_id']) {
				$where [] = 'budget.id = ' . $criteria['budget_code_option_id'];
			}
			if ($criteria ['training_title_option_id']) {
				$where [] = 'tto.id = ' . $criteria['training_title_option_id'];
			}
			if ($criteria ['start_date']) {
				$where [] = 'training.training_start_date >= "' . $this->_date_to_sql( $criteria['start_date'] ). '"';
			}
			if ($criteria ['end_date']) {
				$where [] = 'training.training_start_date <= "' . $this->_date_to_sql( $criteria['end_date']) . ' 23:59:59"';
			}

			if ($where)
				$sql .= ' WHERE ' . implode(' AND ', $where);

			$sql .= ' GROUP BY budget_code_option_id, training_id ';
			$sql .= ' ORDER BY training_id, budget_code_option_id ';
			$db = $this->dbfunc();
			$rowArray = $db->fetchAll($sql);

			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->view->assign ( 'count' , count($rowArray) );

			// output csv if necessary
			if ($this->_getParam ( 'outputType' ))
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );


		} // fi run report

		// assign form drop downs
		$this->viewAssignEscaped ( 'criteria',   $criteria );

		$this->viewAssignEscaped ( 'locations',  Location::getAll("2"));

		$this->viewAssignEscaped ( 'tlocations', TrainingLocation::selectAllLocations ($this->setting('num_location_tiers')));
		$this->view->assign ( 'partners',    DropDown::generateHtml ( 'person_to_training_budget_option', 'budget_code_phrase',    $criteria['budget_code_option_id'], false, $this->view->viewonly, false ) ); //table, col, selected_value
		$this->view->assign ( 'titles',      DropDown::generateHtml ( 'training_title_option',            'training_title_phrase', $criteria['training_title_option_id'], false, $this->view->viewonly, false ) ); //table, col, selected_value
	}

	public function trainersByNameAction() {
		$this->view->assign('is_trainers', true);
		return $this->peopleReport();

	}

	
	public function peopleReport() {
		require_once ('views/helpers/DropDown.php');
		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = '0000-00-00';
		if ($rowArray and $rowArray [0] ['start'])
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		$criteria ['showAge'] = $this->getSanParam ( 'showAge' );
		$criteria ['age_min'] = $this->getSanParam ( 'age_min' );
		$criteria ['age_max'] = $this->getSanParam ( 'age_max' );

		
		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		$criteria ['end-year'] = date ( 'Y' );
		$criteria ['end-month'] = date ( 'm' );
		$criteria ['end-day'] = date ( 'd' );
		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$criteria ['training_gender']              = $this->getSanParam ( 'training_gender' );
		$criteria ['training_active']              = $this->getSanParam ( 'training_active' );
		$criteria ['concatNames']                  = $this->getSanParam ( 'concatNames' );
		$criteria ['training_title_option_id']     = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['training_title_id']            = $this->getSanParam ( 'training_title_id' );
		$criteria ['training_pepfar_id']           = $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_topic_id']            = $this->getSanParam ( 'training_topic_id' );
		$criteria ['qualification_id']             = $this->getSanParam ( 'qualification_id' );
		$criteria ['qualification_secondary_id']   = $this->getSanParam ( 'qualification_secondary_id' );
		$criteria ['facilityInput']                = $this->getSanParam ( 'facilityInput' );
		$criteria ['is_tot']                       = $this->getSanParam ( 'is_tot' );
		$criteria ['training_organizer_id']        = $this->getSanParam ( 'training_organizer_id' );
		$criteria ['training_organizer_option_id'] = $this->getSanParam ( 'training_organizer_option_id' );
		$criteria ['funding_id']                   = $this->getSanParam ( 'funding_id' );
		$criteria ['custom_1_id']                  = $this->getSanParam ( 'custom_1_id' );
		$criteria ['custom_2_id']                  = $this->getSanParam ( 'custom_2_id' );
		$criteria ['custom_3_id']                  = $this->getSanParam ( 'custom_3_id' );
		$criteria ['custom_4_id']                  = $this->getSanParam ( 'custom_4_id' );
		$criteria ['custom_5_id']                  = $this->getSanParam ( 'custom_5_id' );
		$criteria ['distinctCount']                = $this->getSanParam ( 'distinctCount' );
		$criteria ['person_to_training_viewing_loc_option_id'] = $this->getSanParam('person_to_training_viewing_loc_option_id');
		if ($this->view->isScoreReport) {
			$criteria ['score_min'] = (is_numeric ( trim ( $this->getSanParam ( 'score_min' ) ) )) ? trim ( $this->getSanParam ( 'score_min' ) ) : '';
			$criteria ['score_percent_min'] = (is_numeric ( trim ( $this->getSanParam ( 'score_percent_min' ) ) )) ? trim ( $this->getSanParam ( 'score_percent_min' ) ) : '';
		}

		$criteria ['doCount']           = ($this->view->mode == 'count');
		$criteria ['showProvince']      = ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or ! empty ( $criteria ['province_id'] ))));
		$criteria ['showDistrict']      = ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or ! empty ( $criteria ['district_id'] ))));
		$criteria ['showRegionC']       = ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or ! empty ( $criteria ['region_c_id'] ))));
		$criteria ['showRegionD']       = ($this->getSanParam ( 'showRegionD' ) or ($criteria ['doCount'] and ($criteria ['region_d_id'] or ! empty ( $criteria ['region_d_id'] ))));
		$criteria ['showRegionE']       = ($this->getSanParam ( 'showRegionE' ) or ($criteria ['doCount'] and ($criteria ['region_e_id'] or ! empty ( $criteria ['region_e_id'] ))));
		$criteria ['showRegionF']       = ($this->getSanParam ( 'showRegionF' ) or ($criteria ['doCount'] and ($criteria ['region_f_id'] or ! empty ( $criteria ['region_f_id'] ))));
		$criteria ['showRegionG']       = ($this->getSanParam ( 'showRegionG' ) or ($criteria ['doCount'] and ($criteria ['region_g_id'] or ! empty ( $criteria ['region_g_id'] ))));
		$criteria ['showRegionH']       = ($this->getSanParam ( 'showRegionH' ) or ($criteria ['doCount'] and ($criteria ['region_h_id'] or ! empty ( $criteria ['region_h_id'] ))));
		$criteria ['showRegionI']       = ($this->getSanParam ( 'showRegionI' ) or ($criteria ['doCount'] and ($criteria ['region_i_id'] or ! empty ( $criteria ['region_i_id'] ))));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0' or $criteria ['training_title_id'])));
		$criteria ['showPepfar']        = ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_pepfar_id'] === '0')));
		$criteria ['showQualification'] = false; // ($this->getSanParam('showQualification') OR ($criteria['doCount']  and ($criteria['qualification_id'] or $criteria['qualification_id'] === '0') ));
		$criteria ['showTopic']         = ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showFacility']      = ($this->getSanParam ( 'showFacility' ) or ($criteria ['doCount'] and $criteria ['facilityInput']));
		$criteria ['showGender']        = ($this->getSanParam ( 'showGender' ) or ($criteria ['doCount'] and $criteria ['training_gender']));
		$criteria ['showActive']        = ($this->getSanParam ( 'showActive' ) or ($criteria ['doCount'] and $criteria ['training_active']));
		$criteria ['showSuffix']        = ($this->getSanParam ( 'showSuffix' ));
		$criteria ['showEmail']         = ($this->getSanParam ( 'showEmail' ));
		$criteria ['showPhone']         = ($this->getSanParam ( 'showPhone' ));
		$criteria ['showTot']           = ($this->getSanParam ( 'showTot' ) or ($criteria ['doCount'] and $criteria ['is_tot'] !== '' or $criteria ['is_tot'] === '0'));
		$criteria ['showOrganizer']     = ($this->getSanParam ( 'showOrganizer' ) or ($criteria ['doCount'] and ($criteria ['training_organizer_option_id'])));
		$criteria ['showFunding']       = ($this->getSanParam ( 'showFunding' ) or ($criteria ['doCount'] and $criteria ['funding_id'] or $criteria ['funding_id'] === '0'));
		$criteria ['showQualPrim']      = ($this->getSanParam ( 'showQualPrim' ) or ($criteria ['doCount'] and ($criteria ['qualification_id'] or $criteria ['qualification_id'] === '0')));
		$criteria ['showQualSecond']    = ($this->getSanParam ( 'showQualSecond' ) or ($criteria ['doCount'] and ($criteria ['qualification_secondary_id'] or $criteria ['qualification_secondary_id'] === '0')));
		$criteria ['showCustom1']       = ($this->getSanParam ( 'showCustom1' ));
		$criteria ['showCustom2']       = ($this->getSanParam ( 'showCustom2' ));
		$criteria ['showCustom3']       = ($this->getSanParam ( 'showCustom3' ));
		$criteria ['showCustom4']       = ($this->getSanParam ( 'showCustom4' ));
		$criteria ['showCustom5']       = ($this->getSanParam ( 'showCustom5' ));
		$criteria ['showRespPrim']      = ($this->getSanParam ( 'showRespPrim' ));
		$criteria ['showRespSecond']    = ($this->getSanParam ( 'showRespSecond' ));
		$criteria ['showHighestEd']     = ($this->getSanParam ( 'showHighestEd' ));
		$criteria ['showReason']        = ($this->getSanParam ( 'showReason' ));
		$criteria ['showViewingLoc']    = $this->getSanParam ( 'showViewingLoc' );

		$criteria ['primary_responsibility_option_id'] = $this->getSanParam ( 'primary_responsibility_option_id' );
		$criteria ['secondary_responsibility_option_id'] = $this->getSanParam ( 'secondary_responsibility_option_id' );

		$criteria ['highest_edu_level_option_id'] = $this->getSanParam ( 'highest_edu_level_option_id' );
		$criteria ['attend_reason_option_id'] = $this->getSanParam ( 'attend_reason_option_id' );


		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {

			$sql = 'SELECT ';

			if ($criteria ['doCount']) {
				$distinct = ($criteria ['distinctCount']) ? 'DISTINCT ' : '';
				$sql .= ' COUNT(' . $distinct . 'person_id) as "cnt" ';
			} else {
				if ($criteria ['concatNames'])
				$sql .= ' DISTINCT person_id as "id", CONCAT(first_name, ' . "' '" . ',last_name, ' . "' '" . ', IFNULL(suffix_phrase, ' . "' '" . ')) as "name", IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, pt.training_start_date  ';
				else
				$sql .= ' DISTINCT person_id as "id", IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, last_name, first_name, middle_name, pt.training_start_date  ';
			}
			if ($criteria ['showPhone']) {
				$sql .= ", CASE WHEN (pt.phone_work IS NULL OR pt.phone_work = '') THEN NULL ELSE pt.phone_work END as \"phone_work\", CASE WHEN (pt.phone_home IS NULL OR pt.phone_home = '') THEN NULL ELSE pt.phone_home END as \"phone_home\", CASE WHEN (pt.phone_mobile IS NULL OR pt.phone_mobile = '') THEN NULL ELSE pt.phone_mobile END as \"phone_mobile\" ";
			}
			if ($criteria ['showEmail']) {
				$sql .= ', pt.email ';
			}
			if ($criteria ['showAge']) {
				$sql .= ', pt.age ';
			}
			if ($criteria ['showGender']) {
				$sql .= ', pt.gender ';
			}
			if ($criteria ['showActive']) {
				$sql .= ', pt.active ';
			}
			if ($criteria ['showTrainingTitle']) {
				$sql .= ', pt.training_title ';
			}
			if ($criteria ['showDistrict']) {
				$sql .= ', pt.district_name ';
			}
			if ($criteria ['showProvince']) {
				$sql .= ', pt.province_name ';
			}

			if ($criteria ['showRegionC']) {
				$sql .= ', pt.region_c_name ';
			}
			if ($criteria ['showRegionD']) {
				$sql .= ', pt.region_d_name ';
			}
			if ($criteria ['showRegionE']) {
				$sql .= ', pt.region_e_name ';
			}
			if ($criteria ['showRegionF']) {
				$sql .= ', pt.region_f_name ';
			}
			if ($criteria ['showRegionG']) {
				$sql .= ', pt.region_g_name ';
			}
			if ($criteria ['showRegionH']) {
				$sql .= ', pt.region_h_name ';
			}
			if ($criteria ['showRegionI']) {
				$sql .= ', pt.region_i_name ';
			}
			if ($criteria ['showPepfar'] || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0') {
				$sql .= ', tpep.pepfar_category_phrase ';
			}

			if ($criteria ['showTopic']) {
				$sql .= ', ttopic.training_topic_phrase ';
			}

			if ($criteria ['showFacility']) {
				$sql .= ', pt.facility_name ';
			}

			if ($criteria ['showTot']) {
				$sql .= ", IF(pt.is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot";
			}

			if ($criteria ['showOrganizer']) {
				$sql .= ', torg.training_organizer_phrase ';
			}

			if ($criteria ['showFunding']) {
				if ($criteria ['doCount']) {
					$sql .= ', tfund.funding_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tfund.funding_phrase ORDER BY funding_phrase) as "funding_phrase" ';
				}
			}

			if ($criteria ['showQualification']) {
				$sql .= ', pq.qualification_phrase ';
			}

			if ($criteria ['showQualPrim']) {
				$sql .= ', pq.qualification_phrase ';
			}
			if ($criteria ['showQualSecond']) {
				$sql .= ', pqs.qualification_phrase AS qualification_secondary_phrase';
			}

			if ($criteria ['showRespPrim']) {
				$sql .= ', pr.responsibility_phrase as primaryResponsibility';
			}
			if ($criteria ['showRespSecond']) {
				$sql .= ', sr.responsibility_phrase  as secondaryResponsibility';
			}


			if ($criteria ['showHighestEd']) {
				$sql .= ', ed.education_level_phrase ';
			}
			if ($criteria ['showReason']) {
				$sql .= ', CASE WHEN attend_reason_other IS NOT NULL THEN attend_reason_other ELSE attend_reason_phrase END AS attend_reason_phrase ';
			}



			if ( $criteria['showCustom1'] ) {
				$sql .= ', pqc.custom1_phrase ';
			}
			if ( $criteria['showCustom2'] ) {
				$sql .= ', pqc2.custom2_phrase ';
			}
			if ( $criteria['showCustom3'] ) {
				$sql .= ', person_custom_3 ';
			}
			if ( $criteria['showCustom4'] ) {
				$sql .= ', person_custom_4 ';
			}
			if ( $criteria['showCustom5'] ) {
				$sql .= ', person_custom_5 ';
			}

			if ( $criteria['showViewingLoc']) {
				$sql .= ', person_to_training_viewing_loc_option.location_phrase '; // wont work on is_trainers report
			}

			if ($this->view->isScoreReport) {
				$sql .= ', spre.score_value AS score_pre, spost.score_value AS score_post, ' . 'ROUND((spost.score_value - spre.score_value) / spre.score_value * 100) AS score_percent_change';
				$sql .= ', scoreother.labels, scoreother.scores ';
			}

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);


			$intersection_table = 'person_to_training';
			$intersection_person_id = 'person_id';

			if ( $this->view->is_trainers ) {
				$intersection_table = 'training_to_trainer';
				$intersection_person_id = 'trainer_id';
			}

			$sql .= ' FROM (';
			$sql .= 'SELECT training.*, person.facility_id as "facility_id", person.id as "person_id", person.last_name, IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, ';
			$sql .= 'person.first_name, person.middle_name, person.person_custom_1_option_id, person.person_custom_2_option_id, person.custom_3 as person_custom_3, person.custom_4 as person_custom_4, person.custom_5 as person_custom_5, ';
			$sql .= 'CASE WHEN birthdate  IS NULL OR birthdate = \'0000-00-00\' THEN NULL ELSE ((date_format(now(),\'%Y\') - date_format(birthdate,\'%Y\')) - (date_format(now(),\'00-%m-%d\') < date_format(birthdate,\'00-%m-%d\')) ) END as "age", ';
			$sql .= 'person.phone_work, person.phone_home, person.phone_mobile, person.email, ';
			$sql .= 'CASE WHEN person.active = \'deceased\' THEN \'inactive\' ELSE person.active END as "active", ';
			$sql .= 'CASE WHEN person.gender IS NULL THEN \'na\' WHEN person.gender = \'\' THEN \'na\' ELSE person.gender END as "gender", ';
			$sql .= 'primary_qualification_option_id, primary_responsibility_option_id, secondary_responsibility_option_id, highest_edu_level_option_id, attend_reason_option_id, attend_reason_other, tto.training_title_phrase AS training_title,facility.facility_name, ';
			$sql .= $intersection_table.'.id AS ptt_id, l.'.implode(', l.',$field_name);
			$sql .= ' FROM training LEFT JOIN training_title_option tto ON training.training_title_option_id = tto.id ';
			$sql .= '    INNER JOIN '.$intersection_table.' ON training.id = '.$intersection_table.'.training_id ';
			$sql .= '    INNER JOIN person ON person.id = '.$intersection_table.'.'.$intersection_person_id;
			$sql .= '    INNER JOIN facility ON person.facility_id = facility.id ';
			$sql .= '    LEFT JOIN ('.$location_sub_query.') AS l ON facility.location_id = l.id ';
			$sql .= '    LEFT  JOIN person_suffix_option suffix ON person.suffix_option_id = suffix.id ';
			$sql .= ' ) as pt ';

			if ($criteria ['showPepfar'] || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0') {
				$sql .= '	JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if ($criteria ['showTopic'] || $criteria ['training_topic_id']) {
				$sql .= ' LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
			}
			/*
			if ( $criteria['showQualification'] ) {
			$sql .= '	JOIN person_qualification_option as pq ON pq.id = pt.primary_qualification_option_id ';
			}
			*/

			if ($criteria ['showOrganizer']) {
				$sql .= '	JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';
			}

			if ($criteria ['showFunding']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttfo.training_funding_option_id, funding_phrase FROM training_to_training_funding_option as ttfo JOIN training_funding_option as tfo ON ttfo.training_funding_option_id = tfo.id) as tfund ON tfund.training_id = pt.id ';
			}

			if ( $criteria['showCustom1'] ) {
				$sql .= ' LEFT JOIN person_custom_1_option as pqc ON pt.person_custom_1_option_id = pqc.id  ';
			}
			if ( $criteria['showCustom2'] ) {
				$sql .= ' LEFT JOIN person_custom_2_option as pqc2 ON pt.person_custom_2_option_id = pqc2.id  ';
			}

			if ($criteria ['showQualPrim'] || $criteria ['showQualSecond'] || $criteria ['qualification_id']  || $criteria ['qualification_secondary_id']) {
				// primary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pq ON (
				(pt.primary_qualification_option_id = pq.id AND pq.parent_id IS NULL)
				OR
				pq.id = (SELECT parent_id FROM person_qualification_option WHERE id = pt.primary_qualification_option_id LIMIT 1)
				)';

				// secondary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pqs ON (
					pt.primary_qualification_option_id = pqs.id AND pqs.parent_id IS NOT NULL)';
			}

			if ( $criteria['showRespPrim'] ) {
				$sql .= ' LEFT JOIN person_primary_responsibility_option as pr ON pt.primary_responsibility_option_id = pr.id  ';
			}
			if ( $criteria['showRespSecond'] ) {
				$sql .= ' LEFT JOIN person_secondary_responsibility_option as sr ON pt.secondary_responsibility_option_id = sr.id  ';
			}

			if ( $criteria['showHighestEd'] ) {
				$sql .= ' LEFT JOIN person_education_level_option as ed ON pt.highest_edu_level_option_id = ed.id  ';
			}

			if ( $criteria['showReason'] ) {
				$sql .= ' LEFT JOIN person_attend_reason_option as ra ON pt.attend_reason_option_id = ra.id  ';
			}

			if ( $criteria['showViewingLoc'] ) {
				$sql .= ' LEFT JOIN (SELECT id as pttid, viewing_location_option_id,training_id FROM person_to_training) viewloc ON viewloc.pttid = ptt_id AND pt.id = viewloc.training_id';
				$sql .= ' LEFT JOIN person_to_training_viewing_loc_option ON viewing_location_option_id = person_to_training_viewing_loc_option.id ';
			}

			if ($this->view->isScoreReport) {
				$sql .= "LEFT JOIN score AS spre ON (spre.person_to_training_id = pt.ptt_id AND spre.score_label = 'Pre-Test' AND spre.is_deleted = 0)
						 LEFT JOIN score AS spost ON (spost.person_to_training_id = pt.ptt_id AND spost.score_label = 'Post-Test' AND spost.is_deleted = 0)
						 LEFT JOIN (SELECT DISTINCT person_to_training_id, GROUP_CONCAT(score_label) as labels, GROUP_CONCAT(score_value) as scores FROM score WHERE (score_label !=  'Post-Test' AND score_label != 'Pre-Test' AND is_deleted = 0) GROUP BY person_to_training_id  ) as scoreother ON (pt.ptt_id = scoreother.person_to_training_id)";
			}

			$where = array ();

			$where [] = ' pt.is_deleted = 0 ';

			if($locWhere = $this->getLocationCriteriaWhereClause($criteria,  '', 'pt')) {
				$where [] = $locWhere;
			}

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where []= " pt.training_organizer_option_id in ($org_allowed_ids) ";
			}

			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)
				$where []= " pt.training_organizer_option_id in ($site_orgs) ";

			if ($criteria ['age_min']) {
				$where []= ' pt.age >= '.$criteria['age_min'];
			}
			if ($criteria ['age_max']) {
				$where []= ' pt.age <= '.$criteria['age_max'];
			}

			// legacy
			if ($this->_is_not_filter_all($criteria['training_title_option_id']) && $criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$where [] = ' pt.training_title_option_id in (' . $this->_sql_implode($criteria['training_title_option_id']) . ') ';
			}

			if ($this->_is_not_filter_all($criteria['training_title_id']) && $criteria ['training_title_id'] or $criteria ['training_title_id'] === '0') {
				$where [] = ' pt.training_title_option_id in (' . $this->_sql_implode($criteria['training_title_id']) . ') ';
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				$where [] = ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}

			if ($criteria ['custom_1_id'] or $criteria ['custom_1_id'] === '0') {
				$where [] = ' pt.person_custom_1_option_id = \'' . $criteria ['custom_1_id'] . '\'';
			}
			if ($criteria ['custom_2_id'] or $criteria ['custom_2_id'] === '0') {
				$where [] = ' pt.person_custom_2_option_id = \'' . $criteria ['custom_2_id'] . '\'';
			}
			if (isset($this->setting['display_people_custom3']) && $this->setting['display_people_custom3']) {
				if ($criteria ['custom_3_id'] or $criteria ['custom_3_id'] === '0') {
					$where [] = ' person_custom_3 = \'' . $criteria ['custom_3_id'] . '\'';
				}
			}
			if (isset($this->setting['display_people_custom4']) && $this->setting['display_people_custom4']) {
				if ($criteria ['custom_4_id'] or $criteria ['custom_4_id'] === '0') {
					$where [] = ' person_custom_4 = \'' . $criteria ['custom_4_id'] . '\'';
				}
			}
			if (isset($this->setting['display_people_custom5']) && $this->setting['display_people_custom5']) {
				if ($criteria ['custom_5_id'] or $criteria ['custom_5_id'] === '0') {
					$where [] = ' person_custom_5 = \'' . $criteria ['custom_5_id'] . '\'';
				}
			}

			if ($criteria ['qualification_id']) {
				$where [] = ' (pq.id = ' . $criteria ['qualification_id'] . ' OR pqs.parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
			if ($criteria ['qualification_secondary_id']) {
				$where [] = ' pqs.id = ' . $criteria ['qualification_secondary_id'];
			}

			if ($criteria ['primary_responsibility_option_id']) {
				$where [] = ' pt.primary_responsibility_option_id = ' . $criteria ['primary_responsibility_option_id'] . ' ';
			}
			if ($criteria ['secondary_responsibility_option_id']) {
				$where [] = ' pt.secondary_responsibility_option_id = ' . $criteria ['secondary_responsibility_option_id'] . ' ';
			}
			if ($criteria ['highest_edu_level_option_id']) {
				$where [] = ' pt.highest_edu_level_option_id = ' . $criteria ['highest_edu_level_option_id'] . ' ';
			}
			if ($criteria ['attend_reason_option_id']) {
				$where [] = ' pt.attend_reason_option_id = ' . $criteria ['attend_reason_option_id'] . ' ';
			}


			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0') {
				$where [] = ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if ($criteria ['facilityInput']) {
				$where [] = ' pt.facility_id = \'' . $criteria ['facilityInput'] . '\'';
			}

			if ($criteria ['training_gender']) {
				$where [] = ' pt.gender = \'' . $criteria ['training_gender'] . '\'';
			}

			if ($criteria ['training_active']) {
				$where [] = ' pt.active = \'' . $criteria ['training_active'] . '\'';
			}

			if ($criteria ['is_tot'] or $criteria ['is_tot'] === '0') {
				$where [] = ' pt.is_tot = ' . $criteria ['is_tot'];
			}

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0') {
				$where [] = ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';
			}

			if ($criteria ['training_organizer_option_id'][0] && is_array ( $criteria ['training_organizer_option_id'] )) {
				$where [] = ' pt.training_organizer_option_id IN (' . implode ( ',', $criteria ['training_organizer_option_id'] ) . ')';
			}
			
			if ($criteria ['funding_id'] or $criteria ['funding_id'] === '0') {
				$where [] = ' tfund.training_funding_option_id = \'' . $criteria ['funding_id'] . '\'';
			}

			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where [] = ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			if ($criteria ['person_to_training_viewing_loc_option_id']) {
				$where [] = ' viewloc.viewing_location_option_id = ' . $criteria['person_to_training_viewing_loc_option_id'];
			}

			if ($this->view->isScoreReport) {
				$where [] = ' (spre.score_value != "" OR spost.score_value != "" OR scoreother.labels != "")'; // require a score to be present

				if ($criteria ['score_min']) {
					$where [] = ' spost.score_value > ' . $criteria ['score_min'];
				}
			}

			if ($where)
			$sql .= ' WHERE ' . implode ( ' AND ', $where );

			if ($this->view->isScoreReport && $criteria ['score_percent_min']) {
				$sql .= ' HAVING score_percent_change > ' . $criteria ['score_percent_min'];
			}

			if ($criteria ['doCount']) {

				$groupBy = array();

				if ( $criteria['showAge']) $groupBy []= ' pt.age ';

				if ($criteria ['showTrainingTitle']) {
					$groupBy []= ' pt.training_title_option_id';
				}
				if ($criteria ['showGender']) {
					$groupBy []= ' pt.gender';
				}
				if ($criteria ['showActive']) {
					$groupBy []= ' pt.active';
				}
				if ($criteria ['showProvince']) {
					$groupBy []= ' pt.province_id';
				}
				if ($criteria ['showDistrict']) {
					$groupBy []= ' pt.district_id';
				}
				if ($criteria ['showRegionC']) {
					$groupBy []= ' pt.region_c_id';
				}

				if ( $criteria['showRespPrim'] ) {
					$groupBy []= ' pt.primary_responsibility_option_id';
				}
				if ( $criteria['showRespSecond'] ) {
					$groupBy []= ' pt.secondary_responsibility_option_id';
				}

				if ($criteria ['showCustom1']) {
					$groupBy []= '  pt.person_custom_1_option_id';
				}
				if ($criteria ['showCustom2']) {
					$groupBy []= '  pt.person_custom_2_option_id';
				}
				if ($criteria ['showCustom3']) {
					$groupBy []= '  person_custom_3';
				}
				if ($criteria ['showCustom4']) {
					$groupBy []= '  person_custom_4';
				}
				if ($criteria ['showCustom5']) {
					$groupBy []= '  person_custom_5';
				}
				if (isset ( $criteria ['showLocation'] ) and $criteria ['showLocation']) {
					$groupBy []= '  pt.training_location_id';
				}
				if ($criteria ['showTopic']) {
					$groupBy []= '  ttopic.training_topic_option_id';
				}
				if ($criteria ['showQualification']) {
					$groupBy []= '  pt.primary_qualification_option_id';
				}
				if ($criteria ['showPepfar'] || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0') {
					$groupBy []= '  tpep.training_pepfar_categories_option_id';
				}

				if ($criteria ['showFacility']) {
					$groupBy []= '  pt.facility_id';
				}

				if ($criteria ['showTot']) {
					$groupBy []= '  pt.is_tot';
				}

				if ($criteria ['showOrganizer']) {
					$groupBy []= '  pt.training_organizer_option_id';
				}

				if ($criteria ['showFunding']) {
					$groupBy []= '  tfund.training_funding_option_id';
				}
				if ($criteria ['showQualPrim']) {
					$groupBy []= '  pq.id ';
				}
				if ($criteria ['showQualSecond']) {
					$groupBy []= '  pqs.id ';
				}
				if ($criteria ['showViewingLoc'] || $criteria['person_to_training_viewing_loc_option_id']) {
					$groupBy []= ' viewloc.viewing_location_option_id ';
				}


				if ($groupBy ) {
					$groupBy = ' GROUP BY ' . implode(', ',$groupBy);
					$sql .= $groupBy;
				}
			} else {
				if ($criteria ['showPepfar'] || $criteria ['showTopic'] || $criteria ['showFunding']) {
					$sql .= ' GROUP BY person_id, pt.id';
				}
			}

			$rowArray = $db->fetchAll ( $sql );

			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
			}
			
			if ($this->_getParam ( 'outputType' ))
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );

		$this->viewAssignEscaped ( 'results', $rowArray );
		if ($rowArray) {
			$first = reset ( $rowArray );
			if (isset ( $first ['phone_work'] )) {
				foreach ( $rowArray as $key => $val ) {
					$phones = array ();
					if ($val ['phone_work'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_work'] ) ) . '&nbsp;(w)';
					if ($val ['phone_home'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_home'] ) ) . '&nbsp;(h)';
					if ($val ['phone_mobile'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_mobile'] ) ) . '&nbsp;(m)';
					$rowArray [$key] ['phone'] = implode ( ', ', $phones );
				}
				$this->view->assign ( 'results', $rowArray );
			}
		}

		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );

		//location
		$locations = Location::getAll("2");
                                        
		$this->viewAssignEscaped ( 'locations', $locations );

		//trainingTitle
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//topics
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualsArray );
		//qualifications (primary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NULL' );
		$this->viewAssignEscaped ( 'qualifications_primary', $qualsArray );
		//qualifications (secondary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NOT NULL' );
		$this->viewAssignEscaped ( 'qualifications_secondary', $qualsArray );


		$qualsArray = OptionList::suggestionList ( 'person_primary_responsibility_option', 'responsibility_phrase', false, false, false );
		$this->viewAssignEscaped ( 'responsibility_primary', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_secondary_responsibility_option', 'responsibility_phrase', false, false, false );
		$this->viewAssignEscaped ( 'responsibility_secondary', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_attend_reason_option', 'attend_reason_phrase', false, false, false );
		$this->viewAssignEscaped ( 'attend_reason', $qualsArray );

		$qualsArray = OptionList::suggestionList ( 'person_education_level_option', 'education_level_phrase', false, false, false);
		$this->viewAssignEscaped ( 'highest_education_level', $qualsArray );


		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//organizers
		//$organizersArray = OptionList::suggestionList('training_organizer_option','training_organizer_phrase',false,false,false);
		//$this->viewAssignEscaped('organizers',$organizersArray);
		//funding
		$fundingArray = OptionList::suggestionList ( 'training_funding_option', 'funding_phrase', false, false, false );
		$this->viewAssignEscaped ( 'funding', $fundingArray );

		// custom fields
		$customArray = OptionList::suggestionList ( 'person_custom_1_option', 'custom1_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom1', $customArray );
		$custom2Array = OptionList::suggestionList ( 'person_custom_2_option', 'custom2_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom2', $custom2Array );
		$custom3Array = OptionList::suggestionList ( 'person', 'custom_3', false, false, false , "custom_3 != ''");
		$this->viewAssignEscaped ( 'custom3', $custom3Array );
		$custom4Array = OptionList::suggestionList ( 'person', 'custom_4', false, false, false , "custom_4 != ''");
		$this->viewAssignEscaped ( 'custom4', $custom4Array );
		$custom5Array = OptionList::suggestionList ( 'person', 'custom_5', false, false, false , "custom_5 != ''");
		$this->viewAssignEscaped ( 'custom5', $custom5Array );
		
		//viewing location
		$viewingLocArray = OptionList::suggestionList ( 'person_to_training_viewing_loc_option', 'location_phrase', false, false, false );
		$this->viewAssignEscaped ( 'viewing_loc', $viewingLocArray );

		//organizers
		// restricted access?? only show trainings we have the ACL to view
		require_once('views/helpers/TrainingViewHelper.php');
		$org_allowed_ids = allowed_organizer_access($this);
		$orgWhere = '';
		if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
			$org_allowed_ids = implode(',', $org_allowed_ids);
			$orgWhere = " id in ($org_allowed_ids) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		if ($site_orgs) {
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}

		$this->view->assign ( 'organizers_checkboxes', Checkboxes::generateHtml ( 'training_organizer_option', 'training_organizer_phrase', $this->view, array(), $orgWhere ) );
		
		  $this->view->assign ( 'organizers_dropdown', DropDown::generateHtml ('training_organizer_option', 'training_organizer_phrase',
		    $criteria['training_organizer_option_id'], true, $this->view->viewonly, false,null,null,null,     true, 10 ) );
		
		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );

	}
	

	/*
	 * TA:17:10: 10/21/2014
	 */
	public function commodityReport() {
		
		if($this->getSanParam ( 'go' )){
			$criteria = array ();
 			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
 			$facility_id = $this->getSanParam('id');
 			$sql = "SELECT commodity.id, commodity_name_option.commodity_name as commodity_name, DATE_FORMAT(commodity.date, '%m/%y') as date,
			commodity.consumption, commodity.stock_out from commodity
			INNER JOIN commodity_name_option
				ON commodity_name_option.id=commodity.name_id where commodity.facility_id=" . $facility_id;
 			$commodity_name_option_id = $this->getSanParam('commodity_name_option_id');
 			if($commodity_name_option_id){
 				$sql = $sql . " and name_id =" . $commodity_name_option_id;
 			}
 			$dateYYstart = $this->getSanParam ( 'dateYYstart' );
 			$dateMMstart = $this->getSanParam ( 'dateMMstart' );
 			if($dateYYstart){
 				if(!$dateMMstart)
 					$dateMMstart = "01";
 				$sql = $sql . " and date > '" . $dateYYstart . "-" . $dateMMstart . "-01'";
 			}
 			$dateYYend = $this->getSanParam ( 'dateYYend' );
 			$dateMMend = $this->getSanParam ( 'dateMMend' );
 			if($dateYYend){
 				if(!$dateMMend)
 					$dateMMend = "01";
 				$sql = $sql . " and date < '" . $dateYYend . "-" . $dateMMend . "-01'";
 			}
 			
 			$rowArray = $db->fetchAll ( $sql );
      
            $criteria ['go'] = $this->getSanParam ( 'go' );
            $this->view->assign ( 'count', count ( $rowArray ) );
            $criteria['commodity_name_option_id'] = $this->getSanParam('commodity_name_option_id');
            $this->view->assign ( 'criteria', $criteria );
          
            $sql = "SELECT facility_name, location_id from facility where id=" .$facility_id; 
            $facility_name = $db->fetchAll ( $sql );

			$updatedRegions = Location::getCityandParentNames($facility_name[0]['location_id'], Location::getAll("2"), $this->setting('num_location_tiers'));

            
			for($i=0; $i<count($rowArray); $i++){
				$rowArray[$i]['province_name'] = $updatedRegions['province_name'];
				$rowArray[$i]['district_name'] = $updatedRegions['district_name'];
				$rowArray[$i]['facility_name'] = $facility_name[0]['facility_name'];
            }
            $this->viewAssignEscaped ( 'results', $rowArray );
          
            $this->view->assign ( 'commodity_name_id', $commodity_name_option_id);
            $this->view->assign ( 'dateMMstart', $dateMMstart);
            $this->view->assign ( 'dateYYstart', $dateYYstart);
            $this->view->assign ( 'dateMMend', $dateMMend);
            $this->view->assign ( 'dateYYend', $dateYYend);
            
            if ($this->_getParam ( 'outputType' )){
            	$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
            }
            
		}
		
		$commodity_names = OptionList::suggestionList ( 'commodity_name_option', 'commodity_name', false, false, false );
		$this->viewAssignEscaped ( 'commodity_names', $commodity_names );
                $facility_id = $this->getSanParam('id');
		$this->viewAssignEscaped ( 'facility_id', $facility_id );
	}

	public function participantsByTrainingAction() {
		$this->view->assign ( 'mode', 'count' );
		return $this->peopleReport ();
	}
	
	/*
	 * TA:17:10: 10/21/2014
	 */
	public function commodityByFacilityAction() {
		$this->view->assign ( 'mode', 'count' );
		return $this->commodityReport ();
	}

	public function participantsScoresAction() {
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'isScoreReport', TRUE );
		return $this->peopleReport ();
	}

	public function participantsByNameAction() {
		$this->view->assign ( 'mode', 'id' );
		return $this->peopleReport ();
	}

	public function participantsByFacilityTypeAction() {
		require_once ('views/helpers/DropDown.php');
		$this->view->assign ( 'mode', 'id' );
		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = '0000-00-00';
		if ($rowArray and $rowArray [0] ['start'])
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		$criteria ['showAge'] = $this->getSanParam ( 'showAge' );
		$criteria ['age_min'] = $this->getSanParam ( 'age_min' );
		$criteria ['age_max'] = $this->getSanParam ( 'age_max' );

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		$criteria ['end-year'] = date ( 'Y' );
		$criteria ['end-month'] = date ( 'm' );
		$criteria ['end-day'] = date ( 'd' );
		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$criteria ['training_gender'] = $this->getSanParam ( 'training_gender' );
		$criteria ['training_active'] = $this->getSanParam ( 'training_active' );
		$criteria ['concatNames'] = $this->getSanParam ( 'concatNames' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['training_title_id'] = $this->getSanParam ( 'training_title_id' );
		$criteria ['training_pepfar_id'] = $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_topic_id'] = $this->getSanParam ( 'training_topic_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['facility_type_id'] = $this->getSanParam ( 'facility_type_id' );
		$criteria ['facility_sponsor_id'] = $this->getSanParam ( 'facility_sponsor_id' );
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['is_tot'] = $this->getSanParam ( 'is_tot' );
		$criteria ['funding_id'] = $this->getSanParam ( 'funding_id' );
		$criteria ['training_organizer_id'] = $this->getSanParam ( 'training_organizer_id' );
		$criteria ['training_organizer_option_id'] = $this->getSanParam ( 'training_organizer_option_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['qualification_secondary_id'] = $this->getSanParam ( 'qualification_secondary_id' );
		$criteria ['qualification_option_id'] =       $this->getSanParam ( 'qualification_option_id' );

		$criteria ['custom_1_id']                  = $this->getSanParam ( 'custom_1_id' );
		$criteria ['custom_2_id']                  = $this->getSanParam ( 'custom_2_id' );
		$criteria ['custom_3_id']                  = $this->getSanParam ( 'custom_3_id' );
		$criteria ['custom_4_id']                  = $this->getSanParam ( 'custom_4_id' );
		$criteria ['custom_5_id']                  = $this->getSanParam ( 'custom_5_id' );
		
		$criteria ['doCount'] = ($this->view->mode == 'count');
		$criteria ['showProvince'] = ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or $criteria ['province_id'] === '0')));
		$criteria ['showDistrict'] = ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or $criteria ['district_id'] === '0')));
		$criteria ['showRegionC'] = ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or ! empty ( $criteria ['region_c_id'] ))));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_id'] or $criteria ['training_title_option_id'] === '0')));
		$criteria ['showPepfar'] = ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_id'] or $criteria ['training_pepfar_id'] === '0')));
		$criteria ['showTopic'] = ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showFacility'] = ($this->getSanParam ( 'showFacility' ) or ($criteria ['doCount'] and $criteria ['facilityInput']));
		$criteria ['showGender'] = ($this->getSanParam ( 'showGender' ) or ($criteria ['doCount'] and $criteria ['training_gender']));
		$criteria ['showActive'] = ($this->getSanParam ( 'showActive' ) or ($criteria ['doCount'] and $criteria ['training_active']));
		$criteria ['showEmail'] = ($this->getSanParam ( 'showEmail' ));
		$criteria ['showPhone'] = ($this->getSanParam ( 'showPhone' ));
		$criteria ['showType'] = ($this->getSanParam ( 'showType' ) or ($criteria ['doCount'] and ($criteria ['facility_type_id'] or $criteria ['facility_type_id'] === '0')));
		$criteria ['showSponsor'] = ($this->getSanParam ( 'showSponsor' ) or ($criteria ['doCount'] and $criteria ['facility_sponsor_id']));
		$criteria ['showTot'] = ($this->getSanParam ( 'showTot' ) or ($criteria ['doCount'] and $criteria ['is_tot'] !== '' or $criteria ['is_tot'] === '0'));
		$criteria ['showFunding'] = ($this->getSanParam ( 'showFunding' ) or ($criteria ['doCount'] and $criteria ['funding_id'] or $criteria ['funding_id'] === '0'));
		$criteria ['showOrganizer'] = ($this->getSanParam ( 'showOrganizer' ) or ($criteria ['doCount'] and ($criteria ['training_organizer_option_id'])));
		$criteria ['showQualPrim'] = ($this->getSanParam ( 'showQualPrim' ) or ($criteria ['doCount'] and ($criteria ['qualification_id'] or $criteria ['qualification_id'] === '0')));
		$criteria ['showQualSecond'] = ($this->getSanParam ( 'showQualSecond' ) or ($criteria ['doCount'] and ($criteria ['qualification_secondary_id'] or $criteria ['qualification_secondary_id'] === '0')));
		$criteria ['showQualification'] = ($this->getSanParam ( 'showQualification' ) or ($criteria ['doCount'] and ($criteria ['qualification_option_id'] or $criteria ['qualification_option_id'] === '0')));

		$criteria ['showCustom1']       = ($this->getSanParam ( 'showCustom1' ));
		$criteria ['showCustom2']       = ($this->getSanParam ( 'showCustom2' ));
		$criteria ['showCustom3']       = ($this->getSanParam ( 'showCustom3' ));
		$criteria ['showCustom4']       = ($this->getSanParam ( 'showCustom4' ));
		$criteria ['showCustom5']       = ($this->getSanParam ( 'showCustom5' ));
		

		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {

			$sql = 'SELECT ';

			if ($criteria ['doCount']) {
				$sql .= ' COUNT(person_id) as "cnt" ';
			} else {
				if ($criteria ['concatNames'])
				$sql .= ' DISTINCT person_id as "id", CONCAT(first_name, ' . "' '" . ',last_name, ' . "' '" . ', IFNULL(suffix_phrase, ' . "' '" . ')) as "name" ';
				else
				$sql .= ' DISTINCT person_id as "id", IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, last_name, first_name, middle_name  ';
			}
			if ($criteria ['showPhone']) {
				$sql .= ", CASE WHEN (pt.phone_work IS NULL OR pt.phone_work = '') THEN NULL ELSE pt.phone_work END as \"phone_work\", CASE WHEN (pt.phone_home IS NULL OR pt.phone_home = '') THEN NULL ELSE pt.phone_home END as \"phone_home\", CASE WHEN (pt.phone_mobile IS NULL OR pt.phone_mobile = '') THEN NULL ELSE pt.phone_mobile END as \"phone_mobile\" ";
			}
			if ($criteria ['showEmail']) {
				$sql .= ', pt.email ';
			}
			if ($criteria ['showGender']) {
				$sql .= ', pt.gender ';
			}
			if ($criteria ['showAge']) {
				$sql .= ', pt.age ';
			}
			if ($criteria ['showActive']) {
				$sql .= ', pt.active ';
			}
			if ($criteria ['showTrainingTitle']) {
				$sql .= ', pt.training_title, pt.training_start_date ';
			}
			if ($criteria ['showRegionC']) {
				$sql .= ', pt.region_c_name ';
			}
			if ($criteria ['showDistrict']) {
				$sql .= ', pt.district_name ';
			}
			if ($criteria ['showProvince']) {
				$sql .= ', pt.province_name ';
			}


			if ($criteria ['showPepfar']) {
				$sql .= ', tpep.pepfar_category_phrase ';
			}

			if ($criteria ['showOrganizer']) {
				$sql .= ', torg.training_organizer_phrase ';
			}

			if ($criteria ['showFunding']) {
				//if ( $criteria['doCount'] ) {
				$sql .= ', tfund.funding_phrase ';
				//} else {
				//	$sql .= ', GROUP_CONCAT(DISTINCT tfund.funding_phrase ORDER BY funding_phrase) as "funding_phrase" ';
				//}
			}

			if ($criteria ['showTopic']) {
				$sql .= ', ttopic.training_topic_phrase ';
			}

			if ($criteria ['showType']) {
				$sql .= ', fto.facility_type_phrase ';
			}

			if ($criteria ['showSponsor']) {
				$sql .= ', fso.facility_sponsor_phrase ';
			}
			if ($criteria ['showFacility']) {
				$sql .= ', pt.facility_name ';
			}

			if ($criteria ['showTot']) {
				//$sql .= ', pt.is_tot ';
				$sql .= ", IF(pt.is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot";
			}

			if ($criteria ['showQualPrim']) {
				$sql .= ', pq.qualification_phrase ';
			}
			if ($criteria ['showQualSecond']) {
				$sql .= ', pqs.qualification_phrase AS qualification_secondary_phrase';
			}
			if ($criteria ['showQualification']) {
				$sql .= ', pq.qualification_phrase';
			}

			if ( $criteria['showCustom1'] ) {
				$sql .= ', pqc.custom1_phrase ';
			}
			if ( $criteria['showCustom2'] ) {
				$sql .= ', pqc2.custom2_phrase ';
			}
			if ( $criteria['showCustom3'] ) {
				$sql .= ', person_custom_3 ';
			}
			if ( $criteria['showCustom4'] ) {
				$sql .= ', person_custom_4 ';
			}
			if ( $criteria['showCustom5'] ) {
				$sql .= ', person_custom_5 ';
			}

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			$sql .= ' FROM ( SELECT training.*, person.facility_id as "facility_id", person.id as "person_id", person.last_name, IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, ';
			$sql .= '      person.first_name, person.middle_name, person.person_custom_1_option_id, person.person_custom_2_option_id, person.custom_3 as person_custom_3, person.custom_4 as person_custom_4, person.custom_5 as person_custom_5, person.phone_work, person.phone_home, person.phone_mobile, person.email, ';
			$sql .= '      CASE WHEN birthdate  IS NULL OR birthdate = \'0000-00-00\' THEN NULL ELSE ((date_format(now(),\'%Y\') - date_format(birthdate,\'%Y\')) - (date_format(now(),\'00-%m-%d\') < date_format(birthdate,\'00-%m-%d\')) ) END as "age", ';
			$sql .= '      CASE WHEN person.active = \'deceased\' THEN \'inactive\' ELSE person.active END as "active", ';
			$sql .= '      CASE WHEN person.gender IS NULL THEN \'na\' WHEN person.gender = \'\' THEN \'na\' ELSE person.gender END as "gender", ';
			$sql .= '      primary_qualification_option_id, tto.training_title_phrase AS training_title,facility.facility_name, facility.type_option_id, facility.sponsor_option_id, ';
			$sql .= '      l.'.implode(', l.',$field_name);
			$sql .= ' FROM training INNER JOIN training_title_option tto ON training.training_title_option_id = tto.id ';
			$sql .= ' INNER JOIN person_to_training ON training.id = person_to_training.training_id ';
			$sql .= ' INNER JOIN person ON person.id = person_to_training.person_id ';
			$sql .= ' INNER JOIN facility ON person.facility_id = facility.id ';
			$sql .= ' LEFT JOIN ('.$location_sub_query.') as l ON facility.location_id = l.id';
			$sql .= ' LEFT  JOIN person_suffix_option suffix ON person.suffix_option_id = suffix.id ';
			$sql .= ' ) as pt ';


			if ($criteria ['showPepfar']) {
				$sql .= '	JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if ($criteria ['showTopic']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
				//$sql .= '	LEFT JOIN training_topic_option as ttopic ON ttopic.id = ttopic.training_topic_option_id ';
			}

			if ($criteria ['showType']) {
				$sql .= '	JOIN facility_type_option as fto ON fto.id = pt.type_option_id ';
			}

			if ($criteria ['showSponsor']) {
				$sql .= '	JOIN facility_sponsor_option as fso ON fso.id = pt.sponsor_option_id ';
			}

			if ($criteria ['showOrganizer']) {
				$sql .= '	JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';
			}

			if ($criteria ['showFunding']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttfo.training_funding_option_id, funding_phrase FROM training_to_training_funding_option as ttfo JOIN training_funding_option as tfo ON ttfo.training_funding_option_id = tfo.id) as tfund ON tfund.training_id = pt.id ';
			}

			if ( $criteria['showCustom1'] ) {
				$sql .= ' LEFT JOIN person_custom_1_option as pqc ON pt.person_custom_1_option_id = pqc.id  ';
			}
			if ( $criteria['showCustom2'] ) {
				$sql .= ' LEFT JOIN person_custom_2_option as pqc2 ON pt.person_custom_2_option_id = pqc2.id  ';
			}

			if ($criteria ['showQualPrim'] || $criteria ['showQualSecond']) {
				// primary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pq ON (
				(pt.primary_qualification_option_id = pq.id AND pq.parent_id IS NULL)
				OR
				pq.id = (SELECT parent_id FROM person_qualification_option WHERE id = pt.primary_qualification_option_id LIMIT 1)
				)';

				// secondary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pqs ON (
				pt.primary_qualification_option_id = pqs.id AND pqs.parent_id IS NOT NULL
				)';
			}

			if ($criteria ['showQualification']) {
				$sql .= '  LEFT JOIN person_qualification_option as pq ON pq.id = primary_qualification_option_id';
			}

			$where = array();

			$where []= 'pt.is_deleted=0 ';

			if ($criteria ['age_min']) {
				$where []= ' pt.age >= '.$criteria['age_min'];
			}
			if ($criteria ['age_max']) {
				$where []= ' pt.age <= '.$criteria['age_max'];
			}

			// not used
			if ($this->_is_not_filter_all($criteria['training_title_option_id']) && $criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$where [] = ' pt.training_title_option_id in (' . $this->_sql_implode($criteria['training_title_option_id']) . ') ';
			}

			if ($this->_is_not_filter_all($criteria['training_title_id']) && $criteria ['training_title_id'] or $criteria ['training_title_id'] === '0') {
				$where [] = ' pt.training_title_option_id in (' . $this->_sql_implode($criteria['training_title_id']) . ') ';
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				$where []= ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}
			/*
			if ( $criteria['qualification_id'] or $criteria['qualification_id'] === '0'  ) {
			if ( strlen($where) ) $where .= ' AND ';
			$where .= ' pt.primary_qualification_option_id = \''.$criteria['qualification_id'].'\'' ;
			}
			*/
			if ($criteria ['qualification_id']) {
				$where []= ' (pq.id = ' . $criteria ['qualification_id'] . ' OR pqs.parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
			if ($criteria ['qualification_secondary_id']) {
				$where []= ' pqs.id = ' . $criteria ['qualification_secondary_id'];
			}
			if ($criteria ['qualification_option_id']) { // this is the one we use
				$where []= ' pq.id = ' . $criteria ['qualification_option_id'];
			}

			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0') {
				$where []= ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if ($criteria ['facilityInput']) {
				$where []= ' pt.facility_id = \'' . $criteria ['facilityInput'] . '\'';
			}

			if ($criteria ['facility_type_id'] or $criteria ['facility_type_id'] === '0') {
				$where []= ' pt.type_option_id = \'' . $criteria ['facility_type_id'] . '\'';
			}
			if ($criteria ['facility_sponsor_id'] or $criteria ['facility_sponsor_id'] === '0') {
				$where []= ' pt.sponsor_option_id = \'' . $criteria ['facility_sponsor_id'] . '\'';
			}

			if ($criteria ['training_gender']) {
				$where []= ' pt.gender = \'' . $criteria ['training_gender'] . '\'';
			}

			if ($criteria ['training_active']) {
				$where []= ' pt.active = \'' . $criteria ['training_active'] . '\'';
			}

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0') {
				$where []= ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';
			}

			if ($criteria ['training_organizer_option_id'] && is_array ( $criteria ['training_organizer_option_id'] )) {
				$where []= ' pt.training_organizer_option_id IN (' . implode ( ',', $criteria ['training_organizer_option_id'] ) . ')';
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				$where []= ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}

			if ($criteria ['is_tot'] or $criteria ['is_tot'] === '0') {
				$where []= ' pt.is_tot = ' . $criteria ['is_tot'];
			}

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0') {
				$where []= ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';
			}

			if ($criteria ['funding_id'] or $criteria ['funding_id'] === '0') {
				$where []= ' tfund.training_funding_option_id = \'' . $criteria ['funding_id'] . '\'';
			}

			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where []= ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			if ($criteria ['custom_1_id'] or $criteria ['custom_1_id'] === '0') {
				$where [] = ' pt.person_custom_1_option_id = \'' . $criteria ['custom_1_id'] . '\'';
			}
			if ($criteria ['custom_2_id'] or $criteria ['custom_2_id'] === '0') {
				$where [] = ' pt.person_custom_2_option_id = \'' . $criteria ['custom_2_id'] . '\'';
			}
			if (isset($this->setting['display_people_custom3']) && $this->setting['display_people_custom3']) {
				if ($criteria ['custom_3_id'] or $criteria ['custom_3_id'] === '0') {
					$where [] = ' person_custom_3 = \'' . $criteria ['custom_3_id'] . '\'';
				}
			}
			if (isset($this->setting['display_people_custom4']) && $this->setting['display_people_custom4']) {
				if ($criteria ['custom_4_id'] or $criteria ['custom_4_id'] === '0') {
					$where [] = ' person_custom_4 = \'' . $criteria ['custom_4_id'] . '\'';
				}
			}
			if (isset($this->setting['display_people_custom5']) && $this->setting['display_people_custom5']) {
				if ($criteria ['custom_5_id'] or $criteria ['custom_5_id'] === '0') {
					$where [] = ' person_custom_5 = \'' . $criteria ['custom_5_id'] . '\'';
				}
			}

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where []= " pt.training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)
				$where []= " pt.training_organizer_option_id in ($site_orgs) ";

			if ($where)
			$sql .= ' WHERE ' . implode(' AND ',$where);

			$rowArray = $db->fetchAll ( $sql );

			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
			}
			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );

		$this->viewAssignEscaped ( 'results', $rowArray );
		if ($rowArray) {
			$first = reset ( $rowArray );
			if (isset ( $first ['phone_work'] )) {
				foreach ( $rowArray as $key => $val ) {
					$phones = array ();
					if ($val ['phone_work'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_work'] ) ) . '&nbsp;(w)';
					if ($val ['phone_home'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_home'] ) ) . '&nbsp;(h)';
					if ($val ['phone_mobile'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_mobile'] ) ) . '&nbsp;(m)';
					$rowArray [$key] ['phone'] = implode ( ', ', $phones );
				}
				$this->view->assign ( 'results', $rowArray );
			}
		}

		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );

		//locations

		$locations = Location::getAll("2");

		$this->viewAssignEscaped('locations',$locations);
		//courses
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//qualifications (primary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NULL' );
		$this->viewAssignEscaped ( 'qualifications_primary', $qualsArray );
		//qualifications (secondary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NOT NULL' );
		$this->viewAssignEscaped ( 'qualifications_secondary', $qualsArray );

		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//facility types
		$typesArray = OptionList::suggestionList ( 'facility_type_option', 'facility_type_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_types', $typesArray );
		//sponsor types
		$sponsorsArray = OptionList::suggestionList ( 'facility_sponsor_option', 'facility_sponsor_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_sponsors', $sponsorsArray );
		//organizers
		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, false, false );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );
		//funding
		$fundingArray = OptionList::suggestionList ( 'training_funding_option', 'funding_phrase', false, false, false );
		$this->viewAssignEscaped ( 'funding', $fundingArray );
		
		// custom fields
		$customArray = OptionList::suggestionList ( 'person_custom_1_option', 'custom1_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom1', $customArray );
		$custom2Array = OptionList::suggestionList ( 'person_custom_2_option', 'custom2_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom2', $custom2Array );
		$custom3Array = OptionList::suggestionList ( 'person', 'custom_3', false, false, false , "custom_3 != ''");
		$this->viewAssignEscaped ( 'custom3', $custom3Array );
		$custom4Array = OptionList::suggestionList ( 'person', 'custom_4', false, false, false , "custom_4 != ''");
		$this->viewAssignEscaped ( 'custom4', $custom4Array );
		$custom5Array = OptionList::suggestionList ( 'person', 'custom_5', false, false, false , "custom_5 != ''");
		$this->viewAssignEscaped ( 'custom5', $custom5Array );
		
		//organizers
		// restricted access?? only show trainings we have the ACL to view
		require_once('views/helpers/TrainingViewHelper.php');
		$org_allowed_ids = allowed_organizer_access($this);
		$orgWhere = '';
		if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
			$org_allowed_ids = implode(',', $org_allowed_ids);
			$orgWhere = " id in ($org_allowed_ids) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		if ($site_orgs) {
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site
		$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
		if ($site_orgs) {
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}

		$this->view->assign ( 'organizers_checkboxes', Checkboxes::generateHtml ( 'training_organizer_option', 'training_organizer_phrase', $this->view, array(), $orgWhere ) );
		
		$this->view->assign ( 'organizers_dropdown', DropDown::generateHtml ('training_organizer_option', 'training_organizer_phrase',
			$criteria['training_organizer_option_id'], true, $this->view->viewonly, false,null,null,null,     true, 10 ) );
		
		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
		//qualifactions
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
	}

	public function trainersAction() {
		require_once ('models/table/Person.php');
		require_once ('models/table/Trainer.php');

		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$criteria ['skill_id'] = $this->getSanParam ( 'trainer_skill_id' );
		if (is_array ( $criteria ['skill_id'] ) && $criteria ['skill_id'] [0] === "") { // "All"
			$criteria ['skill_id'] = array ();
		}

		$criteria ['concatNames'] = $this->getSanParam ( 'concatNames' );
		$criteria ['type_id'] = $this->getSanParam ( 'trainer_type_id' );
		$criteria ['language_id'] = $this->getSanParam ( 'trainer_language_id' );
		$criteria ['training_topic_option_id'] = $this->getSanParam ( 'training_topic_option_id' ); // checkboxes
		$criteria ['go'] = $this->getSanParam ( 'go' );

		if ($criteria ['go']) {

			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$sql = " SELECT DISTINCT " . " `person`.`id`, ";
			if ($criteria ['concatNames']) {
				$sql .= " CONCAT( `person`.`first_name`, ' ',`person`.`last_name`) as \"name\", ";
			} else {
				$sql .= " `person`.`first_name`, ";
				$sql .= " `person`.`last_name`, ";
				$sql .= " `person`.`middle_name`, ";
				$sql .= " IFNULL(`person_suffix_option`.`suffix_phrase`, ' ') as `suffix_phrase`, ";
			}

			// get training topics taught
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);


			$sql .= " `t`.`type_option_id`, " . " `tt`.`trainer_type_phrase`, " . " `ts`.`trainer_skill_phrase`,	 `tl`.`language_phrase`, " . " topics.training_topic_phrase, l.".implode(', l.',$field_name).
			" FROM `person` " . " JOIN `trainer` AS `t` ON t.person_id = person.id " .
			" JOIN `facility` as f ON person.facility_id = f.id  JOIN (".$location_sub_query.") as l ON f.location_id = l.id " .
			" LEFT JOIN `trainer_type_option` AS `tt` ON t.type_option_id = tt.id " .
			" LEFT JOIN (SELECT trainer_id, trainer_skill_phrase, trainer_skill_option_id FROM trainer_to_trainer_skill_option JOIN trainer_skill_option ON trainer_to_trainer_skill_option.trainer_skill_option_id = trainer_skill_option.id) as ts ON t.person_id = ts.trainer_id " .
			" LEFT JOIN (SELECT trainer_id, language_phrase, trainer_language_option_id FROM trainer_to_trainer_language_option JOIN trainer_language_option ON trainer_to_trainer_language_option.trainer_language_option_id = trainer_language_option.id) as tl ON t.person_id = tl.trainer_id " .
			" LEFT JOIN `person_suffix_option` ON `person`.`suffix_option_id` = `person_suffix_option`.id ";

			$sql .= " LEFT JOIN (

			SELECT ttt.trainer_id, training_topic_phrase, training_topic_option_id
			FROM training_to_trainer ttt
			JOIN training_to_training_topic_option ttto ON ttto.training_id = ttt.training_id
			JOIN training_topic_option tto ON tto.id = ttto.training_topic_option_id
			) as topics ON t.person_id = topics.trainer_id ";

			$where = array();

			if ($criteria ['type_id'] or ($criteria ['type_id'] === '0')) {
				$where []= ' t.type_option_id = ' . $criteria ['type_id'];
			}

			if (! empty ( $criteria ['skill_id'] )) {
				$where []= ' trainer_skill_option_id IN (' . implode ( ',', $criteria ['skill_id'] ) . ')';
			}

			if ($criteria ['language_id']) {
				$where []= ' trainer_language_option_id = ' . $criteria ['language_id'];
			}

			if ($criteria ['training_topic_option_id']) {
				$where []= ' training_topic_option_id IN (' . implode ( ',', $criteria ['training_topic_option_id'] ) . ')';
			}

			if ($where)
			$sql .= ' WHERE ' . implode(' AND ',$where);

			//$sql .= ' GROUP BY person.id ';


			$sql .= " ORDER BY " . " `person`.`last_name` ASC, " . " `person`.`first_name` ASC ";

			//	echo $sql; exit;


			$rowArray = $db->fetchAll ( $sql );
			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$rowArray = array ();
		}

		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'count', count ( $rowArray ) );
		$this->view->assign ( 'criteria', $criteria );

		//locations

		$locations = Location::getAll("2");

		$this->viewAssignEscaped ( 'locations', $locations );
		//types
		$trainerTypesArray = OptionList::suggestionList ( 'trainer_type_option', 'trainer_type_phrase', false, false, false );
		$this->viewAssignEscaped ( 'types', $trainerTypesArray );
		//skillz
		$trainerSkillsArray = OptionList::suggestionList ( 'trainer_skill_option', 'trainer_skill_phrase', false, false );
		$this->viewAssignEscaped ( 'skills', $trainerSkillsArray );
		//languages
		$trainerLanguagesArray = OptionList::suggestionList ( 'trainer_language_option', 'language_phrase', false, false );
		$this->viewAssignEscaped ( 'language', $trainerLanguagesArray );
		//topics
		$this->view->assign ( 'topic_checkboxes', Checkboxes::generateHtml ( 'training_topic_option', 'training_topic_phrase', $this->view ) );

	}
	/*
	public function participantsByCategoryAction() {

	$criteria = array();
	$criteria['start-year'] = date('Y');
	$criteria['start-month'] = date('m');
	$criteria['start-day'] = '01';

	$criteria['cat'] = $this->getSanParam('cat');
	if ( $this->getSanParam('start-year') )
	$criteria['start-year'] = $this->getSanParam('start-year');
	if ( $this->getSanParam('start-month') )
	$criteria['start-month'] = $this->getSanParam('start-month');
	if ( $this->getSanParam('start-day') )
	$criteria['start-day'] = $this->getSanParam('start-day');

	//province
	$provinceArray = OptionList::suggestionList('location_province','province_name',false,false);
	$this->view->assign('provinces',$provinceArray);
	//district
	$districtArray = OptionList::suggestionList('location_district',array('district_name','parent_province_id'),false,false);
	$this->view->assign('districts',$districtArray);
	//http://localhost/itech/web/html/reports/participantsByCategory/cat/pepfar?province_id=&district_id=&start-month=&start-day=&start-year=&go=Preview

	$criteria['district_id'] = $this->getSanParam('district_id');
	$criteria['province_id'] = $this->getSanParam('province_id');

	//Q1 query UNION
	//Q2 query UNION
	//Q3 query UNION
	//Q4 query

	//make sure the date doesn't go back too far
	if ( $criteria['start-year'] < 2000 ) {
	$criteria['start-year'] = 2000;
	}

	$qDate = $criteria['start-year'].'-'.$criteria['start-month'].'-'.$criteria['start-day'];
	$results = array();
	if ( $this->getSanParam('go') ) {
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$grandTotal = 0;
	while( $qDate != '' AND (strtotime($qDate) < time()) ) {
	switch ( $criteria['cat'] ) {
	case 'level':
	$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", tlo.training_level_phrase as "cat", \''.$qDate.'\'+ INTERVAL 3 MONTH - INTERVAL 1 DAY as "quarter_end",  \''.$qDate.'\'+ INTERVAL 3 MONTH as "next_quarter_start" FROM '.
	' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
	$sql .= ' LEFT JOIN training_level_option as tlo ON t.training_level_option_id = tlo.id ';
	break;
	case 'qualification':
	$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", pqo.qualification_phrase as "cat", \''.$qDate.'\'+ INTERVAL 3 MONTH - INTERVAL 1 DAY as "quarter_end",  \''.$qDate.'\'+ INTERVAL 3 MONTH as "next_quarter_start" FROM '.
	' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
	$sql .= '  JOIN person as p ON ptt.person_id = p.id ';
	$sql .= ' LEFT JOIN person_qualification_option as pqo ON p.primary_qualification_option_id = pqo.id ';
	break;
	case 'pepfar':
	$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", pfr.pepfar_category_phrase as "cat", \''.$qDate.'\'+ INTERVAL 3 MONTH - INTERVAL 1 DAY as "quarter_end",  \''.$qDate.'\'+ INTERVAL 3 MONTH as "next_quarter_start" FROM '.
	' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
	$sql .= ' LEFT JOIN training_to_training_pepfar_categories_option as tpfr ON t.id = tpfr.training_id ';
	$sql .= ' LEFT JOIN training_pepfar_categories_option as pfr ON tpfr.training_pepfar_categories_option_id = pfr.id ';
	break;
	}

	if ( $district_id =  $this->getSanParam('district_id')) {
	$sql .= '  JOIN training_location as tl ON t.training_location_id = tl.id AND tl.location_district_id = '.$district_id;
	}
	else if ( $province_id = $this->getSanParam('province_id') ) {
	$sql .= '  JOIN training_location as tl ON t.training_location_id = tl.id AND tl.location_province_id = '.$province_id;
	}

	$sql .= ' WHERE training_start_date >= \''.$qDate.'\'  AND training_start_date < \''.$qDate.'\'+ INTERVAL 3 MONTH ';
	$sql .=	 ' GROUP BY cat ORDER BY cat ASC ';

	$rowArray = $db->fetchAll($sql);

	//add a total row
	if ( !$rowArray ) { //we always want the next start date
	$sql = 'SELECT 0 as "cnt", \'total\' as "cat", \''.$qDate.'\'+ INTERVAL 3 MONTH - INTERVAL 1 DAY as "quarter_end",  \''.$qDate.'\'+ INTERVAL 3 MONTH as "next_quarter_start"';
	$rowArray = $db->fetchAll($sql);
	} else {
	$total = 0;
	foreach($rowArray as $row) {
	$total += $row['cnt'];
	}

	$rowArray []= array('cat'=>'total', 'cnt'=>$total);
	$grandTotal += $total;
	}


	$results[$qDate] = $rowArray;
	$qDate = $rowArray[0]['next_quarter_start'];

	}
	if ( $this->_getParam('outputType')  ) $this->sendData($results);
	}

	$this->view->assign('count',(isset($grandTotal)?$grandTotal:0) );
	$this->view->assign('results', $results);
	$this->view->assign('criteria',$criteria);
	}
	*/

	public function participantsByCategoryAction() {
		require_once ('views/helpers/Location.php');
		require_once ('views/helpers/DropDown.php');
		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0 ";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		$criteria ['end-year'] = date ( 'Y' );
		$criteria ['end-month'] = date ( 'm' );
		$criteria ['end-day'] = date ( 'd' );

		$criteria ['cat'] = $this->getSanParam ( 'cat' );
		$criteria ['training_organizer_option_id'] = $this->getSanParam ( 'training_organizer_option_id' );
		$criteria ['qualification_option_id'] = $this->getSanParam ( 'qualification_option_id' );
		$criteria ['gender'] = $this->getSanParam ( 'gender' );
		$criteria ['age_min'] = $this->getSanParam ( 'age_min' );
		$criteria ['age_max'] = $this->getSanParam ( 'age_max' );
		$criteria ['showGender'] = $this->getSanParam ( 'showGender' );
		$criteria ['showQualification'] = $this->getSanParam ( 'showQualification' );
		$criteria ['showAge'] = $this->getSanParam ( 'showAge' );

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		$qDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
		$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];

		//locations
		$locations = Location::getAll("2");

		$this->view->assign('locations', $locations);

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$rowArray = array ();
		if ($this->getSanParam ( 'go' )) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();

			$selectFields = array ();
			if ($criteria ['training_organizer_option_id']) {
				$selectFields []= 'training_organizer_phrase';
			}
			if ($criteria ['showQualification']) {
				$selectFields []= 'pqo.qualification_phrase';
			}
			if ($criteria ['showAge']) {
				$selectFields []= 'CASE WHEN p.birthdate IS NULL OR p.birthdate = \'0000-00-00\' THEN NULL ELSE ((date_format(now(),\'%Y\') - date_format(p.birthdate,\'%Y\')) - (date_format(now(),\'00-%m-%d\') < date_format(p.birthdate,\'00-%m-%d\')) ) END as "age" ';
			}
			if ($criteria ['showGender']) {
				$selectFields []= 'CASE WHEN p.gender IS NULL THEN \'na\' WHEN p.gender = \'\' THEN \'na\' ELSE p.gender END as "gender" ';
			}

			if ($selectFields) {
				$selectFields = ', ' . implode ( ',', $selectFields );
			} else {
				$selectFields = '';
			}



			switch ($criteria ['cat']) {
				case 'level' :
					$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", count(DISTINCT ptt.person_id) as "person_cnt", tlo.training_level_phrase as "cat" ' . $selectFields . ' FROM ' . ' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
					$sql .= '  JOIN person as p ON ptt.person_id = p.id ';
					$sql .= ' LEFT JOIN training_level_option as tlo ON t.training_level_option_id = tlo.id ';
				break;
				case 'qualification' :
					$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", count(DISTINCT ptt.person_id) as "person_cnt", pqo.qualification_phrase as "cat" ' . $selectFields . ' FROM ' . ' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
					$sql .= '  JOIN person as p ON ptt.person_id = p.id ';
					//$sql .= ' LEFT JOIN person_qualification_option as pqo ON p.primary_qualification_option_id = pqo.id ';


					// primary qualifications only
					$sql .= '
					LEFT JOIN person_qualification_option as pqo ON
							(p.primary_qualification_option_id = pqo.id AND pqo.parent_id IS NULL)
						OR
							(pqo.id = (SELECT parent_id FROM person_qualification_option WHERE id = p.primary_qualification_option_id LIMIT 1))';
				break;

				case 'pepfar' :
					$sql = 'SELECT count(DISTINCT ptt.id) as "cnt", count(DISTINCT ptt.person_id) as "person_cnt", pfr.pepfar_category_phrase as "cat" ' . $selectFields . ' FROM ' . ' person_to_training as ptt JOIN training as t ON ptt.training_id = t.id ';
					$sql .= '  JOIN person as p ON ptt.person_id = p.id ';
					$sql .= ' LEFT JOIN training_to_training_pepfar_categories_option as tpfr ON t.id = tpfr.training_id ';
					$sql .= ' LEFT JOIN training_pepfar_categories_option as pfr ON tpfr.training_pepfar_categories_option_id = pfr.id ';
				break;
			}

			if ($criteria['cat'] != 'qualification' AND ($criteria['qualification_option_id'] OR $criteria['showQualification'])) {
				$sql .= '
					LEFT JOIN person_qualification_option as pqo ON (
						(p.primary_qualification_option_id = pqo.id AND pqo.parent_id IS NULL)
						OR
						pqo.id = (SELECT parent_id FROM person_qualification_option WHERE id = p.primary_qualification_option_id LIMIT 1)
					)';

			}

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			if ($criteria ['district_id'] OR ! empty ( $criteria ['province_id'] ) OR ! empty ( $criteria ['region_c_id'] ) || $criteria['region_d_id'] || $criteria['region_e_id'] || $criteria['region_f_id'] || $criteria['region_g_id'] || $criteria['region_h_id'] || $criteria['region_i_id']) {
				$sql .= '  JOIN facility as f ON p.facility_id = f.id JOIN (' . $location_sub_query . ') as l ON  f.location_id = l.id ';
			}

			if ($criteria ['training_organizer_option_id']) {
				$sql .= '	JOIN training_organizer_option as torg ON torg.id = t.training_organizer_option_id ';
			}

			$sql .= ' WHERE training_start_date >= \'' . $qDate . '\'  AND training_start_date <= \'' . $endDate . '\' ';

			if ($criteria ['age_min']) {
				$sql .= ' AND "age" >= '.$criteria['age_min'];
			}
			if ($criteria ['age_max']) {
				$sql .= ' AND "age" <= '.$criteria['age_max'];
			}
			if ($criteria ['qualification_id']) {
				$sql .= ' AND (pqo.id = ' . $criteria ['qualification_id'] . ' OR pqo.parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
			if ($criteria ['training_gender']) {
				$sql .= ' AND pt.gender = \'' . $criteria ['training_gender'] . '\'';
			}

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			if ($org_allowed_ids = allowed_org_access_full_list($this)) { // doesnt have acl 'training_organizer_option_all'
				$sql .= " AND training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); { // for sites to host multiple training organizers on one domain
				$sql .= $site_orgs ? " AND training_organizer_option_id in ($site_orgs) " : "";
			}

			if ($locWhere = $this->getLocationCriteriaWhereClause($criteria)) {
				$sql .= ' AND ' . $locWhere;
			}

			if ($criteria ['training_organizer_option_id'][0] && is_array ( $criteria ['training_organizer_option_id'] )) {
				$sql .= ' AND t.training_organizer_option_id IN (' . implode ( ',', $criteria ['training_organizer_option_id'] ) . ')';
			}

			$sql .= ' GROUP BY ';
			if ($criteria ['showAge']) {
				$sql .= 'age,';
			}
			if ($criteria ['showQualification']) {
				$sql .= 'qualification_phrase,';
			}
			if ($criteria ['showGender']) {
				$sql .= 'gender,';
			}
			$sql .= ' cat ';

			if ($criteria ['training_organizer_option_id'] && is_array ( $criteria ['training_organizer_option_id'] )) {
				$sql .= ', t.training_organizer_option_id ';
			}

			$sql .= ' ORDER BY cat ASC ';

			$rowArray = $db->fetchAll ( $sql );

			//add a total row
			$total = 0;
			foreach ( $rowArray as $row ) {
				$total += $row ['cnt'];
			}

			if ($this->_getParam ( 'outputType' ))
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
		}

		$this->view->assign ( 'count', (isset ( $total ) ? $total : 0) );
		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'criteria', $criteria );

		//organizers
		$orgWhere = '';
		// restricted access?? only show trainings we have the ACL to view
		require_once('views/helpers/TrainingViewHelper.php');
		if ($org_allowed_ids = allowed_org_access_full_list($this)) { // doesnt have acl 'training_organizer_option_all'
			$orgWhere = " id in ($org_allowed_ids) ";
		}
		// restricted access?? only show organizers that belong to this site if its a multi org site		
		if ($site_orgs = allowed_organizer_in_this_site($this)) { // for sites to host multiple training organizers on one domain
			$orgWhere .= $orgWhere ? " AND id in ($site_orgs) " : " id in ($site_orgs) ";
		}

		$this->view->assign ( 'organizers_checkboxes', Checkboxes::generateHtml ( 'training_organizer_option', 'training_organizer_phrase', 
				$this->view, array(), $orgWhere) );
	//gnr
		$this->view->assign ( 'organizers_dropdown', DropDown::generateHtml ('training_organizer_option', 'training_organizer_phrase', 
				$criteria['training_organizer_option_id'], true, $this->view->viewonly, false,null,null,null,     true, 10 ) );

	}

	public function trainingByFacilityAction() {
		$this->view->assign ( 'mode', 'id' );
		$this->facilityReport ();
	}

	public function trainingByFacilityCountAction() {
		$this->view->assign ( 'mode', 'count' );
		$this->facilityReport ();
	}
	
	//TA:17:19 02/02/2015
	public function trainingByFacilityCount2Action() {
		$this->view->assign ( 'mode', 'count2' );
		$this->facilityReportCount2 ();
	}
	
	//TA:17:19 02/03/2015
	public function trainingByFacilityCount3Action() {
		$this->view->assign ( 'mode', 'count3' );
		$this->facilityReportCount2();
	}
	
	//TA:17:19 facility report by commodity (method)
	public function facilityReportCount2() {
	
		require_once ('models/table/TrainingLocation.php');
	
		$criteria = array ();
	
		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(date) as date FROM commodity";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['date'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];
	
		if ($this->getSanParam ( 'start-year' ))
			$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
			$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
			$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			$sql = "SELECT MIN(date) as date FROM commodity ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['date'];
			$parts = explode ( '-', $end_default );
			$criteria ['end-year'] = $parts [0];
			$criteria ['end-month'] = $parts [1];
			$criteria ['end-day'] = $parts [2];
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}
	
		if ($this->getSanParam ( 'end-year' ))
			$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
			$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
			$criteria ['end-day'] = $this->getSanParam ( 'end-day' );
	
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
	
		$criteria ['method_id'] = $this->getSanParam ( 'method_id' );//TA:17:19
		$criteria ['facility_type_id'] = $this->getSanParam ( 'facility_type_id' );
		$criteria ['training_title_id'] = $this->getSanParam ( 'training_title_id' );
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		$criteria ['doCount2'] = ($this->view->mode == 'count2');//TA:17:19 02/02/2015
		$criteria ['doCount3'] = ($this->view->mode == 'count3');//TA:17:19 02/02/2015
		$criteria ['showProvince'] = ($this->getSanParam ( 'showProvince' ) );
		$criteria ['showDistrict'] = ($this->getSanParam ( 'showDistrict' ) );
		$criteria ['showRegionC'] = ($this->getSanParam ( 'showRegionC' ) );
		$criteria ['showMethod'] = ($this->getSanParam ( 'showMethod' ));
		$criteria ['showType'] = ($this->getSanParam ( 'showType' ) );
		$criteria ['showFacility'] =       true;
		$criteria ['reportedFacility'] = ($this->getSanParam ( 'reportedFacility' ) );
		
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_id'] or $criteria ['training_title_id'] === '0' or $criteria ['training_title_id'])));
		
	
		if ($criteria ['go']) {
	
			$sql = 'SELECT distinct(facility.id) as facility_id, facility_name ';
			
			if ($criteria ['showDistrict']) {
				$sql .= ', l.district_name ';
			}
			if ($criteria ['showProvince']) {
				$sql .= ', l.province_name ';
			}
			if ($criteria ['showRegionC']) {
				$sql .= ', l.region_c_name ';
			}
				
			if ($criteria ['showMethod']) {
				$sql .= ', fc.commodity_name, fc.consumption_count ';
			}
	
			if ($criteria ['showType'] && $criteria ['facility_type_id']) {
				$sql .= ', fto.facility_type_phrase ';
			}
			
			if($criteria ['showTrainingTitle'] && $criteria ['training_title_id']){
				$sql .= ', training_title_option.training_title_phrase ';
			}
	
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);
	
			$sql .= " FROM facility ";
			
			if ($criteria ['reportedFacility']) {
				$sql .= '	JOIN facility_report_rate ON facility.external_id = facility_report_rate.facility_external_id ';
			}
			
			if ($criteria ['showType'] && $criteria ['facility_type_id']) {
				$sql .= '	JOIN facility_type_option as fto ON fto.id = facility.type_option_id ';
			}
			
			if ($criteria ['showTrainingTitle'] && $criteria ['training_title_id']) {
				$sql .= '	JOIN person ON facility.id = person.facility_id ';
				$sql .= '	JOIN person_to_training ON person.id = person_to_training.person_id ';
				$sql .= '	JOIN training ON training.id = person_to_training.training_id ';
				$sql .= '	JOIN training_title_option ON training_title_option.id = training.id ';
			}
			
			$sql .= "LEFT JOIN (" .$location_sub_query. ") as l ON facility.location_id = l.id ";
				
			if ($criteria ['showMethod']) {
				$sql .= " join (select facility.id as fid, commodity.name_id, commodity_name_option.commodity_name as commodity_name, commodity.date,  
						sum(CASE WHEN commodity.consumption > 0 THEN commodity.consumption ELSE 0 END) as consumption_count from facility
				join commodity on commodity.facility_id = facility.id
				join commodity_name_option on commodity_name_option.id = commodity.name_id " ;
				if($criteria ['method_id']){
					$sql .= " where commodity.name_id IN (" . $this->_trainsmart_implode($criteria ['method_id']) . ") ";
				}
				$sql .= " and date between ";
				if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
					$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
					$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
					$sql .=  "'" . $startDate . "' AND '" . $endDate . "' ";
				} 
				$sql .= " group by fid order by date) as fc on fc.fid = facility.id ";
			}
	
			$where = array(' facility.is_deleted=0 ');
			$where = array(' l.province_name is not null ');
	
			if ($criteria ['facilityInput']) {
				$where []= ' facility.id = \'' . $criteria ['facilityInput'] . '\'';
			}
	
			if ($criteria ['facility_type_id'] or $criteria ['facility_type_id'] === '0') {
				$where []= ' facility.type_option_id = \'' . $criteria ['facility_type_id'] . '\'';
			}
			
			if ($criteria ['showTrainingTitle'] && $criteria ['training_title_id']) {
				$where []= ' training_title_option.id IN (' . $this->_trainsmart_implode($criteria ['training_title_id']) . ')';
			}
			
			if ($criteria ['showMethod']) {
				$where []= ' fc.consumption_count>0 ';
			}else if($criteria ['doCount2']){
				if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
					$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
					$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				}
				$where []= " date between '" . $startDate . "' AND '" . $endDate . "' ";
			}
	
			if ($where)
				$sql .= ' WHERE ' . implode(' AND ', $where);
	
			$sql .= ' order by facility_name ';
			
			$rowArray = $db->fetchAll ( $sql);
			$count = count ( $rowArray );
	
			if ($this->_getParam ( 'outputType' ))
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
	
		} else {
			$count = 0;
			$rowArray = array ();
		}
	
		$criteria ['go'] = $this->getSanParam ( 'go' );
	
		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );
	
		//facilities list
		$fArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $fArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	
		//locations

		$locations = Location::getAll("2");

		$this->viewAssignEscaped ( 'locations', $locations );
		//facility types
		$typesArray = OptionList::suggestionList ( 'facility_type_option', 'facility_type_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_types', $typesArray );
		
		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );		
	
		//location
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
	
		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	
		//TA:17:19 01/30/2015 Methods (=commodity names)
		$methodsArray = OptionList::suggestionList ( 'commodity_name_option', 'commodity_name', false, false, false );
		$this->viewAssignEscaped ( 'methods', $methodsArray );
	
	}

	public function facilityReport() {

		require_once ('models/table/TrainingLocation.php');

		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			$sql = "SELECT MAX(training_start_date) as \"start\" FROM training ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['start'];
			$parts = explode ( '-', $end_default );
			$criteria ['end-year'] = $parts [0];
			$criteria ['end-month'] = $parts [1];
			$criteria ['end-day'] = $parts [2];
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}

		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['training_title_id'] = $this->getSanParam ( 'training_title_id' );
		$criteria ['training_location_id'] = $this->getSanParam ( 'training_location_id' );
		$criteria ['training_organizer_id'] = $this->getSanParam ( 'training_organizer_id' );
		$criteria ['method_id'] = $this->getSanParam ( 'method_id' );//TA:17:19
		$criteria ['training_pepfar_id'] = $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_topic_id'] = $this->getSanParam ( 'training_topic_id' );
		$criteria ['training_level_id'] = $this->getSanParam ( 'training_level_id' );
		$criteria ['facility_type_id'] = $this->getSanParam ( 'facility_type_id' );
		$criteria ['facility_sponsor_id'] = $this->getSanParam ( 'facility_sponsor_id' );
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['is_tot'] = $this->getSanParam ( 'is_tot' );

		$criteria ['go'] = $this->getSanParam ( 'go' );
		$criteria ['doCount'] = ($this->view->mode == 'count');
		$criteria ['id'] = ($this->view->mode == 'id');
		//$criteria ['doCount2'] = ($this->view->mode == 'count2');//TA:17:19 02/02/2015
		$criteria ['showProvince'] = ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or $criteria ['province_id'] === '0')));
		$criteria ['showDistrict'] = ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or $criteria ['district_id'] === '0')));
		$criteria ['showRegionC'] = ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or $criteria ['region_c_id'] === '0')));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0' or $criteria ['training_title_id'])));
		$criteria ['showLocation'] = ($this->getSanParam ( 'showLocation' ) or ($criteria ['doCount'] and $criteria ['training_location_id']));
		$criteria ['showOrganizer'] = ($this->getSanParam ( 'showOrganizer' ) or ($criteria ['doCount'] and ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0')));
		//TA:17:19 01/30/2015
		$criteria ['showMethod'] = ($this->getSanParam ( 'showMethod' ) or ($criteria ['doCount'] and ($criteria ['method_id'])));
		$criteria ['showPepfar'] = ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0')));
		$criteria ['showTopic'] = ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showLevel'] = ($this->getSanParam ( 'showLevel' ) or ($criteria ['doCount'] and $criteria ['training_level_id']));
		$criteria ['showType'] = ($this->getSanParam ( 'showType' ) or ($criteria ['doCount'] and ($criteria ['facility_type_id'] or $criteria ['facility_type_id'] === '0')));
		$criteria ['showSponsor'] = ($this->getSanParam ( 'showSponsor' ) or ($criteria ['doCount'] and $criteria ['facility_sponsor_id']));
		$criteria ['showFacility'] =       true;
		$criteria ['showTot'] = ($this->getSanParam ( 'showTot' ) or ($criteria ['doCount'] and $criteria ['is_tot'] !== '' or $criteria ['is_tot'] === '0'));
		$criteria ['age_min'] =            $this->getSanParam ( 'age_min' );
		$criteria ['age_max'] =            $this->getSanParam ( 'age_max' );
		$criteria ['training_gender']    = $this->getSanParam ( 'training_gender' );
		$criteria ['qualification_id']   = $this->getSanParam ( 'qualification_id' );
		$criteria ['showAge']            = ( $this->getSanParam ( 'showAge' )    or ($criteria ['doCount'] and ($criteria['age_min'] or $criteria['age_max'])) );
		$criteria ['showGender']         = ( $this->getSanParam ( 'showGender' ) or ($criteria ['doCount'] and $criteria ['training_gender']) );
		$criteria ['showQual']           = ( $this->getSanParam ( 'showQual' )   or ($criteria ['doCount'] and $criteria ['qualification_id']) );
		
		if ($criteria ['go']) {

			$sql = 'SELECT '; //todo test

			if ($criteria ['doCount']) {
				$sql .= ' COUNT(pt.person_id) as "cnt", pt.facility_name ';
			} else {
				$sql .= ' DISTINCT pt.id as "id", pt.facility_id, pt.facility_name, pt.training_start_date  ';
			}
			if ($criteria ['showFacility']) {
				$sql .= ', pt.facility_name ';
			}

			if ($criteria ['showTrainingTitle'] or ($this->view->mode == 'search')) {
				$sql .= ', pt.training_title ';
			}
			if ($criteria ['showDistrict']) {
				$sql .= ', pt.district_name, pt.district_id ';
			}
			if ($criteria ['showProvince']) {
				$sql .= ', pt.province_name, pt.province_id ';
			}
			if ($criteria ['showRegionC']) {
				$sql .= ', pt.region_c_name, pt.region_c_id ';
			}
			if ($criteria ['showLocation']) {
				$sql .= ', pt.training_location_name ';
			}

			if ($criteria ['showOrganizer']) {
				$sql .= ', torg.training_organizer_phrase ';
			}
			
			//TA:17:19
			if ($criteria ['showMethod']) {
				$sql .= ', cf.commodity_name ';
			}

			if ($criteria ['showLevel']) {
				$sql .= ', tlev.training_level_phrase ';
			}

			if ($criteria ['showType']) {
				$sql .= ', fto.facility_type_phrase ';
			}

			if ($criteria ['showSponsor']) {
				$sql .= ', fso.facility_sponsor_phrase ';
			}

			if ($criteria ['showPepfar']) {
				if ($criteria ['doCount']) {
					$sql .= ', tpep.pepfar_category_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT tpep.pepfar_category_phrase) as "pepfar_category_phrase" ';
				}
			}

			if ($criteria ['showTopic']) {
				if ($criteria ['doCount']) {
					$sql .= ', ttopic.training_topic_phrase ';
				} else {
					$sql .= ', GROUP_CONCAT(DISTINCT ttopic.training_topic_phrase ORDER BY training_topic_phrase) AS "training_topic_phrase" ';
				}
			}

			if ($criteria ['showTot']) {
				//$sql .= ', pt.is_tot ';
				$sql .= ", IF(pt.is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot";
			}
			if ($criteria ['showAge']) {
				$sql .= ', CASE WHEN birthdate  IS NULL OR birthdate = \'0000-00-00\' THEN NULL ELSE ((date_format(now(),\'%Y\') - date_format(birthdate,\'%Y\')) - (date_format(now(),\'00-%m-%d\') < date_format(birthdate,\'00-%m-%d\')) ) END as "age" ';
			}
			if ($criteria ['showGender']) {
				$sql .= ' , CASE WHEN gender IS NULL THEN \'na\' WHEN gender = \'\' THEN \'na\' ELSE gender END as "gender" ';
			}
			if ($criteria ['showQual']) {
				$sql .= ', qualification_phrase ';
			}

			//JOIN with the participants to get facility

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			if ($criteria ['doCount']) {
				$sql .= ' FROM (SELECT training.*, fac.person_id as "person_id", fac.facility_id as "facility_id", fac.type_option_id, fac.sponsor_option_id, fac.facility_name as "facility_name" , tto.training_title_phrase AS training_title,training_location.training_location_name,birthdate,gender,qualification_phrase, l.'.implode(', l.',$field_name).'
				       FROM training
				         JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id)
				         JOIN training_location ON training.training_location_id = training_location.id
				         JOIN (SELECT person_id, facility_name, facility_id, location_id, type_option_id, sponsor_option_id,training_id,birthdate,gender,qualification_phrase
					            FROM person
				               JOIN person_to_training ON person_to_training.person_id = person.id
				               JOIN facility as f ON person.facility_id = f.id
				               JOIN person_qualification_option qual ON qual.id = person.primary_qualification_option_id
				               ) as fac ON training.id = fac.training_id
				         LEFT JOIN ('.$location_sub_query.') as l ON fac.location_id = l.id WHERE training.is_deleted=0) as pt ';
			} else {
				$sql .= ' FROM (SELECT training.*, fac.facility_id as "facility_id", fac.type_option_id, fac.sponsor_option_id ,fac.facility_name as "facility_name" , tto.training_title_phrase AS training_title,training_location.training_location_name, l.'.implode(', l.',$field_name).
				'       FROM training  ' .
				'         JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id) ' .
				'         JOIN training_location ON training.training_location_id = training_location.id ' .
				'         JOIN (SELECT DISTINCT facility_name, facility_id, location_id, training_id, type_option_id, sponsor_option_id FROM person JOIN person_to_training ON person_to_training.person_id = person.id '.
				'         LEFT JOIN facility as f ON person.facility_id = f.id) as fac ON training.id = fac.training_id LEFT JOIN ('.$location_sub_query.') as l ON fac.location_id = l.id  WHERE training.is_deleted=0) as pt ';
			}

			if ($criteria ['showOrganizer']) {
				$sql .= '	JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';
			}
			
			//TA:17:19
			if ($criteria ['showMethod']) {
				$sql .= "	join (select distinct facility.id as fid, commodity.name_id, commodity_name_option.commodity_name as commodity_name from facility " .
				"join commodity on commodity.facility_id = facility.id " .
				"join commodity_name_option on commodity_name_option.id = commodity.name_id " ;
				if($criteria ['method_id']){
					$sql .= "where commodity.name_id=" . $criteria ['method_id'];
				}
				$sql .= ") as cf on cf.fid = pt.facility_id ";
			}
			
			if ($criteria ['showLevel']) {
				$sql .= '	JOIN training_level_option as tlev ON tlev.id = pt.training_level_option_id ';
			}

			if ($criteria ['showType']) {
				$sql .= '	JOIN facility_type_option as fto ON fto.id = pt.type_option_id ';
			}

			if ($criteria ['showSponsor']) {
				$sql .= '	JOIN facility_sponsor_option as fso ON fso.id = pt.sponsor_option_id ';
			}

			if ($criteria ['showPepfar']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if ($criteria ['showTopic']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
			}

			$where = array(' pt.is_deleted=0 ');

			if ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				$where []= ' pt.training_title_option_id = ' . $criteria ['training_title_option_id'];
			}

			if ($criteria ['training_title_id'] or $criteria ['training_title_id'] === '0') {
				$where []= ' pt.training_title_option_id = ' . $criteria ['training_title_id'];
			}

			if ($criteria ['facilityInput']) {
				$where []= ' pt.facility_id = \'' . $criteria ['facilityInput'] . '\'';
			}

			if ($criteria ['training_location_id']) {
				$where []= ' pt.training_location_id = \'' . $criteria ['training_location_id'] . '\'';
			}

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0') {
				$where []= ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				$where []= ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}

			if ($criteria ['facility_type_id'] or $criteria ['facility_type_id'] === '0') {
				$where []= ' pt.type_option_id = \'' . $criteria ['facility_type_id'] . '\'';
			}
			if ($criteria ['facility_sponsor_id'] or $criteria ['facility_sponsor_id'] === '0') {
				$where []= ' pt.sponsor_option_id = \'' . $criteria ['facility_sponsor_id'] . '\'';
			}

			if ($criteria ['training_level_id']) {
				$where []= ' pt.training_level_option_id = \'' . $criteria ['training_level_id'] . '\'';
			}

			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0') {
				$where []= ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where []= ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			if ($criteria ['is_tot'] or $criteria ['is_tot'] === '0') {
				$where []= ' pt.is_tot = ' . $criteria ['is_tot'];
			}

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where []= " training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)
				$where []= " training_organizer_option_id in ($site_orgs) ";

			if ($where)
			$sql .= ' WHERE ' . implode(' AND ', $where);

			if ($criteria ['doCount'] || $criteria ['doCount2']) {

				$groupBy = array();
				if ($criteria ['showFacility'])      $groupBy []= '  pt.facility_id';
				if ($criteria ['showTrainingTitle']) $groupBy []= ' pt.training_title_option_id';
				if ($criteria ['showProvince'])      $groupBy []= ' pt.province_id';
				if ($criteria ['showDistrict'])      $groupBy []= '  pt.district_id';
				if ($criteria ['showRegionC'])       $groupBy []= '  pt.region_c_id';
				if ($criteria ['showLocation'])      $groupBy []= '  pt.training_location_id';
				if ($criteria ['showOrganizer'])     $groupBy []= '  pt.training_organizer_option_id';
				if ($criteria ['showTopic'])         $groupBy []= '  ttopic.training_topic_option_id';
				if ($criteria ['showLevel'])         $groupBy []= '  pt.training_level_option_id';
				if ($criteria ['showPepfar'])        $groupBy []= '  tpep.training_pepfar_categories_option_id';
				if ($criteria ['showType'])          $groupBy []= '  pt.type_option_id';
				if ($criteria ['showSponsor'])       $groupBy []= '  pt.sponsor_option_id';
				if ($criteria ['showTot'])           $groupBy []= '  pt.is_tot';
				if ( $criteria['showAge'])           $groupBy []= '  age ';
				if ($criteria ['showGender'])        $groupBy []= '  pt.gender';
				if ($criteria ['showQual'])          $groupBy []= '  pt.qualification_phrase ';
				//if ($criteria ['doCount2'])          $groupBy []= '  pt.facility_id ';//TA:17:19


				if ($groupBy)
				$groupBy = ' and pt.province_id is not null GROUP BY ' . implode(', ',$groupBy);
				$sql .= $groupBy;
			} else {
				if ($criteria ['showPepfar'] || $criteria ['showTopic']) {
					$sql .= ' and pt.province_id is not null GROUP BY pt.id';
				}
			}
			
			
			$rowArray = $db->fetchAll ( $sql . ' and pt.province_id is not null ORDER BY facility_name ASC ' );

			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
			}

			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );

		//not sure why we are getting multiple PEPFARS
		foreach ( $rowArray as $key => $row ) {
			if (isset ( $row ['pepfar_category_phrase'] )) {
				$rowArray [$key] ['pepfar_category_phrase'] = implode ( ',', array_unique ( explode ( ',', $row ['pepfar_category_phrase'] ) ) );
			}
		}

		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );

		//facilities list
		$fArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $fArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );

		//locations

		$locations = Location::getAll("2");

		$this->viewAssignEscaped ( 'locations', $locations );
		//facility types
		$typesArray = OptionList::suggestionList ( 'facility_type_option', 'facility_type_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_types', $typesArray );
		//sponsor types
		$sponsorsArray = OptionList::suggestionList ( 'facility_sponsor_option', 'facility_sponsor_phrase', false, false );
		$this->viewAssignEscaped ( 'facility_sponsors', $sponsorsArray );

		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );

		//location
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		//organizers
		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, false, false );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//levels
		$levelArray = OptionList::suggestionList ( 'training_level_option', 'training_level_phrase', false, false );
		$this->viewAssignEscaped ( 'levels', $levelArray );
		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );

		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );

		// qualifications
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false, array ('0 AS is_default', 'child.is_default' ) );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		
		//TA:17:19 01/30/2015 Methods (=commodity names)
		$methodsArray = OptionList::suggestionList ( 'commodity_name_option', 'commodity_name', false, false, false );
		$this->viewAssignEscaped ( 'methods', $methodsArray );

	}

	//
	// Recommended Classes
	//


	public function needsReport() {
		require_once ('models/table/TrainingRecommend.php');
		require_once ('models/table/TrainingTitleOption.php');

		$criteria = array ();

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = '0000-00-00';
		if ($rowArray and $rowArray [0] ['start'])
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		$criteria ['end-year'] = date ( 'Y' );
		$criteria ['end-month'] = date ( 'm' );
		$criteria ['end-day'] = date ( 'd' );
		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);

		$criteria ['training_gender'] = $this->getSanParam ( 'training_gender' );
		$criteria ['training_active'] = $this->getSanParam ( 'training_active' );
		$criteria ['concatNames'] = $this->getSanParam ( 'concatNames' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['training_title_id'] = $this->getSanParam ( 'training_title_id' );
		$criteria ['course_recommend_id'] = $this->getSanParam ( 'course_recommend_id' );
		$criteria ['training_pepfar_id'] = $this->getSanParam ( 'training_pepfar_id' );
		$criteria ['training_topic_id'] = $this->getSanParam ( 'training_topic_id' );
		$criteria ['training_topic_recommend_id'] = $this->getSanParam ( 'training_topic_recommend_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['qualification_secondary_id'] = $this->getSanParam ( 'qualification_secondary_id' );
		$criteria ['upcoming_id'] = $this->getSanParam ( 'upcoming_id' );
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['first_name'] = $this->getSanParam ( 'first_name' );
		$criteria ['last_name'] = $this->getSanParam ( 'last_name' );

		$criteria ['doCount'] = ($this->view->mode == 'count');
		$criteria ['showProvince'] = ($this->getSanParam ( 'showProvince' ) or ($criteria ['doCount'] and ($criteria ['province_id'] or $criteria ['province_id'] === '0')));
		$criteria ['showDistrict'] = ($this->getSanParam ( 'showDistrict' ) or ($criteria ['doCount'] and ($criteria ['district_id'] or $criteria ['district_id'] === '0')));
		$criteria ['showRegionC'] = ($this->getSanParam ( 'showRegionC' ) or ($criteria ['doCount'] and ($criteria ['region_c_id'] or $criteria ['region_c_id'] === '0')));
		$criteria ['showTrainingTitle'] = ($this->getSanParam ( 'showTrainingTitle' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0' or $criteria ['training_title_id'])));
		$criteria ['showPepfar'] = ($this->getSanParam ( 'showPepfar' ) or ($criteria ['doCount'] and ($criteria ['training_title_option_id'] or $criteria ['training_pepfar_id'] === '0' or $criteria ['training_title_id'])));
		$criteria ['showQualPrim'] = ($this->getSanParam ( 'showQualPrim' ) or ($criteria ['doCount'] and ($criteria ['qualification_id'] or $criteria ['qualification_id'] === '0')));
		$criteria ['showQualSecond'] = ($this->getSanParam ( 'showQualSecond' ) or ($criteria ['doCount'] and ($criteria ['qualification_secondary_id'] or $criteria ['qualification_secondary_id'] === '0')));
		$criteria ['showTopic'] = ($this->getSanParam ( 'showTopic' ) or ($criteria ['doCount'] and ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')));
		$criteria ['showTopicRecommend'] = ($this->getSanParam ( 'showTopicRecommend' ) or ($criteria ['doCount'] and ($criteria ['training_topic_recommend_id'] or $criteria ['training_topic_recommend_id'] === '0')));
		$criteria ['showCourseRecommend'] = ($this->getSanParam ( 'showCourseRecommend' ) or ($criteria ['doCount'] and ($criteria ['course_recommend_id'] or $criteria ['course_recommend_id'] === '0')));
		$criteria ['showFacility'] = ($this->getSanParam ( 'showFacility' ) or ($criteria ['doCount'] and $criteria ['facility_name']));
		$criteria ['showGender'] = ($this->getSanParam ( 'showGender' ) or ($criteria ['doCount'] and $criteria ['training_gender']));
		$criteria ['showActive'] = ($this->getSanParam ( 'showActive' ) or ($criteria ['doCount'] and $criteria ['training_active']));
		$criteria ['showEmail'] = ($this->getSanParam ( 'showEmail' ));
		$criteria ['showPhone'] = ($this->getSanParam ( 'showPhone' ));
		$criteria ['showClasses'] = ($this->getSanParam ( 'showPhone' ));
		$criteria ['showUpcoming'] = ($this->getSanParam ( 'showUpcoming' ));

		$criteria ['showFirstName'] = ($this->getSanParam ( 'showFirstName' ));
		$criteria ['showLastName'] = ($this->getSanParam ( 'showLastName' ));

		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {

			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id, true);

			$sql = 'SELECT ';

			if ($criteria ['doCount']) {
				$sql .= ' COUNT(pt.person_id) as "cnt" ';
			} else {
				if ($criteria ['concatNames'])
				$sql .= ' DISTINCT pt.person_id as "id", CONCAT(first_name, ' . "' '" . ',last_name, ' . "' '" . ', IFNULL(suffix_phrase, ' . "' '" . ')) as "name", IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, pt.training_start_date  ';
				else
				$sql .= ' DISTINCT pt.person_id as "id", IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, last_name, first_name, middle_name, pt.training_start_date  ';
			}
			if ($criteria ['showPhone']) {
				$sql .= ", CASE WHEN (pt.phone_work IS NULL OR pt.phone_work = '') THEN NULL ELSE pt.phone_work END as \"phone_work\", CASE WHEN (pt.phone_home IS NULL OR pt.phone_home = '') THEN NULL ELSE pt.phone_home END as \"phone_home\", CASE WHEN (pt.phone_mobile IS NULL OR pt.phone_mobile = '') THEN NULL ELSE pt.phone_mobile END as \"phone_mobile\" ";
			}
			if ($criteria ['showEmail']) {
				$sql .= ', pt.email ';
			}
			if ($criteria ['showGender']) {
				$sql .= ', pt.gender ';
			}
			if ($criteria ['showActive']) {
				$sql .= ', pt.active ';
			}
			if ($criteria ['showTrainingTitle']) {
				$sql .= ', pt.training_title, pt.training_title_option_id '; // already GROUP_CONCAT'ed in main SQL
			}
			if ($criteria ['showDistrict']) {
				$sql .= ', pt.district_name ';
			}
			if ($criteria ['showProvince']) {
				$sql .= ', pt.province_name ';
			}
			if ($criteria ['showRegionC']) {
				$sql .= ', pt.region_c_name ';
			}

			if ($criteria ['showPepfar']) {
				$sql .= ', tpep.pepfar_category_phrase ';
			}

			if ($criteria ['showTopic']) {
				$sql .= ', ttopic.training_topic_phrase ';
			}

			if ($criteria ['showFacility']) {
				$sql .= ', pt.facility_name ';
			}

			if ($criteria ['showQualPrim']) {
				$sql .= ', pq.qualification_phrase ';
			}
			if ($criteria ['showQualSecond']) {
				$sql .= ', pqs.qualification_phrase AS qualification_secondary_phrase';
			}

			// NOT USED! (recommended topics are, though)
			if ((! $criteria ['doCount']) and $criteria ['showUpcoming']) {
				//$sql .= ', precommend.training_title_phrase AS recommended';
				$sql .= ", GROUP_CONCAT(DISTINCT CONCAT(recommend.training_title_phrase ) ORDER BY training_title_phrase SEPARATOR ', ') AS upcoming ";

			}

			if ($criteria ['showTopicRecommend']) {
				//$sql .= ', ptopic.training_topic_phrase AS recommended ';


				$sql .= ", GROUP_CONCAT(DISTINCT CONCAT(ptopic.training_topic_phrase) ORDER BY training_topic_phrase SEPARATOR ', ') AS recommended ";

				// same training location? --  AND t.training_location_id = pt.training_location_id


			}

			// select everyone, not just participants
			$sql .= ' FROM (
			SELECT training.*, person.facility_id as "facility_id", person.id as "person_id", person.last_name, IFNULL(suffix_phrase, ' . "' '" . ') as suffix_phrase, person.first_name, person.middle_name, person.phone_work, person.phone_home, person.phone_mobile, person.email, CASE WHEN person.active = \'deceased\' THEN \'inactive\' ELSE person.active END as "active", CASE WHEN person.gender IS NULL THEN \'na\' WHEN person.gender = \'\' THEN \'na\' ELSE person.gender END as "gender",
			primary_qualification_option_id,facility.facility_name, l.'.implode(', l.',$field_name).
			', GROUP_CONCAT(DISTINCT CONCAT(training_title_phrase) ORDER BY training_title_phrase SEPARATOR \', \') AS training_title
			FROM person
			LEFT JOIN person_to_training ON person.id = person_to_training.person_id
			LEFT JOIN training ON training.id = person_to_training.training_id
			LEFT JOIN facility ON person.facility_id = facility.id
			LEFT JOIN ('.$location_sub_query.') as l ON facility.location_id = l.id
			LEFT JOIN training_title_option tto ON `training`.training_title_option_id = tto.id
			LEFT  JOIN person_suffix_option suffix ON person.suffix_option_id = suffix.id
			GROUP BY person.id
			) as pt ';

			if ($criteria ['showPepfar']) {
				$sql .= '	JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';
			}

			if ($criteria ['showTopic']) {
				$sql .= '	LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';
			}
			// Recommended classes
			if ((! $criteria ['doCount']) and ($criteria ['showUpcoming'] or $criteria ['upcoming_id'])) {
				// not tested
				$sql .= ($criteria ['upcoming_id'] ? "INNER" : "LEFT") . " JOIN (SELECT training_title_phrase, person_id
				FROM person_to_training_topic_option as ptto
				JOIN training_to_training_topic_option ttt ON (ttt.training_topic_option_id = ptto.training_topic_option_id)
				JOIN training t ON (t.id = ttt.training_id)
				JOIN training_title_option tt ON (tt.id = t.training_title_option_id)
				WHERE
				t.is_deleted = 0 AND tt.training_title_phrase != 'unknown' AND t.training_start_date > NOW()
				" . (($criteria ['upcoming_id']) ? ' AND t.training_title_option_id = ' . $criteria ['upcoming_id'] : '') . "
				) AS recommend ON recommend.person_id = pt.person_id ";

				//$sql .= ' JOIN person_to_training_topic_option ptto ON ptto.person_id = pt.person_id';
			}

			if ($criteria ['showTopicRecommend'] or $criteria ['training_topic_recommend_id'] or $criteria ['training_topic_id'] or ($criteria ['training_topic_id'] === '0')) {

				$sql .= '
				INNER JOIN (
				SELECT person_id, topicid.id, training_topic_phrase
				FROM person_to_training_topic_option ptto
				INNER JOIN training_topic_option topicid ON (topicid.id = ptto.training_topic_option_id)
				) AS ptopic ON ptopic.person_id = pt.person_id
				';


			}
			if ($criteria ['showQualPrim'] || $criteria ['showQualSecond'] || $criteria ['qualification_id'] || $criteria ['qualification_secondary_id']) {
				// primary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pq ON (
				(pt.primary_qualification_option_id = pq.id AND pq.parent_id IS NULL)
				OR
				pq.id = (SELECT parent_id FROM person_qualification_option WHERE id = pt.primary_qualification_option_id LIMIT 1)
				)';

				// secondary qualifications
				$sql .= '
				LEFT JOIN person_qualification_option as pqs ON (
				pt.primary_qualification_option_id = pqs.id AND pqs.parent_id IS NOT NULL
				)';
			}

			$where = '';

			// legacy
			if ($criteria ['training_title_option_id'] or $criteria ['training_title_option_id'] === '0') {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pt.training_title_option_id = ' . $criteria ['training_title_option_id'];
			}

			if ($criteria ['training_title_id'] or $criteria ['training_title_id'] === '0') {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pt.training_title_option_id = ' . $criteria ['training_title_id'];
			}

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0') {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';
			}

			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0') {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';
			}

			if ($criteria ['facilityInput']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pt.facility_id = \'' . $criteria ['facilityInput'] . '\'';
			}

			if ($criteria ['training_gender']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pt.gender = \'' . $criteria ['training_gender'] . '\'';
			}

			if ($criteria ['training_active']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pt.active = \'' . $criteria ['training_active'] . '\'';
			}

			if ($criteria ['qualification_id']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' (pq.id = ' . $criteria ['qualification_id'] . ' OR pqs.parent_id = ' . $criteria ['qualification_id'] . ') ';
			}
			if ($criteria ['qualification_secondary_id']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= ' pqs.id = ' . $criteria ['qualification_secondary_id'];
			}

			if ($criteria ['training_topic_recommend_id']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= '  ptopic.id = ' . $criteria ['training_topic_recommend_id'];
			}
			if ($criteria ['first_name']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= " first_name = '" . mysql_escape_string ( $criteria ['first_name'] ) . "'";
			}
			if ($criteria ['last_name']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$where .= " last_name = '" . mysql_escape_string ( $criteria ['last_name'] ) . "'";
			}

			if (intval ( $criteria ['end-year'] ) and $criteria ['start-year']) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where .= ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where .= " AND training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			$where .= $site_orgs ? " AND training_organizer_option_id in ($site_orgs) " : "";

			if ($where)
			$sql .= ' WHERE ' . $where;

			if ($criteria ['doCount']) {

				$groupBy = '';

				if ($criteria ['showTrainingTitle']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= ' pt.training_title_option_id';
				}
				if ($criteria ['showGender']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= ' pt.gender';
				}
				if ($criteria ['showActive']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= ' pt.active';
				}
				if ($criteria ['showProvince']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= ' pt.province_id';
				}
				if ($criteria ['showDistrict']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  pt.district_id';
				}
				if (isset ( $criteria ['showLocation'] ) and $criteria ['showLocation']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  pt.training_location_id';
				}
				if ($criteria ['showTopic']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  ttopic.training_topic_option_id';
				}

				if ($criteria ['showTopicRecommend']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  ptopic.id';
				}

				if ($criteria ['showQualPrim'] and ! $criteria ['showQualSecond']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  pq.id'; //added ToddW 090827
				} else if ($criteria ['showQualPrim'] || $criteria ['showQualSecond']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  pt.primary_qualification_option_id';
				}

				/*
				if ( $criteria['showQualPrim']) {
				if ( strlen($groupBy) ) $groupBy .= ' , ';
				//$groupBy .=	'  pq.id ';
				}
				if ( $criteria['showQualSecond']) {
				if ( strlen($groupBy) ) $groupBy .= ' , ';
				//$groupBy .=	'  pqs.id ';
				}
				*/

				if ($criteria ['showPepfar']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  tpep.training_pepfar_categories_option_id';
				}

				if ($criteria ['showFacility']) {
					if (strlen ( $groupBy ))
					$groupBy .= ' , ';
					$groupBy .= '  pt.facility_id';
				}

				if ($groupBy != '')
				$groupBy = ' GROUP BY ' . $groupBy;
				$sql .= $groupBy;
			} else {
				//if ( $criteria['showTopicRecommend'] || $criteria['showCourseRecommend']) {
				$sql .= ' GROUP BY pt.person_id, pt.id'; //added ToddW 090827 -- always group by person
				//}
			}

			//echo $sql; exit;


			$rowArray = $db->fetchAll ( $sql );

			if ($criteria ['doCount']) {
				$count = 0;
				foreach ( $rowArray as $row ) {
					$count += $row ['cnt'];
				}
			} else {
				$count = count ( $rowArray );
				//cheezy
				//get the count of people, now group by topic and run the query again
				//so we get a line for each topic
				if ($criteria ['showTopicRecommend']) {
					$sql .= ',ptopic.training_topic_phrase';
					$rowArray = $db->fetchAll ( $sql );
				}
			}
			if ($this->_getParam ( 'outputType' ))
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

		} else {
			$count = 0;
			$rowArray = array ();
		}

		$criteria ['go'] = $this->getSanParam ( 'go' );

		$this->viewAssignEscaped ( 'results', $rowArray );
		if ($rowArray) {
			$first = reset ( $rowArray );
			if (isset ( $first ['phone_work'] )) {
				foreach ( $rowArray as $key => $val ) {
					$phones = array ();
					if ($val ['phone_work'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_work'] ) ) . '&nbsp;(w)';
					if ($val ['phone_home'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_home'] ) ) . '&nbsp;(h)';
					if ($val ['phone_mobile'])
					$phones [] = str_replace ( ' ', '&nbsp;', trim ( $val ['phone_mobile'] ) ) . '&nbsp;(m)';
					$rowArray [$key] ['phone'] = implode ( ', ', $phones );
				}
				$this->view->assign ( 'results', $rowArray );
			}
		}

		$this->view->assign ( 'count', $count );
		$this->view->assign ( 'criteria', $criteria );

		//province
		/*
		$provinceArray = OptionList::suggestionList ( 'location_province', 'province_name', false, false, false );
		$this->viewAssignEscaped ( 'provinces', $provinceArray );
		//district
		$districtArray = OptionList::suggestionList ( 'location_district', array ('district_name', 'parent_province_id' ), false, false, false );
		$this->viewAssignEscaped ( 'districts', $districtArray );
		*/

		$locations = Location::getAll("2");

		$this->viewAssignEscaped('locations',$locations);

		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//qualifications (primary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NULL' );
		$this->viewAssignEscaped ( 'qualifications_primary', $qualsArray );
		//qualifications (secondary)
		$qualsArray = OptionList::suggestionList ( 'person_qualification_option', 'qualification_phrase', false, false, false, 'parent_id IS NOT NULL' );
		$this->viewAssignEscaped ( 'qualifications_secondary', $qualsArray );
		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//upcoming classes
		if (! $criteria ['doCount']) {
			$upcomingArray = TrainingRecommend::getUpcomingTrainingTitles ();
			$this->viewAssignEscaped ( 'upcoming', $upcomingArray );
		}

		//recommended
		require_once 'models/table/TrainingRecommend.php';
		$topicsRecommend = TrainingRecommend::getTopics ();
		$this->viewAssignEscaped ( 'topicsRecommend', @$topicsRecommend->ToArray () );
		//facilities list
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
			$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );

	}

	public function needsByPersonCountAction() {
		$this->view->assign ( 'mode', 'count' );
		return $this->needsReport ();
	}

	public function needsByPersonNameAction() {
		return $this->needsReport ();
	}
	/*
	function needsReportOLD() {
	//find the first date in the database
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();

	$criteria['province_id'] = $this->getSanParam('province_id');
	$criteria['district_id'] = $this->getSanParam('district_id');
	if ( strstr($criteria['district_id'], '_') !== false ) {
	$parts = explode('_',$criteria['district_id']);
	$criteria['district_id'] = $parts[1];
	}

	$criteria['facility_name'] = $this->getSanParam('facility_name');
	$criteria['qualification_id'] = $this->getSanParam('qualification_id');
	$criteria['qualification_secondary_id'] = $this->getSanParam('qualification_secondary_id');

	$criteria['concatNames'] = $this->getSanParam('concatNames');


	$criteria['doCount'] = ($this->view->mode == 'count');
	$criteria['showProvince'] = ($this->getSanParam('showProvince') OR ($criteria['doCount'] and ($criteria['province_id'] or $criteria['province_id'] === '0') ));
	$criteria['showDistrict'] = ($this->getSanParam('showDistrict') OR ($criteria['doCount'] and ($criteria['district_id'] or $criteria['district_id'] === '0') ));
	//$criteria['showQualification'] = ($this->getSanParam('showQualification') OR ($criteria['doCount']  and ($criteria['qualification_id'] or $criteria['qualification_id'] === '0') ));

	$criteria['showQualPrim'] = ($this->getSanParam('showQualPrim') OR ($criteria['doCount']  and ($criteria['qualification_id'] or $criteria['qualification_id'] === '0') ));
	$criteria['showQualSecond'] = ($this->getSanParam('showQualSecond') OR ($criteria['doCount']  and ($criteria['qualification_secondary_id'] or $criteria['qualification_secondary_id'] === '0') ));

	$criteria['showFacility'] = ($this->getSanParam('showFacility') OR ($criteria['doCount'] and $criteria['facility_name']));
	$criteria['showEmail'] = ($this->getSanParam('showEmail'));
	$criteria['showPhone'] = ($this->getSanParam('showPhone'));

	$criteria['go'] = $this->getSanParam('go');
	if ($criteria['go'] ) {

	$sql = 'SELECT ';

	if ( $criteria['doCount'] ) {
	$distinct = ($criteria['distinctCount']) ? 'DISTINCT ' : '';
	$sql .= ' COUNT('.$distinct.'person_id) as "cnt" ';
	} else {
	if ($criteria['concatNames'] )
	$sql .= ' DISTINCT person_id as "id", CONCAT(first_name, '."' '".',last_name) as "name", pt.training_start_date  ';
	else
	$sql .= ' DISTINCT person_id as "id", last_name, first_name, middle_name, pt.training_start_date  ';
	}
	if ( $criteria['showPhone'] ) {
	$sql .= ", CASE WHEN (pt.phone_work IS NULL OR pt.phone_work = '') THEN NULL ELSE pt.phone_work END as \"phone_work\", CASE WHEN (pt.phone_home IS NULL OR pt.phone_home = '') THEN NULL ELSE pt.phone_home END as \"phone_home\", CASE WHEN (pt.phone_mobile IS NULL OR pt.phone_mobile = '') THEN NULL ELSE pt.phone_mobile END as \"phone_mobile\" ";
	}
	if ( $criteria['showEmail'] ) {
	$sql .= ', pt.email ';
	}
	if ( $criteria['showDistrict'] ) {
	$sql .= ', tld.district_name ';
	}
	if ( $criteria['showProvince'] ) {
	$sql .= ', tlp.province_name ';
	}
	if ( $criteria['showFacility'] ) {
	$sql .= ', pt.facility_name ';
	}
	if ( $criteria['showQualPrim'] ) {
	$sql .= ', pq.qualification_phrase ';
	}
	if ( $criteria['showQualSecond'] ) {
	$sql .= ', pqs.qualification_phrase AS qualification_secondary_phrase';
	}
	$sql .=	' FROM (SELECT training.*, person.facility_id as "facility_id", person.id as "person_id", person.last_name, person.first_name, person.middle_name, person.phone_work, person.phone_home, person.phone_mobile, person.email, CASE WHEN person.active = \'deceased\' THEN \'inactive\' ELSE person.active END as "active", CASE WHEN person.gender IS NULL THEN \'na\' WHEN person.gender = \'\' THEN \'na\' ELSE person.gender END as "gender", primary_qualification_option_id, tto.training_title_phrase AS training_title, facility.province_id as "province_id", facility.district_id as "district_id",facility.facility_name FROM training,course,person_to_training,facility,person,training_title_option tto  WHERE training_title_option_id = tto.id AND training.id = person_to_training.training_id AND person.id = person_to_training.person_id AND training.training_title_option_id = course.id AND person.facility_id = facility.id) as pt ';


	if ( $criteria['showDistrict'] ) {
	$sql .= '	LEFT JOIN location_district as tld ON pt.district_id = tld.id ';
	}
	if ( $criteria['showProvince'] ) {
	$sql .= '	JOIN location_province as tlp ON pt.province_id = tlp.id ';
	}
	if ( $criteria['showQualPrim'] || $criteria['showQualSecond']) {
	//$sql .= '	JOIN person_qualification_option as pq ON pq.id = pt.primary_qualification_option_id ';
	// primary qualifications
	$sql .= '
	LEFT JOIN person_qualification_option as pq ON (
	(pt.primary_qualification_option_id = pq.id AND pq.parent_id IS NULL)
	OR
	pq.id = (SELECT parent_id FROM person_qualification_option WHERE id = pt.primary_qualification_option_id LIMIT 1)
	)';

	// secondary qualifications
	$sql .= '
	LEFT JOIN person_qualification_option as pqs ON (
	pt.primary_qualification_option_id = pqs.id AND pqs.parent_id IS NOT NULL
	)';
	}


	$where = '';


	if ( $criteria['facility_name'] ) {
	if ( strlen($where) ) $where .= ' AND ';
	$where .= ' pt.facility_name = \''.$criteria['facility_name'].'\'' ;
	}

	if ( $criteria['district_id'] or $criteria['district_id'] === '0' ) {
	if ( strlen($where) ) $where .= ' AND ';
	$where .= ' pt.district_id = '.$criteria['district_id'] ;
	} else if ( $criteria['province_id'] or $criteria['province_id'] === '0'  ) {
	if ( strlen($where) ) $where .= ' AND ';
	$where .= ' pt.province_id = '.$criteria['province_id'] ;
	}

	if ( $criteria['qualification_id'] ) {
	if ( strlen($where) ) $where .= ' AND ';
	$where .= ' (pq.id = '.$criteria['qualification_id'] . ' OR pqs.parent_id = ' . $criteria['qualification_id'] . ') ';
	}
	if ( $criteria['qualification_secondary_id'] ) {
	if ( strlen($where) ) $where .= ' AND ';
	$where .= ' pqs.id = '.$criteria['qualification_secondary_id'] ;
	}


	if ( $where )
	$sql .= ' WHERE '.$where;

	if ( $criteria['doCount'] ) {

	$groupBy =  '';
	if ( $criteria['showProvince']) {
	if ( strlen($groupBy) ) $groupBy .= ' , ';
	$groupBy .=	' pt.province_id';
	}
	if ( $criteria['showDistrict']) {
	if ( strlen($groupBy) ) $groupBy .= ' , ';
	$groupBy .=	'  pt.district_id';
	}
	if ( $criteria['showFacility']) {
	if ( strlen($groupBy) ) $groupBy .= ' , ';
	$groupBy .=	'  pt.facility_id';
	}
	if ( $criteria['showQualPrim']) {
	if ( strlen($groupBy) ) $groupBy .= ' , ';
	$groupBy .=	'  pq.id ';
	}
	if ( $criteria['showQualSecond']) {
	if ( strlen($groupBy) ) $groupBy .= ' , ';
	$groupBy .=	'  pqs.id ';
	}

	if ( $groupBy != '' )
	$groupBy = ' GROUP BY '.$groupBy;
	$sql .= $groupBy;
	}
	$rowArray = $db->fetchAll($sql);

	if ( $criteria['doCount'] ) {
	$count = 0;
	foreach( $rowArray as $row ) {
	$count += $row['cnt'];
	}
	} else {
	$count = count($rowArray);
	}
	if ( $this->_getParam('outputType')  ) $this->sendData($this->reportHeaders(false, $rowArray));

	} else {
	$count = 0;
	$rowArray  = array();
	}

	$criteria['go'] = $this->getSanParam('go');


	$this->viewAssignEscaped('results',$rowArray);
	if ( $rowArray ) {
	$first = reset($rowArray);
	if ( isset($first['phone_work'] ) ) {
	foreach($rowArray as $key => $val) {
	$phones = array();
	if ( $val['phone_work'] )
	$phones []= str_replace(' ','&nbsp;',trim($val['phone_work'])).'&nbsp;(w)';
	if ( $val['phone_home'] )
	$phones []= str_replace(' ','&nbsp;',trim($val['phone_home'])).'&nbsp;(h)';
	if ( $val['phone_mobile'] )
	$phones []= str_replace(' ','&nbsp;',trim($val['phone_mobile'])).'&nbsp;(m)';
	$rowArray[$key]['phone'] = implode(', ',$phones);
	}
	$this->view->assign('results',$rowArray);
	}
	}

	$this->view->assign('count',$count);
	$this->view->assign('criteria',$criteria);

	//province
	$provinceArray = OptionList::suggestionList('location_province','province_name',false,false,false);
	$this->viewAssignEscaped('provinces',$provinceArray);
	//district
	$districtArray = OptionList::suggestionList('location_district',array('district_name','parent_province_id'),false,false,false);
	$this->viewAssignEscaped('districts',$districtArray);
	//course
	$courseArray = Course::suggestionList(false,10000);;
	$this->viewAssignEscaped('courses',$courseArray);
	//topics
	$topicsArray = OptionList::suggestionList('training_topic_option','training_topic_phrase',false,false,false);
	$this->viewAssignEscaped('topics',$topicsArray);
	//qualifications (primary)
	$qualsArray = OptionList::suggestionList('person_qualification_option','qualification_phrase',false,false,false,'parent_id IS NULL');
	$this->viewAssignEscaped('qualifications_primary',$qualsArray);
	//qualifications (secondary)
	$qualsArray = OptionList::suggestionList('person_qualification_option','qualification_phrase',false,false,false,'parent_id IS NOT NULL');
	$this->viewAssignEscaped('qualifications_secondary',$qualsArray);
	//pepfar
	$organizersArray = OptionList::suggestionList('training_pepfar_categories_option','pepfar_category_phrase',false,false,false);
	$this->viewAssignEscaped('pepfars',$organizersArray);
	//organizers
	//$organizersArray = OptionList::suggestionList('training_organizer_option','training_organizer_phrase',false,false,false);
	//$this->viewAssignEscaped('organizers',$organizersArray);
	//funding
	$fundingArray = OptionList::suggestionList('training_funding_option','funding_phrase',false,false,false);
	$this->viewAssignEscaped('funding',$fundingArray);
r
	//organizers
	$this->view->assign('organizers_checkboxes', Checkboxes::generateHtml('training_organizer_option', 'training_organizer_phrase', $this->view));

	}

	*/

	public function rosterAction() {
		#ini_set('max_execution_time','120'); // these are now exceeded globally
		#ini_set('memory_limit', '256M');
		$criteria ['training_organizer_id'] = $this->getSanParam ( 'training_organizer_id' );
		$criteria ['training_title_id'] = $this->_getParam ( 'training_title_id' );
		$criteria ['is_extended'] = $is_extended = $this->getSanParam ( 'is_extended' );
		$criteria ['add_additional'] = $add_additional = $this->getSanParam ( 'add_additional' );
		$criteria ['go'] = $this->getSanParam('go');

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(training_start_date) as \"start\" FROM training WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( '-', $start_default );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			$sql = "SELECT MAX(training_start_date) as \"start\" FROM training ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['start'];
			$parts = explode ( '-', $end_default );
			$criteria ['end-year'] = $parts [0];
			$criteria ['end-month'] = $parts [1];
			$criteria ['end-day'] = $parts [2];
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}

		if ($this->getSanParam ( 'end-year' ))
		$criteria ['end-year'] = $this->getSanParam ( 'end-year' );
		if ($this->getSanParam ( 'end-month' ))
		$criteria ['end-month'] = $this->getSanParam ( 'end-month' );
		if ($this->getSanParam ( 'end-day' ))
		$criteria ['end-day'] = $this->getSanParam ( 'end-day' );

		if ($criteria['go'])
		{
			try{
			// select trainings
			$sql = "SELECT id FROM training ";
			$where = "WHERE is_deleted=0";
			// where

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where .= " AND training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			$where .= $site_orgs ? " AND training_organizer_option_id in ($site_orgs) " : "";

			if ( $criteria['training_organizer_id'][0] )
				$where .= " AND training_organizer_option_id in (" . implode(',', $criteria['training_organizer_id']) . ")";
			if ( $criteria['training_title_id'][0] )
				$where .= " AND training_title_option_id in (" . implode(',', $criteria['training_title_id']) . ")";
			if (intval ( $criteria ['start-year'] )) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
				$where .= ' training_start_date >= \'' . $startDate . '\' ';
			}

			if (intval ( $criteria ['end-year'] )) {
				if (strlen ( $where ))
				$where .= ' AND ';
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where .= ' training_start_date <= \'' . $endDate . '\'  ';
			}

			$sql .= $where;
			$rowArray = $db->fetchAll ( $sql );

			// now we have trainings, lets get participants trainers and evaluations
			require_once ('models/table/Training.php');
			require_once ('models/table/TrainingToTrainer.php');
			require_once ('models/table/PersonToTraining.php');
			require_once ('models/table/Evaluation.php');

			$tableObj = new Training ( );

			$output = array();
			echo '<html><body>';
			if (! $rowArray)
			echo 'No trainings found.';

			$locations = Location::getAll("2");

			$answers = $db->fetchAll( 'SELECT * FROM evaluation_question_response' );
			$responselist = $db->fetchAll( 'SELECT *,evaluation_response.id as evaluation_response_id FROM evaluation_response
											LEFT JOIN evaluation_to_training ett ON ett.id = evaluation_response.evaluation_to_training_id ORDER BY training_id ASC');
			// response list is basically a hash of training_id, evaluation_to_training_id, evaluation_id, evaluation_response.id, and trainer_person_id, cool!
			$questionz = array();

			// loop through trainings
			foreach ( $rowArray as $row ) {
				if (!isset($row['id']) || empty($row['id']))
					continue;

				$rowRay = @$tableObj->getTrainingInfo ( $row ['id'] );

				// calculate end date
				switch ($rowRay ['training_length_interval']) {
					case 'week' :
					$days = $rowRay ['training_length_value'] * 7;
					break;
					case 'day' :
					$days = $rowRay ['training_length_value'] - 1; // start day counts as a day?
					break;
					default :
					$days = false;
					break;
				}

				if ($days) {
					$rowRay ['training_end_date'] = strtotime ( "+$days day", strtotime ( $rowRay ['training_start_date'] ) );
					$rowRay ['training_end_date'] = date ( 'Y-m-d', $rowRay ['training_end_date'] );
				} else {
					$rowRay ['training_end_date'] = $rowRay ['training_start_date'];
				}

				$rowRay ['duration'] = $rowRay ['training_length_value'] . ' ' . $rowRay ['training_length_interval'] . (($rowRay ['training_length_value'] == 1) ? "" : "s");

				if (! $rowRay['training_title']) $rowRay['training_title'] = t('Unknown');

				echo "
				<p>
				<strong>" . t ('Training').' '.t('ID' ) . ":</strong> {$rowRay['id']}<br />
				<strong>" . t ('Training').' '.t('Name' ) . ":</strong> {$rowRay['training_title']}<br />
				<strong>" . t ('Training Center' ) . ":</strong> {$rowRay['training_location_name']}<br />
				<strong>" . t ('Dates') . ":</strong> {$rowRay['training_start_date']}" . ($rowRay ['training_start_date'] != $rowRay ['training_end_date'] ? ' - ' . $rowRay ['training_end_date'] : '') . "<br />
				<strong>" . t ('Course Length') . ":</strong> {$rowRay['duration']}<br />
				<strong>" . t ('Training').' '.t('Topic' ) . ":</strong> {$rowRay['training_topic_phrase']}<br />
				<strong>" . t ('Training').' '.t('Level' ) . ":</strong> {$rowRay['training_level_phrase']}<br />
				" . ($rowRay ['training_got_curriculum_phrase'] ? "<strong>" . $this->tr ( 'GOT Curriculum' ) . "</strong>: {$rowRay['training_got_curriculum_phrase']}<br />" : '') . "
				" . ($rowRay ['got_comments'] ? "<strong>" . $this->tr ( 'GOT Comment' ) . "</strong>: {$rowRay['got_comments']}<br />" : '') . "
				" . ($rowRay ['comments'] ? "<strong>" . $this->tr ( 'Comments' ) . "</strong>: {$rowRay['comments']}<br />" : "") . "
				" . ($rowRay ['pepfar'] ? "<strong>" . $this->tr ( 'PEPFAR Category' ) . ":</strong> {$rowRay['pepfar']}<br />" : "") . "
				" . ($rowRay ['objectives'] ? "<strong>" . $this->tr ( 'Course Objectives' ) . ":</strong> " . nl2br ( $rowRay ['objectives'] ) : '') . "
				</p>
				";

				// evaluations
				$question_lookup = array(); // questions needed by attached evaluations

				foreach ($responselist as $responserow) // loop through completed evaluations (responses)
				{

					if ( $responserow['training_id'] > $row['id'])
						break; // speed up, its sorted
					if ( $responserow['training_id'] != $row['id'] )
						continue;

					// found a valid training/repsonse combo, lets attach the answers and questions to the training results for EZness
					if (!isset($row['questions']))
						$row['questions'] = array();
					// get ans
					foreach ($answers as $key => $value) {
						if ($value['evaluation_response_id'] == $responserow['evaluation_response_id']){
							if (!isset($row['answers']))
								$row['answers'][$responserow['evaluation_id']][$responserow['evaluation_response_id']]= array('');
							// training['answers'][response_id][question_id] => answer
							$row['answers'][$responserow['evaluation_id']][$responserow['evaluation_response_id']][$value['evaluation_question_id']] = $value['value_text'] ? $value['value_text'] : $value['value_int'];
						}
					}
					// get q
					$question_lookup[] = $responserow['evaluation_id'];
				}

				// get all questions (usually a larger table than responses)
				foreach (array_unique($question_lookup) as $eval_id) {
					if (!trim($eval_id))
						continue;
					if(! isset($questionz[$eval_id]) ) // fetch once
					$questionz[$eval_id] = @Evaluation::fetchAllQuestions($eval_id)->toArray();
				}
				// evals now in rowRay['answers'], questions in $questionz
				//end evaluations

				/* Trainers */
				$trainers = @TrainingToTrainer::getTrainers ( $row ['id'] )->toArray();
				if ($trainers){
					echo '
				<table border="1" style="border-collapse:collapse;" cellpadding="3">
					<caption style="text-align:left;"><em>' . t ('Course').' '.t('Trainers') . '</em></caption>
				<tr>
				<th>' . $this->tr ( 'Surname' ) . '</th>
				<th>' . $this->tr ( 'First Name' ) . '</th>
				<th>' . t ( 'Days' ) . '</th>
				</tr>
				';
				foreach ( $trainers as $tRow ) {
					echo "
					<tr>
					<td>{$tRow['last_name']}</td>
					<td>{$tRow['first_name']}</td>
					<td>{$tRow['duration_days']}</td>
					</tr>
					";
				}

					echo '</table><br>';
				}


				$persons = @PersonToTraining::getParticipants ( $row ['id'] )->toArray ();

				echo '
				<table border="1" style="border-collapse:collapse;" cellpadding="3">
				<caption style="text-align:left;"><em>' . t ( 'Course Participants' ) . '</em></caption>
				<tr>';
				$headers = array();
				$headers []= $this->tr ( 'Surname' );
				if ( $this->setting ( 'display_middle_name') ) $headers []= $this->tr ( 'Middle Name' );
				$headers []= $this->tr ( 'First Name' );
				$headers []= t ( 'Date of Birth' );
				$headers []= $this->tr ( 'Facility' );
				if ( $add_additional ) {
					$headers []= $this->tr ( 'Region A (Province)' );
					if  ($this->setting ( 'display_region_b' )) $headers []= $this->tr ( 'Region B (Health District)' );
					if  ($this->setting ( 'display_region_c' )) $headers []= $this->tr ( 'Region C (Local Region)' );
					if  ($this->setting ( 'display_region_d' )) $headers []= $this->tr ( 'Region D' );
					if  ($this->setting ( 'display_region_e' )) $headers []= $this->tr ( 'Region E' );
					if  ($this->setting ( 'display_region_f' )) $headers []= $this->tr ( 'Region F' );
					if  ($this->setting ( 'display_region_g' )) $headers []= $this->tr ( 'Region G' );
					if  ($this->setting ( 'display_region_h' )) $headers []= $this->tr ( 'Region H' );
					if  ($this->setting ( 'display_region_i' )) $headers []= $this->tr ( 'Region I' );
					$headers []= t ( 'Primary Qualification' );
					$headers []= t ( 'Secondary Qualification' );
				}
				if ( $this->setting ( 'module_attendance_enabled' ) ) {
					$headers []= t ( 'Days Attended' );
					$headers []= t ( 'Complete' );
				}
				if ( $is_extended ) {
					$headers []= t ( 'Pre-Test' );
					$headers []= t ( 'Post-Test' );
					$headers []= t ( 'Change in Score' );
				}

				/* Participants */
				// map each score-other to a hash
				$scores = array(); $scoreOtherHeaders = array();
				foreach ( $persons as $r ) {
					if (!$r['person_id'])
						continue;
					$keys = explode(',', $r['score_other_k']);
					$values = explode(',', $r['score_other_v']);
					foreach ( $keys as $i=>$k ) {
						$k = trim($k);
						if ($k) {
							$scores[$r['person_id']][$k] = $values[$i] ;
							$scoreOtherHeaders[] = $k;
						}
					}
				}

				$scoreOtherHeaders = array_unique($scoreOtherHeaders);
				foreach ($scoreOtherHeaders as $h)
					$headers [] = $h;

				echo '<th>'.implode('</th><th>', $headers);
				echo '</th></tr>';

				/* Participants */
				foreach ( $persons as $r ) {
					if (is_numeric ( $r ['score_percent_change'] )) { // add percent
						if ($r ['score_percent_change'] > 0) {
							$r ['score_percent_change'] = "+" . $r ['score_percent_change'];
						}
						$r ['score_percent_change'] = "{$r['score_percent_change']}%";
					}
					$r ['score_change'] = '';

					if ($r ['score_post']) {
						$r ['score_change'] = $r ['score_post'] - $r ['score_pre'];
					}

					echo "<tr><td>";
					$body_fields = array();
					$body_fields[] = $r['last_name'];
					if ( $this->setting ( 'display_middle_name') ) $body_fields[] = $r['middle_name'];
					$body_fields[] = $r['first_name'];
					$body_fields[] = $r['birthdate'];
					$body_fields[] = $r['facility_name'];
					if ( $add_additional ) {
						$region_ids = Location::getCityInfo($r['location_id'], $this->setting('num_location_tiers'), $locations);
						$region_ids = Location::cityInfotoHash($region_ids);
						$body_fields[] = $locations[$region_ids['province_id']]['name'];
						if ( $this->setting ( 'display_region_b' ) ) $body_fields[] = $locations[$region_ids['district_id']]['name'];
						if ( $this->setting ( 'display_region_c' ) ) $body_fields[] = $locations[$region_ids['region_c_id']]['name'];
						if ( $this->setting ( 'display_region_d' ) ) $body_fields[] = $locations[$region_ids['region_d_id']]['name'];
						if ( $this->setting ( 'display_region_e' ) ) $body_fields[] = $locations[$region_ids['region_e_id']]['name'];
						if ( $this->setting ( 'display_region_f' ) ) $body_fields[] = $locations[$region_ids['region_f_id']]['name'];
						if ( $this->setting ( 'display_region_g' ) ) $body_fields[] = $locations[$region_ids['region_g_id']]['name'];
						if ( $this->setting ( 'display_region_h' ) ) $body_fields[] = $locations[$region_ids['region_h_id']]['name'];
						if ( $this->setting ( 'display_region_i' ) ) $body_fields[] = $locations[$region_ids['region_i_id']]['name'];

						if ( (!$r['primary_qualification']) OR ($r['primary_qualification'] == 'unknown')) {
							$body_fields[] = $r['qualification'];
							$body_fields[] = '';
						} else {
							$body_fields[] = $r['primary_qualification'];
							$body_fields[] = $r['qualification'];
						}
						//        $body_fields[] = $r['primary_responsibility'];
						//        $body_fields[] = $r['secondary_responsibility'];
					}
					if ( $this->setting ( 'module_attendance_enabled' ) ) {
						$body_fields[] = $r['duration_days'];
						$body_fields[] = $r['award_id'] ? $r['award_id'] : '';
					}
					if ( $is_extended ) {
						$body_fields[] = $r['score_pre'];
						$body_fields[] = $r['score_post'];
						$body_fields[] = $r['score_change'];
						//custom scores
						$pid = $r['person_id'];
						foreach($scoreOtherHeaders as $h) {
							$body_fields[] = ($scores[$pid][$h]) ? $scores[$pid][$h] : '&nbsp;'; // TODO should show a '&nbsp;' on empty not space, TrainSMART standard
						}

					}

					echo implode('</td><td>', $body_fields);
					echo "</td></tr>";
				}

				echo '</table><br>';

				// evaluations
				if ($row['answers'])
				{
					echo '
						<table border="1" style="border-collapse:collapse;" cellpadding="3">
						<caption style="text-align:left;"><em>' . t ('Evaluations') . '</em></caption>';
					$qnames = array();
					$answer = array();
					$qids   = array();
					$last_eval_id = 0;
					foreach( $row['answers'] as $eval_id => $evalresponse ) {
						$hdr_txt = "";
						if ( $eval_id != $last_eval_id ){ // print header row
							foreach ($questionz as $evaluationid => $qArray) {
								if ($eval_id != $evaluationid)
									continue;
								foreach ($qArray as $q){
									$ex = '';
									$qids[] = $q['id'];
									if ($q['question_type'] == 'Likert3' || $q['question_type'] == 'Likert3NA') $ex = "&nbsp;(1-3)";
									if ($q['question_type'] == 'Likert' || $q['question_type'] == 'LikertNA') $ex = "&nbsp;(1-5)";
									$hdr_txt .= '<th>'.$q['question_text'].$ex.'</th>';
								}
								break;
							}
							if($hdr_txt)
								echo "<tr>$hdr_txt</tr>";
						}

						foreach ( $evalresponse as $reponseid => $answerrow ) {
							// attempt has build evalation question list
							if(isset($answerrow[0]) && !$answerrow[0])
								unset($answerrow[0]); // bugfix, one of my array() inits is wrong. TODO

							// pad results (missing answers wont line up in html table otherwise)
							foreach ($qids as $qid) {
								if(! isset($answerrow[$qid]))
									$answerrow[$qid] = '&nbsp;'; // TODO should show a '-', TrainSMART standard
							}
							ksort($answerrow); // due to filling in missing answers above, need to resort here

							echo '<tr><td>'.implode('</td><td>', $answerrow).'</td></tr>';
						}
					}
					echo '</table><br>';

			}
				// done
				echo '<br><hr size="1">';
			}

			echo '</html></body>';

			foreach ($output as $key => $value) {
				echo utf8_decode($value);
				echo PHP_EOL;
			}

			exit ();

			} catch (Exception $e) {
				echo $e->getMessage() . '<br>' . PHP_EOL;
				die();
			}
		}

		//form drop downs
		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, false, false );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );
		$titlesArray = OptionList::suggestionList ( 'training_title_option', 'training_title_phrase', false, false, false );
		$this->viewAssignEscaped ( 'titles', $titlesArray );
		$this->view->assign ( 'criteria', $criteria );


	}

	public function evaluationsAction() {
		$criteria = $this->getAllParams();
		$db = $this->dbfunc();
		$status = ValidationContainer::instance ();
		$criteria['evaluation_id'] =  array_filter($this->_array_me($criteria['evaluation_id'])); // filter out empty items, force to an array
		$eval_ids = implode(',', $criteria['evaluation_id']);

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT MIN(timestamp_created) as \"start\" FROM evaluation_response WHERE is_deleted = 0";
		$rowArray = $db->fetchAll ( $sql );
		$start_default = $rowArray [0] ['start'];
		$parts = explode ( ' ', $start_default );
		$parts = explode ( '-', $parts [0] );
		$criteria ['start-year'] = $parts [0];
		$criteria ['start-month'] = $parts [1];
		$criteria ['start-day'] = $parts [2];

		if ($this->getSanParam ( 'start-year' ))
		$criteria ['start-year'] = $this->getSanParam ( 'start-year' );
		if ($this->getSanParam ( 'start-month' ))
		$criteria ['start-month'] = $this->getSanParam ( 'start-month' );
		if ($this->getSanParam ( 'start-day' ))
		$criteria ['start-day'] = $this->getSanParam ( 'start-day' );
		if ($this->view->mode == 'search') {
			$sql = "SELECT MAX(timestamp_created) as \"start\" FROM evaluation_response ";
			$rowArray = $db->fetchAll ( $sql );
			$end_default = $rowArray [0] ['start'];
			$parts = explode ( ' ', $start_default );
			$parts = explode ( '-', $parts [0] );
			$criteria ['end-year'] = $parts [0];
			$criteria ['end-month'] = $parts [1];
			$criteria ['end-day'] = $parts [2];
		} else {
			$criteria ['end-year'] = date ( 'Y' );
			$criteria ['end-month'] = date ( 'm' );
			$criteria ['end-day'] = date ( 'd' );
		}

		if ($criteria['go']) // run report
		{

			list($a, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			list($locationFlds, $locationsubquery) = Location::subquery($this->setting('num_location_tiers'), $location_tier, $location_id, true);

			$sql = " SELECT
						tl.training_location_name,
						evaluation.title,
						eqr.id,
						evaluation_response_id,
						evaluation_question_id,"
						.//IFNULL(a.answer_phrase, IFNULL(value_text, value_int)) as answer,
						implode(',',$locationFlds).",
						evaluation_to_training_id,
						trainer_person_id,
						evaluation.id as evaluation_id,
						training_id,
						training_title_phrase,
						title,
						question_text,
						question_type,
						weight,
						CONCAT(p.first_name, CONCAT(' ', p.last_name)) as person_full_name,
						CONCAT(t.first_name, CONCAT(' ', t.last_name)) as trainer_full_name,
						GROUP_CONCAT(IFNULL(value_text, value_int)) as score_array,
						GROUP_CONCAT(value_int) as scores_int
					 FROM
						evaluation_question_response eqr
						LEFT JOIN evaluation_response er      ON  eqr.evaluation_response_id = er.id
						INNER JOIN evaluation_to_training ett ON  ett.id = er.evaluation_to_training_id AND ett.training_id IS NOT NULL
						INNER JOIN training                   ON  training.id = ett.training_id AND training.is_deleted = 0
						LEFT JOIN training_location tl        ON  tl.id = training.training_location_id
						LEFT JOIN training_title_option tto   ON  training.training_title_option_id = tto.id
						LEFT JOIN evaluation                  ON  evaluation.id = ett.evaluation_id
						LEFT JOIN evaluation_question eq      ON  eq.id = eqr.evaluation_question_id
						"//LEFT JOIN evaluation_custom_answers a ON  a.question_id = eq.id
						."
						LEFT JOIN person p                    ON  p.id = er.person_id
						LEFT JOIN person t                    ON  t.id = er.trainer_person_id
						LEFT JOIN ($locationsubquery) as l ON tl.location_id = l.id ";

			$where [] = 'evaluation.is_deleted = 0 AND er.is_deleted = 0 AND eq.is_deleted = 0 AND eqr.is_deleted = 0';
			if ($criteria['evaluation_id'])              $where [] = "ett.evaluation_id in ($eval_ids)";
			if ($criteria['training_id'])                $where [] = 'training_id = ' . $criteria['training_id'];
			if ($criteria['training_title_option_id'])   $where [] = 'training.training_title_option_id = ' . $criteria['training_title_option_id'];
			if ($criteria['person_id'])                  $where [] = "(p.id = {$criteria['person_id']} or t.id = {$criteria['person_id']})";
			if ( $criteria ['start-year'] && !$training_id ) { // bugfix: !training_id todo: these do not play well with evaluations by training_id (probably because of my test db-not sure)
					$startDate = $criteria ['start-year'] . '-' . $criteria ['start-month'] . '-' . $criteria ['start-day'];
					$where[] .= ' er.timestamp_created >= \'' . $startDate . '\' ';
				}

			if ( $criteria ['end-year']  && !$training_id ) {
				$endDate = $criteria ['end-year'] . '-' . $criteria ['end-month'] . '-' . $criteria ['end-day'];
				$where[] = ' er.timestamp_created <= \'' . $endDate . ' 23:59\'  ';
			}
			if ($locWhere = $this->getLocationCriteriaWhereClause($criteria)) {
				$where [] = $locWhere;
			}

			$sql .= ' WHERE ' . implode(' AND ', $where);

			$sql .= " GROUP BY eq.id,evaluation.id,training_id,person_full_name, trainer_full_name";
			$sql .= " ORDER BY ett.training_id, ett.evaluation_id, er.timestamp_created, weight";

			$rows = $db->fetchAll($sql);
			if ($rows) {

				// pivot rows to columns, based on the # of times a participant has a linked evaluation
				$maxVisits = 0;
				foreach ($rows as $i => $row) {  // count # of visits, keep the max # visits
					$rows[$i]['parsed_scores'] = explode(',', $row['score_array']);  // explode list of scores
					$cnt = count($rows[$i]['parsed_scores']);
					if ($cnt > $maxVisits)
						$maxVisits = $cnt;
				}

				foreach ($rows as $i => $row) {
					$rows[$i]['question_number'] = $row['weight'] + 1;
					for ($k = 0; $k < $maxVisits; $k++) { // pivot rows to columns
						$rows[$i]['response'.($k+1)] = isset($row['parsed_scores'][$k]) ? $row['parsed_scores'][$k] : ''; // do it here so we can export to excel
					}
					$avgsArray = explode(',', $row['scores_int']); // averages, value_int column only
					$rows[$i]['average'] = ((isset($avgsArray[0]) && trim($avgsArray[0]) !== '') ? number_format( array_sum($avgsArray) / count($avgsArray), 2) : '-'); // if it seems to not be empty we can do some calculations
					// cleanup - in case of export
					unset($rows[$i]['score_array']);
					unset($rows[$i]['scores_int']);
					unset($rows[$i]['parsed_scores']);
					unset($rows[$i]['id']);
					unset($rows[$i]['evaluation_response_id']);
					unset($rows[$i]['evaluation_question_id']);
					unset($rows[$i]['answer']);
					unset($rows[$i]['evaluation_to_training_id']);
					unset($rows[$i]['trainer_person_id']);
					unset($rows[$i]['evaluation_id']);
					unset($rows[$i]['weight']);
					unset($rows[$i]['province_id']);
					unset($rows[$i]['district_id']);
					unset($rows[$i]['region_c_id']);
					unset($rows[$i]['region_d_id']);
					unset($rows[$i]['region_e_id']);
					unset($rows[$i]['region_f_id']);
					unset($rows[$i]['region_g_id']);
					unset($rows[$i]['region_h_id']);
					unset($rows[$i]['region_i_id']);
				}

				$this->viewAssignEscaped('numColumns', $maxVisits);
				$this->viewAssignEscaped('results', $rows);

				if ($this->_getParam ( 'outputType' ))
					$this->sendData ( $this->reportHeaders ( false, $rows ) );

			} else {
				$status->setStatusMessage( 'Error running report. There might be no data.' );
			}
		}
		$this->viewAssignEscaped ('pageTitle', t('Evaluation Report'));

		$this->viewAssignEscaped ('locations', Location::getAll("2"));

		$this->viewAssignEscaped ( 'evaluations', OptionList::suggestionList ( 'evaluation', 'title', false, false, false ) );
		$this->view->assign ( 'criteria', $criteria );
		//people
		require_once('models/table/Person.php');
		if ( ! $criteria['go'] ) { // no report, redirecting, we can skip this expensive call
			$peopleArray = Person::suggestionList(false, 1999, $this->setting('display_middle_name_last'));
			$this->viewAssignEscaped('people', $peopleArray);
		}
		//titles
		require_once('views/helpers/DropDown.php');
		$this->view->assign ( 'titles',   DropDown::generateHtml ( 'training_title_option', 'training_title_phrase', $criteria['training_title_option_id'], false, $this->view->viewonly, false ) );
	}

	public function evaluationsReportAction()
	{
		require_once('models/table/Trainer.php');
		require_once('models/table/TrainingLocation.php');

		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		//criteria
		$criteria['showTrainer']           = $this->getSanParam( 'showTrainer' );
		$criteria['showCategory']          = $this->getSanParam( 'showCategory' );
		$criteria['showTitle']             = $this->getSanParam( 'showTitle' );
		$criteria['showLocation']          = $this->getSanParam( 'showLocation' );
		$criteria['showOrganizer']         = $this->getSanParam( 'showOrganizer' );
		$criteria['showMechanism']         = $this->getSanParam( 'showMechanism' );
		$criteria['showTopic']             = $this->getSanParam( 'showTopic' );
		$criteria['showLevel']             = $this->getSanParam( 'showLevel' );
		$criteria['showPepfar']            = $this->getSanParam( 'showPepfar' );
		$criteria['showMethod']            = $this->getSanParam( 'showMethod' );
		$criteria['showFunding']           = $this->getSanParam( 'showFunding' );
		$criteria['showTOT']               = $this->getSanParam( 'showTOT' );
		$criteria['showRefresher']         = $this->getSanParam( 'showRefresher' );
		$criteria['showGotCurric']         = $this->getSanParam( 'showGotCurric' );
		$criteria['showGotComment']        = $this->getSanParam( 'showGotComment' );
		$criteria['showLang1']             = $this->getSanParam( 'showLang1' );
		$criteria['showLang2']             = $this->getSanParam( 'showLang2' );
		$criteria['showCustom1']           = $this->getSanParam( 'showCustom1' );
		$criteria['showCustom2']           = $this->getSanParam( 'showCustom2' );
		$criteria['showCustom3']           = $this->getSanParam( 'showCustom3' );
		$criteria['showCustom4']           = $this->getSanParam( 'showCustom4' );
		$criteria['showCustom5']           = $this->getSanParam( 'showCustom5' );
		$criteria['showProvince']          = $this->getSanParam( 'showProvince' );
		$criteria['showDistrict']          = $this->getSanParam( 'showDistrict' );
		$criteria['showRegionC']           = $this->getSanParam( 'showRegionC' );
		$criteria['showRegionD']           = $this->getSanParam( 'showRegionD' );
		$criteria['showRegionE']           = $this->getSanParam( 'showRegionE' );
		$criteria['showRegionF']           = $this->getSanParam( 'showRegionF' );
		$criteria['showRegionG']           = $this->getSanParam( 'showRegionG' );
		$criteria['showRegionH']           = $this->getSanParam( 'showRegionH' );
		$criteria['showRegionI']           = $this->getSanParam( 'showRegionI' );
		$criteria['evaluation_id']         = $this->getSanParam( 'evaluation_id' );
		$criteria['trainer_id']            = $this->getSanParam( 'trainer_id' );
		$criteria['training_category_id']  = $this->getSanParam( 'training_category_id' );
		$criteria['training_title_id']     = $this->getSanParam( 'training_title_id' );
		$criteria['training_location_id']  = $this->getSanParam( 'training_location_id' );
		$criteria['training_organizer_id'] = $this->getSanParam( 'training_organizer_id' );
		$criteria['training_mechanism_id'] = $this->getSanParam( 'training_mechanism_id' );
		$criteria['training_topic_id']     = $this->getSanParam( 'training_topic_id' );
		$criteria['training_level_id']     = $this->getSanParam( 'training_level_id' );
		$criteria['training_pepfar_id']    = $this->getSanParam( 'training_pepfar_id' );
		$criteria['training_method_id']    = $this->getSanParam( 'training_method_id' );
		$criteria['training_funding_id']   = $this->getSanParam( 'training_funding_id' );
		$criteria['training_tot_id']       = $this->getSanParam( 'training_tot_id' );
		$criteria['training_refresher_id'] = $this->getSanParam( 'training_refresher_id' );
		$criteria['training_got_id']       = $this->getSanParam( 'training_got_id' );
		$criteria['training_gotcomment_id']= $this->getSanParam( 'training_gotcomment_id' );
		$criteria['training_lang1_id']     = $this->getSanParam( 'training_lang1_id' );
		$criteria['training_lang2_id']     = $this->getSanParam( 'training_lang2_id' );
		$criteria['training_custom1_id']   = $this->getSanParam( 'training_custom1_id' );
		$criteria['training_custom2_id']   = $this->getSanParam( 'training_custom2_id' );
		$criteria['training_custom3_id']   = $this->getSanParam( 'training_custom3_id' );
		$criteria['training_custom4_id']   = $this->getSanParam( 'training_custom4_id' );
		$criteria['province_id']           = $this->getSanParam( 'province_id' );
		$criteria['district_id']           = $this->getSanParam( 'district_id' );
		$criteria['region_c_id']           = $this->getSanParam( 'region_c_id' );
		$criteria['region_d_id']           = $this->getSanParam( 'region_d_id' );
		$criteria['region_e_id']           = $this->getSanParam( 'region_e_id' );
		$criteria['region_f_id']           = $this->getSanParam( 'region_f_id' );
		$criteria['region_g_id']           = $this->getSanParam( 'region_g_id' );
		$criteria['region_h_id']           = $this->getSanParam( 'region_h_id' );
		$criteria['region_i_id']           = $this->getSanParam( 'region_i_id' );
		$criteria['startdate']             = $this->getSanParam( 'startdate' );
		$criteria['enddate']               = $this->getSanParam( 'enddate' );
		$criteria['has_response']          = $this->getSanParam( 'has_response' );
		$criteria ['limit'] = $this->getSanParam ( 'limit' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if($criteria['go'])
		{
			// fields
			$sql = 'SELECT pt.id as "id", ptc.pcnt, pt.training_start_date, pt.training_end_date, pt.has_known_participants  '; // training fields
			$sql .= ',title, trainer_person_id, first_name, last_name, question_text, question_type, weight, value_text, value_int'; // evaluation fields
			if ( $criteria ['showRegionI'] ) {   $sql .= ', pt.region_i_name ';	}
			if ( $criteria ['showRegionH'] ) {   $sql .= ', pt.region_h_name '; }
			if ( $criteria ['showRegionG'] ) {   $sql .= ', pt.region_g_name '; }
			if ( $criteria ['showRegionF'] ) {   $sql .= ', pt.region_f_name '; }
			if ( $criteria ['showRegionE'] ) {   $sql .= ', pt.region_e_name '; }
			if ( $criteria ['showRegionD'] ) {   $sql .= ', pt.region_d_name '; }
			if ( $criteria ['showRegionC'] ) {   $sql .= ', pt.region_c_name '; }
			if ( $criteria ['showDistrict'] ) {  $sql .= ', pt.district_name '; }
			if ( $criteria ['showProvince'] ) {  $sql .= ', pt.province_name '; }
			if ( $criteria ['showLocation'] ) {  $sql .= ', pt.training_location_name '; }
			if ( $criteria ['showOrganizer'] ) { $sql .= ', torg.training_organizer_phrase as training_organizer_phrase ';	}
			if ( $criteria ['showMechanism']  && $this->setting('display_training_partner')) { $sql .= ', organizer_partners.mechanism_id ';	}
			if ( $criteria ['showLevel'] ) {     $sql .= ', tlev.training_level_phrase '; }
			if ( $criteria ['showCategory'] ) {  $sql .= ', tcat.training_category_phrase '; }
			if ( $criteria ['showTitle'] ) {     $sql .= ', training_title '; }
			if ( $criteria ['showPepfar']  || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0') { $sql .= ', GROUP_CONCAT(DISTINCT tpep.pepfar_category_phrase) as "pepfar_category_phrase" '; }
			if ( $criteria ['showMethod'] ) {	 $sql .= ', tmeth.training_method_phrase '; }
			if ( $criteria ['showTopic'] ) {     $sql .= ', GROUP_CONCAT(DISTINCT ttopic.training_topic_phrase ORDER BY training_topic_phrase) AS "training_topic_phrase" '; }
			if ( $criteria ['showTOT'] ) {       $sql .= ", IF(is_tot,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_tot"; }
			if ( $criteria ['showRefresher'] ) { $sql .= ", IF(is_refresher,'" . t ( 'Yes' ) . "','" . t ( 'No' ) . "') AS is_refresher"; }
			if ( $criteria ['showLang2'] ) {     $sql .= ', tlos.language_phrase as "secondary_language_phrase" '; }
			if ( $criteria ['showLang1'] ) {     $sql .= ', tlop.language_phrase as "primary_language_phrase" '; }
			if ( $criteria ['showGotComment'] ){ $sql .= ", pt.got_comments"; }
			if ( $criteria ['showGotCurric'] ) { $sql .= ', tgotc.training_got_curriculum_phrase '; }
			if ( $criteria ['showFunding'] ) {   $sql .= ', GROUP_CONCAT(DISTINCT tfund.funding_phrase ORDER BY funding_phrase) as "funding_phrase" '; }
//			if ( isset($criteria ['showCustom1']) ) {   $sql .= ', tqc.custom1_phrase '; }
//			if ( isset($criteria ['showCustom2']) ) {   $sql .= ', tqc.custom2_phrase '; }
//			if ( isset($criteria ['showCustom3']) ) {   $sql .= ', pt.custom_3'; }
//			if ( isset($criteria ['showCustom4']) ) {   $sql .= ', pt.custom_4'; }
			//if ( $criteria ['showCustom5'] ) {   $sql .= ', pt.custom_5'; }

			list($dontcare, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			$num_location_tiers = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_location_tiers, $location_tier, $location_id, true);

			$sql .= ' FROM (SELECT training.*, tto.training_title_phrase AS training_title,training_location.training_location_name, '.implode(',',$field_name).
					'       FROM training  ' .
					'         LEFT JOIN training_title_option tto ON (`training`.training_title_option_id = tto.id) ' .
					'         LEFT JOIN training_location ON training.training_location_id = training_location.id ' .
					'         LEFT JOIN ('.$location_sub_query.') as l ON training_location.location_id = l.id ' .
					'  WHERE training.is_deleted=0) as pt ';

			$sql .= ' LEFT JOIN (SELECT COUNT(id) as "pcnt",training_id FROM person_to_training GROUP BY training_id) as ptc ON ptc.training_id = pt.id ';

			// joins

			if ($criteria['trainer_id'])
			$sql .= ' LEFT JOIN training_to_trainer as t2t ON (t2t.training_id = pt.id AND t2t.trainer_id = ' . $criteria['trainer_id'].')';

			if ($criteria ['showOrganizer'] or $criteria ['training_organizer_id'] || $criteria ['showMechanism']  || $criteria ['training_mechanism_id'])
			$sql .= ' JOIN training_organizer_option as torg ON torg.id = pt.training_organizer_option_id ';

			if ($criteria ['showMechanism'] || $criteria ['training_mechanism_id'] && @$this->setting('display_training_partner'))
			$sql .= ' LEFT JOIN organizer_partners ON organizer_partners.organizer_id = torg.id';

			if ($criteria ['showLevel'] || $criteria ['training_level_id'])
			$sql .= ' JOIN training_level_option as tlev ON tlev.id = pt.training_level_option_id ';

			if ($criteria ['showMethod'] || $criteria ['training_method_id'])
			$sql .= ' JOIN training_method_option as tmeth ON tmeth.id = pt.training_method_option_id ';

			if ($criteria ['showPepfar'] || $criteria ['training_pepfar_id'] || $criteria ['training_pepfar_id'] === '0')
			$sql .= '	LEFT JOIN (SELECT training_id, ttpco.training_pepfar_categories_option_id, pepfar_category_phrase FROM training_to_training_pepfar_categories_option as ttpco JOIN training_pepfar_categories_option as tpco ON ttpco.training_pepfar_categories_option_id = tpco.id) as tpep ON tpep.training_id = pt.id ';

			if ($criteria ['showTopic'] || $criteria ['training_topic_id'])
			$sql .= ' LEFT JOIN (SELECT training_id, ttto.training_topic_option_id, training_topic_phrase FROM training_to_training_topic_option as ttto JOIN training_topic_option as tto ON ttto.training_topic_option_id = tto.id) as ttopic ON ttopic.training_id = pt.id ';

			if ($criteria ['showLang1'] || $criteria ['training_lang1_id'])
			$sql .= ' LEFT JOIN trainer_language_option as tlop ON tlop.id = pt.training_primary_language_option_id ';

			if ($criteria ['showLang2'] || $criteria ['training_lang2_id'])
			$sql .= ' LEFT JOIN trainer_language_option as tlos ON tlos.id = pt.training_secondary_language_option_id ';

			if ($criteria ['showFunding'] || (intval ( $criteria ['funding_min'] ) or intval ( $criteria ['funding_max'] )))
			$sql .= ' LEFT JOIN (SELECT training_id, ttfo.training_funding_option_id, funding_phrase, ttfo.funding_amount FROM training_to_training_funding_option as ttfo JOIN training_funding_option as tfo ON ttfo.training_funding_option_id = tfo.id) as tfund ON tfund.training_id = pt.id ';

			if ($criteria ['showGotCurric'] || $criteria ['training_got_id'])
			$sql .= ' LEFT JOIN training_got_curriculum_option as tgotc ON tgotc.id = pt.training_got_curriculum_option_id';

			if ($criteria ['showCategory'] or ! empty ( $criteria ['training_category_id'] ))
			$sql .= 'LEFT JOIN training_category_option_to_training_title_option tcotto ON (tcotto.training_title_option_id = pt.training_title_option_id)
					 LEFT JOIN training_category_option tcat ON (tcotto.training_category_option_id = tcat.id)';

			if ( $criteria['showCustom1'] || $criteria ['training_custom1_id'] )
			$sql .= ' LEFT JOIN training_custom_1_option as tqc ON pt.training_custom_1_option_id = tqc.id  ';

			if ( $criteria['showCustom2'] || $criteria ['training_custom2_id'] )
			$sql .= ' LEFT JOIN training_custom_2_option as tqc2 ON pt.training_custom_2_option_id = tqc2.id  ';

			#if ( $criteria['showCustom3'] || $criteria ['custom_3_id'] )
			#todo$sql .= ' LEFT JOIN training_custom_3_option as custom_3 ON pt.training_custom_3_option_id = tqc3.id  ';

			#if ( $criteria['showCustom4'] || $criteria ['custom_4_id'] )
			#todo$sql .= ' LEFT JOIN training_custom_4_option as custom_4 ON pt.training_custom_4_option_id = tqc4.id  ';

			$sql .= ' RIGHT JOIN evaluation_to_training ON pt.id = evaluation_to_training.training_id
					  RIGHT JOIN evaluation 	        ON evaluation_id = evaluation.id
					  RIGHT JOIN evaluation_response    ON evaluation_to_training.id = evaluation_response.evaluation_to_training_id
					  RIGHT JOIN evaluation_question    ON evaluation.id = evaluation_question.evaluation_id
					  RIGHT JOIN evaluation_question_response ON evaluation_response.id = evaluation_question_response.evaluation_response_id AND evaluation_question.id = evaluation_question_response.evaluation_question_id
					  LEFT JOIN person ON trainer_person_id = person.id ';

			// where
			$where =  array( ' pt.is_deleted=0 ' );

			// restricted access?? only show trainings we have the ACL to view
			require_once('views/helpers/TrainingViewHelper.php');
			$org_allowed_ids = allowed_organizer_access($this);
			if ($org_allowed_ids) { // doesnt have acl 'training_organizer_option_all'
				$org_allowed_ids = implode(',', $org_allowed_ids);
				$where [] = " pt.training_organizer_option_id in ($org_allowed_ids) ";
			}
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)
				$where [] = " training_organizer_option_id in ($site_orgs) ";

			if ($criteria ['training_participants_type']) {
				if ($criteria ['training_participants_type'] == 'has_known_participants') {
					$where [] = ' pt.has_known_participants = 1 ';
				}
				if ($criteria ['training_participants_type'] == 'has_unknown_participants') {
					$where [] = ' pt.has_known_participants = 0 ';
				}
			}

			if ($criteria ['evaluation_id'])
				$where [] = ' evaluation.id = '.$criteria['evaluation_id'] ;

			if ($criteria ['trainer_id'])
				$where [] = ' trainer_person_id = '.$criteria['trainer_id'] ;

			if ($criteria ['training_location_id'])
				$where [] = ' pt.training_location_id = \'' . $criteria ['training_location_id'] . '\'';

			if ($criteria ['training_title_id'] or $criteria ['training_title_id'] === '0')
				$where [] = ' pt.training_title_option_id = ' . $criteria ['training_title_id'];

			if ($criteria ['training_organizer_id'] or $criteria ['training_organizer_id'] === '0')
				$where [] = ' pt.training_organizer_option_id = \'' . $criteria ['training_organizer_id'] . '\'';

			if ($criteria ['training_mechanism_id'] or $criteria ['training_mechanism_id'] === '0' && $this->setting('display_training_partner'))
				$where [] = ' organizer_partners.mechanism_id = \'' . $criteria ['training_mechanism_id'] . '\'';

			if ($criteria ['training_topic_id'] or $criteria ['training_topic_id'] === '0')
				$where [] = ' ttopic.training_topic_option_id = \'' . $criteria ['training_topic_id'] . '\'';

			if ($criteria ['training_level_id'])
				$where [] = ' pt.training_level_option_id = \'' . $criteria ['training_level_id'] . '\'';

			if ($criteria ['training_pepfar_id'] or $criteria ['training_pepfar_id'] === '0')
				$where [] = ' tpep.training_pepfar_categories_option_id = \'' . $criteria ['training_pepfar_id'] . '\'';

			if ($criteria ['training_method_id'] or $criteria ['training_method_id'] === '0')
				$where [] = ' tmeth.id = \'' . $criteria ['training_method_id'] . '\'';

			if ($criteria ['training_lang1_id'] or $criteria ['training_lang1_id'] === '0')
				$where [] = ' pt.training_primary_language_option_id = \'' . $criteria ['training_lang1_id'] . '\'';

			if ($criteria ['training_lang2_id'] or $criteria ['training_lang2_id'] === '0')
				$where [] = ' pt.training_secondary_language_option_id = \'' . $criteria ['training_lang2_id'] . '\'';

			if ( $criteria['startdate'] ) {
					$parts = explode('/', $criteria['startdate']);
					$reformattedDate = implode('/', array( @$parts[1], @$parts[0], @$parts[2] ) ); // swap month and date (reverse them)
					$startDate = @date('Y-m-d',@strtotime($reformattedDate));

					$parts2 = explode('/', $criteria['enddate']);
					$reformattedDate = implode('/', array( @$parts2[1], @$parts2[0], @$parts2[2] ) ); // swap month and date (reverse them)
					$endDate = @date('Y-m-d',@strtotime($reformattedDate));

					if (! empty($startDate) && !empty($endDate) )
						$where [] = ' training_start_date >= \'' . $startDate . '\'  AND training_start_date <= \'' . $endDate . '\'  ';
			}

			if (intval ( $criteria ['is_tot'] ))
				$where [] = ' is_tot = ' . $criteria ['is_tot']; // not used

			if ($criteria ['training_funding_id'] or $criteria ['training_funding_id'] === '0')
				$where [] = ' tfund.training_funding_option_id = \'' . $criteria ['training_funding_id'] . '\'';

			if ($criteria ['training_category_id'] or $criteria ['training_category_id'] === '0')
				$where [] = ' tcat.id = \'' . $criteria ['training_category_id'] . '\'';

			if ($criteria ['training_got_id'] or $criteria ['training_got_id'] === '0')
				$where [] = ' tgotc.id = \'' . $criteria ['training_got_id'] . '\'';

			if ($criteria ['training_custom1_id'] or $criteria ['training_custom1_id'] === '0')
				$where [] = ' pt.training_custom_1_option_id = \'' . $criteria ['training_custom1_id'] . '\'';

			if ($criteria ['training_custom2_id'] or $criteria ['training_custom2_id'] === '0')
				$where [] = ' pt.training_custom_2_option_id = \'' . $criteria ['training_custom2_id'] . '\'';

			if ($criteria ['training_custom3_id'] or $criteria ['training_custom3_id'] === '0')
				$where [] = ' pt.custom_3 = \'' . $criteria ['training_custom3_id'] . '\'';

			if ($criteria ['training_custom4_id'] or $criteria ['training_custom4_id'] === '0')
				$where [] = ' pt.custom_4 = \'' . $criteria ['training_custom4_id'] . '\'';

			$where[] = ' evaluation.is_deleted = 0';
			$where[] = ' evaluation_response.is_deleted = 0';
			$where[] = ' evaluation_question.is_deleted = 0';
			$where[] = ' evaluation_question_response.is_deleted = 0';
			if ( $criteria['has_response'] )
				$where[] = ' evaluation_response.evaluation_to_training_id IS NOT NULL ';

			// finish
			if ($where)
				$sql .= ' WHERE ' . implode ( ' AND ', $where );

			$sql .= ' GROUP BY evaluation_question_response.id';

			$rowArray = $db->fetchAll ( $sql );
			// end training lookup

			// output csv if necessary
			if ($this->_getParam ( 'outputType' ))
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );

			//done
		}
		// values for the view
		$this->viewAssignEscaped ( 'results', $rowArray );
		$this->view->assign ( 'count', count($rowArray) );
		$this->view->assign ( 'criteria', $criteria );

		//evaluations drop down
		$evaluationsArray = OptionList::suggestionList ( 'evaluation', 'title', false, false, false );
		$this->viewAssignEscaped ( 'evaluations', $evaluationsArray );
		//trainers
		$trainersArray = $db->fetchAll('select p.id,p.first_name,p.middle_name,p.last_name from trainer left join person p on p.id = person_id order by p.first_name asc');
		foreach ($trainersArray as $i => $row)
			$trainersArray[$i]['fullname'] = $this->setting('display_middle_name_last') ? $row['first_name'].' '.$row['last_name'].' '.$row['middle_name'] :  $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'];
		$this->viewAssignEscaped ( 'trainers', $trainersArray );
		//locations

		$locations = Location::getAll("2");

		$this->viewAssignEscaped('locations', $locations);
		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		//organizers
		$organizersArray = OptionList::suggestionList ( 'training_organizer_option', 'training_organizer_phrase', false, false, false );
		$this->viewAssignEscaped ( 'organizers', $organizersArray );
		//topics
		$topicsArray = OptionList::suggestionList ( 'training_topic_option', 'training_topic_phrase', false, false, false );
		$this->viewAssignEscaped ( 'topics', $topicsArray );
		//levels
		$levelArray = OptionList::suggestionList ( 'training_level_option', 'training_level_phrase', false, false );
		$this->viewAssignEscaped ( 'levels', $levelArray );
		//pepfar
		$organizersArray = OptionList::suggestionList ( 'training_pepfar_categories_option', 'pepfar_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'pepfars', $organizersArray );
		//refresher
		if( $this->setting('multi_opt_refresher_course') ){
			$refresherArray = OptionList::suggestionList ( 'training_refresher_option', 'refresher_phrase_option', false, false, false );
			$this->viewAssignEscaped ( 'refresher', $refresherArray );
		}
		//funding
		$fundingArray = OptionList::suggestionList ( 'training_funding_option', 'funding_phrase', false, false, false );
		$this->viewAssignEscaped ( 'funding', $fundingArray );
		//category
		$categoryArray = OptionList::suggestionList ( 'training_category_option', 'training_category_phrase', false, false, false );
		$this->viewAssignEscaped ( 'category', $categoryArray );
		//primary language
		$langArray = OptionList::suggestionList ( 'trainer_language_option', 'language_phrase', false, false, false );
		$this->viewAssignEscaped ( 'language', $langArray );
		//category+titles
		$categoryTitle = MultiAssignList::getOptions ( 'training_title_option', 'training_title_phrase', 'training_category_option_to_training_title_option', 'training_category_option' );
		$this->view->assign ( 'categoryTitle', $categoryTitle );
		//training methods
		$methodTitle = OptionList::suggestionList ( 'training_method_option', 'training_method_phrase', false, false, false );
		$this->view->assign ( 'methods', $methodTitle );
		//got curric
		$gotCuriccArray = OptionList::suggestionList ( 'training_got_curriculum_option', 'training_got_curriculum_phrase', false, false, false );
		$this->viewAssignEscaped ( 'gotcurric', $gotCuriccArray );
		//mechanism (organizer_partners table)
		$mechanismArray = array();
		if( $this->setting('display_training_partner') ){
			$mechanismArray = OptionList::suggestionList ( 'organizer_partners', 'mechanism_id', false, false, false, "mechanism_id != ''");
		}
		$this->viewAssignEscaped ( 'mechanisms', $mechanismArray );

		//customfields
		$customArray = OptionList::suggestionList ( 'training_custom_1_option', 'custom1_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom1', $customArray );
		$customArray2 = OptionList::suggestionList ( 'training_custom_2_option', 'custom2_phrase', false, false, false );
		$this->viewAssignEscaped ( 'custom2', $customArray2 );
		$customArray3 = OptionList::suggestionList ( 'training', 'custom_3', false, false, false, "custom_3 != ''" );
		$this->viewAssignEscaped ( 'custom3', $customArray3 );
		$customArray4 = OptionList::suggestionList ( 'training', 'custom_4', false, false, false, "custom_4 != ''" );
		$this->viewAssignEscaped ( 'custom4', $customArray4 );
		//$customArray5 = OptionList::suggestionList ( 'training', 'custom_5', false, false, false, "custom_5 != ''" );
		//$this->viewAssignEscaped ( 'custom5', $customArray5 );
		#$createdByArray = $db->fetchAll("select id,CONCAT(first_name, CONCAT(' ', last_name)) as name from user where is_blocked = 0");
		#$this->viewAssignEscaped ( 'createdBy', $createdByArray );
		#// find category based on title
		#$catId = NULL;
		#if ($criteria ['training_category_id']) {
		#	foreach ( $categoryTitle as $r ) {
		#		if ($r ['id'] == $criteria ['training_category_id']) {
		#			$catId = $r ['training_category_option_id'];
		#			break;
		#		}
		#	}
		#}
		#$this->view->assign ( 'catId', $catId );
		//done
	}

	public function psTrainingsByNameAction() {
		$this->view->assign ( 'mode', 'id' );
		#		return $this->trainingReport ();
	}

	public function psTrainingByParticipantsAction() {
		$this->view->assign ( 'mode', 'id' );
		#		return $this->trainingReport ();
	}

	private function institution_link_exists($join){
		$found = false;
		foreach ($join as $j){
			if ($j['table'] == "institution"){
				$found = true;
			}
		}

		return $found;
	}

	public function psStudentsTrainedAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());

		if ($this->getSanParam ( 'process' )){

			$maintable = "person p";
			$select = array();
			//$select[] = "p.id as personid";
			$select[] = "p.first_name";
			$select[] = "p.last_name";

			$headers[] = "First Name";
			$headers[] = "Surname";

			$join = array();
			$join[] = array(
				"table" => "student",
				"abbreviation" => "s",
				"compare" => "s.personid = p.id",
				"type" => "inner"
			);

			$where = array();
			$where[] = "p.is_deleted = 0";

			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();


			// region
			if( $this->getSanParam('showProvince') || $this->getSanParam('province_id') || $this->getSanParam('showDistrict') || $this->getSanParam('district_id')){
				$join[] = array(
					"table" => "location",
					"abbreviation" => "loc",
					"compare" => "loc.id = s.geog1",
					"type" => "left"
				);
				$join[] = array(
					"table" => "location_district",
					"abbreviation" => "locd",
					"compare" => "locd.id = s.geog2",
					"type" => "left"
				);

				if( $this->getSanParam('showProvince') ){
					$select[] = "loc.location_name";
					$headers[] = "Province";
				}
				if( $this->getSanParam('showDistrict') ){
					$select[] = "locd.district_name";
					$headers[] = "District";
				}
			}
			$province_arr = $this->getSanParam('province_id');
			if( !empty($province_arr) ){
				$clause = ''; $or_str = '';
				foreach($province_arr as $item){
					$clause .= "{$or_str}loc.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}
			$district_arr = $this->getSanParam('district_id');
			if( !empty($district_arr) ){
				$clause = ''; $or_str = '';
				foreach($district_arr as $item){
					$clause .= "{$or_str}locd.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}

			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = s.institutionid",
					"type" => "left"
				);
				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}


			if ($this->getSanParam ( 'showcohort' )){
				$select[] = "c.cohortname";
				$headers[] = "Cohort";

				$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
				);

				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
				);

				if ($this->getSanParam('cohort')){
					$where[] = "c.id = " . $this->getSanParam('cohort');
				}
			}

			if ($this->getSanParam ( 'showcadre' )){
				$select[] = "ca.cadrename";
				$headers[] = "Cadre";

				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "ca",
					"compare" => "ca.id = s.cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "ca.id = " . $this->getSanParam('cadre');
				}
			}


			if ($this->getSanParam ( 'showyearinschool' )){

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_student_cohort",
						"abbreviation" => "lsc",
						"compare" => "lsc.id_student = s.id",
						"type" => "left"
					);

					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "c",
						"compare" => "c.id = lsc.id_cohort",
						"type" => "left"
					);
				}

				$select[] = "c.startdate";
				$headers[] = "Start Date";
				if ($this->getSanParam('yearinschool')){
					$where[] = "c.startdate LIKE '" . mysql_real_escape_string(substr($this->getSanParam('yearinschool'), 0, 4)) . "%'";
				}
			}

			// gender
			if( $this->getSanParam('showgender') ){
				$select[] = "p.gender";
				$headers[] = "Gender";
			}
			if ( $this->getSanParam('gender') ){
				$gender_id = $this->getSanParam('gender');
				if($gender_id > 0){
					$gender_arr = array(1 => 'male', 2 => 'female');
					$where[] = "p.gender = '{$gender_arr[$gender_id]}'";
				}
			}

			// nationalities
			if( $this->getSanParam('shownationality') ){
				$select[] = "ln.nationality";
				$headers[] = "Nationality";

				$join[] = array(
					"table" => "lookup_nationalities",
					"abbreviation" => "ln",
					"compare" => "ln.id = s.nationalityid",
					"type" => "left"
				);
			}

			// age
			if( $this->getSanParam('showage') ){
				$select[] = "DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birthdate)), '%Y')+0 AS age";
				$headers[] = "Age";
			}
			if($this->getSanParam('agemin') || $this->getSanParam('agemax')){
				$year_secs = 60 * 60 * 24 * 365;
				if($this->getSanParam('agemin') && $this->getSanParam('agemax')){
					$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
					$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
					$where[] = "p.birthdate BETWEEN '{$max_age_birthdate}' AND '{$min_age_birthdate}'";
				} else {
					if ( $this->getSanParam('agemin') ){
						$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
						$where[] = "p.birthdate <= '{$min_age_birthdate}'";
					}
					if ( $this->getSanParam('agemax') ){
						$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
						$where[] = "p.birthdate >= '{$max_age_birthdate}'";
					}
				}
			}

			// active
			if( $this->getSanParam('showactive') ){
				$select[] = "p.active";
				$headers[] = "Active";
				$where[] = "p.active = 'active'";
			}

			// terminated early
			if( $this->getSanParam('showterminated') ){
				$select[] = "IF(lsc.isgraduated = 0 AND lsc.dropdate != '0000-00-00', 'Terminated Early', '')";
				$headers[] = "Terminated Early";

				$where[] = "lsc.isgraduated = 0";
				$where[] = "lsc.dropdate != '0000-00-00'";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}

			// graduated
			if( $this->getSanParam('showgraduated') ){
				$select[] = "IF(lsc.isgraduated = 1, 'Graduated', '')";
				$headers[] = "Graduated";
				$where[] = "lsc.isgraduated = 1";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}

			// funding source
			if( $this->getSanParam('showfunding') ){
				$select[] = "lf.fundingname";
				$headers[] = "Funding";

				$join[] = array(
					"table" => "link_student_funding",
					"abbreviation" => "lsf",
					"compare" => "lsf.studentid = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_fundingsources",
					"abbreviation" => "lf",
					"compare" => "lf.id = lsf.fundingsource",
					"type" => "left"
				);
			}

			// facility
			if( $this->getSanParam('showfacility') ){
				$select[] = "fac.facility_name";
				$headers[] = "Facility";
			}
			if( $this->getSanParam('facility') ){
				$where[] = "fac.id = ".$this->getSanParam('facility');
			}
			if( $this->getSanParam('showfacility') || $this->getSanParam('facility') ){
				$join[] = array(
					"table" => "link_student_facility",
					"abbreviation" => "lsfac",
					"compare" => "lsfac.id_student = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "facility",
					"abbreviation" => "fac",
					"compare" => "fac.id = lsfac.id_facility",
					"type" => "left"
				);
			}

			// tutor advisor
			if( $this->getSanParam('showtutor') ){
				$select[] = "CONCAT(tutp.first_name,' ',tutp.last_name) AS tutor_name";
				$headers[] = "Tutor Advisor";
			}
			if( $this->getSanParam('tutor') ){
				$where[] = "tut.id = ".$this->getSanParam('tutor');
			}
			if( $this->getSanParam('showtutor') || $this->getSanParam('tutor') ){

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
					);

					$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
					);
				}

				$join[] = array(
					"table" => "link_cadre_tutor",
					"abbreviation" => "lct",
					"compare" => "lct.id_cadre = c.cadreid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "tutor",
					"abbreviation" => "tut",
					"compare" => "tut.id = lct.id_tutor",
					"type" => "left"
				);
				$join[] = array(
					"table" => "person",
					"abbreviation" => "tutp",
					"compare" => "tutp.id = tut.personid",
					"type" => "left"
				);
			}

			// return unique participants
			// ..

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "c.startdate";
				$headers[] = "Start Date";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_student_cohort",
						"abbreviation" => "lsc",
						"compare" => "lsc.id_student = s.id",
						"type" => "left"
					);

					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "c",
						"compare" => "c.id = lsc.id_cohort",
						"type" => "left"
					);
				}
			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "c.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "c.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "c.startdate <= '{$end_date}'";
				}
			}

			/*
			if( !$this->institution_link_exists($join) ){
				$join[] = array(
					"table" => "link_student_institution",
					"abbreviation" => "lsi",
					"compare" => "lsi.id_student = s.id",
					"type" => "left"
				);
			}
			*/

			// filter by user institution
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "s.institutionid IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}

			//echo $query;

			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query', $query);
			//echo $query;

			$this->viewAssignEscaped("headers", $headers);
			$this->viewAssignEscaped("output", $rowArray);
			$this->view->assign('query', "");

			$this->view->criteria = $_GET;
		}
		#		return $this->trainingReport ();
	}


	public function psStudentsByNameAction() {

		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		#		return $this->trainingReport ();

		if ($this->getSanParam ( 'process' )){

			$maintable = "person p";
			$select = array();
			//$select[] = "p.id as personid";
			$select[] = "p.first_name";
			$select[] = "p.last_name";

			$headers[] = "First Name";
			$headers[] = "Surname";

			$join = array();
			$join[] = array(
				"table" => "student",
				"abbreviation" => "s",
				"compare" => "s.personid = p.id",
				"type" => "inner"
			);

			$where = array();
			$where[] = "p.is_deleted = 0";

			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// region
			if( $this->getSanParam('showProvince') || $this->getSanParam('province_id') || $this->getSanParam('showDistrict') || $this->getSanParam('district_id')){
				$join[] = array(
					"table" => "location",
					"abbreviation" => "loc",
					"compare" => "loc.id = s.geog1",
					"type" => "left"
				);
				$join[] = array(
					"table" => "location_district",
					"abbreviation" => "locd",
					"compare" => "locd.id = s.geog2",
					"type" => "left"
				);

				if( $this->getSanParam('showProvince') ){
					$select[] = "loc.location_name";
					$headers[] = "Province";
				}
				if( $this->getSanParam('showDistrict') ){
					$select[] = "locd.district_name";
					$headers[] = "District";
				}
			}
			$province_arr = $this->getSanParam('province_id');
			if( !empty($province_arr) ){
				$clause = ''; $or_str = '';
				foreach($province_arr as $item){
					$clause .= "{$or_str}loc.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}
			$district_arr = $this->getSanParam('district_id');
			if( !empty($district_arr) ){
				$clause = ''; $or_str = '';
				foreach($district_arr as $item){
					$clause .= "{$or_str}locd.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}

			// institution
			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = s.institutionid",
					"type" => "left"
				);
				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ( 'showcadre' )){
				$select[] = "ca.cadrename";
				$headers[] = "Cadre";

				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "ca",
					"compare" => "ca.id = s.cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
				$where[] = "ca.id = " . $this->getSanParam('cadre');
				}
			}

			// cohort
			if ($this->getSanParam ( 'showcohort' )){
				$select[] = "c.cohortname";
				$headers[] = "Cohort";

				$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
				);

				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
				);

				if ($this->getSanParam('cohort')){
				$where[] = "c.id = " . $this->getSanParam('cohort');
				}
			}

			// year in school
			if ($this->getSanParam ( 'showyearinschool' )){
				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
					$found = true;
					}
				}

				if (!$found){
					$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
					);

					$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
					);
				}
				$select[] = "c.startdate";
				$headers[] = "Start Date";
				if ($this->getSanParam('yearinschool')){
					$where[] = "c.startdate LIKE '" . mysql_real_escape_string(substr($this->getSanParam('yearinschool'), 0, 4)) . "%'";
				}
			}

			// gender
			if( $this->getSanParam('showgender') ){
				$select[] = "p.gender";
				$headers[] = "Gender";
			}
			if ( $this->getSanParam('gender') ){
				$gender_id = $this->getSanParam('gender');
				if($gender_id > 0){
					$gender_arr = array(1 => 'male', 2 => 'female');
					$where[] = "p.gender = '{$gender_arr[$gender_id]}'";
				}
			}

			// nationalities
			if($this->getSanParam('shownationality') || $this->getSanParam('nationality')){
				$join[] = array(
					"table" => "lookup_nationalities",
					"abbreviation" => "ln",
					"compare" => "ln.id = s.nationalityid",
					"type" => "left"
				);
			}
			if( $this->getSanParam('shownationality') ){
				$select[] = "ln.nationality";
				$headers[] = "Nationality";
			}
			if( $this->getSanParam('nationality') ){
				$where[] = "ln.id = ".$this->getSanParam('nationality');
			}

			// age
			if( $this->getSanParam('showage') ){
				$select[] = "DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birthdate)), '%Y')+0 AS age";
				$headers[] = "Age";
			}
			if($this->getSanParam('agemin') || $this->getSanParam('agemax')){
				$year_secs = 60 * 60 * 24 * 365;
				if($this->getSanParam('agemin') && $this->getSanParam('agemax')){
					$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
					$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
					$where[] = "p.birthdate BETWEEN '{$max_age_birthdate}' AND '{$min_age_birthdate}'";
				} else {
					if ( $this->getSanParam('agemin') ){
						$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
						$where[] = "p.birthdate <= '{$min_age_birthdate}'";
					}
					if ( $this->getSanParam('agemax') ){
						$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
						$where[] = "p.birthdate >= '{$max_age_birthdate}'";
					}
				}
			}

			// Course Name And Exam Scores To Date
			// ..

			// active
			if( $this->getSanParam('showactive') ){
				$select[] = "p.active";
				$headers[] = "Active";
				$where[] = "p.active = 'active'";
			}

			// terminated early
			if( $this->getSanParam('showterminated') ){
				$select[] = "IF(lsc.isgraduated = 0 AND lsc.dropdate != '0000-00-00', 'Terminated Early', '')";
				$headers[] = "Terminated Early";

				$where[] = "lsc.isgraduated = 0";
				$where[] = "lsc.dropdate != '0000-00-00'";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}

			// graduated
			if( $this->getSanParam('showgraduated') ){
				$select[] = "IF(lsc.isgraduated = 1, 'Graduated', '')";
				$headers[] = "Graduated";
				$where[] = "lsc.isgraduated = 1";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}

			// funding source
			if( $this->getSanParam('showfunding') ){
				$select[] = "lf.fundingname";
				$headers[] = "Funding";

				$join[] = array(
					"table" => "link_student_funding",
					"abbreviation" => "lsf",
					"compare" => "lsf.studentid = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_fundingsources",
					"abbreviation" => "lf",
					"compare" => "lf.id = lsf.fundingsource",
					"type" => "left"
				);
			}

			// facility
			if( $this->getSanParam('showfacility') ){
				$select[] = "fac.facility_name";
				$headers[] = "Facility";
			}
			if( $this->getSanParam('facility') ){
				$where[] = "fac.id = ".$this->getSanParam('facility');
			}
			if( $this->getSanParam('showfacility') || $this->getSanParam('facility') ){
				$join[] = array(
					"table" => "link_student_facility",
					"abbreviation" => "lsfac",
					"compare" => "lsfac.id_student = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "facility",
					"abbreviation" => "fac",
					"compare" => "fac.id = lsfac.id_facility",
					"type" => "left"
				);
			}

			// tutor advisor
			if( $this->getSanParam('showtutor') ){
				$select[] = "CONCAT(tutp.first_name,' ',tutp.last_name) AS tutor_name";
				$headers[] = "Tutor Advisor";
			}
			if( $this->getSanParam('tutor') ){
				$where[] = "tut.id = ".$this->getSanParam('tutor');
			}
			if( $this->getSanParam('showtutor') || $this->getSanParam('tutor') ){

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
					);

					$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
					);
				}

				$join[] = array(
					"table" => "link_cadre_tutor",
					"abbreviation" => "lct",
					"compare" => "lct.id_cadre = c.cadreid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "tutor",
					"abbreviation" => "tut",
					"compare" => "tut.id = lct.id_tutor",
					"type" => "left"
				);
				$join[] = array(
					"table" => "person",
					"abbreviation" => "tutp",
					"compare" => "tutp.id = tut.personid",
					"type" => "left"
				);
			}

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "c.startdate";
				$headers[] = "Start Date";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_student_cohort",
						"abbreviation" => "lsc",
						"compare" => "lsc.id_student = s.id",
						"type" => "left"
					);

					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "c",
						"compare" => "c.id = lsc.id_cohort",
						"type" => "left"
					);
				}
			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "c.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "c.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "c.startdate <= '{$end_date}'";
				}
			}

			// filter by user institution
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "s.institutionid IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);

			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output', $rowArray);
			$this->view->assign('query',"");

			$this->view->criteria = $_GET;
		}

	}

	public function psGraduatedStudentsAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		#		return $this->trainingReport ();
		if ($this->getSanParam ( 'process' )){

			$maintable = "person p";
			$select = array();
			//$select[] = "p.id as personid";
			$select[] = "p.first_name";
			$select[] = "p.last_name";

			$headers[] = "First Name";
			$headers[] = "Surname";

			$join = array();
			$join[] = array(
				"table" => "student",
				"abbreviation" => "s",
				"compare" => "s.personid = p.id",
				"type" => "inner"
			);

			$where = array();
			$where[] = "p.is_deleted = 0";

			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// institution
			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = s.institutionid",
					"type" => "left"
				);
				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ('showcadre')){
				$select[] = "ca.cadrename";
				$headers[] = "Cadre";

				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "ca",
					"compare" => "ca.id = s.cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "ca.id = " . $this->getSanParam('cadre');
				}
			}

			// cohort
			if ($this->getSanParam ( 'showcohort' )){
				$select[] = "c.cohortname";
				$headers[] = "Cohort";

				$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
				);

				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
				);

				if ($this->getSanParam('cohort')){
					$where[] = "c.id = " . $this->getSanParam('cohort');
				}
			}

			// degree
			if($this->getSanParam('showdegrees') || $this->getSanParam('degrees')){
				# REQUIRES INSTITUTION LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "institution"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = s.institutionid",
						"type" => "left"
					);
				}

				$join[] = array(
					"table" => "link_institution_degrees",
					"abbreviation" => "liddeg",
					"compare" => "liddeg.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_degrees",
					"abbreviation" => "ldeg",
					"compare" => "ldeg.id = liddeg.id_degree",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showdegrees') ){
				$select[] = "ldeg.degree";
				$headers[] = "Degree";
			}
			if( $this->getSanParam('degrees') ){
				$where[] = "ldeg.id = ".$this->getSanParam('degrees');
			}

			// gender
			if( $this->getSanParam('showgender') ){
				$select[] = "p.gender";
				$headers[] = "Gender";
			}
			if ( $this->getSanParam('gender') ){
				$gender_id = $this->getSanParam('gender');
				if($gender_id > 0){
					$gender_arr = array(1 => 'male', 2 => 'female');
					$where[] = "p.gender = '{$gender_arr[$gender_id]}'";
				}
			}

			// nationalities
			if($this->getSanParam('shownationality') || $this->getSanParam('nationality')){
				$join[] = array(
					"table" => "lookup_nationalities",
					"abbreviation" => "ln",
					"compare" => "ln.id = s.nationalityid",
					"type" => "left"
				);
			}
			if( $this->getSanParam('shownationality') ){
				$select[] = "ln.nationality";
				$headers[] = "Nationality";
			}
			if( $this->getSanParam('nationality') ){
				$where[] = "ln.id = ".$this->getSanParam('nationality');
			}

			// age
			if( $this->getSanParam('showage') ){
				$select[] = "DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birthdate)), '%Y')+0 AS age";
				$headers[] = "Age";
			}
			if($this->getSanParam('agemin') || $this->getSanParam('agemax')){
				$year_secs = 60 * 60 * 24 * 365;
				if($this->getSanParam('agemin') && $this->getSanParam('agemax')){
					$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
					$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
					$where[] = "p.birthdate BETWEEN '{$max_age_birthdate}' AND '{$min_age_birthdate}'";
				} else {
					if ( $this->getSanParam('agemin') ){
						$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
						$where[] = "p.birthdate <= '{$min_age_birthdate}'";
					}
					if ( $this->getSanParam('agemax') ){
						$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
						$where[] = "p.birthdate >= '{$max_age_birthdate}'";
					}
				}
			}

			// graduation date
			if($this->getSanParam('showgraduation')){
				$select[] = "c.graddate";
				$headers[] = "Graduation Date";
				$where[] = "lsc.isgraduated = 1";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}

			// facility
			if( $this->getSanParam('showfacility') ){
				$select[] = "fac.facility_name";
				$headers[] = "Facility";
			}
			if( $this->getSanParam('facility') ){
				$where[] = "fac.id = ".$this->getSanParam('facility');
			}
			if( $this->getSanParam('showfacility') || $this->getSanParam('facility') ){
				$join[] = array(
					"table" => "link_student_facility",
					"abbreviation" => "lsfac",
					"compare" => "lsfac.id_student = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "facility",
					"abbreviation" => "fac",
					"compare" => "fac.id = lsfac.id_facility",
					"type" => "left"
				);
			}

			// tutor advisor
			if( $this->getSanParam('showtutor') ){
				$select[] = "CONCAT(tutp.first_name,' ',tutp.last_name) AS tutor_name";
				$headers[] = "Tutor Advisor";
			}
			if( $this->getSanParam('tutor') ){
				$where[] = "tut.id = ".$this->getSanParam('tutor');
			}
			if( $this->getSanParam('showtutor') || $this->getSanParam('tutor') ){

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lsc",
					"compare" => "lsc.id_student = s.id",
					"type" => "left"
					);

					$join[] = array(
					"table" => "cohort",
					"abbreviation" => "c",
					"compare" => "c.id = lsc.id_cohort",
					"type" => "left"
					);
				}

				$join[] = array(
					"table" => "link_cadre_tutor",
					"abbreviation" => "lct",
					"compare" => "lct.id_cadre = c.cadreid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "tutor",
					"abbreviation" => "tut",
					"compare" => "tut.id = lct.id_tutor",
					"type" => "left"
				);
				$join[] = array(
					"table" => "person",
					"abbreviation" => "tutp",
					"compare" => "tutp.id = tut.personid",
					"type" => "left"
				);
			}

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "c.startdate";
				$headers[] = "Start Date";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_student_cohort",
						"abbreviation" => "lsc",
						"compare" => "lsc.id_student = s.id",
						"type" => "left"
					);

					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "c",
						"compare" => "c.id = lsc.id_cohort",
						"type" => "left"
					);
				}
			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "c.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "c.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "c.startdate <= '{$end_date}'";
				}
			}

			// filter by user institution
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "s.institutionid IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}

			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);

			//echo $query;
			# exit;

			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output', $rowArray);
			$this->view->assign('query', "");

			$this->view->criteria = $_GET;
		}
	}

	public function psCourseByStudentCountAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());

		#		return $this->trainingReport ();
		if ($this->getSanParam ( 'process' )){

			$maintable = "classes class";
			$select = array();
			//$select[] = "class.id";
			$select[] = "class.classname";
			$select[] = "(SELECT COUNT(*) FROM link_student_classes WHERE classid = class.id) AS student_count";

			$headers[] = "Class Name";
			$headers[] = "Student Count";

			$institution_set = false;

			$where = array();
			$join = array();
			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// institution
			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "link_cohorts_classes",
					"abbreviation" => "lcc",
					"compare" => "lcc.cohortid = class.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "coh",
					"compare" => "coh.id = lcc.cohortid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;

				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ('showcadre')){
				$select[] = "cad.cadrename";
				$headers[] = "Cadre";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				$join[] = array(
					"table" => "link_cadre_institution",
					"abbreviation" => "cai",
					"compare" => "cai.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "cad",
					"compare" => "cad.id = cai.id_cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "cad.id = " . $this->getSanParam('cadre');
				}
			}

			// cohort
			if ($this->getSanParam ( 'showcohort' )){
				$select[] = "coh.cohortname";
				$headers[] = "Cohort";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				if ($this->getSanParam('cohort')){
					$where[] = "coh.id = " . $this->getSanParam('cohort');
				}
			}

			// year in school
			if ($this->getSanParam ( 'showyearinschool' )){
				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
					$found = true;
					}
				}

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}
				$select[] = "coh.startdate";
				$headers[] = "Start Date";
				if ($this->getSanParam('yearinschool')){
					$where[] = "coh.startdate LIKE '" . mysql_real_escape_string(substr($this->getSanParam('yearinschool'), 0, 4)) . "%'";
				}
			}

			if ( $this->getSanParam('showcoursename') ){
				$select[] = "class.classname";
				$headers[] = "Course Name";
			}
			if( $this->getSanParam('coursename') ){
				$course_name = $this->getSanParam('coursename');
				$where[] = "classname LIKE '%{$this->getSanParam('coursename')}%'";
			}

			// course/classes type
			if( $this->getSanParam('showcoursetype') || $this->getSanParam('coursetype') ){
				$join[] = array(
					"table" => "lookup_coursetype",
					"abbreviation" => "ctype",
					"compare" => "ctype.id = class.coursetypeid",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showcoursetype') ){
				$select[] = "ctype.coursetype";
				$headers[] = "Course Type";
			}
			if( $this->getSanParam('coursetype') ){
				$where[] = "ctype.id = ".$this->getSanParam('coursetype');
			}

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "c.startdate";
				$headers[] = "Start Date";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "coh.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "coh.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "coh.startdate <= '{$end_date}'";
				}
			}

			/*
			if( $this->getSanParam('showgrades') || $this->getSanParam('grades') ){
				///////
			}

			// grades
			if( $this->getSanParam('showgrades') ){
				$select[] = "lsclass.grade";
				$headers[] = "Grade";
			}
			if( $this->getSanParam('grades') ){
				$grade = $this->getSanParam('grades');
				$where[] = "lsclass.grade LIKE '%{$grade}%'";
			}
			*/

			// topic
			if( $this->getSanParam('showtopic') ){
				$select[] = "class.coursetopic";
				$headers[] = "Topic";
			}
			if( $this->getSanParam('topic') ){
				$topic = $this->getSanParam('topic');
				$where[] = "class.coursetopic LIKE '%{$topic}%'";
			}

			// filter by user institution
			if(!$institution_set){
				$join[] = array(
					"table" => "link_cohorts_classes",
					"abbreviation" => "lcc",
					"compare" => "lcc.cohortid = class.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "coh",
					"compare" => "coh.id = lcc.cohortid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;
			}
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "i.id IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);

			//echo $query;
			# exit;

			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output', $rowArray);
			$this->view->assign('query', "");

			$this->view->criteria = $_GET;
		}
	}

	public function psCourseByNameAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		$this->view->assign ( 'lookuplanguages', $helper->getLanguages());

		#		return $this->trainingReport ();
		if ($this->getSanParam ( 'process' )){

			$maintable = "classes class";
			$select = array();
			//$select[] = "class.id";
			$select[] = "class.classname";

			$headers[] = "Class Name";

			$institution_set = false;

			$where = array();
			$join = array();
			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// institution
			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "link_cohorts_classes",
					"abbreviation" => "lcc",
					"compare" => "lcc.cohortid = class.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "coh",
					"compare" => "coh.id = lcc.cohortid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;

				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ('showcadre')){
				$select[] = "cad.cadrename";
				$headers[] = "Cadre";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				$join[] = array(
					"table" => "link_cadre_institution",
					"abbreviation" => "cai",
					"compare" => "cai.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "cad",
					"compare" => "cad.id = cai.id_cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "cad.id = " . $this->getSanParam('cadre');
				}
			}

			// cohort
			if ($this->getSanParam ( 'showcohort' )){
				$select[] = "coh.cohortname";
				$headers[] = "Cohort";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				if ($this->getSanParam('cohort')){
					$where[] = "coh.id = " . $this->getSanParam('cohort');
				}
			}

			// year in school
			if ($this->getSanParam ( 'showyearinschool' )){
				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "cohort"){
					$found = true;
					}
				}

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}
				$select[] = "coh.startdate";
				$headers[] = "Start Date";
				if ($this->getSanParam('yearinschool')){
					$where[] = "coh.startdate LIKE '" . mysql_real_escape_string(substr($this->getSanParam('yearinschool'), 0, 4)) . "%'";
				}
			}

			/*
			if ( $this->getSanParam('showcoursename') ){
				$select[] = "class.classname";
				$headers[] = "Course Name";
			}
			*/

			if( $this->getSanParam('coursename') ){
				$course_name = $this->getSanParam('coursename');
				$where[] = "class.classname LIKE '%{$course_name}%'";
			}

			// course/classes type
			if( $this->getSanParam('showcoursetype') || $this->getSanParam('coursetype') ){
				$join[] = array(
					"table" => "lookup_coursetype",
					"abbreviation" => "ctype",
					"compare" => "ctype.id = class.coursetypeid",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showcoursetype') ){
				$select[] = "ctype.coursetype";
				$headers[] = "Course Type";
			}
			if( $this->getSanParam('coursetype') ){
				$where[] = "ctype.id = ".$this->getSanParam('coursetype');
			}

			/*
			if( $this->getSanParam('showgrades') || $this->getSanParam('grades') ){
				///////
			}

			// grades
			if( $this->getSanParam('showgrades') ){
				$select[] = "lsclass.grade";
				$headers[] = "Grade";
			}
			if( $this->getSanParam('grades') ){
				$grade = $this->getSanParam('grades');
				$where[] = "lsclass.grade LIKE '%{$grade}%'";
			}
			*/

			// topic
			if( $this->getSanParam('showtopic') ){
				$select[] = "tto.training_topic_phrase";
				$headers[] = "Topic";
				$join[] = array(
					"table" => "training_topic_option",
					"abbreviation" => "tto",
					"compare" => "tto.id = class.coursetopic",
					"type" => "left"
				);
			}
			if( $this->getSanParam('topic') ){
				$topic = $this->getSanParam('topic');
				$where[] = "class.coursetopic LIKE '%{$topic}%'";
			}

			// # of exams
			// ..

			// # of students
			if( $this->getSanParam('showstudentcount') || $this->getSanParam('studentcount') ){

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}
			}
			if( $this->getSanParam('showstudentcount') ){
				$select[] = "i.studentcount";
				$headers[] = "Student Count";
			}
			if( $this->getSanParam('studentcount') ){
				$where[] = "i.studentcount = ".$this->getSanParam('studentcount');

			}

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "coh.startdate";
				$headers[] = "Start Date";

				if(!$institution_set){
					$join[] = array(
						"table" => "link_cohorts_classes",
						"abbreviation" => "lcc",
						"compare" => "lcc.cohortid = class.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "cohort",
						"abbreviation" => "coh",
						"compare" => "coh.id = lcc.cohortid",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}
			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "coh.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "coh.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "coh.startdate <= '{$end_date}'";
				}
			}

			// filter by user institution
			if(!$institution_set){
				$join[] = array(
					"table" => "link_cohorts_classes",
					"abbreviation" => "lcc",
					"compare" => "lcc.cohortid = class.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cohort",
					"abbreviation" => "coh",
					"compare" => "coh.id = lcc.cohortid",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;
			}
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "i.id IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);
			//echo $query;
			# exit;

			$this->view->criteria = $_GET;
		}
	}

	public function psCohortByParticipantCountAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'facilitytypes', $helper->getFacilityTypes());
		$this->view->assign ( 'sponsors', $helper->getSponsors());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		$this->view->assign ( 'lookuplanguages', $helper->getLanguages());

		if ($this->getSanParam ( 'process' )){

			$maintable = "cohort coh";
			$select = array();
			//$select[] = "coh.id AS cohort_id";
			$select[] = "coh.cohortname";

			$headers[] = "Cohort Name";

			$institution_set = false;

			$join = array();
			$where = array();
			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			$count_query_joins = '';
			$count_query_where = '';

			// institution
			if ($this->getSanParam ( 'showinstitution' )){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;

				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ('showcadre')){
				$select[] = "cad.cadrename";
				$headers[] = "Cadre";

				if(!$institution_set){
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				$join[] = array(
					"table" => "link_cadre_institution",
					"abbreviation" => "cai",
					"compare" => "cai.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "cad",
					"compare" => "cad.id = cai.id_cadre",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "cad.id = " . $this->getSanParam('cadre');
				}
			}

			// degree
			if($this->getSanParam('showdegree') || $this->getSanParam('degree')){
				if(!$institution_set){
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = coh.institutionid",
						"type" => "left"
					);
					$institution_set = true;
				}

				$join[] = array(
					"table" => "link_institution_degrees",
					"abbreviation" => "liddeg",
					"compare" => "liddeg.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_degrees",
					"abbreviation" => "ldeg",
					"compare" => "ldeg.id = liddeg.id_degree",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showdegree') ){
				$select[] = "ldeg.degree";
				$headers[] = "Degree";
			}
			if( $this->getSanParam('degree') ){
				$where[] = "ldeg.id = ".$this->getSanParam('degree');
			}

			// gender
			$count_query_joins .= " INNER JOIN student ON student.id = link_student_cohort.id_student INNER JOIN person ON person.id = student.personid ";
			if( $this->getSanParam('gender') ){
				$gender_id = $this->getSanParam('gender');
				if($gender_id > 0){
					$count_query_where .= " AND person.gender = '{$gender_arr[$gender_id]}'";
				}
			}

			// nationalities
			if( $this->getSanParam('nationality') ){
				$national_id = $this->getSanParam('nationality');
				$count_query_where .= " AND person.national_id = '{$national_id}'";
			}

			if($this->getSanParam('agemin') || $this->getSanParam('agemax')){
				$year_secs = 60 * 60 * 24 * 365;
				if($this->getSanParam('agemin') && $this->getSanParam('agemax')){
					$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
					$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
					$count_query_where .= " AND (person.birthdate BETWEEN '{$max_age_birthdate}' AND '{$min_age_birthdate}') ";
				} else {
					if ( $this->getSanParam('agemin') ){
						$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
						$count_query_where .= " AND person.birthdate <= '{$min_age_birthdate}' ";
					}
					if ( $this->getSanParam('agemax') ){
						$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
						$count_query_where .= " AND person.birthdate >= '{$max_age_birthdate}' ";
					}
				}
			}

			// graduation date
			if($this->getSanParam('showgraduationdate')){
				$select[] = "coh.graddate";
				$headers[] = "Graduation Date";
			}

			// Course Name and Exam Scores to Date
			// ..
			// TODO : how?

			// student names
			// ..
			// TODO : how?

			// active
			if( $this->getSanParam('showactive') ){
				$count_query_where .= " AND person.active = 'active' ";
			}

			/* TODO : how?
			// terminated early
			if( $this->getSanParam('showterminated') ){
				$select[] = "IF(lsc.isgraduated = 0 AND lsc.dropdate != '0000-00-00', 'Terminated Early', '')";
				$headers[] = "Terminated Early";

				$where[] = "lsc.isgraduated = 0";
				$where[] = "lsc.dropdate != '0000-00-00'";

				# REQUIRES COHORT LINK
				$found = false;
				foreach ($join as $j){ if ($j['table'] == "cohort"){ $found = true; } }
				if (!$found){
					$join[] = array("table" => "link_student_cohort", "abbreviation" => "lsc", "compare" => "lsc.id_student = s.id", "type" => "left");
					$join[] = array("table" => "cohort", "abbreviation" => "c", "compare" => "c.id = lsc.id_cohort", "type" => "left");
				}
			}
			*/

			// graduated
			if( $this->getSanParam('showgraduated') ){
				$where[] = "coh.graddate != '0000-00-00'";
			}

			/* TODO : how?
			// funding source
			if( $this->getSanParam('showfunding') ){
				$select[] = "lf.fundingname";
				$headers[] = "Funding";

				$join[] = array(
					"table" => "link_student_funding",
					"abbreviation" => "lsf",
					"compare" => "lsf.studentid = s.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_fundingsources",
					"abbreviation" => "lf",
					"compare" => "lf.id = lsf.fundingsource",
					"type" => "left"
				);
			}
			*/

			// student names
			if( $this->getSanParam('showstudentnames') ){
				$select[] = "CONCAT(p.first_name, ' ', p.last_name) AS student_name";
				$headers[] = "Student";
				$join[] = array(
					"table" => "link_student_cohort",
					"abbreviation" => "lscoh",
					"compare" => "lscoh.id_cohort = coh.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "student",
					"abbreviation" => "st",
					"compare" => "st.id = lscoh.id_student",
					"type" => "left"
				);
				$join[] = array(
					"table" => "person",
					"abbreviation" => "p",
					"compare" => "p.id = st.personid",
					"type" => "left"
				);
			}

			// start date between
			$start_date = '';
			if($this->getSanParam('startday') && $this->getSanParam('startmonth') && $this->getSanParam('startyear')){
				$start_date = $this->getSanParam('startyear').'-'.$this->getSanParam('startmonth').'-'.$this->getSanParam('startday');
			}
			$end_date = '';
			if($this->getSanParam('endday') && $this->getSanParam('endmonth') && $this->getSanParam('endyear')){
				$end_date = $this->getSanParam('endyear').'-'.$this->getSanParam('endmonth').'-'.$this->getSanParam('endday');
			}
			if(($start_date != '') || ($end_date != '')){
				$select[] = "coh.startdate";
				$headers[] = "Start Date";
			}
			if(($start_date != '') && ($end_date != '')){
				$where[] = "coh.startdate BETWEEN '{$start_date}' AND '{$end_date}'";
			} else {
				if ($start_date != ''){
					$where[] = "coh.startdate >= '{$start_date}'";
				}
				if ($end_date != ''){
					$where[] = "coh.startdate <= '{$end_date}'";
				}
			}

			// count query
			$select[] = "(SELECT COUNT(*) FROM link_student_cohort {$count_query_joins} WHERE id_cohort = coh.id {$count_query_where}) AS participate_count";
			$headers[] = "Participation";

			// filter by user institution
			if(!$institution_set){
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = coh.institutionid",
					"type" => "left"
				);
				$institution_set = true;
			}
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "i.id IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}

			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);
			//echo $query;
			# exit;

			$this->view->criteria = $_GET;
		}
	}



	public function psInstitutionInformationAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'facilitytypes', $helper->getFacilityTypes());
		$this->view->assign ( 'sponsors', $helper->getSponsors());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		$this->view->assign ( 'lookuplanguages', $helper->getLanguages());

		if ($this->getSanParam ( 'process' )){

			$maintable = "institution i";
			$select = array();
			//$select[] = "i.id";
			$select[] = "i.institutionname";

			$headers[] = "Institution";

			$join = array();
			$where = array();
			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// region
			if( $this->getSanParam('showProvince') || $this->getSanParam('province_id') || $this->getSanParam('showDistrict') || $this->getSanParam('district_id')){
				$join[] = array(
					"table" => "location",
					"abbreviation" => "loc",
					"compare" => "loc.id = i.geography1",
					"type" => "left"
				);
				$join[] = array(
					"table" => "location_district",
					"abbreviation" => "locd",
					"compare" => "locd.id = i.geography2",
					"type" => "left"
				);

				if( $this->getSanParam('showProvince') ){
					$select[] = "loc.location_name";
					$headers[] = "Province";
				}
				if( $this->getSanParam('showDistrict') ){
					$select[] = "locd.district_name";
					$headers[] = "District";
				}
			}
			$province_arr = $this->getSanParam('province_id');
			if( !empty($province_arr) ){
				$clause = ''; $or_str = '';
				foreach($province_arr as $item){
					$clause .= "{$or_str}loc.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}
			$district_arr = $this->getSanParam('district_id');
			if( !empty($district_arr) ){
				$clause = ''; $or_str = '';
				foreach($district_arr as $item){
					$clause .= "{$or_str}locd.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}

			// institution type
			if( $this->getSanParam('showinstitutiontype') || $this->getSanParam('institutiontype') || $this->getSanParam('showinstitutionsponsors') || $this->getSanParam('institutionsponsors') ){

				$join[] = array(
					"table" => "link_institution_institutiontype",
					"abbreviation" => "liit",
					"compare" => "liit.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_institutiontype",
					"abbreviation" => "lit",
					"compare" => "lit.id = liit.id_institutiontype",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_sponsors",
					"abbreviation" => "ls",
					"compare" => "ls.id = i.sponsor",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showinstitutiontype') ){
				$select[] = "lit.typename";
				$headers[] = "Institution Type";
			}
			if( $this->getSanParam('institutiontype') ){
				$where[] = "lit.id = ".$this->getSanParam('institutiontype');
			}

			// institution sponsors
			if( $this->getSanParam('showinstitutionsponsors') ){
				$select[] = "ls.sponsorname";
				$headers[] = "Institution Sponsor";
			}
			if( $this->getSanParam('institutionsponsors') ){
				$where[] = "lit.id = ".$this->getSanParam('institutionsponsors');
			}

			// cadre
			if( $this->getSanParam('showcadre') ){
				$select[] = "cad.cadrename";
				$headers[] = "Cadre";

				$join[] = array(
					"table" => "link_cadre_institution",
					"abbreviation" => "cai",
					"compare" => "cai.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "cad",
					"compare" => "cad.id = cai.id_cadre",
					"type" => "left"
				);

				if( $this->getSanParam('cadre') ){
					$where[] = "cad.id = " . $this->getSanParam('cadre');
				}
			}

			// degree
			if($this->getSanParam('showdegree') || $this->getSanParam('degree')){
				$join[] = array(
					"table" => "link_institution_degrees",
					"abbreviation" => "liddeg",
					"compare" => "liddeg.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_degrees",
					"abbreviation" => "ldeg",
					"compare" => "ldeg.id = liddeg.id_degree",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showdegree') ){
				$select[] = "ldeg.degree";
				$headers[] = "Degree";
			}
			if( $this->getSanParam('degree') ){
				$where[] = "ldeg.id = ".$this->getSanParam('degree');
			}

			// # of computers
			if( $this->getSanParam('showcomputercount') ){
				$select[] = "i.computercount";
				$headers[] = "Computer Count";
			}

			// # of Tutors
			if( $this->getSanParam('showtutorcount') ){
				$select[] = "i.tutorcount";
				$headers[] = "Tutor Count";
			}

			// # of Students
			if( $this->getSanParam('showstudentcount') ){
				$select[] = "i.studentcount";
				$headers[] = "Student Count";
			}

			// Tutor to Student Ratio
			if( $this->getSanParam('showratio') ){
				$select[] = "(i.tutorcount / i.studentcount) AS tutor_student_ratio";
				$headers[] = "Tutor Student Ratio";
			}

			// Dormitories
			if( $this->getSanParam('showdorms') ){
				$select[] = "i.dormcount";
				$headers[] = "Dorm Count";
			}

			// # of Beds
			if( $this->getSanParam('showbeds') ){
				$select[] = "i.bedcount";
				$headers[] = "Bed Count";
			}

			// filter by user institution
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "i.id IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);
			//echo $query;
			# exit;

			$this->view->criteria = $_GET;
		}
	}




	public function psTutorByNameAction() {
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'facilitytypes', $helper->getFacilityTypes());
		$this->view->assign ( 'sponsors', $helper->getSponsors());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		$this->view->assign ( 'tutortypes', $helper->AdminTutortypes());
		$this->view->assign ( 'lookuplanguages', $helper->getLanguages());

		if ($this->getSanParam ( 'process' )){

			$maintable = "tutor tut";
			$select = array();
			//$select[] = "tut.id";
			$select[] = "p.first_name";
			$select[] = "p.last_name";

			$headers[] = "First Name";
			$headers[] = "Surname";

			$join = array();
			$join[] = array(
				"table" => "person",
				"abbreviation" => "p",
				"compare" => "p.id = tut.personid",
				"type" => "inner"
			);

			$where = array();
			$where[] = "p.is_deleted = 0";
			$sort = array();
			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			// region
			if( $this->getSanParam('showProvince') || $this->getSanParam('province_id') || $this->getSanParam('showDistrict') || $this->getSanParam('district_id')){

				$join[] = array(
					"table" => "link_tutor_institution",
					"abbreviation" => "lti",
					"compare" => "lti.id_tutor = tut.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = lti.id_institution",
					"type" => "left"
				);

				$join[] = array(
					"table" => "location",
					"abbreviation" => "loc",
					"compare" => "loc.id = i.geography1",
					"type" => "left"
				);
				$join[] = array(
					"table" => "location_district",
					"abbreviation" => "locd",
					"compare" => "locd.id = i.geography2",
					"type" => "left"
				);

				if( $this->getSanParam('showProvince') ){
					$select[] = "i.geography1";
					$headers[] = "Province";
				}
				if( $this->getSanParam('showDistrict') ){
					$select[] = "i.geography2";
					$headers[] = "District";
				}
			}
			$province_arr = $this->getSanParam('province_id');
			if( !empty($province_arr) ){
				$clause = ''; $or_str = '';
				foreach($province_arr as $item){
					$clause .= "{$or_str}loc.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}
			$district_arr = $this->getSanParam('district_id');
			if( !empty($district_arr) ){
				$clause = ''; $or_str = '';
				foreach($district_arr as $item){
					$clause .= "{$or_str}locd.id = '{$item}'";
					$or_str = " OR ";
				}
				$clause = "({$clause})";
				$where[] = $clause;
			}

			// institution
			if ( $this->getSanParam('showinstitution') ){
				$select[] = "i.institutionname";
				$headers[] = "Institution";

				# REQUIRES INSTITUTION LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "institution"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_tutor_institution",
						"abbreviation" => "lti",
						"compare" => "lti.id_tutor = tut.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = lti.id_institution",
						"type" => "left"
					);
				}

				if ($this->getSanParam('institution')){
					$where[] = "i.id = " . $this->getSanParam('institution');
				}
			}

			// cadre
			if ($this->getSanParam ( 'showcadre' )){
				$select[] = "ca.cadrename";
				$headers[] = "Cadre";

				$join[] = array(
					"table" => "cadres",
					"abbreviation" => "ca",
					"compare" => "ca.id = tut.cadreid",
					"type" => "left"
				);

				if ($this->getSanParam('cadre')){
					$where[] = "ca.id = " . $this->getSanParam('cadre');
				}
			}

			// facility
			if( $this->getSanParam('showfacility') ){
				$select[] = "fac.facility_name";
				$headers[] = "Facility";
			}
			if( $this->getSanParam('facility') ){
				$where[] = "tut.facilityid = ".$this->getSanParam('facility');
			}
			if( $this->getSanParam('showfacility') || $this->getSanParam('facility') ){
				$join[] = array(
					"table" => "facility",
					"abbreviation" => "fac",
					"compare" => "fac.id = tut.facilityid",
					"type" => "left"
				);
			}

			// degree
			if($this->getSanParam('showdegrees') || $this->getSanParam('degrees')){

				# REQUIRES INSTITUTION LINK
				$found = false;
				foreach ($join as $j){
					if ($j['table'] == "institution"){
						$found = true;
					}
				}
				if (!$found){
					$join[] = array(
						"table" => "link_tutor_institution",
						"abbreviation" => "lti",
						"compare" => "lti.id_tutor = tut.id",
						"type" => "left"
					);
					$join[] = array(
						"table" => "institution",
						"abbreviation" => "i",
						"compare" => "i.id = lti.id_institution",
						"type" => "left"
					);
				}

				$join[] = array(
					"table" => "link_institution_degrees",
					"abbreviation" => "liddeg",
					"compare" => "liddeg.id_institution = i.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_degrees",
					"abbreviation" => "ldeg",
					"compare" => "ldeg.id = liddeg.id_degree",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showdegrees') ){
				$select[] = "ldeg.degree";
				$headers[] = "Degree";
			}
			if( $this->getSanParam('degrees') ){
				$where[] = "ldeg.id = ".$this->getSanParam('degrees');
			}

			// degree institution
			if( $this->getSanParam('showdegreeinstitution') ){
				$select[] = "tut.degreeinst";
				$headers[] = "Degree Institution";
			}

			// degree year
			if( $this->getSanParam('showdegreeyear') ){
				$select[] = "tut.degreeyear";
				$headers[] = "Degree Year";
			}
			if( $this->getSanParam('degreeyear') ){
				$where[] = "tut.degreeyear = ".$this->getSanParam('degreeyear');
			}

			// tutor type
			if( $this->getSanParam('showtutortype') || $this->getSanParam('tutortype') ){
				$join[] = array(
					"table" => "link_tutor_tutortype",
					"abbreviation" => "ltutttype",
					"compare" => "ltutttype.id_tutor = tut.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_tutortype",
					"abbreviation" => "lttype",
					"compare" => "lttype.id = ltutttype.id_tutortype",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showtutortype') ){
				$select[] = "lttype.typename";
				$headers[] = "Tutor Type";
			}
			if( $this->getSanParam('tutortype') ){
				$where[] = "lttype.id = ".$this->getSanParam('tutortype');
			}

			// languages spoken
			if( $this->getSanParam('showtutortype') || $this->getSanParam('tutortype') ){
				$join[] = array(
					"table" => "link_tutor_languages",
					"abbreviation" => "ltutlang",
					"compare" => "ltutlang.id_tutor = tut.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "lookup_languages",
					"abbreviation" => "llang",
					"compare" => "llang.id = ltutlang.id_language",
					"type" => "left"
				);
			}
			if( $this->getSanParam('showlanguages') ){
				$select[] = "llang.language";
				$headers[] = "Language";
			}
			if( $this->getSanParam('languages') ){
				$where[] = "llang.id = ".$this->getSanParam('languages');
			}

			// # of students advised
			if( $this->getSanParam('showstudentsadvised') ){
				$select[] = "(SELECT COUNT(*) FROM student sub_s
									   INNER JOIN cadres sub_c ON sub_c.id = sub_s.cadre
									   INNER JOIN link_cadre_tutor sub_lct ON sub_lct.id_cadre = sub_c.id
									   INNER JOIN tutor sub_t ON sub_t.id = sub_lct.id_tutor
									   WHERE sub_t.id = tut.id) AS students_advised";
				$headers[] = "Students Advised";
			}

			// tutor length
			if( $this->getSanParam('showtutorlength') ){
				$select[] = "(tut.tutortimehere - tut.tutorsince) AS tutor_length";
				$headers[] = "Tutor Length";
			}

			// length with current institution
			if( $this->getSanParam('showtutorcurlength') ){
				$select[] = "(tut.tutortimehere - tut.tutorsince) AS cur_tutor_length";
				$headers[] = "Tutor Current Length";
			}

			// gender
			if( $this->getSanParam('showgender') ){
				$select[] = "p.gender";
				$headers[] = "Gender";
			}

			// age
			if( $this->getSanParam('showage') ){
				$select[] = "DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birthdate)), '%Y')+0 AS age";
				$headers[] = "Age";
			}
			if($this->getSanParam('agemin') || $this->getSanParam('agemax')){
				$year_secs = 60 * 60 * 24 * 365;
				if($this->getSanParam('agemin') && $this->getSanParam('agemax')){
					$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
					$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
					$where[] = "p.birthdate BETWEEN '{$max_age_birthdate}' AND '{$min_age_birthdate}'";
				} else {
					if ( $this->getSanParam('agemin') ){
						$min_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemin') * $year_secs)));
						$where[] = "p.birthdate <= '{$min_age_birthdate}'";
					}
					if ( $this->getSanParam('agemax') ){
						$max_age_birthdate = date('Y-m-d', (time() - ($this->getSanParam('agemax') * $year_secs)));
						$where[] = "p.birthdate >= '{$max_age_birthdate}'";
					}
				}
			}

			if( $this->getSanParam('showemail') || $this->getSanParam('showphone') ){
				$join[] = array(
					"table" => "link_tutor_contacts",
					"abbreviation" => "ltutcon",
					"compare" => "ltutcon.id_tutor = tut.id",
					"type" => "left"
				);
			}

			// email
			if( $this->getSanParam('showemail') ){
				$join[] = array(
					"table" => "lookup_contacts",
					"abbreviation" => "lcon_email",
					"compare" => "(lcon_email.id = ltutcon.id_contact AND lcon_email.contactname = 'email')",
					"type" => "left"
				);
				$select[] = "ltutcon.contactvalue";
				$headers[] = "Email";
			}

			// phone
			if( $this->getSanParam('showphone') ){
				$join[] = array(
					"table" => "lookup_contacts",
					"abbreviation" => "lcon_phone",
					"compare" => "(lcon_phone.id = ltutcon.id_contact AND lcon_phone.contactname = 'phone')",
					"type" => "left"
				);
				$select[] = "ltutcon.contactvalue";
				$headers[] = "Phone";
			}

			// filter by user institution
			# REQUIRES INSTITUTION LINK
			$found = false;
			foreach ($join as $j){
				if ($j['table'] == "institution"){
					$found = true;
				}
			}
			if (!$found){
				$join[] = array(
					"table" => "link_tutor_institution",
					"abbreviation" => "lti",
					"compare" => "lti.id_tutor = tut.id",
					"type" => "left"
				);
				$join[] = array(
					"table" => "institution",
					"abbreviation" => "i",
					"compare" => "i.id = lti.id_institution",
					"type" => "left"
				);
			}
			$login_user_id = $helper->myid();
			$ins_results = $helper->getUserInstitutions($login_user_id);
			if( !empty($ins_results) ){
				$where[] = "i.id IN (SELECT institutionid FROM link_user_institution WHERE userid = {$login_user_id})";
			}

			$query = "SELECT " . implode(", ", $select) . "\n";
			$query .= " FROM " . $maintable . "\n";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " " . $j['abbreviation'] . " ON " . $j['compare'] . "\n";
				}
			}
			if (count ($where) > 0){
				$query .= "WHERE " . implode(" AND ", $where) . "\n";
			}
			//echo $query; exit;

			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$rowArray = $db->fetchAll ($query);
			$this->viewAssignEscaped("headers", $headers);
			$this->view->assign('output',$rowArray);
			$this->view->assign('query',$query);
			# exit;

			$this->view->criteria = $_GET;
		}
	}

        public function summaryresultAction(){
             if ($this->getRequest()->isXmlHttpRequest()){
                 $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                $facilities_new  = $this->getSanParam('facility_id');
                $reports = new Report();
                $helper2 = new Helper2();
                $lastpulled_date = $helper2->getLatestPullDate();
                $error = array();
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $outputs = array();
                $facility = array();
                $facility_location = explode("_",$facilities_new);
                $facility[] = $facility_location[3];
                $details = $this->facility_location_summary_details($facility_location[3]);
    $output = array();
    $headers = array();
    $heading = array();
    $outdetails = array();
    //$summary_details = array();
    $title_array = array();
$title_value = array();
                if($facilities_new=="" || empty($facilities_new)){
    
$error [] = "Please select a facility";
$data['error'] = "Please select a facility";
   // continue;
}else{
    $data['error'] = "";
    
   // $summary_details = array($larc_trained,$fp_trained);
$csql = "SELECT * FROM commodity_name_option WHERE commodity_type='fp' OR commodity_type='larc' ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);
               // $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );

$clientSql = "SELECT * FROM commodity_name_option WHERE commodity_alias='new_acceptors' OR commodity_alias='current_users' ORDER BY commodity_name ASC";
$clientsDataArray = $db->fetchAll($clientSql);
//$clientsDataArray[] = array('id'=>36,'commodity_name'=>'New family planning acceptors');
//$clientsDataArray[] = array('id'=>37,'commodity_name'=>'Current FP Users');


   // print_r($title_value); echo '<br/><br/>';
   // print_r($title_array);echo '<br/><br/>';exit;
    
    $month = strtotime($lastpulled_date);
$headers[] = "FP Commodity"; 
$clientHeaders[] = "Clients";
$output = array();
$details_facility = $details;
$heading = array("Health Worker Name","Type of Training","Training Organizer");
$outdetails = array();
$facility_id = implode(",",$facility);
//$hw_details = $db->fetchAll("SELECT fsummary.first_name,fsummary.middle_name,fsummary.last_name,fsummary.training_title_phrase,fsummary.training_organizer_phrase FROM facility_summary_person_view as fsummary left join person on person.id = person_id WHERE fsummary.facility_id IN (".$facility_id.") AND person.is_deleted='0'");
$hw_details = $db->fetchAll("SELECT p.last_name,p.first_name,p.middle_name,tto.training_title_phrase,too.training_organizer_phrase FROM person_to_training  as ptt left join person as p on p.id=ptt.person_id left join training as t on t.id = ptt.training_id left join training_title_option as tto on tto.id = t.training_title_option_id left join training_organizer_option as too on too.id=t.training_organizer_option_id WHERE p.facility_id IN ($facility_id) AND tto.is_deleted='0' AND too.is_deleted='0' AND p.is_deleted='0' AND t.is_deleted='0'");
$title_array = array();
$title_value = array();
foreach($hw_details as $hw_det){
       $outlook = array();
        $full_name = $hw_det['last_name']." ".$hw_det['first_name']." ".$hw_det['middle_name'];
        $type_of_training = trim($hw_det['training_title_phrase']);
        $type_of_training = str_replace(",", "@", $type_of_training);
        $training_organizer = $hw_det['training_organizer_phrase'];
        $training_organizer = str_replace(",", "@", $training_organizer);
        array_push($outlook,$full_name);
        array_push($outlook,$type_of_training);
        array_push($outlook,$training_organizer);
        array_push($outdetails,$outlook);
        if(in_array($type_of_training,$title_array)){
            $key = array_search($type_of_training, $title_array);//$title_array[$type_of_training];
            $count = $title_value[$key];
            $count = $count + 1;
            $title_value[$key] = $count;
        }else{
            array_push($title_array,$type_of_training);
            $key = array_search($type_of_training, $title_array);
           $title_value[$key] = 1;
            
        }
    }









//$outputs = array();
$heading_date = array();
$month = strtotime($lastpulled_date);
//$month = time();
//$outputs[] = $row['commodity_name'];
$month = strtotime('next month', $month);
   
for ($i = 1; $i <= 12; $i++) {
    $month = strtotime('last month', $month);
    $heading_date[] = date("M Y", $month);
  
  
}
$heading_date = array_reverse($heading_date);
foreach($heading_date as $head_date){
  $headers[] = $head_date;
  $clientHeaders[] = $head_date;
    
}

//this is for the client

$clientData = array();
foreach($clientsDataArray as $client){
    $outputs = array();
    $sum_first = array();
    
    $outputs[] = $client['commodity_name'];
    $id = $client['id'];
    
    $month = strtotime($lastpulled_date);
 //$month = strtotime('last month', $month);
$month = strtotime('next month', $month);
for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
   // $outputs[] = $start;
  $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$id);
  
     //$outputs[]  = $sum;
     $sum_first[] = $sum;
}
$sum_first = array_reverse($sum_first);

foreach($sum_first as $sum_first_date){
    
    $outputs[] = $sum_first_date;
}
array_push($clientData,$outputs);


}

//this is for the consumption
foreach($commodity_name as $row){
    $outputs = array();
    $sum_first = array();
$outputs[] = $row['commodity_name'];
$id = $row['id'];
$month = strtotime($lastpulled_date);
 //$month = strtotime('last month', $month);
$month = strtotime('next month', $month);
for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
   // $outputs[] = $start;
  $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$id);
  
     //$outputs[]  = $sum;
     $sum_first[] = $sum;
}
$sum_first = array_reverse($sum_first);

foreach($sum_first as $sum_first_date){
    
    $outputs[] = $sum_first_date;
}
array_push($output,$outputs);
}


$outputs = array();
//$name_ids_array = array("32","38","","31");
$aliasCommodities = array("so_fp_seven_days","so_implants","so_female_condoms","so_ec");
$name_ids = array();
$name_ids = $reports->getNameIdsUsingAlias($aliasCommodities);
//print_r($name_ids_array);
//exit;
$fullname = array("Any FP for 7 consecutive days","Implants","Female Condoms","Emergency Contraception");
$length = sizeof($name_ids);
//$month = time();


for($r=0;$r<$length;$r++){
    $out = array();
    $out_first = array();
    $out[] = $fullname[$r];
    $name_id = $name_ids[$r];
    $month = strtotime($lastpulled_date);
$month = strtotime('next month', $month);
   // $month = time();
   // echo $name_id.' '.$fullname[$r].'<br/><br/>';
    for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
  
 $out_first[] = $this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id);
 //echo $start.' end '.$end.'<br/>';
 //print_r($this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id)) ;
}
//echo '<br/><br/>';
$out_first = array_reverse($out_first);

foreach($out_first as $out_first_date){
    
    $out[] = $out_first_date;
}
array_push($outputs,$out);
}

}

//print_r($outputs);
//print_r($output); 

//print_r($months);
//print_r($values);
//exit;
$criteria = array();

$criteria['error'] = $error;
$criteria['details_facility'] = json_encode($details_facility);

 
 $data['headers'] = $headers;
 $data['output'] = json_encode($output);
 $data['outputs'] = json_encode($outputs);
 $data['criteria'] = json_encode($criteria);
 $data['outdetails'] = json_encode($outdetails);
 $data['heading'] = $heading;
 $data['clientHeaders'] = $clientHeaders;
 $data['clientData'] = json_encode($clientData);
 $data['summary'] = json_encode($title_array);
 $data['summary_details'] = json_encode($title_value);
 $data['error'] = $error;
 $data['details_facility'] = json_encode($details_facility);;

$dataJSON = json_encode($data);
echo $dataJSON;
                return true;
             } else{
                 
                 return true;
             }
        }
public function facilitysummaryAction(){
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();

 $request = $this->getRequest()->getMethod();
if($this->getSanParam('go')){
  if($request!="POST"){
      $this->_redirect('reports/facilitysummary');
      //$this->_forward ( 'facilitysummary' );
      
  }  
}

		$this->view->assign ( 'mode', 'search' );
                $reports = new Report();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                $helper2 = new Helper2();
                $lastpulled_date = $helper2->getLatestPullDate();
		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                //$number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
              
               $output = array();
               
                $locations = Location::getAll("10");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                }
                //print_r($locations);
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
               //exit;
               //print_r($locations); exit;
		$this->viewAssignEscaped('locations', $locations);
		//course
                //print_r($locations);exit;
                //exit;
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
                
                
                
                //-----------------------------------------the query is here now.----------------------------------------------//
                $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                $facilities_new  = $this->getSanParam('facility_id');
                //var_dump($facilities_new);exit;
               /* foreach($facilities_new as $facer){
                $facility_location = explode("_",$facer);
                $facility[] = $facility_location[3];
                }
                * */
               
                $facility = array();
                $details = array();
                $details_facility = array();
                $facility_location = explode("_",$facilities_new);
                if(isset($facility_location[3])){
                $facility[] = $facility_location[3];
                
                $details = $this->facility_location_summary_details($facility_location[3]);
                }
                
               // print_r($details);exit;
               
                $error = array();
                if($facilities_new==""){
                    
    $outputs = array();
    $output = array();
    $headers = array();
    $heading = array();
    $outdetails = array();
    $clientData = array();
    $clientHeaders = array();
    //$summary_details = array();
    $title_array = array();
$title_value = array();
$error [] = "Please select a facility";
   // continue;
}else{
    
  // $summary = $db->fetchAll("SELECT * FROM facility_worker_training_counts_view WHERE facid='".$facility_id."' LIMIT 1");            
   //print_r($summary);       exit;      
   //$larc_trained = $summary[0]['larctrained'];
               // $fp_trained = $summary[0]['fptrained'];
                //$summary_details = array($larc_trained,$fp_trained);
$csql = "SELECT * FROM commodity_name_option WHERE commodity_type='fp' OR commodity_type='larc' ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);

                
$clientSql = "SELECT * FROM commodity_name_option WHERE commodity_alias='new_acceptors' OR commodity_alias='current_users' ORDER BY commodity_name ASC";
$clientsDataArray = $db->fetchAll($clientSql);
               // $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );
               

$month = strtotime($lastpulled_date);
$headers[] = "FP Commodity"; 
$clientHeaders[] = "Clients";
$output = array();
$details_facility = $details;
$heading = array("Health Worker Name","Type of Training","Training Organizer");
$outdetails = array();
$facility_id = implode(",",$facility);
//$hw_details = $db->fetchAll("SELECT fsummary.first_name,fsummary.middle_name,fsummary.last_name,fsummary.training_title_phrase,fsummary.training_organizer_phrase FROM facility_summary_person_view as fsummary left join person on person.id = person_id WHERE fsummary.facility_id IN (".$facility_id.") AND person.is_deleted='0'");
$hw_details = $db->fetchAll("SELECT p.last_name,p.first_name,p.middle_name,tto.training_title_phrase,too.training_organizer_phrase FROM person_to_training  as ptt left join person as p on p.id=ptt.person_id left join training as t on t.id = ptt.training_id left join training_title_option as tto on tto.id = t.training_title_option_id left join training_organizer_option as too on too.id=t.training_organizer_option_id WHERE p.facility_id IN ($facility_id) AND tto.is_deleted='0' AND too.is_deleted='0' AND p.is_deleted='0' AND t.is_deleted='0'");
$title_array = array();
$title_value = array();
foreach($hw_details as $hw_det){
       $outlook = array();
        $full_name = $hw_det['last_name']." ".$hw_det['first_name']." ".$hw_det['middle_name'];
        $type_of_training = trim($hw_det['training_title_phrase']);
        $training_organizer = $hw_det['training_organizer_phrase'];
        array_push($outlook,$full_name);
        array_push($outlook,$type_of_training);
        array_push($outlook,$training_organizer);
        array_push($outdetails,$outlook);
        if(in_array($type_of_training,$title_array)){
            $key = array_search($type_of_training, $title_array);//$title_array[$type_of_training];
            $count = $title_value[$key];
            $count = $count + 1;
            $title_value[$key] = $count;
        }else{
            array_push($title_array,$type_of_training);
            $key = array_search($type_of_training, $title_array);
           $title_value[$key] = 1;
            
        }
    }
   // print_r($title_value); echo '<br/><br/>';
   // print_r($title_array);echo '<br/><br/>';exit;
    
    

//$outputs = array();
$heading_date = array();
$month = strtotime($lastpulled_date);
//$month = time();
//$outputs[] = $row['commodity_name'];
$month = strtotime('next month', $month);
   
for ($i = 1; $i <= 12; $i++) {
    $month = strtotime('last month', $month);
    $heading_date[] = date("M, Y", $month);
  
  
}
$heading_date = array_reverse($heading_date);
foreach($heading_date as $head_date){
  $headers[] = $head_date;
  $clientHeaders[] = $head_date;
    
}

//this is for the client
$clientData = array();
foreach($clientsDataArray as $client){
    $outputs = array();
    $sum_first = array();
    
    $outputs[] = $client['commodity_name'];
    $id = $client['id'];
    
    $month = strtotime($lastpulled_date);
 //$month = strtotime('last month', $month);
$month = strtotime('next month', $month);
for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
   // $outputs[] = $start;
  $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$id);
  
     //$outputs[]  = $sum;
     $sum_first[] = $sum;
}
$sum_first = array_reverse($sum_first);

foreach($sum_first as $sum_first_date){
    
    $outputs[] = $sum_first_date;
}
array_push($clientData,$outputs);


}

//this is for the consumption
foreach($commodity_name as $row){
    $outputs = array();
    $sum_first = array();
$outputs[] = $row['commodity_name'];
$id = $row['id'];
$month = strtotime($lastpulled_date);
 //$month = strtotime('last month', $month);
$month = strtotime('next month', $month);
for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
   // $outputs[] = $start;
  $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$id);
  
     //$outputs[]  = $sum;
     $sum_first[] = $sum;
}
$sum_first = array_reverse($sum_first);

foreach($sum_first as $sum_first_date){
    
    $outputs[] = $sum_first_date;
}
array_push($output,$outputs);
}

$outputs = array();
//$name_ids_array = array("32","38","","31");
$aliasCommodities = array("so_fp_seven_days","so_implants","so_female_condoms","so_ec");
$name_ids = array();
$name_ids = $reports->getNameIdsUsingAlias($aliasCommodities);
//print_r($name_ids_array);
//exit;
$fullname = array("Any FP for 7 consecutive days","Implants","Female Condoms","Emergency Contraception");
$length = sizeof($name_ids);
//$month = time();


for($r=0;$r<$length;$r++){
    $out = array();
    $out_first = array();
    $out[] = $fullname[$r];
    $name_id = $name_ids[$r];
    $month = strtotime($lastpulled_date);
$month = strtotime('next month', $month);
   // $month = time();
   // echo $name_id.' '.$fullname[$r].'<br/><br/>';
    for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
  
 $out_first[] = $this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id);
 //echo $start.' end '.$end.'<br/>';
 //print_r($this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id)) ;
}
//echo '<br/><br/>';
$out_first = array_reverse($out_first);

foreach($out_first as $out_first_date){
    
    $out[] = $out_first_date;
}
array_push($outputs,$out);
}
}

//print_r($outputs);
//print_r($output); 

//print_r($months);
//print_r($values);
//exit;

$criteria = array();
$criteria['province_id'] = $zone;
 $criteria['district_id'] = $state;
 $criteria['region_c_id'] = $localgovernment;
$criteria['facility_id'] = $this->getSanParam('facility_id');
$criteria['error'] = $error;
$criteria['details_facility'] = $details_facility;

 $this->viewAssignEscaped ( 'headers', $headers );
 $this->viewAssignEscaped ( 'output', $output );    
 $this->viewAssignEscaped ( 'outputs', $outputs );
 $this->viewAssignEscaped('criteria',$criteria);
 $this->viewAssignEscaped ('outdetails',$outdetails);
 $this->viewAssignEscaped ('heading',$heading);
 $this->viewAssignEscaped ('summary',$title_array);
 $this->viewAssignEscaped ('summary_details',$title_value);
 $this->viewAssignEscaped ('clientHeaders',$clientHeaders);
 $this->viewAssignEscaped ('clientData',$clientData);
 $this->view->assign('base_url', $this->baseUrl);
}
public function facilitysummardemoyAction(){
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();

 $request = $this->getRequest()->getMethod();
if($this->getSanParam('go') || $this->getSanParam('download') ){
  if($request!="POST"){
      $this->_redirect('reports/facilitysummary');
      //$this->_forward ( 'facilitysummary' );
      
  }  
}

		$this->view->assign ( 'mode', 'search' );
                $reports = new Report();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                $helper2 = new Helper2();
                $lastpulled_date = $helper2->getLatestPullDate();
		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                $details_facility = array();
                //$number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
              
               $output = array();
               
                $locations = Location::getAll("10");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                }
                //print_r($locations);
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
               //exit;
               //print_r($locations); exit;
		$this->viewAssignEscaped('locations', $locations);
		//course
                //print_r($locations);exit;
                //exit;
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		$this->viewAssignEscaped ('base_url', $this->baseUrl);
                
                
                
                //-----------------------------------------the query is here now.----------------------------------------------//
                $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                $facilities_new  = $this->getSanParam('facility_id');
                //var_dump($facilities_new);exit;
               /* foreach($facilities_new as $facer){
                $facility_location = explode("_",$facer);
                $facility[] = $facility_location[3];
                }
                * */
                $facility = array();
                $facility_location = explode("_",$facilities_new);
                if(isset($facility_location[3])){
                $facility[] = $facility_location[3];
                $details = $this->facility_location_summary_details($facility_location[3]);
                }
               //$details = $this->facility_location_summary_details($facility_location[3]);
               // print_r($details);exit;
               
                $error = array();
                if($facilities_new==""){
                    
    $outputs = array();
    $output = array();
    $headers = array();
    $heading = array();
    $outdetails = array();
    //$summary_details = array();
    $title_array = array();
$title_value = array();
$error [] = "You need to select a facility";
   // continue;
}else{
    
  // $summary = $db->fetchAll("SELECT * FROM facility_worker_training_counts_view WHERE facid='".$facility_id."' LIMIT 1");            
   //print_r($summary);       exit;      
   //$larc_trained = $summary[0]['larctrained'];
               // $fp_trained = $summary[0]['fptrained'];
               // $summary_details = array($larc_trained,$fp_trained);
$csql = "SELECT * FROM commodity_name_option WHERE commodity_type='fp' OR commodity_type='larc' ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);
               // $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );
               

$month = strtotime($lastpulled_date);
$headers[] = "FP Commodity"; 
$output = array();
$details_facility = $details;
$heading = array("Health Worker Name","Type of Training","Training Organizer");
$outdetails = array();
$facility_id = implode(",",$facility);
//$hw_details = $db->fetchAll("SELECT fsummary.first_name,fsummary.middle_name,fsummary.last_name,fsummary.training_title_phrase,fsummary.training_organizer_phrase FROM facility_summary_person_view as fsummary left join person on person.id = person_id WHERE fsummary.facility_id IN (".$facility_id.") AND person.is_deleted='0'");
$hw_details = $db->fetchAll("SELECT p.last_name,p.first_name,p.middle_name,tto.training_title_phrase,too.training_organizer_phrase FROM person_to_training  as ptt left join person as p on p.id=ptt.person_id left join training as t on t.id = ptt.training_id left join training_title_option as tto on tto.id = t.training_title_option_id left join training_organizer_option as too on too.id=t.training_organizer_option_id WHERE p.facility_id IN ($facility_id) AND tto.is_deleted='0' AND too.is_deleted='0' AND p.is_deleted='0' AND t.is_deleted='0'");
$title_array = array();
$title_value = array();
foreach($hw_details as $hw_det){
       $outlook = array();
        $full_name = $hw_det['last_name']." ".$hw_det['first_name']." ".$hw_det['middle_name'];
        $type_of_training = trim($hw_det['training_title_phrase']);
        $training_organizer = $hw_det['training_organizer_phrase'];
        array_push($outlook,$full_name);
        array_push($outlook,$type_of_training);
        array_push($outlook,$training_organizer);
        array_push($outdetails,$outlook);
        if(in_array($type_of_training,$title_array)){
            $key = array_search($type_of_training, $title_array);//$title_array[$type_of_training];
            $count = $title_value[$key];
            $count = $count + 1;
            $title_value[$key] = $count;
        }else{
            array_push($title_array,$type_of_training);
            $key = array_search($type_of_training, $title_array);
           $title_value[$key] = 1;
            
        }
    }
   // print_r($title_value); echo '<br/><br/>';
   // print_r($title_array);echo '<br/><br/>';exit;
    
    

//$outputs = array();
$heading_date = array();
$month = strtotime($lastpulled_date);
//$month = time();
//$outputs[] = $row['commodity_name'];
$month = strtotime('next month', $month);
   
for ($i = 1; $i <= 12; $i++) {
    $month = strtotime('last month', $month);
    $heading_date[] = date("M, Y", $month);
  
  
}
$heading_date = array_reverse($heading_date);
foreach($heading_date as $head_date){
  $headers[] = $head_date;
    
}


foreach($commodity_name as $row){
    $outputs = array();
    $sum_first = array();
$outputs[] = $row['commodity_name'];
$id = $row['id'];
$month = strtotime($lastpulled_date);
 //$month = strtotime('last month', $month);
$month = strtotime('next month', $month);
for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
   // $outputs[] = $start;
  $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$id);
  
     //$outputs[]  = $sum;
     $sum_first[] = $sum;
}
$sum_first = array_reverse($sum_first);

foreach($sum_first as $sum_first_date){
    
    $outputs[] = $sum_first_date;
}
array_push($output,$outputs);
}

$outputs = array();
//$name_ids_array = array("32","38","","31");
$aliasCommodities = array("so_fp_seven_days","so_implants","so_female_condoms","so_ec");
$name_ids = array();
$name_ids = $reports->getNameIdsUsingAlias($aliasCommodities);
//print_r($name_ids_array);
//exit;
$fullname = array("Any FP for 7 consecutive days","Implants","Female Condoms","Emergency Contraception");
$length = sizeof($name_ids);
//$month = time();


for($r=0;$r<$length;$r++){
    $out = array();
    $out_first = array();
    $out[] = $fullname[$r];
    $name_id = $name_ids[$r];
    $month = strtotime($lastpulled_date);
$month = strtotime('next month', $month);
   // $month = time();
   // echo $name_id.' '.$fullname[$r].'<br/><br/>';
    for ($i = 1; $i <= 12; $i++) {
   $month = strtotime('last month', $month);
    $dates = date("Y-m",$month);
    $start = $dates."-01";
    $end = $dates."-31";
  
 $out_first[] = $this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id);
 //echo $start.' end '.$end.'<br/>';
 //print_r($this->get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id)) ;
}
//echo '<br/><br/>';
$out_first = array_reverse($out_first);

foreach($out_first as $out_first_date){
    
    $out[] = $out_first_date;
}
array_push($outputs,$out);
}
}

//print_r($outputs);
//print_r($output); 

//print_r($months);
//print_r($values);
//exit;
$criteria = array();
$criteria['province_id'] = $zone;
 $criteria['district_id'] = $state;
 $criteria['region_c_id'] = $localgovernment;
$criteria['facility_id'] = $this->getSanParam('facility_id');
$criteria['error'] = $error;
$criteria['details_facility'] = $details_facility;

 $this->viewAssignEscaped ( 'headers', $headers );
 $this->viewAssignEscaped ( 'output', $output );    
 $this->viewAssignEscaped ( 'outputs', $outputs );
 $this->viewAssignEscaped('criteria',$criteria);
 $this->viewAssignEscaped ('outdetails',$outdetails);
 $this->viewAssignEscaped ('heading',$heading);
 $this->viewAssignEscaped ('summary',$title_array);
 $this->viewAssignEscaped ('summary_details',$title_value);

}
function aggregatetraininglocationAction(){
    
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $reports = new Report();
		$this->view->assign ( 'mode', 'search' );
                $helper = new Helper2();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                 $training = new Training();
                // echo $training->trainingEndDateLimit("min");
                 //exit;
                 $minimum_end_date = explode("-",$training->trainingEndDateLimit("min"));
                 $maximum_end_date = explode("-",$training->trainingEndDateLimit("max"));
                // print_r($minimum_end_date);
                // echo '<br/>';
                 //print_r($maximum_end_date);
                 //exit;
                 $userLocations = array();
             
                
                $userLocations = Location::getUserLocationWithRole();
             
            // Helper2::jLog(print_r($userLocations,true));//exit;
                 $minimum_year = $minimum_end_date[0];
                 $minimum_month = $minimum_end_date[1];
                 $minimum_day = $minimum_end_date[2];
                 
                 $maximum_year = $maximum_end_date[0];
                 $maximum_month = $maximum_end_date[1];
                 $maximum_day = $maximum_end_date[2];
                 
		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;
                
                
   $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                $number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
                $locationsPerUserGeo = array();
                $locations = Location::getAll("");
                $locationsPerUserGeo = Location::getAll("2");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                    $locationsPerUserGeo[$id] = $facilitiesArray[$v];
                }
                //print_r($locations);
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
                $pergeo = $this->getSanParam('pergeo');
                if($pergeo=="1"){
                    $this->viewAssignEscaped('locationsPerGeo',$locationsPerUserGeo);
                }else{
                   $this->viewAssignEscaped('locationsPerGeo',$locations); 
                }
               
               //exit;
               //print_r($locations); exit;
		$this->viewAssignEscaped('locations', $locations);
}
public function trainingrepdemoAction(){
return $this->trainingrepAction();
	
}

public function allqueriesresultAction(){
     if ($this->getRequest()->isXmlHttpRequest()){
//              ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
        $reports = new Report();
	$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $helper = new Helper2();
	require_once ('models/table/TrainingLocation.php');
	require_once('views/helpers/TrainingViewHelper.php');
        
        //----------------------------------------------------------------------collecting the parameters sent down here -------------------------------------------------------//
        $national = $this->getSanParam('national');
        $zone = $this->getSanParam('province_id');
        $state  = $this->getSanParam('district_id');
        $localgovernment  = $this->getSanParam('region_c_id');
         //$helper->jLog("THis is the error code");
//        //training id data catch
//        $trainingId = $this->getSanParam('training_id');
        //training ids
        $trainingIDs = $this->getSanParam("jqTraining");
        $trainingIDs = $reports->formatTrainingIDs($trainingIDs);    
        $trainingIDArray = array_unique(explode(",",$trainingIDs));
        //echo $trainingIDs;exit;
        //facility data catch
        $facilitiesNew  = $this->getSanParam('facility_id');
        $facilitiesNew = $reports->formatSelection($facilitiesNew);
        $facility = array();
        if(isset($facilitiesNew) && !empty($facilitiesNew)){
        foreach($facilitiesNew as $fac){
             $facility_location = explode("_",$fac);
             $facility[] = $facility_location[3];
              }
        }
          
        
        //training type data catch
        $trainingType = array();
        $trainingType = $this->getSanParam('training_type');
        
        //training organizer trainingorganizer data catch
        $trainingOrganizer = array();
        $trainingOrganizer = $this->getSanParam("trainingorganizer");
        
        //consumption data catch
        $consumption = array();
        $consumption = $this->getSanParam('consumption');
        
        //providing data catch
        $providing = array();
        $providing = $this->getSanParam('providing');
        
        //stock out data catch
        $stockOut = array();
        $stockOut = $this->getSanParam('stock_out');
        
        $client = array();
        $client = $this->getSanParam('client');
       
       
        
        //time period display data catch
        $period = $this->getSanParam('period');
        
        //date range data catch
        $startMonth = $this->getSanParam('StartMonth');
        $startYear = $this->getSanParam('StartYear');
        
        $endMonth = $this->getSanParam('EndMonth');
        $endYear = $this->getSanParam('EndYear');
        
        
        
        $startYears = array();
        $endYears = array();
        //duration data catch
        $duration = $this->getSanParam("duration");
        
        //aggregate method data catch
        $aggregateMethod = $this->getSanParam("aggregate_method");
        
        //-------------------------------------------------------------------------------------------formatting inputs--------------------------------------------------------------------------//
        
        //striping empty values from the location arrays - formatting values
        $zone = $reports->formatSelection($zone);
        $state = $reports->formatSelection($state);
        $localgovernment = $reports->formatSelection($localgovernment);
        $facility = $reports->formatSelection($facility);
        $trainingType = $reports->formatSelection($trainingType);
        $trainingOrganizer = $reports->formatSelection($trainingOrganizer);
        $consumption = $reports->formatSelection($consumption);
        $providing = $reports->formatSelection($providing);
        $stockOut = $reports->formatSelection($stockOut);
        $client = $reports->formatSelection($client);
        if(!empty($client) && !empty($consumption)){
        $consumption = array_merge($consumption,$client);
        }else if(empty($consumption) && !empty($client)){
            $consumption = $client;
        }
       
        //$consumption = $reports->formatSelection($consumption);
        Helper2::jLog("This is the consumptin value");
        Helper2::jLog(print_r($consumption,true));
        Helper2::jLog("This is the client");
        Helper2::jLog(print_r($client,true));
        
        
        if(!empty($startMonth)){
        $startMonthName = $this->return_the_month_name($startMonth);
        $startMonthName = substr($startMonthName,0,3);
        }
        
        if(!empty($endMonth)){
        $endMonthName = $this->return_the_month_name($endMonth);
        $endMonthName = substr($endMonthName,0,3);
        }
        
        $locations = array();
        //get the lowest tier of locations
        list($filter,$locations) = $reports->lowestSelectedGeog($localgovernment,$state,$zone);
         if($aggregateMethod=="aggreg_data"){                               
        if(isset($national) && $national=="0"){
            $locations[] = 0;
        }
         }
         
         //---------------------------------------------------------------------------------------------------Check and return Error Documents------------------------------------------
        
        //print_r($locations);exit;
        //----------------------------------------------------------------------------------------------------implode array parameters to be used in result----------------------------------
        
        $implodeProviding = "";
        $implodeFacility = "";
        $implodeTrainingType = "";
        $implodeTrainingOrganizer = "";
        $implodeConsumption = "";
        $implodeProviding == "";
        $implodeStockOut = "";
        //implode the arrays that exists
        if(!empty($facility)){
        $implodeFacility = implode(",",$facility);
        //$sqlFacility = "";
        }
        if(!empty($trainingType)){
        $implodeTrainingType = implode(",",$trainingType);
        //$sqlTrainingType = "";
        }
        if(!empty($trainingOrganizer)){
        $implodeTrainingOrganizer = implode(",",$trainingOrganizer);
        //$sqlTrainingOrganizer ="";
        }
        if(!empty($consumption)){
        $implodeConsumption = implode(",",$consumption);
        $sqlStockOut = "";
        }
        if(!empty($providing)){
        $implodeProviding = implode(",",$providing);
        //$sqlProviding = "";
        }
        if(!empty($stockOut)){
        $implodeStockOut = implode(",",$stockOut);
        //$sqlStockOut = " AND ";
        }
        
        if(!empty($locations)){
        $implodeLocations = implode(",",$locations);
       // $sqlLocations = " AND ";
        }
        
       
       
        
        
        
        //-------------------------------------------------------------------------------------------------------development and query proper will start here-------------------------------------------
        $criteria['error'] = array();
        $outputData = array();
        $headers = array();
        $whereClause = array();
        $whereTraining  = array();
        $startmonth = "";
        $startyear = "";
        $endmonth = "";
        $endyear = "";
        
        if($aggregateMethod=="facility_training"){
            if(empty($locations) && empty($trainingIDs)){
                $criteria['error'][] = "Please select a geography";
            }else{
                
            }
        }else{
        if(empty($locations) && $national==""){
            $criteria['error'][] = "Please select a geography";
        }
        }
        if($duration=="custom"){
            $profError = array();
        $profError = $reports->validatPeriodTimeRange($period,$startMonth,$startYear,$endMonth,$endYear);
        $criteria['error'] = array_merge($criteria['error'],$profError);
        }
        else if(empty($duration) && $period!="total"){
            $criteria['error'][] = "Please fill in the appropriate duration or date range for this period selection";
        }
        else if(empty($duration) || $period==""){
            $criteria['error'][] = "Please fill in the appropriate duration or date range for this period selection";
        }
        //print_r($criteria['error']);exit;
        if(empty($criteria['error'])){
             
        list($startYears,$endYears,$startmonth,$startyear,$endmonth,$endyear) = $reports->getStartDateEndDateWithPeriod($period,$startMonth,$startYear,$endMonth,$endYear,$duration);
        
       $permanentStartYear =  "1984-01-01";
        $sizeof = sizeof($startYears);
         ini_set('max_execution_time', 0); 
         ini_set('memory_limit', '-1');
       
        if($aggregateMethod=="aggreg_data"){
          
          $headers = $reports->createAggregateHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period);
         
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit', '-1');
        for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
            ini_set('memory_limit', '-1');
          //internal initialization
          
          $permanentStartYear = "1984-01-01";
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
           
           $locations = array_unique($locations);
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            foreach($locations as $loc){
                $provData = array();
                $provDataRaw  = array();
                $resultArray = array();
                $consumptionData = array();
                $whereTraining  = array();
                $whereClause = array();
                $trainingResult= array();
                $stockedOutFac = array();
                $whereStatement = "";
                $whereStatementConsumption = "";
                $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,"");
                $whereTraining = $reports->createWheretrainingArrayFromParameters($implodeTrainingType,$implodeTrainingOrganizer,$permanentStartYear,$tempEndDate);
                if($loc!=0){
                $whereClause[] = "(flv.geo_parent_id ='$loc' OR flv.parent_id='$loc' OR flv.location_id='$loc')";
               
                }
               // print_r($whereClause);exit;
                $whereStatementConsumption = implode(" AND ",$whereClause);
                
                //Helper2::jLog( "Helloer");exit;
                      
//                $facilities = $reports->getFacilitiesWithLocation($loc);
//                $facilityCount = sizeof($facilities);
//                $facility_ids = implode(",",$facilities);
                
                if($loc!=0){
                    
                  $whereTraining[] = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc))"; 
                 
                  $facilitiesReporting = $reports->get_all_facilities_reporte_rate_aggregate($loc,$tempStartDate,$tempEndDate);
                   }else{
                       $nationalLocations = $helper->getLocationTierIDs("1");
                       $implodedLocations = implode(",", $nationalLocations);
                       $whereTraining[] = "(flv.geo_parent_id IN ($implodedLocations) OR flv.parent_id IN ($implodedLocations) OR flv.location_id IN ($implodedLocations))"; 
                       $facilitiesReporting = $reports->get_all_facilities_reporte_rate_aggregate($loc,$tempStartDate,$tempEndDate);
                   }
              
                  $whereStatement = implode(" AND ",$whereTraining);
                
                
                $totalFacilities = $reports->countAllFacilities($loc);
                if(!empty($providing)){
                    if($loc==0 || $loc=="0"){
                    $whereProviding = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";

                    }else {
                    $whereProviding = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc)) AND (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc)) AND (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
//Helper::jLog($whereProviding);
                    }
                     $provDataRaw = $reports->processProvData($whereProviding,$whereProviding2);
                    

                }
                $caculationConsumption = array();
                if(!empty($consumption)){                  
                $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
                }
                if(!empty($stockOut)){
                $stockedOutFac = $reports->facilitiesStockedOut($whereStatementConsumption);
                //Helper2::jLog(print_r($stockedOutFac,true));
                }
                if(!empty($trainingType) || !empty($trainingOrganizer) ){
                $trainingResult = $reports->countHWsResult($whereStatement,$trainingType,$trainingOrganizer);
                }
               
               // print_r($consumptionData);
                $resultArray = $reports->createAggregateResultData($loc,$trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period,$totalFacilities,$facilitiesReporting,$trainingResult,$provDataRaw,$caculationConsumption,$tempStartDate,$stockedOutFac);
         //($resultArray);exit;
                    $outputData[] = $resultArray;
            }
           
            
            
            
          }
          
       
        }
        else if($aggregateMethod=="facility_geog"){
             //ini_set('max_execution_time', 0); 
            for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
          //internal initialization
          
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
          
           $locations = array_unique($locations);
           $implodeLocations = implode(",",$locations);
           $facility = $reports->getFacilitiesWithLocationAndFacilities($implodeFacility,$implodeLocations);
           $headers = $reports->createFacAloneHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period);
           foreach($facility as $fac){
               $provData = array();
               $provDataRaw = array();
               $resultArray = array();
               $consumptionData = array();
               $whereTraining  = array();
               $whereClause = array();
               $whereStatementTraining = "";
               $whereStatementConsumption = "";
               $caculationConsumption = array();
               
               $facility_id = $fac['id'];
               $zoneName = $fac['geo_zone'];
               $stateName = $fac['state'];
               $stateName = str_replace(",", "@", $stateName);
               $lgaName = $fac['lga'];
               $lgaName = str_replace(",", "@", $lgaName);
               $facilityName = $fac['facility_name'];
               $facilityName = str_replace(",", "@", $facilityName);
              
               $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,$facility_id);
               $whereTraining = $reports->createWheretrainingArrayFromParameters($implodeTrainingType,$implodeTrainingOrganizer,$permanentStartYear,$tempEndDate);
               $whereTraining[] = "facility_id IN ($facility_id)"; 
               
               //get the formatted training organizer list if present
               $stringPhrase = $reports->getFormattedTrainingOrganizer($implodeTrainingType,$implodeTrainingOrganizer,$tempStartDate,$tempEndDate,$facility_id);
               $stringPhrase = str_replace(",", "@", $stringPhrase);
                //echo "hello";exit;
               
               $whereStatementConsumption = implode(" AND ",$whereClause);
               $whereStatementTraining = implode(" AND ",$whereTraining);
               $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
               //Helper2::jLog(print_r($caculationConsumption,true));
              // $result = $reports->getAggregateConsumptionResult($whereStatementConsumption);
               
                if(!empty($providing)){
                    
                    
                    $whereProviding = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id)) ";
                    $whereProviding2 = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id))";
                    
                  //Helper::jLog($whereProviding);
                     $provDataRaw = $reports->processProvDataFac($whereProviding,$whereProviding2);
                                        

                }
                
                
            $trainingResult = $reports->countHWsResult($whereStatementTraining,$trainingType,$trainingOrganizer);
               
               $facilitiesReporting = $reports->get_all_facilities_reporte_rates($facility_id,$tempStartDate,$tempEndDate);
              // print_r($trainingResult);exit;
               $resultArray = $reports->createFacilityResultData($stateName,$lgaName,$facilityName,$trainingType,$trainingOrganizer,$trainingResult,$consumption,$caculationConsumption,$providing,$provDataRaw,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting,$stringPhrase);
               
            $outputData[] = $resultArray;    
           //print_r($resultArray); exit;
           }
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            
            }
            
        }
        else if($aggregateMethod=="facility_training"){
            //$criteria['error'][] = "Selection module still undergoing development";
            for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
            
          //internal initialization
          
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
           
           $locations = array_unique($locations);
           $implodeLocations = implode(",",$locations);
           
           foreach($trainingIDArray as $tId){
               $facility = $reports->selectAggregateFacDataWithTrainingID($tId,$tempStartDate,$tempEndDate,$implodeLocations);
           helper2::jLog(print_r($facility,true));
          
            for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
          //internal initialization
          
          $headers = $reports->createFacWithTrainingHeaders($providing,$consumption,$stockOut,$period);
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
                 foreach($facility as $facs){
               $facility_id = $facs['facility_id'];
               $fac =  $reports->getTrainingIDFacilityID($facility_id,$tempStartDate,$tempEndDate);
              
               $provDataRaw = array();
               $resultArray = array();
               $consumptionData = array();
               $caculationConsumption = array();
               $whereTraining  = array();
               $whereClause = array();
               $whereStatementTraining = "";
               $whereStatementConsumption = "";
               
               
               
               $stateName = $facs['state'];
               $stateName = str_replace(",", "@", $stateName);
               $lgaName = $facs['lga'];
               $lgaName = str_replace(",", "@", $lgaName);
               $facilityName = $facs['facility_name'];
               $facilityName = str_replace(",", "@", $facilityName);
               $hwcount = $fac[0]['hwcount'];
               $trainingOrganizerPhrase = $fac[0]['training_organizer_phrase'];
               $trainingOrganizerPhrase = str_replace(",", "@", $trainingOrganizerPhrase);
               
               $trainingTitlePhrase = $fac[0]['training_title_phrase'];
               $trainingTitlePhrase = str_replace(",", "@", $trainingTitlePhrase);
               
               $training_id = $fac[0]['training_id'];
               $training_id = str_replace(",", "@", $training_id);
               $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,$facility_id);
              
               $whereStatementConsumption = implode(" AND ",$whereClause);
               if(!empty($providing)){
                     if($loc==0 || $loc=="0"){
                    $whereProviding = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    }else{
                    $whereProviding = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id)) ";
                    $whereProviding2 = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id))";
                    }
//Helper::jLog($whereProviding);
                     $provDataRaw = $reports->processProvDataFac($whereProviding,$whereProviding2);
                                        

                }
//               $result = $reports->getAggregateConsumptionResult($whereStatementConsumption);
//               
//               list($provData,$consumptionData) = $reports->getSumConsumption($result);
               $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
 
               $facilitiesReporting = $reports->get_all_facilities_reporte_rates($facility_id,$tempStartDate,$tempEndDate);
              
               $resultArray = $reports->createFacilityWithtrainingResultData($stateName,$lgaName,$facilityName,$hwcount,$training_id,$trainingOrganizerPhrase,$trainingTitlePhrase,$consumption,$caculationConsumption,$providing,$provDataRaw,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting);
               
            $outputData[] = $resultArray;    
           //print_r($resultArray); exit;
           }
            }
            }
           
           
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            
            }
            
        }
        }
        $startDate = "01-".$startmonth."-".$startyear;
        $endDate = "31-".$endmonth."-".$endyear;
        $data['zone'] = $zone;
        $data['state'] = $state;
        $data['localgovernment'] = $localgovernment;
        $data['training_id'] = $trainingIDs;
        $data['facility'] = $facility;
        $data['training_type'] = $trainingType;
        $data['trainingOrganizer'] = $trainingOrganizer;
        $data['consumption'] = $consumption;
        $data['providing'] = $providing;
        $data['stock_out'] = $stockOut;
        $data['period'] = $period;
        $data['StartMonth'] = $startMonth;
        $data['StartYear'] = $startYear;
        $data['EndMonth'] = $endMonth;
        $data['EndYear'] = $endYear;
        $data['duration'] = $duration;
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
        $data['startYears']  = $startYears;
        $data['endYears'] = $endYears;
        $data['aggregate_method'] = $aggregateMethod;
        $data['headers'] = $headers;
        $data['error'] = $criteria['error'];
        $data['output'] = json_encode($outputData);
        $data['noOutput']  = sizeof($outputData);
      
       // print_r($data);exit;
        echo json_encode($data);
        
        
        
     }
     else{
         return true;
     }
}
public function alldataAction(){
//     ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                
                 $request = $this->getRequest()->getMethod();
if($this->getSanParam('go') || $this->getSanParam('download') ){
  
  if($request!="POST"){
      $this->_redirect('reports/alldata');
      //$this->_forward ( 'facilitysummary' );
      
  }  
} 
//print_r($this->_getAllParams ());exit;
                $reports = new Report();
		$this->view->assign ( 'mode', 'search' );
                $helper = new Helper2();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                 $training = new Training();
                
		$criteria = array ();
                $criteria['stock_out'] = "";
                $criteria['providing'] = array();
                $criteria['national_report'] = "";
                $criteria['province_id'] = "";
                $criteria['district_id'] = "";
                $criteria['region_c_id'] = "";
                $criteria['facility_id'] = "";
                $criteria['consumption'] = array();
                $criteria['client'] = array();
                $quickLocation = new Location();
                $lastPullDate = $helper->getLatestPullDate();
               // echo $lastPullDate;exit;
                $criteria['lastPullDate'] = $lastPullDate;
                
                //print_r($criteria);exit;
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) ? true : false;
                $trainingLocation = new TrainingLocation();
                $allTrainingIDs = $trainingLocation->getTrainingPlusLocation();
                $this->viewAssignEscaped ( 'trainingIDs', $allTrainingIDs );
		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ('person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                //$number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
                $locationsPerUserGeo = array();
                $locations = Location::getAll("1");
                
                $locationsPerUserGeo = Location::getAll("");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                    $locationsPerUserGeo[$id] = $facilitiesArray[$v];
                }
                
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
                $this->viewAssignEscaped('locations', $locations);
                $this->viewAssignEscaped('locationsPerGeo',$locationsPerUserGeo);
                $tsql = "SELECT * FROM `training_title_option` WHERE is_deleted='0' ORDER BY `training_title_phrase`  ASC";
                
                $trainingtypes = $db->fetchAll($tsql);
                $this->viewAssignEscaped ( 'trainingtypes', $trainingtypes );
				$training_type_not_set = array();
                foreach($trainingtypes as $training){
				$training_type_not_set[] = $training['id'];
				}
                $tosql = "SELECT * FROM `training_organizer_option` WHERE is_deleted='0'  ORDER BY `training_organizer_phrase`ASC";
                
                $trainingorg = $db->fetchAll($tosql);
                $this->viewAssignEscaped ( 'trainingorganizer', $trainingorg );
                
                $csql = "SELECT * FROM commodity_name_option ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);
                $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );
                
                $clientSql = "SELECT * FROM commodity_name_option WHERE commodity_alias='new_acceptors' OR commodity_alias='current_users' ORDER BY commodity_name ASC";
                $clientsDataArray = $db->fetchAll($clientSql);
                $this->viewAssignEscaped('clientDataArray', $clientsDataArray);

                $imploded = "31,32,38,39";
                $commodityStockOut = $this->selectStockOutsCommodity($imploded);
                $this->viewAssignEscaped('stock_out_commodity_name_option',$commodityStockOut);
                
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		$tlocations = TrainingLocation::selectAllLocations ($this->setting('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
                $output = array();
                
                
                $locationName =  $quickLocation->getUserLocationName();
                
                //-----------------------------------------the query is here now.----------------------------------------------//
             
                
                
                
//------------------------------------------------------------------------------------this is manily for the download of the excel template for each document -----------------------------------
                $reports = new Report();
	$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        $helper = new Helper2();
	require_once ('models/table/TrainingLocation.php');
	require_once('views/helpers/TrainingViewHelper.php');
        
        //----------------------------------------------------------------------collecting the parameters sent down here -------------------------------------------------------//
        $national = $this->getSanParam('national');
        $zone = $this->getSanParam('province_id');
        $state  = $this->getSanParam('district_id');
        $localgovernment  = $this->getSanParam('region_c_id');
        
//        //training id data catch
//        $trainingId = $this->getSanParam('training_id');
        //training ids
        $trainingIDs = $this->getSanParam("jqTraining");
        
        //echo $trainingIDs;exit;
        //facility data catch
        $facilitiesNew  = $this->getSanParam('facility_id');
        $facilitiesNew = $reports->formatSelection($facilitiesNew);
        $facility = array();
        if(isset($facilitiesNew) && !empty($facilitiesNew)){
        foreach($facilitiesNew as $fac){
             $facility_location = explode("_",$fac);
             $facility[] = $facility_location[3];
              }
        }
          
        
        //training type data catch
        $trainingType = array();
        $trainingType = $this->getSanParam('training_type');
        
        //training organizer trainingorganizer data catch
        $trainingOrganizer = array();
        $trainingOrganizer = $this->getSanParam("trainingorganizer");
        
        //consumption data catch
        $consumption = array();
        $consumption = $this->getSanParam('consumption');
        
        //providing data catch
        $providing = array();
        $providing = $this->getSanParam('providing');
        
        //stock out data catch
        $stockOut = array();
        $stockOut = $this->getSanParam('stock_out');
        
        $client = array();
        $client = $this->getSanParam('client');
        
        if(!empty($client)){
        $criteria['client'] = $client;
        }
        
        
        //time period display data catch
        $period = $this->getSanParam('period');
        
        //date range data catch
        $startMonth = $this->getSanParam('StartMonth');
        $startYear = $this->getSanParam('StartYear');
        
        $endMonth = $this->getSanParam('EndMonth');
        $endYear = $this->getSanParam('EndYear');
        
        
        $startYears = array();
        $endYears = array();
        //duration data catch
        $duration = $this->getSanParam("duration");
        
        //aggregate method data catch
        $aggregateMethod = $this->getSanParam("aggregate_method");
        
        //-------------------------------------------------------------------------------------------formatting inputs--------------------------------------------------------------------------//
        
        //striping empty values from the location arrays - formatting values
        $zone = $reports->formatSelection($zone);
        $state = $reports->formatSelection($state);
        $localgovernment = $reports->formatSelection($localgovernment);
        $facility = $reports->formatSelection($facility);
        $trainingType = $reports->formatSelection($trainingType);
        $trainingOrganizer = $reports->formatSelection($trainingOrganizer);
        $consumption = $reports->formatSelection($consumption);
        $providing = $reports->formatSelection($providing);
        $stockOut = $reports->formatSelection($stockOut);
        $client = $reports->formatSelection($client);
       
       if(!empty($client) && !empty($consumption)){
        $consumption = array_merge($consumption,$client);
        }else if(empty($consumption) && !empty($client)){
            $consumption = $client;
        }
        
        if(!empty($startMonth)){
        $startMonthName = $this->return_the_month_name($startMonth);
        $startMonthName = substr($startMonthName,0,3);
        }
        
        if(!empty($endMonth)){
        $endMonthName = $this->return_the_month_name($endMonth);
        $endMonthName = substr($endMonthName,0,3);
        }
        
        $locations = array();
        //get the lowest tier of locations
        list($filter,$locations) = $reports->lowestSelectedGeog($localgovernment,$state,$zone);
         if($aggregateMethod=="aggreg_data"){                               
        if(isset($national) && $national=="0"){
            $locations[] = 0;
        }
         }
         
         //---------------------------------------------------------------------------------------------------Check and return Error Documents------------------------------------------
        
        //print_r($locations);exit;
        //----------------------------------------------------------------------------------------------------implode array parameters to be used in result----------------------------------
        
        $implodeProviding = "";
        $implodeFacility = "";
        $implodeTrainingType = "";
        $implodeTrainingOrganizer = "";
        $implodeConsumption = "";
        $implodeProviding == "";
        $implodeStockOut = "";
        //implode the arrays that exists
        if(!empty($facility)){
        $implodeFacility = implode(",",$facility);
        //$sqlFacility = "";
        }
        if(!empty($trainingType)){
        $implodeTrainingType = implode(",",$trainingType);
        //$sqlTrainingType = "";
        }
        if(!empty($trainingOrganizer)){
        $implodeTrainingOrganizer = implode(",",$trainingOrganizer);
        //$sqlTrainingOrganizer ="";
        }
        if(!empty($consumption)){
        $implodeConsumption = implode(",",$consumption);
        $sqlStockOut = "";
        }
        if(!empty($providing)){
        $implodeProviding = implode(",",$providing);
        //$sqlProviding = "";
        }
        if(!empty($stockOut)){
        $implodeStockOut = implode(",",$stockOut);
        //$sqlStockOut = " AND ";
        }
        
        if(!empty($locations)){
        $implodeLocations = implode(",",$locations);
       // $sqlLocations = " AND ";
        }
        
       
       
        
        
        
        //-------------------------------------------------------------------------------------------------------development and query proper will start here-------------------------------------------
        $criteria['error'] = array();
        $outputData = array();
        $headers = array();
        $whereClause = array();
        $whereTraining  = array();
        $startmonth = "";
        $startyear = "";
        $endmonth = "";
        $endyear = "";
        if($aggregateMethod=="facility_training"){
             if(empty($locations) && empty($trainingIDs)){
                 $criteria['error'][] = "Please select a geography";
             }else{

             }
         }else{
         if(empty($locations) && $national==""){
             $criteria['error'][] = "Please select a geography";
         }
         }
        if($duration=="custom"){
            $profError = array();
        $profError = $reports->validatPeriodTimeRange($period,$startMonth,$startYear,$endMonth,$endYear);
        $criteria['error'] = array_merge($criteria['error'],$profError);
        }
        else if(empty($duration) && $period!="total"){
            $criteria['error'][] = "Please fill in the appropriate duration or date range for this period selection";
        }
        else if(empty($duration) || $period==""){
            $criteria['error'][] = "Please fill in the appropriate duration or date range for this period selection";
        }
        //print_r($criteria['error']);exit;
        if(empty($criteria['error'])){
             
        list($startYears,$endYears,$startmonth,$startyear,$endmonth,$endyear) = $reports->getStartDateEndDateWithPeriod($period,$startMonth,$startYear,$endMonth,$endYear,$duration);
        
       $permanentStartYear =  "1984-01-01";
        $sizeof = sizeof($startYears);
         ini_set('max_execution_time', 0); 
         ini_set('memory_limit', '-1');
       
        if($aggregateMethod=="aggreg_data"){
          
          $headers = $reports->createAggregateHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period);
         
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit', '-1');
        for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
            ini_set('memory_limit', '-1');
          //internal initialization
          
          $permanentStartYear = "1984-01-01";
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
           
           $locations = array_unique($locations);
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            foreach($locations as $loc){
                $provData = array();
                $provDataRaw  = array();
                $resultArray = array();
                $consumptionData = array();
                $whereTraining  = array();
                $whereClause = array();
                $trainingResult= array();
                $stockedOutFac = array();
                $whereStatement = "";
                $whereStatementConsumption = "";
                $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,"");
                $whereTraining = $reports->createWheretrainingArrayFromParameters($implodeTrainingType,$implodeTrainingOrganizer,$permanentStartYear,$tempEndDate);
                if($loc!=0){
                $whereClause[] = "(flv.geo_parent_id ='$loc' OR flv.parent_id='$loc' OR flv.location_id='$loc')";
               
                }
               // print_r($whereClause);exit;
                $whereStatementConsumption = implode(" AND ",$whereClause);
                
                //Helper2::jLog( "Helloer");exit;
                      
//                $facilities = $reports->getFacilitiesWithLocation($loc);
//                $facilityCount = sizeof($facilities);
//                $facility_ids = implode(",",$facilities);
                
                if($loc!=0){
                    
                  $whereTraining[] = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc))"; 
                 
                  $facilitiesReporting = $reports->get_all_facilities_reporte_rate_aggregate($loc,$tempStartDate,$tempEndDate);
                   }else{
                       $nationalLocations = $helper->getLocationTierIDs("1");
                       $implodedLocations = implode(",", $nationalLocations);
                       $whereTraining[] = "(flv.geo_parent_id IN ($implodedLocations) OR flv.parent_id IN ($implodedLocations) OR flv.location_id IN ($implodedLocations))"; 
                       $facilitiesReporting = $reports->get_all_facilities_reporte_rate_aggregate($loc,$tempStartDate,$tempEndDate);
                   }
              
                  $whereStatement = implode(" AND ",$whereTraining);
                
                
                $totalFacilities = $reports->countAllFacilities($loc);
                if(!empty($providing)){
                    if($loc==0 || $loc=="0"){
                    $whereProviding = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";

                    }else {
                    $whereProviding = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc)) AND (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(flv.geo_parent_id IN ($loc) OR flv.parent_id IN ($loc) OR flv.location_id IN ($loc)) AND (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
//Helper::jLog($whereProviding);
                    }
                     $provDataRaw = $reports->processProvData($whereProviding,$whereProviding2);
                    

                }
                $caculationConsumption = array();
                if(!empty($consumption)){                  
                $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
                }
                if(!empty($stockOut)){
                $stockedOutFac = $reports->facilitiesStockedOut($whereStatementConsumption);
                //Helper2::jLog(print_r($stockedOutFac,true));
                }
                if(!empty($trainingType) || !empty($trainingOrganizer) ){
                $trainingResult = $reports->countHWsResult($whereStatement,$trainingType,$trainingOrganizer);
                }
               
               // print_r($consumptionData);
                $resultArray = $reports->createAggregateResultData($loc,$trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period,$totalFacilities,$facilitiesReporting,$trainingResult,$provDataRaw,$caculationConsumption,$tempStartDate,$stockedOutFac);
         //($resultArray);exit;
                    $outputData[] = $resultArray;
            }
           
            
            
            
          }
          
       
        }
        else if($aggregateMethod=="facility_geog"){
             //ini_set('max_execution_time', 0); 
            for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
          //internal initialization
          
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
          
           $locations = array_unique($locations);
           $implodeLocations = implode(",",$locations);
           $facility = $reports->getFacilitiesWithLocationAndFacilities($implodeFacility,$implodeLocations);
           $headers = $reports->createFacAloneHeaders($trainingType,$trainingOrganizer,$providing,$consumption,$stockOut,$period);
           foreach($facility as $fac){
               $provData = array();
               $provDataRaw = array();
               $resultArray = array();
               $consumptionData = array();
               $whereTraining  = array();
               $whereClause = array();
               $whereStatementTraining = "";
               $whereStatementConsumption = "";
               $caculationConsumption = array();
               
               $facility_id = $fac['id'];
               $zoneName = $fac['geo_zone'];
               $stateName = $fac['state'];
               $stateName = str_replace(",", "@", $stateName);
               $lgaName = $fac['lga'];
               $lgaName = str_replace(",", "@", $lgaName);
               $facilityName = $fac['facility_name'];
               $facilityName = str_replace(",", "@", $facilityName);
              
               $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,$facility_id);
               $whereTraining = $reports->createWheretrainingArrayFromParameters($implodeTrainingType,$implodeTrainingOrganizer,$permanentStartYear,$tempEndDate);
               $whereTraining[] = "facility_id IN ($facility_id)"; 
               
               //get the formatted training organizer list if present
               $stringPhrase = $reports->getFormattedTrainingOrganizer($implodeTrainingType,$implodeTrainingOrganizer,$tempStartDate,$tempEndDate,$facility_id);
               $stringPhrase = str_replace(",", "@", $stringPhrase);
                //echo "hello";exit;
               
               $whereStatementConsumption = implode(" AND ",$whereClause);
               $whereStatementTraining = implode(" AND ",$whereTraining);
               $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
               //Helper2::jLog(print_r($caculationConsumption,true));
              // $result = $reports->getAggregateConsumptionResult($whereStatementConsumption);
               
                if(!empty($providing)){
                    
                    
                    $whereProviding = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id)) ";
                    $whereProviding2 = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id))";
                    
                  //Helper::jLog($whereProviding);
                     $provDataRaw = $reports->processProvDataFac($whereProviding,$whereProviding2);
                                        

                }
                
                
            $trainingResult = $reports->countHWsResult($whereStatementTraining,$trainingType,$trainingOrganizer);
               
               $facilitiesReporting = $reports->get_all_facilities_reporte_rates($facility_id,$tempStartDate,$tempEndDate);
              // print_r($trainingResult);exit;
               $resultArray = $reports->createFacilityResultData($stateName,$lgaName,$facilityName,$trainingType,$trainingOrganizer,$trainingResult,$consumption,$caculationConsumption,$providing,$provDataRaw,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting,$stringPhrase);
               
            $outputData[] = $resultArray;    
           //print_r($resultArray); exit;
           }
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            
            }
            
        }
        else if($aggregateMethod=="facility_training"){
            //$criteria['error'][] = "Selection module still undergoing development";
            for($i=0;$i<$sizeof;$i++){
            ini_set('max_execution_time', 0); 
            
          //internal initialization
          
          $tempStartDate = $startYears[$i];
          $tempEndDate = $endYears[$i];
          
           
           $locations = array_unique($locations);
           $implodeLocations = implode(",",$locations);
           
           $facility = $reports->selectAggregateFacDataWithTrainingID($trainingIDs,$tempStartDate,$tempEndDate,$implodeLocations);
           
           $headers = $reports->createFacWithTrainingHeaders($providing,$consumption,$stockOut,$period);
           foreach($facility as $fac){
               $provDataRaw = array();
               $resultArray = array();
               $consumptionData = array();
               $caculationConsumption = array();
               $whereTraining  = array();
               $whereClause = array();
               $whereStatementTraining = "";
               $whereStatementConsumption = "";
               
               $facility_id = $fac['facility_id'];
               
               $stateName = $fac['state'];
               $stateName = str_replace(",", "@", $stateName);
               $lgaName = $fac['lga'];
               $lgaName = str_replace(",", "@", $lgaName);
               $facilityName = $fac['facility_name'];
               $facilityName = str_replace(",", "@", $facilityName);
               $hwcount = $fac['hwcount'];
               $trainingOrganizerPhrase = $fac['training_organizer_phrase'];
               $trainingOrganizerPhrase = str_replace(",", "@", $trainingOrganizerPhrase);
               
               $trainingTitlePhrase = $fac['training_title_phrase'];
               $trainingTitlePhrase = str_replace(",", "@", $trainingTitlePhrase);
               
               $training_id = $fac['training_id'];
               $whereClause = $reports->createWhereClauseArrayFromParameters($tempStartDate,$tempEndDate,$implodeConsumption,$implodeStockOut,$facility_id);
              
               $whereStatementConsumption = implode(" AND ",$whereClause);
               if(!empty($providing)){
                     if($loc==0 || $loc=="0"){
                    $whereProviding = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    $whereProviding2 = "(c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') ";
                    }else{
                    $whereProviding = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='fp' OR com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id)) ";
                    $whereProviding2 = " (c.date>='$tempStartDate' AND c.date<='$tempEndDate') AND (com.commodity_type='larc') AND (c.facility_reporting_status = '1') AND (facility_id IN ($facility_id))";
                    }
//Helper::jLog($whereProviding);
                     $provDataRaw = $reports->processProvDataFac($whereProviding,$whereProviding2);
                                        

                }
//               $result = $reports->getAggregateConsumptionResult($whereStatementConsumption);
//               
//               list($provData,$consumptionData) = $reports->getSumConsumption($result);
               $caculationConsumption = $reports->getConsumptionRawData($whereStatementConsumption);
 
               $facilitiesReporting = $reports->get_all_facilities_reporte_rates($facility_id,$tempStartDate,$tempEndDate);
              
               $resultArray = $reports->createFacilityWithtrainingResultData($stateName,$lgaName,$facilityName,$hwcount,$training_id,$trainingOrganizerPhrase,$trainingTitlePhrase,$consumption,$caculationConsumption,$providing,$provDataRaw,$stockOut,$tempStartDate,$tempEndDate,$period,$facilitiesReporting);
               
            $outputData[] = $resultArray;    
           //print_r($resultArray); exit;
           }
           //echo sizeof($locations);exit;
           //--------------------loop through each location to get the result for the current period-------------------------------------------------
            
            }
            
        }
        }
$this->viewAssignEscaped ('headers',$headers);
$this->viewAssignEscaped ('output',$outputData);
$this->viewAssignEscaped ('crtiteria',$criteria);


//$status->setStatusMessage($stat);
$this->viewAssignEscaped('criteria',$criteria);
}
public function allQueriesDataLoad(){
    
}
public function trainingresultAction(){
//     ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
    if ($this->getRequest()->isXmlHttpRequest()) {
  $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                
                $reports = new Report();
		$this->view->assign ( 'mode', 'search' );
                $helper = new Helper2();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                 $training = new Training();
                // echo $training->trainingEndDateLimit("min");
                 //exit;
                 $minimum_end_date = explode("-",$training->trainingEndDateLimit("min"));
                 $maximum_end_date = explode("-",$training->trainingEndDateLimit("max"));
                // print_r($minimum_end_date);
                // echo '<br/>';
                 //print_r($maximum_end_date);
                 //exit;
                 $userLocations = array();
                 $quickLocation = new Location();
                
                $userLocations = $quickLocation->getUserLocationWithRole();
                
            // Helper2::jLog(print_r($userLocations,true));//exit;
                 $minimum_year = $minimum_end_date[0];
                 $minimum_month = $minimum_end_date[1];
                 $minimum_day = $minimum_end_date[2];
                 
                 
                 
                 $maximum_year = $maximum_end_date[0];
                 $maximum_month = $maximum_end_date[1];
                 $maximum_day = $maximum_end_date[2];
                 
                 $defaultTrainingEndDateStart = $minimum_day."/".$minimum_month."/".$minimum_year;
                 $defaultTrainingEndDateEnd = $maximum_day."/".$maximum_month."/".$maximum_year;
                 
		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                //$number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
                $locations = array();
                $locationsPerUserGeo = array();
                $locations = Location::getAll("1");
                
                $locationsPerUserGeo = $quickLocation->getAll("");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                    $locationsPerUserGeo[$id] = $facilitiesArray[$v];
                }
                
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
                $this->viewAssignEscaped('locations', $locations);
                $this->viewAssignEscaped('locationsPerGeo',$locationsPerUserGeo);
               //exit;
               //print_r($locations); exit;
		
		//course
                $tsql = "SELECT * FROM `training_title_option` WHERE is_deleted='0' ORDER BY `training_title_phrase`  ASC";
                
                $trainingtypes = $db->fetchAll($tsql);
                $this->viewAssignEscaped ( 'trainingtypes', $trainingtypes );
				$training_type_not_set = array();
                foreach($trainingtypes as $training){
				$training_type_not_set[] = $training['id'];
				}
                 $tosql = "SELECT * FROM `training_organizer_option` WHERE is_deleted='0'  ORDER BY `training_organizer_phrase`ASC";
                
                $trainingorg = $db->fetchAll($tosql);
                $this->viewAssignEscaped ( 'trainingorganizer', $trainingorg );
                
                $csql = "SELECT * FROM commodity_name_option ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);
                $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );
                
                $filter = "";
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
                $output = array();
                
                
                $locationName =  $quickLocation->getUserLocationName();
                $total_trainings = "";
                //-----------------------------------------the query is here now.----------------------------------------------//
                $agrregate_method = $this->getSanParam('aggregate_method');
                if($agrregate_method=="view_part_names"){
                $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                
                }else{
                $zone = $this->getSanParam('aggreg_province_id');
                $state  = $this->getSanParam('aggreg_district_id');
                $localgovernment  = $this->getSanParam('aggreg_region_c_id'); 
                }
                //Helper2::jLog(print_r($zone,true));
                
                $zone = $reports->formatSelection($zone);
                $state = $reports->formatSelection($state);
                $localgovernment = $reports->formatSelection($localgovernment);
                //$LGA = $localgovernment;
               // print_r($localgovernment);
               
                $person_status_id = array();
                $person_status_id = $this->getSanParam('person_status_id');
                $person_status_id = $reports->formatSelection($person_status_id);
                
//                Helper2::jLog(print_r($this->getSanParam('person_status_id'),true));
//                Helper2::jLog("This is the middle line between the two both of them");
//                Helper2::jLog(print_r($person_status_id,true));
               
                
                //print_r($localgovernment);exit;
                //print_r($LGA);exit;
                //print_r($localgovernment);echo '<br/><br/>';
               //$localgovernment = $reports->removeSelected($this->getSanParam('region_c_id'));
                //print_r($localgovernment); exit;
               // $facility  = $this->getSanParam('facility_id');
                $facilities_new = array();
                $facilities_new  = $this->getSanParam('facility_id');
                $facilities_new = $reports->removeSelected($facilities_new);
                $facility = array();
                if(!empty($facilities_new)){
                foreach($facilities_new as $fac){
                $facility_location = explode("_",$fac);
                $facility[] = $facility_location[3];
                }
                }
                
                $facility = $reports->formatSelection($facility);
                $training_type = $this->getSanParam('training_type');
                 $training_type = $reports->formatSelection($training_type);
                
                $training_type2 = $this->getSanParam('cumu');
                $training_type2 = $reports->formatSelection($training_type2); 
                $stock_out = $this->getSanParam('stock_out');
                
                 $preview = $this->getSanParam('go');
                $locations = array();
               //Helper2::jLog(print_r($state,true));
               // echo sizeof($state);
             // print_r($state);
                if($agrregate_method!="view_aggregate_part" && $agrregate_method!="view_part_names"){
                    $locations = array();
                     if(!empty($localgovernment)){
                    
                foreach($localgovernment as $state){
                  $state_gen =   explode("_",$state);
                  $parent_id = $state_gen[0];
                $district = $state_gen[1];
                $location_id = $state_gen[2];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
                    
                $filter = "location_id";
                
               
}else if(!empty($state)){
 // echo 'hi';
    
    foreach($state as $states){
                  $state_gen =   explode("_",$states);
                  $parent_id = $state_gen[0];
                $location_id = $state_gen[1];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
    
                $filter = "parent_id";
}

else if(!empty($zone)){
    
    $filter = "geo_zone";
//print_r($locations); echo 'here inside ';exit;
//array_push($locations,$zone);
if(is_array($zone)){
//print_r($zonal);
foreach($zone as $zonal){

$locations[] = $zonal;

}
}else{
$locations[] = $zone;
}
}
else{
    
   unset($locations);
}
                }
                else{
                    if(!empty($state)){
 // echo 'hi';
    
    foreach($state as $states){
                  $state_gen =   explode("_",$states);
                  $parent_id = $state_gen[0];
                $location_id = $state_gen[1];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
    
                $filter = "parent_id";
}
else if(!empty($zone)){
    $filter = "geo_zone";
//print_r($locations); echo 'here inside ';exit;
//array_push($locations,$zone);
if(is_array($zone)){
//print_r($zonal);
foreach($zone as $zonal){

$locations[] = $zonal;

}
}else{
$locations[] = $zone;
}
}
else{
    unset($locations);
}

                } 
            
              
$consump_loc = array();
if(!empty($locations)){
     foreach($locations as $loc){
        $consump_loc[$loc] = ""; 
         
     }    
}

         if(!isset($locations) || empty($locations)){
         //$filter = "parent_id";
         $locations = $helper->getLocationTierIDs("1");
         $location[] = 0;
         $filter = "geo_zone";
     }
     
     //print_r($locations);exit;
                $messageAlert = "";
                $perLocation = "";
                
                $outSideLocationPart = "";
                $has_result = "";
                $agrreg = "";
                $fac_with_type = "";
                $total_participants = "";
                
                $list="";
                if(!empty($locations)){
                $list = implode(',', $locations);
                }
                $trainingorganizer = $this->getSanParam('trainingorganizer');
                $trainingorganizer = $reports->formatSelection($trainingorganizer);
                $trainingOrgTemp = $trainingorganizer;
               
               // print_r($trainingorganizer);exit;
                $cadre = $this->getSanParam('cadre');
                $stock_out = $this->getSanParam('stock_out');
                
                $trainingEndDateStartValue = $this->getSanParam('trainingEndDateStart');
                $trainingEndDateEndValue = $this->getSanParam('trainingEndDateEnd');
                
                $trainingEndDatesStart = array();
                $trainingEndDatesEnd = array();
                
                $trainingEndDatesStart = explode("/",$trainingEndDateStartValue);
                $trainingEndDatesEnd = explode("/",$trainingEndDateEndValue);
                
                $start_day = "";
                $start_month = "";
                $start_year = "";
                
               
                if(isset($trainingEndDatesStart[0])){
                    $start_day = $this->check_length_add_one($trainingEndDatesStart[0]);
                }
                if(isset($trainingEndDatesStart[1])){
                    $start_month = $this->check_length_add_one($trainingEndDatesStart[1]);
                }
                if(isset($trainingEndDatesStart[2])){
                    $start_year = $trainingEndDatesStart[2];
                }
                
                
                $end_day = "";
                $end_month = "";
                $end_year = "";
                 if(isset($trainingEndDatesEnd[0])){
                   $end_day = $this->check_length_add_one($trainingEndDatesEnd[0]);
                }
                if(isset($trainingEndDatesEnd[1])){
                    $end_month = $this->check_length_add_one($trainingEndDatesEnd[1]);
                }
                if(isset($trainingEndDatesEnd[2])){
                    $end_year = $trainingEndDatesEnd[2];
                }
                
               
                $dobStartValue = $this->getSanParam('dobStart');
                $dobEndValue = $this->getSanParam('dobEnd');
                
                
                
             
                $dobStartDatesValue = array();
                $dobEndDatesValue = array();
                
                $dobStartDatesValue = explode("/",$dobStartValue);
                $dobEndDatesValue = explode("/",$dobEndValue);
                
                $dob_start_day  = "";
                $dob_start_month = "";
                $dob_start_year = "";
                
                if(isset($dobStartDatesValue[0])){
                    $dob_start_day = $this->check_length_add_one($dobStartDatesValue[0]);
                }
                if(isset($dobStartDatesValue[1])){
                    $dob_start_month = $this->check_length_add_one($dobStartDatesValue[1]);
                }
                if(isset($dobStartDatesValue[2])){
                    $dob_start_year = $dobStartDatesValue[2];
                }
                
//                $dob_start_day = $this->check_length_add_one($dobStartDatesValue[1]);
//                $dob_start_month = $this->check_length_add_one($dobStartDatesValue[0]);
//                $dob_start_year = $dobStartDatesValue[2];
                $dob_end_day  = "";
                $dob_end_month = "";
                $dob_end_year = "";
                if(isset($dobEndDatesValue[0])){
                   $dob_end_day = $this->check_length_add_one($dobEndDatesValue[0]);
                }
                if(isset($dobEndDatesValue[1])){
                    $dob_end_month = $this->check_length_add_one($dobEndDatesValue[1]);
                }
                if(isset($dobEndDatesValue[2])){
                    $dob_end_year = $dobEndDatesValue[2];
                }
                
                $period = $this->getSanParam('period');
                $certification = $this->getSanParam('certification');
                $gender = $this->getSanParam('gender');
               
                $consumption = $this->getSanParam('consumption');
                
                    $current_month = date('m');
                $current_day = date('d');
                $current_year = date('Y');
                 //echo $agrregate_method;exit;
                 if($start_year==""){
                        $start_year = "2011";
                    }
      if($start_day==""){
          $start_day = "01";
      }
      if($start_month==""){
          $start_month = "01";
      }
      if($end_year==""){
                        $end_year = $current_year;
                    }
      if($end_day==""){
          $end_day = $current_day;
      }
      if($end_month==""){
          $end_month = $current_month;
      }
      
      
      $dob_start_date = $dob_start_year.'-'.$dob_start_month.'-'. $dob_start_day ;
                $dob_end_date = $dob_end_year.'-'.$dob_end_month.'-'.$dob_end_day;
                
                
      $fac_types = array();
       $start_date = $start_year.'-'.$start_month.'-'.$start_day;
      $end_date = $end_year.'-'.$end_month.'-'.$end_day;
      $error = array();
  // print_r($locations);
       if($agrregate_method=="" && $preview!="" ){
                    $error[] = "Please select a viewing category";
                }
  if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND birthdate>='".$dob_start_date."' AND birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
         $implodedLocations = "";
              if(isset($locations)){
       $implodedLocations = implode(",",$locations);
              }
              
              $locationQuery = "";
                      if(!empty($implodedLocations)){
                          $locationQuery = "AND (loc.parent_id IN($implodedLocations) OR loc.id IN ($implodedLocations))";
                      }

        $trainingorganizerquery = "";
    if($agrregate_method=="view_individual_train"){
        if(is_array($trainingorganizer)){
            $training_organizer = implode(",",$trainingorganizer);
        }else{
           $training_organizer = ""; 
        }
        
        if(is_array($training_type)){
           $trainingtypes = implode(",",$training_type); 
        }else{
            $trainingtypes  = "";
        }
              
                 
           
              
              if($training_organizer=="" || empty($trainingorganizer)){
                  $trainingorganizerquery=="";
              }else{
                  $training_organizer = implode(",",$trainingorganizer);
                  $trainingorganizerquery = "AND training_organizer_option_id IN (".$training_organizer.")";
              }
             
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND birthdate>='".$dob_start_date."' AND birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
              if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND facility_id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
            
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND training_title_option_id IN (".$trainingtypes.")";
              }
              
             if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND person.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
             // echo $start_date;exit;
              $records = $this->get_training_records_from_db( $trainingorganizerquery,$trainingtypequery,$start_date,$end_date,$locationQuery);
             $start_month_name = $this->return_the_month_name($start_month);
                    $start_month_name = substr($start_month_name,0,3);
               $end_month_name = $this->return_the_month_name($end_month);
                    $end_month_name = substr($end_month_name,0,3);
                    $word = "Training $start_day $start_month_name $start_year to $end_day $end_month_name $end_year";
                    
              $headers = array("ID",$word,"Training Partner","Start Date","End Date","Participants");
               $output = array();
              foreach($records as $record){
                  $data_collector  = array();
                  
                 $training_id = $record['id'];
                  $link = "<a href='../training/view/id/$training_id'>$training_id</a>";
                 $trainingtitle = $record['training_title_phrase'];
                 $training_organizer = $record['training_organizer_phrase'];
                 $training_organizer = str_replace(",", "@", $training_organizer);
                 $trainStartDate = strtotime($record['training_start_date']);
                 $training_start_date = date("d-m-Y",$trainStartDate);
                 $trainEndDate = strtotime($record['training_end_date']);
                 $training_end_date = date("d-m-Y",$trainEndDate);
                 $participants = $this->get_counter_person_trainings($training_id,$facilityq,$pstatusq);
                 $data_collector = array($link,$trainingtitle,$training_organizer,$training_start_date,$training_end_date,$participants);
             
                 array_push($output,$data_collector);
             }
             $total_trainings = sizeof($records);
          }
          else if($agrregate_method=="view_aggregate_train"){
              $start_month_name = $this->return_the_month_name($start_month);
              $start_month_name = substr($start_month_name,0,3);
              $end_month_name = $this->return_the_month_name($end_month);
              $end_month_name = substr($end_month_name,0,3);
                    
                    $total_trainings = 0;
                    if(empty($trainingorganizer)){
                        $trainingorganizer = $this->get_all_training_organizers();
                        $trainingorganizer[] = "";
                    }
                    if(empty($training_type) ){
                        $training_type = $this->get_all_training_titles();
                    }
                    $word = "Training $start_day $start_month_name $start_year to $end_day $end_month_name $end_year";
                $headers = array($word,"Training Type","Training Organizer");
                 $output = array();
                    foreach($trainingorganizer as $training_org){
                  foreach($training_type as $type){
                      
                      $data_collector = array();
                     $trainings =  $this->get_total_number_of_trainings($training_org,$type,$start_date,$end_date,$locationQuery);
                      if($trainings<=0 ){
                          continue;
                      }
                     $total_trainings = $trainings + $total_trainings;
                      $training_organizer = $this->get_training_organizer_phrase($training_org);
                      $training_organizer = str_replace(",", "@", $training_organizer); //replacing the comma with @ because of the json formatting
                      
                      $training_title = $this->get_training_title_option($type);
                      $data_collector = array($trainings,$training_title,$training_organizer);
                      
                      array_push($output,$data_collector);
                  }
              }
          }
  //print_r($locations); exit;
          
//         else if($agrregate_method=="view_aggregate_part"){
//             if(!isset($locations) || empty($locations)){
//         $locations = $this->get_all_locations();
//         }
//         $agrreg = true;
//          
//     if(!empty($locations) && $agrregate_method=="view_aggregate_part" ){
//         //$sql  = 
//      
//      //echo 'hello na here i dey';exit;
//              
//     
//      if($agrregate_method=="view_aggregate_part"){
//          if(!isset($locations) || empty($locations)){
//         $error[] = "Please select Geography";
//         
//         }
//         if(!isset($locations) || empty($locations)){
//         $locations = $this->get_all_locations();
//         }
//		if($trainingorganizer=="" || empty($trainingorganizer)){
//		$trainingorganizer = array();
//		//$training_organizer = implode(",",$trainingorganizer);
//	        //$trainingorganizer[]  = "";
//                $trainingorganizer[] = 0;
//		$all_training_organizer = $this->select_all_from_training_organizer();
//		foreach($all_training_organizer as $id_s){
//		$trainingorganizer[] = $id_s['id'];
//		}
//                
//		$training_organizer = implode(",",$trainingorganizer);
//		}else{
//		$training_organizer = implode(",",$trainingorganizer);
//		}
//		if($training_type=="" || empty($training_type)){
//		$training_type = $training_type_not_set;
//		}
//        //print_r($locations);exit;
//                //echo 'This is the filter '.$filter_location.'filter';exit;
//         $size = sizeof($locations);
//         $output = array();
//         if($filter=="geo_zone"){
//             $geog = "Zone";
//         }else if($filter=="parent_id"){
//             $geog = "State";
//         }
//         else if($filter=="location_id"){
//             $geog = "Local Government";
//         }else{
//             $geog = "Zone";
//         }
//        // echo $filter;exit;
//         $headers = array("Participants",$geog,"Training Type","Training Partner");
//         $total_participants = 0;
//          foreach($locations as $location_id){
//              if($location_id!=""){
//            // $location_id = $locations[$i];
//             $location_name = $this->get_location_name($location_id);
//              foreach($training_type as $trainingtype){
//                  // echo $location_id.'<br/>';
//                   $training_title_phrase = $this->get_training_title_option($trainingtype);
//				  
//                  foreach($trainingorganizer as $training_org){
//                      $trainings = 0;
//                      
//                      $trainings =  $this->get_total_number_of_trainings($training_org,$trainingtype,$start_date,$end_date);
//                      if($trainings<=0 ){
//                          continue;
//                      }
//                      $training_org = addslashes($training_org);
//					  $trainingorgq = "AND facility_summary_person_view.training_organizer_option_id = '".$training_org."'";
//                      $data_collector = array();
//                      $facility_ids = array();
//                     $all_facilities = $this->get_all_facilities_with_location_id($location_id,$trainingorgq);
//                    foreach($all_facilities as $fac_details){
//                     
//                      $facility_ids[$fac_details['facility_id']] = $fac_details['facility_id'];
//                      array_push($facility_ids,$fac_details['facility_id']);
//                      
//                      }
//                      $facility_ids = array_unique($facility_ids);
//                     $facility_ids = implode(',',$facility_ids);
//                    //echo '';
//                      $training_organizer = $training_org;
//                 $trainingtypes = $trainingtype;
//           if(empty($cadre)){
//                  $cadrequery = "";
//              }else{
//                  $cadreImplode = implode(",",$cadre);
//                  $cadrequery = "AND p.primary_qualification_option_id IN ($cadreImplode)";
//              }
//              
//              if($training_organizer==""){
//                  $trainingorganizerquery="";
//              }
//               else if(empty($trainingOrgTemp)){
//                  $trainingorganizerquery="";
//              }
//              else{
//                  //$training_organizer = implode(",",$trainingorganizer);
//                  $trainingorganizerquery = "AND t.training_organizer_option_id ='$training_organizer'";
//              }
//              
//              if($end_day=="" && $end_month=="" && $end_year==""){
//                  $training_end_date  = "";
//              }else{
//                  $training_end_date = "AND t.training_end_date>='$start_date' AND t.training_end_date<='$end_date'";
//              }
//              //print_r($facility);exit;
//             if(!empty($facility)){
//                  $facer = implode(",",$facility);
//                  $facilityq = "AND flv.id IN (".$facer.")";
//                  }
//              else{
//                  $facilityq = "";
//              }
//              
//                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
//                  $dob_query =  "AND p.birthdate>='".$dob_start_date."' AND p.birthdate<='".$dob_end_date."'";
//              }else{
//                  $dob_query = "";
//              }
//             // echo $facer;exit;
//              if($trainingtypes==""){
//                  $trainingtypequery = "";
//              }
//              else{
//                  
//                  
//                  $trainingtypequery = "AND tto.id IN (".$trainingtypes.")";
//              }
//              if($certification==""){
//                  $certificationq = "";
//              }else{
//                  $certificationq = " AND (ptt.certification='".$certification."' OR ptt.certification='".strtolower($certification)."') ";
//              }
//              if($gender==""){
//                  $genderq = "";
//              }
//              else{
//               $genderq = " AND p.gender='".$gender."'";   
//              }
//            $locator = $location_id;
//              $locationquery = "(flv.geo_zone_id IN ($locator) OR flv.lga_id IN ($locator) OR flv.state_id IN ($locator))";
//              $all_participants = $this->get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq,$dob_query);
//       
//                     $participants = 0;
//                    
//                     $training_organizer_phrase = $this->get_training_organizer_phrase($training_org);
//                     $training_organizer_phrase = str_replace(",", "@", $training_organizer_phrase);
//                     $participants = sizeof($all_participants);
//                     if($participants<=0){
//                         continue;
//                     }
//                     $total_participants = $participants + $total_participants;
//                     array_push($data_collector,$participants);
//                     array_push($data_collector,$location_name);
//                     array_push($data_collector,$training_title_phrase);
//                     array_push($data_collector,$training_organizer_phrase);
//                     //print_r($data_collector);
//                     array_push($output,$data_collector);
//                     }
//                     
//                      
//                  }
//          }
//          //echo $location_id;
//                  // echo $location_id;
//              }
//          }
//     }
//     else{
//    //echo 'Na here we dey';exit;
//    $error[] = "Please select a Geography";
//}
//
//}
          else if($agrregate_method=="view_aggregate_part"){
             
         $agrreg = true;
          
     if(!empty($locations) && $agrregate_method=="view_aggregate_part" ){
         //$sql  = 
      
      //echo 'hello na here i dey';exit;
              
     
      if($agrregate_method=="view_aggregate_part"){
          if(!isset($locations) || empty($locations)){
         $error[] = "Please select Geography";
         
         }
        
		if($trainingorganizer=="" || empty($trainingorganizer)){
		$trainingorganizer = array();
		//$training_organizer = implode(",",$trainingorganizer);
	        //$trainingorganizer[]  = "";
                $trainingorganizer[] = 0;
		$all_training_organizer = $this->select_all_from_training_organizer();
		foreach($all_training_organizer as $id_s){
		$trainingorganizer[] = $id_s['id'];
		}
                
		//$training_organizer = implode(",",$trainingorganizer);
		}else{
		//$training_organizer = implode(",",$trainingorganizer);
		}
		if($training_type=="" || empty($training_type)){
		$training_type = $training_type_not_set;
		}
        //print_r($locations);exit;
                //echo 'This is the filter '.$filter_location.'filter';exit;
         $size = sizeof($locations);
         $output = array();
         if($filter=="geo_zone"){
             $geog = "Zone";
         }else if($filter=="parent_id"){
             $geog = "State";
         }
         else if($filter=="location_id"){
             $geog = "Local Government";
         }else{
             $geog = "Zone";
         }
        // echo $filter;exit;
         $headers = array("Participants",$geog,"Training Type","Training Partner");
         $total_participants = 0;
          foreach($locations as $location_id){
              if($location_id!=""){
            // $location_id = $locations[$i];
             $location_name = $this->get_location_name($location_id);
              foreach($training_type as $trainingtype){
                  // echo $location_id.'<br/>';
                   $training_title_phrase = $this->get_training_title_option($trainingtype);
				  
                  foreach($trainingorganizer as $training_org){
                      $trainings = 0;
                      
                      $trainings =  $this->get_total_number_of_trainings($training_org,$trainingtype,$start_date,$end_date);
                      if($trainings<=0 ){
                          continue;
                      }
                      $training_org = addslashes($training_org);
					  $trainingorgq = "AND facility_summary_person_view.training_organizer_option_id = '".$training_org."'";
                      $data_collector = array();
                      $facility_ids = array();
//                     $all_facilities = $this->get_all_facilities_with_location_id($location_id,$trainingorgq);
//                    foreach($all_facilities as $fac_details){
//                     
//                      $facility_ids[$fac_details['facility_id']] = $fac_details['facility_id'];
//                      array_push($facility_ids,$fac_details['facility_id']);
//                      
//                      }
//                      $facility_ids = array_unique($facility_ids);
//                     $facility_ids = implode(',',$facility_ids);
                    //echo '';
                      $training_organizer = $training_org;
                 $trainingtypes = $trainingtype;
           if(empty($cadre)){
                  $cadrequery = "";
              }else{
                  $cadreImplode = implode(",",$cadre);
                  $cadrequery = "AND p.primary_qualification_option_id IN ($cadreImplode)";
              }
              
              //Helper2::jLog("THis is the training".$training_org);
               if(!empty($training_org) || $training_org=='0' || $training_org==0){
                  //$training_organizer = implode(",",$trainingorganizer);
                  $trainingorganizerquery = "AND t.training_organizer_option_id ='$training_org'";
              }
              else  if($training_organizer==""){
                  $trainingorganizerquery="";
              }else if(empty($trainingOrgTemp)){
                  $trainingorganizerquery="";
              }
             
              
              if($end_day=="" && $end_month=="" && $end_year==""){
                  $training_end_date  = "";
              }else{
                  $training_end_date = "AND t.training_end_date>='$start_date' AND t.training_end_date<='$end_date'";
              }
              //print_r($facility);exit;
             if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND flv.id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
              
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND p.birthdate>='".$dob_start_date."' AND p.birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
             // echo $facer;exit;
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND tto.id IN (".$trainingtypes.")";
              }
              if($certification==""){
                  $certificationq = "";
              }else{
                  $certificationq = " AND (ptt.certification='".$certification."' OR ptt.certification='".strtolower($certification)."') ";
              }
              if($gender==""){
                  $genderq = "";
              }
              else{
               $genderq = " AND p.gender='".$gender."'";   
              }
              
              if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND p.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
            $locator = $location_id;
              $locationquery = "(flv.geo_zone_id IN ($locator) OR flv.lga_id IN ($locator) OR flv.state_id IN ($locator))";
              $all_participants = $this->get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq,$dob_query,$pstatusq);
       
                     $participants = 0;
                    
                     $training_organizer_phrase = $this->get_training_organizer_phrase($training_org);
                     $training_organizer_phrase = str_replace(",", "@", $training_organizer_phrase);
                    
                     $participants = sizeof($all_participants);
                     if($participants<=0){
                         continue;
                     }
                     $total_participants = $participants + $total_participants;
                     array_push($data_collector,$participants);
                     array_push($data_collector,$location_name);
                     array_push($data_collector,$training_title_phrase);
                     array_push($data_collector,$training_organizer_phrase);
                     //print_r($data_collector);
                     array_push($output,$data_collector);
                     }
                     
                      
                  }
          }
          //echo $location_id;
                  // echo $location_id;
              }
          }
     }
     else{
    //echo 'Na here we dey';exit;
    $error[] = "Please select a Geography";
}

}
else if($agrregate_method=="view_part_names"){
    $agrreg = false;
   
    $messageAlert = "Each row represents a single training for a health worker. Health workers with multiple trainings will appear multiple times.";
             $error = array();
           if(!isset($locations) || empty($locations)){
            $locations = $this->get_all_locations();
         }
              $headers = array("ID","First Name","Middle Name","Surname","State","LGA","Facility","Professional Qualification","Training Type","Training Organizer","Training Start Date","Training End Date","Gender","Certification","Status");
               
              if($trainingorganizer=="" || empty($trainingorganizer)){
		$trainingorganizer = array();
		//$training_organizer = implode(",",$trainingorganizer);
	        //$trainingorganizer[]  = "";
                $addempty = "OR t.training_organizer_option_id=''";
                $trainingorganizer[] = "0";
		$all_training_organizer = $this->select_all_from_training_organizer();
		foreach($all_training_organizer as $id_s){
		$trainingorganizer[] = $id_s['id'];
		}
                
		$training_organizer = implode(",",$trainingorganizer);
                
		}else{
                    $addempty = "";
		$training_organizer = implode(",",$trainingorganizer);
		}
              //$training_organizer = implode(",",$trainingorganizer);
               
               
               if($training_type=="" || empty($training_type)){
		$training_type = $training_type_not_set;
		}  
               $trainingtypes = implode(",",$training_type);
                 
              if(empty($cadre)){
                  $cadrequery = "";
              }else{
                  $cadreImplode = implode(",",$cadre);
                  $cadrequery = "AND p.primary_qualification_option_id IN ($cadreImplode)";
              }
              
              if($training_organizer==""){
                  $trainingorganizerquery="";
              }
               else if(empty($trainingOrgTemp)){
                  $trainingorganizerquery="";
              }
              else{
                  $training_organizer = implode(",",$trainingorganizer);
                  
                  $trainingorganizerquery = "AND (t.training_organizer_option_id IN (".$training_organizer.") ".$addempty." )";
              }
              
              if($end_day=="" && $end_month=="" && $end_year==""){
                  $training_end_date  = "";
              }else{
                  $training_end_date = "AND t.training_end_date>='$start_date' AND t.training_end_date<='$end_date'";
              }
              //print_r($facility);exit;
             if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND flv.id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
              
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND p.birthdate>='".$dob_start_date."' AND p.birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
             // echo $facer;exit;
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND tto.id IN (".$trainingtypes.")";
              }
              if($certification==""){
                  $certificationq = "";
              }else{
                 $certificationq = " AND (ptt.certification='".$certification."' OR ptt.certification='".strtolower($certification)."') ";
              }
              if($gender==""){
                  $genderq = "";
              }
              else{
               $genderq = " AND p.gender='".$gender."'";   
              }
              
              if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND p.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
              $locator = implode(',',$locations);
              $locationquery = "(flv.geo_zone_id IN ($locator) OR flv.lga_id IN ($locator) OR flv.state_id IN ($locator))";
              $all_participants = $this->get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq,$dob_query,$pstatusq);
        //print_r($all_participants);exit;
              $output = array();
              $outSideLocationPart = 0;
              $perLocation = 0;
              $total_participants = sizeof($all_participants);
              
              $helper2 = new Helper2();
             // Helper2::jLog($total_participants);
              $has_result = "false";
          foreach($all_participants as $persons){
              $has_result = "true";
              $data_collector = array();
              $id = $persons['person_id'];
              $Link = "<a href='../person/view/id/$id'>$id</a>";
              $first_name = $persons['first_name'];
              $first_name = str_replace(",", "@", $first_name);
              $middle_name = $persons['middle_name'];
              $middle_name = str_replace(",", "@", $middle_name);
              $last_name = $persons['last_name'];
              $last_name = str_replace(",","@",$last_name);
              $state = $persons['state'];
              $state = str_replace(",","@",$state);
              $lga = $persons['lga'];
              $lga = str_replace(",","@",$lga);
              $facility_name = $persons['facility_name'];
              $facility_name = str_replace(",", "@", $facility_name);
              $cadre = $persons['qualification_phrase'];
              $training_types = $persons['training_title_phrase'];
              $training_title_phrase = $persons['training_organizer_phrase'];
              $training_title_phrase = str_replace(",", "@", $training_title_phrase);
              $person_status_title  = $persons['person_status_title'];
//            $training_start_date = $persons['training_start_date'];
//            $training_end_date = $persons['training_end_date'];
              
              $trainStartDate = strtotime($persons['training_start_date']);
              $training_start_date = date("d-m-Y",$trainStartDate);
              $trainEndDate = strtotime($persons['training_end_date']);
              $training_end_date = date("d-m-Y",$trainEndDate);
              
              $certification = $persons['certification'];
              $genders = $persons['gender'];
              $location_id = $persons['location_id'];
              //Helper2::jLog($location_id.PHP_EOL);
              
              if(!empty($userLocations)){
                  if(!in_array($location_id,$userLocations)){
                      $outSideLocationPart = $outSideLocationPart + 1;
                      continue;
                  }else {
                  $perLocation = $perLocation + 1;
              }
              }
            
              //echo $perLocation;
              $data_collector = array($Link,$first_name,$middle_name,$last_name,$state,$lga,$facility_name,$cadre,$training_types,$training_title_phrase,$training_start_date,$training_end_date,$genders,$certification,$person_status_title);
        // print_r($data_collector);echo '<br/><br/>';
              array_push($output,$data_collector);
              }
            
          }
 //exit;
          $locationShow = array();
         // echo 'participants in location '.$perLocation.' '.$locationName.' asides location '.$outSideLocationPart.' total participants '.$total_participants; 
             $locationShow['perLocation'] = $perLocation;
           //echo $locationName;exit;
             $locationShow['locationName'] = $locationName;
             $locationShow['outSideLocation'] = $outSideLocationPart;
             $locationShow['has_result'] = $has_result;
             $locationShow['agreggate_participants'] = $agrreg;
             $this->viewAssignEscaped('perLocation',$locationShow);

             
 $criteria['trainingEndDateStart'] = ($this->getSanParam('trainingEndDateStart')!="")?$trainingEndDateStartValue:$defaultTrainingEndDateStart;
 $criteria['trainingEndDateEnd'] = ($this->getSanParam('trainingEndDateEnd')!="")?$trainingEndDateEndValue:$defaultTrainingEndDateEnd;
 
 $criteria['dobStart'] = $dobStartValue;
 $criteria['dobEnd'] = $dobEndValue;
 

 $criteria['period'] = $period;

 $criteria['aggregate_method'] = $agrregate_method;
//$agrregate_method=="aggregate_unifacilities"
  $criteria['trainingorganizer'] = $this->getSanParam('trainingorganizer');
  $criteria['cadre'] = $this->getSanParam('cadre');;
  $criteria['consumption']  = $this->getSanParam('consumption');
  $criteria['facility'] = $facility ;
  $criteria['certification'] = $this->getSanParam('certification');
  $criteria['gender'] = $this->getSanParam('gender');
  $criteria['training_type'] = $this->getSanParam('training_type') ;
 $criteria['cumu'] = $this->getSanParam('cumu') ;
 
 if($agrregate_method=="view_part_names"){
                $criteria['province_id'] = $this->getSanParam('province_id');
                $criteria['district_id']  = $this->getSanParam('district_id');
                $criteria['region_c_id']  = $this->getSanParam('region_c_id');
                
                }else{
                $criteria['province_id'] = $this->getSanParam('aggreg_province_id');
                $criteria['district_id']  = $this->getSanParam('aggreg_district_id');
                $criteria['region_c_id']  = $this->getSanParam('aggreg_region_c_id'); 
                }
 
 $criteria['stock_out'] = $this->getSanParam('stock_out');
 $criteria['total_participants'] = $total_participants;
 $criteria['total_training'] = $total_trainings;
 $criteria['error'] = $error;
 $criteria['facility_id'] = $this->getSanParam('facility_id');
 $criteria['preview'] = $preview;

 
                $end_date = $end_year.'-'.$end_month.'-'.$end_day;
                $date_format = "yyyy-mm-dd";
          
        $data = array();
        $data['headers'] = $headers;
        $data['output'] = json_encode($output);
        $data['tocktack'] = $fac_with_type;
        $data['alert'] = $messageAlert;
        $data['locationNumber'] = $perLocation;
        $data['total_participants'] = $total_participants;
        $data['total_training'] = $total_trainings;
        $data['aggregate_method'] = $agrregate_method;
        $data['locationName'] = $locationName;
        $data['outSideLocation'] = $outSideLocationPart;
        $data['has_result'] = $has_result;
        $data['agreggate_participants'] = $agrreg;
        //Helper2::jLog(print_r($data,true));
        $dataJSON = json_encode($data);
       
        echo $dataJSON;
    } 
    else{
    return true;
}
}
public function trainingrepAction(){
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                
                 $request = $this->getRequest()->getMethod();
if($this->getSanParam('go') || $this->getSanParam('download') ){
  
  if($request!="POST"){
      $this->_redirect('reports/trainingrep');
      //$this->_forward ( 'facilitysummary' );
      
  }  
} 
//print_r($this->_getAllParams ());exit;
                $reports = new Report();
		$this->view->assign ( 'mode', 'search' );
                $helper = new Helper2();
		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
                 $training = new Training();
                 $personObj = new Person();
                // echo $training->trainingEndDateLimit("min");
                 //exit;
                 $minimum_end_date = explode("-",$training->trainingEndDateLimit("min"));
                 $maximum_end_date = explode("-",$training->trainingEndDateLimit("max"));
                // print_r($minimum_end_date);
                // echo '<br/>';
                 //print_r($maximum_end_date);
                 //exit;
                 $userLocations = array();
                  $quickLocation = new Location();
               $outSideLocationPart = 0;
               $perLocation = 0;
                $userLocations = $quickLocation->getUserLocationWithRole();
             
            // Helper2::jLog(print_r($userLocations,true));//exit;
                 $minimum_year = $minimum_end_date[0];
                 $minimum_month = $minimum_end_date[1];
                 $minimum_day = $minimum_end_date[2];
                 
                 
                 
                 $maximum_year = $maximum_end_date[0];
                 $maximum_month = $maximum_end_date[1];
                 $maximum_day = $maximum_end_date[2];
                 
                 $defaultTrainingEndDateStart = $minimum_day."/".$minimum_month."/".$minimum_year;
                 $defaultTrainingEndDateEnd = $maximum_day."/".$maximum_month."/".$maximum_year;
                 
		$criteria = array ();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                //$number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
                $locationsPerUserGeo = array();
                $locations = Location::getAll("1");
                
                $locationsPerUserGeo = Location::getAll("");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                    $locationsPerUserGeo[$id] = $facilitiesArray[$v];
                }
                
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
                $this->viewAssignEscaped('locations', $locations);
                $this->viewAssignEscaped('locationsPerGeo',$locationsPerUserGeo);
               //exit;
               //print_r($locations); exit;
		
		//course
                $tsql = "SELECT * FROM `training_title_option` WHERE is_deleted='0' ORDER BY `training_title_phrase`  ASC";
                
                $trainingtypes = $db->fetchAll($tsql);
                $this->viewAssignEscaped ( 'trainingtypes', $trainingtypes );
				$training_type_not_set = array();
                foreach($trainingtypes as $training){
				$training_type_not_set[] = $training['id'];
				}
                 $tosql = "SELECT * FROM `training_organizer_option` WHERE is_deleted='0'  ORDER BY `training_organizer_phrase`ASC";
                
                $trainingorg = $db->fetchAll($tosql);
                $this->viewAssignEscaped ( 'trainingorganizer', $trainingorg );
                
                $csql = "SELECT * FROM commodity_name_option ORDER BY commodity_name ASC";
                $commodity_name = $db->fetchAll($csql);
                $this->viewAssignEscaped ( 'commodity_name_option', $commodity_name );
                
                $allPersonStatus = $personObj->getAllPersonStatus();
                $this->viewAssignEscaped('person_status',$allPersonStatus);
                   
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
                $output = array();
                
                
                $locationName =  $quickLocation->getUserLocationName();
                
                //-----------------------------------------the query is here now.----------------------------------------------//
                $agrregate_method = $this->getSanParam('aggregate_method');
                if($agrregate_method=="view_part_names"){
                $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                
                }else{
                $zone = $this->getSanParam('aggreg_province_id');
                $state  = $this->getSanParam('aggreg_district_id');
                $localgovernment  = $this->getSanParam('aggreg_region_c_id'); 
                }
                
                
                $zone = $reports->formatSelection($zone);
                $state = $reports->formatSelection($state);
                $localgovernment = $reports->formatSelection($localgovernment);
                //$LGA = $localgovernment;
               // print_r($localgovernment);
               
                $person_status_id = array();
                $person_status_id = $this->getSanParam('person_status_id');
                $person_status_id = $reports->formatSelection($person_status_id);
                
                
                //print_r($localgovernment);exit;
                //print_r($LGA);exit;
                //print_r($localgovernment);echo '<br/><br/>';
               //$localgovernment = $reports->removeSelected($this->getSanParam('region_c_id'));
                //print_r($localgovernment); exit;
               // $facility  = $this->getSanParam('facility_id');
                $facilities_new  = array();
                $facilities_new  = $this->getSanParam('facility_id');
                $facilities_new = $reports->removeSelected($facilities_new);
                $facility = array();
                if(isset($facilities_new) && is_array($facilities_new)){
                foreach($facilities_new as $fac){
                $facility_location = explode("_",$fac);
                $facility[] = $facility_location[3];
                }
                }
                $facility = $reports->formatSelection($facility);
                $training_type = $this->getSanParam('training_type');
                 $training_type = $reports->formatSelection($training_type);
                
                $training_type2 = $this->getSanParam('cumu');
                $training_type2 = $reports->formatSelection($training_type2); 
                $stock_out = $this->getSanParam('stock_out');
                
                 $preview = $this->getSanParam('go');
                $locations = array();
               
               // echo sizeof($state);
             // print_r($state);
                if($agrregate_method!="view_aggregate_part" && $agrregate_method!="view_part_names"){
                     if(!empty($localgovernment)){
                    
                foreach($localgovernment as $state){
                  $state_gen =   explode("_",$state);
                  $parent_id = $state_gen[0];
                $district = $state_gen[1];
                $location_id = $state_gen[2];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
                    
                $filter = "location_id";
                
               
}else if(!empty($state)){
 // echo 'hi';
    
    foreach($state as $states){
                  $state_gen =   explode("_",$states);
                  $parent_id = $state_gen[0];
                $location_id = $state_gen[1];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
    
                $filter = "parent_id";
}

else if(!empty($zone)){
    
    $filter = "geo_zone";
//print_r($locations); echo 'here inside ';exit;
//array_push($locations,$zone);
if(is_array($zone)){
//print_r($zonal);
foreach($zone as $zonal){

$locations[] = $zonal;

}
}else{
$locations[] = $zone;
}
}
else{
    
   unset($locations);
}
                }
                else{
                   
                   if(!empty($state)){
 // echo 'hi';
    
    foreach($state as $states){
                  $state_gen =   explode("_",$states);
                  $parent_id = $state_gen[0];
                $location_id = $state_gen[1];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
    
                $filter = "parent_id";
}
else if(!empty($zone)){
    $filter = "geo_zone";
//print_r($locations); echo 'here inside ';exit;
//array_push($locations,$zone);
if(is_array($zone)){
//print_r($zonal);
foreach($zone as $zonal){

$locations[] = $zonal;

}
}else{
$locations[] = $zone;
}
}
else{
    unset($locations);
}

                } 
            
              
$consump_loc = array();
if(isset($locations)){
     foreach($locations as $loc){
        $consump_loc[$loc] = ""; 
         
     }    
}
     
     if(!isset($locations) || empty($locations)){
         //$filter = "parent_id";
         $locations = $helper->getLocationTierIDs("1");
         $filter = "geo_zone";
     }
     
     //print_r($locations);exit;
                $messageAlert = "";
                $perLocation = "";
                
                $outSideLocationPart = "";
                $has_result = "";
                $agrreg = "";
                $fac_with_type = "";
                $total_participants = "";
                
                $list = implode(',', $locations);
                $trainingorganizer = $this->getSanParam('trainingorganizer');
                $trainingorganizer = $reports->formatSelection($trainingorganizer);
                $trainingOrgTemp = $trainingorganizer;
                $cadre = $this->getSanParam('cadre');
                $stock_out = $this->getSanParam('stock_out');
                
                $trainingEndDateStartValue = $this->getSanParam('trainingEndDateStart');
                $trainingEndDateEndValue = $this->getSanParam('trainingEndDateEnd');
                
                $trainingEndDatesStart = array();
                $trainingEndDatesEnd = array();
                
                $trainingEndDatesStart = explode("/",$trainingEndDateStartValue);
                $trainingEndDatesEnd = explode("/",$trainingEndDateEndValue);
                $start_day = "";
                $start_month = "";
                $start_year = "";
                
               
                if(isset($trainingEndDatesStart[0])){
                    $start_day = $this->check_length_add_one($trainingEndDatesStart[0]);
                }
                if(isset($trainingEndDatesStart[1])){
                    $start_month = $this->check_length_add_one($trainingEndDatesStart[1]);
                }
                if(isset($trainingEndDatesStart[2])){
                    $start_year = $trainingEndDatesStart[2];
                }
                
                
                $end_day = "";
                $end_month = "";
                $end_year = "";
                 if(isset($trainingEndDatesEnd[0])){
                   $end_day = $this->check_length_add_one($trainingEndDatesEnd[0]);
                }
                if(isset($trainingEndDatesEnd[1])){
                    $end_month = $this->check_length_add_one($trainingEndDatesEnd[1]);
                }
                if(isset($trainingEndDatesEnd[2])){
                    $end_year = $trainingEndDatesEnd[2];
                }
                
                
                
//                $end_day = $this->check_length_add_one($trainingEndDatesEnd[1]);
//                $end_month = $this->check_length_add_one($trainingEndDatesEnd[0]);
//                $end_year = $trainingEndDatesEnd[2];
               
                $dobStartValue = $this->getSanParam('dobStart');
                $dobEndValue = $this->getSanParam('dobEnd');
                
                $dobStartDatesValue = array();
                $dobEndDatesValue = array();
                
                $dobStartDatesValue = explode("/",$dobStartValue);
                $dobEndDatesValue = explode("/",$dobEndValue);
                
                $dob_start_day  = "";
                $dob_start_month = "";
                $dob_start_year = "";
                
                if(isset($dobStartDatesValue[0])){
                    $dob_start_day = $this->check_length_add_one($dobStartDatesValue[0]);
                }
                if(isset($dobStartDatesValue[1])){
                    $dob_start_month = $this->check_length_add_one($dobStartDatesValue[1]);
                }
                if(isset($dobStartDatesValue[2])){
                    $dob_start_year = $dobStartDatesValue[2];
                }
                
//                $dob_start_day = $this->check_length_add_one($dobStartDatesValue[1]);
//                $dob_start_month = $this->check_length_add_one($dobStartDatesValue[0]);
//                $dob_start_year = $dobStartDatesValue[2];
                $dob_end_day  = "";
                $dob_end_month = "";
                $dob_end_year = "";
                if(isset($dobEndDatesValue[0])){
                   $dob_end_day = $this->check_length_add_one($dobEndDatesValue[0]);
                }
                if(isset($dobEndDatesValue[1])){
                    $dob_end_month = $this->check_length_add_one($dobEndDatesValue[1]);
                }
                if(isset($dobEndDatesValue[2])){
                    $dob_end_year = $dobEndDatesValue[2];
                }
                
//                $dob_end_day = $this->check_length_add_one($dobEndDatesValue[1]);
//                $dob_end_month = $this->check_length_add_one($dobEndDatesValue[0]);
//                $dob_end_year = $dobEndDatesValue[2];
               
                
                $period = $this->getSanParam('period');
                $certification = $this->getSanParam('certification');
                $gender = $this->getSanParam('gender');
               
                $consumption = $this->getSanParam('consumption');
                
                $current_month = date('m');
                $current_day = date('d');
                $current_year = date('Y');
                 //echo $agrregate_method;exit;
                 if($start_year==""){
                        $start_year = "2011";
                    }
      if($start_day==""){
          $start_day = "01";
      }
      if($start_month==""){
          $start_month = "01";
      }
      if($end_year==""){
                        $end_year = $current_year;
                    }
      if($end_day==""){
          $end_day = $current_day;
      }
      if($end_month==""){
          $end_month = $current_month;
      }
      
      
      $dob_start_date = $dob_start_year.'-'.$dob_start_month.'-'. $dob_start_day ;
                $dob_end_date = $dob_end_year.'-'.$dob_end_month.'-'.$dob_end_day;
                
                
      $fac_types = array();
       $start_date = $start_year.'-'.$start_month.'-'.$start_day;
      $end_date = $end_year.'-'.$end_month.'-'.$end_day;
      $error = array();
  // print_r($locations);
       if($agrregate_method=="" && $preview!="" ){
                    $error[] = "Please select a viewing category";
                }
  if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND birthdate>='".$dob_start_date."' AND birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
              $implodedLocations = "";
              if(isset($locations)){
       $implodedLocations = implode(",",$locations);
              }
              
              $locationQuery = "";
                      if(!empty($implodedLocations)){
                          $locationQuery = "AND (loc.parent_id IN($implodedLocations) OR loc.id IN ($implodedLocations))";
                      }
              //echo $dob_query;exit;
   // echo 'The size of the locations is what you will see now '.$sizeof($locations);
              $has_result = "false";
              $agrreg = 0;
              $total_participants = 0;
              $total_trainings = 0;
              $fac_with_type = "";
              $headers = "";
              
              
    if($agrregate_method=="view_individual_train"){
               if(!empty($trainingorganizer)){
                  $training_organizer = implode(",",$trainingorganizer);
               }else{
                   $training_organizer = "";
               }
               
               if(!empty($training_type)){
                  $trainingtypes = implode(",",$training_type);
               }else{
                   $trainingtypes = "";
               }
               
              
                 //$trainingtypes = implode(",",$training_type);
           
              
              if($training_organizer=="" || empty($trainingorganizer)){
                  $trainingorganizerquery=="";
              }else{
                  $training_organizer = implode(",",$trainingorganizer);
                  $trainingorganizerquery = "AND training_organizer_option_id IN (".$training_organizer.")";
              }
             
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND birthdate>='".$dob_start_date."' AND birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
              if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND facility_id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
            
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND training_title_option_id IN (".$trainingtypes.")";
              }
              
              if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND person.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
             // echo $start_date;exit;
              $records = $this->get_training_records_from_db( $trainingorganizerquery,$trainingtypequery,$start_date,$end_date,$locationQuery);
             $start_month_name = $this->return_the_month_name($start_month);
                    $start_month_name = substr($start_month_name,0,3);
               $end_month_name = $this->return_the_month_name($end_month);
                    $end_month_name = substr($end_month_name,0,3);
                    $word = "Training $start_day $start_month_name $start_year to $end_day $end_month_name $end_year";
                    
              $headers = array("ID",$word,"Training Partner","Start Date","End Date","Participants");
               $output = array();
              foreach($records as $record){
                  $data_collector  = array();
                 $training_id = $record['id'];
                 $link = "<a href='../training/view/id/$training_id'>$training_id</a>";
                 $trainingtitle = $record['training_title_phrase'];
                 $training_organizer = $record['training_organizer_phrase'];
                 $trainStartDate = strtotime($record['training_start_date']);
                 $training_start_date = date("d-m-Y",$trainStartDate);
                 $trainEndDate = strtotime($record['training_end_date']);
                 $training_end_date = date("d-m-Y",$trainEndDate);
                 $participants = $this->get_counter_person_trainings($training_id,$facilityq,$pstatusq);
                 $data_collector = array($link,$trainingtitle,$training_organizer,$training_start_date,$training_end_date,$participants);
             
                 array_push($output,$data_collector);
             }
             $total_trainings = sizeof($records);
          }
else if($agrregate_method=="view_aggregate_train"){
              $start_month_name = $this->return_the_month_name($start_month);
                    $start_month_name = substr($start_month_name,0,3);
               $end_month_name = $this->return_the_month_name($end_month);
                    $end_month_name = substr($end_month_name,0,3);
                    
                    $total_trainings = 0;
                    if(empty($trainingorganizer)){
                        $trainingorganizer = $this->get_all_training_organizers();
                        $trainingorganizer[] = "";
                    }
                    if(empty($training_type) ){
                        $training_type = $this->get_all_training_titles();
                    }
                    $word = "Training $start_day $start_month_name $start_year to $end_day $end_month_name $end_year";
                $headers = array($word,"Training Type","Training Organizer");
                 $output = array();
                    foreach($trainingorganizer as $training_org){
                  foreach($training_type as $type){
                      
                      $data_collector = array();
                     $trainings =  $this->get_total_number_of_trainings($training_org,$type,$start_date,$end_date,$locationQuery);
                      if($trainings<=0 ){
                          continue;
                      }
                     $total_trainings = $trainings + $total_trainings;
                      $training_organizer = $this->get_training_organizer_phrase($training_org);
                      $training_title = $this->get_training_title_option($type);
                      $data_collector = array($trainings,$training_title,$training_organizer);
                      array_push($output,$data_collector);
                  }
              }
          }
  //print_r($locations); exit;
          
else if($agrregate_method=="view_aggregate_part"){
          
         $agrreg = true;
          
     if(!empty($locations) && $agrregate_method=="view_aggregate_part" ){
         //$sql  = 
      
      //echo 'hello na here i dey';exit;
              
     
      if($agrregate_method=="view_aggregate_part"){
          if(!isset($locations) || empty($locations)){
         $error[] = "Please select Geography";
         
         }
        
		if($trainingorganizer=="" || empty($trainingorganizer)){
		$trainingorganizer = array();
		//$training_organizer = implode(",",$trainingorganizer);
	        //$trainingorganizer[]  = "";
                $trainingorganizer[] = 0;
		$all_training_organizer = $this->select_all_from_training_organizer();
		foreach($all_training_organizer as $id_s){
		$trainingorganizer[] = $id_s['id'];
		}
                
		$training_organizer = implode(",",$trainingorganizer);
		}else{
		$training_organizer = implode(",",$trainingorganizer);
		}
		if($training_type=="" || empty($training_type)){
		$training_type = $training_type_not_set;
		}
        //print_r($locations);exit;
                //echo 'This is the filter '.$filter_location.'filter';exit;
         $size = sizeof($locations);
         $output = array();
         if($filter=="geo_zone"){
             $geog = "Zone";
         }else if($filter=="parent_id"){
             $geog = "State";
         }
         else if($filter=="location_id"){
             $geog = "Local Government";
         }else{
             $geog = "Zone";
         }
        // echo $filter;exit;
         $headers = array("Participants",$geog,"Training Type","Training Partner");
         $total_participants = 0;
          foreach($locations as $location_id){
              if($location_id!=""){
            // $location_id = $locations[$i];
             $location_name = $this->get_location_name($location_id);
              foreach($training_type as $trainingtype){
                  // echo $location_id.'<br/>';
                   $training_title_phrase = $this->get_training_title_option($trainingtype);
				  
                  foreach($trainingorganizer as $training_org){
                      $trainings = 0;
                      
                      $trainings =  $this->get_total_number_of_trainings($training_org,$trainingtype,$start_date,$end_date);
                      if($trainings<=0 ){
                          continue;
                      }
                      $training_org = addslashes($training_org);
		       $trainingorgq = "AND facility_summary_person_view.training_organizer_option_id = '".$training_org."'";
                      $data_collector = array();
                      $facility_ids = array();
//                     $all_facilities = $this->get_all_facilities_with_location_id($location_id,$trainingorgq);
//                    foreach($all_facilities as $fac_details){
//                     
//                      $facility_ids[$fac_details['facility_id']] = $fac_details['facility_id'];
//                      array_push($facility_ids,$fac_details['facility_id']);
//                      
//                      }
//                      $facility_ids = array_unique($facility_ids);
//                     $facility_ids = implode(',',$facility_ids);
                    //echo '';
                      $training_organizer = $training_org;
                 $trainingtypes = $trainingtype;
           if(empty($cadre)){
                  $cadrequery = "";
              }else{
                  $cadreImplode = implode(",",$cadre);
                  $cadrequery = "AND p.primary_qualification_option_id IN ($cadreImplode)";
              }
              //Helper2::jLog("THis is the training".$training_org);
               if(!empty($training_org) || $training_org=='0' || $training_org==0){
                  //$training_organizer = implode(",",$trainingorganizer);
                  $trainingorganizerquery = "AND t.training_organizer_option_id ='$training_org'";
              }
              else  if($training_organizer==""){
                  $trainingorganizerquery="";
              }else if(empty($trainingOrgTemp)){
                  $trainingorganizerquery="";
              }
              
              if($end_day=="" && $end_month=="" && $end_year==""){
                  $training_end_date  = "";
              }else{
                  $training_end_date = "AND t.training_end_date>='$start_date' AND t.training_end_date<='$end_date'";
              }
              //print_r($facility);exit;
             if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND flv.id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
              
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND p.birthdate>='".$dob_start_date."' AND p.birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
             // echo $facer;exit;
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND tto.id IN (".$trainingtypes.")";
              }
              if($certification==""){
                  $certificationq = "";
              }else{
                  $certificationq = " AND (ptt.certification='".$certification."' OR ptt.certification='".strtolower($certification)."') ";
              }
              if($gender==""){
                  $genderq = "";
              }
              else{
               $genderq = " AND p.gender='".$gender."'";   
              }
              if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND p.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
            $locator = $location_id;
              $locationquery = "(flv.geo_zone_id IN ($locator) OR flv.lga_id IN ($locator) OR flv.state_id IN ($locator))";
              $all_participants = $this->get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq,$dob_query,$pstatusq);
       
                     $participants = 0;
                    
                     $training_organizer_phrase = $this->get_training_organizer_phrase($training_org);
                    
                     $participants = sizeof($all_participants);
                     if($participants<=0){
                         continue;
                     }
                     $total_participants = $participants + $total_participants;
                     array_push($data_collector,$participants);
                     array_push($data_collector,$location_name);
                     array_push($data_collector,$training_title_phrase);
                     array_push($data_collector,$training_organizer_phrase);
                     //print_r($data_collector);
                     array_push($output,$data_collector);
                     }
                     
                      
                  }
          }
          //echo $location_id;
                  // echo $location_id;
              }
          }
     }
     else{
    //echo 'Na here we dey';exit;
    $error[] = "Please select a Geography";
}

}
else if($agrregate_method=="view_part_names"){
    $agrreg = false;
   
    $messageAlert = "Each row represents a single training for a health worker. Health workers with multiple trainings will appear multiple times.";
             $error = array();
           if(!isset($locations) || empty($locations)){
         $locations = $this->get_all_locations();
         }
              $headers = array("ID","First Name","Middle Name","Surname","State","LGA","Facility","Professional Qualification","Training Type","Training Organizer","Training Start Date","Training End Date","Gender","Certification","Status");
               
              if($trainingorganizer=="" || empty($trainingorganizer)){
		$trainingorganizer = array();
		//$training_organizer = implode(",",$trainingorganizer);
	        //$trainingorganizer[]  = "";
                $addempty = "OR t.training_organizer_option_id=''";
                $trainingorganizer[] = "0";
		$all_training_organizer = $this->select_all_from_training_organizer();
		foreach($all_training_organizer as $id_s){
		$trainingorganizer[] = $id_s['id'];
		}
                
		$training_organizer = implode(",",$trainingorganizer);
                
		}else{
                    $addempty = "";
		$training_organizer = implode(",",$trainingorganizer);
		}
              //$training_organizer = implode(",",$trainingorganizer);
               
               
               if($training_type=="" || empty($training_type)){
		$training_type = $training_type_not_set;
		}  
               $trainingtypes = implode(",",$training_type);
                 
              if(empty($cadre)){
                  $cadrequery = "";
              }else{
                  $cadreImplode = implode(",",$cadre);
                  $cadrequery = "AND p.primary_qualification_option_id IN ($cadreImplode)";
              }
              
              if($training_organizer==""){
                  $trainingorganizerquery="";
              }
              else if(empty($trainingOrgTemp)){
                  $trainingorganizerquery="";
              }else{
                  $training_organizer = implode(",",$trainingorganizer);
                  
                  $trainingorganizerquery = "AND (t.training_organizer_option_id IN (".$training_organizer.") ".$addempty." )";
              }
              
              if($end_day=="" && $end_month=="" && $end_year==""){
                  $training_end_date  = "";
              }else{
                  $training_end_date = "AND t.training_end_date>='$start_date' AND t.training_end_date<='$end_date'";
              }
              //print_r($facility);exit;
             if(!empty($facility)){
                  $facer = implode(",",$facility);
                  $facilityq = "AND flv.id IN (".$facer.")";
                  }
              else{
                  $facilityq = "";
              }
              
                if($dob_start_date!="" AND $dob_end_date!="" AND $dob_end_date!="--" AND $dob_start_date!="--"){
                  $dob_query =  "AND p.birthdate>='".$dob_start_date."' AND p.birthdate<='".$dob_end_date."'";
              }else{
                  $dob_query = "";
              }
             // echo $facer;exit;
              if($trainingtypes==""){
                  $trainingtypequery = "";
              }else{
                  
                  
                  $trainingtypequery = "AND tto.id IN (".$trainingtypes.")";
              }
              if($certification==""){
                  $certificationq = "";
              }else{
                  $certificationq = " AND (ptt.certification='".$certification."' OR ptt.certification='".strtolower($certification)."') ";
              }
              if($gender==""){
                  $genderq = "";
              }
              else{
               $genderq = " AND p.gender='".$gender."'";   
              }
              
              
              if(!empty($person_status_id)){
                 $personStatusList =  implode(",",$person_status_id);
                $pstatusq = "AND p.person_status_id IN ($personStatusList)" ; 
              }else{
                  $pstatusq = "";
              }
              $locator = implode(',',$locations);
              $locationquery = "(flv.geo_zone_id IN ($locator) OR flv.lga_id IN ($locator) OR flv.state_id IN ($locator))";
              $all_participants = $this->get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq,$dob_query,$pstatusq);
        //print_r($all_participants);exit;
              $output = array();
              $outSideLocationPart = 0;
              $perLocation = 0;
              $total_participants = sizeof($all_participants);
              //
              $helper2 = new Helper2();
             // Helper2::jLog(print_r($all_participants,true));
              $has_result = "false";
          foreach($all_participants as $persons){
              $has_result = "true";
              $data_collector = array();
              $id = $persons['person_id'];
              $Link = $id;
              $first_name = $persons['first_name'];
              $middle_name = $persons['middle_name'];
              $last_name = $persons['last_name'];
              $state = $persons['state'];
              $lga = $persons['lga'];
              $facility_name = $persons['facility_name'];
              $cadre = $persons['qualification_phrase'];
              $training_types = $persons['training_title_phrase'];
              $training_title_phrase = $persons['training_organizer_phrase'];
              $person_status_title =  $persons['person_status_title'];
//              $training_start_date = $persons['training_start_date'];
//              $training_end_date = $persons['training_end_date'];
              
              $trainStartDate = strtotime($persons['training_start_date']);
              $training_start_date = date("d-m-Y",$trainStartDate);
              $trainEndDate = strtotime($persons['training_end_date']);
              $training_end_date = date("d-m-Y",$trainEndDate);
              $certification = $persons['certification'];
              $genders = $persons['gender'];
              $location_id = $persons['location_id'];
              //Helper2::jLog($location_id.PHP_EOL);
              $perLocation = 0;
              $outSideLocationPart = 0;
              if(!empty($userLocations)){
                  if(!in_array($location_id,$userLocations)){
                      $outSideLocationPart = $outSideLocationPart + 1;
                      continue;
                  }else {
                  $perLocation = $perLocation + 1;
              }
              }
            
              
              $data_collector = array($Link,$first_name,$middle_name,$last_name,$state,$lga,$facility_name,$cadre,$training_types,$training_title_phrase,$training_start_date,$training_end_date,$genders,$certification,$person_status_title);
        // print_r($data_collector);echo '<br/><br/>';
              array_push($output,$data_collector);
              }
            
          }
 
          $locationShow = array();
         // echo 'participants in location '.$perLocation.' '.$locationName.' asides location '.$outSideLocationPart.' total participants '.$total_participants; 
             $locationShow['perLocation'] = $perLocation;
           //echo $locationName;exit;
             $locationShow['locationName'] = $locationName;
             $locationShow['outSideLocation'] = $outSideLocationPart;
             $locationShow['has_result'] = $has_result;
             $locationShow['agreggate_participants'] = $agrreg;
             $this->viewAssignEscaped('perLocation',$locationShow);

             
 $criteria['trainingEndDateStart'] = ($this->getSanParam('trainingEndDateStart')!="")?$trainingEndDateStartValue:$defaultTrainingEndDateStart;
 $criteria['trainingEndDateEnd'] = ($this->getSanParam('trainingEndDateEnd')!="")?$trainingEndDateEndValue:$defaultTrainingEndDateEnd;
 
 $criteria['dobStart'] = $dobStartValue;
 $criteria['dobEnd'] = $dobEndValue;
 

 $criteria['period'] = $period;

 $criteria['aggregate_method'] = $agrregate_method;
//$agrregate_method=="aggregate_unifacilities"
  $criteria['trainingorganizer'] = $this->getSanParam('trainingorganizer');
  $criteria['cadre'] = $this->getSanParam('cadre');;
  $criteria['consumption']  = $this->getSanParam('consumption');
  $criteria['facility'] = $facility ;
  $criteria['certification'] = $this->getSanParam('certification');
  $criteria['gender'] = $this->getSanParam('gender');
  $criteria['training_type'] = $this->getSanParam('training_type') ;
 $criteria['cumu'] = $this->getSanParam('cumu') ;
 $criteria['person_status_id'] = $this->getSanParam('person_status_id');
 
 if($agrregate_method=="view_part_names"){
                $criteria['province_id'] = $this->getSanParam('province_id');
                $criteria['district_id']  = $this->getSanParam('district_id');
                $criteria['region_c_id']  = $this->getSanParam('region_c_id');
                
                }else{
                $criteria['province_id'] = $this->getSanParam('aggreg_province_id');
                $criteria['district_id']  = $this->getSanParam('aggreg_district_id');
                $criteria['region_c_id']  = $this->getSanParam('aggreg_region_c_id'); 
                }
 
 $criteria['stock_out'] = $this->getSanParam('stock_out');
 $criteria['total_participants'] = $total_participants;
 $criteria['total_training'] = $total_trainings;
 $criteria['error'] = $error;
 $criteria['facility_id'] = $this->getSanParam('facility_id');
 $criteria['preview'] = $preview;

 
                $end_date = $end_year.'-'.$end_month.'-'.$end_day;
                $date_format = "yyyy-mm-dd";
       
$this->viewAssignEscaped ('alert',$messageAlert);
$this->viewAssignEscaped ( 'tocktack', $fac_with_type );
$this->viewAssignEscaped ('headers',$headers);
$this->viewAssignEscaped ('output',$output);
//$status->setStatusMessage($stat);
$this->viewAssignEscaped('criteria',$criteria);
	
}
public function infoAction(){
    $this->_countrySettings = array();
		$this->_countrySettings = System::getAll();

		$this->view->assign ( 'mode', 'search' );
                require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');
$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
$current_month = "03";
$current_year = date('Y');
$date_format = $current_year.'-'.$current_month.'-'.'01';
echo '<nav id="primary_nav_wrap">';
echo '<ul>';
$zones = $this->get_location_category_unique("zone");
foreach($zones as $zone){
    $zone_name = $zone['geo_zone'];
    $zone_id = $zone['geo_parent_id'];
   
    $facilities = $this->get_all_facilities_with_location("zone",$zone_id);
    $facility_ids = implode(",",$facilities);
    
   $report_rates = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
    echo '<li><a href=#"><span></span>'.$zone_name.'::'.$report_rates.'</a>';
    $states = $this->get_location_category_unique("state",$zone_id);
    echo '<ul>';
    foreach($states as $state){
        $state_name = $state['state'];
        $state_id = $state['state_id'];
        $facilities = $this->get_all_facilities_with_location("state",$state_id);
    $facility_ids = implode(",",$facilities);
    $report_rates_state = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
        echo '<li><a href="#"><span></span>'.$state_name.'::'.$report_rates_state.'</a>';
        echo '<ul>';
    
   
    $lgas = $this->get_location_category_unique("lgs",$state_id);
    foreach($lgas as $lga){
        $lga_name = $lga['lga'];
        $lga_id = $lga['lga_id'];
       $facilities = $this->get_all_facilities_with_location("lga",$lga_id);
    $facility_ids = implode(",",$facilities);
    $report_rates_lga = $this->get_all_facilities_reporte_rates($facility_ids,$date_format);
        echo '<li><a href="#"><span></span>'.$lga_name.'::'.$report_rates_lga;echo '</a></li>'; 
        
    }
    echo '</ul>';
      echo '</li>';  
    }
    echo '</ul>';
    echo '</li>';
}

echo '</ul>';
echo '</nav>';
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
    $result = $db->fetchAll($sql);
    return $result;
    
}
public function get_all_facilities_reporte_rates($facility_ids,$date_format){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    if(!empty($facility_ids)){
        $whereFac = "AND facility_id IN (".$facility_ids.")"; 
    }else{
       $whereFac = ""; 
    }
    $sql = "SELECT COUNT(*) as counter FROM facility_report_rate WHERE date='$date_format' $whereFac";
    
    $result = $db->fetchAll($sql);
    //print_r($result);exit;
    return $result[0]['counter'];
    
}
public function get_all_facilities_with_location($category,$id){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    if($category=="zone"){
        $needle = "geo_parent_id";
    }else if($category=="state"){
        $needle = "state_id";
    }else{
        $needle = "lga_id";
    }
    
    $sql  = "SELECT id FROM facility_location_view WHERE `$needle`='$id'";
    $result = $db->fetchAll($sql);
    $facilities = array();
    foreach($result as $facility){
        $facility_id = $facility['id'];
        $facilities[] = $facility_id;
    }
    return $facilities;
}
//TP: this is for the all queries section of the code
public function allqueriesAction() {
		$this->_countrySettings = array();
		$this->_countrySettings = System::getAll();
                $reports = new Report();
		$this->view->assign ( 'mode', 'search' );

		require_once ('models/table/TrainingLocation.php');
		require_once('views/helpers/TrainingViewHelper.php');

		$criteria = array ();
		$output = array();
		$where = array ();
		$display_training_partner = ( isset($this->_countrySettings
['display_training_partner']) && $this->_countrySettings['display_training_partner'] == 1 ) 
? true : false;

		//find the first date in the database
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$qualificationsArray = OptionList::suggestionList ( 
'person_qualification_option', array('qualification_phrase','id'), false, 30 );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
                $sql = "SELECT COUNT(*) FROM facility";
                $rowArray = $db->fetchAll($sql);
                $number = $rowArray['COUNT(*)'];
		//facilities list
		$csql = "SELECT id,facility_name as name,location_id as parent_id,tier FROM facility_location_view ORDER BY facility_name ASC";
                $facilitiesArray = $db->fetchAll($csql);
                $size = sizeof($facilitiesArray);
                for($v=0; $v<$size; $v++){
                    $facilitiesArray[$v]['tier'] = 4;
                    $facilitiesArray[$v]['is_default'] = 0;
                    $facilitiesArray[$v]['is_good'] = 1;
                }
                //print_r($facilitiesArray);exit;
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
                //exit;
              
               
               
                $locations = Location::getAll("2");
                for($v=0; $v<$size; $v++){
                    $id = $facilitiesArray[$v]['id'];
                    //$id = 2592 + $v;
                    $locations[$id] = $facilitiesArray[$v];
                }
                //print_r($locations);
                foreach($locations as $location){
                  // print_r($location);echo '<br/><br/>';
                    
                }
               //exit;
               //print_r($locations); exit;
		$this->viewAssignEscaped('locations', $locations);
		//course
                //print_r($locations);exit;
                //exit;
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
		$tsql = "SELECT * FROM `training_title_option` ORDER BY `training_title_phrase` ASC";
                
                $trainingtypes = $db->fetchAll($tsql);
                $this->viewAssignEscaped ( 'trainingtypes', $trainingtypes );
                $trainingtypes_unset = array();
				 foreach($trainingtypes as $item){
                    $trainingtypes_unset[] = $item['id'];
                }
                 $tosql = "SELECT * FROM `training_organizer_option` ORDER BY `training_organizer_phrase` ASC";
                
                $trainingorg = $db->fetchAll($tosql);
                $training_org_unset = array();
                foreach($trainingorg as $item){
                    $training_org_unset[] = $item['id'];
                }
                $this->viewAssignEscaped ( 'trainingorganizer', $trainingorg );
                
                $csql = "SELECT * FROM commodity_name_option ORDER BY commodity_name ASC";
                $commodity_namedb = $db->fetchAll($csql);
                $this->viewAssignEscaped ( 'commodity_name_option', $commodity_namedb );
                
                //$locations = Location::getAll("2");
		//$this->viewAssignEscaped('locations', $locations);
		//course
		$courseArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $courseArray );
		//location
		// location drop-down
		$tlocations = TrainingLocation::selectAllLocations ($this->setting
('num_location_tiers'));
		$this->viewAssignEscaped ( 'tlocations', $tlocations );
		
                
                
                
                //-----------------------------------------the query is here now.----------------------------------------------//
                $zone = $this->getSanParam('province_id');
                $state  = $this->getSanParam('district_id');
                $localgovernment  = $this->getSanParam('region_c_id');
                //$facility  = $this->getSanParam('facility');
				 $facilities_new  = $this->getSanParam('facility_id');
                //var_dump($facilities_new);exit;
				$facility = array();
				
               foreach($facilities_new as $facesss){
                $facility_location = explode("_",$facesss);
                $facility[] = $facility_location[3];
                }
                $facilitators = implode(",",$facility);
                
                //$facility_location = explode("_",$facilities_new);
                //$facility[] = $facility_location[3];
				
				
                $training_type = $this->getSanParam('training_type');
				if(empty($training_type)){
				$training_type = $trainingtypes_unset;
				}
				//print_r($trainingtypes_unset);exit;
                $training_type2 = $this->getSanParam('cumu');
                $stock_out = $this->getSanParam('stock_out');
                $providing = $this->getSanParam('providing');
				$submit = $this->getSanParam('go');
                $locations = array();
                
               // echo sizeof($state);
             // print_r($state);
               
                if(!empty($localgovernment)){
                foreach($localgovernment as $state){
                  $state_gen =   explode("_",$state);
                  $parent_id = $state_gen[0];
                $district = $state_gen[1];
                $location_id = $state_gen[2];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
                $filter = "location_id";
                //echo 'na here i dey';
}else if(!empty($state)){
 // echo 'hi';
    foreach($state as $states){
                  $state_gen =   explode("_",$states);
                  $parent_id = $state_gen[0];
                $location_id = $state_gen[1];
                if($location_id!=""){
                array_push($locations,$location_id);
                }
                }
                 
                $filter = "parent_id";
}

else{
   unset($locations);
}

$consump_loc = array();
     foreach($locations as $loc){
        $consump_loc[$loc] = ""; 
         
     }    
  // print_r($locations);
   // echo 'The size of the locations is what you will see now '.$sizeof($locations);
  //print_r($locations); exit;
     
        
        $error = array();
    
                $list = implode(',', $locations);
                $trainingorganizer = $this->getSanParam('trainingorganizer');
                $cadre = $this->getSanParam('cadre');
                $stock_out = $this->getSanParam('stock_out');
                $start_day = $this->check_length_add_one($this->getSanParam('start-day'));
                $start_month = $this->check_length_add_one($this->getSanParam('start-month'));
                $start_year = $this->getSanParam('start-year');
                $show = $this->getSanParam('show');
                if($trainingorganizer=="" || empty($trainingorganizer)){
                    $trainingorganizer = $training_org_unset;
                }
                
                $end_day = $this->check_length_add_one($this->getSanParam('end-day'));
                $end_month = $this->check_length_add_one($this->getSanParam('end-month'));
                $end_year = $this->getSanParam('end-year');
                $agrregate_method = $this->getSanParam('aggregate_method');
                $period = $this->getSanParam('period');
                $consumption = $this->getSanParam('consumption');
                //$time = time();
				
                $current_month = date('m');
                $current_day = date('d');
                $current_year = date('Y');
                 //echo $agrregate_method;exit;
                 if($start_year==""){
                        $start_year = "2013";
                    }
      if($start_day==""){
          $start_day = "01";
      }
      if($start_month==""){
          $start_month = "01";
      }
      if($end_year==""){
                        $end_year = $current_year;
                    }
      if($end_day==""){
          $end_day = $current_day;
      }
      if($end_month==""){
          $end_month = $current_month;
      }
       if($training_type2=="Cumulative"  && $start_year==""){
                       $start_day = "01";
                       $start_year = "1910";
                       $start_month = "01";
                    }
      $fac_types = array();
       $start_date = $start_year.'-'.$start_month.'-'.$start_day;
      $end_date = $end_year.'-'.$end_month.'-'.$end_day;
      //$facilities = array();
      //$training_org = array();
	  
       //$training_org = $this->get_all_facilities_with_partner($trainingorganizer);
                   // print_r($training_org); exit;
				   if(isset($submit) && $submit!=""){
                                       if($period=="" || empty($period)){
	  $error[] = "You need to select the period";
	  }
          
                  else  if(!isset($locations) || empty($locations)){
         $error[] = "Geography is required (at least state)";
         }else {
             if($period=="annual"){
                 
                           list($starts_years,$ends_years) = $reports->getAnnualDateRange($start_year,$end_year);
             }
             else if($period=="monthly"){
                 echo $start_year; echo $end_year;
                 list($starts_years,$ends_years) = $reports->getMonthlyDateRange($start_month,$start_year,$end_month,$end_year);
             }
             else if($period=="total"){
                 $starts_years[] = $start_date;
                 $ends_years[] = $end_date;
             }
                if($agrregate_method=="aggregate_unifacilities"){
                    
                    
                    
                    $location_id = $locations[0];
                   
                    if($trainingorganizer || empty($trainingorganizer) ){
					$trainingorgq = "";
                        $trainingorganizer[] = "1";
                    }else{
					$trainingorganizer = implode(",",$trainingorganizer);
					$trainingorgq = "AND training_organizer_option_id IN (".$trainingorganizer.")";
					}
                    $trainingorganizer = implode(",",$trainingorganizer);
                   $small_value = $this->get_name_category($training_type);
                          
                          // $small_value = $providing;
                          //$all_name_ids = $this->get_the_name_ids($small_value);
                        // $all_name_ids = implode(",",$all_name_ids);
                    //$training_org = implode(',',$training_org);
                  // echo 'The location is '.$location_id;
                    //echo $trainingorganizer.'<br/><br/>';
					//print_r($facilities_new);exit;
					if(empty($facility)){
					$facesss = "";
                    $all_facilities_array = $this->all_facilities_id($location_id,$facesss);
                //  print_r($all_facilities_array);exit;
				  }else {
				  //echo 'Hello';exit;
				  $facesss = "AND id IN (".$facilitators.")";
				     $all_facilities_array = $this->all_facilities_id($location_id,$facesss);
				  //
				  }
				 // print_r($all_facilities_array);exit;
				  foreach($all_facilities_array as $facids){
				  $ids[] = $facids['id'];
				  }
				 // print_r($ids);
				// echo  implode(",",$ids);exit;
				  //print_r($all_facilities_array);exit;
				  // $phrase = $this->get_training_organizer_phrase($trainingorganizer);
                    $facility_names = array();
                    $facility_ids = array();
                     $output = array();
                    $headers = array();
                     $month_name = $this->return_the_month_name($start_month);
                    $name = substr($month_name,0,3);
                    $num_of_days = cal_days_in_month(CAL_GREGORIAN, $start_month, "2015");
                     array_push($headers,"");
                       
                    foreach($training_type as $ttype){
                     $value = strtoupper($this->get_name_category($ttype));
                     $word = "HWs trained in ".$value." as of $num_of_days-$name-$start_year";
                     array_push($headers,$word);
                    }
                     $month_name = $this->return_the_month_name($start_month);
                    
                     foreach($providing as $prov){
                     $word2 = "#facilities providing ".strtoupper($prov)." $month_name $start_year ";
                     array_push($headers,$word2);
                }
                     
                         
                         
                  foreach($all_facilities_array as $fac_details){
                     
                      $facility_ids[$fac_details['id']] = $fac_details['id'];
                      array_push($facility_ids,$fac_details['id']);
                      $facility_ids = array_unique($facility_ids);
                      $organizers[$fac_details['id']] = $this->get_training_organizer_phrase_with_facility($fac_details['id']); //$fac_details['training_organizer_phrase'];
                    $facility_names[$fac_details['id']] = $fac_details['facility_name'];
                      $facility_names = array_unique($facility_names);
                      
                       //echo 'Facility name is '.$fac_details['facility_name'].'----------';
                        //echo 'Facility id is '.$fac_details['facility_id'].'----------<br/><br/>';
                  }
                   
                  $hw_counts = array();
                    //print_r($facility_names);echo '<br/><br/>';
                     //print_r($facility_ids);
                    $agreg_fac = array();
                    $consumptions = array();
                    $name_idsum = array(array());
                    //$star_month_name = 
					
                  foreach($facility_ids as $facility_id ){
                      $end_date = $start_year."-".$start_month."-31";
                      $start_date = $start_year."-".$start_month."-01";
                    //  echo $date;
                      $hw_counter = array();
                       foreach($training_type as $ttype){
                      $hw_count = $this->get_hw_count_for_each_facility($start_date,$end_date,$facility_id,$small_value);
                      $hw_countering[$ttype][] = $hw_count;
                     // echo $hw_count .' '.$type;
                      $hw_counts[$facility_id][$ttype] = $hw_count;
                       }
                       //print_r($hw_counter);
                     
                      foreach($providing as $prov){
                          $small_value = $prov;
                           $all_name_ids = $this->get_the_name_ids($small_value);
                         $all_name_ids = implode(",",$all_name_ids);
                      $fac_prov = $this->get_just_customized_facility_id($all_name_ids,$start_date,$end_date,$location_id,$facility_id);
                       if($fac_prov<=0){
                           $answer = "No";
                       }else{
                           $answer = "Yes";
                       }  // array_push($data_collector,$fac_prov);
                       $agreg_fac[$facility_id][$prov] = $answer;
                       $facer[$prov][] = $answer;
                      }
                        $location = implode(',',$locations);
                       foreach($consumption as $name_id){
                       $consumptions[$facility_id][$name_id] = $this->get_consumption_for_unique_fac($start_date,$end_date,$facility_id,$name_id,$location);
//get_consumption_sum_for_them( )                   
					$name_of_consump = $this->get_commodity_option_name($name_id);
                      //$word_consume = " Consumption of $name_of_consump $month_name $start_year";
                      //array_push($headers,$word_consume);
                      //$name_idsum[$name_id][$name_id] = array();
                       }
                       $name_of_consump = $this->get_commodity_option_name($name_id);
                  }
                     
                  foreach($consumption as $name_id){
                       //$consumptions[$facility_id][$name_id] = $this->get_consumption_for_unique_fac($start_date,$end_date,$facility_id,$name_id);
                      $name_of_consump = $this->get_commodity_option_name($name_id);
                      $word_consume = " Consumption of $name_of_consump $month_name $start_year";
                      array_push($headers,$word_consume);
                      //$name_idsum[$name_id][$name_id] = array();
                       }
                       
                  array_push($headers,"Training Organizer");
                  foreach($providing as $prov){
                 $value_count =  $this->arraycount( $facer[$prov], "Yes");
                 if($value_count==0){
                     $percent = "0%";
                 }
                 else{
                     $caculator = ($value_count/(sizeof($facer[$prov]))) * 100;
                     $percent = round($caculator,2)."%";
                 }
                 $precent[$prov] = $percent;
                  }
                  $hw_sum = array();
                  foreach($training_type as $ttype){
                      
                 $hw_sum[$ttype] = array_sum($hw_countering[$ttype]);
                
                  }
				 
                 // print_r($hw_sum);echo'<br/><br/>';
                   //print_r($hw_countering);
                  //exit;
                 //echo 'THis is the percent '.$percent.'<br/><br/>';
                 //echo 'The sum is '.$hw_sum;
                
                 //print_r($consumptions);
                //echo '<br/><br/>';
                // print_r($hw_counts);
                 //echo '<br/><br/>';
                 //print_r($agreg_fac);
                // echo '<br/><br/>';
                // print_r($headers);
                //print_r($training_type);exit;
                 foreach($facility_ids as $facility_id){
                     $data_collector = array();
                     $facility_name = $facility_names[$facility_id];
                     array_push($data_collector,$facility_name);
                      foreach($training_type as $ttype){
                          
                     $hw_counter = $hw_counts[$facility_id][$ttype];
                      array_push($data_collector,$hw_counter);
                      }
                      
                        foreach($providing as $prov){
                            
                     $aggreg_facilities = $agreg_fac[$facility_id][$prov];
                       array_push($data_collector,$aggreg_facilities);
                        }
                     foreach($consumption as $name_id){
                         
                         $name_ids = $consumptions[$facility_id][$name_id];
                        $name_idsum[$name_id][] = $name_ids;
                          array_push($data_collector,$name_ids);
                     }
                     $phrase = $organizers[$facility_id];
                     array_push($data_collector,$phrase);
                      //print_r($data_collector);
                   array_push($output,$data_collector);  
                  
                 }
                 //print_r($output);
                 //echo 'THis is the exit point</br>';
                 //foreach($name_idsum as $name){
                     // print_r($name);
                 //}
                //exit;
                 //print_r($name_idsum);
                 $data_collectors = array();
                 array_push($data_collectors,"Total");
                 
                 foreach($training_type as $ttype){
                      
                 //$hw_sum[$ttype] = array_sum($hw_counts[$ttype]);
                  array_push($data_collectors,$hw_sum[$ttype]);
				 // $total_total[] = $hw_sum[$ttype];
                  }
                
                 foreach($providing as $prov){
				// $total_total[] = $percent[$prov];
                 array_push($data_collectors,$precent[$prov]);
                 }
                   foreach($consumption as $name_id){
                     $sum_condition = 0;
                    // echo '<br/> Here I am ';
                    //print_r( $name_idsum[$name_id]);exit;
                  $sum_condition =  array_sum($name_idsum[$name_id]);
                    //echo '<br/>';exit;
					//$total_total[] = $sum_condition;
                    array_push($data_collectors,$sum_condition);
                 }
				 $final_total = array();
                 array_push($data_collectors,"");
				 $final_total = $data_collectors;
                 //array_push($output,$data_collectors);
                  //foreach
                  
                   //echo '<br/><br/><br/>';
                  // print_r($hw_counts);exit;
     //exit;
                  
                           
                   // get_just_fac_with_type($all_name_ids,$beginning,$end,$location_id)
                   
                    
                  
                }
				else if($agrregate_method=="aggregate_facilities"){
                          
                    
                
                        $headers = array();
                           array_push($headers,"Geography");
                           if($period=="annual"){
                  array_push($headers,"Year");
                           }else if($period=="monthly"){
                               array_push($headers,"Month");
                           }else if($period=="total"){
                               array_push($headers,"Total");
                           }
                          
                          if($stock_out!=""){
                              $stockq = "AND commodity.stock_out='Y' AND commodity.id='".$stock_out."'";
                          }else{
                              $stockq = "";
                          }
						  if(empty($facility)){
					$facesss = "";
                    
				  }else {
				  //echo 'Hello';exit;
				  $facesss = "AND facility_id IN (".$facilitators.")";
				     
				  }
                           if($training_type!=""){
                           $sums = array();
                           $all_name_ids  = array();
                           $small_value = $this->get_name_category($training_type);
                          
                           $small_value = (($small_value=="")?"fp":$small_value);
                          $all_name_ids = $this->get_the_name_ids($small_value);
                         $all_name_ids = implode(",",$all_name_ids);
                           $value = strtoupper($this->get_name_category($training_type));
                           foreach($providing as $prov){
                           $word = "# facilities Providing ".strtoupper($prov);
                           array_push($headers,$word);
                           }
                           
                           
                               if($training_type2=="trained_in"){
                                   $train_status = "Trained in";
                               }else{
                                  $train_status = "Cumulative"; 
                               }
                               foreach($training_type as $ttype){
                                   $value = strtoupper($this->get_name_category($ttype));
                                //$words = "# facilities with a HW ".$train_status.' '.$value.' in '.$end_year;
                               if($training_type2=="Cumulative"){
                                  $words = "Cumulative #facilities with a HW trained in ".$value;  
                               }else{
                                  $words = "# facilities with a HW trained in " .$value;  
                               }
                               array_push($headers,$words);
                               }
                               if(empty($consumption) || !isset($consumption)){
                                  $error[] = "Select the consumption type";
                               }
                               
                               
                               foreach($consumption as $values){
                                   
                                   $consump_word = "Consumption of ".$this->get_commodity_option_name($values);
                                  
                                   array_push($headers,$consump_word);
                                  
                               }
                               //print_r($headers); exit;
                               $result = array();
                               $hw_trained = array();
                        foreach($locations as $location){
                            $lenth =  sizeof($starts_years);
                       for($i=0; $i<$lenth; $i++){
                           $beginning = $starts_years[$i];
                           $end = $ends_years[$i];
                           //$sql = "SELECT * FROM commodity left join facility_location_view on commodity.facility_id = facility_location_view.id  WHERE (commodity.date>='".$beginning."' AND commodity.date<='".$end."') AND (facility_location_view.parent_id IN (".$list.") OR facility_location_view.location_id IN (".$list."))";
                     foreach($training_type as $ttype){
                           $small_value = $this->get_name_category($ttype);
                           $hw_trained[$location][$i][$ttype] = $this->get_facility_with_hw_trained_in($small_value,$beginning,$end,$location,$training_type2,$stockq,$facesss);
                     }  
                      $all_facilities_id = $this->all_facilities_id($location,$facesss);
					  $all_fac_ids = array();
					  foreach($all_facilities_id as $facids){
					  $all_fac_ids[] = $facids['id'];
					  }
					  
                      foreach($consumption as $values){
                               
                               $sum = $this->get_consumption_sum_for_them($beginning,$end,$values,$location,$facesss);
                               //$value = strtoupper($this->get_name_category($training_type));
                               $value  = $this->get_commodity_option_name($values);
                               $consump_loc[$value][$location.$i] = $sum;
                               array_push($sums,$sum);
                              // array_push($headers,$consump_word);
                              
                               
                           }
                           
                           
                       }
                    }
                    
                  //array_push($headers,"Geography");
                  //array_push($headers,"Year");
                  
                  $results = array();
                    $data_collector = array();
                    $lent = sizeof($locations);
                   // $wise_word = "The length of the array is this ".$lent;
                     for($l=0;$l<$lent; $l++){
                         $location = $locations[$l];
                    $lenth =  sizeof($starts_years);
                       for($i=0; $i<$lenth; $i++){
                           $beginning = $starts_years[$i];
                           $end = $ends_years[$i];
                           $explode = explode("-",$end);
                           $get_word = "";
                           $clear = $explode[1];
                           $year = $explode[0];
                           $data_collector = array(); 
                           
                           $location_name = $this->get_location_name($location);
                           array_push($data_collector,$location_name);
                           if($period=="monthly" || $period=="annual"){
                           if($period=="monthly"){
                               
                               $get_word = $this->return_the_month_name($clear);
                               
                           }
                           $year = $get_word." ".$year;
                          
                           }else{
                               $year = $start_date."- to ".$end_date;
                           }
                            array_push($data_collector,$year);
                           foreach($providing as $prov){
                               $all_name_ids = $this->get_the_name_ids($prov);
                               $all_name_ids = implode(",",$all_name_ids);
                          $fac_prov[$prov] = $this->get_just_fac_with_type($all_name_ids,$beginning,$end,$location,$stockq,$facesss);
                           }
                          // $fac_prov = 
                          //echo 'THis is t'.
                           foreach($providing as $prov){
                           array_push($data_collector, $fac_prov[$prov]);
                           }
                            foreach($training_type as $ttype){
                                if($training_type2=="Cumulative"){
                           $fachw += $hw_trained[$location][$i][$ttype];
                                }else{
                               $fachw = $hw_trained[$location][$i][$ttype];     
                                }
                           array_push($data_collector,$fachw);
                            }
                           foreach($consumption as $values){
                              $value  = $this->get_commodity_option_name($values);
                              $sum = $consump_loc[$value][$location.$i];
                               $sum_values = (($sum=="")? 0 : $sum);
                               array_push($data_collector,$sum_values);
                           }
                           array_push($output,$data_collector);
                        //   array_push($results,$data_collector);
                          // array_push($results,$wise_word.''.$l);
                           }
                          
                       }
                       
                    }else{
                       // echo 'Na here i dey ';exit;
                        $error[] = "Training Type is required";
                    }
                    }
                    
                      //array_push($results,$all_name_ids);      
                       
                    
                                   
                    
                    else if($agrregate_method=="agrregate_data"){
                        //echo 'hello';exit;
                         if(!isset($locations) || empty($locations)){
         $locations = $this->get_all_locations();
         }
                        $headers = array();
                        $output = array();
                        
                         if($stock_out!=""){
                              $stockq = "AND commodity.stock_out='Y' AND commodity.id='".$stock_out."'";
                          }else{
                              $stockq = "";
                          }
                          if($cadre!=""){
                              $cadreq = "AND primary_qualification_option_id='".$cadre."'";
                          }else{
                              $cadreq = "";
                          }
						   if(empty($facility)){
					$facesss = "";
                    
				  }else {
				  //echo 'Hello';exit;
				    $facesss = "AND facility_id IN (".$facilitators.")";
				     
				  }
                            array_push($headers,"Geography");
                           
                             $beginning= $start_date;
                    $end = $end_date;
                  // echo $beginning.' '.$end;exit;
                        //$small_value = $this->get_name_category($training_type);
                          $consump = array();
                          // $small_value = (($small_value=="")?"fp":$small_value);
                           $hw_result = array();
                            foreach($training_type as $ttype){
                                $title = $this->get_name_category($ttype,"training_title_phrase");
                             
							   $search = strpos($title," ");
                                if($search===false){
                                         $title = strtoupper($title);
                                          }else{
                                                                                                                                            
                                           $title = ucwords(strtolower($title));
                                           $titles = explode(" ",$title);
                                           if(strtolower($titles[0])=="fp"  || strtolower($titles[0]=="nxt")){
                                           $titles[0] = strtoupper($titles[0]);
                                            }
                                           $title = implode(" ",$titles);
                                            }
                            array_push($headers,"#HWs trained in ".$title."");
                            }
							 
							// 
                            foreach($consumption as $values){
                              // var_dump($title);exit;
							 
                              $value  = $this->get_commodity_option_name($values);
							 
                              //$sum = $this->get_consumption_sum_for_them($beginning,$end,$values,$location_id,$stockq);
                              $display_start_month = substr($this->return_the_month_name($start_month),0,3);
                              $display_end_month = substr($this->return_the_month_name($end_month),0,3);
                              $word = "Consumption of ".$value." ".$start_day."-".$display_start_month."-".$start_year." to ".$end_day."-".$display_end_month."-".$end_year."";
                              array_push($headers,$word);
                           }
						   
                        foreach($locations as $location_id){
                           // print_r($locations);exit;
                            if($location_id!="" && $location_id!=" "){
                                
                            $data_manager = array();
							//print_r($location_id);
							//print_r($consumption);exit;
                            $location_name = $this->get_location_name($location_id);
							 
                            array_push($data_manager,$location_name);
                            foreach($training_type as $ttype){
                                $small_value = $this->get_name_category($ttype);
                           $sql = "SELECT count(*) FROM `facility_summary_person_view` WHERE training_title_option_id='".$ttype."' AND  training_start_date>='".$beginning."' AND training_end_date<='".$end."' ".$facesss." AND (facility_location_id='".$location_id."' OR parent_id='".$location_id."')  ".$cadreq."";
                         //echo $sql;echo '<br/><br/>';continue;
                          // echo $sql;exit;
                           $result = $db->fetchAll($sql);
                          array_push($hw_result, $result[0]['count(*)']);
                         array_push($data_manager,$result[0]['count(*)']);
                            }
                            foreach($consumption as $values){
                               
                              $value  = $this->get_commodity_option_name($values);
                              $sum = $this->get_consumption_sum_for_them($beginning,$end,$values,$location_id,$stockq,$facesss);
                                                  
                               $sum_values = (($sum=="")? 0 : $sum);
                              $consump[$location_id][$value] = $sum_values;
                              array_push($data_manager,$sum_values); 
                           }
                         
                          
                          // echo '<br/><br/><br/>';
                           array_push($output,$data_manager);
                        }
                        }
                       
                   
                    
                        
                    }
     
                  }
                                   }
				  
                  //  array_push($results,$ends_years);
                    //array_push($results,$starts_years);
                  //array_push($headers,"Geography");
                  //array_p/ush($headers,"Year");
                                          
                       
                        
                   
                    
//                  
   
$criteria['start_day'] = ($this->getSanParam('start-day')!="")?$this->check_length_add_one($this->getSanParam('start-day')):$start_day;
$criteria['start_month'] = ($this->getSanParam('start-month')!="")?$this->check_length_add_one($this->getSanParam('start-month')):$start_month;
 $criteria['start_year'] = ($this->getSanParam('start-year')!="")?$this->getSanParam('start-year'):$start_year;
               // echo $start_day;
             
                
 $criteria['end_day'] = ($this->getSanParam('end-day')!="")?$this->check_length_add_one($this->getSanParam('end-day')):$end_day;
$criteria['end_month'] = ($this->getSanParam('end-month')!="")?$this->check_length_add_one($this->getSanParam('end-month')):$end_month;
  $criteria['end_year'] = ($this->getSanParam('end-year')!="")?$this->getSanParam('end-year'):$end_year;
 
 $criteria['period'] = $period;

 $criteria['aggregate_method'] = $agrregate_method;

//$agrregate_method=="aggregate_unifacilities"
 $criteria['trainingorganizer'] = $trainingorganizer;
 if(!is_array($trainingorganizer)){
     $criteria['trainingorganizer'] = explode(",",$trainingorganizer);
 }
  $criteria['trainingorganizer'] = $this->getSanParam('trainingorganizer');
  
  //print_r($criteria['trainingorganizer']);exit;
  $criteria['cadre'] = $this->getSanParam('cadre');
  
  $criteria['consumption']  = $this->getSanParam('consumption');
  $criteria['province_id'] = $this->getSanParam('province_id');
 $criteria['district_id'] = $this->getSanParam('district_id');
 $criteria['region_c_id'] = $this->getSanParam('region_c_id');
                //$facility  = $this->getSanParam('facility');
 $criteria['facility_id'] = $this->getSanParam('facility_id');
  $criteria['training_type'] = $this->getSanParam('training_type') ;
 $criteria['cumu'] = $this->getSanParam('cumu') ;
 $criteria['show'] = $show;
 $criteria['final_total'] = $final_total;
  //$criteria['province_id'] = $zone;
 //$criteria['district_id'] = $state;
 //$criteria['region_c_id'] = $localgovernment;
 $criteria['stock_out'] = $this->getSanParam('stock_out');
  $criteria['error'] = $error;
  $criteria['providing'] = $this->getSanParam('providing');
 //$criteria[''] = 
               // $results = array();
                $end_date = $end_year.'-'.$end_month.'-'.$end_day;
                $date_format = "yyyy-mm-dd";
                //array_push($results,$fac_types);
                //array_push($results,$locations);
               // $sql = "SELECT facility.id,facility.facility_name,location.location_name,location.parent_id FROM facility left join location on   facility.location_id = location.id";

              //  $results = $db->fetchAll($sql);
//$output = array("john"=>array("doe","ojetunde","one","ojetunde","one","one","ojetunde","one","ojetunde"),"doe","ojetunde","one","ojetunde","one","ojetunde","one","ojetunde");
//array_push($results,$locations);
//array_push($results,$location);
//array_push($results,$beginning);
//array_push($results,end_date);

if(!empty($error)){
$output = array();
}
$this->viewAssignEscaped ('headers',$headers);

$this->viewAssignEscaped ('output',$output);
//$status->setStatusMessage($stat);
$this->viewAssignEscaped('criteria',$criteria);
	}
       public function get_consumption_sum_for_them($beginning,$end,$values,$location="",$stock_out="",$facesss=""){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $consump_sum_sql = "SELECT SUM(consumption) as sums  FROM commodity left join facility_location_view on commodity.facility_id = facility_location_view.id  WHERE (commodity.date>='".$beginning."' AND commodity.date<='".$end."') ".$facesss." ".$stock_out." AND (commodity.name_id = '".$values."') AND (facility_location_view.parent_id = ".$location." OR facility_location_view.location_id  = ".$location.")";
          //echo $consump_sum_sql;exit;       
           $res = $db->fetchAll($consump_sum_sql);
                              
                              return $sum = $res[0]['sums'];
           
       }
      public function selectStockOutsCommodity($imploded){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
         $sql = "SELECT * FROM `commodity_name_option` WHERE `id` IN ($imploded)";
         $res = $db->fetchAll($sql);
         return $res;
      }
	   public function get_consumption_for_unique_fac($start_date,$end_date,$facility_id,$name_id,$location){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sum = 0;
          // $sql = "SELECT SUM(consumption) as sums FROM commodity left join facility on commodity.facility_id= facility.id left join facility_summary_person_view on facility_summary_person_view.facility_id = commodity.facility_id WHERE  (commodity.date>='".$start_date."' AND commodity.date<='".$end_date."') AND (commodity.facility_id='".$facility_id."') AND (name_id='".$name_id."')";
           $sql = "SELECT SUM(consumption) as sums FROM commodity left join facility_location_view on commodity.facility_id = facility_location_view.id  WHERE (commodity.date>='".$start_date."' AND commodity.date<='".$end_date."') AND (commodity.facility_id='".$facility_id."') AND (commodity.name_id='".$name_id."') ".$stock_out."  AND (facility_location_view.parent_id = ".$location." OR facility_location_view.location_id   = ".$location." )";      
//echo $sql;exit;
	   $res = $db->fetchAll($sql);
                              
                             $sum = $res[0]['sums'];
                             if($sum==""){
                                 $sum = 0;
                             }
                              return $sum;
           }
	   public function select_all_from_training_organizer(){
	    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT * FROM training_organizer_option ";
		$result = $db->fetchAll($sql);
		return $result;
		
	   }
     public function arraycount($array, $value){
    $counter = 0;
    foreach($array as $thisvalue) /*go through every value in the array*/
     {
           if($thisvalue === $value){ /*if this one value of the array is equal to the value we are checking*/
           $counter++; /*increase the count by 1*/
           }
     }
     return $counter;
     }
     public function all_particpants_with_this_location($facilityq,$training_type,$training_org,$start_date,$end_date,$dob_query,$location_id){
        // echo $location_id; 
         $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
          $sql = "SELECT  facility_summary_person_view.*,person.birthdate FROM facility_summary_person_view left join person on person.id=person_id WHERE  training_title_option_id='".$training_type."' AND training_organizer_option_id='".$training_org."' ".$facilityq." AND (facility_summary_person_view.training_end_date>='$start_date' AND facility_summary_person_view.training_end_date<='$end_date')  ".$dob_query." AND (facility_summary_person_view.parent_id='".$location_id."' OR facility_summary_person_view.geo_zone_id='".$location_id."' OR facility_location_id ='".$location_id."') GROUP BY person_id";
  //echo $sql;echo '<br/><br/>';echo $location_id;exit;
   $res = $db->fetchAll($sql);
   
                              //print_r($consump_sum_sql); exit;
                              return $res;
          }
          public function get_total_number_of_trainings($training_org,$type,$start_date,$end_date,$locationQuery="  "){
              $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
          
          $sql = "SELECT count(*) as count FROM training "
                  . " LEFT JOIN training_location as tl on training.training_location_id=tl.id"
                  . " LEFT JOIN location as loc on tl.location_id=loc.id "
                  . " WHERE training_end_date>='".$start_date."' "
                  . " AND training_end_date<='".$end_date."'  "
                  . " AND training_organizer_option_id='".$training_org."' "
                  . " AND training_title_option_id='".$type."' "
                  ."$locationQuery"
                  . " AND training.is_deleted='0'";
                Helper2::jLog($sql);
                $res = $db->fetchAll($sql);
                             
                              return $res[0]['count'];
          }
      public function get_training_records_from_db( $trainingorganizerquery,$trainingtitleoptionquery,$start_date,$end_date,$locationQuery="  "){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
          
          $sql = "SELECT training.id, training_organizer_phrase,training_title_phrase,training_start_date,training_end_date FROM training 
                  left join training_organizer_option on training_organizer_option_id=training_organizer_option.id 
                  left join training_title_option on training_title_option.id=training_title_option_id 
                  LEFT JOIN training_location as tl on training.training_location_id=tl.id 
                  LEFT JOIN location as loc on tl.location_id=loc.id  
                  WHERE training_end_date>='".$start_date."' AND training_end_date<='".$end_date."'  ".$trainingorganizerquery." ".$trainingtitleoptionquery." $locationQuery AND training.is_deleted='0' ";
             Helper2::jLog($sql);
          $res = $db->fetchAll($sql);
                                    return $res;
      }
      public function get_counter_person_trainings($training_id,$facilityq="",$personstatusq=""){
          $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
          $sql = "SELECT count(*) as count FROM person_to_training left join person on person_to_training.person_id=person.id WHERE training_id='".$training_id."' ".$facilityq." ".$personstatusq;
   $res = $db->fetchAll($sql);
                              //print_r($consump_sum_sql); exit;
                              return $res[0]['count'];
      }
       
           
       public function check_length_add_one($value){
           if(strlen($value)==1){
               $value = "0".$value;
               
           }
           return $value;
       }
       public function get_training_organizer_phrase($id){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT * FROM training_organizer_option WHERE id='".$id."'";
            $res = $db->fetchAll($sql);
                              //print_r($consump_sum_sql); exit;
                              return $phrase = (isset($res[0]['training_organizer_phrase']))?$res[0]['training_organizer_phrase']:"";
       }
       public function get_all_training_organizers(){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT * FROM training_organizer_option WHERE is_deleted='0' ";
            $res = $db->fetchAll($sql);
            $trainingorganizers = array();
                              foreach($res as $ress){
                                  $trainingorganizers[] = $ress['id'];
                              }
                              return $trainingorganizers;
       }
       
       public function get_hw_count_for_each_facility($start_date,$end_date,$facility_id,$small_value){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            $count = 0;
           $sql = "SELECT count(*) as count FROM facility_summary_person_view WHERE training_start_date>='".$start_date."' AND training_end_date<='".$end_date."' AND facility_id='".$facility_id."' AND system_training_type='".$small_value."'";
      $res = $db->fetchAll($sql);
                              //print_r($consump_sum_sql); exit;
                              return $sum = (isset($res[0]['count']))?$res[0]['count']:0;
           }
           public function get_all_locations(){
               $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
               $sql = "SELECT id FROM location WHERE  tier='2'";
               $new_array = array();
                $res = $db->fetchAll($sql);
                foreach($res as $result){
                    $id = $result['id'];
                    array_push($new_array,$id);
                }
                              //print_r($consump_sum_sql); exit;
                              return $new_array;
           }
       public function get_commodity_consumptionSum($start,$end,$facility_id,$name_id){
             $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
             $sum = 0;
           $consump_sum_sql = "SELECT SUM(consumption) as sums  FROM commodity WHERE (commodity.date>='".$start."' AND commodity.date<='".$end."') AND (facility_id IN (".$facility_id.") )  AND (name_id='".$name_id."')";
                               $res = $db->fetchAll($consump_sum_sql);
                            
                              //print_r($consump_sum_sql); exit;
                              return $sum = $res[0]['sums'];
           
           
       }
       public function get_commodity_consumption($start,$end,$facility_id,$name_id){
             $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
             $sum = 0;
           $consump_sum_sql = "SELECT * FROM commodity WHERE (commodity.date>='".$start."' AND commodity.date<='".$end."') AND (facility_id IN (".$facility_id.") )  AND (name_id='".$name_id."')";
                               $res = $db->fetchAll($consump_sum_sql);
                               $sizeof = sizeof($res);
                                echo $sizeof;echo '<br/><br/>';
                              if(empty($res) || $sizeof==0){
                                   $sum = "";
                                   echo 'empty';
                              }
                              else{
                                 $sum = $this->get_commodity_consumptionSum($start,$end,$facility_id,$name_id); 
                              }
                              echo '<br/><br/>';
                              //return $sum = $res[0]['sums'];
                              return $sum;
           
           
       }
       public function get_at_least_one_commodity_out_of_stock($start,$end,$facility_id,$name_id){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
             $stock_out= "";
           $consump_sum_sql = "SELECT stock_out  FROM commodity WHERE (commodity.date>='".$start."' AND commodity.date<='".$end."') AND (facility_id IN (".$facility_id.") )  AND (name_id ='".$name_id."')";
         // echo $consump_sum_sql; echo '<br/><br/>';                  
           $res = $db->fetchAll($consump_sum_sql);
                             foreach($res as $res){
                                $stock_out = trim($res['stock_out']);
                                if($stock_out=="Y" || $stock_out=="y"){
                                 break;   
                                }
                                 
                             }
                              
                              return $stock_out;
       }
       public function get_just_fac_with_type($all_name_ids,$beginning,$end,$location_id,$stockq,$facesss){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT count(*) FROM commodity left join facility_location_view on facility_id=facility_location_view.id WHERE  (commodity.date>='".$beginning."' AND commodity.date<='".$end."') ".$facesss." AND (facility_location_view.parent_id = '".$location_id."' OR facility_location_view.location_id  = '".$location_id."') AND (name_id IN(".$all_name_ids."))  ".$stockq."  ";
       //echo $sql;exit;
           $res = $db->fetchAll($sql);
                              
                              return $sum = $res[0]['count(*)'];
           
       }
       public function get_all_participants_with_all_conditions($locationquery,$cadrequery,$trainingorganizerquery,$trainingtypequery,$training_end_date,$genderq,$certificationq,$facilityq="",$dob_query="",$pstatusq=""){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
      // $sql = "SELECT facility_summary_person_view.*,person.birthdate FROM facility_summary_person_view inner join person on person.id=person_id WHERE (parent_id IN (".$locator.") OR geo_zone_id IN (".$locator.") OR facility_location_id IN (".$locator.")) ".$cadrequery." ".$trainingorganizerquery." ".$trainingtypequery." ".$training_end_date." ".$genderq." ".$certificationq." ".$facilityq." ".$dob_query;    
 
       
       $sql = "SELECT p.id as person_id,p.first_name,p.middle_name,p.last_name,p.person_status_id,ps.title as person_status_title,flv.state,flv.lga,flv.facility_name,pqual.qualification_phrase,tto.training_title_phrase,torg.training_organizer_phrase,t.training_start_date,t.training_end_date,ptt.certification,p.gender,flv.location_id FROM  person_to_training as ptt left join person as p on p.id = ptt.person_id LEFT JOIN person_status as ps on ps.id = p.person_status_id left join person_qualification_option as pqual on pqual.id=p.primary_qualification_option_id  left join training as t on t.id=ptt.training_id  left join training_title_option as tto on tto.id=t.training_title_option_id left join training_organizer_option as torg on torg.id = t.training_organizer_option_id left join facility_location_view as flv on flv.id=p.facility_id WHERE ".$locationquery." ".$cadrequery." ".$trainingorganizerquery." ".$trainingtypequery." ".$training_end_date." ".$genderq." ".$certificationq." ".$facilityq." ".$dob_query." ".$pstatusq." AND p.is_deleted='0' AND t.is_deleted='0' AND tto.is_deleted='0' ";
      //echo $sql;exit;
      $helper2 = new Helper2();
     // Helper2::jLog($sql);
//echo $sql;
       //echo '<br/><br/>';
       $res = $db->fetchAll($sql);
                              
                              return $res;
           
       }
       public function get_just_customized_facility_id($all_name_ids,$beginning,$end,$location_id,$facility_id){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT count(*) FROM commodity WHERE (facility_id='".$facility_id."') AND (commodity.date>='".$beginning."' AND commodity.date<='".$end."') AND name_id IN(".$all_name_ids.") ";
       //echo $sql;exit;   
           $res = $db->fetchAll($sql);
                           
                              return $sum = $res[0]['count(*)'];
       }
       public function get_all_facilities_with_partner($partner_id){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT facility_id FROM person left join person_to_training on person.id=person_to_training.id left join training on person_to_training.training_id=training.id WHERE training_organizer_option_id='".$partner_id."'";
      $all_the_ids = array();
           $fac_ids = $db->fetchAll($sql);
            foreach($fac_ids as $ress){
              $value =  $ress['facility_id'];
              array_push($all_the_ids,$value);
           }
           return $all_the_ids;
           }
       public function get_all_facilities_with_location_id($location_id,$trainingorgq,$facilities=""){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT * FROM facility_summary_person_view left join facility_location_view on facility_id=facility_location_view.id  WHERE (facility_location_view.parent_id='".$location_id."' OR facility_location_view.location_id='".$location_id."')  ".$trainingorgq." ".$facilities."";
        // echo $sql;exit;
           $new = array();
           $results = $db->fetchAll($sql);
           return $results;
           
       }
	   public function all_facilities_id($location_id,$facilities=""){
	   $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT * FROM facility_location_view WHERE (parent_id='".$location_id."' OR facility_location_view.location_id='".$location_id."') ".$facilities."";
         //echo $sql;exit;
           $new = array();
           $results = $db->fetchAll($sql);
		   return $results;
	   }
	   public function get_training_organizer_phrase_with_facility($facility_id){
	   $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT training_organizer_phrase FROM facility_summary_person_view WHERE facility_id=".$facility_id."";
         //echo $sql;exit;
           $new = array();
           $results = $db->fetchAll($sql);
		   $result = $results[0]['training_organizer_phrase'];
		   return $result;
	   }
       public function get_for_particular_id($all_name_ids,$facility_id,$beginning,$end,$location){
            $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $sql = "SELECT count(*) FROM commodity WHERE facility_id IN (  SELECT facility_id FROM commodity left join facility_location_view on commodity.facility_id = facility_location_view.id  WHERE (facility_location_view.id=".$facility_id.")(commodity.date>='".$beginning."' AND commodity.date<='".$end."') AND (facility_location_view.parent_id = ".$location." OR facility_location_view.location_id  = ".$location.")) AND name_id IN(".$all_name_ids.") ";
       $res = $db->fetchAll($sql);
                              
                              return $sum = $res[0]['count(*)'];
       }
       public function get_the_name_ids($category){
           $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
           $all_the_ids = array();
           $sql = "SELECT id FROM commodity_name_option WHERE commodity_type='".$category."'";
           $res = $db->fetchAll($sql);
           foreach($res as $ress){
              $value =  $ress['id'];
              array_push($all_the_ids,$value);
           }
           return $all_the_ids;
       }
       
       public function return_the_month_name($id){
           $mons = array("01" => "January", "02" => "February", "03" => "March", "04" => "April", "05" => "May", "06" => "June", "07" => "July", "08" => "August", "09" => "September", "10" => "October", "11" => "November", "12" => "December");
return $mons[$id];
       }
      public function get_facility_with_hw_trained_in($training_type,$start_date,$end_date,$location_id,$training_type2,$stockq,$facess=""){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
         if($training_type2=="trained_in"){
          if($training_type=="larc"){
              $table_named = "larctrained";
          }else {
              $table_named = "fptrained";
              
          }
         }else{
              $table_named = "factrainedhw";
              
          }
          $sql = "SELECT count(*) FROM facility_worker_training_counts_view WHERE facid IN (  SELECT facility_id FROM commodity left join facility_location_view on commodity.facility_id = facility_location_view.id  WHERE (commodity.date>='".$start_date."' AND commodity.date<='".$end_date."'  ".$stockq.") AND (facility_location_view.parent_id = '".$location_id."' OR facility_location_view.location_id  = '".$location_id."') ".$facess.") AND ".$table_named.">=1";
         //echo $sql;exit;
          $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['count(*)'];
      }
      public function facility_location_summary_details($facility_id){
          $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT * FROM facility_location_view WHERE `id`='$facility_id'";
   $all_sql = $db->fetchAll($sql);
   if(isset($all_sql[0])){
       return $all_sql[0];
   }else{
       return $all_sql_tmp = array();
   }
    
          
      }
public function get_name_category($name_id,$getter="system_training_type"){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT `$getter` FROM training_title_option WHERE id='$name_id' LIMIT 1";
   $all_sql = $db->fetchAll($sql);
   
    return $all_sql[0][''.$getter.''];
}

public function get_training_title_option($trainingtype){
     $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql  = "SELECT * FROM training_title_option WHERE id='".$trainingtype."'";
    $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['training_title_phrase'];
}
public function get_training_organizer_phrase_name($torg){
     $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql  = "SELECT * FROM training_organizer_option WHERE id='".$torg."'";
    $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['training_organizer_phrase'];
}
public function get_all_training_titles(){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql  = "SELECT * FROM training_title_option as tto WHERE tto.is_deleted='0'";
    $all_sql = $db->fetchAll($sql);
    $trainingtitles = array();
    foreach($all_sql as $res){
        $trainingtitles[] = $res['id'];
    }
    return $trainingtitles;
}
public function get_commodity_option_name($name_id){
    $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
    $sql = "SELECT commodity_name FROM commodity_name_option WHERE id='$name_id' LIMIT 1";
   $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['commodity_name'];
    
}

public function get_location_name($location_id){
     $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
     $sql = "SELECT location_name FROM location WHERE id=$location_id";
     $all_sql = $db->fetchAll($sql);
    return $all_sql[0]['location_name'];
    
}
        public function get_location($facid){
            $sql = "SELECT location_id FROM facility WHERE id='$facid'";
            
            
        }
	
public function psFacilityReportAction() {
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		//locations
		$this->viewAssignEscaped ( 'locations', Location::getAll () );

		$helper = new Helper();
		$this->view->assign ( 'mode', 'id' );
		$this->view->assign ( 'institutions', $helper->getInstitutions());
		$this->view->assign ( 'cadres', $helper->getCadres());
		$this->view->assign ( 'institutiontypes', $helper->AdminInstitutionTypes());
		$this->view->assign ( 'cohorts', $helper->getCohorts());
		$this->view->assign ( 'nationalities', $helper->getNationalities());
		$this->view->assign ( 'funding', $helper->getFunding());
		$this->view->assign ( 'tutors', $helper->getTutors());
		$this->view->assign ( 'facilities', $helper->getFacilities());
		$this->view->assign ( 'facilitytypes', $helper->getFacilityTypes());
		$this->view->assign ( 'sponsors', $helper->getOldSponsors());
		$this->view->assign ( 'coursetypes', $helper->AdminCourseTypes());
		$this->view->assign ( 'degrees', $helper->getDegrees());
		$this->view->assign ( 'tutortypes', $helper->AdminTutortypes());

		if ($this->getSanParam ( 'process' )){
			// INITIALIZING ARRAYS
			$headers = array();
			$select = array();
			$from = array();
			$join = array();
			//
			//	$join[] = array(
			// 		"type"   => "inner",
			//		"table"  => "tablename t",
			//		"field1" => "t.field1",
			//		"field2" => "t2.field2",
			//	);
			//
			$where = array();
			$sort = array();

			$locations = Location::getAll ();
			$translation = Translation::getAll ();

			$showfacility 				= isset($_GET['showfacility']);
			$showProvince 				= isset($_GET['showProvince']);
			$showDistrict 				= isset($_GET['showDistrict']);
			$showRegionC 				= isset($_GET['showRegionC']);
			$showfacilitytype 			= isset($_GET['showfacilitytype']);
			$showinstitutionsponsors	= isset($_GET['showinstitutionsponsors']);
			$showcadre					= isset($_GET['showcadre']);
			$showgraduates				= isset($_GET['showgraduates']);
			$showgraduatesyear			= isset($_GET['showgraduatesyear']);
			$showpatients				= isset($_GET['showpatients']);
			$startday					= isset($_GET['startday']);

			$facility 					= $this->getSanParam('facility');
			$province_id 				= $this->getSanParam('province_id');
			$district_id 				= $this->getSanParam('district_id');
			$region_c_id 				= $this->getSanParam('region_c_id');
			$facilitytype 				= $this->getSanParam('facilitytype');
			$institutionsponsors 		= $this->getSanParam('institutionsponsors');
			$cadre 						= $this->getSanParam('cadre');

			$from[] = "facility f";
			//if ($showfacility){
				$headers[] = "Facility";
				$select[] = "f.facility_name";
				$sort[] = "f.facility_name";
			//}

			if ($facility != ""){
				$where[] = "f.id = " . $facility;
			}

			if ($showfacilitytype || $facilitytype){
				// Need join on facility type to show OR filter
				if ($showfacilitytype){
					// Only add header and select if showing field
					$headers[] = "Facility type";
					$select[] = "fto.facility_type_phrase";
				}

				$join[] = array(
					"type"		=> "inner",
					"table"		=> "facility_type_option fto",
					"field1"	=> "fto.id",
					"field2"	=> "f.type_option_id",
				);
				if ($facilitytype){
					$where[] = "fto.id = " . $facilitytype;
				}
				$sort[] = "fto.facility_type_phrase";
			}

			if ($showinstitutionsponsors || $institutionsponsors){
				// Need join on facility type to show OR filter
				if ($showinstitutionsponsors){
					// Only add header and select if showing field
					$headers[] = "Sponsor";
					$select[] = "fso.facility_sponsor_phrase";
				}

				// OPTIONAL LINK - LEFT JOINING
				$join[] = array(
					"type"		=> "left",
					"table"		=> "facility_sponsor_option fso",
					"field1"	=> "fso.id",
					"field2"	=> "f.sponsor_option_id",
				);
				if ($institutionsponsors){
					$where[] = "fso.id = " . $institutionsponsors;
				}
				$sort[] = "fso.facility_sponsor_phrase";
			}

			// INCLUDING LOCATION IDENTIFYER, IF NECESSARY
			if (($region_c_id != "") || ($district_id != "") || ($province_id != "") || ($showProvince != "") || ($showDistrict != "") || ($showRegionC != "")){
				$select[] = "f.location_id";
			}

			if ($showcadre || $cadre){
				$join[] = array(
					"type"		=> "left",
					"table"		=> "person_qualification_option pqo",
					"field1"	=> "pqo.id",
					"field2"	=> "f.sponsor_option_id",
				);
			}

			$query = "SELECT ";
			$query .= implode (", ", $select);
			$query .= " FROM ";
			$query .= implode (", ", $from) . " ";
			if (count ($join) > 0){
				foreach ($join as $j){
					$query .= strtoupper($j['type']) . " JOIN " . $j['table'] . " ON " . $j['field1'] . " = " . $j['field2'] . " ";
				}
			}
			if (count ($where) > 0){
				$query .= " WHERE " . implode(" AND ", $where);
			}

			if (count ($sort) > 0){
				$query .= " ORDER BY " . implode(", ", $sort);
			}



			//echo $query . "<br>";
			$rows = $db->fetchAll ($query);
			$regions = array();


#			var_dump ($rows);
			// Filtering by locations
			if (($region_c_id != "") || ($district_id != "") || ($province_id != "")){
				$__rows = array();
				if ($region_c_id != ""){
					// 3 levels selected. Going with this one first
					$regions = explode("_", $region_c_id[0]);
				} elseif ($district_id != ""){
					// 2 levels selected
					$regions = explode("_", $district_id[0]);
				} elseif ($province_id != ""){
					// 1 level selected
					$regions = explode("_", $province_id[0]);
				}

				// Include headers once
				if ($showProvince){
					$headers[] = @$translation ['Region A (Province)'];
				}
				if ($showDistrict){
					$headers[] = @$translation ['Region B (Health District)'];
				}
				if ($showRegionC){
					$headers[] = @$translation ['Region C (Local Region)'];
				}

				foreach ($rows as $row){
					list ( $cname, $prov, $dist, $regc ) = Location::getCityInfo ( $row['location_id'], $this->setting ( 'num_location_tiers' ) );
					if ($showProvince){
						$loc = $locations[$prov];
						$row[@$translation ['Region A (Province)']] = $loc['name'];
					}
					if ($showDistrict){
						$loc = $locations[$dist];
						$row[@$translation ['Region B (Health District)']] = $loc['name'];
					}
					if ($showRegionC){
						$loc = $locations[$regc];
						$row[@$translation ['Region C (Local Region)']] = $loc['name'];
					}

					unset ($row['location_id']);

					$userow = true;
					if (count ($regions) > 0){
						switch (count ($regions)){
							case 1:
								// Selected province
								if ($prov != $regions[0]){
									$userow = false;
								}
							break;
							case 2:
								// Selected province, district
								if (($prov != $regions[0]) || ($dist != $regions[1])){
									$userow = false;
								}
							break;
							case 3:
								// Selected province, district, regionc
								if (($prov != $regions[0]) || ($dist != $regions[1]) || ($regc != $regions[2])){
									$userow = false;
								}
							break;
						}
					}

					if ($userow){
						$__rows[] = $row;
					}
				}
				$rows = $__rows;
			} elseif (($showProvince != "") || ($showDistrict != "") || ($showRegionC != "")){
				// NOT FILTERING, BUT STILL INCLUDING LOCATION COLUMNS
				// Include headers once
				if ($showProvince){
					$headers[] = @$translation ['Region A (Province)'];
				}
				if ($showDistrict){
					$headers[] = @$translation ['Region B (Health District)'];
				}
				if ($showRegionC){
					$headers[] = @$translation ['Region C (Local Region)'];
				}
				$__rows = array();
				foreach ($rows as $row){
					list ( $cname, $prov, $dist, $regc ) = Location::getCityInfo ( $row['location_id'], $this->setting ( 'num_location_tiers' ) );
					if ($showProvince){
						$loc = $locations[$prov];
						$row[@$translation ['Region A (Province)']] = $loc['name'];
					}
					if ($showDistrict){
						$loc = $locations[$dist];
						$row[@$translation ['Region B (Health District)']] = $loc['name'];
					}
					if ($showRegionC){
						$loc = $locations[$regc];
						$row[@$translation ['Region C (Local Region)']] = $loc['name'];
					}
					unset ($row['location_id']);
					$__rows[] = $row;
				}
				$rows = $__rows;
			}


#			var_dump ($head);
#			var_dump ($rows);

/*
		//locations
		$this->viewAssignEscaped ( 'locations', $locations );
		list ( $cname, $prov, $dist, $regc ) = Location::getCityInfo ( $facilityRow->location_id, $this->setting ( 'num_location_tiers' ) );
		$facilityArray ['facility_city'] = $cname;
		$facilityArray ['region_c_id'] = $regc;
		$facilityArray ['district_id'] = $dist;
		$facilityArray ['province_id'] = $prov;

*/
			$this->viewAssignEscaped("headers", $headers);
			$this->viewAssignEscaped("output", $rows);
		}
	}


	/***************************************************************
	 *                                                             *
	 *    #### #   # # #     #      #### #   #   #   ####  #####   *
	 *   #     #  #  # #     #     #     ## ##  # #  #   #   #     *
	 *   #     # #   # #     #     #     # # #  # #  #   #   #     *
	 *    ###  ##    # #     #      ###  # # # ##### ####    #     *
	 *       # # #   # #     #         # #   # #   # #   #   #     *
	 *       # #  #  # #     #         # #   # #   # #   #   #     *
	 *   ####  #   # # ##### ##### ####  #   # #   # #   #   #     *
	 *                                                             *
	 ***************************************************************/

	public function ssCompAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
			$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
			$where = array('p.is_deleted = 0');
			$whr = array();
			$where []= 'cmpr.person = p.id';
			$where []= 'cmp.person = p.id';
			$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}
			$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
			$where []= 'cmpr.active = \'Y\'';
			$where []= 'cmpr.res = 1';
			$where []= 'cmp.active = \'Y\'';
			if($criteria ['qualification_id']=="6")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
			}
			if($criteria ['qualification_id']=="7")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listdq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="8")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listnq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="9")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
			}
			if( !empty($where) ){ $sql .= ' WHERE ' . implode(' AND ', $where); }
			if( !empty($whr) ){ $sql .= ' AND (' . implode(' OR ', $whr) . ')'; }
			$rowArray = $db->fetchAll ( $sql );
			$qss=array();
			$nmss=array();
			if($criteria ['qualification_id']=="6")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listcq' ));
			}
			if($criteria ['qualification_id']=="7")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listdq' ));
			}
			if($criteria ['qualification_id']=="8")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listnq' ));
			}
			if($criteria ['qualification_id']=="9")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listpq' ));
			}
			$ct;
			$ct=0;
			$rss=array();
			$ctt;
			foreach ( $qss as $kys => $vls ) {
				$rss[$ct]=0;
				$ctt=0;
				$wss=split(",",$nmss[$vls]);
				foreach ( $wss as $kyss => $vlss ) {
					foreach ( $rowArray as $kss => $vss ) {
						if($vlss." " == $vss['question']." ")
						{
							if($vss['option']=="A")
							{
								$rss[$ct]=$rss[$ct]+4;
							}
							else
							{
								if($vss['option']=="B")
								{
									$rss[$ct]=$rss[$ct]+3;
								}
								else
								{
									if($vss['option']=="C")
									{
										$rss[$ct]=$rss[$ct]+2;
									}
									else
									{
										if($vss['option']=="D")
										{
											$rss[$ct]=$rss[$ct]+1;
										}
									}
								}
							}
							$ctt=$ctt+1;
						}
					}
				}
				if($ctt>0)
					$rss[$ct]=number_format((($rss[$ct]/(4*$ctt))*100),2);
				$ct=$ct+1;
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->viewAssignEscaped ( 'rss', $rss );
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function ssCompcompAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['Questions'] = $this->getSanParam ( 'Questions' );
		$criteria ['outputType'] = $this->getSanParam ( 'outputType' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$prsns=array();
			$prsnscnt=0;
			if($criteria ['qualification_id']=="6")
			{
				$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
				$whr = array();
				$whr []= '`question` IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
				$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
				$sql .= ' GROUP BY `person`';
				$rowArray = $db->fetchAll ( $sql );
				$tlques=split(",",$this->getSanParam ( 'listcq' ));
				$ttlques=count($tlques);
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				foreach ( $qs as $kys => $vls ) {
					$fr=split('\^',$vls);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="7")
			{
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				$nms=split("~",$this->getSanParam ( 'listdq' ));
				foreach ( $qs as $kys => $vls ) {
					$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
					$whr = array();
					$fr=split('\^',$vls);
					$whr []= '`question` IN ('."'".str_replace(",","','",$nms[$fr[1]])."'".')';
					$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
					$sql .= ' GROUP BY `person`';
					$rowArray = $db->fetchAll ( $sql );
					$tlques=split(",",$nms[$fr[1]]);
					$ttlques=count($tlques);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="8")
			{
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				$nms=split("~",$this->getSanParam ( 'listnq' ));
				foreach ( $qs as $kys => $vls ) {
					$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
					$whr = array();
					$fr=split('\^',$vls);
					$whr []= '`question` IN ('."'".str_replace(",","','",$nms[$fr[1]])."'".')';
					$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
					$sql .= ' GROUP BY `person`';
					$rowArray = $db->fetchAll ( $sql );
					$tlques=split(",",$nms[$fr[1]]);
					$ttlques=count($tlques);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			if($criteria ['qualification_id']=="9")
			{
				$sql='SELECT `person`, SUM(-(ASCII(`option`)-69)) `sm` FROM `comp`';
				$whr = array();
				$whr []= '`question` IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
				$sql .= ' WHERE `active` = \'Y\' AND `option` <> \'E\' AND `option` <> \'F\' AND (' . implode(' OR ', $whr) . ')';
				$sql .= ' GROUP BY `person`';
				$rowArray = $db->fetchAll ( $sql );
				$tlques=split(",",$this->getSanParam ( 'listpq' ));
				$ttlques=count($tlques);
				$qs=split('\$',$this->getSanParam ( 'Questions' ));
				foreach ( $qs as $kys => $vls ) {
					$fr=split('\^',$vls);
					$min=0;
					$max=0;
					if($fr[2]=="100")
					{
						$min=90;
						$max=100;
					}
					else
					{
						if($fr[2]=="89")
						{
							$min=75;
							$max=90;
						}
						else
						{
							if($fr[2]=="74")
							{
								$min=60;
								$max=75;
							}
							else
							{
								$min=1;
								$max=60;
							}
						}
					}
					foreach ( $rowArray as $prsn => $mrk ) {
						$prcnt=number_format((($mrk['sm']/(4*$ttlques))*100),2);
						if($prcnt>$min && $prcnt<=$max)
						{
							$prsns[$prsnscnt]=$mrk['person'];
							$prsnscnt=$prsnscnt+1;
						}
					}
				}
			}
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
			$sql = 'SELECT  DISTINCT p.`id`, p.`first_name` ,  p.`last_name` ,  p.`gender` FROM `person` as p, facility as f, ('.$location_sub_query.') as l, `person_qualification_option` as q WHERE p.`primary_qualification_option_id` = q.`id` and p.facility_id = f.id and f.location_id = l.id AND p.`primary_qualification_option_id` IN (SELECT `id` FROM `person_qualification_option` WHERE `parent_id` = ' . $criteria ['qualification_id'] . ') AND p.`is_deleted` = 0 AND p.`id` IN (';
			if(count($prsns)>0)
			{
				foreach ( $prsns as $k => $v ) {
					$sql = $sql . $v . ',';
				}
			}
			$sql = $sql . '0';
			if ($criteria ['facilityInput']) {
				$sql = $sql . ') AND p.facility_id = "' . $criteria ['facilityInput'] . '";';
			}
            else {
                $sql = $sql . ');';
            }
			$rowArray = $db->fetchAll ( $sql );
			if ($criteria ['outputType']) {
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );
                                        
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function ssCompcsvAction() {
		$v1=split("~",$this->getSanParam ( 'v1' ));
		$v2=split("~",$this->getSanParam ( 'v2' ));
        $p=$this->getSanParam ( 'p' );
        $d=$this->getSanParam ( 'd' );
        $s=$this->getSanParam ( 's' );
        $f=$this->getSanParam ( 'f' );
		$this->viewAssignEscaped ( 'v1', $v1 );
		$this->viewAssignEscaped ( 'v2', $v2 );
		$this->viewAssignEscaped ( 'p',  $p);
		$this->viewAssignEscaped ( 'd',  $d);
		$this->viewAssignEscaped ( 's',  $s);
		$this->viewAssignEscaped ( 'f',  $f);
	}

	public function ssDetailAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['score_id'] = $this->getSanParam ( 'score_id' );
		$criteria ['primarypatients'] = $this->getSanParam ( 'primarypatients' );
		$criteria ['hivInput'] = $this->getSanParam ( 'hivInput' );
		$criteria ['trainer_type_option_id1'] = $this->getSanParam ( 'trainer_type_option_id1' );
		$criteria ['grp1'] = $this->getSanParam ( 'grp1' );
		$criteria ['grp2'] = $this->getSanParam ( 'grp2' );
		$criteria ['grp3'] = $this->getSanParam ( 'grp3' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
		if ($criteria ['go']) {
			$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
			$num_locs = $this->setting('num_location_tiers');
			list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
			$sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
			if ( $criteria['training_title_option_id'] ) {
				 $sql .= ', person_to_training as ptt ';
				 $sql .= ', training as tr  ';
			}
			$where = array('p.is_deleted = 0');
			$whr = array();
			$where []= 'cmpr.person = p.id';
			$where []= 'cmp.person = p.id';
			$where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
			if ($criteria ['facilityInput']) {
				$where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
			}
			if ( $criteria['training_title_option_id'] ) {
				$where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
			}
			$where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
			$where []= 'cmpr.active = \'Y\'';
			$where []= 'cmpr.res = 1';
			$where []= 'cmp.active = \'Y\'';
			if($criteria ['qualification_id']=="6")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
			}
			if($criteria ['qualification_id']=="7")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listdq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="8")
			{
				$qs=split(",",$this->getSanParam ( 'ques' ));
				$nms=split("~",$this->getSanParam ( 'listnq' ));
				foreach ( $qs as $kys => $vls ) {
					$whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
				}
			}
			if($criteria ['qualification_id']=="9")
			{
				$whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
			}
			if( !empty($where) ){ $sql .= ' WHERE ' . implode(' AND ', $where); }
			if( !empty($whr) ){ $sql .= ' AND (' . implode(' OR ', $whr) . ')'; }

			$rowArray = $db->fetchAll ( $sql );
			$qss=array();
			$nmss=array();
			if($criteria ['qualification_id']=="6")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listcq' ));
			}
			if($criteria ['qualification_id']=="7")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listdq' ));
			}
			if($criteria ['qualification_id']=="8")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listnq' ));
			}
			if($criteria ['qualification_id']=="9")
			{
				$qss=split(",",$this->getSanParam ( 'ques' ));
				$nmss=split("~",$this->getSanParam ( 'listpq' ));
			}
			$ct;
			$ct=0;
			$rss=array();
			$ctt;
			foreach ( $qss as $kys => $vls ) {
				$rss[$ct]=0;
				$ctt=0;
				$wss=split(",",$nmss[$vls]);
				foreach ( $wss as $kyss => $vlss ) {
					foreach ( $rowArray as $kss => $vss ) {
						if($vlss." " == $vss['question']." ")
						{
							if($vss['option']=="A")
							{
								$rss[$ct]=$rss[$ct]+4;
							}
							else
							{
								if($vss['option']=="B")
								{
									$rss[$ct]=$rss[$ct]+3;
								}
								else
								{
									if($vss['option']=="C")
									{
										$rss[$ct]=$rss[$ct]+2;
									}
									else
									{
										if($vss['option']=="D")
										{
											$rss[$ct]=$rss[$ct]+1;
										}
									}
								}
							}
							$ctt=$ctt+1;
						}
					}
				}
				if($ctt>0)
					$rss[$ct]=number_format((($rss[$ct]/(4*$ctt))*100),2);
				$ct=$ct+1;
			}
			$this->viewAssignEscaped ( 'results', $rowArray );
			$this->viewAssignEscaped ( 'rss', $rss );
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		require_once ('models/table/TrainingTitleOption.php');
		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $titleArray );
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );
	}

	public function ssProfAction() {
		if (! $this->hasACL ( 'view_people' ) and ! $this->hasACL ( 'edit_people' )) {
			$this->doNoAccessError ();
		}
		$criteria = array ();
		list($criteria, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
		$criteria ['facilityInput'] = $this->getSanParam ( 'facilityInput' );
		$criteria ['training_title_option_id'] = $this->getSanParam ( 'training_title_option_id' );
		$criteria ['qualification_id'] = $this->getSanParam ( 'qualification_id' );
		$criteria ['ques'] = $this->getSanParam ( 'ques' );
		$criteria ['go'] = $this->getSanParam ( 'go' );
        $criteria ['all'] = $this->getSanParam ( 'all' );
		if ($criteria ['go']) {
#			var_dump ($_GET);
#			exit;
            if ($criteria ['all']) {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $num_locs = $this->setting('num_location_tiers');
                list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);
                $sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
                if ( $criteria['training_title_option_id'] ) {
                     $sql .= ', person_to_training as ptt ';
                     $sql .= ', training as tr  ';
                }
                $where = array('p.is_deleted = 0');
                $where []= 'cmpr.person = p.id';
                $where []= 'cmp.person = p.id';
                $where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
                if ($criteria ['facilityInput']) {
                    $where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
                }
                if ( $criteria['training_title_option_id'] ) {
                    $where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
                }
                $where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id IN (6, 7, 8, 9) ) ';
                $where []= 'cmpr.active = \'Y\'';
                $where []= 'cmpr.res = 1';
                $where []= 'cmp.active = \'Y\'';
                $sql .= ' WHERE ' . implode(' AND ', $where);
die (__LINE__ . " - " . $sql);

                $rowArray = $db->fetchAll ( $sql );
                $qss=array();
                $nmss=array();
                $qss=split(",","0,1,2,3,4,5,6,7");
                $nmss=split("~","1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,200~01,02,03,04,05,06,07,08,09~31,32,33,34,35,36,37,38~41,42,43,44,45~51,52,53,54,55,56,57,58,59,510,511,512,513,514,515,516,517,518~61,62,63,64,65,66,67~71,72,73,74,75,76,77,78,79,710,711~21,22,23");
                $ct;
                $ct=0;
                $rssA=array();
                $rssB=array();
                $rssC=array();
                $rssD=array();
                $rssE=array();
                $ctt;
                foreach ( $qss as $kys => $vls ) {
                    $rssA[$ct]=0;
                    $rssB[$ct]=0;
                    $rssC[$ct]=0;
                    $rssD[$ct]=0;
                    $rssE[$ct]=0;
                    $ctt=0;
                    $wss=split(",",$nmss[$vls]);
                    foreach ( $wss as $kyss => $vlss ) {
                        foreach ( $rowArray as $kss => $vss ) {
                            if($vlss." " == $vss['question']." ")
                            {
                                if($vss['option']=="A")
                                {
                                    $rssA[$ct]=$rssA[$ct]+1;
                                }
                                else
                                {
                                    if($vss['option']=="B")
                                    {
                                        $rssB[$ct]=$rssB[$ct]+1;
                                    }
                                    else
                                    {
                                        if($vss['option']=="C")
                                        {
                                            $rssC[$ct]=$rssC[$ct]+1;
                                        }
                                        else
                                        {
                                            if($vss['option']=="D")
                                            {
                                                $rssD[$ct]=$rssD[$ct]+1;
                                            }
                                            else
                                            {
                                                if($vss['option']=="E")
                                                {
                                                    $rssE[$ct]=$rssE[$ct]+1;
                                                }
                                            }
                                        }
                                    }
                                }
                                $ctt=$ctt+1;
                            }
                        }
                    }
                    if($ctt>0) {
                        $rssA[$ct]=number_format((($rssA[$ct]/$ctt)*100),2);
                        $rssB[$ct]=number_format((($rssB[$ct]/$ctt)*100),2);
                        $rssC[$ct]=number_format((($rssC[$ct]/$ctt)*100),2);
                        $rssD[$ct]=number_format((($rssD[$ct]/$ctt)*100),2);
                        $rssE[$ct]=number_format((($rssE[$ct]/$ctt)*100),2);
                    }
                    $ct=$ct+1;
                }
                $this->viewAssignEscaped ( 'results', $rowArray );
                $this->viewAssignEscaped ( 'rssA', $rssA );
                $this->viewAssignEscaped ( 'rssB', $rssB );
                $this->viewAssignEscaped ( 'rssC', $rssC );
                $this->viewAssignEscaped ( 'rssD', $rssD );
                $this->viewAssignEscaped ( 'rssE', $rssE );
            } else {
                $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
                $num_locs = $this->setting('num_location_tiers');
                list($field_name,$location_sub_query) = Location::subquery($num_locs, $location_tier, $location_id);

                $sql = 'select DISTINCT cmp.person, cmp.question, cmp.option from person as p, person_qualification_option as q, facility as f, ('.$location_sub_query.') as l, comp as cmp, compres as cmpr';
                if ( $criteria['training_title_option_id'] ) {
                     $sql .= ', person_to_training as ptt ';
                     $sql .= ', training as tr  ';
                }
                $where = array('p.is_deleted = 0');
                $whr = array();
                $where []= 'cmpr.person = p.id';
                $where []= 'cmp.person = p.id';
                $where []= ' p.primary_qualification_option_id = q.id and p.facility_id = f.id and f.location_id = l.id ';
                if ($criteria ['facilityInput']) {
                    $where []= ' p.facility_id = "' . $criteria ['facilityInput'] . '"';
                }
                if ( $criteria['training_title_option_id'] ) {
                    $where []= ' p.id = ptt.person_id AND ptt.training_id = tr.id AND tr.training_title_option_id = ' . ($criteria ['training_title_option_id']) . ' ';
                }
                $where []= ' primary_qualification_option_id IN (SELECT id FROM person_qualification_option WHERE parent_id = ' . $criteria ['qualification_id'] . ') ';
                $where []= 'cmpr.active = \'Y\'';
                $where []= 'cmpr.res = 1';
                $where []= 'cmp.active = \'Y\'';

				$qry = "SELECT id FROM competencies_questions WHERE competencyid IN (" . implode(",", $_GET['competencyselect']) . ")";
                $questionresult = $db->fetchAll ($qry);
                $_q = array();
                foreach ($questionresult as $qres){
                	$_q[] = $qres['id'];
                }
                $whr[] = 'cmp.question IN (' . implode(",", $_q) . ')';

#                if($criteria ['qualification_id']=="6"){
#                    $whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listcq' ))."'".')';
#                }
#                if($criteria ['qualification_id']=="7"){
#                    $qs=split(",",$this->getSanParam ( 'ques' ));
#                    $nms=split("~",$this->getSanParam ( 'listdq' ));
#                    foreach ( $qs as $kys => $vls ) {
#                        $whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
#                    }
#                }
#                if($criteria ['qualification_id']=="8"){
#                    $qs=split(",",$this->getSanParam ( 'ques' ));
#                    $nms=split("~",$this->getSanParam ( 'listnq' ));
#                    foreach ( $qs as $kys => $vls ) {
#                        $whr []= 'cmp.question IN ('."'".str_replace(",","','",$nms[$vls])."'".')';
#                    }
#                }
#                if($criteria ['qualification_id']=="9"){
#                    $whr []= 'cmp.question IN ('."'".str_replace(",","','",$this->getSanParam ( 'listpq' ))."'".')';
#                }
            	if( !empty($where) ){ $sql .= ' WHERE ' . implode(' AND ', $where); }
				if( !empty($whr) ){ $sql .= ' AND (' . implode(' OR ', $whr) . ')'; }
				//todo check everything same here!
                $rowArray = $db->fetchAll ( $sql );
                $qss=array();
                $nmss=array();
                if($criteria ['qualification_id']=="6")
                {
                    $qss=split(",",$this->getSanParam ( 'ques' ));
                    $nmss=split("~",$this->getSanParam ( 'listcq' ));
                }
                if($criteria ['qualification_id']=="7")
                {
                    $qss=split(",",$this->getSanParam ( 'ques' ));
                    $nmss=split("~",$this->getSanParam ( 'listdq' ));
                }
                if($criteria ['qualification_id']=="8")
                {
                    $qss=split(",",$this->getSanParam ( 'ques' ));
                    $nmss=split("~",$this->getSanParam ( 'listnq' ));
                }
                if($criteria ['qualification_id']=="9")
                {
                    $qss=split(",",$this->getSanParam ( 'ques' ));
                    $nmss=split("~",$this->getSanParam ( 'listpq' ));
                }
                $ct;
                $ct=0;
                $rssA=array();
                $rssB=array();
                $rssC=array();
                $rssD=array();
                $rssE=array();
                $ctt;
                foreach ( $qss as $kys => $vls ) {
                    $rssA[$ct]=0;
                    $rssB[$ct]=0;
                    $rssC[$ct]=0;
                    $rssD[$ct]=0;
                    $rssE[$ct]=0;
                    $ctt=0;
                    $wss=split(",",$nmss[$vls]);
                    foreach ( $wss as $kyss => $vlss ) {
                        foreach ( $rowArray as $kss => $vss ) {
                            if($vlss." " == $vss['question']." ")
                            {
                                if($vss['option']=="A")
                                {
                                    $rssA[$ct]=$rssA[$ct]+1;
                                }
                                else
                                {
                                    if($vss['option']=="B")
                                    {
                                        $rssB[$ct]=$rssB[$ct]+1;
                                    }
                                    else
                                    {
                                        if($vss['option']=="C")
                                        {
                                            $rssC[$ct]=$rssC[$ct]+1;
                                        }
                                        else
                                        {
                                            if($vss['option']=="D")
                                            {
                                                $rssD[$ct]=$rssD[$ct]+1;
                                            }
                                            else
                                            {
                                                if($vss['option']=="E")
                                                {
                                                    $rssE[$ct]=$rssE[$ct]+1;
                                                }
                                            }
                                        }
                                    }
                                }
                                $ctt=$ctt+1;
                            }
                        }
                    }
                    if($ctt>0) {
                        $rssA[$ct]=number_format((($rssA[$ct]/$ctt)*100),2);
                        $rssB[$ct]=number_format((($rssB[$ct]/$ctt)*100),2);
                        $rssC[$ct]=number_format((($rssC[$ct]/$ctt)*100),2);
                        $rssD[$ct]=number_format((($rssD[$ct]/$ctt)*100),2);
                        $rssE[$ct]=number_format((($rssE[$ct]/$ctt)*100),2);
                    }
                    $ct=$ct+1;
                }
                $this->viewAssignEscaped ( 'results', $rowArray );
                $this->viewAssignEscaped ( 'rssA', $rssA );
                $this->viewAssignEscaped ( 'rssB', $rssB );
                $this->viewAssignEscaped ( 'rssC', $rssC );
                $this->viewAssignEscaped ( 'rssD', $rssD );
                $this->viewAssignEscaped ( 'rssE', $rssE );
            }
		}
		$this->view->assign ( 'criteria', $criteria );

		$this->viewAssignEscaped ( 'locations', Location::getAll("2") );

		require_once ('models/table/TrainingTitleOption.php');
		$titleArray = TrainingTitleOption::suggestionList ( false, 10000 );
		$this->viewAssignEscaped ( 'courses', $titleArray );
		$qualificationsArray = OptionList::suggestionListHierarchical ( 'person_qualification_option', 'qualification_phrase', false, false );
		$this->viewAssignEscaped ( 'qualifications', $qualificationsArray );
		$rowArray = OptionList::suggestionList ( 'facility', array ('facility_name', 'id' ), false, 9999 );
		$facilitiesArray = array ();
		foreach ( $rowArray as $key => $val ) {
			if ($val ['id'] != 0)
				$facilitiesArray [] = $val;
		}
		$this->viewAssignEscaped ( 'facilities', $facilitiesArray );


		$helper = new Helper();
		$this->viewAssignEscaped("competencies",$helper->getCompetencies());
		$this->viewAssignEscaped("compqualification", $helper->getQualificationCompetencies());
	}

	public function ssProfcsvAction() {
		$v1=split("~",$this->getSanParam ( 'v1' ));
		$v2=split("~",$this->getSanParam ( 'v2' ));
		$v3=split("~",$this->getSanParam ( 'v3' ));
		$v4=split("~",$this->getSanParam ( 'v4' ));
		$v5=split("~",$this->getSanParam ( 'v5' ));
		$v6=split("~",$this->getSanParam ( 'v6' ));
        $p=$this->getSanParam ( 'p' );
        $d=$this->getSanParam ( 'd' );
        $s=$this->getSanParam ( 's' );
        $f=$this->getSanParam ( 'f' );
		$this->viewAssignEscaped ( 'v1', $v1 );
		$this->viewAssignEscaped ( 'v2', $v2 );
		$this->viewAssignEscaped ( 'v3', $v3 );
		$this->viewAssignEscaped ( 'v4', $v4 );
		$this->viewAssignEscaped ( 'v5', $v5 );
		$this->viewAssignEscaped ( 'v6', $v6 );
		$this->viewAssignEscaped ( 'p',  $p);
		$this->viewAssignEscaped ( 'd',  $d);
		$this->viewAssignEscaped ( 's',  $s);
		$this->viewAssignEscaped ( 'f',  $f);
	}


	public function employeesAction() {
		require_once ('models/table/Helper.php');
		require_once ('views/helpers/FormHelper.php');
		require_once ('views/helpers/DropDown.php');
		require_once ('views/helpers/Location.php');
		require_once ('views/helpers/CheckBoxes.php');
		require_once ('views/helpers/TrainingViewHelper.php');

		$criteria = $this->getAllParams();
		
		$HOURS_FOR_FULL_WORK_WEEK = 40.0;
		
		if ($criteria['go'])
		{
			$where = array();
#todo is_Deleted not implemented
			$criteria['last_selected_rgn'] = regionFiltersGetLastID('', $criteria); // prefix, $criteria
			list($a, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			list($locationFlds, $locationsubquery) = Location::subquery($this->setting('num_location_tiers'), $location_tier, $location_id);
			$sql = "SELECT
					partner.*,
					employee.*,".implode(',',$locationFlds)."
					,qual.qualification_phrase as staff_cadre
					,facility.facility_name
					,category.category_phrase as staff_category
					-- ,subp.partner as 'sub_partner'
					,CASE WHEN annual_cost REGEXP '[^!0-9,\.][0-9\.,]+' THEN SUBSTRING(annual_cost, 2) ELSE annual_cost END AS 'annual_cost_to_compare'
					,eto.transition_phrase
					,CASE WHEN employee.transition_confirmed = 1 THEN 'yes' WHEN employee.transition_confirmed = 0 THEN 'no' ELSE employee.transition_confirmed END AS 'transition_confirmed'
					,fto.facility_type_phrase
					,ero.role_phrase
					,pio.importance_phrase
					,funders.funding_end_date
					,employee_to_partner_to_subpartner_to_funder_to_mechanism.percentage as percentage
					,employee.comments as comments
					FROM employee LEFT JOIN ($locationsubquery) as l ON l.id = employee.location_id
					LEFT JOIN employee_qualification_option qual ON qual.id = employee.employee_qualification_option_id
					LEFT JOIN partner on partner.id = partner_id
					-- LEFT JOIN partner subp on subp.id = subpartner_id
					LEFT JOIN facility ON site_id = facility.id
					LEFT JOIN facility_type_option fto          ON fto.id = facility.type_option_id
					LEFT JOIN employee_category_option category ON category.id = employee.employee_category_option_id
					LEFT JOIN employee_transition_option eto    ON eto.id = employee.employee_transition_option_id
					LEFT JOIN employee_role_option ero          ON ero.id = employee.employee_role_option_id
					LEFT JOIN partner_importance_option pio     ON pio.id = partner.partner_importance_option_id
					-- LEFT JOIN partner_to_funder funders         ON funders.partner_id = partner.id
					LEFT JOIN partner_to_subpartner_to_funder_to_mechanism funders ON partner.id = funders.partner_id
					LEFT JOIN employee_to_partner_to_subpartner_to_funder_to_mechanism ON employee.id = employee_to_partner_to_subpartner_to_funder_to_mechanism.employee_id
					"; //annual cost regex logic is: 'if non-number followed by numbers, ex: $1234, then select substring(1) of ($1234), result: 1234'

			// restricted access?? only show partners by organizers that we have the ACL to view
			$org_allowed_ids = allowed_org_access_full_list($this); // doesnt have acl 'training_organizer_option_all'
			if ($org_allowed_ids)                              $where[] = "partner.organizer_option_id in ($org_allowed_ids)";
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)                                    $where[] = "partner.organizer_option_id in ($site_orgs) ";

			// criteria
			if ($criteria['partner_id'])                       $where[] = 'employee.partner_id = '.$criteria['partner_id'];

			if ($criteria['last_selected_rgn'])                $where[] = 'province_name is not null'; // bugfix - location subquery is not working like a inner join or whereclause, not sure why

			if ($criteria['facilityInput'])                    $where[] = 'facility.id = '.$criteria['facilityInput'];

			if ($criteria['facility_type_option_id'])          $where[] = 'facility.type_option_id = '.$criteria['facility_type_option_id'];

			if ($criteria['employee_qualification_option_id']) $where[] = 'employee_qualification_option_id = '.$criteria['employee_qualification_option_id'];

			if ($criteria['employee_category_option_id'])      $where[] = 'employee_category_option_id = '.$criteria['employee_category_option_id'];

			if ($criteria['hours_min'])                        $where[] = 'funded_hours_per_week >=' .$criteria['hours_min'];
			if ($criteria['hours_max'])                        $where[] = 'funded_hours_per_week <=' .$criteria['hours_min'];

			if ($criteria['cost_min'])                         $where[] = 'annual_cost_to_compare =' .$criteria['cost_min'];
			if ($criteria['cost_max'])                         $where[] = 'annual_cost_to_compare =' .$criteria['cost_max'];

			if ($criteria['employee_role_option_id'])          $where[] = 'employee_role_option_id = '.$criteria['employee_role_option_id'];

			if ($criteria['partner_importance_option_id'])     $where[] = 'partner.partner_importance_option_id = ' .$criteria['partner_importance_option_id'];

			if ($criteria['start_date'])                       $where[] = 'funding_end_date >= \''.$this->_date_to_sql( $criteria['start_date'] ) .' 00:00:00\'';
			if ($criteria['end_date'])                         $where[] = 'funding_end_date <= \''.$this->_date_to_sql( $criteria['end_date'] ) .' 23:59:59\'';

			if ($criteria['employee_transition_option_id'])    $where[] = 'employee.employee_transition_option_id = ' .$criteria['employee_transition_option_id'];

			if ($criteria['transition_confirmed'])             $where[] = 'employee.transition_confirmed = 1';

			// BS:8,9:20141013 add employee per-mechanism hours and costs
			if ($criteria['hours_per_mechanism_min'] || $criteria['hours_per_mechanism_max'] || $criteria['employee_cost_per_mechanism_min'] || $criteria['employee_cost_per_mechanism_max']) 
			{
			    $where[] = 'percentage > 0';
			}
			
			if ($criteria['hours_per_mechanism_min'])          $where[] = 'percentage >= '.($criteria['hours_per_mechanism_min']/$HOURS_FOR_FULL_WORK_WEEK) * 100;
			if ($criteria['hours_per_mechanism_max'])          $where[] = 'employee_to_partner_to_subpartner_to_funder_to_mechanism.percentage <= '.($criteria['hours_per_mechanism_max']/$HOURS_FOR_FULL_WORK_WEEK) * 100;
			
			if ($criteria['employee_cost_per_mechanism_min'])  $where[] = 'employee_to_partner_to_subpartner_to_funder_to_mechanism.percentage >= ('.$criteria['employee_cost_per_mechanism_min'].'/employee.annual_cost) * 100';
			if ($criteria['employee_cost_per_mechanism_max'])  $where[] = 'employee_to_partner_to_subpartner_to_funder_to_mechanism.percentage <= ('.$criteria['employee_cost_per_mechanism_max'].'/employee.annual_cost) * 100';
			
			if ( count ($where) ){
				$sql .= ' WHERE ' . implode(' AND ', $where);
			}

			$sql .= ' GROUP BY employee.id ';

			$db = $this->dbfunc();
			$rowArray = $db->fetchAll( $sql );
			$this->viewAssignEscaped ('results', $rowArray );


			$locations = Location::getAll("2");

			// hack #TODO - seems Region A -> ASDF, Region B-> *Multiple Province*, Region C->null Will not produce valid locations with Location::subquery
			// the proper solution is to add "Default" districts under these subdistricts, not sure if i can at this point the table is 12000 rows, todo later
			foreach ($rowArray as $i => $row) {
				if ($row['province_name'] == "" && $row['location_id']){ // empty province
					$updatedRegions = Location::getCityandParentNames($row['location_id'], $locations, $this->setting('num_location_tiers'));
					$rowArray[$i] = array_merge($row, $updatedRegions);
				}
			}

			$this->view->assign ('count', count($rowArray) );

			if ($criteria ['outputType']) {
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
			}
		}


		// assign form drop downs
		$this->view->assign( 'status',   $status );
		$this->view->assign( 'criteria', $criteria );
		$this->view->assign ( 'pageTitle', t('Reports'));
		$this->viewAssignEscaped ( 'locations', $locations );
		$this->view->assign ( 'partners',    DropDown::generateHtml ( 'partner', 'partner', $criteria['partner_id'], false, $this->view->viewonly, false ) ); //table, col, selected_value
		$this->view->assign ( 'importance',  DropDown::generateHtml ( 'partner_importance_option', 'importance_phrase', $criteria['partner_importance_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'transitions', DropDown::generateHtml ( 'employee_transition_option', 'transition_phrase', $criteria['employee_transition_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'incomingPartners', DropDown::generateHtml ( 'partner', 'partner', $criteria['incoming_partner'], false, $this->view->viewonly, false, true, array('name' => 'incoming_partner'), true ) );
		$helper = new Helper();
		$this->viewAssignEscaped ( 'facilities', $helper->getFacilities() );
		$this->view->assign ( 'facilitytypes', DropDown::generateHtml ( 'facility_type_option', 'facility_type_phrase', $criteria['facility_type_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'cadres',        DropDown::generateHtml ( 'employee_qualification_option', 'qualification_phrase', $criteria['employee_qualification_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'categories',    DropDown::generateHtml ( 'employee_category_option', 'category_phrase', $criteria['employee_category_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'roles',         DropDown::generateHtml ( 'employee_role_option', 'role_phrase', $criteria['employee_role_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'transitions',   DropDown::generateHtml ( 'employee_transition_option', 'transition_phrase', $criteria['employee_transition_option_id'], false, $this->view->viewonly, false ) );
	}

	public function partnersAction() {
		require_once ('models/table/Helper.php');
		require_once ('views/helpers/FormHelper.php');
		require_once ('views/helpers/DropDown.php');
		require_once ('views/helpers/Location.php');
		require_once ('views/helpers/CheckBoxes.php');
		require_once ('views/helpers/TrainingViewHelper.php');

		$criteria = $this->getAllParams();

		if ($criteria['go'])
		{

			$where = array();
			$criteria['last_selected_rgn'] = regionFiltersGetLastID('', $criteria);
			list($a, $location_tier, $location_id) = $this->getLocationCriteriaValues($criteria);
			list($locationFlds, $locationsubquery) = Location::subquery($this->setting('num_location_tiers'), $location_tier, $location_id);

			$selCategories = $criteria['show_cadres'] ? ',GROUP_CONCAT(distinct ec.qualification_phrase) as cadres ' : "";


			$sql = "SELECT
					partner.*,
					partner.id,partner.partner,partner.location_id,".implode(',',$locationFlds)."
					,GROUP_CONCAT(distinct facility.facility_name) as facilities
					,CASE WHEN annual_cost REGEXP '[^!0-9,\.][0-9\.,]+' THEN SUBSTRING(annual_cost, 2) ELSE annual_cost END AS 'annual_cost_to_compare'
					,COUNT(distinct e.id) AS pcnt
					$selCategories
					FROM partner LEFT JOIN ($locationsubquery) as l ON l.id = partner.location_id
					LEFT JOIN partner_to_subpartner_to_funder_to_mechanism funders ON partner.id = funders.partner_id
					LEFT JOIN partner_funder_option funderopt ON funders.partner_funder_option_id = funderopt.id
					-- LEFT JOIN partner_to_subpartner subpartners ON subpartners.partner_id = partner.id
					LEFT JOIN employee e on e.partner_id = partner.id
					LEFT JOIN facility ON e.site_id = facility.id";
#todo is_deleted not implemented
			if ($criteria['facility_type_option_id']) $sql .= " LEFT JOIN facility_type_option fto ON fto.id = facility.type_option_id ";
			if ($criteria['show_cadres'])             $sql .= " LEFT JOIN employee_qualification_option ec ON ec.id = e.employee_qualification_option_id ";

			// restricted access?? only show partners by organizers that we have the ACL to view
			$org_allowed_ids = allowed_org_access_full_list($this); // doesnt have acl 'training_organizer_option_all'
			if ($org_allowed_ids)                             $where[] = "partner.organizer_option_id in ($org_allowed_ids)";
			// restricted access?? only show organizers that belong to this site if its a multi org site
			$site_orgs = allowed_organizer_in_this_site($this); // for sites to host multiple training organizers on one domain
			if ($site_orgs)                                   $where[] = "partner.organizer_option_id in ($site_orgs)";

			// criteria
			if ($criteria['partner_id'])                      $where[] = 'partner.id = '.$criteria['partner_id'];

			if ($criteria['last_selected_rgn'])               $where[] = 'province_name is not null'; // bugfix - location subquery is not working like a inner join or where, not sure why

			if ($criteria['facilityInput'])                   $where[] = 'facility.id = '.$criteria['facilityInput'];

			if ($criteria['facility_type_option_id'])         $where[] = 'facility.type_option_id = '.$criteria['facility_type_option_id'];

			if ($criteria['employee_qualification_option_id'])$where[] = 'employee_qualification_option_id = '.$criteria['employee_qualification_option_id'];

			if ($criteria['employee_category_option_id'])     $where[] = 'employee_category_option_id = '.$criteria['employee_category_option_id'];

			if ($criteria['hours_min'])                       $where[] = 'e.funded_hours_per_week >=' .$criteria['hours_min'];
			if ($criteria['hours_max'])                       $where[] = 'e.funded_hours_per_week <=' .$criteria['hours_min'];

			if ($criteria['cost_min'])                        $where[] = 'e.annual_cost_to_compare >=' .$criteria['cost_min'];
			if ($criteria['cost_max'])                        $where[] = 'e.annual_cost_to_compare <=' .$criteria['cost_max'];

			#TODO: marking EMPLOYEE ROLE, TRANSITION CONFIRMED, START_DATE, END_DATE as TO BE REMOVED at clients request. these are disabled in the view
			if ($criteria['employee_role_option_id'])         $where[] = 'funding_end_date >= \''.$this->_date_to_sql( $criteria['start_date'] ) .' 00:00:00\'';

			if ($criteria['partner_importance_option_id'])    $where[] = 'partner_importance_option_id = ' .$criteria['partner_importance_option_id'];

			if ($criteria['start_date'])                      $where[] = 'funding_end_date >= \''.$this->_date_to_sql( $criteria['start_date'] ) .' 00:00:00\'';

			if ($criteria['end_date'])                        $where[] = 'funding_end_date <= \''.$this->_date_to_sql( $criteria['end_date'] ) .' 23:59:59\'';

			if ($criteria['employee_transition_option_id'])   $where[] = 'employee_transition_option_id = ' .$criteria['employee_transition_option_id'];

			if ($criteria['transition_confirmed'])            $where[] = 'transition_confirmed = 1';

			if ( count ($where) )
				$sql .= ' WHERE ' . implode(' AND ', $where);

			$sql .= ' GROUP BY partner.id ';

			$db = $this->dbfunc();
			$rowArray = $db->fetchAll( $sql );
			$this->viewAssignEscaped ('results', $rowArray );
			$this->view->assign ('count', count($rowArray) );

			if ($criteria ['outputType']) {
				$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
			}
		}


		// assign form drop downs
		$this->view->assign ( 'status',   $status );
		$this->view->assign ( 'criteria', $criteria );
		$this->view->assign ( 'pageTitle', t('Reports'));
		$this->viewAssignEscaped ( 'locations', Location::getAll () );
		$this->view->assign ( 'partners',    DropDown::generateHtml ( 'partner', 'partner', $criteria['partner_id'], false, $this->view->viewonly, false ) ); //table, col, selected_value
		$this->view->assign ( 'subpartners', DropDown::generateHtml ( 'partner', 'partner', $criteria['subpartner_id'], false, $this->view->viewonly, false, true, array('name' => 'subpartner_id'), true ) );
		$this->view->assign ( 'importance',  DropDown::generateHtml ( 'partner_importance_option', 'importance_phrase', $criteria['partner_importance_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'transitions', DropDown::generateHtml ( 'employee_transition_option', 'transition_phrase', $criteria['employee_transition_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'incomingPartners', DropDown::generateHtml ( 'partner', 'partner', $criteria['incoming_partner'], false, $this->view->viewonly, false, true, array('name' => 'incoming_partner'), true ) );
		$helper = new Helper();
		$this->viewAssignEscaped ( 'facilities', $helper->getFacilities() );
		$this->view->assign ( 'facilitytypes', DropDown::generateHtml ( 'facility_type_option', 'facility_type_phrase', $criteria['facility_type_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'cadres',        DropDown::generateHtml ( 'employee_qualification_option', 'qualification_phrase', $criteria['employee_qualification_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'categories',    DropDown::generateHtml ( 'employee_category_option', 'category_phrase', $criteria['employee_category_option_id'], false, $this->view->viewonly, true ) );
		$this->view->assign ( 'roles',         DropDown::generateHtml ( 'employee_role_option', 'role_phrase', $criteria['employee_role_option_id'], false, $this->view->viewonly, false ) );
		$this->view->assign ( 'transitions',   DropDown::generateHtml ( 'employee_transition_option', 'transition_phrase', $criteria['employee_transition_option_id'], false, $this->view->viewonly, false ) );
	}


  public function mechanismsAction() {
	require_once ('models/table/Helper.php');
	require_once ('views/helpers/FormHelper.php');
	require_once ('views/helpers/DropDown.php');
	require_once ('views/helpers/Location.php');
	require_once ('views/helpers/CheckBoxes.php');
	require_once ('views/helpers/TrainingViewHelper.php');

	$criteria = $this->getAllParams();
	
	// file_put_contents('c:\wamp\logs\php_debug.log', 'repCont 10911>'.PHP_EOL, FILE_APPEND | LOCK_EX);	ob_start();
	// var_dump($criteria);
	// $result = ob_get_clean(); file_put_contents('c:\wamp\logs\php_debug.log', $result .PHP_EOL, FILE_APPEND | LOCK_EX);

	if ($criteria['go'])
	{

		$where = array();
		
		switch ($criteria['report']) {
			case "defined":
				
				$sql = "
select sfm.id, subp.partner as subpartner, funder_phrase, mechanism_phrase, sfm.funding_end_date
from subpartner_to_funder_to_mechanism sfm
left join partner subp on subp.id = sfm.subpartner_id
left join partner_funder_option pf on pf.id = sfm.partner_funder_option_id
left join mechanism_option m on m.id = sfm.mechanism_option_id
";
				break;
				
			case "definedByPartner":
				
				$sql = "
select psfm.id, p.partner, subp.partner as subpartner, funder_phrase, mechanism_phrase, psfm.funding_end_date
from partner_to_subpartner_to_funder_to_mechanism psfm
left join partner p on p.id = psfm.partner_id
left join partner subp on subp.id = psfm.subpartner_id
left join partner_funder_option pf on pf.id = psfm.partner_funder_option_id
left join mechanism_option m on m.id = psfm.mechanism_option_id
";
				break;
				
			case "definedByEmployee":
				
				$sql = "
select epsfm.id, e.employee_code, p.partner, subp.partner as subpartner, funder_phrase, mechanism_phrase, epsfm.percentage
from employee_to_partner_to_subpartner_to_funder_to_mechanism epsfm
left join employee e on e.id = epsfm.employee_id
left join partner p on p.id = epsfm.partner_id
left join partner subp on subp.id = epsfm.subpartner_id
left join partner_funder_option pf on pf.id = epsfm.partner_funder_option_id
left join mechanism_option m on m.id = epsfm.mechanism_option_id
";
				break;
		}
		
		
				// criteria
		if ($criteria['partner_id'] && $criteria['report'] != 'defined' ) $where[] = 'p.id = '.$criteria['partner_id'];
		if ($criteria['subpartner_id'])                   $where[] = 'subp.id = '.$criteria['subpartner_id'];

		if ($criteria['start_date'])                      $where[] = 'funding_end_date >= \''.$this->_date_to_sql( $criteria['start_date'] ) .' 00:00:00\'';

		if ($criteria['end_date'])                        $where[] = 'funding_end_date <= \''.$this->_date_to_sql( $criteria['end_date'] ) .' 23:59:59\'';


		
		switch ($criteria['report']) {
			case "defined":
				if ( count ($where) ){
					$sql .= ' WHERE ' . implode(' AND ', $where);
					$sql .= ' AND sfm.is_deleted = false ';
				}
				else  $sql .= ' WHERE sfm.is_deleted = false ';
				$sql .= ' order by subp.partner, funder_phrase, mechanism_phrase ';
				break;
			case "definedByPartner":
				if ( count ($where) ){
					$sql .= ' WHERE ' . implode(' AND ', $where);
					$sql .= ' AND psfm.is_deleted = false ';
				}
				else  $sql .= ' WHERE psfm.is_deleted = false ';
				$sql .= ' order by p.partner, subp.partner, funder_phrase, mechanism_phrase ';
				break;
			case "definedByEmployee":
				if ( count ($where) ){
					$sql .= ' WHERE ' . implode(' AND ', $where);
					$sql .= ' AND epsfm.is_deleted = false ';
				}
				else  $sql .= ' WHERE epsfm.is_deleted = false ';
				$sql .= ' order by e.employee_code, p.partner, subp.partner, funder_phrase, mechanism_phrase ';
				break;
		}

		$db = $this->dbfunc();
		$rowArray = $db->fetchAll( $sql );
		$this->viewAssignEscaped ('results', $rowArray );
		$this->view->assign ('count', count($rowArray) );

		if ($criteria ['outputType']) {
			$this->sendData ( $this->reportHeaders ( false, $rowArray ) );
		}
	}

    $sql = '';
	// assign form drop downs
	$this->view->assign ( 'status',   $status );
	$this->view->assign ( 'criteria', $criteria );
	$this->view->assign ( 'pageTitle', t('Reports'));
	$this->view->assign ( 'report', $criteria['report']);
	
	$this->view->assign ( 'partners',    DropDown::generateHtml ( 'partner', 'partner', $criteria['partner_id'], false, $this->view->viewonly, false ) ); //table, col, selected_value
	$this->view->assign ( 'subpartners', DropDown::generateHtml ( 'partner', 'partner', $criteria['subpartner_id'], false, $this->view->viewonly, false, true, array('name' => 'subpartner_id'), true ) );
	
  }
}
