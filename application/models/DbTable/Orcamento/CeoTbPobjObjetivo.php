<?php
class Application_Model_DbTable_Orcamento_CeoTbPobjObjetivo extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_POBJ_OBJETIVO';
	protected $_primary = 'POBJ_CD_OBJETIVO';
	protected $_sequence = 'CEO_TB_POBJ';
	
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