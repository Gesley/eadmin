<?php

class Arquivo_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Arquivo";
    }

    public function indexAction()
    {
    }
}