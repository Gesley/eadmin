<?php
class Application_Model_DbTable_Orcamento_CeoTbRembRemanjtoBloq extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'CEO';
	protected $_name = 'CEO_TB_REMB_REMANJTO_BLOQUEADO';
	protected $_primary = array ('REMB_CD_PT_RESUMIDO', 'REMB_CD_ELEMENTO_DESPESA_SUB' );
	
	/*protected $_referenceMap = array(
			'Ptres' 		=> array(
			'columns'       => array('REMB_CD_PT_RESUMIDO'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbPtresPrograma',
			'refColumns'    => array('PTRS_CD_PT_RESUMIDO')
		),
			'ElementoSub' 	=> array(
			'columns'       => array('REMB_CD_ELEMENTO_DESPESA_SUB'),
			'refTableClass' => 'Application_Model_DbTable_CeoTbEdsbElemento',
			'refColumns'    => array('EDSB_CD_ELEMENTO_DESPESA_SUB')
		),
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