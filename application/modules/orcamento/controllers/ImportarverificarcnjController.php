<?php

/**
 * Contém controller da aplicação.
 * 
 * e-Admin
 * e-Orçamento
 * Controller
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza controller de importação.
 *
 * @category Orcamento
 * @package Orcamento_ImportarVerificarCNJController
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2015 <http://www.trf1.jus.br>
 */
class Orcamento_ImportarverificarcnjController extends Zend_Controller_Action {

    /**
     * Método init para ser executado no início de cada action
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init() {
        parent::init();
    }
    
    public function indexAction() {
        
    }

    /**
     * Funcionalidade padrão que exibe a listagem dos registros
     *
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function ajaxverificarimportadoAction() {
        // dados da requisição
        $dados = $this->_request->getPost();

        $negocial = new Orcamento_Business_Importacao_Base();
        $retorno = $negocial->verificarExisteCadastro($dados);

        $this->_helper->json->sendJson($retorno);
    }

}
