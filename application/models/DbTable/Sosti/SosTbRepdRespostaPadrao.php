<?php
/**
 * Tabela de Respostas Padrões do Sistema
 * @author Daniel Rodrigues
 */
class Application_Model_DbTable_Sosti_SosTbRepdRespostaPadrao extends Zend_Db_Table_Abstract {
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_REPD_RESPOSTA_PADRAO';
    protected $_primary = 'REPD_ID_RESPOSTA_PADRAO';
    protected $_sequence = 'SOS_SQ_REPD';
    
}

?>
