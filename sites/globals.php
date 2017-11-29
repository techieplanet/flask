<?php
/*
 * Created on Feb 11, 2008
 *
*  Built for I-Tech
*  Fuse IQ -- todd@fuseiq.com
*
*/

ini_set('max_execution_time','300');
ini_set('memory_limit', '1024M');
ini_set("error_log", "/Users/swedge-mac/dev/php/trainsmart/php_error.log");

define('space',  " ");


class Globals {
	//public static $BASE_PATH = '/home/techie17/public_html/chai/trainsmart/';
	//public static $BASE_PATH = '/web/www/trainsmart/';
    public static $BASE_PATH = 'C:/wamp64/www/trainsmart/';
	public static $WEB_FOLDER = 'html';
	public static $COUNTRY = 'test';

	public function __construct() {

		require_once('settings.php');
		// PATH_SEPARATOR =  ; for windows, : for *nix

		$iReturn = ini_set( 'include_path',
					(Globals::$BASE_PATH).PATH_SEPARATOR.
					(Globals::$BASE_PATH).'app'.PATH_SEPARATOR.
					(Globals::$BASE_PATH.'ZendFramework'.DIRECTORY_SEPARATOR.'library').PATH_SEPARATOR.
					ini_get('include_path'));
               // echo ini_get('include_path');exit;
		//echo $iReturn; exit;

		require_once 'Zend/Loader.php';

		require_once 'Zend/Db.php';
		//fixes mysterious configuration issue
		require_once('Zend/Db/Adapter/Pdo/Mysql.php');
		//set a default database adaptor
		$db = Zend_Db::factory('PDO_MYSQL', array(
			'host'     => Settings::$DB_SERVER,
			'username' => Settings::$DB_USERNAME,
			'password' => Settings::$DB_PWD,
			'dbname'   => Settings::$DB_DATABASE
		));
		 require_once 'Zend/Db/Table/Abstract.php';
		 require_once 'Zend/Debug.php';
		Zend_Db_Table_Abstract::setDefaultAdapter($db);
	}
}

new Globals();
