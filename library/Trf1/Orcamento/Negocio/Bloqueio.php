<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Bloqueio
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Bloqueio de Movimentação
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
class Trf1_Orcamento_Negocio_Bloqueio {
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
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbRembRemanjtoBloq ();
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
		$cacheId = $cache->retornaID_Combo ( 'bloqueio' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	REMB_CD_PT_RESUMIDO,
	REMB_CD_ELEMENTO_DESPESA_SUB
FROM
	CEO_TB_REMB_REMANJTO_BLOQUEADO
WHERE
	REMB_DH_EXCLUSAO_LOGICA		IS Null
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
		$cacheId = $cache->gerarID_Listagem ( 'bloqueio' );
		$dados = $cache->lerCache ( $cacheId );
		
		if ($dados === false) {
			//Não existindo o cache, busca do banco
			$sql = "
SELECT
	REMB.REMB_CD_PT_RESUMIDO,
	UNOR.UNOR_CD_UNID_ORCAMENTARIA,
	REMB.REMB_CD_ELEMENTO_DESPESA_SUB
FROM
	CEO_TB_REMB_REMANJTO_BLOQUEADO REMB
Left JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO			PTRS ON
		REMB.REMB_CD_PT_RESUMIDO          = PTRS.PTRS_CD_PT_RESUMIDO
Left JOIN
    CEO_TB_UNOR_UNID_ORCAMENTARIA UNOR ON
        UNOR.UNOR_CD_UNID_ORCAMENTARIA = 	PTRS.PTRS_CD_UNID_ORCAMENTARIA
WHERE
	REMB_DH_EXCLUSAO_LOGICA		IS Null
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
	 * @param	int		$ptres		PTRES
	 * @param	int		$elemento	NATUREZA
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($ptres, $elemento) {
		$sql = "
SELECT
	B.REMB_CD_PT_RESUMIDO,
	B.REMB_CD_ELEMENTO_DESPESA_SUB,
	UPPER(P.PTRS_CD_PT_RESUMIDO || ' - ' || P.PTRS_SG_PT_RESUMIDO || ' - ' || P.PTRS_DS_PT_RESUMIDO)	AS PTRES,
	UPPER(E.EDSB_CD_ELEMENTO_DESPESA_SUB || ' - ' || E.EDSB_DS_ELEMENTO_DESPESA_SUB)					AS ELEMENTO 
FROM
	CEO_TB_REMB_REMANJTO_BLOQUEADO		B
INNER JOIN
	CEO_TB_PTRS_PROGRAMA_TRABALHO		P ON
		P.PTRS_CD_PT_RESUMIDO = B.REMB_CD_PT_RESUMIDO
INNER JOIN
	CEO_TB_EDSB_ELEMENTO_SUB_DESP		E ON
		E.EDSB_CD_ELEMENTO_DESPESA_SUB = B.REMB_CD_ELEMENTO_DESPESA_SUB
WHERE
	B.REMB_DH_EXCLUSAO_LOGICA			IS Null		AND
	B.REMB_CD_PT_RESUMIDO				= $ptres	AND
	B.REMB_CD_ELEMENTO_DESPESA_SUB		= $elemento  
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
	public function retornaRegistroNomeAmigavel($ptres, $elemento) {
		
		$sql = "
SELECT
	UPPER(P.PTRS_CD_PT_RESUMIDO||' - '||P.PTRS_DS_PT_RESUMIDO)AS \"PTRES\",
	UPPER(E.EDSB_CD_ELEMENTO_DESPESA_SUB||' - '||E.EDSB_DS_ELEMENTO_DESPESA_SUB) AS \"Natureza da despesa\"
FROM
	CEO_TB_REMB_REMANJTO_BLOQUEADO B
INNER JOIN  CEO_TB_PTRS_PROGRAMA_TRABALHO P
        ON  B.REMB_CD_PT_RESUMIDO = P.PTRS_CD_PT_RESUMIDO
INNER JOIN  CEO_TB_EDSB_ELEMENTO_SUB_DESP E
        ON  B.REMB_CD_ELEMENTO_DESPESA_SUB = E.EDSB_CD_ELEMENTO_DESPESA_SUB
WHERE
        	B.REMB_DH_EXCLUSAO_LOGICA		    IS Null
AND 
            B.REMB_CD_PT_RESUMIDO              = $ptres
AND 
            B.REMB_CD_ELEMENTO_DESPESA_SUB     = $elemento  
		
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
		$cont = 0;
		$registro = array ();
		
		foreach ( $chaves as $dados ) {
			$registro [$cont] = "'" . $dados . "'";
			$cont ++;
		}
		
		$registro = implode ( ',', $registro );
		$sql = "
SELECT
		--	B.REMB_CD_PT_RESUMIDO || ' - ' || B.REMB_CD_ELEMENTO_DESPESA_SUB AS COD,
		B.REMB_CD_PT_RESUMIDO,
		 B.REMB_CD_ELEMENTO_DESPESA_SUB,
		UPPER(P.PTRS_CD_PT_RESUMIDO||' - '||P.PTRS_DS_PT_RESUMIDO)AS \"PTRES\",
			UPPER(E.EDSB_CD_ELEMENTO_DESPESA_SUB||' - '||E.EDSB_DS_ELEMENTO_DESPESA_SUB) AS \"Natureza da despesa\"
FROM
        	CEO_TB_REMB_REMANJTO_BLOQUEADO B

INNER JOIN  CEO_TB_PTRS_PROGRAMA_TRABALHO P
        ON  B.REMB_CD_PT_RESUMIDO = P.PTRS_CD_PT_RESUMIDO
INNER JOIN  CEO_TB_EDSB_ELEMENTO_SUB_DESP E
        ON  B.REMB_CD_ELEMENTO_DESPESA_SUB = E.EDSB_CD_ELEMENTO_DESPESA_SUB
WHERE
        	B.REMB_DH_EXCLUSAO_LOGICA		    IS Null
AND 
            TO_CHAR(B.REMB_CD_PT_RESUMIDO||'-'||B.REMB_CD_ELEMENTO_DESPESA_SUB) IN ($registro)
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
		$bloqueios = "'" . implode ( "', '", $chaves ) . "'";
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		// Exclui um ou mais registros
		$sql = "
UPDATE
	CEO_TB_REMB_REMANJTO_BLOQUEADO
SET
	REMB_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	REMB_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
	REMB_DH_EXCLUSAO_LOGICA					IS Null
AND 
    TO_CHAR(REMB_CD_PT_RESUMIDO || '-' || REMB_CD_ELEMENTO_DESPESA_SUB) IN ($bloqueios)
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$banco->query ( $sql );
	}
	
	/**
	 * Retorna true ou falso, caso haja registro com a combinação de PTRES + NATUREZA
	 *
	 * @param	int		$ptres				PTRES
	 * @param	int		$elemento			NATUREZA
	 * @return	boolean						True, há bloqueio; False, não há bloqueio
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaBloqueio($ptres, $natureza) {
		$elemento = substr ( $natureza, 0, 6 );
		
		$sql = "
SELECT
	Count(*) AS QTDE
FROM
	CEO_TB_REMB_REMANJTO_BLOQUEADO
WHERE
	REMB_DH_EXCLUSAO_LOGICA						IS Null		AND
	REMB_CD_PT_RESUMIDO							= $ptres	AND
	(
	REMB_CD_ELEMENTO_DESPESA_SUB				= $natureza	OR
	REMB_CD_ELEMENTO_DESPESA_SUB				= $elemento || '00'
	)
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		$qtde = $banco->fetchOne ( $sql );
		
		// Se tiver registros, há bloqueio para essa movimentação
		return ($qtde != 0);
	}

}