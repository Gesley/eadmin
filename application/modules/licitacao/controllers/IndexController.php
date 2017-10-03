<?php

class Licitacao_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Licitação";
    }

    public function indexAction()
    {
        // action body
        exit ('licitacao');
    }
}