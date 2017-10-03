<?php
class Application_Model_DbTable_Orcamento_CeoTbNocrNotaCredito extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_NOCR_NOTA_CREDITO';
	protected $_primary = 'NOCR_CD_NOTA_CREDITO';
	
	/*protected $_referenceMap = array(
		'TipoNotaCredito' 	=> array(
			'columns'       => array('NOCR_CD_TIPO_NC'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbTpncTipoNC',
			'refColumns'    => array('TPNC_CD_TIPO_NC')
		),
		'UnidadeGestora' 	=> array(
			'columns'       => array('NOCR_CD_UG_OPERADOR'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbUngeUnidadeGestora',
			'refColumns'    => array('UNGE_CD_UG')
		),
		'Despesa' 			=> array(
			'columns'       => array('NOCR_NR_DESPESA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
		),
		'Categoria' => array(
			'columns'       => array('NOCR_CD_CATEGORIA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbCateCategoria',
			'refColumns'    => array('CATE_CD_CATEGORIA')
		),
		'Elemento' => array(
			'columns'       => array('NOCR_CD_ELEMENTO_DESPESA_SUB'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbEdsbElemento',
			'refColumns'    => array('EDSB_CD_ELEMENTO_DESPESA_SUB')
		),
		'Fonte' => array(
			'columns'       => array('NOCR_CD_FONTE'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbFontFonte',
			'refColumns'    => array('FONT_CD_FONTE')
		),
		'Ptres' => array(
			'columns'       => array('NOCR_CD_PT_RESUMIDO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbPtresPrograma',
			'refColumns'    => array('PTRS_CD_PT_RESUMIDO')
		),
		'Vinculacao' => array(
			'columns'       => array('NOCR_CD_VINCULACAO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbVincVinculacao',
			'refColumns'    => array('VINC_CD_VINCULACAO')
		),
	);*/
	
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