<?php

/**
 * @category    TRF1
 * @package        Trf1_Orcamento_Negocio_Rdo
 * @copyright    Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author        Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license        FREE, keep original copyrights
 * @version        controlada pelo SVN
 * @tutorial    Tutorial abaixo
 *
 * TRF1, Classe negocial sobre Orçamento - Requisições (RDO )
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
class Trf1_Orcamento_Negocio_Rdo {

    /**
     * Model da Unidade Orçamentária
     */
    protected $_dados = null;

    /**
     * Classe construtora
     *
     * @param
     *            none
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct() {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbReqvRequVariacao();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return array primária ou composta
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela() {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return array primária ou composta
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria() {
        return $this->_dados->chavePrimaria();
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param integer $ano
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaCombo($ano) {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        $cacheId = $cache->retornaID_Combo('rdo');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            // Não existindo o cache, busca do banco
            $sql = "
SELECT
    REQV_NR_DESPESA,
    REQV_DH_VARIACAO
FROM
    CEO_TB_REQV_REQU_VARIACAO
WHERE
    REQV_DH_EXCLUSAO_LOGICA IS NULL
                    ";

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchPairs($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Apresenta opções de variação do campo REQV_IC_TP_VARIACAO
     *
     * @param
     *            none
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function getVariacaoCombo() {
        $dados[0] = 'Ajuste (+)';
        $dados[1] = 'Cancelamento (-)';

        return $dados;
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param
     *            none
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaListagem($despesa = Null, $data = Null) {

        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        // $cacheId = $cache->gerarID_Listagem('rdo', array(
        //     '$bDadosSensiveis' => true,
        // ));
        // $dados = $cache->lerCache($cacheId);

        // if ($dados === false) {
        // Não existindo o cache, busca do banco
        // Valida parâmetros
        $condicaoDespesa = "";
        if ($despesa) {
            $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $despesa ) ";
        }

        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        $sql = "
                SELECT DISTINCT
                NOEM.NOEM_CD_NOTA_EMPENHO,
                DESP.DESP_AA_DESPESA AS REQV_ANO,

                CASE
                    WHEN DESP.DESP_AA_DESPESA = " . date('Y') . " THEN 1
                    ELSE 2
                END AS EXERCICIO,

                REQV.REQV_NR_DESPESA,
                DESP.DESP_CD_UG AS REQV_CD_UG,

                DESP.DESP_CD_FONTE,
                DESP.DESP_CD_PT_RESUMIDO,
                UNOR.UNOR_CD_UNID_ORCAMENTARIA,
                P.PTRS_SG_PT_RESUMIDO,
                DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
                TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "')    AS REQV_DH_VARIACAO,
                REQV.REQV_DS_DETALHAMENTO,
                REQV_NR_PROCESSO_ADM,

                -- Verificação para determinar qual o tipo do processo, se houver
                CASE
                    WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN 'Físico'
                    WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN 'Digital'
                END AS REQV_TIPO_PROCESSO,

                -- Verificação para determinar qual número do processo, se houver

                CASE
                    WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN REQV_CD_PROC_FISICO
                    WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE                 DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
                END AS REQV_PROCESSO,

                -- Verificação para determinar o tipo de variação
                CASE REQV.REQV_IC_TP_VARIACAO
                        WHEN 0 THEN '(+) Ajuste'
                        WHEN 1 THEN '(-) Cancelamento'
                END AS REQV_IC_TP_VARIACAO,

                -- Verificação para determinar a polaridade do sinal do valor; se positivo ou negativo
                CASE REQV.REQV_IC_TP_VARIACAO
                        WHEN 0 THEN NVL(REQV.REQV_VL_VARIACAO, 0)
                        WHEN 1 THEN NVL(REQV.REQV_VL_VARIACAO, 0) * (-1)
                END AS VL_VARIACAO,

                -- Verificação para verificar se existe nota de emprenho vinculada ao RDO.
                --   DECODE(NOEM.NOEM_NR_PROCESSO, NULL, 'Nao','Sim') AS FL_NOTA_EMPENHO
                
                NOEM.NOEM_VL_NE_ACERTADO

                FROM CEO_TB_REQV_REQU_VARIACAO REQV

                Left JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA

                Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO P ON P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO

                Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA

                Left JOIN CEO_TB_RESP_RESPONSAVEL RESP ON RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL

                Left JOIN RH_CENTRAL_LOTACAO RHCL ON RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO
                AND RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO

                /* NR PROCESSO */
                Left JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM ON NOEM.NOEM_NR_PROCESSO = REQV.REQV_NR_PROCESSO_ADM AND NOEM_NR_DESPESA = REQV.REQV_NR_DESPESA

                WHERE REQV.REQV_DH_EXCLUSAO_LOGICA IS NULL

        $condicaoDespesa
	$condicaoResponsaveis

        ORDER BY EXERCICIO";

        $banco = Zend_Db_Table::getDefaultAdapter();
        $dados = $banco->fetchAll($sql);

        // Cria o cache
        $cache->criarCache($dados, $cacheId);
        // }

        return $dados;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param int $uo
     *            para busca do registro
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistro($despesa, $data) {
        $sql = "
SELECT
    REQV_NR_DESPESA,
    TO_CHAR(REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "')         AS REQV_DH_VARIACAO,
    REQV_DS_DETALHAMENTO,
    REQV_NR_PROCESSO_ADM,
    -- Verificação para determinar qual o tipo do processo, se houver
    /*
     CASE
        WHEN NVL(REQV_CD_PROC_FISICO, 0) <> 0 THEN 1
        WHEN NVL(REQV_CD_PROC_DIGITAL, 0) <> 0 THEN 0
    END AS TIPO_PROCESSO,
    */
    -- Verificação para determinar qual número do processo, se houver
    /*
    CASE
        WHEN NVL(REQV_CD_PROC_FISICO, 0) <> 0 THEN REQV_CD_PROC_FISICO
        WHEN NVL(REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
    END AS REQV_PROCESSO,
    */
    -- Verificação para buscar a descrição do processo, se houver
    /*
    CASE
        WHEN NVL(REQV_CD_PROC_FISICO, 0) <> 0 THEN (SELECT PAPA_COD_PROC || ' - ' || PAPA_DESC_ASSUNTO || ' - ' || PAPA_TEXTO AS DESC_PROC_FISICO FROM PA_PROCESSO_ADM_TRF1 WHERE PAPA_COD_PROC = REQV_CD_PROC_FISICO AND PAPA_CD_SECSUBSEC = (SELECT UNGE.UNGE_CD_SECSUBSEC FROM CEO_TB_REQV_REQU_VARIACAO REQV Left JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA Left JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON UNGE.UNGE_CD_UG = DESP.DESP_CD_UG WHERE REQV.REQV_NR_DESPESA = $despesa AND TO_CHAR(REQV.REQV_DH_VARIACAO, 'YYYY-MM-DD HH24:MI:SS')  = '$data'))
        WHEN NVL(REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO || ' - ' || DOCM_DS_ASSUNTO_DOC AS DESC_PROC_DIGITAL FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
    END AS REQV_DS_PROCESSO,
    */
    -- Verificação para determinar o tipo de variação
    REQV_IC_TP_VARIACAO,
    REQV_VL_VARIACAO
FROM
    CEO_TB_REQV_REQU_VARIACAO
WHERE
    REQV_DH_EXCLUSAO_LOGICA     IS NULL         AND
    REQV_NR_DESPESA             = $despesa      AND
    TO_CHAR(REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "')     = '$data'
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchRow($sql);
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param
     *            $despesa
     * @param
     *            $dataHora
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistroNomeAmigavel($despesa, $dataHora) {
        $sql = "
            SELECT
            REQV.REQV_NR_DESPESA || ' - ' || EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || DESP.DESP_DS_ADICIONAL AS \"Despesa\",
            DESP.DESP_AA_DESPESA AS \"Ano\",
            DESP.DESP_CD_UG || ' - ' || UNGE.UNGE_DS_UG AS \"UG\",
            DESP.DESP_CD_FONTE AS \"Fonte\",
            DESP.DESP_CD_PT_RESUMIDO AS \"Programa de trabalho resumido\",
            DESP.DESP_CD_ELEMENTO_DESPESA_SUB AS \"Natureza da despesa\",
            TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "') AS \"Data e Hora\",
            REQV.REQV_DS_DETALHAMENTO AS \"Descrição\",
            REQV_NR_PROCESSO_ADM AS \"Processo\",
            NOEM.NOEM_CD_NOTA_EMPENHO AS \"Nota de Empenho\",
            SUM(NOEM.NOEM_VL_NE) AS \"Valor Empenhado\",
            SUM(NOEM.NOEM_VL_NE) - NVL(REQV.REQV_VL_VARIACAO, 0) AS \"Valor Saldo\",

            -- Verificação para determinar qual número do processo, se houver
	/*
	CASE
		WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN REQV.REQV_CD_PROC_FISICO
		WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
	END AS \"Código Processo\",

        */
	-- Verificação para determinar qual o tipo do processo, se houver
	/*
	CASE
        WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN 'Físico'
        WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN 'Digital'
    END                                                                                                     AS \"Tipo de Processo\",
    */
	-- Verificação para buscar a descrição do processo, se houver
	/*
	CASE
		WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN (SELECT PAPA_COD_PROC || ' - ' || PAPA_DESC_ASSUNTO || ' - ' || PAPA_TEXTO AS DESC_PROC_FISICO FROM PA_PROCESSO_ADM_TRF1 WHERE PAPA_COD_PROC = REQV_CD_PROC_FISICO AND PAPA_CD_SECSUBSEC = (SELECT UNGE.UNGE_CD_SECSUBSEC FROM CEO_TB_REQV_REQU_VARIACAO REQV Left JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA Left JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON UNGE.UNGE_CD_UG = DESP.DESP_CD_UG WHERE REQV.REQV_NR_DESPESA = $despesa AND TO_CHAR(REQV.REQV_DH_VARIACAO, 'YYYY-MM-DD HH24:MI:SS')  = '$dataHora'))
		WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO || ' - ' || DOCM_DS_ASSUNTO_DOC AS DESC_PROC_DIGITAL FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
	END AS \"Processo\",

        */
	-- Verificação para determinar o tipo de variação
	CASE REQV.REQV_IC_TP_VARIACAO
		WHEN 0 THEN '(+) Ajuste'
		WHEN 1 THEN '(-) Cancelamento'
	END AS \"Variação\",
	NVL(REQV.REQV_VL_VARIACAO, 0)                                                                           AS \"Valor\"
        FROM
            CEO_TB_REQV_REQU_VARIACAO				REQV
            Left JOIN
            CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= REQV.REQV_NR_DESPESA
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
		EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB	= DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON UNGE.UNGE_CD_UG = DESP.DESP_CD_UG

         /* NR PROCESSO */
         Left JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM ON NOEM.NOEM_NR_PROCESSO = REQV.REQV_NR_PROCESSO_ADM AND NOEM_NR_DESPESA = REQV.REQV_NR_DESPESA

        WHERE REQV.REQV_DH_EXCLUSAO_LOGICA IS NULL AND REQV.REQV_NR_DESPESA = $despesa AND
	TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_INVERTIDA . "') = '$dataHora'

        GROUP BY REQV.REQV_NR_DESPESA || ' - ' || EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
            DESP.DESP_DS_ADICIONAL,
            DESP.DESP_AA_DESPESA,
            NOEM.NOEM_CD_NOTA_EMPENHO,
            DESP.DESP_CD_UG || ' - ' || UNGE.UNGE_DS_UG,
            DESP.DESP_CD_FONTE,
            DESP.DESP_CD_PT_RESUMIDO,
            DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
            TO_CHAR(REQV.REQV_DH_VARIACAO, 'DD-MM-YYYY HH24:MI:SS'),
            REQV.REQV_DS_DETALHAMENTO,
            REQV_NR_PROCESSO_ADM,
            REQV.REQV_IC_TP_VARIACAO,
            NVL(REQV.REQV_VL_VARIACAO, 0)

				";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    public function alteraRequisicao($dados, $rdo) {

        $despesa = $dados['REQV_NR_DESPESA'];
        $descricao = str_replace("'", "´", $dados['REQV_DS_DETALHAMENTO']);
        // $processo = ($dados ['REQV_DS_PROCESSO']) ? $dados ['REQV_DS_PROCESSO'] : 'NULL';
        $variacao = $dados['REQV_IC_TP_VARIACAO'];

        $formatoBanco = new Trf1_Orcamento_Valor();
        $valor = $formatoBanco->retornaValorParaBancoRod($dados['REQV_VL_VARIACAO']);

        /*
         * Testes Zend_Debug::dump($despesa); Zend_Debug::dump($descricao); Zend_Debug::dump($processo); Zend_Debug::dump($variacao); Zend_Debug::dump($valor); Zend_Debug::dump($dados); exit;
         */

        if (!is_null($dados["TIPO_PROCESSO"])) {
            if ($dados["TIPO_PROCESSO"] == 0) {
                $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                if ($processo != 'NULL') {
                    // $numero = $mapperDocumento->getDocumentoIdByNrDoc ( $dados ["REQV_DS_PROCESSO"] );
                    $digital = $numero[0]["DOCM_ID_DOCUMENTO"];
                } else {
                    $digital = 'NULL';
                }

                $sql = "
UPDATE
    CEO_TB_REQV_REQU_VARIACAO
SET
	REQV_NR_DESPESA			= $despesa,
	REQV_DS_DETALHAMENTO	= '$descricao',
	REQV_CD_PROC_DIGITAL	= $digital,
	REQV_CD_PROC_FISICO		= NULL,
	REQV_IC_TP_VARIACAO		= $variacao,
	REQV_VL_VARIACAO		= TO_NUMBER($valor),
	REQV_NR_PROCESSO_ADM    = '" . $dados["REQV_NR_PROCESSO_ADM"] . "'
WHERE
	REQV_DH_EXCLUSAO_LOGICA IS NULL AND
	TO_CHAR(REQV_NR_DESPESA||'-'||TO_CHAR(REQV_DH_VARIACAO, 'DD-MM-YYYY HH24:MI:SS')) = '$rdo' ";
            } else {
                if ($processo != 'NULL') {
                    // $fisico = $dados ["REQV_DS_PROCESSO"];
                } else {
                    $fisico = "NULL";
                }

                $sql = "
UPDATE
    CEO_TB_REQV_REQU_VARIACAO
SET
	REQV_NR_DESPESA			= $despesa,
	REQV_DS_DETALHAMENTO	= '$descricao',
	REQV_CD_PROC_DIGITAL	= NULL,
	REQV_CD_PROC_FISICO		= $fisico,
	REQV_IC_TP_VARIACAO		= $variacao,
	REQV_VL_VARIACAO		= TO_NUMBER($valor),
	REQV_NR_PROCESSO_ADM    = '" . $dados["REQV_NR_PROCESSO_ADM"] . "'
WHERE
	REQV_DH_EXCLUSAO_LOGICA IS NULL AND
	TO_CHAR(REQV_NR_DESPESA||'-'||TO_CHAR(REQV_DH_VARIACAO, 'DD-MM-YYYY HH24:MI:SS')) = '$rdo' ";
            }
        } else {
            $sql = "
UPDATE
    CEO_TB_REQV_REQU_VARIACAO
SET
	REQV_NR_DESPESA			= $despesa,
	REQV_DS_DETALHAMENTO	= '$descricao',
	REQV_CD_PROC_DIGITAL    = NULL,
	REQV_CD_PROC_FISICO    =  NULL,
	REQV_IC_TP_VARIACAO		= $variacao,
	REQV_VL_VARIACAO		= TO_NUMBER($valor),
	REQV_NR_PROCESSO_ADM    = " . $dados["REQV_NR_PROCESSO_ADM"] . "
WHERE
	REQV_DH_EXCLUSAO_LOGICA IS NULL AND
	TO_CHAR(REQV_NR_DESPESA||'-'||TO_CHAR(REQV_DH_VARIACAO, 'DD-MM-YYYY HH24:MI:SS')) = '$rdo' ";
        }

        $banco = Zend_Db_Table::getDefaultAdapter();
        $banco->query($sql);

        return true;
    }

    /**
     * Retorna requisições sem nota de empenho
     *
     * @return array
     * @author Gesley Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaRequisicoesSemNotaListagem($despesa = Null, $data = Null) {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        $cacheId = $cache->gerarID_Listagem('rdo', array(
            '$bDadosSensiveis' => true,
        ));
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            // Não existindo o cache, busca do banco
            // Valida parâmetros
            $condicaoDespesa = "";
            if ($despesa) {
                $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $despesa ) ";
            }

            $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

            $sql = "
	   SELECT
	   DESP.DESP_AA_DESPESA AS REQV_ANO,
       REQV.REQV_NR_DESPESA,
       DESP.DESP_CD_UG AS REQV_CD_UG,
       DESP.DESP_CD_FONTE,
       DESP.DESP_CD_PT_RESUMIDO,
       UNOR.UNOR_CD_UNID_ORCAMENTARIA,
       DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
       TO_CHAR(REQV.REQV_DH_VARIACAO, 'YYYY-MM-DD HH24:MI:SS') AS REQV_DH_VARIACAO,
       REQV.REQV_DS_DETALHAMENTO,
       REQV_NR_PROCESSO_ADM,
       -- Verificação para determinar qual o tipo do processo, se houver
/*
       CASE
           WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN 'Físico'
           WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN 'Digital'
       END AS REQV_TIPO_PROCESSO,
*/
       -- Verificação para determinar qual nÃºmero do processo, se houver
/*
       CASE
           WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN REQV_CD_PROC_FISICO
           WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN
                  (SELECT DOCM_NR_DOCUMENTO
                   FROM SAD_TB_DOCM_DOCUMENTO
                   WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
       END AS REQV_PROCESSO,
*/
       -- Verificação para determinar o tipo de variaÃ§Ã£o

       CASE REQV.REQV_IC_TP_VARIACAO
           WHEN 0 THEN '(+) Ajuste'
           WHEN 1 THEN '(-) Cancelamento'
       END AS REQV_IC_TP_VARIACAO,
       -- Verificação para determinar a polaridade do sinal do valor; se positivo ou negativo

       CASE REQV.REQV_IC_TP_VARIACAO
           WHEN 0 THEN NVL(REQV.REQV_VL_VARIACAO, 0)
           WHEN 1 THEN NVL(REQV.REQV_VL_VARIACAO, 0) * (-1)
       END AS VL_VARIACAO
        FROM CEO_TB_REQV_REQU_VARIACAO REQV
        LEFT JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA
        LEFT JOIN CEO_TB_RESP_RESPONSAVEL RESP ON RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
        LEFT JOIN RH_CENTRAL_LOTACAO RHCL ON RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO
        AND RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
        LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO P ON P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
        LEFT JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA
        WHERE REQV.REQV_DH_EXCLUSAO_LOGICA IS NULL
          AND REQV_NR_DESPESA NOT IN
            (SELECT DISTINCT NOEM_NR_DESPESA
             FROM CEO_TB_NOEM_NOTA_EMPENHO
             WHERE NOEM_NR_DESPESA IS NOT NULL)

        $condicaoDespesa
        $condicaoResponsaveis
        ";

            $banco = Zend_Db_Table::getDefaultAdapter();
            $dados = $banco->fetchAll($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Retorna requisições com nota de empenho
     *
     * @return array
     * @author Gesley Rodrigues [rodrigues.gesley@gmail.com]
     */
    public function retornaRequisicoesComNotaListagem($despesa = Null) {

        $condicaoDespesa = "";
        if ($despesa) {
            $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $despesa ) ";
        }

        // Filtro por ug
        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        $sql = "
			SELECT
				DESP.DESP_AA_DESPESA AS REQV_ANO,
				REQV.REQV_NR_DESPESA,
				DESP.DESP_CD_UG AS REQV_CD_UG,
				DESP.DESP_CD_FONTE,
				DESP.DESP_CD_PT_RESUMIDO,
				UNOR.UNOR_CD_UNID_ORCAMENTARIA,
				DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
				TO_CHAR(REQV.REQV_DH_VARIACAO, 'YYYY-MM-DD HH24:MI:SS') AS REQV_DH_VARIACAO,
				REQV.REQV_DS_DETALHAMENTO,
				REQV_NR_PROCESSO_ADM,

				CASE REQV.REQV_IC_TP_VARIACAO
				WHEN 0 THEN '(+) Ajuste'
				WHEN 1 THEN '(-) Cancelamento'
				END AS REQV_IC_TP_VARIACAO,
				CASE REQV.REQV_IC_TP_VARIACAO
				WHEN 0 THEN NVL(REQV.REQV_VL_VARIACAO, 0)
				WHEN 1 THEN NVL(REQV.REQV_VL_VARIACAO, 0) * (-1)
				END AS VL_VARIACAO

			FROM CEO_TB_REQV_REQU_VARIACAO REQV
				LEFT JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA
				LEFT JOIN CEO_TB_RESP_RESPONSAVEL RESP ON RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
				LEFT JOIN RH_CENTRAL_LOTACAO RHCL ON RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO
				AND RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
				LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO P ON P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
				LEFT JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA
				WHERE REQV.REQV_DH_EXCLUSAO_LOGICA IS NULL
				AND REQV_NR_DESPESA IN
				(
					SELECT DISTINCT NOEM_NR_DESPESA
				 FROM CEO_TB_NOEM_NOTA_EMPENHO
				 WHERE NOEM_NR_DESPESA IS NOT NULL
				 )

		$condicaoDespesa
		$condicaoResponsaveis
		";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
     *
     * @param array $chaves
     *            chaves primárias para busca de um ou mais registros
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaVariosRegistros($chaves) {
        // Concatena múltiplas chaves
        $registro = explode(',', $chaves);
        $registro = implode("', '", $registro);

        $sql = "
SELECT
    REQV_NR_DESPESA,
    REQV_DH_VARIACAO,
    REQV.REQV_NR_DESPESA || ' - ' || EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || DESP.DESP_DS_ADICIONAL   AS \"Despesa\",
    DESP.DESP_AA_DESPESA AS \"Ano\",
    DESP.DESP_CD_UG || ' - ' || UNGE.UNGE_DS_UG                                                             AS \"UG\",
    DESP.DESP_CD_FONTE                                                                                      AS \"Fonte\",
    DESP.DESP_CD_PT_RESUMIDO                                                                                AS \"Programa de trabalho resumido\",
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB                                                                       AS \"Natureza da despesa\",
    TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "')        AS \"Data e Hora\",
    REQV.REQV_DS_DETALHAMENTO                                                                               AS \"Descrição\",
    -- Verificação para determinar qual número do processo, se houver
    CASE
        WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN REQV.REQV_CD_PROC_FISICO
        WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (SELECT DOCM_NR_DOCUMENTO FROM SAD_TB_DOCM_DOCUMENTO WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
    END                                                                                                     AS \"Código Processo\",
    -- Verificação para determinar qual o tipo do processo, se houver
    CASE
        WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN 'Físico'
        WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN 'Digital'
    END AS \"Tipo de Processo\",

    /*

    -- Verificação para buscar a descrição do processo, se houver
    CASE WHEN NVL(REQV.REQV_CD_PROC_FISICO, 0) <> 0 THEN

        (SELECT PAPA_COD_PROC || ' - ' || PAPA_DESC_ASSUNTO || ' - ' || PAPA_TEXTO AS DESC_PROC_FISICO
            FROM PA_PROCESSO_ADM_TRF1
            WHERE PAPA_COD_PROC = REQV_CD_PROC_FISICO AND PAPA_CD_SECSUBSEC =
                (SELECT UNGE.UNGE_CD_SECSUBSEC
                    FROM CEO_TB_REQV_REQU_VARIACAO REQV
                    Left JOIN CEO_TB_DESP_DESPESA DESP ON DESP.DESP_NR_DESPESA = REQV.REQV_NR_DESPESA
                    Left JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON UNGE.UNGE_CD_UG = DESP.DESP_CD_UG
                    WHERE TO_CHAR(REQV.REQV_NR_DESPESA) || '-' || TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "') IN ('$registro')
                ))

                WHEN NVL(REQV.REQV_CD_PROC_DIGITAL, 0) <> 0 THEN (
                    SELECT DOCM_NR_DOCUMENTO || ' - ' || DOCM_DS_ASSUNTO_DOC AS DESC_PROC_DIGITAL
                        FROM SAD_TB_DOCM_DOCUMENTO
                        WHERE DOCM_ID_DOCUMENTO = REQV_CD_PROC_DIGITAL)
    END AS \"Processo\",
     */

    -- Verificação para determinar o tipo de variação
    CASE REQV.REQV_IC_TP_VARIACAO
        WHEN 0 THEN '(+) Ajuste'
        WHEN 1 THEN '(-) Cancelamento'
    END                                                                                                     AS \"Variação\",
    --TO_CHAR(NVL(REQV.REQV_VL_VARIACAO, 0), '" . Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')               AS \"Valor\"
    NVL(REQV.REQV_VL_VARIACAO, 0)                                                                           AS \"Valor\"
FROM
    CEO_TB_REQV_REQU_VARIACAO               REQV
Left JOIN
    CEO_TB_DESP_DESPESA                     DESP ON
        DESP.DESP_NR_DESPESA                = REQV.REQV_NR_DESPESA
Left JOIN
    CEO_TB_EDSB_ELEMENTO_SUB_DESP           EDSB ON
        EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB   = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
    CEO_TB_UNGE_UNIDADE_GESTORA             UNGE ON
        UNGE.UNGE_CD_UG                     = DESP.DESP_CD_UG
WHERE
    REQV.REQV_DH_EXCLUSAO_LOGICA                                                                            IS NULL         AND
    TO_CHAR(REQV_NR_DESPESA) || '-' || TO_CHAR(REQV.REQV_DH_VARIACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_TRACO . "') IN ('$registro')
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    /**
     * Realiza a exclusão lógica de uma unidade gestora
     *
     * @param array $chaves
     *            chaves primárias para exclusão de um ou mais registros
     * @return none
     * @author Dayane Freire / Robson Pereira
     */
    public function exclusaoLogica($chaves) {
        $registro = explode(',', $chaves);
        $registro = implode("', '", $registro);

        $sessao = new Zend_Session_Namespace('userNs');

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_REQV_REQU_VARIACAO
SET
    REQV_CD_MATRICULA_EXCLUSAO              = '$sessao->matricula',
    REQV_DH_EXCLUSAO_LOGICA                 = SYSDATE
WHERE
    TO_CHAR(REQV_NR_DESPESA||'-'||TO_CHAR(REQV_DH_VARIACAO, 'DD-MM-YYYY HH24:MI:SS')) IN ('$registro')
AND
    REQV_DH_EXCLUSAO_LOGICA                 IS NULL
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $banco->query($sql);
    }

    public function processoFisico($parametro, $despesa) {
        $sql = "SELECT PROC.PAPA_COD_PROC || ' - ' ||SUBSTR(PROC.PAPA_DESC_ASSUNTO,0,100)|| '...'  AS LABEL
                  FROM PA_PROCESSO_ADM_TRF1 PROC,
                       CEO_TB_DESP_DESPESA  DESP,
                       CEO_TB_UNGE_UNIDADE_GESTORA UNGE
                 WHERE DESP.DESP_NR_DESPESA = $despesa
                   AND UNGE.UNGE_CD_UG = DESP.DESP_CD_UG
                   AND PROC.PAPA_CD_SECSUBSEC = UNGE.UNGE_CD_SECSUBSEC
                   AND (PROC.PAPA_COD_PROC  LIKE  UPPER('%$parametro%')
                   OR  PROC.PAPA_DESC_ASSUNTO LIKE  UPPER('%$parametro%')
                   OR  PROC.PAPA_TEXTO LIKE  UPPER('%$parametro%'))";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function validaProcessoFisico($parametro) {
        $sql = "SELECT PROC.PAPA_COD_PROC
                  FROM PA_PROCESSO_ADM_TRF1 PROC
                 WHERE PROC.PAPA_COD_PROC = '$parametro'";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function processoDigital($parametro) {
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        $lotacao = $userNs->codlotacao;

        $sql = "SELECT DOCM_NR_DOCUMENTO || ' - ' || SUBSTR(DOCM_DS_ASSUNTO_DOC,0,100)|| '...' AS LABEL,
                                   DOCM_NR_DOCUMENTO AS VALUE
                            FROM SAD_TB_DOCM_DOCUMENTO
                             WHERE DOCM_ID_TIPO_DOC = 152
                               AND DOCM_NR_DOCUMENTO LIKE '%$parametro%'
                               OR DOCM_DS_ASSUNTO_DOC LIKE '%$parametro%'
                               OR DOCM_DS_PALAVRA_CHAVE LIKE '%$parametro%'";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function validaProcessoDigital($parametro) {
        $sql = "SELECT DOCM_NR_DOCUMENTO
                            FROM SAD_TB_DOCM_DOCUMENTO
                             WHERE
                               -- DOCM_ID_TIPO_DOC = 152 AND
                               DOCM_NR_DOCUMENTO = '$parametro'";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

}
