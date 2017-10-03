<?php
class Application_Model_DbTable_Orcamento_CeoTbTideTipoDespesa extends Zend_Db_Table_Abstract {

        protected $_schema = 'CEO';	
        protected $_name = 'CEO_TB_TIDE_TIPO_DESPESA';
	protected $_primary = 'TIDE_CD_TIPO_DESPESA';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbDespDespesa',
		'Application_Model_DbTable_CeoTbSoldSolicitacaoDesp'
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