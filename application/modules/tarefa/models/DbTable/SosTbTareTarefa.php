<?php

/**
 * Tabela onde são cadastradas as tarefas das solicitações
 */
class Tarefa_Model_DbTable_SosTbTareTarefa extends Zend_Db_Table_Abstract
{
    protected $_schema  = 'SOS';
    protected $_name    = 'SOS_TB_TARE_TAREFA';
    protected $_primary = array('TARE_ID_TAREFA');
    protected $_sequence = 'SOS_SQ_TARE';
    protected $_dependentTables = array();
    protected $_referenceMap = array(
        'SOS_TB_TARE_TB_TPTA' => array(
            'refTableClass' => 'Os_Model_DbTable_SosTbTptaTipoTarefa',
            'columns'       => 'TARE_ID_TIPO_TAREFA',
            'refColumns'    => 'TPTA_ID_TIPO_TAREFA'
        )
    );

}
