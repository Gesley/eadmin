<?php
class Application_Model_DbTable_Orcamento_CeoTbProjProjecao extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
	protected $_name = 'CEO_TB_PROJ_PROJECAO';
	protected $_primary = array ('PROJ_NR_DESPESA');
	
	/*protected $_referenceMap = array(
		'Despesa' => array(
			'columns'       => array('PROJ_NR_DESPESA'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbDespDespesa',
			'refColumns'    => array('DESP_NR_DESPESA')
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
		return $this->_primary;
	}
}

