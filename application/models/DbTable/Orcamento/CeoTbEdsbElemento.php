<?php
class Application_Model_DbTable_Orcamento_CeoTbEdsbElemento extends Zend_Db_Table_Abstract {
		
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_EDSB_ELEMENTO_SUB_DESP';
	protected $_primary = 'EDSB_CD_ELEMENTO_DESPESA_SUB';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_Orcamento_CeoTbDespDespesa',
		'Application_Model_DbTable_Orcamento_CeoTbFolhFolhaPagamento',
		'Application_Model_DbTable_Orcamento_CeoTbNoemNotaEmpenho',
		'Application_Model_DbTable_Orcamento_CeoTbNocrNotaCredito',
		'Application_Model_DbTable_Orcamento_CeoTbRembRemanjtoBloq',
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