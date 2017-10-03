<?php
class Application_Model_DbTable_Orcamento_CeoTbSoldSolicitacaoDesp extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_SOLD_SOLIC_DESPESA';
	protected $_primary = 'SOLD_NR_SOLICITACAO';
	protected $_sequence = 'CEO_SQ_SOLD';
	
	/*
	protected $_referenceMap = array(
		'UnidadeGestora'	=> array(
			'columns'       => 'SOLD_CD_UG',
			'refTableClass' => 'Application_Model_DbTable_CeoTbUngeUnidadeGestora',
			'refColumns'    => 'UNGE_CD_UG'
		),
		'TipoDespesa'		=> array(
			'columns'       => 'SOLD_CD_TIPO_DESPESA',
			'refTableClass' => 'Application_Model_DbTable_CeoTbTideTipoDespesa',
			'refColumns'    => 'TIDE_CD_TIPO_DESPESA'
		),
		'LotacaoSECOR'		=> array(
			'columns'		=> 'SOLD_NR_LOTACAO',
			'refTableClass' => 'Application_Model_DbTable_CeoTbLotaLotacaoSecor',
			'refColumns'    => 'LOTA_NR_LOTACAO'
		),
		'TipoSolicitacao'	=> array(
			'columns'       => array('SOLD_CD_TIPO_SOLICITACAO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbTsolTipoSolicitacao',
			'refColumns'    => array('TSOL_CD_TIPO_SOLICITACAO')
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