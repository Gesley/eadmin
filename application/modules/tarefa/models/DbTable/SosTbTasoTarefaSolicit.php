<?php

/**
 * Tabela responsável pela associação entre as solicitações e as tarefas
 */
class Tarefa_Model_DbTable_SosTbTasoTarefaSolicit extends Zend_Db_Table_Abstract
{
    
    protected $_schema  = 'SOS';
    protected $_name    = 'SOS_TB_TASO_TAREFA_SOLICIT';
    protected $_primary = array('TASO_ID_TAREFA_SOLICIT');
    protected $_sequence = 'SOS_SQ_TASO';
    protected $_dependentTables = array();
    protected $_referenceMap = array(
        'SOS_TB_TASO_TB_TARE' => array(
            'refTableClass' => 'Os_Model_DbTable_SosTbTareTarefa',
            'columns'       => 'TASO_ID_TAREFA',
            'refColumns'    => 'TARE_ID_TAREFA'
        ),
        'SOS_TB_TASO_TB_DOCM' => array(
            'refTableClass' => 'Sisad_Model_DbTable_SadTbDocmDocumento',
            'columns'       => 'TASO_ID_DOCUMENTO',
            'refColumns'    => 'DOCM_ID_DOCUMENTO'
        )
    );

}
