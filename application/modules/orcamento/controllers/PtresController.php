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
 * Disponibiliza as funcionalidades ao usuário sobre programa de trabalho
 * resumido.
 *
 * @category Orcamento
 * @package Orcamento_PtresController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_PtresController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'PTRES' );
        
        // Define a classe facade
        $this->defineFacade ( 'Ptres' );
                
        // Conforme oriental na tag @tutorial
        parent::init ();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function indexAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de PTRES (programas de trabalho resumido)';

        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar PTRES';
        
        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function incluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir PTRES';
        
        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function editarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar PTRES';
        
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
        $funcionalidade = 'Listagem de PTRES (programas de trabalho resumido)';
        
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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir PTRES';
        
        // Exclusão do registro
        $this->excluir ( true );
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar PTRES';
        
        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

    public function ajaxptresAction ()
    {
        $cod = $this->_getParam ( 'term', '' );
        $negocio = new Trf1_Orcamento_Negocio_Ptres ();
        $ptres = $negocio->getPtresAjax ( $cod );
        
        $fim = count ( $ptres );
        
        for ( $i = 0; $i < $fim; $i ++ ) {
            $ptres [ $i ] = array_change_key_case ( $ptres [ $i ], CASE_LOWER );
        }
        
        $this->_helper->json->sendJson ( $ptres );
    }

}