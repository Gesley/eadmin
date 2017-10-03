<?php
class Application_Model_DbTable_Orcamento_CeoTbEvenEventoNe extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_EVEN_EVENTO_NE';
	protected $_primary = 'EVEN_CD_EVENTO';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbNoemNotaEmpenho'
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