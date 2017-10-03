<?php
class Application_Model_DbTable_Orcamento_CeoTbVincVinculacao extends Zend_Db_Table_Abstract {
	        
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_VINC_VINCULACAO';
	protected $_primary = 'VINC_CD_VINCULACAO';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbDespDespesa',
		'Application_Model_DbTable_CeoTbNoemNotaEmpenho',
		'Application_Model_DbTable_CeoTbNocrNotaCredito',
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