<?php

/**
 * O DataMapper é responsável por mapear a classe de acesso ao banco de dados
 * DbTable e o criar o objeto Model.
 *
 * @author Marcelo Caixeta Rocha <marcelo.caixeta@trf1.jus.br>
 */
class Sosti_Model_DataMapper_Solicitacao extends Zend_Db_Table_Abstract
{

    protected $_dbTable;

    public function __construct()
    {
        $this->setDbTable(new Sosti_Model_DbTable_SosTbSsolSolicitacao());
    }

    private function setDbTable(Sosti_Model_DbTable_SosTbSsolSolicitacao $dbtable)
    {
        $this->_dbTable = $dbtable;
    }

    private function getDbTable()
    {
        return $this->_dbTable;
    }

    public function getSosti($sosti)
    {
        $end = PHP_EOL;
        $sql = "
        SELECT DISTINCT
            SSOL_ID_DOCUMENTO,
            SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

            DOCM_NR_DOCUMENTO,
            DOCM_CD_MATRICULA_CADASTRO,
            DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC,
            DOCM_DH_CADASTRO,
            SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
            SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,

            MOFA_ID_MOVIMENTACAO,
            TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
            MOFA_ID_FASE,

            TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL,

            MOVI_DH_ENCAMINHAMENTO,
            TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,

            MODE_ID_CAIXA_ENTRADA,
            MODE_SG_SECAO_UNID_DESTINO,
            MODE_CD_SECAO_UNID_DESTINO,
            TO_CHAR(MOVI_DH_ENCAMINHAMENTO, 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

            SSER_ID_SERVICO,
            SSER_DS_SERVICO,

            SESP_DH_LIMITE_ESP,
            TRUNC(SESP_DH_LIMITE_ESP - SYSDATE) AS ESPERA_FLAG,

            SOS_P.PKG_SOLIC.SOLIC_VINCULADA(SSOL_ID_DOCUMENTO) VINCULADA,

            TO_CHAR
                (
                    (
                        SELECT
                            SSPA_1.SSPA_DT_PRAZO
                        FROM
                            SAD_TB_MOFA_MOVI_FASE MOFA_1
                        INNER JOIN
                            SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_1
                        ON
                            MOFA_1.MOFA_ID_MOVIMENTACAO  = SSPA_1.SSPA_ID_MOVIMENTACAO
                        AND
                            MOFA_1.MOFA_DH_FASE          = SSPA_1.SSPA_DH_FASE
                        WHERE
                            (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (
                                SELECT
                                    MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),
                                    MAX(MOFA_2.MOFA_DH_FASE)
                                FROM
                                    SAD_TB_MOFA_MOVI_FASE MOFA_2
                                INNER JOIN
                                    SOS_TB_SSPA_SOLIC_PRAZO_ATEND SSPA_2
                                ON
                                    MOFA_2.MOFA_ID_MOVIMENTACAO  = SSPA_2.SSPA_ID_MOVIMENTACAO
                                AND
                                    MOFA_2.MOFA_DH_FASE = SSPA_2.SSPA_DH_FASE
                                WHERE
                                    MOFA_2.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO
                            )
                        AND
                            MOFA_1.MOFA_ID_MOVIMENTACAO = MOVI_ID_MOVIMENTACAO
                        AND
                            SSPA_IC_CONFIRMACAO = 'S'
                    ),
                    'DD/MM/YYYY HH24:MI:SS'
                ) SSPA_DT_PRAZO

        FROM
            (
                SELECT
                    SUB_3.*,
                    MOFA.*,
                    (
                        SELECT
                            MAX(SSES_1.SSES_DH_FASE)
                        FROM
                            SOS_TB_SSES_SERVICO_SOLIC SSES_1
                        WHERE
                            SSES_1.SSES_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                    ) DATA_ULTIMO_SERVICO,
                    (
                        SELECT
                            MAX(SESP_1.SESP_DH_FASE)
                        FROM
                            SOS_TB_SESP_SOLIC_ESPERA SESP_1
                        WHERE
                            SESP_1.SESP_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO
                    ) DATA_ULTIMA_ESPERA
                FROM
                    (
                        SELECT
                            MAX(MOFA_DH_FASE) ULTIMA_FASE_DATA,
                            ULTIMA_MOVIMENTACAO
                        FROM
                            (
                                SELECT
                                    MAX(MODO_MOVI_R_1.MODO_ID_MOVIMENTACAO) ULTIMA_MOVIMENTACAO
                                FROM
                                    (
                                        SELECT
                                            *
                                        FROM
                                            SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                                    ) SUB_1
                                INNER JOIN
                                    SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                                ON
                                    MODO_MOVI.MODO_ID_MOVIMENTACAO = SUB_1.MODE_ID_MOVIMENTACAO
                                INNER JOIN
                                    SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_R_1
                                ON
                                    MODO_MOVI_R_1.MODO_ID_DOCUMENTO = MODO_MOVI.MODO_ID_DOCUMENTO
                                GROUP BY
                                    MODO_MOVI_R_1.MODO_ID_DOCUMENTO
                            ) SUB_2
                        INNER JOIN
                            SAD_TB_MOFA_MOVI_FASE MOFA
                        ON
                            SUB_2.ULTIMA_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                        INNER JOIN
                            SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                        ON
                            SUB_2.ULTIMA_MOVIMENTACAO = MODE_MOVI.MODE_ID_MOVIMENTACAO
                        GROUP BY
                            ULTIMA_MOVIMENTACAO
                    ) SUB_3
                INNER JOIN
                    SAD_TB_MOFA_MOVI_FASE MOFA
                ON
                    SUB_3.ULTIMA_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO
                AND
                    SUB_3.ULTIMA_FASE_DATA     = MOFA.MOFA_DH_FASE
                WHERE
                    MOFA_ID_FASE NOT IN (1000,1014,1026,1081)
            ) SUB_4

        LEFT JOIN
            SOS_TB_SSES_SERVICO_SOLIC SSES
        ON
            SUB_4.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
        AND
            SUB_4.DATA_ULTIMO_SERVICO = SSES.SSES_DH_FASE
        LEFT JOIN
            SOS_TB_SSER_SERVICO SSER
        ON
            SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO
        LEFT JOIN
            SOS_TB_SESP_SOLIC_ESPERA SESP
        ON
            SUB_4.MOFA_ID_MOVIMENTACAO  = SESP.SESP_ID_MOVIMENTACAO
        AND
            SUB_4.DATA_ULTIMA_ESPERA = SESP.SESP_DH_FASE
        INNER JOIN
            SAD_TB_MOVI_MOVIMENTACAO MOVI
        ON
            MOVI.MOVI_ID_MOVIMENTACAO  = SUB_4.MOFA_ID_MOVIMENTACAO
        INNER JOIN
            SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
        ON
            MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO
        INNER JOIN
            SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON
            MOVI.MOVI_ID_MOVIMENTACAO = MODO_MOVI.MODO_ID_MOVIMENTACAO
        INNER JOIN
            SAD_TB_DOCM_DOCUMENTO DOCM
        ON
            DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
        INNER JOIN
            SOS_TB_SSOL_SOLICITACAO SSOL
        ON
            SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
        WHERE
            DOCM_ID_TIPO_DOC = 160
        ";

        if (is_array($sosti)) {
            $sql .= "
                AND
                    SSOL.SSOL_ID_DOCUMENTO IN (" . implode(', ', $sosti) . ")
            ";
        } else {
            $sql .= "
                AND
                    SSOL.SSOL_ID_DOCUMENTO = $sosti
            ";
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        return $db->fetchRow($sql);
    }

    public function getVinculos($id_doc)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $stmt = "SELECT" . PHP_EOL;
        $stmt .= "*" . PHP_EOL;
        $stmt .= "FROM SAD.SAD_TB_VIDC_VINCULACAO_DOC A" . PHP_EOL;
        $stmt .= "WHERE A.VIDC_ID_DOC_PRINCIPAL = $id_doc" . PHP_EOL;
        return $db->fetchAll($stmt);
    }

}
