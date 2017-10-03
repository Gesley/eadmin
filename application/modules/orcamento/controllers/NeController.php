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
 * Disponibiliza as funcionalidades ao usuário sobre nota de empenho.
 *
 * @category Orcamento
 * @package Orcamento_NeController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_NeController extends Zend_Controller_Action {

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

    public function init () {
        // Título apresentado no Browser
        $this->view->title = 'Nota de empenho';

        // Ajuda & Informações
        $this->view->msgAjuda = AJUDA_AJUDA;
        $this->view->msgInfo = AJUDA_INFOR;

        // Timer para mensuração do tempo de carregamento da página
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        // Informações sobre a requisição
        $requisicao = $this->getRequest();
        $this->_modulo = strtolower($requisicao->getModuleName());
        $this->_controle = strtolower($requisicao->getControllerName());

        // Define o nome da classe negocial padrão
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst($this->_controle);

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);

        // Grava nova tabela de log (NOVO)
        $this->_logdados = new Orcamento_Business_Negocio_Logdados();
        
        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $log->gravaLog($requisicao);
    }

    public function postDispatch () {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function indexAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de notas de empenho';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        $memoria = $mem->retornaMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();

        $dados = $negocio->retornaListagem();

        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar', 'excluir');
        $camposDetalhes = array(
            'NOEM_ANO' => array('title' => 'Ano', 'abbr' => ''),
            'NOEM_CD_UG_FAVORECIDO' => array('title' => 'UG', 'abbr' => ''),
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de empenho', 'abbr' => ''),
            'NOEM_CD_NE_REFERENCIA' => array('title' => 'Referência', 'abbr' => ''),
            'NOEM_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'NOEM_CD_PT_RESUMIDO' => array('title' => 'PTRES Ne', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'SG_FAMILIA_RESPONSAVEL' => array('title' => 'Responsavel', 'abbr' => ''),
            'NOEM_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'NOEM_DH_NE' => array('title' => 'Emissão', 'abbr' => ''),
            'NOEM_DS_OBSERVACAO' => array('title' => 'Descrição', 'abbr' => ''),
            'NOEM_NR_PROCESSO' => array('title' => 'Processo', 'abbr' => ''),
            'NOEM_CD_EVENTO' => array('title' => 'Evento', 'abbr' => ''),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'NOEM_IC_ACERTADO_MANUALMENTE' => array('title' => 'Acertado manualmente', 'abbr' => '')
        );
        $camposOcultos = array('EXERCICIO', 'PTRS_SG_PT_RESUMIDO', 'DS_RESPONSAVEL', 'SG_FAMILIA_RESPONSAVEL', 'SG_DS_FAMILIA_RESPONSAVEL', 'LOTA_SIGLA_SECAO', 'LOTA_COD_LOTACAO');

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

    public function inconsistenciaAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem das inconsistências nas notas de empenho';

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemInconsistencia();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar');
        $camposDetalhes = array('NOEM_INCONSISTENCIA' => array('title' => 'Inconsistência', 'abbr' => ''),
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de empenho', 'abbr' => '', 'format' => 'Notas'),
            'NOEM_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'NOEM_ANO' => array('title' => 'Ano (NE)', 'abbr' => ''),
            'DESP_AA_DESPESA' => array('title' => 'Ano (Desp)', 'abbr' => ''),
            'NOEM_CD_UG_FAVORECIDO' => array('title' => 'UG (NE)', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG (Desp)', 'abbr' => ''),
            'NOEM_CD_FONTE' => array('title' => 'Fonte (NE)', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte (Desp)', 'abbr' => ''),
            'NOEM_CD_PT_RESUMIDO' => array('title' => 'PTRES (NE)', 'abbr' => ''),
            'UNOR_NOEM' => array('title' => 'UO (NE)', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES (Desp)', 'abbr' => ''),
            'UNOR_DESP' => array('title' => 'UO (Desp)', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO (Desp)', 'abbr' => ''),
            'NOEM_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza (NE)', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza (Desp)', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'NOEM_CD_NE_REFERENCIA' => array('title' => 'Referência', 'abbr' => ''),
            'NOEM_DH_NE' => array('title' => 'Emissão', 'abbr' => ''),
            'NOEM_DS_OBSERVACAO' => array('title' => 'Descrição', 'abbr' => ''),
            'NOEM_PROCESSO' => array('title' => 'Processo', 'abbr' => ''),
            'NOEM_CD_EVENTO' => array('title' => 'Evento', 'abbr' => ''),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'NOEM_IC_ACERTADO_MANUALMENTE' => array('title' => 'Acertado manualmente', 'abbr' => '')
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

    public function detalheAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar nota de empenho';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            // Busca registro específico
            $negocio = new $this->_classeNegocio ();
            $registro = $negocio->retornaRegistroNomeAmigavel($chavePrimaria);

            $negocioExecucao = new Trf1_Orcamento_Negocio_Ne ();
            $execucao = $negocioExecucao->retornaExecucaoEmpenho($chavePrimaria);

            if ($registro) {
                // Exibe os dados do registro
                $this->view->dados = $registro;
                $this->view->execucao = $execucao;
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    public function editarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar nota de empenho';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio ();
        $camposChave = $negocio->chavePrimaria();

        // Instancia o formulário negocial
        $formulario = new $this->_formulario ();
        $this->view->formulario = $formulario;

        // Busca dados na sessão
        $sessao = new Orcamento_Business_Sessao ();
        $dadosPerfil = $sessao->retornaPerfil();
        $perfil = $dadosPerfil['perfil'];

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            // Busca dados para o preenchimento do formulário
            $chavePrimaria = $this->_getParam('cod');
            if ($chavePrimaria) {
                // Busca registro específico
                $registro = $negocio->retornaRegistro($chavePrimaria);

                if ($registro) {
                    // Bloqueia a edição da chave primária
                    foreach ($camposChave as $chave) {
                        $formulario->$chave->setAttrib('readonly', true);
                    }

                    // Bloqueia campo específico caso perfil não for:
                    // DIPOR ou DESENVOLVEDOR
                    if ($perfil != Orcamento_Business_Dados::PERMISSAO_DIPOR && $perfil != Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR) {
                        $formulario->NOEM_IC_ACERTADO_MANUALMENTE->setAttrib('disabled', true);
                    }

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
                $chavePrimaria = $this->_getParam('cod');

                // Instancia a model para edição do registro
                $tabela = $negocio->tabela();

                // Busca registro pela chave primária
                $registro = $tabela->find($chavePrimaria)->current();

                // Não permite alteração na chave primária
                foreach ($camposChave as $chave) {
                    unset($dados [$chave]);
                }

                $camposNecessarios = array('NOEM_NR_DESPESA');

                // Não permite atualização de campo específico caso perfil não for:
                // DIPOR ou DESENVOLVEDOR
                if ($perfil == Orcamento_Business_Dados::PERMISSAO_DIPOR || $perfil == Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR) {
                    $camposNecessarios = array_merge($camposNecessarios, array('NOEM_IC_ACERTADO_MANUALMENTE'), array('NOEM_NR_PROCESSO'));
                }

                $todoscampos = array_keys($dados);

                foreach ($todoscampos as $campo) {
                    if (!in_array($campo, $camposNecessarios)) {
                        unset($dados[$campo]);
                    }
                }

                $registro->setFromArray($dados);

                try {
                    // Grava as alterações no banco
                    $codigo = $registro->save();

                    // inclui na tabela de log do orçamento
                    $this->_logdados->incluirLog( $codigo );
                    
                    // Recria os cache referentes a esta controlles
                    $this->recriarCaches();

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
                } catch (Exception $e) {
                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta o formulário para correção dos dados informados
                $formulario->populate($dados);
            }
        }
    }

    private function recriarCaches () {
        $cache = new Trf1_Orcamento_Cache ();
        $cache->excluirCachesSensiveis($this->_controle);
    }

    private function registroNaoEncontrado () {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest();
        $log->gravaLog($requisicao, $erro, zend_log::NOTICE);

        $this->_helper->flashMessenger(array(message => $erro, 'status' => 'notice'));
        $this->voltarIndexAction();
    }

    private function codigoNaoInformado () {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;

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

    public function importarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Importar Notas de Empenho';
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function voltarIndexAction () {
        // Retorna a sessão das preferências do usuário para essa grid
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

    /**
     * Método responsável por excluir notas de empenho
     *
     * @name excluirAction
     * @author Victor Eduardo Barreto
     * @date Jun 22, 2015
     * @version 1.0
     */
    public function excluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Notas de Empenho';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio ();

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            $chavePrimaria = $this->_getParam('cod');

            if ($chavePrimaria) {

                // Transforma o parâmetro informado para array de $chaves, se for o caso
                $chaves = explode(',', $chavePrimaria);

                // Busca os registros selecionados
                foreach ($chaves as $chave => $valor) {

                    $registros[] = $negocio->retornaRegistroNomeAmigavel($valor);
                }

                // se houver registros apresenta na view.
                if ($registros) {

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

                if ($chavePrimaria) {

                    // Transforma o parâmetro informado para array de $chaves, se for o caso
                    $chaves = explode(',', $chavePrimaria);

                    try {

                        // Exclui o registro

                        /* Foi feita solicitaco de exclusao fisica  2014010001108011080160000094  */

                        // exclui a execucao                        
                        $negocio->exclusaoExecucao($chaves);

                        // exclui a ne
                        $negocio->exclusaoFisica($chaves);

                        // inclui na tabela de log do orçamento
                        $this->_logdados->incluirLog();
                        
                        // Recria os cache referentes a esta controlles
                        $this->recriarCaches($chavePrimaria);

                        $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO, 'status' => 'success'));
                    } catch (Exception $e) {

                        $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage());
                    }
                }
            } else {

                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_CANCELAR, 'status' => 'notice'));
            }

            $this->voltarIndexAction();
        }
    }

}
