<?php
class Guardiao_LogController extends Zend_Controller_Action {
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
	
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var int $_temporizador
	 */
	//private $_temporizador;
	
	/**
	 * Nome do modulo para usos diversos
	 * 
	 * @var string $_modulo
	 */
	private $_modulo = null;
	
	/**
	 * Nome do controle para usos diversos
	 * 
	 * @var string $_controle
	 */
	private $_controle = null;
	
	/**
	 * Classe negocial padrão
	 * 
	 * @var string $_classeNegocio
	 */
	private $_classeNegocio = null;
	
	public function init() {
		/*
		 * @TODO: LAYOUT; É necessária a inclusão do arquivo orcamento.css para total compatibilidade com o controle;
		 * @TODO: LAYOUT; É necessária a inclusão do arquivo orcamento.js para total compatibilidade com o controle;
		 * @TODO: LAYOUT; Não há variável para exibição do título da action (se for diferente do título do navegador) [$this->view->telaTitle];
		 * @TODO: LAYOUT; Não há variável para exibição do tempo de carregamento da página [$this->view->tempoResposta];
		 * @TODO: AJUDA; Não há ajuda cadastrada para esse controle / actions
		 */
		// Título apresentado no Browser
		$this->view->title = 'Log de acesso';
		
		// Ajuda
		$this->view->msgAjuda = AJUDA_AJUDA;
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Orcamento_Timer ();
		$this->_temporizador->Inicio ();
		
		// Informações sobre a requisição
		$requisicao = $this->getRequest ();
		$this->_modulo = strtolower ( $requisicao->getModuleName () );
		$this->_controle = strtolower ( $requisicao->getControllerName () );
		
		// Define o nome da classe negocial padrão
		$this->_classeNegocio = 'Trf1_Guardiao_' . ucfirst ( $this->_controle );
	}
	
//	public function postDispatch() {
//		// Apresenta o tempo de carregamento da página
//		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
//	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->title = 'Listagem dos acessos ao sistema e-Admin';
		$this->view->telaTitle = 'Listagem dos acessos ao sistema e-Admin';
		
		// Dados do grid
		$log = new Trf1_Guardiao_Log ();
		$dados = $log->retornaLog ();
		$chavePrimaria = array ('cod' );
		
		// Geração do grid
		$acoes = array ('detalhe' );
		$camposDetalhes = array (	/* 'cod' => array ('title' => '', 'abbr' => '' ), */
									'usuario' => array ('title' => 'Usuário', 'abbr' => 'Matrícula do usuário' ),
									'ip' => array ('title' => 'IP', 'abbr' => 'Endereço IP do equipamento de acesso' ),
									'data' => array ('title' => 'Data', 'abbr' => 'Data do acesso (no formato aaaa-mm-dd)' ),
									'hora' => array ('title' => 'Hora', 'abbr' => 'Hora do acesso (no formato hh:mm:ss)' )
								);
		$camposOcultos = array ('cod' );
		$exportacoes = array ('pdf', 'excel' );
		
		$classeGrid = new Trf1_Guardiao_Grid ();
		$grid = $classeGrid->criaGrid ( $this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes, $exportacoes );
		
		// Personalização do grid
		foreach ( $camposDetalhes as $campo => $opcoes ) {
			$grid->updateColumn ( $campo, $opcoes );
		}
		
		// Oculta campos do grid
		$grid->setColumnsHidden ( $camposOcultos );
		
		// Exibição do grid
		$this->view->grid = $grid->deploy ();
	}
	
	public function detalheAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Visualizar log de acesso';
		
		// Identifica o parâmetro da chave primária a ser buscada
		$chavePrimaria = $this->_getParam ( 'cod' );
		
		if ($chavePrimaria) {
			// Busca registro específico
			$negocio = new $this->_classeNegocio ();
			$registro = $negocio->retornaRegistro ( $chavePrimaria );
			
			if ($registro) {
				// Exibe os dados do registro
				$this->view->dados = $registro;
			} else {
				$this->registroNaoEncontrado ();
			}
		} else {
			$this->codigoNaoInformado ();
		}
	}
	
	private function recriarCaches() {
		// nada a fazer nessa funcionalidade
	}
	
	private function registroNaoEncontrado() {
		$erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->_redirect ( $this->_modulo . '/' . $this->_controle );
	}
	
	private function codigoNaoInformado() {
		$erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->_redirect ( $this->_modulo . '/' . $this->_controle );
	}

}
