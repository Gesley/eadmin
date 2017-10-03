<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Uo
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Unidades orçamentárias (UO)
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
class Trf1_Orcamento_Negocio_Uo {
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
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbUnorUnidadeOrcamentaria ();
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
		$cacheId = $cache->retornaID_Combo ( 'uo' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	UNOR_CD_UNID_ORCAMENTARIA,
	UNOR_DS_UNID_ORCAMENTARIA
FROM
	CEO_TB_UNOR_UNID_ORCAMENTARIA
WHERE
	UNOR_DH_EXCLUSAO_LOGICA IS NULL
ORDER BY
	UNOR_DS_UNID_ORCAMENTARIA
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
		$cacheId = $cache->gerarID_Listagem ( 'uo' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	UNOR_CD_UNID_ORCAMENTARIA,
	UNOR_DS_UNID_ORCAMENTARIA
FROM
	CEO_TB_UNOR_UNID_ORCAMENTARIA
WHERE
	UNOR_DH_EXCLUSAO_LOGICA IS NULL
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
	public function retornaRegistro($uo) {
		$sql = "
SELECT
	UNOR_CD_UNID_ORCAMENTARIA,
	UNOR_DS_UNID_ORCAMENTARIA
FROM
	CEO_TB_UNOR_UNID_ORCAMENTARIA 
WHERE
	UNOR_CD_UNID_ORCAMENTARIA				= $uo	AND
	UNOR_DH_EXCLUSAO_LOGICA   				IS NULL
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
	public function retornaRegistroNomeAmigavel($uo) {
		$sql = "
SELECT
	UNOR_CD_UNID_ORCAMENTARIA				AS \"UO\",
	UNOR_DS_UNID_ORCAMENTARIA				AS \"Descrição\"
FROM
	CEO_TB_UNOR_UNID_ORCAMENTARIA 
WHERE
	UNOR_CD_UNID_ORCAMENTARIA				= $uo	AND
	UNOR_DH_EXCLUSAO_LOGICA   				IS NULL
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
		$uos = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	UNOR_CD_UNID_ORCAMENTARIA,
	UNOR_CD_UNID_ORCAMENTARIA				AS \"UO\",
	UNOR_DS_UNID_ORCAMENTARIA				AS \"Descrição\"
FROM
	CEO_TB_UNOR_UNID_ORCAMENTARIA 
WHERE
	UNOR_CD_UNID_ORCAMENTARIA				IN ($uos)	AND
	UNOR_DH_EXCLUSAO_LOGICA   				IS NULL
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
		$uos = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_UNOR_UNID_ORCAMENTARIA
SET
	UNOR_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	UNOR_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	UNOR_CD_UNID_ORCAMENTARIA				IN ($uos)		AND
	UNOR_DH_EXCLUSAO_LOGICA					IS NULL
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
}
