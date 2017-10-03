<?php
class Application_Model_DbTable_Orcamento_CeoTbMovcMovimentacaoCred extends Zend_Db_Table_Abstract {

        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_MOVC_MOVIMENTACAO_CRED';
	protected $_primary = 'MOVC_CD_MOVIMENTACAO';
	protected $_sequence = 'CEO_SQ_MOVC';
	
	/*
	protected $_referenceMap = array(
		'Despesa1' => array(
			'columns'       => array('MOVC_NR_DESPESA_ORIGEM'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
		),
		'Despesa2' => array(
			'columns'       => array('MOVC_NR_DESPESA_DESTINO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
		),
		'TipoSolicitacao' => array(
			'columns'       => array('MOVC_CD_TIPO_SOLICITACAO'),
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