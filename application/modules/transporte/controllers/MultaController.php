<?php

class Transporte_MultaController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
		$this->view->titleBrowser = 'e-Transporte';
    }

    public function indexAction()
    {
        /*paginação*/
        $page = Zend_Filter::filterStatic($this->_getParam('page', 1), 'int');
        /*Ordenação das paginas*/
        $order = $this->_getParam('ordem', 'MULT_ID_MULTA');
        $direction = $this->_getParam('direcao', 'ASC');
        $order_aux = $order.' '.$direction;
        ($direction == 'ASC') ? ($direction = 'DESC') : ($direction = 'ASC');
        /*Ordenação*/

        $table = new Application_Model_DbTable_TraTbMultMulta();
        $select = $table->select()->order($order_aux);

        $paginator = Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber($page)
                   ->setItemCountPerPage(15);
        $this->view->ordem = $order;
        $this->view->direcao = $direction;
        $this->view->data = $paginator;
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('pagination.phtml');
        $this->view->title = 'Multa';
    }
    
    
                public function addAction()
    {
        $this->view->title = 'Cadastrar nova Multa';
        $form = new Transporte_Form_Multa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbMultMulta();
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                unset($data['SSER_ID_SERVICO']);
                $message = $data['SSER_DS_SERVICO'];
                $row = $table->createRow($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "O serviço: $message foi cadastrado!", 'status' => 'success'));
                return $this->_helper->_redirector('index','multa','transporte');
            }
        }
    }
    
    public function editAction()
    {
        $this->view->title = 'Alterar Multa';
        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
        $form   = new Transporte_Form_Multa();
        $this->view->form = $form;
        $table  = new Application_Model_DbTable_TraTbMultMulta();
        /**
         * Busca pelo id da linha a ser alterada
         */
        if ($id) {
            $row = $table->fetchRow(array('MULT_ID_MULTA = ?' => $id));
            if ($row) {
                $data = $row->toArray();
                $form->populate($data);
            }
        }
        /**
         * Se a requisição for post pega os dados postados pelo formulário e atribui ao array $data
         */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if ($form->isValid($data)) {
                $message = $data['MULT_ID_MULTA'];
                $row = $table->find($data['MULT_ID_MULTA'])->current();
                $row->setFromArray($data);
                $row->save();
                $this->_helper->flashMessenger ( array('message' => "A multa: $message foi atualizada!", 'status' => 'success'));
                return $this->_helper->_redirector('index','multa','transporte');
            }
        }
//    public function formAction()
//    {
//        $id     = Zend_Filter::FilterStatic($this->_getParam('id'),'int');
//        $form   = new Transporte_Form_Multa();
//        $table  = new Application_Model_DbTable_TraTbMultMulta();
//
//        if ($id) {
//            $row = $table->fetchRow(array('MULT_ID_MULTA = ?' => $id));
//            if ($row) {
//                $data = $row->toArray();
//                $form->populate($data);
//            }
//        }
//        $this->view->form = $form;
//    }
//
//    public function saveAction()
//    {
//        $form = new Transporte_Form_Multa();
//        $table = new Application_Model_DbTable_TraTbMultMulta();
//
//        if ($this->getRequest()->isPost()){
//            $data = $this->getRequest()->getPost();
//            if ($form->isValid($data)) {
//                if (isset($data['MULT_ID_MULTA']) && $data['MULT_ID_MULTA']) {
//                    $row = $table->find($data['MULT_ID_MULTA'])->current();
//                    $row->setFromArray($data);
//                } else {
//                    unset($data['MULT_ID_MULTA']);
//                    $row = $table->createRow($data);
//                }
//                $id = $row->save();
//                $this->_helper->flashMessenger ( array('message' => 'Registrado!', 'status' => 'success'));
//                return $this->_helper->_redirector('index','motorista','transporte', array('id' => $id));
//
//            } else {
//                $form->populate($data);
//                $this->view->form = $form;
//                $this->render('form');
//            }
//        }
//    }
}
}

