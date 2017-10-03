<?php

class Sisad_Bootstrap extends Zend_Application_Module_Bootstrap {

//	function _initView()
//	{
//		$view = Zend_Layout::getMvcInstance()->getView();
//		$view->setEncoding('UTF-8');
//        $view->doctype('XHTML1_STRICT');
//        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
//    	$view->addHelperPath('App/View/Helper/', 'App_View_Helper');
//    	$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
//		$viewRenderer->setView($view);
//    	$view->headMeta('Content-Type','text/html; charset=utf-8');
//    	Zend_Dojo::enableView($view);
//        return $view;
//	}

    protected function _initAutoload() {
        $this->bootstrap('frontController');

        //Zend_Debug::dump('passou');
        //new Zend_
        $autoloader = new Zend_Loader_Autoloader_Resource(array(
            'namespace' => 'Sisad',
            'basePath' => APPLICATION_PATH . '/modules/sisad',
            'resourceTypes' => array(
                'form' => array(
                    'path' => 'forms',
                    'namespace' => 'Form'
                ), 'form/juntada' => array(
                    'path' => 'forms/juntada',
                    'namespace' => 'Form_Juntada'
                ),
                'form/leitura' => array(
                    'path' => 'forms/leitura',
                    'namespace' => 'Form_Leitura'
                ),
                'form/documento' => array(
                    'path' => 'forms/documento',
                    'namespace' => 'Form_Documento'
                ),
                'form/table' => array(
                    'path' => 'forms/table',
                    'namespace' => 'Form_Table'
                ), /*
              'model' => array(
              'path'      => 'models',
              'namespace' => 'Model',
              ), */
            )
        ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');
        return $autoloader;
    }

//    public function _initDbRegistry()
//    {
//        $this->bootstrap('multidb');
//        $multidb = $this->getPluginResource('multidb');
//        Zend_Registry::set('db_sisad', $multidb->getDb('sisad'));
//    }
    /*
      protected function _initControllerPlugins()
      {
      //$this->bootstrap('FirePHP');
      $this->bootstrap('frontController');
      $frontController = $this->getResource('frontController');
      $frontController->registerPlugin(new App_Controller_Plugin_CustomView());
      $frontController->registerPlugin(new App_Controller_Plugin_DojoLayer());
      } */
    protected function _initControllerPlugins() {
        $this->bootstrap('FrontController');
        /*
          $AcessoCaixaUnidade  = new App_Controller_Plugin_AcessoCaixaUnidade();
          Zend_Registry::set('AcessoCaixaUnidade', $AcessoCaixaUnidade);
         * 
         */
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin(new App_Controller_Plugin_AcessoCaixaUnidade());
    }

}