<?php
class Orcamento_LogController extends Zend_Controller_Action {
	/**
	 * Timer para mensuração do tempo de carregamento da página
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
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
	
	/**
	 * Classe negocial padrão
	 * 
	 * @var string $_formulario
	 */
	private $_formulario = null;
	
	public function init() {
		// Título apresentado no Browser
		$this->view->title = 'Log de eventos';
		
		// Ajuda & Informações
		$this->view->msgAjuda = AJUDA_AJUDA;
		$this->view->msgInfo = AJUDA_INFOR;
		
		// Timer para mensuração do tempo de carregamento da página
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
		// Informações sobre a requisição
		$requisicao = $this->getRequest ();
		$this->_modulo = strtolower ( $requisicao->getModuleName () );
		$this->_controle = strtolower ( $requisicao->getControllerName () );
		
		// Define o nome da classe negocial padrão
		$this->_classeNegocio = 'Trf1_Orcamento_' . ucfirst ( $this->_controle );
		
		// Define o nome do formulário
		$this->_formulario = 'Orcamento_Form_' . ucfirst ( $this->_controle );
		
		// Grava log de cada ação
		$log = new Trf1_Orcamento_Log ();
		$log->gravaLog ( $requisicao );
	}
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da página
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
	public function indexAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Consulta aos eventos registrados em log do e-Orçamento';
		
		$formulario = new $this->_formulario ();
		$formulario->ANO->setValue ( date ( 'Y' ) );
		$formulario->MES->setValue ( date ( 'm' ) );
		$this->view->formulario = $formulario;
	}
	
	public function listagemAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Listagem dos eventos registrados em log';
		
		// Busca dados específicos da requisição
		$parametros = $this->getRequest ()->getParams ();
		
		$ano = $parametros ['ANO'];
		$mes = $parametros ['MES'];
		$parametro = $parametros ['PARAMETRO'];
		
		// Dados do grid
		$log = new Trf1_Orcamento_Log ();
		$dados = $log->retornaLog ( $ano, $mes, $parametro );
		$chavePrimaria = array ('cod' );
		
		// Geração do grid
		$acoes = array ('detalhe' );
		$camposDetalhes = array (	/* 'cod' => array ('title' => '', 'abbr' => '' ), */
									'data' => array ('title' => 'Data', 'abbr' => '' ),
									'hora' => array ('title' => 'Hora', 'abbr' => '' ),
									'usuario' => array ('title' => 'Usuário', 'abbr' => '' ),
									/* 'modulo' => array ('title' => 'Módulo', 'abbr' => '' ), */
									'controle' => array ('title' => 'Controle', 'abbr' => '' ),
									'acao' => array ('title' => 'Ação', 'abbr' => '' ),
									'parametros' => array ('title' => 'Parâmetros', 'abbr' => '' ),
									'mensagem' => array ('title' => 'Mensagem', 'abbr' => '' ),
									'prioridade' => array ('title' => 'Prior.', 'abbr' => '', 'orderField', 'cod_prioridade' )
									/* 'cod_prioridade' => array ('title' => 'ID Prior', 'abbr' => '' ) */
							);
		$camposOcultos = array ('cod', 'modulo', 'cod_prioridade' );
		
		$classeGrid = new Trf1_Orcamento_Grid ();
		$grid = $classeGrid->criaGrid ( $this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes );
		
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
		$this->view->telaTitle = 'Visualizar evento de log';
		
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
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->_redirect ( $this->_modulo . '/' . $this->_controle );
	}
	
	private function codigoNaoInformado() {
		$erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->_redirect ( $this->_modulo . '/' . $this->_controle );
	}
	
	private function erroOperacao($mensagemErro) {
		$erro = $mensagemErro;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::ERR );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'error' ) );
	}

}