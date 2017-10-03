<?php
/**
 * Plugin para análise da permissão, a nível de registros, específicos ao e-Orçamento
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Plugin_Permissao
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
class Trf1_Orcamento_Plugin_Permissao extends Zend_Controller_Plugin_Abstract {
	/**
	 * preDispatch: Função do Plugin de Ajuda que busca e retorna em constantes
	 * a Ajuda e Informação para serem apresentadas em cada tela.
	 *
	 * @see		Zend_Controller_Plugin_Abstract::preDispatch()
	 * @return	array	preenchidas com níveis de permissão e lotação / responsáveis
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $requisicao) {
		// Variáveis do $request
		$module = strtolower ( $requisicao->getModuleName () );
		$controller = strtolower ( $requisicao->getControllerName () );
		$action = strtolower ( $requisicao->getActionName () );
		
		// Evita execução fora do orçamento
		if ($module == Trf1_Orcamento_Definicoes::NOME_MODULO) {
			// Regra de negócio para as definições de Ajuda e Informação
			/* $classe = 'Trf1_Orcamento_Permissao'; */
			$classe = 'Trf1_Orcamento_Acl';
			
			if (class_exists ( $classe )) {
				$permissao = new $classe ();
				/* $permissao->definePermissoes(); */
				$permissao->autoriza ( $requisicao );
			}
		}
	}
}
