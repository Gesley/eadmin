<?php
class Application_Model_DbTable_Orcamento_CeoTbPprgPrograma extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_PPRG_PROGRAMA';
	protected $_primary = 'PPRG_CD_PROGRAMA';
	protected $_sequence = 'CEO_TB_PPRG';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbDespDespesa'
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