<?php
/**
 * Classe negocial sobre Orçamento - Novas despesas (solicitações de...)
 *
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Novadespesa
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
class Trf1_Orcamento_Negocio_Novadespesa {
	/**
	 * Model das Solicitações de Novas Despesas
	 */
	protected $_dados = null;

	/**
	 * Classe construtora
	 *
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//Perfil
        $this->SessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $this->perfil = $this->SessaoOrcamento->perfil;		
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbSoldSolicitacaoDesp ();
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
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaCombo() {
		return false;
	}

	/**
	 * Retorna array com campos e registros desejados
	 *
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem() {
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->gerarID_Listagem ( 'novadespesa' );
		$dados = $cache->lerCache ( $cacheId );

		$condicaoResponsaveis = str_replace('DESP.DESP_CD_UG', 'SOLD.SOLD_CD_UG', CEO_PERMISSAO_RESPONSAVEIS);

		if($this->perfil == 'planejamento')
        {
        	$campo = 'SOLD_DS_DESPESA,';
        	$campods = "SOLD.SOLD_DS_DESPESA AS \"Descrição da despesa\",";
        }		

		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	SOLD.SOLD_NR_SOLICITACAO,
	SOLD.SOLD_NR_DESPESA,
	$campo
	SOLD.SOLD_AA_SOLICITACAO,
        CASE WHEN 
                    SOLD.SOLD_AA_SOLICITACAO = '".date('Y')."' THEN 1
                    ELSE 2
        END AS EXERCICIO,         
	SOLD.SOLD_CD_UG,
	SOLD_DS_JUSTIFICATIVA_SOLICIT,
	SOLD_DS_JUSTIFICATIVA_SECOR,
	RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)		AS SG_FAMILIA_RESPONSAVEL,
	SOLD.SOLD_CD_PT_RESUMIDO,
	UNOR.UNOR_CD_UNID_ORCAMENTARIA,
        P.PTRS_SG_PT_RESUMIDO,
	SOLD.SOLD_CD_ELEMENTO_DESPESA_SUB,
	TIDE.TIDE_DS_TIPO_DESPESA,
	DECODE(SOLD.SOLD_NR_PRIORIDADE, 1, 'Alta', 2, 'Média ', 3, 'Baixa')				AS SOLD_NR_PRIORIDADE,
	TSOL.TSOL_DS_TIPO_SOLICITACAO,
	SOLD.SOLD_VL_SOLICITADO,
	SOLD.SOLD_VL_ATENDIDO
FROM
	CEO_TB_SOLD_SOLIC_DESPESA				SOLD
        
Left JOIN CEO_TB_PTRS_PROGRAMA_TRABALHO                                 P ON
        P.PTRS_CD_PT_RESUMIDO = SOLD.SOLD_CD_PT_RESUMIDO

Left JOIN CEO_TB_UNOR_UNID_ORCAMENTARIA 								UNOR ON
		UNOR.UNOR_CD_UNID_ORCAMENTARIA = P.PTRS_CD_UNID_ORCAMENTARIA

Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= SOLD.SOLD_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO					AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
		TIDE.TIDE_CD_TIPO_DESPESA			= SOLD.SOLD_CD_TIPO_DESPESA
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			TSOL ON
		TSOL.TSOL_CD_TIPO_SOLICITACAO		= SOLD.SOLD_CD_TIPO_SOLICITACAO
WHERE
	SOLD.SOLD_DH_EXCLUSAO_LOGICA			IS NULL
	$condicaoResponsaveis

ORDER BY EXERCICIO
					";

			$banco = Zend_Db_Table::getDefaultAdapter ();

			$dados = $banco->fetchAll ( $sql );

			// Cria o cache
			$cache->criarCache ( $dados, $cacheId );
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
	public function retornaRegistro($solicitacao) {
		$sql = "
SELECT
	SOLD.SOLD_NR_SOLICITACAO,
    SOLD.SOLD_AA_SOLICITACAO,
    SOLD.SOLD_CD_UG,
    SOLD.SOLD_CD_TIPO_DESPESA,
    SOLD.SOLD_CD_TIPO_SOLICITACAO,
    SOLD.SOLD_CD_RESPONSAVEL,
    SOLD.SOLD_VL_SOLICITADO,
    SOLD.SOLD_DS_JUSTIFICATIVA_SOLICIT,
    SOLD.SOLD_DS_JUSTIFICATIVA_SECOR,
    SOLD.SOLD_DT_SOLICITACAO,
    SOLD.SOLD_NR_PRIORIDADE,
    SOLD.SOLD_CD_PT_RESUMIDO,
    SOLD.SOLD_CD_ELEMENTO_DESPESA_SUB,
    SOLD.SOLD_IC_REC_DESCENTRALIZADO,
    SOLD.SOLD_NR_DESPESA,
    SOLD.SOLD_VL_ATENDIDO,
    RHCL.LOTA_SIGLA_LOTACAO || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS RESPONSAVEL,
    TIDE.TIDE_DS_TIPO_DESPESA,
	TSOL.TSOL_DS_TIPO_SOLICITACAO,
	SOLD_NR_REC_DESCENTRALIZAR,
	SOLD_DS_DESPESA
FROM
	CEO_TB_SOLD_SOLIC_DESPESA				SOLD
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= SOLD.SOLD_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO					AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
		TIDE.TIDE_CD_TIPO_DESPESA			= SOLD.SOLD_CD_TIPO_DESPESA
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			TSOL ON
		TSOL.TSOL_CD_TIPO_SOLICITACAO		= SOLD.SOLD_CD_TIPO_SOLICITACAO
WHERE
	SOLD.SOLD_DH_EXCLUSAO_LOGICA			IS NULL
AND
    SOLD.SOLD_NR_SOLICITACAO = $solicitacao
				";

		$banco = Zend_Db_Table::getDefaultAdapter ();

		return $banco->fetchRow ( $sql );
	}

	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$uo					Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($solicitacao) {
		$formatoDinheiro = Trf1_Orcamento_Definicoes::FORMATO_DINHEIRO;
		if($this->perfil == 'planejamento')
        {
        	$campo = 'SOLD_DS_DESPESA,';
        	$campods = "SOLD.SOLD_DS_DESPESA AS \"Descrição da despesa\",";
        }

		$sql = "
SELECT
    SOLD.SOLD_NR_SOLICITACAO																					AS \"Código da solicitação\",
    $campods
	CASE
		WHEN SOLD_NR_DESPESA IS NULL THEN 'Não informada'
	ELSE
		SOLD.SOLD_NR_DESPESA || ' - ' ||
		EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
		DESP.DESP_DS_ADICIONAL
	END																											AS \"Despesa\",
    SOLD.SOLD_AA_SOLICITACAO																					AS \"Ano\",
    SOLD.SOLD_CD_UG || ' - ' ||
    UNGE_DS_UG																									AS \"UG\",
    RHCL.LOTA_SIGLA_LOTACAO || ' - ' ||
    REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' ||
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)									AS \"Responsável\",
    UPPER(PTRS.PTRS_CD_PT_RESUMIDO || ' - ' ||
    PTRS_SG_PT_RESUMIDO || ' - ' ||
    PTRS.PTRS_DS_PT_RESUMIDO)																					AS \"Ptres\",
    EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' ||
    UPPER(EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB)																	AS \"Elemento\",
    TIDE.TIDE_DS_TIPO_DESPESA																					AS \"Tipo de Despesa\",
    TSOL.TSOL_DS_TIPO_SOLICITACAO																				AS \"Tipo de Solicitação\",
    DECODE(SOLD.SOLD_NR_PRIORIDADE, 1, 'Alta', 2, 'Média ', 3, 'Baixa')											AS \"Prioridade\",
	NVL(SOLD.SOLD_VL_SOLICITADO, 0)                                                                             AS \"Valor Solicitado\",
    NVL(SOLD.SOLD_VL_ATENDIDO, 0)                                                                               AS \"Valor Atendido\",
    SOLD.SOLD_DS_JUSTIFICATIVA_SOLICIT																			AS \"Justificativa\",
    SOLD.SOLD_DS_JUSTIFICATIVA_SECOR																			AS \"Motivação setorial\"
FROM
	CEO_TB_SOLD_SOLIC_DESPESA				SOLD
Left JOIN
	CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= SOLD.SOLD_NR_DESPESA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
	RESP.RESP_CD_RESPONSAVEL				= SOLD.SOLD_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
	RHCL.LOTA_COD_LOTACAO					= RESP.RESP_CD_LOTACAO					AND
	RHCL.LOTA_SIGLA_SECAO					= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
	TIDE.TIDE_CD_TIPO_DESPESA				= SOLD.SOLD_CD_TIPO_DESPESA
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			TSOL ON
	TSOL.TSOL_CD_TIPO_SOLICITACAO			= SOLD.SOLD_CD_TIPO_SOLICITACAO
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA				UG ON
	UG.UNGE_CD_UG							= SOLD.SOLD_CD_UG
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTRS ON
	PTRS.PTRS_CD_PT_RESUMIDO				= SOLD.SOLD_CD_PT_RESUMIDO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
	EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB		= SOLD.SOLD_CD_ELEMENTO_DESPESA_SUB
WHERE
	SOLD.SOLD_DH_EXCLUSAO_LOGICA			IS NULL
AND
	SOLD.SOLD_NR_SOLICITACAO				= $solicitacao
				";

		$banco = Zend_Db_Table::getDefaultAdapter ();
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
		$solicitacao = implode ( ', ', $chaves );
		$formatoDinheiro = Trf1_Orcamento_Definicoes::FORMATO_DINHEIRO;
		$sql = "
SELECT
    SOLD.SOLD_NR_SOLICITACAO,
    SOLD.SOLD_NR_SOLICITACAO                                                                                    AS \"Código da solicitação\",
	CASE
		WHEN SOLD_NR_DESPESA IS NULL THEN 'Não informada'
	ELSE
		SOLD.SOLD_NR_DESPESA || ' - ' ||
		EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
		DESP.DESP_DS_ADICIONAL
	END																											AS \"Despesa\",
    SOLD.SOLD_AA_SOLICITACAO																					AS \"Ano\",
    SOLD.SOLD_CD_UG || ' - ' || UNGE_DS_UG																		AS \"UG\",
    RHCL.LOTA_SIGLA_LOTACAO || ' - ' ||
    REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' ||
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)									AS \"Responsável\",
    UPPER(PTRS.PTRS_CD_PT_RESUMIDO || ' - ' ||
    PTRS_SG_PT_RESUMIDO || ' - ' ||
    PTRS.PTRS_DS_PT_RESUMIDO)																					AS \"Ptres\",
    EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' ||
    UPPER(EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB)																	AS \"Elemento\",
    TIDE.TIDE_DS_TIPO_DESPESA																					AS \"Tipo de Despesa\",
    SOLD_CD_TIPO_SOLICITACAO																					AS \"Tipo\",
    TSOL.TSOL_DS_TIPO_SOLICITACAO																				AS \"Tipo de Solicitação\",
    DECODE(SOLD.SOLD_NR_PRIORIDADE, 1, 'Alta', 2, 'Média ', 3, 'Baixa')											AS \"Prioridade\",
    TO_CHAR(NVL(SOLD.SOLD_VL_SOLICITADO, 0), '" . $formatoDinheiro . "')										AS \"Valor Solicitado\",
    TO_CHAR(NVL(SOLD.SOLD_VL_ATENDIDO, 0), '" . $formatoDinheiro . "')											AS \"Valor Atendido\",
    SOLD.SOLD_DS_JUSTIFICATIVA_SOLICIT																			AS \"Justificativa\",
    SOLD.SOLD_DS_JUSTIFICATIVA_SECOR																			AS \"Motivação setorial\"
FROM
	CEO_TB_SOLD_SOLIC_DESPESA				SOLD
Left JOIN
	CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= SOLD.SOLD_NR_DESPESA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
	RESP.RESP_CD_RESPONSAVEL				= SOLD.SOLD_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
	RHCL.LOTA_COD_LOTACAO					= RESP.RESP_CD_LOTACAO					AND
	RHCL.LOTA_SIGLA_SECAO					= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
	TIDE.TIDE_CD_TIPO_DESPESA				= SOLD.SOLD_CD_TIPO_DESPESA
Left JOIN
	CEO_TB_TSOL_TIPO_SOLICITACAO			TSOL ON
	TSOL.TSOL_CD_TIPO_SOLICITACAO			= SOLD.SOLD_CD_TIPO_SOLICITACAO
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA				UG ON
	UG.UNGE_CD_UG							= SOLD.SOLD_CD_UG
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTRS ON
	PTRS.PTRS_CD_PT_RESUMIDO				= SOLD.SOLD_CD_PT_RESUMIDO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
	EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB		= SOLD.SOLD_CD_ELEMENTO_DESPESA_SUB
WHERE
	SOLD.SOLD_DH_EXCLUSAO_LOGICA			IS NULL
AND
	SOLD.SOLD_NR_SOLICITACAO				IN ($solicitacao)
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
		$solicitacao = implode ( ', ', $chaves );

		$sessao = new Zend_Session_Namespace ( 'userNs' );

		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_SOLD_SOLIC_DESPESA
SET
    SOLD_CD_MATRICULA_EXCLUSAO 	= '$sessao->matricula',
    SOLD_DH_EXCLUSAO_LOGICA 	= SYSDATE
WHERE
	SOLD_NR_SOLICITACAO			IN ($solicitacao)		AND
	SOLD_DH_EXCLUSAO_LOGICA		IS NULL
				";

		$banco = Zend_Db_Table::getDefaultAdapter ();

		$banco->query ( $sql );
	}

	public function atualizaRecurso ( $chavePrimaria, $recurso ) {
		$sql = "
			UPDATE
				CEO_TB_SOLD_SOLIC_DESPESA
			SET
				SOLD_NR_REC_DESCENTRALIZAR = $recurso
			WHERE
				SOLD_NR_SOLICITACAO = $chavePrimaria
			AND
				SOLD_NR_REC_DESCENTRALIZAR IS NULL
			";

			$banco = Zend_Db_Table::getDefaultAdapter ();

			$banco->query ( $sql );
	}

	public function removeSolicitacaoDespesa($idRec) {
		$sql = "
			UPDATE
				CEO_TB_SOLD_SOLIC_DESPESA
			SET
				SOLD_NR_REC_DESCENTRALIZAR = NULL
			WHERE
				SOLD_NR_REC_DESCENTRALIZAR = $idRec
			AND
				SOLD_NR_REC_DESCENTRALIZAR IS NOT NULL
			";

			$banco = Zend_Db_Table::getDefaultAdapter ();

			$banco->query ( $sql );
	}

	public function retornaTravaProjecao($ug) {
		$sql = "
		SELECT
			*
		FROM
			CEO.CEO_TB_TRVP_TRAVA_PROJECAO
		WHERE
			TRVP_CD_UG = $ug
		";

		$banco = Zend_Db_Table::getDefaultAdapter ();

		return $banco->fetchAll ( $sql );
	}

}
