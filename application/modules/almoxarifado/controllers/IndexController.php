<?php

class Almoxarifado_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Almoxarifado";
    }

    public function indexAction()
    {
        // action body
        Zend_Debug::dump('almoxarifado');
    }
}