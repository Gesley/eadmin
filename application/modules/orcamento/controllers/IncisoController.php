<?php

/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre inciso.
 *
 * @category Orcamento
 * @package Orcamento_IncisoController
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_IncisoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Inciso');

        // Define a classe facade
        $this->defineFacade('Inciso');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Inciso';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Inciso';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function incluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Inciso';

        try {
            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();

            if (true === $this->_requisicao->isPost()) {
                // preenche a data de inclusão
                $dados['INCI_DT_INCISO'] = new Zend_Db_Expr('SYSDATE');
                
                // retira a posição para poder fazer nextval no sequence
                unset($dados['INCI_ID_INCISO']);
            }
            
            // valor sempre em maiusculo
            $dados['INCI_VL_INCISO'] = strtoupper($dados['INCI_VL_INCISO']);

            // Efetua validações e inclusão do inciso
            $classe = 'Orcamento_Business_Negocio_Inciso';
            $metodo = 'validacaoRN093';
            
            $this->incluirEditarComValidacao($dados, $classe, $metodo, $dados);
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function editarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Inciso';

        try {
            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();

            if ($this->_requisicao->isPost()) {
                $paramValidacao = array();
                $paramValidacao['id'] = $dados['INCI_ID_INCISO'];
                $paramValidacao['novo'] = $dados['INCI_VL_INCISO'];
            }
            
            // valor sempre em maiusculo
            $dados['INCI_VL_INCISO'] = strtoupper($dados['INCI_VL_INCISO']);

            // Efetua validações e edição do inciso
            $classe = 'Orcamento_Business_Negocio_Inciso';
            $metodo = 'validacaoRN093Update';

            $this->incluirEditarComValidacao($dados, $classe, $metodo, $paramValidacao);
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Inciso';
        
        $dados = $this->_requisicao->getPost();

        if ($this->_requisicao->isPost() && $dados['cmdExcluir'] === "Sim") {
            $parametros = $this->trataParametroGet('cod');
            $negocio = new Orcamento_Business_Negocio_Inciso();

            if ($negocio->consultarExistRegraAssociada($parametros)) {
                $this->operacaoErro($negocio::MENSAGEM_032);
                return;
            }
        }

        // Exclusão do registro
        $this->excluirComValidacao();
    }

}
