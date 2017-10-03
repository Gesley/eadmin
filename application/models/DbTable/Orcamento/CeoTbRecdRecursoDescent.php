<?php
class Application_Model_DbTable_Orcamento_CeoTbRecdRecursoDescent extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_RECD_RECURSO_DESCENT';
	protected $_primary = 'RECD_CD_RECURSO';
	protected $_sequence = 'CEO_SQ_RECD';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbRecfRecursoFase'
	);
	protected $_referenceMap = array(
		'Despesa' => array(
			'columns'       => array('RECD_NR_DESPESA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
		)
	)
	*/
	
	/**
	 * Retorna a chave primária
	 *
	 * @return	mixed		Primary key (atring ou array)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function chavePrimaria() {
		return array ($this->_primary );
	}
}