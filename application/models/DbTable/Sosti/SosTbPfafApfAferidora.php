<?php

/**
 * Tabela de Auditoria das Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbPfafApfAferidora extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_PFAF_APF_AFERIDORA';
    protected $_primary = 'PFAF_ID_APF_AFERICAO';
    protected $_sequence = 'SOS_SQ_PFAF';
    
}

?>
