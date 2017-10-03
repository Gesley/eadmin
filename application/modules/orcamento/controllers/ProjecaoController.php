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
 * Disponibiliza as funcionalidades ao usuário sobre projeção orçamentária.
 *
 * @category Orcamento
 * @package Orcamento_ProjecaoController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ProjecaoController extends Zend_Controller_Action {

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
        $this->view->title = 'Projeção orçamentária';

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
        $this->_negocioTrava = new Trf1_Orcamento_Negocio_Travaprojecao ();

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);

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
        $this->view->telaTitle = 'Consulta a projeção da despesa';

        $formulario = new Orcamento_Form_Despesapergunta ();

        // botão extra para editar uma dada projeção
        $cmdEditar = new Zend_Form_Element_Button('Editar');
        $cmdEditar->setLabel('Editar')->setAttrib('type', 'submit')->setAttrib('class', 'ceo_editar');
        $formulario->addElement($cmdEditar);

        $this->view->formulario = $formulario;

        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->getRequest();
        $sessao = new Orcamento_Business_Sessao ();
        $sessao->defineOrdemFiltro($requisicao);

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if ($formulario->isValid($dados)) {
                $novaUrl = 'orcamento/projecao/';

                if ($dados ['cmdProjecaoAcao'] == 'Editar') {
                    $novaUrl .= 'editar';
                } else {
                    $novaUrl .= 'detalhe';
                }

                $novaUrl .= '/cod/' . $dados ['DESP_NR_DESPESA'];

                $this->_redirect($novaUrl);
            }
        }
    }

    public function listagemAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem das projeções orçamentárias';

        // Dados do grid
        $negocio = new Trf1_Orcamento_Negocio_Saldo ();
        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar');

        $camposDetalhes = array('DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'SG_FAMILIA_RESPONSAVEL' => array('title' => 'Responsavel', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'TIDE_DS_TIPO_DESPESA' => array('title' => 'Caráter', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'VR_PROPOSTA_RECEBIDA' => array('title' => 'Proposta recebida', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_ADICIONAL' => array('title' => 'Crédito adicional', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_CONTINGENCIA' => array('title' => 'Contigência', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_EXTRA' => array('title' => 'Crédito extra', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_ALTERACAO_QDD' => array('title' => 'Alteração de QDD', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_SAIDA' => array('title' => 'Saída de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_DESTAQUE' => array('title' => 'Destaque de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_MOVIMENTACAO' => array('title' => 'Movimentação de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_A_RECEBER' => array('title' => 'Proposta a receber', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_A_RECEBER' => array('title' => 'Crédito a receber', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_SUB_TOTAL' => array('title' => 'Dotação final', 'abbr' => '', 'format' => 'Numerocor'),
            // Remover campos VR_MES_ATUAL e VR_EXEC_PASSADA
            //'VR_PROJ_FUTURA' => array ('title' => 'Projeção futura', 'abbr' => '', 'format' => 'Numerocor' ),
            // 'VR_MES_ATUAL' => array ('title' => 'Mês atual', 'abbr' => '', 'format' => 'Numerocor' ),
            // 'VR_EXEC_PASSADA' => array ('title' => 'Execução anterior', 'abbr' => '', 'format' => 'Numerocor' ),
            'VR_PROJECAO' => array('title' => 'Projeção', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PERCENTUAL_REAJUSTE' => array('title' => 'Percentual reajuste', 'abbr' => '', 'format' => 'Percentual'),
            'VR_SITUACAO_ORCAMENTARIA' => array('title' => 'Situação orçamentária', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JANEIRO' => array('title' => 'Valor Executado Janeiro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_FEVEREIRO' => array('title' => 'Valor Executado Fevereiro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_MARCO' => array('title' => 'Valor Executado Março', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_ABRIL' => array('title' => 'Valor Executado Abril', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_MAIO' => array('title' => 'Valor Executado Maio', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JUNHO' => array('title' => 'Valor Executado Junho', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JULHO' => array('title' => 'Valor Executado Julho', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_AGOSTO' => array('title' => 'Valor Executado Agosto', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_SETEMBRO' => array('title' => 'Valor Executado Setembro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_OUTUBRO' => array('title' => 'Valor Executado Outubro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_NOVEMBRO' => array('title' => 'Valor Executado Novembro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_DEZEMBRO' => array('title' => 'Valor Executado Dezembro', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_TOTAL_EXECUTADO' => array('title' => 'Valor Total Executado', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_SALDO_MENOS_DOTACAO' => array('title' => 'Valor do Saldo Menos Dotação', 'abbr' => '', 'format' => 'Numerocor'),
        );
        $camposOcultos = array('VR_PROPOSTA_SECOR', 'VR_PROPOSTA_REMANEJADA', 'VR_PROPOSTA_APROVADA', 'VR_TOTAL_DESPESA', 'VR_RDO', 'VR_A_AUTORIZAR', 'VR_EMPENHADO', 'VR_A_EMPENHAR', 'VR_EXECUTADO', 'VR_A_EXECUTAR', 'TIDE_IC_RESERVA_RECURSO', 'DESP_NR_COPIA_DESPESA', 'VR_BASE_EXERCICIO', 'DESP_CD_CATEGORIA', 'DESP_CD_VINCULACAO', 'TREC_DS_TIPO_RECURSO', 'PORC_DS_TIPO_ORCAMENTO', 'POBJ_DS_OBJETIVO', 'PPRG_DS_PROGRAMA', 'POPE_DS_TIPO_OPERACIONAL', 'EXERCICIO');

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
        $this->view->telaTitle = 'Visualizar projeção';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        if ($chavePrimaria) {
            // Busca registro específico
            $negocio = new $this->_classeNegocio ();
            $negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
            $negocioNe = new Trf1_Orcamento_Negocio_Ne ();
            $negocioSaldo = new Trf1_Orcamento_Negocio_Saldo ();
            $ncMoldel = new Trf1_Orcamento_Negocio_Nc();

            $sql = $ncMoldel->retornaSqlListagemInconsistencia( null, false, $despesa);
            $banco = Zend_Db_Table::getDefaultAdapter();
            $inconsistencia = $banco->fetchAll($sql);
            if(is_array($inconsistencia)){
                $this->view->inconsistencia = true;
            }

            $registro = $negocioDespesa->retornaDespesa($chavePrimaria);

            if ($registro) {
                $projecao = $negocio->retornaProjecao($chavePrimaria);

                $this->view->despesa = $registro;
                $this->view->projecao = $projecao;
                $this->view->execucao = $negocioNe->retornaExecucao($chavePrimaria);
                $this->view->saldo = $negocioSaldo->retornaSaldo($chavePrimaria);
                $this->view->justificativas = $negocio->retornaJustificativas($chavePrimaria);
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    public function editarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar projeção';

        $formulario = new $this->_formulario ();

        $negocio = new $this->_classeNegocio ();
        $camposChave = $negocio->chavePrimaria();

        // Busca a despesa no parâmetro
        $despesa = Zend_Filter::filterStatic($this->_getParam('cod'), 'int');

        // Retorna período de travamento da projeção, se houver
        $travamento = $this->_negocioTrava->retornaPeriodoTravamentoProjecao($despesa);
        
        $dataTravaIni = $travamento ['DT_INI'];
        $dataTravaFim = $travamento ['DT_FIM'];
        $bMostraTrava = ($dataTravaIni ? true : false);

        $bperiodoTrava = $this->periodoTravamento( $travamento );                        
        
        // $mesAtual = date ( 'n' );
        $mesAtual = 1;
        $iMes = 0;

        // Busca permissões para edição da projeção
        $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
        $perfil = $sessaoOrcamento->perfil;

        /*         * ***********************************************************
         * Perfis para teste
         * ************************************************************
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR;
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR;
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_SECCIONAL;
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_SECRETARIA;
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_DIEFI;
          $perfil = Trf1_Orcamento_Definicoes::PERMISSAO_CONSULTA;
         * *********************************************************** */

        // $bLiberaPerfil => Liberação dos campos de projeção garantida, independente do mês
        $bLiberaPerfil = ($perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIPOR || $perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DESENVOLVEDOR);
        if ($bLiberaPerfil) {
            $bMostraTrava = false;
            $bperiodoTrava= false;
        }

        // $bTravaPerfil => Travamento obrigatória dos campos de projeçao, independente do mês
        $bTravaPerfil = ($perfil == Trf1_Orcamento_Definicoes::PERMISSAO_CONSULTA || $perfil == Trf1_Orcamento_Definicoes::PERMISSAO_DIEFI);

        // Varre os campos de projeção para liberação ou não de edição conforme mês atual e nível de permissão
        foreach ($formulario->getElements() as $controle) {
            if ((substr($controle->getName(), 0, 8) == 'PROJ_VR_') && ($controle->getName() != 'PROJ_VR_TOTAL')) {
                // $bTravaMes => Travamento do mês anterior ao mês atual
                $bTravaMes = ($mesAtual - 1 > $iMes ++);
                
                // Valida se deve travar
                $bTrava = (!$bLiberaPerfil && $bperiodoTrava && ($bTravaPerfil || $bTravaMes || $bMostraTrava));

                if ($bTrava OR $bperiodoTrava) {
                    $controle->setAttrib('readonly', true);
                }
                
            }
        }

        // Exibir mensagem de bloqueio, se houver
        $this->view->bTravaTudo = $bMostraTrava;
        $this->view->dtTravaIni = $dataTravaIni;
        $this->view->dtTravaFim = $dataTravaFim;

        $formulario->PROJ_VR_TOTAL->setAttrib('readonly', true);

        $this->view->formulario = $formulario;

        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if ($formulario->isValid($dados)) {
                $despesa = Zend_Filter::filterStatic($this->_getParam('cod'), 'int');
                $dados = $formulario->getValues();
                $dados ['PROJ_NR_DESPESA'] = $despesa;

                try {
                    $negocio->atualizaProjecao($dados);

                    // Recria os cache referentes a esta controlles
                    $this->recriarCaches();

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
                } catch (Exception $e) {
                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage(), 'status' => 'error'));
                }
                $this->voltarIndexAction();
            } else {
                $formulario->populate($dados);
            }
        } else {
            $despesa = Zend_Filter::filterStatic($this->_getParam('cod'), 'int');

            if ($despesa) {
                $negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
                $negocioNe = new Trf1_Orcamento_Negocio_Ne ();
                $negocioSaldo = new Trf1_Orcamento_Negocio_Saldo ();

                $registroDespesa = $negocioDespesa->retornaDespesa($despesa);

                if ($registroDespesa) {
                    $projecao = $negocio->retornaProjecao($despesa);
                    $execucao = $negocioNe->retornaExecucao($despesa);
                    $registroSaldo = $negocioSaldo->retornaSaldo($despesa);

                    $this->view->despesa = $registroDespesa;
                    $this->view->execucao = $execucao;
                    $this->view->saldo = $registroSaldo;
                    $this->view->justificativas = $negocio->retornaJustificativas($despesa);

                    $formulario->populate($projecao);

                    $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
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

    public function execucaomensalAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem das projeções orçamentárias';

        // Dados do grid
        $negocio = new Trf1_Orcamento_Negocio_Saldo ();


        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'SG_FAMILIA_RESPONSAVEL' => array('title' => 'Responsavel', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'TIDE_DS_TIPO_DESPESA' => array('title' => 'Caráter', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'VR_PROPOSTA_RECEBIDA' => array('title' => 'Proposta recebida', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_ADICIONAL' => array('title' => 'Crédito adicional', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_CONTINGENCIA' => array('title' => 'Contigência', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_EXTRA' => array('title' => 'Crédito extra', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_ALTERACAO_QDD' => array('title' => 'Alteração de QDD', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_SAIDA' => array('title' => 'Saída de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_CREDITO_DESTAQUE' => array('title' => 'Destaque de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_MOVIMENTACAO' => array('title' => 'Movimentação de crédito', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_A_RECEBER' => array('title' => 'Proposta a receber', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_A_RECEBER' => array('title' => 'Crédito a receber', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_SUB_TOTAL' => array('title' => 'Dotação final', 'abbr' => '', 'format' => 'Numerocor'),
            // Remover campos VR_MES_ATUAL e VR_EXEC_PASSADA
            //'VR_PROJ_FUTURA' => array ('title' => 'Projeção futura', 'abbr' => '', 'format' => 'Numerocor' ),
            // 'VR_MES_ATUAL' => array ('title' => 'Mês atual', 'abbr' => '', 'format' => 'Numerocor' ),
            // 'VR_EXEC_PASSADA' => array ('title' => 'Execução anterior', 'abbr' => '', 'format' => 'Numerocor' ),
            'VR_PROJECAO' => array('title' => 'Projeção', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PERCENTUAL_REAJUSTE' => array('title' => 'Percentual reajuste', 'abbr' => '', 'format' => 'Percentual'),
            'VR_SITUACAO_ORCAMENTARIA' => array('title' => 'Situação orçamentária', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JANEIRO' => array('title' => 'Janeiro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_FEVEREIRO' => array('title' => 'Fevereiro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_MARCO' => array('title' => 'Março', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_ABRIL' => array('title' => 'Abril', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_MAIO' => array('title' => 'Maio', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JUNHO' => array('title' => 'Junho', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_JULHO' => array('title' => 'Julho', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_AGOSTO' => array('title' => 'Agosto', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_SETEMBRO' => array('title' => 'Setembro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_OUTUBRO' => array('title' => 'Outubro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_NOVEMBRO' => array('title' => 'Novembro', 'abbr' => '', 'format' => 'Numerocor'),
            'EXEC_VR_DEZEMBRO' => array('title' => 'Dezembro', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_TOTAL_EXECUTADO' => array('title' => 'Total', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_SALDO_MENOS_DOTACAO' => array('title' => 'Situação', 'abbr' => '', 'format' => 'Numerocor')
        );
        $camposOcultos = array(
            'EXERCICIO',
            'TIDE_IC_RESERVA_RECURSO',
            'DESP_NR_COPIA_DESPESA',
            'NOEM CD NOTA DE EMPENHO',
            'VR_PROPOSTA_SECOR',
            'VR_PROPOSTA_REMANEJADA',
            'VR_PROPOSTA_APROVADA',
            'VR_TOTAL_DESPESA',
            'VR_RDO',
            'VR_A_AUTORIZAR',
            'VR_EMPENHADO',
            'VR_A_EMPENHAR',
            'VR_A_EXECUTAR',
            'VR_PROJECAO',
            'VR_PERCENTUAL_REAJUSTE',
            'VR_SITUACAO_ORCAMENTARIA',
            'VR_BASE_EXERCICIO',
            'DESP_CD_CATEGORIA',
            'DESP_CD_VINCULACAO',
            'TREC_DS_TIPO_RECURSO',
            'PORC_DS_TIPO_ORCAMENTO',
            'POBJ_DS_OBJETIVO',
            'PPRG_DS_PROGRAMA',
            'POPE_DS_TIPO_OPERACIONAL',
            'VR_EXECUTADO'
        );

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

    public function periodoTravamento($travamento){
        
        $dtini = explode('/', $travamento['DT_INI']);
        $dtend = explode('/', $travamento['DT_FIM']);
        $today = strtotime(date('Y-m-d'));
        
        $dataini = strtotime($dtini[2]."-".$dtini[1]."-".$dtini[0]);
        $dataend = strtotime($dtend[2]."-".$dtend[1]."-".$dtend[0]);
               
        if($today >= $dataini && $today <= $dataend ) {
            return true;
        }
         
        return false;
    }
    
}
