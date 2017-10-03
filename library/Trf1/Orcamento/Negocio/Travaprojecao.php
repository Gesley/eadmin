<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Travaprojecao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Travamento de Projeção
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
class Trf1_Orcamento_Negocio_Travaprojecao {
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
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbTrvpTravaProjecao ();
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
		return null;
		
		// Verifica existência dos dados em cache
		$cache = new Trf1_Orcamento_Cache ();
		$cacheId = $cache->retornaID_Combo ( 'travaprojecao' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	T.TRVP_CD_UG || ' - ' || U.UNGE_SG_UG,
	T.TRVP_DT_INICIO
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO				T
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA 			U ON
		U.UNGE_CD_UG						= T.TRVP_CD_UG
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
		$cacheId = $cache->gerarID_Listagem ( 'travaprojecao' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	T.TRVP_CD_UG,
	U.UNGE_SG_UG,
        CASE WHEN 
                    TO_CHAR(T.TRVP_DT_INICIO, 'YYYY') = '".date('Y')."' THEN 1
                    ELSE 2
        END AS EXERCICIO,         
	TO_CHAR(T.TRVP_DT_INICIO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')		AS TRVP_DT_INICIO,	
	TO_CHAR(T.TRVP_DT_INICIO, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')			AS DT_INICIO,
	TO_CHAR(T.TRVP_DT_FIM, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA . "')				AS DT_FIM
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO				T
Left JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA 			U ON
		U.UNGE_CD_UG						= T.TRVP_CD_UG

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
	public function retornaRegistro($ug, $data) {
		$sql = "
SELECT
	TRVP_CD_UG,
	TO_CHAR(TRVP_DT_INICIO, 'DD/MM/YYYY')	AS TRVP_DT_INICIO,
    TO_CHAR(TRVP_DT_FIM, 'DD/MM/YYYY')		AS TRVP_DT_FIM
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO
WHERE
	TRVP_CD_UG								= $ug		AND
	TO_CHAR(TRVP_DT_INICIO, 'DD')		= '$data'
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
	public function retornaRegistroNomeAmigavel($ug, $data) {
		$sql = "
SELECT
	(T.TRVP_CD_UG||' - '||U.UNGE_DS_UG) 		AS \"Sigla\",
	TO_CHAR(T.TRVP_DT_INICIO, 'DD/MM/YYYY')		AS \"Início do bloqueio\",
	TO_CHAR(T.TRVP_DT_FIM, 'DD/MM/YYYY')		AS \"Término do bloqueio\"
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO	T
INNER JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA	U ON
		U.UNGE_CD_UG = T.TRVP_CD_UG
WHERE
	T.TRVP_CD_UG								= $ug	AND
	TO_CHAR(T.TRVP_DT_INICIO, 'YYYYMMDD')		= ('$data')
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
		$travamentos = "'" . implode ( "', '", $chaves ) . "'";
		
		$sql = "
SELECT
	T.TRVP_CD_UG,
	TO_CHAR(T.TRVP_DT_INICIO, 'DD/MM/YYYY')		AS TRVP_DT_INICIO,
	(T.TRVP_CD_UG||' - '||U.UNGE_DS_UG)			AS \"Sigla\",
	TO_CHAR(T.TRVP_DT_INICIO, 'DD/MM/YYYY')		AS \"Início do bloqueio\",
	TO_CHAR(T.TRVP_DT_FIM, 'DD/MM/YYYY')		AS \"Término do bloqueio\"
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO	T
INNER JOIN
	CEO_TB_UNGE_UNIDADE_GESTORA	U ON
		U.UNGE_CD_UG = T.TRVP_CD_UG
WHERE
	T.TRVP_CD_UG||'-'||TO_CHAR(T.TRVP_DT_INICIO, 'YYYYMMDD') IN ($travamentos)
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}
	
	/**
	 * 
	 * 
	 * @param	int			$despesa
	 * @throws	Exception
	 * @return	boolean
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaPeriodoTravamentoProjecao($despesa) {
		if (!$despesa) {
			throw new Exception('Favor informar o código da despesa.');
		}
		
		$sql = "
SELECT
	TO_CHAR(MIN(T.TRVP_DT_INICIO), 'DD/MM/YYYY')	DT_INI,
	TO_CHAR(MAX(T.TRVP_DT_FIM), 'DD/MM/YYYY')		DT_FIM
FROM
	CEO_TB_TRVP_TRAVA_PROJECAO	T
Left JOIN
	CEO_TB_DESP_DESPESA			D ON
		D.DESP_CD_UG = T.TRVP_CD_UG
WHERE
	D.DESP_NR_DESPESA = $despesa		AND
	SYSDATE BETWEEN T.TRVP_DT_INICIO AND T.TRVP_DT_FIM + 1 /* O +1 evita que a projeção seja liberada em sua último dia */
GROUP BY
	D.DESP_NR_DESPESA
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter();
		
		return $banco->fetchRow($sql);
	}
	
}