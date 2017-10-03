<?php

/**
 * Tabela onde são cadastrados os tipos de tarefas
 */
class Tarefa_Model_DbTable_SosTbTptaTipoTarefa extends Zend_Db_Table_Abstract
{

    protected $_schema  = 'SOS';
    protected $_name    = 'SOS_TB_TPTA_TIPO_TAREFA';
    protected $_primary = 'TPTA_ID_TIPO_TAREFA';
    protected $_sequence = 'SOS_SQ_TPTA';
    protected $_dependentTables = array();
    protected $_referenceMap = array();

}
