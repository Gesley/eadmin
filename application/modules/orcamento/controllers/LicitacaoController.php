<?php
/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 **/

/**
 * Disponibiliza as funcionalidades ao usuário sobre Licitação.
 *
 * @category Orcamento
 * @package Orcamento_Licitacao
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_LicitacaoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     **/
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Licitação');

        // Define a classe facade
        $this->defineFacade('licitacao');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     */
    public function indexAction() {

        // Título da tela (action)
        $this->view->telaTitle = 'Licitação';

        // Dados do grid
        $negocio = new Trf1_Orcamento_Negocio_Licitacao();
        $dados = $negocio->retornaListagem();
        $chavePrimaria = $negocio->chavePrimaria();

        // Geração do grid
        $acoes = array('detalhe', 'editar', 'excluir');

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
            'VR_PROPOSTA_SECOR' => array('title' => 'Proposta orçamentária', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_REMANEJADA' => array('title' => 'Ajuste da proposta orçamentária', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_APROVADA' => array('title' => 'Proposta aprovada', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_RECEBIDA' => array('title' => 'Proposta aprovada recebida', 'abbr' => '', 'format' => 'Numerocor'),
            'VR_PROPOSTA_A_RECEBER' => array('title' => 'Proposta aprovada a receber', 'abbr' => '', 'format' => 'Numerocor'),
            'FASL_DS_FASE' => array('title' => 'Fase da Licitação', 'abbr' => ''),
        );

        $ocultos[] = 'LICT_ID_LICITACAO';
        $ocultos[] = 'FASL_CD_FASE';
        $ocultos[] = 'VR_PROJECAO';
        $ocultos[] = 'VR_EXECUTADO';
        $ocultos[] = 'DESP_NR_COPIA_DESPESA';
        $ocultos[] = 'EXERCICIO';
        $ocultos[] = 'TIDE_IC_RESERVA_RECURSO';

        $camposOcultos = $ocultos;

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
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     */
    public function detalheAction() {

        // Título da tela (action)
        $this->view->telaTitle = 'Licitação';

        // Identifica o parâmetro da chave primária a ser buscada
        $despesa = $this->_getParam('cod');

        if ($despesa) {

            $negocio = new Trf1_Orcamento_Negocio_Licitacao();
            $dados = $negocio->retornaListagem($despesa);

            if ($dados) {

                $dados = current($dados);

                // Exibe dados da despesa na view
                $this->view->dados = $dados;

            } else {
                $this->registroNaoEncontrado();
            }
        } else {
            $this->codigoNaoInformado();
        }
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     */
    public function editarAction() {

        // Título da tela (action)
        $this->view->telaTitle = 'Editar Licitação';
        $negocio = new Trf1_Orcamento_Negocio_Licitacao();
        $form = new Orcamento_Form_Licitacao();
        $despesa = $this->_getParam('cod');
        $dados = $negocio->retornaListagem($despesa);
        $dados = current($dados);

        if ($this->getRequest()->isGet()) {

            $form->populate($dados);

        } else if ($this->getRequest()->isPost()) {

            if ($form->isValid($this->getRequest()->getPost())) {

                $faseNegocio = new Orcamento_Business_Negocio_Licitacao();
                $retorno = $faseNegocio->salvar($this->getRequest()->getPost(), $dados);

                if ($retorno) {

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_ALTERAR_SUCESSO, 'status' => 'success'));
                } else {

                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                }

                $this->voltarIndexAction();

            } else {

                $dadosPost = $this->getRequest()->getPost();
                $form->populate($dadosPost);
            }

        }

        $this->view->dados = $dados;
        $this->view->form = $form;
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     */
    public function excluirAction() {

        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Licitação';
        $negocio = new Trf1_Orcamento_Negocio_Licitacao();
        $despesa = $this->_getParam('cod');
        $dados = $negocio->retornaListagem($despesa);

        if ($this->getRequest()->isGet()) {

            // ajusta o nome das chaves.
            foreach ($dados as $key) {

                $dado[] = array(
                    'Código' => $key['LICT_ID_LICITACAO'],
                    'UG' => $key['DESP_CD_UG'],
                    'Despesa' => $key['NR_DESPESA'],
                    'Descrição' => $key['DESP_DS_ADICIONAL'],
                    'Responsável' => $key['SG_FAMILIA_RESPONSAVEL'],
                    'PTRES' => $key['DESP_CD_PT_RESUMIDO'],
                    'UO' => $key['UNOR_CD_UNID_ORCAMENTARIA'],
                    'Sigla' => $key['PTRS_SG_PT_RESUMIDO'],
                    'Natureza' => $key['DESP_CD_ELEMENTO_DESPESA_SUB'],
                    'Caráter' => $key['TIDE_DS_TIPO_DESPESA'],
                    'Valor Proposta Orçamentária' => $key['VR_PROPOSTA_SECOR'],
                    'Valor Proposta Aprovada' => $key['VR_PROPOSTA_APROVADA'],
                    'Valor Proposta Recebida' => $key['VR_PROPOSTA_RECEBIDA'],
                    'Valor Proposta Recebida' => $key['VR_PROPOSTA_RECEBIDA'],
                );
            }

            $this->view->dados = $dado;
            $this->view->codigo = array('0' => 'Despesa');

        } else if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            if ("Sim" == $post['cmdExcluir']) {

                $faseNegocio = new Orcamento_Business_Negocio_Licitacao();
                $retorno = $faseNegocio->excluir($dados);

                if ($retorno) {

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_SUCESSO, 'status' => 'success'));

                } else {

                    $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_EXCLUIR_ERRO . '<br />' . $e->getMessage());
                }

                $this->voltarIndexAction();

            } else {

                $this->voltarIndexAction();
            }
        }
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     */
    public function restaurarAction() {

        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Status da Licitação';

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

}
