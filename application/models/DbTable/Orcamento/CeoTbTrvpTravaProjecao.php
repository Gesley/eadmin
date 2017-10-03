<?php
class Application_Model_DbTable_Orcamento_CeoTbTrvpTravaProjecao extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
        protected $_name = 'CEO_TB_TRVP_TRAVA_PROJECAO';
	protected $_primary = array ('TRVP_CD_UG', 'TRVP_DT_INICIO' );
	
	/*
	protected $_referenceMap = array(
		'UnidadeGestora' => array(
			'columns'       => array('TRVP_CD_UG'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbUngeUnidadeGestora',
			'refColumns'    => array('UNGE_CD_UG')
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