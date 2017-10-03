<?php

class Guardiao_Bootstrap extends Zend_Application_Module_Bootstrap {

    function _initView()
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $view->setEncoding('UTF-8');
        $view->doctype('XHTML1_STRICT');
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        $view->addHelperPath('App/View/Helper/', 'App_View_Helper');
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);
        $view->headMeta('Content-Type', 'text/html; charset=utf-8');
        Zend_Dojo::enableView($view);
        return $view;
    }

    protected function _initAutoload()
    {
        $this->bootstrap('frontController');

        $autoloader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => 'Guardiao',
                    'basePath' => APPLICATION_PATH . '/modules/guardiao',
                    'resourceTypes' => array(
                        'form' => array(
                            'path' => 'forms',
                            'namespace' => 'Form'
                        ),
                    )
                ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');
        return $autoloader;
    }

    protected function _initPlugins() {
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new Trf1_Guardiao_Plugin_Ajuda());
    }

}