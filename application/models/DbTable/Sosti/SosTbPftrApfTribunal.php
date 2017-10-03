<?php

/**
 * Tabela de Auditoria das Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbPftrApfTribunal extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_PFTR_APF_TRIBUNAL';
    protected $_primary = 'PFTR_ID_PF_CONTRATANTE';
    protected $_sequence = 'SOS_SQ_PFTR';
    
}

?>
