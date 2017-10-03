<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_OcsTbUnmeUnidadeMedida extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_UNME_UNIDADE_MEDIDA';
    protected $_primary = 'UNME_ID_UNID_MEDIDA';
    //protected $_sequence = 'SOS_SQ_SUME';

    public function getUnidadeMedida()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT  UNME_ID_UNID_MEDIDA,
                                    UNME_DS_UNID_MEDIDA,
                                    UNME_SG_UNID_MEDIDA
                            FROM    OCS_TB_UNME_UNIDADE_MEDIDA");
        return $stmt->fetchAll();
    }
}



