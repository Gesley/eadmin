<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */


class Application_Model_DbTable_SosTbTideTipoDefeito extends Zend_Db_Table_Abstract
{
    protected $_name = 'SOS_TB_TIDE_TIPO_DEFEITO_SIST';
    protected $_primary = 'TIDE_ID_TIPO_DEFEITO_SISTEMA';
    protected $_sequence = 'SOS_SQ_TIDE';

    /**
     * Retorna os tipos de defeitos registrado no sitema 
     */

    public function getDefeitos($order){
        if (!isset($order)) {
            $order = " TIDE_NM_DEFEITO ASC ";
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$stmt = "SELECT A.TIDE_ID_TIPO_DEFEITO_SISTEMA, 
						A.TIDE_ID_INDICADOR,
						A.TIDE_NM_DEFEITO, 
						A.TIDE_DS_DEFEITO,
						A.TIDE_CD_MATRICULA_INCLUSAO, 
						A.TIDE_DH_INCLUSAO,
						A.TIDE_IC_ATIVO, 
						A.TIDE_CD_MATRICULA_INATIVACAO,
						A.TIDE_DH_INATIVACAO,
						C.PNAT_NO_PESSOA
				   FROM SOS.SOS_TB_TIDE_TIPO_DEFEITO_SIST A, 
				        OCS_TB_PMAT_MATRICULA B,
						OCS_TB_PNAT_PESSOA_NATURAL C 
			      WHERE B.PMAT_ID_PESSOA = C.PNAT_ID_PESSOA
				    AND B.PMAT_CD_MATRICULA = A.TIDE_CD_MATRICULA_INCLUSAO
					ORDER BY $order
		";
		return $db->query($stmt)->fetchAll();
    
	}
}