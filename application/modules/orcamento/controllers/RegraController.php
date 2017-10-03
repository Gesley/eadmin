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
class Orcamento_RegraController extends Orcamento_Business_Tela_Crud {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function init () {
        // Título apresentado no Browser
        $this->defineTituloBrowser('Regra');

        // Define a classe facade
        $this->defineFacade('Regra');

        // Conforme oriental na tag @tutorial
        parent::init();
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function indexAction () {
        // Nome da funcionalidade
        $funcionalidade = 'Pesquisar Regras de Ajuste';

        // Exibir listagem de registros
        $this->listar($funcionalidade);
    }

    /**
     * Funcionalidade de exibição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function detalheAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Visualizar Regra';

        // Exibição de um registro
        $this->detalhe();

        // Instancia o formulário para tratamento
        $formulario = new Orcamento_Form_Regra ();
        $this->view->formulario = $formulario;

        $cod = $this->_getParam('cod');

        // Busca as regras aplicadas
        if ($cod) {

            $modelo = new Orcamento_Model_DbTable_Elemregr ();
            $regras = $modelo->fetchAll("ELRG_ID_REGRA_EXERCICIO = $cod");
            $this->view->codigo = $cod;
            $this->view->regras = $regras;
        }
    }

    /**
     * Funcionalidade de inclusão de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function incluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Cadastrar Regra';

        // Inclusão do registro
        $this->incluir();
    }

    /**
     * Funcionalidade de edição de um único registro
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function editarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Editar Regra';

        // Edição do registro
        $this->editar();

        $cod = $this->_getParam('cod');

        // Busca regras aplicadas
        if ($cod) {
            $modelo = new Orcamento_Model_DbTable_Elemregr ();
            $regras = $modelo->fetchAll("ELRG_ID_REGRA_EXERCICIO = $cod");
            $this->view->regras = $regras;
        }
    }

    /**
     * Funcionalidade de exclusão lógica de registros realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function excluirAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Excluir Regra';

        // Exclui os elementos antes de excluir uma regra
        if ($this->_request->isPost()) {

            $cod = $this->_getParam('cod');
            if ($cod) {
                $elemento = new Orcamento_Business_Negocio_Elementosregra ();
                $exclui = $elemento->excluiElementos($cod);
            }
        }

        // Exclusão do registro
        $this->excluir(false);
    }

    /**
     * Funcionalidade para restauração de registros logicamente excluídos
     * realizadas em lote
     *
     * @author Gesley B Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function restaurarAction () {
        // Título da tela (action)
        $this->view->telaTitle = 'Restaurar Regra';

        // Restauração de registro logicamente excluído
        $this->restaurar();
    }

    public function aplicarAction () {
        try {
            // Retorna parâmetros informados via get, após validações
            $parametros = $this->trataParametroGet('cod');

            // Retorna o resultado da operação
            $resultado = $this->_facade->aplicar($parametros);

            $acao = 'aplicar';

            if (!$resultado ['sucesso']) {
                // Define mensagem de erro e status
                $msg = $this->retornaMensagem($acao, false);
                $msg .= '<br />' . $resultado ['msgErro'];

                // Exibe mensagem de erro na próxima tela
                $this->operacaoErro($msg);
            }

            // Define mensagem de sucesso
            $msg = $this->retornaMensagem($acao, true);

            // Apresenta mensagem de sucesso na próxima tela
            $this->operacaoSucesso($msg);
        } catch (Zend_Exception $e) {
            $msg = "";
            $msg .= $resultado ['msgErro'];
            $msg .= $e->getMessage();

            $this->operacaoErro($msg);
        }
    }

}
