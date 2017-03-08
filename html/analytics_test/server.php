<?php

require_once 'jsonrpcserver.php';
require "vendor/autoload.php";

class GetSearch {

	public function saveSearch($obj)
	{
		$connection = new MongoDB\Client("mongodb://localhost:27017");
  		//select mongoDB database and table
  		$collection = $connection->fpdashboard_db->analytics;

  		
  		$result = $collection->insertOne($obj);
		
		file_put_contents('techieFile.log', $obj, FILE_APPEND);
		file_put_contents('techieFile.log', PHP_EOL, FILE_APPEND);
		return 200;
	}
}

$objClass = new GetSearch();

jsonRPCServer::handle($objClass) or print 'no request';

?>