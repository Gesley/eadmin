<?php
/**
 * Lista as solicitações das caixas que não tem nível de atendimento
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_CaixaSemNivel
{
    public function getQuery($idCaixa, $params, $order, $count = false, $cxSolicitInfo = false, $priorizaDemanda = false)
    {
        /** Cláusula select */
        if ($count === false) {
            $SQL .= "SELECT DISTINCT ".PHP_EOL;
            $SQL .= "  SSOL_ID_DOCUMENTO, ".PHP_EOL;
            $SQL .= "  SSOL_CD_MATRICULA_ATENDENTE ".PHP_EOL;
            $SQL .= "  ||' - ' ".PHP_EOL;
            $SQL .= "  ||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE, ".PHP_EOL;
            $SQL .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE, ".PHP_EOL;
            $SQL .= "  DOCM_NR_DOCUMENTO, ".PHP_EOL;
            $SQL .= "  DOCM_CD_MATRICULA_CADASTRO, ".PHP_EOL;
            $SQL .= "  DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 32000, 1) DOCM_DS_ASSUNTO_DOC, ".PHP_EOL;
            $SQL .= "  DOCM_DH_CADASTRO, ".PHP_EOL;
            $SQL .= "  SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM, ".PHP_EOL;
            $SQL .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO, ".PHP_EOL;
            $SQL .= "  MOFA_ID_MOVIMENTACAO, ".PHP_EOL;
            $SQL .= "  TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE, ".PHP_EOL;
            $SQL .= "  MOFA_ID_FASE, ".PHP_EOL;
            $SQL .= "  TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, ".PHP_EOL;
            $SQL .= "  MOVI_DH_ENCAMINHAMENTO, ".PHP_EOL;
            $SQL .= "  TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL, ".PHP_EOL;
            $SQL .= "  MODE_ID_CAIXA_ENTRADA, ".PHP_EOL;
            $SQL .= "  MODE_SG_SECAO_UNID_DESTINO, ".PHP_EOL;
            $SQL .= "  MODE_CD_SECAO_UNID_DESTINO, ".PHP_EOL;
            $SQL .= "  TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO, ".PHP_EOL;
            $SQL .= "  SSER_ID_SERVICO, ".PHP_EOL;
            $SQL .= "  SSER_DS_SERVICO, ".PHP_EOL;
            $SQL .= "  SESP_DH_LIMITE_ESP, ".PHP_EOL;
            $SQL .= "  TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG, ".PHP_EOL;
            $SQL .= "  SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA, ".PHP_EOL;
            $SQL .= "  TO_CHAR( ".PHP_EOL;
            $SQL .= "  (SELECT SSPA_1.SSPA_DT_PRAZO ".PHP_EOL;
            $SQL .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
            $SQL .= "  INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1 ".PHP_EOL;
            $SQL .= "  ON MOFA_1.MOFA_ID_MOVIMENTACAO                           = SSPA_1.SSPA_ID_MOVIMENTACAO ".PHP_EOL;
            $SQL .= "  AND MOFA_1.MOFA_DH_FASE                                  = SSPA_1.SSPA_DH_FASE ".PHP_EOL;
            $SQL .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
            $SQL .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
            $SQL .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
            $SQL .= "    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
            $SQL .= "    INNER JOIN SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2 ".PHP_EOL;
            $SQL .= "    ON MOFA_2.MOFA_ID_MOVIMENTACAO    = SSPA_2.SSPA_ID_MOVIMENTACAO ".PHP_EOL;
            $SQL .= "    AND MOFA_2.MOFA_DH_FASE           = SSPA_2.SSPA_DH_FASE ".PHP_EOL;
            $SQL .= "    WHERE MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO ".PHP_EOL;
            $SQL .= "    ) ".PHP_EOL;
            $SQL .= "  AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO ".PHP_EOL;
            $SQL .= "  AND SSPA_IC_CONFIRMACAO         = 'S' ".PHP_EOL;
            $SQL .= "  ),'DD/MM/YYYY HH24:MI:SS') SSPA_DT_PRAZO , ".PHP_EOL;
            $SQL .= "  INITCAP(LOWER(OSIS_NM_OCORRENCIA))OSIS_NM_OCORRENCIA, ".PHP_EOL;
            $SQL .= "  INITCAP( LOWER(CTSS_NM_CATEGORIA_SERVICO)) CTSS_NM_CATEGORIA_SERVICO, ".PHP_EOL;
            $SQL .= "  CTSS_ID_CATEGORIA_SERVICO, ".PHP_EOL;
            $SQL .= "  ASSO_IC_ATENDIMENTO_EMERGENCIA, ".PHP_EOL;
            $SQL .= "  ASSO_IC_SOLUCAO_PROBLEMA, ".PHP_EOL;
            $SQL .= "  ASSO_IC_SOLUCAO_CAUSA_PROBLEMA, ".PHP_EOL;
            $SQL .= "  ASIS_IC_NIVEL_CRITICIDADE, ".PHP_EOL;
            $SQL .= "  (SELECT PRAT_QT_PRAZO ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||UNME_DS_UNID_MEDIDA ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||PRAT_IC_CONTAGEM ".PHP_EOL;
            $SQL .= "  FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT ".PHP_EOL;
            $SQL .= "  INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME ".PHP_EOL;
            $SQL .= "  ON UNME.UNME_ID_UNID_MEDIDA     = PRAT.PRAT_ID_UNIDADE_MEDIDA ".PHP_EOL;
            $SQL .= "  WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_INICIO_ATENDIMENTO ".PHP_EOL;
            $SQL .= "  ) ASIS_PRZ_INICIO_ATENDIMENTO, ".PHP_EOL;
            $SQL .= "  ( ".PHP_EOL;
            $SQL .= "  SELECT PRAT_QT_PRAZO ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||UNME_DS_UNID_MEDIDA ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||PRAT_IC_CONTAGEM ".PHP_EOL;
            $SQL .= "  FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT ".PHP_EOL;
            $SQL .= "  INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME ".PHP_EOL;
            $SQL .= "  ON UNME.UNME_ID_UNID_MEDIDA     = PRAT.PRAT_ID_UNIDADE_MEDIDA ".PHP_EOL;
            $SQL .= "  WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_PROBLEMA ".PHP_EOL;
            $SQL .= "  ) ".PHP_EOL;
            $SQL .= "  ASIS_PRZ_SOL_PROBLEMA, ".PHP_EOL;
            $SQL .= "  ( ".PHP_EOL;
            $SQL .= "  SELECT PRAT_QT_PRAZO ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||UNME_DS_UNID_MEDIDA ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||PRAT_IC_CONTAGEM ".PHP_EOL;
            $SQL .= "  FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT ".PHP_EOL;
            $SQL .= "  INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME ".PHP_EOL;
            $SQL .= "  ON UNME.UNME_ID_UNID_MEDIDA     = PRAT.PRAT_ID_UNIDADE_MEDIDA ".PHP_EOL;
            $SQL .= "  WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_SOL_CAUSA_PROBLEMA ".PHP_EOL;
            $SQL .= "  ) ".PHP_EOL;
            $SQL .= "  ASIS_PRZ_SOL_CAUSA_PROBLEMA, ".PHP_EOL;
            $SQL .= "  (SELECT PRAT_QT_PRAZO ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||UNME_DS_UNID_MEDIDA ".PHP_EOL;
            $SQL .= "    ||'|' ".PHP_EOL;
            $SQL .= "    ||PRAT_IC_CONTAGEM ".PHP_EOL;
            $SQL .= "  FROM SOS_TB_PRAT_PRAZO_ATENDIMENTO PRAT ".PHP_EOL;
            $SQL .= "  INNER JOIN OCS_TB_UNME_UNIDADE_MEDIDA UNME ".PHP_EOL;
            $SQL .= "  ON UNME.UNME_ID_UNID_MEDIDA     = PRAT.PRAT_ID_UNIDADE_MEDIDA ".PHP_EOL;
            $SQL .= "  WHERE PRAT_ID_PRAZO_ATENDIMENTO = ASIS_PRZ_EXECUCAO_SERVICO ".PHP_EOL;
            $SQL .= "  ) ASIS_PRZ_EXECUCAO_SERVICO, ".PHP_EOL;
            $SQL .= "  CASE ".PHP_EOL;
            $SQL .= "    WHEN ((SELECT ASIS_ID_OCORRENCIA ".PHP_EOL;
            $SQL .= "      FROM SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO ".PHP_EOL;
            $SQL .= "      INNER JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS ".PHP_EOL;
            $SQL .= "      ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS ".PHP_EOL;
            $SQL .= "      WHERE ASIS_ID_CATEGORIA_SERVICO     = $idCaixa ".PHP_EOL;
            $SQL .= "      AND MOFA_ID_MOVIMENTACAO            = ASSO.ASSO_ID_MOVIMENTACAO ) IS NULL ) ".PHP_EOL;
            $SQL .= "    THEN 'N' ".PHP_EOL;
            $SQL .= "    ELSE 'S' ".PHP_EOL;
            $SQL .= "  END AS CORRETIVA, ".PHP_EOL;
            $SQL .= "  PKG_SOLIC.SOLICITACAO_INFORMACAO(SSOL_ID_DOCUMENTO) SOLICITACAO_INFORMACAO, ".PHP_EOL;
            $SQL .= "  PRDE_NR_PRIORIDADE ".PHP_EOL;
        }
        /** Monta a query apenas para mostrar a quantidade de linhas **/
        $SQL .= ($count ? "  SELECT COUNT(*)  QTDE " : "").PHP_EOL;
        /** Cláusula from **/
        $SQL .= "FROM ".PHP_EOL;
        $SQL .= "  (SELECT SUB_3.*, ".PHP_EOL;
        $SQL .= "    MOFA.* , ".PHP_EOL;
        $SQL .= "    (SELECT MAX(SSES_1.SSES_DH_FASE) ".PHP_EOL;
        $SQL .= "    FROM SOS_TB_SSES_SERVICO_SOLIC SSES_1 ".PHP_EOL;
        $SQL .= "    WHERE SSES_1.SSES_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    )DATA_ULTIMO_SERVICO , ".PHP_EOL;
        $SQL .= "    (SELECT MAX(SESP_1.SESP_DH_FASE) ".PHP_EOL;
        $SQL .= "    FROM SOS_TB_SESP_SOLIC_ESPERA SESP_1 ".PHP_EOL;
        $SQL .= "    WHERE SESP_1.SESP_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    )DATA_ULTIMA_ESPERA ".PHP_EOL;
        $SQL .= "  FROM ".PHP_EOL;
        $SQL .= "    (SELECT MAX(MOFA_DH_FASE) ULTIMA_FASE_DATA, ".PHP_EOL;
        $SQL .= "      ULTIMA_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    FROM ".PHP_EOL;
        $SQL .= "      (SELECT MAX(MODO_MOVI_R_1.MODO_ID_MOVIMENTACAO) ULTIMA_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "      FROM ".PHP_EOL;
        $SQL .= "        (SELECT * ".PHP_EOL;
        $SQL .= "        FROM SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ".PHP_EOL;
        $SQL .= "        WHERE MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa ".PHP_EOL;
        $SQL .= "        ) SUB_1 ".PHP_EOL;
        $SQL .= "      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI ".PHP_EOL;
        $SQL .= "      ON MODO_MOVI.MODO_ID_MOVIMENTACAO = SUB_1.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "      INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_R_1 ".PHP_EOL;
        $SQL .= "      ON MODO_MOVI_R_1.MODO_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO ".PHP_EOL;
        $SQL .= "      GROUP BY MODO_MOVI_R_1.MODO_ID_DOCUMENTO ".PHP_EOL;
        $SQL .= "      )SUB_2 ".PHP_EOL;
        $SQL .= "    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA ".PHP_EOL;
        $SQL .= "    ON SUB_2.ULTIMA_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ".PHP_EOL;
        $SQL .= "    ON SUB_2.ULTIMA_MOVIMENTACAO        = MODE_MOVI.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    AND MODE_MOVI.MODE_ID_CAIXA_ENTRADA = $idCaixa ".PHP_EOL;
        $SQL .= "    GROUP BY ULTIMA_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "    )SUB_3 ".PHP_EOL;
        $SQL .= "  INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA ".PHP_EOL;
        $SQL .= "  ON SUB_3.ULTIMA_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "  AND SUB_3.ULTIMA_FASE_DATA   = MOFA.MOFA_DH_FASE ".PHP_EOL;
        $SQL .= "  WHERE MOFA_ID_FASE NOT      IN (1000,1014,1026,1081) ".PHP_EOL;
        /** Fases: 1000 baixa, 1014 avaliada, 1026 cancelada, 1081 DESVINCULADA SLA **/
        $SQL .= "  )SUB_4 ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES ".PHP_EOL;
        $SQL .= "ON SUB_4.MOFA_ID_MOVIMENTACAO = SSES.SSES_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "AND SUB_4.DATA_ULTIMO_SERVICO = SSES.SSES_DH_FASE ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_SSER_SERVICO SSER ".PHP_EOL;
        $SQL .= "ON SSES.SSES_ID_SERVICO = SSER.SSER_ID_SERVICO ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP ".PHP_EOL;
        $SQL .= "ON SUB_4.MOFA_ID_MOVIMENTACAO = SESP.SESP_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "AND SUB_4.DATA_ULTIMA_ESPERA  = SESP.SESP_DH_FASE ".PHP_EOL;
        $SQL .= "INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI ".PHP_EOL;
        $SQL .= "ON MOVI.MOVI_ID_MOVIMENTACAO = SUB_4.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ".PHP_EOL;
        $SQL .= "ON MOVI.MOVI_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI ".PHP_EOL;
        $SQL .= "ON MOVI.MOVI_ID_MOVIMENTACAO = MODO_MOVI.MODO_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM ".PHP_EOL;
        $SQL .= "ON DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO ".PHP_EOL;
        $SQL .= "INNER JOIN SOS_TB_SSOL_SOLICITACAO SSOL ".PHP_EOL;
        $SQL .= "ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO ".PHP_EOL;
        /** Ocorrencia, categoria, emergencia de sistemas **/
        $SQL .= "LEFT JOIN SOS_TB_ASSO_ATEND_SISTEM_SOLIC ASSO ".PHP_EOL;
        $SQL .= "ON MOVI.MOVI_ID_MOVIMENTACAO = ASSO.ASSO_ID_MOVIMENTACAO ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_ASIS_ATEND_SISTEMA ASIS ".PHP_EOL;
        $SQL .= "ON ASIS.ASIS_ID_ATENDIMENTO_SISTEMA = ASSO.ASSO_ID_ATENDIMENTO_SISTEMAS ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_CTSS_CATEG_SERV_SISTEMA CTSS ".PHP_EOL;
        $SQL .= "ON ASIS.ASIS_ID_CATEGORIA_SERVICO = CTSS.CTSS_ID_CATEGORIA_SERVICO ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_OSIS_OCORRENCIA_SISTEMA OSIS ".PHP_EOL;
        $SQL .= "ON OSIS.OSIS_ID_OCORRENCIA = ASIS.ASIS_ID_OCORRENCIA ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS_TB_SESI_SERVICO_SISTEMA SESI ".PHP_EOL;
        $SQL .= "ON CTSS.CTSS_ID_SERVICO_SISTEMA = SESI.SESI_ID_SERVICO_SISTEMA ".PHP_EOL;
        $SQL .= "LEFT JOIN SOS.SOS_TB_PRDE_PRIORIZA_DEMANDA PRDE ".PHP_EOL;
        $SQL .= "ON PRDE.PRDE_ID_SOLICITACAO = DOCM.DOCM_ID_DOCUMENTO ".PHP_EOL;
        /** Cláusula where **/
        $SQL .= "WHERE ".PHP_EOL;
        $SQL .= "DOCM_ID_TIPO_DOC                                                          = 160 ".PHP_EOL;
        $SQL .= "AND SOS_P.PKG_SOLIC.SOLIC_MOSTR_VINC_PRINC_OU_ORF(SSOL.SSOL_ID_DOCUMENTO) = 1 ".PHP_EOL;
        /** Solicitação de informação **/
        $SQL .= ($cxSolicitInfo ? "AND PKG_SOLIC.SOLICITACAO_INFORMACAO(SSOL_ID_DOCUMENTO) = 'S' " : "").PHP_EOL;
        /** Atendente **/
        $SQL .= ($params['SSOL_CD_MATRICULA_ATENDENTE'] ? "AND SSOL_CD_MATRICULA_ATENDENTE = '" . $params['SSOL_CD_MATRICULA_ATENDENTE'] . "' " : "").PHP_EOL;
        /** Unidade fase **/
        $SQL .= ($params['MOFA_ID_FASE'] ? "AND MOFA_ID_FASE = '" . $params['MOFA_ID_FASE'] . "' " : "").PHP_EOL;
        /** Unidade solicitante **/
        $SQL .= ($params['DOCM_SG_SECAO_GERADORA'] ? "AND DOCM_SG_SECAO_GERADORA = '" . $params['DOCM_SG_SECAO_GERADORA'] . "' " : "").PHP_EOL;
        $SQL .= ($params['DOCM_CD_LOTACAO_GERADORA'] ? "AND DOCM_CD_LOTACAO_GERADORA = " . $params['DOCM_CD_LOTACAO_GERADORA'] . " " : "").PHP_EOL;
        /** Solicitante **/
        $SQL .= ($params['DOCM_CD_MATRICULA_CADASTRO'] ? "AND DOCM_CD_MATRICULA_CADASTRO = '" . $params['DOCM_CD_MATRICULA_CADASTRO'] . "' " : "").PHP_EOL;
        /** Categorias **/
        if (is_array($params['CATE_ID_CATEGORIA'])) {
            //Remove valores vazios da array
            if (array_search("", $params['CATE_ID_CATEGORIA']) !== false) {
                unset($params['CATE_ID_CATEGORIA'][array_search("", $params['CATE_ID_CATEGORIA'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['CATE_ID_CATEGORIA']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['CATE_ID_CATEGORIA']);
                // Retira a utima virgula
                $SQL .= (($params['CATE_ID_CATEGORIA']) ? (
                    "AND SSOL_ID_DOCUMENTO IN(
                        SELECT B.CASO_ID_DOCUMENTO 
                        FROM SOS.SOS_TB_CATE_CATEGORIA A,
                        SOS.SOS_TB_CASO_CATEGORIA_SOLIC B
                        WHERE A.CATE_ID_CATEGORIA = B.CASO_ID_CATEGORIA
                        AND A.CATE_ID_CATEGORIA IN ($value_query)
                        AND B.CASO_DH_INATIVACAO_CATEGORIA IS NULL
                        AND B.CASO_CD_MATRICULA_INATIVACAO IS NULL
                    ) "
                ) : ('')).PHP_EOL;
            }
        }
        /** Serviço **/
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $SQL .= ($params['SSER_ID_SERVICO'] ? "AND SSER_ID_SERVICO IN( " . $value_query . ") " : "").PHP_EOL;
            }
        } else {
            $SQL .= ($params['SSER_ID_SERVICO'] ? "AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " " : "").PHP_EOL;
        }
        $SQL .= ($params['SSER_DS_SERVICO'] ? "AND UPPER(SSER_DS_SERVICO) LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')" : "").PHP_EOL;
        /** Data de cadastro **/
        (($params['DATA_INICIAL_CADASTRO'] == "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($SQL .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] == "")) ? ($SQL .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL_CADASTRO'] != "") && ($params['DATA_FINAL_CADASTRO'] != "")) ? ($SQL .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE('" . $params['DATA_INICIAL_CADASTRO'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL_CADASTRO'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        /** Data da Ultima fase **/
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($SQL .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($SQL .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($SQL .= "AND MOFA_DH_FASE BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60  ") : ("");
        /** Número da solicitação **/
        $docm_nr_documento = $params['DOCM_NR_DOCUMENTO'];
        if (!empty($docm_nr_documento)) {
            $SQL .= ((strlen(trim($docm_nr_documento)) == 28) ? ("AND DOCM_NR_DOCUMENTO = $docm_nr_documento") :
                ("AND TO_NUMBER(SUBSTR(DOCM_NR_DOCUMENTO,-6,6)) = TO_NUMBER(SUBSTR($docm_nr_documento,5))
                    AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)")).PHP_EOL;
        }
        /** Ordenação **/
        $SQL .= "ORDER BY $order, ".PHP_EOL;
        $SQL .= $priorizaDemanda ? "MOFA_ID_MOVIMENTACAO  DESC " : "DOCM_NR_DOCUMENTO ";
        return $SQL;
    }
}