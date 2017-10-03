<?php
class Application_Model_DbTable_Orcamento_CeoTbUngeUnidadeGestora extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_UNGE_UNIDADE_GESTORA';
	protected $_primary = 'UNGE_CD_UG';
	
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