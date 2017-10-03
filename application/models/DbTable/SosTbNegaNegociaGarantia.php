<?php
class Application_Model_DbTable_SosTbNegaNegociaGarantia extends Zend_Db_Table_Abstract
{
    protected $_schema = 'SOS';
    protected $_name = 'SOS_TB_NEGA_NEGOCIA_GARANTIA';
    protected $_primary = 'NEGA_ID_MOVIMENTACAO';
    
    public function getDetalheGarantia($idSolic){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT 
                            NEGA_ID_MOVIMENTACAO,           
                            TO_CHAR(NEGA_DH_SOLIC_GARANTIA,'DD/MM/YYYY HH24:MI:SS') NEGA_DH_SOLIC_GARANTIA_CHAR,
                            NEGA_DH_SOLIC_GARANTIA NEGA_DH_SOLIC_GARANTIA_DATE,         
                            NEGA_CD_MATR_SOLIC,
                            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_SOLIC) NEGA_CD_MATR_SOLIC_NM, 
                            NEGA_DS_JUSTIFICATIVA_PEDIDO,   
                            NEGA_IC_ACEITE,                 
                            NEGA_DH_ACEITE_RECUSA,          
                            NEGA_CD_MATR_ACEITE_RECUSA,
                            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_ACEITE_RECUSA) NEGA_CD_MATR_ACEITE_RECUSA_NM,      
                            NEGA_DS_JUST_ACEITE_RECUSA,     
                            NEGA_IC_CONCORDANCIA,           
                            NEGA_DH_CONCORDANCIA,           
                            NEGA_CD_MATR_CONCORDANCIA, 
                            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_CONCORDANCIA) NEGA_CD_MATR_CONCORDANCIA_NM,      
                            NEGA_DS_JUSTIFICATIVA_CONCOR   
                            FROM 
                            -- solicitacao
                            SOS_TB_SSOL_SOLICITACAO SSOL
                            -- documento
                            INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                            ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                            -- documento movimentacao
                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                            ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
                            -- movimentacao garantia
                            INNER JOIN SOS_TB_NEGA_NEGOCIA_GARANTIA NEGA
                            ON   NEGA.NEGA_ID_MOVIMENTACAO  =  MODO_MOVI.MODO_ID_MOVIMENTACAO      
                            WHERE DOCM.DOCM_ID_DOCUMENTO = $idSolic
                            AND  NEGA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                            FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                            INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                            ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                            INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                            ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                            WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO)
                            ORDER BY NEGA_ID_MOVIMENTACAO DESC");
        return $stmt->fetch();
    }
}
