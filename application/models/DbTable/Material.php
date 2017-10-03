<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */
class Application_Model_DbTable_Material extends Zend_Db_Table_Abstract {
 
    protected $_name = 'MATERIAL';
    protected $_primary = array('CO_MAT');
    
    /**
     * Retorna a descricao do material por ID 
     * @param type $cod
     * @return Array 
     */
    public function getCodMaterial($cod){
        
        $codigo = strtoupper($cod);
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = "
            SELECT 
                CO_MAT AS ID
                , CO_MAT || ' - ' || DE_MAT AS LABEL
            FROM 
                MATERIAL 
            WHERE 
                CO_MAT LIKE '$codigo%' OR
                DE_MAT LIKE '%$codigo%'
            ";
        
        $rows = $db->fetchAll($sql);
        return $rows;
    }
    
    
}
