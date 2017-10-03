<?php

class Sarh_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload() {
        $this->bootstrap('frontController');

        //Zend_Debug::dump('passou');
        //new Zend_
        $autoloader = new Zend_Loader_Autoloader_Resource(array(
            'namespace' => 'Sarh',
            'basePath' => APPLICATION_PATH . '/modules/sarh',
            'resourceTypes' => array(
                'form' => array(
                    'path' => 'forms',
                    'namespace' => 'Form'
                )
            )
        ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');
        return $autoloader;
    }

    protected function _initControllerPlugins() {
        $this->bootstrap('FrontController');
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new App_Controller_Plugin_AcessoCaixaUnidade());
    }

}