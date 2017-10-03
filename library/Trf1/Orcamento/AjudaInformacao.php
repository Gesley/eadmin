<?php
/**
 * Classe para exibição de ajuda / informação para exibição na tela
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_AjudaInformacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Orcamento_AjudaInformacao {
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/**
	 * Busca dados de ajuda e informações para exibição na tela
	 * 
	 * @param	string		$sController
	 * @param	string		$sAction
	 * @return	array
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function getAjudaInformacao($sController, $sAction, $link) {
		$sController = strtolower ( $sController );
		$sAction = strtolower ( $sAction );
		
		$sql = "
SELECT
	DBMS_LOB.SUBSTR(ACAO.ACAO_DS_AJUDA, 4000, 1)		AS AJUDA,
	DBMS_LOB.SUBSTR(ACAO.ACAO_DS_INFORMACAO, 4000, 1)	AS INFORMACAO
FROM
	OCS_TB_MODL_MODULO              MODL
Left JOIN
	OCS_TB_CTRL_CONTROLE_SISTEMA    CTRL ON
		CTRL.CTRL_ID_MODULO = MODL.MODL_ID_MODULO
		AND CTRL.CTRL_NM_CONTROLE_SISTEMA = '" . strtolower ( $sController ) . "'
Left JOIN
	OCS_TB_ACAO_ACAO_SISTEMA        ACAO ON
		ACAO.ACAO_ID_CONTROLE_SISTEMA = CTRL.CTRL_ID_CONTROLE_SISTEMA
		AND ACAO.ACAO_NM_ACAO_SISTEMA = '" . strtolower ( $sAction ) . "'
Left JOIN
	OCS_TB_PAPL_PAPEL               PAPL ON
		PAPL.PAPL_ID_ACAO_SISTEMA = ACAO.ACAO_ID_ACAO_SISTEMA
		AND PAPL_CD_MATRICULA_EXCLUSAO IS NULL
WHERE
	MODL.MODL_NM_MODULO = '" . strtolower ( Trf1_Orcamento_Definicoes::NOME_MODULO ) . "'
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		
		try {
			$registro = $banco->fetchRow ( $sql );
		} catch ( Exception $e ) {
			$registro ['AJUDA'] = 'Banco de dados indisponível.<br />' . $e->getMessage ();
			$registro ['INFORMACAO'] = 'Banco de dados indisponível.<br />' . $e->getMessage ();
		}
		
		// Retorno das strings para exibição na tela 
		$txtAjuda = $registro ['AJUDA'];
		$txtInformacao = $registro ['INFORMACAO'];
		
		// Ajuste para ajuda ainda não informada
		if ($txtAjuda == '') {
			$txtAjuda = 'Nenhuma ajuda disponível.';
		}
		
		// Ajuste para informação ainda não informada
		if ($txtInformacao == '') {
			$txtInformacao = 'Nenhuma informação disponível.';
		}
		
		$txtInformacao .= "<br /><br />Adicionalmente, você pode <a href='$link'>ver mais informações sobre funcionalidades e outros recursos</a> do sistema.";
		
		// Retorno da função
		$textos = array ('ajuda' => $txtAjuda, 'informacao' => $txtInformacao );
		
		return $textos;
	}

}
