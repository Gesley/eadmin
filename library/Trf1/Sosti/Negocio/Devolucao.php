<?php
/**
 * @category	        TRF1
 * @package		Trf1_Sosti_Negocio_Devolucao
 * @copyright	        Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Daniel Rodrigues
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
*/
class Trf1_Sosti_Negocio_Devolucao
{
	public function __construct() {
		
	}
	
	/* ************************************************************
	 * Funções específicas
	 *********************************************************** */
	/*
	 * Busca os dados da solicitação necessários para devolve-la para a caixa de helpdesk
	 *
	 * @param	int $idSolic - Código da solicitação
	 * @author	Daniel Rodrigues
	 */
	public function getDadosDevolucao($idSolic) {
		
		$sql = "
SELECT SGRS.*,CXEN.*, TPCX.*,CXGS.* ,SOS_P.PKG_SOLIC.SOLIC_VINCULADA(DOCM.DOCM_ID_DOCUMENTO) VINCULADA,
(SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO) SSES_ID_SERVICO,
(SELECT SSES_NR_TOMBO 
FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
       INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
       ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
       AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
       INNER JOIN SOS_TB_SSER_SERVICO SSER_1
       ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO 
WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                               FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                               INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                               ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                               AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                               WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO)
AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO) SSES_NR_TOMBO,
(SELECT SSES_SG_TIPO_TOMBO 
FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
        INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
        ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
        AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
        INNER JOIN SOS_TB_SSER_SERVICO SSER_1
        ON  SSES_1.SSES_ID_SERVICO       = SSER_1.SSER_ID_SERVICO 
WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO)
AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO) SSES_SG_TIPO_TOMBO

FROM
-- documento
SAD_TB_DOCM_DOCUMENTO DOCM

-- documento movimentacao
INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

-- movimentacao origem
INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

-- movimentacao destino
INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

--Caixa de entrada
INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
ON  MODE_MOVI.MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA

INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA

INNER JOIN SAD_TB_TPCX_TIPO_CAIXA TPCX
ON CXEN.CXEN_ID_TIPO_CAIXA = TPCX.TPCX_ID_TIPO_CAIXA

INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO

WHERE DOCM.DOCM_ID_DOCUMENTO = $idSolic 
AND TPCX_ID_TIPO_CAIXA = 1 --TIPO DE CAIXA DE ATENDIMENTO AO USUÁRIO
AND CXEN_ID_CAIXA_ENTRADA <> (SELECT
MODE_ID_CAIXA_ENTRADA
FROM
-- documento
SAD_TB_DOCM_DOCUMENTO DOCM_3
-- documento movimentacao
INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_3
ON  DOCM_3.DOCM_ID_DOCUMENTO     = MODO_MOVI_3.MODO_ID_DOCUMENTO
-- movimentacao origem
INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_3
ON  MODO_MOVI_3.MODO_ID_MOVIMENTACAO  = MOVI_3.MOVI_ID_MOVIMENTACAO
-- movimentacao destino
INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_3
ON  MOVI_3.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_3.MODE_ID_MOVIMENTACAO
 --fase
INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_3
ON  MOVI_3.MOVI_ID_MOVIMENTACAO  = MOFA_3.MOFA_ID_MOVIMENTACAO
WHERE 
 --última movimentação
(MOFA_3.MOFA_DH_FASE,MOFA_3.MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                                                FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                                ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                                ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                                WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM_3.DOCM_ID_DOCUMENTO
                                                                                AND DOCM_1.DOCM_ID_TIPO_DOC = 160))
AND DOCM_3.DOCM_ID_DOCUMENTO = $idSolic ) --CAIXA ATUAL
ORDER BY MOVI.MOVI_DH_ENCAMINHAMENTO DESC --PEGAR O PRIMEIRO REGISTRO REFERENTE A ÚLTIMA CAIXA DE ATENDIMENTO AO USUÁRIO
		";
               
		$banco = Zend_Db_Table::getDefaultAdapter();
		return $banco->fetchRow($sql);
	}
	
}








































