<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 *
 */

class Application_Model_DbTable_SosTbTpsfTipoServico extends Zend_Db_Table_Abstract {
	
        protected $_schema = 'SOS';
        protected $_name = 'SOS_TB_TPSF_TIPO_SERVICO_FICHA';
	protected $_primary = array ('TPSF_ID_DOCUMENTO', 'TPSF_ID_TP_SERVICO' );
	protected $_sequence = '';
	
	/**
	 * Retorna a lista de Servicços associado ao documento/Ficha de Serviço
	 * 
	 * @param unknown_type $documentoID
	 */
	public function getServicosAssociadoaodocumento($documentoID) 
	{
		$db = Zend_Db_Table_Abstract::getDefaultAdapter ();
		$stmt = "SELECT A.*,B.TPSE_DS_TP_SERVICO FROM SOS_TB_TPSF_TIPO_SERVICO_FICHA A,SOS_TB_TPSE_TIPO_SERVICO B
				WHERE 
				A.TPSF_ID_DOCUMENTO=$documentoID AND
				A.TPSF_ID_TP_SERVICO = B.TPSE_ID_TP_SERVICO
		";
		
		return $db->query($stmt)->fetchAll();
	}
}