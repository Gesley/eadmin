<?php

/**
 * Tabela Tipo de Serviço
 * @author tr18484ps
 */
class Application_Model_DbTable_Sosti_SosTbSetpTipoServico extends Zend_Db_Table_Abstract{
    
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_TPSE_TIPO_SERVICO';
    protected $_primary = 'TPSE_ID_TP_SERVICO';
    protected $_sequence = 'SOS_SQ_TPSE';
    
}

?>
