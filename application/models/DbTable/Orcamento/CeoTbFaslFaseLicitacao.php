<?php
class Application_Model_DbTable_Orcamento_CeoTbFaslFaseLicitacao extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_FASL_FASE_PROC_LICIT';
	protected $_primary = 'FASL_CD_FASE';
	protected $_sequence = 'CEO_TB_FASL';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbRecfRecursoFase'
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