<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Objetivo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Objetivos (do planejamento estratégico)
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
class Trf1_Orcamento_Negocio_Objetivo {
	/**
	 * Model dos Objetivos
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbPobjObjetivo ();
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
	 * @param	integer		$ano
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaCombo($ano) {
		// Determina ano, caso o mesmo não seja informado
		if ($ano == 0 || $ano == '' || ! is_numeric ( $ano )) {
			$ano = date ( 'Y' );
		}
		
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->retornaID_Combo ( 'objetivo' . $ano );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	POBJ_CD_OBJETIVO,
	POBJ_DS_OBJETIVO
FROM
	CEO_TB_POBJ_OBJETIVO
WHERE
	POBJ_DH_EXCLUSAO_LOGICA IS NULL		AND
	POBJ_AA_OBJETIVO					= $ano
ORDER BY
	POBJ_CD_OBJETIVO
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
		$cacheId = $cache->gerarID_Listagem ( 'objetivo' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	POBJ_CD_OBJETIVO,
	POBJ_AA_OBJETIVO,
	POBJ_DS_OBJETIVO,
	CASE
            WHEN POBJ_AA_OBJETIVO = ".date('Y')." THEN 1
            ELSE 2
	END AS EXERCICIO     
FROM
	CEO_TB_POBJ_OBJETIVO
WHERE
	POBJ_DH_EXCLUSAO_LOGICA IS Null
        
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
	 * @param	int		$programa			Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($objetivo) {
		$sql = "
SELECT
	POBJ_CD_OBJETIVO,
	POBJ_AA_OBJETIVO,
	POBJ_DS_OBJETIVO
FROM
	CEO_TB_POBJ_OBJETIVO
WHERE
	POBJ_CD_OBJETIVO				= $objetivo		AND
	POBJ_DH_EXCLUSAO_LOGICA			IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$programa			Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($objetivo) {
		$sql = "
SELECT
	POBJ_CD_OBJETIVO				AS \"Objetivo\",
	POBJ_AA_OBJETIVO				AS \"Ano\",
	POBJ_DS_OBJETIVO				AS \"Descrição\"
FROM
	CEO_TB_POBJ_OBJETIVO
WHERE
	POBJ_CD_OBJETIVO				= $objetivo		AND
	POBJ_DH_EXCLUSAO_LOGICA			IS Null
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
		$objetivos = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	POBJ_CD_OBJETIVO,
	POBJ_CD_OBJETIVO				AS \"Objetivo\",
	POBJ_AA_OBJETIVO				AS \"Ano\",
	POBJ_DS_OBJETIVO				AS \"Descrição\"
FROM
	CEO_TB_POBJ_OBJETIVO
WHERE
	POBJ_CD_OBJETIVO				IN ($objetivos)		AND
	POBJ_DH_EXCLUSAO_LOGICA			IS Null
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
		$objetivos = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_POBJ_OBJETIVO
SET
	POBJ_CD_MATRICULA_EXCLUSAO			= '$sessao->matricula',
	POBJ_DH_EXCLUSAO_LOGICA				= SYSDATE
WHERE
	POBJ_CD_OBJETIVO					IN ($objetivos)		AND
	POBJ_DH_EXCLUSAO_LOGICA				IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}

}