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
 * Disponibiliza as funcionalidades ao usuário sobre requisição de
 * disponibilidade orçamentária.
 *
 * @category Orcamento
 * @package Orcamento_RdoController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_RdoController extends Zend_Controller_Action {

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
     * Nome da action para usos diversos
     *
     * @var string $_action
     */
    private $_action = null;

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
        $this->view->title = 'RDO';

        // Ajuda & Informações
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;

        // Timer para mensuração do tempo de carregamento da página
        $this->_temporizador = new Trf1_Admin_Timer();
        $this->_temporizador->Inicio();

        // Informações sobre a requisição
        $requisicao = $this->getRequest();
        $this->_modulo = strtolower($requisicao->getModuleName());
        $this->_controle = strtolower($requisicao->getControllerName());
        $this->_action = strtolower($requisicao->getActionName());

        // Define o nome da classe negocial padrão
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst($this->_controle);

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);
        
        // Grava nova tabela de log 
        $this->_logdados = new Orcamento_Business_Negocio_Logdados();                
        
        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $log->gravaLog ( $requisicao );
	}


    public function postDispatch() {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function indexAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de requisições de disponibilidade orçamentária (RDO)';

        // Dados do grid
        $negocio = new $this->_classeNegocio();
        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'excluir');
        $camposDetalhes = array('REQV_ANO' => array('title' => 'Ano', 'abbr' => ''),
            'REQV_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'REQV_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'REQV_DH_VARIACAO' => array('title' => 'Data', 'abbr' => ''),
            'REQV_DS_DETALHAMENTO' => array('title' => 'Variação', 'abbr' => ''),
            'REQV_TIPO_PROCESSO' => array('title' => 'Tipo de processo', 'abbr' => ''),
            'REQV_NR_PROCESSO_ADM' => array('title' => 'Processo', 'abbr' => ''),
            //'REQV_PROCESSO' => array ('title' => 'Processo', 'abbr' => '' ),
            'REQV_IC_TP_VARIACAO' => array('title' => 'Variação', 'abbr' => ''),
            'VL_VARIACAO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'FL_NOTA_EMPENHO' => array('title' => 'Empenho', 'abbr' => ''),
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota Empenho', 'abbr' => ''),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor Nota Empenho', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
        );

        $camposOcultos = array('REQV_PROCESSO', 'EXERCICIO', 'NOEM_CD_NOTA_EMPENHO', 'NOEM_VL_NE_ACERTADO');

        $classeGrid = new Trf1_Orcamento_Grid();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        // Oculta campos do grid
        $grid->setColumnsHidden($camposOcultos);

        // Exibição do grid
        $this->view->grid = $grid->deploy();

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao();
        $sessao->defineOrdemFiltro($requisicao);
    }

    public function incluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir requisição de disponibilidade orçamentária (RDO)';

        // Adiciona o formulário
        $formulario = new $this->_formulario();
        $formulario->Salvar->SetLabel('Incluir');

        // Exibe o formulário
        $this->view->formulario = $formulario;

        // Se for post...
        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            // Se os dados estiverem válidos
            if ($formulario->isValid($dados)) {
                try {
                    // Busca dados para compor a informações sobre processo administrativo
                    $ug = new Trf1_Orcamento_Negocio_Ug();
                    $ug = $ug->secsubsecaoDespesa($dados["REQV_NR_DESPESA"]);
                    $dados["REQV_CD_SECSUBSEC"] = $ug["UNGE_CD_SECSUBSEC"];

                    /*
                    comentado pois o campo agora é um texto livre
                    if ($dados ["REQV_DS_PROCESSO"]) {
                    // Processo Digital
                    if ($dados ["TIPO_PROCESSO"] == 0) {
                    $mapperDocumento = new Sisad_Model_DataMapper_Documento ();
                    $numero = $mapperDocumento->getDocumentoIdByNrDoc ( $dados ["REQV_DS_PROCESSO"] );
                    $dados ["REQV_CD_PROC_DIGITAL"] = $numero [0] ["DOCM_ID_DOCUMENTO"];
                    } else {
                    $dados ["REQV_CD_PROC_FISICO"] = $dados ["REQV_DS_PROCESSO"];
                    }
                    unset ( $dados ["REQV_DS_PROCESSO"] );
                    }
                     */

                    $dados['REQV_DH_VARIACAO'] = new Zend_Db_Expr("SYSDATE");

                    $valor = new Trf1_Orcamento_Valor();
                    $valordo = $valor->retornaValorParaBancoRod($dados["REQV_VL_VARIACAO"]);
                    $dados["REQV_VL_VARIACAO"] = new Zend_Db_Expr("TO_NUMBER(" . $valordo . ")");

                    // Busca a tabela para inclusão do registro
                    $negocio = new $this->_classeNegocio();
                    $tabela = $negocio->tabela();
                    $registro = $tabela->createRow($dados);

                    // Grava o novo registro no banco
                    $codigo = $registro->save ();

                    // Grava na tabela de log do orçamento
                    $this->_logdados->incluirLog( $codigo["REQV_NR_DESPESA"] );                               

                    // Recria cache
                    $this->recriarCaches ();
                } catch ( Exception $e ) {
                        $this->erroOperacao ( Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage () );
                }

                // Volta para a index
                $this->voltarIncluirAction();
            } else {
                /* comentado pois o campo agora é um texto livre
                if ($dados ["REQV_DS_PROCESSO"]) {
                $dados ['REQV_DS_PROCESSO'] = $processo;
                }
                 */

                // Reapresenta os dados no formulário para correção do usuário
                $formulario->populate($dados);
            }
        }
    }

    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar requisição de disponibilidade orçamentária (RDO)';

        // Identifica o parâmetro da chave composta a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            $chaves = explode("-", $chavePrimaria);

            $despesa = $chaves[0];
            $dataHora = substr($chavePrimaria, strpos($chavePrimaria, '-') + 1);
            // fix do formato de datas do sosti 2015010001218012180160000011
            $ndata = date('Y-m-d H:i:s', strtotime($dataHora));

            // Busca registro específico
            $negocio = new $this->_classeNegocio();
            $registro = $negocio->retornaRegistroNomeAmigavel($despesa, $ndata);

            $ajuste['Despesa'] = $registro['Despesa'];
            $ajuste['Ano'] = $registro['Ano'];
            $ajuste['UG'] = $registro['UG'];
            $ajuste['Fonte'] = $registro['Fonte'];
            $ajuste['Programa de trabalho resumido'] = $registro['Programa de trabalho resumido'];
            $ajuste['Natureza da despesa'] = $registro['Natureza da despesa'];
            $ajuste['Data e Hora'] = $registro['Data e Hora'];
            $ajuste['Processo'] = $registro['Processo'];
            $ajuste['Nota de Empenho'] = $registro['Nota de Empenho'];
            $ajuste['Variação'] = $registro['Variação'];
            $ajuste['Valor'] = $registro['Valor'];
            $ajuste['Valor Empenhado'] = $registro['Valor Empenhado'];
            $ajuste['Valor Saldo'] = $registro['Valor Saldo'];

            if ($ajuste) {
                // Exibe os dados do registro
                $this->view->dados = $ajuste;
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    public function editarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar requisição de disponibilidade orçamentária (RDO)';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio();
        $camposChave = $negocio->chavePrimaria();

        $formulario = new $this->_formulario();
        $this->view->formulario = $formulario;

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            // Busca dados para o preenchimento do formulário
            $chavePrimaria = $this->_getParam('cod');
            if ($chavePrimaria) {
                $chavePrimaria = explode("-", $chavePrimaria);
                $chavePrimaria[1] = $chavePrimaria[1] . "-" . $chavePrimaria[2] . "-" . $chavePrimaria[3];
                $registro = $negocio->retornaRegistro($chavePrimaria[0], $chavePrimaria[1]);
                if ($registro) {
                    // Exibe os dados do registro no formulário
                    $formulario->populate($registro);
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            // Busca dados do formulário
            $dados = $this->getRequest()->getPost();

            if ($formulario->isValid($dados)) {
                // Define campo para conter apenas o código do processo
                try {
                    $cod = $this->_getParam('cod');
                    $cod = str_replace("%20", "-", $cod);

                    $chavePrimaria = explode("-", $cod);
                    $chavePrimaria[1] = $chavePrimaria[1] . "-" . $chavePrimaria[2] . "-" . $chavePrimaria[3];
                    $tabela = $negocio->tabela();
                    $chavePrimaria[1] = new Zend_Db_Expr("TO_DATE('$chavePrimaria[1]','DD-MM-YYYY HH24:MI:SS')");

                    $registro = $tabela->find($chavePrimaria[0], $chavePrimaria[1])->current();

                    $negocio->alteraRequisicao($dados, $cod);
                    
                    // Grava na tabela de log do orçamento
                    $this->_logdados->incluirLog();
                    
                    $cache = new Trf1_Cache();
                    $controller = Zend_Controller_Front::getInstance()->getRequest()
                        ->getControllerName();
                    $cache->excluirCache("orcamento_{$controller}_listagem");

                    // Recria os cache referentes a esta controlles
                    // obsoleto $this->recriarCaches ();

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
                } catch (Exception $e) {
                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta o formulário para correção dos dados informados
                /* comentao pois o campo agora é um texto livre
                if ($dados ["REQV_DS_PROCESSO"]) {
                $dados ['REQV_DS_PROCESSO'] = $processo;
                }
                 */

                $formulario->populate($dados);
            }
        }
    }

    public function excluirAction() {
		// Título da tela (action)
		$this->view->telaTitle = 'Excluir requisições de disponibilidade orçamentária (RDO)';

		// Instancia a regra de negócio
		$negocio = new $this->_classeNegocio ();

		// Verifica o tipo de requisição Get / Post
		if ($this->getRequest ()->isGet ()) {
			$chavePrimaria = $this->_getParam ( 'cod' );
			if ($chavePrimaria) {
				// Transforma o parâmetro informado para array de $chaves, se for o caso
				$chaves = str_replace ( "%20", ' ', substr ( strstr ( $_SERVER ['REQUEST_URI'], 'cod/' ), 4 ) );
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
					$chaves = str_replace ( "%3A", ':', substr ( strstr ( $_SERVER ['REQUEST_URI'], 'cod/' ), 4 ) );
					$chaves = str_replace ( "+", ' ', $chaves );
					$chaves = str_replace ( "%2C", ',', $chaves );

					try {
						// Exclui o registro
						$negocio->exclusaoLogica ( $chaves );

                                                // Grava na tabela de log do orçamento
                                                $this->_logdados->incluirLog();
                                                
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

			$this->voltarIndexAction();
		}
	}

    public function requisicoessemempenhoAction () {
            // Título da tela (action)
            $this->view->telaTitle = 'Listagem de requisições RDO sem nota de empenho';
            // Retorna instância de classe para manipulação de memória
            $mem = Orcamento_Business_Memoria::retornaInstancia();
            // Expande a quantidade de memória disponível para essa requisição
            $mem->expandeMemoria();
    
            // Dados do grid
            $negocio = new $this->_classeNegocio ();
            $dados = $negocio->retornaRequisicoesSemNotaListagem();
            $chavePrimaria = $negocio->chavePrimaria();
    
            // Geração do grid
            $acoes = array('incluir', 'detalhe', 'editar', 'excluir');
            $camposDetalhes = array('REQV_ANO' => array('title' => 'Ano', 'abbr' => ''),
                'REQV_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
                'REQV_CD_UG' => array('title' => 'UG', 'abbr' => ''),
                'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
                'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
                'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
                'REQV_DH_VARIACAO' => array('title' => 'Data', 'abbr' => ''),
                'REQV_DS_DETALHAMENTO' => array('title' => 'Variação', 'abbr' => ''),
                'REQV_TIPO_PROCESSO' => array('title' => 'Tipo de processo', 'abbr' => ''),
                'REQV_NR_PROCESSO_ADM' => array('title' => 'Processo', 'abbr' => ''),
                // 'REQV_PROCESSO' => array ('title' => 'Processo', 'abbr' => '' ),
                'REQV_IC_TP_VARIACAO' => array('title' => 'Variação', 'abbr' => ''),
                'VL_VARIACAO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid')
            );
    
            $camposOcultos = array();
    
            $classeGrid = new Trf1_Orcamento_Grid ();
            $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);
    
            // Personalização do grid
            foreach ($camposDetalhes as $campo => $opcoes) {
                $grid->updateColumn($campo, $opcoes);
            }
    
            // Oculta campos do grid
            $grid->setColumnsHidden($camposOcultos);
    
            // Exibição do grid
            $this->view->grid = $grid->deploy();
    
            // Grava em sessão as preferências do usuário para essa grid
            $requisicao = $this->getRequest();
            $sessao = new Orcamento_Business_Sessao ();
            $sessao->defineOrdemFiltro($requisicao);
        }

    public function requisicoescomempenhoAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de requisições RDO com nota de empenho';
        // Dados do grid
        $negocio = new $this->_classeNegocio();
        $dados = $negocio->retornaRequisicoesComNotaListagem();

        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'excluir');
        $camposDetalhes = array(
            'REQV_ANO' => array('title' => 'Ano', 'abbr' => ''),
            'REQV_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'REQV_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'REQV_DH_VARIACAO' => array('title' => 'Data', 'abbr' => ''),
            'REQV_DS_DETALHAMENTO' => array('title' => 'Variação', 'abbr' => ''),
            'REQV_TIPO_PROCESSO' => array('title' => 'Tipo de processo', 'abbr' => ''),
            'REQV_NR_PROCESSO_ADM' => array('title' => 'Processo', 'abbr' => ''),
            'REQV_IC_TP_VARIACAO' => array('title' => 'Variação', 'abbr' => ''),
            // 'REQV_PROCESSO' => array ('title' => 'Processo', 'abbr' => '' ),
            'VL_VARIACAO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
        );

        $camposOcultos = array();

        $classeGrid = new Trf1_Orcamento_Grid();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        // Oculta campos do grid
        $grid->setColumnsHidden($camposOcultos);

        // Exibição do grid
        $this->view->grid = $grid->deploy();

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao();
        $sessao->defineOrdemFiltro($requisicao);
    }

    public function ajaxprocessofisicoAction() {
        $parametro = $this->_getParam('term', '');
        $despesa = $this->_getParam('despesa', '');
        $negocio = new $this->_classeNegocio();
        $processo = $negocio->processoFisico($parametro, $despesa);
        /*         Old process
        $fim = count ( $processo );
        for($i = 0; $i < $fim; $i ++) {
        $processo [$i] = array_change_key_case ( $processo [$i], CASE_LOWER );
        }
         */
        $resultado = '';
        foreach ($processo as $value) {
            $resultado[] = array('label' => $value['LABEL']);
        }

        $this->_helper->json->sendJson($resultado);
    }

    public function ajaxprocessodigitalAction() {
        $parametro = $this->_getParam('term', '');
        $negocio = new $this->_classeNegocio();
        $processo = $negocio->processoDigital($parametro);

        $resultado = '';
        foreach ($processo as $value) {
            $resultado[] = array('label' => $value['LABEL']);
        }
        $this->_helper->json->sendJson($resultado);
    }

    private function recriarCaches() {
        $cache = new Trf1_Orcamento_Cache();
        $cache->excluirCachesSensiveis($this->_controle);
    }

    private function registroNaoEncontrado() {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    private function codigoNaoInformado() {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    private function erroOperacao($mensagemErro) {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::ERR);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'error'));
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function voltarIndexAction() {
        // Retorna a sessão das preferências do usuário para essa grid
        $sessao = new Orcamento_Business_Sessao();
        $url = $sessao->retornaOrdemFiltro($this->_controle);

        if ($url) {
            // Redireciona para a url salva em sessão
            $this->_redirect($url);
        } else {
            // Redireciona para a url combinada entre modulo/controle/index
            $this->_redirect($this->_modulo . '/' . $this->_controle);
        }
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function voltarIncluirAction() {

        // Redireciona para a url combinada entre modulo/controle/index
        $this->_redirect($this->_modulo . '/' . $this->_controle . '/incluir');
    }

    public function rdoneAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de requisições de disponibilidade orçamentária (RDO x NE)';

        // Dados do grid
        $negocio = new $this->_classeNegocio();
        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar', 'excluir');
        $camposDetalhes = array(
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota Empenho', 'abbr' => ''),
            'REQV_ANO' => array('title' => 'Ano', 'abbr' => ''),
            'REQV_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'REQV_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'REQV_DH_VARIACAO' => array('title' => 'Data', 'abbr' => ''),
            'REQV_DS_DETALHAMENTO' => array('title' => 'Variação', 'abbr' => ''),
            'REQV_TIPO_PROCESSO' => array('title' => 'Tipo de processo', 'abbr' => ''),
            'REQV_NR_PROCESSO_ADM' => array('title' => 'Processo', 'abbr' => ''),
            'REQV_IC_TP_VARIACAO' => array('title' => 'Variação', 'abbr' => ''),
            'VL_VARIACAO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            /*'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor Nota Empenho', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),*/
            );

        $camposOcultos = array('REQV_PROCESSO', 'EXERCICIO', 'NOEM_VL_NE_ACERTADO');

        $classeGrid = new Trf1_Orcamento_Grid();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        // Oculta campos do grid
        $grid->setColumnsHidden($camposOcultos);

        // Exibição do grid
        $this->view->grid = $grid->deploy();

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao();
        $sessao->defineOrdemFiltro($requisicao);
    }
}
