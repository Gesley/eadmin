<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Ug
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Unidades gestoras (UG)
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
class Trf1_Orcamento_Negocio_Ug {
	/**
	 * Model da Unidade Gestora
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbUngeUnidadeGestora ();
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
		$cacheId = $cache->retornaID_Combo ( 'ug' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	UNGE_CD_UG,
	UNGE_DS_UG || ' - (' || UNGE_CD_UG || ')'
FROM
	CEO_TB_UNGE_UNIDADE_GESTORA
WHERE
	UNGE_DH_EXCLUSAO_LOGICA IS NULL
ORDER BY
	UNGE_DS_UG
					";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchPairs ( $sql );
			
			// Cria o cache
			$cache->criarCache ( $dados, $cacheId );
		}
		
		return $dados;
	}
	
	/**
	 * Retorna a listagem de todos as UGs
	 *
	 * @param	none
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem() {
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->gerarID_Listagem ( 'ug' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	U.UNGE_CD_UG,
	U.UNGE_DS_UG,
	U.UNGE_SG_UG,
	NVL(P.PADR_DS_UG, 'Sem padrão')		AS PADR_DS_UG
FROM
	CEO_TB_UNGE_UNIDADE_GESTORA			U
LEFT JOIN
	CEO_TB_PADR_PADRAO_UG				P ON
	P.PADR_CD_UG = U.UNGE_CD_UG_PADRAO
WHERE
	U.UNGE_DH_EXCLUSAO_LOGICA			IS Null
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
	 * @param	int		$ug					Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($ug) {
		$sql = "
SELECT
	U.UNGE_CD_UG,
	U.UNGE_DS_UG,
	U.UNGE_SG_UG,
	U.UNGE_CD_UG_PADRAO,
	U.UNGE_SG_SECAO,
	U.UNGE_CD_LOTACAO,
    U.UNGE_CD_SECSUBSEC
FROM
	CEO_TB_UNGE_UNIDADE_GESTORA			U
Left JOIN
	CEO_TB_PADR_PADRAO_UG				P ON
	P.PADR_CD_UG = U.UNGE_CD_UG_PADRAO
Left JOIN
	RH_CENTRAL_LOTACAO					L ON
		L.LOTA_SIGLA_SECAO = U.UNGE_SG_SECAO	AND
		L.LOTA_COD_LOTACAO = U.UNGE_CD_LOTACAO
WHERE
	UNGE_CD_UG							= $ug	AND
	UNGE_DH_EXCLUSAO_LOGICA				IS Null
				";
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 * 
	 * @param	int		$ug					Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($ug) {
		$sql = "
SELECT
	U.UNGE_CD_UG						AS \"UG\",
	U.UNGE_DS_UG						AS \"Descrição\",
	U.UNGE_SG_UG						AS \"Sigla\",
	NVL(P.PADR_DS_UG, 'Sem padrão')		AS \"Padrão\",
	L.LOTA_DSC_LOTACAO					AS \"Vinculada à lotação\"
FROM
	CEO_TB_UNGE_UNIDADE_GESTORA			U
Left JOIN
	CEO_TB_PADR_PADRAO_UG				P ON
	P.PADR_CD_UG = U.UNGE_CD_UG_PADRAO
Left JOIN
	RH_CENTRAL_LOTACAO					L ON
		L.LOTA_SIGLA_SECAO = U.UNGE_SG_SECAO	AND
		L.LOTA_COD_LOTACAO = U.UNGE_CD_LOTACAO
WHERE
	UNGE_CD_UG							= $ug	AND
	UNGE_DH_EXCLUSAO_LOGICA				IS Null
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
		$ugs = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	U.UNGE_CD_UG,
	U.UNGE_CD_UG						AS \"UG\",
	U.UNGE_DS_UG						AS \"Descrição\",
	U.UNGE_SG_UG						AS \"Sigla\",
	NVL(P.PADR_DS_UG, 'Sem padrão')		AS \"Padrão\",
	L.LOTA_DSC_LOTACAO					AS \"Vinculada à lotação\"
FROM
	CEO_TB_UNGE_UNIDADE_GESTORA			U
Left JOIN
	CEO_TB_PADR_PADRAO_UG				P ON
	P.PADR_CD_UG = U.UNGE_CD_UG_PADRAO
Left JOIN
	RH_CENTRAL_LOTACAO					L ON
		L.LOTA_SIGLA_SECAO = U.UNGE_SG_SECAO		AND
		L.LOTA_COD_LOTACAO = U.UNGE_CD_LOTACAO
WHERE
	UNGE_CD_UG							IN ($ugs)	AND
	UNGE_DH_EXCLUSAO_LOGICA				IS Null
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
		$ugs = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_UNGE_UNIDADE_GESTORA
SET
	UNGE_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	UNGE_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	UNGE_CD_UG								IN ($ugs)		AND
	UNGE_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	/**
	 * Retorna dados de dada lotação
	 * 
	 * @param	int		$codigo
	 * @param	string	$sigla
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaLotacao($codigo = null, $sigla = null) {
		$sql = "
SELECT DISTINCT
	LOTA_SIGLA_LOTACAO ||
	' - ' ||
	REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ')
	|| ' - ' ||
	RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO)						AS DESC_LOTACAO
FROM
	RH_CENTRAL_LOTACAO
WHERE
	LOTA_DAT_FIM		IS NULL		AND
	LOTA_COD_LOTACAO	= $codigo	AND
	LOTA_SIGLA_SECAO	= '$sigla'
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
     /**
	 * Retorna dados para uso do AutoComplete no campo de lotação
	 * 
	 * @param	string	$descricao
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaLotacoesAutoComplete($descricao) {
		$sql = "
SELECT DISTINCT
	LOTA_SIGLA_SECAO || ' - ' || LOTA_SIGLA_LOTACAO || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO) AS LABEL,
	LOTA_COD_LOTACAO	AS COD,
	LOTA_SIGLA_SECAO	AS SIGLA
FROM
	RH_CENTRAL_LOTACAO
WHERE
	LOTA_DAT_FIM		IS NULL		AND
	LOTA_SIGLA_SECAO || ' - ' || LOTA_SIGLA_LOTACAO || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO) LIKE UPPER ('%$descricao%') 
ORDER BY
	LOTA_SIGLA_SECAO
				";

		$banco = Zend_Db_Table::getDefaultAdapter ();
		return $banco->fetchAll ( $sql );
	}
	
	public function retornaLotacoesAutoCompleteResp($cod, $sigla, $descricao) {
		$sql = "
SELECT DISTINCT
    UPPER(LOTA_SIGLA_LOTACAO) || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO) AS LABEL,
    LOTA_COD_LOTACAO    AS COD,
    LOTA_SIGLA_SECAO    AS SIGLA        
FROM
    (
    SELECT
        LOTA_SIGLA_SECAO,
        LOTA_COD_LOTACAO,
        LOTA_LOTA_COD_LOTACAO_PAI,
        LOTA_DSC_LOTACAO,
        LOTA_SIGLA_LOTACAO
    FROM
        RH_CENTRAL_LOTACAO
    WHERE
        LOTA_SIGLA_SECAO            = '$sigla'   AND
        LOTA_DAT_FIM                IS NULL
    )
WHERE
	/* UPPER(LOTA_SIGLA_LOTACAO || ' - ' || LOTA_COD_LOTACAO || ' - ' || LOTA_DSC_LOTACAO) LIKE UPPER ('%$descricao%') */
	UPPER(LOTA_SIGLA_LOTACAO) || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO) LIKE UPPER ('%$descricao%')  

CONNECT BY PRIOR
    LOTA_COD_LOTACAO                = LOTA_LOTA_COD_LOTACAO_PAI

START WITH
    LOTA_COD_LOTACAO                = $cod ";
          $banco = Zend_Db_Table::getDefaultAdapter ();
	  return $banco->fetchAll ( $sql ); 
       } 
        
	
	/**
	 * Apresenta dados para exibição no combo de UG com dados vindos da tabela RH_CENTRAL_LOTACAO
	 * 
	 * @param    none
	 * @return   array
	 * @author   Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaComboLotacoes() {
		$sql = "
SELECT
	LOTACOES.LOTA_COD_LOTACAO || '-' || LOTACOES.LOTA_SIGLA_SECAO,
	LOTACOES.LOTA_DSC_LOTACAO,
	LOTACOES.LOTA_SIGLA_SECAO
FROM
	(
	SELECT 01 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    2 AND LOTA_SIGLA_SECAO = 'TR' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 02 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    3 AND LOTA_SIGLA_SECAO = 'AC' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 03 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    4 AND LOTA_SIGLA_SECAO = 'AM' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 04 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    5 AND LOTA_SIGLA_SECAO = 'AP' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 05 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    6 AND LOTA_SIGLA_SECAO = 'BA' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 06 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    7 AND LOTA_SIGLA_SECAO = 'DF' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 07 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    8 AND LOTA_SIGLA_SECAO = 'GO' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 08 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =    9 AND LOTA_SIGLA_SECAO = 'MA' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 09 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   10 AND LOTA_SIGLA_SECAO = 'MG' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 10 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   11 AND LOTA_SIGLA_SECAO = 'MT' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 11 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   12 AND LOTA_SIGLA_SECAO = 'PA' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 12 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   13 AND LOTA_SIGLA_SECAO = 'PI' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 13 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   14 AND LOTA_SIGLA_SECAO = 'RO' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 14 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   15 AND LOTA_SIGLA_SECAO = 'RR' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 15 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO =   16 AND LOTA_SIGLA_SECAO = 'TO' AND LOTA_DAT_FIM IS NULL UNION ALL
	SELECT 16 AS SEQUENCIA, LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO, REPLACE(REPLACE(REPLACE(REPLACE(LOTA_DSC_LOTACAO, 'SECAO', 'SEÇÃO'), 'JUDICIARIA', 'JUDICIÁRIA'), '  ', ' '), 'DO ESTADO ', '') AS LOTA_DSC_LOTACAO FROM RH_CENTRAL_LOTACAO WHERE LOTA_COD_LOTACAO = 1100 AND LOTA_SIGLA_SECAO = 'TR' AND LOTA_DAT_FIM IS NULL 
	) LOTACOES
ORDER BY
	LOTACOES.SEQUENCIA
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchPairs ( $sql );
	}

    
    public function comboSecSubsecao(){
        $sql = "SELECT  SESU_CD_SECSUBSEC, 
                        SESU_DS_SECSUBSEC
                   FROM P_SECAO_SUBSECAO 
                  WHERE SESU_CD_SECSUBSEC NOT IN (200,300,400,500)
                    AND SESU_TP_SECSUBSEC = 1 
               ORDER BY SESU_TP_SECSUBSEC,SESU_CD_SECSUBSEC";
    	$banco = Zend_Db_Table::getDefaultAdapter ();
		return $banco->fetchPairs ( $sql );
    }
   
     public function secsubsecaoDespesa($despesa){
        $sql = " SELECT
                        U.UNGE_CD_SECSUBSEC
                   FROM
                       CEO_TB_UNGE_UNIDADE_GESTORA  U
              Left JOIN
                      CEO_TB_DESP_DESPESA             DESP ON
                      DESP.DESP_CD_UG = U.UNGE_CD_UG
                  WHERE
                      DESP.DESP_NR_DESPESA  = $despesa";
        
    	$banco = Zend_Db_Table::getDefaultAdapter ();
       return $banco->fetchRow ( $sql );
    }
   
    public function retornaUG($descricao){
    	$sql = "
				SELECT					
					UNGE_CD_UG || ' - (' || UNGE_DS_UG || ')' as label
				FROM
					CEO_TB_UNGE_UNIDADE_GESTORA
				WHERE
					UNGE_DH_EXCLUSAO_LOGICA IS NULL
				  AND UNGE_DS_UG || ' - (' || UNGE_CD_UG || ')' LIKE UPPER ('%$descricao%') 
				ORDER BY
					UNGE_DS_UG
    	";
    	$banco = Zend_Db_Table::getDefaultAdapter ();
       return $banco->fetchAll ( $sql );
    }

}
