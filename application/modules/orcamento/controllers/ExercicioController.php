<?php

/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre exercicio.
 *
 * @category Orcamento
 * @package Orcamento_ExercicioController
 * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ExercicioController extends Zend_Controller_Action {

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

    /**
     * Definições de variaveis
     * 
     * @name init()
     */
    public function init () {
        // Título apresentado no Browser
        $this->view->title = 'Exercício Orçamentário';

        // Ajuda & Informações
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;

        // Timer para mensuração do tempo de carregamento da página
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        // Informações sobre a requisição
        $requisicao = $this->getRequest();

        // Define o nome do modulo
        $this->_modulo = strtolower($requisicao->getModuleName());

        // Define o nome do controller 
        $this->_controle = strtolower($requisicao->getControllerName());

        // Define o nome da classe negocial padrão
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst($this->_controle);

        // Instancia de fase ano exercicio
        $this->_classeNegocioFaseAno = 'Trf1_Orcamento_Negocio_Faseano';

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);

        // Grava nova tabela de log 
        $this->_logdados = new Orcamento_Business_Negocio_Logdados();            
        
        // Define a tabela de exercicio
        $this->_CeoTbAnoExercicio = new Trf1_Orcamento_Negocio_Exercicio ();

        // Define a tabela de fase
        $this->_CeoTbAnoFaseAno = new Trf1_Orcamento_Negocio_Faseano ();


        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $log->gravaLog($requisicao);
    }

    /**
     * Dispara o tempo de resposta depois de carregar a pagina
     * 
     * @name postDispatch
     */
    public function postDispatch () {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    /**
     * Exibe a listagem de ano exercicio
     * 
     * @name indexAction
     */
    public function indexAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Pesquisar Exercício Orçamentário';

        // Dados do grid
        $negocio = new $this->_classeNegocio ();

        $dados = $negocio->retornaListagemSimplificada();

        if (!$dados) {
            $this->_redirect($this->_modulo . '/' . $this->_controle . '/incluir');
        }



        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'excluir');

        $camposDetalhes = array(
            'ANOE_AA_ANO' => array('title' => 'Ano Exercício', 'abbr' => ''),
            'ANOE_DS_OBSERVACAO' => array('title' => 'Descrição', 'abbr' => ''),
            'FASE_NM_FASE_EXERCICIO' => array('title' => 'Status', 'abbr' => '')
        );

        $classeGrid = new Trf1_Orcamento_Grid ();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        // Oculta campos do grid
        // $grid->setColumnsHidden($camposOcultos);
        // Exibição do grid
        $this->view->grid = $grid->deploy();

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao ();
        $sessao->defineOrdemFiltro($requisicao);
    }

    /**
     * Inclui o ano e a fase do exercicio
     * 
     * @name incluirAction
     */
    public function incluirAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Cadastrar Exercício Orçamentário';

        // Adiciona o formulário
        $formulario = new $this->_formulario ();

        // Define a tabela de negocio Exercicio
        $negocio = new Orcamento_Business_Negocio_Exercicio();
        $negocio->excluiCaches();

        // Exibe o formulário
        $this->view->formulario = $formulario;

        // Se for post...
        if ($this->getRequest()->isPost()) {

            $dados = $this->getRequest()->getPost();

            // Se os dados estiverem válidos
            if ($formulario->isValid($dados)) {

                // Verifica se o ano exercicio já foi excluido lógicamente
                $registro = $negocio->retornaExercicioExcluido($dados['ANOE_AA_ANO']);

                // Verifica se o ano exercicio do mesmo estatus ja existe
                $status = $negocio->retornaAnoExercicioComStatus($dados['ANOE_AA_ANO'], Trf1_Orcamento_Definicoes::FASE_EXERCICIO);
                
                // Verifica se ja existe
                if ($registro) {
                    // seta mensagem de erro e redireciona
                    $this->erroOperacao("Ocorreu um erro ao cadastrar a exercicio. " . Orcamento_Business_Dados::MSG_REGISTRO_NAO_ENCONTRADO . '<br />');
                    $this->voltarIndexAction();
                }

                // Faz a inclusão do na tabela de exercicio e fase
                if( $negocio->incluirExercicioFase ( $dados ) ) {
                    
                    // inclui na tabela de log do orçamento
                    $this->_logdados->incluirLog( $dados["ANOE_AA_ANO"] );
                    // Exclui o cache do sistema
                    $negocio->excluiCaches ();
                    
                    $this->_helper->flashMessenger ( array ( message => Orcamento_Business_Dados::MSG_SUCESSO_EXERCICIO, 'status' => 'success' ) );
                } else {
                    // TODO: A mensagem abaixo deveria ser a Orcamento_Business_Dados::MSG_INCLUIR_ERRO
                    $this->erroOperacao("Ocorreu um erro ao cadastrar a exercicio. O Exercicio pode ter sido excluido logicamente.");
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta os dados no formulário para correção do usuário
                $formulario->populate($dados);
            }
        }
    }

    /**
     * Exibe os detalhes do ano e da fase do exercicio
     * 
     * @name detalheAction
     */
    public function detalheAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Exercício Orçamentário';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            // Busca registro específico
            $negocio = new $this->_classeNegocio ();
            $registro = $negocio->retornaRegistro($chavePrimaria);

            if ($registro) {
                // Exibe os dados do registro
                $this->view->dados = $registro;
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    /**
     * Edita a fase do ano exercicio
     * 
     * @name editarAction
     */
    public function editarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Exercício Orçamentário';

        // Instancia a regra de negócio
        $negocio = new Orcamento_Business_Negocio_Exercicio();

        // Fase
        $fase = new Orcamento_Model_DbTable_FaseAnoExercicio();

        $formulario = new $this->_formulario ();
        $this->view->formulario = $formulario;

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            // Busca dados para o preenchimento do formulário
            $cod = $this->_getParam('cod');

            if ($cod) {
                // Busca registro específico
                $registro = $negocio->retornaRegistro($cod);

                if ($registro) {
                    // TODO Remover a linha a seguir
                    // $contrato = $this->_CeoTbAnoExercicio->retornaRegistro($cod);

                    $camposChave = $negocio->chavePrimaria();

                    foreach ($camposChave as $chave) {
                        $formulario->$chave->setAttrib('readonly', true);
                    }
                    $formulario->populate($registro);
                } else {
                    $this->registroNaoEncontrado();
                }

                $this->view->fase = $dadosfase = $fase->fetchRow("FANE_NR_ANO = $cod");
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            // Busca dados do formulário
            $dados = $this->getRequest()->getPost();
            $chavePrimaria = $this->_getParam('cod');

            if ($formulario->isValid($dados)) {

                $dados = $formulario->getValues();

                $resultado = $negocio->editarExercicioFase($dados);

                if ($dados['FANE_ID_FASE_EXERCICIO'] == Trf1_Orcamento_Definicoes::FASE_EXERCICIO_EM_EXECUCAO) {

                    $negocio->copiaValores($dados);
                }

                if( !$resultado [ 'sucesso' ] ) {
                    // inclui na tabela de log do orçamento
                    $this->_logdados->incluirLog( $dados["ANOE_AA_ANO"] );
                    // Mensagem sucesso
                    $this->_helper->flashMessenger ( array ( message => Orcamento_Business_Dados::MSG_ALTERAR_ERRO . '<br />' . $resultado [ 'msgErro' ] ) );
                }

                // Salvo com sucesso
                $this->_helper->flashMessenger(array(message => Orcamento_Business_Dados::MSG_SUCESSO_EXERCICIO, 'status' => 'success'));

                // Limpa o cache para listagem na index
                $negocio->excluiCaches();

                // Redireciona para o modulo
                $this->voltarIndexAction();
            } else {
                // Reapresenta os dados no formulário para correção do usuário
                $formulario->populate($dados);
            }
        }
    }

    /**
     * Exclui lógicamente um exercicio da listagem
     * 
     * @name excluirAction
     */
    public function excluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Exercício Orçamentário';

        // Instancia a regra de negócio
        $negocio = new Orcamento_Business_Negocio_Exercicio();
        $negocioFase = new Orcamento_Business_Negocio_FaseAnoExercicio();

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            $chavePrimaria = $this->_getParam('cod');

            // Verifica se foi marcado apenas um registro
            if (strlen($chavePrimaria) > 4) {
                $this->_helper->flashMessenger(array(message => Orcamento_Business_Dados::MSG_EXCESSO_REGISTROS, 'status' => 'notice'));
                $this->voltarIndexAction();
            }

            if ($chavePrimaria) {
                // Transforma o parâmetro informado para array de $chaves, se for o caso
                $chaves = explode(',', $chavePrimaria);

                // Busca os registros selecionados
                $registros = $this->_CeoTbAnoExercicio->retornaVariosRegistros($chaves);

                if ($registros) {
                    $this->view->codigo = $negocio->chavePrimaria();
                    $this->view->dados = $registros;
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            // Busca a confirmação da exclusão
            $excluir = $this->getRequest()->getPost('cmdExcluir');

            if ($excluir == 'Sim') {
                $chavePrimaria = $this->_getParam('cod');

                // Verifica se existe despesa no ano a excluir
                $desp = new Application_Model_DbTable_Orcamento_CeoTbDespDespesa ();
                $despesa = $desp->fetchRow("DESP_AA_DESPESA = " . $chavePrimaria . 'AND DESP_CD_MATRICULA_EXCLUSAO IS NULL AND DESP_DH_EXCLUSAO_LOGICA IS NULL');

                if ($despesa) {
                    // TODO: A mensagem abaixo deveria ser a Orcamento_Business_Dados::MSG_EXCLUIR_ERRO
                    $this->erroOperacao("Você não pode excluir o ano exercicio. O ano exercicio " . $chavePrimaria . " contem despesas.");
                    $this->voltarIndexAction();
                }

                if ($chavePrimaria) {
                    try {
                        // Exclui a fase
                        $negocioFase->exclusaoFisica($chavePrimaria);
                        // Exclui o ano exercicio
                        $negocio->exclusaoFisica($chavePrimaria);
                        // inclui na tabela de log do orçamento
                        $this->_logdados->incluirLog();
                        // Recria os cache referentes a esta controlles
                        $this->recriarCaches($chavePrimaria);
                        // Carrega as mensagens
                        $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_SUCESSO_EXERCICIO, 'status' => 'success'));
                    } catch (Exception $e) {
                        $this->erroOperacao(Orcamento_Business_Dados::MSG_EXCLUIR_ERRO . '<br />' . $e->getMessage());
                    }
                }
            } else {
                $this->_helper->flashMessenger(array(message => Orcamento_Business_Dados::MSG_EXCLUIR_CANCELAR, 'status' => 'notice'));
            }

            $this->voltarIndexAction();
        }
    }

    /**
     * Gera um novo cache
     * 
     * @name recriarCaches
     */
    private function recriarCaches ($exercicio = 0) {
        $cache = new Trf1_Orcamento_Cache ();
        $cache->excluirCachesSensiveis($this->_controle, $exercicio);
    }

    private function registroNaoEncontrado () {
        $erro = Orcamento_Business_Dados::MSG_REGISTRO_NAO_ENCONTRADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    private function codigoNaoInformado () {
        $erro = Orcamento_Business_Dados::MSG_CODIGO_NAO_INFORMADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    private function erroOperacao ($mensagemErro) {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::ERR);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'error'));
    }

    /**
     * Busca a fase do exercicio pelo ano
     * 
     * @param int $ano Ano exercicio
     * @name buscaFase
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function buscaFase ($ano) {
        // Retorna a fase caso exista
        return $this->_classeNegocioFase->fetchRow("FANE_NR_ANO = " . $ano);
    }

    /**
     * Copia as despesas para o ano exercicio atual
     * 
     * @param int $ano Ano exercicio
     * @name copiardespesasAction
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function copiardespesasAction () {
        // exercicio a ser copiado
        $ano = $this->_getParam('cod');

        // sem ano
        if (!$ano) {
            $this->erroOperacao(Orcamento_Business_Dados::MSG_COPIARDESPESA_ERRO . ' Ano Exercicio não informado!');
            $this->voltarIndexAction();
        }

        // Verifica o status do exercicio e altera o valor base do exercicio anterior dinamico - RN150
        $permitidos = array(
            Trf1_Orcamento_Definicoes::FASE_EXERCICIO_EM_EXECUCAO, /* 5 */
            Trf1_Orcamento_Definicoes::FASE_EXERCICIO_ENCERRADO, /* 6 */
            Trf1_Orcamento_Definicoes::FASE_EXERCICIO_EM_APROVACAO /* 7 */
        );

        // instancia de despesa
        $facade = new Orcamento_Facade_Despesa();

        // Retorna fase do exericio
        $modelfase = new Orcamento_Business_Negocio_FaseAnoExercicio();
        $fase = $modelfase->retornaFasePorExercicio($ano);

        $existe = in_array($fase, $permitidos);
        // Se não esta nos permitidos altera o campo valor - RN150
        if (!$existe) {
            // copia com ajuste
            $resultado = $facade->copiaDespesas($ano, $fase);
        } else {
            // copia normalmente
            $resultado = $facade->copiaDespesas($ano);
        }



        // Define mensagem de sucesso
        $anoAnterior = $ano - 1;

        $msgSucesso = "";
        $msgSucesso .= "<br />";
        $msgSucesso .= "Foi(ram) copiada(s) ";
        $msgSucesso .= $resultado ['qtdeRegistrosAfetados'][0];
        $msgSucesso .= " despesa(s) do exercício de ";
        $msgSucesso .= $anoAnterior;
        $msgSucesso .= " para ";
        $msgSucesso .= $ano;
        $msgSucesso .= ".";

        // se não copiar
        if (!$resultado ['sucesso']) {
//            exiber o motivo caso n efetue a copia da despesa
//            $this->erroOperacao ( Orcamento_Business_Dados::MSG_COPIARDESPESA_ERRO . $resultado ['msgErro' ] );
            $this->erroOperacao(Orcamento_Business_Dados::MSG_COPIARDESPESA_ERRO);
        } else {
            $this->_helper->flashMessenger(array(message => Orcamento_Business_Dados::MSG_COPIARDESPESA_SUCESSO . $msgSucesso, 'status' => 'success'));
        }

        // redireciona
        $this->voltarIndexAction();
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    private function voltarIndexAction () {
        // Grava em sessão as preferências do usuário para essa grid
        $sessao = new Orcamento_Business_Sessao ();
        $url = $sessao->retornaOrdemFiltro($this->_controle);

        if ($url) {
            // Redireciona para a url salva em sessão
            $this->_redirect($url);
        } else {
            // Redireciona para a url combinada entre modulo/controle/index
            $this->_redirect($this->_modulo . '/' . $this->_controle);
        }
    }

}
