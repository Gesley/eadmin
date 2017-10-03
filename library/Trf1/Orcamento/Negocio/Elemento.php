<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Elemento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Elemento
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
class Trf1_Orcamento_Negocio_Elemento {
	/**
	 * Model dos Elemento
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbEdsbElemento();
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
		return $this->_dados->chavePrimaria();
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
		$cacheId = $cache->retornaID_Combo ( 'elemento' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	EDSB_CD_ELEMENTO_DESPESA_SUB,
	EDSB_DS_ELEMENTO_DESPESA_SUB
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE
	EDSB_DH_EXCLUSAO_LOGICA IS NULL
ORDER BY
	EDSB_CD_ELEMENTO_DESPESA_SUB
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
		$cacheId = $cache->gerarID_Listagem ( 'elemento' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	EDSB_CD_ELEMENTO_DESPESA_SUB,
	EDSB_DS_ELEMENTO_DESPESA_SUB
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE
	EDSB_DH_EXCLUSAO_LOGICA					IS Null
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
	 * @param	int		$evento				Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($elemento) {
		$sql = "
SELECT
	EDSB_CD_ELEMENTO_DESPESA_SUB,
	EDSB_DS_ELEMENTO_DESPESA_SUB
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE
	EDSB_CD_ELEMENTO_DESPESA_SUB			= $elemento		AND
	EDSB_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Retorna um único registro, com o nome descritivo dos campos (utilizando ALIAS)
	 *
	 * @param	int		$evento				Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistroNomeAmigavel($elemento) {
		// TODO: Aplicar o formato ao elemento de despesa na linha comentada
		$sql = "
SELECT
	EDSB_CD_ELEMENTO_DESPESA_SUB						AS \"Natureza da despesa\",
	/* EDSB_CD_ELEMENTO_DESPESA_SUB						AS \"Natureza da despesa\", */
	EDSB_DS_ELEMENTO_DESPESA_SUB						AS \"Descrição\"
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE
	EDSB_CD_ELEMENTO_DESPESA_SUB						= $elemento		AND
	EDSB_DH_EXCLUSAO_LOGICA								IS Null
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
		$elementos = implode ( ', ', $chaves );
		
		// TODO: Aplicar o formato ao elemento de despesa na linha comentada
		$sql = "
SELECT
	EDSB_CD_ELEMENTO_DESPESA_SUB,
	EDSB_CD_ELEMENTO_DESPESA_SUB							AS \"Natureza da despesa\",
	/* EDSB_CD_ELEMENTO_DESPESA_SUB							AS \"Natureza da despesa\", */
	EDSB_DS_ELEMENTO_DESPESA_SUB							AS \"Descrição\"
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE
	EDSB_CD_ELEMENTO_DESPESA_SUB							IN ($elementos)	AND
	EDSB_DH_EXCLUSAO_LOGICA									IS Null
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
		$elementos = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
SET
	EDSB_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	EDSB_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	EDSB_CD_ELEMENTO_DESPESA_SUB			IN ($elementos)	AND
	EDSB_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	public function getElementoAjax($elemento) {
		$sql = "
SELECT DISTINCT
	UPPER(EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' || EDSB_DS_ELEMENTO_DESPESA_SUB)	AS label, 
	EDSB_CD_ELEMENTO_DESPESA_SUB													AS COD,
	EDSB_DS_ELEMENTO_DESPESA_SUB													AS descricao
FROM
	CEO_TB_EDSB_ELEMENTO_SUB_DESP
WHERE 
	EDSB_DH_EXCLUSAO_LOGICA															IS Null		AND
	UPPER(EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' || EDSB_DS_ELEMENTO_DESPESA_SUB)	Like UPPER('%$elemento%')
ORDER BY
	1 DESC
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}

}
