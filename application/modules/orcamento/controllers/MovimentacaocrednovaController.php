<?php

/**
 * Contém controller da aplicação
 *
 * e-Admin
 * e-Orçamento
 * Controller
 *
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre regra.
 *
 * @category Orcamento
 * @package Orcamento_RegraController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_MovimentacaocrednovaController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Solicitação de movimentação de crédito');

        // Define a classe facade
        $this->defineFacade('movimentacaocrednova');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de solicitações de movimentações de crédito';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $titulo = 'Visualizar solicitação de movimentação de crédito';
        $this->view->telaTitle = $titulo;

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction() {

        // Título da tela (action)
        $titulo = 'Cadastrar solicitação de movimentação de crédito';
        $this->view->telaTitle = $titulo;
        $formulario = $this->retornaFormulario($acao);
        $acao = Orcamento_Business_Dados::ACTION_INCLUIR;
        $formulario = $this->transformaFormulario($formulario, $acao);
        $negocio = new Orcamento_Business_Negocio_Movimentacaocrednova();

        if ($this->getRequest()->isPost()) {

            $dados = $this->getRequest()->getPost();

            if (!empty($dados['MOVC_NR_DESPESA_ORIGEM']) && !empty($dados['MOVC_NR_DESPESA_DESTINO'])) {

                $businessDespesa = new Trf1_Orcamento_Negocio_Despesa();

                // Pesquisa despesas;
                $despesaOrigem = $businessDespesa->retornaDespesa($dados['MOVC_NR_DESPESA_ORIGEM']);
                $despesaDestino = $businessDespesa->retornaDespesa($dados['MOVC_NR_DESPESA_DESTINO']);

                if ($despesaOrigem['DESP_CD_TIPO_DESPESA'] == 1) {

                    // torna o campo motivo da solicitação como obrigatório.
                    $formulario->getElement('MOVC_DS_JUSTIF_SOLICITACAO')->setRequired(TRUE);

                    if ($formulario->isValid($this->getRequest()->getPost())) {

                        // inclui justificativa.
                        $businessJustificativa = new Orcamento_Business_Negocio_Justificativa();

                        // juta dados.
                        $dados['DESTINO'] = $despesaDestino;

                        $businessJustificativa->incluirJustificativa($dados);
                        $res = $negocio->incluir($dados);

                        if (!$res['sucesso']) {

                            $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                        }

                        $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));

// Volta para a o fluxo de inclusão. sosti 2016010001108011080160000041.
                        $this->_redirect($this->_modulo . '/' . $this->_controle . "/incluir");
                    }
                } else {

                    $res = $negocio->incluir($dados);

                    if (!$res['sucesso']) {

                        $this->erroOperacao(Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_ERRO . '<br />' . $e->getMessage());
                    }

                    $this->_helper->flashMessenger(array(message => Trf1_Orcamento_Definicoes::MENSAGEM_INCLUIR_SUCESSO, 'status' => 'success'));

// Volta para a o fluxo de inclusão. sosti 2016010001108011080160000041.
                    $this->_redirect($this->_modulo . '/' . $this->_controle . "/incluir");
                }
            }

            $formulario->populate($dados);
        }

        $this->view->formulario = $formulario;
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction() {
        // Título da tela (action)
        $titulo = 'Editar solicitação de movimentação de crédito';
        $this->view->telaTitle = $titulo;

        // Edição do registro
        $this->editar();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $titulo = 'Excluir solicitações de movimentação de crédito';
        $this->view->telaTitle = $titulo;

        // Exclusão do registro
        $this->excluir(true);
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction() {
        // Título da tela (action)
        $titulo = 'Restaurar solicitações de movimentação de crédito';
        $this->view->telaTitle = $titulo;

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

}
