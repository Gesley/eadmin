<?php

class Portaria_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Portaria';
    }

    public function indexAction()
    {
        // action body
        exit ('portaria');
    }
}