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
 * Disponibiliza as funcionalidades ao usuário sobre justificativa da projeção.
 *
 * @category Orcamento
 * @package Orcamento_ProjecaojustificativaController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ProjecaojustificativaController extends Zend_Controller_Action {
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
		$this->view->title = 'Justificativa da projeção';
		
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
		// Dados do grid
		$negocio = new $this->_classeNegocio ();
		$despesa = $this->_getParam ( 'cod' );
		// Título da tela (action)
		$this->view->telaTitle = 'Listagem das justificativas da projeções orçamentária da despesa - ' . $despesa;
		
		$projecao = new Zend_Session_Namespace ( 'projecao' );
		$projecao->despesa = $despesa;
		
		if ($despesa) {
			$dados = $negocio->retornaListagem ( $despesa );
			$chavePrimaria = $negocio->chavePrimaria ();
			$chavePrimaria [1] = "DT_INI";
			
			// Geração do grid
			if (true) {
				$acoes = array ('incluir', 'detalhe', 'editar', 'excluir' );
			} else {
				$acoes = array ('detalhe' );
			}
			
			$camposDetalhes = array (	'PRJJ_NR_DESPESA' => array ('title' => 'Despesa', 'abbr' => '' ),
										'PRJJ_DH_JUSTIFICATIVA' => array ('title' => 'Data', 'abbr' => '' ),
										'PRJJ_DS_JUSTIFICATIVA' => array ('title' => 'Justificativa', 'abbr' => '' ),
										'PRJJ_IC_ORIGEM' => array ('title' => 'Origem', 'abbr' => '' )
								);
			
			$camposOcultos = array ('DT_INI' );
			$classeGrid = new Trf1_Orcamento_Grid ();
			$grid = $classeGrid->criaGrid ( $this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes );
			
			// Personalização do grid
			foreach ( $camposDetalhes as $campo => $opcoes ) {
				$grid->updateColumn ( $campo, $opcoes );
			}
			
			// Oculta campos do grid
			$grid->setColumnsHidden ( $camposOcultos );
			$this->view->grid = $grid->deploy ();
			$this->view->despesa = $despesa;
			
			// Grava em sessão as preferências do usuário para essa grid
			$requisicao = $this->getRequest ();
			$sessao = new Orcamento_Business_Sessao ();
			$sessao->defineOrdemFiltro ( $requisicao );
		} else {
			$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO, 'status' => 'notice' ) );
			// $this->_redirect ( 'orcamento/projecao' );
			$this->voltarIndexAction ( 'projecao' );
		}
	}
	
	public function incluirAction() {
		// Título da tela (action)
		$projecao = new Zend_Session_Namespace ( 'projecao' );
		$this->view->telaTitle = 'Incluir justificativa da projeções orçamentária da despesa - ' . $projecao->despesa;
		
		// Adiciona o formulário
		$formulario = new $this->_formulario ();
		$formulario->Salvar->SetLabel ( 'Incluir' );
		
		// Se for post...
		if ($this->getRequest ()->isPost ()) {
			$dados = $this->getRequest ()->getPost ();
			
			// Se os dados estiverem válidos
			if ($formulario->isValid ( $dados )) {
				// Busca a tabela para inclusão do registro
				$dados ['PRJJ_NR_DESPESA'] = $projecao->despesa;
				
				// Perfil
				$sessaoOrcamento = new Zend_Session_Namespace ( 'sessaoOrcamento' );
				$perfil = $sessaoOrcamento->perfil;
				
				// Identifica se é DIPOR
				if ($perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR) {
					$dados ['PRJJ_IC_ORIGEM'] = Trf1_Orcamento_Dados::PROJECAO_JUSTIFICATIVA_ORIGEM_DIPOR;
				} else {
					$dados ['PRJJ_IC_ORIGEM'] = Trf1_Orcamento_Dados::PROJECAO_JUSTIFICATIVA_ORIGEM_RESPONSAVEL;
				}
				$dados ['PRJJ_DH_JUSTIFICATIVA'] = new Zend_Db_Expr ( 'SYSDATE' );
				
				$negocio = new $this->_classeNegocio ();
				$tabela = $negocio->tabela ();
				$registro = $tabela->createRow ( $dados );
				
				try {
					// Grava o novo registro no banco
					$codigo = $registro->save ();
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success' ) );
				} catch ( Exception $e ) {
					$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage () );
					$this->voltarIndexAction ( 'projecao' );
				}
				
				// Volta para a index
				//$this->_redirect ( 'orcamento/projecaojustificativa/index/cod/' . $dados ['PRJJ_NR_DESPESA'] );
				$this->voltarIndexAction( $this->_controle, $dados ['PRJJ_NR_DESPESA'] );
			} else {
				// Reapresenta os dados no formulário para correção do usuário
				$formulario->populate ( $dados );
			}
		}
		$this->view->formulario = $formulario;
	}
	
	public function detalheAction() {
		// Título da tela (action)
		$projecao = new Zend_Session_Namespace ( 'projecao' );
		$this->view->telaTitle = 'Visualizar justificativa da projeções orçamentária da despesa - ' . $projecao->despesa;
		
		// Identifica o parâmetro da chave primária a ser buscada
		$chavePrimaria = $this->_getParam ( 'cod' );
		
		if ($chavePrimaria) {
			// Busca registro específico
			$negocio = new $this->_classeNegocio ();
			$registro = $negocio->retornaRegistroNomeAmigavel ( $chavePrimaria );
			
			if ($registro) {
				// Exibe os dados do registro
				$this->view->dados = $registro;
				$negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
				$this->view->despesa = $negocioDespesa->retornaDespesa ( $projecao->despesa );
			} else {
				$this->registroNaoEncontrado ();
			}
		} else {
			$this->codigoNaoInformado ();
		}
		
		$this->view->projecao = $projecao->despesa;
	}
	
	public function editarAction() {
		// Título da tela (action)
		$projecao = new Zend_Session_Namespace ( 'projecao' );
		$this->view->telaTitle = 'Editar justificativa da projeções orçamentária da despesa - ' . $projecao->despesa;
		
		// Instancia a regra de negócio
		$negocio = new $this->_classeNegocio ();
		$camposChave = $negocio->chavePrimaria ();
		
		$formulario = new $this->_formulario ();
		$this->view->formulario = $formulario;
		
		// Verifica o tipo de requisição Get / Post
		if ($this->getRequest ()->isGet ()) {
			// Busca dados para o preenchimento do formulário
			$chavePrimaria = $this->_getParam ( 'cod' );
			if ($chavePrimaria) {
				$registro = $negocio->retornaRegistro ( $chavePrimaria );
				
				if ($registro) {

					$this->verificaOrigem($registro["PRJJ_IC_ORIGEM"]);

					$formulario->populate ( $registro );
				} else {
					$this->registroNaoEncontrado ();
				}
			} else {
				$this->codigoNaoInformado ();
			}
		} else {
			// Busca dados do formulário
			$dados = $this->getRequest ()->getPost ();
			
			if ($formulario->isValid ( $dados )) {
				$chave = $this->_getParam ( 'cod' );
				$chavePrimaria = explode ( '-', $chave );
				$alterar = $negocio->alterarJustificativa ( $dados, $chave );
				if ($alterar == true) {
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success' ) );
				} else {
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />', 'status' => 'error' ) );
				}
				
				// Volta para a index
				//$this->_redirect ( 'orcamento/projecaojustificativa/index/cod/' . $chavePrimaria [0] );
				$this->voltarIndexAction( $this->_controle, $dados ['PRJJ_NR_DESPESA'] );
			} else {
				// Reapresenta o formulário para correção dos dados informados
				$formulario->populate ( $dados );
			}
		}
	}
	
	public function excluirAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Excluir justificativa da projeções orçamentária';

		// recupera a sessão.
		$sessao = new Orcamento_Business_Sessao ();
			
		// recupera o perfil do usuário logado na sessão.
		$perfil = $sessao->retornaPerfil();	

		Zend_Debug::dump($perfil);

		// Instancia a regra de negócio
		$negocio = new $this->_classeNegocio ();
		$projecao = new Zend_Session_Namespace ( 'projecao' );
		
		// Verifica o tipo de requisição Get / Post
		if ($this->getRequest ()->isGet ()) {
			$chavePrimaria = $this->_getParam ( 'cod' );
			if ($chavePrimaria) {
				// Busca os registros selecionados
				$registros = $negocio->retornaVariosRegistros ( $chavePrimaria );

				if ($registros) {
					
					// verifica se os registro(s) podem ser deletados pelo perfil logado
					foreach ($registros as $value) {
						// manda a origem para ver se pode ser excluido
						$this->verificaPerfil( $value['Origem'] );										
					}

					$this->view->codigo = $negocio->chavePrimaria ();
					$this->view->dados = $registros;
				} else {
					$this->registroNaoEncontrado ();
				}
			} else {
				$this->codigoNaoInformado ();
			}
		} else {
			// Busca a confirmação da exclusão
			$excluir = $this->getRequest ()->getPost ( 'cmdExcluir' );
			
			if ($excluir == 'Sim') {
				$chavePrimaria = $this->_getParam ( 'cod' );
				
				if ($chavePrimaria) {
					try {
						// Exclui o registro
						$negocio->exclusaoLogica ( $chavePrimaria );
						$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO, 'status' => 'success' ) );
					} catch ( Exception $e ) {
						$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage () );
					}
				}
			} else {
				$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_CANCELAR, 'status' => 'notice' ) );
			}
			
			// $this->_redirect ( $this->_modulo . '/' . $this->_controle . '/index/cod/' . $projecao->despesa );
			$this->voltarIndexAction( $this->_controle, $projecao->despesa );
		}
	}
	
	private function recriarCaches() {
		$cache = new Trf1_Orcamento_Cache ();
		$cache->excluirCachesSensiveis ( $this->_controle );
	}
	
	private function registroNaoEncontrado() {
		$erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->voltarIndexAction ( 'projecao' );
	}
	
	private function codigoNaoInformado() {
		$erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'notice' ) );
		$this->voltarIndexAction ( 'projecao' );
	}
	
	private function erroOperacao($mensagemErro) {
		$erro = $mensagemErro;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::ERR );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'error' ) );
	}
	
	/**
	 * Redireciona para a indexAction do _modulo e _controle
	 *
	 * @param integer $controle
	 *        Atipicamente, verifica se deve ir para a justificativa da projeção
	 *        ou a própria projeção orçamentária
	 * @param integer $despesa
	 *        Código da despesa para informar a justificativa da projeção
	 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
	 */
	private function voltarIndexAction ( $controle, $despesa = null )
	{
	    // Retorna a sessão das preferências do usuário para essa grid
	    $sessao = new Orcamento_Business_Sessao ();
	    $url = $sessao->retornaOrdemFiltro ( $controle );
	     
	    if ( $url ) {
	        // Redireciona para a url salva em sessão
	        $this->_redirect ( $url );
	    }
	    
	    if ( $controle ) {
	        $this->_redirect ( 'orcamento/projecao' );
	    } else {
	        // Redireciona para a url combinada entre modulo/controle/index
	        $this->_redirect ( 'orcamento/projecaojustificativa/index/cod/' . $despesa );
	    }
	}
	
	/**
	 * Verifica se o perfil seleionado pode excluir
	 */
	private function verificaPerfil ( $origem )
	{
		// recupera a sessão.
		$sessao = new Orcamento_Business_Sessao ();
			
		// recupera o perfil do usuário logado na sessão.
		$perfil = $sessao->retornaPerfil();			

		// Perfis nao permitidos
		$arrayPerfis = array('dipor', 'desenvolvedor');

		// Dipor n pode excluir justificativas originais
		if( $origem == '0') {
			if( in_array($perfil['perfil'], $arrayPerfis) ){
				$this->erroOperacao ( Trf1_Orcamento_Definicoes::PERMISSAO_PROJECAO_JUSTIFICATIVA_ERRO. ". Não é possivel editar/excluir uma justificativa original" );
				$this->voltarIndexAction ( 'projecaojustificativa' );
			}
		} else {
			if( !in_array($perfil['perfil'], $arrayPerfis) ){
				$this->erroOperacao ( Trf1_Orcamento_Definicoes::PERMISSAO_PROJECAO_JUSTIFICATIVA_ERRO. ". Não é possivel editar/excluir uma justificativa setorial" );
				$this->voltarIndexAction ( 'projecaojustificativa' );
			}
		}

		return true;		
	}

	/**
	 * Verifica se a origem e responsavel
	 */
	private function verificaOrigem( $origem )
	{
		if( $origem == 'Responsável' ){
			$origem = 0;
			$this->verificaPerfil ( $origem );
		}
	}

}
