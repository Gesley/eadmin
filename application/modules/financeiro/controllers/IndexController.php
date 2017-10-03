<?php

class Financeiro_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = "e-Financeiro";
    }

    public function indexAction()
    {
        // action body
        exit ('financeiro');
    }
}