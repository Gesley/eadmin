<?php
class Application_Model_DbTable_Orcamento_CeoTbPrjjJustifProjecao extends Zend_Db_Table_Abstract
{

        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_PRJJ_JUSTIF_PROJECAO';
	protected $_primary = array('PRJJ_NR_DESPESA', 'PRJJ_DH_JUSTIFICATIVA');
	
	/*protected $_referenceMap = array(
		'Despesa' => array(
			'columns'       => array('PRJJ_NR_DESPESA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
		)
	);*/
	
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