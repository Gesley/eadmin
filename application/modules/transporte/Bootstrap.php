<?php
class Transporte_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initAutoload()
    {
    	$this->bootstrap('frontController');
		    	
        $autoloader = new Zend_Loader_Autoloader_Resource (array(
            'namespace' => 'Transporte',
            'basePath'  => APPLICATION_PATH . '/modules/transporte',
        	
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

//    public function _initDbRegistry()
//     {
//        $this->bootstrap('multidb');
//        $multidb = $this->getPluginResource('multidb');
//        //$dbName = $this->loadDatabase('trf1dsv');
//        $dbCustomer = array('host' => '172.16.3.216',
//                            'username' => 'TRA',
//                            'password' => 'MOTORISTA',
//                            'dbname' => 'trf1dsv',
//                            'default' => true,);
//
//	$dbCustomerAdapter = new Zend_Db_Adapter_Pdo_Oci($dbCustomer);
//
//        Zend_Db_Table::setDefaultAdapter($dbCustomerAdapter);
//        Zend_Registry::set('sisad', $dbCustomerAdapter);
//        Zend_Registry::set('transporte', $multidb->getDb('transporte'));
//     }

}