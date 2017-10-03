<?php
class Application_Model_DbTable_Orcamento_CeoTbUnorUnidadeOrcamentaria extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_UNOR_UNID_ORCAMENTARIA';
	protected $_primary = 'UNOR_CD_UNID_ORCAMENTARIA';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbPtresPrograma'
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