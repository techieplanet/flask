<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExcelValidator
 *
 * @author Prestige
 */
class ExcelValidator {
    
    public $rows;
    public $status;
    public $values;
    public $values_person;
    
    public function __construct($excelData, $validationContainerObj,$values,$values_person) {
        $this->rows = $excelData;
        $this->status = $validationContainerObj;
        $this->values = $values;
        $this->values_person = $values_person;
    }
    
    public function validateTrainingDetails(){
        
        $rows = $this->rows;
        
        $this->values['training_organizer_phrase'] = $rows[9][2];
        $this->values['training_organizer_option_id'] = 0;
        
        //Validate Training End Date
        if(!trim($rows[11][3])){
                $this->status->addError( 'end-day', 'Your changes has not been saved: End date is required.' );
        }else{
           list($ye,$me,$de) = $this->status->dateFormatter($rows[11][3]);
           if($ye=="")
               $ye="0000";

           if($me=="")
               $me="00";

           if($de=="")
               $de ="00";
           
            $this->values['training_end_date'] =  $ye . '-' . $me . '-' . $de;
            $true = $this->status->isValidDate ( $this, 'end-day','Your changes have been not saved: Training end date', $this->values['training_end_date'] );

        }
        
        //Validate Start Date
        if(trim($rows[10][3]) !== ''){ 
            
            list($ys,$ms,$ds) = $this->status->dateFormatter($rows[10][3]);

            $this->values['training_start_date'] =  $ys . '-' . $ms . '-' . $ds;
            $this->status->isValidDate ( $this, 'start-day','Your changes have been not saved: Training start date', $this->values['training_start_date'] );
            if (strtotime ($this->values['training_end_date'] ) < strtotime ( $this->values['training_start_date'] )) {

                    $this->status->addError ( 'end-day', t ( 'Your changes have been not saved: End date must be after start date.' ) );
            }
        }
        else
        {
            $this->values['training_start_date'] = $this->values['training_end_date'];
        }
        
        
        //Validate Training type
        $this->values['training_title_option_id'] = 0;

        for($i=14; $i<31; $i++){
                if(! empty($rows[$i][3])){
                        $this->values['training_title_option_id'] = $rows[$i][2];
                        break;
                }
        }
        
        if(!trim($this->values['training_title_option_id'])){
                $this->status->addError ( 'title_option_id', 'Your changes have not been saved: Type of training is required.'  );

        }
        
        
        //Validate training level
        $this->values['training_level_option_id'] = 0;
        
        for($v=32; $v<35; $v++){
            
            if(!empty($rows[$v][3])){
                
                if($v==32){
                    $id = '1';
                }else if($v==33){
                    $id ='2'; 
                }else if($v==34){
                    $id = '3';
                }

                if($rows[$v][2]=="Master training"){
                  $this->values['training_level_option_id'] = $id;

                }else if($rows[$v][2]=="Training of trainer (TOT)"){
                    $this->values['training_level_option_id'] = $id;
                }else if($rows[$v][2]=="Cascade training"){
                    $this->values['training_level_option_id'] = $id;
                }
                break;
            }
        }
        
        $this->values_person['training_level_option_id'] = trim($this->values['training_level_option_id']);
        
        
        if(!$this->values['training_level_option_id']){
                $this->status->addError ( 'training_level_option_id', 'Your changes have not been saved: Level of training is required. '.$rows[34][2]  );
        }

        if(empty($rows[12][2])){
            $this->status->addError ( 'training_location_id', 'Your changes have not been saved: Training Location is Required. '.$rows[12][2]  );
        }
        
        
        return array($this->status,$this->values,$this->values_person);
        
    }
    
    
    
}
