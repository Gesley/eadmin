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
 * Disponibiliza as funcionalidades ao usuário sobre execução de nota de
 * empenho.
 *
 * @category Orcamento
 * @package Orcamento_NeexecController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_NeexecController extends Zend_Controller_Action {
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
		$this->view->title = 'Execução da Nota de empenho';
		
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
		$this->_classeNegocio = 'Trf1_Orcamento_Negocio_Ne';
		
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
		$this->view->telaTitle = 'Listagem da execução das notas de empenho por despesa';
		
		// ASmR - Alteração para compatibilização
		// Retorna instância de classe para manipulação de memória
		$mem = Orcamento_Business_Memoria::retornaInstancia();
		
		// Expande a quantidade de memória disponível para essa requisição
		$mem->expandeMemoria ();
		
		// Dados do grid
		$negocio = new $this->_classeNegocio ();
		$dados = $negocio->retornaListagemExecucao ();
		$chavePrimaria = array ( 'EXEC_NR_DESPESA' );
		
		// Zend_Debug::dump ( $dados );
		//exit;
		
		// Geração do grid
		$acoes = array ('detalhe', 'editar');
		$camposDetalhes = array (	'EXEC_NR_DESPESA' => array ('title' => 'Despesa', 'abbr' => '' ),
									'EXEC_CD_UG' => array ('title' => 'UG', 'abbr' => '' ),
									'EXEC_VL_JANEIRO' => array ('title' => 'Janeiro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_FEVEREIRO' => array ('title' => 'Fevereiro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_MARCO' => array ('title' => 'Março', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_ABRIL' => array ('title' => 'Abril', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_MAIO' => array ('title' => 'Maio', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_JUNHO' => array ('title' => 'Junho', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_JULHO' => array ('title' => 'Julho', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_AGOSTO' => array ('title' => 'Agosto', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_SETEMBRO' => array ('title' => 'Setembro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_OUTUBRO' => array ('title' => 'Outubro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_NOVEMBRO' => array ('title' => 'Novembro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'EXEC_VL_DEZEMBRO' => array ('title' => 'Dezembro', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' ),
									'VR_EXEC_TOTAL' => array ('title' => 'Total', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' )
								);
		$camposOcultos = array ( 'VR_EXEC_MES_ATUAL', 'VR_EXEC_PASSADA', 'EXEC_VL_TOTAL', 'VALOR' );
		
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
		
		// Grava em sessão as preferências do usuário para essa grid
		$requisicao = $this->getRequest ();
		$sessao = new Orcamento_Business_Sessao ();
		$sessao->defineOrdemFiltro ( $requisicao );
	}
	
	public function detalheAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Visualizar nota de empenho';
		
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
		$this->view->telaTitle = 'Editar nota de empenho';
		
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

                                if ($registro) {
					// Bloqueia a edição da chave primária
					foreach ( $camposChave as $chave ) {
						$formulario->$chave->setAttrib ( 'readonly', true );
					}
					
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
				
				$camposNecessarios = array('NOEM_NR_DESPESA');
				$todoscampos = array_keys($dados);
				
				foreach ($todoscampos as $campo) {
					if (!in_array($campo, $camposNecessarios)) {
						unset($dados[$campo]);
					}
				}
				
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

	public function importarAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Importar Notas de Empenho';
	}
	
	/**
	 * Redireciona para a indexAction do _modulo e _controle
	 *
	 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
	 */
	private function voltarIndexAction ()
	{
	    // Retorna a sessão das preferências do usuário para essa grid
	    $sessao = new Orcamento_Business_Sessao ();
	    $url = $sessao->retornaOrdemFiltro ( $this->_controle );
	     
	    if ( $url ) {
	        // Redireciona para a url salva em sessão
	        $this->_redirect ( $url );
	    } else {
	        // Redireciona para a url combinada entre modulo/controle/index
	        $this->_redirect ( $this->_modulo . '/' . $this->_controle );
	    }
	}
	
}