<?php
class Orcamento_ImportacaoController extends Zend_Controller_Action {
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
		$this->view->title = 'Importação de dados';
		
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
		/**
		 * @TODO: IMPORTANTE!!
		 * Para efetuar a exibição e contagem do tempo de resposta de cada 
		 * importação o form deverá ser redirecionado para novas actions:
		 * NC, NE e EXEC, onde o resultado de cada importação será apresentado.
		 * 
		 * Ver como mudar a action conforme os radio buttons selecionados (ver 
		 * com Daniel e/ou Leidison).
		 * 
		 * Cada nova action deverá mostrar um quadro resumo da importação,
		 * devendo chamar um _partial com dados pré determinados, como:
		 * Tempo de resposta (padrão),
		 * Qtde de linhas no arquivo,
		 * Qtde de registros importados com sucesso,
		 * Qtde de registros com erros,
		 * etc...
		 */
		// Título da tela (action)
		$this->view->telaTitle = 'Importação de dados externos';
		
		// Adiciona o formulário
		$formulario = new $this->_formulario ();
		
		// Exibe o formulário
		$this->view->formulario = $formulario;
		
		// Se for post...
		if ($this->getRequest ()->isPost ()) {
			$dados = $this->getRequest ()->getPost ();
			
			// Se os dados estiverem válidos
			if ($formulario->isValid ( $dados )) {
				// Variável para melhor descrição de erro, se for o caso, conforme a parte da rotina de importação
				$msgErro = '';
				$fase = '<strong>Fase: </strong>';
				
				try {
					/**
					 * Se não fosse necessário renomear os múltiplos arquivos ou
					 * se fosse o caso de 'upar' um único arquivo por ver
					 * então teria sido utilizado APENAS o próprio formulário para tal.
					 * 
					 * Contudo, será necessário 'upar' múltiplos arquivos e ainda
					 * os renomear, assim adotei o Zend_File_Transfer_Adapter_Http
					 */
					$negocio = new $this->_classeNegocio ();
					
					// Busca matrícula do usuário logado
					$sessao = new Zend_Session_Namespace ( 'userNs' );
					$matricula = strtolower ( $sessao->matricula );
					
					// Busca tipo de arquivo a importar
					$msgErro = $fase . "Busca tipo de arquivo a importar";
					$importacaoTipo = $formulario->TIPO_IMPORTACAO->getValue ();
					$importacaoDescricao = strtolower ( $negocio->retornaNomeImportacao ( $importacaoTipo ) );
					
					// Inicia o 'upload' do(s) arquivo(s) a importar
					$msgErro = $fase . "Inicia o 'upload' do(s) arquivo(s) a importar";
					
					// @TODO: Ver se funfa no próprio $formulario->TEXTO
					$upload = new Zend_File_Transfer_Adapter_Http ();
					$upload->setDestination ( $negocio->retornaPastaImportacao () );
					
					$arquivos = $upload->getFileInfo ();
					
					foreach ( $arquivos as $arquivo ) {
						// Define novo nome do(s) arquivo(s) a importar
						$msgErro = $fase . "Define novo nome do(s) arquivo(s) a importar";
						$nomeNovo = strtolower ( Date ( 'Y-m-d_H-i-s' ) . '_' . $importacaoDescricao . '_' . $matricula . '.' . pathinfo ( $arquivo ['name'], PATHINFO_EXTENSION ) );
						
						$upload->addFilter ( 'rename', $nomeNovo, $arquivo ['name'] );
						
						// Copia o(s) arquivo(s) para o servidor
						$msgErro = $fase . "Copia o(s) arquivo(s) para o servidor";
						$upload->receive ( $arquivo ['name'] );
					}
					
					// Busca nomes do(s) arquivo(s) recem importados
					$msgErro = $fase . "Busca nomes do(s) arquivo(s) recem importados";
					$arquivos = $upload->getFileName ( null, false );
					
					// Informações sobre '[TIPO DE ARQUIVO IMPORTADO]' não importadas.
					$negocio = new $this->_classeNegocio ();
					$msgErro = $fase . "Análise e tratamento dos dados sobre '$importacaoDescricao'.";
					$importa = $negocio->importacao ( $importacaoDescricao, $arquivos );
					
					$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_IMPORTAR_SUCESSO, 'status' => 'success' ) );
				} catch ( Exception $e ) {
					$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_IMPORTAR_ERRO . '<br />' . $msgErro . '<br />' . $e->getMessage () );
				}
				
				// Volta para a index
				$this->_redirect ( $this->_modulo . '/' . $this->_controle );
			} else {
				// Reapresenta os dados no formulário para correção do usuário
				$formulario->populate ( $dados );
			}
		}
	}
	
	public function reimportaAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Re-importação de dados pré-existentes';
		
		// Variável para melhor descrição de erro, se for o caso, conforme a parte da rotina de importação
		$msgErro = '';
		
		try {
			$negocio = new $this->_classeNegocio ();
			
			$msgErro = "Realiza nova importação a partir dos dados pré-existentes.";
			$importa = $negocio->importacaoDadosExistentes ();
			
			$this->_helper->flashMessenger ( array (message => Trf1_Orcamento_Definicoes::MENSAGEM_IMPORTAR_SUCESSO, 'status' => 'success' ) );
		} catch ( Exception $e ) {
			$this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_IMPORTAR_ERRO . '<br />' . $msgErro . '<br />' . $e->getMessage () );
		}
		
		// Volta para a index
		$this->_redirect ( $this->_modulo . '/' . $this->_controle );
	}
	
	public function errosAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Erros presentes após última importação de dados externos';
		
		// Dados do grid
		$negocio = new $this->_classeNegocio ();
		$dados = $negocio->retornaListagemErros ();
		$chavePrimaria = array ('IMPD_NR_LINHA' );
		
		// Geração do grid
		$acoes = array ('excluir' );
		$camposDetalhes = array (	'IMPD_NR_ERRO' => array ('title' => 'Erro', 'abbr' => '', 'format' => 'Linkvariavel' ),
									'IMPD_DS_CLASSE_ARQUIVO' => array ('title' => 'Importação', 'abbr' => '' ),
									'IMPD_DH_IMPORTACAO' => array ('title' => 'Data/Hora', 'abbr' => '' ),
									'IMPD_DS_ARQUIVO_ORIGEM' => array ('title' => 'Arquivo', 'abbr' => '' )
							);
		
		$camposOcultos = array ('IMPD_NR_LINHA' );
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
	
	private function erroOperacao($mensagemErro) {
		$erro = $mensagemErro;
		
		// Registra o erro
		$log = new Trf1_Orcamento_Log ();
		$requisicao = $this->getRequest ();
		$log->gravaLog ( $requisicao, $erro, zend_log::ERR );
		
		$this->_helper->flashMessenger ( array (message => $erro, 'status' => 'error' ) );
	}

}