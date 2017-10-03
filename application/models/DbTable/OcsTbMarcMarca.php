<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbMarcMarca extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_MARC_MARCA';
    protected $_primary = 'MARC_ID_MARCA';
    protected $_sequence = 'OCS_SQ_MARC';
         
    
    public function getMarca($order)
    {
        if ( !isset($order) ) {
            $order = 'MARC_DS_MARCA ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MARC_ID_MARCA, MARC_DS_MARCA 
                            FROM OCS_TB_MARC_MARCA
                            ORDER BY $order");
        return $stmt->fetchAll();
    }
    
    public function getMarcaCheckBox($order){
        
        if ( !isset($order) ) {
            $order = 'MARC_DS_MARCA ASC';
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        return $db->fetchPairs("SELECT MARC_ID_MARCA, MARC_DS_MARCA 
                            FROM OCS_TB_MARC_MARCA
                            ORDER BY $order");
        
    }
    
    public function getMarcaLab($marca)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT MARC_ID_MARCA AS ID, MARC_DS_MARCA AS LABEL  
                            FROM OCS_TB_MARC_MARCA
                            WHERE UPPER(MARC_DS_MARCA) LIKE UPPER('%$marca%')
                            ORDER BY MARC_DS_MARCA ASC");
        return $stmt->fetchAll();
    }
    
    
}