<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as regras negociais sobre empenho
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Empenho
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Empenho extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Empe();

        // Define a negocio
        $this->_negocio = 'empenho';
    }

    /**
     * Efetua transformações no formulario, se aplicável
     *
     * @param Zend_Form $formulario
     *        Formulário a ser transformado
     * @param string $acao
     *        Informa a ação que fez a chamada
     * @return Zend_Form $formulario
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function transformaFormulario($formulario, $acao) {
        return $formulario;
    }

    /**
     * Retorna os campos para serem incluídos na instrução sql para retorno de
     * dados desta classe
     *
     * @param string $acao
     *        Nome ada ação (action) em questão
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCampos($acao = 'todos') {
        // Campos para a serem apresentados na indexAction
        $campos['todos'] = " * ";

        // Campos para a serem apresentados na indexAction
        $campos['index'] = "
            DESP_AA_DESPESA,
            UNGE_SG_SECAO,
            DESP_NR_DESPESA,
            DESP_DS_ADICIONAL,
            PTRS_CD_PT_RESUMIDO,
            PTRS_SG_PT_RESUMIDO,
            EDSB_CD_ELEMENTO_DESPESA_SUB,
            NOEM_CD_NOTA_EMPENHO,
            NOEM_VL_NE_ACERTADO,
            VL_EXECUTADO,
            VL_SALDO
        ";

        // Campos para a serem apresentados na editarAction
        $campos['editar'] = $campos['index'];

        // Campos para a serem apresentados na detalheAction
        $campos['detalhe'] = "
            DESP_AA_DESPESA              AS \"Ano\",
            UNGE_SG_SECAO                AS \"Seção\",
            DESP_NR_DESPESA              AS \"Despesa\",
            DESP_DS_ADICIONAL            AS \"Descrição\",
            PTRS_CD_PT_RESUMIDO          AS \"PTRES\",
            PTRS_SG_PT_RESUMIDO          AS \"Sigla\",
            EDSB_CD_ELEMENTO_DESPESA_SUB AS \"Natureza da Despesa\",
            NOEM_CD_NOTA_EMPENHO         AS \"Nota de Empenho\",
            NOEM_DS_OBSERVACAO           AS \"Descrição da NE\",
            NOEM_VL_NE_ACERTADO          AS \"Valor\",
            VL_EXECUTADO                 AS \"Valor Executado\",
            VL_SALDO                     AS \"Saldo da NE\"
        ";

        // Campos para a serem apresentados na excluirAction
        $campos['excluir'] = "NOEM_CD_NOTA_EMPENHO, ";
        $campos['excluir'] .= $campos['detalhe'];

        // Campos para a serem apresentados na restaurarAction
        $campos['restaurar'] = $campos['excluir'];

        // Campos para a serem apresentados num combo
        $campos['combo'] = "";

        // Devolve os campos, conforme ação
        return $campos[$acao];
    }

    /**
     * Retorna as condições restritivas, se houver para a montagem da instrução
     * sql.
     *
     * @param string $acao
     *        Nome da ação (action) em questão
     * @param string $chaves
     *        Informa a chave, já tratada, se for o caso
     * @return string
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaRestricoes($acao = 'todos', $chaves = null) {
        // Condição para ação editar
        $restricao['detalhe'] = " AND DESP_NR_DESPESA IN ( $chaves ) ";

        // Condição para ação editar
        $restricao['editar'] = $restricao['detalhe'];

        // Condição para ação excluir
        $restricao['excluir'] = $restricao['detalhe'];

        // Condição para ação restaurar
        $restricao['restaurar'] = $restricao['detalhe'];

        return $restricao[$acao];
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaOpcoesGrid() {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'UNGE_SG_SECAO' => array('title' => 'UG', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'PTRS_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'EDSB_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da Despesa', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte de recursos', 'abbr' => ''),
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de Empenho', 'abbr' => ''),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor', 'abbr' => ''),
            'VL_EXECUTADO' => array('title' => 'Executado', 'abbr' => ''),
            'VL_SALDO' => array('title' => 'A executar', 'abbr' => ''));

        // Combina as opções num array
        $opcoes['detalhes'] = $detalhes;
        $opcoes['controle'] = $this->_negocio;
        $opcoes['ocultos'] = array('JUST_DT_EXCLUSAO_LOGICA', 'JUST_CD_MATRICULA_EXCLUSAO');

        // Devolve o array de opções
        return $opcoes;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaCacheIds($acao = null) {
        // Instancia o cache
        $cache = new Trf1_Cache();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id['index'] = $cache->retornaID_Listagem('orcamento', $negocio);

        // Id para combo
        $id['combo'] = $cache->retornaID_Combo('orcamento', $negocio);

        // Determina qual valor será retornado
        $retorno = ($acao != null ? $id[$acao] : $id);

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaEmpenhos($despesa = null) {
        $strWhere = "";
        if ($despesa) {
            $strWhere = "AND a.noem_nr_despesa = $despesa";
        }

        $sql = "
SELECT DISTINCT
DESP.DESP_AA_DESPESA,
CASE
    WHEN DESP.DESP_AA_DESPESA = 2015 THEN 1
    ELSE 2
END AS EXERCICIO,
U.UNGE_CD_UG,
A.NOEM_NR_DESPESA,
DESP.DESP_DS_ADICIONAL,
P.PTRS_CD_PT_RESUMIDO,
UNOR.UNOR_CD_UNID_ORCAMENTARIA,
P.PTRS_SG_PT_RESUMIDO,
E.EDSB_CD_ELEMENTO_DESPESA_SUB,
DESP.DESP_CD_FONTE,
A.NOEM_CD_NOTA_EMPENHO,
A.NOEM_CD_NOTA_EMPENHO||' - '||NOEM_DS_OBSERVACAO AS NOEM_DS_OBSERVACAO,
--A.NOEM_CD_NE_REFERENCIA,
SUM (A.NOEM_VL_NE_ACERTADO) + COALESCE(REFE.VALOR_REFERENCIA,0) EMPENHADO,
COALESCE(B.VALOR,0) EXECUTADO,
SUM (A.NOEM_VL_NE_ACERTADO) + COALESCE(REFE.VALOR_REFERENCIA,0) - COALESCE(B.VALOR,0) A_EXECUTAR

FROM CEO.CEO_TB_NOEM_NOTA_EMPENHO A

LEFT JOIN (SELECT exec_cd_nota_empenho, exec_cd_ug, AVG(exec_vl_janeiro +
       exec_vl_fevereiro + exec_vl_marco + exec_vl_abril +
       exec_vl_maio + exec_vl_junho + exec_vl_julho +
       exec_vl_agosto + exec_vl_setembro + exec_vl_outubro +
       exec_vl_novembro + exec_vl_dezembro) VALOR FROM CEO.CEO_TB_EXEC_EXECUCAO_NE

       GROUP BY exec_cd_nota_empenho, exec_cd_ug) B
    ON
    A.NOEM_CD_NOTA_EMPENHO = B.EXEC_CD_NOTA_EMPENHO
    OR A.NOEM_CD_NE_REFERENCIA = B.EXEC_CD_NOTA_EMPENHO

LEFT JOIN (SELECT NOEM_CD_NE_REFERENCIA, SUM (NOEM_VL_NE_ACERTADO) VALOR_REFERENCIA
        FROM CEO.CEO_TB_NOEM_NOTA_EMPENHO

       GROUP BY NOEM_CD_NE_REFERENCIA) REFE
    ON
    A.NOEM_CD_NOTA_EMPENHO = REFE.NOEM_CD_NE_REFERENCIA


INNER JOIN CEO_TB_DESP_DESPESA DESP ON
    A.NOEM_NR_DESPESA = DESP.DESP_NR_DESPESA

LEFT JOIN
    CEO_TB_RESP_RESPONSAVEL RSP ON
        RSP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
LEFT JOIN
    RH_CENTRAL_LOTACAO RHCL ON
        RHCL.LOTA_COD_LOTACAO = RSP.RESP_CD_LOTACAO AND
        RHCL.LOTA_SIGLA_SECAO = RSP.RESP_DS_SECAO

INNER JOIN CEO_TB_UNGE_UNIDADE_GESTORA U ON
    U.UNGE_CD_UG = DESP.DESP_CD_UG

INNER JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO P ON
    P.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO

Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON
    UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA

INNER JOIN CEO_TB_EDSB_ELEMENTO_SUB_DESP E ON
    E.EDSB_CD_ELEMENTO_DESPESA_SUB = DESP.DESP_CD_ELEMENTO_DESPESA_SUB

WHERE 1=1

    " . CEO_PERMISSAO_RESPONSAVEIS . "
    $strWhere

AND A.NOEM_CD_NE_REFERENCIA IS NULL

GROUP BY
--A.NOEM_CD_NE_REFERENCIA,
A.NOEM_CD_NOTA_EMPENHO,
A.NOEM_DS_OBSERVACAO,
A.NOEM_NR_DESPESA,
DESP.DESP_AA_DESPESA,
U.UNGE_CD_UG,
DESP.DESP_DS_ADICIONAL,
P.PTRS_CD_PT_RESUMIDO,
UNOR.UNOR_CD_UNID_ORCAMENTARIA,
P.PTRS_SG_PT_RESUMIDO,
E.EDSB_CD_ELEMENTO_DESPESA_SUB,
DESP.DESP_CD_FONTE,
B.VALOR,
REFE.VALOR_REFERENCIA
ORDER BY EXERCICIO
";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

}
