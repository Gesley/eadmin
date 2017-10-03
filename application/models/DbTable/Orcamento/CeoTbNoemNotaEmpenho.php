<?php
class Application_Model_DbTable_Orcamento_CeoTbNoemNotaEmpenho extends Zend_Db_Table_Abstract {

	protected $_schema = 'CEO';
	protected $_name = 'CEO_TB_NOEM_NOTA_EMPENHO';
	protected $_primary = 'NOEM_CD_NOTA_EMPENHO';
	
	/*protected $_referenceMap = array(
		'NotaEmpenho1' => array(
			'columns'       => array('NOEM_CD_NE_REFERENCIA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbNoemNotaEmpenho',
			'refColumns'    => array('NOEM_CD_NOTA_EMPENHO')
		),
		'Requisicao' => array(
			'columns'       => array('NOEM_DH_VARIACAO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbReqvRequVariacao',
			'refColumns'    => array('REQV_DH_VARIACAO')
		),
		'UnidadeGestora' => array(
			'columns'       => array('NOEM_CD_UG_OPERADOR'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbUngeUnidadeGestora',
			'refColumns'    => array('UNGE_CD_UG')
		),
		'Categoria' => array(
			'columns'       => array('NOEM_CD_CATEGORIA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbCateCategoria',
			'refColumns'    => array('CATE_CD_CATEGORIA')
		),
		'Elemento' => array(
			'columns'       => array('NOEM_CD_ELEMENTO_DESPESA_SUB'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbEdsbElemento',
			'refColumns'    => array('EDSB_CD_ELEMENTO_DESPESA_SUB')
		),
		'Evento' => array(
			'columns'       => array('NOEM_CD_EVENTO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbEvenEventoNe',
			'refColumns'    => array('EVEN_CD_EVENTO')
		),
		'Execucao' => array(
			'columns'       => array('NOEM_MM_EXECUCAO', 'NOEM_AA_EXECUCAO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbExefExecucaoFolha',
			'refColumns'    => array('EXEF_MM_EXECUCAO', 'EXEF_AA_EXECUCAO')
		),
		'Fonte' => array(
			'columns'       => array('NOEM_CD_FONTE'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbFontFonte',
			'refColumns'    => array('FONT_CD_FONTE')
		),
		'Ptres' => array(
			'columns'       => array('NOEM_CD_PT_RESUMIDO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbPtresPrograma',
			'refColumns'    => array('PTRS_CD_PT_RESUMIDO')
		),
		'Vinculacao' => array(
			'columns'       => array('NOEM_CD_VINCULACAO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbVincVinculacao',
			'refColumns'    => array('VINC_CD_VINCULACAO')
		)
		
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