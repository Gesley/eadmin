<?php

class Sosti_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected function _initAutoload() {
        $this->bootstrap('frontController');

        $autoloader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => 'Sosti',
                    'basePath' => APPLICATION_PATH . '/modules/sosti',
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

        $autoloader = new Zend_Loader_Autoloader_Resource(array(
                    'namespace' => '',
                    'basePath' => realpath(APPLICATION_PATH . '/../library/PHPExcel/Classes'),
                    'resourceTypes' => array(
                        'PHPExcel' => array(
                            'path' => 'PHPExcel',
                            'namespace' => 'PHPExcel'
                        ),
                    )
                ));
        $autoloader->addResourceType('PHPExcel', 'PHPExcel/', 'PHPExcel');

        return $autoloader;
    }

}