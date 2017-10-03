<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbStsaTipoSatisfacao extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_STSA_TIPO_SATISFACAO';
    protected $_primary = 'STSA_ID_TIPO_SAT';
    
    public function getTipoSatisfacao()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT STSA_ID_TIPO_SAT, STSA_DS_TIPO_SAT 
                            FROM SOS_TB_STSA_TIPO_SATISFACAO
                            WHERE STSA_ID_TIPO_SAT NOT IN 7");
        $solicitacao = $stmt->fetchAll();
        return $solicitacao;
    }

}