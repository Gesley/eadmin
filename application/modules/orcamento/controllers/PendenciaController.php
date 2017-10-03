<?php
class Orcamento_PendenciaController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var $_temporizador
	 */
	private $_temporizador;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Pendências do sistema';
		
		// Ajuda & Informações
		$this->view->msgAjuda = AJUDA_AJUDA;
		$this->view->msgInfo = AJUDA_INFOR;
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		// Grava log de cada ação
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao );
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function indexAction() {
            // Título da tela (action)
            $this->view->telaTitle = 'Pendências do sistema';
		
            // recupera a sessão.
            $sessao = new Orcamento_Business_Sessao ();

            // recupera o perfil do usuário logado na sessão.
            $perfil = $sessao->retornaPerfil();

            $formulario = new Orcamento_Form_Pendencia ();
            $this->view->formulario = $formulario;
            $pendencias = array();

            if ($this->getRequest ()->isGet ()) {
                    $formulario->ANO->setValue ( date ( 'Y' ) );
            } else {
                    $dados = $this->getRequest ()->getPost ();
                     $formulario->ANO->setValue ( $dados ['ANO'] );

                    // Regras negocial
                    $negocio = new Trf1_Orcamento_Negocio_Pendencia ();
                    $pendencias = $negocio->retornaPendencias ( $dados ['ANO'] );

            }

            // Busca pendências diversas
            $this->view->pendencia = $pendencias;

            // envia o perfil para a visão.
            $this->view->perfil = $perfil['perfil'];
        
	}

}
