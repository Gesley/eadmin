<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbOsisOcorrenciaSistema extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_OSIS_OCORRENCIA_SISTEMA';
    protected $_primary = 'OSIS_ID_OCORRENCIA';
    protected $_sequence = 'SOS_SQ_OSIS';


    public function getOcorrencias()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT OSIS_ID_OCORRENCIA,
                                   OSIS_NM_OCORRENCIA,
                                   OSIS_DS_OCORRENCIA
                            FROM SOS_TB_OSIS_OCORRENCIA_SISTEMA
                            ");
        return $stmt->fetchAll();
    }

}