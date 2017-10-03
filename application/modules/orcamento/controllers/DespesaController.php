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
 * Disponibiliza as funcionalidades ao usuário sobre despesa.
 *
 * @category Orcamento
 * @package Orcamento_DespesaController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_DespesaController extends Zend_Controller_Action {

    /**
     * Timer para mensuração do tempo de carregamento da página
     *
     * @var int $_temporizador
     *
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
        $this->view->title = 'Despesa';

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
        $this->_fase = new Orcamento_Business_Negocio_FaseAnoExercicio();

        // Define o nome da classe negocial padrão
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' . ucfirst($this->_controle);

        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_' . ucfirst($this->_controle);

         // Grava nova tabela de log 
        $this->_logdados = new Orcamento_Business_Negocio_Logdados();    
        
        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $log->gravaLog($requisicao);

        $context = $this->_helper->getHelper('AjaxContext');
        $context->addActionContext('despesaexercicio', 'json')
            ->initContext();

        Zend_Controller_Action_HelperBroker::addHelper(new Zend_Layout_Controller_Action_Helper_Layout);
    }

    public function postDispatch () {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function indexAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de Despesas - Resumo';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemSimplificada();

        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_NR_COPIA_DESPESA' => array('title' => 'Despesa exercicio anterior', 'abbr' => ''),
            'DS_DESPESA' => array('title' => 'Descrição', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'SG_FAMILIA_RESPONSAVEL' => array('title' => 'Responsável', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'TIDE_DS_TIPO_DESPESA' => array('title' => 'Caráter da despesa', 'abbr' => ''),
            'VL_DESPESA_SECOR' => array('title' => 'Valor aprovado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'DESP_IC_CONFERIDO' => array('title' => 'Conferido', 'abbr' => ''),
            'DESP_IC_CONFERIDO' => array('title' => 'Conferido', 'abbr' => ''),
            'DESP_IC_FINALIZADO' => array('title' => 'Finalizado', 'abbr' => '')
        );

        $camposOcultos = array(
            'SOLA_VL_ATENDIDO',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_RESP',
            'DESP_CD_PERS_PERSPECTIVA',
            'DESP_CD_MACRO_MACRODESAFIO',
            'PERS_TX_PERSPECTIVA',
            'MACRO_TX_MACRODESAFIO',
            'VL_REAJUSTE_APLICADO_LIMITE',
            'VL_PERCENT_REAJUSTE_PROPOSTA',
            'VL_REAJUSTE_PROPOSTA',
            'VL_PERCENT_APLICADO_LIMITE',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'DESP_IC_REFLEXO_EXERCICIO',
            'DESP_CD_PROGRAMA',
            'DESP_CD_OBJETIVO',
            'DESP_CD_TIPO_ORCAMENTO',
            'DESP_VL_MAX_MENSAL_AUTORIZADO',
            'DESP_IC_REFLEXO_EXERCICIO',
            'EXERCICIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'PTRS_CD_PT_RESUMIDO'
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

    public function orcamAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas - Identificação orçamentária';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemOrcamento();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'ESFE_DS_ESFERA' => array('title' => 'Esfera', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'TIDE_DS_TIPO_DESPESA' => array('title' => 'Caráter da despesa', 'abbr' => '')
        );

        $camposOcultos = array(
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'DESP_CD_PERS_PERSPECTIVA',
            'PERS_TX_PERSPECTIVA',
            'MACRO_TX_MACRODESAFIO',
            'DESP_CD_MACRO_MACRODESAFIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'VL_PERCENT_REAJUSTE_PROPOSTA',
            'VL_REAJUSTE_PROPOSTA',
            'VL_PERCENT_APLICADO_LIMITE',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'VL_REAJUSTE_APLICADO_LIMITE',
            'DESP_IC_CONFERIDO',
            'DESP_IC_FINALIZADO',
            'DESP_IC_REFLEXO_EXERCICIO',
            'DESP_CD_PROGRAMA',
            'DESP_CD_OBJETIVO',
            'DESP_CD_TIPO_ORCAMENTO',
            'DESP_VL_MAX_MENSAL_AUTORIZADO',
            'PTRS_CD_PT_RESUMIDO',
            'SOLA_VL_ATENDIDO',
            'VL_SOLICITACAO_ACRESCIMO_RESP',
            'EXERCICIO'
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

    public function finanAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas - Programação financeira';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemFinanceiro();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
        $camposDetalhes = array('DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''), 'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''), 'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''), 'DESP_CD_VINCULACAO' => array('title' => 'Vinculação', 'abbr' => ''), 'DESP_CD_CATEGORIA' => array('title' => 'Categoria', 'abbr' => ''), 'TREC_DS_TIPO_RECURSO' => array('title' => 'Tipo recurso', 'abbr' => ''));
        $camposOcultos = array(
            'UNOR_CD_UNID_ORCAMENTARIA',
            'PTRS_CD_PT_RESUMIDO',
            'EXERCICIO',
            'VL_SOLICITACAO_ACRESCIMO_RESP',
            'SOLA_VL_ATENDIDO',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'DESP_CD_PERS_PERSPECTIVA',
            'PERS_TX_PERSPECTIVA',
            'MACRO_TX_MACRODESAFIO',
            'DESP_CD_MACRO_MACRODESAFIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'VL_PERCENT_REAJUSTE_PROPOSTA',
            'VL_REAJUSTE_PROPOSTA',
            'VL_PERCENT_APLICADO_LIMITE',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'VL_REAJUSTE_APLICADO_LIMITE',
            'DESP_IC_CONFERIDO',
            'DESP_IC_FINALIZADO',
            'PTRS_SG_PT_RESUMIDO',
            'DESP_IC_REFLEXO_EXERCICIO', 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO');

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

    public function planjAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas - Planejamento estratégico';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemPlanejamento();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PORC_DS_TIPO_ORCAMENTO' => array('title' => 'Tipo de orçamento', 'abbr' => ''),
            'POBJ_DS_OBJETIVO' => array('title' => 'Objetivo estratégico', 'abbr' => ''),
            'PPRG_DS_PROGRAMA' => array('title' => 'Programa', 'abbr' => ''),
            'POPE_DS_TIPO_OPERACIONAL' => array('title' => 'Tipo operacional', 'abbr' => ''));
        $camposOcultos = array(
            'VL_SOLICITACAO_ACRESCIMO_RESP',
            'SOLA_VL_ATENDIDO',
            'EXERCICIO',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'PTRS_SG_PT_RESUMIDO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'DESP_CD_PERS_PERSPECTIVA',
            'PERS_TX_PERSPECTIVA',
            'MACRO_TX_MACRODESAFIO',
            'DESP_CD_MACRO_MACRODESAFIO',
            'VL_PERCENT_REAJUSTE_PROPOSTA',
            'VL_REAJUSTE_PROPOSTA',
            'VL_PERCENT_APLICADO_LIMITE',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'VL_REAJUSTE_APLICADO_LIMITE',
            'DESP_IC_CONFERIDO',
            'DESP_IC_FINALIZADO',
            'PTRS_CD_PT_RESUMIDO',
            'DESP_IC_REFLEXO_EXERCICIO', 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO');

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

    public function contrAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas - Contratos';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemContrato();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DS_DESPESA' => array('title' => 'Descrição', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'CTRD_ID_CONTRATO_DESPESA' => array('title' => 'PTRES', 'abbr' => ''),
            'CTRD_NR_CONTRATO' => array('title' => 'Contrato', 'abbr' => ''),
            'CTRD_NM_EMPRESA_CONTRATADA' => array('title' => 'Empresa', 'abbr' => ''),
            'CTRD_DT_INICIO_VIGENCIA' => array('title' => 'Início vigência', 'abbr' => ''),
            'CTRD_DT_TERMINO_VIGENCIA' => array('title' => 'Término vigência', 'abbr' => '')
        );

        $camposOcultos = array('CTRD_ID_CONTRATO_DESPESA', 'DESP_VL_MAX_MENSAL_AUTORIZADO');

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

    public function valorAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas - Recursos inicias';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemRecursos();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');

        $camposDetalhes ['DESP_AA_DESPESA'] = array('title' => 'Ano', 'abbr' => '');
        $camposDetalhes ['DESP_NR_DESPESA'] = array('title' => 'Despesa', 'abbr' => '');
        $camposDetalhes ['DS_DESPESA'] = array('title' => 'Descrição', 'abbr' => '');
        // $camposDetalhes [ 'aaaaaa' ] = array ( 'title' => 'Dotação autorizada do exercício anterior', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' );
        $camposDetalhes ['VL_DESPESA_BASE_EXERC_ANTERIOR'] = array('title' => 'Base do exercício anterior', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_PERCENTUAL'] = array('title' => 'Reajuste (%)', 'abbr' => '', 'format' => 'Percentual', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_DIFERENCA'] = array('title' => 'Reajuste aplicado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_EXERC_ATUAL'] = array('title' => 'Base exercício atual', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_AJUSTE_DIPLA'] = array('title' => 'Reajuste ano atual', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_RESPONSAVEL'] = array('title' => 'Proposta inicial', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_SOLIC_RESPONSAVEL'] = array('title' => 'Solicitado pelo responsável:', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_DIPLA'] = array('title' => 'Ajuste setorial', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_CONGRESSO'] = array('title' => 'Ajuste ao limite', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_SECOR'] = array('title' => 'Orçamento aprovado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');

        $camposOcultos = array(
            'DESP_CD_PT_RESUMIDO',
            'UNOR_CD_UNID_ORCAMENTARIA',
            'EXERCICIO',
            'VL_SOLICITACAO_ACRESCIMO_RESP',
            'SOLA_VL_ATENDIDO',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'PTRS_CD_PT_RESUMIDO',
            'DESP_CD_PERS_PERSPECTIVA',
            'PERS_TX_PERSPECTIVA',
            'MACRO_TX_MACRODESAFIO',
            'DESP_CD_MACRO_MACRODESAFIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'VL_PERCENT_REAJUSTE_PROPOSTA',
            'VL_REAJUSTE_PROPOSTA',
            'VL_PERCENT_APLICADO_LIMITE',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'VL_REAJUSTE_APLICADO_LIMITE',
            'DESP_IC_CONFERIDO',
            'DESP_IC_FINALIZADO',
            'PTRS_SG_PT_RESUMIDO',
            'DESP_IC_REFLEXO_EXERCICIO',
            'DESP_CD_PROGRAMA',
            'DESP_CD_OBJETIVO',
            'DESP_CD_TIPO_ORCAMENTO',
            'DESP_VL_MAX_MENSAL_AUTORIZADO'
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

    public function empenAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de saldo de empenho por despesa';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->empenhoPorDespesas();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe');
        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'UNGE_SG_SECAO' => array('title' => 'UG', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'PTRS_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'EDSB_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'NOEM_CD_NOTA_EMPENHO' => array('title' => 'Nota de empenho', 'abbr' => '', 'format' => 'Notas'),
            'NOEM_DS_OBSERVACAO' => array('title' => 'Descrição da NE', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Valor', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'NOEM_VL_NE_ACERTADO' => array('title' => 'Saldo da NE', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'));

        $classeGrid = new Trf1_Orcamento_Grid ();
        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        $todoscampos = array_keys($dados [0]);
        $campos = array_keys($camposDetalhes);

        $camposOcultos = array();
        foreach ($todoscampos as $campo) {
            if (!in_array($campo, $campos)) {
                $camposOcultos [] = $campo;
            }
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

    public function distrAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de distribuição orçamentária';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemDistribuicao();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe');

        $camposDetalhes = array(
            'DESP_AA_DESPESA' => array('title' => 'Ano', 'abbr' => ''),
            'DESP_NR_DESPESA' => array('title' => 'Despesa', 'abbr' => ''),
            'DESP_DS_ADICIONAL' => array('title' => 'Descrição', 'abbr' => ''),
            'DESP_CD_UG' => array('title' => 'UG', 'abbr' => ''),
            'SG_FAMILIA_RESPONSAVEL' => array('title' => 'Responsável', 'abbr' => ''),
            'DESP_CD_FONTE' => array('title' => 'Fonte', 'abbr' => ''),
            'DESP_CD_PT_RESUMIDO' => array('title' => 'PTRES', 'abbr' => ''),
            'UNOR_CD_UNID_ORCAMENTARIA' => array('title' => 'UO', 'abbr' => ''),
            'PTRS_SG_PT_RESUMIDO' => array('title' => 'Sigla', 'abbr' => ''),
            'TIDE_DS_TIPO_DESPESA' => array('title' => 'Caráter da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'DESP_CD_ELEMENTO_DESPESA_SUB' => array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa'),
            'VR_PROPOSTA_APROVADA' => array('title' => 'Proposta aprovada', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'VR_PROPOSTA_RECEBIDA' => array('title' => 'Proposta recebida', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'),
            'VR_PROPOSTA_A_RECEBER' => array('title' => 'Proposta a receber', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid'));

        $classeGrid = new Trf1_Orcamento_Grid ();

        $grid = $classeGrid->criaGrid($this->_controle, $dados, $chavePrimaria, $this->view->telaTitle, $acoes);

        // Personalização do grid
        foreach ($camposDetalhes as $campo => $opcoes) {
            $grid->updateColumn($campo, $opcoes);
        }

        //Pega as colunas de todos os campos e todos desejados
        $todoscampos = array_keys($dados [0]);
        $campos = array_keys($camposDetalhes);

        $camposOcultos = array();
        foreach ($todoscampos as $campo) {
            if (!in_array($campo, $campos)) {
                $camposOcultos [] = $campo;
            }
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

    public function complAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de despesas';

        // ASmR - Alteração para compatibilização
        // Retorna instância de classe para manipulação de memória
        $mem = Orcamento_Business_Memoria::retornaInstancia();

        // Expande a quantidade de memória disponível para essa requisição
        $mem->expandeMemoria();

        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemCompleta(); 
        
        // Verifica fase do exercicio e bloqueia a listagem se necessario
        $this->verificaFase();
        
        // Pega o id
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('incluir', 'detalhe', 'editar', 'editarcontrato', 'excluir');

        $camposDetalhes ['DESP_AA_DESPESA'] = array('title' => 'Ano', 'abbr' => '');
        $camposDetalhes ['DESP_NR_DESPESA'] = array('title' => 'Despesa', 'abbr' => '');
        $camposDetalhes ['DESP_NR_COPIA_DESPESA'] = array('title' => 'Despesa exercício anterior', 'abbr' => '');
        $camposDetalhes ['DS_DESPESA'] = array('title' => 'Descrição', 'abbr' => '');
        $camposDetalhes ['DESP_CD_UG'] = array('title' => 'UG', 'abbr' => '');
        $camposDetalhes ['SG_FAMILIA_RESPONSAVEL'] = array('title' => 'Responsável', 'abbr' => '');
        $camposDetalhes ['ESFE_DS_ESFERA'] = array('title' => 'Esfera', 'abbr' => '');
        $camposDetalhes ['DESP_CD_PT_RESUMIDO'] = array('title' => 'PTRES', 'abbr' => '');
        $camposDetalhes ['PTRS_SG_PT_RESUMIDO'] = array('title' => 'Sigla', 'abbr' => '');
        $camposDetalhes ['UNOR_CD_UNID_ORCAMENTARIA'] = array('title' => 'UO', 'abbr' => '');
        $camposDetalhes ['DESP_CD_ELEMENTO_DESPESA_SUB'] = array('title' => 'Natureza da despesa', 'abbr' => '', 'format' => 'Naturezadespesa');
        $camposDetalhes ['TIDE_DS_TIPO_DESPESA'] = array('title' => 'Caráter da despesa', 'abbr' => '');
        $camposDetalhes ['DESP_CD_FONTE'] = array('title' => 'Fonte de recursos', 'abbr' => '');
        $camposDetalhes ['DESP_CD_VINCULACAO'] = array('title' => 'Vinculação', 'abbr' => '');
        $camposDetalhes ['DESP_CD_CATEGORIA'] = array('title' => 'Categoria de recursos', 'abbr' => '');
        $camposDetalhes ['TREC_DS_TIPO_RECURSO'] = array('title' => 'Tipo de recurso', 'abbr' => '');
        $camposDetalhes ['PORC_DS_TIPO_ORCAMENTO'] = array('title' => 'Tipo de orçamento', 'abbr' => '');
        /* sosti 2015010001108011080160000022 */
        $camposDetalhes ['PERS_TX_PERSPECTIVA'] = array('title' => 'Perspectiva', 'abbr' => '');
        $camposDetalhes ['MACRO_TX_MACRODESAFIO'] = array('title' => 'Macrodesafio', 'abbr' => '');

        $camposDetalhes ['POBJ_DS_OBJETIVO'] = array('title' => 'Objetivo estratégico', 'abbr' => '');
        $camposDetalhes ['PPRG_DS_PROGRAMA'] = array('title' => 'Programa estratégico', 'abbr' => '');
        $camposDetalhes ['POPE_DS_TIPO_OPERACIONAL'] = array('title' => 'Tipo operacional', 'abbr' => '');
        // Dados do contrato
        $camposDetalhes ['CTRD_NR_CONTRATO'] = array('title' => 'Número do Contrato', 'abbr' => '');
        $camposDetalhes ['CTRD_NM_EMPRESA_CONTRATADA'] = array('title' => 'Nome da Contratada', 'abbr' => '');
        $camposDetalhes ['CTRD_CPFCNPJ_DESPESA'] = array('title' => 'CPF/CNPJ da contratada', 'abbr' => '');
        $camposDetalhes ['CTRD_DT_INICIO_VIGENCIA'] = array('title' => 'Início  da vigência', 'abbr' => '');
        $camposDetalhes ['CTRD_DT_TERMINO_VIGENCIA'] = array('title' => 'Término da vigência', 'abbr' => '');
        $camposDetalhes ['CTRD_VL_DESPESA'] = array('title' => 'Valor do contrato', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        // $camposDetalhes [ 'aaaaaa' ] = array ( 'title' => 'Dotação autorizada do exercício anterior', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid' );
        $camposDetalhes ['VL_DESPESA_BASE_EXERC_ANTERIOR'] = array('title' => 'Base do exercício anterior estática', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_PERCENTUAL'] = array('title' => 'Composição da base (%)', 'abbr' => '', 'format' => 'Percentual', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_DIFERENCA'] = array('title' => 'Composição da base (R$)', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_BASE_EXERC_ATUAL'] = array('title' => 'Base da pré proposta', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_PERCENT_REAJUSTE_PROPOSTA'] = array('title' => 'Reajuste pré proposta (%)', 'abbr' => '', 'format' => 'Percentual', 'class' => 'valorgrid');
        $camposDetalhes ['VL_REAJUSTE_PROPOSTA'] = array('title' => 'Reajuste pré proposta (R$)', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_RESPONSAVEL'] = array('title' => 'Proposta inicial', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_SOLIC_RESPONSAVEL'] = array('title' => 'Valor de Ajuste Solicitado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['SOLA_VL_ATENDIDO'] = array('title' => 'Valor de Ajuste Atendido', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_SOLICITACAO_ACRESCIMO_SOLI'] = array('title' => 'Valor de acréscimo solicitado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_SOLICITACAO_ACRESCIMO_RESP'] = array('title' => 'Valor de acréscimo atendido', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_DIPLA'] = array('title' => 'Ajuste setorial', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_PERCENT_APLICADO_LIMITE'] = array('title' => 'Reajuste limite (%)', 'abbr' => '', 'format' => 'Percentual', 'class' => 'valorgrid');
        $camposDetalhes ['VL_REAJUSTE_APLICADO_LIMITE'] = array('title' => 'Reajuste limite (R$)', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_CONGRESSO'] = array('title' => 'Ajustada ao limite', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
        $camposDetalhes ['VL_DESPESA_SECOR'] = array('title' => 'Orçamento aprovado', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');

//        $camposDetalhes ['VL_DESPESA_AJUSTE_DIPLA'] = array('title' => 'Reajuste exercício atual', 'abbr' => '', 'format' => 'Numerocor', 'class' => 'valorgrid');
//        $camposDetalhes ['DESP_IC_CONFERIDO'] = array('title' => 'Conferido', 'abbr' => '');
//        $camposDetalhes ['DESP_IC_FINALIZADO'] = array('title' => 'Finalizado', 'abbr' => '');
        
        $camposOcultos = array(
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'VL_SOLICITACAO_ACRESCIMO_SOLI',
            'DESP_IC_CONFERIDO',
            'DESP_IC_FINALIZADO',
            'EXERCICIO',
            'VL_DESPESA_AJUSTE_DIPLA',
            'DESP_CD_PERS_PERSPECTIVA',
            'DESP_CD_MACRO_MACRODESAFIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'VL_REAJUSTE_PROPOSTA_ATUAL',
            'VL_AJUSTE_DO_LIMITE',
            'DESP_CD_PROGRAMA',
            'DESP_CD_OBJETIVO',
            'DESP_CD_TIPO_ORCAMENTO',
            'DESP_VL_MAX_MENSAL_AUTORIZADO',
            'DESP_IC_REFLEXO_EXERCICIO',
            'PTRS_CD_UNID_ORCAMENTARIA',
            'PTRS_CD_PT_RESUMIDO'
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

    public function incluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir despesa';

        // Adiciona o formulário
        $formulario = new $this->_formulario ();
        $formContratoDespesa = new Orcamento_Form_ContratoDespesa ();
        $formValorDespesa = new Orcamento_Form_ValorDespesa ();

        $formulario->Salvar->SetLabel('Incluir');

        $ano = $formulario->DESP_AA_DESPESA->getValue();

        // Trava ou não campos de valores
        $formValorDespesa = $this->travaCamposValor($formValorDespesa, $ano);

        // Exibe o formulário
        $this->view->formulario = $formulario;
        $this->view->formContrato = $formContratoDespesa;
        $this->view->formValorDespesa = $formValorDespesa;

        // Se for post...
        if ($this->getRequest()->isPost()) {
            $dados = $this->getRequest()->getPost();

            if ($dados ['DESP_CD_PT_RESUMIDO'] != "") {
                $ptres = $dados ['DESP_CD_PT_RESUMIDO'];
                $dados ['DESP_CD_PT_RESUMIDO'] = substr($dados ['DESP_CD_PT_RESUMIDO'], 0, strpos($dados ['DESP_CD_PT_RESUMIDO'], ' -'));
            }

            if ($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] != "") {
                $elemento = $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'];
                $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] = substr($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, strpos($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'], ' -'));
            }

            if ($dados ["DESP_CD_TIPO_ORCAMENTO"] == 1) {
                $formulario->DESP_CD_OBJETIVO->setRequired(true);
                /* $formulario->DESP_CD_PROGRAMA->setRequired ( false ); */
            }

            // Se os dados estiverem válidos
            if ($formulario->isValid($dados) && $formValorDespesa->isValid($dados) && $formContratoDespesa->isValid($dados)) {
                // Busca a tabela para inclusão do registro
                $negocio = new $this->_classeNegocio ();
                $novaProjecao = new Trf1_Orcamento_Negocio_Projecao ();
                $contrato = new Application_Model_DbTable_Orcamento_CeoTbCtrdContratoDespesa ();
                $contratoDespesa = new Trf1_Orcamento_Negocio_Contratodespesa ();

                $dados = $formulario->getValues();
                $dadosValores = $formValorDespesa->getValues();
                $dadosContrato = $formContratoDespesa->getValues();

                $tabela = $negocio->tabela();
                $valor = new Trf1_Orcamento_Valor ();
                $valordespesa = $valor->retornaValorParaBancoRod($dados ["DESP_VL_MAX_MENSAL_AUTORIZADO"]);
                $dados ["DESP_VL_MAX_MENSAL_AUTORIZADO"] = new Zend_Db_Expr("TO_NUMBER(" . $valordespesa . ")");

                $registro = $tabela->createRow($dados);

                try {
                    $valores [1] = $dadosValores ['VL_DESPESA_RESPONSAVEL'];
                    $valores [2] = $dadosValores ['VL_DESPESA_DIPLA'];
                    $valores [3] = $dadosValores ['VL_DESPESA_CONGRESSO'];
                    $valores [4] = $dadosValores ['VL_DESPESA_SECOR'];
                    $valores [7] = $dadosValores ['VL_DESPESA_AJUSTE_DIPLA'];
                    $valores [8] = $dadosValores ['VL_DESPESA_SOLIC_RESPONSAVEL'];

                    // $valores = array(1 => $dadosValores ['VL_DESPESA_RESPONSAVEL'], 2 => $dadosValores ['VL_DESPESA_DIPLA'], 3 => $dadosValores ['VL_DESPESA_CONGRESSO'], 4 => $dadosValores ['VL_DESPESA_SECOR']);
                    // Grava a despesa
                    $codigo = $registro->save();

                    //Grava o contrato
                    if ($dadosContrato ["CTRD_NR_CONTRATO"]) {
                        $dadosContrato ["CTRD_NR_DESPESA"] = $codigo;
                        if ($dadosContrato ["CTRD_VL_DESPESA"] != "") {
                            $valorcontrato = $valor->retornaValorParaBancoRod($dadosContrato ["CTRD_VL_DESPESA"]);
                            $dadosContrato ["CTRD_VL_DESPESA"] = new Zend_Db_Expr("TO_NUMBER(" . $valorcontrato . ")");
                        }
                        if ($dadosContrato ["CTRD_CPFCNPJ_DESPESA"] != "") {
                            $dadosContrato ["CTRD_CPFCNPJ_DESPESA"] = $contratoDespesa->retirarCaractercpf($dadosContrato ["CTRD_CPFCNPJ_DESPESA"]);
                        }

                        $contrato = $contrato->createRow($dadosContrato);

                        try {
                            $codigoContrato = $contrato->save();
                        } catch (Exception $e) {
                            $this->erroOperacao("Ocorreu um erro ao cadastrar os dados do contrato da despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                        }
                    } else {
                        $dadosContrato ["CTRD_NR_CONTRATO"] = 0;
                        $dadosContrato ["CTRD_NR_DESPESA"] = $codigo;
                        $dadosContrato ["CTRD_NM_EMPRESA_CONTRATADA"] = "";
                        $dadosContrato ["CTRD_DT_INICIO_VIGENCIA"] = NULL;
                        $dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"] = NULL;
                        $registroContrato = $contrato->createRow($dadosContrato);

                        try {
                            $codigoContrato = $registroContrato->save();
                        } catch (Exception $e) {
                            $this->erroOperacao("Ocorreu um erro ao cadastrar os dados do contrato em branco da despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                        }
                    }

                    //Insere os valores
                    $valorDespesa = $negocio->insereValoresDespesa($codigo, $valores);
                    if ($valorDespesa) {
                        $this->erroOperacao("Ocorreu um erro ao cadastrar os valores da despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />');
                    }

                    //Insere projecao
                    $projecao = $novaProjecao->insereProjecao($codigo);
                    if ($projecao) {
                        $this->erroOperacao("Ocorreu um erro ao cadastrar a projeção orçamentária. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />');
                    }

                    // Recria os cache referentes a esta controlles
                    $this->recriarCaches($codigo);

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO . " Despesa: $codigo criada com sucesso.", 'status' => 'success'));
                } catch (Exception $e) {
                    $this->erroOperacao("Ocorreu um erro ao cadastrar a despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta os dados no formulário para correção do usuário
                if ($dados ['DESP_CD_PT_RESUMIDO'] != "") {
                    $dados ['DESP_CD_PT_RESUMIDO'] = $ptres;
                }

                if ($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] != "") {
                    $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] = $elemento;
                }

                $formulario->populate($dados);
                $formContratoDespesa->populate($dados);

                $this->popularFormulario($formValorDespesa, $dados);
            }
        }
    }

    public function detalheAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar despesa';

        // Identifica o parâmetro da chave primária a ser buscada
        $chavePrimaria = $this->_getParam('cod');

        // recupera a sessão.
        $sessao = new Orcamento_Business_Sessao ();

        // recupera o perfil do usuário logado na sessão.
        $perfil = $sessao->retornaPerfil();

        // envia o perfil para a visão.
        $this->view->perfil = $perfil['perfil'];

        if ($chavePrimaria) {

            /*
             * Recupera dados do contrato.
             */
            $businessContrato = new Orcamento_Business_Negocio_Contrato();
            $this->view->contrato = $businessContrato->retornaRegistros($chavePrimaria);

            // Busca registro específico
            $negocio = new $this->_classeNegocio ();
            $registro = $negocio->retornaDespesa($chavePrimaria);

            if ($registro) {

                $negocioFase = new Orcamento_Business_Negocio_FaseAnoExercicio ();
                $fase = $negocioFase->retornaFasePorExercicio($registro["DESP_AA_DESPESA"]);
                if($fase == Orcamento_Business_Dados::FASE_EXERCICIO_CONSOLIDACAO){
                    $this->view->bloqueioConsolidacao = true;
                }
                $this->view->despesa = $registro;
                $this->view->fase = $fase;
                
            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    public function editarAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Editar despesa';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio ();
        $camposChave = $negocio->chavePrimaria();
        $CeotbCtrdContratoDespesa = new Trf1_Orcamento_Negocio_Contratodespesa ();
        $contrato = new Application_Model_DbTable_Orcamento_CeoTbCtrdContratoDespesa ();

        $formulario = new $this->_formulario ();
        $formContratoDespesa = new Orcamento_Form_ContratoDespesa ();
        $formValorDespesa = new Orcamento_Form_ValorDespesa ();

        $this->view->formulario = $formulario;
        $this->view->formContrato = $formContratoDespesa;
        $this->view->formValorDespesa = $formValorDespesa;

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {

            // Busca dados para o preenchimento do formulário
            $chavePrimaria = $this->_getParam('cod');

            if ($chavePrimaria) {

                /*
                 * Recupera dados do contrato.
                 */
                $businessContrato = new Orcamento_Business_Negocio_Contrato();
                $this->view->contrato = $businessContrato->retornaRegistros($chavePrimaria);

            // recupera a sessão.
            $sessao = new Orcamento_Business_Sessao ();

            // recupera o perfil do usuário logado na sessão.
            $perfil = $sessao->retornaPerfil();

            // envia o perfil para a visão.
            $this->view->perfil = $perfil['perfil'];

                // Busca registro específico
                $registro = $negocio->retornaDespesa($chavePrimaria);

                if ($registro) {

                    // verifica a regra 147
                    $regra = $this->rn147($perfil, $registro['DESP_NR_DESPESA']);

                    // caso n tenha permissao
                    if ($regra == false) {
                        // mensagem para planejamento - dipla
                        if ($perfil['perfil'] == 'planejamento') {
                            $this->_helper->flashMessenger(array(message => "Essa despesa não é uma despesa do exercicio anterior ou seu perfil não permite edição de despesas nesse status.", 'status' => 'notice'));
                        } else {
                            // mensagem para dipor
                            $this->_helper->flashMessenger(array(message => "Você só pode editar despesas no qual o exericio esteja no status: Proposta Liberada, Em Execução, Encerrado e Em Aprovação.", 'status' => 'notice'));
                        }
                        $this->view->bloqueioRegra = true;
                    }

                    // Verifica a existência de despesa anterior
                    $despesaAnterior = $registro ['DESP_NR_COPIA_DESPESA'];
                    $saldoAnterior = 0;
                    $saldoBaseAnterior = 0;

                    if ($despesaAnterior) {
                        $negocioSaldo = new Trf1_Orcamento_Negocio_Saldo ();
                        $saldo = $negocioSaldo->retornaSaldo($despesaAnterior);

                        // campo: Dotação autorizada do exercício anterior
                        $saldoAnterior = $saldo ['VR_SUB_TOTAL'];

                        // campo: Base do exercício anterior dinâmico
                        $saldoBaseAnterior = $saldo ['VR_BASE_EXERCICIO'];
                        // Zend_Debug::dump ( $saldo );
                    }

                    // Verifica se a despesa já foi copiada
                    if ($registro['DESP_NR_COPIA_DESPESA'] && $registro['DESP_NR_COPIA_DESPESA'] != '') {

                        $options = $formulario->DESP_AA_DESPESA->getMultiOptions();
                        $formulario->DESP_AA_DESPESA->setMultiOptions(array($registro['DESP_AA_DESPESA'] => $options[$registro['DESP_AA_DESPESA']]));
                    }

                    // Agrega valor dinâmico do saldo atual da despesa anterior
                    $registro ['VL_SALDO_ANTERIOR'] = $saldoAnterior;
                    $registro ['VL_SALDO_BASE_ANTERIOR'] = $saldoBaseAnterior;

                    // Agrega valor dinâmico do saldo atual da despesa anterior
                    // $registro [ 'VL_DESPESA_ANTERIOR' ] = $saldoAnterior;

                    $contrato = $CeotbCtrdContratoDespesa->retornaRegistro($chavePrimaria);
                    $this->validaPermissao($registro ['LOTA_SIGLA_SECAO'], $registro ['LOTA_COD_LOTACAO']);

                    $registro ['DESP_CD_PT_RESUMIDO'] = $registro ['CD_DS_SG_PTRES'];
                    $registro ['DESP_CD_ELEMENTO_DESPESA_SUB'] = $registro ['CD_DS_ELEMENTO'];

                    foreach ($camposChave as $chave) {
                        $formulario->$chave->setAttrib('readonly', true);
                    }

                    $this->view->nrDespesa = $registro ['DESP_NR_DESPESA'];
                    $this->view->despesa = $registro;

                    // Despesa
                    $formulario->populate($registro);

                    // Contrato
                    if ($contrato) {
                        $formContratoDespesa->populate($contrato);
                    }

                    // Valores
                    // $formValorDespesa->populate($registro);
                    $ano = $registro ['DESP_AA_DESPESA'];
                    $this->popularFormulario($formValorDespesa, $registro, $ano);
                } else {
                    $this->registroNaoEncontrado();
                }
            } else {
                $this->codigoNaoInformado();
            }
        } else {
            $chavePrimaria = $this->_getParam('cod');
            // Busca dados do formulário
            $dados = $this->getRequest()->getPost();

            if (!is_null($dados ['DESP_CD_PT_RESUMIDO'])) {
                $ptres = $dados ['DESP_CD_PT_RESUMIDO'];
                $dados ['DESP_CD_PT_RESUMIDO'] = substr($dados ['DESP_CD_PT_RESUMIDO'], 0, strpos($dados ['DESP_CD_PT_RESUMIDO'], ' -'));
            }
            if (!is_null($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'])) {
                $elemento = $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'];
                $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] = substr($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'], 0, strpos($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'], ' -'));
            }

            if ($formulario->isValid($dados) && $formValorDespesa->isValid($dados) && $formContratoDespesa->isValid($dados)) {
                $dados = $formulario->getValues();
                $dadosValores = $formValorDespesa->getValues();
                $dadosContrato = $formContratoDespesa->getValues();

                // Instancia a model para edição do registro
                $tabela = $negocio->tabela();

                // Busca registro pela chave primária
                $registro = $tabela->find($chavePrimaria)->current();

                // Não permite alteração na chave primária
                foreach ($camposChave as $chave) {
                    unset($dados [$chave]);
                }

                $valor = new Trf1_Orcamento_Valor ();
                $valordespesa = $valor->formataMoedaBanco($dados ["DESP_VL_MAX_MENSAL_AUTORIZADO"]);

                /* bug fix da moeda banco oracle */
                $dados ["DESP_VL_MAX_MENSAL_AUTORIZADO"] = new Zend_Db_Expr("TO_NUMBER(" . $valordespesa . ")");
                $registro->setFromArray($dados);

                try {
                    // Editar Despesa
                    $codigo = $registro->save();

                    if ($codigo) {
                        // caso 0 não reflete no ano seguinte e ainda deleta logicamente a despesa caso exista
                        if ($dados['DESP_IC_REFLEXO_EXERCICIO'] != '1') {
                            // verifica se a despesa existe no ano seguinte
                            $despAnoSeguinte = $negocio->retornaDespesaPorExercicio($chavePrimaria, $dados["DESP_AA_DESPESA"]);
                            // configura o exercicio seguinte
                            $exercicioSeguinte = $dados["DESP_AA_DESPESA"] + 1;
                            // Caso exista despesa, apaga lógicamente
                            if ($despAnoSeguinte) {
                                $exclui = $negocio->exclusaoLogicaporAno($chaves, $exercicio);
                            }
                        }

                        // Update na tabela valores
                        $this->alteraValoresDespesa($chavePrimaria, $dadosValores);
                    }

                    //Alteração ou Criação do contrato (Despesas antigas)
                    if ($dadosContrato ["CTRD_NR_CONTRATO"] != "") {
                        $nrContrato = $CeotbCtrdContratoDespesa->retornaRegistro($chavePrimaria);
                        $contratoDespesaAlterar = $contrato->find($nrContrato ['CTRD_ID_CONTRATO_DESPESA'])->current();
                        $dadosContrato ["CTRD_NR_DESPESA"] = $chavePrimaria;
                        if ($dadosContrato ["CTRD_DT_INICIO_VIGENCIA"]) {
                            $dadosContrato ["CTRD_DT_INICIO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_INICIO_VIGENCIA'] . "','DD/MM/YYYY')");
                        }
                        if ($dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"]) {
                            $dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"] = new Zend_Db_Expr("TO_DATE('" . $dadosContrato ['CTRD_DT_TERMINO_VIGENCIA'] . "','DD/MM/YYYY')");
                        }
                        if ($dadosContrato ["CTRD_VL_DESPESA"] != "") {
                            $valorcontrato = $valor->retornaValorParaBancoRod($dadosContrato ["CTRD_VL_DESPESA"]);
                            $dadosContrato ["CTRD_VL_DESPESA"] = new Zend_Db_Expr("TO_NUMBER(" . $valorcontrato . ")");
                        }
                        if ($dadosContrato ["CTRD_CPFCNPJ_DESPESA"] != "") {
                            $dadosContrato ["CTRD_CPFCNPJ_DESPESA"] = $CeotbCtrdContratoDespesa->retirarCaractercpf($dadosContrato ["CTRD_CPFCNPJ_DESPESA"]);
                        }

                        if (is_null($contratoDespesaAlterar)) {
                            $contratoDespesaAlterar = $contrato->createRow($dadosContrato);
                        } else {
                            $contratoDespesaAlterar->setFromArray($dadosContrato);
                        }

                        try {
                            $contratoDespesaAlterar->save();
                        } catch (Exception $e) {
                            $this->erroOperacao("Ocorreu um erro ao editar o contrato. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                        }
                    } else {
                        $nrContrato = $CeotbCtrdContratoDespesa->retornaRegistro($chavePrimaria);
                        $contratoDespesa = $contrato->find($nrContrato ['CTRD_ID_CONTRATO_DESPESA'])->current();
                        $dadosContrato ["CTRD_NR_CONTRATO"] = 0;
                        $dadosContrato ["CTRD_NR_DESPESA"] = $chavePrimaria;
                        $dadosContrato ["CTRD_NM_EMPRESA_CONTRATADA"] = NULL;
                        $dadosContrato ["CTRD_DT_INICIO_VIGENCIA"] = NULL;
                        $dadosContrato ["CTRD_DT_TERMINO_VIGENCIA"] = NULL;
                        if (!is_null($contratoDespesa)) {
                            $contratoDespesa->setFromArray($dadosContrato);
                        } else {
                            $contratoDespesa = $contrato->createRow($dadosContrato);
                        }

                        try {
                            $contrato = $contratoDespesa->save();
                        } catch (Exception $e) {
                            $this->erroOperacao("Ocorreu um erro ao editar o contrato. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                        }
                    } // Fim do contrato

                    $this->recriarCaches($chavePrimaria);

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
                } catch (Exception $e) {
                    $this->erroOperacao("Ocorreu um erro na edição da despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
                }

                // Volta para a index
                $this->voltarIndexAction();
            } else {
                // Reapresenta os dados no formulário para correção do usuário
                if (!is_null($dados ['DESP_CD_PT_RESUMIDO'])) {
                    $dados ['DESP_CD_PT_RESUMIDO'] = $ptres;
                }

                if (!is_null($dados ['DESP_CD_ELEMENTO_DESPESA_SUB'])) {
                    $dados ['DESP_CD_ELEMENTO_DESPESA_SUB'] = $elemento;
                }

                $this->view->nrDespesa = $chavePrimaria;
                $this->view->despesa = $registro;

                $formulario->populate($dados);
                $formContratoDespesa->populate($dados);
                // $formValorDespesa->populate($dados);
                $this->popularFormulario($formValorDespesa, $dados);
            }
        }
    }

    public function editarcontratoAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar contrato da despesa';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio ();
        $camposChave = $negocio->chavePrimaria();

        $formulario = new Orcamento_Form_ContratoDespesa ();
        $this->view->formContrato = $formulario;

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            // Busca dados para o preenchimento do formulário
            $chavePrimaria = $this->_getParam('cod');

            if ($chavePrimaria) {
                // Busca registro específico
                $registro = $negocio->retornaDespesa($chavePrimaria);

                if ($registro) {
                    // Bloqueia a edição da chave primária
                    foreach ($camposChave as $chave) {
                        //$formulario->$chave->setAttrib ( 'readonly', true );
                    }

                    $this->view->nrDespesa = $registro ['DESP_NR_DESPESA'];
                    $this->view->despesa = $registro;

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

                // Adiciona vínculo com a despesa
                $dados ['CTRD_NR_DESPESA'] = $chavePrimaria;

                // Instancia a model para edição do registro
                $tabela = new Orcamento_Model_DbTable_Ctrd ();

                // Busca registro pelo campo CTRD_NR_DESPESA
                // // $registro = $tabela->find ( 100 )->current ();
                $s = $tabela->select();
                $cond = $s->where('CTRD_NR_DESPESA = ?', $chavePrimaria);
                $registro = $tabela->fetchRow($cond);

                // Não permite alteração na chave primária
                foreach ($camposChave as $chave) {
                    unset($dados [$chave]);
                }

                $registro->setFromArray($dados);

                try {
                    // Grava as alterações no banco
                    $codigo = $registro->save();



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

    public function excluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir despesas';

        // Instancia a regra de negócio
        $negocio = new $this->_classeNegocio ();

        // Verifica o tipo de requisição Get / Post
        if ($this->getRequest()->isGet()) {
            $chavePrimaria = $this->_getParam('cod');

            if ($chavePrimaria) {
                // Transforma o parâmetro informado para array de $chaves, se for o caso
                $chaves = explode(',', $chavePrimaria);

                // Busca os registros selecionados
                $registros = $negocio->retornaVariosRegistros($chaves);

                if ($registros) {
                    // Verifica a existência de despesa anterior
                    foreach ($registros as $registro) {
                        $despAnterior [] = $registro ['DESP_NR_COPIA_DESPESA'];
                    }

                    if ($despAnterior) {
                        $negocioSaldo = new Trf1_Orcamento_Negocio_Saldo ();
                        $saldos = $negocioSaldo->retornaSaldo($despAnterior);

                        $qtdeDespesas = count($despAnterior);

                        if ($qtdeDespesas > 1) {
                            $sValores = array();

                            foreach ($saldos as $saldo) {
                                // campo: Despesa anterior
                                $sDesp = $saldo ['NR_DESPESA'];

                                // campo: Dotação autorizada do exercício anterior
                                $saldoAnterior = $saldo ['VR_SUB_TOTAL'];

                                // campo: Base do exercício anterior dinâmico
                                $saldoBaseAnterior = $saldo ['VR_BASE_EXERCICIO'];

                                // Compõe novos valores do array
                                $sValores [$sDesp] ['VL_SALDO_ANTERIOR'] = $saldo ['VR_SUB_TOTAL'];
                                $sValores [$sDesp] ['VL_SALDO_BASE_ANTERIOR'] = $saldo ['VR_BASE_EXERCICIO'];
                            }
                        } else {
                            // campo: Despesa anterior
                            $sDesp = $saldos ['NR_DESPESA'];

                            // campo: Dotação autorizada do exercício anterior
                            $saldoAnterior = $saldos ['VR_SUB_TOTAL'];

                            // campo: Base do exercício anterior dinâmico
                            $saldoBaseAnterior = $saldos ['VR_BASE_EXERCICIO'];

                            // Compõe novos valores do array
                            $sValores [$sDesp] ['VL_SALDO_ANTERIOR'] = $saldos ['VR_SUB_TOTAL'];
                            $sValores [$sDesp] ['VL_SALDO_BASE_ANTERIOR'] = $saldos ['VR_BASE_EXERCICIO'];
                        }
                    }

                    // Exibe valores na view
                    $this->view->codigo = $negocio->chavePrimaria();
                    $this->view->dados = $registros;
                    $this->view->saldos = $sValores;
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
                        // Seta o campo desp_nr_copia_despesa como null SOSTI 2015010001101011010160000052
                        $busines = new Orcamento_Business_Negocio_Despesa ();

                        // Exclui o registro
                        $negocio->exclusaoLogica($chaves);

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

    /**
     * a descrever...
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function trocaptresAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Alteração de PTRES em múltiplas despesas';

        // Instancia formulário base
        $formBase = new Orcamento_Form_Despesaptres ();

        // Instancia o formulario de despesa
        $formulario = new Orcamento_Form_Despesapergunta ();

        // Altera esse...
        $formulario->DESP_NR_DESPESA->setAttrib('size', 90);

        // Cria novo botão...
        $botao = new Zend_Form_Element_Button('DESP_DESPESA_ADD');
        $botao->setLabel('Adicionar');
        $botao->setAttrib('class', Orcamento_Business_Dados::CLASSE_INCLUIR);

        // Altera componentes
        $formulario->removeElement('Consultar');
        $formulario->addElement($botao);

        // Exibe os formulários
        $this->view->formBase = $formBase;
        $this->view->formulario = $formulario;



        if ($this->getRequest()->isPost()) {
            // Busca dados do formulário preenchido
            $dados = $this->getRequest()->getPost();

            // Model de Despesa
            $model = new Orcamento_Business_Negocio_Despesa();

            // Tratamento para codigo de ptres
            $dados['PTRES_INICIAL'] = split(" ", $dados['PTRES_INICIAL'], 5);
            $dados['PTRES_NOVO'] = split(" ", $dados['PTRES_NOVO'], 5);

            // Troca de ptres
            try {
                $model->retornaSqlAlteraDespesaPTRES($dados['DESPESA_ANO'], $dados['PTRES_INICIAL'][0], $dados['PTRES_NOVO'][0], $dados['despesas']);
                $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
            } catch (Exception $e) {
                $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
            }

            // volta para mesma tela
            $this->_redirect($this->_modulo . '/' . $this->_controle . '/trocaptres');
        }
    }

    /**
     * a descrever...
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function trocafonteAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Alteração de Fonte em múltiplas despesas';

        // Instancia formulário base
        $formBase = new Orcamento_Form_Despesafonte ();

        // Instancia o formulario de despesa
        $formulario = new Orcamento_Form_Despesapergunta ();

        // Altera esse...
        $formulario->DESP_NR_DESPESA->setAttrib('size', 90);

        // Cria novo botão...
        $botao = new Zend_Form_Element_Button('DESP_DESPESA_ADD');
        $botao->setLabel('Adicionar');
        $botao->setAttrib('class', Orcamento_Business_Dados::CLASSE_INCLUIR);

        // Altera componentes
        $formulario->removeElement('Consultar');
        $formulario->addElement($botao);

        // Exibe os formulários
        $this->view->formBase = $formBase;
        $this->view->formulario = $formulario;

        if ($this->getRequest()->isPost()) {
            // Busca dados do formulário preenchido
            $dados = $this->getRequest()->getPost();

            // Model de Despesa
            $model = new Orcamento_Business_Negocio_Fonte ();

            // Tratamento para codigo de ptres
            $dados['FONTE_INICIAL'] = split(" ", $dados['FONTE_INICIAL'], 5);
            $dados['FONTE_NOVO'] = split(" ", $dados['FONTE_NOVO'], 5);

            // Troca de ptres
            try {
                $model->retornaSqlAlteraDespesaFONTE($dados['DESPESA_ANO'], $dados['FONTE_INICIAL'][0], $dados['FONTE_NOVO'][0], $dados['despesas']);
                $this->_helper->flashMessenger(array(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
            } catch (Exception $e) {
                $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_ERRO . '<br />' . $e->getMessage());
            }

            // volta para mesma tela
            $this->_redirect($this->_modulo . '/' . $this->_controle . '/trocafonte');
        }
    }

    private function recriarCaches ($despesa = 0) {
        $cache = new Trf1_Orcamento_Cache ();
        $cache->excluirCachesSensiveis($this->_controle, $despesa);
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
     * Popula o formulário com o $registro informado
     *
     * @param Zend_Form $formulario
     *        Objeto formulário
     * @param array $registro
     *        Dados para popular o formulário
     * @param numeric $ano
     *        Ano a ser validado para a exibição ou não dos campos de valor
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function popularFormulario ($formulario, $registro, $ano) {
        // Preenche os campos do formulário com os campos do $registro
        $formulario->populate($registro);

        // Trava ou não campos de valores
        $formulario = $this->travaCamposValor($formulario, $ano);

        // Devolve o formulário preenchido com o $registro
        return $formulario;
    }

    /**
     * Trava ou não campos de valores
     *
     * @param Zend_Form $formulario
     *        Objeto formulário
     * @param numeric $ano
     *        Ano a ser validado para a exibição ou não dos campos de valor
     * @return Zend_Form $formulario
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    protected function travaCamposValor ($formulario, $ano) {
        // Permissões específicas por fase do exercício
        $negocioFase = new Orcamento_Business_Negocio_FaseAnoExercicio ();

        $campos [1] = 'VL_DESPESA_AJUSTE_DIPLA';
        $campos [2] = 'VL_DESPESA_SOLIC_RESPONSAVEL';
        $campos [3] = 'VL_DESPESA_DIPLA';
        $campos [4] = 'VL_DESPESA_CONGRESSO';
        $campos [5] = 'VL_DESPESA_SECOR';

        foreach ($campos as $campo) {
            $edita = $negocioFase->matrizAcesso($ano, $campo);

            if ($edita != true) {
                $formulario->$campo->setAttrib('readonly', true);
            }
        }

        // Devolve o formulário preenchido com o $registro
        return $formulario;
    }

    public function ajaxdespesaAction () {
        $ano = $this->_getParam('ano', 0);
        $ptres = $this->_getParam('ptres', 0);
        $term = $this->_getParam('term', '');

        $negocio = new Orcamento_Business_Negocio_Despesa ();
        $dados = $negocio->retornaComboDespesaPorAnoPtres($ano, $ptres, $term);

        foreach ($dados as $value) {
            $results[] = array('label' => $value["DS_COMBO_DESPESA"]);
        }

        $this->_helper->json->sendJson($results);
    }

    public function ajaxfonteAction () {
        $ano = $this->_getParam('ano', 0);
        $fonte = $this->_getParam('fonte', 0);
        $ptres = $this->_getParam('ptres', 0);
        $ug = $this->_getParam('ug', 0);
        $term = $this->_getParam('term', '');

        $negocio = new Orcamento_Business_Negocio_Fonte ();
        $dados = $negocio->retornaComboDespesaPorAnoFonte($ano, $fonte, $ptres, $ug, $term);

        foreach ($dados as $value) {
            $results[] = array('label' => $value["DS_COMBO_DESPESA"]);
        }

        $this->_helper->json->sendJson($results);
    }

    /**
     * Pesquisa um ptres valido
     * @name ajaxValidaPtres
     * @return boolean
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function ajaxvalidaptresAction () {
        $modelPtres = new Trf1_Orcamento_Negocio_Ptres();
        $ptres = $this->_getParam('ptres');
        $dados = $modelPtres->retornaRegistro($ptres);
        $this->_helper->json->sendJson($dados);
    }

    public function ajaxvalidafonteAction () {
        $modelFonte = new Trf1_Orcamento_Negocio_Fonte();
        $fonte = $this->_getParam('fonte');
        $dados = $modelFonte->retornaRegistro($fonte);
        $this->_helper->json->sendJson($dados);
    }

    private function validaPermissao ($secao, $lotacao) {
        $permissao = new Trf1_Orcamento_Permissao ();
        $nivelPermissao = $permissao->retornaNivelPermissao($secao, $lotacao);

        switch ($nivelPermissao) {
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_DIPOR :
                // permissão total
                break;
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_SECCIONAL :
            // bloqueia campos para edição
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_TRF_SECRETARIA :
            // bloqueia campos para edição
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_TRF_DIEFI :
            // bloqueia campos para edição
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_CONSULTA :
            // bloqueia TODOS os campos para edição
            case Trf1_Orcamento_Definicoes::NIVEL_PERMISSAO_SEM_ACESSO :

            // volta para tela de listagem
        }
    }

    /**
     * Redireciona para a indexAction do _modulo e _controle
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
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

    // verifica se a despesa existe no ano seguinte
    public function despesaexercicioAction () {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $despesa = $this->_getParam('despesa');
        $exercicio = $this->_getParam('exercicio');

        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaDespesaPorExercicio($despesa, $exercicio);

        $this->_helper->json($dados);
    }

    public function alteraValoresDespesa ($chavePrimaria, $dadosValores) {
        $negocio = new $this->_classeNegocio ();

        // Monta os valores
        $valores [1] = $dadosValores ['VL_DESPESA_RESPONSAVEL'];
        $valores [2] = $dadosValores ['VL_DESPESA_DIPLA'];
        $valores [3] = $dadosValores ['VL_DESPESA_CONGRESSO'];
        $valores [4] = $dadosValores ['VL_DESPESA_SECOR'];
        $valores [7] = $dadosValores ['VL_DESPESA_AJUSTE_DIPLA'];
        $valores [8] = $dadosValores ['VL_DESPESA_SOLIC_RESPONSAVEL'];
        $valores [11] = $dadosValores ['VL_DESPESA_BASE_DIFERENCA'];
        $valores [12] = $dadosValores ['VL_DESPESA_BASE_EXERC_ATUAL'];
        $valores [13] = $dadosValores ['VL_REAJUSTE_PROPOSTA'];


        try {

            // Chamada negocial para edição Valores
            $negocio->editaValoresDespesa($chavePrimaria, $valores);
        } catch (Exception $ex) {

            // Em caso de erro retorna o exception
            $this->erroOperacao("Ocorreu um erro ao editar os valores da despesa. " . Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />');
        }
    }

    /**
     * O perfil DIPLA pode realizar edição de despesas copiadas do exercício anterior e se o status do exercício
     * estiver em: “Em Definição”, “Liberado para Responsáveis”, “Bloqueado para Consolidação” e “Retornado
     * ao Planejamento”.
     *
     * O perfil DIPOR só pode editar as despesas em que seu exercício se encontre nos status: “Proposta
     * Liberada”, “Em Execução”, “Encerrado” e “Em Aprovação”.
     *
     * @param string $perfil
     * @param int $despesa
     * @throws Exception
     */
    public function rn147 ($perfil, $despesa) {

        $negocio = new Trf1_Orcamento_Negocio_Despesa();
        $exercicio = new Orcamento_Business_Negocio_Exercicio();
        // retorna dados da despesa
        $despesaanterior = $negocio->retornaDespesa($despesa);
        // verifica a fase do exercicio
        $statusexercicio = $exercicio->retornaRegistro($despesaanterior['DESP_AA_DESPESA']);


        // perfil dipla
        if ($perfil['perfil'] == 'planejamento') {
            //“Em Definição”, “Liberado para Responsáveis”, “Bloqueado para Consolidação” e “Retornado * ao Planejamento”.
            $statusPermitidos = array(1, 2, 3, 8);
            if (!in_array($statusexercicio["FANE_ID_FASE_EXERCICIO"], $statusPermitidos)) {
                return false;
            }
            // Verifica se é uma despesa do exercicio anterior
            if (empty($despesaanterior['DESP_NR_COPIA_DESPESA'])) {
                return false;
            }
            return true;
        }

        // perfil dipor
        if ($perfil['perfil'] == 'dipor') {
            $statusPermitidosDipor = array(4, 5, 6, 7);
            if (!in_array($statusexercicio["FANE_ID_FASE_EXERCICIO"], $statusPermitidosDipor)) {
                return false;
            }
            return true;
        }
    }

    public function verificaFase()
    {
        // recupera a sessão.
        $sessao = new Orcamento_Business_Sessao ();
        // recupera o perfil do usuário logado na sessão.
        $perfil = $sessao->retornaPerfil();
        // perfis em que a listagem pode ser vista
        $perfisAutorizados = array( 'dipor', 'desenvolvedor', 'planejamento' );
        // casso não seja um perfil autorizado redireciona o usuário
        if( !in_array($perfil['perfil'], $perfisAutorizados)) {
            // Pega fase do exercicio vigente
            $fase = $this->_fase->retornaFasePorExercicio(date('Y'));            
            if( $fase != Orcamento_Business_Dados::FASE_EXERCICIO_EXECUCAO ){
                // msg para gestor
                $this->_helper->flashMessenger(array(message => "Prezado Gestor, a listagem completa não esta liberada. Favor verificar a listagem resumida.", 'status' => 'error'));
                // volta para index
                $this->_redirect($this->_modulo . '/' . 'index' . '');
            }           
        }          

    }

}
