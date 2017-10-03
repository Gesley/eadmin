<?php

/**
 * Tabela responsável pela associação entre os anexos, as solicitações e as tarefas
 */
class Tarefa_Model_DbTable_SosTbAntsAnexTarefaSolicit extends Zend_Db_Table_Abstract
{
    
    protected $_schema  = 'SOS';
    protected $_name    = 'SOS_TB_ANTS_ANEX_TAREFA_SOLIC';
    protected $_primary = array('ANTS_ID_ANEX_TAREFA_SOLICIT');
    protected $_sequence = 'SOS_SQ_ANTS';
    protected $_dependentTables = array();
    protected $_referenceMap = array(
        'SOS_TB_ANTS_SOS_TB_TASO_FK' => array(
            'refTableClass' => 'Tarefa_Model_DbTable_SosTbTasoTarefaSolicit',
            'columns'       => 'ANTS_ID_TAREFA_SOLICIT',
            'refColumns'    => 'TASO_ID_TAREFA_SOLICIT'
        )
    );

}