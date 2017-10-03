<?php

/**
 * Contém controller da aplicação
 * e-Admin
 * e-Orçamento
 * Controller
 * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
 */

/**
 * Disponibiliza as funcionalidades ao usuário sobre contrato.
 * @category Orcamento
 * @package Orcamento_ContratoController
 * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_ContratoController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function init () {

        // Título apresentado no Browser
        $this->defineTituloBrowser('Contrato');

        // Define a classe facade
        $this->defineFacade('Contrato');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function indexAction () {

        // Nome da funcionalidade
        $funcionalidade = 'Listagem de contratos';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function detalheAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar contrato';

        // Exibição de um registro
        $this->detalhe();
    }

    /**
     * Funcionalidade de inclusão de um único registro
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function incluirAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Incluir contrato';

        // Inclusão do registro
        $this->incluir();
    }

    /**
     * Funcionalidade de edição de um único registro
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function editarAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Editar contrato';

        // Edição do registro
        $this->editar();
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function excluirAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Excluir contato';

        // Exclusão do registro
        $this->excluir(true);
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     * @author Victor Eduardo Barreto <vesilva1@latam.stefanini.com>
     */
    public function restaurarAction () {

        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar contato';

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

}
