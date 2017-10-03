<?php

class Transporte_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Transporte';
    }

/*    public function indexAction()
    {
        // action body
        $table = new Application_Model_DbTable_TraTbVeicVeiculo();
        $select = $table->select();
        Zend_Debug::dump($select);
        exit;
    }*/

    public function indexAction()
    {
		$this->view->title = "Seja Bem-Vindo ao Sistema e-Transporte!";
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        $order = $this->_getParam('ORDER', 'VEIC_ID_VEICULO');
        $direction = $this->_getParam('direction', 'ASC');

        $table = new Application_Model_DbTable_TraTbVeicVeiculo();
        $select = $table->select();

        /*Zend_Debug::dump($select);
        exit;*/
        
        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage(10);

        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');

        $this->view->dir = array('VEIC_ID_VEICULO' => 'ASC',
                                 'description'  => 'ASC');
        $this->view->data = $paginator;
    }
}