<?php
/**
 * @category	TRF1
 * @package		Trf1_Sisad_Plugin_Informacao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Plugin para busca dos textos de informacao a serem exibidas nas telas do sistema
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
 */
class Trf1_Sisad_Plugin_Informacao extends Zend_Controller_Plugin_Abstract
{
	/**
	 * preDispatch: Função do Plugin de Informacao que busca e retorna em constantes
	 *				a Informacao para serem apresentadas em cada tela.
	 *
	 * @see		Zend_Controller_Plugin_Abstract::preDispatch()
	 * @return	informacao_INFORMACAO preenchidas com as respectivas mensagens
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// Variáveis do $request
		$module		= strtolower($request->getModuleName());
		$controller	= strtolower($request->getControllerName());
		$action		= strtolower($request->getActionName());

		// Evita execução fora do orçamento
		if ($module == 'sisad') {
			// Regra de negócio para as definições de Informacao
			$informacao	= new Trf1_Sisad_Informacao();			
			$txtInformacao = $informacao->getInformacao($controller, $action);
			
			// Criação das variáveis - por requisição - que conterá a mensagem de informacao
			define('INFORMACAO_INFORMACAO', $txtInformacao);
		}
	}
}
