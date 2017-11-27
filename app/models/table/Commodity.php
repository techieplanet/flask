<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Commodity
 *
 * @author swedge-mac
 */
class Commodity {
    //put your code here
    
    /**
     * 
     * @param Array $commodityIDList
     * @return Array - id/commodity name map
     */
    function getCommodityMap($commodityIDList){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter ();
        
        if(empty($commodityIDList))
            $commodityIDList = $this->getCommodityNames ('', TRUE);
       else
           $commodityIDList = implode(',', $commodityIDList);
            
        
        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('id', 'commodity_name'))
                     ->where('cno.id IN (' . $commodityIDList . ')')
                     ->order(array('display_order'));

        $result = $db->fetchAll($select);
        
        $commArrayMap = [];
        foreach($result as $commodity)
            $commArrayMap[$commodity['id']] = $commodity['commodity_name'];
        
        return $commArrayMap;
    }
    
    public function getCommodityName($commID){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($commID == 0) return '';

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('commodity_name'))
                     ->where('cno.id = ' . $commID);

        $result = $db->fetchRow($select);
        return $result['commodity_name'];            
    }

    public function getCommodityByAlias($alias){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($alias == "") return '';

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('*'))
                     ->where('cno.commodity_alias = ' . "'$alias'");

        $result = $db->fetchRow($select);
        return $result;
    }
    
    //get the commodity full info
    public function getCommodity($commID){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($commID == 0) return '';

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('*'))
                     ->where('cno.id = ' . $commID);

        $result = $db->fetchRow($select);
        return $result;            
    }
    
    /**
     * Get full info of commodities
     * @param type $commodityIDList
     * @return Array commodity list
     */
    public function getCommoditiesByID($commodityIDList){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if(empty($commodityIDList)) return [];

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('*'))
                     ->where('cno.id IN (' . implode(',', $commodityIDList) . ')');

        $result = $db->fetchRow($select);
        return $result;            
    }


    
    public function getCommodities($commodity_type=''){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($commodity_type =='fp')
            $commodityWhere = "commodity_type = 'fp'";
        else if($commodity_type == 'larc')
            $commodityWhere = "commodity_type = 'larc'";
        else
            $commodityWhere = "commodity_type = 'fp' OR commodity_type = 'larc'";

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('id', 'commodity_name'))
                     ->where($commodityWhere)
                     ->order(array('display_order'));

        $result = $db->fetchAll($select);
        return $result;
    }

    /**
     * Returns either commodity names or commoditiy keys
     * @param type $commodity_type
     * @param type $keysOnly
     * @return String (comma separated)
     */
    public function getCommodityNames($commodity_type='', $keysOnly = false){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if($commodity_type =='fp')
            $commodityWhere = "commodity_type = 'fp'";
        else if($commodity_type == 'larc')
            $commodityWhere = "commodity_type = 'larc'";
        else
            $commodityWhere = "commodity_type = 'fp' OR commodity_type = 'larc'";

        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('id', 'commodity_name'))
                     ->where($commodityWhere)
                     ->order(array('display_order'));

        $result = $db->fetchAll($select);

        $values ='';
        if($keysOnly){
            foreach($result as $row)
                $values .=  "'" . $row['id'] . "',";
                $values = substr($values, 0, -1);
        }
        else{
             foreach($result as $row)
                 $values .=  $row['commodity_name'] . ",";
             $values = substr($values, 0, -1);
        }

        return $values;
    }
    
    
    /**
     * Returns either commodity names or commodity keys
     * @param type $commodityIDList
     * @param type $keysOnly
     * @return String (comma separated)
     */
    public function getCommodityNamesByID($commodityIDList, $keysOnly = false){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $select = $db->select()
                     ->from(array('cno'=>'commodity_name_option'), array('id', 'commodity_name'))
                     ->where('cno.id IN (' . implode(',', $commodityIDList). ')')
                     ->order(array('display_order'));

        $result = $db->fetchAll($select);

        $values ='';
        if($keysOnly){
            foreach($result as $row)
                $values .=  "'" . $row['id'] . "',";
            
            $values = substr($values, 0, -1);
        }
        else{
             foreach($result as $row)
                 $values .=  $row['commodity_name'] . ",";
             
             $values = substr($values, 0, -1);
        }

        return $values;
    }
}
