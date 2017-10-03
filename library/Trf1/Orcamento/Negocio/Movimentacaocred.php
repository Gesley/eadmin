<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Movimentacaocred
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Unidades orçamentárias (Movimentacaocred)
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
class Trf1_Orcamento_Negocio_Movimentacaocred {
	/**
	 * Model da Unidade Orçamentária
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbMovcMovimentacaoCred ();
	}
	
	/**
	 * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
	 * 
	 * @return	array		Chave primária ou composta
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function tabela() {
		return $this->_dados;
	}
	
	/**
	 * Retorna a chave primária (ou composta) da tabela principal desta classe negocial
	 * 
	 * @return	array		Chave primária ou composta
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function chavePrimaria() {
		return $this->_dados->chavePrimaria ();
	}
	
	/**
	 * Apresenta dados (código e descrição) para montagem de combos
	 *
	 * @param	none
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaCombo() {
		// Verifica possível restrição de registros
		$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
		$ug = $sessaoOrcamento->ug;
		
		$condicaoUg = ' WHERE ';
		if ($ug != 'todas') {
			$condicaoUg = "
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA	UNGE ON
		UNGE.UNGE_CD_UG = D1.DESP_CD_UG
WHERE
	UNGE_CD_UG NOT IN (90032, 90049, 110407)	AND
	UNGE_SG_SECAO = '$ug'						AND
			
							";
		}
		
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->retornaID_Combo ( 'movimentacaocred_' . $ug );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	MOVC_CD_MOVIMENTACAO,
	MOVC_DS_JUSTIF_SOLICITACAO
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED
$condicaoUg
	MOVC_DH_EXCLUSAO_LOGICA			IS Null
					";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchPairs ( $sql );
			
			// Cria o cache
			$cache->criarCache ( $dados, $cacheId );
		}
		
		return $dados;
	}
	
	/**
	 * Retorna array com campos e registros desejados
	 *
	 * @param	boolean	$solicitacao		Determina se a funcao foi ou não chamada da tela de solicitação
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem($solicitacao = false) {
	    // Determina comportamentos diferenciados para Movimentação de Crédito e suas Solicitações
		if ($solicitacao) {
			$nomeCache = 'novamovimentacaocred';
			$condicaoUg = ' M.MOVC_CD_TIPO_SOLICITACAO = ' . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA . ' AND ';
		} else {
			$nomeCache = 'movimentacaocred';
			$condicaoUg = '';
		}
		
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->gerarID_Listagem ( $nomeCache );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			// Não existindo o cache, busca do banco
			$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
			$ug = $sessaoOrcamento->ug;
			
			// Verifica possível restrição de registros
			$joinUg = "";
			if ($ug != 'todas') {
				$joinUg = "
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA	UNGE ON
		UNGE.UNGE_CD_UG = D1.DESP_CD_UG
							";
				
				$condicaoUg .= "
	UNGE.UNGE_CD_UG NOT IN (90032, 90049, 110407)	AND
	UNGE.UNGE_SG_SECAO = '$ug'						AND
								";
			}
			
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	M.MOVC_CD_MOVIMENTACAO,
	D1.DESP_AA_DESPESA,
        CASE WHEN 
                    D1.DESP_AA_DESPESA = '".date('Y')."' THEN 1
                    ELSE 2
        END AS EXERCICIO, 
	D1.DESP_CD_UG,
	M.MOVC_NR_DESPESA_ORIGEM,
	D1.DESP_CD_PT_RESUMIDO																				AS PTRES_ORIGEM,
	UNOR1.UNOR_CD_UNID_ORCAMENTARIA																		AS UNOR_ORIGEM,
	D1.DESP_CD_ELEMENTO_DESPESA_SUB																		AS NATUREZA_ORIGEM,
	RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO)							AS RESPONSAVEL_ORIGEM,
	M.MOVC_NR_DESPESA_DESTINO,
	D2.DESP_CD_PT_RESUMIDO																				AS PTRES_DESTINO,
	UNOR2.UNOR_CD_UNID_ORCAMENTARIA																		AS UNOR_DESTINO,
	D2.DESP_CD_ELEMENTO_DESPESA_SUB																		AS NATUREZA_DESTINO,
	RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO)							AS RESPONSAVEL_DESTINO,
	TO_CHAR(M.MOVC_DH_MOVIMENTACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')	AS MOVC_DH_MOVIMENTACAO,
	DECODE ( M.MOVC_ID_TIPO_MOVIMENTACAO,
			2, 'Movimentação regular',
			3, 'Alteração na proposta (LOA)  ' )														AS MOVC_ID_TIPO_MOVIMENTACAO,
	T.TSOL_DS_TIPO_SOLICITACAO,
	/* DECODE(M.MOVC_IC_MOVIMENT_REPASSADA, 1, 'Sim', 0, 'Não')											AS MOVC_IC_MOVIMENT_REPASSADA, */
	M.MOVC_VL_MOVIMENTACAO																				AS MOVC_VL_MOVIMENTACAO
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED		M
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO		T ON
		T.TSOL_CD_TIPO_SOLICITACAO		= M.MOVC_CD_TIPO_SOLICITACAO
Left JOIN
	CEO_TB_DESP_DESPESA					D1 ON
		D1.DESP_NR_DESPESA				= M.MOVC_NR_DESPESA_ORIGEM
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO   P1 ON
		P1.PTRS_CD_PT_RESUMIDO = D1.DESP_CD_PT_RESUMIDO

Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR1 ON
        UNOR1.UNOR_CD_UNID_ORCAMENTARIA = P1.PTRS_CD_UNID_ORCAMENTARIA

Left JOIN
	CEO_TB_RESP_RESPONSAVEL				RSP1 ON
		RSP1.RESP_CD_RESPONSAVEL		= D1.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO					RHC1 ON
		RHC1.LOTA_COD_LOTACAO			= RSP1.RESP_CD_LOTACAO				AND
		RHC1.LOTA_SIGLA_SECAO			= RSP1.RESP_DS_SECAO
Left JOIN
	CEO_TB_DESP_DESPESA					D2 ON
		D2.DESP_NR_DESPESA				= M.MOVC_NR_DESPESA_DESTINO
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO   P2 ON
		P2.PTRS_CD_PT_RESUMIDO = D2.DESP_CD_PT_RESUMIDO

Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR2 ON
        UNOR2.UNOR_CD_UNID_ORCAMENTARIA = P2.PTRS_CD_UNID_ORCAMENTARIA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL				RSP2 ON
		RSP2.RESP_CD_RESPONSAVEL		= D2.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO					RHC2 ON
		RHC2.LOTA_COD_LOTACAO			= RSP2.RESP_CD_LOTACAO				AND
		RHC2.LOTA_SIGLA_SECAO			= RSP2.RESP_DS_SECAO
$joinUg
WHERE
$condicaoUg
	M.MOVC_DH_EXCLUSAO_LOGICA			IS Null
        
ORDER BY EXERCICIO
					";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			// Zend_Debug::dump ( $sql );
			// exit;
			
			$dados = $banco->fetchAll ( $sql );
			
			// Cria o cache
			//$cache->criarCache ( $dados, $cacheId );
		}
		
		return $dados;
	}
	
	/**
	 * Retorna um único registro sem uso de ALIAS
	 *
	 * @param	int		$uo					Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($movimentacao) {
		$sql = "
SELECT
	MOVC_CD_MOVIMENTACAO,
	MOVC_NR_DESPESA_ORIGEM,
	MOVC_NR_DESPESA_DESTINO,
	MOVC_VL_MOVIMENTACAO,
	MOVC_DS_JUSTIF_SOLICITACAO,
	MOVC_DS_JUSTIF_SECOR,
	MOVC_DH_MOVIMENTACAO,
	MOVC_ID_TIPO_MOVIMENTACAO,
	MOVC_CD_TIPO_SOLICITACAO
	/* MOVC_IC_MOVIMENT_REPASSADA */
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED
WHERE
	MOVC_CD_MOVIMENTACAO				= $movimentacao     
AND
	MOVC_DH_EXCLUSAO_LOGICA				IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$uo					Chave primária para busca do registro
	 * @param	boolean	$solicitacao		Determina se a funcao foi ou não chamada da tela de solicitação
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($movimentacao, $solicitacao = false) {
		// Determina comportamentos diferenciados para Movimentação de Crédito e suas Solicitações
		$condicaoUg = '';
		if ($solicitacao) {
			$condicaoUg = ' M.MOVC_CD_TIPO_SOLICITACAO = ' . Trf1_Orcamento_Dados::TIPO_SOLICITACAO_SOLICITADA . ' AND ';
		}
		
		// Verifica possível restrição de registros
		$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
		$ug = $sessaoOrcamento->ug;
		
		$joinUg = '';
		if ($ug != 'todas') {
			$joinUg = "
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA	UNGE ON
		UNGE.UNGE_CD_UG = D1.DESP_CD_UG
						";
			
			$condicaoUg .= "
	UNGE_CD_UG NOT IN (90032, 90049, 110407)	AND
	UNGE_SG_SECAO = '$ug'						AND
							";
		}
		
		$sql = "
SELECT
	M.MOVC_CD_MOVIMENTACAO																				AS \"Código da movimentação\",
	TO_CHAR(M.MOVC_DH_MOVIMENTACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')				AS \"Data e hora da solicitação\",
	D1.DESP_AA_DESPESA																					AS \"Ano\",
	D1.DESP_CD_UG || ' - ' || 
	U.UNGE_DS_UG																						AS \"UG\",
	
	M.MOVC_NR_DESPESA_ORIGEM || ' - ' ||
	EDS1.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
	D1.DESP_DS_ADICIONAL																				AS \"Despesa de origem\",
	D1.DESP_CD_PT_RESUMIDO || ' - ' ||
	PTR1.PTRS_DS_PT_RESUMIDO																			AS \"PTRES (origem)\",
	D1.DESP_CD_ELEMENTO_DESPESA_SUB || ' - ' ||
	EDS1.EDSB_DS_ELEMENTO_DESPESA_SUB																	AS \"Natureza (origem)\",
	RHC1.LOTA_SIGLA_LOTACAO || ' - ' || 
	REPLACE(
		RH_DESCRICAO_CENTRAL_LOTACAO(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO),
		'-', ' ') || ' - ' || 
	RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC1.LOTA_SIGLA_SECAO, RHC1.LOTA_COD_LOTACAO)							AS \"Responsável (origem)\",
	
	M.MOVC_NR_DESPESA_DESTINO || ' - ' ||
	EDS2.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
	D2.DESP_DS_ADICIONAL																				AS \"Despesa de destino\",
	D2.DESP_CD_PT_RESUMIDO || ' - ' ||
	PTR2.PTRS_DS_PT_RESUMIDO																			AS \"PTRES (destino)\",
	D2.DESP_CD_ELEMENTO_DESPESA_SUB || ' - ' ||
	EDS2.EDSB_DS_ELEMENTO_DESPESA_SUB																	AS \"Natureza (destino)\",
	RHC2.LOTA_SIGLA_LOTACAO || ' - ' || 
	REPLACE(
		RH_DESCRICAO_CENTRAL_LOTACAO(RHC2.LOTA_SIGLA_SECAO, RHC2.LOTA_COD_LOTACAO),
		'-', ' ') || ' - ' || 
	RH_SIGLAS_FAMILIA_CENTR_LOTA(RHC2.LOTA_SIGLA_SECAO, RHC2.LOTA_COD_LOTACAO)							AS \"Responsável (destino)\",
	
	M.MOVC_DS_JUSTIF_SOLICITACAO																		AS \"Motivo da solicitação\",
	M.MOVC_DS_JUSTIF_SECOR																				AS \"Motivação setorial\",
	DECODE ( M.MOVC_ID_TIPO_MOVIMENTACAO,
			2, 'Movimentação regular',
			3, 'Alteração na proposta (LOA) ' )															AS \"Tipo de movimentação\",
	T.TSOL_DS_TIPO_SOLICITACAO																			AS \"Status da solicitação\",
	/*
	DECODE(M.MOVC_IC_MOVIMENT_REPASSADA,
			1, 'Sim',
			0, 'Não')																					AS \"Repassado\",
	*/
	NVL(M.MOVC_VL_MOVIMENTACAO, 0)                                                                      AS \"Valor\"
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED			M
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			T ON
		T.TSOL_CD_TIPO_SOLICITACAO			= M.MOVC_CD_TIPO_SOLICITACAO
/* Origem */
Left JOIN
	CEO_TB_DESP_DESPESA						D1 ON
		D1.DESP_NR_DESPESA					= M.MOVC_NR_DESPESA_ORIGEM
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTR1 ON
		PTR1.PTRS_CD_PT_RESUMIDO			= D1.DESP_CD_PT_RESUMIDO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDS1 ON
		EDS1.EDSB_CD_ELEMENTO_DESPESA_SUB	= D1.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RSP1 ON
		RSP1.RESP_CD_RESPONSAVEL			= D1.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHC1 ON
		RHC1.LOTA_COD_LOTACAO				= RSP1.RESP_CD_LOTACAO				AND
		RHC1.LOTA_SIGLA_SECAO				= RSP1.RESP_DS_SECAO

/* Destino */
Left JOIN
	CEO_TB_DESP_DESPESA						D2 ON
		D2.DESP_NR_DESPESA					= M.MOVC_NR_DESPESA_DESTINO
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTR2 ON
		PTR2.PTRS_CD_PT_RESUMIDO			= D2.DESP_CD_PT_RESUMIDO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDS2 ON
		EDS2.EDSB_CD_ELEMENTO_DESPESA_SUB	= D2.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RSP2 ON
		RSP2.RESP_CD_RESPONSAVEL			= D2.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHC2 ON
		RHC2.LOTA_COD_LOTACAO				= RSP2.RESP_CD_LOTACAO				AND
		RHC2.LOTA_SIGLA_SECAO				= RSP2.RESP_DS_SECAO
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA				U ON
		U.UNGE_CD_UG						= D1.DESP_CD_UG
$joinUg
WHERE
$condicaoUg
	M.MOVC_CD_MOVIMENTACAO					= $movimentacao						AND
	M.MOVC_DH_EXCLUSAO_LOGICA				IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		/*
		Zend_Debug::dump($sql);
		exit;
		*/
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	array	$chaves				Array de chaves primárias para busca de um ou mais registros
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaVariosRegistros($chaves) {
		$movimentacao = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	MOVC.MOVC_CD_MOVIMENTACAO,
	MOVC.MOVC_CD_MOVIMENTACAO														AS \"Código da movimentação\",
	MOVC.MOVC_NR_DESPESA_ORIGEM || ' - ' ||
	EDSO.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
	DSPO.DESP_DS_ADICIONAL															AS \"Despesa de origem\",
	MOVC.MOVC_NR_DESPESA_DESTINO || ' - ' ||
	EDSD.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
	DSPD.DESP_DS_ADICIONAL															AS \"Despesa de destino\",
	MOVC.MOVC_DS_JUSTIF_SOLICITACAO													AS \"Motivo da solicitação\",
	MOVC.MOVC_DS_JUSTIF_SECOR														AS \"Motivação setorial\",
	DECODE ( MOVC.MOVC_ID_TIPO_MOVIMENTACAO,
			2, 'Movimentação regular',
			3, 'Alteração na proposta (LOA) ' )										AS \"Tipo de movimentação\",
	TSOL.TSOL_DS_TIPO_SOLICITACAO													AS \"Status da solicitação\",
	/*
	DECODE(MOVC.MOVC_IC_MOVIMENT_REPASSADA,
			1, 'Repassado',
			0, 'Não repassado ')													AS \"Status do crédito\",
	*/
	TO_CHAR(MOVC.MOVC_DH_MOVIMENTACAO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')		AS \"Data\",
            DSPO.DESP_AA_DESPESA															AS \"Ano\",
	NVL(MOVC.MOVC_VL_MOVIMENTACAO, 0)                                               AS \"Valor\"
FROM
	CEO_TB_MOVC_MOVIMENTACAO_CRED			MOVC
Left JOIN
	CEO_TB_DESP_DESPESA						DSPO ON
		DSPO.DESP_NR_DESPESA				= MOVC.MOVC_NR_DESPESA_ORIGEM
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSO ON
		EDSO.EDSB_CD_ELEMENTO_DESPESA_SUB	= DSPO.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_DESP_DESPESA						DSPD ON
		DSPD.DESP_NR_DESPESA				= MOVC.MOVC_NR_DESPESA_DESTINO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSD ON
		EDSD.EDSB_CD_ELEMENTO_DESPESA_SUB	= DSPD.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			TSOL ON
		TSOL.TSOL_CD_TIPO_SOLICITACAO		= MOVC.MOVC_CD_TIPO_SOLICITACAO
WHERE
	MOVC.MOVC_CD_MOVIMENTACAO				IN ($movimentacao)
AND
	MOVC.MOVC_DH_EXCLUSAO_LOGICA			IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}
	
	/**
	 * Realiza a exclusão lógica de uma unidade gestora
	 *
	 * @param	array	$chaves				Array de chaves primárias para exclusão de um ou mais registros
	 * @return	none
	 * @author	Dayane Freire / Robson Pereira
	 */
	public function exclusaoLogica($chaves) {
		$movimentacao = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_MOVC_MOVIMENTACAO_CRED
SET
	MOVC_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	MOVC_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	MOVC_CD_MOVIMENTACAO					IN ($movimentacao)
AND
	MOVC_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	/**
	 * Retorna os tipos de movimentação de crédito
	 *
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaTiposDeMovimentacao() {
		return array (/* OBSOLETO! 1 => 'Alteração de QDD', */ 2 => 'Movimentação regular', 3 => 'Alteração na proposta (LOA)' );
	}
	
	/**
	 * Verifica se uma solicitação de movimentação de crédito pode ser atendida automaticamente
	 * 
	 * @param	array		$dados
	 * @param	boolean		$solicitacao		// Se é uma solicitação de movimentação (TRUE) ou não
	 * @param	boolean 	$validaSaldo		// Só valida saldo quando for ATENDER uma movimentação ou solicitação; automaticamente ou não
	 * @param	currency	$valorAnterior		// Acresce o saldo anterior para evitar
	 * @return	array		permissao = (true ou false); mensagem = 'texto' 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function permiteMovimentacao($dados, $solicitacao = true, $validaSaldo = true /*, $valorAnterior = 0*/ ) {
		/* ************************************************************
		 * NOTA:
		 * ************************************************************
		 * Para uma movimentação possa ser atendida é necessário que:
		 * 1) DESPESA de origem e destino devem ser distintas;
		 * 2) ANO da despesa de origem e destino devem ser a mesma;
		 * 3) PTRES da despesa de origem e destino devem ser a mesma;
		 * 4) UG da despesa de origem e destino devem ser a mesma;
		 * 5) NATUREZA da despesa de origem e destino devem ser a mesma - Considerar apenas o ELEMENTO (6 primeiros dígitos) - Comparação não efetuada em movimentações de LOA.
		 * 6) Se for solicitação; Não permite movimentação de LOA; 
		 * 7) Se for solicitação; A combinação de PTRES e NATUREZA não podem estar contidas na listagem de bloqueios de movimentação;
		 * 8) A despesa de origem não pode ter valor negativo no campo [Requisição a empenhar].
		 * 9) A despesa de origem deve possuir [Saldo orçamentário sem requisição] maior ou igual ao valor solicitado; e
		 * 
		 * Após SOSTI: 2013010001155011550160000571, houve mudança nesta função para que as validações fossem distintas ao perfil DIPOR.
		 * No caso, as únicas validações serão:
		 *  
		 */
		$msgBase = 'Solicitação não pode ser atendida automaticamente pois ';
	    $mensagemErro = '';
		
		$despesaOrigem = $dados ['MOVC_NR_DESPESA_ORIGEM'];
		$despesaDestino = $dados ['MOVC_NR_DESPESA_DESTINO'];
		
		// Verifica item 1
		if ($despesaOrigem == $despesaDestino) {
		    $mensagemErro = $msgBase;
			$mensagemErro .= ' as despesas de origem e destino devem ser diferentes.';
			return array ('permissao' => false, 'mensagem' => $mensagemErro );
		}
		
		// Busca dados das despesas, informadas na chamada da função
		$despesa = new Trf1_Orcamento_Negocio_Despesa ();
		$dadosOrigem = $despesa->retornaDespesa ( $despesaOrigem );
		$dadosDestino = $despesa->retornaDespesa ( $despesaDestino );
		
		// Verifica item 2
		if ($dadosOrigem ['DESP_AA_DESPESA'] != $dadosDestino ['DESP_AA_DESPESA']) {
			$mensagemErro = $msgBase;
			$mensagemErro .= ' o ano das despesas de origem e destino deve ser o mesmo.';
			return array ('permissao' => false, 'mensagem' => $mensagemErro );
		}
		
		// Verifica item 3
		if ($dadosOrigem ['DESP_CD_PT_RESUMIDO'] != $dadosDestino ['DESP_CD_PT_RESUMIDO']) {
			$mensagemErro = $msgBase;
			$mensagemErro .= ' o PTRES das despesas de origem e destino deve ser o mesmo.';
			return array ('permissao' => false, 'mensagem' => $mensagemErro );
		}
		
		// Busca perfil para verificações especial da DIPO
		$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
		$perfil = $sessaoOrcamento->perfil;
		
		// Validações a realizar - valores padrão!
		$bValidaUG = true;
		$bValidaNatureza = true;
		$bValidaSaldoRDO = true;
		
		// Se for DIPOR...
		if ($perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR || $perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR) {
			// Se tiver a 90032 envolvida...
			if ($dadosOrigem ['DESP_CD_UG'] == Trf1_Orcamento_Dados::UNIDADE_GESTORA_DIPOR || $dadosDestino ['DESP_CD_UG'] == Trf1_Orcamento_Dados::UNIDADE_GESTORA_DIPOR) {
				// Pode movimentar para qualquer UG, se uma das despesas (origem ou destina) for da 90032
				$bValidaUG = false;
			}
			
			// Faz validação 'alternativa' da Natureza - ...verifica item 5
			if (substr ( $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 2 ) != substr ( $dadosDestino ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 2 )) {
                $mensagemErro = $msgBase;
                $mensagemErro .= ' a natureza (apenas os 2 primeiros dígitos) das despesas de origem e destino deve ser a mesma.';
				return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
			
			$bValidaNatureza = false;
			$bValidaSaldoRDO = false;
		}
		
		// Verifica item 4
		if ($bValidaUG) {
			if ($dadosOrigem ['DESP_CD_UG'] != $dadosDestino ['DESP_CD_UG']) {
				$mensagemErro = $msgBase;
                $mensagemErro .= ' a unidade gestora das despesas de origem e destino deve ser a mesma.';
				return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
		}
		
		// ...verifica item 5
		if ($bValidaNatureza) {
			if (substr ( $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 6 ) != substr ( $dadosDestino ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 6 )) {
				$mensagemErro = $msgBase;
                $mensagemErro .= ' a natureza (apenas os 6 primeiros dígitos) das despesas de origem e destino deve ser a mesma.';
				return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
		}
		
		$bloqueio = new Trf1_Orcamento_Negocio_Bloqueio ();
		
		// Se for uma solicitação de movimentação
		if ($solicitacao) {
			// Verifica item 6
			if ($dados ['MOVC_ID_TIPO_MOVIMENTACAO'] == Trf1_Orcamento_Dados::TIPO_MOVIMENTACAO_CREDITO_PROPOSTA) {
				$mensagemErro = $msgBase;
                $mensagemErro .= ' não é permitido para movimentação de LOA.';
				return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
			
			// Busca dados sobre o bloqueio
			$bTemBloqueio = $bloqueio->retornaBloqueio ( $dadosOrigem ['DESP_CD_PT_RESUMIDO'], $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'] );
			
			// Verifica item 7
			if ($bTemBloqueio) {
				// O registro poderá ser salvo, sem ser atendimento automaticamente ou ainda por força da DIPOR
				$mensagemErro = $msgBase;
                $mensagemErro .= ' há bloqueio de movimentação automática para essa combinação de PTRES e os 6 primeiros dígitos de NATUREZA.';
				return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
		}
		
		if ($validaSaldo) {
			// Busca dados sobre o saldo
			$saldo = new Trf1_Orcamento_Negocio_Saldo ();
			$regraValor = new Trf1_Orcamento_Valor ();
			
			$saldoDespesa = $saldo->retornaSaldo ( $despesaOrigem );
			
			// Verifica item 8
			$valorRequisicaoAEmpenhar = $regraValor->retornaValorParaBancoRod ( $saldoDespesa ['VR_A_EMPENHAR'] );
			if ($valorRequisicaoAEmpenhar < 0) {
			    $mensagemErro = $msgBase;
                $mensagemErro .= ' o valor da Requisição a empenhar não pode estar negativo.';
			    return array ('permissao' => false, 'mensagem' => $mensagemErro );
			}
			
			$valorFormatado = $regraValor->retornaValorParaBancoRod ( $dados ['MOVC_VL_MOVIMENTACAO'] );
			
			// Verifica item 9
			if ($bValidaSaldoRDO) {
				$valorRequisicaoAAutorizar = $regraValor->retornaValorParaBancoRod ( $saldoDespesa ['VR_A_AUTORIZAR'] );
				if ($valorRequisicaoAAutorizar < $valorFormatado) {
					$mensagemErro = $msgBase;
                    $mensagemErro .= ' o Saldo orçamentário sem requisição é insuficiente para essa movimentação.';
					return array ('permissao' => false, 'mensagem' => $mensagemErro );
				}
			}
		}
		
		/*
		// Trecho de código para testes
		$bloq = ($bloqueio->retornaBloqueio ( $dadosOrigem ['DESP_CD_PT_RESUMIDO'], $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'] ) ? 'com bloqueio' : 'sem bloqueio');
		
		echo '<h3>Dados a comparar</h3>';
		echo 'ANO: ' . $dadosOrigem ['DESP_AA_DESPESA'] . ' - ' . $dadosDestino ['DESP_AA_DESPESA'] . '<br />';
		echo 'UG: ' . $dadosOrigem ['DESP_CD_UG'] . ' - ' . $dadosDestino ['DESP_CD_UG'] . '<br />';
		echo 'PTRES: ' . $dadosOrigem ['DESP_CD_PT_RESUMIDO'] . ' - ' . $dadosDestino ['DESP_CD_PT_RESUMIDO'] . '<br />';
		echo 'ELEMENTO: ' . substr ( $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 6 ) . ' - ' . substr ( $dadosDestino ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, 6 ) . '<br />';
		echo 'NATUREZA: ' . $dadosOrigem ['DESP_CD_ELEMENTO_DESPESA_SUB'] . ' - ' . $dadosDestino ['DESP_CD_ELEMENTO_DESPESA_SUB'] . '<br />';
		echo 'Bloqueio (PTRES + NATUREZA): ' . $bloq . '<br />';
		echo 'Valor solicitado: ' . $valorFormatado . '<br />';
		echo 'Requisição a empenhar: ' . $valorRequisicaoAEmpenhar . '<br />';
		echo 'Saldo orçamentário sem requisição: ' . $valorRequisicaoAAutorizar . '<br />';
		
		$v = $regraValor->retornaNumeroFormatado($saldoDespesa ['VR_A_AUTORIZAR']);
		Zend_Debug::dump ( $validaSaldo );
		Zend_Debug::dump ( $saldoDespesa ['VR_A_AUTORIZAR'] );
		Zend_Debug::dump ( $valorRequisicaoAAutorizar );
		Zend_Debug::dump ( $v );
		exit;
		// return true;
		
		// Zend_Debug::dump ( $dadosOrigem );
		// Zend_Debug::dump ( $dadosDestino );
		// exit;
		*/
		
		return array ('permissao' => true, 'mensagem' => 'Solicitação atendida automaticamente!' );
	}

}