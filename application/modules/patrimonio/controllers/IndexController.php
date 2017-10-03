<?php

class Patrimonio_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Patrimônio';
    }

    public function indexAction()
    {
        // action body
        exit ('patrimonio');
    }
}