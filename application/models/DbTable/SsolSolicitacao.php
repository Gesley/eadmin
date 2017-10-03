<?php

class Application_Model_DbTable_SsolSolicitacao  extends  Zend_Db_Table_Abstract{
	
    
    protected $_name = 'SOS_TB_SOLICITACAO';
    protected $_primary = 'SSOL_ID_DOCUMENTO';
    protected $_sequence = 'SAD_SQ_DOCM';

    public function getSolicitacaoInfo($docID){
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$stmt = $db->query("SELECT  SOS_TB_SSOL_SOLICITACAO.SSOL_ID_DOCUMENTO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_ID_TIPO_CAD, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_ED_LOCALIZACAO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_NR_TOMBO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_SG_TIPO_TOMBO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_DS_OBSERVACAO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_CD_MATRICULA_ATENDENTE, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_DS_EMAIL_EXTERNO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_NR_TELEFONE_EXTERNO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_NR_CPF_EXTERNO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_NM_USUARIO_EXTERNO, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_HH_FINAL_ATEND, 
    								SOS_TB_SSOL_SOLICITACAO.SSOL_HH_INICIO_ATEND,
    								SOS_TB_LSBK_SERVICO_BACKUP.LSBK_NR_TOMBO,
    								TOMBO.TI_TOMBO,
    								SOS_TB_STCA_TIPO_CADASTRO.STCA_DS_TIPO_CAD as TIPO 
								FROM SOS.SOS_TB_SSOL_SOLICITACAO, SOS_TB_STCA_TIPO_CADASTRO, TOMBO,SOS_TB_LSBK_SERVICO_BACKUP
								WHERE SOS_TB_SSOL_SOLICITACAO.SSOL_ID_TIPO_CAD = SOS_TB_STCA_TIPO_CADASTRO.STCA_ID_TIPO_CAD AND
								SOS_TB_SSOL_SOLICITACAO.SSOL_NR_TOMBO = TOMBO.NU_TOMBO AND 
								SOS_TB_SSOL_SOLICITACAO.SSOL_ID_DOCUMENTO = SOS_TB_LSBK_SERVICO_BACKUP.LSBK_ID_DOCUMENTO
								AND SSOL_ID_DOCUMENTO = '$docID'
								");
    	return $stmt->fetchAll();
    	
    	
    }
    
    
    
}

?>