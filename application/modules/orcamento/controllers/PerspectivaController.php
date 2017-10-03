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
 * Disponibiliza as funcionalidades sobre a controller de Perspectiva.
 *
 * @category Orcamento
 * @package Orcamento_ImportacaoncController
 * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_PerspectivaController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Listagem de Perspectiva' );

        // Define a Business
        $this->business = new Orcamento_Business_Negocio_Perspectiva();

        // Define a classe facade
        $this->defineFacade ( 'Perspectiva' );

        // Conforme oriental na tag @tutorial
        parent::init ();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Perspectiva';

        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Importarnc listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Perspectiva';

        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Perspectiva';

        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Perspectiva';

        // Edição do registro
        $this->editar ();
    }

    /**
     * Listagem de ptres excluidos
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluidosAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de Perspectiva';
        
        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Perspectiva';

        // Exclusão do registro
        $this->excluir ();
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Perspectiva';

        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

    private function erroOperacao ( $mensagemErro )
    {
        $erro = $mensagemErro;

        // Registra o erro
        $log = new Trf1_Orcamento_Log ();
        $requisicao = $this->getRequest ();
        $log->gravaLog ( $requisicao, $erro, zend_log::ERR );

        $this->_helper->flashMessenger ( array ( message => $erro, 'status' => 'error' ) );
    }

}











