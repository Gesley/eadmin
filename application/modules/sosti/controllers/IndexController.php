<?php
class Sosti_IndexController extends Zend_Controller_Action {
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function init() {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		/* Initialize action controller here */
		$this->view->titleBrowser = 'e-Sosti';
		$this->view->title = 'Seja Bem-Vindo ao Sistema e-Sosti!';
	}
	
	public function indexAction() {
		// Validação de acesso às funcionalidades do SLA, conforme servidor web
		$negocio = new Trf1_Sosti_Negocio_Sla ();
		$permiteSla = $negocio->permiteSla ();
		
		if ($permiteSla ['permissao']) {
			// rotina comentada anteriormente
		} else {
			$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
			$this->_helper->_redirector ( 'index', 'index', 'admin' );
		}
		
		/*
		 * Código antigo: validação por horário 
		$horaInicio = mktime ( 10, 00, 00 );
		$horaFinal = mktime ( 19, 00, 00 );
		$horaAtual = mktime ( date ( "H" ), date ( "i" ), date ( "s" ) );
		$msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a visualização do Dashboard somente estará disponível antes das 10:00 e após às 19:00.";
		
		if ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) {
//            $userNs = new Zend_Session_Namespace('userNs');
//
//            $ocsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
//            $arrayPerfis = $ocsTbPupePerfilUnidPessoa->getPerfilUnidadePessoa($userNs->siglasecao, $userNs->codlotacao, $userNs->matricula);
//
//            foreach ($arrayPerfis as $perfil) {
//                if($perfil["PERF_ID_PERFIL"] == 54){
//                    //$this->view->dashboardSosti = 'INATIVO';
//                    $this->view->dashboardSosti = 'ATIVO';
//                    $this->view->title = 'Dashboard Sosti';
//                }else {
//
//                    $this->view->title = 'Seja Bem-Vindo ao Sistema e-Sosti!';
//                }
//            }
		} else {
			$this->_helper->flashMessenger ( array ('message' => $msgUsuario, 'status' => 'notice' ) );
			$this->_helper->_redirector ( 'index', 'index', 'admin' );
		}
		*/
	}
}
