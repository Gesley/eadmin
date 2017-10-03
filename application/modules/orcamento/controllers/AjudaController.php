<?php
class Orcamento_AjudaController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var $_temporizador
	 */
	private $_temporizador;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Ajuda do e-Orçamento';
		
		// Ajuda & Informações
		/*
		$this->view->msgAjuda = AJUDA_AJUDA;
		$this->view->msgInfo = AJUDA_INFOR;
		*/
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		// Grava log de cada ação
		$log = new Trf1_Orcamento_Log();
		$requisicao = $this->getRequest();
		$log->gravaLog($requisicao);
	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Ajuda do e-Orçamento';
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function gridAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Funcionalidades do grid';
		
		// Busca opção, se for o caso
		$opcao = $this->_getParam ( 'opcao' );
		
		if ($opcao) {
			$opcoes = array ('ordem', 'semordem', 'filtro', 'semfiltro', 'semfiltroordem', 'acao', 'impressao', 'exportacao', 'paginacao', 'registros' );
			
			if (! in_array ( $opcao, $opcoes )) {
				$this->_redirect ( 'orcamento/ajuda/grid' );
			}
			
			$this->view->gridOpcao = $opcao;
		}
	}
	
	public function permissaoAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Níveis de permissão';
	}

}
