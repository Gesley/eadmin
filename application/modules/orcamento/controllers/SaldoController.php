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
 * Disponibiliza as funcionalidades ao usuário sobre consulta saldo.
 *
 * @category Orcamento
 * @package Orcamento_SaldoController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_SaldoController extends Zend_Controller_Action
{

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

    public function init ()
    {
        // Título apresentado no Browser
        $this->view->title = 'Saldo';
        
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
        $this->_classeNegocio = 'Trf1_Orcamento_Negocio_' .
         ucfirst ( $this->_controle );
        
        // Define o nome do formulário
        $this->_formulario = 'Orcamento_Form_Despesapergunta';
        
        // Define o nome do formulario de consulta
        $this->_formularioSaldo = new Orcamento_Form_Saldo();

        // Grava log de cada ação
        $log = new Trf1_Orcamento_Log ();
        $log->gravaLog ( $requisicao );
    }

    public function postDispatch ()
    {
        // Apresenta o tempo de carregamento da página
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
    }

    public function indexAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Consulta saldo da despesa';
        
        $formulario = new $this->_formulario ();
        $this->view->formulario = $this->_formularioSaldo;
        
        if ( $this->getRequest ()->isPost () ) {
            $dados = $this->getRequest ()->getPost ();
            
            if ( $formulario->isValid ( $dados ) ) {
                $this->_redirect ( 
                'orcamento/saldo/detalhe/cod/' . $dados [ 'DESP_NR_DESPESA' ] );
            }
        }
    }

    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Saldo da despesa';
        
        // Identifica o parâmetro da chave primária a ser buscada
        $despesa = $this->_getParam ( 'cod' );

        if ( $despesa ) {
            // Busca dados da despesa
            $negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
            $dadosDespesa = $negocioDespesa->retornaDespesa ( $despesa );
            $ncMoldel = new Trf1_Orcamento_Negocio_Nc();           

            if ( $dadosDespesa ) {
                // Exibe dados da despesa na view
                $this->view->despesa = $dadosDespesa;
                
                // Busca registro específico
                $negocio = new $this->_classeNegocio ();
                
                $dados = $negocio->retornaSaldo ( $despesa );
                
                $this->view->saldo = $dados;
                
                // Testes
                // Zend_Debug::dump($dados);
                // Zend_Debug::dump($dadosDespesa);
                
                $formato = new Bvb_Grid_Formatter_Numerocor ();
                
                // +Testes
                // $valor = $dados['VR_PROPOSTA_APROVADA'];
                // $vFormatado = $formato->format($valor);
                // Zend_Debug::dump($valor);
                // Zend_Debug::dump($vFormatado);
            } else {
                $this->registroNaoEncontrado ();
            }
        } else {
            $this->codigoNaoInformado ();
        }
    }

    public function listagemAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de saldos';
        
        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagem ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'detalhe' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DESP_DS_ADICIONAL' => array ( 'title' => 'Descrição', 
                        'abbr' => '' ), 
                'SG_FAMILIA_RESPONSAVEL' => array ( 'title' => 'Responsavel', 
                        'abbr' => '' ), 
                'DESP_CD_FONTE' => array ( 'title' => 'Fonte', 'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ),
                'UNOR_CD_UNID_ORCAMENTARIA' => array ( 'title' => 'UO',
                'abbr' => '' ),
                'PTRS_SG_PT_RESUMIDO' => array ( 'title' => 'Sigla', 
                        'abbr' => '' ),
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 'title' => 'Natureza', 
                        'abbr' => '', 'format' => 'Naturezadespesa' ), 
                'TIDE_DS_TIPO_DESPESA' => array ( 'title' => 'Caráter', 
                        'abbr' => '', 'format' => 'Naturezadespesa' ), 
                'VR_PROPOSTA_SECOR' => array ( 'title' => 'Proposta orçamentária', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_PROPOSTA_REMANEJADA' => array ( 
                        'title' => 'Ajuste da proposta orçamentária', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_PROPOSTA_APROVADA' => array ( 
                        'title' => 'Proposta aprovada', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_PROPOSTA_RECEBIDA' => array ( 
                        'title' => 'Proposta aprovada recebida', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_CREDITO_ADICIONAL' => array ( 
                        'title' => 'Crédito adicional', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_CREDITO_CONTINGENCIA' => array ( 
                        'title' => 'Contingenciamento', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_CREDITO_EXTRA' => array ( 'title' => 'Crédito extra', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_ALTERACAO_QDD' => array ( 'title' => 'Alteração de QDD', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_CREDITO_SAIDA' => array ( 'title' => 'Saída de crédito', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_CREDITO_DESTAQUE' => array ( 
                        'title' => 'Destaque', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_MOVIMENTACAO' => array ( 
                        'title' => 'Movimentações de crédito', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_TOTAL_DESPESA' => array ( 'title' => 'Dotação descentralizada no exercício', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_PROPOSTA_A_RECEBER' => array ( 
                        'title' => 'Proposta aprovada a receber', 'abbr' => '', 
                        'format' => 'Numerocor' ), 
                'VR_A_RECEBER' => array ( 'title' => 'Crédito aprovado a receber', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_SUB_TOTAL' => array ( 'title' => 'Dotação autorizada do exercício', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_RDO' => array ( 'title' => 'Requisição efetivada', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_A_AUTORIZAR' => array ( 'title' => 'Saldo orçamentário sem requisição', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_EMPENHADO' => array ( 'title' => 'Requisição empenhada', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_A_EMPENHAR' => array ( 'title' => 'Requisição a empenhar', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_EXECUTADO' => array ( 'title' => 'Requisição executada', 
                        'abbr' => '', 'format' => 'Numerocor' ), 
                'VR_A_EXECUTAR' => array ( 'title' => 'Requisição a executar', 
                        'abbr' => '', 'format' => 'Numerocor' ),
                'VR_PROJECAO' => array ( 'title' => 'Projeção',
                        'abbr' => '', 'format' => 'Numerocor' ),
                'VR_PERCENTUAL_REAJUSTE' => array ( 'title' => 'Reajuste',
                        'abbr' => '', 'format' => 'Percentual' ) );
        
        $ocultos [] = 'VR_PERCENTUAL_REAJUSTE';
        $ocultos [] = 'VR_PROJECAO';
        $ocultos [] = 'DESP_NR_COPIA_DESPESA';
        // $ocultos [] = 'NOEM_CD_NOTA_EMPENHO';
        $ocultos [] = 'VR_PROJ_FUTURA';
        $ocultos [] = 'VR_MES_ATUAL';
        $ocultos [] = 'VR_EXEC_PASSADA';
        $ocultos [] = 'VR_SITUACAO_ORCAMENTARIA';
        $ocultos [] = 'VR_SITUACAO_ORCAMENTARIA';
        $ocultos [] = 'DESP_CD_CATEGORIA';
        $ocultos [] = 'DESP_CD_VINCULACAO';
        $ocultos [] = 'TREC_DS_TIPO_RECURSO';
        $ocultos [] = 'PORC_DS_TIPO_ORCAMENTO';
        $ocultos [] = 'POBJ_DS_OBJETIVO';
        $ocultos [] = 'PPRG_DS_PROGRAMA';
        $ocultos [] = 'POPE_DS_TIPO_OPERACIONAL';
        $ocultos [] = 'VR_BASE_EXERCICIO';
        $ocultos [] = 'EXEC_VR_JANEIRO';
        $ocultos [] = 'EXEC_VR_FEVEREIRO';
        $ocultos [] = 'EXEC_VR_MARCO';
        $ocultos [] = 'EXEC_VR_ABRIL';
        $ocultos [] = 'EXEC_VR_MAIO';
        $ocultos [] = 'EXEC_VR_JUNHO';
        $ocultos [] = 'EXEC_VR_JULHO';
        $ocultos [] = 'EXEC_VR_AGOSTO';
        $ocultos [] = 'EXEC_VR_SETEMBRO';
        $ocultos [] = 'EXEC_VR_OUTUBRO';
        $ocultos [] = 'EXEC_VR_NOVEMBRO';
        $ocultos [] = 'EXEC_VR_DEZEMBRO';
        $ocultos [] = 'VR_TOTAL_EXECUTADO';        
        $ocultos [] = 'VR_SALDO_MENOS_DOTACAO';     
        $ocultos [] = 'EXERCICIO';        
              

        
        $camposOcultos = $ocultos;
        
        $classeGrid = new Trf1_Orcamento_Grid ();
        $grid = $classeGrid->criaGrid ( $this->_controle, $dados, 
        $chavePrimaria, $this->view->telaTitle, $acoes );
        
        // Personalização do grid
        foreach ( $camposDetalhes as $campo => $opcoes ) {
            $grid->updateColumn ( $campo, $opcoes );
        }
        
        // Oculta campos do grid
        $grid->setColumnsHidden ( $camposOcultos );
        
        // Exibição do grid
        $this->view->grid = $grid->deploy ();
    }
    
    public function planejamentoAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Listagem de saldos planejamento estratégico';
        
        // Dados do grid
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagem ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'detalhe' );
        
        // Personaliza a exibição dos campos no grid (index)
        $campo [ 'title' ] = 'Ano';
        $detalhes [ 'DESP_AA_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'UG';
        $detalhes [ 'DESP_CD_UG' ] = $campo;
        
        $campo [ 'title' ] = 'Despesa';
        $detalhes [ 'NR_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'Descrição';
        $detalhes [ 'DESP_DS_ADICIONAL' ] = $campo;
        
        $campo [ 'title' ] = 'Responsável';
        $detalhes [ 'SG_FAMILIA_RESPONSAVEL' ] = $campo;
        
        $campo [ 'title' ] = 'Fonte';
        $detalhes [ 'DESP_CD_FONTE' ] = $campo;
        
        $campo [ 'title' ] = 'PTRES';
        $detalhes [ 'DESP_CD_PT_RESUMIDO' ] = $campo;

        $campo [ 'title' ] = 'UO';
        $detalhes [ 'UNOR_CD_UNID_ORCAMENTARIA' ] = $campo;

        $campo [ 'title' ] = 'Sigla';
        $detalhes [ 'PTRS_SG_PT_RESUMIDO' ] = $campo;

        $campo [ 'title' ] = 'Natureza';
        $campo [ 'format' ] = 'Naturezadespesa';
        $detalhes [ 'DESP_CD_ELEMENTO_DESPESA_SUB' ] = $campo;
        
        $campo [ 'title' ] = 'Caráter';
        unset ( $campo [ 'format' ] );
        $detalhes [ 'TIDE_DS_TIPO_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'Proposta orçamentária';
        $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_PROPOSTA_SECOR' ] = $campo;
        
        $campo [ 'title' ] = 'Ajuste da proposta orçamentária';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_PROPOSTA_REMANEJADA' ] = $campo;
        
        $campo [ 'title' ] = 'Proposta aprovada';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_PROPOSTA_APROVADA' ] = $campo;
        
        $campo [ 'title' ] = 'Proposta aprovada recebida';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_PROPOSTA_RECEBIDA' ] = $campo;
        
        $campo [ 'title' ] = 'Crédito adicional';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_CREDITO_ADICIONAL' ] = $campo;
        
        $campo [ 'title' ] = 'Contingenciamento';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_CREDITO_CONTINGENCIA' ] = $campo;
        
        $campo [ 'title' ] = 'Crédito extra';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_CREDITO_EXTRA' ] = $campo;
        
        $campo [ 'title' ] = 'Alteração de QDD';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_ALTERACAO_QDD' ] = $campo;
        
        $campo [ 'title' ] = 'Saída de crédito';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_CREDITO_SAIDA' ] = $campo;
        
        $campo [ 'title' ] = 'Destaque';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_CREDITO_DESTAQUE' ] = $campo;
        
        $campo [ 'title' ] = 'Movimentações de crédito';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_MOVIMENTACAO' ] = $campo;
        
        $campo [ 'title' ] = 'Dotação descentralizada no exercício';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_TOTAL_DESPESA' ] = $campo;
        
        $campo [ 'title' ] = 'Proposta aprovada a receber';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_PROPOSTA_A_RECEBER' ] = $campo;
        
        $campo [ 'title' ] = 'Crédito aprovado a receber';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_A_RECEBER' ] = $campo;
        
        $campo [ 'title' ] = 'Dotação autorizada do exercício';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_SUB_TOTAL' ] = $campo;
        
        $campo [ 'title' ] = 'Requisição efetivada';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_RDO' ] = $campo;
        
        $campo [ 'title' ] = 'Saldo orçamentário sem requisição';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_A_AUTORIZAR' ] = $campo;
        
        $campo [ 'title' ] = 'Requisição empenhada';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_EMPENHADO' ] = $campo;
        
        $campo [ 'title' ] = 'Requisição a empenhar';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_A_EMPENHAR' ] = $campo;
        
        $campo [ 'title' ] = 'Requisição executada';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_EXECUTADO' ] = $campo;
        
        $campo [ 'title' ] = 'Requisição a executar';
        // $campo [ 'format' ] = 'Numerocor';
        $detalhes [ 'VR_A_EXECUTAR' ] = $campo;
        
        $campo [ 'title' ] = 'Categoria';
        unset ( $campo [ 'format' ] );
        $detalhes [ 'DESP_CD_CATEGORIA' ] = $campo;
        
        $campo [ 'title' ] = 'Vinculação';
        $detalhes [ 'DESP_CD_VINCULACAO' ] = $campo;
        
        $campo [ 'title' ] = 'Tipo de recurso';
        $detalhes [ 'TREC_DS_TIPO_RECURSO' ] = $campo;
        
        $campo [ 'title' ] = 'Tipo de orçamento';
        $detalhes [ 'PORC_DS_TIPO_ORCAMENTO' ] = $campo;
        
        $campo [ 'title' ] = 'Objetivo';
        $detalhes [ 'POBJ_DS_OBJETIVO' ] = $campo;
        
        $campo [ 'title' ] = 'Programa';
        $detalhes [ 'PPRG_DS_PROGRAMA' ] = $campo;
        
        $campo [ 'title' ] = 'Tipo operacional';
        $detalhes [ 'POPE_DS_TIPO_OPERACIONAL' ] = $campo;
        
        $camposDetalhes = $detalhes;

        $ocultos [] = 'EXERCICIO';
        $ocultos [] = 'NOEM_CD_NOTA_EMPENHO';
        $ocultos [] = 'VR_PROJ_FUTURA';
        $ocultos [] = 'VR_MES_ATUAL';
        $ocultos [] = 'VR_EXEC_PASSADA';
        $ocultos [] = 'VR_SITUACAO_ORCAMENTARIA';
        $ocultos [] = 'VR_SITUACAO_ORCAMENTARIA';
        $ocultos [] = 'VR_PROJECAO';
        $ocultos [] = 'VR_PERCENTUAL_REAJUSTE';
        $ocultos [] = 'TIDE_IC_RESERVA_RECURSO';
        $ocultos [] = 'DESP_NR_COPIA_DESPESA';
        $ocultos [] = 'VR_BASE_EXERCICIO';
        $ocultos [] = 'EXEC_VR_JANEIRO';
        $ocultos [] = 'EXEC_VR_FEVEREIRO';
        $ocultos [] = 'EXEC_VR_MARCO';
        $ocultos [] = 'EXEC_VR_ABRIL';
        $ocultos [] = 'EXEC_VR_MAIO';
        $ocultos [] = 'EXEC_VR_JUNHO';
        $ocultos [] = 'EXEC_VR_JULHO';
        $ocultos [] = 'EXEC_VR_AGOSTO';
        $ocultos [] = 'EXEC_VR_SETEMBRO';
        $ocultos [] = 'EXEC_VR_OUTUBRO';
        $ocultos [] = 'EXEC_VR_NOVEMBRO';
        $ocultos [] = 'EXEC_VR_DEZEMBRO';
        $ocultos [] = 'VR_TOTAL_EXECUTADO';
        $ocultos [] = 'VR_SALDO_MENOS_DOTACAO';
        
        $camposOcultos = $ocultos;
        
        $classeGrid = new Trf1_Orcamento_Grid ();
        $grid = $classeGrid->criaGrid ( $this->_controle, $dados, 
        $chavePrimaria, $this->view->telaTitle, $acoes );
        
        // Personalização do grid
        foreach ( $camposDetalhes as $campo => $opcoes ) {
            $grid->updateColumn ( $campo, $opcoes );
        }
        
        // Oculta campos do grid
        $grid->setColumnsHidden ( $camposOcultos );
        
        // Exibição do grid
        $this->view->grid = $grid->deploy ();
    }

    private function registroNaoEncontrado ()
    {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_REGISTRO_NAO_ENCONTRADO;
        
        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest ();
        $log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
        
        $this->_helper->flashMessenger ( 
        array ( message => $erro, 'status' => 'notice' ) );
        $this->_redirect ( $this->_modulo . '/' . $this->_controle );
    }

    private function codigoNaoInformado ()
    {
        $erro = Trf1_Orcamento_Definicoes::MENSAGEM_CODIGO_NAO_INFORMADO;
        
        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest ();
        $log->gravaLog ( $requisicao, $erro, zend_log::NOTICE );
        
        $this->_helper->flashMessenger ( 
        array ( message => $erro, 'status' => 'notice' ) );
        $this->_redirect ( $this->_modulo . '/' . $this->_controle );
    }

    public function dashboardAction ()
    {
        /*
         * TODO: A documentar após a conclusão do Dashboard pelo Wilton /
         * Anderson tipo:	string	pizza, barra, coluna, linha, grid, html e texto
         * titulo:	string	Texto de título do bloco individual do dashboard
         * dados:	array	Contém label e valor para os registros a serem
         * mostrados. Limite de 10 registros?? legenda:	string	Texto no rodapé
         * do bloco individual do dashboard
         */
        $dados = array ( 'tipo' => 'grid', 'titulo' => 'Grid teste', 
                'dados' => array ( 
                        'labels' => array ( 'UG', 'PTRes', 
                                'Natureza de Despesa', 'Valor' ), 
                        'linhas' => array ( 
                                array ( '90027', '880', '339030', 546213.99 ), 
                                array ( '90013', '821', '339033', 321987.77 ), 
                                array ( '90012', '821', '339036', 85285.55 ), 
                                array ( '90004', '821', '339039', 30741.33 ), 
                                array ( '90002', '821', '339030', 9850.11 ) ) ), 
                'legenda' => 'Fonte: e-Orçamento' );
        
        return $this->_helper->json->sendJson ( $dados );
    }

}