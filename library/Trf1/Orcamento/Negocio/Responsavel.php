<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Responsavel
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Responsáveis
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
class Trf1_Orcamento_Negocio_Responsavel {
	/**
	 * Model do Responsável
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbRespResponsavel ();
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
		$cacheId = $cache->retornaID_Combo ( 'programa' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	L.LOTA_SIGLA_LOTACAO,
	L.LOTA_DSC_LOTACAO
FROM
	CEO_TB_RESP_RESPONSAVEL				R
Left JOIN
	RH_CENTRAL_LOTACAO					L ON
		L.LOTA_COD_LOTACAO				= R.RESP_CD_LOTACAO		AND
		L.LOTA_SIGLA_SECAO				= R.RESP_DS_SECAO
WHERE 
		R.RESP_DH_EXCLUSAO_LOGICA		IS Null
ORDER BY
	L.LOTA_DSC_LOTACAO
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
		$cacheId = $cache->gerarID_Listagem ( 'responsavel' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql_OLD = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	L.LOTA_SIGLA_LOTACAO,
	L.LOTA_DSC_LOTACAO
FROM
	CEO_TB_RESP_RESPONSAVEL			R
Left JOIN
	RH_CENTRAL_LOTACAO				L ON
		L.LOTA_COD_LOTACAO			= R.RESP_CD_LOTACAO		AND
		L.LOTA_SIGLA_SECAO			= R.RESP_DS_SECAO		AND
		L.LOTA_DAT_FIM				IS Null
WHERE 
	R.RESP_DH_EXCLUSAO_LOGICA		IS Null
					";
			
			$sql = "
SELECT
    RESP.RESP_CD_RESPONSAVEL,
    RHCL.LOTA_SIGLA_LOTACAO,
    RHCL.LOTA_DSC_LOTACAO,
    RH_SIGLAS_FAMILIA_CENTR_LOTA  (
        RHCL.LOTA_SIGLA_SECAO,
		RHCL.LOTA_COD_LOTACAO     )     AS SG_FAMILIA_RESPONSAVEL,
    RHCL.LOTA_SIGLA_SECAO
FROM
    CEO_TB_RESP_RESPONSAVEL             RESP
Left JOIN
    RH_CENTRAL_LOTACAO                  RHCL ON
        RHCL.LOTA_COD_LOTACAO              = RESP.RESP_CD_LOTACAO     AND
        RHCL.LOTA_SIGLA_SECAO              = RESP.RESP_DS_SECAO
WHERE 
    RESP.RESP_DH_EXCLUSAO_LOGICA       IS Null
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
	 * @param	int		$programa			Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($responsavel) {
		$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	L.LOTA_DSC_LOTACAO,
	R.RESP_CD_LOTACAO,
	R.RESP_DS_SECAO,
    L.LOTA_SIGLA_LOTACAO,
    RH_BUSCA_CENTRAL_LOTACAO_SECAO(R.RESP_DS_SECAO, L.LOTA_COD_LOTACAO) || '-' || R.RESP_DS_SECAO	AS UG,
    UPPER(LOTA_SIGLA_LOTACAO) || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO, LOTA_COD_LOTACAO) AS RESPONSAVEL
FROM
	CEO_TB_RESP_RESPONSAVEL                 R
Left JOIN
	RH_CENTRAL_LOTACAO			L ON
		L.LOTA_COD_LOTACAO		= R.RESP_CD_LOTACAO		AND
		L.LOTA_SIGLA_SECAO		= R.RESP_DS_SECAO		AND
		L.LOTA_DAT_FIM			IS Null
WHERE 
	RESP_CD_RESPONSAVEL			= $responsavel			AND
	RESP_DH_EXCLUSAO_LOGICA		IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}

	/*
	 * Ajax - Busca de responsaveis
	 */
	
	
public function retornaResponsaveisAutoComplete($ug, $descricao) {
			$sql = "SELECT
                          RHCL.LOTA_SIGLA_LOTACAO || ' - ' || REPLACE(RH_DESCRICAO_CENTRAL_LOTACAO(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO), '-', ' ') || ' - ' || RH_SIGLAS_FAMILIA_CENTR_LOTA(RHCL.LOTA_SIGLA_SECAO, RHCL.LOTA_COD_LOTACAO) AS LABEL,
                          RESP.RESP_CD_RESPONSAVEL AS COD
                      FROM
                          CEO_TB_RESP_RESPONSAVEL   RESP
                      Left JOIN
                          RH_CENTRAL_LOTACAO        RHCL ON
                          RHCL.LOTA_COD_LOTACAO  =  RESP.RESP_CD_LOTACAO                  
                       AND
                          RHCL.LOTA_SIGLA_SECAO  = RESP.RESP_DS_SECAO
                       Left JOIN
                          CEO_TB_UNGE_UNIDADE_GESTORA   UG ON
                          UG.UNGE_SG_SECAO = RHCL.LOTA_SIGLA_SECAO
                      WHERE
                          RHCL.LOTA_DAT_FIM  IS NULL     
                        AND
                          UPPER(RHCL.LOTA_SIGLA_LOTACAO || ' - ' || RHCL.LOTA_COD_LOTACAO || ' - ' || RHCL.LOTA_DSC_LOTACAO) LIKE UPPER ('%$descricao%')
                        AND 
                          UG.UNGE_CD_UG = $ug
                       AND
                          RESP_DH_EXCLUSAO_LOGICA IS NULL";
		$banco = Zend_Db_Table::getDefaultAdapter ();
		return $banco->fetchAll ( $sql );
	}
	
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$programa			Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($responsavel) {
		$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL AS \"Responsável\",
	L.LOTA_SIGLA_LOTACAO AS \"Sigla\",
    L.LOTA_DSC_LOTACAO AS \"Descrição\"
FROM
	CEO_TB_RESP_RESPONSAVEL							R
Left JOIN
	RH_CENTRAL_LOTACAO								L ON
	L.LOTA_COD_LOTACAO		= R.RESP_CD_LOTACAO		AND
	L.LOTA_SIGLA_SECAO		= R.RESP_DS_SECAO		AND
	L.LOTA_DAT_FIM			IS Null
WHERE
	RESP_CD_RESPONSAVEL		= $responsavel			AND
	RESP_DH_EXCLUSAO_LOGICA	IS Null
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
		$responsavel = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	R.RESP_CD_RESPONSAVEL AS \"Responsável\",
	L.LOTA_SIGLA_LOTACAO AS \"Sigla\",
    L.LOTA_DSC_LOTACAO AS \"Descrição\"
FROM
	CEO_TB_RESP_RESPONSAVEL                 R
Left JOIN
	RH_CENTRAL_LOTACAO			L ON
	L.LOTA_COD_LOTACAO		= R.RESP_CD_LOTACAO			AND
	L.LOTA_SIGLA_SECAO		= R.RESP_DS_SECAO			AND
	L.LOTA_DAT_FIM			IS Null
WHERE 
    RESP_CD_RESPONSAVEL         IN ($responsavel)  AND
	RESP_DH_EXCLUSAO_LOGICA     IS Null

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
		$responsavel = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_RESP_RESPONSAVEL
SET
	RESP_CD_MATRICULA_EXCLUSAO		= '$sessao->matricula',
	RESP_DH_EXCLUSAO_LOGICA			= SYSDATE
WHERE
	RESP_CD_RESPONSAVEL				IN ($responsavel)		AND
	RESP_DH_EXCLUSAO_LOGICA			IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	/*
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 * 
	 */
	
	/**
	 * Apresenta dados (código e descrição) para montagem de combos
	 *
	 * @param	none
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getCombo2() {
		$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	L.LOTA_DSC_LOTACAO
FROM
	CEO_TB_RESP_RESPONSAVEL				R
Left JOIN
	RH_CENTRAL_LOTACAO					L ON
		L.LOTA_COD_LOTACAO				= R.RESP_CD_LOTACAO		AND
		L.LOTA_SIGLA_SECAO				= R.RESP_DS_SECAO
WHERE 
		R.RESP_DH_EXCLUSAO_LOGICA		IS Null
ORDER BY
	L.LOTA_DSC_LOTACAO
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchPairs ( $sql );
	}
	
	/**
	 * Retorna a relação de registros para confecção da listagem de Responsáveis
	 * 
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getListagem2() {
		//Verifica existência dos dados em cache
		$orcCache = new Trf1_Orcamento_Cache ();
		$cacheId = $orcCache->getID_Listagem ( 'responsavel' );
		
		$dados = $orcCache->lerCache ( $cacheId );
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	--R.RESP_CD_LOTACAO,
	--R.RESP_DS_SECAO,
	L.LOTA_SIGLA_LOTACAO,
	L.LOTA_DSC_LOTACAO
FROM
	CEO_TB_RESP_RESPONSAVEL			R
Left JOIN
	RH_CENTRAL_LOTACAO				L ON
		L.LOTA_COD_LOTACAO			= R.RESP_CD_LOTACAO		AND
		L.LOTA_SIGLA_SECAO			= R.RESP_DS_SECAO		AND
		L.LOTA_DAT_FIM				IS Null
WHERE 
	R.RESP_DH_EXCLUSAO_LOGICA		IS Null
			";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchAll ( $sql );
			
			// Cria o cache
			$orcCache->criarCache ( $dados, $cacheId );
		}
		
		return $dados;
	}
	
	/**
	 * Retorna um único registro
	 * 
	 * @param	int		$responsavel	Chave primária para busca do registro
	 * @return	array
	 * @author	Dayane Freire
	 */
	public function getResponsavel2($responsavel) {
		$sql = "
SELECT
	R.RESP_CD_RESPONSAVEL,
	R.RESP_CD_LOTACAO,
	R.RESP_DS_SECAO,
        L.LOTA_SIGLA_LOTACAO,
        RH_BUSCA_CENTRAL_LOTACAO_SECAO(R.RESP_DS_SECAO, L.LOTA_COD_LOTACAO)||'-'||R.RESP_DS_SECAO AS UG,   
	L.LOTA_DSC_LOTACAO AS LOTACAO
FROM
	CEO_TB_RESP_RESPONSAVEL                 R
Left JOIN
	RH_CENTRAL_LOTACAO			L ON
		L.LOTA_COD_LOTACAO		= R.RESP_CD_LOTACAO			AND
		L.LOTA_SIGLA_SECAO		= R.RESP_DS_SECAO			AND
		L.LOTA_DAT_FIM			IS Null
WHERE 
        RESP_CD_RESPONSAVEL         = $responsavel  AND
	RESP_DH_EXCLUSAO_LOGICA     IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		//Zend_debug::dump($sql); exit;
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Realiza a exclusão lógica de um responsável
	 *
	 * @param	int		$responsavel		Código do Responsável
	 * @return	none
	 * @author	Dayane Freire
	 */
	public function exclusaoLogica2($responsavel) {
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		$sql = "
UPDATE
	CEO_TB_RESP_RESPONSAVEL
SET
	RESP_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	RESP_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	RESP_CD_RESPONSAVEL                                     = $responsavel     AND
	RESP_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	public function getResposavel2($descricao, $codigo) {
		
		$sql = " SELECT  LOTA_SIGLA_LOTACAO||' - '||REPLACE( RH_DESCRICAO_CENTRAL_LOTACAO(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO),'-',' ') ||' - '||RH_SIGLAS_FAMILIA_CENTR_LOTA(LOTA_SIGLA_SECAO,LOTA_COD_LOTACAO) AS label,
                   LOTA_COD_LOTACAO AS COD,
                   LOTA_SIGLA_SECAO AS SIGLA,
                   B.RESP_CD_RESPONSAVEL AS RESP
            FROM 
                   RH_CENTRAL_LOTACAO A,
                   CEO_TB_RESP_RESPONSAVEL B 
           WHERE 
                   A.LOTA_SIGLA_SECAO = B.RESP_DS_SECAO
             AND 
                  A.LOTA_COD_LOTACAO  = B.RESP_CD_LOTACAO
             AND 
                  B.RESP_DH_EXCLUSAO_LOGICA IS NULL
             AND
                  A.LOTA_DAT_FIM       IS Null    
             AND
     UPPER(A.LOTA_SIGLA_LOTACAO||' - '||A.LOTA_COD_LOTACAO||' - '||A.LOTA_DSC_LOTACAO)    Like UPPER('%$descricao%')   
     ";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		return $banco->fetchAll ( $sql );
	}

}   