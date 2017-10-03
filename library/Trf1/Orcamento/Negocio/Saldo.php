<?php

/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Saldo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 *
 * TRF1, Classe negocial sobre Orçamento - Saldo
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
class Trf1_Orcamento_Negocio_Saldo {

    /**
     * Classe construtora
     *
     * @param	none
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct () {
        //
    }

    /**
     * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
     *
     * @return	array		Chave primária ou composta
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function chavePrimaria () {
        return array('NR_DESPESA');
    }

    /**
     * Retorna o saldo de uma única despesa informada
     *
     * @param	int	| array  $despesa
     * @return	array
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSaldo ($despesa) {
        if (!$despesa) {
            throw new Exception('Código da despesa é obrigatório');
        }

        $sql = $this->_retornaQueryCompleta($despesa);


        $banco = Zend_Db_Table::getDefaultAdapter();

        $qtde = count($despesa);

        if ($qtde == 1) {
            $dados = $banco->fetchRow($sql);
        } else {
            $dados = $banco->fetchAll($sql);
        }

        return $dados;
    }

    public function retornaListagem () {
        $sql = $this->_retornaQueryCompleta();

        $banco = Zend_Db_Table::getDefaultAdapter();

        return $banco->fetchAll($sql);
    }

    /**
     * Retorna array com campos e registros desejados
     *
     * @param	int	| array  $despesa
     * @return	array
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function _retornaQueryCompleta ($despesa = null, $faseAjuste = null) {

        if($faseAjuste){
            $demandante = Trf1_Orcamento_Dados::DEMANDANTE_CONGRESSO_NACIONAL;
        }else{
            $demandante = Trf1_Orcamento_Dados::DEMANDANTE_SETORIAL_DIPOR ;
        }


        // Preserva o parâmetro
        $strDespesas = $despesa;

        if (is_array($despesa)) {
            // Junta numa string os valores separados por vírgula
            $strDespesas = implode(', ', $despesa);
        }

        $condicaoDespesa = "";
        if ($strDespesas) {
            $condicaoDespesa = " AND DESP.DESP_NR_DESPESA IN ( $strDespesas ) ";
        }

        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;
        // $ncTiposSaida = array ( Trf1_Orcamento_Dados::TIPO_NOTA_CREDITO_PROPOSTA, Trf1_Orcamento_Dados::TIPO_NOTA_CREDITO_ADICIONAL, Trf1_Orcamento_Dados::TIPO_NOTA_CREDITO_EXTRA, Trf1_Orcamento_Dados::TIPO_NOTA_CREDITO_SAIDA );
        $tipos [0] = Orcamento_Business_Dados::TIPO_NOTA_CREDITO_PROPOSTA;
        $tipos [1] = Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ADICIONAL;
        $tipos [2] = Orcamento_Business_Dados::TIPO_NOTA_CREDITO_EXTRA;
        $tipos [3] = Orcamento_Business_Dados::TIPO_NOTA_CREDITO_SAIDA;

        $sql = "
        SELECT DISTINCT
	/* Dados básicos da despesa */
	-- NOEM_CD_NOTA_EMPENHO,
	DESP_AA_DESPESA,
        CASE
            WHEN DESP_AA_DESPESA = ".date('Y')." THEN 1
            ELSE 2
        END AS EXERCICIO,
	DESP_CD_UG,
	NR_DESPESA,
	DESP_DS_ADICIONAL,
	SG_FAMILIA_RESPONSAVEL,
	DESP_CD_FONTE,
	DESP_CD_PT_RESUMIDO,
	UNOR_CD_UNID_ORCAMENTARIA,
    PTRS_SG_PT_RESUMIDO,
	DESP_CD_ELEMENTO_DESPESA_SUB,
	TIDE_DS_TIPO_DESPESA,
        TIDE_IC_RESERVA_RECURSO,
        DESP_NR_COPIA_DESPESA,

	VR_PROPOSTA_SECOR,
	VR_PROPOSTA_REMANEJADA,

	/* Campo calculado VR_PROPOSTA_APROVADA */
	VR_PROPOSTA_SECOR +
	VR_PROPOSTA_REMANEJADA															AS VR_PROPOSTA_APROVADA,

	VR_PROPOSTA_RECEBIDA,
	VR_CREDITO_ADICIONAL,
	VR_CREDITO_CONTINGENCIA,
        VR_CREDITO_EXTRA,
	VR_ALTERACAO_QDD,
	VR_CREDITO_SAIDA,
	VR_CREDITO_DESTAQUE,
	VR_MOVIMENTACAO,

	/* Campo calculado VR_TOTAL_DESPESA */
	VR_PROPOSTA_RECEBIDA +
	VR_CREDITO_ADICIONAL +
        VR_CREDITO_CONTINGENCIA +
	VR_CREDITO_EXTRA +
	VR_ALTERACAO_QDD +
	VR_CREDITO_SAIDA +
	VR_CREDITO_DESTAQUE	+
	VR_MOVIMENTACAO																	AS VR_TOTAL_DESPESA,

	/* Campo calculado VR_PROPOSTA_A_RECEBER */
	(VR_PROPOSTA_SECOR +
	VR_PROPOSTA_REMANEJADA) -
	VR_PROPOSTA_RECEBIDA															AS VR_PROPOSTA_A_RECEBER,

	VR_A_RECEBER,

	/* Campo calculado VR_SUB_TOTAL */
	VR_PROPOSTA_RECEBIDA +
	VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
	VR_CREDITO_EXTRA +
	VR_ALTERACAO_QDD +
	VR_CREDITO_SAIDA +
	VR_CREDITO_DESTAQUE	+
	VR_MOVIMENTACAO +
	(
	(
	VR_PROPOSTA_SECOR +
	VR_PROPOSTA_REMANEJADA
	) -
	VR_PROPOSTA_RECEBIDA
	) +
	VR_A_RECEBER																	AS VR_SUB_TOTAL,

	VR_RDO,

	/* Campo calculado VR_A_AUTORIZAR */
	VR_PROPOSTA_RECEBIDA +
	VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
	VR_CREDITO_EXTRA +
	VR_ALTERACAO_QDD +
	VR_CREDITO_SAIDA +
	VR_CREDITO_DESTAQUE +
	VR_MOVIMENTACAO -
	VR_RDO																			AS VR_A_AUTORIZAR,

	VR_EMPENHADO,

	/* Campo calculado VR_A_EMPENHAR */
	VR_RDO -
	VR_EMPENHADO																	AS VR_A_EMPENHAR,

	VR_EXECUTADO,

	/* Campo calculado VR_A_EXECUTAR */
	VR_EMPENHADO -
	VR_EXECUTADO																	AS VR_A_EXECUTAR,

    VR_PROJECAO,

    /* CAMPOS OBSOLETOS */
    /*
	VR_PROJ_FUTURA,
	VR_MES_ATUAL,
	VR_EXEC_PASSADA,
    */

	/* Campo calculado VR_SUB_TOTAL */
    /* CAMPO OBSOLETO */
    /*
	(
	VR_PROPOSTA_RECEBIDA	+
	VR_CREDITO_ADICIONAL	+
    VR_CREDITO_CONTINGENCIA +
	VR_CREDITO_EXTRA		+
	VR_ALTERACAO_QDD		+
	VR_CREDITO_SAIDA		+
	VR_CREDITO_DESTAQUE		+
	VR_MOVIMENTACAO			+
	VR_A_RECEBER
	)						-
	VR_PROJ_FUTURA			-
	VR_MES_ATUAL			-
	VR_EXEC_PASSADA																	AS VR_SITUACAO_ORCAMENTARIA
    */

    /* Campo calculado VR_SITUACAO_ORCAMENTARIA / VR_SUB_TOTAL */
    CASE WHEN
    (
    /* Campo calculado VR_SUB_TOTAL */
	VR_PROPOSTA_RECEBIDA +
	VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
	VR_CREDITO_EXTRA +
	VR_ALTERACAO_QDD +
	VR_CREDITO_SAIDA +
	VR_CREDITO_DESTAQUE	+
	VR_MOVIMENTACAO +
	(
	(
	VR_PROPOSTA_SECOR +
	VR_PROPOSTA_REMANEJADA
	) -
	VR_PROPOSTA_RECEBIDA
	) +
	VR_A_RECEBER
    ) = 0 THEN 0
	ELSE
    (
    /* Campo calculado VR_SUB_TOTAL */
	(
    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE	+
    VR_MOVIMENTACAO +
    (
    (
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA
    ) -
    VR_PROPOSTA_RECEBIDA
    ) +
    VR_A_RECEBER
    ) - VR_PROJECAO
	) /
    (
    (
    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE	+
    VR_MOVIMENTACAO +
    (
    (
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA
    ) -
    VR_PROPOSTA_RECEBIDA
    ) +
    VR_A_RECEBER
    )
    ) END                                                                           AS VR_PERCENTUAL_REAJUSTE,

    /* Campo calculado VR_SUB_TOTAL */
	(
    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE	+
    VR_MOVIMENTACAO +
    (
    (
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA
    ) -
    VR_PROPOSTA_RECEBIDA
    ) +
    VR_A_RECEBER
    ) - VR_PROJECAO                                                                 AS VR_SITUACAO_ORCAMENTARIA,

    /* Campo (exibido na funcionalidade de despesa) Base do exercício anterior */
    (
    VR_PROPOSTA_SECOR +
	VR_PROPOSTA_REMANEJADA
    ) +
    VR_CREDITO_ADICIONAL +
    VR_MOVIMENTACAO                                                                 AS VR_BASE_EXERCICIO,

    DESP_CD_CATEGORIA,
    DESP_CD_VINCULACAO,
    -- DESP_CD_TIPO_RECURSO,
    TREC_DS_TIPO_RECURSO,
    -- DESP_CD_TIPO_ORCAMENTO,
    PORC_DS_TIPO_ORCAMENTO,
    -- DESP_CD_OBJETIVO,
    POBJ_DS_OBJETIVO,
    -- DESP_CD_PROGRAMA,
    PPRG_DS_PROGRAMA,
    -- DESP_CD_TIPO_OPERACIONAL,
    POPE_DS_TIPO_OPERACIONAL,
	EXEC_VR_JANEIRO,
    EXEC_VR_FEVEREIRO,
    EXEC_VR_MARCO,
    EXEC_VR_ABRIL,
    EXEC_VR_MAIO,
    EXEC_VR_JUNHO,
    EXEC_VR_JULHO,
    EXEC_VR_AGOSTO,
    EXEC_VR_SETEMBRO,
    EXEC_VR_OUTUBRO,
    EXEC_VR_NOVEMBRO,
    EXEC_VR_DEZEMBRO,
    VR_EXECUTADO AS VR_TOTAL_EXECUTADO,

    /* Campo calculado VR_SUB_TOTAL */
    VR_PROPOSTA_RECEBIDA +
    VR_CREDITO_ADICIONAL +
    VR_CREDITO_CONTINGENCIA +
    VR_CREDITO_EXTRA +
    VR_ALTERACAO_QDD +
    VR_CREDITO_SAIDA +
    VR_CREDITO_DESTAQUE +
    VR_MOVIMENTACAO +
    (
    (
    VR_PROPOSTA_SECOR +
    VR_PROPOSTA_REMANEJADA
    ) -
    VR_PROPOSTA_RECEBIDA
    ) +
    VR_A_RECEBER - VR_EXECUTADO                                                     AS VR_SALDO_MENOS_DOTACAO

    /* ************************************************************
    CAMPO VR_SALDO_EMPENHO NÃO MAIS UTILIZADO!
    ************************************************************ */
    /* Campo calculado: [VR_TOTAL_DESPESA - VR_EMPENHADO] */
    /*
    NVL(VLD4.VLDE_VL_DESPESA, 0) +
    NVL(TNPA.VALOR, 0) +
    NVL(TNPC.VALOR, 0) +
    NVL(TNPE.VALOR, 0) +
    NVL(TNPQ.VALOR, 0) +
    NVL(TNPS.VALOR, 0) +
    NVL(TNPT.VALOR, 0) +
    NVL(MOVC.VALOR, 0) -
    NVL(NOEM.VALOR, 0)																AS VR_SALDO_EMPENHO,
    */

FROM
	(
	SELECT
		/* Dados básicos da despesa */
		NOEM_CD_NOTA_EMPENHO,
		NR_DESPESA,
		DESP_DS_ADICIONAL,
		DESP_AA_DESPESA,
		DESP_CD_UG,
		SG_FAMILIA_RESPONSAVEL,
		DESP_CD_FONTE,
		DESP_CD_PT_RESUMIDO,
		UNOR_CD_UNID_ORCAMENTARIA,
        PTRS_SG_PT_RESUMIDO,
		DESP_CD_ELEMENTO_DESPESA_SUB,
		TIDE_DS_TIPO_DESPESA,
                TIDE_IC_RESERVA_RECURSO,
                DESP_NR_COPIA_DESPESA,

        DESP_CD_CATEGORIA,
        DESP_CD_VINCULACAO,
        -- DESP_CD_TIPO_RECURSO,
        TREC_DS_TIPO_RECURSO,
        -- DESP_CD_TIPO_ORCAMENTO,
        PORC_DS_TIPO_ORCAMENTO,
        -- DESP_CD_OBJETIVO,
        POBJ_DS_OBJETIVO,
        -- DESP_CD_PROGRAMA,
        PPRG_DS_PROGRAMA,
        -- DESP_CD_TIPO_OPERACIONAL,
        POPE_DS_TIPO_OPERACIONAL,
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
	VR_PROPOSTA_SECOR,
	VR_PROPOSTA_REMANEJADA,

        VR_PROPOSTA_RECEBIDA_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            -- THEN VR_PROPOSTA_RECEBIDA_RESERVA
            THEN 0
            ELSE VR_PROPOSTA_RECEBIDA_NC
        END AS VR_PROPOSTA_RECEBIDA,

        VR_CREDITO_ADICIONAL_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            -- THEN VR_CREDITO_ADICIONAL_RESERVA
            THEN 0
            ELSE VR_CREDITO_ADICIONAL_NC
        END AS VR_CREDITO_ADICIONAL,

        VR_CREDITO_CONTING_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            -- THEN VR_CREDITO_CONTING_RESERVA
            THEN 0
            ELSE VR_CREDITO_CONTINGENCIA_NC
        END AS VR_CREDITO_CONTINGENCIA,

        VR_CREDITO_EXTRA_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            -- THEN VR_CREDITO_EXTRA_RESERVA
            THEN 0
            ELSE VR_CREDITO_EXTRA_NC
        END AS VR_CREDITO_EXTRA,

        VR_CREDITO_SAIDA_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            THEN
                VR_PROPOSTA_RECEBIDA_RESERVA +
                VR_CREDITO_ADICIONAL_RESERVA +
                VR_CREDITO_CONTING_RESERVA +
                VR_CREDITO_EXTRA_RESERVA +
                VR_CREDITO_SAIDA_RESERVA
            ELSE VR_CREDITO_SAIDA_NC
        END AS VR_CREDITO_SAIDA,

        VR_ALTERACAO_QDD_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            THEN VR_ALTERACAO_QDD_RESERVA
            ELSE VR_ALTERACAO_QDD_NC
        END AS VR_ALTERACAO_QDD,

        VR_CREDITO_DESTAQUE_CREDITO + CASE TIDE_IC_RESERVA_RECURSO WHEN 1
            THEN VR_CREDITO_DESTAQUE_RESERVA
            ELSE VR_CREDITO_DESTAQUE_NC
        END AS VR_CREDITO_DESTAQUE,

        BASE.EXEC_VL_JANEIRO AS EXEC_VR_JANEIRO,
        BASE.EXEC_VL_FEVEREIRO AS EXEC_VR_FEVEREIRO,
        BASE.EXEC_VL_MARCO AS EXEC_VR_MARCO,
        BASE.EXEC_VL_ABRIL AS EXEC_VR_ABRIL,
        BASE.EXEC_VL_MAIO AS EXEC_VR_MAIO,
        BASE.EXEC_VL_JUNHO AS EXEC_VR_JUNHO,
        BASE.EXEC_VL_JULHO AS EXEC_VR_JULHO,
        BASE.EXEC_VL_AGOSTO AS EXEC_VR_AGOSTO,
        BASE.EXEC_VL_SETEMBRO AS EXEC_VR_SETEMBRO,
        BASE.EXEC_VL_OUTUBRO AS EXEC_VR_OUTUBRO,
        BASE.EXEC_VL_NOVEMBRO AS EXEC_VR_NOVEMBRO,
        BASE.EXEC_VL_DEZEMBRO AS EXEC_VR_DEZEMBRO,

	VR_MOVIMENTACAO,
	VR_A_RECEBER,
	VR_RDO,
	VR_EMPENHADO,
	VR_EXECUTADO,

        /* VR_PROJ_FUTURA, */
        VR_PROJECAO

	/* Campo calculado VR_MES_ATUAL */
        /* CAMPO OBSOLETO */
        /*
	CASE
            WHEN VR_PROJ_MES_ATUAL >= VR_EXEC_MES_ATUAL THEN VR_PROJ_MES_ATUAL
            WHEN VR_PROJ_MES_ATUAL < VR_EXEC_MES_ATUAL THEN VR_EXEC_MES_ATUAL
	END

        AS VR_MES_ATUAL,
        VR_EXEC_PASSADA
        */

        FROM
            (SELECT
            NOEM.NOEM_CD_NOTA_EMPENHO,
            DESP.DESP_NR_DESPESA AS NR_DESPESA,
            DESP_NR_DESPESA,
            DESP_DS_ADICIONAL,
            DESP_AA_DESPESA,
            DESP_CD_UG,
            RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)  AS SG_FAMILIA_RESPONSAVEL,
            DESP_CD_FONTE,
            DESP_CD_PT_RESUMIDO,
            UNOR_CD_UNID_ORCAMENTARIA,
            PTRS_SG_PT_RESUMIDO,
            DESP_CD_ELEMENTO_DESPESA_SUB,
            TIDE.TIDE_DS_TIPO_DESPESA,
            TIDE_IC_RESERVA_RECURSO,
            DESP_NR_COPIA_DESPESA,

            DESP_CD_CATEGORIA,
            DESP_CD_VINCULACAO,
            -- DESP_CD_TIPO_RECURSO,
            TREC.TREC_DS_TIPO_RECURSO,
            -- DESP_CD_TIPO_ORCAMENTO,
            PORC.PORC_DS_TIPO_ORCAMENTO,
            -- DESP_CD_OBJETIVO,
            POBJ.POBJ_DS_OBJETIVO,
            -- DESP_CD_PROGRAMA,
            PPRG.PPRG_DS_PROGRAMA,
            -- DESP_CD_TIPO_OPERACIONAL,
            POPE.POPE_DS_TIPO_OPERACIONAL,
             EXEC.EXEC_VL_JANEIRO,
            EXEC.EXEC_VL_FEVEREIRO,
            EXEC.EXEC_VL_MARCO,
            EXEC.EXEC_VL_ABRIL,
            EXEC.EXEC_VL_MAIO,
            EXEC.EXEC_VL_JUNHO,
            EXEC.EXEC_VL_JULHO,
            EXEC.EXEC_VL_AGOSTO,
            EXEC.EXEC_VL_SETEMBRO,
            EXEC.EXEC_VL_OUTUBRO,
            EXEC.EXEC_VL_NOVEMBRO,
            EXEC.EXEC_VL_DEZEMBRO,

            NVL(VLD4.VLDE_VL_DESPESA, 0) AS VR_PROPOSTA_SECOR,

            NVL(MOVP.VALOR, 0) AS VR_PROPOSTA_REMANEJADA,

			/* ************************************************************ */
			/* Campos abaixo para despesas <> 090032 */
			/* ************************************************************ */
			NVL(CRDP.VALOR, 0)															AS VR_PROPOSTA_RECEBIDA_CREDITO,
			NVL(CRDA.VALOR, 0)															AS VR_CREDITO_ADICIONAL_CREDITO,
			NVL(CRDC.VALOR, 0)															AS VR_CREDITO_CONTING_CREDITO,
			NVL(CRDE.VALOR, 0)															AS VR_CREDITO_EXTRA_CREDITO,
			NVL(CRDQ.VALOR, 0)															AS VR_ALTERACAO_QDD_CREDITO,
			NVL(CRDS.VALOR, 0)															AS VR_CREDITO_SAIDA_CREDITO,
			NVL(CRDT.VALOR, 0)															AS VR_CREDITO_DESTAQUE_CREDITO,

			/* ************************************************************ */
			/* Campos abaixo para despesas <> 090032 */
			/* ************************************************************ */
			NVL(TNPP.VALOR, 0)															AS VR_PROPOSTA_RECEBIDA_NC,
			NVL(TNPA.VALOR, 0)															AS VR_CREDITO_ADICIONAL_NC,
			NVL(TNPC.VALOR, 0)															AS VR_CREDITO_CONTINGENCIA_NC,
			NVL(TNPE.VALOR, 0)															AS VR_CREDITO_EXTRA_NC,
			NVL(TNPQ.VALOR, 0)															AS VR_ALTERACAO_QDD_NC,
			NVL(TNPS.VALOR, 0)															AS VR_CREDITO_SAIDA_NC,
			NVL(TNPT.VALOR, 0)															AS VR_CREDITO_DESTAQUE_NC,

			/* ************************************************************ */
			/* Campos abaixo para despesas = 090032 */
			/* ************************************************************ */
			NVL(TNRP.VALOR, 0)															AS VR_PROPOSTA_RECEBIDA_RESERVA,
			NVL(TNRA.VALOR, 0)															AS VR_CREDITO_ADICIONAL_RESERVA,
			NVL(TNRC.VALOR, 0)															AS VR_CREDITO_CONTING_RESERVA,
			NVL(TNRE.VALOR, 0)															AS VR_CREDITO_EXTRA_RESERVA,
			NVL(TNRQ.VALOR, 0)															AS VR_ALTERACAO_QDD_RESERVA,
			NVL(TNRS.VALOR, 0)															AS VR_CREDITO_SAIDA_RESERVA,
			NVL(TNRT.VALOR, 0)															AS VR_CREDITO_DESTAQUE_RESERVA,
			/* ************************************************************ */

			NVL(MOVC.VALOR, 0)															AS VR_MOVIMENTACAO,
			NVL(RECD.VALOR, 0)															AS VR_A_RECEBER,
			NVL(REQV.VALOR, 0)															AS VR_RDO,
			NVL(NOEM.VALOR, 0)															AS VR_EMPENHADO,
			NVL(EXEC.VALOR, 0)															AS VR_EXECUTADO,

            NVL(PROJ.VR_PROJECAO, 0)                                                    AS VR_PROJECAO

			/*
			NVL(PROJ.VR_PROJ_FUTURA, 0)													AS VR_PROJ_FUTURA,
			NVL(PROJ.VR_PROJ_MES_ATUAL, 0)												AS VR_PROJ_MES_ATUAL,
			NVL(EXEC.VR_EXEC_MES_ATUAL, 0)												AS VR_EXEC_MES_ATUAL,
			NVL(EXEC.VR_EXEC_PASSADA, 0)												AS VR_EXEC_PASSADA
			*/
		FROM
			CEO_TB_DESP_DESPESA					DESP

        Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO     PTRS ON
                PTRS.PTRS_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
        Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON
                UNOR.UNOR_CD_UNID_ORCAMENTARIA = PTRS.PTRS_CD_UNID_ORCAMENTARIA

		/* Inclui o codigo do empenho 27/11/2014 */
		Left JOIN CEO_TB_NOEM_NOTA_EMPENHO          NOEM ON
            	NOEM.NOEM_NR_DESPESA 				= DESP.DESP_NR_DESPESA

		/* Responsável e Família */
		Left JOIN
			CEO_TB_RESP_RESPONSAVEL					RESP ON
				RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
		Left JOIN
			RH_CENTRAL_LOTACAO						RHCL ON
				RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO				AND
				RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
		Left JOIN CEO_TB_TIDE_TIPO_DESPESA TIDE ON TIDE.TIDE_CD_TIPO_DESPESA = DESP.DESP_CD_TIPO_DESPESA
                Left JOIN CEO_TB_TREC_TIPO_RECURSO                TREC ON
                TREC.TREC_CD_TIPO_RECURSO           = DESP.DESP_CD_TIPO_RECURSO
        Left JOIN
            CEO_TB_PORC_TIPO_ORCAMENTO              PORC ON
                PORC.PORC_CD_TIPO_ORCAMENTO         = DESP.DESP_CD_TIPO_ORCAMENTO
        Left JOIN
            CEO_TB_POBJ_OBJETIVO                    POBJ ON
                POBJ.POBJ_CD_OBJETIVO               = DESP.DESP_CD_OBJETIVO
        Left JOIN
            CEO_TB_PPRG_PROGRAMA                    PPRG ON
                PPRG.PPRG_CD_PROGRAMA               = DESP.DESP_CD_PROGRAMA
        Left JOIN
            CEO_TB_POPE_TIPO_OPERACIONAL            POPE ON
                POPE.POPE_CD_TIPO_OPERACIONAL       = DESP.DESP_CD_TIPO_OPERACIONAL

		/* Valor aprovado pela SECOR */
		Left JOIN
			CEO_TB_VLDE_VALOR_DESPESA				VLD4 ON
				VLD4.VLDE_NR_DESPESA				= DESP.DESP_NR_DESPESA				AND
				VLD4.VLDE_CD_DEMANDANTE				= " . $demandante . "

		/* Créditos por tipo */
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_PROPOSTA) . ")	CRDP ON
				CRDP.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ADICIONAL) . ")	CRDA ON
				CRDA.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_CONTINGENCIA) . ")	CRDC ON
				CRDC.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_EXTRA) . ")	CRDE ON
				CRDE.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ALTERACAO_QDD) . ")	CRDQ ON
				CRDQ.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_SAIDA) . ")	CRDS ON
				CRDS.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlCreditoPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_DESTAQUE) . ")	CRDT ON
				CRDT.CRED_NR_DESPESA			= DESP.DESP_NR_DESPESA

		/* Notas de crédito (diferente das reservas) por tipo */
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_PROPOSTA) . ")	TNPP ON
				TNPP.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ADICIONAL) . ")	TNPA ON
				TNPA.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_CONTINGENCIA) . ")	TNPC ON
				TNPC.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_EXTRA) . ")	TNPE ON
				TNPE.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ALTERACAO_QDD) . ")	TNPQ ON
				TNPQ.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_SAIDA) . ")	TNPS ON
				TNPS.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_DESTAQUE) . ")	TNPT ON
				TNPT.NOCR_NR_DESPESA			= DESP.DESP_NR_DESPESA

		/* Notas de crédito (apenas reservas) por tipo */
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_PROPOSTA) . ")	TNRP ON
				TNRP.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ADICIONAL) . ")	TNRA ON
				TNRA.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_CONTINGENCIA) . ")	TNRC ON
				TNRC.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_EXTRA) . ")	TNRE ON
				TNRE.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_ALTERACAO_QDD) . ")	TNRQ ON
				TNRQ.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_SAIDA) . ")	TNRS ON
				TNRS.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlNCReservaPorTipo($strDespesas, Orcamento_Business_Dados::TIPO_NOTA_CREDITO_DESTAQUE) . ")	TNRT ON
				TNRT.NOCR_NR_DESPESA_RESERVA	= DESP.DESP_NR_DESPESA

		/* Movimentações de crédito */
		Left JOIN
			(" . $this->_retornaSqlMovimentacoesCredito($strDespesas) . ")	MOVC ON
				MOVC.MOVC_NR_DESPESA			= DESP.DESP_NR_DESPESA
		Left JOIN
			(" . $this->_retornaSqlMovimentacoesProposta($strDespesas) . ")	MOVP ON
				MOVP.MOVC_NR_DESPESA			= DESP.DESP_NR_DESPESA

		/* RDOs */
		Left JOIN
			(" . $this->_retornaSqlRequisicoes($strDespesas) . ")			REQV ON
				REQV.REQV_NR_DESPESA			= DESP.DESP_NR_DESPESA

		/* Recursos a descentralizar */
		Left JOIN
			(" . $this->_retornaSqlRecursosDescentralizar($strDespesas) . ")	RECD ON
				RECD.RECD_NR_DESPESA			= DESP.DESP_NR_DESPESA

		/* Empenhos */
		Left JOIN
			(" . $this->_retornaSqlEmpenhos($strDespesas) . ")				NOEM ON
				NOEM.NOEM_NR_DESPESA			= DESP.DESP_NR_DESPESA
		/* Projeção */
		Left JOIN
			(" . $this->_retornaSqlProjecao($strDespesas) . ")				PROJ ON
				PROJ.PROJ_NR_DESPESA			= DESP.DESP_NR_DESPESA
		/* Execução */
		Left JOIN
			(" . $this->_retornaSqlExecucao($strDespesas) . ")				EXEC ON
				EXEC.EXEC_NR_DESPESA			= DESP.DESP_NR_DESPESA
		WHERE
			DESP.DESP_DH_EXCLUSAO_LOGICA		IS NULL
			$condicaoDespesa
			$condicaoResponsaveis
		) BASE
	) FIM
	ORDER BY EXERCICIO			";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado dos créditos por
     * $despesa e $tipo de NCs
     *
     * @param	int		$despesa
     * @param	string	$tipo
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlCreditoPorTipo ($despesa, $tipo) {
            $whereDespCred = "";

            if ($despesa) {
                $whereDespCred = " CRED_NR_DESPESA IN ( $despesa ) AND ";
            }

            $sql = "
                SELECT
                    CRED_NR_DESPESA,
                    SUM(CRED_VL_CREDITO_RECEBIDO) AS VALOR
                FROM
                    CEO_TB_CRED_CREDITO_RECEBIDO
                WHERE
                    $whereDespCred
                    CRED_CD_TIPO_NC = '$tipo' AND
                    CRED_DH_EXCLUSAO_LOGICA IS NULL
                GROUP BY
                    CRED_NR_DESPESA
                	            ";


            // Devolve a instrução sql
            return $sql;
    }


    /**
     * Retorna a instrução sql que busca o valor agrupado das NCs por $despesa e
     * $tipo de NC diferentes das reservas (UG <> 090032)
     *
     * @param	int		$despesa
     * @param	string	$tipo
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlNCPorTipo ($despesa, $tipo) {

        // verifica se a despesa e reserva
        $whereDespNC = "";
        if ($despesa) {
            $whereDespNC = " NOCR_NR_DESPESA IN ( $despesa ) AND ";
        }

        $sql = "
SELECT
    NOCR.NOCR_NR_DESPESA,
    NOCR.NOCR_CD_UG_FAVORECIDO,
    SUM(NOCR.NOCR_VL_NC_ACERTADO) AS VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO NOCR

LEFT JOIN
    CEO_TB_DESP_DESPESA DESP ON
      DESP.DESP_NR_DESPESA = NOCR.NOCR_NR_DESPESA

WHERE
    $whereDespNC
    NOCR_CD_TIPO_NC = '$tipo'
-- not in pendencias
    -- ptres
    -- AND NOCR.NOCR_CD_PT_RESUMIDO = DESP.DESP_CD_PT_RESUMIDO
    -- natureza
    -- AND NOCR.NOCR_CD_ELEMENTO_DESPESA_SUB = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
    -- ug
    AND NOCR.NOCR_CD_UG_FAVORECIDO = DESP.DESP_CD_UG
    --fonte
    -- AND NOCR.NOCR_CD_FONTE = DESP.DESP_CD_FONTE

    GROUP BY
    NOCR.NOCR_NR_DESPESA,NOCR.NOCR_CD_UG_FAVORECIDO
                ";
        // Devolve a instrução sql
        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das NCs por $despesa e $tipo de NC específicas das reservas (UG = 090032)
     *
     * @param	int		$despesa
     * @param	string	$tipo
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlNCReservaPorTipo ($despesa, $tipo) {
        $whereDespNCReserva = "";

        if ($despesa) {
            $whereDespNCReserva = " NOCR_NR_DESPESA_RESERVA IN ( $despesa ) AND ";
        }

        $sql = "
SELECT
    NOCR_NR_DESPESA_RESERVA,
    SUM(NOCR_VL_NC_ACERTADO) * (-1) AS VALOR
FROM
    CEO_TB_NOCR_NOTA_CREDITO
WHERE
    $whereDespNCReserva
    NOCR_CD_TIPO_NC = '$tipo'
GROUP BY
    NOCR_NR_DESPESA_RESERVA
		        ";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das movimentações de crédito por $despesa
     *
     * @param	int		$despesa
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlMovimentacoesCredito ($despesa) {
        if ($despesa) {
            $whereDespesaOrigem = " MOVC_NR_DESPESA_ORIGEM IN ( $despesa ) AND ";
            $whereDespesaDestino = " MOVC_NR_DESPESA_DESTINO IN ( $despesa ) AND ";
        } else {
            $whereDespesaOrigem = "";
            $whereDespesaDestino = "";
        }

        $sql = "
SELECT
	DESPESA							AS MOVC_NR_DESPESA,
	SUM(VALOR)						AS VALOR
FROM
	(
	SELECT
		MOVC_NR_DESPESA_ORIGEM		AS DESPESA,
		MOVC_VL_MOVIMENTACAO * (-1)	AS VALOR
	FROM
		CEO_TB_MOVC_MOVIMENTACAO_CRED
	WHERE
		$whereDespesaOrigem
		MOVC_ID_TIPO_MOVIMENTACAO	= " . Trf1_Orcamento_Dados::TIPO_MOVIMENTACAO_CREDITO_REMANEJAMENTO . " AND
		MOVC_CD_TIPO_SOLICITACAO	= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . " AND
		/* MOVC_IC_MOVIMENT_REPASSADA	= " . Trf1_Orcamento_Dados::MOVIMENTACAO_CREDITO_REPASSADA_SIM . " AND */
		MOVC_DH_EXCLUSAO_LOGICA		IS NULL

	UNION ALL

	SELECT
		MOVC_NR_DESPESA_DESTINO		AS DESPESA,
		MOVC_VL_MOVIMENTACAO		AS VALOR
	FROM
		CEO_TB_MOVC_MOVIMENTACAO_CRED
	WHERE
		$whereDespesaDestino
		MOVC_ID_TIPO_MOVIMENTACAO	= " . Trf1_Orcamento_Dados::TIPO_MOVIMENTACAO_CREDITO_REMANEJAMENTO . " AND
		MOVC_CD_TIPO_SOLICITACAO	= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . " AND
		/* MOVC_IC_MOVIMENT_REPASSADA	= " . Trf1_Orcamento_Dados::MOVIMENTACAO_CREDITO_REPASSADA_SIM . " AND */
		MOVC_DH_EXCLUSAO_LOGICA		IS NULL
	)								M
GROUP BY
	M.DESPESA
				";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das movimentações de crédito do tipo proposta por $despesa
     *
     * @param	int		$despesa
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlMovimentacoesProposta ($despesa) {
        if ($despesa) {
            $whereDespesaOrigem = " MOVC_NR_DESPESA_ORIGEM IN ( $despesa ) AND ";
            $whereDespesaDestino = " MOVC_NR_DESPESA_DESTINO IN ( $despesa ) AND ";
        } else {
            $whereDespesaOrigem = "";
            $whereDespesaDestino = "";
        }

        $sql = "
SELECT
	DESPESA							AS MOVC_NR_DESPESA,
	SUM(VALOR)						AS VALOR
FROM
	(
	SELECT
		MOVC_NR_DESPESA_ORIGEM		AS DESPESA,
		MOVC_VL_MOVIMENTACAO * (-1)	AS VALOR
	FROM
		CEO_TB_MOVC_MOVIMENTACAO_CRED
	WHERE
		$whereDespesaOrigem
		MOVC_ID_TIPO_MOVIMENTACAO	= " . Trf1_Orcamento_Dados::TIPO_MOVIMENTACAO_CREDITO_PROPOSTA . " AND
		MOVC_CD_TIPO_SOLICITACAO	= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . " AND
		/* MOVC_IC_MOVIMENT_REPASSADA	= " . Trf1_Orcamento_Dados::MOVIMENTACAO_CREDITO_REPASSADA_SIM . " AND */
		MOVC_DH_EXCLUSAO_LOGICA		IS NULL

	UNION ALL

	SELECT
		MOVC_NR_DESPESA_DESTINO		AS DESPESA,
		MOVC_VL_MOVIMENTACAO		AS VALOR
	FROM
		CEO_TB_MOVC_MOVIMENTACAO_CRED
	WHERE
		$whereDespesaDestino
		MOVC_ID_TIPO_MOVIMENTACAO	= " . Trf1_Orcamento_Dados::TIPO_MOVIMENTACAO_CREDITO_PROPOSTA . " AND
		MOVC_CD_TIPO_SOLICITACAO	= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . " AND
		/* MOVC_IC_MOVIMENT_REPASSADA	= " . Trf1_Orcamento_Dados::MOVIMENTACAO_CREDITO_REPASSADA_SIM . " AND */
		MOVC_DH_EXCLUSAO_LOGICA		IS NULL
	)								M
GROUP BY
	M.DESPESA
				";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das requisições de disponibilidade orçamentária (RDOs) por $despesa
     *
     * @param	int		$despesa
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlRequisicoes ($despesa) {
        if ($despesa) {
            $whereDespesa = " REQV_NR_DESPESA IN ( $despesa ) AND ";
        } else {
            $whereDespesa = "";
        }

        $sql = "
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
	WHERE
		$whereDespesa
		REQV_DH_EXCLUSAO_LOGICA IS NULL
	)
GROUP BY
	REQV_NR_DESPESA
				";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das notas de empenhos por $despesa
     *
     * @param	int		$despesa
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlRecursosDescentralizar ($despesa) {
        $whereDespesa = "";
        if ($despesa) {
            $whereDespesa = " RECD_NR_DESPESA IN ( $despesa ) AND ";
        }

        $sql = "
SELECT
	RECD_NR_DESPESA,
	SUM(RECD_VL_RECURSO) AS VALOR
FROM
	CEO_TB_RECD_RECURSO_DESCENT
WHERE
	$whereDespesa
	RECD_IC_RECURSO				= 0 AND
	RECD_DH_EXCLUSAO_LOGICA		IS NULL
GROUP BY
	RECD_NR_DESPESA
				";

        return $sql;
    }

    /**
     * Retorna a instrução sql que busca o valor agrupado das notas de empenhos por $despesa
     *
     * @param	int		$despesa
     * @return	string
     * @author	Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function _retornaSqlEmpenhos ($despesa) {

        $whereDespesa = "";
        if ($despesa) {
            $whereDespesa = " WHERE NOEM_NR_DESPESA IN ( $despesa ) ";
        }

        $sql = "
SELECT
	DADOS.NOEM_NR_DESPESA			AS NOEM_NR_DESPESA,
	SUM(VALORES.TOTAL)				AS VALOR
FROM
	(
    SELECT
        NE                          AS NOEM_CD_NOTA_EMPENHO,
    UG              AS NOEM_CD_UG_OPERADOR,
        SUM(VALOR)                  AS TOTAL
    FROM
        (
        SELECT
            NOEM.NOEM_CD_NOTA_EMPENHO   AS NE,
            NOEM.NOEM_VL_NE_ACERTADO        AS VALOR,
      NOEM.NOEM_CD_UG_OPERADOR   AS UG,
      NOEM.NOEM_NR_DESPESA AS DESPESA
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO NOEM
      
    JOIN CEO_TB_DESP_DESPESA DESP ON NOEM.NOEM_NR_DESPESA = DESP.DESP_NR_DESPESA AND NOEM.NOEM_CD_UG_OPERADOR = DESP.DESP_CD_UG
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NULL

        UNION ALL

        SELECT
            NOEM.NOEM_CD_NE_REFERENCIA  AS NE,
            NOEM.NOEM_VL_NE_ACERTADO        AS VALOR,
      NOEM.NOEM_CD_UG_OPERADOR   AS UG,
      NOEM.NOEM_NR_DESPESA AS DESPESA
        FROM
            CEO_TB_NOEM_NOTA_EMPENHO NOEM
      
    JOIN CEO_TB_DESP_DESPESA DESP ON NOEM.NOEM_NR_DESPESA = DESP.DESP_NR_DESPESA AND NOEM.NOEM_CD_UG_OPERADOR = DESP.DESP_CD_UG
    
        WHERE
            NOEM_CD_NE_REFERENCIA   IS NOT NULL
        )
    GROUP BY
        NE,
    UG,
    DESPESA
    ) VALORES
Left JOIN
	CEO_TB_NOEM_NOTA_EMPENHO		DADOS ON
		DADOS.NOEM_CD_NOTA_EMPENHO = VALORES.NOEM_CD_NOTA_EMPENHO
Left JOIN
  CEO_TB_DESP_DESPESA DESP ON
   DESP.DESP_CD_UG = VALORES.NOEM_CD_UG_OPERADOR AND
   DESP.DESP_NR_DESPESA = NOEM_NR_DESPESA        
	$whereDespesa
GROUP BY
	NOEM_NR_DESPESA
				";



        return $sql;
    }

    private function _retornaSqlProjecao ($despesa, $mesProjecao = 0) {
        $whereDespesa = "";
        if ($despesa) {
            $whereDespesa = " WHERE PRJBASE.PROJ_NR_DESPESA IN ( $despesa ) ";
        }

        // Se não for informado o mês o sistema assume o mês atual
        if ($mesProjecao == 0) {
            $mesProjecao = date('n');
        }

        $sql_OLD = "
SELECT
	PRJBASE.PROJ_NR_DESPESA,
	SUM(PRJBASE.PROJ_FUTURA)	AS VR_PROJ_FUTURA,
	SUM(PRJBASE.PROJ_MES)		AS VR_PROJ_MES_ATUAL
FROM
	(
	SELECT
		PROJ_NR_DESPESA,
		NVL(SUM(PROJ_VL_PROJECAO), 0) AS PROJ_FUTURA,
		0 AS PROJ_MES
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO > $mesProjecao
	GROUP BY
		PROJ_NR_DESPESA

	UNION ALL

	SELECT
		PROJ_NR_DESPESA,
		0 AS PROJ_FUTURA,
		NVL(SUM(PROJ_VL_PROJECAO), 0) AS PROJ_MES
	FROM
		CEO_TB_PROJ_PROJECAO
	WHERE
		PROJ_MM_PROJECAO = $mesProjecao
	GROUP BY
		PROJ_NR_DESPESA
	) PRJBASE
$whereDespesa
GROUP BY
	PRJBASE.PROJ_NR_DESPESA
				";

        $sql = "
SELECT
    PROJ_NR_DESPESA,
    SUM(PROJ_VL_PROJECAO) AS VR_PROJECAO
FROM
    CEO_TB_PROJ_PROJECAO PRJBASE
$whereDespesa
GROUP BY
    PROJ_NR_DESPESA
                ";

        return $sql;
    }

    public function _retornaSqlExecucao ($despesa = null, $mes = 0) {
        $negocio = new Trf1_Orcamento_Negocio_Ne ();
        $sql = $negocio->_retornaSqlExecucaoPorDespesa($despesa, $mes);

        return $sql;
    }

}
