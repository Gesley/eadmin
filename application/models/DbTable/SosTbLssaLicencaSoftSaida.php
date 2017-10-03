<?php

class Application_Model_DbTable_SosTbLssaLicencaSoftSaida extends Zend_Db_Table_Abstract {

    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_LSSA_LICENCA_SOFT_SAIDA';
    protected $_primary = 'LSSA_ID_LICEN_SOFT_SAIDA';
    protected $_sequence = 'SOS_SQ_LSSA';

    public function todosSoftwaresDocumento($idDocumento) {
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT 
            LSSA_ID_SOFTWARE
            FROM
            SOS_TB_LSSA_LICENCA_SOFT_SAIDA
            WHERE
            LSSA_ID_DOCUMENTO = $idDocumento
        ";
        return $db->fetchAll($stmt);
    }
    
    public function verificaPendenciaSoft($idDocumento){
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = "
            SELECT *FROM 
            SOS_TB_LSSA_LICENCA_SOFT_SAIDA
            WHERE
            LSSA_ID_DOCUMENTO = $idDocumento AND
            LSSA_IC_APROVACAO = 'S'
        ";
        return $db->fetchAll($stmt);
        
    }

}

