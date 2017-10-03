<?php

/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Nc
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 *
 * TRF1, Classe negocial sobre Orçamento - Notas de crédito
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
class Trf1_Orcamento_Negocio_Nc {

    /**
     * Model das Notas de Crédito
     */
    protected $_dados = null;

    /**
     * Classe construtora
     *
     * @param
     *        none
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct () {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbNocrNotaCredito ();
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe
     * negocial
     *
     * @return array primária ou composta
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela () {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe
     * negocial
     *
     * @return array primária ou composta
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria () {
        return $this->_dados->chavePrimaria();
    }

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @param
     *        none
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaCombo () {
        return false;
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param
     *        none
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaListagem () {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->gerarID_Listagem('nc');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            // Não existindo o cache, busca do banco
            $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
            $ug = $sessaoOrcamento->ug;

            // Verifica possível restrição de registros
            $condicaoUg = '';

            if ($ug != 'todas') {

                $condicaoUg = "
                LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON
                    UNGE.UNGE_CD_UG = NOCR.NOCR_CD_UG_FAVORECIDO
                    OR UNGE.UNGE_CD_UG = NOCR.NOCR_CD_UG_OPERADOR
                INNER JOIN CEO_TB_DESP_DESPESA DESP
                    ON NOCR.NOCR_NR_DESPESA = DESP_NR_DESPESA
                    OR NOCR.NOCR_NR_DESPESA_RESERVA = DESP_NR_DESPESA

                LEFT JOIN CEO_TB_RESP_RESPONSAVEL RESP ON
                    RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
                --LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA  UNGE ON
                    --UNGE.UNGE_CD_UG = RESP.UNGE_CD_UG
                LEFT JOIN RH_CENTRAL_LOTACAO RHCL ON
                    RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND
                    RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO

                WHERE UNGE.UNGE_SG_SECAO = '$ug'";

                // Verifica tratamento especial para perfil: secretaria_reserva
                $sessao = new Orcamento_Business_Sessao ();
                $perfilFull = $sessao->retornaPerfil();
                $perfil = $perfilFull['perfil'];
                $reserva = Orcamento_Business_Dados::PERMISSAO_SECRETARIA_RESERVA;
                if ($perfil == $reserva) {
                    $sessao = new Orcamento_Business_Sessao ();
                    $perfilFull = $sessao->retornaPerfil();
                    $lotacao = $perfilFull["responsavel"];

                    $condicaoUg .= " AND NOCR.NOCR_CD_UG_FAVORECIDO NOT IN ( ";
                    $condicaoUg .= " 90049, 110407 ";
                    $condicaoUg .= " ) ";

                    $condicaoUg .= " AND RH_SIGLAS_FAMILIA_CENTR_LOTA ( ";
                    $condicaoUg .= " RHCL.LOTA_SIGLA_SECAO, ";
                    $condicaoUg .= " RHCL.LOTA_COD_LOTACAO ";
                    $condicaoUg .= " ) = '$lotacao' ";
                }
            }

            $sql = "
                SELECT
                    SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) AS NOCR_ANO,
                    CASE WHEN
                        SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) = '" . date('Y') . "' THEN 1
                        ELSE 2
                    END AS EXERCICIO,                    
                    NOCR.NOCR_CD_UG_FAVORECIDO,
                    NOCR_CD_UG_OPERADOR,
                    NOCR.NOCR_CD_NOTA_CREDITO,
                    TO_CHAR(NOCR.NOCR_DT_EMISSAO, '" .
                Trf1_Orcamento_Definicoes::FORMATO_DATA .
                "') AS NOCR_DT_EMISSAO,
                    NOCR.NOCR_DS_OBSERVACAO,
                    NVL(NOCR.NOCR_NR_DESPESA, 0)           AS NOCR_NR_DESPESA,
                    NVL(NOCR.NOCR_NR_DESPESA_RESERVA, 0)   AS NOCR_NR_DESPESA_RESERVA,
                    NVL(NOCR.NOCR_CD_TIPO_NC, 'sem tipo')  AS NOCR_CD_TIPO_NC,
                    NOCR.NOCR_CD_EVENTO,
                    NOCR.NOCR_CD_FONTE,
                    NOCR.NOCR_CD_PT_RESUMIDO,
                    UNOR.UNOR_CD_UNID_ORCAMENTARIA,
                    P.PTRS_SG_PT_RESUMIDO,
                    NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB,
                    NVL(NOCR.NOCR_VL_NC_ACERTADO, 0) as NOCR_VL_NC_ACERTADO,
                    CASE NOCR.NOCR_IC_ACERTADO_MANUALMENTE
                    WHEN 1 THEN 'Sim '
                    ELSE 'Não '
                    END AS NOCR_IC_ACERTADO_MANUALMENTE
                FROM
                    CEO_TB_NOCR_NOTA_CREDITO NOCR

                Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO P ON
                    P.PTRS_CD_PT_RESUMIDO = NOCR.NOCR_CD_PT_RESUMIDO
                Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR
                    ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA

                /* limitar a pegar as ncs que tem ptres iguais evitando as inconsistencias */
                Left JOIN CEO_TB_DESP_DESPESA DESP
                    ON DESP.DESP_NR_DESPESA = NOCR.NOCR_NR_DESPESA

                $condicaoUg
         
                    ";

            $cond = empty($condicaoUg) ? 'WHERE NOCR_DH_EXCLUSAO_LOGICA IS NULL' : 'AND NOCR_DH_EXCLUSAO_LOGICA IS NULL';

            // Sosti - 2016010001108011080160000043 - compara o ptres e a natureza para serem exibidos somentes
            // os iguais a da despesa evitando inconsitencias de notas de credito
            /*$cond .= " AND NOCR.NOCR_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
                       AND substr(NOCR_CD_ELEMENTO_DESPESA_SUB,1,6) = substr(DESP_CD_ELEMENTO_DESPESA_SUB,1,6)
                     ";
            */

            $sql = $sql . $cond . " ORDER BY EXERCICIO ";
            
            $banco = Zend_Db_Table::getDefaultAdapter();
            $dados = $banco->fetchAll($sql);

            // Cria o cache
            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Retorna um único registro sem uso de ALIAS
     *
     * @param int $empenho
     *        para busca do registro
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistro ($empenho = null, $despesa = null) {

        $strEmpenho = "";
        if($empenho) {
            $strEmpenho = " NOCR_CD_NOTA_CREDITO = '$empenho' ";
        }

        $strDespesa = "";
        if($despesa) {
            $strDespesa = " NOCR_NR_DESPESA = $despesa ";
        }


        $sql = "
SELECT
	NOCR_CD_NOTA_CREDITO,
	SUBSTR(NOCR_CD_NOTA_CREDITO, 0, 4)          AS NOCR_ANO,
    NOCR_CD_UG_OPERADOR,
	NOCR_CD_UG_FAVORECIDO,
	NOCR_CD_FONTE,
	NOCR_CD_PT_RESUMIDO,
	NOCR_CD_ELEMENTO_DESPESA_SUB,
	NOCR_CD_TIPO_NC,
	NOCR_NR_DESPESA,
	NOCR_NR_DESPESA_RESERVA,
	TO_CHAR(NOCR_DH_NC,'DD/MM/YYYY')													AS NOCR_DH_NC,
	TO_CHAR(NOCR_DT_EMISSAO,'DD/MM/YYYY')												AS NOCR_DT_EMISSAO,
	TO_CHAR(NOCR_VL_NC,  '" .
            Trf1_Orcamento_Definicoes::FORMATO_NUMERO .
            "')			AS NOCR_VL_NC,
   	TO_CHAR(NOCR_VL_NC_ACERTADO,  '" .
            Trf1_Orcamento_Definicoes::FORMATO_NUMERO . "')	AS NOCR_VL_NC_ACERTADO,
   	NOCR_CD_EVENTO,
	NOCR_DS_OBSERVACAO,
    NOCR_IC_ACERTADO_MANUALMENTE
FROM
	CEO_TB_NOCR_NOTA_CREDITO
WHERE
    $strEmpenho
	$strDespesa
				";
        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna um único registro, com o nome descritivo dos campos (utilizando
     * ALIAS)
     *
     * @param int $empenho
     *        para busca do registro
     * @return array
     * @author Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaRegistroNomeAmigavel ($empenho) {
        $sql = "
SELECT
    NOCR_CD_NOTA_CREDITO                        AS \"Nota de crédito\",
    SUBSTR(NOCR_CD_NOTA_CREDITO, 0, 4)          AS \"Ano\",
    NOCR_CD_UG_OPERADOR                         AS \"UG operador\",
    NOCR_CD_UG_FAVORECIDO                       AS \"UG favorecida\",
    NOCR_NR_DESPESA                             AS \"Despesa\",
    NOCR_NR_DESPESA_RESERVA                     AS \"Despesa (reserva)\",
    NOCR_CD_TIPO_NC                             AS \"Tipo de crédito\",
    TO_CHAR(NOCR_DH_NC,'DD/MM/YYYY')            AS \"Data\",
    TO_CHAR(NOCR_DT_EMISSAO,'DD/MM/YYYY')       AS \"Emissão\",
    NOCR_CD_FONTE                               AS \"Fonte\",
    NOCR_CD_PT_RESUMIDO                         AS \"PTRES\",
    NOCR_CD_ELEMENTO_DESPESA_SUB                AS \"Natureza da despesa\",
    NOCR_DS_OBSERVACAO                          AS \"Observação\",
    NOCR_CD_EVENTO                              AS \"Evento\",
    NVL(NOCR_VL_NC_ACERTADO, 0)                 AS \"Valor\",
    CASE NOCR_IC_ACERTADO_MANUALMENTE
	   WHEN 1 THEN 'Sim '
	   ELSE 'Não '
   END                                          AS \"Acertado manualmente\"
FROM
    CEO_TB_NOCR_NOTA_CREDITO
WHERE
    NOCR_CD_NOTA_CREDITO                        = '$empenho'
				";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchRow($sql);
    }

    /**
     * Retorna uma listagem completa de despesas com inconsistencias
     * @param int $despesa
     * @return array
     */    
    public function retornaListagemInconsistencia () {
        $sql = $this->retornaSqlListagemInconsistencia();

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    public function retornaListagemInconsistenciaReserva ( $despesa = null, $despesa_reserva = null ) {
        $sql = $this->retornaSqlListagemInconsistenciaReserva( null, null, $despesa, $despesa_reserva );

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    /**
     * Retorna uma lista de despesas com inconsistencias a partir da despesa
     * @param int $despesa
     * @return array
     */
    public function retornaListagemInconsistenciaPorDespesa( $despesa ) {
        $sql = $this->retornaSqlListagemInconsistencia( null, null, $despesa);

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    /**
     * Instrução sql que apresenta as ocorrências de inconsistências entre os
     * dados da nota de crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Opcionalmente pode-se informar o ano para restringir ainda mais os
     *        registros resultantes
     * @param boolean $filtra
     *        Filtra, ou não, registros manualmente acertados
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlListagemInconsistencia ($ano = null, $filtra = false, $despesa = null) {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;

        // Verifica UG
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $ug = $sessaoOrcamento->ug;

        // Verifica possível restrição de registros
        $joinUg = '';
        $condicaoUg = '';
        $condicaoAno = '';
        $condicaoAcertos = '';
        $condicaoDespesa = '';

        if($despesa) {
            $strWhere = "NOCR.NOCR_NR_DESPESA in ( $despesa ) AND";
        }

        if($despesa) {
            $strWhere = "NOCR.NOCR_NR_DESPESA in ( $despesa ) AND";
        }

        if($despesa) {
            $strWhere = "NOCR.NOCR_NR_DESPESA in ( $despesa ) AND";
        }

        if ($ug != 'todas') {
            $joinUg = "
Left JOIN
    CEO_TB_UNGE_UNIDADE_GESTORA	UNGE ON
        UNGE.UNGE_CD_UG = NOCR.NOCR_CD_UG_FAVORECIDO
                        ";

            $condicaoUg = "
    UNGE.UNGE_CD_UG NOT IN (90032, 90049, 110407) AND
    UNGE.UNGE_SG_SECAO = '$ug' AND
                            ";
        }

        if ($ano) {
            $condicaoAno = " SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) = $ano AND ";
        }

        if ($filtra) {
            $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
            $condicaoAcertos = " NOCR.NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND ";
        }

        $sql = "
SELECT
    CASE WHEN SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) <> DESP.DESP_AA_DESPESA THEN 'Ano; ' ELSE '' END ||
    CASE WHEN NOCR.NOCR_CD_UG_FAVORECIDO <> DESP.DESP_CD_UG THEN 'UG Favorecida; ' ELSE '' END ||
    CASE WHEN NOCR.NOCR_CD_FONTE <> DESP.DESP_CD_FONTE THEN 'Fonte; ' ELSE '' END ||
    CASE WHEN NOCR.NOCR_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO THEN 'PTRES; ' ELSE '' END ||
    CASE WHEN SUBSTR(NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6) THEN 'Natureza; ' ELSE '' END AS NOCR_INCONSISTENCIA,
    NOCR.NOCR_CD_NOTA_CREDITO,
    NVL(NOCR.NOCR_NR_DESPESA, 0) AS NOCR_NR_DESPESA,
    NVL(NOCR.NOCR_NR_DESPESA_RESERVA, 0) AS NOCR_NR_DESPESA_RESERVA,
    SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) AS NOCR_ANO,
    DESP.DESP_AA_DESPESA,
    NOCR.NOCR_CD_UG_FAVORECIDO,
    DESP.DESP_CD_UG,
    NOCR.NOCR_CD_FONTE,
    DESP.DESP_CD_FONTE,
    NOCR.NOCR_CD_PT_RESUMIDO,
    UNOR2.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_NOCR,
    DESP.DESP_CD_PT_RESUMIDO,
    UNOR1.UNOR_CD_UNID_ORCAMENTARIA AS UNOR_DESP,
    NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB,
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
    NOCR.NOCR_CD_UG_OPERADOR,
    TO_CHAR(NOCR.NOCR_DT_EMISSAO, '" .
            Trf1_Orcamento_Definicoes::FORMATO_DATA_INVERTIDA_COM_TRACO . "') AS NOCR_DT_EMISSAO,
    NOCR.NOCR_DS_OBSERVACAO,
    NOCR.NOCR_CD_EVENTO,
    NVL(NOCR.NOCR_VL_NC_ACERTADO, 0) AS NOCR_VL_NC_ACERTADO,
	CASE NOCR.NOCR_IC_ACERTADO_MANUALMENTE
	   WHEN 1 THEN 'Sim '
	   ELSE 'Não '
    END AS NOCR_IC_ACERTADO_MANUALMENTE
FROM
    CEO_TB_NOCR_NOTA_CREDITO NOCR
LEFT JOIN
    CEO_TB_DESP_DESPESA DESP ON
        DESP.DESP_NR_DESPESA = NOCR.NOCR_NR_DESPESA
LEFT JOIN
    CEO_TB_PTRS_PROGRAMA_TRABALHO P1 ON
        P1.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
LEFT JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR1 ON
        UNOR1.UNOR_CD_UNID_ORCAMENTARIA = P1.PTRS_CD_UNID_ORCAMENTARIA
LEFT JOIN
    CEO_TB_PTRS_PROGRAMA_TRABALHO P2 ON
        P2.PTRS_CD_PT_RESUMIDO = NOCR.NOCR_CD_PT_RESUMIDO
LEFT JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR2 ON
        UNOR2.UNOR_CD_UNID_ORCAMENTARIA = P2.PTRS_CD_UNID_ORCAMENTARIA
$joinUg
WHERE
    NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
$condicaoUg
$condicaoAno
$condicaoAcertos
$condicaoDespesa
    NOCR.NOCR_NR_DESPESA IS NOT NULL AND
    NOCR.NOCR_NR_DESPESA_RESERVA IS NOT NULL AND
    $strWhere
    NOCR.NOCR_CD_TIPO_NC IS NOT NULL AND
    (
        SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) <> DESP.DESP_AA_DESPESA OR
        NOCR.NOCR_CD_UG_FAVORECIDO <> DESP.DESP_CD_UG OR
        NOCR.NOCR_CD_FONTE <> DESP.DESP_CD_FONTE OR
        NOCR.NOCR_CD_PT_RESUMIDO <> DESP.DESP_CD_PT_RESUMIDO OR
        SUBSTR(NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB, 0, 6) <> SUBSTR(DESP.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 6)
    )
        		";
                
        return $sql;
    }

    /**
     * Instrução sql que apresenta as ocorrências de inconsistências entre os
     * dados da nota de crédito e a respectiva despesa relacionada
     *
     * @param integer $ano
     *        Opcionalmente pode-se informar o ano para restringir ainda mais os
     *        registros resultantes
     * @param boolean $filtra
     *        Filtra, ou não, registros manualmente acertados
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlListagemInconsistenciaReserva ($ano = null, $filtra = false, $despesa = null, $despesa_reserva = null) {
        // Define o código do tipo de solicitação
        $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
        // Verifica UG
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $ug = $sessaoOrcamento->ug;

        // Verifica possível restrição de registros
        $joinUg = '';
        $condicaoUg = '';
        $condicaoAno = '';
        $condicaoAcertos = '';

        if($despesa) {
            $strWhere = "NOCR.NOCR_NR_DESPESA in ( $despesa ) AND";
        }

        if($despesa_reserva) {
            $strWhereReserva = "NOCR.NOCR_NR_DESPESA_RESERVA in ( $despesa_reserva ) AND";
        }

        if ($ano) {
            $condicaoAno = " SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) = $ano AND ";
        }

        if ($filtra) {
            $acertadaNao = Orcamento_Business_Dados::ACERTO_MANUAL_NAO;
            $condicaoAcertos = " NOCR.NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND ";
        }

        $sql = "
SELECT
    CASE WHEN SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) <> DESPR.DESP_AA_DESPESA THEN 'Ano; ' ELSE '' END ||    
    CASE WHEN NOCR.NOCR_CD_PT_RESUMIDO <> DESPR.DESP_CD_PT_RESUMIDO THEN 'PTRES; ' ELSE '' END ||
    CASE WHEN SUBSTR(NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB, 0, 2) <> SUBSTR(DESPR.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 2) THEN 'Natureza; ' ELSE '' END AS NOCR_INCONSISTENCIA,
    NOCR.NOCR_CD_NOTA_CREDITO,
    NVL(NOCR.NOCR_NR_DESPESA, 0) AS NOCR_NR_DESPESA,
    NVL(NOCR.NOCR_NR_DESPESA_RESERVA, 0) AS NOCR_NR_DESPESA_RESERVA,
    SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) AS NOCR_ANO,
    DESPR.DESP_AA_DESPESA,
    NOCR.NOCR_CD_UG_FAVORECIDO,
    DESPR.DESP_CD_UG,
    NOCR.NOCR_CD_FONTE,
    DESPR.DESP_CD_FONTE,
    NOCR.NOCR_CD_PT_RESUMIDO,
    DESPR.DESP_CD_PT_RESUMIDO,
    NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB,
    DESPR.DESP_CD_ELEMENTO_DESPESA_SUB,
    NOCR.NOCR_CD_UG_OPERADOR,
    TO_CHAR(NOCR.NOCR_DT_EMISSAO, 'YYYY-MM-DD') AS NOCR_DT_EMISSAO,
    NOCR.NOCR_DS_OBSERVACAO,
    NOCR.NOCR_CD_EVENTO,
    NVL(NOCR.NOCR_VL_NC_ACERTADO, 0) AS NOCR_VL_NC_ACERTADO,
    CASE NOCR.NOCR_IC_ACERTADO_MANUALMENTE
       WHEN 1 THEN 'Sim '
       ELSE 'Não '
    END AS NOCR_IC_ACERTADO_MANUALMENTE
FROM
    CEO_TB_NOCR_NOTA_CREDITO NOCR
LEFT JOIN 
    CEO.CEO_TB_DESP_DESPESA DESPR ON
        DESPR.DESP_NR_DESPESA = NOCR.NOCR_NR_DESPESA_RESERVA          
WHERE
    NOCR_IC_ACERTADO_MANUALMENTE = $acertadaNao AND
$condicaoAno
$condicaoAcertos
    NOCR.NOCR_NR_DESPESA IS NOT NULL AND
    NOCR.NOCR_NR_DESPESA_RESERVA IS NOT NULL AND
    $strWhere
    $strWhereReserva
    NOCR.NOCR_CD_TIPO_NC IS NOT NULL AND
    (
        SUBSTR(NOCR.NOCR_CD_NOTA_CREDITO, 0, 4) <> DESPR.DESP_AA_DESPESA OR                
        NOCR.NOCR_CD_PT_RESUMIDO <> DESPR.DESP_CD_PT_RESUMIDO OR -- nao podemos comparar com a reserva pq e a mesma 
        SUBSTR(NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB, 0, 2) <> SUBSTR(DESPR.DESP_CD_ELEMENTO_DESPESA_SUB, 0, 2)
    )
    
    ORDER BY DESPR.DESP_AA_DESPESA DESC
                ";
        return $sql;
    }

}
