<?php
class Application_Model_DbTable_Orcamento_CeoTbCtrdContratoDespesa extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_CTRD_CONTRATO_DESPESA';
	protected $_primary = 'CTRD_ID_CONTRATO_DESPESA';
	protected $_sequence = 'CEO_SQ_CTRD';
	
	
	
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