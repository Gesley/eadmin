<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class Application_Model_DbTable_OcsTbGrupGrupo extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'OCS';    
        protected $_name = 'OCS_TB_GRUP_GRUPO_MAT_SERV';
	protected $_primary = 'GRUP_ID_GRUPO_MAT_SERV';
	protected $_sequence = 'OCS_SQ_GRUP';
	
	public function getGrupos($order) {
		if (! isset ( $order )) {
			$order = 'GRUP_DS_GRUPO_MAT_SERV ASC';
		}
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$stmt = "SELECT 
        A.GRUP_ID_GRUPO_MAT_SERV, 
        A.GRUP_DS_GRUPO_MAT_SERV,
		A.GRUP_CD_MAT_INCLUSAO, 
		A.GRUP_DT_INCLUSAO
				FROM 
					OCS.OCS_TB_GRUP_GRUPO_MAT_SERV A  
				ORDER BY $order";
		return $db->query ( $stmt )->fetchAll ();
	}
	public function getgrupoAssociacoes($grupoID) {
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$sql = "SELECT A.GRMA_ID_GRUPO_MAT_SERV, A.GRMA_ID_MARCA
  					FROM OCS_TB_GRMA_GRUPO_MARCA A
  					WHERE A.GRMA_ID_GRUPO_MAT_SERV =$grupoID
                                        AND A.GRMA_IC_ATIVO = 'S'";
		
		return $db->query($sql)->fetchAll();
    	}
    	
 	public function autoCompleteGrupo($grupo){
 		
 		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
 		$sql= "SELECT A.GRUP_DS_GRUPO_MAT_SERV AS LABEL
 			FROM
 			OCS_TB_GRUP_GRUPO_MAT_SERV A
 			WHERE
 			A.GRUP_DS_GRUPO_MAT_SERV LIKE('$grupo%')";
 		
 		return $db->query($sql)->fetchAll();
 	}
 	/**
 	 * Retorna as Marcas ativas associadas a um grupo.
 	 * 
 	 * @param int $grupoID
 	 */
 	public function getMarcapeloGrupoID($grupoID){

 		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
 		$sql= " SELECT B.marc_ds_marca, B.marc_id_marca
                FROM OCS_TB_GRMA_GRUPO_MARCA A, ocs_tb_marc_marca B
                WHERE A.GRMA_ID_GRUPO_MAT_SERV = $grupoID
                AND A.GRMA_IC_ATIVO = 'S'
                AND A.grma_id_marca= B.marc_id_marca";
 		return $db->query($sql)->fetchAll();
 	}
 	/**
 	 * retorna as informações atuais do grupo
 	 * 
 	 * @param int $grupo
 	 */ 
 	public function getgrupoInfo($grupoID){
 		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
 		$stmt="SELECT GRUP_ID_GRUPO_MAT_SERV, GRUP_DS_GRUPO_MAT_SERV,GRUP_CD_MAT_INCLUSAO,TO_CHAR(GRUP_DT_INCLUSAO,'DD/MM/YYYY HH24:MI:SS') GRUP_DT_INCLUSAO
 		 		FROM OCS_TB_GRUP_GRUPO_MAT_SERV
 		 		WHERE
 		 		GRUP_ID_GRUPO_MAT_SERV = $grupoID";
 					
 		return  $db->query($stmt)->fetchAll();
 		
 	}
}