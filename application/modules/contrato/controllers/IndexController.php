<?php

class Contrato_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Contrato";
    }

    public function indexAction()
    {
        // action body
        exit ('contrato');
    }
}