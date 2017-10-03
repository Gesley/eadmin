<?php
class Application_Model_DbTable_Orcamento_CeoTbDemnDemandante extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_DEMN_DEMANDANTE_VALOR';
	protected $_primary = 'DEMN_CD_DEMANDANTE';
	protected $_sequence = 'CEO_TB_DEMN';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbVldeValorDespesa'
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