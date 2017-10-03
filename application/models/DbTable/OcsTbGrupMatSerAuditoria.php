<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */


class Application_Model_DbTable_OcsTbGrupMatSerAuditoria extends Zend_Db_Table_Abstract
{
    protected $_schema = 'OCS';
    protected $_name = 'OCS_TB_GRUP_MAT_SERV_AUDITORIA';
    protected $_primary = 'GRUP_TS_OPERACAO';

    
    /**
     * 
     * Retorna  a ultima uatualização do registro
     * @param unknown_type $grupoID
     */
 public function getLastAuditRow($grup_id_grupo){
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$stmt = "SELECT GRUP.*,TO_CHAR(GRUP.NEW_GRUP_DT_INCLUSAO,'DD/MM/YYYY HH24:MI:SS') as NEW_GRUP_DT_INCLUSAO
		FROM OCS_TB_GRUP_MAT_SERV_AUDITORIA GRUP
		WHERE GRUP.new_grup_id_grupo_mat_serv = $grup_id_grupo 
		AND GRUP.grup_ic_operacao = 'A'
		AND GRUP.GRUP_TS_OPERACAO = (SELECT MAX(GRUP2.GRUP_TS_OPERACAO)FROM OCS_TB_GRUP_MAT_SERV_AUDITORIA GRUP2)";
		
		return $db->query($stmt)->fetchAll();
		
	}
    
}