<?php
/**
 * @category	Bvb
 * @package		Bvb_Grid_Formatter_Linkvariavel
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe para formatação de Link (variável) para exibição dentro do grid (controle Bvb)
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
class Bvb_Grid_Formatter_Linkvariavel implements Bvb_Grid_Formatter_FormatterInterface {
	/**
	 * Classe construtora 
	 */
	public function __construct($options = array()) {
		// não faz nada!
	}
	
	/**
	 * Formata um texto incluindo link para outro Módulo / Controle / Ação
	 * @see library/Bvb/Grid/Formatter/Bvb_Grid_Formatter_FormatterInterface::format()
	 */
	public function format($texto) {
		try {
			/*
			 * Modelo antigo!
			 * 
			 * Antes, eram separadamente identificados:
			 * [MODULO=ooooo]
			 * [CONTROLE=ccccc]
			 * [ACAO=aaaaa]
			 * 
			// Zera variáveis
			$modulo = '';
			$controle = '';
			$acao = '';
			
			// Busca módulo
			if (strpos ( $texto, '[MODULO=' ) > 0) {
				$posIni = strpos ( $texto, '[MODULO=' );
				$posFim = strpos ( $texto, ']', $posIni + 1 );
				
				$modulo = substr ( $texto, $posIni + strlen ( '[MODULO=' ), $posFim - $posIni - strlen ( '[MODULO=' ) );
			}
			
			// Busca controle
			if (strpos ( $texto, '[CONTROLE=' ) > 0) {
				$posIni = strpos ( $texto, '[CONTROLE=' );
				$posFim = strpos ( $texto, ']', $posIni + 1 );
				
				$controle = substr ( $texto, $posIni + strlen ( '[CONTROLE=' ), $posFim - $posIni - strlen ( '[CONTROLE=' ) );
			}
			
			// Busca ação
			if (strpos ( $texto, '[ACAO=' ) > 0) {
				$posIni = strpos ( $texto, '[ACAO=' );
				$posFim = strpos ( $texto, ']', $posIni + 1 );
				
				$acao = substr ( $texto, $posIni + strlen ( '[ACAO=' ), $posFim - $posIni - strlen ( '[ACAO=' ) );
			}
			*/
			
			// Busca opções para url
			$sOpcoes = '';
			$url = '';
			$posIni = strpos ( $texto, '[AHREF=' );
			$posFim = strpos ( $texto, '=AHREF]', $posIni + 1 );
			
			if ($posIni > 0) {
				$sOpcoes = trim ( substr ( $texto, $posIni + strlen ( '[AHREF=' ), $posFim - $posIni - strlen ( '[AHREF=' ) ) );
				$opcao = explode ( '/', $sOpcoes );
				
				// Monta link
				$requisicao = new Zend_View_Helper_Url ();
				$opcoes = array ('module' => $opcao [0], 'controller' => $opcao [1], 'action' => $opcao [2] );
				
				$url = $requisicao->url ( $opcoes, null, true );
			}
			
			$textoFormatado = $texto;
			$textoFormatado = str_replace ( '[AHREF=', '<a href="', $textoFormatado );
			$textoFormatado = str_replace ( '=AHREF]', '" target="_blank">', $textoFormatado );
			$textoFormatado = str_replace ( $sOpcoes, $url, $textoFormatado );
			$textoFormatado = str_replace ( '[/A]', '</a>', $textoFormatado );
		} catch ( Exception $e ) {
			$textoFormatado = $texto;
		}
		
		/* TESTES
		Zend_Debug::dump($texto);
		Zend_Debug::dump($textoFormatado);
		*/
		
		return $textoFormatado;
	}

}