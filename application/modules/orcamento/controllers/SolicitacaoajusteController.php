<?php
/**
 * Contém controller da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre Solicitacao de Ajuste.
 *
 * @category Orcamento
 * @package Orcamento_SolicitacaoajusteController
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_SolicitacaoajusteController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Solicitacões de Ajuste' );
        
        // Define a classe facade
        $this->defineFacade ( 'solicitacaoajuste' );

        // Business
        $this->_business = new Orcamento_Business_Negocio_Solicitacaoajuste();

        // Conforme oriental na tag @tutorial
        parent::init ();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Solicitacões de Ajuste';
        
        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction ()
    {
        // Configura o titulo
        $solicitacao = $this->_getParam('cod');
        $tipo = $this->_business->retornaTipo($solicitacao);
        $this->view->telaTitle = 'Visualizar '.$tipo["SOLA_TP_SOLICITACAO"];
        
        // Exibição de um registro
        $this->detalhe ();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction ()
    {
        // Altera o titulo da tela de acordo com o tipo de solicitacao
        $titulo = $this->_getParam('solicitar');
        if(!isset($titulo)){
            $titulo = 'Ajuste';
        }

        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Solicitacão de '.$titulo;

        // Verifica o tipo de solicitacao
        $this->view->tipo = $this->_getParam('tipo');

        // configura a despesa no form
        if( $this->_getParam('despesa') != "" ){
            $this->view->despesa = $this->_getParam('despesa');
        }

        // configura o valor no form
        if( $this->_getParam('base') != "" ){
            $this->view->base = $this->_getParam('base');
        }
        
        // configura o valor no form
        if( $this->_getParam('acrescimo') != "" ){
            $this->view->acrescimo = $this->_getParam('acrescimo');
        }
                
        // Inclusão do registro
        $this->incluir ();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Solicitacao de Ajuste';

        // Edição do registro
        $this->editar ();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Solicitacao de Ajuste';
        
        // Exclusão do registro
        $this->excluir ( true );
    }

   /**
     * Listagem de solicitadoes excluidas
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluidosAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Solicitacões de Ajuste';
        
        // remove cache
        $cache = new Trf1_Cache();
        $controller = Zend_Controller_Front::getInstance()->getRequest()
                ->getControllerName();
        $cache->excluirCache("orcamento_{$controller}_listagem");

        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Solicitacao de Ajuste';
        
        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

}