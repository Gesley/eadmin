<?php
class Contrato_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
    	$this->bootstrap('frontController');
		    	
        $autoloader = new Zend_Loader_Autoloader_Resource (array(
            'namespace' => 'Contrato',
            'basePath'  => APPLICATION_PATH . '/modules/contrato',
        	
	        'resourceTypes' => array (
		        'form' => array(
                	'path'      => 'forms',
                	'namespace' => 'Form'
        		),
        	)
        ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');
        return $autoloader;
    }

}