<?php

/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Sandro Maceno <smaceno@stefanini.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre inciso.
 *
 * @category Orcamento
 * @package Orcamento_AlineaController
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_AlineaController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Alínea');

        // Define a classe facade
        $this->defineFacade('alinea');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function indexAction() {
        // Nome da funcionalidade
        $funcionalidade = 'Consultar de Alínea';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function detalheAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Alínea';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function incluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Alínea';

        try {
            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();

            if (true === $this->_requisicao->isPost()) {
                // pega o próximo id
                $dados['INCI_DT_INCISO'] = new Zend_Db_Expr('SYSDATE');

                // retira a posição para poder fazer nextval no sequence
                unset($dados['ALIN_ID_ALINEA']);
            }

            // Efetua validações e inclusão do inciso
            $classe = 'Orcamento_Business_Negocio_Alinea';
            $metodo = 'validacaoRN';

            $this->incluirEditarComValidacao($dados, $classe, $metodo, $dados);
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function editarAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Alínea';

        try {
            // Busca dados do formulário após o post e ação incluir
            $cod = $this->_getParam('cod');

            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();
            $dados['ALIN_ID_ALINEA'] = $cod;

            if ($this->_requisicao->isPost()) {
                $paramValidacao = array();
                $paramValidacao['id'] = $dados['ALIN_ID_ALINEA'];
                $paramValidacao['novo'] = $dados['ALIN_VL_ALINEA'];
                $paramValidacao['inciso'] = $dados['ALIN_ID_INCISO'];
            }

            // Efetua validações e edição do inciso
            $classe = 'Orcamento_Business_Negocio_Alinea';
            $metodo = 'validacaoRN093Update';

            $this->incluirEditarComValidacao($dados, $classe, $metodo, $paramValidacao);
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }

//        $this->editar();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Alínea';

        $dados = $this->_requisicao->getPost();
        
        if ($this->_requisicao->isPost() && $dados['cmdExcluir'] === "Sim") {
            $parametros = $this->trataParametroGet('cod');
            $negocio = new Orcamento_Business_Negocio_Alinea();

            if ($negocio->consultarExistRegraAssociada($parametros)) {
                $this->operacaoErro($negocio::MENSAGEM_030);
                return;
            }
        }

        // Exclusão do registro
        $this->excluirComValidacao();
    }

    public function ajaxcomboalineaAction()
    {

        $codigo = $this->getRequest()->getPost();
        $facade = new Orcamento_Facade_Alinea ();
        $combo = $facade->retornaComboAlinea($codigo['IMPO_ID_INCISO']);

        echo $combo;

    }

    public function ajaxretornaalineaAction()
    {
        $codigo = $this->_getParam('cod');
        
        $facade = new Orcamento_Facade_Alinea ();
        $alinea = $facade->retornaAlinea( $codigo );

        echo $alinea;
    }

}
