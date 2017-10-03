<?php
class Application_Model_DbTable_Orcamento_CeoTbTpncTipoNC extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_TPNC_TIPO_NOTA_CREDITO';
	protected $_primary = 'TPNC_CD_TIPO_NC';
	
	/*
	protected $_dependentTables = array(
		'Application_Model_DbTable_CeoTbNocrNotaCredito'
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