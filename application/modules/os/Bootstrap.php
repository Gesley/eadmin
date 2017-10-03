<?php
class Os_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload() {
        $this->bootstrap('frontController');

        $autoloader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => 'Os',
                    'basePath' => APPLICATION_PATH . '/modules/os',
                    'resourceTypes' => array(
                        'form' => array(
                            'path' => 'forms',
                            'namespace' => 'Form'
                        ),
                        'facades' => array(
                            'path' => 'facades',
                            'namespace' => 'Facade'
                        ),
                        'business' => array(
                            'path' => 'business',
                            'namespace' => 'Business'
                        )
                    )
                ));
        $autoloader->addResourceType('Form', 'forms/', 'Form');

        return $autoloader;
    }
}