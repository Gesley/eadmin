<?php

class Compras_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Compras";
    }

    public function indexAction()
    {
        // action body
        //exit ('compras');
    }
}