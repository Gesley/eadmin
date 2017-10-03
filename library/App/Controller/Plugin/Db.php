<?php

Class App_Controller_Plugin_Db extends Zend_Controller_Plugin_Abstract 
{

    public function preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        $module = strtolower($request->getModuleName());
        $resource = Zend_Controller_Front::getInstance ()->getParam('bootstrap')->getPluginResource('multidb');
        $arrayResource = array(
            'default' => 'guardiao',
            'admin'   => 'guardiao',
            'tarefa'  => 'sosti',
            'os'      => 'sosti',
            'orcamento' => 'orcamento'
        );
        $dbName = ($arrayResource[$module])?($arrayResource[$module]):($module);
        $db = $resource->getDb($dbName);
        Zend_Db_Table::setDefaultAdapter($db);
    }

}
