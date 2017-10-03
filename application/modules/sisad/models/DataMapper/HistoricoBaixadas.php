<?php

class Sisad_Model_DataMapper_HistoricoBaixadas extends Zend_Db_Table_Abstract
{

    
     public function historicoDeSostiBaixados($matricula, $order = null){
        
         /*
          * Consulta todos os Sostis que foram baixados
          */
        $this->fromBaixas = "
            
               SELECT DISTINCT
                --solicitação sos_tb_ssol_solicitacao
                SSOL.SSOL_ID_DOCUMENTO,
                SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,
                --documento sad_tb_docm_documento
                DOCM_NR_DOCUMENTO,
                SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                DOCM_CD_MATRICULA_CADASTRO, 
                TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS')DOCM_DH_CADASTRO,
                DOCM_DH_CADASTRO DH_CADASTRO,
                SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_RESP_TECNICO(SSOL_ID_DOCUMENTO) ENCAMINHADORR,

                --fase sad_tb_mofa_movi_fase
                MOFA_ID_MOVIMENTACAO, 
                MOFA_ID_FASE,TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL,
                TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                TO_DATE(SYSDATE,'DD/MM/YYYY') - TO_DATE(MOFA_DH_FASE,'DD/MM/YYYY') DIAS_BAIXA,
                TO_DATE(MOFA_DH_FASE,'DD/MM/YYYY') DATA_FASE,
                TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                MOFA_CD_MATRICULA,

                --movimentacao destino sad_tb_mode_movi_destinatario
                MODE_ID_CAIXA_ENTRADA,

                --nivel sos_tb_snat_nivel_atendimento
                SNAS_ID_NIVEL,

                --servico sos_tb_sser_servico
                SSER_ID_SERVICO,
                SSER_DS_SERVICO, 

                --espera sos_tb_sesp_solic_espera
                TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,
                SESP_DH_LIMITE_ESP,

                SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASC_NR_DOCUMENTO
                    
                FROM
                -- solicitacao
                SOS_TB_SSOL_SOLICITACAO SSOL
                -- documento
                INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                -- documento movimentacao
                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI ON DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
                -- movimentacao origem
                INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI ON MODO_MOVI.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO
                -- movimentacao destino
                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ON MOVI.MOVI_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                --fase
                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA ON MOVI.MOVI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                --descricao fase
                INNER JOIN SAD_TB_FADM_FASE_ADM FADM ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE
                --servico
                LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES ON MOFA.MOFA_ID_MOVIMENTACAO = SSES.SSES_ID_MOVIMENTACAO
                LEFT JOIN SOS_TB_SSER_SERVICO SSER ON SSES.SSES_ID_SERVICO = SSER.SSER_ID_SERVICO
                --nivel
                LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS ON MOFA.MOFA_ID_MOVIMENTACAO = SNAS.SNAS_ID_MOVIMENTACAO
                LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT ON SNAT.SNAT_ID_NIVEL = SNAS.SNAS_ID_NIVEL
                --espera
                LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP ON MOFA.MOFA_ID_MOVIMENTACAO = SESP.SESP_ID_MOVIMENTACAO
                WHERE
                -- tipo documento solicitacao
                DOCM_ID_TIPO_DOC = 160
                AND
                -- matricula do solicitante
                DOCM.DOCM_CD_MATRICULA_CADASTRO = '$matricula'
                AND
                -- 1000 baixa
                MOFA.MOFA_ID_FASE = 1000 ";
                
        $this->fromBaixas .= $order ? " ORDER BY $order" : "";
        return $this->fromBaixas;
    }
}
