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
 * @package Orcamento_CreditoController
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_CreditoController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Créditos' );
        
        // Define a classe facade
        $this->defineFacade ( 'credito' );
        
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
        $funcionalidade = 'Listagem de créditos';
        
        // Exibir listagem de registros
        $this->listar ( $funcionalidade );
    }

    /**
     * Funcionalidade que exibe a listagem dos registros inconsistentes
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function inconsistenciaAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem das inconsistências nos créditos';
        
        // Título da tela (action)
        $this->view->telaTitle = $funcionalidade;
        
        // Retorna as opções para confecção do grid
        $opcoesGrid = $this->retornaOpcoesGrid ();
        
        // Define a opção 'funcionalidade' com a opção passada por parâmetro
        $opcoesGrid [ 'funcionalidade' ] = $funcionalidade;
        
        // Define opção 'dados' com o retorno dos dados a serem exibidos
        $dados = $this->_facade->retornaInconsistencias ( null, false );
        $opcoesGrid [ 'dados' ] = $dados;
        
        // Define a opção 'chavePrimaria' com o retorno do(s) campo(s) chave(s)
        $opcoesGrid [ 'chavePrimaria' ] = $this->retornaChave ();
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        // Define o grid
        $grid = $classeGrid->criaGrid ( $opcoesGrid );
        
        // Exibição do grid
        $this->view->grid = $grid->deploy ();
        
        // Grava em sessão as preferências do usuário para essa grid
        $requisicao = $this->_requisicao;
        $sessao = new Orcamento_Business_Sessao ();
        $sessao->defineOrdemFiltro ( $requisicao );
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function detalheAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar crédito';
        
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
        $this->view->telaTitle = 'Incluir crédito';
        
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
        $this->view->telaTitle = 'Editar crédito';
        
        // Edição do registro
        $this->editar ();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function excluirAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir créditos';
        
        // Exclusão do registro
        $this->excluir ( true );
    }

    /**
     * Listagem de creditos excluidos
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluidosAction ()
    {
        // Nome da funcionalidade
        $funcionalidade = 'Listagem de créditos';

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
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function restaurarAction ()
    {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar créditos';
        
        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

}