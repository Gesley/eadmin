<?php

class App_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract {

    protected $_auth = null;
    protected $_acl = null;
    protected $_flashMessenger = null;

    public function __construct (Zend_Auth $auth, Zend_Acl $acl) {
        $this->_auth = $auth;
        $this->_acl = $acl;
        $this->_flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }

    public function preDispatch (Zend_Controller_Request_Abstract $request) {

        defined('TESTING_MODE') || define('TESTING_MODE', false);

        if (TESTING_MODE == true) {
            $aNamespace = new Zend_Session_Namespace('userNs');
            if ($aNamespace->perfil != "") {
                $role = $aNamespace->perfil;
            } else {
                $role = 'guest';
            }

            $controller = strtolower($request->getControllerName());
            $action = strtolower($request->getActionName());
            $module = strtolower($request->getModuleName());
            $resource = $module . ':' . $controller;
        } else {
            $aNamespace = new Zend_Session_Namespace('userNs');
            if ($aNamespace->perfil != "") {
                $role = $aNamespace->perfil;
            } else {
                $role = 'guest';
            }

            $controller = strtolower($request->getControllerName());
            $action = strtolower($request->getActionName());
            $module = strtolower($request->getModuleName());
            $resource = $module . ':' . $controller;

            if (!$this->_acl->isAllowed($role, $resource, $action)) {
                if ($this->_auth->hasIdentity()) {
                    $request->setModuleName('default');
                    $request->setControllerName('error');
                    $request->setActionName('nopermission');
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'status' => 'error',
                        'message' => 'Favor logar novamente.'
                    ));

                    $request->setModuleName('default');
                    $request->setControllerName('login');
                    $request->setActionName('index');
                }
            }
        }
    }

}
