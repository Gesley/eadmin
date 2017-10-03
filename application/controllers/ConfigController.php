<?php
/**
 * @category	e-Admin
 * @package		ConfigController
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Controller para exibição da configuração, parâmetros e variáveis do servidor
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
class ConfigController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Configuração';
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Orcamento_Timer ();
		$this->_temporizador->Inicio ();
		
		$this->_helper->layout->setLayout ( 'padrao' );
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Configuração do sistema';
		
		// Busca dos dados
		$this->view->servidorSo = $_SERVER ['OS'];
		$this->view->servidorNome = $_SERVER ['COMPUTERNAME'];
		$this->view->servidorIp = getenv ( 'HTTP_X_FORWARDED_VARNISH' );
		$this->view->apache = $_SERVER ['SERVER_SOFTWARE'];
		$this->view->php = phpversion ();
		$this->view->zendFramework = Zend_Version::getLatest();
		$this->view->zendEngine = zend_version();
		$this->view->navegador = $_SERVER ['HTTP_USER_AGENT'];
	}

}