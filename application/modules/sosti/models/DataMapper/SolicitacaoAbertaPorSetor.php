<?php
/**
 * Lista as solicitações das caixas que não tem nível de atendimento
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_SolicitacaoAbertaPorSetor
{
    public static function getQuery($param)
    {
        $secao = explode('|', $param["UNPE_SG_SECAO"]);
        $solicitante = explode(' - ', $param["SOLICITANTE"]);
        $docmCdLotacaoGeradora =  $secao[1];
        $docmSgSecaoGeradora = $secao[0];
        $docmCdMatriculaCadastro = $solicitante[0];
        $sserIdServico = $param["SSER_ID_SERVICO"];
        $sserIdGrupo = $param['SGRS_ID_GRUPO'];
        
        $sql .= "SELECT DISTINCT ".PHP_EOL;
        $sql .= "  --solicitaÃ§Ã£o sos_tb_ssol_solicitacao ".PHP_EOL;
        $sql .= "  SSOL_ID_DOCUMENTO, ".PHP_EOL;
        $sql .= "  SSOL_CD_MATRICULA_ATENDENTE ".PHP_EOL;
        $sql .= "  ||' - ' ".PHP_EOL;
        $sql .= "  ||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE, ".PHP_EOL;
        $sql .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE, ".PHP_EOL;
        $sql .= "  --documento sad_tb_docm_documento ".PHP_EOL;
        $sql .= "  DOCM_NR_DOCUMENTO, ".PHP_EOL;
        $sql .= "  DOCM_CD_MATRICULA_CADASTRO, ".PHP_EOL;
        $sql .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO, ".PHP_EOL;
        $sql .= "  TO_CHAR(DOCM_DH_CADASTRO,'DD/MM/YYYY HH24:MI:SS') DOCM_DH_CADASTRO, ".PHP_EOL;
        $sql .= "  DOCM_DH_CADASTRO DH_CADASTRO, ".PHP_EOL;
        $sql .= "  --fase sad_tb_mofa_movi_fase ".PHP_EOL;
        $sql .= "  MOFA_ID_FASE, ".PHP_EOL;
        $sql .= "  TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, ".PHP_EOL;
        $sql .= "  MOFA_ID_MOVIMENTACAO, ".PHP_EOL;
        $sql .= "  TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE, ".PHP_EOL;
        $sql .= "  TRUNC((SYSDATE - MOFA_DH_FASE)*24*60,2) TEMPO_TOTAL, ".PHP_EOL;
        $sql .= "  TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO, ".PHP_EOL;
        $sql .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(MOFA_CD_MATRICULA) NOME_USARIO_BAIXA, ".PHP_EOL;
        $sql .= "  --movimentacao destino sad_tb_mode_movi_destinatario ".PHP_EOL;
        $sql .= "  MODE_ID_CAIXA_ENTRADA, ".PHP_EOL;
        $sql .= "  MODE_SG_SECAO_UNID_DESTINO, ".PHP_EOL;
        $sql .= "  MODE_CD_SECAO_UNID_DESTINO, ".PHP_EOL;
        $sql .= "  --nivel fase sos_tb_snas_nivel_atend_solic ".PHP_EOL;
        $sql .= "  SNAS_ID_NIVEL, ".PHP_EOL;
        $sql .= "  --nivel sos_tb_snat_nivel_atendimento ".PHP_EOL;
        $sql .= "  SNAT_CD_NIVEL, ".PHP_EOL;
        $sql .= "  --servico sos_tb_sser_servico ".PHP_EOL;
        $sql .= "  SSER_ID_SERVICO, ".PHP_EOL;
        $sql .= "  SSER_DS_SERVICO, ".PHP_EOL;
        $sql .= "  --espera sos_tb_sesp_solic_espera ".PHP_EOL;
        $sql .= "  SESP_DH_LIMITE_ESP, ".PHP_EOL;
        $sql .= "  TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG, ".PHP_EOL;
        $sql .= "  --avaliacao  sos_tb_stsa_tipo_satisfacao ".PHP_EOL;
        $sql .= "  STSA_DS_TIPO_SAT, ".PHP_EOL;
        $sql .= "  SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME( ".PHP_EOL;
        $sql .= "  (SELECT MOFA_1.MOFA_CD_MATRICULA ".PHP_EOL;
        $sql .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
        $sql .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
        $sql .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
        $sql .= "    FROM SAD_TB_DOCM_DOCUMENTO DOCM_2 ".PHP_EOL;
        $sql .= "    INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_2 ".PHP_EOL;
        $sql .= "    ON DOCM_2.DOCM_ID_DOCUMENTO = MODO_MOVI_2.MODO_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "    INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI_2 ".PHP_EOL;
        $sql .= "    ON MODO_MOVI_2.MODO_ID_MOVIMENTACAO = MOVI_2.MOVI_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_2 ".PHP_EOL;
        $sql .= "    ON MOVI_2.MOVI_ID_MOVIMENTACAO = MODE_MOVI_2.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
        $sql .= "    ON MOVI_2.MOVI_ID_MOVIMENTACAO = MOFA_2.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    WHERE DOCM_2.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "    AND MOFA_2.MOFA_ID_FASE        = 1000 ".PHP_EOL;
        $sql .= "    ) ".PHP_EOL;
        $sql .= "  )) NOME_USARIO_BAIXA ".PHP_EOL;
        $sql .= "FROM ".PHP_EOL;
        $sql .= "  -- solicitacao ".PHP_EOL;
        $sql .= "  SOS_TB_SSOL_SOLICITACAO SSOL ".PHP_EOL;
        $sql .= "  -- documento ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM ".PHP_EOL;
        $sql .= "ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "  -- documento movimentacao ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI ".PHP_EOL;
        $sql .= "ON DOCM.DOCM_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "  -- movimentacao origem ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI ".PHP_EOL;
        $sql .= "ON MODO_MOVI.MODO_ID_MOVIMENTACAO = MOVI.MOVI_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  -- movimentacao destino ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI ".PHP_EOL;
        $sql .= "ON MOVI.MOVI_ID_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  --fase ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA ".PHP_EOL;
        $sql .= "ON MOVI.MOVI_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  --descricao fase ".PHP_EOL;
        $sql .= "INNER JOIN SAD_TB_FADM_FASE_ADM FADM ".PHP_EOL;
        $sql .= "ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE ".PHP_EOL;
        $sql .= "  --servico ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES ".PHP_EOL;
        $sql .= "ON MOFA.MOFA_ID_MOVIMENTACAO = SSES.SSES_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SSER_SERVICO SSER ".PHP_EOL;
        $sql .= "ON SSES.SSES_ID_SERVICO = SSER.SSER_ID_SERVICO ".PHP_EOL;
        $sql .= "  --nivel ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS ".PHP_EOL;
        $sql .= "ON MOFA.MOFA_ID_MOVIMENTACAO = SNAS.SNAS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SNAT_NIVEL_ATENDIMENTO SNAT ".PHP_EOL;
        $sql .= "ON SNAT.SNAT_ID_NIVEL = SNAS.SNAS_ID_NIVEL ".PHP_EOL;
        $sql .= "  --espera ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SESP_SOLIC_ESPERA SESP ".PHP_EOL;
        $sql .= "ON MOFA.MOFA_ID_MOVIMENTACAO = SESP.SESP_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  --grupo serviÃ§o e serviÃ§o ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SGRS_GRUPO_SERVICO SGRS ".PHP_EOL;
        $sql .= "ON SGRS.SGRS_ID_GRUPO = SSER.SSER_ID_GRUPO ".PHP_EOL;
        $sql .= "  --avaliaÃ§Ã£o ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS ".PHP_EOL;
        $sql .= "ON MOFA.MOFA_ID_MOVIMENTACAO = SAVS.SAVS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "LEFT JOIN SOS_TB_STSA_TIPO_SATISFACAO STSA ".PHP_EOL;
        $sql .= "ON SAVS.SAVS_ID_TIPO_SAT = STSA.STSA_ID_TIPO_SAT ".PHP_EOL;
        $sql .= "WHERE ".PHP_EOL;
        $sql .= "  --Ãºltimo serviÃ§o ".PHP_EOL;
        $sql .= "  (SSER.SSER_ID_SERVICO = ".PHP_EOL;
        $sql .= "  (SELECT SSES_1.SSES_ID_SERVICO ".PHP_EOL;
        $sql .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1 ".PHP_EOL;
        $sql .= "  ON MOFA_1.MOFA_ID_MOVIMENTACAO                           = SSES_1.SSES_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_DH_FASE                                  = SSES_1.SSES_DH_FASE ".PHP_EOL;
        $sql .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
        $sql .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
        $sql .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
        $sql .= "    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
        $sql .= "    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2 ".PHP_EOL;
        $sql .= "    ON MOFA_2.MOFA_ID_MOVIMENTACAO    = SSES_2.SSES_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    AND MOFA_2.MOFA_DH_FASE           = SSES_2.SSES_DH_FASE ".PHP_EOL;
        $sql .= "    WHERE MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    ) ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  ) ".PHP_EOL;
        $sql .= "OR SSER.SSER_ID_SERVICO IS NULL) ".PHP_EOL;
        $sql .= "AND ".PHP_EOL;
        $sql .= "  --Ãºltimo nÃ­vel ".PHP_EOL;
        $sql .= "  (SNAT.SNAT_ID_NIVEL = ".PHP_EOL;
        $sql .= "  (SELECT SNAS_1.SNAS_ID_NIVEL ".PHP_EOL;
        $sql .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_1 ".PHP_EOL;
        $sql .= "  ON MOFA_1.MOFA_ID_MOVIMENTACAO                           = SNAS_1.SNAS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_DH_FASE                                  = SNAS_1.SNAS_DH_FASE ".PHP_EOL;
        $sql .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
        $sql .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
        $sql .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
        $sql .= "    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
        $sql .= "    INNER JOIN SOS_TB_SNAS_NIVEL_ATEND_SOLIC SNAS_2 ".PHP_EOL;
        $sql .= "    ON MOFA_2.MOFA_ID_MOVIMENTACAO    = SNAS_2.SNAS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    AND MOFA_2.MOFA_DH_FASE           = SNAS_2.SNAS_DH_FASE ".PHP_EOL;
        $sql .= "    WHERE MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    ) ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  ) ".PHP_EOL;
        $sql .= "OR SNAT.SNAT_ID_NIVEL IS NULL) ".PHP_EOL;
        $sql .= "AND ".PHP_EOL;
        $sql .= "  --Ãºltima espera ".PHP_EOL;
        $sql .= "  (SESP.SESP_DH_LIMITE_ESP = ".PHP_EOL;
        $sql .= "  (SELECT SESP_1.SESP_DH_LIMITE_ESP ".PHP_EOL;
        $sql .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_1 ".PHP_EOL;
        $sql .= "  ON MOFA_1.MOFA_ID_MOVIMENTACAO                           = SESP_1.SESP_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_DH_FASE                                  = SESP_1.SESP_DH_FASE ".PHP_EOL;
        $sql .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
        $sql .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
        $sql .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
        $sql .= "    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
        $sql .= "    INNER JOIN SOS_TB_SESP_SOLIC_ESPERA SESP_2 ".PHP_EOL;
        $sql .= "    ON MOFA_2.MOFA_ID_MOVIMENTACAO    = SESP_2.SESP_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    AND MOFA_2.MOFA_DH_FASE           = SESP_2.SESP_DH_FASE ".PHP_EOL;
        $sql .= "    WHERE MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    ) ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  ) ".PHP_EOL;
        $sql .= "OR SESP.SESP_DH_LIMITE_ESP IS NULL) ".PHP_EOL;
        $sql .= "AND ".PHP_EOL;
        $sql .= "  --Ãºltima avaliacao ".PHP_EOL;
        $sql .= "  (SAVS.SAVS_ID_TIPO_SAT = ".PHP_EOL;
        $sql .= "  (SELECT SAVS_1.SAVS_ID_TIPO_SAT ".PHP_EOL;
        $sql .= "  FROM SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_1 ".PHP_EOL;
        $sql .= "  ON MOFA_1.MOFA_ID_MOVIMENTACAO                           = SAVS_1.SAVS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_DH_FASE                                  = SAVS_1.SAVS_DH_FASE ".PHP_EOL;
        $sql .= "  WHERE (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = ".PHP_EOL;
        $sql .= "    (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO), ".PHP_EOL;
        $sql .= "      MAX(MOFA_2.MOFA_DH_FASE) ".PHP_EOL;
        $sql .= "    FROM SAD_TB_MOFA_MOVI_FASE MOFA_2 ".PHP_EOL;
        $sql .= "    INNER JOIN SOS_TB_SAVS_AVALIACAO_SERVICO SAVS_2 ".PHP_EOL;
        $sql .= "    ON MOFA_2.MOFA_ID_MOVIMENTACAO    = SAVS_2.SAVS_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    AND MOFA_2.MOFA_DH_FASE           = SAVS_2.SAVS_DH_FASE ".PHP_EOL;
        $sql .= "    WHERE MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "    ) ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  ) ".PHP_EOL;
        $sql .= "OR SAVS.SAVS_ID_TIPO_SAT IS NULL) ".PHP_EOL;
        $sql .= "AND ".PHP_EOL;
        $sql .= "  -- tipo documento solicitacao ".PHP_EOL;
        $sql .= "  DOCM_ID_TIPO_DOC = 160 ".PHP_EOL;
        $sql .= "AND ".PHP_EOL;
        $sql .= "  -- contendo a Ãºltima fase no historico ".PHP_EOL;
        $sql .= "  (MOFA.MOFA_DH_FASE, MOFA.MOFA_ID_MOVIMENTACAO) = ".PHP_EOL;
        $sql .= "  (SELECT MAX(MOFA_1.MOFA_DH_FASE), ".PHP_EOL;
        $sql .= "    MAX(MOFA_1.MOFA_ID_MOVIMENTACAO) ".PHP_EOL;
        $sql .= "  FROM SAD_TB_DOCM_DOCUMENTO DOCM_1 ".PHP_EOL;
        $sql .= "  INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1 ".PHP_EOL;
        $sql .= "  ON DOCM_1.DOCM_ID_DOCUMENTO = MODO_MOVI_1.MODO_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "  INNER JOIN SAD_TB_MOVI_MOVIMENTACAO MOVI_1 ".PHP_EOL;
        $sql .= "  ON MODO_MOVI_1.MODO_ID_MOVIMENTACAO = MOVI_1.MOVI_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI_1 ".PHP_EOL;
        $sql .= "  ON MOVI_1.MOVI_ID_MOVIMENTACAO = MODE_MOVI_1.MODE_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA_1 ".PHP_EOL;
        $sql .= "  ON MOVI_1.MOVI_ID_MOVIMENTACAO = MOFA_1.MOFA_ID_MOVIMENTACAO ".PHP_EOL;
        $sql .= "  WHERE DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO ".PHP_EOL;
        $sql .= "  AND MOFA_1.MOFA_ID_FASE        = 1000 ".PHP_EOL;
        $sql .= "  ) ".PHP_EOL;
        if ($docmCdLotacaoGeradora != "") {
            $sql .= "AND DOCM_CD_LOTACAO_GERADORA   = $docmCdLotacaoGeradora ".PHP_EOL;
        }
        if ($docmSgSecaoGeradora != "") {
            $sql .= "AND DOCM_SG_SECAO_GERADORA     = '$docmSgSecaoGeradora' ".PHP_EOL;
        }
        if ($docmCdMatriculaCadastro != "") {
            $sql .= "AND DOCM_CD_MATRICULA_CADASTRO = '$docmCdMatriculaCadastro' ".PHP_EOL;
        }
        $sql .= "  --and CXEN_ID_CAIXA_ENTRADA = 1 ".PHP_EOL;
        $sql .= "  --and ".PHP_EOL;
        if (($param["DATA_INICIAL"] != "") && ($param["DATA_FINAL"] != "")) {
            $sql .= "AND DOCM_DH_CADASTRO BETWEEN TO_DATE( '".$param["DATA_INICIAL"]."', 'DD/MM/YYYY hh24:mi:ss' ) "
                    . "AND TO_DATE( '".$param["DATA_FINAL"]."', 'DD/MM/YYYY hh24:mi:ss' ) ".PHP_EOL;
        }
        if ($sserIdServico != "") {
            $sql .= "AND SSER_ID_SERVICO = $sserIdServico ".PHP_EOL;
        }
        if ($sserIdGrupo != "") {
            $sql .= "AND SSER_ID_GRUPO = $sserIdGrupo".PHP_EOL;
        }
        $sql .= "ORDER BY DOCM_DH_CADASTRO ASC, DOCM_NR_DOCUMENTO ".PHP_EOL;
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        return $db->fetchAll($sql);
    }
}