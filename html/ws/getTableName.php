<?php

function getTableSchema()
{

$dbname = "techie17_fp2";
$dbuser = "techie17_user50";
$dbpass = "user50pass";
$dbhost = "localhost";


/**
$dbname = "traindb";
$dbuser = "root";
$dbpass = "";
$dbhost = "localhost";
**/

$db  = new mysqli($dbhost,$dbuser,$dbpass,$dbname);

$sql = "show full tables where Table_Type = 'VIEW'";

$query = $db->query($sql);

$checkList = array('commodity150116','commodity','facility_report_rate','facility_report_rate150116');

$tables = array();

while($result = mysqli_fetch_array($query))
{
	$found = false;
	
	foreach($checkList as $val)
	{
	  if($val == $result[0])
		  $found = true;
	  
	 
	}
	
	if(!$found)
		 $tables[] = $result[0];
}

$db->close();
copyView($tables);
//var_dump($tables);
}

/***
* Table copy starts here
***/



 function tableCopy($tables)
 {
$dbname_old = "traindb";
$dbuser = "root";
$dbpass = "";
$dbhost = "localhost";

$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname_old);

	if($db->connect_errno)
	{
	 
		echo "Cannot connect " . $db->connect_error;
	 
	}
	else
	{

	 $count = 1;
		foreach($tables as $table)	
		{ 
		  
			
			$sql_create = "create table traindb_new.$table like traindb.$table";
			$sql_insert = "insert into traindb_new.$table select * from traindb.$table";
	 
			$db->query($sql_create);
			$db->query($sql_insert);
	 
	 $count++;
		}

	}
	
	echo "completed, $count Tables copied";
 }
 
 function copyView($tables)
 {
	
	$dbname = "techie17_fp2";
	$dbuser = "techie17_user50";
	$dbpass = "user50pass";
	$dbhost = "localhost";
	
	
	/**
	$dbname = "traindb";
	$dbuser = "root";
	$dbpass = "";
	$dbhost = "localhost";
	**/

$db  = new mysqli($dbhost,$dbuser,$dbpass,$dbname);

$viewQuerys = array();

	$count = 0;
     foreach($tables as $table)
	 {
		 $sql_create = "show create view $dbname.$table";
		 $query = $db->query($sql_create);
		 
		 while($result = mysqli_fetch_array($query))
		 {
		 $viewQuerys[] = $result[1];
		 }
		 $count++;
	 }
	 
	 $db->close();
	 
	 /***
	 *creating new views 
	 ***/
	 
	
	 $viewQuerys =  replaceViewDefiner($viewQuerys);
	 
	 $db = new mysqli($dbhost,$dbuser,$dbpass,'techie17_fp2_new');
	 
	$count = 0;

	 foreach($viewQuerys as $viewQuery)
	 {
		 
		 try
		 {
			$db->query($viewQuery);
		 }
		 catch(Exception $err)
		 {
			 echo $err->getMessage() . "<br/>";
		 }
		 
		 if($db->connect_errno)
		 {
			 echo "Creating view error : " . $db->connect_error . "<br />";
		 }

	   $count++;
	 }
	
	 //var_dump($viewQuerys);
	echo "Created $count views <br />";
	
     
 }
 
 
 function getViewList()
 {
	 /**
	$dbname = "traindb_new";
	$dbuser = "root";
	$dbpass = "";
	$dbhost = "localhost";
	**/
	
	$dbname = "techie17_fp2_new";
	$dbuser = "techie17_user50";
	$dbpass = "user50pass";
	$dbhost = "localhost";
	
	$views = array();
	
	$db = new mysqli($dbhost,$dbuser,$dbpass,$dbname);
	
	$sql = "show full tables where table_type = 'VIEW'";
	
	try
	{
		$query = $db->query($sql);
	}
	catch(Exception $err)
	{
		echo $err->getMessage() . "<br />";
	}
	
	while($result = mysqli_fetch_array($query))
	{
		$views[] = $result[0];
	}
	
	$count = count($views);
	echo "found $count views <br />";
	
 }
 
 
 function replaceViewDefiner($viewQuerys)
 {
	 $tempArray = array();
	 
	 foreach($viewQuerys as $viewQuery)
	 {
		 $viewQuery = str_replace("ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER","",$viewQuery);
		 $tempArray[] = $viewQuery;
	 }
	 
	 return $tempArray;
 }
 
getTableSchema();
getViewList();

?>