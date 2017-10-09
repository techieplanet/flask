<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 * This is a REST API file. It will be called directly from the Data donwloader REST file 
 */


class ExcelUpdateManager{
    public $fileNamePrefix;
    
    public function __construct($fileNamePrefix) {
        $this->fileNamePrefix = $fileNamePrefix;
    }
    
    public function run(){
        $newFacilities = $this->readNewFacilities();
        
        foreach($newFacilities as $facility){ //$facility[id, facility_external_id, facility_name, state, lga]
            $inputFileName = $this->fileNamePrefix . $facility['state'] . '.xlsx';
            
            $sheet = $this->findDataSheet($inputFileName);
            $column = $this->findDataColumn($sheet, $facility['lga']);
            $colData = $this->getColumnData($sheet, $column);
            
            $colData[] = $facility['facility_name']; //append the new facility name
            sort($colData); //sort the array
            
            $this->writeDataToColumn($sheet, $column, $colData, $inputFileName);
         }
    }
    
    /** Section 1: Reader
     *   Reads all temp data from table: temp_new_facility
     *  We need facility name, state name, lga name 
     */
    public function readNewFacilities(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
                                ->from(array('f'=>'facility'), array('f.id'))
                                ->joinInner(array('tf' => 'temp_new_facility'), 'f.external_id = tf.facility_external_id', array('tf.facility_external_id'))
                                ->joinInner(array('flv'=>'facility_location_view'), 'f.id=flv.id', array('facility_name', 'state', 'lga'));
        //echo $select->__toString(); exit;
        $result = $db->fetchAll($select);
        return $result;
    }
    
    /**
     * Find the data sheet we need to work with
     * @param type $facilityDetails
     */
    public function findDataSheet($inputFileName){
        $objReader = new PHPExcel_Reader_Excel2007();
        //$objReader->setLoadSheetsOnly('Dropdowns'); 
        $objPHPExcel = $objReader->load($inputFileName);
        
        //load the sheet
        return $sheet = $objPHPExcel->getSheet(1);
    }
    
    
    /** 
     * Locate the column the data is to be inserted into
     * @param type $facilityDetails an array containing fac name, state and lga of the new facility
     * @return type
     */
    public function findDataColumn($sheet,$lga){
        $highestColumn = $sheet->getHighestColumn(1);
        $rowData = $sheet->rangeToArray('J1:'. $highestColumn . '1', NULL, TRUE, FALSE, TRUE);        
        $rowData = $rowData[1]; //pull out the array of values
        //echo 'lga: ' . $lga . '<br>';       var_dump($rowData); exit;
        
        foreach($rowData as $col=>$col_data){
            if(strtoupper($lga) === strtoupper($col_data))
                return $col;
        }
            
        return null;
    }
    
    /**
     * Retrieve the data for the column
     * @param PHPExcel_Worksheet $sheet
     * @param type $col
     * @param type $facilityName
     */
    public function getColumnData($sheet, $col){
        //get the current column data from row 2
        $highestDataRow = $sheet->getHighestDataRow($col);
        $colData = $sheet->rangeToArray($col.'2:' . $col.$highestDataRow, NULL, TRUE, FALSE, FALSE);
        return array_map(function($v){ return $v[0]; }, $colData);
    }
    
    /**
     * Write the sorted list onto the column
     * @param PHPExcel_Worksheet $sheet
     * @param type $col
     * @param type $colData
     */
    public function writeDataToColumn($sheet, $col, $colData, $inputFileName){
        $row = 2;
        foreach($colData as $facName){
            $sheet->setCellValue($col.$row, $facName);
            $row++;
        }
        
        $objPHPExcel = $sheet->getParent();
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
        //$objWriter->save('../../templates/ImportTrainingTemplate ' . 'Abia' . '.xlsx');
        $objWriter->save($inputFileName);
    }

}




//require_once '../../../sites/globals.php';
set_include_path(get_include_path() . PATH_SEPARATOR . 
                            Globals::$BASE_PATH . 'html/ws/excel/PHPExcel-nekulin/Classes/');

require_once 'PHPExcel/IOFactory.php';
date_default_timezone_set('Africa/Lagos');