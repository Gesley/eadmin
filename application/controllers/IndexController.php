<?php
class IndexController extends Zend_Controller_Action {

    public function init()
    {
        $this->_helper->layout->setLayout('login');
		$this->view->titleBrowser = "e-Admin";
    }

    public function indexAction()
    {
        $this->_redirect('login');
    }

    public function inicioAction()
    {
        $aNamespace = new Zend_Session_Namespace('userNs');
        Zend_Debug::dump($aNamespace->matricula. ' - '. $aNamespace->banco);
    }

	public function infoAction()
	{
	
		phpinfo();
		exit;
	
	}
}