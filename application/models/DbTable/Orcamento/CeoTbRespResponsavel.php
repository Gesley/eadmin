<?php
class Application_Model_DbTable_Orcamento_CeoTbRespResponsavel extends Zend_Db_Table_Abstract {

        protected $_schema = 'CEO';	
        protected $_name = 'CEO_TB_RESP_RESPONSAVEL';
	protected $_primary = 'RESP_CD_RESPONSAVEL';
	
	/*
	protected $_dependentTables = array (
			'Application_Model_DbTable_CeoTbSoldSolicitacaoDesp',
			'Application_Model_DbTable_CeoTbUsloUsuarioLotacao',
			'Application_Model_DbTable_CeoTbDespDespesa'
			);
	
	protected $_referenceMap = array(
			'SiglaLotacao' => array(
					'columns' => array('LOTA_DS_LOTACAO'),
					'refTableClass' => 'Application_Model_DbTable_RhCentralLotacao',
					'refColumns' => array('LOTA_COD_LOTACAO')
			),
			// Falta alterar RhCentralLotacao
			'CódigoLotacao' => array(
					'columns' => array('LOTA_DS_SECAO'),
					'refTableClass' => 'Application_Model_DbTable_CeoRhCentralLotacao',
					'refColumns' => array('LOTA_SIGLA_SECAO')
			)
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