<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Recursodesc
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Recurso a Descentralizar
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
class Trf1_Orcamento_Negocio_Recursodesc {
	/**
	 * Model dos Tipos de Solicitações
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbRecdRecursoDescent();
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
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->retornaID_Combo ( 'recursodesc' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	RECD_CD_RECURSO,
	RECD_NR_DESPESA || ' - ' || RECD_DS_JUSTIFICATIVA,
FROM
	CEO_TB_RECD_RECURSO_DESCENT
WHERE
	RECD_DH_EXCLUSAO_LOGICA					IS Null
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
	 * @param	none
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem() {
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->gerarID_Listagem ( 'recursodesc', array ( '$bDadosSensiveis' => true ) );
		$dados = $cache->lerCache ( $cacheId );
		
                $condicao = CEO_PERMISSAO_RESPONSAVEIS;
                
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	RECD.RECD_CD_RECURSO,
    DESP.DESP_AA_DESPESA AS RECD_ANO,
    CASE WHEN 
                DESP.DESP_AA_DESPESA = '".date('Y')."' THEN 1
                ELSE 2
    END AS EXERCICIO,     
    DESP.DESP_CD_UG,
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)	AS SG_FAMILIA_RESPONSAVEL,
    RECD.RECD_NR_DESPESA,
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
        DESP.DESP_DS_ADICIONAL													AS DESP_DS_DESPESA,
    TO_CHAR(RECD.RECD_DT_RECURSO, '" . 
		Trf1_Orcamento_Definicoes::FORMATO_DATA . "')		AS DT_RECURSO,
	DESP.DESP_CD_PT_RESUMIDO,
	UNOR.UNOR_CD_UNID_ORCAMENTARIA,
	PTRS.PTRS_SG_PT_RESUMIDO,
	DESP.DESP_CD_ELEMENTO_DESPESA_SUB,
	TIDE.TIDE_DS_TIPO_DESPESA,
	RECD.RECD_DS_JUSTIFICATIVA,
	DECODE(RECD.RECD_IC_RECURSO, 0, 'Não ', '1', 'Sim')							AS RECD_IC_RECURSO,
	RECD.RECD_VL_RECURSO
FROM
	CEO_TB_RECD_RECURSO_DESCENT				RECD
Left JOIN
	CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= RECD.RECD_NR_DESPESA
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTRS ON
		DESP.DESP_CD_PT_RESUMIDO            = PTRS.PTRS_CD_PT_RESUMIDO
Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON
        UNOR.UNOR_CD_UNID_ORCAMENTARIA = 	PTRS.PTRS_CD_UNID_ORCAMENTARIA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO				AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
		EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB	= DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
		TIDE.TIDE_CD_TIPO_DESPESA			= DESP.DESP_CD_TIPO_DESPESA
WHERE
	RECD.RECD_DH_EXCLUSAO_LOGICA			IS Null

$condicao

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
	 * @param	int		$tiposolicitacao	Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($recurso) {
		$sql = "
SELECT
	RECD_CD_RECURSO,
	RECD_NR_DESPESA,
	RECD_DS_JUSTIFICATIVA,
	RECD_VL_RECURSO,
	TO_CHAR(RECD_DT_RECURSO,'DD/MM/YYYY')	AS RECD_DT_RECURSO,
	RECD_IC_RECURSO
FROM
	CEO_TB_RECD_RECURSO_DESCENT
WHERE
    RECD_CD_RECURSO             =  $recurso  AND
	RECD_DH_EXCLUSAO_LOGICA		IS Null 
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$tiposolicitacao			Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($recurso) {
		$sql = "
SELECT
    RECD.RECD_CD_RECURSO                                                        AS \"Código\",
    DESP.DESP_AA_DESPESA                                                        AS \"Ano\",
    DESP.DESP_CD_UG                                                             AS \"UG\",
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)  AS \"Responsável\",
    RECD.RECD_NR_DESPESA                                                        AS \"Despesa\",
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
		DESP.DESP_DS_ADICIONAL                                                  AS \"Descrição da despesa\",
    TO_CHAR(RECD.RECD_DT_RECURSO, '" .
        Trf1_Orcamento_Definicoes::FORMATO_DATA . "')       AS \"Data\",
    DESP.DESP_CD_PT_RESUMIDO                                                    AS \"PTRES\",
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB                                           AS \"Natureza da despesa\",
    TIDE.TIDE_DS_TIPO_DESPESA                                                   AS \"Caráter da despesa\",
    RECD.RECD_DS_JUSTIFICATIVA                                                  AS \"Justificativa\",
    DECODE(RECD.RECD_IC_RECURSO, 0, 'Não ', '1', 'Sim')                         AS \"Descentralizado ?\",
    NVL(RECD.RECD_VL_RECURSO, 0)                                                AS \"Valor\"
FROM
	CEO_TB_RECD_RECURSO_DESCENT				RECD
Left JOIN
	CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= RECD.RECD_NR_DESPESA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO				AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
		EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB	= DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
		TIDE.TIDE_CD_TIPO_DESPESA			= DESP.DESP_CD_TIPO_DESPESA
WHERE
	RECD.RECD_DH_EXCLUSAO_LOGICA			IS Null								AND
	RECD.RECD_CD_RECURSO					= $recurso
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
		$recursos = implode ( ', ', $chaves );
		
		$sql = "
SELECT
    RECD_CD_RECURSO,
    RECD.RECD_CD_RECURSO                                                        AS \"Código\",
    DESP.DESP_AA_DESPESA                                                        AS \"Ano\",
    DESP.DESP_CD_UG                                                             AS \"UG\",
    RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO)  AS \"Responsável\",
    RECD.RECD_NR_DESPESA                                                        AS \"Despesa\",
    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
		DESP.DESP_DS_ADICIONAL                                                  AS \"Descrição da despesa\",
    TO_CHAR(RECD.RECD_DT_RECURSO, '" .
        Trf1_Orcamento_Definicoes::FORMATO_DATA . "')       AS \"Data\",
    DESP.DESP_CD_PT_RESUMIDO                                                    AS \"PTRES\",
    DESP.DESP_CD_ELEMENTO_DESPESA_SUB                                           AS \"Natureza da despesa\",
    TIDE.TIDE_DS_TIPO_DESPESA                                                   AS \"Caráter da despesa\",
    RECD.RECD_DS_JUSTIFICATIVA                                                  AS \"Justificativa\",
    DECODE(RECD.RECD_IC_RECURSO, 0, 'Não ', '1', 'Sim')                         AS \"Descentralizado ?\",
    NVL(RECD.RECD_VL_RECURSO, 0)                                                AS \"Valor\"
FROM
	CEO_TB_RECD_RECURSO_DESCENT				RECD
Left JOIN
	CEO_TB_DESP_DESPESA						DESP ON
		DESP.DESP_NR_DESPESA				= RECD.RECD_NR_DESPESA
Left JOIN
	CEO_TB_RESP_RESPONSAVEL					RESP ON
		RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
Left JOIN
	RH_CENTRAL_LOTACAO						RHCL ON
		RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO				AND
		RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
Left JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP			EDSB ON
		EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB	= DESP.DESP_CD_ELEMENTO_DESPESA_SUB
Left JOIN
	CEO_TB_TIDE_TIPO_DESPESA				TIDE ON
		TIDE.TIDE_CD_TIPO_DESPESA			= DESP.DESP_CD_TIPO_DESPESA
WHERE
	RECD.RECD_DH_EXCLUSAO_LOGICA			IS Null								AND
	RECD.RECD_CD_RECURSO					IN ($recursos)
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
		$recursos = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_RECD_RECURSO_DESCENT
SET
	RECD_CD_MATRICULA_EXCLUSAO	= '$sessao->matricula',
	RECD_DH_EXCLUSAO_LOGICA		= SYSDATE
WHERE
	RECD_CD_RECURSO             IN ($recursos)
AND
	RECD_DH_EXCLUSAO_LOGICA		IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
}