<?php
class Patrimonio_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
    	$this->bootstrap('frontController');
		    	
        $autoloader = new Zend_Loader_Autoloader_Resource (array(
            'namespace' => 'Patrimonio',
            'basePath'  => APPLICATION_PATH . '/modules/patrimonio',
        	
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