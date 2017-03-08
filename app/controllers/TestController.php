<?php 
require_once('UserController.php');
require_once('models/table/OptionList.php');
require_once('models/table/Helper.php');
require_once('EditTableController.php');
require_once ('views/helpers/DropDown.php');
require_once ('views/helpers/CheckBoxes.php');

class TestController extends UserController
{       
    
    private $_csvHandle = null;



	public function init()

	{

		$this->view->assign('pageTitle', t('Administration'));

	}

        
         public function indexAction()    
        {            
    
        }

	public function preDispatch()

	{

		$rtn =	parent::preDispatch();



		if ( !$this->isLoggedIn() )

		$this->doNoAccessError();



		if ( ! $this->hasEditorACL() && ! $this->hasACL('edit_country_options') )

			$this->doNoAccessError();



		return $rtn;



	}
    
   

}