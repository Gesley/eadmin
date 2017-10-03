<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_TraTbCoveCorVeiculo extends Zend_Db_Table_Abstract
{
    protected $_name = 'TRA_TB_COVE_COR_VEICULO';
    protected $_primary = 'COVE_ID_COR';
    protected $_sequence = 'TRA_SQ_COVE_ID_COR';
    
    public function getCorVeiculo()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT COVE_ID_COR, COVE_NO_COR 
                            FROM TRA_TB_COVE_COR_VEICULO 
                            ORDER BY 2");
        return $stmt->fetchAll();
    }

}