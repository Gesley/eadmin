<?php
/**
 * Plugin para busca dos textos de ajuda e informação a serem exibidas nas telas do sistema
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Plugin_Ajuda
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights 
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
 */
class Trf1_Orcamento_Plugin_Ajuda extends Zend_Controller_Plugin_Abstract
{
	/**
	 * preDispatch: Função do Plugin de Ajuda que busca e retorna em constantes
	 *				a Ajuda e Informação para serem apresentadas em cada tela.
	 *
	 * @see		Zend_Controller_Plugin_Abstract::preDispatch()
	 * @return	AJUDA_AJUDA e AJUDA_INFOR preenchidas com as respectivas mensagens
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		// Variáveis do $request
		$module		= strtolower($request->getModuleName());
		$controller	= strtolower($request->getControllerName());
		$action		= strtolower($request->getActionName());
		
		// Evita execução fora do orçamento
		if ($module == Trf1_Orcamento_Definicoes::NOME_MODULO) {
			// Respostas padrão para o caso de qualquer erro ao tentar obter os textos do banco
			$txtAjuda['ajuda'] = 'Não foi possível localizar a classe de ajuda.';
			$txtAjuda['informacao'] = 'Não foi possível localizar a classe de ajuda.';
			
			// Próximas 2 linhas são réplica de Zend_View_Helper_Url->url();
			$router = Zend_Controller_Front::getInstance()->getRouter();
			$link = $router->assemble(array('module'		=> $module,
											'controller'	=> 'ajuda',
											'action'		=> 'index'), null, true, true);
			
			// Regra de negócio para as definições de Ajuda e Informação
			$classe = 'Trf1_Orcamento_AjudaInformacao';
			
			if (class_exists ( $classe )) {
				$ajuda = new $classe();
				
				$txtAjuda = $ajuda->getAjudaInformacao($controller, $action, $link);
			}
			
			// Retorno forçado - via "constantes" (...que são geradas a cada request.)
			define('AJUDA_AJUDA', $txtAjuda['ajuda']);
			define('AJUDA_INFOR', $txtAjuda['informacao']);
		}
	}
}
