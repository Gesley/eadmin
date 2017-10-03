<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Extrato
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Extrato
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
class Trf1_Orcamento_Negocio_Extrato {
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/**
	 * Retorna os registros referentes a dadas despesa para apresentação em forma de extrato
	 * 
	 * Os campos utilizados nesta consulta são, na ordem:
	 * NR_DESPESA; Código da despesa [campo não exibido];
	 * DATA; Data do lançamento, formatado como 'DD/MM/YYYY';
	 * DT_LANCAMENTO; Data do lançamento apenas para ordenação no grid, formatado como YYMMDD [campo não exibido];
	 * DS_LANCAMENTO; Descrição padronizada do tipo de lançamento;
	 * DS_ORIGEM; Breve informação da origem do lançamento;
	 * VL_LANCAMENTO; Valor do lançamento, formatado para #.##0,00;
	 * TB_FONTE; Fonte da informação, sendo o prefixo da tabela [campo não exibido];
	 * LINK; Url montada conforme o tipo de registro (controller) para ser utilizado como link [campo não exibido];
	 * CODIGO; Chave primária / composta para uso no link [campo não exibido]
	 * 
	 * @param	int		$despesa
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem($despesa) {
		if (! $despesa) {
			$despesa = 0;
		}
		
		// Criação dos links para uso do extrato
		$linkDespesa = $this->linkUrl ( 'despesa' );
		$linkRdo = $this->linkUrl ( 'rdo' );
		$linkOrigem = $this->linkUrl ( 'movimentacaocred' );
		$linkDestino = $this->linkUrl ( 'movimentacaocred' );
		$linkCreditoExtra = $this->linkUrl ( 'creditoextra' );
		$linkNotaEmpenho = $this->linkUrl ( 'ne' );
		$linkNotaCredito = $this->linkUrl ( 'nc' );
		
		$sql = "
/* Padronização da exibição */
SELECT
	-- NR_DESPESA, /* Exibição não necessária! */
	TO_CHAR(DT_LANCAMENTO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')					AS DT_LANCAMENTO,
	DS_LANCAMENTO,
	DS_ORIGEM,
	VL_LANCAMENTO,
	-- TB_FONTE, /* Não exibir em produção! */
	LINK,
	CODIGO
FROM
(

/* Busca do valor inicial da despesa */
SELECT
	V.VLDE_NR_DESPESA													AS NR_DESPESA,
	V.VLDE_DH_DESPESA													AS DT_LANCAMENTO,
	'Criação da despesa'												AS DS_LANCAMENTO,
	D.DEMN_DS_DEMANDANTE												AS DS_ORIGEM,
	V.VLDE_VL_DESPESA													AS VL_LANCAMENTO,
	'DESP'																AS TB_FONTE,
	'$linkDespesa'														AS LINK,
	TO_CHAR(V.VLDE_NR_DESPESA)											AS CODIGO
FROM
	CEO_TB_VLDE_VALOR_DESPESA											V
Left JOIN
	CEO_TB_DEMN_DEMANDANTE_VALOR										D ON
		D.DEMN_CD_DEMANDANTE = V.VLDE_CD_DEMANDANTE
WHERE
	D.DEMN_CD_DEMANDANTE												= 4 /* 4= último valor pela SECOR */	AND
	V.VLDE_NR_DESPESA													= $despesa								AND
	VLDE_DH_EXCLUSAO_LOGICA												IS Null
	
UNION ALL
	
/* Busca das RDOs */
SELECT
	REQV_NR_DESPESA														AS NR_DESPESA,
	REQV_DH_VARIACAO													AS DT_LANCAMENTO,
	'Requisição de disponibilidade orçamentária'						AS DS_LANCAMENTO,
	'RDO-' || REQV_NR_DESPESA											AS DS_ORIGEM,
	CASE REQV_IC_TP_VARIACAO
		WHEN 0 THEN REQV_VL_VARIACAO
		WHEN 1 THEN REQV_VL_VARIACAO * (-1)
	END																	AS VL_LANCAMENTO,
	'REQV'																AS TB_FONTE,
	'$linkRdo'															AS LINK,
	TO_CHAR(0)															AS CODIGO
FROM
	CEO_TB_REQV_REQU_VARIACAO
WHERE
	REQV_NR_DESPESA														= $despesa								AND
	REQV_DH_EXCLUSAO_LOGICA												IS Null
	
UNION ALL
	
/* Busca dos movimentações de créditos - Origem */
SELECT
	MOVC_NR_DESPESA_ORIGEM			 									AS NR_DESPESA,
	MOVC_DH_MOVIMENTACAO												AS DT_LANCAMENTO,
	'Movimentação de crédito concedida para despesa ' || MOVC_NR_DESPESA_DESTINO	AS DS_LANCAMENTO,
	' - '																AS DS_ORIGEM,
	MOVC_VL_MOVIMENTACAO * (-1)		 									AS VL_LANCAMENTO,
	'MOVC'							 									AS TB_FONTE,
	'$linkOrigem'														AS LINK,
	TO_CHAR(MOVC_CD_MOVIMENTACAO)										AS CODIGO
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED
WHERE
	MOVC_NR_DESPESA_ORIGEM												= $despesa								AND
	MOVC_CD_TIPO_SOLICITACAO											= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . "	AND
	MOVC_DH_EXCLUSAO_LOGICA												IS Null
	
UNION ALL
	
/* Busca dos movimentações de créditos - Destino */
SELECT
	MOVC_NR_DESPESA_DESTINO												AS NR_DESPESA,
	MOVC_DH_MOVIMENTACAO												AS DT_LANCAMENTO,
	'Movimentação de crédito recebida'									AS DS_LANCAMENTO,
	'Despesa ' || MOVC_NR_DESPESA_ORIGEM								AS DS_ORIGEM,
	MOVC_VL_MOVIMENTACAO												AS VL_LANCAMENTO,
	'MOVC'																AS TB_FONTE,
	'$linkDestino'														AS LINK,
	TO_CHAR(MOVC_CD_MOVIMENTACAO)										AS CODIGO
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED
WHERE
	MOVC_NR_DESPESA_DESTINO												= $despesa								AND
	MOVC_CD_TIPO_SOLICITACAO											= " . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_ATENDIDA . "	AND
	MOVC_DH_EXCLUSAO_LOGICA												IS Null
	
UNION ALL

/* Busca dos créditos extras */
SELECT
    CRED_NR_DESPESA                                                     AS NR_DESPESA,
    CRED_DT_EMISSAO                                                     AS DT_LANCAMENTO,
    'Crédito extra recebido '                                           AS DS_LANCAMENTO,
    -- 'Crédito extra recebido tipo ' || CRED_CD_TIPO_NC                   AS DS_LANCAMENTO,
    CRED_DS_DOCUMENTO                                                   AS DS_ORIGEM,
    CRED_VL_CREDITO_RECEBIDO                                            AS VL_LANCAMENTO,
    'CRED'                                                              AS TB_FONTE,
    '$linkCreditoExtra'                                                 AS LINK,
    TO_CHAR(CRED_ID_CREDITO_RECEBIDO)                                   AS CODIGO
FROM
    CEO_TB_CRED_CREDITO_RECEBIDO
WHERE
    CRED_NR_DESPESA                                                     = $despesa AND
    CRED_DH_EXCLUSAO_LOGICA                                             IS NULL

UNION ALL

/* Busca das notas de empenho */
SELECT
	E.NOEM_NR_DESPESA													AS NR_DESPESA,
	E.NOEM_DT_EMISSAO													AS DT_LANCAMENTO,
	-- E.NOEM_DS_OBSERVACAO												AS DS_LANCAMENTO,
	'Nota de empenho emitida'											AS DS_LANCAMENTO,
	E.NOEM_CD_NOTA_EMPENHO												AS DS_ORIGEM,
	E.NOEM_VL_NE_ACERTADO												AS VL_LANCAMENTO,
	'NOEM'																AS TB_FONTE,
	'$linkNotaEmpenho'													AS LINK,
	TO_CHAR(NOEM_CD_NOTA_EMPENHO)										AS CODIGO
FROM
	CEO_TB_NOEM_NOTA_EMPENHO											E
Left JOIN
	CEO_TB_DESP_DESPESA													D ON
		D.DESP_NR_DESPESA = E.NOEM_NR_DESPESA
WHERE
	E.NOEM_NR_DESPESA													= $despesa
	
UNION ALL
	
/* Busca das notas de crédito */
SELECT
	NOCR_NR_DESPESA														AS NR_DESPESA,
	NOCR_DT_EMISSAO														AS DT_LANCAMENTO,
	-- NOCR_DS_OBSERVACAO												AS DS_LANCAMENTO,
	CASE NVL(NOCR_CD_TIPO_NC, 0)
		WHEN '0' THEN 'Nota de crédito emitida sem tipo'
		ELSE 'Nota de crédito emitida tipo ' || NOCR_CD_TIPO_NC
	END																	AS DS_LANCAMENTO,
	NOCR_CD_NOTA_CREDITO												AS DS_ORIGEM,
	NOCR_VL_NC_ACERTADO													AS VL_LANCAMENTO,
	'NOCR'																AS TB_FONTE,
	'$linkNotaCredito'													AS LINK,
	TO_CHAR(NOCR_CD_NOTA_CREDITO)										AS CODIGO
FROM
	CEO_TB_NOCR_NOTA_CREDITO
WHERE
	NOCR_NR_DESPESA														= $despesa
	
	
/*
UNION ALL
*/	

/* Busca da execução dos empenhos */
/*
SELECT
	D.DESP_NR_DESPESA													AS NR_DESPESA,
	LAST_DAY(TO_DATE('20121231', '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "'))	AS DT_LANCAMENTO,
	'Execução da despesa'												AS DS_LANCAMENTO,
	X.EXEC_CD_NOTA_EMPENHO												AS DS_ORIGEM,
	--
	-- REVER LINHA ABAIXO, E NOVA DEFINIÇÃO DA TABELA DE EXECUÇÃO
	--
	X.EXEC_VL_EXECUCAO													AS VL_LANCAMENTO,
	'EXEC'																AS TB_FONTE
FROM
	CEO_TB_EXEC_EXECUCAO_NE												X
Left JOIN
	CEO_TB_NOEM_NOTA_EMPENHO											E ON
		E.NOEM_CD_NOTA_EMPENHO = X.EXEC_CD_NOTA_EMPENHO
Left JOIN
	CEO_TB_DESP_DESPESA													D ON
		D.DESP_NR_DESPESA = E.NOEM_NR_DESPESA
WHERE
	D.DESP_NR_DESPESA													= $despesa
*/
) EXTRATO
		";
		
		//TODO: Rever último select desta função, pois a tabela de Execução (CEO_TB_EXEC_EXECUCAO_NE) tem definição diferenciado do CEO MS Access
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}
	
	/**
	 * Monta um link para ser utilizado no decorator do grid, conforme o tipo de registro do extrato
	 * 
	 * @param	string	$controle
	 * @return	string
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function linkUrl ( $controle )
	{
		$zvhu = new Zend_View_Helper_Url ();
		$endereco = array ('module' => 'orcamento', 'controller' => $controle, 'action' => 'detalhe', 'cod' => '' );
		$link = $zvhu->url ( $endereco, null, true );
		
		return $link;
	}
}
