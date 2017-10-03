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
 * Disponibiliza as funcionalidades ao usuário sobre REGRACNJ.
 *
 * @category Orcamento
 * @package Orcamento_RegracnjController
 * @author Sandro Maceno <smaceno@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_RegracnjController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function init() {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Regra CNJ');
        
        // Define a classe facade
        $this->defineFacade('regracnj');

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
        $funcionalidade = 'Pesquisar Regras';
               
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
        $this->view->telaTitle = 'Visualizar Regra';
        
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
        $this->view->telaTitle = 'Cadastra Regra';

        try {
            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();

            // Efetua validações e inclusão do inciso
            $classe = 'Orcamento_Business_Negocio_Regracnj';
            $metodo = 'validacaoRN';

            if ($this->_requisicao->isPost()) {
                $this->view->alinea_selected = $dados['REGC_ID_ALINEA'];
            }

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
        $this->view->telaTitle = 'Editar Regra';
               
        try {
            // Busca dados do formulário após o post e ação incluir
            $cod = $this->_getParam('cod');
            
            // Busca dados do formulário após o post e ação incluir
            $dados = $this->_requisicao->getPost();
            $dados['REGC_ID_REGRA'] = $cod;
            $dados['REGC_DT_REGRA'] = new Zend_Db_Expr('SYSDATE');

            // Efetua validações e edição do inciso
            $classe = 'Orcamento_Business_Negocio_Regracnj';
            $metodo = 'validacaoRNUpdate';
            
            $registro = $this->retornaRegistro('editar', $cod);
            
            $this->view->alinea_selected = $registro['REGC_ID_ALINEA'];

            $this->incluirEditarComValidacao($dados, $classe, $metodo, $dados);
        } catch (Exception $e) {
            throw new Zend_Exception($e->getMessage());
        }
        
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function excluirAction() {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Regra';

        // Exclusão do registro
        $this->excluirComValidacao();
    }
    
    /**
     * Funcionalidade padrão que exibe a listagem dos alinea com base no inciso.
     *
     * @author Sandro Maceno <smaceno@stefanini.com>
     */
    public function ajaxmontacomboregracnjAction() {
        // dados da requisição
        $dados = $this->_request->getPost();
        
        $negocial = new Orcamento_Business_Negocio_Regracnj();
        $retorno = $negocial->verificarcomboregracnj($dados);
        
        $this->_helper->json->sendJson($retorno);
        
    }
        
}