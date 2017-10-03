<?php
class Soseg_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
    	$this->bootstrap('frontController');

        $autoloader = new Zend_Loader_Autoloader_Resource (array(
            'namespace' => 'Soseg',
            'basePath'  => APPLICATION_PATH . '/modules/soseg',
        	
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