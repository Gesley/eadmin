<?php
/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre extrato.
 *
 * @category Orcamento
 * @package Orcamento_ExtratoController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ExtratoController extends Zend_Controller_Action {
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
		$this->view->title = 'Extrato';
		
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
		$this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst ( $this->_controle );
		
		// Define o nome do formulário
		$this->_formulario = 'Orcamento_Form_Despesapergunta';
		
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
		$this->view->telaTitle = 'Consulta a extrato da despesa';
		
		$formulario = new $this->_formulario ();
		$this->view->formulario = $formulario;
		
		if ($this->getRequest ()->isPost ()) {
			$dados = $this->getRequest ()->getPost ();
			
			if ($formulario->isValid ( $dados )) {
				$this->_redirect ( 'orcamento/extrato/detalhe/cod/' . $dados ['DESP_NR_DESPESA'] . '/order/DT_LANCAMENTO_ASC');
			}
		}
	}
	
	public function detalheAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Extrato da despesa';
		
		// Identifica o parâmetro da chave primária a ser buscada
		$despesa = $this->_getParam ( 'cod' );
		
		if ($despesa) {
			// Busca dados da despesa
			$negocioDespesa = new Trf1_Orcamento_Negocio_Despesa();
			$dadosDespesa = $negocioDespesa->retornaDespesa( $despesa );
			
			if ( $dadosDespesa ) {
				// Exibe dados da despesa na view
				$this->view->despesa = $dadosDespesa;
				
				// Busca registro específico
				$negocio = new $this->_classeNegocio ();
				$dados = $negocio->retornaListagem ( $despesa );
				// $chavePrimaria = $negocio->chavePrimaria ();
				$chavePrimaria = array();
				
				// Geração do grid
				// $acoes = array ( 'detalhe' );
				$acoes = array();
				
				$decoradorLink = '{{DS_LANCAMENTO}}';
				// $decoradorLink = '<a href="{{LINK}}{{CODIGO}}" title="Detalhamento deste lançamento" target="_blank"> {{DS_LANCAMENTO}} </a>'
				
				$camposDetalhes = array (	'DT_LANCAMENTO' => array ('title' => 'Data', 'abbr' => '' ),
											'DS_LANCAMENTO' => array ('title' => 'Lançamento', 'abbr' => '', 'decorator' => $decoradorLink ),
											'DS_ORIGEM' => array ('title' => 'Origem', 'abbr' => '' ),
											'VL_LANCAMENTO' => array ('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor' )
										);
				$camposOcultos = array ( 'LINK', 'CODIGO' );
				
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
			} else {
				$this->registroNaoEncontrado ();
			}
		} else {
			$this->codigoNaoInformado ();
		}
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
	
	public function dashboardAction() {
		/*
		 * TODO: A documentar após a conclusão do Dashboard pelo Wilton /
		 * Anderson tipo:	string	pizza, barra, coluna, linha, grid, html e texto
		 * titulo:	string	Texto de título do bloco individual do dashboard
		 * dados:	array	Contém label e valor para os registros a serem
		 * mostrados. Limite de 10 registros?? legenda:	string	Texto no rodapé
		 * do bloco individual do dashboard
		 */
		$dados = array ('tipo' => 'grid', 'titulo' => 'Grid teste', 'dados' => array ('labels' => array ('UG', 'PTRes', 'Natureza de Despesa', 'Valor' ), 'linhas' => array (array ('90027', '880', '339030', 546213.99 ), array ('90013', '821', '339033', 321987.77 ), array ('90012', '821', '339036', 85285.55 ), array ('90004', '821', '339039', 30741.33 ), array ('90002', '821', '339030', 9850.11 ) ) ), 'legenda' => 'Fonte: e-Orçamento' );
		
		return $this->_helper->json->sendJson ( $dados );
	}

}
