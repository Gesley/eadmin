<?php

/**
 * @category    TRF1
 * @package        Trf1_Orcamento_Negocio_Ne
 * @copyright    Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author        Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license        FREE, keep original copyrights
 * @version        controlada pelo SVN
 * @tutorial    Tutorial abaixo
 *
 * TRF1, Classe negocial sobre Orçamento - Notas de empenho
 *
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 *
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 *
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 *
 * ====================================================================================================
 * TUTORIAL
 * ====================================================================================================
 * Descrever...
 *
 */
class Trf1_Orcamento_Negocio_Ne {

    /**
     * Model das Notas de Empenho
     */
    protected $_dados = null;

    /**
     * Classe construtora
     *
     * @param    none
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct() {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbNoemNotaEmpenho();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return    array        Chave primária ou composta
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela() {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return    array        Chave primária ou composta
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria() {
        return $this->_dados->chavePrimaria();
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param    none
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaCombo() {
        return false;
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param    none
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaListagem() {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        $cacheId = $cache->gerarID_Listagem('ne');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            $sql = $this->retornaSqlListagem();

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchAll($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    public function retornaSqlListagem($ano = null) {
        // Não existindo o cache, busca do banco
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $ug = $sessaoOrcamento->ug;
        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        // Verifica possível restrição de registros
        $joinUg = '';
        $condicaoUg = '';
        $condicaoAno = '';
        if ($ug != 'todas') {
            $joinUg = "
Left JOIN
  CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON
    UNGE.UNGE_CD_UG = NOEM.NOEM_CD_UG_FAVORECIDO
                        ";

            $condicaoUg = "
  UNGE.UNGE_CD_UG NOT IN (90032, 90049, 110407) AND
  UNGE.UNGE_SG_SECAO = '$ug'
    $condicaoResponsaveis
  AND

                            ";
        }

        if ($ano) {
            $condicaoAno = " SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) = $ano AND ";
        }

        $sql = "
            SELECT
                SUBSTR(NOEM_CD_NOTA_EMPENHO, 0, 4) AS NOEM_ANO,
                CASE WHEN
                    SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) = '" . date('Y') . "' THEN 1
                    ELSE 2
                END AS EXERCICIO,
                NOEM.NOEM_CD_UG_FAVORECIDO,
                NOEM.NOEM_CD_NOTA_EMPENHO,
                
                NVL(NOEM.NOEM_CD_NE_REFERENCIA, 'NE original') AS NOEM_CD_NE_REFERENCIA,
                -- NVL(NOEM.NOEM_NR_DESPESA, 0) AS NOEM_NR_DESPESA,
                
                -- transforma despesas null em 0 para o filtro da grid
                CASE WHEN
                  NOEM.NOEM_NR_DESPESA IS NULL
                  THEN 0
                  ELSE NOEM_NR_DESPESA
                END AS NOEM_NR_DESPESA,


                                -- segundo sosti 2016010001784017840160000426 - busca ptres da RDO                               
                CASE WHEN 
                 NOEM.NOEM_CD_PT_RESUMIDO > 0
                 THEN NOEM.NOEM_CD_PT_RESUMIDO
                ELSE
                  DESP.DESP_CD_PT_RESUMIDO
                  END AS NOEM_CD_PT_RESUMIDO,
                  
                P.PTRS_SG_PT_RESUMIDO,
                
                UNOR.UNOR_CD_UNID_ORCAMENTARIA,
                CASE WHEN 
                 NOEM.NOEM_CD_ELEMENTO_DESPESA_SUB > 0
                 THEN NOEM.NOEM_CD_ELEMENTO_DESPESA_SUB 
                 ELSE
                 DESP.DESP_CD_ELEMENTO_DESPESA_SUB
                 END AS NOEM_CD_ELEMENTO_DESPESA_SUB,
                TO_CHAR(NOEM.NOEM_DH_NE, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')     AS NOEM_DH_NE,
                /*
                NOEM.NOEM_CD_EVENTO,
                NOEM.NOEM_CD_FONTE,
                NOEM.NOEM_CD_CATEGORIA,
                NOEM.NOEM_CD_VINCULACAO,
                */
                /* configuracao para restricao de ug */
                RHCL.LOTA_DSC_LOTACAO AS DS_RESPONSAVEL,
                RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_FAMILIA_RESPONSAVEL,
                RHCL.LOTA_SIGLA_LOTACAO || ' - ' ||
                REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' ||
                RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_DS_FAMILIA_RESPONSAVEL,
                RHCL.LOTA_SIGLA_SECAO,
                RHCL.LOTA_COD_LOTACAO,

                NOEM.NOEM_DS_OBSERVACAO,
                NOEM.NOEM_NR_PROCESSO,
                NOEM.NOEM_CD_EVENTO,
                -- NOEM.NOEM_NU_TIPO_NE
                -- NOEM.NOEM_VL_NE,
                NOEM.NOEM_VL_NE_ACERTADO,
                CASE NOEM.NOEM_IC_ACERTADO_MANUALMENTE
                    WHEN 1 THEN 'Sim '
                    ELSE 'Não '
                END AS NOEM_IC_ACERTADO_MANUALMENTE

            FROM CEO_TB_NOEM_NOTA_EMPENHO NOEM

            Left JOIN CEO_TB_DESP_DESPESA DESP ON
                DESP.DESP_NR_DESPESA = NOEM.NOEM_NR_DESPESA

            Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO                                 P ON
                P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO

            Left JOIN CEO_TB_RESP_RESPONSAVEL RESP ON
                RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL

            Left JOIN RH_CENTRAL_LOTACAO RHCL ON
                RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND
                RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO

            Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR
                ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = p.PTRS_CD_UNID_ORCAMENTARIA

            Left JOIN(
                SELECT
                    REQV_NR_DESPESA,
                    MAX(
                    CASE WHEN REQV_CD_PROC_FISICO IS NOT NULL THEN REQV_CD_PROC_FISICO
                         WHEN REQV_CD_PROC_DIGITAL IS NOT NULL THEN REQV_CD_PROC_DIGITAL
                    END
                    ) PROCESSO

                FROM CEO_TB_REQV_REQU_VARIACAO
                GROUP BY REQV_NR_DESPESA) BASE ON BASE.REQV_NR_DESPESA = NOEM.NOEM_NR_DESPESA
            $joinUg WHERE $condicaoAno $condicaoUg 0 = 0 AND NOEM_DH_EXCLUSAO_LOGICA IS NULL AND 
            /* CORRECAO BUG DA UG */
            NOEM.NOEM_CD_UG_FAVORECIDO = DESP.DESP_CD_UG
            ORDER BY EXERCICIO
  ";
        
    
Zend_Debug::dump($sql);
die;

        return $sql;
    }

    public function retornaListagemInconsistencia() {
        $sql = $this->retornaSqlListagemInconsistencia();

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    /**
     * Retorna registros que apresentam inconsistências entre NEs e RDOs
     *
     * @param string $ano
     * @param boolean $filtra Filtra, ou não, registros manualmente acertados
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlListagemInconsistencia($ano = null, $filtra = false) {
        // Verifica UG
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $ug = $sessaoOrcamento->ug;

        // Verifica possível restrição de registros
        $joinUg = '';
        $condicaoUg = '';
        $condicaoAno = '';
        if ($ug != 'todas') {
            $joinUg = "
Left JOIN
  CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON
    UNGE.UNGE_CD_UG = NOEM.NOEM_CD_UG_FAVORECIDO
                        ";

            $condicaoUg = "
  UNGE.UNGE_CD_UG NOT IN (90032, 90049, 110407) AND
  UNGE.UNGE_SG_SECAO = '$ug' AND
                            ";
        }

        if ($ano) {
            $condicaoAno = " SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) = $ano AND ";
        }

        if ($filtra) {
            $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
            $condicaoAcertos = " NOEM.NOEM_IC_ACERTADO_MANUALMENTE = $acertadaNao AND ";
        }

        $sql = "
SELECT
    CASE WHEN SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) <> DESP.DESP_AA_DESPESA
    THEN 'Ano; ' ELSE '' END ||
    CASE WHEN NOEM.NOEM_CD_UG_FAVORECIDO <> DESP.DESP_CD_UG
    THEN 'UG Favorecida; ' ELSE '' END ||
    CASE WHEN NOEM.NOEM_CD_FONTE <> DESP.DESP_CD_FONTE
    THEN 'Fonte; ' ELSE '' END ||
    CASE WHEN NOEM.NOEM_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO
    THEN 'PTRES; ' ELSE '' END ||
    CASE WHEN SUBSTR(NOEM.NOEM_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6)
    THEN 'Natureza; ' ELSE '' END ||
    CASE WHEN NOEM.NOEM_CD_EVENTO = '400093' THEN 'Ajuste' ELSE ''
    END AS NOEM_INCONSISTENCIA,

    NOEM.NOEM_CD_NOTA_EMPENHO,
    NVL(NOEM.NOEM_NR_DESPESA, 0) AS NOEM_NR_DESPESA,
    SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) AS NOEM_ANO,
    DESP.DESP_AA_DESPESA,
    NOEM.NOEM_CD_UG_FAVORECIDO,
    DESP.DESP_CD_UG,
    NOEM.NOEM_CD_FONTE,
    DESP.DESP_CD_FONTE,
    NOEM.NOEM_CD_PT_RESUMIDO,
    UNOR2.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_NOEM,
    DESP.DESP_CD_PT_RESUMIDO,
    UNOR1.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_DESP,
    NOEM.NOEM_CD_ELEMENTO_DESPESA_SUB,
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
    NVL(NOEM.NOEM_CD_NE_REFERENCIA, 'NE original') AS NOEM_CD_NE_REFERENCIA,
    TO_CHAR(NOEM.NOEM_DH_NE, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "') AS NOEM_DH_NE,
    NOEM.NOEM_DS_OBSERVACAO,
    BASE.PROCESSO AS NOEM_PROCESSO,
    NOEM.NOEM_CD_EVENTO,
    NVL(NOEM.NOEM_VL_NE_ACERTADO, 0) AS NOEM_VL_NE_ACERTADO,
    CASE NOEM.NOEM_IC_ACERTADO_MANUALMENTE
        WHEN 1 THEN 'Sim '
        ELSE 'Não '
    END AS NOEM_IC_ACERTADO_MANUALMENTE
FROM
    CEO_TB_NOEM_NOTA_EMPENHO NOEM
LEFT JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOEM.NOEM_NR_DESPESA
LEFT JOIN
    CEO_TB_PTRS_PROGRAMA_TRABALHO P1 ON
        P1.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
LEFT JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR1 ON
        UNOR1.UNOR_CD_UNID_ORCAMENTARIA = P1.PTRS_CD_UNID_ORCAMENTARIA

LEFT JOIN
    CEO_TB_PTRS_PROGRAMA_TRABALHO P2 ON
        P2.PTRS_CD_PT_RESUMIDO = NOEM.NOEM_CD_PT_RESUMIDO
LEFT JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR2 ON
        UNOR2.UNOR_CD_UNID_ORCAMENTARIA = P2.PTRS_CD_UNID_ORCAMENTARIA

Left JOIN
    (
    SELECT
        REQV_NR_DESPESA,
        MAX( CASE
            WHEN REQV_CD_PROC_FISICO IS NOT NULL THEN REQV_CD_PROC_FISICO
            WHEN REQV_CD_PROC_DIGITAL IS NOT NULL THEN REQV_CD_PROC_DIGITAL
        END ) PROCESSO
    FROM
        CEO_TB_REQV_REQU_VARIACAO
    GROUP BY
        REQV_NR_DESPESA
    ) BASE ON
        BASE.REQV_NR_DESPESA = NOEM.NOEM_NR_DESPESA
$joinUg
WHERE
$condicaoUg
$condicaoAno
$condicaoAcertos
    NOEM.NOEM_CD_NOTA_EMPENHO IS NOT NULL AND
    NOEM.NOEM_CD_NE_REFERENCIA IS NULL AND
    (
        SUBSTR(NOEM.NOEM_CD_NOTA_EMPENHO, 0, 4) <> DESP.DESP_AA_DESPESA OR
        NOEM.NOEM_CD_UG_FAVORECIDO <> DESP.DESP_CD_UG OR
        NOEM.NOEM_CD_FONTE <> DESP.DESP_CD_FONTE OR
        NOEM.NOEM_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO OR
        SUBSTR(NOEM.NOEM_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6)
    )
              ";

        return $sql;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param    int        $empenho            Chave primária para busca do registro
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistro($empenho) {
        $sql = "
SELECT
    NOEM_CD_NOTA_EMPENHO,
    SUBSTR(NOEM_CD_NOTA_EMPENHO, 0, 4) AS NOEM_ANO,
    NOEM_CD_UG_OPERADOR,
    NOEM_CD_NE_REFERENCIA,
    NOEM_CD_EVENTO,
    NOEM_CD_FONTE,
    NOEM_CD_VINCULACAO,
    NOEM_VL_NE,
    NOEM_CD_PT_RESUMIDO,
    NOEM_CD_ELEMENTO_DESPESA_SUB,
    NOEM_CD_CATEGORIA,
    NOEM_NR_DESPESA,
    NOEM_NR_PROCESSO,
    TO_CHAR(NOEM_DH_NE,'DD/MM/YYYY')                                                  AS NOEM_DH_NE,
    TO_CHAR(NOEM_DT_EMISSAO,'DD/MM/YYYY')                                             AS NOEM_DT_EMISSAO,
    NOEM_DS_OBSERVACAO,
    TO_CHAR(NOEM_VL_NE_ACERTADO,  '" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "') AS NOEM_VL_NE_ACERTADO,
    NOEM_NU_TIPO_NE,
    NOEM_CD_UG_FAVORECIDO,
    NOEM_IC_ACERTADO_MANUALMENTE
 FROM
    CEO_TB_NOEM_NOTA_EMPENHO
WHERE
    NOEM_CD_NOTA_EMPENHO = '$empenho'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param    int        $empenho            Chave primária para busca do registro
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistroNomeAmigavel($empenho) {
        $sql = "
SELECT
  NOEM_CD_NOTA_EMPENHO               AS \"Nota de empenho\",
  NOEM_CD_NE_REFERENCIA              AS \"Referência\",
  SUBSTR(NOEM_CD_NOTA_EMPENHO, 0, 4) AS \"Ano\",
  NOEM_CD_UG_OPERADOR                AS \"UG operador\",
  NOEM_NR_DESPESA                    AS \"Despesa\",
  NOEM_DH_NE                         AS \"Data e hora do empenho\",
  NOEM_DT_EMISSAO                    AS \"Emissao\",
  NOEM_CD_FONTE                      AS \"Fonte\",
  NOEM_CD_PT_RESUMIDO                AS \"PTRES\",
  NOEM_CD_ELEMENTO_DESPESA_SUB       AS \"Natureza da despesa\",
  NOEM_DS_OBSERVACAO                 AS \"Observação\",
  BASE.PROCESSO                      AS \"Processo\",
  NOEM_CD_EVENTO                     AS \"Evento\",
  NOEM_VL_NE_ACERTADO                AS \"Valor\",
  /*
  NOEM_CD_VINCULACAO AS \"Vinculação\",
  NOEM_CD_CATEGORIA AS \"Categoria\",
  NOEM_NU_TIPO_NE  AS \"Tipo de empenho\"
  */
    CASE NOEM_IC_ACERTADO_MANUALMENTE
        WHEN 1 THEN 'Sim '
        ELSE 'Não '
    END                                 AS \"Acertado manualmente\"
FROM
  CEO_TB_NOEM_NOTA_EMPENHO
Left JOIN
    (
    SELECT
        REQV_NR_DESPESA,
        MAX( CASE
            WHEN REQV_CD_PROC_FISICO IS NOT NULL THEN REQV_CD_PROC_FISICO
            WHEN REQV_CD_PROC_DIGITAL IS NOT NULL THEN REQV_CD_PROC_DIGITAL
        END ) PROCESSO
    FROM
        CEO_TB_REQV_REQU_VARIACAO
    GROUP BY
        REQV_NR_DESPESA
    ) BASE ON
        BASE.REQV_NR_DESPESA = NOEM_NR_DESPESA
WHERE
  NOEM_CD_NOTA_EMPENHO          = '$empenho'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * @deprecated Apresenta a listagem da nota de empenho
     *
     * @param    none
     * @return    array
     * @author    Dayane Freire
     */
    public function getListagemOLD() {
        $sql = "
                        SELECT
                              NOEM_CD_NOTA_EMPENHO,
                              UNGE_DS_UG,
                              NOEM_CD_NE_REFERENCIA,
                              EVEN_DS_EVENTO,
                              PTRS_DS_PT_RESUMIDO,
                              FONT_DS_FONTE,
                              VINC_DS_VINCULACAO,
                              -- NOEM_MM_EXECUCAO,
                              -- NOEM_AA_EXECUCAO,
                              NOEM_VL_NE,
                              EDSB_DS_ELEMENTO_DESPESA_SUB,
                              CATE_DS_CATEGORIA,
                              NOEM_NR_DESPESA,
                              NOEM_DH_NE,
                              NOEM_DT_EMISSAO
                              NOEM_DS_OBSERVACAO,
                              NOEM_VL_NE_ACERTADO,
                              NOEM_NU_TIPO_NE
                      FROM
                              CEO_TB_NOEM_NOTA_EMPENHO  NEMP
                      LEFT JOIN
                             CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON
                             UNGE.UNGE_CD_UG = NEMP.NOEM_CD_UG_OPERADOR
                      LEFT JOIN
                             CEO_TB_FONT_FONTE FONT ON
                             FONT.FONT_CD_FONTE =  NEMP.NOEM_CD_FONTE
                      LEFT JOIN
                                   CEO_TB_PTRS_PROGRAMA_TRABALHO      PTRS ON
                                         PTRS.PTRS_CD_PT_RESUMIDO     = NEMP.NOEM_CD_PT_RESUMIDO
                      LEFT JOIN
                                   CEO_TB_EDSB_ELEMENTO_SUB_DESP      EDSB ON
                                         EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB  = NEMP.NOEM_CD_ELEMENTO_DESPESA_SUB
                      LEFT JOIN
                                   CEO_TB_CATE_CATEGORIA          CATE ON
                                        CATE.CATE_CD_CATEGORIA        = NEMP.NOEM_CD_CATEGORIA
                      LEFT JOIN
                                  CEO_TB_VINC_VINCULACAO          VINC ON
                                        VINC.VINC_CD_VINCULACAO       = NEMP.NOEM_CD_VINCULACAO
                      LEFT JOIN
                           CEO_TB_EVEN_EVENTO_NE            EVEN ON
                           EVEN.EVEN_CD_EVENTO            = NEMP.NOEM_CD_EVENTO";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    /**
     * Retorna um único registro
     *
     * @param    int        $elemento    Chave primária para busca do registro
     * @return    array
     * @author    Dayane Freire
     */
    public function getNotaEmpenho($notaEmpenho) {
        $sql = "
                         SELECT
                                NOEM_CD_NOTA_EMPENHO,
                                NOEM_CD_UG_OPERADOR,
                                NOEM_CD_NE_REFERENCIA,
                                NOEM_CD_EVENTO,
                                NOEM_CD_PT_RESUMIDO,
                                NOEM_CD_FONTE,
                                NOEM_CD_VINCULACAO,
                                -- NOEM_MM_EXECUCAO,
                                -- NOEM_AA_EXECUCAO,
                                NOEM_VL_NE,
                                NOEM_CD_ELEMENTO_DESPESA_SUB,
                                NOEM_CD_CATEGORIA,
                                NOEM_NR_DESPESA,
                                NOEM_DH_NE,
                                NOEM_DT_EMISSAO,
                                NOEM_DS_OBSERVACAO,
                                NOEM_VL_NE_ACERTADO,
                                NOEM_NU_TIPO_NE
                        FROM
                                CEO_TB_NOEM_NOTA_EMPENHO
                        WHERE
                                NOEM_CD_NOTA_EMPENHO =  '$notaEmpenho'
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna os valores executados de dada despesa
     *
     * @param    int        $despesa            Código da despesa
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaExecucao($despesa = null, $mes = 0) {
        /*
        $negocio = new Trf1_Orcamento_Negocio_Saldo ();
        $sql = $negocio->_retornaSqlExecucao ( $despesa, $mes );
         */
        $sql = $this->_retornaSqlExecucaoPorDespesa($despesa, $mes);

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchRow($sql);

        if (!$dados) {
            $dados = array('EXEC_VL_JANEIRO' => 0, 'EXEC_VL_FEVEREIRO' => 0, 'EXEC_VL_MARCO' => 0, 'EXEC_VL_ABRIL' => 0, 'EXEC_VL_MAIO' => 0, 'EXEC_VL_JUNHO' => 0, 'EXEC_VL_JULHO' => 0, 'EXEC_VL_AGOSTO' => 0, 'EXEC_VL_SETEMBRO' => 0, 'EXEC_VL_OUTUBRO' => 0, 'EXEC_VL_NOVEMBRO' => 0, 'EXEC_VL_DEZEMBRO' => 0, 'EXEC_VL_TOTAL' => 0);
        }

        return $dados;
    }

    /**
     * Retorna os valores executados de dada despesa
     *
     * @param    int        $despesa            Código da despesa
     * @return    array
     * @author    Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaExecucaoEmpenho($empenho = null, $mes = 0) {
        /*
        $negocio = new Trf1_Orcamento_Negocio_Saldo ();
        $sql = $negocio->_retornaSqlExecucao ( $despesa, $mes );
         */
        $sql = $this->_retornaSqlExecucaoPorEmpenho($empenho, $mes);

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchRow($sql);

        if (!$dados) {
            $dados = array('EXEC_VL_JANEIRO' => 0, 'EXEC_VL_FEVEREIRO' => 0, 'EXEC_VL_MARCO' => 0, 'EXEC_VL_ABRIL' => 0, 'EXEC_VL_MAIO' => 0, 'EXEC_VL_JUNHO' => 0, 'EXEC_VL_JULHO' => 0, 'EXEC_VL_AGOSTO' => 0, 'EXEC_VL_SETEMBRO' => 0, 'EXEC_VL_OUTUBRO' => 0, 'EXEC_VL_NOVEMBRO' => 0, 'EXEC_VL_DEZEMBRO' => 0, 'EXEC_VL_TOTAL' => 0);
        }

        return $dados;
    }

    public function retornaListagemExecucao() {
        $sql = $this->_retornaSqlExecucaoPorDespesa();
        $sql = $this->_retornaSqlExecucaoPorEmpenho();

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    private function _retornaSqlExecucaoPorEmpenho($empenho = null, $mes = 0) {
        $whereEmpenho = "";
        if ($empenho) {
            $whereEmpenho = " WHERE NOEM_CD_NOTA_EMPENHO = '$empenho' ";
        }

        // Se não for informado o mês o sistema assume o mês atual
        if ($mes == 0) {
            $mes = date('n');
        }

        $sql = "
SELECT
  NOEM_CD_NOTA_EMPENHO,
  SUM(NVL(EXEC_VL_JANEIRO, 0))  AS EXEC_VL_JANEIRO,
  SUM(NVL(EXEC_VL_FEVEREIRO, 0))  AS EXEC_VL_FEVEREIRO,
  SUM(NVL(EXEC_VL_MARCO, 0))    AS EXEC_VL_MARCO,
  SUM(NVL(EXEC_VL_ABRIL, 0))    AS EXEC_VL_ABRIL,
  SUM(NVL(EXEC_VL_MAIO, 0))   AS EXEC_VL_MAIO,
  SUM(NVL(EXEC_VL_JUNHO, 0))    AS EXEC_VL_JUNHO,
  SUM(NVL(EXEC_VL_JULHO, 0))    AS EXEC_VL_JULHO,
  SUM(NVL(EXEC_VL_AGOSTO, 0))   AS EXEC_VL_AGOSTO,
  SUM(NVL(EXEC_VL_SETEMBRO, 0)) AS EXEC_VL_SETEMBRO,
  SUM(NVL(EXEC_VL_OUTUBRO, 0))  AS EXEC_VL_OUTUBRO,
  SUM(NVL(EXEC_VL_NOVEMBRO, 0)) AS EXEC_VL_NOVEMBRO,
  SUM(NVL(EXEC_VL_DEZEMBRO, 0)) AS EXEC_VL_DEZEMBRO,
  SUM(NVL(VR_EXEC_MES_ATUAL, 0))  AS VR_EXEC_MES_ATUAL,
  SUM(NVL(VR_EXEC_PASSADA, 0))  AS VR_EXEC_PASSADA,
  SUM(NVL(VR_EXEC_TOTAL, 0))    AS VR_EXEC_TOTAL,
  SUM(NVL(VR_EXEC_TOTAL, 0))    AS EXEC_VL_TOTAL,
  SUM(NVL(VR_EXEC_TOTAL, 0))    AS VALOR
FROM
  (
  SELECT
    NOEM_CD_NOTA_EMPENHO,
    /* Valores dos meses */
    EXEC_VL_JANEIRO,
    EXEC_VL_FEVEREIRO,
    EXEC_VL_MARCO,
    EXEC_VL_ABRIL,
    EXEC_VL_MAIO,
    EXEC_VL_JUNHO,
    EXEC_VL_JULHO,
    EXEC_VL_AGOSTO,
    EXEC_VL_SETEMBRO,
    EXEC_VL_OUTUBRO,
    EXEC_VL_NOVEMBRO,
    EXEC_VL_DEZEMBRO,
    /* Valor do mês atual */
    CASE $mes
      WHEN 01 THEN EXEC_VL_JANEIRO
      WHEN 02 THEN EXEC_VL_FEVEREIRO
      WHEN 03 THEN EXEC_VL_MARCO
      WHEN 04 THEN EXEC_VL_ABRIL
      WHEN 05 THEN EXEC_VL_MAIO
      WHEN 06 THEN EXEC_VL_JUNHO
      WHEN 07 THEN EXEC_VL_JULHO
      WHEN 08 THEN EXEC_VL_AGOSTO
      WHEN 09 THEN EXEC_VL_SETEMBRO
      WHEN 10 THEN EXEC_VL_OUTUBRO
      WHEN 11 THEN EXEC_VL_NOVEMBRO
      WHEN 12 THEN EXEC_VL_DEZEMBRO
    END                           AS VR_EXEC_MES_ATUAL,
    /* Valor executado acumulado até o mês atual */
    CASE $mes - 1
      WHEN 01 THEN EXEC_VL_JANEIRO
      WHEN 02 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO
      WHEN 03 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO
      WHEN 04 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL
      WHEN 05 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO
      WHEN 06 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO
      WHEN 07 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO
      WHEN 08 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO
      WHEN 09 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO
      WHEN 10 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO
      WHEN 11 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO + EXEC_VL_NOVEMBRO
      WHEN 12 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO + EXEC_VL_NOVEMBRO +  + EXEC_VL_DEZEMBRO
      ELSE 0
    END                           AS VR_EXEC_PASSADA,
    /* Valor executado total */
    EXEC_VL_JANEIRO   +
    EXEC_VL_FEVEREIRO +
    EXEC_VL_MARCO   +
    EXEC_VL_ABRIL   +
    EXEC_VL_MAIO    +
    EXEC_VL_JUNHO   +
    EXEC_VL_JULHO   +
    EXEC_VL_AGOSTO    +
    EXEC_VL_SETEMBRO  +
    EXEC_VL_OUTUBRO   +
    EXEC_VL_NOVEMBRO  +
    EXEC_VL_DEZEMBRO                    AS VR_EXEC_TOTAL
  FROM
    (
    SELECT
      AA2.NOEM_CD_NOTA_EMPENHO
    FROM
      CEO_TB_NOEM_NOTA_EMPENHO    AA2
    WHERE
      AA2.NOEM_CD_NE_REFERENCIA IS NULL

    UNION ALL

    /* Empenhos de reforço ou anulação */
    SELECT
      AA3.NOEM_CD_NOTA_EMPENHO
    FROM
      CEO_TB_NOEM_NOTA_EMPENHO    AA3
    Left JOIN
      CEO_TB_NOEM_NOTA_EMPENHO    BB2 ON
        BB2.NOEM_CD_NOTA_EMPENHO = AA3.NOEM_CD_NE_REFERENCIA
    WHERE
      AA3.NOEM_CD_NE_REFERENCIA IS NOT NULL
    )                                         Campos
  Left JOIN
    CEO_TB_EXEC_EXECUCAO_NE               Execucao ON
      Execucao.EXEC_CD_NOTA_EMPENHO = Campos.NOEM_CD_NOTA_EMPENHO
  )
$whereEmpenho
GROUP BY
  NOEM_CD_NOTA_EMPENHO
        ";

        return $sql;
    }

    public function _retornaSqlExecucaoPorDespesa($despesa = null, $mes = 0) {
        // Preserva o parâmetro
        $strDespesas = $despesa;

        if (is_array($despesa)) {
            // Junta numa string os valores separados por vírgula
            $strDespesas = implode(', ', $despesa);
        }

        $whereDespesa = "";
        if ($strDespesas) {
            $whereDespesa = " WHERE EXEC_NR_DESPESA IN ( $strDespesas ) ";
        }

        // Se não for informado o mês o sistema assume o mês atual
        /*
        if ($mes == 0) {
        $mes = date ( 'n' );
        }
         */

        $sql_OLD = "
SELECT
  EXEC_NR_DESPESA,
  SUM(NVL(EXEC_VL_JANEIRO, 0))  AS EXEC_VL_JANEIRO,
  SUM(NVL(EXEC_VL_FEVEREIRO, 0))  AS EXEC_VL_FEVEREIRO,
  SUM(NVL(EXEC_VL_MARCO, 0))    AS EXEC_VL_MARCO,
  SUM(NVL(EXEC_VL_ABRIL, 0))    AS EXEC_VL_ABRIL,
  SUM(NVL(EXEC_VL_MAIO, 0))   AS EXEC_VL_MAIO,
  SUM(NVL(EXEC_VL_JUNHO, 0))    AS EXEC_VL_JUNHO,
  SUM(NVL(EXEC_VL_JULHO, 0))    AS EXEC_VL_JULHO,
  SUM(NVL(EXEC_VL_AGOSTO, 0))   AS EXEC_VL_AGOSTO,
  SUM(NVL(EXEC_VL_SETEMBRO, 0)) AS EXEC_VL_SETEMBRO,
  SUM(NVL(EXEC_VL_OUTUBRO, 0))  AS EXEC_VL_OUTUBRO,
  SUM(NVL(EXEC_VL_NOVEMBRO, 0)) AS EXEC_VL_NOVEMBRO,
  SUM(NVL(EXEC_VL_DEZEMBRO, 0)) AS EXEC_VL_DEZEMBRO,
  SUM(NVL(VR_EXEC_MES_ATUAL, 0))  AS VR_EXEC_MES_ATUAL,
  SUM(NVL(VR_EXEC_PASSADA, 0))  AS VR_EXEC_PASSADA,
  SUM(NVL(VR_EXEC_TOTAL, 0))    AS VR_EXEC_TOTAL,
  SUM(NVL(VR_EXEC_TOTAL, 0))    AS VALOR
FROM
  (
  SELECT
    EXEC_NR_DESPESA,
    NOEM_CD_NOTA_EMPENHO,
    /* Valores dos meses */
    EXEC_VL_JANEIRO,
    EXEC_VL_FEVEREIRO,
    EXEC_VL_MARCO,
    EXEC_VL_ABRIL,
    EXEC_VL_MAIO,
    EXEC_VL_JUNHO,
    EXEC_VL_JULHO,
    EXEC_VL_AGOSTO,
    EXEC_VL_SETEMBRO,
    EXEC_VL_OUTUBRO,
    EXEC_VL_NOVEMBRO,
    EXEC_VL_DEZEMBRO,
    /* Valor do mês atual */
    CASE $mes
      WHEN 01 THEN EXEC_VL_JANEIRO
      WHEN 02 THEN EXEC_VL_FEVEREIRO
      WHEN 03 THEN EXEC_VL_MARCO
      WHEN 04 THEN EXEC_VL_ABRIL
      WHEN 05 THEN EXEC_VL_MAIO
      WHEN 06 THEN EXEC_VL_JUNHO
      WHEN 07 THEN EXEC_VL_JULHO
      WHEN 08 THEN EXEC_VL_AGOSTO
      WHEN 09 THEN EXEC_VL_SETEMBRO
      WHEN 10 THEN EXEC_VL_OUTUBRO
      WHEN 11 THEN EXEC_VL_NOVEMBRO
      WHEN 12 THEN EXEC_VL_DEZEMBRO
    END                           AS VR_EXEC_MES_ATUAL,
    /* Valor executado acumulado até o mês atual */
    CASE $mes - 1
      WHEN 01 THEN EXEC_VL_JANEIRO
      WHEN 02 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO
      WHEN 03 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO
      WHEN 04 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL
      WHEN 05 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO
      WHEN 06 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO
      WHEN 07 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO
      WHEN 08 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO
      WHEN 09 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO
      WHEN 10 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO
      WHEN 11 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO + EXEC_VL_NOVEMBRO
      WHEN 12 THEN EXEC_VL_JANEIRO + EXEC_VL_FEVEREIRO + EXEC_VL_MARCO + EXEC_VL_ABRIL + EXEC_VL_MAIO + EXEC_VL_JUNHO + EXEC_VL_JULHO + EXEC_VL_AGOSTO + EXEC_VL_SETEMBRO + EXEC_VL_OUTUBRO + EXEC_VL_NOVEMBRO +  + EXEC_VL_DEZEMBRO
      ELSE 0
    END                           AS VR_EXEC_PASSADA,
    /* Valor executado total */
    EXEC_VL_JANEIRO   +
    EXEC_VL_FEVEREIRO +
    EXEC_VL_MARCO   +
    EXEC_VL_ABRIL   +
    EXEC_VL_MAIO    +
    EXEC_VL_JUNHO   +
    EXEC_VL_JULHO   +
    EXEC_VL_AGOSTO    +
    EXEC_VL_SETEMBRO  +
    EXEC_VL_OUTUBRO   +
    EXEC_VL_NOVEMBRO  +
    EXEC_VL_DEZEMBRO                    AS VR_EXEC_TOTAL
FROM
    (
    SELECT
      AA2.NOEM_NR_DESPESA     AS EXEC_NR_DESPESA,
      AA2.NOEM_CD_NOTA_EMPENHO
    FROM
      CEO_TB_NOEM_NOTA_EMPENHO    AA2
    WHERE
      AA2.NOEM_CD_NE_REFERENCIA IS NULL

    UNION ALL

    /* Empenhos de reforço ou anulação */
    SELECT
      BB2.NOEM_NR_DESPESA     AS EXEC_NR_DESPESA,
      AA3.NOEM_CD_NOTA_EMPENHO
    FROM
      CEO_TB_NOEM_NOTA_EMPENHO    AA3
    Left JOIN
      CEO_TB_NOEM_NOTA_EMPENHO    BB2 ON
        BB2.NOEM_CD_NOTA_EMPENHO = AA3.NOEM_CD_NE_REFERENCIA
    WHERE
      AA3.NOEM_CD_NE_REFERENCIA IS NOT NULL
    )                                         Campos
  Left JOIN
    CEO_TB_EXEC_EXECUCAO_NE               Execucao ON
      Execucao.EXEC_CD_NOTA_EMPENHO = Campos.NOEM_CD_NOTA_EMPENHO
  )
$whereDespesa
GROUP BY
  EXEC_NR_DESPESA
        ";

        $sql = "
SELECT
    EXECUCOES.EXEC_NR_DESPESA,
    SUM(EXEC_VL_JANEIRO) AS EXEC_VL_JANEIRO,
    SUM(EXEC_VL_FEVEREIRO) AS EXEC_VL_FEVEREIRO,
    SUM(EXEC_VL_MARCO) AS EXEC_VL_MARCO,
    SUM(EXEC_VL_ABRIL) AS EXEC_VL_ABRIL,
    SUM(EXEC_VL_MAIO) AS EXEC_VL_MAIO,
    SUM(EXEC_VL_JUNHO) AS EXEC_VL_JUNHO,
    SUM(EXEC_VL_JULHO) AS EXEC_VL_JULHO,
    SUM(EXEC_VL_AGOSTO) AS EXEC_VL_AGOSTO,
    SUM(EXEC_VL_SETEMBRO) AS EXEC_VL_SETEMBRO,
    SUM(EXEC_VL_OUTUBRO) AS EXEC_VL_OUTUBRO,
    SUM(EXEC_VL_NOVEMBRO) AS EXEC_VL_NOVEMBRO,
    SUM(EXEC_VL_DEZEMBRO) AS EXEC_VL_DEZEMBRO,
    SUM(EXECUCOES.VR_EXECUTADO) AS VR_EXEC_TOTAL,
    SUM(EXECUCOES.VR_EXECUTADO) AS VALOR
FROM
    (
    SELECT
        BASE.EXEC_NR_DESPESA,
        -- BASE.NOEM_CD_NOTA_EMPENHO,
        -- EXECUCAO.VR_EXECUTADO,
        EXEC_VL_JANEIRO,
        EXEC_VL_FEVEREIRO,
        EXEC_VL_MARCO,
        EXEC_VL_ABRIL,
        EXEC_VL_MAIO,
        EXEC_VL_JUNHO,
        EXEC_VL_JULHO,
        EXEC_VL_AGOSTO,
        EXEC_VL_SETEMBRO,
        EXEC_VL_OUTUBRO,
        EXEC_VL_NOVEMBRO,
        EXEC_VL_DEZEMBRO,
        NVL(EXECUCAO.VR_EXECUTADO, 0) AS VR_EXECUTADO
    FROM
        (
        SELECT
            AA2.NOEM_NR_DESPESA         AS EXEC_NR_DESPESA,
            AA2.NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO    AA2
        WHERE
            AA2.NOEM_CD_NE_REFERENCIA IS NULL

        UNION ALL

        /* Empenhos de reforço ou anulação */
        SELECT
            BB2.NOEM_NR_DESPESA         AS EXEC_NR_DESPESA,
            AA3.NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO    AA3
        Left JOIN
            CEO_TB_NOEM_NOTA_EMPENHO    BB2 ON
                BB2.NOEM_CD_NOTA_EMPENHO = AA3.NOEM_CD_NE_REFERENCIA
        WHERE
            AA3.NOEM_CD_NE_REFERENCIA IS NOT NULL
        ) BASE
    Left JOIN
        (
        SELECT

            EXEC_CD_NOTA_EMPENHO,
            NVL(EXEC_VL_JANEIRO, 0) AS EXEC_VL_JANEIRO,
            NVL(EXEC_VL_FEVEREIRO, 0) AS EXEC_VL_FEVEREIRO,
            NVL(EXEC_VL_MARCO, 0) AS EXEC_VL_MARCO,
            NVL(EXEC_VL_ABRIL, 0) AS EXEC_VL_ABRIL,
            NVL(EXEC_VL_MAIO, 0) AS EXEC_VL_MAIO,
            NVL(EXEC_VL_JUNHO, 0) AS EXEC_VL_JUNHO,
            NVL(EXEC_VL_JULHO, 0) AS EXEC_VL_JULHO,
            NVL(EXEC_VL_AGOSTO, 0) AS EXEC_VL_AGOSTO,
            NVL(EXEC_VL_SETEMBRO, 0) AS EXEC_VL_SETEMBRO,
            NVL(EXEC_VL_OUTUBRO, 0) AS EXEC_VL_OUTUBRO,
            NVL(EXEC_VL_NOVEMBRO, 0) AS EXEC_VL_NOVEMBRO,
            NVL(EXEC_VL_DEZEMBRO, 0) AS EXEC_VL_DEZEMBRO,
            NVL(EXEC_VL_JANEIRO, 0) +
            NVL(EXEC_VL_FEVEREIRO, 0) +
            NVL(EXEC_VL_MARCO, 0) +
            NVL(EXEC_VL_ABRIL, 0) +
            NVL(EXEC_VL_MAIO, 0) +
            NVL(EXEC_VL_JUNHO, 0) +
            NVL(EXEC_VL_JULHO, 0) +
            NVL(EXEC_VL_AGOSTO, 0) +
            NVL(EXEC_VL_SETEMBRO, 0) +
            NVL(EXEC_VL_OUTUBRO, 0) +
            NVL(EXEC_VL_NOVEMBRO, 0) +
            NVL(EXEC_VL_DEZEMBRO, 0) AS VR_EXECUTADO
        FROM
            CEO_TB_EXEC_EXECUCAO_NE
        ) EXECUCAO ON
            EXECUCAO.EXEC_CD_NOTA_EMPENHO = BASE.NOEM_CD_NOTA_EMPENHO
    WHERE
        EXECUCAO.VR_EXECUTADO IS NOT NULL AND
        EXEC_NR_DESPESA IS NOT NULL
    ) EXECUCOES
$whereDespesa
GROUP BY
    EXECUCOES.EXEC_NR_DESPESA
                ";

        return $sql;
    }

    /**
     * Método responsável pela exclusão lógica
     *
     * @name exclusaoLogica
     * @author Victor Eduardo Barreto
     * @param $chaves Identificador dos dados
     * @date Jul 9, 2015
     * @version 1.0
     */
    public function exclusaoLogica($chaves) {

        $notas = "'" . implode("'" . ',' . "'", $chaves) . "'";

        $sessao = new Zend_Session_Namespace('userNs');

        // Exclui um ou mais registros
        $sql = "
        UPDATE CEO_TB_NOEM_NOTA_EMPENHO
        SET NOEM_CD_MATRICULA_EXCLUSAO = '$sessao->matricula',
  NOEM_DH_EXCLUSAO_LOGICA = SYSDATE
        WHERE NOEM_CD_NOTA_EMPENHO IN ($notas) AND NOEM_DH_EXCLUSAO_LOGICA IS NULL
  ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $banco->query($sql);
    }

    /**
     * Método responsável pela exclusão lógica
     *
     * @name exclusaoLogica
     * @author Gesley Rodrigues
     * @param $chaves Identificador dos dados
     * @date Jul 9, 2015
     * @version 1.0
     */
    public function exclusaoFisica($chaves) {

        $notas = "'" . implode("'" . ',' . "'", $chaves) . "'";

        $sessao = new Zend_Session_Namespace('userNs');

        // Exclui um ou mais registros
        $sql = "
        DELETE FROM CEO_TB_NOEM_NOTA_EMPENHO
        WHERE NOEM_CD_NOTA_EMPENHO IN ($notas)
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $banco->query($sql);
    }

    /**
     * Método responsável pela exclusão lógica
     *
     * @name exclusaoLogica
     * @author Gesley Rodrigues
     * @param $chaves Identificador dos dados
     * @date Jul 9, 2015
     * @version 1.0
     */
    public function exclusaoExecucao($chaves) {

        $sessao = new Zend_Session_Namespace('userNs');

        $notas = "'" . implode("'" . ',' . "'", $chaves) . "'";

        // Exclui um ou mais registros
        $sql = "
        DELETE FROM CEO_TB_EXEC_EXECUCAO_NE
        WHERE EXEC_CD_NOTA_EMPENHO IN ($notas)
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $banco->query($sql);
    }

}
