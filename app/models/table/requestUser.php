<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of requestUser
 *
 * @author TP
 */
class requestUser {
    //put your code here
    
    public function updateRequestUser($tableName,$data,$where){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         try{
           
             $update = $db->update($tableName, $data, $where);
             
             return $update;
             
         }catch (Exception $ex){
             print $ex->getMessage();exit;
         }
         
    }
    
     public function insertRequestUser($tableName,$data){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         try{
           
             $insert = $db->insert($tableName, $data);
             $id = $db->lastInsertId($tableName);
             return $id;
             
         }catch (Exception $ex){
             print $ex->getMessage();exit;
         }
         
    }
    
    public function selectRequestUser($tableName,$where){
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         try{
           
             if(!empty($where)){
             $select = $db->select()
                     ->from($tableName)
                     ->where($where)
                     ->order("req_id DESC");
             }else{
                 $select = $db->select()
                     ->from($tableName)
                     ->order("req_id DESC");
             }
             $result = $db->fetchAll($select);
             return $result;
             
         }catch (Exception $ex){
             print $ex->getMessage();exit;
         }
         
    }
    public function statusLink($status){
        $placeHolder = "";
        if($status==-1 || $status=="-1"){
           $placeHolder = "Rejected";
        }
        else if($status==0 || $status=="0"){
            $placeHolder = "<a href=''>Accept</a>&nbsp;&nbsp;&nbsp;<a href=''>Decline</a>";
        }
        else if($status==1 || $status="1"){
            $placeHolder = "Approved";
        }
        return $placeHolder;
    }
}
