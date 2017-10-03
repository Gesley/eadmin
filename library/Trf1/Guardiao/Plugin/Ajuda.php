<?php
/**
 * Plugin para busca dos textos de ajuda a serem exibidas nas telas do sistema
 * 
 * @category	TRF1
 * @package		Trf1_Guardiao_Plugin_Ajuda
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
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
class Trf1_Guardiao_Plugin_Ajuda extends Zend_Controller_Plugin_Abstract
{
	/**
	 * preDispatch: Função do Plugin de Ajuda que busca e retorna em constantes
	 *				a Ajuda para serem apresentadas em cada tela.
	 *
	 * @see		Zend_Controller_Plugin_Abstract::preDispatch()
	 * @return	AJUDA_AJUDA preenchidas com as respectivas mensagens
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// Variáveis do $request
		$module		= strtolower($request->getModuleName());
		$controller	= strtolower($request->getControllerName());
		$action		= strtolower($request->getActionName());
		
		// Evita execução fora do orçamento
		if ($module == 'guardiao') {
			// Regra de negócio para as definições de Ajuda
			$ajuda	= new Trf1_Guardiao_Ajuda();			
			$txtAjuda = $ajuda->getAjuda($controller, $action);
			
			// Criação das variáveis - por requisição - que conterá a mensagem de ajuda
			define('AJUDA_AJUDA', $txtAjuda);
		}
	}
}
