<?php

/**
 * Tabela de Auditoria das Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbSctaStatusContratada extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_SCTA_STATUS_CONTRATADA';
    protected $_primary = 'SCTA_ID_STATUS';
    
}

?>
