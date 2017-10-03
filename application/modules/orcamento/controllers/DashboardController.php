<?php
class Orcamento_DashboardController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var $_temporizador
	 */
	private $_temporizador;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Dashboard';
		
		// Ajuda & Informações
		$this->view->msgAjuda = AJUDA_AJUDA;
		$this->view->msgInfo = AJUDA_INFOR;
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		// Grava log de cada ação
		$log = new Trf1_Orcamento_Log();
		$requisicao = $this->getRequest();
		$log->gravaLog($requisicao);
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Dashboard do sistema e-Orçamento!';
		
		// Dashboard
		// TODO: Ver confecção do Dashboard por usuário
		/*
		 * $dashboard = new Trf1_Sosti_Negocio_Dashboard();
		 * Zend_Debug::dump($dashboard->retornaInfraAvaliacoes(Trf1_Sosti_Negocio_Dashboard::CAIXA_INFRAESTRUTURA,
		 * 'hoje'));
		 */
		
	}

}