<?php
class Application_Model_DbTable_Orcamento_CeoTbPadrPadraoUG extends Zend_Db_Table_Abstract {

        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_PADR_PADRAO_UG';
	protected $_primary = 'PADR_CD_UG';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbUngeUnidadeGestora'
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