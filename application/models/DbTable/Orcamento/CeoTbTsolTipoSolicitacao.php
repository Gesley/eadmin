<?php
class Application_Model_DbTable_Orcamento_CeoTbTsolTipoSolicitacao extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_TSOL_TIPO_SOLICITACAO';
	protected $_primary = 'TSOL_CD_TIPO_SOLICITACAO';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbSoldSolicitacaoDesp',
		'Application_Model_DbTable_CeoTbMovcMovimentacaoCred'
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