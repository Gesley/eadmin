<?php
class Application_Model_DbTable_Orcamento_CeoTbReqvRequVariacao extends Zend_Db_Table_Abstract {
    
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_REQV_REQU_VARIACAO';
	protected $_primary = array ('REQV_NR_DESPESA','REQV_DH_VARIACAO' );
	
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
		return $this->_primary;
	}
}