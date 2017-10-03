<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbSesiServicoSistema extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SESI_SERVICO_SISTEMA';
    protected $_primary = 'SESI_ID_SERVICO_SISTEMA';
    protected $_sequence = 'SOS_SQ_SESI';


    public function getServicoSistema()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter($id);
        $stmt = $db->query("SELECT SESI_ID_SERVICO_SISTEMA,
                                   SESI_NM_SERVICO_SISTEMA
                            FROM SOS_TB_SESI_SERVICO_SISTEMA
                            ");
        return $stmt->fetchAll();
    }

}