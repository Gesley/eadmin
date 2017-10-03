<?php

/**
 * Tabela de Auditoria das Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_PFDS_APF_DESENVOLVEDORA';
    protected $_primary = 'PFDS_ID_APF_DESENVOLVEDORA';
    protected $_sequence = 'SOS_SQ_PFDS';
    
}

?>
