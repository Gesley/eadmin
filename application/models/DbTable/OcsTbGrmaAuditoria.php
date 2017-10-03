<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbGrmaAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_GRMA_AUDITORIA';
    protected $_primary = 'GRMA_TS_OPERACAO';
    //protected $_sequence = '';
 
	/**
	 * retorna o último registro da associação do grupo com a marca 
	 * 
	 * @param int $grma_id_grupo
	 * @param int $grma_id_marca
	 */
    public function getLastAuditRow($grma_id_grupo,$grma_id_marca){
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$stmt = "SELECT GRMA.* 
		FROM OCS_TB_GRMA_AUDITORIA GRMA 
		WHERE GRMA.NEW_GRMA_ID_MARCA =$grma_id_marca 
		AND GRMA.new_grma_id_grupo_mat_serv = $grma_id_grupo 
		AND GRMA.GRMA_TS_OPERACAO = (SELECT MAX(GRMA2.GRMA_TS_OPERACAO)FROM OCS_TB_GRMA_AUDITORIA GRMA2)";
		
		return $db->query($stmt)->fetchAll();
		
	}
    
    
	
	
}