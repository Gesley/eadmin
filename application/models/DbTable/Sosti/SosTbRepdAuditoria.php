<?php

/**
 * Tabela de Auditoria das Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbRepdAuditoria extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_REPD_AUDITORIA';
    protected $_primary = 'REPD_TS_OPERACAO';
    
}

?>
