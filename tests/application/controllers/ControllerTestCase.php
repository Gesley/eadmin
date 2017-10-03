<?php

require_once 'Zend/Application.php';
require_once 'Zend/Test/PHPUnit/ControllerTestCase.php';
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/Interface.php';
require_once 'Zend/Acl.php';
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'App/Auth/Adapter/Db.php';
require_once 'Zend/Test/PHPUnit/DatabaseTestCase.php';
require_once 'ConexaoBaseDados.php';
require_once 'App/Controller/Plugin/Db.php';

/**
 * Classe responsável por iniciar a aplicação para que seja possível os testes.
 * Todas as classes de teste devem estender esta classe.
 * @abstract
 * @copyright (c) 2015 TRF1.
 * @author Equipe desenvolvimento Stefanini <www.stefanini.com>
 * @version 1.1
 */
abstract class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase {

    protected $application;

    protected function setUp () {

        $this->bootstrap = array($this, 'appBootstrap');
        parent::setUp();
    }

    public function appBootstrap () {

        $this->application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $this->application->bootstrap();

        /**
         * Fix for ZF-8193
         * http://framework.zend.com/issues/browse/ZF-8193
         * Zend_Controller_Action->getInvokeArg('bootstrap') doesn't work
         * under the unit testing environment.
         */
        $front = Zend_Controller_Front::getInstance();

        if ($front->getParam('bootstrap') === null) {
            $front->setParam('bootstrap', $this->application->getBootstrap());
        }
    }

    /**
     * Função responsável pela autenticação no e-Admin.
     * @param String $matricula Matricula de acesso no e-Admin
     * @param String $senha Senha para acesso no e-Admin
     * @param String $banco Banco a ser acessado no e-Admin
     * @author Equipe desenvolvimento Stefanini <www.stefanini.com>
     * @copyright (c) 2015 TRF1.
     * @version 1.1
     */
    protected function autenticar ($matricula, $senha, $banco) {

        # Configura parametros.
        $this->request->setPost(array(
            'COU_COD_MATRICULA' => $matricula,
            'COU_COD_PASSWORD' => $senha,
            'COU_NM_BANCO' => $banco,
            'Conectar' => 'Conectar'
        ));

        # Configura metodo de envio.
        $this->request->setMethod('POST');

        # Faz login.
        $this->dispatch('/login');
    }

}
