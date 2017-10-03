<?php

/**
 * @category    TRF1
 * @package     Trf1_Orcamento_Negocio_Despesa
 * @copyright   Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author      Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license     FREE, keep original copyrights
 * @version     controlada pelo SVN
 * @tutorial    Tutorial abaixo
 *
 * TRF1, Classe negocial sobre Orçamento -  Despesa
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
class Trf1_Orcamento_Negocio_Despesa {

    /**
     * Model dos Tipos de Recursos
     */
    protected $_dados = null;

    /**
     * Classe construtora
     *
     * @param   none
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct() {
        $this->_dados = new Application_Model_DbTable_Orcamento_CeoTbDespDespesa();
    }

    /**
     * Retorna a tabela principal desta classe negocial
     *
     * @return  array       Chave primária ou composta
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function tabela() {
        return $this->_dados;
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return  array       Chave primária ou composta
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function chavePrimaria() {
        return $this->_dados->chavePrimaria();
    }

    /*     * ***********************************************************
     * Funções básicas (gets e exclusão lógica)
     * ********************************************************** */

    /**
     * Apresenta dados (código e descrição) para montagem de combos
     *
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaCombo() {
        return false;
    }

    /**
     * Apresenta todos os campos da despesa informada
     *
     * @param   int     $despesa
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaDespesa($despesa) {
        if (!$despesa) {
            throw new Exception('Código da despesa é obrigatório');
        }

        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        $cacheId = $cache->retornaID_Despesa($despesa);
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            $sql = $this->_retornaQueryCompleta($despesa);

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchRow($sql);

            $cache->criarCache($dados, $cacheId);
        }

        if ($dados["DESP_NR_COPIA_DESPESA"]) {
            $negocioSaldo = new Trf1_Orcamento_Negocio_Saldo();
            $saldoCopiaDespesa = $negocioSaldo->retornaSaldo($dados["DESP_NR_COPIA_DESPESA"]);

            $dados['VL_SALDO_ANTERIOR'] = str_replace(",", ".", $saldoCopiaDespesa['VR_SUB_TOTAL']);
            $dados['VL_SALDO_BASE_ANTERIOR'] = str_replace(",", ".", $saldoCopiaDespesa['VR_PROPOSTA_APROVADA']) +
            str_replace(",", ".", $saldoCopiaDespesa['VR_CREDITO_ADICIONAL']) +
            str_replace(",", ".", $saldoCopiaDespesa['VR_MOVIMENTACAO']);
        }

        return $dados;
    }

    /**
     * Apresenta todos os campos da(s) despesa(s) informada(s)
     *
     * @param   array   $chaves
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaVariosRegistros($chaves) {
        $despesas = implode(', ', $chaves);

        $sql = $this->_retornaQueryCompleta($despesas);

        $banco = Zend_Db_Table::getDefaultAdapter();

        $dados = $banco->fetchAll($sql);

        return $dados;
    }

    /*     * ***********************************************************
     * Dados completos
     * ********************************************************** */

    /**
     * Retorna a instrução sql para consulta das despesas
     *
     * @param   string  $despesas
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    private function _retornaQueryCompleta($despesas = null) {
        // Valida parâmetros
        $condicaoDespesa = "";
        if ($despesas) {
            $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $despesas ) ";
        }

        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        // Busca perfil
        $sessao = new Orcamento_Business_Sessao();
        $perfilFull = $sessao->retornaPerfil();
        $perfil = $perfilFull['perfil'];

        $condicaoFaseExercicio = "";

        if ($perfil != Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR && $perfil != Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO) {

            $condicaoFaseExercicio = " AND FANE.FANE_ID_FASE_EXERCICIO <> ";
            $condicaoFaseExercicio .= Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
        }

        // Formatos aplicados
        $formatoDinheiro = Trf1_Orcamento_Definicoes::FORMATO_DINHEIRO;
        $formatoNumero = Trf1_Orcamento_Definicoes::FORMATO_NUMERO;
        $formatoData = Trf1_Orcamento_Definicoes::FORMATO_DATA;

        $sql = "
    SELECT
        DESP.DESP_CD_PERS_PERSPECTIVA,
        DESP.DESP_CD_MACRO_MACRODESAFIO,
    DESP.DESP_NR_DESPESA,
    DESP.DESP_NR_COPIA_DESPESA,
    DESP.DESP_AA_DESPESA,
    DESP.DESP_DS_ADICIONAL,
        DESP.DESP_IC_REFLEXO_EXERCICIO,
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' || DESP.DESP_DS_ADICIONAL AS DS_DESPESA,
    DESP.DESP_CD_UG,
    UNGE.UNGE_DS_UG,
    DESP.DESP_CD_RESPONSAVEL,
        RHCL.LOTA_DSC_LOTACAO AS DS_RESPONSAVEL,
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_FAMILIA_RESPONSAVEL,
    RHCL.LOTA_SIGLA_LOTACAO || ' - ' ||
    REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' ||
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_DS_FAMILIA_RESPONSAVEL,
    RHCL.LOTA_SIGLA_SECAO,
    RHCL.LOTA_COD_LOTACAO,
    DESP.DESP_CD_ESFERA,
    ESFE.ESFE_DS_ESFERA,
    DESP.DESP_CD_PT_RESUMIDO,
    PTRS.PTRS_DS_PT_RESUMIDO,
        PTRS.PTRS_SG_PT_RESUMIDO,
        UNOR.UNOR_CD_UNID_ORCAMENTARIA,
    PTRS.PTRS_CD_UNID_ORCAMENTARIA,
    UPPER(PTRS.PTRS_CD_PT_RESUMIDO || ' - ' || PTRS_SG_PT_RESUMIDO || ' - ' || PTRS.PTRS_DS_PT_RESUMIDO)        AS CD_DS_SG_PTRES,
    PTRS_CD_PT_RESUMIDO,
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB,
    UPPER(EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' || EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB)                      AS CD_DS_ELEMENTO,
    DESP.DESP_CD_TIPO_DESPESA,
    TIDE.TIDE_DS_TIPO_DESPESA,

    DESP.DESP_CD_FONTE,
    FONT.FONT_DS_FONTE,
    DESP.DESP_CD_CATEGORIA,
    CATE.CATE_DS_CATEGORIA,
    DESP.DESP_CD_VINCULACAO,
    VINC.VINC_DS_VINCULACAO,
    DESP.DESP_CD_TIPO_RECURSO,
    TREC.TREC_DS_TIPO_RECURSO,

    DESP.DESP_CD_TIPO_ORCAMENTO,
    PORC.PORC_DS_TIPO_ORCAMENTO,
        PERS.PERS_TX_PERSPECTIVA,
        MACRO.MACRO_TX_MACRODESAFIO,
    DESP.DESP_CD_OBJETIVO,
    POBJ.POBJ_DS_OBJETIVO,
    DESP.DESP_CD_PROGRAMA,
    PPRG.PPRG_DS_PROGRAMA,
    DESP.DESP_CD_TIPO_OPERACIONAL,
    POPE.POPE_DS_TIPO_OPERACIONAL,
    DESP.DESP_CD_TIPO_OPERACIONAL || ' - ' || POPE.POPE_DS_TIPO_OPERACIONAL AS CD_DS_TIPO_OPERACIONAL,

    /* Contrato */
    CTRD_ID_CONTRATO_DESPESA,
    CTRD_NR_CONTRATO,
    CTRD_NM_EMPRESA_CONTRATADA,
	CTRD_VL_DESPESA,
    CTRD_CPFCNPJ_DESPESA,
    TO_CHAR(CTRD_DT_INICIO_VIGENCIA, '" . $formatoData . "') AS CTRD_DT_INICIO_VIGENCIA,
    TO_CHAR(CTRD_DT_TERMINO_VIGENCIA, '" . $formatoData . "') AS CTRD_DT_TERMINO_VIGENCIA,

    /* VL_DESPESA_XYZ = Valor puro para uso nos campos */
            NVL(VLD5.VLDE_VL_DESPESA, 0) AS VL_DESPESA_BASE_EXERC_ANTERIOR,

    CASE
        WHEN DESP.DESP_AA_DESPESA = " . date('Y') . " THEN 1
        ELSE 2
    END AS EXERCICIO,

    /* Composição da base % */
    CASE
        WHEN VLD5.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN VLD6.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN NVL(VLD5.VLDE_VL_DESPESA, 0) = 0 THEN -1

        WHEN NVL(VLD11.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD11.VLDE_VL_DESPESA, 0) - NVL(VLD5.VLDE_VL_DESPESA, 0)) / NVL(VLD5.VLDE_VL_DESPESA, 0)

        WHEN NVL(VLD5.VLDE_VL_DESPESA, 0) >= 0 THEN (NVL(VLD6.VLDE_VL_DESPESA, 0) - NVL(VLD5.VLDE_VL_DESPESA, 0)) / NVL(VLD5.VLDE_VL_DESPESA, 0)
    END AS VL_DESPESA_BASE_PERCENTUAL,

    /* Composição da base R$*/
        CASE
        WHEN VLD5.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN VLD6.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN NVL(VLD11.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD11.VLDE_VL_DESPESA, 0)
        ELSE NVL(VLD6.VLDE_VL_DESPESA, 0) - NVL(VLD5.VLDE_VL_DESPESA, 0)
        END AS VL_DESPESA_BASE_DIFERENCA,

    /* Base da pré proposta */
        CASE
        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD12.VLDE_VL_DESPESA, 0)
        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD6.VLDE_VL_DESPESA, 0)
        ELSE 0
        END AS VL_DESPESA_BASE_EXERC_ATUAL,

    /* Reajuste pré proposta %*/
        CASE
        WHEN VLD6.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN VLD9.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN ( NVL(VLD12.VLDE_VL_DESPESA, 0) > 0  AND NVL(VLD13.VLDE_VL_DESPESA, 0) > 0 ) THEN (NVL(VLD13.VLDE_VL_DESPESA, 0) / NVL(VLD12.VLDE_VL_DESPESA, 0) * 100) / 100
        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0  THEN (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD12.VLDE_VL_DESPESA, 0) * 100) / 100
        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100
        END AS VL_PERCENT_REAJUSTE_PROPOSTA,


    /* Reajuste pré proposta R$*/
        CASE
        WHEN NVL(VLD13.VLDE_VL_DESPESA, 0) > 0 AND NVL(VLD12.VLDE_VL_DESPESA, 0) > 0
            THEN NVL(VLD12.VLDE_VL_DESPESA, 0) * (NVL(VLD13.VLDE_VL_DESPESA, 0) / NVL(VLD12.VLDE_VL_DESPESA, 0) * 100) / 100

        WHEN NVL(VLD13.VLDE_VL_DESPESA, 0) > 0 AND NVL(VLD6.VLDE_VL_DESPESA, 0) > 0
            THEN NVL(VLD6.VLDE_VL_DESPESA, 0) * (NVL(VLD13.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100

        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) > 0
            THEN NVL(VLD6.VLDE_VL_DESPESA, 0) * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100

        END AS VL_REAJUSTE_PROPOSTA,

        /* Percentual Reajuste aplicado ao limite*/
        CASE
        WHEN VLD6.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN VLD10.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) = 0 THEN -1
        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD10.VLDE_VL_DESPESA, 0) - NVL(VLD6.VLDE_VL_DESPESA, 0)) / NVL(VLD6.VLDE_VL_DESPESA, 0)
        END AS VL_PERCENT_APLICADO_LIMITE,

        /* Valor Reajuste aplicado ao limite*/
        CASE
        WHEN VLD6.VLDE_VL_DESPESA IS NULL THEN 0
        WHEN VLD10.VLDE_VL_DESPESA IS NULL THEN 0
        ELSE NVL(VLD6.VLDE_VL_DESPESA, 0) - NVL(VLD10.VLDE_VL_DESPESA, 0)
        END AS VL_REAJUSTE_APLICADO_LIMITE,

        /* Valor Proposta inicial */
        CASE
        /* Testa se a base e o reajuste foram enviados manualmente */
        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0 AND NVL(VLD13.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD12.VLDE_VL_DESPESA, 0) + NVL(VLD13.VLDE_VL_DESPESA, 0))  + NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0)

        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD12.VLDE_VL_DESPESA, 0) + NVL(VLD12.VLDE_VL_DESPESA, 0))  * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD12.VLDE_VL_DESPESA, 0) * 100 ) /100 + NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0)

        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD9.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD6.VLDE_VL_DESPESA, 0) * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100 + ( NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0) )

        ELSE NVL(SOLA.SOLA_VL_ATENDIDO, 0)

        END AS VL_DESPESA_RESPONSAVEL,

        NVL(SOLA1.SOLA_VL_SOLICITADO, 0) AS VL_SOLICITACAO_ACRESCIMO_SOLI,

        /* Solicitação de acréscimo pelo responsável. */
        NVL(SOLA1.SOLA_VL_ATENDIDO, 0) AS VL_SOLICITACAO_ACRESCIMO_RESP,

        /* Calcula valor do campo Ajuste do Limite (R$) Veficar onde conseguir o perceutal deste campo*/
        /* Fórmula: Base da proposta + Solicitação de acrescimo pelo responsável + Reajuste aplicado ao limite */
        /* O campo --Solicitação de acrescimo pelo responsável-- ainda nao foi implementado (VL_SOL_ACRESCIMO_RESP) */
        NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD10.VLDE_VL_DESPESA, 0) as VL_AJUSTE_DO_LIMITE,

        /* NVL(VLD6.VLDE_VL_DESPESA, 0) AS VL_DESPESA_BASE_EXERC_ATUAL, -- Base da pré proposta */

        NVL(VLD7.VLDE_VL_DESPESA, 0) AS VL_DESPESA_AJUSTE_DIPLA,

    /*NVL(VLD1.VLDE_VL_DESPESA, 0) AS VL_DESPESA_RESPONSAVEL, removido para fazer o campo VL_DESPESA_RESPONSAVEL */

    SOLA.SOLA_VL_SOLICITADO AS VL_DESPESA_SOLIC_RESPONSAVEL, /*  NVL(VLD8.VLDE_VL_DESPESA, 0) AS VL_DESPESA_SOLIC_RESPONSAVEL, */
    SOLA.SOLA_VL_ATENDIDO,

       /* Ajuste setorial da pré proposta */
        /* Regra não documentada!!!. Valor deste campo é igual ao campo PROPOSTA INICIAL quando não houver valor salvo na tabela quando editado o campo AJUSTE SETORIAL DA PRE PROPOSTA */
        CASE

        WHEN NVL(VLD2.VLDE_VL_DESPESA, 0) > 0 THEN VLD2.VLDE_VL_DESPESA /* Se o valor for editado manualmente, prevalece ele */

        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0 AND NVL(VLD13.VLDE_VL_DESPESA, 0) > 0 AND SOLA.SOLA_TP_SOLICITACAO = 1 AND SOLA.SOLA_IC_SITUACAO = 1 THEN (NVL(VLD12.VLDE_VL_DESPESA, 0) + NVL(VLD13.VLDE_VL_DESPESA, 0))  + ( NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0) )

        WHEN NVL(VLD12.VLDE_VL_DESPESA, 0) > 0 THEN (NVL(VLD12.VLDE_VL_DESPESA, 0) + NVL(VLD12.VLDE_VL_DESPESA, 0))  * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD12.VLDE_VL_DESPESA, 0) * 100 ) /100 + ( NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0) )

        WHEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD9.VLDE_VL_DESPESA, 0) > 0 THEN NVL(VLD6.VLDE_VL_DESPESA, 0) + NVL(VLD6.VLDE_VL_DESPESA, 0) * (NVL(VLD9.VLDE_VL_DESPESA, 0) / NVL(VLD6.VLDE_VL_DESPESA, 0) * 100) / 100 + ( NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0) )

        ELSE NVL(SOLA.SOLA_VL_ATENDIDO, 0) + NVL(SOLA1.SOLA_VL_ATENDIDO, 0)

        END AS VL_DESPESA_DIPLA,

    /*NVL(VLD2.VLDE_VL_DESPESA, 0) AS VL_DESPESA_DIPLA,  Este campo é igual o campo Proposta inicial*/
    NVL(VLD3.VLDE_VL_DESPESA, 0) AS VL_DESPESA_CONGRESSO,
    NVL(VLD4.VLDE_VL_DESPESA, 0) AS VL_DESPESA_SECOR,
    NVL(VLD9.VLDE_VL_DESPESA, 0) AS VL_REAJUSTE_PROPOSTA_ATUAL,
    NVL(VLD10.VLDE_VL_DESPESA, 0) AS VL_REAJUSTE_APLICADO_LIMITE,

    DESP.DESP_VL_MAX_MENSAL_AUTORIZADO AS DESP_VL_MAX_MENSAL_AUTORIZADO,

    /* VL_DINHEIRO_XYZ = Valor em formato monetário para exibição em tela */
    TO_CHAR(NVL(VLD1.VLDE_VL_DESPESA, 0), '" . $formatoDinheiro . "') AS VL_DINHEIRO_RESPONSAVEL,
    TO_CHAR(NVL(VLD2.VLDE_VL_DESPESA, 0), '" . $formatoDinheiro . "') AS VL_DINHEIRO_DIPLA,
    TO_CHAR(NVL(VLD3.VLDE_VL_DESPESA, 0), '" . $formatoDinheiro . "') AS VL_DINHEIRO_CONGRESSO,
    TO_CHAR(NVL(VLD4.VLDE_VL_DESPESA, 0), '" . $formatoDinheiro . "') AS VL_DINHEIRO_SECOR,
    TO_CHAR(NVL(DESP.DESP_VL_MAX_MENSAL_AUTORIZADO, 0), '" . $formatoDinheiro . "') AS VL_DINHEIRO_MENSAL_AUTORIZADO,

    /* VL_NUMERO_XYZ = Valor em formato numérico para exibição em tela */
    TO_CHAR(NVL(VLD1.VLDE_VL_DESPESA, 0), '" . $formatoNumero . "') AS VL_NUMERO_RESPONSAVEL,
    TO_CHAR(NVL(VLD2.VLDE_VL_DESPESA, 0), '" . $formatoNumero . "') AS VL_NUMERO_DIPLA,
    TO_CHAR(NVL(VLD3.VLDE_VL_DESPESA, 0), '" . $formatoNumero . "') AS VL_NUMERO_CONGRESSO,
    TO_CHAR(NVL(VLD4.VLDE_VL_DESPESA, 0), '" . $formatoNumero . "') AS VL_NUMERO_SECOR,
    TO_CHAR(NVL(DESP.DESP_VL_MAX_MENSAL_AUTORIZADO, 0), '" . $formatoNumero . "') AS VL_NUMERO_MENSAL_AUTORIZADO,

    FANE.FANE_ID_FASE_EXERCICIO,
    FANE.FASE_NM_FASE_EXERCICIO,

    CASE
        WHEN DESP.DESP_IC_CONFERIDO = 0
        THEN 'Não '
        ELSE 'Sim '
    END AS DESP_IC_CONFERIDO,

    CASE
        WHEN DESP.DESP_IC_FINALIZADO = 0
        THEN 'Não '
        ELSE 'Sim '
    END AS DESP_IC_FINALIZADO

        FROM CEO_TB_DESP_DESPESA DESP
        Left JOIN CEO_TB_PERS_PERSPECTIVA PERS ON PERS.PERS_ID_PERSPECTIVA = DESP.DESP_CD_PERS_PERSPECTIVA
        Left JOIN CEO_TB_MACRO_MACRODESAFIO MACRO ON MACRO.MACRO_ID_MACRODESAFIO = DESP.DESP_CD_MACRO_MACRODESAFIO
        Left JOIN CEO_TB_EDSB_ELEMENTO_SUB_DESP EDSB ON EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
        Left JOIN CEO_TB_UNGE_UNIDADE_GESTORA UNGE ON UNGE.UNGE_CD_UG = DESP.DESP_CD_UG
        Left JOIN CEO_TB_RESP_RESPONSAVEL RESP ON RESP.RESP_CD_RESPONSAVEL = DESP.DESP_CD_RESPONSAVEL
        Left JOIN RH_CENTRAL_LOTACAO RHCL ON RHCL.LOTA_COD_LOTACAO = RESP.RESP_CD_LOTACAO AND RHCL.LOTA_SIGLA_SECAO = RESP.RESP_DS_SECAO
        Left JOIN CEO_TB_ESFE_ESFERA ESFE ON ESFE.ESFE_CD_ESFERA = DESP.DESP_CD_ESFERA
        Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO PTRS ON PTRS.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
        Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON UNOR.UNOR_CD_UNID_ORCAMENTARIA = PTRS.PTRS_CD_UNID_ORCAMENTARIA
        Left JOIN CEO_TB_TIDE_TIPO_DESPESA TIDE ON TIDE.TIDE_CD_TIPO_DESPESA = DESP.DESP_CD_TIPO_DESPESA
        Left JOIN CEO_TB_FONT_FONTE FONT ON FONT.FONT_CD_FONTE = DESP.DESP_CD_FONTE
        Left JOIN CEO_TB_CATE_CATEGORIA CATE ON CATE.CATE_CD_CATEGORIA = DESP.DESP_CD_CATEGORIA
        Left JOIN CEO_TB_VINC_VINCULACAO VINC ON VINC.VINC_CD_VINCULACAO = DESP.DESP_CD_VINCULACAO
        Left JOIN CEO_TB_TREC_TIPO_RECURSO TREC ON TREC.TREC_CD_TIPO_RECURSO = DESP.DESP_CD_TIPO_RECURSO
        Left JOIN CEO_TB_PORC_TIPO_ORCAMENTO PORC ON PORC.PORC_CD_TIPO_ORCAMENTO = DESP.DESP_CD_TIPO_ORCAMENTO
        Left JOIN CEO_TB_PPRG_PROGRAMA PPRG ON PPRG.PPRG_CD_PROGRAMA = DESP.DESP_CD_PROGRAMA
        Left JOIN CEO_TB_POBJ_OBJETIVO POBJ ON POBJ.POBJ_CD_OBJETIVO = DESP.DESP_CD_OBJETIVO
        Left JOIN CEO_TB_POPE_TIPO_OPERACIONAL POPE ON  POPE.POPE_CD_TIPO_OPERACIONAL = DESP.DESP_CD_TIPO_OPERACIONAL
        Left JOIN CEO_TB_CTRD_CONTRATO_DESPESA CTRD ON CTRD.CTRD_NR_DESPESA = DESP.DESP_NR_DESPESA

        Left JOIN CEO_TB_SOLA_SOLICITACAO_AJUSTE SOLA ON SOLA.SOLA_NR_DESPESA = DESP.DESP_NR_DESPESA AND SOLA.SOLA_TP_SOLICITACAO = 0 AND SOLA.SOLA_IC_SITUACAO = 1

        Left JOIN CEO_TB_SOLA_SOLICITACAO_AJUSTE SOLA1 ON SOLA1.SOLA_NR_DESPESA = DESP.DESP_NR_DESPESA AND SOLA1.SOLA_TP_SOLICITACAO = 1 AND SOLA1.SOLA_IC_SITUACAO = 1

        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD1 ON VLD1.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD1.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_PROPOSTA_INICIAL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD2 ON VLD2.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD2.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_DIPLA_AJUSTE_POS_RESPONSAVEL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD3 ON VLD3.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD3.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_AJUSTE_LIMITE . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD4 ON VLD4.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD4.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_DIPOR_APROVADO . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD5 ON VLD5.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD5.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ANTERIOR . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD6 ON VLD6.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD6.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_BASE_ANO_ATUAL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD7 ON VLD7.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD7.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_DIPLA_AJUSTE_POS_BASE . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD8 ON VLD8.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD8.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_SOLIC_RESPONSAVEL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD9 ON VLD9.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD9.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_PROPOSTA_ATUAL . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD10 ON VLD10.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD10.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_APLICADO_LIMITE . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD11 ON VLD11.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD11.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_COMPOSICAO_BASE . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD12 ON VLD12.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD12.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_BASE_PREPROPOSTA . "
        Left JOIN CEO_TB_VLDE_VALOR_DESPESA VLD13 ON VLD13.VLDE_NR_DESPESA = DESP.DESP_NR_DESPESA AND VLD13.VLDE_CD_DEMANDANTE = " . Orcamento_Business_Dados::DEMANDANTE_REAJUSTE_PREPROPOSTA . "
        Left JOIN (" . $this->retornaSqlFaseExercicio() . ") FANE ON FANE.FANE_NR_ANO = DESP.DESP_AA_DESPESA
        WHERE DESP.DESP_DH_EXCLUSAO_LOGICA IS Null

        AND (CTRD_ID_CONTRATO_DESPESA = (SELECT MAX(CTRD_ID_CONTRATO_DESPESA) FROM CEO_TB_CTRD_CONTRATO_DESPESA
        WHERE CTRD_NR_DESPESA = DESP.DESP_NR_DESPESA) OR CTRD_ID_CONTRATO_DESPESA IS NULL)

    $condicaoDespesa
    $condicaoResponsaveis
    $condicaoFaseExercicio

        ORDER BY EXERCICIO, DESP_AA_DESPESA DESC ";

        return $sql;
    }

    public function retornaListagemDistribuicao() {
        $saldo = new Trf1_Orcamento_Negocio_Saldo();

        $sql = "
SELECT
    SALDO.NR_DESPESA                    AS DESP_NR_DESPESA,
    SALDO.DESP_AA_DESPESA               AS DESP_AA_DESPESA,
    SALDO.DESP_DS_ADICIONAL             AS DESP_DS_ADICIONAL,
    SALDO.DESP_CD_UG                    AS DESP_CD_UG,
    SALDO.SG_FAMILIA_RESPONSAVEL        AS SG_FAMILIA_RESPONSAVEL,
    SALDO.DESP_CD_FONTE                 AS DESP_CD_FONTE,
    SALDO.DESP_CD_PT_RESUMIDO           AS DESP_CD_PT_RESUMIDO,
    SALDO.UNOR_CD_UNID_ORCAMENTARIA     AS UNOR_CD_UNID_ORCAMENTARIA,
    SALDO.PTRS_SG_PT_RESUMIDO           AS PTRS_SG_PT_RESUMIDO,
    SALDO.DESP_CD_ELEMENTO_DESPESA_SUB  AS DESP_CD_ELEMENTO_DESPESA_SUB,
    SALDO.TIDE_DS_TIPO_DESPESA          AS TIDE_DS_TIPO_DESPESA,
    SALDO.VR_PROPOSTA_APROVADA          AS VR_PROPOSTA_APROVADA,
    SALDO.VR_PROPOSTA_RECEBIDA          AS VR_PROPOSTA_RECEBIDA,
    SALDO.VR_PROPOSTA_A_RECEBER         AS VR_PROPOSTA_A_RECEBER
FROM
    (
    " . $saldo->_retornaQueryCompleta() . "
    ) SALDO
                ";

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    public function retornaSqlFaseExercicio() {
        $fase = new Orcamento_Business_Negocio_FaseAnoExercicio();
        $sql = $fase->retornaSqlFaseExercicio();

        // Devolve a instrução sql
        return $sql;
    }

    /*     * ***********************************************************
     * Listagens
     * ********************************************************** */

    /**
     * Retorna array contendo os campos desejados por uma listagem
     *
     * @param   array   $camposDesejados
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    private function _retornaListagemBase($camposDesejados) {
        if (!$camposDesejados) {
            return array();
        }

        $dadosBase = $this->retornaDadosCompletos();

        $camposExcluir = array_diff($this->_retornaCampos(), $camposDesejados);

        $dados = array();
        foreach ($dadosBase as $registro) {
            // Elimina campos não desejados
            foreach ($camposExcluir as $campo) {
                unset($registro[$campo]);
            }

            $dados[] = $registro;
        }

        return $dados;
    }

    public function retornaListagemSimplificada() {
        $camposDesejados = array('DESP_NR_DESPESA', 'DESP_NR_COPIA_DESPESA', 'DESP_AA_DESPESA', 'DS_DESPESA', 'DESP_CD_UG', 'SG_FAMILIA_RESPONSAVEL', 'DESP_CD_PT_RESUMIDO', 'DESP_CD_ELEMENTO_DESPESA_SUB', 'TIDE_DS_TIPO_DESPESA', 'VL_DESPESA_SECOR');

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemOrcamento() {
        $camposDesejados = array('DESP_NR_DESPESA', 'DESP_AA_DESPESA', 'ESFE_DS_ESFERA', 'DESP_CD_PT_RESUMIDO', 'DESP_CD_ELEMENTO_DESPESA_SUB', 'TIDE_DS_TIPO_DESPESA');

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemFinanceiro() {
        $camposDesejados = array('DESP_NR_DESPESA', 'DESP_AA_DESPESA', 'DESP_CD_FONTE', 'DESP_CD_CATEGORIA', 'DESP_CD_VINCULACAO', 'TREC_DS_TIPO_RECURSO', 'PTRS_CD_PT_RESUMIDO', 'UNOR_CD_UNID_ORCAMENTARIA');

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemPlanejamento() {
        $camposDesejados = array('DESP_NR_DESPESA', 'DESP_AA_DESPESA', 'DESP_CD_PT_RESUMIDO', 'UNOR_CD_UNID_ORCAMENTARIA', 'PORC_DS_TIPO_ORCAMENTO', 'POBJ_DS_OBJETIVO', 'PPRG_DS_PROGRAMA', 'POPE_DS_TIPO_OPERACIONAL');

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemContrato() {
        $camposDesejados = array('DESP_NR_DESPESA', 'DESP_AA_DESPESA', 'DS_DESPESA', 'DESP_CD_UG', 'DESP_CD_PT_RESUMIDO', 'DESP_CD_ELEMENTO_DESPESA_SUB', 'CTRD_ID_CONTRATO_DESPESA', 'CTRD_NR_CONTRATO', 'CTRD_NM_EMPRESA_CONTRATADA', 'CTRD_DT_INICIO_VIGENCIA', 'CTRD_DT_TERMINO_VIGENCIA');

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemRecursos() {
        $i = 0;
        $camposDesejados[$i++] = 'DESP_NR_DESPESA';
        $camposDesejados[$i++] = 'DESP_AA_DESPESA';
        $camposDesejados[$i++] = 'DS_DESPESA';
        $camposDesejados[$i++] = 'DESP_CD_PT_RESUMIDO';
        $camposDesejados[$i++] = 'UNOR_CD_UNID_ORCAMENTARIA';
        // $camposDesejados [ $i++ ] = '';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_EXERC_ANTERIOR';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_PERCENTUAL';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_DIFERENCA';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_EXERC_ATUAL';
        $camposDesejados[$i++] = 'VL_DESPESA_AJUSTE_DIPLA';
        $camposDesejados[$i++] = 'VL_DESPESA_RESPONSAVEL';
        $camposDesejados[$i++] = 'VL_DESPESA_SOLIC_RESPONSAVEL';
        $camposDesejados[$i++] = 'VL_DESPESA_DIPLA';
        $camposDesejados[$i++] = 'VL_DESPESA_CONGRESSO';
        $camposDesejados[$i++] = 'VL_DESPESA_SECOR';
        $camposDesejados[$i++] = 'VL_MAX_MENSAL_AUTORIZADO';

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    public function retornaListagemCompleta() {
        $i = 0;
        $camposDesejados[$i++] = 'DESP_NR_DESPESA';
        $camposDesejados[$i++] = 'DESP_AA_DESPESA';
        $camposDesejados[$i++] = 'DS_DESPESA';
        $camposDesejados[$i++] = 'DESP_CD_UG';
        $camposDesejados[$i++] = 'SG_FAMILIA_RESPONSAVEL';
        $camposDesejados[$i++] = 'ESFE_DS_ESFERA';
        $camposDesejados[$i++] = 'DESP_CD_PT_RESUMIDO';
        $camposDesejados[$i++] = 'DESP_CD_ELEMENTO_DESPESA_SUB';
        $camposDesejados[$i++] = 'TIDE_DS_TIPO_DESPESA';
        $camposDesejados[$i++] = 'DESP_CD_FONTE';
        $camposDesejados[$i++] = 'DESP_CD_CATEGORIA';
        $camposDesejados[$i++] = 'DESP_CD_VINCULACAO';
        $camposDesejados[$i++] = 'TREC_DS_TIPO_RECURSO';
        $camposDesejados[$i++] = 'PORC_DS_TIPO_ORCAMENTO';
        $camposDesejados[$i++] = 'POBJ_DS_OBJETIVO';
        $camposDesejados[$i++] = 'PPRG_DS_PROGRAMA';
        $camposDesejados[$i++] = 'POPE_DS_TIPO_OPERACIONAL';
        $camposDesejados[$i++] = 'CTRD_NR_CONTRATO';
        $camposDesejados[$i++] = 'CTRD_NM_EMPRESA_CONTRATADA';
        $camposDesejados[$i++] = 'CTRD_CPFCNPJ_DESPESA';
        $camposDesejados[$i++] = 'CTRD_DT_INICIO_VIGENCIA';
        $camposDesejados[$i++] = 'CTRD_DT_TERMINO_VIGECNCIA';
        $camposDesejados[$i++] = 'CTRD_VL_DESPESA';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_EXERC_ANTERIOR';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_PERCENTUAL';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_DIFERENCA';
        $camposDesejados[$i++] = 'VL_DESPESA_BASE_EXERC_ATUAL';
        $camposDesejados[$i++] = 'VL_DESPESA_AJUSTE_DIPLA';
        $camposDesejados[$i++] = 'VL_DESPESA_RESPONSAVEL';
        $camposDesejados[$i++] = 'VL_DESPESA_SOLIC_RESPONSAVEL';
        $camposDesejados[$i++] = 'VL_DESPESA_DIPLA';
        $camposDesejados[$i++] = 'VL_DESPESA_CONGRESSO';
        $camposDesejados[$i++] = 'VL_DESPESA_SECOR';
        $camposDesejados[$i++] = 'VL_MAX_MENSAL_AUTORIZADO';
        $camposDesejados[$i++] = 'DESP_NR_COPIA_DESPESA';

        $listagem = $this->_retornaListagemBase($camposDesejados);

        return $listagem;
    }

    /*     * ***********************************************************
     * Funções auxiliares
     * ********************************************************** */

    /**
     * Retorna todos os campos existentes na consulta completa de despesas
     *
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    private function _retornaCampos() {
        $i = 0;
        $campos[$i++] = 'DESP_NR_DESPESA';
        $campos[$i++] = 'DESP_AA_DESPESA';
        $campos[$i++] = 'DESP_DS_ADICIONAL';
        $campos[$i++] = 'DS_DESPESA';
        $campos[$i++] = 'DESP_CD_UG';
        $campos[$i++] = 'UNGE_DS_UG';
        $campos[$i++] = 'DESP_CD_RESPONSAVEL';
        $campos[$i++] = 'DS_RESPONSAVEL';
        $campos[$i++] = 'SG_FAMILIA_RESPONSAVEL';
        $campos[$i++] = 'SG_DS_FAMILIA_RESPONSAVEL';
        $campos[$i++] = 'LOTA_SIGLA_SECAO';
        $campos[$i++] = 'LOTA_COD_LOTACAO';
        $campos[$i++] = 'DESP_CD_ESFERA';
        $campos[$i++] = 'ESFE_DS_ESFERA';
        $campos[$i++] = 'DESP_CD_PT_RESUMIDO';
        $campos[$i++] = 'PTRS_DS_PT_RESUMIDO';
        $campos[$i++] = 'CD_DS_SG_PTRES';
        $campos[$i++] = 'DESP_CD_ELEMENTO_DESPESA_SUB';
        $campos[$i++] = 'EDSB_DS_ELEMENTO_DESPESA_SUB';
        $campos[$i++] = 'CD_DS_ELEMENTO';
        $campos[$i++] = 'DESP_CD_TIPO_DESPESA';
        $campos[$i++] = 'TIDE_DS_TIPO_DESPESA';
        $campos[$i++] = 'DESP_CD_FONTE';
        $campos[$i++] = 'FONT_DS_FONTE';
        $campos[$i++] = 'DESP_CD_CATEGORIA';
        $campos[$i++] = 'CATE_DS_CATEGORIA';
        $campos[$i++] = 'DESP_CD_VINCULACAO';
        $campos[$i++] = 'VINC_DS_VINCULACAO';
        $campos[$i++] = 'DESP_CD_TIPO_RECURSO';
        $campos[$i++] = 'TREC_DS_TIPO_RECURSO';
        $campos[$i++] = 'DESP_CD_TIPO_ORCAMENTO';
        $campos[$i++] = 'PORC_DS_TIPO_ORCAMENTO';
        $campos[$i++] = 'DESP_CD_OBJETIVO';
        $campos[$i++] = 'POBJ_DS_OBJETIVO';
        $campos[$i++] = 'DESP_CD_PROGRAMA';
        $campos[$i++] = 'PPRG_DS_PROGRAMA';
        $campos[$i++] = 'DESP_CD_TIPO_OPERACIONAL';
        $campos[$i++] = 'POPE_DS_TIPO_OPERACIONAL';
        $campos[$i++] = 'CD_DS_TIPO_OPERACIONAL';
        $campos[$i++] = 'CTRD_ID_CONTRATO_DESPESA';
        $campos[$i++] = 'CTRD_NR_CONTRATO';
        $campos[$i++] = 'CTRD_NM_EMPRESA_CONTRATADA';
        $campos[$i++] = 'CTRD_CPFCNPJ_DESPESA';
        $campos[$i++] = 'CTRD_DT_INICIO_VIGENCIA';
        $campos[$i++] = 'CTRD_DT_TERMINO_VIGENCIA';
        $campos[$i++] = 'CTRD_VL_DESPESA';
        $campos[$i++] = 'VL_DESPESA_BASE_EXERC_ANTERIOR';
        $campos[$i++] = 'VL_DESPESA_BASE_PERCENTUAL';
        $campos[$i++] = 'VL_DESPESA_BASE_DIFERENCA';
        $campos[$i++] = 'VL_DESPESA_BASE_EXERC_ATUAL';
        $campos[$i++] = 'VL_DESPESA_AJUSTE_DIPLA';
        $campos[$i++] = 'VL_DESPESA_RESPONSAVEL';
        $campos[$i++] = 'VL_DESPESA_SOLIC_RESPONSAVEL';
        $campos[$i++] = 'VL_DESPESA_DIPLA';
        $campos[$i++] = 'VL_DESPESA_CONGRESSO';
        $campos[$i++] = 'VL_DESPESA_SECOR';
        $campos[$i++] = 'DESP_VL_MAX_MENSAL_AUTORIZADO';
        $campos[$i++] = 'VL_DINHEIRO_RESPONSAVEL';
        $campos[$i++] = 'VL_DINHEIRO_DIPLA';
        $campos[$i++] = 'VL_DINHEIRO_CONGRESSO';
        $campos[$i++] = 'VL_DINHEIRO_SECOR';
        $campos[$i++] = 'VL_DINHEIRO_MENSAL_AUTORIZADO';
        $campos[$i++] = 'VL_NUMERO_RESPONSAVEL';
        $campos[$i++] = 'VL_NUMERO_DIPLA';
        $campos[$i++] = 'VL_NUMERO_CONGRESSO';
        $campos[$i++] = 'VL_NUMERO_SECOR';
        $campos[$i++] = 'VL_NUMERO_MENSAL_AUTORIZADO';
        $campos[$i++] = 'DESP_NR_COPIA_DESPESA';
        $campos[$i++] = 'FANE_ID_FASE_EXERCICIO';
        $campos[$i++] = 'FASE_NM_FASE_EXERCICIO';

        return $campos;
    }

    /**
     * Retorna array contendo todos os campos das despesas ativas no banco
     *
     * @param   int     $despesa
     * @return  array
     * @author  Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function retornaDadosCompletos() {
        // Verifica existência dos dados em cache
        $cache = new Trf1_Orcamento_Cache();
        $cacheId = $cache->gerarID_Listagem('despesa');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            //Não existindo o cache, busca do banco
            $sql = $this->_retornaQueryCompleta();

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchAll($sql);

            $cache->criarCache($dados, $cacheId);
        }

        return $dados;
    }

    /**
     * Realiza a exclusão lógica de uma unidade gestora
     *
     * @param   array   $chaves             Array de chaves primárias para exclusão de um ou mais registros
     * @return  none
     * @author  Dayane Freire / Robson Pereira
     */
    public function exclusaoLogica($chaves) {
        $despesas = implode(', ', $chaves);

        $sessao = new Zend_Session_Namespace('userNs');

        // Exclui um ou mais registros
        $sql = "
UPDATE
    CEO_TB_DESP_DESPESA
SET
    DESP_CD_MATRICULA_EXCLUSAO                  = '$sessao->matricula',
    DESP_DH_EXCLUSAO_LOGICA                     = SYSDATE,
    DESP_NR_COPIA_DESPESA                       = NULL
WHERE
    DESP_NR_DESPESA                             IN ($despesas)      AND
    DESP_DH_EXCLUSAO_LOGICA                     IS NULL
    ";

        $contrato = "DELETE FROM CEO_TB_CTRD_CONTRATO_DESPESA WHERE CTRD_NR_DESPESA IN ($despesas)";

        $banco = Zend_Db_Table::getDefaultAdapter();

        $banco->query($sql);
        $banco->query($contrato);
    }

    public function insereValoresDespesa($despesa, $valores) {
        $formatoBanco = new Trf1_Orcamento_Valor();

        $valores = array_filter($valores);

        if (!empty($valores)) {

            $sql = "BEGIN " . PHP_EOL;

            foreach ($valores as $demandante => $valor) {
                $valor = $formatoBanco->retornaValorParaBancoRod($valor);

                if ($valor == null || $valor == '') {
                    // não grava registro com valor nulo!
                } else {
                    $sql .= "INSERT INTO ";
                    $sql .= "CEO_TB_VLDE_VALOR_DESPESA ";
                    $sql .= "( ";
                    $sql .= "VLDE_NR_DESPESA, ";
                    $sql .= "VLDE_CD_DEMANDANTE, ";
                    $sql .= "VLDE_VL_DESPESA, ";
                    $sql .= "VLDE_DH_DESPESA ";
                    $sql .= ") VALUES (";
                    $sql .= "$despesa, ";
                    $sql .= "$demandante, ";
                    $sql .= "TO_NUMBER($valor), ";
                    $sql .= "SYSDATE ";
                    $sql .= "); ";
                    $sql .= PHP_EOL;
                }
            }

            $sql .= "END; " . PHP_EOL;

            $banco = Zend_Db_Table::getDefaultAdapter();
            $banco->query($sql);
        }
    }

    public function editaValoresDespesa($despesa, $valores) {

        $formatoBanco = new Trf1_Orcamento_Valor();
        $banco = Zend_Db_Table::getDefaultAdapter();

        try {

            $banco->beginTransaction();

            foreach ($valores as $demandante => $valor) {

                $sqlCount = "SELECT CASE COUNT(*) "
                    . "WHEN 0 THEN 0 ELSE 1 END AS QTDE_REGISTROS "
                    . "FROM CEO_TB_VLDE_VALOR_DESPESA "
                    . "WHERE VLDE_NR_DESPESA = $despesa AND VLDE_CD_DEMANDANTE = $demandante";

                $qtde = $banco->fetchOne($sqlCount);

                // Converte o valor para o formato do banco Oracle
                $vlde_vl = $formatoBanco->formataMoedaBanco($valor);

                $sqlCondicional = "UPDATE CEO_TB_VLDE_VALOR_DESPESA SET VLDE_VL_DESPESA = $vlde_vl "
                    . "WHERE VLDE_NR_DESPESA = $despesa "
                    . "AND VLDE_CD_DEMANDANTE = $demandante";

                if ($qtde == 0) {

                    $sqlCondicional = "INSERT INTO CEO_TB_VLDE_VALOR_DESPESA "
                        . "(VLDE_NR_DESPESA, VLDE_CD_DEMANDANTE, VLDE_VL_DESPESA, VLDE_DH_DESPESA) "
                        . "VALUES ($despesa, $demandante, $vlde_vl, SYSDATE)";
                }

                $banco->query($sqlCondicional);
            }

            return $banco->commit();
        } catch (Exception $ex) {

            $banco->rollBack();
            throw $ex;
        }
    }

    public function ListagemDistribuicao() {
        $sql = "
SELECT
    DESP_NR_DESPESA,
    DESP_DS_ADICIONAL,
    DESP_CD_UG,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
    SG_FAMILIA_RESPONSAVEL,
    NOEM1.NOEM_CD_NOTA_EMPENHO,
    NOEM1.NOEM_DS_OBSERVACAO,
    VR_PROPOSTA_SECOR,
    VR_PROPOSTA_REMANEJADA,
    VR_PROPOSTA_RECEBIDA,
    VR_CREDITO_ADICIONAL,
    VR_CREDITO_EXTRA,
    VR_ALTERACAO_QDD,
    VR_CREDITO_SAIDA,
    VR_CREDITO_DESTAQUE,
    VR_MOVIMENTACAO,
    VR_A_RECEBER,
    VR_RDO,
    VR_EMPENHADO,
    VR_EXECUTADO,

    /* Campos calculados */
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA -
    VR_PROPOSTA_RECEBIDA                                            AS VR_PROPOSTA_A_RECEBER,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO                                                 AS VR_TOTAL_DESPESA,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO +
    VR_A_RECEBER                                                    AS VR_SUB_TOTAL,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO -
    VR_RDO                                                          AS VR_A_AUTORIZAR,

    VR_RDO -
    VR_EMPENHADO                                                    AS VR_A_EMPENHAR,

    VR_EMPENHADO -
    VR_EXECUTADO                                                    AS VR_A_EXECUTAR


FROM (



SELECT
    /* Campos vindos do banco, sem os cÃ¡lculos */
    DESP_NR_DESPESA,
    DESP_CD_UG,
    DESP_DS_ADICIONAL,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
    SG_FAMILIA_RESPONSAVEL,
    VR_PROPOSTA_SECOR,
    VR_PROPOSTA_REMANEJADA,
    VR_PROPOSTA_RECEBIDA,
    VR_CREDITO_ADICIONAL,
    VR_CREDITO_EXTRA,
    VR_ALTERACAO_QDD,
    VR_CREDITO_SAIDA,
    VR_CREDITO_DESTAQUE,
    VR_MOVIMENTACAO,
    VR_A_RECEBER,
    VR_RDO,
    VR_EMPENHADO,
    VR_EXECUTADO,

    /* Campos calculados */
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA -
    VR_PROPOSTA_RECEBIDA                                            AS VR_PROPOSTA_A_RECEBER,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO                                                 AS VR_TOTAL_DESPESA,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO +
    VR_A_RECEBER                                                    AS VR_SUB_TOTAL,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO -
    VR_RDO                                                          AS VR_A_AUTORIZAR,

    VR_RDO -
    VR_EMPENHADO                                                    AS VR_A_EMPENHAR,

    VR_EMPENHADO -
    VR_EXECUTADO                                                    AS VR_A_EXECUTAR


FROM
    (
    SELECT
     DESP_NR_DESPESA,
    DESP_CD_UG,
    DESP_DS_ADICIONAL,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
     RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_FAMILIA_RESPONSAVEL,
        NVL(VLD4.VLDE_VL_DESPESA, 0)                                AS VR_PROPOSTA_SECOR,
        NVL(MOVP.VALOR, 0)                                          AS VR_PROPOSTA_REMANEJADA,
        NVL(TNPP.VALOR, 0)                                          AS VR_PROPOSTA_RECEBIDA,
        NVL(TNPA.VALOR, 0)                                          AS VR_CREDITO_ADICIONAL,
        NVL(TNPE.VALOR, 0)                                          AS VR_CREDITO_EXTRA,
        /*
        TODO: HÃ¡ erro nessa instruÃ§Ã£o sql sobre Tipo NC = Q.
        Ver origem e destino deste tipo de NC
        */
        NVL(TNPQ.VALOR, 0)                                          AS VR_ALTERACAO_QDD,
        NVL(TNPS.VALOR, 0)                                          AS VR_CREDITO_SAIDA,
        NVL(TNPT.VALOR, 0)                                          AS VR_CREDITO_DESTAQUE,
        NVL(MOVC.VALOR, 0)                                          AS VR_MOVIMENTACAO,
        NVL(0, 0)                                                   AS VR_A_RECEBER,
        NVL(REQV.VALOR, 0)                                          AS VR_RDO,
        NVL(NOEM.VALOR, 0)                                          AS VR_EMPENHADO,
        NVL(EXEC.VALOR, 0)                                          AS VR_EXECUTADO

        /*
        -- Utilizados na projeÃ§Ã£o
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_EXECUTADO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_MES_ATUAL,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_PROJETADO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_TOTAL_NECESSARIO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS SITUACAO_ATUAL,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS STATUS
        */
    FROM
        CEO_TB_DESP_DESPESA                 DESP

    /* Valor aprovado pela SECOR */
    Left JOIN
        CEO_TB_VLDE_VALOR_DESPESA           VLD4 ON
            VLD4.VLDE_NR_DESPESA            = DESP.DESP_NR_DESPESA  AND
            VLD4.VLDE_CD_DEMANDANTE         = 4
              Left JOIN
                       CEO_TB_RESP_RESPONSAVEL                 RESP ON
                        RESP.RESP_CD_RESPONSAVEL            = DESP.DESP_CD_RESPONSAVEL
                    Left JOIN
                       RH_CENTRAL_LOTACAO                      RHCL ON
                        RHCL.LOTA_COD_LOTACAO               = RESP.RESP_CD_LOTACAO                  AND
                        RHCL.LOTA_SIGLA_SECAO               = RESP.RESP_DS_SECAO

    /* Notas de crÃ©dito por tipo */
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
   LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP.DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'P'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPP ON
            TNPP.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'A'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPA ON
            TNPA.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
    LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'E'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPE ON
            TNPE.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
    LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'Q'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPQ ON
            TNPQ.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'S'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPS ON
            TNPS.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'T'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPT ON
            TNPT.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* MovimentaÃ§Ãµes de crÃ©dito */
    Left JOIN
        (
SELECT
    DESPESA                         AS MOVC_NR_DESPESA,
    SUM(VALOR)                      AS VALOR
FROM
    (
    SELECT
        MOVC_NR_DESPESA_ORIGEM      AS DESPESA,
        MOVC_VL_MOVIMENTACAO * (-1) AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
     LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_ORIGEM
    WHERE
        /* MOVC_CD_TIPO_MOVIMENTACAO    = 2 AND */
        MOVC_CD_TIPO_SOLICITACAO    = 2 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL

    UNION ALL

    SELECT
        MOVC_NR_DESPESA_DESTINO     AS DESPESA,
        MOVC_VL_MOVIMENTACAO        AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
      LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO

    WHERE
        /* MOVC_CD_TIPO_MOVIMENTACAO    = 2 AND */
        MOVC_CD_TIPO_SOLICITACAO    = 2 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL
    )                               M
GROUP BY
    M.DESPESA
                )   MOVC ON
            MOVC.MOVC_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    DESPESA                         AS MOVC_NR_DESPESA,
    SUM(VALOR)                      AS VALOR
FROM
    (
    SELECT
        MOVC_NR_DESPESA_ORIGEM      AS DESPESA,
        MOVC_VL_MOVIMENTACAO * (-1) AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO
    WHERE
        MOVC_CD_TIPO_SOLICITACAO    = 3 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL

    UNION ALL

    SELECT
        MOVC_NR_DESPESA_DESTINO     AS DESPESA,
        MOVC_VL_MOVIMENTACAO        AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO
    WHERE
        MOVC_CD_TIPO_SOLICITACAO    = 3 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL
    )                               M
GROUP BY
    M.DESPESA
                )   MOVP ON
            MOVP.MOVC_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* RDOs */
    Left JOIN
        (
SELECT
    REQV_NR_DESPESA,
    SUM(VALOR) AS VALOR
FROM
    (
    SELECT
        REQV_NR_DESPESA,
        REQV_IC_TP_VARIACAO,
        CASE
            WHEN REQV_IC_TP_VARIACAO = 0 THEN REQV_VL_VARIACAO
            WHEN REQV_IC_TP_VARIACAO = 1 THEN REQV_VL_VARIACAO * (-1)
        END AS VALOR
    FROM
        CEO_TB_REQV_REQU_VARIACAO
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  REQV_NR_DESPESA
    WHERE
        REQV_DH_EXCLUSAO_LOGICA IS NULL
    )
GROUP BY
    REQV_NR_DESPESA
                )           REQV ON
            REQV.REQV_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* Empenhos */
    Left JOIN
        (
SELECT
    NOEM_NR_DESPESA,
    SUM(NOEM_VL_NE_ACERTADO)            AS VALOR
FROM
    (
    SELECT
        NOEM_NR_DESPESA,
        NOEM_VL_NE_ACERTADO
    FROM
        CEO_TB_NOEM_NOTA_EMPENHO
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOEM_NR_DESPESA
    WHERE
        NOEM_CD_NE_REFERENCIA IS NULL

    UNION ALL

    SELECT
         NOEM_NR_DESPESA,
        NOEM_VL_NE_ACERTADO
    FROM
        CEO_TB_NOEM_NOTA_EMPENHO
     LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP.DESP_NR_DESPESA =  NOEM_NR_DESPESA
    WHERE
        NOEM_CD_NE_REFERENCIA IS NOT NULL   AND
        NOEM_CD_NE_REFERENCIA IN (  SELECT
                                        NOEM_CD_NOTA_EMPENHO
                                    FROM
                                        CEO_TB_NOEM_NOTA_EMPENHO
                                       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
                                     DESP_NR_DESPESA =  NOEM_NR_DESPESA
                                    WHERE
                                        NOEM_NR_DESPESA = DESP_NR_DESPESA
                                )
    )
GROUP BY
    NOEM_NR_DESPESA
                )               NOEM ON
            NOEM.NOEM_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
     NOEM.NOEM_NR_DESPESA            AS EXEC_NR_DESPESA,
    SUM(EXEC_VL_EXECUCAO)   AS VALOR
FROM
    CEO_TB_EXEC_EXECUCAO_NE
   LEFT JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM ON
   NOEM.NOEM_CD_NOTA_EMPENHO = EXEC_CD_NOTA_EMPENHO
WHERE
    EXEC_CD_NOTA_EMPENHO IN (
        SELECT
            NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO
             LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
             DESP_NR_DESPESA =  NOEM_NR_DESPESA
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NULL

        UNION ALL

        SELECT
            NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NOT NULL   AND
            NOEM_CD_NE_REFERENCIA   IN (
                SELECT
                    NOEM_CD_NOTA_EMPENHO
                FROM
                    CEO_TB_NOEM_NOTA_EMPENHO
                   LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
             DESP_NR_DESPESA =  NOEM_NR_DESPESA
                WHERE
                    NOEM_CD_NE_REFERENCIA   IS NULL)
)
GROUP BY
   NOEM.NOEM_NR_DESPESA
                )               EXEC ON
            EXEC.EXEC_NR_DESPESA            = DESP.DESP_NR_DESPESA
    WHERE
        DESP.DESP_DH_EXCLUSAO_LOGICA        IS NULL

 )                                       BASE

            )
            LEFT JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM1 ON
            NOEM1.NOEM_NR_DESPESA = DESP_NR_DESPESA
            AND NOEM1.NOEM_CD_EVENTO = 401091
            WHERE DESP_NR_DESPESA IS NOT NULL";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function ListagemEmpenho() {
        $sql = "
SELECT
    DESP_NR_DESPESA,
    DESP_DS_ADICIONAL,
    DESP_CD_UG,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
    SG_FAMILIA_RESPONSAVEL,
    NOEM1.NOEM_CD_NOTA_EMPENHO,
    NOEM1.NOEM_DS_OBSERVACAO,
    VR_PROPOSTA_SECOR,
    VR_PROPOSTA_REMANEJADA,
    VR_PROPOSTA_RECEBIDA,
    VR_CREDITO_ADICIONAL,
    VR_CREDITO_EXTRA,
    VR_ALTERACAO_QDD,
    VR_CREDITO_SAIDA,
    VR_CREDITO_DESTAQUE,
    VR_MOVIMENTACAO,
    VR_A_RECEBER,
    VR_RDO,
    VR_EMPENHADO,
    VR_EXECUTADO,

    /* Campos calculados */
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA -
    VR_PROPOSTA_RECEBIDA                                            AS VR_PROPOSTA_A_RECEBER,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO                                                 AS VR_TOTAL_DESPESA,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO +
    VR_A_RECEBER                                                    AS VR_SUB_TOTAL,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO -
    VR_RDO                                                          AS VR_A_AUTORIZAR,

    VR_RDO -
    VR_EMPENHADO                                                    AS VR_A_EMPENHAR,

    VR_EMPENHADO -
    VR_EXECUTADO                                                    AS VR_A_EXECUTAR


FROM (



SELECT
    /* Campos vindos do banco, sem os cÃ¡lculos */
    DESP_NR_DESPESA,
    DESP_CD_UG,
    DESP_DS_ADICIONAL,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
    SG_FAMILIA_RESPONSAVEL,
    VR_PROPOSTA_SECOR,
    VR_PROPOSTA_REMANEJADA,
    VR_PROPOSTA_RECEBIDA,
    VR_CREDITO_ADICIONAL,
    VR_CREDITO_EXTRA,
    VR_ALTERACAO_QDD,
    VR_CREDITO_SAIDA,
    VR_CREDITO_DESTAQUE,
    VR_MOVIMENTACAO,
    VR_A_RECEBER,
    VR_RDO,
    VR_EMPENHADO,
    VR_EXECUTADO,

    /* Campos calculados */
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA -
    VR_PROPOSTA_RECEBIDA                                            AS VR_PROPOSTA_A_RECEBER,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO                                                 AS VR_TOTAL_DESPESA,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO +
    VR_A_RECEBER                                                    AS VR_SUB_TOTAL,

    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO -
    VR_RDO                                                          AS VR_A_AUTORIZAR,

    VR_RDO -
    VR_EMPENHADO                                                    AS VR_A_EMPENHAR,

    VR_EMPENHADO -
    VR_EXECUTADO                                                    AS VR_A_EXECUTAR


FROM
    (
    SELECT
     DESP_NR_DESPESA,
    DESP_CD_UG,
    DESP_DS_ADICIONAL,
    DESP_CD_PT_RESUMIDO,
    DESP_CD_ELEMENTO_DESPESA_SUB,
    DESP_CD_FONTE,
     RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS SG_FAMILIA_RESPONSAVEL,
        NVL(VLD4.VLDE_VL_DESPESA, 0)                                AS VR_PROPOSTA_SECOR,
        NVL(MOVP.VALOR, 0)                                          AS VR_PROPOSTA_REMANEJADA,
        NVL(TNPP.VALOR, 0)                                          AS VR_PROPOSTA_RECEBIDA,
        NVL(TNPA.VALOR, 0)                                          AS VR_CREDITO_ADICIONAL,
        NVL(TNPE.VALOR, 0)                                          AS VR_CREDITO_EXTRA,
        /*
        TODO: HÃ¡ erro nessa instruÃ§Ã£o sql sobre Tipo NC = Q.
        Ver origem e destino deste tipo de NC
        */
        NVL(TNPQ.VALOR, 0)                                          AS VR_ALTERACAO_QDD,
        NVL(TNPS.VALOR, 0)                                          AS VR_CREDITO_SAIDA,
        NVL(TNPT.VALOR, 0)                                          AS VR_CREDITO_DESTAQUE,
        NVL(MOVC.VALOR, 0)                                          AS VR_MOVIMENTACAO,
        NVL(0, 0)                                                   AS VR_A_RECEBER,
        NVL(REQV.VALOR, 0)                                          AS VR_RDO,
        NVL(NOEM.VALOR, 0)                                          AS VR_EMPENHADO,
        NVL(EXEC.VALOR, 0)                                          AS VR_EXECUTADO

        /*
        -- Utilizados na projeÃ§Ã£o
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_EXECUTADO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_MES_ATUAL,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_PROJETADO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS VR_TOTAL_NECESSARIO,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS SITUACAO_ATUAL,
        TO_CHAR(NVL(0, 0), 'FM9G999G999G999G999G999G990D00')        AS STATUS
        */
    FROM
        CEO_TB_DESP_DESPESA                 DESP

    /* Valor aprovado pela SECOR */
    Left JOIN
        CEO_TB_VLDE_VALOR_DESPESA           VLD4 ON
            VLD4.VLDE_NR_DESPESA            = DESP.DESP_NR_DESPESA  AND
            VLD4.VLDE_CD_DEMANDANTE         = 4
              Left JOIN
                       CEO_TB_RESP_RESPONSAVEL                 RESP ON
                        RESP.RESP_CD_RESPONSAVEL            = DESP.DESP_CD_RESPONSAVEL
                    Left JOIN
                       RH_CENTRAL_LOTACAO                      RHCL ON
                        RHCL.LOTA_COD_LOTACAO               = RESP.RESP_CD_LOTACAO                  AND
                        RHCL.LOTA_SIGLA_SECAO               = RESP.RESP_DS_SECAO

    /* Notas de crÃ©dito por tipo */
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
   LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP.DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'P'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPP ON
            TNPP.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'A'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPA ON
            TNPA.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
    LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'E'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPE ON
            TNPE.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
    LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'Q'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPQ ON
            TNPQ.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'S'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPS ON
            TNPS.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    NOCR_NR_DESPESA,
    SUM(NOCR_VL_NC_ACERTADO)    VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
 LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOCR_NR_DESPESA
WHERE
    NOCR_CD_TIPO_NC = 'T'
GROUP BY
    NOCR_NR_DESPESA,
    NOCR_CD_TIPO_NC
                )   TNPT ON
            TNPT.NOCR_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* MovimentaÃ§Ãµes de crÃ©dito */
    Left JOIN
        (
SELECT
    DESPESA                         AS MOVC_NR_DESPESA,
    SUM(VALOR)                      AS VALOR
FROM
    (
    SELECT
        MOVC_NR_DESPESA_ORIGEM      AS DESPESA,
        MOVC_VL_MOVIMENTACAO * (-1) AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
     LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_ORIGEM
    WHERE
        /* MOVC_CD_TIPO_MOVIMENTACAO    = 2 AND */
        MOVC_CD_TIPO_SOLICITACAO    = 2 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL

    UNION ALL

    SELECT
        MOVC_NR_DESPESA_DESTINO     AS DESPESA,
        MOVC_VL_MOVIMENTACAO        AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
      LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO

    WHERE
        /* MOVC_CD_TIPO_MOVIMENTACAO    = 2 AND */
        MOVC_CD_TIPO_SOLICITACAO    = 2 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL
    )                               M
GROUP BY
    M.DESPESA
                )   MOVC ON
            MOVC.MOVC_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
    DESPESA                         AS MOVC_NR_DESPESA,
    SUM(VALOR)                      AS VALOR
FROM
    (
    SELECT
        MOVC_NR_DESPESA_ORIGEM      AS DESPESA,
        MOVC_VL_MOVIMENTACAO * (-1) AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO
    WHERE
        MOVC_CD_TIPO_SOLICITACAO    = 3 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL

    UNION ALL

    SELECT
        MOVC_NR_DESPESA_DESTINO     AS DESPESA,
        MOVC_VL_MOVIMENTACAO        AS VALOR
    FROM
        CEO_TB_MOVC_MOVIMENTACAO_CRED
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  MOVC_NR_DESPESA_DESTINO
    WHERE
        MOVC_CD_TIPO_SOLICITACAO    = 3 AND
        MOVC_DH_EXCLUSAO_LOGICA     IS NULL
    )                               M
GROUP BY
    M.DESPESA
                )   MOVP ON
            MOVP.MOVC_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* RDOs */
    Left JOIN
        (
SELECT
    REQV_NR_DESPESA,
    SUM(VALOR) AS VALOR
FROM
    (
    SELECT
        REQV_NR_DESPESA,
        REQV_IC_TP_VARIACAO,
        CASE
            WHEN REQV_IC_TP_VARIACAO = 0 THEN REQV_VL_VARIACAO
            WHEN REQV_IC_TP_VARIACAO = 1 THEN REQV_VL_VARIACAO * (-1)
        END AS VALOR
    FROM
        CEO_TB_REQV_REQU_VARIACAO
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  REQV_NR_DESPESA
    WHERE
        REQV_DH_EXCLUSAO_LOGICA IS NULL
    )
GROUP BY
    REQV_NR_DESPESA
                )           REQV ON
            REQV.REQV_NR_DESPESA            = DESP.DESP_NR_DESPESA

    /* Empenhos */
    Left JOIN
        (
SELECT
    NOEM_NR_DESPESA,
    SUM(NOEM_VL_NE_ACERTADO)            AS VALOR
FROM
    (
    SELECT
        NOEM_NR_DESPESA,
        NOEM_VL_NE_ACERTADO
    FROM
        CEO_TB_NOEM_NOTA_EMPENHO
       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP_NR_DESPESA =  NOEM_NR_DESPESA
    WHERE
        NOEM_CD_NE_REFERENCIA IS NULL

    UNION ALL

    SELECT
         NOEM_NR_DESPESA,
        NOEM_VL_NE_ACERTADO
    FROM
        CEO_TB_NOEM_NOTA_EMPENHO
     LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
   DESP.DESP_NR_DESPESA =  NOEM_NR_DESPESA
    WHERE
        NOEM_CD_NE_REFERENCIA IS NOT NULL   AND
        NOEM_CD_NE_REFERENCIA IN (  SELECT
                                        NOEM_CD_NOTA_EMPENHO
                                    FROM
                                        CEO_TB_NOEM_NOTA_EMPENHO
                                       LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
                                     DESP_NR_DESPESA =  NOEM_NR_DESPESA
                                    WHERE
                                        NOEM_NR_DESPESA = DESP_NR_DESPESA
                                )
    )
GROUP BY
    NOEM_NR_DESPESA
                )               NOEM ON
            NOEM.NOEM_NR_DESPESA            = DESP.DESP_NR_DESPESA
    Left JOIN
        (
SELECT
     NOEM.NOEM_NR_DESPESA            AS EXEC_NR_DESPESA,
    SUM(EXEC_VL_EXECUCAO)   AS VALOR
FROM
    CEO_TB_EXEC_EXECUCAO_NE
   LEFT JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM ON
   NOEM.NOEM_CD_NOTA_EMPENHO = EXEC_CD_NOTA_EMPENHO
WHERE
    EXEC_CD_NOTA_EMPENHO IN (
        SELECT
            NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO
             LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
             DESP_NR_DESPESA =  NOEM_NR_DESPESA
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NULL

        UNION ALL

        SELECT
            NOEM_CD_NOTA_EMPENHO
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NOT NULL   AND
            NOEM_CD_NE_REFERENCIA   IN (
                SELECT
                    NOEM_CD_NOTA_EMPENHO
                FROM
                    CEO_TB_NOEM_NOTA_EMPENHO
                   LEFT JOIN CEO_TB_DESP_DESPESA DESP ON
             DESP_NR_DESPESA =  NOEM_NR_DESPESA
                WHERE
                    NOEM_CD_NE_REFERENCIA   IS NULL)
)
GROUP BY
   NOEM.NOEM_NR_DESPESA
                )               EXEC ON
            EXEC.EXEC_NR_DESPESA            = DESP.DESP_NR_DESPESA
    WHERE
        DESP.DESP_DH_EXCLUSAO_LOGICA        IS NULL

 )                                       BASE

            )
            LEFT JOIN CEO_TB_NOEM_NOTA_EMPENHO NOEM1 ON
            NOEM1.NOEM_NR_DESPESA = DESP_NR_DESPESA
            AND NOEM1.NOEM_CD_EVENTO = 401091
            WHERE DESP_NR_DESPESA IS NOT NULL";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function empenhoPorDespesas($despesa = null) {

        $strWhere = "";
        if ($despesa) {
            $strWhere = "WHERE DESP_NR_DESPESA = $despesa";
        }

        $sql = "
SELECT
 DESP_AA_DESPESA
,UNGE_SG_SECAO
,DESP_NR_DESPESA
,DESP_DS_ADICIONAL
,PTRS_CD_PT_RESUMIDO
,EDSB_CD_ELEMENTO_DESPESA_SUB
,NOEM_CD_NOTA_EMPENHO
,NOEM_DS_OBSERVACAO
,NOEM_VL_NE
,NOEM_VL_NE_ACERTADO
,(
    SELECT
    (
        SUM(

            EXEC_VL_JANEIRO +
            EXEC_VL_FEVEREIRO +
            EXEC_VL_MARCO +
            EXEC_VL_ABRIL +
            EXEC_VL_MAIO +
            EXEC_VL_JUNHO +
            EXEC_VL_JULHO +
            EXEC_VL_AGOSTO +
            EXEC_VL_SETEMBRO +
            EXEC_VL_OUTUBRO +
            EXEC_VL_NOVEMBRO +
            EXEC_VL_DEZEMBRO
        )
    )
    FROM CEO_TB_EXEC_EXECUCAO_NE EXEC
    WHERE
        NOEM.NOEM_CD_NOTA_EMPENHO = EXEC.EXEC_CD_NOTA_EMPENHO AND
        NOEM.noem_cd_ug_operador = EXEC.exec_cd_ug

) AS VL_EXECUTADO

, NOEM_VL_NE_ACERTADO - (
    SELECT
    (
        SUM(

            EXEC_VL_JANEIRO +
            EXEC_VL_FEVEREIRO +
            EXEC_VL_MARCO +
            EXEC_VL_ABRIL +
            EXEC_VL_MAIO +
            EXEC_VL_JUNHO +
            EXEC_VL_JULHO +
            EXEC_VL_AGOSTO +
            EXEC_VL_SETEMBRO +
            EXEC_VL_OUTUBRO +
            EXEC_VL_NOVEMBRO +
            EXEC_VL_DEZEMBRO
        )
    )
    FROM CEO_TB_EXEC_EXECUCAO_NE EXEC
    WHERE
        NOEM.NOEM_CD_NOTA_EMPENHO = EXEC.EXEC_CD_NOTA_EMPENHO AND
        NOEM.noem_cd_ug_operador = EXEC.exec_cd_ug

)  AS VL_SALDO

FROM CEO.CEO_TB_DESP_DESPESA DESP

LEFT JOIN CEO.CEO_TB_NOEM_NOTA_EMPENHO      NOEM ON
          DESP.DESP_NR_DESPESA = NOEM.NOEM_NR_DESPESA
LEFT JOIN CEO_TB_UNGE_UNIDADE_GESTORA       UNGE ON
          UNGE.UNGE_CD_UG = DESP.DESP_CD_UG
LEFT JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO     PTRS ON
          PTRS.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
LEFT JOIN CEO_TB_EDSB_ELEMENTO_SUB_DESP     EDSB ON
          EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB   = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
INNER JOIN CEO_TB_EXEC_EXECUCAO_NE           EXEC ON
   NOEM.NOEM_CD_NOTA_EMPENHO = EXEC.EXEC_CD_NOTA_EMPENHO

$strWhere


";
        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchAll($sql);
    }

    public function retornaDespesaPorExercicio($despesa, $exercicio) {
        $faseProposta = Orcamento_Business_Dados::FASE_EXERCICIO_LIBERADA; // 4
        $faseExecucao = Orcamento_Business_Dados::FASE_EXERCICIO_EXECUCAO; // 5

        $sql = "
    SELECT
        DESP_NR_DESPESA,
        DESP_AA_DESPESA,
        FASE_ID_FASE_EXERCICIO,
        FASE_NM_FASE_EXERCICIO

    FROM CEO.CEO_TB_DESP_DESPESA DESP

    INNER JOIN CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO FANE ON
        FANE.FANE_NR_ANO = DESP.DESP_AA_DESPESA

    INNER JOIN CEO.CEO_TB_FASE_FASE_EXERCICIO FASE ON
        FASE.FASE_ID_FASE_EXERCICIO = FANE.FANE_ID_FASE_EXERCICIO

    INNER JOIN CEO.CEO_TB_ANOE_ANO_EXERCICIO ANOE ON
        ANOE.ANOE_AA_ANO = DESP.DESP_AA_DESPESA

    WHERE
        DESP_NR_DESPESA = '$despesa' AND
        DESP_AA_DESPESA = '$exercicio' AND
        FASE_ID_FASE_EXERCICIO NOT IN ($faseProposta, $faseExecucao) AND
        DESP_CD_MATRICULA_EXCLUSAO IS NULL
";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->fetchRow($sql);
    }

    /**
     * Realiza a exclusão lógica de uma unidade gestora
     *
     * @param   array   $chaves             Array de chaves primárias para exclusão de um ou mais registros
     * @return  none
     * @author  Dayane Freire / Robson Pereira
     */
    public function exclusaoLogicaporAno($despesa, $exercicio) {

        $sessao = new Zend_Session_Namespace('userNs');

        // Exclui um ou mais registros
        $sql = "
        UPDATE
            CEO_TB_DESP_DESPESA
        SET
            DESP_CD_MATRICULA_EXCLUSAO                  = '$sessao->matricula',
            DESP_DH_EXCLUSAO_LOGICA                     = SYSDATE
        WHERE
            DESP_NR_DESPESA                             = '$despesa'   AND
            DESP_AA_DESPESA                             = '$exercicio' AND
            DESP_DH_EXCLUSAO_LOGICA                     IS NULL
        ";

        $banco = Zend_Db_Table::getDefaultAdapter();
        return $banco->query($sql);
    }

}
