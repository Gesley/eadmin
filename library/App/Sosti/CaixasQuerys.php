<?php

/**
 * Description of Anexo
 *
 * @author Leonan Alves dos Anjos
 */
class App_Sosti_CaixasQuerys {

    public function __construct() {
        
    }

    /**
     * 
     * 
     */
    public function selectCaixa($tipo) {


        switch ($tipo) {
            case 1:
                /* Caixa de atendimento com nível */
                $this->currentselectCaixa = "
                    SELECT DISTINCT
                    
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    MOFA_ID_FASE,
                    
                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    MOVI_ID_MOVIMENTACAO,
                    MOVI_DH_ENCAMINHAMENTO,

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO,
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,
                    TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --nivel sos_tb_snat_nivel_atendimento
                    SNAT_CD_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO,

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP,
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

                    --vinculacao flag
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,
                    SSOL_NM_USUARIO_EXTERNO 
                ";

                break;
            case 2:
                /* Caixa de atendimento sem nível */
                $this->currentselectCaixa = "
                    SELECT DISTINCT
                    
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    DOCM_DH_CADASTRO,
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    MOFA_ID_FASE,
                    
                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    MOVI_DH_ENCAMINHAMENTO,
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO,
                    TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO,

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP,
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

                    --vinculacao flag
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,
					--Prazo
                    TO_CHAR(( SELECT SSPA_1.SSPA_DT_PRAZO 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                                    AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO)
                                                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO AND SSPA_IC_CONFIRMACAO = 'S'),'DD/MM/YYYY HH24:MI:SS') SSPA_DT_PRAZO,
                                                PRDE_NR_PRIORIDADE,
                    --verifica as associações de OS
                      (SELECT COALESCE(
                        (SELECT DOCM.DOCM_NR_DOCUMENTO
                        FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                          SAD_TB_DOCM_DOCUMENTO DOCM
                        WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                        AND VIDC.VIDC_ID_DOC_VINCULADO   = SSOL_ID_DOCUMENTO
                        ), 0)
                       FROM DUAL) ASSOCIADO_OS ";

                break;
            case 3:
                /* Dados solicitação mais antiga */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 

                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_ED_LOCALIZACAO, 
                    SSOL_DS_EMAIL_EXTERNO, 
                    SSOL_NR_TELEFONE_EXTERNO, 

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO, 
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    DOCM_CD_MATRICULA_CADASTRO, 
                    DOCM_SG_SECAO_GERADORA,
                    DOCM_CD_LOTACAO_GERADORA,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,


                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_ID_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,

                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,
                    MOVI_ID_MOVIMENTACAO, 

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO, 

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP, 

                    -- lotacao geradora
                    LOTA_DSC_LOTACAO, 
                    LOTA_SIGLA_LOTACAO,
                    PRDE_NR_PRIORIDADE
                ";

                break;
            case 4:
                /* Dados solicitação */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 
                    --DOCUMENTO
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO(DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC, 
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                    --SOLICITACAO
                    SSOL_ID_DOCUMENTO,
                    SSOL_DS_OBSERVACAO, 
                    SSOL_ED_LOCALIZACAO,
                    SSOL_NR_TOMBO,
                    SSOL_NM_USUARIO_EXTERNO,
                    SSOL_NR_CPF_EXTERNO,
                    SSOL_DS_EMAIL_EXTERNO,
                    SSOL_NR_TELEFONE_EXTERNO,
                    SSOL_ID_TIPO_CAD,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    --MOVIMENTACAO
                    MOVI_ID_MOVIMENTACAO,
                    MOFA_ID_FASE,
                    MODE_ID_CAIXA_ENTRADA,
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO, 
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                    TO_DATE(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') ORDEM_MOV ,
                    --CAIXA DE ATENDIMENTO
                    CXEN_ID_CAIXA_ENTRADA,
                    CXEN_DS_CAIXA_ENTRADA,
                    --GRUPO DE SERVICO
                    SGRS_ID_GRUPO,
                    --SERVICO
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO,
                    TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY HH24:MI:SS') SSES_DT_INICIO_VIDEO,
                    SSES_IC_VIDEO_REALIZADA,
                    --NIVEL ATENDIMENTO
                    SNAS_ID_NIVEL,
                    SNAT_CD_NIVEL,
                    SNAT_ID_NIVEL,
                    --ESPERA
                    SESP_DH_LIMITE_ESP, 
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG, 
                    --FASE
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_CD_MATRICULA,
                    --TO_CHAR(SAD_TB_MOFA_MOVI_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE,
                    --LOTACAO
                    RHCL.LOTA_COD_LOTACAO,
                    RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO,RHCL.LOTA_COD_LOTACAO) LOTA_DSC_LOTACAO,
                    RHCL.LOTA_SIGLA_LOTACAO,  
                    RHCL.LOTA_SIGLA_SECAO,
                    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO,RHCL.LOTA_COD_LOTACAO) FAMILIA,
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,
                    --PRAZO
					TO_CHAR(SSPA_DT_PRAZO ,'dd/mm/yyyy HH24:MI:SS') SSPA_DT_PRAZO,
                    SSPA_IC_CONFIRMACAO,
                    FADM_DS_FASE,
                    TO_CHAR(( SELECT SSPA_1.SSPA_DT_PRAZO 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                                    AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO)
                                                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO AND SSPA_IC_CONFIRMACAO = 'S'),'DD/MM/YYYY HH24:MI:SS') SSPA_DT_PRAZO
                ";

                break;
            case 5:
                /* Minhas solicitacoes */
                $this->currentselectCaixa = "
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
                ";

                break;
            case 6:
                /* Pesquisa de solicitacoes */
                $this->currentselectCaixa = "
                    SELECT DISTINCT
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE, 

                        (SELECT LISTAGG(A.cate_no_categoria, ', ') WITHIN GROUP (ORDER BY A.cate_no_categoria) AS CATEGORIA
                        FROM SOS.SOS_TB_CATE_CATEGORIA A,
                        SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                        WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                        AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                        AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                        AND B.caso_id_documento = SSOL_ID_DOCUMENTO) AS CATEGORIA,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO, 
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS')DH_CADASTRO,
                    DOCM_DH_CADASTRO, 
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                    MOFA_ID_MOVIMENTACAO, 
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

                    --movimentacao origem sad_tb_movi_movimentacao
                    MOVI_DH_ENCAMINHAMENTO,

                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO,
                    SSER_DS_SERVICO, 

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP,
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

                    --vinculacao flag
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,
                    SSOL_NM_USUARIO_EXTERNO 
                ";

                break;
            case 7:
                /* Solicitações por periodo SLA */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 

                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO, 
                    DOCM_CD_MATRICULA_CADASTRO,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                    DOCM_DH_CADASTRO DH_CADASTRO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_FASE,TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 
                    MOFA_ID_MOVIMENTACAO, 
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA_CD_MATRICULA) NOME_USARIO_BAIXA,

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --nivel sos_tb_snat_nivel_atendimento
                    SNAT_CD_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO, 

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP, 
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

                    --avaliacao  sos_tb_stsa_tipo_satisfacao
                    STSA_DS_TIPO_SAT,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME((SELECT MOFA_1.MOFA_CD_MATRICULA
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1000 ))) NOME_USARIO_BAIXA

                ";

                break;
            case 8:
                /* Minhas Solicitações por periodo SLA */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 

                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE, 
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE, 

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO, 
                    DOCM_CD_MATRICULA_CADASTRO,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DH_CADASTRO,
                    DOCM_DH_CADASTRO, 
                    
                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_FASE,
                    
                    MOFA_ID_MOVIMENTACAO, 
                    TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') DH_FASE,
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                    MOFA_DH_FASE,

                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO, 
                    MODE_CD_SECAO_UNID_DESTINO, 

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO, 

                    --avaliacao  sos_tb_stsa_tipo_satisfacao
                    STSA_DS_TIPO_SAT,

                    --data do encaminhamento
                    MOVI_DH_ENCAMINHAMENTO
                ";

                break;
            case 12:
                /* Caixa de atendimento com nível */
                $this->currentselectCaixa = "
                    SELECT DISTINCT
                    
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL.SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    MOFA_ID_FASE,
                    
                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    TO_CHAR(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS') MOVI_DH_ENCAMINHAMENTO,

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO,
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --nivel sos_tb_snat_nivel_atendimento
                    SNAT_CD_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO,

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP,
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

                    --vinculacao flag
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,

                    --vinculacao mostra vinculacao
                    SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL.SSOL_ID_DOCUMENTO) MOSTRA_VINCULACAO,
                    --Prazo
                    TO_CHAR(( SELECT SSPA_1.SSPA_DT_PRAZO 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                                    AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO AND SSPA_IC_CONFIRMACAO = 'S'),'DD/MM/YYYY HH24:MI:SS') SSPA_DT_PRAZO
                ";

                break;
            case 13:
                /* Dados solicitação garantia */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 
                    --DOCUMENTO
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO,
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    --SOLICITACAO
                    SSOL_ID_DOCUMENTO,
                    SSOL_DS_OBSERVACAO, 
                    SSOL_ED_LOCALIZACAO,
                    SSOL_NR_TOMBO,
                    SSOL_NM_USUARIO_EXTERNO,
                    SSOL_NR_CPF_EXTERNO,
                    SSOL_DS_EMAIL_EXTERNO,
                    SSOL_NR_TELEFONE_EXTERNO,
                    SSOL_ID_TIPO_CAD,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    --MOVIMENTACAO ORIGEM
                    MOVI_ID_MOVIMENTACAO,
                    --MOVIMENTACAO
                    MODE_ID_CAIXA_ENTRADA,
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO, 
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                    TO_DATE(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') ORDEM_MOV ,
                    --SERVICO
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO,
                    TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY HH24:MI:SS') SSES_DT_INICIO_VIDEO,
                    SSES_IC_VIDEO_REALIZADA,
                    --FASE
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_CD_MATRICULA,
                    --TO_CHAR(SAD_TB_MOFA_MOVI_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE,
                    --Garantia
                    NEGA_ID_MOVIMENTACAO,           
                    TO_CHAR(NEGA_DH_SOLIC_GARANTIA,'DD/MM/YYYY HH24:MI:SS') NEGA_DH_SOLIC_GARANTIA_CHAR,
                    NEGA_DH_SOLIC_GARANTIA NEGA_DH_SOLIC_GARANTIA_DATE,         
                    NEGA_CD_MATR_SOLIC,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_SOLIC) NEGA_CD_MATR_SOLIC_NM, 
                    NEGA_DS_JUSTIFICATIVA_PEDIDO,   
                    NEGA_IC_ACEITE,                 
                    NEGA_DH_ACEITE_RECUSA,  
                    TO_CHAR(NEGA_DH_ACEITE_RECUSA,'DD/MM/YYYY HH24:MI:SS') NEGA_DH_ACEITE_RECUSA_CHAR,
                    NEGA_DH_ACEITE_RECUSA NEGA_DH_ACEITE_RECUSA_DATE,  
                    NEGA_CD_MATR_ACEITE_RECUSA,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_ACEITE_RECUSA) NEGA_CD_MATR_ACEITE_RECUSA_NM,      
                    NEGA_DS_JUST_ACEITE_RECUSA,     
                    NEGA_IC_CONCORDANCIA,           
                    NEGA_DH_CONCORDANCIA,           
                    NEGA_CD_MATR_CONCORDANCIA, 
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(NEGA_CD_MATR_CONCORDANCIA) NEGA_CD_MATR_CONCORDANCIA_NM,      
                    NEGA_DS_JUSTIFICATIVA_CONCOR,
                    --vinculacao flag
                    SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,
                    --vinculacao mostra vinculacao
                    SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL.SSOL_ID_DOCUMENTO) MOSTRA_VINCULACAO
                ";
                break;
            case 14:
                // Relatorios de sosti 
                $this->currentselectCaixa = "
                    SELECT DISTINCT
                    
                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE, 

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO, 
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,
                    TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS')DH_CADASTRO,
                    DOCM_DH_CADASTRO, 
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1)  DESCRICAO_SERVICO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL,
                    MOFA_ID_MOVIMENTACAO, 
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,
                    FADM_DS_FASE DESCRICAO_FASE,
                    
                    --movimentacao origem sad_tb_movi_movimentacao
                    MOVI_DH_ENCAMINHAMENTO,

                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO,
                    SSER_DS_SERVICO, 

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP,
                    TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,
                    
                    -- classificacao da avaliacao
                    STSA_DS_TIPO_SAT TIPO_AVALIACAO, 
                    
                    " .
                        $this->colunasBaixaSolitacao()
                        .
                        $this->colunasDescricaoAvaliacao()
                ;
                break;
             case 15:
                // Relatorios de sosti 
                $this->currentselectCaixa = " SELECT COUNT(*) QTDE ";
                 break;
             case 16:
                /* Dados solicitação mais antiga */
                $this->currentselectCaixa = "
                    SELECT DISTINCT 

                    --solicitação sos_tb_ssol_solicitacao
                    SSOL_ID_DOCUMENTO,
                    SSOL_ED_LOCALIZACAO, 
                    SSOL_DS_EMAIL_EXTERNO, 
                    SSOL_NR_TELEFONE_EXTERNO, 

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO, 
                    DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
                    DOCM_CD_MATRICULA_CADASTRO, 
                    DOCM_SG_SECAO_GERADORA,
                    DOCM_CD_LOTACAO_GERADORA,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,


                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    MOFA_ID_FASE,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,

                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,
                    MOVI_ID_MOVIMENTACAO, 

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA,

                    --nivel fase sos_tb_snas_nivel_atend_solic
                    SNAS_ID_NIVEL,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO, 

                    --espera sos_tb_sesp_solic_espera
                    SESP_DH_LIMITE_ESP, 

                    -- lotacao geradora
                    LOTA_DSC_LOTACAO, 
                    LOTA_SIGLA_LOTACAO
                ";
                break;
            default:
                break;
        }



        return $this->currentselectCaixa;
    }

    /**
     * @r 
     * 
     */
    public function selectCountIdSolicitacao($label = 'COUNT') {
        $this->selectCountIdSolicitacao = "
        SELECT COUNT(DISTINCT SSOL_ID_DOCUMENTO) $label
        ";
        return $this->selectCountIdSolicitacao;
    }

    public function from() {

        $this->from = "
                    FROM
                ";
        return $this->from;
    }

    public function where() {

        $this->from = "
                    WHERE
                ";
        return $this->from;
    }

    public function fromSubQueryCaixaAtendimento(Trf1_Sosti_Negocio_Caixas_OpcoesConsulta $opcoes) {

        $qr = "
            FROM
            (
                SELECT 
                SUB_3.*,
                MOFA.*
        ";

        ($opcoes->getOpServico()) ?
                        (
                        $qr .=
                        "
            ,
           (SELECT MAX(SSES_1.SSES_DH_FASE)
            FROM  SOS_TB_SSES_SERVICO_SOLIC SSES_1
            WHERE SSES_1.SSES_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
           )DATA_ULTIMO_SERVICO
        "
                        ) : ('');

        ($opcoes->getOpEspera()) ?
                        (
                        $qr .=
                        "
           ,
           (
           SELECT MAX(SESP_1.SESP_DH_FASE) 
           FROM   SOS_TB_SESP_SOLIC_ESPERA SESP_1
           WHERE  SESP_1.SESP_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
           )DATA_ULTIMA_ESPERA
        "
                        ) : ('');

        ($opcoes->getOpPrazo()) ?
                        (
                        $qr .=
                        "
           ,
           (
           SELECT MAX(SSPA_1.SSPA_DH_FASE) 
           FROM   SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
           WHERE  SSPA_1.SSPA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
           )DATA_ULTIMO_PRAZO
        "
                        ) : ('');

        ($opcoes->getOpNivel()) ?
                        (
                        $qr .=
                        "
            ,
           (
           SELECT MAX(SNAS_1.SNAS_DH_FASE) 
           FROM   SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_1
           WHERE  SNAS_1.SNAS_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
           )DATA_ULTIMO_NIVEL
        "
                        ) : ('');


        $qr .= "
            FROM
            (
                SELECT MAX(MOFA_DH_FASE) ULTIMA_FASE_DATA, ULTIMA_MOVIMENTACAO
                FROM
                (       
                    SELECT 
                    MAX(MODO_MOVI_R_1.MODO_ID_MOVIMENTACAO) ULTIMA_MOVIMENTACAO
                    FROM
                    (
                     SELECT * FROM SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ";
        $qr .= (!is_array($opcoes->getOpIdCaixa())) ? (" WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA = " . $opcoes->getOpIdCaixa()) : (" WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA IN (" . implode(',', $opcoes->getOpIdCaixa()) . ") ");
        $qr .= "
                    ) SUB_1
                    INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                    ON MODO_MOVI.MODO_ID_MOVIMENTACAO = SUB_1.MODE_ID_MOVIMENTACAO
                    INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_R_1
                    ON MODO_MOVI_R_1.MODO_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
                    GROUP BY MODO_MOVI_R_1.MODO_ID_DOCUMENTO
                )SUB_2
                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                ON  SUB_2.ULTIMA_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                ON SUB_2.ULTIMA_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO 
                AND  ";
        $qr .= (!is_array($opcoes->getOpIdCaixa())) ? (" MODE_MOVI.MODE_ID_CAIXA_ENTRADA = " . $opcoes->getOpIdCaixa()) : (" MODE_MOVI.MODE_ID_CAIXA_ENTRADA IN (" . implode(',', $opcoes->getOpIdCaixa()) . ") ");
        $qr .= "
                GROUP BY ULTIMA_MOVIMENTACAO
            )SUB_3
            INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
            ON  SUB_3.ULTIMA_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
            AND SUB_3.ULTIMA_FASE_DATA     = MOFA.MOFA_DH_FASE
            WHERE MOFA_ID_FASE NOT IN (1000,1014,1026,1081) -- 1000 baixa 1014 avaliada 1026 cancelada 1081 DESVINCULADA SLA
        )SUB_4
       ";

        ($opcoes->getOpServico()) ?
                        (
                        $qr .=
                        "
       LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
       ON  SUB_4.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
       AND SUB_4.DATA_ULTIMO_SERVICO = SSES.SSES_DH_FASE
       LEFT JOIN SOS_TB_SSER_SERVICO SSER
       ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO
          "
                        ) : ('');

        ($opcoes->getOpNivel()) ?
                        (
                        $qr .=
                        "
       LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS
       ON  SUB_4.MOFA_ID_MOVIMENTACAO  = SNAS.SNAS_ID_MOVIMENTACAO
       AND SUB_4.DATA_ULTIMO_NIVEL = SNAS.SNAS_DH_FASE
       LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
       ON  SNAT.SNAT_ID_NIVEL         =  SNAS.SNAS_ID_NIVEL 
        "
                        ) : ('');

        ($opcoes->getOpEspera()) ?
                        (
                        $qr .=
                        "
       LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP
       ON  SUB_4.MOFA_ID_MOVIMENTACAO  = SESP.SESP_ID_MOVIMENTACAO
       AND SUB_4.DATA_ULTIMA_ESPERA = SESP.SESP_DH_FASE
        "
                        ) : ('');

        ($opcoes->getOpPrazo()) ?
                        (
                        $qr .=
                        "
        LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA
       ON   SUB_4.MOFA_ID_MOVIMENTACAO = SSPA.SSPA_ID_MOVIMENTACAO
       AND SUB_4.DATA_ULTIMO_PRAZO = SSPA.SSPA_DH_FASE
        "
                        ) : ('');

        $qr .= "
        INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = SUB_4.MOFA_ID_MOVIMENTACAO

        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON MOVI.MOVI_ID_MOVIMENTACAO = MODO_MOVI.MODO_ID_MOVIMENTACAO

        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        INNER JOIN SOS_TB_SSOL_SOLICITACAO SSOL
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
       ";

        return $qr;
    }

    public function innerJoinSolicitacaoDocumentoMovimentacaoFase() {

        $this->innerJoinSolicitacaoDocumentoMovimentacaoFase = "
        -- solicitacao    
        SOS_TB_SSOL_SOLICITACAO SSOL

        -- documento
        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

        -- movimentacao origem
        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

        -- movimentacao destino
        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

        --fase
        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
        
        --descricao fase
        INNER JOIN SAD_TB_FADM_FASE_ADM FADM
        ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE
        ";

        return $this->innerJoinSolicitacaoDocumentoMovimentacaoFase;
    }

    public function leftJoinFaseServico() {

        $this->leftJoinFaseServico = "
        --servico

        LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_SSER_SERVICO SSER
        ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO 
        ";

        return $this->leftJoinFaseServico;
    }
    
    public function leftJoinPriorizaDemanda() {

        $this->leftJoinPriorizaDemanda = "
        -- prioriza demanda

        LEFT JOIN SOS.SOS_TB_PRDE_PRIORIZA_DEMANDA PRDE 
        ON PRDE.PRDE_ID_SOLICITACAO = DOCM.DOCM_ID_DOCUMENTO
        ";

        return $this->leftJoinPriorizaDemanda;
    }

    public function leftJoinFaseNivel() {

        $this->leftJoinFaseNivel = "
        --nivel

        LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SNAS.SNAS_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT
        ON  SNAT.SNAT_ID_NIVEL         =  SNAS.SNAS_ID_NIVEL
        ";
        return $this->leftJoinFaseNivel;
    }

    public function leftJoinFaseEspera() {

        $this->leftJoinFaseEspera = "
        --espera

        LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP
        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SESP.SESP_ID_MOVIMENTACAO
        ";
        return $this->leftJoinFaseEspera;
    }

    public function leftJoinFasePrazo() {

        $this->leftJoinFaseEspera = "
        --prazo

        LEFT JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA
        ON   MOFA.MOFA_ID_MOVIMENTACAO = SSPA.SSPA_ID_MOVIMENTACAO 
        ";
        return $this->leftJoinFaseEspera;
    }

    public function leftJoinFaseAvaliacao() {

        $this->leftJoinFaseAvaliacao = "
        --avaliação    

        LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS
        ON   MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA
        ON   SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT 
        ";
        return $this->leftJoinFaseAvaliacao;
    }

    public function leftJoinServicoGrupoServico() {

        $this->leftJoinServicoGrupoServico = "
        --grupo serviço e serviço    

         LEFT JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
         ON SGRS.SGRS_ID_GRUPO = SSER.SSER_ID_GRUPO 
        ";
        return $this->leftJoinServicoGrupoServico;
    }

    public function leftJoinLotacaoGeradora() {

        $this->leftJoinLotacaoGeradora = "
        --lotacao geradora da solicitacao

        LEFT JOIN RH_CENTRAL_LOTACAO RHCL
        ON  RHCL.LOTA_SIGLA_SECAO = DOCM.DOCM_SG_SECAO_GERADORA
        AND RHCL.LOTA_COD_LOTACAO = DOCM.DOCM_CD_LOTACAO_GERADORA
        ";
        return $this->leftJoinLotacaoGeradora;
    }

    public function innerJoinMovimentacaoDestinatarioCaixaDeEntrada() {

        $this->innerJoinCaixaDeEntrada = "
        --Caixa de entrada
        
        INNER JOIN SAD_TB_CXEN_CAIXA_ENTRADA CXEN
        ON  MODE_MOVI.MODE_ID_CAIXA_ENTRADA = CXEN.CXEN_ID_CAIXA_ENTRADA
        INNER JOIN SAD_TB_CXGS_GRUPO_SERVICO CXGS
        ON CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
        ";
        return $this->innerJoinCaixaDeEntrada;
    }

    public function innerJoinCaixaDeEntradaGrupoServico() {

        $this->innerJoinCaixaGrupoServico = "
        --Grupo de servico
        
        INNER JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS
        ON CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
        ";
        return $this->innerJoinCaixaGrupoServico;
    }

    public function innerJoinGarantia() {

        $this->innerJoinCaixaGrupoServico = "
        --Grupo de servico
        
        INNER JOIN SOS_TB_NEGA_NEGOCIA_GARANTIA NEGA
        ON MOVI.MOVI_ID_MOVIMENTACAO         = NEGA.NEGA_ID_MOVIMENTACAO
        ";
        return $this->innerJoinCaixaGrupoServico;
    }

    public function leftJoinFechamento() {

        $this->leftJoinFechamento = "
        --chamandos fechamento

        LEFT JOIN SOS_TB_FEMV_FECHAMENTO_MOVIMEN
        ON FEMV_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
        ";
        return $this->leftJoinFechamento;
    }

    public function innerJoinPapd() {
        $this->innerJoinPapd = "
        -- Acompanhamento de SOSTI
           
        INNER JOIN SAD_TB_PAPD_PARTE_PROC_DOC PAPD
        ON DOCM.DOCM_ID_DOCUMENTO = PAPD.PAPD_ID_DOCUMENTO
        ";
        return $this->innerJoinPapd;
    }

    public function leftJoinServicosSistemas() {

        $this->innerJoinServicosSistemas = "
        --Ocorrencia, categoria, emergencia de sistemas
        
        LEFT JOIN SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
        ON MOVI.MOVI_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
        LEFT JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
        ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
        LEFT JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA CTSS
        ON ASIS.ASIS_ID_CATEGORIA_SERVICO = CTSS.CTSS_ID_CATEGORIA_SERVICO
        LEFT JOIN SOS_TB_OSIS_OCORRENCIA_SISTEMA OSIS
        ON OSIS.OSIS_ID_OCORRENCIA = ASIS.ASIS_ID_OCORRENCIA
        LEFT JOIN SOS_TB_SESI_SERVICO_SISTEMA SESI
        ON CTSS.CTSS_ID_SERVICO_SISTEMA = SESI.SESI_ID_SERVICO_SISTEMA
        ";
        return $this->innerJoinServicosSistemas;
    }

    public function colunasServicosSistemas() {

        $this->colunasServicosSistemas = "
        ,
		INITCAP(LOWER(OSIS_NM_OCORRENCIA))OSIS_NM_OCORRENCIA,
        INITCAP( LOWER(CTSS_NM_CATEGORIA_SERVICO)) CTSS_NM_CATEGORIA_SERVICO,
        CTSS_ID_CATEGORIA_SERVICO,
        ASSO_IC_ATENDIMENTO_EMERGENCIA,
        ASSO_IC_SOLUCAO_PROBLEMA,
        ASSO_IC_SOLUCAO_CAUSA_PROBLEMA,
        ASIS_IC_NIVEL_CRITICIDADE,
        (SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
           FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
         INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
           ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
         WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_INICIO_ATENDIMENTO) ASIS_PRZ_INICIO_ATENDIMENTO,
        
        --CASE 
        --WHEN ASSO_IC_SOLUCAO_CAUSA_PROBLEMA = 'S' AND ASSO_IC_ATENDIMENTO_EMERGENCIA = 'S' THEN
        (SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
           FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
         INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
           ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
         WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_PROBLEMA) 
        --END 
        ASIS_PRZ_SOL_PROBLEMA,
        
        --CASE
        --WHEN ASSO_IC_SOLUCAO_PROBLEMA = 'S' AND ASSO_IC_ATENDIMENTO_EMERGENCIA = 'S' THEN
        (SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
           FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
         INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
           ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
         WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_CAUSA_PROBLEMA) 
        --END 
        ASIS_PRZ_SOL_CAUSA_PROBLEMA,
        
        (SELECT PRAT_QT_PRAZO||'|'||UNME_DS_UNID_MEDIDA||'|'||PRAT_IC_CONTAGEM
           FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT
         INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME
           ON  UNME.UNME_ID_UNID_MEDIDA = PRAT.PRAT_ID_UNIDADE_MEDIDA
         WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_EXECUCAO_SERVICO) ASIS_PRZ_EXECUCAO_SERVICO,
         CASE  
            WHEN   ((SELECT ASIS_ID_OCORRENCIA 
                    FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO
                    INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS
                    ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS
                    WHERE ASIS_ID_CATEGORIA_SERVICO = 2
                    AND   MOFA_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO
                    ) IS NULL )
                THEN 'N'  
            ELSE 'S'  
         END AS CORRETIVA
        ";
        return $this->colunasServicosSistemas;
    }

    public function whereUltimaMovimentacao($and = true) {

        $this->whereUltimaMovimentacao = "
        --última movimentação

        (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
        WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                        AND DOCM_1.DOCM_ID_TIPO_DOC = 160)
        )
        ";

        if ($and) {
            $this->whereUltimaMovimentacao = " 
            AND 
            " . $this->whereUltimaMovimentacao;
        }

        return $this->whereUltimaMovimentacao;
    }

    public function whereNaoFechadoSla($and = true) {

        $this->whereNaoFechadoSla = "
        --Não listar os chamandos fechados
        FEMV_ID_MOVIMENTACAO IS NULL
        ";

        if ($and) {
            $this->whereNaoFechadoSla = " 
            AND 
            " . $this->whereNaoFechadoSla;
        }

        return $this->whereNaoFechadoSla;
    }

    public function whereUltimoServico($and = true) {

        $this->whereUltimoServico = "
        --último serviço                                                  
        (SSER.SSER_ID_SERVICO = (SELECT SSES_1.SSES_ID_SERVICO 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SSER.SSER_ID_SERVICO IS NULL)

        ";

        if ($and) {
            $this->whereUltimoServico = " 
            AND 
            " . $this->whereUltimoServico;
        }

        return $this->whereUltimoServico;
    }

    public function whereUltimoNivel($and = true) {

        $this->whereUltimoNivel = "
        --último nível
        (SNAT.SNAT_ID_NIVEL = (SELECT SNAS_1.SNAS_ID_NIVEL 
                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                            INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_1
                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SNAS_1.SNAS_ID_MOVIMENTACAO
                                            AND MOFA_1.MOFA_DH_FASE          = SNAS_1.SNAS_DH_FASE
                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                        FROM SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                            INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_2
                                                                            ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SNAS_2.SNAS_ID_MOVIMENTACAO
                                                                            AND MOFA_2.MOFA_DH_FASE          = SNAS_2.SNAS_DH_FASE
                                                                            WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)  OR SNAT.SNAT_ID_NIVEL IS NULL)
        ";

        if ($and) {
            $this->whereUltimoNivel = " 
            AND 
            " . $this->whereUltimoNivel;
        }

        return $this->whereUltimoNivel;
    }

    public function whereUltimaEspera($and = true) {

        $this->whereUltimaEspera = "
        --última espera
        (SESP.SESP_DH_LIMITE_ESP = ( SELECT SESP_1.SESP_DH_LIMITE_ESP 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                    INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_1
                                                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SESP_1.SESP_ID_MOVIMENTACAO
                                                    AND MOFA_1.MOFA_DH_FASE          = SESP_1.SESP_DH_FASE
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                    INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_2
                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SESP_2.SESP_ID_MOVIMENTACAO
                                                                                    AND MOFA_2.MOFA_DH_FASE          = SESP_2.SESP_DH_FASE
                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SESP.SESP_DH_LIMITE_ESP IS NULL)                                                                                    
        ";

        if ($and) {
            $this->whereUltimaEspera = " 
            AND 
            " . $this->whereUltimaEspera;
        }

        return $this->whereUltimaEspera;
    }

    public function whereUltimoPrazo($and = true) {

        $this->whereUltimoPrazo = "
        --última espera
        (SSPA.SSPA_DT_PRAZO = ( SELECT SSPA_1.SSPA_DT_PRAZO 
                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                                                    ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                                                    AND MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                                                WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                    FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSPA_2.SSPA_DH_FASE
                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                                AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SSPA.SSPA_DT_PRAZO IS NULL)                                                                                    
        ";

        if ($and) {
            $this->whereUltimoPrazo = " 
            AND 
            " . $this->whereUltimoPrazo;
        }

        return $this->whereUltimoPrazo;
    }

    public function whereUltimaAvaliacao($and = true) {

        $this->whereUltimaAvaliacao = "
        --última avaliacao
        (SAVS.SAVS_ID_TIPO_SAT = ( SELECT SAVS_1.SAVS_ID_TIPO_SAT
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                   INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_1
                                                   ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SAVS_1.SAVS_ID_MOVIMENTACAO
                                                   AND MOFA_1.MOFA_DH_FASE          = SAVS_1.SAVS_DH_FASE
                                            WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                FROM  SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                   INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_2
                                                                                   ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SAVS_2.SAVS_ID_MOVIMENTACAO
                                                                                   AND MOFA_2.MOFA_DH_FASE          = SAVS_2.SAVS_DH_FASE
                                                                                WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                            AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO )OR SAVS.SAVS_ID_TIPO_SAT IS NULL)                                                                                 
        ";

        if ($and) {
            $this->whereUltimaAvaliacao = " 
            AND 
            " . $this->whereUltimaAvaliacao;
        }

        return $this->whereUltimaAvaliacao;
    }

    public function whereEmAtendimento($and = true) {

        $this->whereEmAtendimento = "
        -- em atendimento
        -- dispensa fases
        -- 1000 baixa
        -- 1014 avaliada
        -- 1026 cancelada
        -- 1081 desvinculada SLA --
        MOFA.MOFA_ID_FASE NOT IN (1000, 1014, 1026, 1081)                                                                                  
        ";

        if ($and) {
            $this->whereEmAtendimento = " 
            AND 
            " . $this->whereEmAtendimento;
        }

        return $this->whereEmAtendimento;
    }

    public function whereUltimafaseBaixa($and = true) {

        $this->whereUltimafaseBaixa = "
        -- 1000 baixa
        MOFA.MOFA_ID_FASE = 1000
        ";

        if ($and) {
            $this->whereUltimafaseBaixa = " 
            AND 
            " . $this->whereUltimafaseBaixa;
        }

        return $this->whereUltimafaseBaixa;
    }

    public function whereUltimafaseAvaliadaPositivamente($and = true) {

        $this->whereUltimafaseAvaliadaPositivamente = "
        -- 1014 avaliada positivamente
        MOFA.MOFA_ID_FASE = 1014
        ";

        if ($and) {
            $this->whereUltimafaseAvaliadaPositivamente = " 
            AND 
            " . $this->whereUltimafaseAvaliadaPositivamente;
        }

        return $this->whereUltimafaseAvaliadaPositivamente;
    }

    public function whereTipoSolicitacao($and = true) {

        $this->whereTipoSolicitacao = "
            -- tipo documento solicitacao
            DOCM_ID_TIPO_DOC = 160                                                                               
        ";

        if ($and) {
            $this->whereTipoSolicitacao = " 
            AND 
            " . $this->whereTipoSolicitacao;
        }

        return $this->whereTipoSolicitacao;
    }

    public function whereIdSolicitacao($and = true, $idSolicitacao) {

        $this->whereIdSolicitacao = "
            -- id da solicitacao 
            DOCM.DOCM_ID_DOCUMENTO = $idSolicitacao                                                                            
        ";

        if ($and) {
            $this->whereIdSolicitacao = " 
            AND 
            " . $this->whereIdSolicitacao;
        }

        return $this->whereIdSolicitacao;
    }

    public function whereMovimentacao($and = true, $idMovimentacao) {

        $this->whereMoivmentacao = "
            -- Movimentacao por id
            MOVI.MOVI_ID_MOVIMENTACAO = $idMovimentacao                                                                            
        ";

        if ($and) {
            $this->whereMoivmentacao = " 
            AND 
            " . $this->whereMoivmentacao;
        }

        return $this->whereMoivmentacao;
    }

    public function whereUltimaFasePorMovimentacao($and = true, $idMovimentacao) {

        $this->whereUltimaFasePorMovimentacao = "
            -- Ultima fase Movimentacao por id 
            (MOFA.MOFA_DH_FASE,MOFA.MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                                                                FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = $idMovimentacao)
        ";
        if ($and) {
            $this->whereUltimaFasePorMovimentacao = " 
            AND 
            " . $this->whereUltimaFasePorMovimentacao;
        }

        return $this->whereUltimaFasePorMovimentacao;
    }

    public function whereSolicitante($and = true, $matricula) {

        $this->whereSolicitante = "
            -- matricula do solicitante 
            DOCM.DOCM_CD_MATRICULA_CADASTRO = '$matricula'                                                                           
        ";

        if ($and) {
            $this->whereSolicitante = " 
            AND 
            " . $this->whereSolicitante;
        }

        return $this->whereSolicitante;
    }

    public function whereCaixa($and = true, $idCaixa) {
        if (!is_array($idCaixa)) {
            $this->whereCaixa = "
                -- escolhe caixa
                 MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa                                                                            
            ";
        } else {
            $this->whereCaixa = "
                -- escolhe caixa
                 MODE_MOVI.MODE_ID_CAIXA_ENTRADA IN (" . implode(',', $idCaixa) . ")                                                                            
            ";
        }

        if ($and) {
            $this->whereCaixa = " 
            AND 
            " . $this->whereCaixa;
        }

        return $this->whereCaixa;
    }

    public function whereNivel($and = true, $idNivel) {

        $this->whereNivel = "
            -- escolhe nivel
            SNAT.SNAT_CD_NIVEL = $idNivel                                                                           
        ";

        if ($and) {
            $this->whereNivel = " 
            AND 
            " . $this->whereNivel;
        }

        return $this->whereNivel;
    }

    public function whereNaoEstaEmEspera($and = true, $idNivel) {

        $this->whereNaoEstaEmEspera = "
            -- nao esta em espera
            (TRUNC(SESP.SESP_DH_LIMITE_ESP - SYSDATE) <= 0 OR SESP.SESP_DH_LIMITE_ESP IS NULL)                                                                         
        ";

        if ($and) {
            $this->whereNaoEstaEmEspera = " 
            AND 
            " . $this->whereNaoEstaEmEspera;
        }

        return $this->whereNaoEstaEmEspera;
    }

    public function whereAtendente($and = true, $matricula) {

        $this->whereAtendente = "
            -- Com o atendente
            SSOL.SSOL_CD_MATRICULA_ATENDENTE = '$matricula'                                                                        
        ";

        if ($and) {
            $this->whereAtendente = " 
            AND 
            " . $this->whereAtendente;
        }

        return $this->whereAtendente;
    }

    public function whereSemAtendenteOuComAtendentePorMatricula($and = true, $matricula) {

        $this->whereSemAtendenteOuComAtendentePorMatricula = "
            -- sem atendente ou com um atendente específico
            (SSOL.SSOL_CD_MATRICULA_ATENDENTE IS NULL OR SSOL.SSOL_CD_MATRICULA_ATENDENTE = '$matricula')                                                                         
        ";

        if ($and) {
            $this->whereSemAtendenteOuComAtendentePorMatricula = " 
            AND 
            " . $this->whereSemAtendenteOuComAtendentePorMatricula;
        }

        return $this->whereSemAtendenteOuComAtendentePorMatricula;
    }

    public function whereEncaminhadasdeUmaCaixa($and = true, $idCaixaOrigem) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $aux = $db->fetchCol("SELECT '''' || LOTA_SIGLA_SECAO || '|' || LOTA_COD_LOTACAO || '''' CAIXA
            FROM    
            (
              SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO            
              FROM (                           
                      SELECT LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, LOTA_LOTA_COD_LOTACAO_PAI, LOTA_TIPO_LOTACAO
                      FROM RH_CENTRAL_LOTACAO
                      WHERE   LOTA_SIGLA_SECAO   = 'TR'
                      AND  LOTA_DAT_FIM IS NULL
                  )
              CONNECT BY PRIOR LOTA_COD_LOTACAO = LOTA_LOTA_COD_LOTACAO_PAI 
              AND LOTA_TIPO_LOTACAO NOT IN (2) /*SUBSEÃ‡ÃƒO*/
              START WITH LOTA_COD_LOTACAO = 2
          )");
        $unidades = implode(',', $aux);
        $this->whereEncaminhadasdeUmaCaixa = "
            -- sem atendente ou com um atendente específico
            SSOL.SSOL_ID_DOCUMENTO IN (SELECT  DISTINCT SSOL_ID_DOCUMENTO
                                                        FROM   SOS_TB_SSOL_SOLICITACAO A
                                                               INNER JOIN SAD_TB_DOCM_DOCUMENTO B
                                                               ON  A.SSOL_ID_DOCUMENTO     = B.DOCM_ID_DOCUMENTO
                                                               INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO D
                                                               ON  B.DOCM_ID_DOCUMENTO     = D.MODO_ID_DOCUMENTO
                                                               INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO C
                                                               ON  D.MODO_ID_MOVIMENTACAO  = C.MOVI_ID_MOVIMENTACAO
                                                               INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO E
                                                               ON  C.MOVI_ID_MOVIMENTACAO  = E.MODE_ID_MOVIMENTACAO
                                                               INNER JOIN SAD_TB_MOFA_MOVI_FASE F
                                                               ON  C.MOVI_ID_MOVIMENTACAO  = F.MOFA_ID_MOVIMENTACAO
                                                        AND E.MODE_ID_CAIXA_ENTRADA IN (SELECT  CXEN_ID_CAIXA_ENTRADA  
                                                                FROM SAD_TB_TPCX_TIPO_CAIXA TPCX,
                                                                         SAD_TB_CXEN_CAIXA_ENTRADA CXEN,
                                                                         SAD_TB_CXGS_GRUPO_SERVICO CXGS,
                                                                         SOS_TB_SGRS_GRUPO_SERVICO SGRS,
                                                                         RH_CENTRAL_LOTACAO RHLOTA
                                                                 WHERE TPCX.TPCX_ID_TIPO_CAIXA    = CXEN.CXEN_ID_TIPO_CAIXA
                                                                 AND   CXEN.CXEN_ID_CAIXA_ENTRADA = CXGS.CXGS_ID_CAIXA_ENTRADA
                                                                 AND   CXGS.CXGS_ID_GRUPO         = SGRS.SGRS_ID_GRUPO
                                                                 AND   SGRS.SGRS_SG_SECAO_LOTACAO = RHLOTA.LOTA_SIGLA_SECAO
                                                                 AND   SGRS.SGRS_CD_LOTACAO = RHLOTA.LOTA_COD_LOTACAO
                                                                 AND   (SGRS.SGRS_SG_SECAO_LOTACAO||'|'||SGRS.SGRS_CD_LOTACAO) IN
                                                                                                            (   
                                                                                                                    $unidades
                                                                                                            ) )
                                                        AND C.MOVI_ID_CAIXA_ENTRADA = $idCaixaOrigem
                                                        AND B.DOCM_ID_TIPO_DOC = 160)                                                                         
        ";

        if ($and) {
            $this->whereEncaminhadasdeUmaCaixa = " 
            AND 
            " . $this->whereEncaminhadasdeUmaCaixa;
        }

        return $this->whereEncaminhadasdeUmaCaixa;
    }

    public function whereUltimaFaseHistorico($and = true, $fase) {

        $this->whereUltimaFaseHistorico = "
            -- contendo a última fase no historico
            (MOFA.MOFA_DH_FASE, MOFA.MOFA_ID_MOVIMENTACAO)  =  (SELECT MAX(MOFA_1.MOFA_DH_FASE), MAX(MOFA_1.MOFA_ID_MOVIMENTACAO)
                                                                    FROM  SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1
                                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_1.MODE_ID_MOVIMENTACAO
                                                                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1 
                                                                        ON  MOVI_1.MOVI_ID_MOVIMENTACAO  = MOFA_1.MOFA_ID_MOVIMENTACAO                                                                                      
                                                                    WHERE    DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                    AND      MOFA_1.MOFA_ID_FASE = $fase )
                                                                                
        ";

        if ($and) {
            $this->whereUltimaFaseHistorico = " 
            AND 
            " . $this->whereUltimaFaseHistorico;
        }

        return $this->whereUltimaFaseHistorico;
    }

    public function ordemCaixa($order) {
        if (empty($order)) {
            $order = $this->ordemPadraoCaixa();
            $this->ordemCaixa = $order;
        } else {
            /** Ordenação pela ordem de serviço */
            $colOrder = explode(" ", $order);
            $order = 'ASSOCIADO_OS' == $colOrder[0] ? '(SELECT COALESCE(
                        (SELECT DOCM.DOCM_NR_DOCUMENTO
                        FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                          SAD_TB_DOCM_DOCUMENTO DOCM
                        WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
                        AND VIDC.VIDC_ID_DOC_VINCULADO   = SSOL_ID_DOCUMENTO
                        ), 0)
                       FROM DUAL) '.$colOrder[1] : $order; 
            $this->ordemCaixa = "
                ORDER BY $order,DOCM_NR_DOCUMENTO                                                                          
            ";
        }
        return $this->ordemCaixa;
    }

    public function ordem($order) {

        $this->ordem = "
            ORDER BY $order                                                                          
        ";

        return $this->ordem;
    }

    public function ordemPadraoCaixa() {

        $this->ordemPadraoCaixa = "
            ORDER BY TO_DATE(MOVI_DH_ENCAMINHAMENTO) DESC                                                                         
        ";

        return $this->ordemPadraoCaixa;
    }

    public function whereStatusVideoconferencia() {
        $this->whereStatusVideoconferencia = " (SELECT DISTINCT 
                    CASE
                       WHEN ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') = 
                             TO_DATE(TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY'),'DD/MM/YYYY'))
                           THEN 'HOJE'
                        WHEN
                            ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') = 
                             TO_DATE(TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY'),'DD/MM/YYYY')-1)
                           THEN 'AMANHA'  
                        WHEN ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') > 
                               TO_DATE(TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY'),'DD/MM/YYYY'))
                           THEN 'REALIZADA'
                        WHEN ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') < 
                               TO_DATE(TO_CHAR(SSES_DT_INICIO_VIDEO,'DD/MM/YYYY'),'DD/MM/YYYY'))
                           THEN 'PENDENTE'     
                    END   AS STATUS_VIDEOCONFERENCIA
                    FROM  SOS_TB_SSES_SERVICO_SOLIC
                    WHERE SSES_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO
                    AND (SSES_DH_FASE,SSES_ID_MOVIMENTACAO) = (SELECT MAX(SSES_2.SSES_DH_FASE),MAX(SSES_2.SSES_ID_MOVIMENTACAO)
                                                                             FROM   SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                             WHERE  SSES_2.SSES_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO) 
                    AND   SSES_DT_INICIO_VIDEO IS NOT NULL) AS STATUSVIDEO          
        ";
        return $this->whereStatusVideoconferencia;
    }

    public function whereStatusExtensao() {
        $this->whereStatusExtensao = "(SELECT DISTINCT 
                    CASE
                       WHEN ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') = 
                             TO_DATE(TO_CHAR(SSPA_DT_PRAZO,'DD/MM/YYYY'),'DD/MM/YYYY')-1)
                           THEN 'AMANHA'  
                        WHEN ( TO_DATE(TO_CHAR(SYSDATE,'DD/MM/YYYY'),'DD/MM/YYYY') < 
                               TO_DATE(TO_CHAR(SSPA_DT_PRAZO,'DD/MM/YYYY'),'DD/MM/YYYY'))
                           THEN 'PENDENTE'     
                    END   AS STATUS_EXTENSAO
                    FROM  SOS_TB_SSPA_SOLIC_PRAZO_ATEND
                    WHERE SSES_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO
                    AND   SSPA_IC_CONFIRMACAO = 'S'
                    AND (SSPA_DH_FASE,SSPA_ID_MOVIMENTACAO) = (SELECT MAX(SSPA_2.SSPA_DH_FASE),MAX(SSPA_2.SSPA_ID_MOVIMENTACAO)
                                                                             FROM   SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                                                             WHERE  SSPA_2.SSPA_ID_MOVIMENTACAO = MOFA_ID_MOVIMENTACAO) 
                    AND   SSPA_IC_CONFIRMACAO = 'S') AS STATUS_EXTENSAO         
        ";
        return $this->whereStatusExtensao;
    }

    public function whereAcompanhamentoSosti($matricula) {
        $this->acompanhamentoSosti = "
          -- Acompanhamento SOSTI
                
          AND   PAPD_CD_MATRICULA_INTERESSADO = '$matricula'
          AND   PAPD_ID_TP_PARTE = 6
          AND   PAPD_CD_MATRICULA_EXCLUSAO IS NULL
          AND   PAPD_DH_EXCLUSAO IS NULL
        ";
        return $this->acompanhamentoSosti;
    }
    
    public function whereAssociacaoOs($possuiOs) {
        $operador = $possuiOs == 'S' ? '<>' : '=';  
        $this->associacaoOs = "
            AND   (SELECT COALESCE(
              (SELECT DOCM.DOCM_NR_DOCUMENTO
              FROM SAD_TB_VIDC_VINCULACAO_DOC VIDC,
                SAD_TB_DOCM_DOCUMENTO DOCM
              WHERE VIDC.VIDC_ID_DOC_PRINCIPAL = DOCM.DOCM_ID_DOCUMENTO
              AND VIDC.VIDC_ID_DOC_VINCULADO   = SSOL_ID_DOCUMENTO
              ), 0)
             FROM DUAL) $operador 0
        ";
        return $this->associacaoOs;
    }

    /**
     * De acordo com a caixa pegar solicitações de informação para o encaminhador
     * ou solicitante
     * 
     * @param string $matricula
     * @param string $tipoDeVisao aousuario|aoencaminhador
     * @param string $and
     * @return string 
     */
    public function whereMatriculaSolicitacaoInformacao($matricula, $tipoDeVisao = 'aoencaminhador', $and = true) {

        if ($tipoDeVisao == 'aoencaminhador') {
            $this->matricula = " MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                AND MOVI_CD_MATR_ENCAMINHADOR = '" . $matricula . "'";
        } else {
            $this->matricula = " DOCM_CD_MATRICULA_CADASTRO = '" . $matricula . "'";
        }

        if ($and) {
            $this->matricula = "
            AND 
            " . $this->matricula;
        }
        return $this->matricula;
    }

    /**
     * De acordo com a caixa pegar solicitações de informação para o encaminhador
     * ou solicitante
     * usar SUB_QUERY como apelido da query
     * 
     * @param string $matricula
     * @param string $tipoDeVisao aousuario|aoencaminhador
     * @param string $and
     * @return string 
     */
    public function whereRegraSolicitacaoInformacao($tipoDeVisao = 'aoencaminhador', $and = true) {

        if (in_array($tipoDeVisao, array('aoencaminhador', 'aunidade'))) {
            $this->solicitacaoDeInformacao = " 
                --So mostrar se na movimentação tiver alguma solicitação de informação
                0 < 
                (
                    SELECT  COUNT(*)
                    FROM    SAD_TB_MOFA_MOVI_FASE MOFA_2
                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_2
                            ON MODE_2.MODE_ID_MOVIMENTACAO = MOFA_2.MOFA_ID_MOVIMENTACAO
                    WHERE
                            MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                            AND MOFA_2.MOFA_ID_FASE = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "'
                )
                --Não mostrar se tiver a resposta OU SE FOI BAIXADA numa data superior ao da solicitação de informação
                AND  0 =
                (
                    SELECT COUNT(*)
                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                    WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                    AND    MOFA_2.MOFA_ID_FASE IN ('" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI . "', '" . Trf1_Sosti_Definicoes::FASE_BAIXA_SOLICITACAO_TI . "')
                    AND    MOFA_2.MOFA_DH_FASE > (
                                            SELECT MAX(MOFA_3.MOFA_DH_FASE)
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                            WHERE  MOFA_3.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                                                AND    MOFA_3.MOFA_ID_FASE = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "'
                                           )
                )";
        } else {
            $this->solicitacaoDeInformacao = "
                --So mostrar se na movimentação tiver alguma solicitação de informação
                0 < 
                (
                    SELECT COUNT(*)
                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_2
                                ON MODE_2.MODE_ID_MOVIMENTACAO = MOFA_2.MOFA_ID_MOVIMENTACAO
                    WHERE
                                MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                                AND (( 
                                    MODE_2.MODE_ID_CAIXA_ENTRADA != '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                    AND MOFA_2.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "'
                                )
                                OR
                                (
                                    MODE_2.MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                    AND MOFA_2.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "'
                                ))          
                )
                --Não mostrar se tiver a resposta numa data superior ao da solicitação de informação
                AND  0 =
                (
                    SELECT COUNT(*)
                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_2
                                ON MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA_2.MOFA_ID_MOVIMENTACAO
                    WHERE
                                MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                                AND (( 
                                        MODE_2.MODE_ID_CAIXA_ENTRADA != '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                        AND MOFA_2.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI . "'--resposta de solicitação
                                    )
                                    OR
                                    (
                                        MODE_2.MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                        AND MOFA_2.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "'--resposta de solicitação
                                ))
                                AND    MOFA_2.MOFA_DH_FASE > (
                                            SELECT MAX(MOFA_3.MOFA_DH_FASE)
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_3
                                            ON MODE_3.MODE_ID_MOVIMENTACAO = MOFA_3.MOFA_ID_MOVIMENTACAO
                                            WHERE
                                            MOFA_3.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                                            AND (( 
                                                    MODE_3.MODE_ID_CAIXA_ENTRADA != '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                                    AND MOFA_3.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "'
                                                    )
                                                    OR
                                                    (
                                                    MODE_3.MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                                    AND MOFA_3.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "'
                                                ))
                                            )
                )
                --NÃO APARECER SOLICITAÇÕES QUE FORAM BAIXADAS numa data superior ao da solicitação de informação
                AND  0 =
                (
                    SELECT COUNT(*)
                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                    WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO
                    AND    MOFA_2.MOFA_ID_FASE = '" . Trf1_Sosti_Definicoes::FASE_BAIXA_SOLICITACAO_TI . "' --baixa de solicitação
                    AND    MOFA_2.MOFA_DH_FASE > (
                                            SELECT MAX(MOFA_3.MOFA_DH_FASE)
                                            FROM   SAD_TB_MOFA_MOVI_FASE MOFA_3
                                            INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_3
                                            ON MODE_3.MODE_ID_MOVIMENTACAO = MOFA_3.MOFA_ID_MOVIMENTACAO
                                            WHERE
                                            MOFA_3.MOFA_ID_MOVIMENTACAO = SUB_QUERY.MOFA_ID_MOVIMENTACAO

                                            AND (( 
                                                MODE_3.MODE_ID_CAIXA_ENTRADA != '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                                AND MOFA_3.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI . "'
                                                )
                                                OR
                                                (
                                                MODE_3.MODE_ID_CAIXA_ENTRADA = '" . Trf1_Sosti_Definicoes::CAIXA_DESENVOLVIMENTO_SUSTENTACAO_TRIBUNAL_PRIMEIRA_INSTANCIA . "'
                                                AND MOFA_3.MOFA_ID_FASE  = '" . Trf1_Sosti_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_TI_AO_USUARIO . "'
                                            ))
                                        )
                )";
        }

        if ($and) {
            $this->solicitacaoDeInformacao = "
            AND 
            " . $this->solicitacaoDeInformacao;
        }
        return $this->solicitacaoDeInformacao;
    }

    public function colunasBaixaSolitacao() {

        $this->colunasBaixaSolitacao = " 
             -- DESCRICAO_BAIXA
                    (SELECT MOFA_1.MOFA_DS_COMPLEMENTO
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1000 )) DESCRICAO_BAIXA,
                 -- DATA_BAIXA
                    (SELECT TO_CHAR(MOFA_1.MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS') MOFA_DH_FASE
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1000 )) DH_BAIXA,              

        ";

        return $this->colunasBaixaSolitacao;
    }

    public function colunasDescricaoAvaliacao() {

        $this->descricaoAvaliacao = "
             -- DESCRICAO_AVALIACAO
                    (SELECT MOFA_1.MOFA_DS_COMPLEMENTO
                                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                        WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), MAX(MOFA_2.MOFA_DH_FASE)
                                                                        FROM  SAD_TB_DOCM_DOCUMENTO DOCM_2
                                                                                INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2
                                                                                ON  DOCM_2.DOCM_ID_DOCUMENTO     = MODO_MOVI_2.MODO_ID_DOCUMENTO
                                                                                INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_2
                                                                                ON  MODO_MOVI_2.MODO_ID_MOVIMENTACAO  = MOVI_2.MOVI_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MODE_MOVI_2.MODE_ID_MOVIMENTACAO
                                                                                INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 
                                                                                ON  MOVI_2.MOVI_ID_MOVIMENTACAO  = MOFA_2.MOFA_ID_MOVIMENTACAO
                                                                                WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                                                AND   MOFA_2.MOFA_ID_FASE = 1014 )) DESCRICAO_AVALIACAO ";

        return $this->descricaoAvaliacao;
    }

}
