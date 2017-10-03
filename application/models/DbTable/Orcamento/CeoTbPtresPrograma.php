<?php
class Application_Model_DbTable_Orcamento_CeoTbPtresPrograma extends Zend_Db_Table_Abstract {
    
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_PTRS_PROGRAMA_TRABALHO';
	protected $_primary = 'PTRS_CD_PT_RESUMIDO';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbDespDespesa',
		'Application_Model_DbTable_CeoTbRembRemanjtoBloq',
		'Application_Model_DbTable_CeoTbFolhFolhaPagamento',
		'Application_Model_DbTable_CeoTbNoemNotaEmpenho',
		'Application_Model_DbTable_CeoTbNocrNotaCredito'
	
	);
	
	protected $_referenceMap = array(
		'UnidOrcamentaria' 	=> array(
			'columns'       => array('PTRS_CD_UNID_ORCAMENTARIA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbUnorUnidadeOrcamentaria',
			'refColumns'    => array('UNOR_CD_UNID_ORCAMENTARIA')
		)
	);
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