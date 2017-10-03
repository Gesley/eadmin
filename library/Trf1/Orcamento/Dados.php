<?php
/**
 * Classe genérica para atribuição de valores fixos, conforme os dados dispostos no banco de dados
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Dados
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
final class Trf1_Orcamento_Dados {
	/* ************************************************************
	 * DEMANDANTE / CEO_TB_TPNC_TIPO_NOTA_CREDITO
	************************************************************ */
	/**
	 * Tipo de nota de crédito
	 * 
	 * @var		DEMANDANTE_xyz	string		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const DEMANDANTE_RESPONSAVEL = 1;
	const DEMANDANTE_PLANEJAMENTO = 2;
	const DEMANDANTE_CONGRESSO_NACIONAL = 3;
	const DEMANDANTE_SETORIAL_DIPOR = 4;
	
	/* ************************************************************
	 * TIPOS DE NOTAS DE CRÉDITO / CEO_TB_TPNC_TIPO_NOTA_CREDITO
	************************************************************ */
	/**
	 * Tipo de nota de crédito
	 * 
	 * @var		TIPO_NOTA_CREDITO_xyz	string		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const TIPO_NOTA_CREDITO_PROPOSTA = 'P';
	const TIPO_NOTA_CREDITO_ADICIONAL = 'A';
	const TIPO_NOTA_CREDITO_CONTINGENCIA = 'C';
	const TIPO_NOTA_CREDITO_EXTRA = 'E';
	const TIPO_NOTA_CREDITO_ALTERACAO_QDD = 'Q';
	const TIPO_NOTA_CREDITO_SAIDA = 'S';
	const TIPO_NOTA_CREDITO_DESTAQUE = 'T';
	
	/* ************************************************************
	 * TIPOS DE SOLICITAÇÃO / CEO_TB_TSOL_TIPO_SOLICITACAO
	************************************************************ */
	/**
	 * Tipos de solicitação
	 * 
	 * @var		TIPO_SOLICITACAO_xyz	int		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const TIPO_SOLICITACAO_SOLICITADA = 1;
	const TIPO_SOLICITACAO_ATENDIDA = 2;
	const TIPO_SOLICITACAO_RECUSADA = 3;
	const TIPO_SOLICITACAO_PENDENTE = 4;
	
	/* ************************************************************
	 * TIPOS DE MOVIMENTAÇÃO DE CRÉDITO / CEO_TB_TMOV_TIPO_MOVIMENTACAO
	************************************************************ */
	/**
	 * Tipos de movimentação de crédito
	 * 
	 * @var		TIPO_MOVIMENTACAO_CREDITO_xyz	int		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	/** @deprecated const TIPO_MOVIMENTACAO_CREDITO_ALTERACAO_QDD	= 1; */
	const TIPO_MOVIMENTACAO_CREDITO_REMANEJAMENTO = 2;
	const TIPO_MOVIMENTACAO_CREDITO_PROPOSTA = 3;
	
	/* ************************************************************
	 * MOVIMENTAÇÃO DE CRÉDITO REPASSADA
	************************************************************ */
	/**
	 * Movimentação de crédito repassada (ou não)
	 * 
	 * @var		TIPO_MOVIMENTACAO_CREDITO_xyz	int		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const MOVIMENTACAO_CREDITO_REPASSADA_SIM = 1;
	const MOVIMENTACAO_CREDITO_REPASSADA_NAO = 0;
	
	/* ************************************************************
	 * ORIGEM DA JUSTIFICATIVA DA PROJEÇÃO
	************************************************************ */
	/**
	 * Origem da justificativa da projeção
	 *
	 * @var		PROJECAO_JUSTIFICATIVA_ORIGEM_RESPONSAVEL_xyz	int		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const PROJECAO_JUSTIFICATIVA_ORIGEM_RESPONSAVEL = 0;
	const PROJECAO_JUSTIFICATIVA_ORIGEM_DIPOR = 1;
	
	/* ************************************************************
	 * CÓDIGO DE UG
	************************************************************ */
	/**
	 * Código da unidade gestora
	 *
	 * @var		UNIDADE_GESTORA_xyz	int		(constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const UNIDADE_GESTORA_DIPOR = 90032;
	
}
