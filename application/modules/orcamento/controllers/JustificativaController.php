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
 * Disponibiliza as funcionalidades ao usuário sobre Justificativa
 *
 * @category Orcamento
 * @package Orcamento_JustificativaController
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_JustificativaController extends Orcamento_Business_Tela_Crud
{

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init ()
    {
        // Título apresentado no Browser
        $this->defineTituloBrowser ( 'Justificativa' );

        // Define a classe facade
        $this->defineFacade ( 'Justificativa' );

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
        $funcionalidade = 'Justificativa';

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
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Justificativa';

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
        // Título da tela (action)
        $this->view->telaTitle = 'Incluir Justificativa';

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
        $this->view->telaTitle = 'Editar Justificativa';

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
        $this->view->telaTitle = 'Excluir Justificativa';

        // Exclusão do registro
        $this->excluir ( true );
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
        $this->view->telaTitle = 'Restaurar Justificativa';

        // Restauração de registro logicamente excluído
        $this->restaurar ();
    }

}
