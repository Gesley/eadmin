<?php
/**
 * @category	TRF1
 * @package		Trf1_Orcamento_Negocio_Projecaojustificativa
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Programas (do planejamento estratégico)
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
class Trf1_Orcamento_Negocio_Projecaojustificativa {
	/**
	 * Model dos Programas
	 */
	protected $_dados = null;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		$this->_dados = new Application_Model_DbTable_Orcamento_CeoTbPrjjJustifProjecao ();
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
	public function retornaOrigemCombo() {
		$dados[0]		= 'Responsável ';
		$dados[1]		= 'Setorial ';
		
		return $dados;
	}
	
	/**
	 * Retorna a listagem de todos os eventos
	 * 
	 * @param	$despesa	Código da despesa
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaListagem($despesa, $tipo = null) {
		if($tipo != null) {
			$complemento = "AND PRJJ_IC_ORIGEM = $tipo";
		} else {
			$complemento = '';
		}
		
		$sql = "
SELECT
	PRJJ_NR_DESPESA,
	TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')					AS PRJJ_DH_JUSTIFICATIVA,
	PRJJ_DS_JUSTIFICATIVA,
	TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')	AS DT_INI,    
	CASE PRJJ_IC_ORIGEM
		WHEN 0 THEN 'Responsável '
		WHEN 1 THEN 'Setorial '
	END AS PRJJ_IC_ORIGEM
FROM
	CEO_TB_PRJJ_JUSTIF_PROJECAO
WHERE
	PRJJ_CD_MATRICULA_EXCLUSAO IS NULL		AND
	PRJJ_NR_DESPESA							= $despesa
	$complemento
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter();
		
		return $banco->fetchAll($sql);
	}
	
	/**
	 * Retorna um único registro sem uso de ALIAS
	 *
	 * @param	int		$evento				Chave primária para busca do registro
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaRegistro($registro) {
		$sql = "
SELECT
	PRJJ_NR_DESPESA,
	TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA . "')					AS PRJJ_DH_JUSTIFICATIVA,
	PRJJ_DS_JUSTIFICATIVA,
	TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')	AS DT_INI,    
	CASE PRJJ_IC_ORIGEM
		WHEN 0 THEN 'Responsável '
		WHEN 1 THEN 'Setorial '
	END AS PRJJ_IC_ORIGEM
FROM
	CEO_TB_PRJJ_JUSTIF_PROJECAO
WHERE
	PRJJ_CD_MATRICULA_EXCLUSAO IS NULL		AND
	TO_CHAR(PRJJ_NR_DESPESA||'-'||TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '". Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')) IN ('$registro')
				";
		//Zend_Debug::dump($registro);
		//exit();	
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	/**
	 * Realiza a exclusão lógica da justificativa da projeção informada
	 * 
	 * @param	int		$despesa			Código do evento desejado
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function exclusaoLogica($chaves) {
		$registro = explode(',', $chaves);
		$registro = implode("','", $registro);
		
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		
		$sql = "
UPDATE
	CEO_TB_PRJJ_JUSTIF_PROJECAO
SET
	PRJJ_CD_MATRICULA_EXCLUSAO				= '$sessao->matricula',
	PRJJ_DH_EXCLUSAO_LOGICA					= SYSDATE
WHERE
TO_CHAR(PRJJ_NR_DESPESA||'-'||TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '". Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')) IN ('$registro')        
				";
		$banco = Zend_Db_Table::getDefaultAdapter();
		
		$banco->query($sql);
	}
	
	public function retornaRegistroNomeAmigavel($justificativa) {
		$sql = "
SELECT
	PRJJ_NR_DESPESA				AS \"Despesa\",
	PRJJ_DH_JUSTIFICATIVA		AS \"Data\",
	PRJJ_IC_ORIGEM				AS \"Origem\",
	PRJJ_DS_JUSTIFICATIVA		AS \"Justificativa\"
		
FROM
	CEO_TB_PRJJ_JUSTIF_PROJECAO 
WHERE
    TO_CHAR(PRJJ_NR_DESPESA || '-' || TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '". Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')) = '$justificativa'
	AND PRJJ_DH_EXCLUSAO_LOGICA IS NULL
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchRow ( $sql );
	}
	
	public function retornaVariosRegistros($chaves) {
		$registro = explode(',', $chaves);
		$registro = implode("','", $registro);
		
		$sql = "
SELECT
	PRJJ_NR_DESPESA,
	PRJJ_DH_JUSTIFICATIVA,
	PRJJ_NR_DESPESA				AS \"Despesa\",
	PRJJ_DH_JUSTIFICATIVA		AS \"Data\",
	PRJJ_IC_ORIGEM				AS \"Origem\",
	PRJJ_DS_JUSTIFICATIVA		AS \"Justificativa\"
FROM
	CEO_TB_PRJJ_JUSTIF_PROJECAO 
WHERE
    TO_CHAR(PRJJ_NR_DESPESA || '-' || TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '". Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')) IN ('$registro')        
	AND PRJJ_DH_EXCLUSAO_LOGICA IS NULL
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		return $banco->fetchAll ( $sql );
	}
        
     public function alterarJustificativa($dados, $projecao) {
        $projecao = explode('-', $projecao);
        $sql = "UPDATE CEO_TB_PRJJ_JUSTIF_PROJECAO
                     SET PRJJ_DS_JUSTIFICATIVA = '" . $dados['PRJJ_DS_JUSTIFICATIVA'] . "'
                   WHERE PRJJ_NR_DESPESA = $projecao[0]
                     AND TO_CHAR(PRJJ_DH_JUSTIFICATIVA, '" . Trf1_Orcamento_Definicoes::FORMATO_DATA_HORA_SEM_SEPARADORES . "')	= '$projecao[1]'";
        $banco = Zend_Db_Table::getDefaultAdapter();
        $result = $banco->query($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
}
