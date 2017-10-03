<?php
class Orcamento_UgController extends Zend_Controller_Action {
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
		$this->view->title = 'Unidade gestora';
		
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
		// Título da tela (action)
		$this->view->telaTitle = 'Listagem das unidades gestoras';
		
		// Dados do grid
		$negocio = new $this->_classeNegocio ();
		$dados = $negocio->retornaListagem ();
		$chavePrimaria = $negocio->chavePrimaria ();
		
		// Geração do grid
		$acoes = array ('incluir', 'detalhe', 'editar', 'excluir' );
		$camposDetalhes = array (	'UNGE_CD_UG' => array ('title' => 'UG', 'abbr' => '' ),
									'UNGE_DS_UG' => array ('title' => 'Descrição', 'abbr' => '' ),
									'UNGE_SG_UG' => array ('title' => 'Sigla', 'abbr' => '' ),
									'PADR_DS_UG' => array ('title' => 'Padrão', 'abbr' => '' )
								);
		$camposOcultos = array ();
		
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
	
	public function incluirAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Incluir unidade gestora';
		
		// Adiciona o formulário
		$formulario = new $this->_formulario ();
		$formulario->Salvar->SetLabel ( 'Incluir' );
		
		// Exibe o formulário
		$this->view->formulario = $formulario;
		
		// Se for post...
		if ($this->getRequest ()->isPost ()) {
			$dados = $this->getRequest ()->getPost ();
			
			// Se os dados estiverem válidos
			if ($formulario->isValid ( $dados )) {
				// Busca a tabela para inclusão do registro
				$negocio = new $this->_classeNegocio ();
				$tabela = $negocio->tabela ();
				$registro = $tabela->createRow ( $dados );
				
				try {
					// Grava o novo registro no banco
					$codigo = $registro->save ();
					
					// Recria os cache referentes a esta controlles
					$this->recriarCaches ();
					
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success' ) );
				} catch ( Exception $e ) {
					$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage () );
				}
				
				// Volta para a index
				$this->_redirect ( $this->_modulo . '/' . $this->_controle );
			} else {
				// Reapresenta os dados no formulário para correção do usuário
				$formulario->populate ( $dados );
			}
		}
	}
	
	public function detalheAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Visualizar unidade gestora';
		
		// Identifica o parâmetro da chave primária a ser buscada
		$chavePrimaria = $this->_getParam ( 'cod' );
		
		if ($chavePrimaria) {
			// Busca registro específico
			$negocio = new $this->_classeNegocio ();
			$registro = $negocio->retornaRegistroNomeAmigavel ( $chavePrimaria );
			
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
	
	public function editarAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Editar unidade gestora';
		
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
				// Busca registro específico
				$registro = $negocio->retornaRegistro ( $chavePrimaria );
				$lotacao = $negocio->retornaLotacao ( $registro ['UNGE_CD_LOTACAO'], $registro ['UNGE_SG_SECAO'] );
				
				if ($registro) {
					// Bloqueia a edição da chave primária
					foreach ( $camposChave as $chave ) {
						$formulario->$chave->setAttrib ( 'readonly', true );
					}
					$formulario->LOTACAO->setValue ( $lotacao ['DESC_LOTACAO'] );
					
					// Exibe os dados do registro no formulário
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
				$chavePrimaria = $this->_getParam ( 'cod' );
				
				// Instancia a model para edição do registro
				$tabela = $negocio->tabela ();
				
				// Busca registro pela chave primária
				$registro = $tabela->find ( $chavePrimaria )->current ();
				
				// Não permite alteração na chave primária
				foreach ( $camposChave as $chave ) {
					unset ( $dados [$chave] );
				}
				// TODO: Analisar trecho de código abaixo
				unset ( $dados ['LOTACAO'] );
				$registro->setFromArray ( $dados );
				
				try {
					// Grava as alterações no banco
					$codigo = $registro->save ();
					
					// Recria os cache referentes a esta controlles
					$this->recriarCaches ();
					
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success' ) );
				} catch ( Exception $e ) {
					$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage () );
				}
				
				// Volta para a index
				$this->_redirect ( $this->_modulo . '/' . $this->_controle );
			} else {
				// Reapresenta o formulário para correção dos dados informados
				$formulario->populate ( $dados );
			}
		}
	}
	
	public function excluirAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Excluir unidades gestoras';
		
		// Instancia a regra de negócio
		$negocio = new $this->_classeNegocio ();
		
		// Verifica o tipo de requisição Get / Post
		if ($this->getRequest ()->isGet ()) {
			$chavePrimaria = $this->_getParam ( 'cod' );
			
			if ($chavePrimaria) {
				// Transforma o parâmetro informado para array de $chaves, se for o caso
				$chaves = explode ( ',', $chavePrimaria );
				
				// Busca os registros selecionados
				$registros = $negocio->retornaVariosRegistros ( $chaves );
				
				if ($registros) {
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
					// Transforma o parâmetro informado para array de $chaves, se for o caso
					$chaves = explode ( ',', $chavePrimaria );
					
					try {
						// Exclui o registro
						$negocio->exclusaoLogica ( $chaves );
						
						// Recria os cache referentes a esta controlles
						$this->recriarCaches ();
						
						$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO, 'status' => 'success' ) );
					} catch ( Exception $e ) {
						$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage () );
					}
				}
			} else {
				$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_CANCELAR, 'status' => 'notice' ) );
			}
			
			$this->_redirect ( $this->_modulo . '/' . $this->_controle );
		}
	}
	
	//public function ajaxtodaslotacoesfilhasAction() {
	public function ajaxlotacoesAction() {
		$descricao = $this->_getParam ( 'term', '' );
		$negocio = new $this->_classeNegocio ();
		$lotacoes = $negocio->retornaLotacoesAutoComplete ( $descricao );
		$fim = count ( $lotacoes );
		for($i = 0; $i < $fim; $i ++) {
			$lotacoes [$i] = array_change_key_case ( $lotacoes [$i], CASE_LOWER );
		}
		
		$this->_helper->json->sendJson ( $lotacoes );
	}
	
	public function ajaxugAction() {
		$descricao = $this->_getParam ( 'term', '' );
		$negocio = new $this->_classeNegocio ();
		$lotacoes = $negocio->retornaUG ( $descricao );
        
        $fim = count ( $lotacoes );
        
        for ( $i = 0; $i < $fim; $i ++ ) {
            $lotacoes [ $i ] = array_change_key_case ( $lotacoes [ $i ], CASE_LOWER );
        }
        
        $this->_helper->json->sendJson ( $lotacoes );
	}

	public function ajaxlotacoesrespAction() {
		$cod = $this->_getParam('cod','');
		$sigla = $this->_getParam('sigla','');
		$descricao = $this->_getParam ( 'term', '' );
		
		$negocio = new $this->_classeNegocio ();
		$lotacoes = $negocio->retornaLotacoesAutoCompleteResp ($cod, $sigla, $descricao );
		$fim = count ( $lotacoes );
		for($i = 0; $i < $fim; $i ++) {
			$lotacoes [$i] = array_change_key_case ( $lotacoes [$i], CASE_LOWER );
		}
		
		$this->_helper->json->sendJson ( $lotacoes );
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