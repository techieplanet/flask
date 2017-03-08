<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MetricDataCollector
 *
 * @author Swedge
 */
require_once 'mongodb_php_lib/vendor/autoload.php';

class MetricDataCollector {
    //put your code here
    /**
     * @param type $metricData The json coming from the client. Contains the metric data to log.
     * @return int
     */
    function setMetric($metricData){
            if(isset($metricData)){
                $mongoConn = new MongoDB\Client("mongodb://localhost:27017");
                //$mongoConn = new MongoDB\Client("mongodb://63.139.189.115:27017");
                $collection = $mongoConn->analytics->metrics;    //select database and collection

                $arr = json_decode($metricData, TRUE);
                $arr['mtimestamp'] = new MongoDB\BSON\UTCDateTime(floor(microtime(true) * 1000));
                $insertOneResult = $collection->insertOne($arr);
                
                return 1;
            }
            
            return 0;
    }
    
    
    //                if(array_key_exists("mtimestamp", $queryArray)){
//                    foreach($queryArray as $key=>$value){
//                        if($key == 'mtimestamp'){
//                            $innerArray = $value;
//                            reset($innerArray);
//                            $innerKey = key($innerArray);
//                            $innerArray[$innerKey] = new MongoDB\BSON\UTCDateTime($innerArray[$inner]);
//                        }
//                    }
//                }
//                        
    
    function getMetric($queryArray, $optionsArray, $operationKeyword, $tableName){
            if( (!empty($operationKeyword) ) ){
                $mongoConn = new MongoDB\Client("mongodb://localhost:27017");
                
                $collection = $mongoConn->analytics->$tableName;    //select database and collection

                $paramsArray = json_decode($queryArray, TRUE);
                $projectionArray = json_decode($optionsArray, TRUE);
                $resultSet = $collection->$operationKeyword($paramsArray, $projectionArray);
                
                return json_encode(iterator_to_array($resultSet));
            
            }
            
            return 'Unsuccesful: You might have sent a wrong set of parameters or you parameters have incorrect values';
    }
    
    
    function dumpData($dataToDump, $tablename){
            if( (!empty($dataToDump)) && (!empty($tablename)) ){
                $dataArray = json_decode($dataToDump, TRUE);
                
                $mongoConn = new MongoDB\Client("mongodb://localhost:27017");
                
                $mongoConn->analytics->$tablename->drop();
                
                $collection = $mongoConn->analytics->$tablename;    //select database and collection

                $insertResult = $collection->insertMany($dataArray);
                
                return 1;
            }
            
            return 0;
    }
}

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

date_default_timezone_set("Africa/Lagos");

$server = new SoapServer("MetricCollectorService.wsdl");
$server->setClass("MetricDataCollector");
$server->handle();

//$document = array( 
//  "title" => "MongoDB-PHP", 
//  "description" => "Soap Coming", 
//  "likes" => 22200,
//  "url" => "http://www.tutorialspoint.com/mongodb/",
//  "by", "TP Tuts"
//);
//
//$m = new MetricDataCollector();
//$m->setMetricData($document);
