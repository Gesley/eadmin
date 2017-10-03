<?php
/**
 * Classe para manipulação genérica de logs de acesso
 * 
 * @category	TRF1
 * @package		Trf1_Guardiao_Log
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
class Trf1_Guardiao_Log {
	/**
	 * Nome do log utilizado no e-Orçamento
	 */
	private $_logNome = null;
	
	/**
	 * Classe construtora
	 * 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/**
	 * Prepara as informações para registro de log de acesso ao sistema e-Admin
	 * 
	 * @return	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function gravaLog($origem = 'e-Admin') {
		// Gera o array com os dados necessários para o registro do acesso ao sistema
		/* $sistema = 'e-Admin'; */
		$sistema = $origem;
		$sessao = new Zend_Session_Namespace ( 'userNs' );
		$usuario = strtoupper ( $sessao->matricula );
		/* ************************************************************
		 * Busca IP;
		 * ============================================================
		 * NOTA: A exibição do ip pode ser facilmente burlada com plugins do firefox!
		 * ============================================================
		 * 
		 * NOTA: A linha abaixo dá erro na homologação / produção pela não adoção do PHP 5.3
		 * $ip = gethostbyname ( gethostname () );		 *
		 * 
		 * NOTA: A linha abaixo mostra o nome da estação em uso
		 * $ip = gethostbyaddr ( xyz );
		 * 
		 * NOTA: A pedido do Thiago, foi subida a rotina de log de acesso ao sistema, neste momento, sem a identificação de IP
		 * $ip = gethostbyname ( php_uname ( 'n' ) );
		 */
		$ip = getenv ( 'HTTP_X_FORWARDED_VARNISH' );
		
		if (!$ip) {
			$ip = 'ip não identificado';
		}
		
		/*
		 * Será utilizado o SYSDATE
		 *
		$data = date ( 'Y-m-d' );
		$hora = date ( 'H:i:s' );
		*/
		
		$sql = "
INSERT INTO
	OCS_TB_LOGS_ACESSO_SISTEMAS
(
	LOGS_NM_SISTEMA,
	LOGS_USER_ID,
	LOGS_IP_ACESSO,
	LOGS_DH_ACESSO
) VALUES (
	'$sistema',
	'$usuario',
	'$ip',
	SYSDATE
)
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter ();
		/* exit($sql); */ 
		
		try {
			$banco->query ( $sql );
		} catch ( Exception $e ) {
			// A linha abaixo não será de fato útil, pois a rotina de login não detalha o tipo do erro; apenas informa acesso negado!
			throw new Zend_Exception ( 'Não foi possível gravar o log deste acesso.' );
		}
	
	}

}