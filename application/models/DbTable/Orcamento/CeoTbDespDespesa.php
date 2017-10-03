<?php
class Application_Model_DbTable_Orcamento_CeoTbDespDespesa extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_DESP_DESPESA';
	protected $_primary = 'DESP_NR_DESPESA';
	protected $_sequence = 'CEO_SQ_DESP';
	
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