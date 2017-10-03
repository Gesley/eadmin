<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Evento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Eventos
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
class Trf1_Orcamento_Negocio_Evento {
	/**
	 * Model dos Eventos
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbEvenEventoNe ();
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
		$cacheId = $cache->retornaID_Combo ( 'evento' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	EVEN_CD_EVENTO,
	EVEN_DS_EVENTO
FROM
	CEO_TB_EVEN_EVENTO_NE
WHERE
	EVEN_DH_EXCLUSAO_LOGICA IS NULL
ORDER BY
	EVEN_CD_EVENTO
			";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchPairs ( $sql );
			
			// Cria o cache
			$cache->criarCache ( $dados, $cacheId );
		}
		
		return $dados;
	}
	
	/**
	 * Apresenta opções de variação do campo EVEN_IC_SINAL_EVENTO
	 *
	 * @param	none
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaVariacaoCombo() {
		$dados [0] = 'Positivo';
		$dados [1] = 'Negativo';
		
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
		$cacheId = $cache->gerarID_Listagem ( 'evento' );
		$dados = $cache->lerCache ( $cacheId );
		
			$sql = "
SELECT
	EVEN_CD_EVENTO,
	EVEN_DS_EVENTO,
	CASE EVEN_IC_SINAL_EVENTO
		WHEN 0 THEN '(+) Positivo'
		WHEN 1 THEN '(-) Negativo'
	END AS EVEN_IC_SINAL_EVENTO,
	EVEN_DS_DOCUMENTO
FROM
	CEO_TB_EVEN_EVENTO_NE
WHERE
	EVEN_DH_EXCLUSAO_LOGICA					IS Null
				";
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$dados = $banco->fetchAll ( $sql );
			
			// Cria o cache
			$cache->criarCache ( $dados, $cacheId );
	       return $dados;
	}
	
	/**
	 * Retorna um único registro sem uso de ALIAS
	 *
	 * @param	int		$evento				Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($evento) {
		$sql = "
SELECT
	EVEN_CD_EVENTO,
	EVEN_DS_EVENTO,
	CASE EVEN_IC_SINAL_EVENTO
		WHEN 0 THEN '(+) Positivo'
		WHEN 1 THEN '(-) Negativo'
	END AS EVEN_IC_SINAL_EVENTO,
	EVEN_DS_DOCUMENTO
FROM
	CEO_TB_EVEN_EVENTO_NE
WHERE
	EVEN_CD_EVENTO					= $evento		AND
	EVEN_DH_EXCLUSAO_LOGICA			IS Null
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
	public function retornaRegistroNomeAmigavel($evento) {
		$sql = "
SELECT
	EVEN_CD_EVENTO							AS \"Evento\",
	EVEN_DS_EVENTO							AS \"Descrição\",
	CASE EVEN_IC_SINAL_EVENTO
		WHEN 0 THEN '(+) Positivo'
		WHEN 1 THEN '(-) Negativo'
	END										AS \"Sinal\",
	EVEN_DS_DOCUMENTO						AS \"Documento\"
FROM
	CEO_TB_EVEN_EVENTO_NE
WHERE
	EVEN_CD_EVENTO							= $evento		AND
	EVEN_DH_EXCLUSAO_LOGICA					IS Null
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
		$eventos = implode ( ', ', $chaves );
		
		$sql = "
SELECT
	EVEN_CD_EVENTO,
	EVEN_CD_EVENTO							AS \"Evento\",
	EVEN_DS_EVENTO							AS \"Descrição\",
	CASE EVEN_IC_SINAL_EVENTO
		WHEN 0 THEN '(+) Positivo'
		WHEN 1 THEN '(-) Negativo'
	END										AS \"Sinal\",
	EVEN_DS_DOCUMENTO						AS \"Documento\"
FROM
	CEO_TB_EVEN_EVENTO_NE
WHERE
	EVEN_CD_EVENTO							IN ($eventos)	AND
	EVEN_DH_EXCLUSAO_LOGICA					IS Null
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
		$eventos = implode ( ', ', $chaves );
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_EVEN_EVENTO_NE
SET
	EVEN_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	EVEN_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	EVEN_CD_EVENTO							IN ($eventos)	AND
	EVEN_DH_EXCLUSAO_LOGICA					IS Null
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
}
