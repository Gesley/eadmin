<?php

class ErrorController extends Zend_Controller_Action {
    
    public function init()
    {
        /**/
        $this->_helper->layout->setLayout('error');
    }

    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        if (!$errors) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Página não encontrada';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Sistema indisponível, tente novamente dentro de instantes.';
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }
    
    public function nopermissionAction()
    {
        if (defined('APPLICATION_ENV') && APPLICATION_ENV == 'development') {
            $this->view->message = 'Acesso não autorizado. <br/> Verifique acesso no e-Guardião ou se o caminho especificado existe.';
        } else {
            $this->getResponse()->setHttpResponseCode(401);
            $this->view->message = 'Acesso não autorizado. <br/> ';
            $this->render();
        }
    }

    public function getLog() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

}

